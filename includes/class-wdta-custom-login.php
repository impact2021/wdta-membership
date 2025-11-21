<?php
/**
 * Custom login page handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDTA_Custom_Login {
    
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
        // Register custom login page
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'custom_login_page'));
        
        // Redirect default login to custom page
        add_action('login_init', array($this, 'redirect_to_custom_login'));
        
        // Handle login form submission
        add_action('wp_ajax_nopriv_wdta_custom_login', array($this, 'handle_login'));
        add_action('wp_ajax_nopriv_wdta_lost_password', array($this, 'handle_lost_password'));
        
        // Enqueue styles for custom login
        add_action('wp_enqueue_scripts', array($this, 'enqueue_login_styles'));
    }
    
    /**
     * Add rewrite rules for custom login page
     */
    public function add_rewrite_rules() {
        add_rewrite_rule('^member-login/?$', 'index.php?wdta_custom_login=1', 'top');
        add_rewrite_rule('^member-login/lost-password/?$', 'index.php?wdta_custom_login=1&wdta_action=lost_password', 'top');
    }
    
    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'wdta_custom_login';
        $vars[] = 'wdta_action';
        return $vars;
    }
    
    /**
     * Display custom login page
     */
    public function custom_login_page() {
        if (get_query_var('wdta_custom_login')) {
            // If already logged in, redirect to homepage
            if (is_user_logged_in()) {
                wp_redirect(home_url());
                exit;
            }
            
            $action = get_query_var('wdta_action', 'login');
            
            include WDTA_MEMBERSHIP_PLUGIN_DIR . 'templates/custom-login.php';
            exit;
        }
    }
    
    /**
     * Redirect default login to custom page
     */
    public function redirect_to_custom_login() {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
        
        if ($action == 'lostpassword') {
            wp_redirect(home_url('/member-login/lost-password/'));
            exit;
        } else {
            wp_redirect(home_url('/member-login/'));
            exit;
        }
    }
    
    /**
     * Handle login form submission
     */
    public function handle_login() {
        check_ajax_referer('wdta_login_nonce', 'nonce');
        
        $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $remember = isset($_POST['remember']) ? true : false;
        
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );
        
        $user = wp_signon($creds, false);
        
        if (is_wp_error($user)) {
            wp_send_json_error(array('message' => $user->get_error_message()));
        } else {
            wp_send_json_success(array('redirect' => home_url()));
        }
    }
    
    /**
     * Handle lost password form submission
     */
    public function handle_lost_password() {
        check_ajax_referer('wdta_login_nonce', 'nonce');
        
        $user_login = isset($_POST['user_login']) ? sanitize_text_field($_POST['user_login']) : '';
        
        if (empty($user_login)) {
            wp_send_json_error(array('message' => 'Please enter your username or email address.'));
            return;
        }
        
        $errors = retrieve_password($user_login);
        
        if (is_wp_error($errors)) {
            wp_send_json_error(array('message' => $errors->get_error_message()));
        } else {
            wp_send_json_success(array('message' => 'Check your email for the confirmation link.'));
        }
    }
    
    /**
     * Enqueue login styles
     */
    public function enqueue_login_styles() {
        if (get_query_var('wdta_custom_login')) {
            wp_enqueue_style(
                'wdta-custom-login-css',
                WDTA_MEMBERSHIP_PLUGIN_URL . 'assets/css/custom-login.css',
                array(),
                WDTA_MEMBERSHIP_VERSION
            );
        }
    }
}
