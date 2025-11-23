<?php
/**
 * Membership form template (shortcode) - Version 2.1
 * Now works for non-logged-in users with registration
 * Current year membership only
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_year = date('Y');
$user_id = get_current_user_id();

// If user is logged in and has active membership, show status
if ($user_id) {
    $has_active = WDTA_Database::has_active_membership($user_id, $current_year);
    
    if ($has_active) {
        ?>
        <div class="wdta-membership-form">
            <div class="wdta-success-message">
                <h3>✓ Your membership is active</h3>
                <p>Your WDTA membership for <?php echo $current_year; ?> is active until December 31, <?php echo $current_year; ?>.</p>
                <?php
                $current_month = date('n');
                if ($current_month >= 11): // November onwards
                    ?>
                    <p>Looking to renew for next year? Use the renewal form available from November onwards.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return;
    }
    
    $membership = WDTA_Database::get_user_membership($user_id, $current_year);
    if ($membership && $membership->payment_status === 'pending_verification') {
        ?>
        <div class="wdta-membership-form">
            <div class="wdta-info-message">
                <h3>Payment Pending Verification</h3>
                <p>Your bank transfer payment is being verified. You will receive an email once your membership is activated.</p>
            </div>
        </div>
        <?php
        return;
    }
}
?>

<div class="wdta-membership-form">
    <h2>WDTA Membership - <?php echo $current_year; ?></h2>
    
    <div class="wdta-pricing-info">
        <p><strong>Annual membership fee: <?php echo wdta_get_membership_price(true); ?></strong></p>
        <p>Membership is valid until December 31, <?php echo $current_year; ?></p>
    </div>
    
    <?php if (!$user_id): ?>
        <div class="wdta-registration-section">
            <h3>Create Your Account</h3>
            <p>Please fill in your details to create an account and purchase membership:</p>
            
            <form id="wdta-registration-form">
                <p>
                    <label for="wdta_username">Username: *</label>
                    <input type="text" id="wdta_username" name="username" required 
                           placeholder="Choose a username">
                </p>
                <p>
                    <label for="wdta_email">Email Address: *</label>
                    <input type="email" id="wdta_email" name="email" required 
                           placeholder="your@email.com">
                </p>
                <p>
                    <label for="wdta_password">Password: *</label>
                    <input type="password" id="wdta_password" name="password" required 
                           placeholder="Choose a strong password">
                </p>
                <p>
                    <label for="wdta_confirm_password">Confirm Password: *</label>
                    <input type="password" id="wdta_confirm_password" name="confirm_password" required 
                           placeholder="Re-enter your password">
                </p>
                <input type="hidden" id="wdta_registration_user_id" value="">
            </form>
            
            <div id="wdta-registration-message"></div>
        </div>
    <?php endif; ?>
    
    <div class="wdta-payment-methods">
        <h3>Choose Payment Method:</h3>
        
        <div class="wdta-payment-option wdta-stripe-payment">
            <h4>Pay with Credit Card</h4>
            <p class="wdta-payment-description">Secure payment via Stripe</p>
            
            <div class="wdta-stripe-pricing">
                <?php 
                $membership_price = wdta_get_membership_price();
                $stripe_price = wdta_get_stripe_price();
                $surcharge = $stripe_price - $membership_price;
                ?>
                <div class="wdta-price-line">
                    <span>Membership fee:</span>
                    <span>$<?php echo number_format($membership_price, 2); ?> AUD</span>
                </div>
                <div class="wdta-price-line wdta-surcharge">
                    <span>Card processing fee (2.2%):</span>
                    <span>$<?php echo number_format($surcharge, 2); ?> AUD</span>
                </div>
                <div class="wdta-price-line wdta-total">
                    <span><strong>Total amount:</strong></span>
                    <span><strong>$<?php echo number_format($stripe_price, 2); ?> AUD</strong></span>
                </div>
            </div>
            
            <form id="wdta-stripe-payment-form">
                <div id="wdta-card-element" class="wdta-card-element">
                    <!-- Stripe Card Element will be inserted here -->
                </div>
                
                <div id="wdta-card-errors" class="wdta-error-message" role="alert"></div>
                
                <button id="wdta-stripe-submit" class="button button-primary" type="submit">
                    <span id="wdta-button-text">Pay $<?php echo number_format(wdta_get_stripe_price(), 2); ?> AUD</span>
                    <span id="wdta-spinner" class="wdta-spinner" style="display:none;"></span>
                </button>
            </form>
        </div>
        
        <div class="wdta-payment-divider">
            <span>OR</span>
        </div>
        
        <div class="wdta-payment-option">
            <h4>Pay via Bank Transfer</h4>
            <div class="wdta-bank-details">
                <p><strong>Bank Details:</strong></p>
                <ul>
                    <li><strong>Bank:</strong> <?php echo esc_html(get_option('wdta_bank_name', 'To be configured')); ?></li>
                    <li><strong>Account Name:</strong> <?php echo esc_html(get_option('wdta_bank_account_name', 'To be configured')); ?></li>
                    <li><strong>BSB:</strong> <?php echo esc_html(get_option('wdta_bank_bsb', 'To be configured')); ?></li>
                    <li><strong>Account Number:</strong> <?php echo esc_html(get_option('wdta_bank_account_number', 'To be configured')); ?></li>
                    <li><strong>Amount:</strong> <?php echo wdta_get_membership_price(true); ?></li>
                    <li><strong>Reference:</strong> Your name and "WDTA <?php echo $current_year; ?>"</li>
                </ul>
            </div>
            
            <div class="wdta-bank-form">
                <h5>After making your transfer, submit the details:</h5>
                <form id="wdta-bank-transfer-form">
                    <p>
                        <label for="wdta_reference">Payment Reference:</label>
                        <input type="text" id="wdta_reference" name="reference" required 
                               placeholder="e.g., John Smith WDTA <?php echo $current_year; ?>">
                    </p>
                    <p>
                        <label for="wdta_payment_date">Payment Date:</label>
                        <input type="date" id="wdta_payment_date" name="payment_date" required>
                    </p>
                    <p>
                        <button type="submit" class="button button-primary">Submit Bank Transfer Details</button>
                    </p>
                </form>
            </div>
        </div>
    </div>
    
    <div id="wdta-message"></div>
</div>

<script>
jQuery(document).ready(function($) {
    var isLoggedIn = <?php echo $user_id ? 'true' : 'false'; ?>;
    var registeredUserId = null;
    
    // Initialize Stripe Elements
    var stripe = null;
    var elements = null;
    var cardElement = null;
    var clientSecret = null;
    
    // Check if Stripe is available and public key is set
    if (typeof wdtaStripe !== 'undefined' && wdtaStripe.publicKey) {
        stripe = Stripe(wdtaStripe.publicKey);
        elements = stripe.elements();
        
        // Create card element
        var style = {
            base: {
                color: '#32325d',
                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };
        
        cardElement = elements.create('card', {style: style});
        cardElement.mount('#wdta-card-element');
        
        // Handle real-time validation errors
        cardElement.on('change', function(event) {
            var displayError = document.getElementById('wdta-card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
    }
    
    // Function to create payment intent
    function createPaymentIntent(userId) {
        return $.ajax({
            url: wdtaStripe.ajaxurl,
            type: 'POST',
            data: {
                action: 'wdta_create_payment_intent',
                nonce: '<?php echo wp_create_nonce('wdta_membership_nonce'); ?>',
                year: <?php echo $current_year; ?>,
                user_id: userId
            }
        });
    }
    
    // If logged in, create payment intent immediately
    if (isLoggedIn) {
        createPaymentIntent(<?php echo $user_id ? $user_id : 0; ?>).done(function(response) {
            if (response.success) {
                clientSecret = response.data.clientSecret;
            } else {
                $('#wdta-card-errors').text(response.data.message);
            }
        });
    }
    
    // Handle registration for non-logged-in users
    function registerUser() {
        var username = $('#wdta_username').val();
        var email = $('#wdta_email').val();
        var password = $('#wdta_password').val();
        var confirmPassword = $('#wdta_confirm_password').val();
        
        // Validate passwords match
        if (password !== confirmPassword) {
            $('#wdta-registration-message').html('<p class="error">Passwords do not match.</p>');
            return $.Deferred().reject();
        }
        
        return $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'wdta_register_user',
                nonce: '<?php echo wp_create_nonce('wdta_membership_nonce'); ?>',
                username: username,
                email: email,
                password: password
            }
        });
    }
    
    // Handle Stripe form submission
    $('#wdta-stripe-payment-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!stripe || !cardElement) {
            $('#wdta-card-errors').text('Payment system not properly initialized. Please refresh and try again.');
            return;
        }
        
        setLoading(true);
        
        // If not logged in, register user first
        if (!isLoggedIn && !registeredUserId) {
            registerUser().then(function(response) {
                if (response.success) {
                    registeredUserId = response.data.user_id;
                    $('#wdta_registration_user_id').val(registeredUserId);
                    $('#wdta-registration-message').html('<p class="success">✓ Account created! Processing payment...</p>');
                    
                    // Create payment intent with new user ID
                    return createPaymentIntent(registeredUserId);
                } else {
                    $('#wdta-registration-message').html('<p class="error">' + response.data.message + '</p>');
                    setLoading(false);
                    return $.Deferred().reject();
                }
            }).then(function(response) {
                if (response.success) {
                    clientSecret = response.data.clientSecret;
                    return processPayment();
                } else {
                    $('#wdta-card-errors').text(response.data.message);
                    setLoading(false);
                }
            });
        } else {
            processPayment();
        }
    });
    
    function processPayment() {
        if (!clientSecret) {
            $('#wdta-card-errors').text('Payment not initialized. Please try again.');
            setLoading(false);
            return;
        }
        
        var email = isLoggedIn ? '<?php echo esc_js(wp_get_current_user()->user_email); ?>' : $('#wdta_email').val();
        var name = isLoggedIn ? '<?php echo esc_js(wp_get_current_user()->display_name); ?>' : $('#wdta_username').val();
        
        // Confirm the payment with Stripe
        stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    email: email,
                    name: name
                }
            }
        }).then(function(result) {
            if (result.error) {
                // Show error to customer
                $('#wdta-card-errors').text(result.error.message);
                setLoading(false);
            } else {
                // Payment succeeded
                if (result.paymentIntent.status === 'succeeded') {
                    $('#wdta-message').html('<div class="wdta-success-message"><p>✓ Payment successful! Redirecting to your account...</p></div>');
                    setTimeout(function() {
                        window.location.href = '<?php echo esc_url(home_url('/my-account/')); ?>';
                    }, 2000);
                }
            }
        });
    }
    
    function setLoading(isLoading) {
        if (isLoading) {
            $('#wdta-stripe-submit').prop('disabled', true);
            $('#wdta-button-text').hide();
            $('#wdta-spinner').show();
        } else {
            $('#wdta-stripe-submit').prop('disabled', false);
            $('#wdta-button-text').show();
            $('#wdta-spinner').hide();
        }
    }
    
    // Bank transfer form
    $('#wdta-bank-transfer-form').on('submit', function(e) {
        e.preventDefault();
        
        // If not logged in, register user first
        if (!isLoggedIn && !registeredUserId) {
            registerUser().then(function(response) {
                if (response.success) {
                    registeredUserId = response.data.user_id;
                    $('#wdta_registration_user_id').val(registeredUserId);
                    $('#wdta-registration-message').html('<p class="success">✓ Account created! Submitting bank transfer details...</p>');
                    submitBankTransfer(registeredUserId);
                } else {
                    $('#wdta-registration-message').html('<p class="error">' + response.data.message + '</p>');
                }
            });
        } else {
            submitBankTransfer(isLoggedIn ? <?php echo $user_id ? $user_id : 0; ?> : registeredUserId);
        }
    });
    
    function submitBankTransfer(userId) {
        var formData = $('#wdta-bank-transfer-form').serialize();
        formData += '&action=wdta_submit_bank_transfer';
        formData += '&nonce=<?php echo wp_create_nonce('wdta_membership_nonce'); ?>';
        formData += '&year=<?php echo $current_year; ?>';
        formData += '&user_id=' + userId;
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#wdta-message').html('<p class="success">' + response.data.message + '</p>');
                    $('#wdta-bank-transfer-form')[0].reset();
                    setTimeout(function() {
                        window.location.href = '<?php echo esc_url(home_url('/my-account/')); ?>';
                    }, 2000);
                } else {
                    $('#wdta-message').html('<p class="error">' + response.data.message + '</p>');
                }
            }
        });
    }
});
</script>
