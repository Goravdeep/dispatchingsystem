<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once 'email_functions.php';

// Initialize dispatches in session if not set
if (!isset($_SESSION['dispatches'])) {
    $_SESSION['dispatches'] = [
        1 => ['id' => 1, 'driver_id' => 1, 'driver_name' => 'John Smith', 'driver_email' => 'john@example.com', 'pickup_location' => 'New York, NY', 'delivery_location' => 'Boston, MA', 'client_name' => 'ABC Logistics', 'load_reference' => 'REF12345', 'special_instructions' => 'Fragile load', 'status' => 'dispatched'],
        2 => ['id' => 2, 'driver_id' => 2, 'driver_name' => 'Mike Johnson', 'driver_email' => 'mike@example.com', 'pickup_location' => 'Chicago, IL', 'delivery_location' => 'Detroit, MI', 'client_name' => 'Global Shipping', 'load_reference' => 'REF67890', 'special_instructions' => 'Time-sensitive', 'status' => 'at_pickup']
    ];
}

$drivers = [
    1 => ['id' => 1, 'name' => 'John Smith', 'email' => 'john@example.com', 'phone' => '555-0101'],
    2 => ['id' => 2, 'name' => 'Mike Johnson', 'email' => 'mike@example.com', 'phone' => '555-0102']
];

// Use session data
$dispatches = $_SESSION['dispatches'];

// Handle status update with email
if (isset($_POST['update_status'])) {
    $dispatch_id = (int)$_POST['dispatch_id'];
    $new_status = $_POST['status'];
    
    if ($new_status && isset($dispatches[$dispatch_id])) {
        $_SESSION['dispatches'][$dispatch_id]['status'] = $new_status;
        
        $dispatch = $dispatches[$dispatch_id];
        $driver = $drivers[$dispatch['driver_id']];
        
        $email_sent = sendDispatchEmail(
            $driver['email'],
            $driver['name'],
            $dispatch,
            $new_status
        );
        
        $_SESSION['message'] = $email_sent 
            ? "✅ Status updated to " . ucfirst($new_status) . " & email sent!" 
            : "✅ Status updated to " . ucfirst($new_status) . "!";
    } else {
        $_SESSION['message'] = "❌ Please select a status";
    }
    
    header('Location: dispatches.php');
    exit;
}

// Handle creating new dispatch
if (isset($_POST['create_dispatch'])) {
    $new_id = count($dispatches) + 1;
    $driver_id = (int)$_POST['driver_id'];
    
    $_SESSION['dispatches'][$new_id] = [
        'id' => $new_id,
        'driver_id' => $driver_id,
        'driver_name' => $drivers[$driver_id]['name'],
        'driver_email' => $drivers[$driver_id]['email'],
        'pickup_location' => $_POST['pickup_location'],
        'delivery_location' => $_POST['delivery_location'],
        'client_name' => $_POST['client_name'],
        'load_reference' => $_POST['load_reference'],
        'special_instructions' => $_POST['special_instructions'],
        'status' => 'dispatched'
    ];
    
    $_SESSION['message'] = "✅ New dispatch created!";
    header('Location: dispatches.php');
    exit;
}

// Handle editing dispatch
if (isset($_POST['edit_dispatch'])) {
    $dispatch_id = (int)$_POST['dispatch_id'];
    $driver_id = (int)$_POST['driver_id'];
    
    if (isset($dispatches[$dispatch_id])) {
        $_SESSION['dispatches'][$dispatch_id] = [
            'id' => $dispatch_id,
            'driver_id' => $driver_id,
            'driver_name' => $drivers[$driver_id]['name'],
            'driver_email' => $drivers[$driver_id]['email'],
            'pickup_location' => $_POST['pickup_location'],
            'delivery_location' => $_POST['delivery_location'],
            'client_name' => $_POST['client_name'],
            'load_reference' => $_POST['load_reference'],
            'special_instructions' => $_POST['special_instructions'],
            'status' => $dispatches[$dispatch_id]['status'] // Keep existing status
        ];
        
        $_SESSION['message'] = "✅ Dispatch #$dispatch_id updated successfully!";
    }
    
    header('Location: dispatches.php');
    exit;
}

