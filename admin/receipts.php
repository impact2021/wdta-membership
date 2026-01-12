<?php
/**
 * Admin receipts list template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle settings save
if (isset($_POST['wdta_receipts_settings_submit'])) {
    check_admin_referer('wdta_receipts_settings_action', 'wdta_receipts_settings_nonce');
    
    // Save organization details
    if (isset($_POST['wdta_org_name'])) {
        update_option('wdta_org_name', sanitize_text_field($_POST['wdta_org_name']));
    }
    if (isset($_POST['wdta_org_address'])) {
        update_option('wdta_org_address', sanitize_textarea_field($_POST['wdta_org_address']));
    }
    if (isset($_POST['wdta_org_abn'])) {
        update_option('wdta_org_abn', sanitize_text_field($_POST['wdta_org_abn']));
    }
    if (isset($_POST['wdta_org_phone'])) {
        update_option('wdta_org_phone', sanitize_text_field($_POST['wdta_org_phone']));
    }
    if (isset($_POST['wdta_org_email'])) {
        update_option('wdta_org_email', sanitize_email($_POST['wdta_org_email']));
    }
    if (isset($_POST['wdta_org_website'])) {
        update_option('wdta_org_website', esc_url_raw($_POST['wdta_org_website']));
    }
    if (isset($_POST['wdta_org_logo_url'])) {
        update_option('wdta_org_logo_url', esc_url_raw($_POST['wdta_org_logo_url']));
    }
    
    add_settings_error('wdta_receipts_settings', 'settings_updated', 'Receipt settings saved successfully.', 'updated');
}

// Display any admin notices
settings_errors('wdta_receipts_settings');

// Define earliest year for filtering (when the plugin was first used)
define('WDTA_EARLIEST_YEAR', 2020);

// Get filter parameters with validation
$requested_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$max_year = date('Y') + 1;
$current_year = ($requested_year >= WDTA_EARLIEST_YEAR && $requested_year <= $max_year) ? $requested_year : date('Y');

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
    
    <h2>Receipt Settings</h2>
    <p>Configure the organization details that appear on all PDF receipts.</p>
    
    <form method="post" action="">
        <?php wp_nonce_field('wdta_receipts_settings_action', 'wdta_receipts_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="wdta_org_name">Organization Name</label></th>
                <td>
                    <input type="text" id="wdta_org_name" name="wdta_org_name" 
                           value="<?php echo esc_attr(get_option('wdta_org_name', 'Workplace Drug Testing Association')); ?>" 
                           class="regular-text">
                    <p class="description">Full organization name displayed on receipts</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wdta_org_address">Organization Address</label></th>
                <td>
                    <textarea id="wdta_org_address" name="wdta_org_address" rows="3" class="large-text"><?php echo esc_textarea(get_option('wdta_org_address', '')); ?></textarea>
                    <p class="description">Full address displayed on receipts (e.g., 123 Main St, Sydney NSW 2000, Australia)</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wdta_org_abn">ABN / GST Number</label></th>
                <td>
                    <input type="text" id="wdta_org_abn" name="wdta_org_abn" 
                           value="<?php echo esc_attr(get_option('wdta_org_abn', '')); ?>" 
                           class="regular-text">
                    <p class="description">Australian Business Number (ABN) or GST registration number</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wdta_org_phone">Contact Phone</label></th>
                <td>
                    <input type="text" id="wdta_org_phone" name="wdta_org_phone" 
                           value="<?php echo esc_attr(get_option('wdta_org_phone', '')); ?>" 
                           class="regular-text">
                    <p class="description">Contact phone number displayed on receipts</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wdta_org_email">Contact Email</label></th>
                <td>
                    <input type="email" id="wdta_org_email" name="wdta_org_email" 
                           value="<?php echo esc_attr(get_option('wdta_org_email', 'admin@wdta.org.au')); ?>" 
                           class="regular-text">
                    <p class="description">Contact email address displayed on receipts</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wdta_org_website">Website</label></th>
                <td>
                    <input type="url" id="wdta_org_website" name="wdta_org_website" 
                           value="<?php echo esc_attr(get_option('wdta_org_website', 'https://www.wdta.org.au')); ?>" 
                           class="regular-text">
                    <p class="description">Organization website displayed on receipts</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wdta_org_logo_url">Logo URL</label></th>
                <td>
                    <input type="url" id="wdta_org_logo_url" name="wdta_org_logo_url" 
                           value="<?php echo esc_attr(get_option('wdta_org_logo_url', 'https://www.wdta.org.au/wp-content/uploads/2025/11/Workplace-Drug-Testing-Association.png')); ?>" 
                           class="large-text">
                    <p class="description">Full URL to your organization logo (PNG format recommended, will be cached locally)</p>
                </td>
            </tr>
        </table>
        
        <?php submit_button('Save Receipt Settings', 'primary', 'wdta_receipts_settings_submit'); ?>
    </form>
    
    <hr style="margin: 40px 0;">
    
    <h2>Membership Receipts</h2>
    <p>View, download, and email PDF receipts for completed membership payments.</p>
    
    <div class="wdta-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="wdta-receipts">
            
            <label for="year">Year:</label>
            <select name="year" id="year">
                <?php 
                $max_year = date('Y') + 1;
                for ($y = $max_year; $y >= WDTA_EARLIEST_YEAR; $y--): 
                ?>
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
                    // Validate user_id exists before using
                    if (!isset($receipt->user_id)) {
                        continue;
                    }
                    
                    $user_id = intval($receipt->user_id);
                    if ($user_id <= 0) {
                        continue;
                    }
                    
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
                            <button type="button" 
                                    class="button wdta-send-receipt-email" 
                                    data-user-id="<?php echo esc_attr($receipt->user_id); ?>"
                                    data-year="<?php echo esc_attr($receipt->membership_year); ?>"
                                    style="margin-left: 5px;">
                                Send Receipt Email
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
