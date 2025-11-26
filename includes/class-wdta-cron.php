<?php
/**
 * Cron job handler for scheduled tasks
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDTA_Cron {
    
    /**
     * Schedule all cron events
     */
    public static function schedule_events() {
        // Schedule daily check for email notifications
        if (!wp_next_scheduled('wdta_daily_email_check')) {
            wp_schedule_event(strtotime('00:00:00'), 'daily', 'wdta_daily_email_check');
        }
        
        // Schedule daily membership expiry check
        if (!wp_next_scheduled('wdta_daily_expiry_check')) {
            wp_schedule_event(strtotime('00:00:00'), 'daily', 'wdta_daily_expiry_check');
        }
        
        // Schedule daily role check
        if (!wp_next_scheduled('wdta_daily_role_check')) {
            wp_schedule_event(strtotime('00:00:00'), 'daily', 'wdta_daily_role_check');
        }
        
        // Add actions
        add_action('wdta_daily_email_check', array(__CLASS__, 'process_email_notifications'));
        add_action('wdta_daily_expiry_check', array(__CLASS__, 'process_membership_expiry'));
    }
    
    /**
     * Clear all scheduled events
     */
    public static function clear_scheduled_events() {
        wp_clear_scheduled_hook('wdta_daily_email_check');
        wp_clear_scheduled_hook('wdta_daily_expiry_check');
        wp_clear_scheduled_hook('wdta_daily_role_check');
    }
    
    /**
     * Process email notifications based on current date
     */
    public static function process_email_notifications() {
        $current_date = date('Y-m-d');
        // For December reminders, we're reminding about next year's membership (due Jan 1st of next year)
        $next_year = date('Y') + 1;
        $current_year = date('Y');
        
        // January 1st - send inactive users report
        if (date('m-d') === '01-01') {
            self::send_inactive_users_report();
        }
        
        // Process dynamic reminders
        self::process_dynamic_reminders($current_year, $next_year);
    }
    
    /**
     * Cache for sent reminders to avoid multiple database queries
     */
    private static $sent_reminders_cache = null;
    
    /**
     * Process dynamic reminders based on configuration
     */
    private static function process_dynamic_reminders($current_year, $next_year) {
        $reminders = get_option('wdta_email_reminders', array());
        
        if (empty($reminders)) {
            return;
        }
        
        $today = new DateTime();
        $today->setTime(0, 0, 0); // Reset time to start of day for date comparison
        
        // Membership expiry date is always December 31st of current year
        $expiry_date = new DateTime($current_year . '-12-31');
        
        // Load sent reminders cache once for all reminder checks
        self::$sent_reminders_cache = get_option('wdta_sent_reminders', array());
        
        foreach ($reminders as $reminder) {
            // Skip if reminder is disabled
            if (empty($reminder['enabled'])) {
                continue;
            }
            
            // Calculate the send date for this reminder
            $send_date = clone $expiry_date;
            $timing = intval($reminder['timing']);
            $unit = $reminder['unit'];
            $period = $reminder['period'];
            
            // Convert timing to days
            $days = ($unit === 'weeks') ? $timing * 7 : $timing;
            
            // Adjust send date based on period (before or after)
            if ($period === 'before') {
                $send_date->modify("-{$days} days");
            } else {
                $send_date->modify("+{$days} days");
            }
            
            // Determine which year's membership to check
            // Before expiry: remind about next year's membership (due Jan 1 of next year)
            // After expiry: remind about current year's overdue membership
            $target_year = ($period === 'before') ? $next_year : $current_year;
            
            // Get reminder ID using a deterministic key based on reminder properties
            $reminder_id = self::get_reminder_id($reminder);
            
            // Check if the send date has passed and this reminder hasn't been sent yet for this target year
            // This allows reminders to be sent even if the cron didn't run on the exact send date
            if ($today >= $send_date && !self::reminder_already_sent($reminder_id, $target_year)) {
                // Send reminders
                self::send_dynamic_reminder($reminder, $target_year);
                
                // Mark this reminder as sent for this year
                self::mark_reminder_sent($reminder_id, $target_year);
            }
        }
        
        // Clear cache after processing
        self::$sent_reminders_cache = null;
    }
    
    /**
     * Generate a deterministic reminder ID
     * Uses reminder properties to create a stable identifier
     */
    private static function get_reminder_id($reminder) {
        // Use the explicit ID if set
        if (isset($reminder['id'])) {
            return $reminder['id'];
        }
        
        // Create a deterministic key from timing, unit, and period
        $timing = isset($reminder['timing']) ? $reminder['timing'] : 0;
        $unit = isset($reminder['unit']) ? $reminder['unit'] : 'days';
        $period = isset($reminder['period']) ? $reminder['period'] : 'before';
        
        return "reminder_{$timing}_{$unit}_{$period}";
    }
    
    /**
     * Check if a reminder has already been sent for a specific year
     */
    private static function reminder_already_sent($reminder_id, $year) {
        // Use cached value if available
        $sent_reminders = self::$sent_reminders_cache !== null 
            ? self::$sent_reminders_cache 
            : get_option('wdta_sent_reminders', array());
        $key = $reminder_id . '_' . $year;
        return isset($sent_reminders[$key]);
    }
    
    /**
     * Mark a reminder as sent for a specific year
     */
    private static function mark_reminder_sent($reminder_id, $year) {
        // Get fresh data from database to avoid race conditions
        $sent_reminders = get_option('wdta_sent_reminders', array());
        $key = $reminder_id . '_' . $year;
        $sent_reminders[$key] = true;
        update_option('wdta_sent_reminders', $sent_reminders);
        
        // Update cache
        if (self::$sent_reminders_cache !== null) {
            self::$sent_reminders_cache[$key] = true;
        }
    }
    
    /**
     * Send a dynamic reminder email
     */
    private static function send_dynamic_reminder($reminder, $year) {
        // Get all users who should have membership but don't
        $users = WDTA_Database::get_users_without_membership($year);
        
        foreach ($users as $user) {
            // Check if user has pending payment (for after expiry reminders)
            $membership = WDTA_Database::get_user_membership($user->ID, $year);
            
            // Only send if no membership or payment not completed
            if (!$membership || $membership->payment_status !== 'completed') {
                WDTA_Email_Notifications::send_dynamic_reminder($user->ID, $year, $reminder);
            }
        }
    }
    
    /**
     * Send inactive users report to administrators
     */
    private static function send_inactive_users_report() {
        // Check if report is enabled
        if (get_option('wdta_email_inactive_report_enabled', '1') !== '1') {
            return; // Report is disabled
        }
        
        $current_year = date('Y');
        $inactive_users = WDTA_Database::get_users_without_membership($current_year);
        
        if (empty($inactive_users)) {
            return; // No inactive users to report
        }
        
        // Get admin email(s)
        $admin_emails = get_option('wdta_inactive_report_emails', get_option('admin_email'));
        
        // Build email content
        $subject = 'WDTA Inactive Members Report - ' . date('F j, Y');
        
        $message = '<html><body>';
        $message .= '<h2>Inactive WDTA Members as of ' . date('F j, Y') . '</h2>';
        $message .= '<p>The following members do not have an active membership for ' . $current_year . ':</p>';
        $message .= '<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse;">';
        $message .= '<thead><tr>';
        $message .= '<th>User ID</th>';
        $message .= '<th>Name</th>';
        $message .= '<th>Email</th>';
        $message .= '<th>Role</th>';
        $message .= '</tr></thead>';
        $message .= '<tbody>';
        
        foreach ($inactive_users as $user) {
            $user_obj = get_userdata($user->ID);
            $message .= '<tr>';
            $message .= '<td>' . esc_html($user->ID) . '</td>';
            $message .= '<td>' . esc_html($user->display_name) . '</td>';
            $message .= '<td>' . esc_html($user->user_email) . '</td>';
            $message .= '<td>' . esc_html(implode(', ', $user_obj->roles)) . '</td>';
            $message .= '</tr>';
        }
        
        $message .= '</tbody></table>';
        $message .= '<p><strong>Total inactive members: ' . count($inactive_users) . '</strong></p>';
        $message .= '</body></html>';
        
        // Send email
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($admin_emails, $subject, $message, $headers);
    }
    
    /**
     * Process membership expiry
     */
    public static function process_membership_expiry() {
        $current_date = date('Y-m-d');
        
        // Check if it's January 1st - deactivate expired memberships from previous year
        if (date('m-d') === '01-01') {
            $previous_year = date('Y') - 1;
            self::deactivate_expired_memberships($previous_year);
        }
    }
    
    /**
     * Deactivate expired memberships after December 31st
     */
    private static function deactivate_expired_memberships($year) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        // Update all memberships for the year that haven't been paid
        // Set status to 'inactive' for all non-completed payments
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name 
            SET status = 'inactive' 
            WHERE membership_year = %d 
            AND (payment_status != 'completed' OR status != 'active')",
            $year
        ));
    }
}
