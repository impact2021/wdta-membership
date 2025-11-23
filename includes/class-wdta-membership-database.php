<?php
/**
 * Database management class
 */

class WDTA_Membership_Database {
    
    /**
     * Initialize database hooks
     */
    public static function init() {
        // Nothing needed here for now
    }
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            membership_year int(4) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'inactive',
            payment_date datetime DEFAULT NULL,
            payment_amount decimal(10,2) DEFAULT NULL,
            payment_method varchar(50) DEFAULT NULL,
            transaction_id varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY membership_year (membership_year),
            KEY status (status),
            UNIQUE KEY user_year (user_id, membership_year)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Get membership record
     */
    public static function get_membership($user_id, $year) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND membership_year = %d",
            $user_id,
            $year
        ));
    }
    
    /**
     * Create or update membership
     */
    public static function update_membership($user_id, $year, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        $existing = self::get_membership($user_id, $year);
        
        $defaults = array(
            'user_id' => $user_id,
            'membership_year' => $year,
            'status' => 'inactive'
        );
        
        $data = wp_parse_args($data, $defaults);
        
        if ($existing) {
            unset($data['user_id']);
            unset($data['membership_year']);
            
            // Build format array dynamically based on data
            $formats = array();
            foreach ($data as $key => $value) {
                if (in_array($key, array('payment_amount'))) {
                    $formats[] = '%f';
                } else {
                    $formats[] = '%s';
                }
            }
            
            return $wpdb->update(
                $table_name,
                $data,
                array(
                    'user_id' => $user_id,
                    'membership_year' => $year
                ),
                $formats,
                array('%d', '%d')
            );
        } else {
            // Build format array dynamically based on data
            $formats = array();
            foreach ($data as $key => $value) {
                if ($key === 'user_id' || $key === 'membership_year') {
                    $formats[] = '%d';
                } elseif ($key === 'payment_amount') {
                    $formats[] = '%f';
                } else {
                    $formats[] = '%s';
                }
            }
            
            return $wpdb->insert(
                $table_name,
                $data,
                $formats
            );
        }
    }
    
    /**
     * Get all memberships for a user
     */
    public static function get_user_memberships($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY membership_year DESC",
            $user_id
        ));
    }
    
    /**
     * Get all inactive users for a specific year
     */
    public static function get_inactive_users($year) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        $sql = $wpdb->prepare(
            "SELECT u.ID, u.user_login, u.user_email, u.display_name, m.status, m.membership_year
            FROM {$wpdb->users} u
            LEFT JOIN $table_name m ON u.ID = m.user_id AND m.membership_year = %d
            WHERE m.status IS NULL OR m.status = 'inactive'
            ORDER BY u.display_name ASC",
            $year
        );
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get users who need reminders
     */
    public static function get_users_for_reminder($year) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        // Get all users who don't have an active membership for the specified year
        $sql = $wpdb->prepare(
            "SELECT DISTINCT u.ID, u.user_email, u.display_name
            FROM {$wpdb->users} u
            LEFT JOIN $table_name m ON u.ID = m.user_id AND m.membership_year = %d
            WHERE m.status IS NULL OR m.status = 'inactive'",
            $year
        );
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Update all unpaid memberships to inactive
     */
    public static function set_unpaid_to_inactive($year) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wdta_memberships';
        
        return $wpdb->query($wpdb->prepare(
            "UPDATE $table_name SET status = 'inactive' 
            WHERE membership_year = %d AND status != 'active'",
            $year
        ));
    }
}
