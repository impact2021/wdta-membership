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
        
        <button type="button" class="button button-primary wdta-add-membership" style="margin-left: 10px;">
            Add New Membership
        </button>
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
                    <?php 
                    $user = get_userdata($membership->user_id);
                    // Skip administrators
                    if ($user && in_array('administrator', $user->roles)) {
                        continue;
                    }
                    ?>
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
                            <?php if ($membership->payment_status === 'completed'): ?>
                                <button class="button wdta-preview-payment-email" 
                                        data-user-id="<?php echo esc_attr($membership->user_id); ?>"
                                        data-year="<?php echo esc_attr($membership->membership_year); ?>"
                                        title="Preview payment confirmation email">
                                    Preview Email
                                </button>
                                <button class="button wdta-resend-payment-email" 
                                        data-user-id="<?php echo esc_attr($membership->user_id); ?>"
                                        data-year="<?php echo esc_attr($membership->membership_year); ?>"
                                        title="Resend payment confirmation email">
                                    Resend Email
                                </button>
                            <?php endif; ?>
                            <button class="button button-link-delete wdta-delete-membership" 
                                    data-user-id="<?php echo esc_attr($membership->user_id); ?>"
                                    data-year="<?php echo esc_attr($membership->membership_year); ?>">
                                Delete
                            </button>
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

<!-- Add New Membership Modal -->
<div id="wdta-add-membership-modal">
    <div class="wdta-modal-overlay"></div>
    <div class="wdta-modal-content">
        <div class="wdta-modal-header">
            <h2>Add New Membership</h2>
            <button class="wdta-modal-close">&times;</button>
        </div>
        <div class="wdta-modal-body">
            <form id="wdta-add-membership-form">
                <table class="form-table">
                    <tr>
                        <th><label for="add-user-search">User</label></th>
                        <td>
                            <select id="add-user-id" name="user_id" required style="width: 100%;">
                                <option value="">Select a user...</option>
                                <?php
                                // Get all non-admin users
                                $users = get_users(array(
                                    'role__not_in' => array('administrator'),
                                    'orderby' => 'display_name',
                                    'order' => 'ASC'
                                ));
                                foreach ($users as $user) {
                                    printf(
                                        '<option value="%d">%s (%s)</option>',
                                        esc_attr($user->ID),
                                        esc_html($user->display_name),
                                        esc_html($user->user_email)
                                    );
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="add-year">Membership Year</label></th>
                        <td>
                            <select id="add-year" name="year" required>
                                <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php selected($current_year, $y); ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="add-payment-method">Payment Method</label></th>
                        <td>
                            <select id="add-payment-method" name="payment_method">
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="stripe">Stripe</option>
                                <option value="manual">Manual / Admin</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="add-payment-status">Payment Status</label></th>
                        <td>
                            <select id="add-payment-status" name="payment_status">
                                <option value="pending">Pending</option>
                                <option value="pending_verification">Pending Verification</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="add-status">Membership Status</label></th>
                        <td>
                            <select id="add-status" name="status">
                                <option value="pending">Pending</option>
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="add-payment-amount">Payment Amount (AUD)</label></th>
                        <td>
                            <input type="number" id="add-payment-amount" name="payment_amount" step="0.01" min="0" value="950.00">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="add-expiry-date">Expiry Date</label></th>
                        <td>
                            <input type="date" id="add-expiry-date" name="expiry_date" value="<?php echo date('Y') . '-12-31'; ?>">
                        </td>
                    </tr>
                </table>
                
                <div class="wdta-modal-actions">
                    <button type="submit" class="button button-primary">Add Membership</button>
                    <button type="button" class="button wdta-modal-close">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Email Preview Modal -->
<div id="wdta-email-preview-modal">
    <div class="wdta-modal-overlay"></div>
    <div class="wdta-modal-content" style="max-width: 800px;">
        <div class="wdta-modal-header">
            <h2>Email Preview</h2>
            <button class="wdta-modal-close">&times;</button>
        </div>
        <div class="wdta-modal-body">
            <div class="email-preview-meta" style="background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 3px;">
                <p style="margin: 5px 0;"><strong>To:</strong> <span id="preview-email-to"></span></p>
                <p style="margin: 5px 0;"><strong>Subject:</strong> <span id="preview-email-subject"></span></p>
            </div>
            <div class="email-preview-content" style="border: 1px solid #ddd; padding: 20px; background: white; max-height: 500px; overflow-y: auto;">
                <div id="preview-email-body"></div>
            </div>
            <div class="wdta-modal-actions">
                <button type="button" class="button wdta-modal-close">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
#wdta-edit-membership-modal,
#wdta-add-membership-modal,
#wdta-email-preview-modal {
    display: none;
}
#wdta-edit-membership-modal.wdta-modal-active,
#wdta-add-membership-modal.wdta-modal-active,
#wdta-email-preview-modal.wdta-modal-active {
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
.wdta-filters {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    gap: 10px;
}
.wdta-filters form {
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>
