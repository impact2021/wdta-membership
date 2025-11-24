<?php
/**
 * Admin memberships list template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>WDTA Memberships</h1>
    
    <div class="wdta-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="wdta-memberships">
            
            <label for="year">Year:</label>
            <select name="year" id="year">
                <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php selected($current_year, $y); ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
            
            <label for="status">Status:</label>
            <select name="status" id="status">
                <option value="">All</option>
                <option value="active" <?php selected($status_filter, 'active'); ?>>Active</option>
                <option value="pending" <?php selected($status_filter, 'pending'); ?>>Pending</option>
                <option value="expired" <?php selected($status_filter, 'expired'); ?>>Expired</option>
                <option value="rejected" <?php selected($status_filter, 'rejected'); ?>>Rejected</option>
            </select>
            
            <input type="submit" class="button" value="Filter">
        </form>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>User</th>
                <th>User Role</th>
                <th>Year</th>
                <th>Payment Method</th>
                <th>Payment Status</th>
                <th>Amount</th>
                <th>Payment Date</th>
                <th>Reference</th>
                <th>Expiry Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($memberships)): ?>
                <tr>
                    <td colspan="11">No memberships found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($memberships as $membership): ?>
                    <?php $user = get_userdata($membership->user_id); ?>
                    <tr>
                        <td>
                            <?php echo esc_html($user ? $user->display_name : 'Unknown'); ?>
                            <br><small><?php echo esc_html($user ? $user->user_email : ''); ?></small>
                        </td>
                        <td>
                            <?php 
                            if ($user && !empty($user->roles)) {
                                $role = $user->roles[0];
                                echo '<span class="user-role user-role-' . esc_attr($role) . '">';
                                echo esc_html(WDTA_User_Roles::get_role_display_name($role));
                                echo '</span>';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td><?php echo esc_html($membership->membership_year); ?></td>
                        <td><?php echo esc_html(ucwords(str_replace('_', ' ', $membership->payment_method))); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo esc_attr($membership->payment_status); ?>">
                                <?php echo esc_html(ucwords(str_replace('_', ' ', $membership->payment_status))); ?>
                            </span>
                        </td>
                        <td>$<?php echo number_format($membership->payment_amount, 2); ?> AUD</td>
                        <td><?php echo $membership->payment_date ? wdta_format_date($membership->payment_date) : '-'; ?></td>
                        <td><?php echo esc_html($membership->payment_reference ?: '-'); ?></td>
                        <td><?php echo wdta_format_date($membership->expiry_date); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo esc_attr($membership->status); ?>">
                                <?php echo esc_html(ucwords($membership->status)); ?>
                            </span>
                        </td>
                        <td>
                            <button class="button wdta-edit-membership" 
                                    data-user-id="<?php echo esc_attr($membership->user_id); ?>"
                                    data-year="<?php echo esc_attr($membership->membership_year); ?>"
                                    data-payment-status="<?php echo esc_attr($membership->payment_status); ?>"
                                    data-status="<?php echo esc_attr($membership->status); ?>"
                                    data-payment-amount="<?php echo esc_attr($membership->payment_amount); ?>"
                                    data-expiry-date="<?php echo esc_attr($membership->expiry_date); ?>"
                                    data-payment-method="<?php echo esc_attr($membership->payment_method); ?>">
                                Edit
                            </button>
                            <?php if ($membership->payment_status === 'pending_verification'): ?>
                                <button class="button button-primary wdta-approve-membership" 
                                        data-user-id="<?php echo esc_attr($membership->user_id); ?>"
                                        data-year="<?php echo esc_attr($membership->membership_year); ?>">
                                    Approve
                                </button>
                                <button class="button wdta-reject-membership" 
                                        data-user-id="<?php echo esc_attr($membership->user_id); ?>"
                                        data-year="<?php echo esc_attr($membership->membership_year); ?>">
                                    Reject
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Edit Membership Modal -->
<div id="wdta-edit-membership-modal">
    <div class="wdta-modal-overlay"></div>
    <div class="wdta-modal-content">
        <div class="wdta-modal-header">
            <h2>Edit Membership</h2>
            <button class="wdta-modal-close">&times;</button>
        </div>
        <div class="wdta-modal-body">
            <form id="wdta-edit-membership-form">
                <input type="hidden" id="edit-user-id" name="user_id">
                <input type="hidden" id="edit-year" name="year">
                
                <table class="form-table">
                    <tr>
                        <th><label for="edit-payment-status">Payment Status</label></th>
                        <td>
                            <select id="edit-payment-status" name="payment_status">
                                <option value="pending">Pending</option>
                                <option value="pending_verification">Pending Verification</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="edit-status">Membership Status</label></th>
                        <td>
                            <select id="edit-status" name="status">
                                <option value="pending">Pending</option>
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="edit-payment-amount">Payment Amount (AUD)</label></th>
                        <td>
                            <input type="number" id="edit-payment-amount" name="payment_amount" step="0.01" min="0">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="edit-expiry-date">Expiry Date</label></th>
                        <td>
                            <input type="date" id="edit-expiry-date" name="expiry_date">
                        </td>
                    </tr>
                </table>
                
                <div class="wdta-modal-actions">
                    <button type="submit" class="button button-primary">Save Changes</button>
                    <button type="button" class="button wdta-modal-close">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
#wdta-edit-membership-modal {
    display: none;
}
#wdta-edit-membership-modal.wdta-modal-active {
    display: block;
}
.wdta-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 100000;
}
.wdta-modal-content {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    z-index: 100001;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow: auto;
}
.wdta-modal-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.wdta-modal-header h2 {
    margin: 0;
}
.wdta-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    line-height: 1;
}
.wdta-modal-body {
    padding: 20px;
}
.wdta-modal-actions {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
    text-align: right;
}
.wdta-modal-actions button {
    margin-left: 10px;
}
</style>
