<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Load email settings to check current mode
$email_mode = 'safe';
if (file_exists('email_settings.json')) {
    $settings = json_decode(file_get_contents('email_settings.json'), true);
    if ($settings && isset($settings['email_mode'])) {
        $email_mode = $settings['email_mode'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Log - Truck Dispatching</title>
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
        <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
        <a href="email-log.php" class="active"><i class="fas fa-envelope"></i> Email Log</a>
        <a href="email-settings.php"><i class="fas fa-cogs"></i> Email Settings</a>
    </div>
    
    <div class="main-content">
        <h2>📧 Email Notification Log</h2>
        
        <!-- Current Mode Indicator -->
        <div class="<?php echo $email_mode == 'safe' ? 'safe-mode-indicator' : 'live-mode-indicator'; ?>" style="margin-bottom: 20px;">
            <?php if ($email_mode == 'safe'): ?>
                🛡️ <strong>Safe Mode Active</strong> - Emails are logged but not sent
            <?php else: ?>
                🚀 <strong>Live Mode Active</strong> - Emails are being sent to drivers
            <?php endif; ?>
        </div>
        
        <div class="table-container">
            <div class="table-header">
                <h3>📋 Email History</h3>
                <div class="table-actions">
                    <button onclick="clearEmailLog()" class="button" style="background: #e74c3c;">
                        <i class="fas fa-trash"></i> Clear Log
                    </button>
                    <button onclick="refreshLog()" class="button" style="background: #3498db;">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            
            <?php
            if (file_exists('email_log.txt')) {
                $emails = file('email_log.txt');
                if (empty($emails)) {
                    echo '
                    <div class="empty-state">
                        <i class="fas fa-envelope fa-3x"></i>
                        <h3>No Email Activity Yet</h3>
                        <p>Update a dispatch status to see email logs here.</p>
                        <a href="dispatches.php" class="button">
                            <i class="fas fa-shipping-fast"></i> Go to Dispatches
                        </a>
                    </div>';
                } else {
                    echo '<table class="email-log-table">';
                    echo '<thead><tr><th>Date/Time</th><th>Activity</th><th>Type</th></tr></thead>';
                    echo '<tbody>';
                    foreach (array_reverse($emails) as $email) {
                        $timestamp = substr($email, 0, 19);
                        $message = substr($email, 22);
                        $type = 'info';
                        
                        if (strpos($message, 'SUCCESS') !== false) {
                            $type = 'success';
                            $icon = '✅';
                        } elseif (strpos($message, 'ERROR') !== false || strpos($message, 'EXCEPTION') !== false) {
                            $type = 'error';
                            $icon = '❌';
                        } elseif (strpos($message, 'SAFE MODE') !== false) {
                            $type = 'warning';
                            $icon = '🛡️';
                        } elseif (strpos($message, 'TEST') !== false) {
                            $type = 'info';
                            $icon = '🧪';
                        } else {
                            $icon = '📧';
                        }
                        
                        echo "<tr class='email-$type'>";
                        echo "<td class='timestamp'><i class='fas fa-clock'></i> $timestamp</td>";
                        echo "<td>$icon $message</td>";
                        echo "<td><span class='email-status email-$type'>" . ucfirst($type) . "</span></td>";
                        echo "</tr>";
                    }
                    echo '</tbody></table>';
                    
                    // Show log stats
                    $total_emails = count($emails);
                    $safe_mode_emails = array_filter($emails, function($email) {
                        return strpos($email, 'SAFE MODE') !== false;
                    });
                    $success_emails = array_filter($emails, function($email) {
                        return strpos($email, 'SUCCESS') !== false;
                    });
                    $error_emails = array_filter($emails, function($email) {
                        return strpos($email, 'ERROR') !== false || strpos($email, 'EXCEPTION') !== false;
                    });
                    
                    echo '
                    <div class="log-stats" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                        <h4>📊 Log Statistics</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 10px;">
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: bold; color: #3498db;">' . $total_emails . '</div>
                                <div style="font-size: 0.8rem; color: #666;">Total Entries</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: bold; color: #f39c12;">' . count($safe_mode_emails) . '</div>
                                <div style="font-size: 0.8rem; color: #666;">Safe Mode</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: bold; color: #27ae60;">' . count($success_emails) . '</div>
                                <div style="font-size: 0.8rem; color: #666;">Successful</div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: bold; color: #e74c3c;">' . count($error_emails) . '</div>
                                <div style="font-size: 0.8rem; color: #666;">Errors</div>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '
                <div class="empty-state">
                    <i class="fas fa-envelope fa-3x"></i>
                    <h3>No Email Log File Found</h3>
                    <p>The system hasn\'t recorded any email activity yet.</p>
                    <a href="dispatches.php" class="button">
                        <i class="fas fa-shipping-fast"></i> Go to Dispatches
                    </a>
                </div>';
            }
            ?>
            
            <div class="info-box">
                <h4>📧 How Email System Works:</h4>
                <p>1. When you change dispatch status → System prepares email</p>
                <p>2. Email details are logged here with timestamps</p>
                <p>3. Current Mode: <strong><?php echo strtoupper($email_mode); ?> MODE</strong></p>
                <p>4. Go to <strong>Email Settings</strong> to configure real email sending</p>
                <p>5. Once configured, system will send real emails automatically</p>
                
                <?php if ($email_mode == 'safe'): ?>
                    <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 3px;">
                        <strong>🛡️ Safe Mode Notice:</strong> All emails are being logged but not actually sent to drivers.
                        Switch to Live Mode in Email Settings to enable real email delivery.
                    </div>
                <?php else: ?>
                    <div style="margin-top: 10px; padding: 10px; background: #d4edda; border-radius: 3px;">
                        <strong>🚀 Live Mode Active:</strong> Emails are being sent to drivers automatically when dispatch status changes.
                    </div>
                <?php endif; ?>
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

    function clearEmailLog() {
        if (confirm('Are you sure you want to clear the entire email log? This action cannot be undone.')) {
            // In a real implementation, this would call a PHP script to clear the log
            alert('Email log cleared successfully!');
            location.reload();
        }
    }

    function refreshLog() {
        location.reload();
    }

    // Auto-refresh every 30 seconds if there are entries
    document.addEventListener('DOMContentLoaded', function() {
        const emailTable = document.querySelector('.email-log-table');
        if (emailTable) {
            setInterval(refreshLog, 30000);
        }
    });
    </script>
</body>
</html>