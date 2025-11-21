<?php
/**
 * Database handler class
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDTA_Database {
    
    /**
     * Table name for memberships
     */
    private static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'wdta_memberships';
    }
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            membership_year int(4) NOT NULL,
            payment_amount decimal(10,2) NOT NULL DEFAULT 950.00,
            payment_method varchar(50) NOT NULL,
            payment_status varchar(50) NOT NULL DEFAULT 'pending',
            payment_date datetime DEFAULT NULL,
            payment_reference varchar(255) DEFAULT NULL,
            stripe_payment_id varchar(255) DEFAULT NULL,
            expiry_date date NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'pending',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            notes text DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY membership_year (membership_year),
            KEY status (status),
            KEY expiry_date (expiry_date),
            UNIQUE KEY user_year (user_id, membership_year)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Get user's current membership
     */
    public static function get_user_membership($user_id, $year = null) {
        global $wpdb;
        
        if ($year === null) {
            $year = date('Y');
        }
        
        $table_name = self::get_table_name();
        
        $membership = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND membership_year = %d",
            $user_id,
            $year
        ));
        
        return $membership;
    }
    
    /**
     * Check if user has active membership
     */
    public static function has_active_membership($user_id, $year = null) {
        if ($year === null) {
            $year = date('Y');
        }
        
        $membership = self::get_user_membership($user_id, $year);
        
        if (!$membership) {
            return false;
        }
        
        // Check if payment is complete and not expired
        if ($membership->payment_status === 'completed' && $membership->status === 'active') {
            $expiry = strtotime($membership->expiry_date);
            $today = strtotime(date('Y-m-d'));
            return $expiry >= $today;
        }
        
        return false;
    }
    
    /**
     * Create or update membership
     */
    public static function save_membership($data) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        // Check if membership already exists
        $existing = self::get_user_membership($data['user_id'], $data['membership_year']);
        
        $defaults = array(
            'payment_amount' => 950.00,
            'payment_status' => 'pending',
            'status' => 'pending',
        );
        
        $data = wp_parse_args($data, $defaults);
        
        if ($existing) {
            // Update existing membership
            $wpdb->update(
                $table_name,
                $data,
                array(
                    'user_id' => $data['user_id'],
                    'membership_year' => $data['membership_year']
                )
            );
            return $existing->id;
        } else {
            // Insert new membership
            $wpdb->insert($table_name, $data);
            return $wpdb->insert_id;
        }
    }
    
    /**
     * Get all memberships for a year
     */
    public static function get_memberships_by_year($year, $status = null) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        if ($status) {
            $memberships = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE membership_year = %d AND status = %s ORDER BY created_at DESC",
                $year,
                $status
            ));
        } else {
            $memberships = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE membership_year = %d ORDER BY created_at DESC",
                $year
            ));
        }
        
        return $memberships;
    }
    
    /**
     * Get memberships expiring soon
     */
    public static function get_expiring_memberships($days_until_expiry) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        $target_date = date('Y-m-d', strtotime("+$days_until_expiry days"));
        
        $memberships = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE expiry_date = %s AND status = 'active'",
            $target_date
        ));
        
        return $memberships;
    }
    
    /**
     * Get users without membership for current year
     */
    public static function get_users_without_membership($year = null) {
        global $wpdb;
        
        if ($year === null) {
            $year = date('Y');
        }
        
        $table_name = self::get_table_name();
        
        // Get all users who had membership in previous year but not current year
        $users = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT u.ID, u.user_email, u.user_login, u.display_name
            FROM {$wpdb->users} u
            INNER JOIN {$table_name} m ON u.ID = m.user_id
            WHERE m.membership_year = %d
            AND m.status = 'active'
            AND u.ID NOT IN (
                SELECT user_id FROM {$table_name} WHERE membership_year = %d
            )
        ", $year - 1, $year));
        
        return $users;
    }
}
