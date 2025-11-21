<?php
/**
 * Documentation admin page
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>WDTA Membership Documentation</h1>
    
    <nav class="nav-tab-wrapper">
        <a href="#overview" class="nav-tab nav-tab-active" onclick="showTab('overview'); return false;">Overview</a>
        <a href="#installation" class="nav-tab" onclick="showTab('installation'); return false;">Installation</a>
        <a href="#configuration" class="nav-tab" onclick="showTab('configuration'); return false;">Configuration</a>
        <a href="#shortcodes" class="nav-tab" onclick="showTab('shortcodes'); return false;">Shortcodes</a>
        <a href="#user-roles" class="nav-tab" onclick="showTab('user-roles'); return false;">User Roles</a>
        <a href="#troubleshooting" class="nav-tab" onclick="showTab('troubleshooting'); return false;">Troubleshooting</a>
    </nav>
    
    <div id="overview" class="tab-content" style="display:block;">
        <h2>Overview</h2>
        <p>The WDTA Membership plugin manages annual memberships for Workplace Drug Testing Australia with payment processing, access control, and automated email reminders.</p>
        
        <h3>Features</h3>
        <ul>
            <li><strong>Payment Processing:</strong> Accept payments via Stripe ($970.90 AUD with 2.2% surcharge) or bank transfer ($950 AUD)</li>
            <li><strong>Access Control:</strong> Restrict specific pages to active members only</li>
            <li><strong>Automated Emails:</strong> 8 scheduled reminders from December 1st to March 31st deadline</li>
            <li><strong>User Role Management:</strong> Automatic role assignment based on membership status</li>
            <li><strong>Admin Dashboard:</strong> Comprehensive membership management interface</li>
            <li><strong>Custom Login Page:</strong> Branded login experience at <code>/member-login/</code></li>
        </ul>
        
        <h3>Membership Timeline</h3>
        <ul>
            <li><strong>January 1st:</strong> Membership year begins</li>
            <li><strong>January 1 - December 31:</strong> Active membership period</li>
            <li><strong>January 1 - March 31:</strong> Grace period for payment</li>
            <li><strong>March 31st:</strong> Final payment deadline</li>
            <li><strong>November 1st onwards:</strong> Members can pay for next year in advance</li>
        </ul>
    </div>
    
    <div id="installation" class="tab-content" style="display:none;">
        <h2>Installation</h2>
        
        <h3>Standard Installation</h3>
        <ol>
            <li>Upload the <code>wdta-membership</code> folder to <code>/wp-content/plugins/</code></li>
            <li>Activate the plugin through the 'Plugins' menu in WordPress</li>
            <li><strong>IMPORTANT:</strong> Go to <strong>Settings → Permalinks</strong> and click "Save Changes" to flush rewrite rules</li>
            <li>Configure the plugin in <strong>WDTA Membership → Settings</strong></li>
        </ol>
        
        <h3>Using Git Updater</h3>
        <p>If using Git Updater, make sure to:</p>
        <ol>
            <li>Configure Git Updater to use branch: <code>copilot/add-membership-access-plugin</code></li>
            <li>After installation/update, go to <strong>Settings → Permalinks</strong> and click "Save Changes"</li>
        </ol>
        
        <h3>Development Installation (Symlink)</h3>
        <pre>
# Clone repository
git clone https://github.com/impact2021/wdta-membership.git

# Create symlink to WordPress
ln -s /path/to/wdta-membership /path/to/wordpress/wp-content/plugins/wdta-membership

# Activate in WordPress Admin
# Then flush permalinks in Settings → Permalinks
        </pre>
        
        <p><strong>Note:</strong> After activation, user roles (Active Member, Inactive, Grace Period) are automatically created.</p>
    </div>
    
    <div id="configuration" class="tab-content" style="display:none;">
        <h2>Configuration</h2>
        
        <h3>Stripe Settings</h3>
        <ol>
            <li>Go to <strong>WDTA Membership → Settings</strong></li>
            <li>Enter your Stripe Public Key, Secret Key, and Webhook Secret</li>
            <li>In Stripe Dashboard, create a webhook pointing to: <code><?php echo home_url('/wp-json/wdta-membership/v1/stripe-webhook'); ?></code></li>
            <li>Subscribe to event: <code>checkout.session.completed</code></li>
        </ol>
        
        <h3>Bank Transfer Settings</h3>
        <ol>
            <li>Enter your bank account details in Settings</li>
            <li>Members can submit bank transfer references</li>
            <li>Approve payments from <strong>All Memberships</strong> page</li>
        </ol>
        
        <h3>Access Control</h3>
        <ol>
            <li>In Settings, select which pages should be restricted to active members</li>
            <li>Choose an "Access Denied" page to show non-members</li>
            <li>Admins always have access for testing</li>
        </ol>
        
        <h3>Email Templates</h3>
        <ol>
            <li>Customize all 8 automated email templates in Settings</li>
            <li>Edit subject lines and message content using the WYSIWYG editor</li>
            <li>Available placeholders: <code>{user_name}</code>, <code>{user_email}</code>, <code>{year}</code>, <code>{amount}</code>, <code>{deadline}</code>, <code>{renewal_url}</code>, <code>{site_name}</code></li>
        </ol>
    </div>
    
    <div id="shortcodes" class="tab-content" style="display:none;">
        <h2>Shortcodes</h2>
        
        <h3>[wdta_membership_form]</h3>
        <p>Displays the membership payment form with Stripe and bank transfer options.</p>
        <p><strong>Usage:</strong> Add to any page or post</p>
        <pre>[wdta_membership_form]</pre>
        <p><strong>Features:</strong></p>
        <ul>
            <li>Inline Stripe payment with Elements (no redirect)</li>
            <li>Clear pricing breakdown with 2.2% surcharge for Stripe</li>
            <li>Bank transfer option</li>
            <li>Year selector (appears from November 1st for next year payment)</li>
            <li>Smart logic: current members only see next year option from November</li>
        </ul>
        
        <h3>[wdta_membership_status]</h3>
        <p>Shows the current user's membership status and payment information.</p>
        <p><strong>Usage:</strong> Add to member dashboard or profile page</p>
        <pre>[wdta_membership_status]</pre>
        <p><strong>Displays:</strong></p>
        <ul>
            <li>Current membership year</li>
            <li>Payment status</li>
            <li>Valid until date (December 31st shown to members)</li>
            <li>Payment method and amount</li>
            <li>Option to pay for next year (from November onwards)</li>
        </ul>
        
        <h3>Custom Login Page</h3>
        <p>The custom login page doesn't use a shortcode. It's automatically available at:</p>
        <ul>
            <li><strong>Login:</strong> <code><?php echo home_url('/member-login/'); ?></code></li>
            <li><strong>Lost Password:</strong> <code><?php echo home_url('/member-login/lost-password/'); ?></code></li>
        </ul>
        <p>The plugin automatically redirects <code>wp-login.php</code> to the custom branded login page.</p>
    </div>
    
    <div id="user-roles" class="tab-content" style="display:none;">
        <h2>User Roles</h2>
        
        <p>The plugin automatically manages user roles based on membership status. Roles are updated when:</p>
        <ul>
            <li>A payment is completed (Stripe or bank transfer approved)</li>
            <li>Daily at midnight via automated cron job</li>
            <li>Membership expires</li>
        </ul>
        
        <h3>Role Types</h3>
        
        <h4>Active Member</h4>
        <ul>
            <li><strong>Status:</strong> Paid and current</li>
            <li><strong>Period:</strong> January 1 - December 31 of membership year</li>
            <li><strong>Access:</strong> Full access to all restricted pages</li>
            <li><strong>Triggers:</strong> Payment completed before Dec 31st</li>
        </ul>
        
        <h4>Grace Period Member</h4>
        <ul>
            <li><strong>Status:</strong> Past Dec 31st but before March 31st deadline</li>
            <li><strong>Period:</strong> January 1 - March 31</li>
            <li><strong>Access:</strong> Full access to restricted pages (internal tracking only)</li>
            <li><strong>Triggers:</strong> Active membership with valid until date after Dec 31st</li>
            <li><strong>Note:</strong> Members don't see "grace period" label - they see "Valid Until December 31"</li>
        </ul>
        
        <h4>Inactive Member</h4>
        <ul>
            <li><strong>Status:</strong> No current membership or expired</li>
            <li><strong>Access:</strong> No access to restricted pages</li>
            <li><strong>Triggers:</strong>
                <ul>
                    <li>Payment pending or not made</li>
                    <li>Expired after March 31st</li>
                    <li>Payment rejected</li>
                </ul>
            </li>
        </ul>
        
        <h3>Role Management</h3>
        <ul>
            <li><strong>Administrators:</strong> Roles never change (always keep admin access)</li>
            <li><strong>New users:</strong> Start as Inactive Member</li>
            <li><strong>Automatic updates:</strong> Roles update daily at midnight</li>
            <li><strong>Manual update:</strong> Roles update immediately when payment is approved</li>
        </ul>
    </div>
    
    <div id="troubleshooting" class="tab-content" style="display:none;">
        <h2>Troubleshooting</h2>
        
        <h3>Custom Login Page Shows 404</h3>
        <p><strong>Problem:</strong> <code>/member-login/</code> shows a 404 error</p>
        <p><strong>Solution:</strong></p>
        <ol>
            <li>Go to <strong>Settings → Permalinks</strong></li>
            <li>Click <strong>"Save Changes"</strong> (don't change anything)</li>
            <li>This flushes WordPress permalinks and registers the custom login route</li>
        </ol>
        <p><strong>Why:</strong> Custom login uses WordPress rewrite rules. When installing via symlink or Git Updater, activation hooks may not fire properly.</p>
        
        <h3>Approved Bank Transfer Not Granting Access</h3>
        <p><strong>Problem:</strong> Member paid via bank transfer, admin approved it, but member still can't access restricted pages</p>
        <p><strong>Solution:</strong></p>
        <ol>
            <li>Check if user role updated to "Active Member" (in Users list)</li>
            <li>If not, go to membership record and click "Approve" again</li>
            <li>Verify the payment status is "completed" and status is "active"</li>
            <li>Member may need to log out and log back in</li>
        </ol>
        
        <h3>Email Reminders Not Sending</h3>
        <p><strong>Problem:</strong> Automated emails aren't being sent on schedule</p>
        <p><strong>Solution:</strong></p>
        <ol>
            <li>Verify WordPress cron is working: Install "WP Crontrol" plugin to check</li>
            <li>Check that events are scheduled: Look for <code>wdta_daily_email_check</code></li>
            <li>Verify email settings in <strong>WDTA Membership → Settings</strong></li>
            <li>Test your server's email functionality</li>
        </ol>
        
        <h3>Stripe Webhook Not Working</h3>
        <p><strong>Problem:</strong> Stripe payments complete but membership not activated</p>
        <p><strong>Solution:</strong></p>
        <ol>
            <li>Verify webhook URL in Stripe Dashboard: <code><?php echo home_url('/wp-json/wdta-membership/v1/stripe-webhook'); ?></code></li>
            <li>Check webhook secret is correct in Settings</li>
            <li>Verify webhook is subscribed to <code>checkout.session.completed</code></li>
            <li>Check webhook logs in Stripe Dashboard for errors</li>
            <li>Ensure site is accessible from internet (not localhost)</li>
        </ol>
        
        <h3>User Role Not Updating</h3>
        <p><strong>Problem:</strong> User paid but still shows as "Inactive"</p>
        <p><strong>Solution:</strong></p>
        <ol>
            <li>Check membership record shows payment_status = "completed" and status = "active"</li>
            <li>Wait up to 24 hours for automatic daily role update</li>
            <li>Deactivate and reactivate plugin to trigger immediate role check</li>
            <li>Verify user is not an Administrator (admin roles don't change)</li>
        </ol>
        
        <h3>Date Format Issues</h3>
        <p><strong>Problem:</strong> Dates showing in wrong format</p>
        <p><strong>Solution:</strong></p>
        <p>The plugin uses dd/mm/yyyy format throughout. If you see different formats:</p>
        <ol>
            <li>Clear WordPress cache</li>
            <li>Check WordPress timezone setting matches your location</li>
            <li>Report to administrator if issue persists</li>
        </ol>
        
        <h3>Year Selector Not Appearing</h3>
        <p><strong>Problem:</strong> Can't see option to pay for next year</p>
        <p><strong>Solution:</strong></p>
        <ul>
            <li>Year selector only appears from November 1st onwards</li>
            <li>If you already paid for current year, you'll only see next year option</li>
            <li>Check your browser's date/time is correct</li>
        </ul>
        
        <h3>Need More Help?</h3>
        <p>For additional support:</p>
        <ul>
            <li>Check the WordPress debug log: Enable <code>WP_DEBUG</code> and <code>WP_DEBUG_LOG</code> in wp-config.php</li>
            <li>Review server error logs</li>
            <li>Contact your WordPress administrator</li>
        </ul>
    </div>
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
}
.tab-content h4 {
    margin-top: 20px;
    color: #2c3338;
}
.tab-content pre {
    background: #f0f0f1;
    padding: 15px;
    border-left: 4px solid #2271b1;
    overflow-x: auto;
}
.tab-content code {
    background: #f0f0f1;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: Consolas, Monaco, monospace;
}
.tab-content ul {
    line-height: 1.8;
}
</style>

<script>
function showTab(tabId) {
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
    document.getElementById(tabId).style.display = 'block';
    
    // Add active class to clicked nav tab
    event.target.classList.add('nav-tab-active');
}
</script>
