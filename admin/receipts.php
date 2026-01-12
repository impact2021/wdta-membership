<?php
/**
 * Admin receipts list template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get filter parameters
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Get all memberships with completed payments
global $wpdb;
$table_name = $wpdb->prefix . 'wdta_memberships';
$receipts = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name 
     WHERE payment_status = 'completed' 
     AND membership_year = %d 
     ORDER BY payment_date DESC",
    $current_year
));
?>

<div class="wrap">
    <h1>WDTA Receipts</h1>
    
    <p>View and download PDF receipts for completed membership payments.</p>
    
    <div class="wdta-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="wdta-receipts">
            
            <label for="year">Year:</label>
            <select name="year" id="year">
                <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php selected($current_year, $y); ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
            
            <input type="submit" class="button" value="Filter">
        </form>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Receipt Number</th>
                <th>Member Name</th>
                <th>Email</th>
                <th>Year</th>
                <th>Payment Method</th>
                <th>Amount</th>
                <th>Payment Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($receipts)): ?>
                <tr>
                    <td colspan="8">No receipts found for <?php echo esc_html($current_year); ?>.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($receipts as $receipt): ?>
                    <?php 
                    $user_id = intval($receipt->user_id);
                    $user = get_userdata($user_id);
                    if (!$user) {
                        continue;
                    }
                    
                    // Generate receipt number
                    $receipt_number = 'WDTA-' . $receipt->membership_year . '-' . str_pad($receipt->id, 6, '0', STR_PAD_LEFT);
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($receipt_number); ?></strong></td>
                        <td><?php echo esc_html($user->display_name); ?></td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td><?php echo esc_html($receipt->membership_year); ?></td>
                        <td><?php echo esc_html(ucwords(str_replace('_', ' ', $receipt->payment_method))); ?></td>
                        <td>$<?php echo number_format($receipt->payment_amount, 2); ?> AUD</td>
                        <td><?php echo $receipt->payment_date ? wdta_format_date($receipt->payment_date) : '-'; ?></td>
                        <td>
                            <button type="button" 
                                    class="button button-primary wdta-download-receipt" 
                                    data-user-id="<?php echo esc_attr($receipt->user_id); ?>"
                                    data-year="<?php echo esc_attr($receipt->membership_year); ?>">
                                Download PDF
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.wdta-filters {
    margin: 20px 0;
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.wdta-filters form {
    display: inline-block;
}

.wdta-filters label {
    margin-right: 5px;
    font-weight: 600;
}

.wdta-filters select {
    margin-right: 10px;
}
</style>
