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
        add_action('user_register', array($this, 'handle_user_registration'), 10, 1);
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
        
        // Transactional email settings
        register_setting('wdta_membership_settings', 'wdta_email_signup_enabled');
        register_setting('wdta_membership_settings', 'wdta_email_signup_recipient');
        register_setting('wdta_membership_settings', 'wdta_email_signup_subject');
        register_setting('wdta_membership_settings', 'wdta_email_signup_body');
        
        register_setting('wdta_membership_settings', 'wdta_email_payment_enabled');
        register_setting('wdta_membership_settings', 'wdta_email_payment_recipient');
        register_setting('wdta_membership_settings', 'wdta_email_payment_subject');
        register_setting('wdta_membership_settings', 'wdta_email_payment_body');
        
        register_setting('wdta_membership_settings', 'wdta_email_grace_enabled');
        register_setting('wdta_membership_settings', 'wdta_email_grace_recipient');
        register_setting('wdta_membership_settings', 'wdta_email_grace_subject');
        register_setting('wdta_membership_settings', 'wdta_email_grace_body');
        
        register_setting('wdta_membership_settings', 'wdta_email_expiry_enabled');
        register_setting('wdta_membership_settings', 'wdta_email_expiry_recipient');
        register_setting('wdta_membership_settings', 'wdta_email_expiry_subject');
        register_setting('wdta_membership_settings', 'wdta_email_expiry_body');
    }
    
    /**
     * Handle user registration - send signup confirmation email
     */
    public function handle_user_registration($user_id) {
        // Send signup confirmation email
        $current_year = date('Y');
        self::send_signup_confirmation($user_id, $current_year);
    }
    
    /**
     * Send reminder email 1 month before (December 1st)
     */
    public static function send_reminder_1_month($user_id, $year) {
        $user = get_userdata($user_id);
        $subject = get_option('wdta_email_reminder_1month_subject', 'WDTA Membership Renewal - Due January 1st, ' . $year);
        $template = get_option('wdta_email_reminder_1month', self::get_default_template('1_month'));
        
        $message = self::parse_template($template, $user, $year);
        self::send_email($user->user_email, $subject, $message);
    }
    
    /**
     * Send reminder email 1 week before (December 25th)
     */
    public static function send_reminder_1_week($user_id, $year) {
        $user = get_userdata($user_id);
        $subject = get_option('wdta_email_reminder_1week_subject', 'WDTA Membership Renewal - Due in 1 Week (January 1st, ' . $year . ')');
        $template = get_option('wdta_email_reminder_1week', self::get_default_template('1_week'));
        
        $message = self::parse_template($template, $user, $year);
        self::send_email($user->user_email, $subject, $message);
    }
    
    /**
     * Send reminder email 1 day before (December 31st)
     */
    public static function send_reminder_1_day($user_id, $year) {
        $user = get_userdata($user_id);
        $subject = get_option('wdta_email_reminder_1day_subject', 'WDTA Membership Renewal - Due Tomorrow (January 1st, ' . $year . ')');
        $template = get_option('wdta_email_reminder_1day', self::get_default_template('1_day'));
        
        $message = self::parse_template($template, $user, $year);
        self::send_email($user->user_email, $subject, $message);
    }
    
    /**
     * Send overdue email 1 day after (January 2nd)
     */
    public static function send_overdue_1_day($user_id, $year) {
        $user = get_userdata($user_id);
        $subject = get_option('wdta_email_overdue_1day_subject', 'WDTA Membership Overdue - Please Renew');
        $template = get_option('wdta_email_overdue_1day', self::get_default_template('overdue_1_day'));
        
        $message = self::parse_template($template, $user, $year);
        self::send_email($user->user_email, $subject, $message);
    }
    
    /**
     * Send overdue email 1 week after (January 8th)
     */
    public static function send_overdue_1_week($user_id, $year) {
        $user = get_userdata($user_id);
        $subject = get_option('wdta_email_overdue_1week_subject', 'WDTA Membership Overdue - Action Required');
        $template = get_option('wdta_email_overdue_1week', self::get_default_template('overdue_1_week'));
        
        $message = self::parse_template($template, $user, $year);
        self::send_email($user->user_email, $subject, $message);
    }
    
    /**
     * Send overdue email end of January (January 31st)
     */
    public static function send_overdue_end_jan($user_id, $year) {
        $user = get_userdata($user_id);
        $subject = get_option('wdta_email_overdue_end_jan_subject', 'WDTA Membership Overdue - 2 Months Remaining');
        $template = get_option('wdta_email_overdue_end_jan', self::get_default_template('overdue_end_jan'));
        
        $message = self::parse_template($template, $user, $year);
        self::send_email($user->user_email, $subject, $message);
    }
    
    /**
     * Send overdue email end of February (February 28th/29th)
     */
    public static function send_overdue_end_feb($user_id, $year) {
        $user = get_userdata($user_id);
        $subject = get_option('wdta_email_overdue_end_feb_subject', 'WDTA Membership Overdue - Final Month');
        $template = get_option('wdta_email_overdue_end_feb', self::get_default_template('overdue_end_feb'));
        
        $message = self::parse_template($template, $user, $year);
        self::send_email($user->user_email, $subject, $message);
    }
    
    /**
     * Send final overdue email end of March (March 31st)
     */
    public static function send_overdue_end_mar($user_id, $year) {
        $user = get_userdata($user_id);
        $subject = get_option('wdta_email_overdue_end_mar_subject', 'WDTA Membership - Final Notice');
        $template = get_option('wdta_email_overdue_end_mar', self::get_default_template('overdue_end_mar'));
        
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
            '{amount}' => number_format(wdta_get_membership_price(), 2),
            '{deadline}' => wdta_format_date('March 31, ' . $year),
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
            
            'overdue_end_mar' => "Dear {user_name},\n\nFINAL NOTICE: This is your last day to renew your WDTA membership for {year}.\n\nIf payment of {amount} is not received by midnight tonight ({deadline}), your membership will expire and you will lose access to all member-only content.\n\nRenew immediately: {renewal_url}\n\nBest regards,\n{site_name}",
            
            'signup' => "Dear {user_name},\n\nThank you for signing up for WDTA membership!\n\nWe have received your registration. To complete your membership, please make your payment of ${amount} AUD by {deadline}.\n\nYou can make a payment at: {renewal_url}\n\nIf you have any questions, please don't hesitate to contact us.\n\nBest regards,\nWDTA Team",
            
            'payment' => "Dear {user_name},\n\nThank you for your payment!\n\nYour WDTA membership for {year} is now active. Your membership is valid until December 31, {year}.\n\nPayment Details:\n- Amount: ${amount} AUD\n- Payment Method: {payment_method}\n- Payment Date: {payment_date}\n\nYou now have access to all member-only content on our website.\n\nBest regards,\nWDTA Team",
            
            'grace' => "Dear {user_name},\n\nYour WDTA membership for {year} is now in the grace period.\n\nYou have until {deadline} to renew your membership. After this date, your access to member-only content will be suspended.\n\nAmount due: ${amount} AUD\n\nRenew now at: {renewal_url}\n\nBest regards,\nWDTA Team",
            
            'expiry' => "Dear {user_name},\n\nYour WDTA membership for {year} has expired.\n\nYour access to member-only content has been suspended. To restore your membership, please make a payment of ${amount} AUD.\n\nRenew now at: {renewal_url}\n\nIf you have any questions or believe this is an error, please contact us.\n\nBest regards,\nWDTA Team"
        );
        
        return isset($templates[$type]) ? $templates[$type] : '';
    }
    
    /**
     * Send signup confirmation email
     */
    public static function send_signup_confirmation($user_id, $year) {
        $user = get_userdata($user_id);
        $subject = get_option('wdta_email_signup_subject', 'Welcome to WDTA Membership');
        $template = get_option('wdta_email_signup_body', self::get_default_template('signup'));
        
        $message = self::parse_template($template, $user, $year);
        
        // Send to customer if enabled
        if (get_option('wdta_email_signup_enabled', '1') === '1') {
            self::send_email($user->user_email, $subject, $message);
        }
        
        // Send to additional recipient if set
        $recipient = get_option('wdta_email_signup_recipient', '');
        if (!empty($recipient)) {
            self::send_email($recipient, '[Admin] ' . $subject, $message);
        }
    }
    
    /**
     * Send payment confirmation email
     */
    public static function send_payment_confirmation($user_id, $year, $payment_amount = null, $payment_method = 'Stripe', $payment_date = null) {
        // Use default membership price if no amount specified
        if ($payment_amount === null) {
            $payment_amount = wdta_get_membership_price();
        }
        $user = get_userdata($user_id);
        $subject = get_option('wdta_email_payment_subject', 'WDTA Membership Payment Confirmed');
        $template = get_option('wdta_email_payment_body', self::get_default_template('payment'));
        
        // Extended replacements for payment confirmation
        $replacements = array(
            '{user_name}' => $user->display_name,
            '{user_email}' => $user->user_email,
            '{year}' => $year,
            '{amount}' => number_format($payment_amount, 2),
            '{deadline}' => wdta_format_date('March 31, ' . $year),
            '{renewal_url}' => home_url('/membership'),
            '{site_name}' => get_bloginfo('name'),
            '{payment_method}' => $payment_method,
            '{payment_date}' => $payment_date ? wdta_format_date($payment_date) : wdta_format_date(current_time('mysql'))
        );
        
        $message = str_replace(array_keys($replacements), array_values($replacements), $template);
        
        // Send to customer if enabled
        if (get_option('wdta_email_payment_enabled', '1') === '1') {
            self::send_email($user->user_email, $subject, $message);
        }
        
        // Send to additional recipient if set
        $recipient = get_option('wdta_email_payment_recipient', '');
        if (!empty($recipient)) {
            self::send_email($recipient, '[Admin] ' . $subject, $message);
        }
    }
    
    /**
     * Send grace period notification email
     */
    public static function send_grace_period_notification($user_id, $year) {
        $user = get_userdata($user_id);
        $subject = get_option('wdta_email_grace_subject', 'WDTA Membership - Grace Period');
        $template = get_option('wdta_email_grace_body', self::get_default_template('grace'));
        
        $message = self::parse_template($template, $user, $year);
        
        // Send to customer if enabled
        if (get_option('wdta_email_grace_enabled', '1') === '1') {
            self::send_email($user->user_email, $subject, $message);
        }
        
        // Send to additional recipient if set
        $recipient = get_option('wdta_email_grace_recipient', '');
        if (!empty($recipient)) {
            self::send_email($recipient, '[Admin] ' . $subject, $message);
        }
    }
    
    /**
     * Send membership expiry notification email
     */
    public static function send_expiry_notification($user_id, $year) {
        $user = get_userdata($user_id);
        $subject = get_option('wdta_email_expiry_subject', 'WDTA Membership Expired');
        $template = get_option('wdta_email_expiry_body', self::get_default_template('expiry'));
        
        $message = self::parse_template($template, $user, $year);
        
        // Send to customer if enabled
        if (get_option('wdta_email_expiry_enabled', '1') === '1') {
            self::send_email($user->user_email, $subject, $message);
        }
        
        // Send to additional recipient if set
        $recipient = get_option('wdta_email_expiry_recipient', '');
        if (!empty($recipient)) {
            self::send_email($recipient, '[Admin] ' . $subject, $message);
        }
    }
}
