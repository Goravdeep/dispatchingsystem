<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'truck_dispatching';

// Create connection
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// Check if tables exist and create them if they don't
$tables_sql = "
CREATE TABLE IF NOT EXISTS trucks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_no VARCHAR(50) UNIQUE NOT NULL,
    model VARCHAR(100) NOT NULL,
    load_capacity DECIMAL(10,2) NOT NULL,
    year INT,
    fuel_type VARCHAR(20),
    notes TEXT,
    status ENUM('available', 'on_route', 'maintenance', 'out_of_service') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    license_number VARCHAR(100) UNIQUE NOT NULL,
    contact_phone VARCHAR(20),
    contact_email VARCHAR(100),
    address TEXT,
    status ENUM('available', 'on_duty', 'off_duty', 'sick', 'vacation') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    tax_id VARCHAR(50),
    payment_terms VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dispatches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dispatch_id VARCHAR(20) UNIQUE NOT NULL,
    truck_id INT,
    driver_id INT,
    client_id INT,
    pickup_address TEXT NOT NULL,
    delivery_address TEXT NOT NULL,
    load_type VARCHAR(100) NOT NULL,
    load_weight DECIMAL(10,2) NOT NULL,
    load_quantity INT NOT NULL,
    status ENUM('pending', 'assigned', 'in_transit', 'delivered', 'cancelled') DEFAULT 'pending',
    total_cost DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ⬇️⬇️⬇️ ADD EMAIL SETTINGS TABLE RIGHT HERE ⬇️⬇️⬇️
CREATE TABLE IF NOT EXISTS email_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    smtp_host VARCHAR(100) DEFAULT 'smtp.gmail.com',
    smtp_port INT DEFAULT 587,
    smtp_username VARCHAR(100),
    smtp_password VARCHAR(100),
    from_email VARCHAR(100),
    from_name VARCHAR(100) DEFAULT 'Truck Dispatching System',
    status_subject VARCHAR(200) DEFAULT 'Dispatch Status Update: {dispatch_id}',
    status_template TEXT,
    invoice_subject VARCHAR(200) DEFAULT 'Invoice for Dispatch: {dispatch_id}',
    invoice_template TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- ⬆️⬆️⬆️ END OF EMAIL SETTINGS TABLE ⬆️⬆️⬆️
";

// Execute each table creation separately
$tables = explode(';', $tables_sql);
foreach ($tables as $table) {
    if (trim($table) != '') {
        $conn->query($table);
    }
}

// Add missing columns if they don't exist
$conn->query("ALTER TABLE trucks ADD COLUMN IF NOT EXISTS year INT AFTER load_capacity");
$conn->query("ALTER TABLE trucks ADD COLUMN IF NOT EXISTS fuel_type VARCHAR(20) AFTER year");
$conn->query("ALTER TABLE trucks ADD COLUMN IF NOT EXISTS notes TEXT AFTER fuel_type");
$conn->query("ALTER TABLE drivers ADD COLUMN IF NOT EXISTS address TEXT AFTER contact_email");
$conn->query("ALTER TABLE clients ADD COLUMN IF NOT EXISTS tax_id VARCHAR(50) AFTER address");
$conn->query("ALTER TABLE clients ADD COLUMN IF NOT EXISTS payment_terms VARCHAR(50) AFTER tax_id");

// Insert sample data only if tables are empty
$truck_count = $conn->query("SELECT COUNT(*) as count FROM trucks")->fetch_assoc()['count'];
$driver_count = $conn->query("SELECT COUNT(*) as count FROM drivers")->fetch_assoc()['count'];
$client_count = $conn->query("SELECT COUNT(*) as count FROM clients")->fetch_assoc()['count'];

if ($truck_count == 0) {
    $conn->query("INSERT INTO trucks (registration_no, model, load_capacity, year, fuel_type, notes) VALUES 
        ('TRK-001', 'Volvo FH16', 25.00, 2022, 'Diesel', 'Regular maintenance done. Good condition.'),
        ('TRK-002', 'Scania R500', 20.00, 2021, 'Diesel', 'New tires installed last month.'),
        ('TRK-003', 'Mercedes Actros', 18.00, 2023, 'Diesel', 'Brand new truck.')
    ");
}

if ($driver_count == 0) {
    $conn->query("INSERT INTO drivers (name, license_number, contact_phone, contact_email, address) VALUES 
        ('John Smith', 'DRV-001', '+1 (555) 123-4567', 'john@company.com', '123 Main St, New York, NY'),
        ('Mike Johnson', 'DRV-002', '+1 (555) 987-6543', 'mike@company.com', '456 Oak Ave, Los Angeles, CA'),
        ('David Wilson', 'DRV-003', '+1 (555) 456-7890', 'david@company.com', '789 Pine St, Chicago, IL')
    ");
}

if ($client_count == 0) {
    $conn->query("INSERT INTO clients (company_name, contact_person, email, phone, address, tax_id, payment_terms) VALUES 
        ('ABC Construction Co.', 'Robert Brown', 'bob@abcconstruction.com', '+1 (555) 111-2222', '123 Main St, New York, NY', 'TAX-12345', 'Net 30'),
        ('XYZ Logistics', 'Sarah Wilson', 'sarah@xyzlogistics.com', '+1 (555) 333-4444', '456 Oak Ave, Los Angeles, CA', 'TAX-67890', 'Net 15'),
        ('City Builders Inc.', 'Michael Davis', 'mike@citybuilders.com', '+1 (555) 555-6666', '789 Pine St, Chicago, IL', 'TAX-11223', 'Due on receipt')
    ");
}

// ⬇️⬇️⬇️ ADD DEFAULT EMAIL SETTINGS RIGHT HERE ⬇️⬇️⬇️
// Insert default email settings
$email_settings_count = $conn->query("SELECT COUNT(*) as count FROM email_settings")->fetch_assoc()['count'];
if ($email_settings_count == 0) {
    $conn->query("INSERT INTO email_settings (id, status_template, invoice_template) VALUES (
        1,
        'Hello {client_name},\\\\n\\\\nYour dispatch status has been updated:\\\\n\\\\nDispatch ID: {dispatch_id}\\\\nNew Status: {new_status}\\\\nLoad Type: {load_type}\\\\nPickup: {pickup_address}\\\\nDelivery: {delivery_address}\\\\n\\\\nThank you for choosing our services!\\\\n\\\\nBest regards,\\\\nTruck Dispatching Team',
        'Hello {client_name},\\\\n\\\\nYour invoice has been generated:\\\\n\\\\nDispatch ID: {dispatch_id}\\\\nAmount: ${total_cost}\\\\nDue Date: {due_date}\\\\n\\\\nPlease make payment within 30 days.\\\\n\\\\nThank you!\\\\nTruck Dispatching Team'
    )");
}
// ⬆️⬆️⬆️ END OF EMAIL SETTINGS DATA ⬆️⬆️⬆️

// Uncomment the line below for debugging
// echo "Database setup completed successfully!";
?>