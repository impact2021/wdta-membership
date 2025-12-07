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
        <a href="#email-placeholders" class="nav-tab" onclick="showTab('email-placeholders'); return false;">Email Placeholders</a>
        <a href="#renewal-settings" class="nav-tab" onclick="showTab('renewal-settings'); return false;">Renewal Settings</a>
    </nav>
    
    <div id="overview" class="tab-content" style="display:block;">
        <h2>Overview</h2>
        <p>The WDTA Membership plugin manages annual memberships for Workplace Drug Testing Australia with payment processing, access control, and automated email reminders.</p>
        
        <h3>Key Features</h3>
        <ul>
            <li><strong>Payment Processing:</strong> Accept payments via Stripe ($970.90 AUD with 2.2% surcharge) or bank transfer ($950 AUD)</li>
            <li><strong>Access Control:</strong> Restrict specific pages to active members only</li>
            <li><strong>Automated Emails:</strong> Flexible email reminders that can be customized and scheduled before/after December 31st</li>
            <li><strong>User Role Management:</strong> Automatic role assignment based on membership status (active/inactive)</li>
            <li><strong>Custom Login Page:</strong> Branded login experience at <code>/member-login/</code></li>
        </ul>
        
        <h3>Membership Timeline</h3>
        <ul>
            <li><strong>January 1st:</strong> Membership year begins</li>
            <li><strong>January 1 - December 31:</strong> Active membership period</li>
            <li><strong>December 31st:</strong> Payment deadline - memberships become inactive on January 1st if unpaid</li>
            <li><strong>Binary Status:</strong> Memberships are either active or inactive (no grace period)</li>
        </ul>

        <h3>Shortcodes</h3>
        <ul>
            <li><code>[wdta_membership_form]</code> - Display payment form and membership options</li>
            <li><code>[wdta_membership_status]</code> - Show current membership status</li>
            <li><code>[wdta_login_form]</code> - Display login form anywhere on your site</li>
        </ul>
    </div>
    
    <div id="email-placeholders" class="tab-content" style="display:none;">
        <h2>Email Placeholders Reference</h2>
        
        <p class="description">Use these placeholders in your email templates to automatically insert personalized information. Placeholders use single curly braces like <code>{placeholder_name}</code>.</p>
        
        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
            <strong>Note:</strong> All placeholders use single curly braces <code>{}</code>, not double braces <code>{{}}</code>.
        </div>
        
        <h3>Available Placeholders</h3>
        
        <table class="widefat" style="margin: 20px 0;">
            <thead>
                <tr>
                    <th style="width: 25%;">Placeholder</th>
                    <th style="width: 40%;">Description</th>
                    <th style="width: 35%;">Example Output</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>{user_name}</code></td>
                    <td>The member's full display name</td>
                    <td>John Smith</td>
                </tr>
                <tr>
                    <td><code>{user_email}</code></td>
                    <td>The member's email address</td>
                    <td>john.smith@example.com</td>
                </tr>
                <tr>
                    <td><code>{year}</code></td>
                    <td>The membership year</td>
                    <td>2025</td>
                </tr>
                <tr>
                    <td><code>{amount}</code></td>
                    <td>The membership fee amount in AUD (without $ symbol)</td>
                    <td>950.00</td>
                </tr>
                <tr>
                    <td><code>{deadline}</code></td>
                    <td>The payment deadline date (formatted)</td>
                    <td>December 31, 2025</td>
                </tr>
                <tr>
                    <td><code>{renewal_url}</code></td>
                    <td>Link to the membership renewal/payment page</td>
                    <td>https://yoursite.com/membership</td>
                </tr>
                <tr>
                    <td><code>{site_name}</code></td>
                    <td>Your WordPress site name</td>
                    <td>WDTA - Workplace Drug Testing Australia</td>
                </tr>
            </tbody>
        </table>
        
        <h3>Usage Examples</h3>
        
        <div style="background: #f0f0f1; border-left: 4px solid #2271b1; padding: 15px; margin: 20px 0;">
            <h4 style="margin-top: 0;">Example 1: Simple Reminder</h4>
            <pre style="background: #fff; padding: 15px; border-radius: 3px; overflow-x: auto;">Dear {user_name},

Your WDTA membership for {year} is due soon. 
The annual fee is ${amount} AUD.

Renew here: {renewal_url}

Best regards,
{site_name}</pre>
            <p><strong>This would render as:</strong></p>
            <pre style="background: #fff; padding: 15px; border-radius: 3px; overflow-x: auto;">Dear John Smith,

Your WDTA membership for 2025 is due soon. 
The annual fee is $950.00 AUD.

Renew here: https://yoursite.com/membership

