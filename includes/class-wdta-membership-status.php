<?php
/**
 * Membership status management
 */

class WDTA_Membership_Status {
    
    /**
     * Initialize status hooks
     */
    public static function init() {
        // Hook to check membership status
        add_action('init', array(__CLASS__, 'check_current_status'));
    }
    
    /**
     * Check if user has active membership for a year
     */
    public static function is_member_active($user_id, $year = null) {
        if ($year === null) {
            $year = date('Y');
        }
        
        $membership = WDTA_Membership_Database::get_membership($user_id, $year);
        
        return $membership && $membership->status === 'active';
    }
    
    /**
     * Activate membership for a user
     */
    public static function activate_membership($user_id, $year, $payment_data = array()) {
        $data = array(
            'status' => 'active',
            'payment_date' => current_time('mysql'),
            'payment_amount' => isset($payment_data['amount']) ? $payment_data['amount'] : null,
            'payment_method' => isset($payment_data['method']) ? $payment_data['method'] : null,
            'transaction_id' => isset($payment_data['transaction_id']) ? $payment_data['transaction_id'] : null,
        );
        
        return WDTA_Membership_Database::update_membership($user_id, $year, $data);
    }
    
    /**
     * Deactivate membership for a user
     */
    public static function deactivate_membership($user_id, $year) {
        $data = array(
            'status' => 'inactive'
        );
        
        return WDTA_Membership_Database::update_membership($user_id, $year, $data);
    }
    
    /**
     * Check and update membership status based on current date
     */
    public static function check_current_status() {
        // On January 1st, deactivate all memberships for the previous year that weren't paid
        $current_date = current_time('Y-m-d');
        $current_year = (int) date('Y');
        $previous_year = $current_year - 1;
        
        // Check if it's January 1st
        if (date('m-d') === '01-01') {
            // Set all unpaid previous year memberships to inactive
            WDTA_Membership_Database::set_unpaid_to_inactive($previous_year);
        }
    }
    
    /**
     * Get current membership year for payment
     */
    public static function get_current_year() {
        return (int) date('Y');
    }
    
    /**
     * Get next membership year for renewal
     */
    public static function get_next_year() {
        return (int) date('Y') + 1;
    }
}
