<?php
/**
 * Plugin activation and deactivation handler
 */

class WDTA_Membership_Activator {
    
    /**
     * Plugin activation
     */
    public static function activate() {
        // Create database tables
        WDTA_Membership_Database::create_tables();
        
        // Schedule cron jobs
        if (!wp_next_scheduled('wdta_membership_daily_check')) {
            wp_schedule_event(time(), 'daily', 'wdta_membership_daily_check');
        }
        
        // Set default options
        self::set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('wdta_membership_daily_check');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        $defaults = array(
            'wdta_membership_currency' => 'USD',
            'wdta_membership_current_year_price' => '50.00',
            'wdta_membership_next_year_price' => '50.00',
            'wdta_membership_payment_method' => 'manual',
            
            // Email settings
            'wdta_membership_from_email' => get_option('admin_email'),
            'wdta_membership_from_name' => get_bloginfo('name'),
            
            // Inactive users email
            'wdta_membership_inactive_email_enabled' => 'yes',
            'wdta_membership_inactive_email_recipients' => get_option('admin_email'),
            'wdta_membership_inactive_email_subject' => 'Inactive WDTA Members Report',
            
            // Reminder emails - Before December 31st
            'wdta_membership_reminder1_enabled' => 'yes',
            'wdta_membership_reminder1_timing' => '30',
            'wdta_membership_reminder1_unit' => 'days',
            'wdta_membership_reminder1_period' => 'before',
            'wdta_membership_reminder1_subject' => 'WDTA Membership Renewal Reminder',
            
            'wdta_membership_reminder2_enabled' => 'yes',
            'wdta_membership_reminder2_timing' => '1',
            'wdta_membership_reminder2_unit' => 'weeks',
            'wdta_membership_reminder2_period' => 'before',
            'wdta_membership_reminder2_subject' => 'WDTA Membership Expires Soon',
            
            // Reminder emails - After December 31st
            'wdta_membership_reminder3_enabled' => 'yes',
            'wdta_membership_reminder3_timing' => '1',
            'wdta_membership_reminder3_unit' => 'weeks',
            'wdta_membership_reminder3_period' => 'after',
            'wdta_membership_reminder3_subject' => 'WDTA Membership Has Expired',
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
}
