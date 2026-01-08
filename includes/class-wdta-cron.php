<?php
/**
 * Cron job handler for scheduled tasks
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDTA_Cron {
    
    /**
     * The time at which membership expires on December 31st
     * Used for calculating reminder send times with hours/minutes precision
     */
    const EXPIRY_TIME = '23:59:59';
    
    /**
     * Initialize cron action hooks
     * This must be called on every page load to ensure cron callbacks are registered
     * 
     * Note: The wdta_daily_role_check action is registered separately in WDTA_User_Roles::init()
     * to maintain separation of concerns between email/expiry processing and role management.
     */
    public static function init() {
        // Register action callbacks for cron events
        // These must be registered on every request so that when WordPress cron
        // triggers the scheduled hooks, the callbacks are available to execute
        add_action('wdta_daily_email_check', array(__CLASS__, 'process_email_notifications'));
        add_action('wdta_daily_expiry_check', array(__CLASS__, 'process_membership_expiry'));
    }
    
    /**
     * Schedule all cron events
     */
    public static function schedule_events() {
        // Schedule hourly check for email notifications
        // This allows hour-based and minute-based reminders to work properly
        if (!wp_next_scheduled('wdta_daily_email_check')) {
            wp_schedule_event(time(), 'hourly', 'wdta_daily_email_check');
        }
        
        // Schedule daily membership expiry check
        if (!wp_next_scheduled('wdta_daily_expiry_check')) {
            wp_schedule_event(strtotime('00:00:00'), 'daily', 'wdta_daily_expiry_check');
        }
        
        // Schedule daily role check
        if (!wp_next_scheduled('wdta_daily_role_check')) {
            wp_schedule_event(strtotime('00:00:00'), 'daily', 'wdta_daily_role_check');
        }
    }
    
    /**
     * Reschedule email check cron to run hourly
     * Call this to update existing daily schedules to hourly
     */
    public static function reschedule_email_check() {
        // Clear existing schedule
        wp_clear_scheduled_hook('wdta_daily_email_check');
        
        // Reschedule as hourly
        if (!wp_next_scheduled('wdta_daily_email_check')) {
            wp_schedule_event(time(), 'hourly', 'wdta_daily_email_check');
        }
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
        // Don't reset time - we need actual time for hours/minutes calculations
        
        // Load sent reminders cache once for all reminder checks
        self::$sent_reminders_cache = get_option('wdta_sent_reminders', array());
        
        // Check reminders for both current and previous year's expiry dates
        // This ensures we catch overdue "before" reminders that were scheduled
        // relative to the previous year's December 31st
        $previous_year = $current_year - 1;
        $expiry_time = self::EXPIRY_TIME;
        $expiry_dates = array(
            $previous_year => new DateTime($previous_year . '-12-31 ' . $expiry_time),
            $current_year => new DateTime($current_year . '-12-31 ' . $expiry_time)
        );
        
        foreach ($reminders as $reminder) {
            // Skip if reminder is disabled
            if (empty($reminder['enabled'])) {
                continue;
            }
            
            $timing = intval($reminder['timing']);
            $unit = isset($reminder['unit']) ? $reminder['unit'] : 'days';
            $period = isset($reminder['period']) ? $reminder['period'] : 'before';
            
            // Get reminder ID using a deterministic key based on reminder properties
            $reminder_id = self::get_reminder_id($reminder);
            
            // Check each expiry date
            foreach ($expiry_dates as $expiry_year => $expiry_date) {
                // Calculate the send date for this reminder
                $send_date = clone $expiry_date;
                
                // Calculate offset based on unit (supports minutes, hours, days, weeks)
                switch ($unit) {
                    case 'minutes':
                        $offset = $timing . ' minutes';
                        break;
                    case 'hours':
                        $offset = $timing . ' hours';
                        break;
                    case 'weeks':
                        $offset = ($timing * 7) . ' days';
                        break;
                    case 'days':
                    default:
                        $offset = $timing . ' days';
                        break;
                }
                
                // Adjust send date based on period (before or after)
                if ($period === 'before') {
                    $send_date->modify("-{$offset}");
                    // For "before" reminders, target year is the year after expiry
                    $target_year = $expiry_year + 1;
                } else {
                    $send_date->modify("+{$offset}");
                    // For "after" reminders, target year is the expiry year
                    $target_year = $expiry_year;
                }
                
                // Check if the send date has passed and this reminder hasn't been sent yet for this target year
                // This allows reminders to be sent even if the cron didn't run on the exact send date
                if ($today >= $send_date && !self::reminder_already_sent($reminder_id, $target_year)) {
                    // Send reminders
                    self::send_dynamic_reminder($reminder, $target_year);
                    
                    // Mark this reminder as sent for this year
                    self::mark_reminder_sent($reminder_id, $target_year);
                }
            }
        }
        
        // Clear cache after processing
        self::$sent_reminders_cache = null;
    }
    
    /**
     * Generate a deterministic reminder ID
     * Uses reminder properties to create a stable identifier
     * Always returns a string for consistent comparison and storage
     */
    private static function get_reminder_id($reminder) {
        // Use the explicit ID if set, converting to string for consistency
        if (isset($reminder['id'])) {
            return strval($reminder['id']);
        }
        
        // Create a deterministic key from timing, unit, and period
        $timing = isset($reminder['timing']) ? $reminder['timing'] : 0;
        $unit = isset($reminder['unit']) ? $reminder['unit'] : 'days';
        $period = isset($reminder['period']) ? $reminder['period'] : 'before';
        
        return "reminder_{$timing}_{$unit}_{$period}";
    }
    
    /**
     * Generate a unique key for tracking sent reminders
     */
    private static function get_sent_reminder_key($reminder_id, $year) {
        // Ensure reminder_id is a string for consistent key generation
        return strval($reminder_id) . '_' . $year;
    }
    
    /**
     * Check if a reminder has already been sent for a specific year
     */
    private static function reminder_already_sent($reminder_id, $year) {
        $key = self::get_sent_reminder_key($reminder_id, $year);
        return isset(self::$sent_reminders_cache[$key]);
    }
    
    /**
     * Mark a reminder as sent for a specific year
     */
    private static function mark_reminder_sent($reminder_id, $year) {
        $key = self::get_sent_reminder_key($reminder_id, $year);
        
        // Update cache first to prevent duplicate sends in same execution
        self::$sent_reminders_cache[$key] = true;
        
        // Persist to database
        update_option('wdta_sent_reminders', self::$sent_reminders_cache);
    }
    
    /**
     * Public method to mark a reminder as sent for a specific year
     * Used when admin manually sends all overdue emails via the "Send All Now" button
     * This prevents the cron from sending duplicate emails
     * 
     * @param string|int $reminder_id The reminder identifier
     * @param int $year The target year for the reminder
     * @return bool True if marked successfully, false if invalid parameters
     */
    public static function mark_reminder_as_sent($reminder_id, $year) {
        // Validate and normalize reminder_id to string
        if (empty($reminder_id)) {
            return false;
        }
        $reminder_id = strval($reminder_id);
        
        $year = intval($year);
        $current_year = intval(date('Y'));
        if ($year < $current_year - 10 || $year > $current_year + 10) {
            return false;
        }
        
        // Load existing sent reminders from database
        $sent_reminders = get_option('wdta_sent_reminders', array());
        $key = $reminder_id . '_' . $year;
        
        // Mark as sent
        $sent_reminders[$key] = true;
        
        // Persist to database
        update_option('wdta_sent_reminders', $sent_reminders);
        
        return true;
    }
    
    /**
     * Mark a reminder as sent for a specific user
     * Used when admin manually sends an email to a single user via the "Send Now" button
     * This prevents the user from receiving duplicate emails
     * 
     * @param string|int $reminder_id The reminder identifier
     * @param int $year The target year for the reminder
     * @param int $user_id The WordPress user ID
     * @return bool True if marked successfully, false if invalid parameters
     */
    public static function mark_user_reminder_sent($reminder_id, $year, $user_id) {
        // Validate and normalize reminder_id to string
        if (empty($reminder_id)) {
            return false;
        }
        $reminder_id = strval($reminder_id);
        
        $year = intval($year);
        $user_id = intval($user_id);
        $current_year = intval(date('Y'));
        
        // Validate year is within a reasonable range (10 years past or future)
        // This prevents storing/processing invalid years while allowing historical data
        // and future scheduling within a practical business timeframe
        if ($year < $current_year - 10 || $year > $current_year + 10) {
            return false;
        }
        
        if ($user_id <= 0) {
            return false;
        }
        
        // Load existing sent user reminders from database
        $sent_user_reminders = get_option('wdta_sent_reminder_users', array());
        $key = $reminder_id . '_' . $year . '_' . $user_id;
        
        // Mark as sent
        $sent_user_reminders[$key] = true;
        
        // Persist to database
        update_option('wdta_sent_reminder_users', $sent_user_reminders);
        
        return true;
    }
    
    /**
     * Check if a reminder has been sent to a specific user
     * 
     * @param string|int $reminder_id The reminder identifier
     * @param int $year The target year for the reminder
     * @param int $user_id The WordPress user ID
     * @return bool True if already sent, false otherwise
     */
    public static function has_user_received_reminder($reminder_id, $year, $user_id) {
        $sent_user_reminders = get_option('wdta_sent_reminder_users', array());
        // Normalize reminder_id to string for consistent key lookup
        $key = strval($reminder_id) . '_' . $year . '_' . $user_id;
        return isset($sent_user_reminders[$key]);
    }
    
    /**
     * Send a dynamic reminder email
     */
    private static function send_dynamic_reminder($reminder, $year) {
        // Get all users who should have membership but don't
        $users = WDTA_Database::get_users_without_membership($year);
        
        // Get reminder ID for tracking
        $reminder_id = self::get_reminder_id($reminder);
        
        foreach ($users as $user) {
            // Check if user has already received this reminder
            if (self::has_user_received_reminder($reminder_id, $year, $user->ID)) {
                continue;
            }
            
            // Check if user has pending payment (for after expiry reminders)
            $membership = WDTA_Database::get_user_membership($user->ID, $year);
            
            // Only send if no membership or payment not completed
            if (!$membership || $membership->payment_status !== 'completed') {
                WDTA_Email_Notifications::send_dynamic_reminder($user->ID, $year, $reminder);
                
                // Mark user as having received this reminder
                self::mark_user_reminder_sent($reminder_id, $year, $user->ID);
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
        
        // Get all users who will be affected by this deactivation
        // before we update the database
        $affected_users = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT user_id FROM $table_name 
            WHERE membership_year = %d 
            AND (payment_status != 'completed' OR status != 'active')",
            $year
        ));
        
        // Update all memberships for the year that haven't been paid
        // Set status to 'inactive' for all non-completed payments
        $wpdb->query($wpdb->prepare(
            "UPDATE $table_name 
            SET status = 'inactive' 
            WHERE membership_year = %d 
            AND (payment_status != 'completed' OR status != 'active')",
            $year
        ));
        
        // Update user roles for all affected users
        // This ensures their WordPress role matches their membership status
        if (!empty($affected_users)) {
            $user_roles = WDTA_User_Roles::get_instance();
            $current_year = date('Y');
            
            foreach ($affected_users as $user_id) {
                // Update role based on current year membership status
                // This will set them to inactive_member if they don't have
                // an active membership for the current year
                $user_roles->update_user_role($user_id, $current_year);
            }
        }
    }
}
