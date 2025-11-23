<?php
/**
 * Shortcode management class
 */

class WDTA_Membership_Shortcodes {
    
    /**
     * Initialize shortcodes
     */
    public static function init() {
        add_shortcode('wdta_membership_form', array(__CLASS__, 'membership_form_shortcode'));
        add_shortcode('wdta_membership_renewal_form', array(__CLASS__, 'renewal_form_shortcode'));
        
        // AJAX handlers
        add_action('wp_ajax_wdta_register_and_pay', array(__CLASS__, 'ajax_register_and_pay'));
        add_action('wp_ajax_nopriv_wdta_register_and_pay', array(__CLASS__, 'ajax_register_and_pay'));
        add_action('wp_ajax_wdta_renew_membership', array(__CLASS__, 'ajax_renew_membership'));
    }
    
    /**
     * Membership form shortcode for non-logged in users
     * Creates account and pays for current year
     */
    public static function membership_form_shortcode($atts) {
        // If user is already logged in, show a message
        if (is_user_logged_in()) {
            return '<div class="wdta-membership-notice">You are already logged in. To renew your membership for next year, please visit the renewal page.</div>';
        }
        
        $current_year = WDTA_Membership_Status::get_current_year();
        $price = get_option('wdta_membership_current_year_price', '50.00');
        
        ob_start();
        ?>
        <div class="wdta-membership-form-container">
            <h3>Join WDTA - <?php echo esc_html($current_year); ?> Membership</h3>
            <p>Create your account and pay for your <?php echo esc_html($current_year); ?> membership.</p>
            
            <form id="wdta-membership-form" class="wdta-form">
                <div class="wdta-form-row">
                    <label for="wdta_username">Username *</label>
                    <input type="text" id="wdta_username" name="username" required />
                </div>
                
                <div class="wdta-form-row">
                    <label for="wdta_email">Email Address *</label>
                    <input type="email" id="wdta_email" name="email" required />
                </div>
                
                <div class="wdta-form-row">
                    <label for="wdta_password">Password *</label>
                    <input type="password" id="wdta_password" name="password" required />
                </div>
                
                <div class="wdta-form-row">
                    <label for="wdta_first_name">First Name *</label>
                    <input type="text" id="wdta_first_name" name="first_name" required />
                </div>
                
                <div class="wdta-form-row">
                    <label for="wdta_last_name">Last Name *</label>
                    <input type="text" id="wdta_last_name" name="last_name" required />
                </div>
                
                <div class="wdta-membership-details">
                    <h4>Membership Details</h4>
                    <p><strong>Year:</strong> <?php echo esc_html($current_year); ?></p>
                    <p><strong>Price:</strong> $<?php echo esc_html(number_format($price, 2)); ?></p>
                </div>
                
                <div class="wdta-payment-section">
                    <h4>Payment Information</h4>
                    <p class="wdta-payment-note">After submitting this form, you will be redirected to complete payment.</p>
                    
                    <div class="wdta-form-row">
                        <label>
                            <input type="checkbox" name="terms" id="wdta_terms" required />
                            I agree to the terms and conditions
                        </label>
                    </div>
                </div>
                
                <input type="hidden" name="membership_year" value="<?php echo esc_attr($current_year); ?>" />
                <input type="hidden" name="amount" value="<?php echo esc_attr($price); ?>" />
                <input type="hidden" name="action" value="wdta_register_and_pay" />
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wdta_membership_nonce'); ?>" />
                
                <div class="wdta-form-row">
                    <button type="submit" class="wdta-submit-button">Create Account & Pay</button>
                </div>
                
                <div class="wdta-form-message" style="display:none;"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Renewal form shortcode for logged-in users
     * Pays for next year
     */
    public static function renewal_form_shortcode($atts) {
        // Must be logged in
        if (!is_user_logged_in()) {
            return '<div class="wdta-membership-notice">Please <a href="' . wp_login_url(get_permalink()) . '">log in</a> to renew your membership.</div>';
        }
        
        $user_id = get_current_user_id();
        $next_year = WDTA_Membership_Status::get_next_year();
        $current_year = WDTA_Membership_Status::get_current_year();
        $price = get_option('wdta_membership_next_year_price', '50.00');
        
        // Check if already paid for next year
        if (WDTA_Membership_Status::is_member_active($user_id, $next_year)) {
            return '<div class="wdta-membership-notice wdta-success">Your membership for ' . esc_html($next_year) . ' is already active. Thank you!</div>';
        }
        
        $user = wp_get_current_user();
        
        ob_start();
        ?>
        <div class="wdta-membership-renewal-container">
            <h3>Renew WDTA Membership - <?php echo esc_html($next_year); ?></h3>
            <p>Hello <strong><?php echo esc_html($user->display_name); ?></strong>, renew your membership for <?php echo esc_html($next_year); ?>.</p>
            
            <form id="wdta-renewal-form" class="wdta-form">
                <div class="wdta-membership-details">
                    <h4>Renewal Details</h4>
                    <p><strong>Current Year Status:</strong> 
                        <?php 
                        if (WDTA_Membership_Status::is_member_active($user_id, $current_year)) {
                            echo '<span class="wdta-status-active">Active</span>';
                        } else {
                            echo '<span class="wdta-status-inactive">Inactive</span>';
                        }
                        ?>
                    </p>
                    <p><strong>Renewal Year:</strong> <?php echo esc_html($next_year); ?></p>
                    <p><strong>Price:</strong> $<?php echo esc_html(number_format($price, 2)); ?></p>
                </div>
                
                <div class="wdta-payment-section">
                    <h4>Payment Information</h4>
                    <p class="wdta-payment-note">Click the button below to proceed with payment for your <?php echo esc_html($next_year); ?> membership.</p>
                    
                    <div class="wdta-form-row">
                        <label>
                            <input type="checkbox" name="terms" id="wdta_renewal_terms" required />
                            I agree to the terms and conditions
                        </label>
                    </div>
                </div>
                
                <input type="hidden" name="membership_year" value="<?php echo esc_attr($next_year); ?>" />
                <input type="hidden" name="amount" value="<?php echo esc_attr($price); ?>" />
                <input type="hidden" name="action" value="wdta_renew_membership" />
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wdta_membership_nonce'); ?>" />
                
                <div class="wdta-form-row">
                    <button type="submit" class="wdta-submit-button">Renew Membership</button>
                </div>
                
                <div class="wdta-form-message" style="display:none;"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * AJAX handler for registration and payment
     */
    public static function ajax_register_and_pay() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wdta_membership_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }
        
        // Validate input
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password']; // Don't sanitize passwords, but validate
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $year = intval($_POST['membership_year']);
        $amount = floatval($_POST['amount']);
        
        // Validate required fields
        if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
            wp_send_json_error(array('message' => 'All fields are required'));
        }
        
