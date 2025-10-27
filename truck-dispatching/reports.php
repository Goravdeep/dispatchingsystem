<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/database.php';

// Generate report based on filters
$where_conditions = [];
$params = [];

if (isset($_GET['generate_report'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $status = $_GET['status'];
    
    if (!empty($start_date)) {
        $where_conditions[] = "d.created_at >= '$start_date'";
    }
    if (!empty($end_date)) {
        $where_conditions[] = "d.created_at <= '$end_date 23:59:59'";
    }
    if (!empty($status)) {
        $where_conditions[] = "d.status = '$status'";
    }
    
    $where_sql = '';
    if (!empty($where_conditions)) {
        $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
    }
}

$report_sql = "SELECT d.*, t.registration_no, dr.name as driver_name, c.company_name 
               FROM dispatches d 
               LEFT JOIN trucks t ON d.truck_id = t.id 
               LEFT JOIN drivers dr ON d.driver_id = dr.id 
               LEFT JOIN clients c ON d.client_id = c.id 
               $where_sql
               ORDER BY d.created_at DESC";

$reports = $conn->query($report_sql);

// Calculate totals
$total_revenue = 0;
$total_dispatches = 0;
$completed_dispatches = 0;
while($row = $reports->fetch_assoc()) {
    $total_revenue += $row['total_cost'];
    $total_dispatches++;
    if ($row['status'] == 'delivered') {
        $completed_dispatches++;
    }
}
$reports->data_seek(0); // Reset pointer
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports - Truck Dispatching</title>
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
        <a href="clients.php"><i class="fas fa-building"></i> Clients</a>
        <a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a>
        <a href="email-log.php"><i class="fas fa-envelope"></i> Email Log</a>
        <a href="email-settings.php"><i class="fas fa-cogs"></i> Email Settings</a>
    </div>
    
    <div class="main-content">
        <h2>📊 Reports & Analytics</h2>
        
        <!-- Report Filters -->
        <div class="form-container">
            <h3>📈 Generate Report</h3>
            <form method="GET">
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date:</label>
                        <input type="date" name="start_date" value="<?php echo $_GET['start_date'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>End Date:</label>
                        <input type="date" name="end_date" value="<?php echo $_GET['end_date'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Status:</label>
                        <select name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo ($_GET['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="assigned" <?php echo ($_GET['status'] ?? '') == 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                            <option value="in_transit" <?php echo ($_GET['status'] ?? '') == 'in_transit' ? 'selected' : ''; ?>>In Transit</option>
                            <option value="delivered" <?php echo ($_GET['status'] ?? '') == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="generate_report" class="button">Generate Report</button>
                <button type="button" onclick="window.print()" class="button" style="background: #95a5a6;">🖨️ Print Report</button>
            </form>
        </div>
        
        <!-- Report Summary -->
        <?php if (isset($_GET['generate_report'])): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Dispatches</h3>
                <span class="stat-number"><?php echo $total_dispatches; ?></span>
            </div>
            <div class="stat-card">
                <h3>Completed</h3>
                <span class="stat-number"><?php echo $completed_dispatches; ?></span>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <span class="stat-number">$<?php echo number_format($total_revenue, 2); ?></span>
            </div>
            <div class="stat-card">
                <h3>Success Rate</h3>
                <span class="stat-number"><?php echo $total_dispatches > 0 ? number_format(($completed_dispatches / $total_dispatches) * 100, 1) : '0'; ?>%</span>
            </div>
        </div>
        
        <!-- Detailed Report -->
        <div class="table-container">
            <h3>📋 Detailed Report</h3>
            <table>
                <thead>
                    <tr>
                        <th>Dispatch ID</th>
                        <th>Client</th>
                        <th>Truck</th>
                        <th>Driver</th>
                        <th>Load Type</th>
                        <th>Status</th>
                        <th>Revenue</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($report = $reports->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $report['dispatch_id']; ?></td>
                        <td><?php echo $report['company_name']; ?></td>
                        <td><?php echo $report['registration_no']; ?></td>
                        <td><?php echo $report['driver_name']; ?></td>
                        <td><?php echo $report['load_type']; ?></td>
                        <td>
                            <span class="status status-<?php echo $report['status']; ?>">
                                <?php echo ucfirst($report['status']); ?>
                            </span>
                        </td>
                        <td><strong>$<?php echo number_format($report['total_cost'], 2); ?></strong></td>
                        <td><?php echo date('M j, Y', strtotime($report['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <?php if ($total_dispatches == 0): ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-chart-bar" style="font-size: 3rem; margin-bottom: 15px;"></i>
                    <h3>No Data Found</h3>
                    <p>No dispatches match your current filters. Try adjusting your search criteria.</p>
                </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="info-box">
            <h4>📊 Report Instructions</h4>
            <p>• Select date range and status to generate customized reports</p>
            <p>• View total revenue, dispatch counts, and success rates</p>
            <p>• Print reports for meetings and record keeping</p>
            <p>• Filter by specific time periods and dispatch statuses</p>
        </div>
        <?php endif; ?>
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

    // Set default dates (last 30 days)
    document.addEventListener('DOMContentLoaded', function() {
        const endDate = new Date();
        const startDate = new Date();
        startDate.setDate(startDate.getDate() - 30);
        
        document.querySelector('input[name="end_date"]').value = endDate.toISOString().split('T')[0];
        document.querySelector('input[name="start_date"]').value = startDate.toISOString().split('T')[0];
    });
    </script>
</body>
</html>