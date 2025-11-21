# WDTA Membership Plugin

A comprehensive WordPress membership plugin for WDTA (Workplace Drug Testing Australia) that manages annual memberships with payment processing, access control, and automated email reminders.

## Features

### 💳 Payment Processing
- **Stripe Integration**: Accept credit card payments securely
- **Bank Transfer Support**: Allow members to pay via bank transfer with admin verification
- **Annual Fee**: $950 AUD per year

### 🔒 Access Control
- Restrict specific pages to active members only
- Automatic access revocation after expiry (March 31st deadline)
- Custom access denied pages
- Admin bypass for testing

### 📧 Automated Email Notifications
The plugin sends automated reminder emails on the following schedule:
- **December 1st**: 1 month before due date
- **December 25th**: 1 week before due date
- **December 31st**: 1 day before due date
- **January 2nd**: 1 day overdue
- **January 8th**: 1 week overdue
- **January 31st**: End of first month
- **February 28th/29th**: End of second month
- **March 31st**: Final deadline (access revoked after this date)

### 🎛️ Admin Dashboard
- View all memberships by year and status
- Approve/reject bank transfer payments
- Configure Stripe and bank account details
- Manage restricted pages
- Customize email templates

### 📊 Membership Management
- Track payment status and history
- Member portal with status information
- Expiry date tracking
- Multiple payment method support

## Installation

1. Upload the `wdta-membership` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **WDTA Membership > Settings** to configure:
   - Stripe API keys
   - Bank account details
   - Email settings
   - Restricted pages

## Configuration

### Stripe Setup
1. Create a Stripe account at https://stripe.com
2. Get your API keys from the Stripe Dashboard
3. Enter your Publishable Key and Secret Key in the plugin settings
4. Set up a webhook endpoint pointing to: `https://yoursite.com/wp-json/wdta/v1/stripe-webhook`
5. Copy the webhook signing secret to the plugin settings

### Bank Transfer Setup
1. Enter your bank account details in the settings
2. These will be displayed to members choosing bank transfer
3. Admin must manually approve payments after verification

### Access Control
1. Select which pages require active membership
2. Optionally create a custom "Access Denied" page
3. Administrators always have access to test

## Usage

### For Members

**Shortcodes:**
- `[wdta_membership_form]` - Display payment form and membership options
- `[wdta_membership_status]` - Show current membership status

**Custom Login Page:**
The plugin creates a custom branded login page at `/member-login/` that replaces the default WordPress login. Features include:
- Professional gradient design
- Lost password functionality at `/member-login/lost-password/`
- AJAX-powered login (no page refresh)
- Remember me option
- Automatic redirect from wp-login.php

Members can log in at: `https://yoursite.com/member-login/`

### For Administrators

**Admin Menu:**
- **All Memberships**: View and manage all member payments
- **Settings**: Configure plugin options

**Approving Bank Transfers:**
1. Go to WDTA Membership > All Memberships
2. Find pending bank transfer payments
3. Verify the payment in your bank account
4. Click "Approve" to activate the membership

## Payment Flow

### Stripe Payment
1. Member clicks "Pay with Card"
2. Redirected to Stripe Checkout
3. Payment processed securely
4. Webhook confirms payment
5. Membership automatically activated
6. Confirmation email sent

### Bank Transfer
1. Member views bank account details
2. Makes transfer with reference
3. Submits payment details via form
4. Admin receives notification
5. Admin verifies and approves payment
6. Membership activated
7. Confirmation email sent

## Email Schedule

The plugin uses WordPress Cron to send reminder emails automatically. Emails are sent at midnight (server time) on the scheduled dates.

**Customization:**
Email templates can be customized in the Settings page. Available placeholders:
- `{user_name}` - Member's display name
- `{user_email}` - Member's email address
- `{year}` - Membership year
- `{amount}` - Membership amount ($950 AUD)
- `{deadline}` - Payment deadline (March 31st)
- `{renewal_url}` - Link to membership form
- `{site_name}` - Website name