        // Validate password strength (minimum 8 characters)
        if (strlen($password) < 8) {
            wp_send_json_error(array('message' => 'Password must be at least 8 characters long'));
        }
        
        // Create user
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => $user_id->get_error_message()));
        }
        
        // Update user meta
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name
        ));
        
        // In a real implementation, you would integrate with a payment gateway here
        // For now, we'll mark the membership as active (manual payment)
        $payment_data = array(
            'amount' => $amount,
            'method' => 'manual',
            'transaction_id' => 'MANUAL-' . time()
        );
        
        WDTA_Membership_Status::activate_membership($user_id, $year, $payment_data);
        
        // Send confirmation email
        WDTA_Membership_Email::send_payment_confirmation($user_id, $year, $amount);
        
        // Log the user in
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        
        wp_send_json_success(array(
            'message' => 'Account created and membership activated!',
            'redirect' => home_url('/membership-confirmation/')
        ));
    }
    
    /**
     * AJAX handler for membership renewal
     */
    public static function ajax_renew_membership() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wdta_membership_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }
        
        // Must be logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in'));
        }
        
        $user_id = get_current_user_id();
        $year = intval($_POST['membership_year']);
        $amount = floatval($_POST['amount']);
        
        // Check if already active
        if (WDTA_Membership_Status::is_member_active($user_id, $year)) {
            wp_send_json_error(array('message' => 'Membership already active for this year'));
        }
        
        // In a real implementation, you would integrate with a payment gateway here
        // For now, we'll mark the membership as active (manual payment)
        $payment_data = array(
            'amount' => $amount,
            'method' => 'manual',
            'transaction_id' => 'MANUAL-' . time()
        );
        
        WDTA_Membership_Status::activate_membership($user_id, $year, $payment_data);
        
        // Send confirmation email
        WDTA_Membership_Email::send_payment_confirmation($user_id, $year, $amount);
        
        wp_send_json_success(array(
            'message' => 'Membership renewed successfully!',
            'redirect' => home_url('/membership-confirmation/')
        ));
    }
}
