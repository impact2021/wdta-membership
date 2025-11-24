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
    event.target.classList.add('nav-tab-active');
}
</script>
