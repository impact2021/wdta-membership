<?php
/**
 * Plugin Name: WDTA Membership
 * Plugin URI: https://github.com/impact2021/wdta-membership
 * Description: Membership management plugin for WDTA with payment integration and automated status tracking
 * Version: 1.0.0
 * Author: WDTA
 * License: GPL v2 or later
 * Text Domain: wdta-membership
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WDTA_MEMBERSHIP_VERSION', '1.0.0');
define('WDTA_MEMBERSHIP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WDTA_MEMBERSHIP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-membership-activator.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-membership-database.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-membership-status.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-membership-email.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-membership-shortcodes.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-membership-admin.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-membership-cron.php';

// Activation and deactivation hooks
register_activation_hook(__FILE__, array('WDTA_Membership_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('WDTA_Membership_Activator', 'deactivate'));

// Initialize the plugin
function wdta_membership_init() {
    // Initialize database
    WDTA_Membership_Database::init();
    
    // Initialize shortcodes
    WDTA_Membership_Shortcodes::init();
    
    // Initialize admin
    if (is_admin()) {
        WDTA_Membership_Admin::init();
    }
    
    // Initialize cron jobs
    WDTA_Membership_Cron::init();
    
    // Initialize status checker
    WDTA_Membership_Status::init();
}
add_action('plugins_loaded', 'wdta_membership_init');

// Enqueue scripts and styles
function wdta_membership_enqueue_scripts() {
    wp_enqueue_style('wdta-membership-style', WDTA_MEMBERSHIP_PLUGIN_URL . 'assets/css/wdta-membership.css', array(), WDTA_MEMBERSHIP_VERSION);
    wp_enqueue_script('wdta-membership-script', WDTA_MEMBERSHIP_PLUGIN_URL . 'assets/js/wdta-membership.js', array('jquery'), WDTA_MEMBERSHIP_VERSION, true);
    
    // Localize script for AJAX
    wp_localize_script('wdta-membership-script', 'wdtaMembership', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wdta_membership_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'wdta_membership_enqueue_scripts');
