<?php
/**
 * Access control class for restricting page access
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDTA_Access_Control {
    
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
     * Initialize access control
     */
    public function init() {
        add_action('template_redirect', array($this, 'check_page_access'));
        add_filter('the_content', array($this, 'filter_content'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wdta_membership_settings', 'wdta_restricted_pages');
        register_setting('wdta_membership_settings', 'wdta_access_denied_page');
    }
    
    /**
     * Check if page requires membership
     */
    private function is_restricted_page($page_id) {
        $restricted_pages = get_option('wdta_restricted_pages', array());
        return in_array($page_id, $restricted_pages);
    }
    
    /**
     * Check page access on template redirect
     */
    public function check_page_access() {
        if (!is_page() && !is_single()) {
            return;
        }
        
        $page_id = get_the_ID();
        
        if (!$this->is_restricted_page($page_id)) {
            return;
        }
        
        // Allow access for administrators
        if (current_user_can('manage_options')) {
            return;
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            $this->deny_access('login_required');
            return;
        }
        
        // Check membership status
        $user_id = get_current_user_id();
        if (!WDTA_Database::has_active_membership($user_id)) {
            $this->deny_access('membership_required');
            return;
        }
    }
    
    /**
     * Filter content for restricted pages
     */
    public function filter_content($content) {
        if (!is_main_query() || !in_the_loop()) {
            return $content;
        }
        
        $page_id = get_the_ID();
        
        if (!$this->is_restricted_page($page_id)) {
            return $content;
        }
        
        // Allow access for administrators
        if (current_user_can('manage_options')) {
            return $content;
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return $this->get_access_denied_message('login_required');
        }
        
        // Check membership status
        $user_id = get_current_user_id();
        if (!WDTA_Database::has_active_membership($user_id)) {
            return $this->get_access_denied_message('membership_required');
        }
        
        return $content;
    }
    
    /**
     * Deny access and redirect
     */
    private function deny_access($reason) {
        $access_denied_page = get_option('wdta_access_denied_page');
        
        if ($access_denied_page) {
            wp_redirect(get_permalink($access_denied_page) . '?reason=' . $reason);
            exit;
        } else {
            // Default message
            if ($reason === 'login_required') {
                wp_redirect(wp_login_url(get_permalink()));
                exit;
            } else {
                wp_die($this->get_access_denied_message($reason), 'Access Denied', array('response' => 403));
            }
        }
    }
    
    /**
     * Get access denied message
     */
    private function get_access_denied_message($reason) {
        if ($reason === 'login_required') {
            return '<div class="wdta-access-denied">
                <h3>Login Required</h3>
                <p>You must be logged in to view this content.</p>
                <p><a href="' . wp_login_url(get_permalink()) . '">Click here to login</a></p>
            </div>';
        } else {
            return '<div class="wdta-access-denied">
                <h3>Membership Required</h3>
                <p>This content is only available to active WDTA members.</p>
                <p>Annual membership is ' . wdta_get_membership_price(true) . ' and must be paid by March 31st each year.</p>
                <p><a href="' . esc_url(home_url('/membership')) . '">Renew or purchase membership</a></p>
            </div>';
        }
    }
}
