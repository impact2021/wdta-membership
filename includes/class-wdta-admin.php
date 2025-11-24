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
     * Save settings
     */
    private function save_settings() {
        // Membership pricing
        if (isset($_POST['wdta_membership_price'])) {
            $price = floatval($_POST['wdta_membership_price']);
            update_option('wdta_membership_price', $price);
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
}
