<?php
/**
 * Membership form template (shortcode)
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_year = date('Y');
$current_month = date('n'); // 1-12
$current_day = date('j'); // 1-31
$next_year = $current_year + 1;
$user_id = get_current_user_id();
$membership = $user_id ? WDTA_Database::get_user_membership($user_id, $current_year) : null;
$has_active = $user_id ? WDTA_Database::has_active_membership($user_id, $current_year) : false;

// Get cutoff date from settings (default: November 1st)
$cutoff_month = get_option('wdta_year_cutoff_month', 11);
$cutoff_day = get_option('wdta_year_cutoff_day', 1);

// Check if current date is past the cutoff date
$current_date = mktime(0, 0, 0, $current_month, $current_day, $current_year);
$cutoff_date = mktime(0, 0, 0, $cutoff_month, $cutoff_day, $current_year);
$is_past_cutoff = ($current_date >= $cutoff_date);

// Determine the applicable year for payment based on cutoff date
// Before cutoff: payments are for current year
// After cutoff: payments are for next year
$applicable_year = $is_past_cutoff ? $next_year : $current_year;

// Check if user has paid for the applicable year
$applicable_membership = $user_id ? WDTA_Database::get_user_membership($user_id, $applicable_year) : null;
$has_paid_applicable_year = $user_id ? WDTA_Database::has_active_membership($user_id, $applicable_year) : false;

// Next year membership check (always check next year for display purposes)
$next_year_membership = $user_id ? WDTA_Database::get_user_membership($user_id, $next_year) : null;
$has_next_year_active = $user_id ? WDTA_Database::has_active_membership($user_id, $next_year) : false;

// Show year selector if past cutoff and user hasn't paid for next year yet
$show_year_selector = false; // Simplified: no year selector, just show applicable year

// The payment year is determined by the cutoff date
$payment_year = $applicable_year;

$payment_year_membership = $user_id ? WDTA_Database::get_user_membership($user_id, $payment_year) : null;
$payment_year_active = $user_id ? WDTA_Database::has_active_membership($user_id, $payment_year) : false;

// Get membership price from settings (default: $950.00)
$membership_price = floatval(get_option('wdta_membership_price', '950.00'));
?>

<div class="wdta-membership-form">
    <?php if (!is_user_logged_in()): ?>
        <!-- Allow non-logged in users to register and purchase on the same page -->
        <h2>WDTA Membership - <?php echo $payment_year; ?></h2>
        <div class="wdta-pricing-info">
            <p><strong>Annual membership fee: $<?php echo number_format($membership_price, 2); ?> AUD</strong></p>
            <?php if ($is_past_cutoff): ?>
                <p class="wdta-info-text">You are registering for <?php echo $payment_year; ?> membership.</p>
            <?php endif; ?>
        </div>
        
        <div class="wdta-info-message">
            <p><strong>New members:</strong> Complete the form below to register and pay for your membership.</p>
            <p>Already have an account? <a href="https://www.wdta.org.au/member-login/">Log in here</a></p>
        </div>
        
        <!-- Registration Form for New Users -->
        <div class="wdta-registration-section">
            <h3>Your Information</h3>
            <form id="wdta-registration-form">
                <div class="wdta-form-row">
                    <div class="wdta-form-field">
                        <label for="wdta_first_name">First Name *</label>
                        <input type="text" id="wdta_first_name" name="first_name" required>
                    </div>
                    <div class="wdta-form-field">
                        <label for="wdta_last_name">Last Name *</label>
                        <input type="text" id="wdta_last_name" name="last_name" required>
                    </div>
                </div>
                <div class="wdta-form-row">
                    <div class="wdta-form-field">
                        <label for="wdta_email">Email Address (will be your username) *</label>
                        <input type="email" id="wdta_email" name="email" required>
                    </div>
                </div>
                <div class="wdta-form-row">
                    <div class="wdta-form-field">
                        <label for="wdta_password">Password *</label>
                        <div class="wdta-password-wrapper">
                            <input type="password" id="wdta_password" name="password" required minlength="8">
                            <button type="button" class="wdta-password-toggle" aria-label="Show password">
                                <span class="wdta-eye-icon">👁</span>
                            </button>
                        </div>
                        <small>Minimum 8 characters</small>
                    </div>
                    <div class="wdta-form-field">
                        <label for="wdta_password_confirm">Confirm Password *</label>
                        <div class="wdta-password-wrapper">
                            <input type="password" id="wdta_password_confirm" name="password_confirm" required minlength="8">
                            <button type="button" class="wdta-password-toggle" aria-label="Show password">
                                <span class="wdta-eye-icon">👁</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="wdta-registration-errors" class="wdta-error-message" style="display:none;"></div>
            </form>
        </div>
        
        <div class="wdta-payment-methods">
            <h3>Choose Payment Method:</h3>
            
            <div class="wdta-payment-option wdta-stripe-payment">
                <h4>Pay with Credit Card</h4>
                <p class="wdta-payment-description">Secure payment via Stripe</p>
                
                <div class="wdta-stripe-pricing">
                    <?php
                    $stripe_surcharge = $membership_price * 0.022; // 2.2%
                    $stripe_total = $membership_price + $stripe_surcharge;
                    ?>
                    <div class="wdta-price-line">
                        <span>Membership fee:</span>
                        <span>$<?php echo number_format($membership_price, 2); ?> AUD</span>
                    </div>
                    <div class="wdta-price-line wdta-surcharge">
                        <span>Card processing fee (2.2%):</span>
                        <span>$<?php echo number_format($stripe_surcharge, 2); ?> AUD</span>
                    </div>
                    <div class="wdta-price-line wdta-total">
                        <span><strong>Total amount:</strong></span>
                        <span><strong>$<?php echo number_format($stripe_total, 2); ?> AUD</strong></span>
                    </div>
                </div>
                
                <form id="wdta-stripe-payment-form-new-user">
                    <div id="wdta-card-element-new-user" class="wdta-card-element">
                        <!-- Stripe Card Element will be inserted here -->
                    </div>
                    
                    <div id="wdta-card-errors-new-user" class="wdta-error-message" role="alert" style="display:none;"></div>
                    
                    <button id="wdta-stripe-submit-new-user" class="button button-primary" type="submit">
                        <span id="wdta-button-text-new-user">Register & Pay $<?php echo number_format($stripe_total, 2); ?> AUD</span>
                        <span id="wdta-spinner-new-user" class="wdta-spinner" style="display:none;"></span>
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
                        <li><strong>Amount:</strong> $<?php echo number_format($membership_price, 2); ?> AUD</li>
                        <li><strong>Reference:</strong> Your name and "WDTA <?php echo $current_year; ?>"</li>
                    </ul>
                </div>
                
                <div class="wdta-bank-form">
                    <h5>After making your transfer, submit your details:</h5>
                    <form id="wdta-bank-transfer-form-new-user">
                        <p>
                            <label for="wdta_reference_new">Payment Reference:</label>
                            <input type="text" id="wdta_reference_new" name="reference" required 
                                   placeholder="e.g., John Smith WDTA <?php echo $current_year; ?>">
                        </p>
                        <p>
                            <label for="wdta_payment_date_new">Payment Date:</label>
                            <input type="date" id="wdta_payment_date_new" name="payment_date" required>
                        </p>
                        <p>
                            <button type="submit" id="wdta-bank-submit-new-user" class="button button-primary">Register & Submit Bank Transfer Details</button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
        
        <div id="wdta-message-new-user"></div>
    <?php else: ?>
        
        <?php 
        // Check if user has paid for the applicable year
        if ($payment_year_active): 
        ?>
            <div class="wdta-success-message">
                <h3>✓ Your account is paid up for this year</h3>
                <p>Your WDTA membership for <?php echo $payment_year; ?> is active until December 31, <?php echo $payment_year; ?>.</p>
                <p>No further action is needed.</p>
            </div>
        <?php elseif ($payment_year_membership && $payment_year_membership->payment_status === 'pending_verification'): ?>
            <div class="wdta-info-message">
                <h3>Payment Pending Verification</h3>
                <p>Your bank transfer payment is being verified. You will receive an email once your membership is activated.</p>
            </div>
        <?php else: ?>
            <h2>WDTA Membership - <?php echo $payment_year; ?></h2>
            <div class="wdta-pricing-info">
                <p><strong>Annual membership fee: $<?php echo number_format($membership_price, 2); ?> AUD</strong></p>
                <?php if ($is_past_cutoff): ?>
                    <p class="wdta-info-text">You are paying for <?php echo $payment_year; ?> membership.</p>
                <?php endif; ?>
            </div>
        
        <div class="wdta-payment-methods">
            <h3>Choose Payment Method:</h3>
            
            <div class="wdta-payment-option wdta-stripe-payment">
                <h4>Pay with Credit Card</h4>
                <p class="wdta-payment-description">Secure payment via Stripe</p>
                
                <div class="wdta-stripe-pricing">
                    <?php
                    $stripe_surcharge_logged = $membership_price * 0.022; // 2.2%
                    $stripe_total_logged = $membership_price + $stripe_surcharge_logged;
                    ?>
                    <div class="wdta-price-line">
                        <span>Membership fee:</span>
                        <span>$<?php echo number_format($membership_price, 2); ?> AUD</span>
                    </div>
                    <div class="wdta-price-line wdta-surcharge">
                        <span>Card processing fee (2.2%):</span>
                        <span>$<?php echo number_format($stripe_surcharge_logged, 2); ?> AUD</span>
                    </div>
                    <div class="wdta-price-line wdta-total">
                        <span><strong>Total amount:</strong></span>
                        <span><strong>$<?php echo number_format($stripe_total_logged, 2); ?> AUD</strong></span>
                    </div>
                </div>
                
                <form id="wdta-stripe-payment-form">
                    <div id="wdta-card-element" class="wdta-card-element">
                        <!-- Stripe Card Element will be inserted here -->
                    </div>
                    
                    <div id="wdta-card-errors" class="wdta-error-message" role="alert" style="display:none;"></div>
                    
                    <button id="wdta-stripe-submit" class="button button-primary" type="submit">
                        <span id="wdta-button-text">Pay $<?php echo number_format($stripe_total_logged, 2); ?> AUD</span>
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
                        <li><strong>Amount:</strong> $<?php echo number_format($membership_price, 2); ?> AUD</li>
                        <li><strong>Reference:</strong> Your name and "WDTA <?php echo $payment_year; ?>"</li>
                    </ul>
                </div>
                
                <div class="wdta-bank-form">
                    <h5>After making your transfer, submit the details:</h5>
                    <form id="wdta-bank-transfer-form">
                        <p>
                            <label for="wdta_reference">Payment Reference:</label>
                            <input type="text" id="wdta_reference" name="reference" required 
                                   placeholder="e.g., John Smith WDTA <?php echo $payment_year; ?>">
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
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if (is_user_logged_in() && !$payment_year_active): ?>
<script>
jQuery(document).ready(function($) {
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
        
        // Create Payment Intent when form is ready
        $.ajax({
            url: wdtaStripe.ajaxurl,
            type: 'POST',
            data: {
                action: 'wdta_create_payment_intent',
                nonce: '<?php echo wp_create_nonce('wdta_membership_nonce'); ?>',
                year: <?php echo $payment_year; ?>
            },
            success: function(response) {
                if (response.success) {
                    clientSecret = response.data.clientSecret;
                } else {
                    $('#wdta-card-errors').text(response.data.message).show();
                }
            }
        });
    }
    
    // Handle Stripe form submission
    $('#wdta-stripe-payment-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!stripe || !cardElement || !clientSecret) {
            $('#wdta-card-errors').text('Payment system not properly initialized. Please refresh and try again.').show();
            return;
        }
        
        setLoading(true);
        
        // Confirm the payment with Stripe
        stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    email: '<?php echo esc_js(wp_get_current_user()->user_email); ?>',
                    name: '<?php echo esc_js(wp_get_current_user()->display_name); ?>'
                }
            }
        }).then(function(result) {
            if (result.error) {
                // Show error to customer
                $('#wdta-card-errors').text(result.error.message).show();
                setLoading(false);
            } else {
                // Payment succeeded
                if (result.paymentIntent.status === 'succeeded') {
                    $('#wdta-message').html('<div class="wdta-success-message"><p>✓ Payment successful! Your membership is now active.</p></div>');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            }
        });
    });
    
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
        
        var formData = $(this).serialize();
        formData += '&action=wdta_submit_bank_transfer';
        formData += '&nonce=<?php echo wp_create_nonce('wdta_membership_nonce'); ?>';
        formData += '&year=<?php echo $payment_year; ?>';
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#wdta-message').html('<p class="success">' + response.data.message + '</p>');
                    $('#wdta-bank-transfer-form')[0].reset();
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $('#wdta-message').html('<p class="error">' + response.data.message + '</p>');
                }
            }
        });
    });
});
</script>
<?php endif; ?>

<?php if (!is_user_logged_in()): ?>
<script>
jQuery(document).ready(function($) {
    // Password toggle functionality for registration form
    $('.wdta-password-toggle').on('click', function() {
        var $input = $(this).siblings('input');
        var $icon = $(this).find('.wdta-eye-icon');
        
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.text('🙈');
            $(this).attr('aria-label', 'Hide password');
        } else {
            $input.attr('type', 'password');
            $icon.text('👁');
            $(this).attr('aria-label', 'Show password');
        }
    });
    
    // Initialize Stripe Elements for new users
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
        cardElement.mount('#wdta-card-element-new-user');
        
        // Handle real-time validation errors
        cardElement.on('change', function(event) {
            var displayError = document.getElementById('wdta-card-errors-new-user');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
    }
    
    // Validate registration form
    function validateRegistrationForm() {
        var errors = [];
        var email = $('#wdta_email').val();
        var password = $('#wdta_password').val();
        var passwordConfirm = $('#wdta_password_confirm').val();
        var firstName = $('#wdta_first_name').val();
        var lastName = $('#wdta_last_name').val();
        
        if (!firstName.trim()) {
            errors.push('First name is required');
        }
        if (!lastName.trim()) {
            errors.push('Last name is required');
        }
        if (!email.trim() || !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            errors.push('Valid email address is required');
        }
        if (password.length < 8) {
            errors.push('Password must be at least 8 characters');
        }
        if (password !== passwordConfirm) {
            errors.push('Passwords do not match');
        }
        
        return errors;
    }
    
    // Handle Stripe form submission for new users
    $('#wdta-stripe-payment-form-new-user').on('submit', function(e) {
        e.preventDefault();
        
        // Validate registration form first
        var errors = validateRegistrationForm();
        if (errors.length > 0) {
            $('#wdta-registration-errors').html(errors.join('<br>')).show();
            return;
        }
        $('#wdta-registration-errors').hide();
        
        if (!stripe || !cardElement) {
            $('#wdta-card-errors-new-user').text('Payment system not properly initialized. Please refresh and try again.').show();
            return;
        }
        
        setLoadingNewUser(true);
        
        // First, register the user
        $.ajax({
            url: wdtaStripe.ajaxurl,
            type: 'POST',
            data: {
                action: 'wdta_register_and_create_payment_intent',
                nonce: '<?php echo wp_create_nonce('wdta_membership_nonce'); ?>',
                year: <?php echo $current_year; ?>,
                first_name: $('#wdta_first_name').val(),
                last_name: $('#wdta_last_name').val(),
                email: $('#wdta_email').val(),
                password: $('#wdta_password').val()
            },
            success: function(response) {
                if (response.success) {
                    clientSecret = response.data.clientSecret;
                    
                    // Now confirm the payment with Stripe
                    stripe.confirmCardPayment(clientSecret, {
                        payment_method: {
                            card: cardElement,
                            billing_details: {
                                email: $('#wdta_email').val(),
                                name: $('#wdta_first_name').val() + ' ' + $('#wdta_last_name').val()
                            }
                        }
                    }).then(function(result) {
                        if (result.error) {
                            $('#wdta-card-errors-new-user').text(result.error.message).show();
                            setLoadingNewUser(false);
                        } else {
                            if (result.paymentIntent.status === 'succeeded') {
                                $('#wdta-message-new-user').html('<div class="wdta-success-message"><p>✓ Registration and payment successful! Your membership is now active. Redirecting...</p></div>');
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            }
                        }
                    });
                } else {
                    $('#wdta-card-errors-new-user').text(response.data.message).show();
                    setLoadingNewUser(false);
                }
            },
            error: function() {
                $('#wdta-card-errors-new-user').text('An error occurred. Please try again.').show();
                setLoadingNewUser(false);
            }
        });
    });
    
    function setLoadingNewUser(isLoading) {
        if (isLoading) {
            $('#wdta-stripe-submit-new-user').prop('disabled', true);
            $('#wdta-button-text-new-user').hide();
            $('#wdta-spinner-new-user').show();
        } else {
            $('#wdta-stripe-submit-new-user').prop('disabled', false);
            $('#wdta-button-text-new-user').show();
            $('#wdta-spinner-new-user').hide();
        }
    }
    
    // Bank transfer form for new users
    $('#wdta-bank-transfer-form-new-user').on('submit', function(e) {
        e.preventDefault();
        
        // Validate registration form first
        var errors = validateRegistrationForm();
        if (errors.length > 0) {
            $('#wdta-registration-errors').html(errors.join('<br>')).show();
            return;
        }
        $('#wdta-registration-errors').hide();
        
        var formData = $(this).serialize();
        formData += '&action=wdta_register_and_submit_bank_transfer';
        formData += '&nonce=<?php echo wp_create_nonce('wdta_membership_nonce'); ?>';
        formData += '&year=<?php echo $current_year; ?>';
        formData += '&first_name=' + encodeURIComponent($('#wdta_first_name').val());
        formData += '&last_name=' + encodeURIComponent($('#wdta_last_name').val());
        formData += '&email=' + encodeURIComponent($('#wdta_email').val());
        formData += '&password=' + encodeURIComponent($('#wdta_password').val());
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#wdta-message-new-user').html('<p class="success">' + response.data.message + '</p>');
                    $('#wdta-bank-transfer-form-new-user')[0].reset();
                    $('#wdta-registration-form')[0].reset();
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $('#wdta-message-new-user').html('<p class="error">' + response.data.message + '</p>');
                }
            }
        });
    });
});
</script>
<?php endif; ?>
