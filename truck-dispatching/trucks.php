<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/database.php';

// Add new truck
if (isset($_POST['add_truck'])) {
    $registration_no = $conn->real_escape_string($_POST['registration_no']);
    $model = $conn->real_escape_string($_POST['model']);
    $load_capacity = $_POST['load_capacity'];
    $year = $_POST['year'];
    $fuel_type = $conn->real_escape_string($_POST['fuel_type']);
    $notes = $conn->real_escape_string($_POST['notes']);
    
    $sql = "INSERT INTO trucks (registration_no, model, load_capacity, year, fuel_type, notes) 
            VALUES ('$registration_no', '$model', '$load_capacity', '$year', '$fuel_type', '$notes')";
    
    if ($conn->query($sql)) {
        $message = "Truck added successfully!";
    } else {
        $error = "Error adding truck: " . $conn->error;
    }
}

// Update truck
if (isset($_POST['update_truck'])) {
    $truck_id = $_POST['truck_id'];
    $registration_no = $conn->real_escape_string($_POST['registration_no']);
    $model = $conn->real_escape_string($_POST['model']);
    $load_capacity = $_POST['load_capacity'];
    $year = $_POST['year'];
    $fuel_type = $conn->real_escape_string($_POST['fuel_type']);
    $notes = $conn->real_escape_string($_POST['notes']);
    
    $sql = "UPDATE trucks SET 
            registration_no='$registration_no', 
            model='$model', 
            load_capacity='$load_capacity',
            year='$year',
            fuel_type='$fuel_type',
            notes='$notes'
            WHERE id='$truck_id'";
    
    if ($conn->query($sql)) {
        $message = "Truck updated successfully!";
    } else {
        $error = "Error updating truck: " . $conn->error;
    }
}

// DELETE MANUAL STATUS UPDATE - TRUCK STATUS IS NOW AUTOMATIC

// Delete truck
if (isset($_GET['delete_truck'])) {
    $truck_id = $_GET['delete_truck'];
    
    // Check if truck has dispatches
    $check_sql = "SELECT COUNT(*) as dispatch_count FROM dispatches WHERE truck_id = '$truck_id'";
    $result = $conn->query($check_sql);
    $dispatch_count = $result->fetch_assoc()['dispatch_count'];
    
    if ($dispatch_count == 0) {
        $sql = "DELETE FROM trucks WHERE id = '$truck_id'";
        if ($conn->query($sql)) {
            $message = "Truck deleted successfully!";
        } else {
            $error = "Error deleting truck: " . $conn->error;
        }
    } else {
        $error = "Cannot delete truck. It has $dispatch_count dispatch(es) associated.";
    }
}

// Get truck data for editing
$edit_truck = null;
if (isset($_GET['edit_truck'])) {
    $truck_id = $_GET['edit_truck'];
    $result = $conn->query("SELECT * FROM trucks WHERE id = '$truck_id'");
    $edit_truck = $result->fetch_assoc();
}

