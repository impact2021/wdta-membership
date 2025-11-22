<?php
/**
 * Main plugin class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDTA_Membership {
    
    /**
     * Single instance of the class
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
        // Private constructor to prevent direct instantiation
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Initialize components
        $this->init_access_control();
        $this->init_payment_handlers();
        $this->init_email_notifications();
        $this->init_admin();
        $this->init_hooks();
    }
    
    /**
     * Initialize access control
     */
    private function init_access_control() {
        WDTA_Access_Control::get_instance()->init();
    }
    
    /**
     * Initialize payment handlers
     */
    private function init_payment_handlers() {
        WDTA_Payment_Stripe::get_instance()->init();
        WDTA_Payment_Bank::get_instance()->init();
    }
    
    /**
     * Initialize email notifications
     */
    private function init_email_notifications() {
        WDTA_Email_Notifications::get_instance()->init();
    }
    
    /**
     * Initialize admin interface
     */
    private function init_admin() {
        if (is_admin()) {
            WDTA_Admin::get_instance()->init();
        }
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Add shortcodes
        add_shortcode('wdta_membership_form', array($this, 'membership_form_shortcode'));
        add_shortcode('wdta_membership_status', array($this, 'membership_status_shortcode'));
        add_shortcode('wdta_login_form', array($this, 'login_form_shortcode'));
        
        // Enqueue frontend styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
        
        // Hide admin bar for non-admin users
        add_action('after_setup_theme', array($this, 'hide_admin_bar_for_non_admins'));
        
        // Redirect to my-account after login
        add_filter('login_redirect', array($this, 'redirect_after_login'), 10, 3);
        
        // AJAX login handler
        add_action('wp_ajax_nopriv_wdta_ajax_login', array($this, 'handle_ajax_login'));
        add_action('wp_ajax_wdta_ajax_login', array($this, 'handle_ajax_login'));
    }
    
    /**
     * Enqueue frontend styles
     */
    public function enqueue_frontend_styles() {
        wp_enqueue_style(
            'wdta-frontend-css',
            WDTA_MEMBERSHIP_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            WDTA_MEMBERSHIP_VERSION
        );
    }
    
    /**
     * Membership form shortcode
     */
    public function membership_form_shortcode($atts) {
        ob_start();
        include WDTA_MEMBERSHIP_PLUGIN_DIR . 'templates/membership-form.php';
        return ob_get_clean();
    }
    
    /**
     * Membership status shortcode
     */
    public function membership_status_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please log in to view your membership status.</p>';
        }
        
        $user_id = get_current_user_id();
        $membership = WDTA_Database::get_user_membership($user_id);
        
        ob_start();
        include WDTA_MEMBERSHIP_PLUGIN_DIR . 'templates/membership-status.php';
        return ob_get_clean();
    }
    
    /**
     * Login form shortcode
     */
    public function login_form_shortcode($atts) {
        // Enqueue jQuery if not already enqueued
        wp_enqueue_script('jquery');
        
        ob_start();
        include WDTA_MEMBERSHIP_PLUGIN_DIR . 'templates/login-form-shortcode.php';
        return ob_get_clean();
    }
    
    /**
     * Hide admin bar for non-admin users
     */
    public function hide_admin_bar_for_non_admins() {
        if (!current_user_can('administrator') && !is_admin()) {
            show_admin_bar(false);
        }
    }
    
    /**
     * Redirect to my-account page after login
     */
    public function redirect_after_login($redirect_to, $request, $user) {
        // Only redirect if it's a successful login (user object exists)
        if (isset($user->roles) && is_array($user->roles)) {
            // Redirect non-admin users to my-account page
            if (!in_array('administrator', $user->roles)) {
                return home_url('/my-account/');
            }
        }
        return $redirect_to;
    }
    
    /**
     * Handle AJAX login request
     */
    public function handle_ajax_login() {
        // Verify nonce if you want (optional for login)
        
        // Get posted data
        $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $remember = isset($_POST['remember']) && $_POST['remember'] === '1';
        
        // Validate input
        if (empty($username) || empty($password)) {
            wp_send_json_error(array(
                'message' => 'Please enter both username and password.'
            ));
        }
        
        // Attempt login
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );
        
        $user = wp_signon($creds, false);
        
        if (is_wp_error($user)) {
            wp_send_json_error(array(
                'message' => $user->get_error_message()
            ));
        }
        
        // Login successful
        // Determine redirect URL
        $redirect_url = home_url('/my-account/');
        
        // If user is admin, use admin dashboard
        if (in_array('administrator', $user->roles)) {
            $redirect_url = admin_url();
        }
        
        wp_send_json_success(array(
            'message' => 'Login successful! Redirecting...',
            'redirect' => $redirect_url
        ));
    }
}
