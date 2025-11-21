<?php
/**
 * Membership form template (shortcode)
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_year = date('Y');
$user_id = get_current_user_id();
$membership = $user_id ? WDTA_Database::get_user_membership($user_id, $current_year) : null;
$has_active = $user_id ? WDTA_Database::has_active_membership($user_id, $current_year) : false;
?>

<div class="wdta-membership-form">
    <?php if (!is_user_logged_in()): ?>
        <p>Please <a href="<?php echo wp_login_url(get_permalink()); ?>">log in</a> to purchase or renew your membership.</p>
    <?php elseif ($has_active): ?>
        <div class="wdta-success-message">
            <h3>✓ Your membership is active</h3>
            <p>Your WDTA membership for <?php echo $current_year; ?> is active until March 31, <?php echo $current_year; ?>.</p>
        </div>
    <?php elseif ($membership && $membership->payment_status === 'pending_verification'): ?>
        <div class="wdta-info-message">
            <h3>Payment Pending Verification</h3>
            <p>Your bank transfer payment is being verified. You will receive an email once your membership is activated.</p>
        </div>
    <?php else: ?>
        <h2>WDTA Membership - <?php echo $current_year; ?></h2>
        <p><strong>Annual membership fee: $950 AUD</strong></p>
        <p>Payment must be received by <strong>March 31, <?php echo $current_year; ?></strong></p>
        
        <div class="wdta-payment-methods">
            <h3>Choose Payment Method:</h3>
            
            <div class="wdta-payment-option">
                <h4>Pay with Credit Card (Stripe)</h4>
                <button id="wdta-stripe-button" class="button button-primary">Pay with Card - $950 AUD</button>
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
                    <h5>After making your transfer, submit the details:</h5>
                    <form id="wdta-bank-transfer-form">
                        <p>
                            <label for="wdta_reference">Payment Reference:</label>
                            <input type="text" id="wdta_reference" name="reference" required 
                                   placeholder="e.g., John Smith WDTA 2024">
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
</div>

<?php if (is_user_logged_in() && !$has_active): ?>
<script>
jQuery(document).ready(function($) {
    // Stripe payment
    $('#wdta-stripe-button').on('click', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'wdta_create_stripe_session',
                nonce: '<?php echo wp_create_nonce('wdta_membership_nonce'); ?>',
                year: <?php echo $current_year; ?>
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.url;
                } else {
                    $('#wdta-message').html('<p class="error">' + response.data.message + '</p>');
                }
            }
        });
    });
    
    // Bank transfer form
    $('#wdta-bank-transfer-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        formData += '&action=wdta_submit_bank_transfer';
        formData += '&nonce=<?php echo wp_create_nonce('wdta_membership_nonce'); ?>';
        formData += '&year=<?php echo $current_year; ?>';
        
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
