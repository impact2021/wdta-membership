<?php
/**
 * Admin settings template with tabs
 */

if (!defined('ABSPATH')) {
    exit;
}

$all_pages = get_pages();
$restricted_pages = get_option('wdta_restricted_pages', array());

// Get WordPress roles excluding administrator
$wp_roles = wp_roles();
$all_roles = $wp_roles->roles;
$user_roles = array();
foreach ($all_roles as $role_key => $role_data) {
    if ($role_key !== 'administrator') {
        $user_roles[$role_key] = $role_data['name'];
    }
}
?>

<div class="wrap">
    <h1>WDTA Membership Settings</h1>
    
    <?php settings_errors('wdta_settings'); ?>
    
    <nav class="nav-tab-wrapper">
        <a href="#access-control" class="nav-tab nav-tab-active" onclick="showTab('access-control', this); return false;">Access Control</a>
        <a href="#payment-settings" class="nav-tab" onclick="showTab('payment-settings', this); return false;">Payment Settings</a>
        <a href="#login-redirects" class="nav-tab" onclick="showTab('login-redirects', this); return false;">Login Redirects</a>
        <a href="#shortcodes" class="nav-tab" onclick="showTab('shortcodes', this); return false;">Shortcodes</a>
    </nav>
    
    <form method="post" action="">
        <?php wp_nonce_field('wdta_settings_action', 'wdta_settings_nonce'); ?>
        
        <!-- Tab 1: Access Control (Pages for Members Only) -->
        <div id="access-control" class="tab-content" style="display:block;">
            <h2>Access Control</h2>
            <p class="description">Configure which pages require active membership to access.</p>
            
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
        </div>
        
        <!-- Tab 2: Payment Settings -->
        <div id="payment-settings" class="tab-content" style="display:none;">
            <h2>Payment Settings</h2>
            
            <h3>Stripe Payment Settings</h3>
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
            
            <h3>Bank Transfer Settings</h3>
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
            
            <h3>Email Settings</h3>
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
        </div>
        
        <!-- Tab 3: Login Redirects -->
        <div id="login-redirects" class="tab-content" style="display:none;">
            <h2>Login Redirect URLs</h2>
            <p class="description">Configure where users are redirected after logging in based on their role. Leave blank to use WordPress default.</p>
            
            <table class="form-table">
                <?php foreach ($user_roles as $role_key => $role_name): ?>
                    <tr>
                        <th scope="row"><label for="wdta_redirect_<?php echo esc_attr($role_key); ?>"><?php echo esc_html($role_name); ?></label></th>
                        <td>
                            <?php
                            $current_redirect = get_option('wdta_login_redirect_' . $role_key, '');
                            ?>
                            <select name="wdta_login_redirect_<?php echo esc_attr($role_key); ?>" id="wdta_redirect_<?php echo esc_attr($role_key); ?>" class="regular-text">
                                <option value="">WordPress Default</option>
                                <option value="home" <?php selected($current_redirect, 'home'); ?>>Home Page</option>
                                <?php foreach ($all_pages as $page): ?>
                                    <option value="<?php echo esc_attr($page->ID); ?>" 
                                            <?php selected($current_redirect, $page->ID); ?>>
                                        <?php echo esc_html($page->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Redirect URL for <?php echo esc_html($role_name); ?> users after login</p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <!-- Tab 4: Shortcodes -->
        <div id="shortcodes" class="tab-content" style="display:none;">
            <h2>Available Shortcodes</h2>
            <p class="description">Copy and paste these shortcodes into any page or post.</p>
            
            <div class="wdta-shortcode-list">
                <div class="wdta-shortcode-item">
                    <h3>[wdta_membership_form]</h3>
                    <p><strong>Description:</strong> Displays the membership payment form with Stripe and bank transfer options.</p>
                    <p><strong>Usage:</strong></p>
                    <pre><code>[wdta_membership_form]</code></pre>
                    <p><strong>Features:</strong></p>
                    <ul>
                        <li>Inline Stripe payment with Elements (no redirect)</li>
                        <li>Clear pricing breakdown with 2.2% surcharge for Stripe</li>
                        <li>Bank transfer option with bank details</li>
                        <li>Year selector (appears from November 1st for next year payment)</li>
                        <li>Smart logic: current members only see next year option from November</li>
                        <li>Allows non-logged-in users to view pricing and payment options</li>
                    </ul>
                </div>
                
                <div class="wdta-shortcode-item">
                    <h3>[wdta_membership_status]</h3>
                    <p><strong>Description:</strong> Shows the current user's membership status and payment information.</p>
                    <p><strong>Usage:</strong></p>
                    <pre><code>[wdta_membership_status]</code></pre>
                    <p><strong>Displays:</strong></p>
                    <ul>
                        <li>Current membership year</li>
                        <li>Payment status (active, pending, expired)</li>
                        <li>Valid until date (December 31st shown to members)</li>
                        <li>Payment method and amount</li>
                        <li>Option to pay for next year (from November onwards)</li>
                    </ul>
                    <p><strong>Note:</strong> User must be logged in to see their status.</p>
                </div>
                
                <div class="wdta-shortcode-item">
                    <h3>[wdta_login_form]</h3>
                    <p><strong>Description:</strong> Displays a custom login form anywhere on your site.</p>
                    <p><strong>Usage:</strong></p>
                    <pre><code>[wdta_login_form]</code></pre>
                    <p><strong>Features:</strong></p>
                    <ul>
                        <li>Professional styled login form</li>
                        <li>Username and password fields</li>
                        <li>Remember me option</li>
                        <li>Lost password link</li>
                        <li>AJAX-powered (no page refresh)</li>
                        <li>Responsive mobile design</li>
                    </ul>
                    <p><strong>Example:</strong> Use this shortcode to add a login form to your homepage, sidebar, or any page where you want visitors to log in.</p>
                </div>
            </div>
            
            <h3>Custom Login Page (URL-based)</h3>
            <p>In addition to the shortcodes above, the plugin also provides dedicated pages at custom URLs:</p>
            <ul>
                <li><strong>Member Login:</strong> <code><?php echo home_url('/member-login/'); ?></code></li>
                <li><strong>Lost Password:</strong> <code><?php echo home_url('/member-login/lost-password/'); ?></code></li>
            </ul>
            <p>The plugin automatically redirects <code>wp-login.php</code> to the custom branded login page.</p>
        </div>
        
        <?php submit_button('Save Settings', 'primary', 'wdta_settings_submit'); ?>
    </form>
</div>

<style>
.tab-content {
    background: #fff;
    padding: 20px;
    margin-top: 0;
    border: 1px solid #ccd0d4;
    border-top: none;
}
.tab-content h2 {
    margin-top: 0;
}
.tab-content h3 {
    margin-top: 30px;
    color: #1d2327;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 10px;
}
.wdta-shortcode-item {
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-left: 4px solid #2271b1;
    padding: 20px;
    margin: 20px 0;
}
.wdta-shortcode-item h3 {
    margin-top: 0;
    color: #2271b1;
    border-bottom: none;
}
.wdta-shortcode-item pre {
    background: #f0f0f1;
    padding: 10px 15px;
    border-radius: 3px;
    overflow-x: auto;
}
.wdta-shortcode-item code {
    background: #f0f0f1;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: Consolas, Monaco, monospace;
    font-size: 13px;
}
.wdta-shortcode-item ul {
    line-height: 1.8;
}
</style>

<script>
function showTab(tabId, element) {
    // Hide all tabs
    var tabs = document.querySelectorAll('.tab-content');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].style.display = 'none';
    }
    
    // Remove active class from all nav tabs
    var navTabs = document.querySelectorAll('.nav-tab');
    for (var i = 0; i < navTabs.length; i++) {
        navTabs[i].classList.remove('nav-tab-active');
    }
    
    // Show selected tab
    var selectedTab = document.getElementById(tabId);
    if (selectedTab) {
        selectedTab.style.display = 'block';
    }
    
    // Add active class to clicked nav tab
    if (element) {
        element.classList.add('nav-tab-active');
    }
}
</script>
