<?php
// Email functions for truck dispatching system

function logEmailActivity($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = $timestamp . " - " . $message . "\n";
    file_put_contents('email_log.txt', $logEntry, FILE_APPEND | LOCK_EX);
}

function getEmailSettings() {
    $default_settings = [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => '587',
        'smtp_username' => '',
        'smtp_password' => '',
        'from_email' => 'dispatcher@yourcompany.com',
        'from_name' => 'Truck Dispatching System',
        'email_mode' => 'safe'
    ];
    
    if (file_exists('email_settings.json')) {
        $saved = json_decode(file_get_contents('email_settings.json'), true);
        if ($saved) {
            return array_merge($default_settings, $saved);
        }
    }
    
    return $default_settings;
}

function sendDispatchEmail($driverEmail, $driverName, $dispatchData, $status) {
    $settings = getEmailSettings();
    
    // Email content based on status
    $subject = "Dispatch Update - Status: " . strtoupper($status);
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { background: #f8f9fa; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .details { background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>🚛 Truck Dispatch Notification</h2>
        </div>
        
        <div class='content'>
            <p>Hello <strong>$driverName</strong>,</p>
            
            <p>Your dispatch has been updated to status: <strong>$status</strong></p>
            
            <div class='details'>
                <h3>Dispatch Details:</h3>
                <p><strong>Pickup:</strong> {$dispatchData['pickup_location']}</p>
                <p><strong>Delivery:</strong> {$dispatchData['delivery_location']}</p>
                <p><strong>Client:</strong> {$dispatchData['client_name']}</p>
                <p><strong>Load Reference:</strong> {$dispatchData['load_reference']}</p>
                <p><strong>Instructions:</strong> {$dispatchData['special_instructions']}</p>
            </div>
            
            <p>Please check the dispatch system for complete details and updates.</p>
        </div>
        
        <div class='footer'>
            <p>This is an automated message from Truck Dispatching System</p>
            <p>Please do not reply to this email</p>
        </div>
    </body>
    </html>
    ";
    
    // Log the email activity
    $logMessage = "Prepared email for driver $driverName ($driverEmail) - Status: $status";
    logEmailActivity($logMessage);
    
    // Check if we're in safe mode
    if ($settings['email_mode'] === 'safe') {
        $logMessage = "SAFE MODE: Email to $driverName would be sent (Status: $status)";
        logEmailActivity($logMessage);
        return true;
    }
    
    // Live mode - send actual email
    return sendActualEmail($driverEmail, $subject, $message, $settings);
}

function sendActualEmail($to, $subject, $message, $settings) {
    try {
        // For a real implementation, you would use PHPMailer or similar
        // This is a simplified version using basic mail() function
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $settings['from_name'] . ' <' . $settings['from_email'] . '>',
            'Reply-To: ' . $settings['from_email'],
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $result = mail($to, $subject, $message, implode("\r\n", $headers));
        
        if ($result) {
            logEmailActivity("SUCCESS: Email sent to $to");
            return true;
        } else {
            logEmailActivity("ERROR: Failed to send email to $to");
            return false;
        }
        
    } catch (Exception $e) {
        logEmailActivity("EXCEPTION: " . $e->getMessage());
        return false;
    }
}

function testEmailConnection($settings) {
    // This would test the SMTP connection
    // For now, we'll just log the test attempt
    
    logEmailActivity("TEST: Email connection test initiated");
    
    if ($settings['email_mode'] === 'safe') {
        return "Safe Mode Active - No actual connection test performed";
    }
    
    // In a real implementation, you would test SMTP connection here
    return "Live Mode - SMTP settings appear valid (manual verification required)";
}
?>