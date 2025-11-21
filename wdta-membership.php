<?php
/**
 * Plugin Name: WDTA Membership
 * Plugin URI: https://github.com/impact2021/wdta-membership
 * Description: Annual membership plugin with Stripe/bank transfer payments and automatic page access control. Membership fee is $950 AUD annually, due by March 31st.
 * Version: 1.1.5
 * Author: WDTA
 * Author URI: https://wdta.org
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wdta-membership
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WDTA_MEMBERSHIP_VERSION', '1.1.5');
define('WDTA_MEMBERSHIP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WDTA_MEMBERSHIP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WDTA_MEMBERSHIP_PLUGIN_FILE', __FILE__);

/**
 * Format date in dd/mm/yyyy format
 */
function wdta_format_date($date_string) {
    if (empty($date_string)) {
        return '';
    }
    return date('d/m/Y', strtotime($date_string));
}

// Include required files
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-membership.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-database.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-access-control.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-payment-stripe.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-payment-bank.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-email-notifications.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-admin.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-cron.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-custom-login.php';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-user-roles.php';

/**
 * Initialize the plugin
 */
function wdta_membership_init() {
    $plugin = WDTA_Membership::get_instance();
    $plugin->init();
    
    // Initialize custom login
    $custom_login = WDTA_Custom_Login::get_instance();
    $custom_login->init();
    
    // Initialize user roles
    $user_roles = WDTA_User_Roles::get_instance();
    $user_roles->init();
}
add_action('plugins_loaded', 'wdta_membership_init');

/**
 * Activation hook
 */
function wdta_membership_activate() {
    require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-database.php';
    require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-user-roles.php';
    WDTA_Database::create_tables();
    WDTA_Cron::schedule_events();
    
    // Add custom roles
    $user_roles = WDTA_User_Roles::get_instance();
    $user_roles->add_custom_roles();
    
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wdta_membership_activate');

/**
 * Deactivation hook
 */
function wdta_membership_deactivate() {
    WDTA_Cron::clear_scheduled_events();
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'wdta_membership_deactivate');
