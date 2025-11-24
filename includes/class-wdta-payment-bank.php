<?php
/**
 * Bank transfer payment handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDTA_Payment_Bank {
    
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
        add_action('wp_ajax_wdta_submit_bank_transfer', array($this, 'submit_bank_transfer'));
        add_action('wp_ajax_nopriv_wdta_register_and_submit_bank_transfer', array($this, 'register_and_submit_bank_transfer'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wdta_membership_settings', 'wdta_bank_name');
        register_setting('wdta_membership_settings', 'wdta_bank_account_name');
        register_setting('wdta_membership_settings', 'wdta_bank_bsb');
        register_setting('wdta_membership_settings', 'wdta_bank_account_number');
    }
    
    /**
     * Handle bank transfer submission
     */
    public function submit_bank_transfer() {
        check_ajax_referer('wdta_membership_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in'));
            return;
        }
        
        $user_id = get_current_user_id();
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        $reference = isset($_POST['reference']) ? sanitize_text_field($_POST['reference']) : '';
        $payment_date = isset($_POST['payment_date']) ? sanitize_text_field($_POST['payment_date']) : current_time('mysql');
        
        // Create pending membership record
        $expiry_date = $year . '-12-31';
        WDTA_Database::save_membership(array(
            'user_id' => $user_id,
            'membership_year' => $year,
            'payment_amount' => 950.00,
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending_verification',
            'payment_reference' => $reference,
            'expiry_date' => $expiry_date,
            'status' => 'pending'
        ));
        
        // Notify admin
        $this->notify_admin_of_bank_transfer($user_id, $year, $reference);
        
        wp_send_json_success(array(
            'message' => 'Bank transfer details submitted. Your membership will be activated once payment is verified.'
        ));
    }
    
    /**
     * Notify admin of bank transfer
     */
    private function notify_admin_of_bank_transfer($user_id, $year, $reference) {
        $user = get_userdata($user_id);
        $admin_email = get_option('admin_email');
        $additional_recipients = get_option('wdta_payment_admin_recipients', '');
        
        // Get customizable template
        $subject = get_option('wdta_email_bank_pending_subject', 'New Bank Transfer Submission - WDTA Membership {year}');
        $template = get_option('wdta_email_bank_pending_body',
'A new bank transfer payment has been submitted:

User: {user_name} ({user_email})
Year: {year}
Reference: {reference}
Amount: $950 AUD

Please verify the payment and update the membership status in the admin panel.
{admin_url}');
        
        // Replace placeholders
        $replacements = array(
            '{user_name}' => $user->display_name,
            '{user_email}' => $user->user_email,
            '{year}' => $year,
            '{reference}' => $reference,
            '{admin_url}' => admin_url('admin.php?page=wdta-memberships'),
            '{site_name}' => get_bloginfo('name')
        );
        
        $subject = str_replace(array_keys($replacements), array_values($replacements), $subject);
        $message = str_replace(array_keys($replacements), array_values($replacements), $template);
        
        // Convert line breaks to HTML for proper email formatting
        $message = wpautop($message);
        
        // Send to admin
        wp_mail($admin_email, $subject, $message);
        
        // Send to additional recipients if configured
        if (!empty($additional_recipients)) {
            $recipients = array_map('trim', explode(',', $additional_recipients));
            foreach ($recipients as $recipient) {
                if (is_email($recipient)) {
                    wp_mail($recipient, $subject, $message);
                }
            }
        }
    }
    
    /**
     * Manually approve bank transfer (called from admin)
     */
    public static function approve_bank_transfer($user_id, $year) {
        WDTA_Database::save_membership(array(
            'user_id' => $user_id,
            'membership_year' => $year,
            'payment_status' => 'completed',
            'payment_date' => current_time('mysql'),
            'status' => 'active'
        ));
        
        // Update user role
        do_action('wdta_membership_activated', $user_id, $year);
        
        // Send confirmation email
        self::send_approval_confirmation($user_id, $year);
    }
    
    /**
     * Send approval confirmation email
     */
    private static function send_approval_confirmation($user_id, $year) {
        $user = get_userdata($user_id);
        $to = $user->user_email;
        
        // Get customizable template
        $subject = get_option('wdta_email_bank_approved_subject', 'WDTA Membership Activated - {year}');
        $template = get_option('wdta_email_bank_approved_body',
'Dear {user_name},

Your bank transfer payment of $950.00 AUD for {year} has been verified.

Your WDTA membership is now active and will remain valid until December 31, {year}.

Best regards,
WDTA Team');
        
        // Replace placeholders
        $replacements = array(
            '{user_name}' => $user->display_name,
            '{user_email}' => $user->user_email,
            '{year}' => $year,
            '{site_name}' => get_bloginfo('name')
        );
        
        $subject = str_replace(array_keys($replacements), array_values($replacements), $subject);
        $message = str_replace(array_keys($replacements), array_values($replacements), $template);
        
        // Convert line breaks to HTML for proper email formatting
        $message = wpautop($message);
        
        // Prepare headers with CC
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // Add CC recipients for signup emails
        $cc_recipients = get_option('wdta_signup_email_cc', 'marketing@wdta.org.au, treasurer@wdta.org.au');
        if (!empty($cc_recipients)) {
            $cc_emails = array_map('trim', explode(',', $cc_recipients));
            foreach ($cc_emails as $cc_email) {
                if (is_email($cc_email)) {
                    $headers[] = 'Cc: ' . $cc_email;
                }
            }
        }
        
        wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Register new user and submit bank transfer
     */
    public function register_and_submit_bank_transfer() {
        check_ajax_referer('wdta_membership_nonce', 'nonce');
        
        // Get and validate registration data
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        $reference = isset($_POST['reference']) ? sanitize_text_field($_POST['reference']) : '';
        $payment_date = isset($_POST['payment_date']) ? sanitize_text_field($_POST['payment_date']) : current_time('mysql');
        
        // Validate required fields
        if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
            wp_send_json_error(array('message' => 'All registration fields are required'));
            return;
        }
        
        // Validate email format
        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Invalid email address'));
            return;
        }
        
        // Validate password strength
        if (strlen($password) < 8) {
            wp_send_json_error(array('message' => 'Password must be at least 8 characters'));
            return;
        }
        
        // Check if email already exists
        if (email_exists($email)) {
            wp_send_json_error(array('message' => 'This email is already registered. Please log in instead.'));
            return;
        }
        
        // Create the user account
        $username = $email; // Use email as username
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => 'Could not create account: ' . $user_id->get_error_message()));
            return;
        }
        
        // Update user meta with first and last name
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name
        ));
        
        // Log the user in
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        
        // Create pending membership record
        $expiry_date = $year . '-12-31';
        WDTA_Database::save_membership(array(
            'user_id' => $user_id,
            'membership_year' => $year,
            'payment_amount' => 950.00,
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending_verification',
            'payment_reference' => $reference,
            'expiry_date' => $expiry_date,
            'status' => 'pending'
        ));
        
        // Notify admin
        $this->notify_admin_of_bank_transfer($user_id, $year, $reference);
        
        wp_send_json_success(array(
            'message' => 'Registration successful! Bank transfer details submitted. Your membership will be activated once payment is verified.'
        ));
    }
}
