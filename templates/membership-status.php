<?php
/**
 * Membership status template (shortcode)
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_year = date('Y');
?>

<div class="wdta-membership-status">
    <h3>Your Membership Status</h3>
    
    <?php if ($membership): ?>
        <table class="wdta-status-table">
            <tr>
                <th>Year:</th>
                <td><?php echo esc_html($membership->membership_year); ?></td>
            </tr>
            <tr>
                <th>Status:</th>
                <td>
                    <span class="status-badge status-<?php echo esc_attr($membership->status); ?>">
                        <?php echo esc_html(ucwords($membership->status)); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Payment Status:</th>
                <td>
                    <span class="status-badge status-<?php echo esc_attr($membership->payment_status); ?>">
                        <?php echo esc_html(ucwords(str_replace('_', ' ', $membership->payment_status))); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <th>Amount Paid:</th>
                <td>$<?php echo number_format($membership->payment_amount, 2); ?> AUD</td>
            </tr>
            <?php if ($membership->payment_date): ?>
            <tr>
                <th>Payment Date:</th>
                <td><?php echo wdta_format_date($membership->payment_date); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>Valid Until:</th>
                <td><?php echo wdta_format_date('December 31, ' . $membership->membership_year); ?></td>
            </tr>
        </table>
        
        <?php if ($membership->status === 'active'): ?>
            <p class="wdta-success">✓ Your membership is active and in good standing.</p>
        <?php elseif ($membership->payment_status === 'pending_verification'): ?>
            <p class="wdta-info">Your payment is being verified. You will receive an email once your membership is activated.</p>
        <?php elseif ($membership->status === 'pending'): ?>
            <p class="wdta-warning">Your membership is pending payment. Please complete your payment to activate your membership.</p>
            <p><a href="<?php echo home_url('/membership'); ?>" class="button">Complete Payment</a></p>
        <?php elseif ($membership->status === 'expired'): ?>
            <p class="wdta-error">Your membership has expired. Please renew to regain access.</p>
            <p><a href="<?php echo home_url('/membership'); ?>" class="button">Renew Membership</a></p>
        <?php endif; ?>
    <?php else: ?>
        <p>You do not have a membership for <?php echo $current_year; ?>.</p>
        <p><a href="<?php echo home_url('/membership'); ?>" class="button">Purchase Membership</a></p>
    <?php endif; ?>
</div>
