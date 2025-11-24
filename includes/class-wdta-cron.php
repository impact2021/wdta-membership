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
        $current_month_day = date('m-d');
        // For December reminders, we're reminding about next year's membership (due Jan 1st of next year)
        $next_year = date('Y') + 1;
        $current_year = date('Y');
        
        // Determine which emails to send based on date
        switch ($current_month_day) {
            case '12-01': // December 1st - 1 month before Jan 1st (next year's membership)
                self::send_reminder_emails($next_year, '1_month');
                break;
                
            case '12-25': // December 25th - 1 week before Jan 1st (next year's membership)
                self::send_reminder_emails($next_year, '1_week');
                break;
                
            case '12-31': // December 31st - 1 day before Jan 1st (next year's membership)
                self::send_reminder_emails($next_year, '1_day');
                break;
                
            case '01-01': // January 1st - send inactive users report
                self::send_inactive_users_report();
                break;
                
            case '01-02': // January 2nd - 1 day overdue
                self::send_overdue_emails($current_year, 'overdue_1_day');
                break;
                
            case '01-08': // January 8th - 1 week overdue
                self::send_overdue_emails($current_year, 'overdue_1_week');
                break;
                
            case '01-31': // January 31st - end of month 1
                self::send_overdue_emails($current_year, 'overdue_end_jan');
                break;
                
            case '02-28': // February 28th - end of month 2 (non-leap year)
            case '02-29': // February 29th - end of month 2 (leap year)
                self::send_overdue_emails($current_year, 'overdue_end_feb');
                break;
                
            case '03-31': // March 31st - final deadline (kept for legacy users)
                self::send_overdue_emails($current_year, 'overdue_end_mar');
                break;
        }
    }
    
    /**
     * Send reminder emails to users without membership for upcoming year
     */
    private static function send_reminder_emails($year, $type) {
        // Check if this reminder is enabled
        $enabled_option = 'wdta_email_reminder_' . str_replace('_', '', $type) . '_enabled';
        if (get_option($enabled_option, '1') !== '1') {
            return; // Email is disabled
        }
        
        $users = WDTA_Database::get_users_without_membership($year);
        
        foreach ($users as $user) {
            switch ($type) {
                case '1_month':
                    WDTA_Email_Notifications::send_reminder_1_month($user->ID, $year);
                    break;
                case '1_week':
                    WDTA_Email_Notifications::send_reminder_1_week($user->ID, $year);
                    break;
                case '1_day':
                    WDTA_Email_Notifications::send_reminder_1_day($user->ID, $year);
                    break;
            }
        }
    }
    
    /**
     * Send overdue emails to users without active membership
     */
    private static function send_overdue_emails($year, $type) {
        // Check if this reminder is enabled
        $enabled_option = 'wdta_email_' . $type . '_enabled';
        if (get_option($enabled_option, '1') !== '1') {
            return; // Email is disabled
        }
        
        // Get all users who should have membership but don't
        $users = WDTA_Database::get_users_without_membership($year);
        
        foreach ($users as $user) {
            // Check if user has pending payment
            $membership = WDTA_Database::get_user_membership($user->ID, $year);
            
            // Only send if no membership or payment not completed
            if (!$membership || $membership->payment_status !== 'completed') {
                switch ($type) {
                    case 'overdue_1_day':
                        WDTA_Email_Notifications::send_overdue_1_day($user->ID, $year);
                        break;
                    case 'overdue_1_week':
                        WDTA_Email_Notifications::send_overdue_1_week($user->ID, $year);
                        break;
                    case 'overdue_end_jan':
                        WDTA_Email_Notifications::send_overdue_end_jan($user->ID, $year);
                        break;
                    case 'overdue_end_feb':
                        WDTA_Email_Notifications::send_overdue_end_feb($user->ID, $year);
                        break;
                    case 'overdue_end_mar':
                        WDTA_Email_Notifications::send_overdue_end_mar($user->ID, $year);
                        break;
                }
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
