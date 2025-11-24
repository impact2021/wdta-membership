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
        
        // AJAX handlers for login form
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
     * Handle AJAX login request
     */
    public function handle_ajax_login() {
        // Get credentials
        $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $remember = isset($_POST['remember']) ? (bool) $_POST['remember'] : false;
        
        // Validate inputs
        if (empty($username) || empty($password)) {
            wp_send_json_error(array(
                'message' => 'Please provide both username and password.'
            ));
            return;
        }
        
        // Attempt to authenticate
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );
        
        $user = wp_signon($creds, is_ssl());
        
        if (is_wp_error($user)) {
            wp_send_json_error(array(
                'message' => $user->get_error_message()
            ));
            return;
        }
        
        // Login successful
        // Determine redirect URL based on user role
        $redirect_url = home_url('/');
        
        // Get user roles
        if (isset($user->roles) && is_array($user->roles)) {
            $user_role = $user->roles[0];
            
            // Check for custom redirect
            $custom_redirect = get_option('wdta_login_redirect_' . $user_role, '');
            
            if (!empty($custom_redirect)) {
                if ($custom_redirect === 'home') {
                    $redirect_url = home_url('/');
                } elseif (is_numeric($custom_redirect)) {
                    $page_url = get_permalink(intval($custom_redirect));
                    if ($page_url) {
                        $redirect_url = $page_url;
                    }
                }
            }
        }
        
        wp_send_json_success(array(
            'message' => 'Login successful!',
            'redirect' => $redirect_url
        ));
    }
}
