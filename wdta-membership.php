<?php
/**
 * Plugin Name: WDTA Membership
 * Plugin URI: https://github.com/impact2021/wdta-membership
 * Description: Annual membership plugin with Stripe/bank transfer payments and automatic page access control. Membership fee is $950 AUD annually, due by December 31st.
 * Version: 3.0.0
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
define('WDTA_MEMBERSHIP_VERSION', '3.0.0');
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

/**
 * Get default email reminder template
 */
function wdta_get_default_reminder_template() {
    return 'Dear {user_name},

This is a reminder about your WDTA membership for {year}.

The annual membership fee is ${amount} AUD and must be paid by {deadline}.

You can renew your membership at: {renewal_url}

Best regards,
WDTA Team';
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
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/class-wdta-user-roles.php';

/**
 * Initialize the plugin
 */
function wdta_membership_init() {
    $plugin = WDTA_Membership::get_instance();
    $plugin->init();
    
    // Initialize user roles
    $user_roles = WDTA_User_Roles::get_instance();
    $user_roles->init();
    
    // Initialize cron action hooks (must be called on every request)
    WDTA_Cron::init();
    
    // Run migration if needed
    wdta_membership_migrate_reminders();
    
    // Ensure email check cron runs hourly (for hour-based reminders)
    wdta_ensure_hourly_email_check();
}
add_action('plugins_loaded', 'wdta_membership_init');

/**
 * Ensure the email check cron runs hourly instead of daily
 * This allows hour-based and minute-based reminders to be sent on time
 */
function wdta_ensure_hourly_email_check() {
    // Check if already migrated to hourly
    if (get_option('wdta_cron_hourly_migrated', false)) {
        return;
    }
    
    // Reschedule to hourly
    WDTA_Cron::reschedule_email_check();
    
    // Mark migration complete
    update_option('wdta_cron_hourly_migrated', true);
}

/**
 * Migrate old hardcoded reminders to new dynamic system
 */
function wdta_membership_migrate_reminders() {
    // Check if migration has already been done
    if (get_option('wdta_reminders_migrated', false)) {
        return;
    }
    
    // Get existing reminders configuration
    $existing_reminders = get_option('wdta_email_reminders', array());
    
    // Only migrate if no reminders exist yet
    if (!empty($existing_reminders)) {
        update_option('wdta_reminders_migrated', true);
        return;
    }
    
    // Define legacy reminder mappings
    $legacy_reminders = array(
        array(
            'id' => 1,
            'timing' => 30,
            'unit' => 'days',
            'period' => 'before',
            'enabled_option' => 'wdta_email_reminder_1month_enabled',
            'subject_option' => 'wdta_email_reminder_1month_subject',
            'body_option' => 'wdta_email_reminder_1month_body',
            'default_subject' => 'WDTA Membership Renewal - Due January 1st'
        ),
        array(
            'id' => 2,
            'timing' => 1,
            'unit' => 'weeks',
            'period' => 'before',
            'enabled_option' => 'wdta_email_reminder_1week_enabled',
            'subject_option' => 'wdta_email_reminder_1week_subject',
            'body_option' => 'wdta_email_reminder_1week_body',
            'default_subject' => 'WDTA Membership - Due in 1 Week'
        ),
        array(
            'id' => 3,
            'timing' => 1,
            'unit' => 'days',
            'period' => 'before',
            'enabled_option' => 'wdta_email_reminder_1day_enabled',
            'subject_option' => 'wdta_email_reminder_1day_subject',
            'body_option' => 'wdta_email_reminder_1day_body',
            'default_subject' => 'WDTA Membership - Due Tomorrow'
        ),
        array(
            'id' => 4,
            'timing' => 1,
            'unit' => 'days',
            'period' => 'after',
            'enabled_option' => 'wdta_email_reminder_1day_overdue_enabled',
            'subject_option' => 'wdta_email_reminder_1day_overdue_subject',
            'body_option' => 'wdta_email_reminder_1day_overdue_body',
            'default_subject' => 'WDTA Membership - Payment Overdue'
        ),
        array(
            'id' => 5,
            'timing' => 1,
            'unit' => 'weeks',
            'period' => 'after',
            'enabled_option' => 'wdta_email_reminder_1week_overdue_enabled',
            'subject_option' => 'wdta_email_reminder_1week_overdue_subject',
            'body_option' => 'wdta_email_reminder_1week_overdue_body',
            'default_subject' => 'WDTA Membership - Urgent: Payment Required'
        ),
        array(
            'id' => 6,
            'timing' => 31,
            'unit' => 'days',
            'period' => 'after',
            'enabled_option' => 'wdta_email_reminder_month1_enabled',
            'subject_option' => 'wdta_email_reminder_month1_subject',
            'body_option' => 'wdta_email_reminder_month1_body',
            'default_subject' => 'WDTA Membership - Final Notice Month 1'
        ),
        array(
            'id' => 7,
            'timing' => 59,
            'unit' => 'days',
            'period' => 'after',
            'enabled_option' => 'wdta_email_reminder_month2_enabled',
            'subject_option' => 'wdta_email_reminder_month2_subject',
            'body_option' => 'wdta_email_reminder_month2_body',
            'default_subject' => 'WDTA Membership - Final Notice Month 2'
        ),
        array(
            'id' => 8,
            'timing' => 90,
            'unit' => 'days',
            'period' => 'after',
            'enabled_option' => 'wdta_email_reminder_final_enabled',
            'subject_option' => 'wdta_email_reminder_final_subject',
            'body_option' => 'wdta_email_reminder_final_body',
            'default_subject' => 'WDTA Membership - Access Suspended'
        )
    );
    
    $migrated_reminders = array();
    
    foreach ($legacy_reminders as $legacy) {
        // Check if this reminder was enabled in the old system
        $enabled = get_option($legacy['enabled_option'], '1') === '1';
        
        // Get subject and body if they exist
        $subject = get_option($legacy['subject_option'], $legacy['default_subject']);
        $body = get_option($legacy['body_option'], '');
        
        // Only migrate if there's content or it was explicitly enabled
        if (!empty($body) || $enabled) {
            $migrated_reminders[] = array(
                'id' => $legacy['id'],
                'enabled' => $enabled,
                'timing' => $legacy['timing'],
                'unit' => $legacy['unit'],
                'period' => $legacy['period'],
                'subject' => $subject,
                'body' => $body
            );
        }
    }
    
    // If we migrated any reminders, save them
    if (!empty($migrated_reminders)) {
        update_option('wdta_email_reminders', $migrated_reminders);
    } else {
        // No existing reminders found, set up the default one
        $default_reminder = array(
            array(
                'id' => 1,
                'enabled' => true,
                'timing' => 30,
                'unit' => 'days',
                'period' => 'before',
                'subject' => 'WDTA Membership Renewal - Due January 1st',
                'body' => wdta_get_default_reminder_template()
            )
        );
        update_option('wdta_email_reminders', $default_reminder);
    }
    
    // Mark migration as complete
    update_option('wdta_reminders_migrated', true);
}

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
