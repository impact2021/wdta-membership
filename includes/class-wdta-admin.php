<?php
/**
 * Admin interface class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDTA_Admin {
    
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
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_wdta_approve_membership', array($this, 'approve_membership'));
        add_action('wp_ajax_wdta_reject_membership', array($this, 'reject_membership'));
        add_action('wp_ajax_wdta_update_membership', array($this, 'update_membership'));
        add_action('wp_ajax_wdta_delete_membership', array($this, 'delete_membership'));
        add_action('wp_ajax_wdta_add_membership', array($this, 'add_membership'));
        add_action('wp_ajax_wdta_send_scheduled_email', array($this, 'send_scheduled_email'));
        add_action('wp_ajax_wdta_mark_reminder_sent', array($this, 'mark_reminder_sent'));
        add_action('wp_ajax_wdta_debug_scheduled_emails', array($this, 'debug_scheduled_emails'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Membership',
            'Membership',
            'manage_options',
            'wdta-memberships',
            array($this, 'memberships_page'),
            'dashicons-groups',
            30
        );
        
        add_submenu_page(
            'wdta-memberships',
            'All Memberships',
            'All Memberships',
            'manage_options',
            'wdta-memberships',
            array($this, 'memberships_page')
        );
        
        add_submenu_page(
            'wdta-memberships',
            'Settings',
            'Settings',
            'manage_options',
            'wdta-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'wdta-memberships',
            'Emails',
            'Emails',
            'manage_options',
            'wdta-emails',
            array($this, 'emails_page')
        );
        
        add_submenu_page(
            'wdta-memberships',
            'Scheduled Emails',
            'Scheduled Emails',
            'manage_options',
            'wdta-scheduled-emails',
            array($this, 'scheduled_emails_page')
        );
        
        add_submenu_page(
            'wdta-memberships',
            'Documentation',
            'Docs',
            'manage_options',
            'wdta-documentation',
            array($this, 'documentation_page')
        );
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // List of our admin page hooks
        $wdta_pages = array(
            'toplevel_page_wdta-memberships',
            'membership_page_wdta-memberships',
            'membership_page_wdta-settings',
            'membership_page_wdta-emails',
            'membership_page_wdta-scheduled-emails',
            'membership_page_wdta-documentation'
        );
        
        // Check if we're on one of our admin pages
        // Also check for any page containing 'wdta-' or 'wdta_' as fallback
        if (!in_array($hook, $wdta_pages) && 
            strpos($hook, 'wdta-') === false && 
            strpos($hook, 'wdta_') === false) {
            return;
        }
        
        wp_enqueue_style(
            'wdta-admin-css',
            WDTA_MEMBERSHIP_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WDTA_MEMBERSHIP_VERSION
        );
        
        wp_enqueue_script(
            'wdta-admin-js',
            WDTA_MEMBERSHIP_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            WDTA_MEMBERSHIP_VERSION,
            true
        );
        
        wp_localize_script('wdta-admin-js', 'wdtaAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wdta_admin_nonce')
        ));
    }
    
    /**
     * Memberships page
     */
    public function memberships_page() {
        $current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        
        $memberships = WDTA_Database::get_memberships_by_year($current_year, $status_filter);
        
        include WDTA_MEMBERSHIP_PLUGIN_DIR . 'admin/memberships-list.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        if (isset($_POST['wdta_settings_submit'])) {
            check_admin_referer('wdta_settings_action', 'wdta_settings_nonce');
            $this->save_settings();
        }
        
        include WDTA_MEMBERSHIP_PLUGIN_DIR . 'admin/settings.php';
    }
    
    /**
     * Emails page
     */
    public function emails_page() {
        if (isset($_POST['wdta_emails_submit'])) {
            check_admin_referer('wdta_emails_action', 'wdta_emails_nonce');
            $this->save_email_templates();
        }
        
        include WDTA_MEMBERSHIP_PLUGIN_DIR . 'admin/emails.php';
    }
    
    /**
     * Documentation page
     */
    public function documentation_page() {
        include WDTA_MEMBERSHIP_PLUGIN_DIR . 'admin/documentation.php';
    }
    
    /**
     * Scheduled emails page - shows upcoming email reminders
     */
    public function scheduled_emails_page() {
        include WDTA_MEMBERSHIP_PLUGIN_DIR . 'admin/scheduled-emails.php';
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        // Membership pricing
        if (isset($_POST['wdta_membership_price'])) {
            $price = floatval($_POST['wdta_membership_price']);
            // Validate price is positive and within reasonable bounds (0-10000 AUD)
            if ($price >= 0 && $price <= 10000) {
                update_option('wdta_membership_price', $price);
            } else {
                add_settings_error('wdta_settings', 'invalid_price', 'Membership price must be between $0 and $10,000 AUD.', 'error');
            }
        }
        
        // Stripe settings
        if (isset($_POST['wdta_stripe_public_key'])) {
            update_option('wdta_stripe_public_key', sanitize_text_field($_POST['wdta_stripe_public_key']));
        }
        if (isset($_POST['wdta_stripe_secret_key'])) {
            update_option('wdta_stripe_secret_key', sanitize_text_field($_POST['wdta_stripe_secret_key']));
        }
        if (isset($_POST['wdta_stripe_webhook_secret'])) {
            update_option('wdta_stripe_webhook_secret', sanitize_text_field($_POST['wdta_stripe_webhook_secret']));
        }
        
        // Bank transfer settings
        if (isset($_POST['wdta_bank_name'])) {
            update_option('wdta_bank_name', sanitize_text_field($_POST['wdta_bank_name']));
        }
        if (isset($_POST['wdta_bank_account_name'])) {
            update_option('wdta_bank_account_name', sanitize_text_field($_POST['wdta_bank_account_name']));
        }
        if (isset($_POST['wdta_bank_bsb'])) {
            update_option('wdta_bank_bsb', sanitize_text_field($_POST['wdta_bank_bsb']));
        }
        if (isset($_POST['wdta_bank_account_number'])) {
            update_option('wdta_bank_account_number', sanitize_text_field($_POST['wdta_bank_account_number']));
        }
        
        // Email settings
        if (isset($_POST['wdta_email_from_name'])) {
            update_option('wdta_email_from_name', sanitize_text_field($_POST['wdta_email_from_name']));
        }
        if (isset($_POST['wdta_email_from_address'])) {
            update_option('wdta_email_from_address', sanitize_email($_POST['wdta_email_from_address']));
        }
        if (isset($_POST['wdta_renewal_url'])) {
            update_option('wdta_renewal_url', esc_url_raw($_POST['wdta_renewal_url']));
        }
        
        // Year cutoff settings
        if (isset($_POST['wdta_year_cutoff_month'])) {
            update_option('wdta_year_cutoff_month', intval($_POST['wdta_year_cutoff_month']));
        }
        if (isset($_POST['wdta_year_cutoff_day'])) {
            update_option('wdta_year_cutoff_day', intval($_POST['wdta_year_cutoff_day']));
        }
        
        // Restricted pages
        if (isset($_POST['wdta_restricted_pages'])) {
            $restricted_pages = array_map('intval', (array) $_POST['wdta_restricted_pages']);
            update_option('wdta_restricted_pages', $restricted_pages);
        } else {
            update_option('wdta_restricted_pages', array());
        }
        
        // Access denied page
        if (isset($_POST['wdta_access_denied_page'])) {
            update_option('wdta_access_denied_page', intval($_POST['wdta_access_denied_page']));
        }
        
        // Login redirect settings for each role
        $wp_roles = wp_roles();
        foreach ($wp_roles->roles as $role_key => $role_data) {
            if ($role_key !== 'administrator') {
                $redirect_key = 'wdta_login_redirect_' . $role_key;
                if (isset($_POST[$redirect_key])) {
                    $redirect_value = sanitize_text_field($_POST[$redirect_key]);
                    update_option($redirect_key, $redirect_value);
                }
            }
        }
        
        add_settings_error('wdta_settings', 'settings_updated', 'Settings saved successfully.', 'updated');
    }
    
    /**
     * Approve membership (AJAX)
     */
    public function approve_membership() {
        check_ajax_referer('wdta_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Invalid user ID'));
            return;
        }
        
        WDTA_Payment_Bank::approve_bank_transfer($user_id, $year);
        
        wp_send_json_success(array('message' => 'Membership approved'));
    }
    
    /**
     * Reject membership (AJAX)
     */
    public function reject_membership() {
        check_ajax_referer('wdta_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Invalid user ID'));
            return;
        }
        
        WDTA_Database::save_membership(array(
            'user_id' => $user_id,
            'membership_year' => $year,
            'payment_status' => 'rejected',
            'status' => 'rejected'
        ));
        
        wp_send_json_success(array('message' => 'Membership rejected'));
    }
    
    /**
     * Update membership
     */
    public function update_membership() {
        check_ajax_referer('wdta_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        $payment_status = isset($_POST['payment_status']) ? sanitize_text_field($_POST['payment_status']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $payment_amount = isset($_POST['payment_amount']) ? floatval($_POST['payment_amount']) : 0;
        $expiry_date = isset($_POST['expiry_date']) ? sanitize_text_field($_POST['expiry_date']) : '';
        
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Invalid user ID'));
            return;
        }
        
        // If payment status is being set to completed, automatically set membership status to active
        // This allows admins to activate memberships regardless of current status (pending, rejected, expired)
        // as payment completion should grant access
        if ($payment_status === 'completed' && $status !== 'active') {
            $status = 'active';
        }
        
        // Update membership record
        WDTA_Database::save_membership(array(
            'user_id' => $user_id,
            'membership_year' => $year,
            'payment_status' => $payment_status,
            'status' => $status,
            'payment_amount' => $payment_amount,
            'expiry_date' => $expiry_date
        ));
        
        // Update user role using the centralized WDTA_User_Roles system
        $user_roles = WDTA_User_Roles::get_instance();
        $user_roles->update_user_role($user_id, $year);
        
        wp_send_json_success(array('message' => 'Membership updated successfully'));
    }
    
    /**
     * Delete membership (AJAX)
     * 
     * Permanently removes a membership record from the database. This endpoint
     * handles AJAX requests from the admin interface to delete memberships.
     * Useful for cleaning up orphaned memberships or removing incorrect entries.
     * 
     * Security:
     * - Verifies AJAX nonce to prevent CSRF attacks
     * - Checks user has 'manage_options' capability
     * - Validates and sanitizes all input parameters
     * 
     * Expected $_POST parameters:
     * - user_id (int): The WordPress user ID
     * - year (int): The membership year
     * - nonce (string): WordPress nonce for verification
     * 
     * @return void Sends JSON response and exits
     */
    public function delete_membership() {
        check_ajax_referer('wdta_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Invalid user ID'));
            return;
        }
        
        $result = WDTA_Database::delete_membership($user_id, $year);
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Membership deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete membership'));
        }
    }
    
    /**
     * Add new membership (AJAX)
     * 
     * Creates a new membership record for a user who doesn't have one for the specified year.
     * This allows admins to add memberships for users who currently have no membership records.
     * 
     * Security:
     * - Verifies AJAX nonce to prevent CSRF attacks
     * - Checks user has 'manage_options' capability
     * - Validates and sanitizes all input parameters
     * 
     * Expected $_POST parameters:
     * - user_id (int): The WordPress user ID
     * - year (int): The membership year
     * - payment_method (string): Payment method (bank_transfer, stripe, manual)
     * - payment_status (string): Payment status
     * - status (string): Membership status
     * - payment_amount (float): Payment amount
     * - expiry_date (string): Expiry date in Y-m-d format
     * - nonce (string): WordPress nonce for verification
     * 
     * @return void Sends JSON response and exits
     */
    public function add_membership() {
        check_ajax_referer('wdta_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        $payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : 'manual';
        $payment_status = isset($_POST['payment_status']) ? sanitize_text_field($_POST['payment_status']) : 'pending';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'pending';
        $payment_amount = isset($_POST['payment_amount']) ? floatval($_POST['payment_amount']) : 950.00;
        $expiry_date = isset($_POST['expiry_date']) ? sanitize_text_field($_POST['expiry_date']) : $year . '-12-31';
        
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Please select a user'));
            return;
        }
        
        // Check if user exists
        $user = get_userdata($user_id);
        if (!$user) {
            wp_send_json_error(array('message' => 'User not found'));
            return;
        }
        
        // Validate year (reasonable range)
        if ($year < 2000 || $year > date('Y') + 5) {
            wp_send_json_error(array('message' => 'Invalid membership year'));
            return;
        }
        
        // Validate payment_method
        $valid_payment_methods = array('bank_transfer', 'stripe', 'manual');
        if (!in_array($payment_method, $valid_payment_methods)) {
            wp_send_json_error(array('message' => 'Invalid payment method'));
            return;
        }
        
        // Validate payment_status  
        $valid_payment_statuses = array('pending', 'pending_verification', 'completed', 'failed', 'rejected');
        if (!in_array($payment_status, $valid_payment_statuses)) {
            wp_send_json_error(array('message' => 'Invalid payment status'));
            return;
        }
        
        // Validate membership status
        $valid_statuses = array('pending', 'active', 'expired', 'rejected');
        if (!in_array($status, $valid_statuses)) {
            wp_send_json_error(array('message' => 'Invalid membership status'));
            return;
        }
        
        // Validate expiry_date format
        if (!empty($expiry_date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiry_date)) {
            wp_send_json_error(array('message' => 'Invalid expiry date format'));
            return;
        }
        
        // Check if membership already exists for this user and year
        $existing = WDTA_Database::get_user_membership($user_id, $year);
        if ($existing) {
            wp_send_json_error(array('message' => 'This user already has a membership for ' . $year . '. Please edit the existing membership instead.'));
            return;
        }
        
        // If payment status is being set to completed, automatically set membership status to active
        if ($payment_status === 'completed' && $status !== 'active') {
            $status = 'active';
        }
        
        // Create membership record
        $result = WDTA_Database::save_membership(array(
            'user_id' => $user_id,
            'membership_year' => $year,
            'payment_method' => $payment_method,
            'payment_status' => $payment_status,
            'status' => $status,
            'payment_amount' => $payment_amount,
            'expiry_date' => $expiry_date,
            'payment_date' => ($payment_status === 'completed') ? current_time('mysql') : null
        ));
        
        if ($result) {
            // Update user role using the centralized WDTA_User_Roles system
            $user_roles = WDTA_User_Roles::get_instance();
            $user_roles->update_user_role($user_id, $year);
            
            wp_send_json_success(array('message' => 'Membership added successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to add membership'));
        }
    }
    
    /**
     * Send scheduled email now (AJAX)
     * 
     * Allows admins to manually trigger a scheduled email for a specific user.
     * This is useful for sending overdue emails or resending failed emails.
     * 
     * Security:
     * - Verifies AJAX nonce to prevent CSRF attacks
     * - Checks user has 'manage_options' capability
     * - Validates and sanitizes all input parameters
     * 
     * Expected $_POST parameters:
     * - user_id (int): The WordPress user ID to send email to
     * - reminder_id (string): The reminder identifier
     * - target_year (int): The membership year the reminder is for
     * - nonce (string): WordPress nonce for verification
     * 
     * @return void Sends JSON response and exits
     */
    public function send_scheduled_email() {
        check_ajax_referer('wdta_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $reminder_id = isset($_POST['reminder_id']) ? sanitize_text_field($_POST['reminder_id']) : '';
        $target_year = isset($_POST['target_year']) ? intval($_POST['target_year']) : date('Y');
        
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Invalid user ID'));
            return;
        }
        
        // Check if user exists
        $user = get_userdata($user_id);
        if (!$user) {
            wp_send_json_error(array('message' => 'User not found'));
            return;
        }
        
        // Find the reminder configuration
        $reminders = get_option('wdta_email_reminders', array());
        $reminder = null;
        
        foreach ($reminders as $r) {
            // Validate that required fields exist
            if (!isset($r['timing']) || !isset($r['unit']) || !isset($r['period'])) {
                continue;
            }
            
            // Build the deterministic ID for this reminder (same logic as WDTA_Cron::get_reminder_id)
            $r_explicit_id = isset($r['id']) ? strval($r['id']) : '';
            $r_generated_id = "reminder_{$r['timing']}_{$r['unit']}_{$r['period']}";
            
            // Match against explicit ID or generated ID
            if (($r_explicit_id !== '' && $r_explicit_id === $reminder_id) || $r_generated_id === $reminder_id) {
                $reminder = $r;
                break;
            }
        }
        
        if (!$reminder) {
            wp_send_json_error(array('message' => 'Reminder template not found'));
            return;
        }
        
        // Send the email
        WDTA_Email_Notifications::send_dynamic_reminder($user_id, $target_year, $reminder);
        
        // Mark this user as having received this reminder to prevent duplicates
        WDTA_Cron::mark_user_reminder_sent($reminder_id, $target_year, $user_id);
        
        wp_send_json_success(array('message' => 'Email sent successfully to ' . $user->user_email));
    }
    
    /**
     * Mark a reminder as sent (AJAX)
     * 
     * This is called after the admin uses "Send All Now" to manually send all overdue emails.
     * Marking the reminder as sent prevents the cron from sending duplicate emails.
     * 
     * Security:
     * - Verifies AJAX nonce to prevent CSRF attacks
     * - Checks user has 'manage_options' capability
     * - Validates and sanitizes all input parameters
     * 
     * Expected $_POST parameters:
     * - reminder_id (string): The reminder identifier
     * - target_year (int): The membership year the reminder is for
     * - nonce (string): WordPress nonce for verification
     * 
     * @return void Sends JSON response and exits
     */
    public function mark_reminder_sent() {
        check_ajax_referer('wdta_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        
        $reminder_id = isset($_POST['reminder_id']) ? sanitize_text_field($_POST['reminder_id']) : '';
        $target_year = isset($_POST['target_year']) ? intval($_POST['target_year']) : 0;
        
        if (empty($reminder_id)) {
            wp_send_json_error(array('message' => 'Invalid reminder ID'));
            return;
        }
        
        // Validate year is within reasonable bounds
        $current_year = intval(date('Y'));
        if ($target_year < $current_year - 10 || $target_year > $current_year + 10) {
            wp_send_json_error(array('message' => 'Invalid target year'));
            return;
        }
        
        // Mark the reminder as sent so cron won't send duplicates
        $result = WDTA_Cron::mark_reminder_as_sent($reminder_id, $target_year);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Reminder marked as sent'));
        } else {
            wp_send_json_error(array('message' => 'Failed to mark reminder as sent'));
        }
    }
    
    /**
     * Save email templates
     */
    private function save_email_templates() {
        // Inactive users report settings
        update_option('wdta_email_inactive_report_enabled', isset($_POST['wdta_email_inactive_report_enabled']) ? '1' : '0');
        if (isset($_POST['wdta_inactive_report_emails'])) {
            update_option('wdta_inactive_report_emails', sanitize_text_field($_POST['wdta_inactive_report_emails']));
        }
        
        // Additional admin recipients for payment notifications
        if (isset($_POST['wdta_payment_admin_recipients'])) {
            update_option('wdta_payment_admin_recipients', sanitize_text_field($_POST['wdta_payment_admin_recipients']));
        }
        
        // CC recipients for signup emails
        if (isset($_POST['wdta_signup_email_cc'])) {
            update_option('wdta_signup_email_cc', sanitize_text_field($_POST['wdta_signup_email_cc']));
        }
        
        // CC recipients for reminder emails
        if (isset($_POST['wdta_reminder_email_cc'])) {
            update_option('wdta_reminder_email_cc', sanitize_text_field($_POST['wdta_reminder_email_cc']));
        }
        
        // Welcome/confirmation email templates
        $confirmation_templates = array(
            'stripe_confirmation',
            'bank_pending',
            'bank_approved'
        );
        
        foreach ($confirmation_templates as $template) {
            // Save subject and body
            if (isset($_POST['wdta_email_' . $template . '_subject'])) {
                update_option('wdta_email_' . $template . '_subject', sanitize_text_field($_POST['wdta_email_' . $template . '_subject']));
            }
            if (isset($_POST['wdta_email_' . $template . '_body'])) {
                update_option('wdta_email_' . $template . '_body', wp_kses_post($_POST['wdta_email_' . $template . '_body']));
            }
        }
        
        // Save dynamic reminders
        if (isset($_POST['wdta_reminders']) && is_array($_POST['wdta_reminders'])) {
            $reminders = array();
            
            foreach ($_POST['wdta_reminders'] as $reminder_id => $reminder_data) {
                $reminders[] = array(
                    'id' => intval($reminder_data['id']),
                    'enabled' => isset($reminder_data['enabled']) && $reminder_data['enabled'] === '1',
                    'timing' => intval($reminder_data['timing']),
                    'unit' => sanitize_text_field($reminder_data['unit']),
                    'period' => sanitize_text_field($reminder_data['period']),
                    'subject' => sanitize_text_field($reminder_data['subject']),
                    'body' => wp_kses_post($reminder_data['body'])
                );
            }
            
            update_option('wdta_email_reminders', $reminders);
        } else {
            // If no reminders submitted, clear the option
            update_option('wdta_email_reminders', array());
        }
        
        add_settings_error('wdta_emails', 'emails_updated', 'Email templates saved successfully.', 'updated');
    }
    
    /**
     * Debug scheduled emails (AJAX)
     * 
     * Provides detailed diagnostic information about why emails may or may not be showing
     * in the scheduled emails list. This helps troubleshoot issues with email scheduling.
     * 
     * Returns:
     * - User counts (total, admins, potential recipients)
     * - Membership statistics for previous/current/next year
     * - Reminder configuration details
     * - Sent reminder status
     * - Grace period members
     * - Detailed breakdown of why each user is included/excluded
     * 
     * @return void Sends JSON response and exits
     */
    public function debug_scheduled_emails() {
        check_ajax_referer('wdta_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        $current_year = (int) date('Y');
        $previous_year = $current_year - 1;
        $next_year = $current_year + 1;
        
        $debug_info = array();
        
        // Get all users
        $all_users = get_users(array('fields' => array('ID', 'user_email', 'display_name')));
        $debug_info['total_users'] = count($all_users);
        
        // Count administrators
        $admins = array_filter($all_users, function($user) {
            return user_can($user->ID, 'manage_options');
        });
        $debug_info['admin_count'] = count($admins);
        $debug_info['admin_users'] = array_map(function($user) {
            return array(
                'id' => $user->ID,
                'email' => $user->user_email,
                'name' => $user->display_name
            );
        }, array_values($admins));
        
        // Get users without membership for each year
        $recipients_prev = WDTA_Database::get_users_without_membership($previous_year);
        $recipients_curr = WDTA_Database::get_users_without_membership($current_year);
        $recipients_next = WDTA_Database::get_users_without_membership($next_year);
        
        $debug_info['recipients'] = array(
            'for_year_' . $previous_year => count($recipients_prev),
            'for_year_' . $current_year => count($recipients_curr),
            'for_year_' . $next_year => count($recipients_next)
        );
        
        // Get membership counts by year and status
        foreach (array($previous_year, $current_year, $next_year) as $year) {
            $memberships = $wpdb->get_results($wpdb->prepare(
                "SELECT status, payment_status, COUNT(*) as count 
                FROM $table_name 
                WHERE membership_year = %d 
                GROUP BY status, payment_status",
                $year
            ));
            
            $debug_info['memberships_year_' . $year] = array();
            foreach ($memberships as $m) {
                $key = $m->status . '/' . $m->payment_status;
                $debug_info['memberships_year_' . $year][$key] = (int) $m->count;
            }
        }
        
        // Get reminder configuration
        $reminders = get_option('wdta_email_reminders', array());
        $debug_info['reminder_config'] = array(
            'total_reminders' => count($reminders),
            'enabled_count' => count(array_filter($reminders, function($r) { return !empty($r['enabled']); })),
            'disabled_count' => count(array_filter($reminders, function($r) { return empty($r['enabled']); })),
            'reminders' => array_map(function($r) {
                return array(
                    'id' => isset($r['id']) ? $r['id'] : 'not_set',
                    'enabled' => !empty($r['enabled']),
                    'timing' => isset($r['timing']) ? $r['timing'] : 'not_set',
                    'unit' => isset($r['unit']) ? $r['unit'] : 'not_set',
                    'period' => isset($r['period']) ? $r['period'] : 'not_set',
                    'subject' => isset($r['subject']) ? substr($r['subject'], 0, 50) . '...' : 'not_set'
                );
            }, $reminders)
        );
        
        // Get sent reminders
        $sent_reminders = get_option('wdta_sent_reminders', array());
        $debug_info['sent_reminders'] = array(
            'total_sent' => count($sent_reminders),
            'list' => array_keys($sent_reminders)
        );
        
        // Get sent user reminders
        $sent_user_reminders = get_option('wdta_sent_reminder_users', array());
        $debug_info['sent_user_reminders'] = array(
            'total_sent' => count($sent_user_reminders),
            'sample' => array_slice(array_keys($sent_user_reminders), 0, 10)
        );
        
        // Get sample of users and their membership status
        $sample_users = array_slice($all_users, 0, 10);
        $debug_info['sample_user_analysis'] = array();
        
        foreach ($sample_users as $user) {
            $is_admin = user_can($user->ID, 'manage_options');
            $prev_membership = WDTA_Database::get_user_membership($user->ID, $previous_year);
            $curr_membership = WDTA_Database::get_user_membership($user->ID, $current_year);
            $next_membership = WDTA_Database::get_user_membership($user->ID, $next_year);
            
            $debug_info['sample_user_analysis'][] = array(
                'id' => $user->ID,
                'email' => $user->user_email,
                'name' => $user->display_name,
                'is_admin' => $is_admin,
                'prev_year_membership' => $prev_membership ? array(
                    'status' => $prev_membership->status,
                    'payment_status' => $prev_membership->payment_status
                ) : null,
                'curr_year_membership' => $curr_membership ? array(
                    'status' => $curr_membership->status,
                    'payment_status' => $curr_membership->payment_status
                ) : null,
                'next_year_membership' => $next_membership ? array(
                    'status' => $next_membership->status,
                    'payment_status' => $next_membership->payment_status
                ) : null,
                'would_receive_reminders_for' => array(
                    $previous_year => in_array($user->ID, array_map(function($u) { return $u->ID; }, $recipients_prev)),
                    $current_year => in_array($user->ID, array_map(function($u) { return $u->ID; }, $recipients_curr)),
                    $next_year => in_array($user->ID, array_map(function($u) { return $u->ID; }, $recipients_next))
                )
            );
        }
        
        // Current date/time info
        $now = new DateTime();
        $debug_info['current_datetime'] = array(
            'now' => $now->format('Y-m-d H:i:s'),
            'timezone' => $now->getTimezone()->getName(),
            'current_year' => $current_year,
            'previous_year' => $previous_year,
            'next_year' => $next_year
        );
        
        // Calculate what should be showing on the page
        $debug_info['expected_scheduled_emails'] = $this->calculate_expected_scheduled_emails($reminders, $current_year, $now);
        
        wp_send_json_success($debug_info);
    }
    
    /**
     * Calculate what emails should be showing on the scheduled emails page
     * 
     * @param array $reminders Reminder configurations
     * @param int $current_year Current year
     * @param DateTime $now Current date/time
     * @return array List of expected scheduled emails
     */
    private function calculate_expected_scheduled_emails($reminders, $current_year, $now) {
        $expected = array();
        
        if (empty($reminders)) {
            return array('message' => 'No reminders configured');
        }
        
        $previous_year = $current_year - 1;
        $next_year = $current_year + 1;
        $expiry_time = WDTA_Cron::EXPIRY_TIME;
        
        $end_date = clone $now;
        $end_date->modify('+3 months');
        
        $overdue_start_date = clone $now;
        $overdue_start_date->modify('-6 months');
        
        $expiry_dates = array(
            $previous_year => new DateTime($previous_year . '-12-31 ' . $expiry_time),
            $current_year => new DateTime($current_year . '-12-31 ' . $expiry_time),
            $next_year => new DateTime($next_year . '-12-31 ' . $expiry_time)
        );
        
        $sent_reminders = get_option('wdta_sent_reminders', array());
        $sent_user_reminders = get_option('wdta_sent_reminder_users', array());
        
        foreach ($reminders as $reminder) {
            if (empty($reminder['enabled'])) {
                continue;
            }
            
            $timing = intval($reminder['timing']);
            $unit = isset($reminder['unit']) ? $reminder['unit'] : 'days';
            $period = isset($reminder['period']) ? $reminder['period'] : 'before';
            
            foreach ($expiry_dates as $expiry_year => $expiry_date) {
                $send_date = clone $expiry_date;
                
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
                
                if ($period === 'before') {
                    $send_date->modify("-{$offset}");
                    $target_year = $expiry_year + 1;
                } else {
                    $send_date->modify("+{$offset}");
                    $target_year = $expiry_year;
                }
                
                $is_upcoming = ($send_date >= $now && $send_date <= $end_date);
                $is_overdue = ($send_date < $now && $send_date >= $overdue_start_date);
                
                if ($is_upcoming || $is_overdue) {
                    $reminder_id = isset($reminder['id']) ? $reminder['id'] : "reminder_{$timing}_{$unit}_{$period}";
                    $sent_key = $reminder_id . '_' . $target_year;
                    $already_sent = isset($sent_reminders[$sent_key]);
                    
                    $recipients = WDTA_Database::get_users_without_membership($target_year);
                    
                    // Filter out users who have already received this specific reminder
                    $recipients = array_filter($recipients, function($user) use ($reminder_id, $target_year, $sent_user_reminders) {
                        $key = $reminder_id . '_' . $target_year . '_' . $user->ID;
                        return !isset($sent_user_reminders[$key]);
                    });
                    
                    $expected[] = array(
                        'reminder_id' => $reminder_id,
                        'timing' => $timing . ' ' . $unit . ' ' . $period,
                        'send_date' => $send_date->format('Y-m-d H:i:s'),
                        'target_year' => $target_year,
                        'is_upcoming' => $is_upcoming,
                        'is_overdue' => $is_overdue,
                        'already_sent_batch' => $already_sent,
                        'recipient_count_before_filter' => count(WDTA_Database::get_users_without_membership($target_year)),
                        'recipient_count_after_filter' => count($recipients),
                        'will_show_on_page' => !$already_sent && count($recipients) > 0
                    );
                }
            }
        }
        
        return $expected;
    }
}
