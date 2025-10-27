<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/database.php';

// Add new driver
if (isset($_POST['add_driver'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $license_number = $conn->real_escape_string($_POST['license_number']);
    $contact_phone = $conn->real_escape_string($_POST['contact_phone']);
    $contact_email = $conn->real_escape_string($_POST['contact_email']);
    $address = $conn->real_escape_string($_POST['address']);
    
    $sql = "INSERT INTO drivers (name, license_number, contact_phone, contact_email, address) 
            VALUES ('$name', '$license_number', '$contact_phone', '$contact_email', '$address')";
    
    if ($conn->query($sql)) {
        $message = "Driver added successfully!";
    } else {
        $error = "Error adding driver: " . $conn->error;
    }
}

// Update driver
if (isset($_POST['update_driver'])) {
    $driver_id = $_POST['driver_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $license_number = $conn->real_escape_string($_POST['license_number']);
    $contact_phone = $conn->real_escape_string($_POST['contact_phone']);
    $contact_email = $conn->real_escape_string($_POST['contact_email']);
    $address = $conn->real_escape_string($_POST['address']);
    
    $sql = "UPDATE drivers SET 
            name='$name', 
            license_number='$license_number', 
            contact_phone='$contact_phone', 
            contact_email='$contact_email',
            address='$address'
            WHERE id='$driver_id'";
    
    if ($conn->query($sql)) {
        $message = "Driver updated successfully!";
    } else {
        $error = "Error updating driver: " . $conn->error;
    }
}

// DELETE MANUAL STATUS UPDATE - DRIVER STATUS IS NOW AUTOMATIC

// Delete driver
if (isset($_GET['delete_driver'])) {
    $driver_id = $_GET['delete_driver'];
    
    // Check if driver has dispatches
    $check_sql = "SELECT COUNT(*) as dispatch_count FROM dispatches WHERE driver_id = '$driver_id'";
    $result = $conn->query($check_sql);
    $dispatch_count = $result->fetch_assoc()['dispatch_count'];
    
    if ($dispatch_count == 0) {
        $sql = "DELETE FROM drivers WHERE id = '$driver_id'";
        if ($conn->query($sql)) {
            $message = "Driver deleted successfully!";
        } else {
            $error = "Error deleting driver: " . $conn->error;
        }
    } else {
        $error = "Cannot delete driver. They have $dispatch_count dispatch(es) associated.";
    }
}

// Get driver data for editing
$edit_driver = null;
if (isset($_GET['edit_driver'])) {
    $driver_id = $_GET['edit_driver'];
    $result = $conn->query("SELECT * FROM drivers WHERE id = '$driver_id'");
    $edit_driver = $result->fetch_assoc();
}

$drivers = $conn->query("SELECT d.*, 
                         (SELECT COUNT(*) FROM dispatches WHERE driver_id = d.id AND status IN ('assigned', 'in_transit')) as active_dispatches
                         FROM drivers d 
                         ORDER BY d.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Drivers - Truck Dispatching</title>
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
        <a href="drivers.php" class="active"><i class="fas fa-users"></i> Drivers</a>
        <a href="clients.php"><i class="fas fa-building"></i> Clients</a>
        <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
        <a href="email-log.php"><i class="fas fa-envelope"></i> Email Log</a>
        <a href="email-settings.php"><i class="fas fa-cogs"></i> Email Settings</a>
    </div>
    
    <div class="main-content">
        <h2>👨‍💼 Manage Drivers</h2>
        
        <!-- Add/Edit Driver Form -->
        <div class="form-container">
            <h3><?php echo $edit_driver ? '✏️ Edit Driver' : '➕ Add New Driver'; ?></h3>
            <form method="POST">
                <?php if ($edit_driver): ?>
                    <input type="hidden" name="driver_id" value="<?php echo $edit_driver['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Driver Name:</label>
                        <input type="text" name="name" 
                               value="<?php echo $edit_driver ? $edit_driver['name'] : ''; ?>" 
                               placeholder="John Smith" required>
                    </div>
                    <div class="form-group">
                        <label>License Number:</label>
                        <input type="text" name="license_number" 
                               value="<?php echo $edit_driver ? $edit_driver['license_number'] : ''; ?>" 
                               placeholder="DRV-001" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Phone:</label>
                        <input type="text" name="contact_phone" 
                               value="<?php echo $edit_driver ? $edit_driver['contact_phone'] : ''; ?>" 
                               placeholder="+1 (555) 123-4567">
                    </div>
                    <div class="form-group">
                        <label>Contact Email:</label>
                        <input type="email" name="contact_email" 
                               value="<?php echo $edit_driver ? $edit_driver['contact_email'] : ''; ?>" 
                               placeholder="driver@company.com">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Address:</label>
                    <textarea name="address" rows="3" placeholder="Driver's full address"><?php echo $edit_driver ? ($edit_driver['address'] ?? '') : ''; ?></textarea>
                </div>
                
                <?php if ($edit_driver): ?>
                    <button type="submit" name="update_driver" class="button">Update Driver</button>
                    <a href="drivers.php" class="button" style="background: #95a5a6; text-decoration: none;">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_driver" class="button">Add Driver</button>
                <?php endif; ?>
                
                <?php if (isset($message)): ?>
                    <div class="alert success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Drivers List -->
        <div class="table-container">
            <h3>All Drivers (<?php echo $drivers->num_rows; ?>)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>License Number</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Active Jobs</th>
                        <th>Added Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($driver = $drivers->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo $driver['name']; ?></strong>
                            <?php if ($driver['address']): ?>
                                <br><small style="color: #666;"><?php echo substr($driver['address'], 0, 30) . '...'; ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $driver['license_number']; ?></td>
                        <td>
                            <?php if ($driver['contact_phone']): ?>
                                📞 <?php echo $driver['contact_phone']; ?><br>
                            <?php endif; ?>
                            <?php if ($driver['contact_email']): ?>
                                ✉️ <?php echo $driver['contact_email']; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status status-<?php echo $driver['status']; ?>">
                                <?php echo ucfirst($driver['status']); ?>
                            </span>
                            <br>
                            <small style="color: #666;">
                                <?php if ($driver['status'] == 'on_duty'): ?>
                                    🔄 Auto-managed
                                <?php else: ?>
                                    ✅ Ready
                                <?php endif; ?>
                            </small>
                        </td>
                        <td>
                            <?php if ($driver['active_dispatches'] > 0): ?>
                                <span style="color: #e74c3c; font-weight: bold;">
                                    <?php echo $driver['active_dispatches']; ?> active
                                </span>
                            <?php else: ?>
                                <span style="color: #27ae60;">0 active</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($driver['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="?edit_driver=<?php echo $driver['id']; ?>" 
                                   class="btn-edit">Edit</a>
                                <a href="?delete_driver=<?php echo $driver['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete driver: <?php echo $driver['name']; ?>?')"
                                   class="btn-delete">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                <h4>🚀 Automatic Driver Status Management</h4>
                <p style="margin: 5px 0; color: #666;">
                    • <strong>When assigned to dispatch:</strong> Driver automatically becomes "On Duty"<br>
                    • <strong>When dispatch delivered/cancelled:</strong> Driver automatically becomes "Available"<br>
                    • <strong>No manual status changes needed</strong> - System manages everything automatically
                </p>
            </div>
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