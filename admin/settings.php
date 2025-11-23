<?php
/**
 * Admin settings template with tabs (Version 2.0)
 */

if (!defined('ABSPATH')) {
    exit;
}

$all_pages = get_pages();
$restricted_pages = get_option('wdta_restricted_pages', array());
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'payment';
?>

<div class="wrap">
    <h1>WDTA Membership Settings</h1>
    
    <?php settings_errors('wdta_settings'); ?>
    
    <h2 class="nav-tab-wrapper">
        <a href="?page=wdta-settings&tab=payment" class="nav-tab <?php echo $active_tab == 'payment' ? 'nav-tab-active' : ''; ?>">
            Payment Settings
        </a>
        <a href="?page=wdta-settings&tab=access" class="nav-tab <?php echo $active_tab == 'access' ? 'nav-tab-active' : ''; ?>">
            Access Control
        </a>
        <a href="?page=wdta-settings&tab=login" class="nav-tab <?php echo $active_tab == 'login' ? 'nav-tab-active' : ''; ?>">
            Login Redirects
        </a>
    </h2>
    
    <form method="post" action="">
        <?php wp_nonce_field('wdta_settings_action', 'wdta_settings_nonce'); ?>
        
        <?php if ($active_tab == 'payment'): ?>
            <!-- Payment Settings Tab -->
            <h2>Membership Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="wdta_membership_price">Annual Membership Price (AUD)</label></th>
                    <td>
                        <input type="number" id="wdta_membership_price" name="wdta_membership_price" 
                               value="<?php echo esc_attr(get_option('wdta_membership_price', '950.00')); ?>" 
                               class="regular-text" step="0.01" min="0">
                        <p class="description">Annual membership fee in Australian Dollars (default: $950.00 AUD)</p>
                    </td>
                </tr>
            </table>
            
            <h2>Stripe Payment Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="wdta_stripe_public_key">Stripe Publishable Key</label></th>
                    <td>
                        <input type="text" id="wdta_stripe_public_key" name="wdta_stripe_public_key" 
                               value="<?php echo esc_attr(get_option('wdta_stripe_public_key')); ?>" 
                               class="regular-text">
                        <p class="description">Your Stripe publishable key (starts with pk_)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="wdta_stripe_secret_key">Stripe Secret Key</label></th>
                    <td>
                        <input type="text" id="wdta_stripe_secret_key" name="wdta_stripe_secret_key" 
                               value="<?php echo esc_attr(get_option('wdta_stripe_secret_key')); ?>" 
                               class="regular-text">
                        <p class="description">Your Stripe secret key (starts with sk_)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="wdta_stripe_webhook_secret">Stripe Webhook Secret</label></th>
                    <td>
                        <input type="text" id="wdta_stripe_webhook_secret" name="wdta_stripe_webhook_secret" 
                               value="<?php echo esc_attr(get_option('wdta_stripe_webhook_secret')); ?>" 
                               class="regular-text">
                        <p class="description">Your Stripe webhook signing secret (starts with whsec_)</p>
                        <p class="description">Webhook URL: <code><?php echo rest_url('wdta/v1/stripe-webhook'); ?></code></p>
                    </td>
                </tr>
            </table>
            
            <h2>Bank Transfer Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="wdta_bank_name">Bank Name</label></th>
                    <td>
                        <input type="text" id="wdta_bank_name" name="wdta_bank_name" 
                               value="<?php echo esc_attr(get_option('wdta_bank_name')); ?>" 
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="wdta_bank_account_name">Account Name</label></th>
                    <td>
                        <input type="text" id="wdta_bank_account_name" name="wdta_bank_account_name" 
                               value="<?php echo esc_attr(get_option('wdta_bank_account_name')); ?>" 
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="wdta_bank_bsb">BSB</label></th>
                    <td>
                        <input type="text" id="wdta_bank_bsb" name="wdta_bank_bsb" 
                               value="<?php echo esc_attr(get_option('wdta_bank_bsb')); ?>" 
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="wdta_bank_account_number">Account Number</label></th>
                    <td>
                        <input type="text" id="wdta_bank_account_number" name="wdta_bank_account_number" 
                               value="<?php echo esc_attr(get_option('wdta_bank_account_number')); ?>" 
                               class="regular-text">
                    </td>
                </tr>
            </table>
            
            <h2>Email Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="wdta_email_from_name">From Name</label></th>
                    <td>
                        <input type="text" id="wdta_email_from_name" name="wdta_email_from_name" 
                               value="<?php echo esc_attr(get_option('wdta_email_from_name', get_bloginfo('name'))); ?>" 
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="wdta_email_from_address">From Email Address</label></th>
                    <td>
                        <input type="email" id="wdta_email_from_address" name="wdta_email_from_address" 
                               value="<?php echo esc_attr(get_option('wdta_email_from_address', get_option('admin_email'))); ?>" 
                               class="regular-text">
                    </td>
                </tr>
            </table>
            
        <?php elseif ($active_tab == 'access'): ?>
            <!-- Access Control Tab -->
            <h2>Access Control</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label>Restricted Pages</label></th>
                    <td>
                        <p class="description">Select pages that require active membership to access:</p>
                        <?php foreach ($all_pages as $page): ?>
                            <label style="display: block; margin: 5px 0;">
                                <input type="checkbox" name="wdta_restricted_pages[]" 
                                       value="<?php echo esc_attr($page->ID); ?>"
                                       <?php checked(in_array($page->ID, $restricted_pages)); ?>>
                                <?php echo esc_html($page->post_title); ?>
                            </label>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="wdta_access_denied_page">Access Denied Page</label></th>
                    <td>
                        <select name="wdta_access_denied_page" id="wdta_access_denied_page">
                            <option value="">Default Message</option>
                            <?php foreach ($all_pages as $page): ?>
                                <option value="<?php echo esc_attr($page->ID); ?>" 
                                        <?php selected(get_option('wdta_access_denied_page'), $page->ID); ?>>
                                    <?php echo esc_html($page->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Optional: Custom page to redirect users without access</p>
                    </td>
                </tr>
            </table>
            
        <?php elseif ($active_tab == 'login'): ?>
            <!-- Login Redirects Tab -->
            <h2>Login Redirect Settings</h2>
            <p class="description">Configure where users are redirected after login based on their role. Administrators always use the default WordPress dashboard.</p>
            
            <table class="form-table">
                <?php
                // Get all WordPress roles except administrator
                $roles = wp_roles()->roles;
                foreach ($roles as $role_key => $role_data):
                    if ($role_key === 'administrator') continue; // Skip administrators
                    ?>
                    <tr>
                        <th scope="row">
                            <label for="wdta_login_redirect_<?php echo esc_attr($role_key); ?>">
                                <?php echo esc_html($role_data['name']); ?> Redirect
                            </label>
                        </th>
                        <td>
                            <select name="wdta_login_redirect_<?php echo esc_attr($role_key); ?>" 
                                    id="wdta_login_redirect_<?php echo esc_attr($role_key); ?>" 
                                    class="regular-text">
                                <option value="">Default (/my-account/)</option>
                                <?php foreach ($all_pages as $page): ?>
                                    <option value="<?php echo esc_attr($page->ID); ?>" 
                                            <?php selected(get_option('wdta_login_redirect_' . $role_key), $page->ID); ?>>
                                        <?php echo esc_html($page->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">
                                Page to redirect <?php echo esc_html($role_data['name']); ?> users to after login. 
                                Leave empty to use default /my-account/ page.
                            </p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            
        <?php endif; ?>
        
        <?php submit_button('Save Settings', 'primary', 'wdta_settings_submit'); ?>
    </form>
</div>
