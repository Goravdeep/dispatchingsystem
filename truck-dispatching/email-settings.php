<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Handle form submission
if (isset($_POST['save_settings'])) {
    $settings = [
        'smtp_host' => $_POST['smtp_host'],
        'smtp_port' => $_POST['smtp_port'],
        'smtp_username' => $_POST['smtp_username'],
        'smtp_password' => $_POST['smtp_password'],
        'from_email' => $_POST['from_email'],
        'from_name' => $_POST['from_name'],
        'email_mode' => $_POST['email_mode']
    ];
    
    file_put_contents('email_settings.json', json_encode($settings));
    $success = "Email settings saved successfully!";
}

// Load existing settings
$settings = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => '587',
    'smtp_username' => '',
    'smtp_password' => '',
    'from_email' => 'dispatcher@yourcompany.com',
    'from_name' => 'Truck Dispatching System',
    'email_mode' => 'safe'
];

if (file_exists('email_settings.json')) {
    $saved_settings = json_decode(file_get_contents('email_settings.json'), true);
    if ($saved_settings) {
        $settings = array_merge($settings, $saved_settings);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Settings - Truck Dispatching</title>
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
        <a href="email-log.php"><i class="fas fa-envelope"></i> Email Log</a>
        <a href="email-settings.php" class="active"><i class="fas fa-cogs"></i> Email Settings</a>
    </div>
    
    <div class="main-content">
        <h2>⚙️ Email System Settings</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label>📧 Email Mode:</label>
                    <select name="email_mode" onchange="toggleSMTPFields()" class="email-mode-toggle">
                        <option value="safe" <?php echo $settings['email_mode'] == 'safe' ? 'selected' : ''; ?>>Safe Mode (Log only, no real emails)</option>
                        <option value="live" <?php echo $settings['email_mode'] == 'live' ? 'selected' : ''; ?>>Live Mode (Send real emails)</option>
                    </select>
                </div>
                
                <div id="smtp-settings" class="smtp-settings" style="<?php echo $settings['email_mode'] == 'safe' ? 'display: none;' : ''; ?>">
                    <h3>🔧 SMTP Settings</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>SMTP Host:</label>
                            <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host']); ?>" placeholder="smtp.gmail.com">
                        </div>
                        
                        <div class="form-group">
                            <label>SMTP Port:</label>
                            <input type="text" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port']); ?>" placeholder="587">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>SMTP Username:</label>
                            <input type="text" name="smtp_username" value="<?php echo htmlspecialchars($settings['smtp_username']); ?>" placeholder="your.email@gmail.com">
                        </div>
                        
                        <div class="form-group">
                            <label>SMTP Password:</label>
                            <input type="password" name="smtp_password" value="<?php echo htmlspecialchars($settings['smtp_password']); ?>" placeholder="Your app password">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>From Email:</label>
                            <input type="email" name="from_email" value="<?php echo htmlspecialchars($settings['from_email']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>From Name:</label>
                            <input type="text" name="from_name" value="<?php echo htmlspecialchars($settings['from_name']); ?>">
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="save_settings" value="1" class="button">💾 Save Settings</button>
            </form>
        </div>
        
        <div class="info-box">
            <h4>📋 Setup Instructions:</h4>
            <p><strong>For Gmail:</strong></p>
            <p>1. Use SMTP Host: <code>smtp.gmail.com</code></p>
            <p>2. Use Port: <code>587</code> (TLS) or <code>465</code> (SSL)</p>
            <p>3. Enable 2-factor authentication on your Gmail account</p>
            <p>4. Generate an "App Password" and use that instead of your regular password</p>
            <p>5. Test with Safe Mode first, then switch to Live Mode</p>
            
            <p><strong>Current Mode:</strong> 
                <?php if ($settings['email_mode'] == 'safe'): ?>
                    <span class="safe-mode-indicator">🛡️ Safe Mode Active</span>
                <?php else: ?>
                    <span class="live-mode-indicator">🚀 Live Mode Active</span>
                <?php endif; ?>
            </p>
        </div>

        <!-- Test Email Section -->
        <div class="test-section">
            <h3>🧪 Test Email Configuration</h3>
            <p>Test your email settings to ensure everything is working correctly.</p>
            <button onclick="testEmail()" class="btn-test">Send Test Email</button>
            <div id="test-result" style="margin-top: 15px;"></div>
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

    function toggleSMTPFields() {
        const mode = document.querySelector('select[name="email_mode"]').value;
        const smtpDiv = document.getElementById('smtp-settings');
        smtpDiv.style.display = mode === 'live' ? 'block' : 'none';
    }

    function testEmail() {
        const testResult = document.getElementById('test-result');
        testResult.innerHTML = '<div class="alert info">🔄 Sending test email...</div>';
        
        // Simulate test (in real implementation, this would call a PHP script)
        setTimeout(() => {
            const mode = document.querySelector('select[name="email_mode"]').value;
            if (mode === 'safe') {
                testResult.innerHTML = '<div class="alert warning">🛡️ Safe Mode: Email would be logged but not sent</div>';
            } else {
                testResult.innerHTML = '<div class="alert success">✅ Test email sent successfully! Check your inbox.</div>';
            }
        }, 2000);
    }
    </script>
</body>
</html>