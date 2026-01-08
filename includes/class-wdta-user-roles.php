<?php
/**
 * User role management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDTA_User_Roles {
    
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
        // Register custom roles on plugin activation
        register_activation_hook(WDTA_MEMBERSHIP_PLUGIN_FILE, array($this, 'add_custom_roles'));
        
        // Schedule daily role update check
        add_action('wdta_daily_role_check', array($this, 'update_all_user_roles'));
        
        // Hook into existing activation events
        add_action('wdta_membership_activated', array($this, 'update_user_role'), 10, 2);
        
        // Add login redirect based on role
        add_filter('login_redirect', array($this, 'custom_login_redirect'), 10, 3);
    }
    
    /**
     * Add custom roles
     */
    public function add_custom_roles() {
        // Add Active Member role
        add_role(
            'active_member',
            'Active Member',
            array(
                'read' => true,
            )
        );
        
        // Add Grace Period Member role
        add_role(
            'grace_period_member',
            'Grace Period Member',
            array(
                'read' => true,
            )
        );
        
        // Add Inactive Member role
        add_role(
            'inactive_member',
            'Inactive Member',
            array(
                'read' => true,
            )
        );
    }
    
    /**
     * Update user role based on membership status
     */
    public function update_user_role($user_id, $year = null) {
        if ($year === null) {
            $year = date('Y');
        }
        
        $user = get_userdata($user_id);
        if (!$user) {
            return;
        }
        
        // Don't change admin roles
        if (in_array('administrator', $user->roles)) {
            return;
        }
        
        // Get membership status
        $role = $this->determine_membership_role($user_id, $year);
        
        // Update user role
        $user->set_role($role);
    }
    
    /**
     * Determine what role a user should have based on their membership
     */
    private function determine_membership_role($user_id, $year) {
        $membership = WDTA_Database::get_user_membership($user_id, $year);
        
        // No membership record for current year - check if they had one previously
        if (!$membership) {
            // Check if they have a grace_period membership from previous year
            // This happens on Jan 1 when unpaid members are moved to grace_period
            // but they don't yet have a current year membership record
            $previous_year = $year - 1;
            $previous_membership = WDTA_Database::get_user_membership($user_id, $previous_year);
            
            if ($previous_membership && $previous_membership->status === 'grace_period') {
                // User has grace_period status from last year, so they should be grace_period_member
                // This gives them continued access until April 1st
                return 'grace_period_member';
            }
            
            // Check if user has any membership record
            $has_any_membership = $this->user_has_any_membership($user_id);
            if ($has_any_membership) {
                return 'inactive_member';
            }
            // New user, keep subscriber role or set to inactive
            return 'inactive_member';
        }
        
        // Has membership record - determine status based on payment and status
        
        // Active: payment completed and status is active
        if ($membership->payment_status === 'completed' && $membership->status === 'active') {
            return 'active_member';
        }
        
        // Grace period: status is grace_period (unpaid after Jan 1, before Apr 1)
        if ($membership->status === 'grace_period') {
            return 'grace_period_member';
        }
        
        // Any other status (pending, inactive, etc.)
        return 'inactive_member';
    }
    
    /**
     * Check if user has any membership record in database
     */
    private function user_has_any_membership($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE user_id = %d",
            $user_id
        ));
        
        return $count > 0;
    }
    
    /**
     * Update all user roles (run daily via cron)
     */
    public function update_all_user_roles() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        // Get all users with memberships
        $user_ids = $wpdb->get_col(
            "SELECT DISTINCT user_id FROM $table_name"
        );
        
        $current_year = date('Y');
        
        foreach ($user_ids as $user_id) {
            $this->update_user_role($user_id, $current_year);
        }
    }
    
    /**
     * Get role display name
     */
    public static function get_role_display_name($role) {
        $roles = array(
            'active_member' => 'Active Member',
            'inactive_member' => 'Inactive',
            'subscriber' => 'Subscriber',
            'administrator' => 'Administrator',
        );
        
        return isset($roles[$role]) ? $roles[$role] : ucfirst($role);
    }
    
    /**
     * Custom login redirect based on user role
     */
    public function custom_login_redirect($redirect_to, $request, $user) {
        // Check if user is valid
        if (!isset($user->roles) || !is_array($user->roles)) {
            return $redirect_to;
        }
        
        // Don't redirect administrators
        if (in_array('administrator', $user->roles)) {
            return $redirect_to;
        }
        
        // Get the first role of the user
        $user_role = $user->roles[0];
        
        // Get redirect setting for this role
        $redirect_setting = get_option('wdta_login_redirect_' . $user_role, '');
        
        // If no redirect is set, use default
        if (empty($redirect_setting)) {
            return $redirect_to;
        }
        
        // Handle special 'home' redirect
        if ($redirect_setting === 'home') {
            return home_url('/');
        }
        
        // Handle page ID redirect
        if (is_numeric($redirect_setting)) {
            $page_url = get_permalink(intval($redirect_setting));
            if ($page_url) {
                return $page_url;
            }
        }
        
        // Default fallback
        return $redirect_to;
    }
}
