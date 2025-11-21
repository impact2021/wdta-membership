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
