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
        if (strpos($hook, 'wdta-') === false) {
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
     * Save email templates
     */
    private function save_email_templates() {
        // Inactive users report settings
        update_option('wdta_email_inactive_report_enabled', isset($_POST['wdta_email_inactive_report_enabled']) ? '1' : '0');
        if (isset($_POST['wdta_inactive_report_emails'])) {
            update_option('wdta_inactive_report_emails', sanitize_text_field($_POST['wdta_inactive_report_emails']));
        }
        
        // Reminder email templates
        $email_templates = array(
            'reminder_1month',
            'reminder_1week',
            'reminder_1day',
            'overdue_1day',
            'overdue_1week',
            'overdue_end_jan',
            'overdue_end_feb',
            'overdue_end_mar'
        );
        
        foreach ($email_templates as $template) {
            // Save enabled/disabled status
            update_option('wdta_email_' . $template . '_enabled', isset($_POST['wdta_email_' . $template . '_enabled']) ? '1' : '0');
            
            // Save subject and body
            if (isset($_POST['wdta_email_' . $template . '_subject'])) {
                update_option('wdta_email_' . $template . '_subject', sanitize_text_field($_POST['wdta_email_' . $template . '_subject']));
            }
            if (isset($_POST['wdta_email_' . $template . '_body'])) {
                update_option('wdta_email_' . $template . '_body', wp_kses_post($_POST['wdta_email_' . $template . '_body']));
            }
        }
        
        add_settings_error('wdta_emails', 'emails_updated', 'Email templates saved successfully.', 'updated');
    }
}
