<?php
/**
 * Renewal form template for next year's membership (logged-in users only)
 * Shortcode: [wdta_renewal_form]
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_year = date('Y');
$current_month = date('n'); // 1-12
$next_year = $current_year + 1;
$user_id = get_current_user_id();

// Check if user can renew (from November 1st onwards)
$can_renew = ($current_month >= 11);

if (!$can_renew) {
    echo '<div class="wdta-info-message"><p>Membership renewals for ' . $next_year . ' will be available from November ' . $current_year . '.</p></div>';
    return;
}

$next_year_membership = $user_id ? WDTA_Database::get_user_membership($user_id, $next_year) : null;
$has_next_year_active = $user_id ? WDTA_Database::has_active_membership($user_id, $next_year) : false;
?>

<div class="wdta-renewal-form">
    <?php if (!is_user_logged_in()): ?>
        <p>Please <a href="<?php echo wp_login_url(get_permalink()); ?>">log in</a> to renew your membership.</p>
    <?php elseif ($has_next_year_active): ?>
        <div class="wdta-success-message">
            <h3>✓ Your <?php echo $next_year; ?> membership is active</h3>
            <p>Your WDTA membership for <?php echo $next_year; ?> is active until December 31, <?php echo $next_year; ?>.</p>
        </div>
    <?php elseif ($next_year_membership && $next_year_membership->payment_status === 'pending_verification'): ?>
        <div class="wdta-info-message">
            <h3>Payment Pending Verification</h3>
            <p>Your bank transfer payment is being verified. You will receive an email once your membership is activated.</p>
        </div>
    <?php else: ?>
        <h2>Renew Your Membership for <?php echo $next_year; ?></h2>
        <div class="wdta-pricing-info">
            <p><strong>Annual membership fee: <?php echo wdta_get_membership_price(true); ?></strong></p>
            <p>Renew now for <?php echo $next_year; ?> membership.</p>
        </div>
    
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
                    <span>Credit card surcharge (2.2%):</span>
                    <span>$<?php echo number_format($surcharge, 2); ?> AUD</span>
                </div>
                <div class="wdta-price-line wdta-total">
                    <span><strong>Total:</strong></span>
                    <span><strong>$<?php echo number_format($stripe_price, 2); ?> AUD</strong></span>
                </div>
            </div>
            
            <form id="wdta-stripe-form" class="wdta-payment-form">
                <div id="card-element"></div>
                <div id="card-errors" role="alert"></div>
                <input type="hidden" name="year" value="<?php echo esc_attr($next_year); ?>">
                <button type="submit" id="submit-payment" class="button button-primary">Pay $<?php echo number_format($stripe_price, 2); ?> AUD</button>
                <div class="wdta-processing" style="display:none;">Processing...</div>
            </form>
        </div>
        
        <div class="wdta-payment-option wdta-bank-payment">
            <h4>Pay via Bank Transfer</h4>
            <p class="wdta-payment-description">No credit card surcharge</p>
            
            <div class="wdta-bank-pricing">
                <div class="wdta-price-line wdta-total">
                    <span><strong>Amount to transfer:</strong></span>
                    <span><strong><?php echo wdta_get_membership_price(true); ?></strong></span>
                </div>
            </div>
            
            <div class="wdta-bank-details">
                <h5>Bank Details:</h5>
                <table class="wdta-bank-info">
                    <tr>
                        <td><strong>Bank:</strong></td>
                        <td><?php echo esc_html(get_option('wdta_bank_name', '')); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Account Name:</strong></td>
                        <td><?php echo esc_html(get_option('wdta_bank_account_name', '')); ?></td>
                    </tr>
                    <tr>
                        <td><strong>BSB:</strong></td>
                        <td><?php echo esc_html(get_option('wdta_bank_bsb', '')); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Account Number:</strong></td>
                        <td><?php echo esc_html(get_option('wdta_bank_account_number', '')); ?></td>
                    </tr>
                </table>
                <p><strong>Reference:</strong> Your name and "<?php echo $next_year; ?> Membership"</p>
            </div>
            
            <form id="wdta-bank-form" class="wdta-payment-form">
                <input type="hidden" name="year" value="<?php echo esc_attr($next_year); ?>">
                <button type="submit" class="button button-primary">I've Made the Transfer</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.wdta-renewal-form {
    max-width: 800px;
    margin: 0 auto;
}
.wdta-pricing-info {
    background: #f0f0f1;
    padding: 15px;
    margin: 20px 0;
    border-left: 4px solid #2271b1;
}
.wdta-payment-methods {
    margin-top: 30px;
}
.wdta-payment-option {
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 20px;
    background: #fff;
}
.wdta-payment-option h4 {
    margin-top: 0;
}
.wdta-stripe-pricing, .wdta-bank-pricing {
    background: #f9f9f9;
    padding: 15px;
    margin: 15px 0;
}
.wdta-price-line {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
}
.wdta-price-line.wdta-total {
    border-top: 2px solid #ddd;
    margin-top: 10px;
    padding-top: 10px;
}
.wdta-bank-details {
    margin: 15px 0;
}
.wdta-bank-info {
    width: 100%;
    margin: 10px 0;
}
.wdta-bank-info td {
    padding: 8px;
    border-bottom: 1px solid #eee;
}
#card-element {
    border: 1px solid #ddd;
    padding: 12px;
    margin: 15px 0;
    background: #fff;
}
#card-errors {
    color: #c00;
    margin: 10px 0;
}
.wdta-processing {
    margin: 10px 0;
    color: #2271b1;
}
.wdta-success-message, .wdta-info-message {
    background: #d7f3e3;
    border-left: 4px solid #00a32a;
    padding: 15px;
    margin: 20px 0;
}
.wdta-info-message {
    background: #f0f6fc;
    border-left-color: #2271b1;
}
</style>

<script src="https://js.stripe.com/v3/"></script>
<script>
jQuery(document).ready(function($) {
    // Initialize Stripe
    var stripe = Stripe('<?php echo esc_js(get_option('wdta_stripe_publishable_key')); ?>');
    var elements = stripe.elements();
    var cardElement = elements.create('card');
    cardElement.mount('#card-element');
    
    // Handle card errors
    cardElement.on('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
    
    // Handle Stripe payment
    $('#wdta-stripe-form').on('submit', function(e) {
        e.preventDefault();
        
        var submitButton = $('#submit-payment');
        var processing = $('.wdta-processing');
        
        submitButton.prop('disabled', true);
        processing.show();
        
        var year = $('input[name="year"]', this).val();
        
        // Create payment intent
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            method: 'POST',
            data: {
                action: 'wdta_create_payment_intent',
                year: year
            },
            success: function(response) {
                if (response.success) {
                    // Confirm payment
                    stripe.confirmCardPayment(response.data.clientSecret, {
                        payment_method: {
                            card: cardElement
                        }
                    }).then(function(result) {
                        if (result.error) {
                            $('#card-errors').text(result.error.message);
                            submitButton.prop('disabled', false);
                            processing.hide();
                        } else {
                            // Payment successful
                            window.location.href = '<?php echo esc_url(home_url('/my-account/')); ?>';
                        }
                    });
                } else {
                    $('#card-errors').text(response.data.message);
                    submitButton.prop('disabled', false);
                    processing.hide();
                }
            },
            error: function() {
                $('#card-errors').text('An error occurred. Please try again.');
                submitButton.prop('disabled', false);
                processing.hide();
            }
        });
    });
    
    // Handle bank transfer
    $('#wdta-bank-form').on('submit', function(e) {
        e.preventDefault();
        
        var year = $('input[name="year"]', this).val();
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            method: 'POST',
            data: {
                action: 'wdta_record_bank_transfer',
                year: year
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});
</script>
