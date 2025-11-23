<?php
/**
 * Cron job management
 */

class WDTA_Membership_Cron {
    
    /**
     * Initialize cron hooks
     */
    public static function init() {
        add_action('wdta_membership_daily_check', array(__CLASS__, 'daily_check'));
    }
    
    /**
     * Daily check for membership status and reminder emails
     */
    public static function daily_check() {
        $current_date = current_time('Y-m-d');
        $current_month_day = date('m-d');
        
        // On January 1st, mark all unpaid memberships as inactive
        if ($current_month_day === '01-01') {
            $previous_year = (int) date('Y') - 1;
            WDTA_Membership_Database::set_unpaid_to_inactive($previous_year);
            
            // Send inactive users report
            WDTA_Membership_Email::send_inactive_users_report();
        }
        
        // Check for reminder emails
        self::check_and_send_reminders();
    }
    
    /**
     * Check if any reminder emails need to be sent
     */
    private static function check_and_send_reminders() {
        // Check each reminder (1, 2, 3)
        for ($i = 1; $i <= 3; $i++) {
            if (self::should_send_reminder($i)) {
                WDTA_Membership_Email::send_reminder_email($i);
                
                // Update last sent date
                update_option('wdta_membership_reminder' . $i . '_last_sent', current_time('Y-m-d'));
            }
        }
    }
    
    /**
     * Check if a reminder should be sent today
     */
    private static function should_send_reminder($reminder_number) {
        // Check if enabled
        $enabled = get_option('wdta_membership_reminder' . $reminder_number . '_enabled');
        if ($enabled !== 'yes') {
            return false;
        }
        
        // Check if already sent today
        $last_sent = get_option('wdta_membership_reminder' . $reminder_number . '_last_sent');
        if ($last_sent === current_time('Y-m-d')) {
            return false;
        }
        
        // Get reminder settings
        $timing = (int) get_option('wdta_membership_reminder' . $reminder_number . '_timing', 30);
        $unit = get_option('wdta_membership_reminder' . $reminder_number . '_unit', 'days');
        $period = get_option('wdta_membership_reminder' . $reminder_number . '_period', 'before');
        
        // Calculate target date
        $current_year = (int) date('Y');
        $december_31 = $current_year . '-12-31';
        
        // Convert timing to days
        $days = self::convert_to_days($timing, $unit);
        
        // Calculate the date when this reminder should be sent
        if ($period === 'before') {
            // Send X days/weeks/months BEFORE December 31st
            $target_date = date('Y-m-d', strtotime($december_31 . ' -' . $days . ' days'));
        } else {
            // Send X days/weeks/months AFTER December 31st (next year's January)
            $target_date = date('Y-m-d', strtotime($december_31 . ' +' . $days . ' days'));
        }
        
        // Check if today is the target date
        return current_time('Y-m-d') === $target_date;
    }
    
    /**
     * Convert timing value to days
     */
    private static function convert_to_days($timing, $unit) {
        switch ($unit) {
            case 'days':
                return $timing;
            case 'weeks':
                return $timing * 7;
            case 'months':
                return $timing * 30; // Approximate
            default:
                return $timing;
        }
    }
    
    /**
     * Manual trigger for testing (admin only)
     */
    public static function manual_trigger() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        self::daily_check();
    }
}
