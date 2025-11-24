<?php
/**
 * Stripe payment handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class WDTA_Payment_Stripe {
    
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
        add_action('wp_ajax_wdta_create_stripe_session', array($this, 'create_checkout_session'));
        add_action('wp_ajax_nopriv_wdta_create_stripe_session', array($this, 'create_checkout_session'));
        add_action('wp_ajax_wdta_create_payment_intent', array($this, 'create_payment_intent'));
        add_action('wp_ajax_nopriv_wdta_create_payment_intent', array($this, 'create_payment_intent'));
        add_action('wp_ajax_nopriv_wdta_register_and_create_payment_intent', array($this, 'register_and_create_payment_intent'));
        add_action('rest_api_init', array($this, 'register_webhook_endpoint'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_stripe_scripts'));
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wdta_membership_settings', 'wdta_stripe_public_key');
        register_setting('wdta_membership_settings', 'wdta_stripe_secret_key');
        register_setting('wdta_membership_settings', 'wdta_stripe_webhook_secret');
    }
    
    /**
     * Get Stripe API instance
     */
    private function get_stripe_api() {
        $secret_key = get_option('wdta_stripe_secret_key');
        
        if (!$secret_key) {
            return null;
        }
        
        // Initialize Stripe (this would use Stripe PHP SDK in production)
        // For now, we'll use wp_remote_post for API calls
        return $secret_key;
    }
    
    /**
     * Create Stripe checkout session
     */
    public function create_checkout_session() {
        check_ajax_referer('wdta_membership_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in'));
            return;
        }
        
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        
        $secret_key = get_option('wdta_stripe_secret_key');
        
        if (!$secret_key) {
            wp_send_json_error(array('message' => 'Stripe not configured'));
            return;
        }
        
        // Create pending membership record
        $expiry_date = $year . '-12-31';
        WDTA_Database::save_membership(array(
            'user_id' => $user_id,
            'membership_year' => $year,
            'payment_amount' => 970.90, // $950 + 2.2% surcharge
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'expiry_date' => $expiry_date,
            'status' => 'pending'
        ));
        
        // In production, this would create a Stripe checkout session with metadata
        // Example Stripe API call (requires Stripe PHP SDK):
        // $session = \Stripe\Checkout\Session::create([
        //     'payment_method_types' => ['card'],
        //     'line_items' => [[
        //         'price_data' => [
        //             'currency' => 'aud',
        //             'product_data' => ['name' => 'WDTA Membership ' . $year],
        //             'unit_amount' => 95000, // $950.00 in cents
        //         ],
        //         'quantity' => 1,
        //     ]],
        //     'mode' => 'payment',
        //     'success_url' => home_url('/membership-success/'),
        //     'cancel_url' => home_url('/membership/'),
        //     'metadata' => [
        //         'user_id' => $user_id,
        //         'year' => $year,
        //     ],
        // ]);
        
        // For now, return mock session data
        $session_data = array(
            'id' => 'cs_test_' . uniqid(),
            'url' => home_url('/stripe-checkout/?session=' . uniqid()),
            'metadata' => array(
                'user_id' => $user_id,
                'year' => $year
            )
        );
        
        wp_send_json_success(array(
            'sessionId' => $session_data['id'],
            'url' => $session_data['url']
        ));
    }
    
    /**
     * Register webhook endpoint
     */
    public function register_webhook_endpoint() {
        register_rest_route('wdta/v1', '/stripe-webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => '__return_true'
        ));
    }
    
    /**
     * Handle Stripe webhook
     */
    public function handle_webhook($request) {
        $payload = $request->get_body();
        $sig_header = $request->get_header('stripe-signature');
        $webhook_secret = get_option('wdta_stripe_webhook_secret');
        
        // Verify webhook signature (simplified for this implementation)
        // In production, use Stripe's webhook signature verification
        
        $event = json_decode($payload, true);
        
        if (!$event || !isset($event['type'])) {
            return new WP_Error('invalid_payload', 'Invalid webhook payload', array('status' => 400));
        }
        
        // Handle the event
        switch ($event['type']) {
            case 'checkout.session.completed':
                $this->handle_successful_payment($event['data']['object']);
                break;
            case 'payment_intent.succeeded':
                $this->handle_payment_intent_succeeded($event['data']['object']);
                break;
            default:
                // Unhandled event type
                break;
        }
        
        return new WP_REST_Response(array('received' => true), 200);
    }
    
    /**
     * Handle successful payment
     */
    private function handle_successful_payment($session) {
        // Extract user_id and year from session metadata
        $user_id = isset($session['metadata']['user_id']) ? intval($session['metadata']['user_id']) : 0;
        $year = isset($session['metadata']['year']) ? intval($session['metadata']['year']) : date('Y');
        
        if (!$user_id) {
            return;
        }
        
        // Update membership record
        WDTA_Database::save_membership(array(
            'user_id' => $user_id,
            'membership_year' => $year,
            'payment_status' => 'completed',
            'payment_date' => current_time('mysql'),
            'stripe_payment_id' => $session['payment_intent'],
            'status' => 'active'
        ));
        
        // Update user role
        do_action('wdta_membership_activated', $user_id, $year);
        
        // Send confirmation email
        $this->send_payment_confirmation($user_id, $year);
    }
    
    /**
     * Handle payment intent succeeded
     */
    private function handle_payment_intent_succeeded($payment_intent) {
        // Extract user_id and year from payment intent metadata
        $user_id = isset($payment_intent['metadata']['user_id']) ? intval($payment_intent['metadata']['user_id']) : 0;
        $year = isset($payment_intent['metadata']['year']) ? intval($payment_intent['metadata']['year']) : date('Y');
        
        if (!$user_id) {
            error_log('WDTA Payment: No user_id in payment intent metadata');
            return;
        }
        
        // Update membership record
        WDTA_Database::save_membership(array(
            'user_id' => $user_id,
            'membership_year' => $year,
            'payment_status' => 'completed',
            'payment_date' => current_time('mysql'),
            'stripe_payment_id' => $payment_intent['id'],
            'status' => 'active'
        ));
        
        // Update user role
        do_action('wdta_membership_activated', $user_id, $year);
        
        // Send confirmation email
        $this->send_payment_confirmation($user_id, $year);
    }
    
    /**
     * Send payment confirmation email
     */
    private function send_payment_confirmation($user_id, $year) {
        $user = get_userdata($user_id);
        $to = $user->user_email;
        
        // Get customizable template
        $subject = get_option('wdta_email_stripe_confirmation_subject', 'WDTA Membership Payment Confirmed - {year}');
        $template = get_option('wdta_email_stripe_confirmation_body', 
'Dear {user_name},

Thank you for your WDTA membership payment for {year}.

Payment Details:
Membership fee: $950.00 AUD
Card processing fee (2.2%): $20.90 AUD
Total paid: $970.90 AUD

Your membership is now active and will remain valid until December 31, {year}.

Best regards,
WDTA Team');
        
        // Replace placeholders
        $replacements = array(
            '{user_name}' => $user->display_name,
            '{user_email}' => $user->user_email,
            '{year}' => $year,
            '{site_name}' => get_bloginfo('name')
        );
        
        $subject = str_replace(array_keys($replacements), array_values($replacements), $subject);
        $message = str_replace(array_keys($replacements), array_values($replacements), $template);
        
        // Prepare headers with CC
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // Add CC recipients for signup emails
        $cc_recipients = get_option('wdta_signup_email_cc', 'marketing@wdta.org.au, treasurer@wdta.org.au');
        if (!empty($cc_recipients)) {
            $cc_emails = array_map('trim', explode(',', $cc_recipients));
            foreach ($cc_emails as $cc_email) {
                if (is_email($cc_email)) {
                    $headers[] = 'Cc: ' . $cc_email;
                }
            }
        }
        
        wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Enqueue Stripe scripts
     */
    public function enqueue_stripe_scripts() {
        $public_key = get_option('wdta_stripe_public_key');
        
        if (!$public_key) {
            return;
        }
        
        // Enqueue Stripe.js
        wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', array(), null, false);
        
        // Pass Stripe public key to frontend
        wp_localize_script('stripe-js', 'wdtaStripe', array(
            'publicKey' => $public_key,
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }
    
    /**
     * Create Payment Intent for inline payment form
     */
    public function create_payment_intent() {
        check_ajax_referer('wdta_membership_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in'));
            return;
        }
        
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        
        $secret_key = get_option('wdta_stripe_secret_key');
        
        if (!$secret_key) {
            wp_send_json_error(array('message' => 'Stripe not configured'));
            return;
        }
        
        // Create pending membership record
        $expiry_date = $year . '-12-31';
        WDTA_Database::save_membership(array(
            'user_id' => $user_id,
            'membership_year' => $year,
            'payment_amount' => 970.90, // $950 + 2.2% surcharge
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'expiry_date' => $expiry_date,
            'status' => 'pending'
        ));
        
        // Create PaymentIntent using Stripe API
        $response = wp_remote_post('https://api.stripe.com/v1/payment_intents', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body' => array(
                'amount' => 97090, // $970.90 in cents (includes 2.2% surcharge)
                'currency' => 'aud',
                'description' => 'WDTA Membership ' . $year,
                'metadata[user_id]' => $user_id,
                'metadata[year]' => $year,
                'metadata[user_email]' => $user->user_email,
                'metadata[user_name]' => $user->display_name,
            ),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'Failed to connect to payment processor: ' . $response->get_error_message()));
            return;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code !== 200) {
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Payment processor error';
            wp_send_json_error(array('message' => $error_message));
            return;
        }
        
        if (!isset($body['client_secret'])) {
            wp_send_json_error(array('message' => 'Invalid payment processor response'));
            return;
        }
        
        wp_send_json_success(array(
            'clientSecret' => $body['client_secret'],
            'amount' => 970.90,
            'currency' => 'AUD'
        ));
    }
    
    /**
     * Register new user and create payment intent
     */
    public function register_and_create_payment_intent() {
        check_ajax_referer('wdta_membership_nonce', 'nonce');
        
        // Get and validate registration data
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');
        
        // Validate required fields
        if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
            wp_send_json_error(array('message' => 'All fields are required'));
            return;
        }
        
        // Validate email format
        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Invalid email address'));
            return;
        }
        
        // Validate password strength
        if (strlen($password) < 8) {
            wp_send_json_error(array('message' => 'Password must be at least 8 characters'));
            return;
        }
        
        // Check if email already exists
        if (email_exists($email)) {
            wp_send_json_error(array('message' => 'This email is already registered. Please log in instead.'));
            return;
        }
        
        // Create the user account
        $username = $email; // Use email as username
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => 'Could not create account: ' . $user_id->get_error_message()));
            return;
        }
        
        // Update user meta with first and last name
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $first_name . ' ' . $last_name
        ));
        
        // Log the user in
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        
        // Create membership record
        $expiry_date = $year . '-12-31';
        WDTA_Database::save_membership(array(
            'user_id' => $user_id,
            'membership_year' => $year,
            'payment_amount' => 970.90,
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'expiry_date' => $expiry_date,
            'status' => 'pending'
        ));
        
        $secret_key = get_option('wdta_stripe_secret_key');
        
        if (!$secret_key) {
            wp_send_json_error(array('message' => 'Stripe not configured'));
            return;
        }
        
        // Get user data for payment intent
        $user = get_userdata($user_id);
        
        // Create PaymentIntent using Stripe API
        $response = wp_remote_post('https://api.stripe.com/v1/payment_intents', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body' => array(
                'amount' => 97090, // $970.90 in cents (includes 2.2% surcharge)
                'currency' => 'aud',
                'description' => 'WDTA Membership ' . $year,
                'metadata[user_id]' => $user_id,
                'metadata[year]' => $year,
                'metadata[user_email]' => $user->user_email,
                'metadata[user_name]' => $user->display_name,
            ),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => 'Failed to connect to payment processor: ' . $response->get_error_message()));
            return;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code !== 200) {
            $error_message = isset($body['error']['message']) ? $body['error']['message'] : 'Payment processor error';
            wp_send_json_error(array('message' => $error_message));
            return;
        }
        
        if (!isset($body['client_secret'])) {
            wp_send_json_error(array('message' => 'Invalid payment processor response'));
            return;
        }
        
        wp_send_json_success(array(
            'clientSecret' => $body['client_secret'],
            'amount' => 970.90,
            'currency' => 'AUD',
            'user_id' => $user_id
        ));
    }
}
