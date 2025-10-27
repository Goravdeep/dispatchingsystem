<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/database.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Truck Dispatching</title>
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
        <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="dispatches.php"><i class="fas fa-shipping-fast"></i> Dispatches</a>
        <a href="trucks.php"><i class="fas fa-truck"></i> Trucks</a>
        <a href="drivers.php"><i class="fas fa-users"></i> Drivers</a>
        <a href="clients.php"><i class="fas fa-building"></i> Clients</a>
        <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
        <a href="email-log.php"><i class="fas fa-envelope"></i> Email Log</a>
        <a href="email-settings.php"><i class="fas fa-cogs"></i> Email Settings</a>
    </div>
    
    <div class="main-content">
        <h2>📊 Dashboard</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Dispatches</h3>
                <span class="stat-number">
                    <?php
                    $result = $conn->query("SELECT COUNT(*) as total FROM dispatches");
                    echo $result->fetch_assoc()['total'];
                    ?>
                </span>
            </div>
            
            <div class="stat-card">
                <h3>Pending</h3>
                <span class="stat-number">
                    <?php
                    $result = $conn->query("SELECT COUNT(*) as total FROM dispatches WHERE status='pending'");
                    echo $result->fetch_assoc()['total'];
                    ?>
                </span>
            </div>
            
            <div class="stat-card">
                <h3>In Transit</h3>
                <span class="stat-number">
                    <?php
                    $result = $conn->query("SELECT COUNT(*) as total FROM dispatches WHERE status='in_transit'");
                    echo $result->fetch_assoc()['total'];
                    ?>
                </span>
            </div>
            
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <span class="stat-number">
                    $<?php
                    $result = $conn->query("SELECT SUM(total_cost) as total FROM dispatches WHERE status='delivered'");
                    $revenue = $result->fetch_assoc()['total'];
                    echo number_format($revenue ?: 0, 2);
                    ?>
                </span>
            </div>
            
            <div class="stat-card">
                <h3>Available Trucks</h3>
                <span class="stat-number">
                    <?php
                    $result = $conn->query("SELECT COUNT(*) as total FROM trucks WHERE status='available'");
                    echo $result->fetch_assoc()['total'];
                    ?>
                </span>
            </div>
            
            <div class="stat-card">
                <h3>Active Drivers</h3>
                <span class="stat-number">
                    <?php
                    $result = $conn->query("SELECT COUNT(*) as total FROM drivers WHERE status='available'");
                    echo $result->fetch_assoc()['total'];
                    ?>
                </span>
            </div>
        </div>

        <!-- Recent Dispatches -->
        <div class="table-container" style="margin-top: 30px;">
            <h3>📋 Recent Dispatches</h3>
            <table>
                <thead>
                    <tr>
                        <th>Dispatch ID</th>
                        <th>Client</th>
                        <th>Load Type</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $recent = $conn->query("SELECT d.*, c.company_name 
                                           FROM dispatches d 
                                           LEFT JOIN clients c ON d.client_id = c.id 
                                           ORDER BY d.created_at DESC LIMIT 5");
                    
                    if ($recent->num_rows > 0):
                        while($dispatch = $recent->fetch_assoc()): 
                    ?>
                    <tr>
                        <td>#<?php echo $dispatch['dispatch_id']; ?></td>
                        <td><?php echo $dispatch['company_name'] ?: 'N/A'; ?></td>
                        <td><?php echo $dispatch['load_type'] ?: 'General'; ?></td>
                        <td>
                            <span class="status status-<?php echo $dispatch['status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $dispatch['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($dispatch['created_at'])); ?></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No recent dispatches found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Quick Actions -->
        <div class="form-container" style="margin-top: 30px;">
            <h3>⚡ Quick Actions</h3>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="dispatches.php" class="button" style="text-decoration: none;">
                    <i class="fas fa-plus"></i> New Dispatch
                </a>
                <a href="drivers.php" class="button" style="text-decoration: none;">
                    <i class="fas fa-user-plus"></i> Add Driver
                </a>
                <a href="trucks.php" class="button" style="text-decoration: none;">
                    <i class="fas fa-truck"></i> Add Truck
                </a>
                <a href="reports.php" class="button" style="text-decoration: none;">
                    <i class="fas fa-chart-bar"></i> View Reports
                </a>
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