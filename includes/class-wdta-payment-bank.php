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
        $expiry_date = $year . '-03-31';
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
        
        $subject = 'New Bank Transfer Submission - WDTA Membership ' . $year;
        $message = "A new bank transfer payment has been submitted:\n\n";
        $message .= "User: {$user->display_name} ({$user->user_email})\n";
        $message .= "Year: {$year}\n";
        $message .= "Reference: {$reference}\n";
        $message .= "Amount: \$950 AUD\n\n";
        $message .= "Please verify the payment and update the membership status in the admin panel.\n";
        $message .= admin_url('admin.php?page=wdta-memberships');
        
        wp_mail($admin_email, $subject, $message);
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
        
        // Get template and subject from settings
        $subject = get_option('wdta_email_payment_subject', 'WDTA Membership Payment Confirmed - {year}');
        $template = get_option('wdta_email_payment', 
'Dear {user_name},

Your bank transfer payment of $950.00 AUD for {year} has been verified.

Your WDTA membership is now active and will remain valid until December 31, {year}.

Best regards,
WDTA Team');
        
        // Parse template (modify for bank transfer - no card processing fee)
        $message = WDTA_Email_Notifications::parse_template($template, $user, $year);
        // Remove card processing fee line for bank transfers
        $message = str_replace("Card processing fee (2.2%): $20.90 AUD\n", "", $message);
        $message = str_replace("Total paid: $970.90 AUD", "Total paid: $950.00 AUD", $message);
        
        $subject = str_replace('{year}', $year, $subject);
        
        // Send to customer if enabled
        if (get_option('wdta_email_payment_to_customer', 1)) {
            wp_mail($user->user_email, $subject, $message);
        }
        
        // Send to admin if enabled
        if (get_option('wdta_email_payment_to_admin', 1)) {
            $admin_email = get_option('wdta_email_admin_recipient', get_option('admin_email'));
            $admin_subject = '[Admin] ' . $subject . ' - ' . $user->display_name;
            wp_mail($admin_email, $admin_subject, $message);
        }
    }
}
