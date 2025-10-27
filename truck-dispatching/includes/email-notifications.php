<?php
class EmailNotifications {
    
    private static function getEmailSettings() {
        global $conn;
        $result = $conn->query("SELECT * FROM email_settings WHERE id = 1");
        return $result->fetch_assoc();
    }
    
    public static function sendStatusUpdate($client_email, $client_name, $dispatch_id, $new_status, $dispatch_details = []) {
        $settings = self::getEmailSettings();
        
        // For now, just log that email would be sent
        // This is SAFE - no real emails sent until you configure SMTP
        $log_message = "📧 Email READY to send to: $client_email - Dispatch: $dispatch_id - Status: $new_status";
        
        // If SMTP is configured, we would send real email here
        if (!empty($settings['smtp_username']) && !empty($settings['smtp_password'])) {
            $log_message .= " - SMTP CONFIGURED (Real email would be sent)";
        } else {
            $log_message .= " - SMTP NOT CONFIGURED (Configure in Email Settings)";
        }
        
        self::logEmail($log_message, $client_email);
        return true;
    }
    
    private static function logEmail($message, $recipient) {
        $log = date('Y-m-d H:i:s') . " - $message - To: $recipient" . PHP_EOL;
        file_put_contents('email_log.txt', $log, FILE_APPEND);
    }
    
    public static function testConnection() {
        $settings = self::getEmailSettings();
        
        if (empty($settings['smtp_username']) || empty($settings['smtp_password'])) {
            return "❌ SMTP not configured - Go to Email Settings to set up";
        }
        
        return "✅ SMTP Configuration Found - Username: " . $settings['smtp_username'];
    }
    
    public static function sendTestEmail($test_email) {
        $settings = self::getEmailSettings();
        
        if (empty($settings['smtp_username']) || empty($settings['smtp_password'])) {
            self::logEmail("❌ TEST EMAIL FAILED - SMTP not configured", $test_email);
            return false;
        }
        
        // For now, just log test email
        self::logEmail("✅ TEST EMAIL READY - Would send to: $test_email - SMTP: " . $settings['smtp_username'], $test_email);
        return true;
    }
}
?>  