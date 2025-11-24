<?php
/**
 * Email notification handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDTA_Email_Notifications {
    
    /**
     * Single instance
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Private constructor
    }
    
    /**
     * Initialize
     */
    public function init() {
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // Email templates - subjects
        register_setting('wdta_membership_settings', 'wdta_email_reminder_1month_subject');
        register_setting('wdta_membership_settings', 'wdta_email_reminder_1week_subject');
        register_setting('wdta_membership_settings', 'wdta_email_reminder_1day_subject');
        register_setting('wdta_membership_settings', 'wdta_email_overdue_1day_subject');
        register_setting('wdta_membership_settings', 'wdta_email_overdue_1week_subject');
        register_setting('wdta_membership_settings', 'wdta_email_overdue_end_jan_subject');
        register_setting('wdta_membership_settings', 'wdta_email_overdue_end_feb_subject');
        register_setting('wdta_membership_settings', 'wdta_email_overdue_end_mar_subject');
        
        // Email templates - content
        register_setting('wdta_membership_settings', 'wdta_email_from_name');
        register_setting('wdta_membership_settings', 'wdta_email_from_address');
        register_setting('wdta_membership_settings', 'wdta_email_reminder_1month');
        register_setting('wdta_membership_settings', 'wdta_email_reminder_1week');
        register_setting('wdta_membership_settings', 'wdta_email_reminder_1day');
        register_setting('wdta_membership_settings', 'wdta_email_overdue_1day');
        register_setting('wdta_membership_settings', 'wdta_email_overdue_1week');
        register_setting('wdta_membership_settings', 'wdta_email_overdue_end_jan');
        register_setting('wdta_membership_settings', 'wdta_email_overdue_end_feb');
        register_setting('wdta_membership_settings', 'wdta_email_overdue_end_mar');
    }
    
    /**
     * Send dynamic reminder email
     */
    public static function send_dynamic_reminder($user_id, $year, $reminder) {
        $user = get_userdata($user_id);
        
        if (!$user) {
            return false;
        }
        
        $subject = isset($reminder['subject']) ? $reminder['subject'] : 'WDTA Membership Reminder';
        $template = isset($reminder['body']) ? $reminder['body'] : '';
        
        $message = self::parse_template($template, $user, $year);
        self::send_email($user->user_email, $subject, $message);
    }
    
    /**
     * Parse email template
     */
    private static function parse_template($template, $user, $year) {
        $replacements = array(
            '{user_name}' => $user->display_name,
            '{user_email}' => $user->user_email,
            '{year}' => $year,
            '{amount}' => '950.00',
            '{deadline}' => wdta_format_date('December 31, ' . $year),
            '{renewal_url}' => home_url('/membership'),
            '{site_name}' => get_bloginfo('name')
        );
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    
    /**
     * Send email
     */
    private static function send_email($to, $subject, $message) {
        $from_name = get_option('wdta_email_from_name', get_bloginfo('name'));
        $from_address = get_option('wdta_email_from_address', get_option('admin_email'));
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            "From: {$from_name} <{$from_address}>"
        );
        
        wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Get default email template
     */
    private static function get_default_template($type) {
        $templates = array(
            '1_month' => "Dear {user_name},\n\nThis is a friendly reminder that your WDTA membership for {year} is due on January 1st, {year}.\n\nThe annual membership fee is {amount} and must be paid by {deadline} to maintain access to member-only content.\n\nYou can renew your membership here: {renewal_url}\n\nBest regards,\n{site_name}",
            
            '1_week' => "Dear {user_name},\n\nYour WDTA membership for {year} is due in one week (January 1st, {year}).\n\nPlease renew your membership of {amount} before {deadline} to ensure uninterrupted access to member-only content.\n\nRenew now: {renewal_url}\n\nBest regards,\n{site_name}",
            
            '1_day' => "Dear {user_name},\n\nThis is your final reminder that your WDTA membership for {year} is due tomorrow (January 1st, {year}).\n\nPlease renew your membership of {amount} as soon as possible. Payment must be received by {deadline}.\n\nRenew now: {renewal_url}\n\nBest regards,\n{site_name}",
            
            'overdue_1_day' => "Dear {user_name},\n\nYour WDTA membership for {year} is now overdue. The payment of {amount} was due on January 1st, {year}.\n\nTo maintain access to member-only content, please renew by {deadline}.\n\nRenew now: {renewal_url}\n\nBest regards,\n{site_name}",
            
            'overdue_1_week' => "Dear {user_name},\n\nYour WDTA membership payment for {year} remains outstanding. The payment of {amount} is now one week overdue.\n\nPlease renew by {deadline} to avoid losing access to member-only content.\n\nRenew now: {renewal_url}\n\nBest regards,\n{site_name}",
            
            'overdue_end_jan' => "Dear {user_name},\n\nYour WDTA membership for {year} is overdue. Payment of {amount} must be received by {deadline}.\n\nYou have 2 months remaining to renew your membership before access is revoked.\n\nRenew now: {renewal_url}\n\nBest regards,\n{site_name}",
            
            'overdue_end_feb' => "Dear {user_name},\n\nThis is an urgent reminder that your WDTA membership payment for {year} is overdue.\n\nYou have until {deadline} to pay the {amount} membership fee. After this date, you will lose access to all member-only content.\n\nRenew now: {renewal_url}\n\nBest regards,\n{site_name}",
            
            'overdue_end_mar' => "Dear {user_name},\n\nFINAL NOTICE: This is your last day to renew your WDTA membership for {year}.\n\nIf payment of {amount} is not received by midnight tonight ({deadline}), your membership will expire and you will lose access to all member-only content.\n\nRenew immediately: {renewal_url}\n\nBest regards,\n{site_name}"
        );
        
        return isset($templates[$type]) ? $templates[$type] : '';
    }
}