// Refresh local variable from session
$dispatches = $_SESSION['dispatches'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dispatches - Truck Dispatching</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .edit-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #007bff;
        }
        .edit-form h4 {
            margin-top: 0;
            color: #007bff;
        }
        .btn-edit {
            background: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
        .btn-edit:hover {
            background: #218838;
        }
        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            margin-left: 5px;
        }
        .btn-cancel:hover {
            background: #5a6268;
        }
    </style>
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
        <a href="dispatches.php" class="active"><i class="fas fa-shipping-fast"></i> Dispatches</a>
        <a href="trucks.php"><i class="fas fa-truck"></i> Trucks</a>
        <a href="drivers.php"><i class="fas fa-users"></i> Drivers</a>
        <a href="clients.php"><i class="fas fa-building"></i> Clients</a>
        <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
        <a href="email-log.php"><i class="fas fa-envelope"></i> Email Log</a>
        <a href="email-settings.php"><i class="fas fa-cogs"></i> Email Settings</a>
    </div>
    
    <div class="main-content">
        <h2>📋 Dispatch Management</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <!-- Create New Dispatch Form -->
        <div class="form-container">
            <h3>➕ Create New Dispatch</h3>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Driver:</label>
                        <select name="driver_id" required>
                            <option value="">Select Driver</option>
                            <?php foreach ($drivers as $driver): ?>
                                <option value="<?php echo $driver['id']; ?>">
                                    <?php echo $driver['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Client Name:</label>
                        <input type="text" name="client_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Pickup Location:</label>
                        <input type="text" name="pickup_location" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Delivery Location:</label>
                        <input type="text" name="delivery_location" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Load Reference:</label>
                        <input type="text" name="load_reference" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Special Instructions:</label>
                    <textarea name="special_instructions" rows="2"></textarea>
                </div>
                
                <button type="submit" name="create_dispatch" class="button">Create Dispatch</button>
            </form>
        </div>
        
        <!-- Dispatches Table -->
        <div class="table-container">
            <h3>Active Dispatches (<?php echo count($dispatches); ?>)</h3>
            
            <?php if (empty($dispatches)): ?>
                <p>No dispatches found. Create your first dispatch above.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Driver</th>
                            <th>Route</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dispatches as $dispatch): ?>
                        <tr>
                            <td>#<?php echo $dispatch['id']; ?></td>
                            <td>
                                <strong><?php echo $dispatch['driver_name']; ?></strong><br>
                                <small><?php echo $dispatch['driver_email']; ?></small>
                            </td>
                            <td>
                                🚚 <?php echo $dispatch['pickup_location']; ?><br>
                                → 🏁 <?php echo $dispatch['delivery_location']; ?>
                            </td>
                            <td><?php echo $dispatch['client_name']; ?></td>
                            <td>
                                <span class="status status-<?php echo $dispatch['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $dispatch['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <!-- Status Update Form -->
                                <form method="POST" style="display: inline-block; margin-right: 10px;">
                                    <input type="hidden" name="dispatch_id" value="<?php echo $dispatch['id']; ?>">
                                    <select name="status" onchange="this.form.submit()" style="padding: 5px;">
                                        <option value="">Update Status</option>
                                        <option value="dispatched" <?php echo $dispatch['status'] == 'dispatched' ? 'selected' : ''; ?>>Dispatched</option>
                                        <option value="at_pickup" <?php echo $dispatch['status'] == 'at_pickup' ? 'selected' : ''; ?>>At Pickup</option>
                                        <option value="loaded" <?php echo $dispatch['status'] == 'loaded' ? 'selected' : ''; ?>>Loaded</option>
                                        <option value="in_transit" <?php echo $dispatch['status'] == 'in_transit' ? 'selected' : ''; ?>>In Transit</option>
                                        <option value="at_delivery" <?php echo $dispatch['status'] == 'at_delivery' ? 'selected' : ''; ?>>At Delivery</option>
                                        <option value="delivered" <?php echo $dispatch['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                                
                                <!-- Edit Button -->
                                <button class="btn-edit" onclick="showEditForm(<?php echo $dispatch['id']; ?>)">
                                    ✏️ Edit
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Edit Form (Hidden by default) -->
                        <tr id="edit-form-<?php echo $dispatch['id']; ?>" style="display: none;">
                            <td colspan="6">
                                <div class="edit-form">
                                    <h4>✏️ Edit Dispatch #<?php echo $dispatch['id']; ?></h4>
                                    <form method="POST">
                                        <input type="hidden" name="dispatch_id" value="<?php echo $dispatch['id']; ?>">
                                        <div class="form-row">
                                            <div class="form-group">
                                                <label>Driver:</label>
                                                <select name="driver_id" required>
                                                    <option value="">Select Driver</option>
                                                    <?php foreach ($drivers as $driver): ?>
                                                        <option value="<?php echo $driver['id']; ?>" 
                                                            <?php echo $dispatch['driver_id'] == $driver['id'] ? 'selected' : ''; ?>>
                                                            <?php echo $driver['name']; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Client Name:</label>
                                                <input type="text" name="client_name" value="<?php echo htmlspecialchars($dispatch['client_name']); ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Pickup Location:</label>
                                                <input type="text" name="pickup_location" value="<?php echo htmlspecialchars($dispatch['pickup_location']); ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Delivery Location:</label>
                                                <input type="text" name="delivery_location" value="<?php echo htmlspecialchars($dispatch['delivery_location']); ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Load Reference:</label>
                                                <input type="text" name="load_reference" value="<?php echo htmlspecialchars($dispatch['load_reference']); ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Special Instructions:</label>
                                            <textarea name="special_instructions" rows="2"><?php echo htmlspecialchars($dispatch['special_instructions']); ?></textarea>
                                        </div>
                                        
                                        <button type="submit" name="edit_dispatch" class="button">Save Changes</button>
                                        <button type="button" class="btn-cancel" onclick="hideEditForm(<?php echo $dispatch['id']; ?>)">Cancel</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
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

    function showEditForm(dispatchId) {
        // Hide all other edit forms first
        document.querySelectorAll('[id^="edit-form-"]').forEach(form => {
            form.style.display = 'none';
        });
        
        // Show the selected edit form
        document.getElementById('edit-form-' + dispatchId).style.display = 'table-row';
        
        // Scroll to the form
        document.getElementById('edit-form-' + dispatchId).scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }
    
    function hideEditForm(dispatchId) {
        document.getElementById('edit-form-' + dispatchId).style.display = 'none';
    }
    
    // Hide edit form when clicking outside (optional)
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.edit-form') && !event.target.closest('.btn-edit')) {
            document.querySelectorAll('[id^="edit-form-"]').forEach(form => {
                form.style.display = 'none';
            });
        }
    });
    </script>
</body>
</html>