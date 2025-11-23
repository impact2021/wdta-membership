<?php
/**
 * Membership form template (shortcode)
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_year = date('Y');
$current_month = date('n'); // 1-12
$next_year = $current_year + 1;
$user_id = get_current_user_id();
$membership = $user_id ? WDTA_Database::get_user_membership($user_id, $current_year) : null;
$has_active = $user_id ? WDTA_Database::has_active_membership($user_id, $current_year) : false;

// Check if user can pay for next year (from November 1st onwards)
$can_pay_next_year = ($current_month >= 11);
$next_year_membership = $user_id && $can_pay_next_year ? WDTA_Database::get_user_membership($user_id, $next_year) : null;
$has_next_year_active = $user_id && $can_pay_next_year ? WDTA_Database::has_active_membership($user_id, $next_year) : false;

// Check if user should see year selector
// Show year selector only if:
// 1. It's November or later AND
// 2. Either they haven't paid for current year OR they've paid but can also pay for next year
$show_year_selector = $can_pay_next_year && (!$has_active || !$has_next_year_active);

// Determine which year to show payment form for
// If current year is already paid, default to next year (from November onwards)
$default_year = ($has_active && $can_pay_next_year) ? $next_year : $current_year;
$payment_year = isset($_GET['year']) ? intval($_GET['year']) : $default_year;

// Only allow current year or next year (if from November onwards)
if ($payment_year != $current_year && (!$can_pay_next_year || $payment_year != $next_year)) {
    $payment_year = $default_year;
}

$payment_year_membership = $user_id ? WDTA_Database::get_user_membership($user_id, $payment_year) : null;
$payment_year_active = $user_id ? WDTA_Database::has_active_membership($user_id, $payment_year) : false;
?>

<div class="wdta-membership-form">
    <?php if (!is_user_logged_in()): ?>
        <!-- Allow non-logged in users to register and purchase on the same page -->
        <h2>WDTA Membership - <?php echo $current_year; ?></h2>
        <div class="wdta-pricing-info">
            <p><strong>Annual membership fee: $950 AUD</strong></p>
            <p>Payment must be received by <strong><?php echo wdta_format_date('December 31, ' . $current_year); ?></strong></p>
        </div>
        
        <div class="wdta-info-message">
            <p><strong>New members:</strong> Complete the form below to register and pay for your membership.</p>
            <p>Already have an account? <a href="<?php echo wp_login_url(get_permalink()); ?>">Log in here</a></p>
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
                    <div class="wdta-price-line">
                        <span>Membership fee:</span>
                        <span>$950.00 AUD</span>
                    </div>
                    <div class="wdta-price-line wdta-surcharge">
                        <span>Card processing fee (2.2%):</span>
                        <span>$20.90 AUD</span>
                    </div>
                    <div class="wdta-price-line wdta-total">
                        <span><strong>Total amount:</strong></span>
                        <span><strong>$970.90 AUD</strong></span>
                    </div>
                </div>
                
                <form id="wdta-stripe-payment-form-new-user">
                    <div id="wdta-card-element-new-user" class="wdta-card-element">
                        <!-- Stripe Card Element will be inserted here -->
                    </div>
                    
                    <div id="wdta-card-errors-new-user" class="wdta-error-message" role="alert"></div>
                    
                    <button id="wdta-stripe-submit-new-user" class="button button-primary" type="submit">
                        <span id="wdta-button-text-new-user">Register & Pay $970.90 AUD</span>
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
                        <li><strong>Amount:</strong> $950 AUD</li>
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
                            <button type="submit" class="button button-primary">Register & Submit Bank Transfer Details</button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
        
        <div id="wdta-message-new-user"></div>
    <?php else: ?>
        
        <?php if ($show_year_selector): ?>
            <div class="wdta-year-selector">
                <h3>Select Membership Year:</h3>
                <div class="wdta-year-buttons">
                    <?php if (!$has_active): ?>
                        <a href="?year=<?php echo $current_year; ?>" class="button <?php echo $payment_year == $current_year ? 'button-primary' : ''; ?>">
                            <?php echo $current_year; ?> Membership
                        </a>
                    <?php endif; ?>
                    <?php if (!$has_next_year_active): ?>
                        <a href="?year=<?php echo $next_year; ?>" class="button <?php echo $payment_year == $next_year ? 'button-primary' : ''; ?>">
                            <?php echo $next_year; ?> Membership
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($payment_year_active): ?>
            <div class="wdta-success-message">
                <h3>✓ Your membership is active</h3>
                <p>Your WDTA membership for <?php echo $payment_year; ?> is active until December 31, <?php echo $payment_year; ?>.</p>
                <?php if ($can_pay_next_year && $payment_year == $current_year && !$has_next_year_active): ?>
                    <p><a href="?year=<?php echo $next_year; ?>" class="button">Pay for <?php echo $next_year; ?> Now</a></p>
                <?php endif; ?>
            </div>
        <?php elseif ($payment_year_membership && $payment_year_membership->payment_status === 'pending_verification'): ?>
            <div class="wdta-info-message">
                <h3>Payment Pending Verification</h3>
                <p>Your bank transfer payment is being verified. You will receive an email once your membership is activated.</p>
            </div>
        <?php else: ?>
            <h2>WDTA Membership - <?php echo $payment_year; ?></h2>
            <div class="wdta-pricing-info">
                <p><strong>Annual membership fee: $950 AUD</strong></p>
                <p>Payment must be received by <strong><?php echo wdta_format_date('December 31, ' . $payment_year); ?></strong></p>
            </div>
        
        <div class="wdta-payment-methods">
            <h3>Choose Payment Method:</h3>
            
            <div class="wdta-payment-option wdta-stripe-payment">
                <h4>Pay with Credit Card</h4>
                <p class="wdta-payment-description">Secure payment via Stripe</p>
                
                <div class="wdta-stripe-pricing">
                    <div class="wdta-price-line">
                        <span>Membership fee:</span>
                        <span>$950.00 AUD</span>
                    </div>
                    <div class="wdta-price-line wdta-surcharge">
                        <span>Card processing fee (2.2%):</span>
                        <span>$20.90 AUD</span>
                    </div>
                    <div class="wdta-price-line wdta-total">
                        <span><strong>Total amount:</strong></span>
                        <span><strong>$970.90 AUD</strong></span>
                    </div>
                </div>
                
                <form id="wdta-stripe-payment-form">
                    <div id="wdta-card-element" class="wdta-card-element">
                        <!-- Stripe Card Element will be inserted here -->
                    </div>
                    
                    <div id="wdta-card-errors" class="wdta-error-message" role="alert"></div>
                    
                    <button id="wdta-stripe-submit" class="button button-primary" type="submit">
                        <span id="wdta-button-text">Pay $970.90 AUD</span>
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
                        <li><strong>Amount:</strong> $950 AUD</li>
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
                    $('#wdta-card-errors').text(response.data.message);
                }
            }
        });
    }
    
    // Handle Stripe form submission
    $('#wdta-stripe-payment-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!stripe || !cardElement || !clientSecret) {
            $('#wdta-card-errors').text('Payment system not properly initialized. Please refresh and try again.');
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
                $('#wdta-card-errors').text(result.error.message);
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
            $('#wdta-card-errors-new-user').text('Payment system not properly initialized. Please refresh and try again.');
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
                            $('#wdta-card-errors-new-user').text(result.error.message);
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
                    $('#wdta-card-errors-new-user').text(response.data.message);
                    setLoadingNewUser(false);
                }
            },
            error: function() {
                $('#wdta-card-errors-new-user').text('An error occurred. Please try again.');
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