## Database

The plugin creates a custom table: `wp_wdta_memberships`

**Fields:**
- `id` - Unique membership ID
- `user_id` - WordPress user ID
- `membership_year` - Year of membership
- `payment_amount` - Amount paid
- `payment_method` - 'stripe' or 'bank_transfer'
- `payment_status` - 'pending', 'completed', 'pending_verification', 'rejected'
- `payment_date` - Date payment completed
- `payment_reference` - Bank transfer reference
- `stripe_payment_id` - Stripe payment intent ID
- `expiry_date` - Membership expiry (March 31st)
- `status` - 'pending', 'active', 'expired', 'rejected'
- `created_at` - Record creation date
- `updated_at` - Last update date
- `notes` - Admin notes

## Technical Details

### WordPress Requirements
- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

### File Structure
```
wdta-membership/
├── wdta-membership.php          # Main plugin file
├── includes/
│   ├── class-wdta-membership.php
│   ├── class-wdta-database.php
│   ├── class-wdta-access-control.php
│   ├── class-wdta-payment-stripe.php
│   ├── class-wdta-payment-bank.php
│   ├── class-wdta-email-notifications.php
│   ├── class-wdta-admin.php
│   └── class-wdta-cron.php
├── admin/
│   ├── memberships-list.php
│   └── settings.php
├── templates/
│   ├── membership-form.php
│   └── membership-status.php
├── assets/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
└── README.md
```

### Hooks and Filters

**Actions:**
- `wdta_daily_email_check` - Daily cron for email notifications
- `wdta_daily_expiry_check` - Daily cron for membership expiry
- `wdta_membership_activated` - Fires when membership is activated
- `wdta_membership_expired` - Fires when membership expires

**Filters:**
- `wdta_membership_fee` - Filter the membership fee amount
- `wdta_restricted_pages` - Filter restricted page IDs
- `wdta_email_template` - Filter email templates

## Troubleshooting

### Custom Login Page Shows 404

If `/member-login/` shows a 404 error:

1. Go to **Settings → Permalinks** in WordPress admin
2. Click **"Save Changes"** (don't change anything, just click save)
3. This flushes WordPress permalinks and registers the custom login route
4. Now visit your login page at: `https://yoursite.com/member-login/`

**Why this happens:** The custom login page uses WordPress rewrite rules. When installing via symlink, Git Updater, or manual upload, the plugin activation hook may not fire, so permalinks need to be manually flushed.

**Note:** You only need to do this once after plugin installation or updates that add new routes.

### Approved Bank Transfer Not Granting Access

If a member's bank transfer was approved but they still can't access restricted pages:

1. Go to **WDTA Membership → All Memberships**
2. Find the member's record
3. Verify the status shows "Active"
4. Check the expiry date is in the future
5. Ask the member to log out and log back in
6. Clear any page caching plugins

### Email Reminders Not Sending

1. Check WordPress Cron is working: Install "WP Crontrol" plugin
2. Verify email server settings are correct
3. Check spam folders
4. Test email delivery with a WordPress mail tester plugin

### Stripe Webhook Not Working

1. Verify webhook endpoint: `https://yoursite.com/wp-json/wdta/v1/stripe-webhook`
2. Check webhook signing secret is entered correctly
3. View webhook logs in Stripe Dashboard
4. Ensure your site is accessible (not behind firewall/localhost)

### Date Format Issues

All dates are displayed in dd/mm/yyyy (Australian) format throughout the plugin. If you see different formats, clear your browser cache.

## Security

- All AJAX requests use WordPress nonces
- Database queries use prepared statements
- User input is sanitized and validated
- Admin capabilities required for sensitive operations
- Stripe webhook signature verification

## Support

For issues, questions, or feature requests, please contact the WDTA development team.

## License

This plugin is licensed under GPL v2 or later.

## Changelog

### Version 1.0.0
- Initial release
- Stripe payment integration
- Bank transfer support
- Automated email reminders
- Page access control
- Admin dashboard
- Member portal
