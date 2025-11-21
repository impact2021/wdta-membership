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
    }
    
    /**
     * Process email notifications based on current date
     */
    public static function process_email_notifications() {
        $current_date = date('Y-m-d');
        $current_month_day = date('m-d');
        $next_year = date('Y') + 1;
        $current_year = date('Y');
        
        // Determine which emails to send based on date
        switch ($current_month_day) {
            case '12-01': // December 1st - 1 month before
                self::send_reminder_emails($next_year, '1_month');
                break;
                
            case '12-25': // December 25th - 1 week before
                self::send_reminder_emails($next_year, '1_week');
                break;
                
            case '12-31': // December 31st - 1 day before
                self::send_reminder_emails($next_year, '1_day');
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
                
            case '03-31': // March 31st - final deadline
                self::send_overdue_emails($current_year, 'overdue_end_mar');
                break;
        }
    }
    
    /**
     * Send reminder emails to users without membership for upcoming year
     */
    private static function send_reminder_emails($year, $type) {
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
     * Process membership expiry
     */
    public static function process_membership_expiry() {
        $current_date = date('Y-m-d');
        $current_year = date('Y');
        
        // Check if it's April 1st - deactivate expired memberships
        if (date('m-d') === '04-01') {
            self::deactivate_expired_memberships($current_year);
        }
    }
    
    /**
     * Deactivate expired memberships after March 31st
     */
    private static function deactivate_expired_memberships($year) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        // Update all memberships that expired on March 31st and are still pending
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name 
            SET status = 'expired' 
            WHERE membership_year = %d 
            AND expiry_date = %s 
            AND payment_status != 'completed'",
            $year,
            $year . '-03-31'
        ));
    }
}
