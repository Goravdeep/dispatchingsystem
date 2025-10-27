<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/database.php';

// Add new client
if (isset($_POST['add_client'])) {
    $company_name = $conn->real_escape_string($_POST['company_name']);
    $contact_person = $conn->real_escape_string($_POST['contact_person']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $tax_id = $conn->real_escape_string($_POST['tax_id']);
    $payment_terms = $conn->real_escape_string($_POST['payment_terms']);
    
    $sql = "INSERT INTO clients (company_name, contact_person, email, phone, address, tax_id, payment_terms) 
            VALUES ('$company_name', '$contact_person', '$email', '$phone', '$address', '$tax_id', '$payment_terms')";
    
    if ($conn->query($sql)) {
        $message = "Client added successfully!";
    } else {
        $error = "Error adding client: " . $conn->error;
    }
}

// Update client
if (isset($_POST['update_client'])) {
    $client_id = $_POST['client_id'];
    $company_name = $conn->real_escape_string($_POST['company_name']);
    $contact_person = $conn->real_escape_string($_POST['contact_person']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $tax_id = $conn->real_escape_string($_POST['tax_id']);
    $payment_terms = $conn->real_escape_string($_POST['payment_terms']);
    
    $sql = "UPDATE clients SET 
            company_name='$company_name', 
            contact_person='$contact_person', 
            email='$email', 
            phone='$phone', 
            address='$address',
            tax_id='$tax_id',
            payment_terms='$payment_terms'
            WHERE id='$client_id'";
    
    if ($conn->query($sql)) {
        $message = "Client updated successfully!";
    } else {
        $error = "Error updating client: " . $conn->error;
    }
}

// Delete client
if (isset($_GET['delete_client'])) {
    $client_id = $_GET['delete_client'];
    
    // Check if client has dispatches
    $check_sql = "SELECT COUNT(*) as dispatch_count FROM dispatches WHERE client_id = '$client_id'";
    $result = $conn->query($check_sql);
    $dispatch_count = $result->fetch_assoc()['dispatch_count'];
    
    if ($dispatch_count == 0) {
        $sql = "DELETE FROM clients WHERE id = '$client_id'";
        if ($conn->query($sql)) {
            $message = "Client deleted successfully!";
        } else {
            $error = "Error deleting client: " . $conn->error;
        }
    } else {
        $error = "Cannot delete client. They have $dispatch_count dispatch(es) associated.";
    }
}

// Get client data for editing
$edit_client = null;
if (isset($_GET['edit_client'])) {
    $client_id = $_GET['edit_client'];
    $result = $conn->query("SELECT * FROM clients WHERE id = '$client_id'");
    $edit_client = $result->fetch_assoc();
}

$clients = $conn->query("SELECT c.*, 
                         (SELECT COUNT(*) FROM dispatches WHERE client_id = c.id) as total_dispatches,
                         (SELECT SUM(total_cost) FROM dispatches WHERE client_id = c.id AND status='delivered') as total_revenue
                         FROM clients c 
                         ORDER BY c.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Clients - Truck Dispatching</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h1>🚛 Truck Dispatching System</h1>
        </div>
        <div class="header-actions">
            <button class="hamburger-menu" onclick="toggleSidebar()">☰</button>
            <a href="login.php?logout=1" class="logout">Logout</a>
        </div>
    </div>

    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="sidebar">
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="dispatches.php"><i class="fas fa-shipping-fast"></i> Dispatches</a>
        <a href="trucks.php"><i class="fas fa-truck"></i> Trucks</a>
        <a href="drivers.php"><i class="fas fa-users"></i> Drivers</a>
        <a href="clients.php" class="active"><i class="fas fa-building"></i> Clients</a>
        <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
        <a href="email-log.php"><i class="fas fa-envelope"></i> Email Log</a>
        <a href="email-settings.php"><i class="fas fa-cogs"></i> Email Settings</a>
    </div>
    
    <div class="main-content">
        <h2>🏢 Manage Clients</h2>
        
        <!-- Add/Edit Client Form -->
        <div class="form-container">
            <h3><?php echo $edit_client ? '✏️ Edit Client' : '➕ Add New Client'; ?></h3>
            <form method="POST">
                <?php if ($edit_client): ?>
                    <input type="hidden" name="client_id" value="<?php echo $edit_client['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Company Name:</label>
                        <input type="text" name="company_name" 
                               value="<?php echo $edit_client ? $edit_client['company_name'] : ''; ?>" 
                               placeholder="ABC Construction Co." required>
                    </div>
                    <div class="form-group">
                        <label>Contact Person:</label>
                        <input type="text" name="contact_person" 
                               value="<?php echo $edit_client ? $edit_client['contact_person'] : ''; ?>" 
                               placeholder="John Doe">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" 
                               value="<?php echo $edit_client ? $edit_client['email'] : ''; ?>" 
                               placeholder="contact@company.com">
                    </div>
                    <div class="form-group">
                        <label>Phone:</label>
                        <input type="text" name="phone" 
                               value="<?php echo $edit_client ? $edit_client['phone'] : ''; ?>" 
                               placeholder="+1 (555) 123-4567">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Tax ID:</label>
                        <input type="text" name="tax_id" 
                               value="<?php echo $edit_client ? $edit_client['tax_id'] : ''; ?>" 
                               placeholder="Tax Identification Number">
                    </div>
                    <div class="form-group">
                        <label>Payment Terms:</label>
                        <select name="payment_terms">
                            <option value="Net 15" <?php echo ($edit_client && $edit_client['payment_terms'] == 'Net 15') ? 'selected' : ''; ?>>Net 15</option>
                            <option value="Net 30" <?php echo ($edit_client && $edit_client['payment_terms'] == 'Net 30') ? 'selected' : ''; ?>>Net 30</option>
                            <option value="Net 45" <?php echo ($edit_client && $edit_client['payment_terms'] == 'Net 45') ? 'selected' : ''; ?>>Net 45</option>
                            <option value="Due on receipt" <?php echo ($edit_client && $edit_client['payment_terms'] == 'Due on receipt') ? 'selected' : ''; ?>>Due on receipt</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Address:</label>
                    <textarea name="address" rows="3" placeholder="Full company address"><?php echo $edit_client ? $edit_client['address'] : ''; ?></textarea>
                </div>
                
                <?php if ($edit_client): ?>
                    <button type="submit" name="update_client" class="button">Update Client</button>
                    <a href="clients.php" class="button" style="background: #95a5a6; text-decoration: none;">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_client" class="button">Add Client</button>
                <?php endif; ?>
                
                <?php if (isset($message)): ?>
                    <div class="alert success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Clients List -->
        <div class="table-container">
            <h3>All Clients (<?php echo $clients->num_rows; ?>)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Company Info</th>
                        <th>Contact</th>
                        <th>Business Details</th>
                        <th>Stats</th>
                        <th>Added Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($client = $clients->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo $client['company_name']; ?></strong>
                            <?php if ($client['contact_person']): ?>
                                <br><small>Contact: <?php echo $client['contact_person']; ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($client['email']): ?>
                                ✉️ <?php echo $client['email']; ?><br>
                            <?php endif; ?>
                            <?php if ($client['phone']): ?>
                                📞 <?php echo $client['phone']; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($client['tax_id']): ?>
                                <small>Tax ID: <?php echo $client['tax_id']; ?></small><br>
                            <?php endif; ?>
                            <?php if ($client['payment_terms']): ?>
                                <small>Terms: <?php echo $client['payment_terms']; ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small>Dispatches: <?php echo $client['total_dispatches']; ?></small><br>
                            <small>Revenue: $<?php echo number_format($client['total_revenue'] ?: 0, 2); ?></small>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($client['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="?edit_client=<?php echo $client['id']; ?>" 
                                   class="btn-edit">Edit</a>
                                <a href="?delete_client=<?php echo $client['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete client: <?php echo $client['company_name']; ?>?')"
                                   class="btn-delete">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const hamburger = document.querySelector('.hamburger-menu');
        
        if (!sidebar.contains(event.target) && !hamburger.contains(event.target) && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
    });

    // Close sidebar on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
    });
    </script>
</body>
</html>