$trucks = $conn->query("SELECT t.*, 
                       (SELECT COUNT(*) FROM dispatches WHERE truck_id = t.id AND status IN ('assigned', 'in_transit')) as active_dispatches
                       FROM trucks t 
                       ORDER BY t.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trucks - Truck Dispatching</title>
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
        <a href="trucks.php" class="active"><i class="fas fa-truck"></i> Trucks</a>
        <a href="drivers.php"><i class="fas fa-users"></i> Drivers</a>
        <a href="clients.php"><i class="fas fa-building"></i> Clients</a>
        <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
        <a href="email-log.php"><i class="fas fa-envelope"></i> Email Log</a>
        <a href="email-settings.php"><i class="fas fa-cogs"></i> Email Settings</a>
    </div>
    
    <div class="main-content">
        <h2>🚚 Manage Trucks</h2>
        
        <!-- Add/Edit Truck Form -->
        <div class="form-container">
            <h3><?php echo $edit_truck ? '✏️ Edit Truck' : '➕ Add New Truck'; ?></h3>
            <form method="POST">
                <?php if ($edit_truck): ?>
                    <input type="hidden" name="truck_id" value="<?php echo $edit_truck['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Registration No:</label>
                        <input type="text" name="registration_no" 
                               value="<?php echo $edit_truck ? $edit_truck['registration_no'] : ''; ?>" 
                               placeholder="TRK-001" required>
                    </div>
                    <div class="form-group">
                        <label>Model:</label>
                        <input type="text" name="model" 
                               value="<?php echo $edit_truck ? $edit_truck['model'] : ''; ?>" 
                               placeholder="Volvo FH16" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Load Capacity (tons):</label>
                        <input type="number" step="0.01" name="load_capacity" 
                               value="<?php echo $edit_truck ? $edit_truck['load_capacity'] : ''; ?>" 
                               required>
                    </div>
                    <div class="form-group">
                        <label>Year:</label>
                        <input type="number" name="year" 
                               value="<?php echo $edit_truck ? ($edit_truck['year'] ?? date('Y')) : date('Y'); ?>" 
                               min="2000" max="<?php echo date('Y'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Fuel Type:</label>
                        <select name="fuel_type">
                            <option value="Diesel" <?php echo ($edit_truck && ($edit_truck['fuel_type'] ?? '') == 'Diesel') ? 'selected' : ''; ?>>Diesel</option>
                            <option value="Gasoline" <?php echo ($edit_truck && ($edit_truck['fuel_type'] ?? '') == 'Gasoline') ? 'selected' : ''; ?>>Gasoline</option>
                            <option value="Electric" <?php echo ($edit_truck && ($edit_truck['fuel_type'] ?? '') == 'Electric') ? 'selected' : ''; ?>>Electric</option>
                            <option value="Hybrid" <?php echo ($edit_truck && ($edit_truck['fuel_type'] ?? '') == 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Notes:</label>
                    <textarea name="notes" rows="2" placeholder="Additional notes about the truck"><?php echo $edit_truck ? ($edit_truck['notes'] ?? '') : ''; ?></textarea>
                </div>
                
                <?php if ($edit_truck): ?>
                    <button type="submit" name="update_truck" class="button">Update Truck</button>
                    <a href="trucks.php" class="button" style="background: #95a5a6; text-decoration: none;">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_truck" class="button">Add Truck</button>
                <?php endif; ?>
                
                <?php if (isset($message)): ?>
                    <div class="alert success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Trucks List -->
        <div class="table-container">
            <h3>All Trucks (<?php echo $trucks->num_rows; ?>)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Truck Details</th>
                        <th>Specifications</th>
                        <th>Status</th>
                        <th>Active Jobs</th>
                        <th>Added Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($truck = $trucks->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo $truck['registration_no']; ?></strong>
                            <br><small><?php echo $truck['model']; ?></small>
                            <?php if ($truck['year']): ?>
                                <br><small>Year: <?php echo $truck['year']; ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small>Capacity: <?php echo $truck['load_capacity']; ?> tons</small><br>
                            <small>Fuel: <?php echo $truck['fuel_type'] ?: 'N/A'; ?></small>
                        </td>
                        <td>
                            <span class="status status-<?php echo $truck['status']; ?>">
                                <?php echo ucfirst($truck['status']); ?>
                            </span>
                            <br>
                            <small style="color: #666;">
                                <?php if ($truck['status'] == 'on_route'): ?>
                                    🔄 Auto-managed
                                <?php else: ?>
                                    ✅ Ready
                                <?php endif; ?>
                            </small>
                        </td>
                        <td>
                            <?php if ($truck['active_dispatches'] > 0): ?>
                                <span style="color: #e74c3c; font-weight: bold;">
                                    <?php echo $truck['active_dispatches']; ?> active
                                </span>
                            <?php else: ?>
                                <span style="color: #27ae60;">0 active</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($truck['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="?edit_truck=<?php echo $truck['id']; ?>" 
                                   class="btn-edit">Edit</a>
                                <a href="?delete_truck=<?php echo $truck['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete truck: <?php echo $truck['registration_no']; ?>?')"
                                   class="btn-delete">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                <h4>🚀 Automatic Truck Status Management</h4>
                <p style="margin: 5px 0; color: #666;">
                    • <strong>When assigned to dispatch:</strong> Truck automatically becomes "On Route"<br>
                    • <strong>When dispatch delivered/cancelled:</strong> Truck automatically becomes "Available"<br>
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