Best regards,
WDTA - Workplace Drug Testing Australia</pre>
        </div>
        
        <div style="background: #f0f0f1; border-left: 4px solid #2271b1; padding: 15px; margin: 20px 0;">
            <h4 style="margin-top: 0;">Example 2: Overdue Notice</h4>
            <pre style="background: #fff; padding: 15px; border-radius: 3px; overflow-x: auto;">Hello {user_name},

Your membership payment for {year} was due on {deadline}.
Please pay ${amount} AUD as soon as possible.

Member email: {user_email}
Payment link: {renewal_url}

Thank you,
{site_name}</pre>
            <p><strong>This would render as:</strong></p>
            <pre style="background: #fff; padding: 15px; border-radius: 3px; overflow-x: auto;">Hello John Smith,

Your membership payment for 2025 was due on December 31, 2025.
Please pay $950.00 AUD as soon as possible.

Member email: john.smith@example.com
Payment link: https://yoursite.com/membership

Thank you,
WDTA - Workplace Drug Testing Australia</pre>
        </div>
        
        <h3>Where to Use Placeholders</h3>
        <p>You can use these placeholders in the following areas:</p>
        <ul>
            <li><strong>Email Templates</strong> (WDTA Membership → Emails):
                <ul>
                    <li>Stripe Payment Confirmation emails</li>
                    <li>Bank Transfer Pending/Approved emails</li>
                    <li>Payment Reminder emails</li>
                </ul>
            </li>
            <li>Both email subjects and email bodies support placeholders</li>
            <li>The <code>{renewal_url}</code> can be configured in Settings → Payment Settings</li>
        </ul>
        
        <h3>Tips for Using Placeholders</h3>
        <div style="background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0;">
            <ul style="margin: 0;">
                <li><strong>Personalization:</strong> Use <code>{user_name}</code> to make emails feel personal and direct</li>
                <li><strong>Clear CTAs:</strong> Always include <code>{renewal_url}</code> so members know where to pay</li>
                <li><strong>Transparency:</strong> Include <code>{amount}</code> so members know exactly what to pay</li>
                <li><strong>Context:</strong> Use <code>{year}</code> and <code>{deadline}</code> to clarify which membership period</li>
                <li><strong>Consistency:</strong> Keep the same placeholders across all your email templates for a cohesive experience</li>
            </ul>
        </div>
        
        <h3>Formatting Notes</h3>
        <ul>
            <li>Placeholders are case-sensitive and must be in lowercase</li>
            <li>Use single curly braces: <code>{user_name}</code> ✓  not <code>{{user_name}}</code> ✗</li>
            <li>Placeholders work in both plain text and HTML formatted content</li>
            <li>Line breaks in your template will be preserved in the email</li>
            <li>The WordPress editor may add HTML formatting; placeholders will still work</li>
        </ul>
        
        <div style="background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0;">
            <h4 style="margin-top: 0;">Pro Tip</h4>
            <p style="margin-bottom: 0;">Create a test email template and send it to yourself first to verify all placeholders are rendering correctly before enabling it for all members.</p>
        </div>
    </div>
    
    <div id="renewal-settings" class="tab-content" style="display:none;">
        <h2>Setting When Renewals Change to Next Year</h2>
        
        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;">
            <strong>Important:</strong> This setting determines whether members are paying for the current year or next year's membership.
        </div>
        
        <h3>Payment Year Cutoff Date</h3>
        <p>The <strong>Payment Year Cutoff Date</strong> controls which year's membership members are purchasing when they make a payment. This is critical because WDTA memberships expire on December 31st each year.</p>
        
        <h3>How It Works</h3>
        <table class="widefat" style="margin: 20px 0;">
            <thead>
                <tr>
                    <th style="width: 40%;">Payment Date</th>
                    <th style="width: 60%;">Membership Year Purchased</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Before the cutoff date</strong></td>
                    <td>Payment is for the <strong>current year's</strong> membership (expires Dec 31 of current year)</td>
                </tr>
                <tr>
                    <td><strong>After the cutoff date</strong></td>
                    <td>Payment is for the <strong>next year's</strong> membership (expires Dec 31 of next year)</td>
                </tr>
            </tbody>
        </table>
        
        <h3>Default Setting</h3>
        <p>The default cutoff date is <strong>November 1st</strong>. This means:</p>
        <ul>
            <li><strong>January 1 to October 31:</strong> Members pay for the current year</li>
            <li><strong>November 1 to December 31:</strong> Members pay for the next year</li>
        </ul>
        
        <div style="background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0;">
            <h4 style="margin-top: 0;">Example Scenario</h4>
            <p style="margin-bottom: 0;">On <strong>November 15, 2024</strong>:</p>
            <ul style="margin-top: 10px; margin-bottom: 0;">
                <li>A member making a payment is purchasing membership for <strong>2025</strong> (expires Dec 31, 2025)</li>
                <li>This allows active members to renew early for the following year</li>
                <li>The member gains immediate access to restricted content through December 31, 2025</li>
            </ul>
        </div>
        
        <h3>How to Change the Cutoff Date</h3>
        <p>To modify when renewals switch from current year to next year:</p>
        <ol>
            <li>Go to <strong>WDTA Membership → Settings</strong></li>
            <li>Scroll to the <strong>"Membership Year Settings"</strong> section</li>
            <li>Find <strong>"Payment Year Cutoff Date"</strong></li>
            <li>Select your desired month and day</li>
            <li>Click <strong>"Save Settings"</strong></li>
        </ol>
        
        <h3>Common Cutoff Date Configurations</h3>
        <table class="widefat" style="margin: 20px 0;">
            <thead>
                <tr>
                    <th style="width: 30%;">Cutoff Date</th>
                    <th style="width: 35%;">Advance Renewal Period</th>
                    <th style="width: 35%;">Best For</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>October 1st</strong></td>
                    <td>3 months early renewal</td>
                    <td>Maximum advance payment time</td>
                </tr>
                <tr>
                    <td><strong>November 1st</strong> (Default)</td>
                    <td>2 months early renewal</td>
                    <td>Balanced approach - most common</td>
                </tr>
                <tr>
                    <td><strong>December 1st</strong></td>
                    <td>1 month early renewal</td>
                    <td>Minimal confusion, shorter advance period</td>
                </tr>
                <tr>
                    <td><strong>December 15th</strong></td>
                    <td>2 weeks early renewal</td>
                    <td>Last-minute renewals only</td>
                </tr>
            </tbody>
        </table>
        
        <h3>Why This Matters</h3>
        <ul>
            <li><strong>Prevents confusion:</strong> Members know exactly which year they're paying for</li>
            <li><strong>Early renewals:</strong> Active members can renew for next year in advance</li>
            <li><strong>Cash flow:</strong> Allows you to collect next year's revenue before year-end</li>
            <li><strong>Reduces Dec 31 rush:</strong> Spreads payment processing over a longer period</li>
            <li><strong>Planning:</strong> Better forecast next year's membership numbers</li>
        </ul>
        
        <h3>Important Notes</h3>
        <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0;">
            <ul style="margin: 0;">
                <li><strong>Already paid members:</strong> Current members who have already paid for the current year will only see the option to pay for next year after the cutoff date</li>
                <li><strong>No retroactive changes:</strong> Changing the cutoff date does not affect existing memberships</li>
                <li><strong>Binary status:</strong> There is no grace period - memberships are either active or inactive</li>
                <li><strong>Expiry date:</strong> All memberships expire on December 31st regardless of the cutoff date</li>
            </ul>
        </div>
        
        <h3>Recommended Email Reminders</h3>
        <p>Once you've set your cutoff date, configure email reminders accordingly:</p>
        <ol>
            <li>Go to <strong>WDTA Membership → Emails</strong></li>
            <li>Set up reminders that align with your cutoff date</li>
            <li>Example for November 1st cutoff:
                <ul>
                    <li>First reminder: 30 days before expiry (December 1)</li>
                    <li>Second reminder: 7 days before expiry (December 24)</li>
                    <li>Third reminder: 1 day after expiry (January 1)</li>
                </ul>
            </li>
        </ol>
        
        <h3>Testing Your Configuration</h3>
        <p>To verify your cutoff date is working correctly:</p>
        <ol>
            <li>Log in as a test user (not an admin)</li>
            <li>Visit the membership form page with <code>[wdta_membership_form]</code></li>
            <li>Check which year is displayed in the payment form</li>
            <li>Before cutoff date: Should show current year</li>
            <li>After cutoff date: Should show next year</li>
        </ol>
        
        <div style="background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0;">
            <h4 style="margin-top: 0;">Pro Tip</h4>
            <p style="margin-bottom: 0;">Most organizations find that <strong>November 1st</strong> works well as it gives members two months to renew for the next year, reducing the December rush while not starting renewals too early.</p>
        </div>
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
    if (event && event.target) {
        event.target.classList.add('nav-tab-active');
    } else {
        // If called without an event (e.g., on page load), find the matching nav link
        var matchingLink = document.querySelector('a[href="#' + tabId + '"]');
        if (matchingLink) {
            matchingLink.classList.add('nav-tab-active');
        }
    }
}

// Check URL hash on page load to show the correct tab
document.addEventListener('DOMContentLoaded', function() {
    var hash = window.location.hash;
    var urlParams = new URLSearchParams(window.location.search);
    var tabParam = urlParams.get('tab');
    
    // Support both hash and tab parameter
    var tabId = null;
    if (hash && hash.length > 1) {
        tabId = hash.substring(1); // Remove the # from hash
    } else if (tabParam) {
        tabId = tabParam;
    }
    
    // If a tab ID was found, show that tab
    if (tabId && document.getElementById(tabId)) {
        showTab(tabId);
    }
});
</script>
