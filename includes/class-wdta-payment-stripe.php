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
        $expiry_date = $year . '-03-31';
        WDTA_Database::save_membership(array(
            'user_id' => $user_id,
            'membership_year' => $year,
            'payment_amount' => 950.00,
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
        
        // Send confirmation email
        $this->send_payment_confirmation($user_id, $year);
    }
    
    /**
     * Handle payment intent succeeded
     */
    private function handle_payment_intent_succeeded($payment_intent) {
        // Additional handling if needed
    }
    
    /**
     * Send payment confirmation email
     */
    private function send_payment_confirmation($user_id, $year) {
        $user = get_userdata($user_id);
        $to = $user->user_email;
        $subject = 'WDTA Membership Payment Confirmed - ' . $year;
        $message = "Dear {$user->display_name},\n\n";
        $message .= "Thank you for your WDTA membership payment of \$950 AUD for {$year}.\n\n";
        $message .= "Your membership is now active and will remain valid until March 31, {$year}.\n\n";
        $message .= "Best regards,\nWDTA Team";
        
        wp_mail($to, $subject, $message);
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
        $expiry_date = $year . '-03-31';
        WDTA_Database::save_membership(array(
            'user_id' => $user_id,
            'membership_year' => $year,
            'payment_amount' => 950.00,
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'expiry_date' => $expiry_date,
            'status' => 'pending'
        ));
        
        // In production, this would create a Stripe PaymentIntent with Stripe PHP SDK:
        // \Stripe\Stripe::setApiKey($secret_key);
        // $paymentIntent = \Stripe\PaymentIntent::create([
        //     'amount' => 95000, // $950.00 in cents
        //     'currency' => 'aud',
        //     'description' => 'WDTA Membership ' . $year,
        //     'metadata' => [
        //         'user_id' => $user_id,
        //         'year' => $year,
        //         'user_email' => $user->user_email,
        //         'user_name' => $user->display_name,
        //     ],
        // ]);
        
        // For now, return mock payment intent data
        $client_secret = 'pi_test_' . uniqid() . '_secret_' . uniqid();
        
        wp_send_json_success(array(
            'clientSecret' => $client_secret,
            'amount' => 950.00,
            'currency' => 'AUD'
        ));
    }
}
