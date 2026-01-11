# WDTA Membership Plugin

A comprehensive WordPress membership plugin for WDTA (Workplace Drug Testing Australia) that manages annual memberships with payment processing, access control, and automated email reminders.

## Features

### 💳 Payment Processing
- **Stripe Integration**: Accept credit card payments securely
- **Bank Transfer Support**: Allow members to pay via bank transfer with admin verification
- **Annual Fee**: $950 AUD per year
- **PDF Receipts**: Automatic PDF receipt generation and email attachment upon membership activation

### 🔒 Access Control
- Restrict specific pages to active members only
- Automatic access revocation after grace period expires (March 31st)
- 3-month grace period (January 1 - March 31) for unpaid memberships
- Custom access denied pages
- Admin bypass for testing

### 📧 Automated Email Notifications
The plugin features a **dynamic email reminder system** that allows you to:
- **Add unlimited reminders**: Click "Add Another Reminder" to create as many reminders as needed
- **Flexible timing**: Configure each reminder to be sent X days or weeks BEFORE or AFTER membership expiry (Dec 31)
- **Enable/disable**: Each reminder has its own checkbox to turn it on or off
- **Customize content**: Full control over subject line and email body for each reminder
- **Default setup**: New installations start with 1 reminder (30 days before expiry)

**Example configurations:**
- 30 days BEFORE expiry (Dec 1st)
- 1 week BEFORE expiry (Dec 24th)
- 1 day AFTER expiry (Jan 1st)
- 2 weeks AFTER expiry (Jan 14th)

**Special emails:**
- **January 1st**: Inactive users report (sent to admins) - can be enabled/disabled separately

### 📄 PDF Receipts
- **Automatic Generation**: PDF receipts are automatically generated when membership is activated
- **Professional Design**: Receipts include WDTA logo, member details, payment breakdown, and receipt number
- **Email Attachment**: PDF receipts are automatically attached to confirmation emails
- **Receipt Structure**:
  - Receipt number (format: WDTA-YEAR-XXXXXX)
  - Payment date and method
  - Member information (name, email, membership year)
  - Payment breakdown (membership fee + processing fee if applicable)
  - Total amount paid

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

## Quick Start

1. Upload the `wdta-membership` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **WDTA Membership > Settings** to configure:
   - Stripe API keys (get from https://stripe.com)
   - Bank transfer details
   - Payment year cutoff date (default: November 1st)
   - Access control (restricted pages)
4. Go to **WDTA Membership > Emails** to configure:
   - Email reminder schedules
   - CC recipients for signup emails (default: marketing@wdta.org.au, treasurer@wdta.org.au)
   - CC recipients for reminder emails (default: marketing@wdta.org.au)
   - Inactive users report settings
5. Create pages with shortcodes: `[wdta_membership_form]` and `[wdta_membership_status]`

**To understand how the plugin works in detail**, see **HOW-IT-WORKS.md**

### Available Shortcodes

- `[wdta_membership_form]` - Display payment form and membership options
- `[wdta_membership_status]` - Show current membership status
- `[wdta_login_form]` - Display login form anywhere on your site

### Custom Login Page

The plugin creates a custom branded login page at `/member-login/` with:
- Professional gradient design
- Lost password functionality
- AJAX-powered login (no page refresh)
- Automatic redirect from wp-login.php

## Email Schedule & Configuration

### Dynamic Reminder System

The plugin uses WordPress Cron to send reminder emails automatically based on your configuration. Emails are sent at midnight (server time) on the calculated dates.

**Configuring Reminders:**
1. Go to **WDTA Membership > Emails**
2. Scroll to "Payment Reminder Emails" section
3. Each reminder can be configured with:
   - **Enable/Disable**: Checkbox to turn the reminder on or off
   - **Timing**: Number (e.g., 1, 7, 30)
   - **Unit**: Days or Weeks
   - **Period**: BEFORE or AFTER membership expires (Dec 31)
   - **Subject**: Email subject line
   - **Body**: Email content with placeholders

**Adding New Reminders:**
1. Click the "+ Add Another Reminder" button
2. Configure the timing, subject, and body
3. Click "Save Email Templates"

**Removing Reminders:**
1. Click "Remove Reminder" button on any reminder
2. Confirm the deletion
3. Click "Save Email Templates"

**Available Placeholders:**
- `{user_name}` - Member's display name
- `{user_email}` - Member's email address
- `{year}` - Membership year
- `{amount}` - Membership amount ($950 AUD)
- `{deadline}` - Payment deadline (December 31st)
- `{renewal_url}` - Link to membership form
- `{site_name}` - Website name

**Example Configurations:**
- **30 days before expiry**: Sends on December 1st (for next year's membership)
- **1 week before expiry**: Sends on December 24th
- **1 day after expiry**: Sends on January 1st (for current year's overdue members)
- **2 weeks after expiry**: Sends on January 14th

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
│   ├── class-wdta-pdf-receipt.php
│   ├── class-wdta-admin.php
│   ├── class-wdta-cron.php
│   └── lib-fpdf/
│       └── fpdf.php              # PDF generation library
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

### Version 3.7
- **NEW FEATURE**: Automatic PDF receipt generation for membership payments
- PDF receipts are automatically generated when membership is activated (both Stripe and bank transfer)
- Receipts include WDTA logo, member details, payment breakdown, and unique receipt number
- PDF receipts are attached to confirmation emails
- Receipt format: Professional A4 layout with all payment details
- Receipts stored temporarily and cleaned up after email is sent

### Version 3.4
- **CRITICAL FIX**: Fixed bug preventing grace period members from appearing in scheduled email lists
- Grace period members now correctly appear in email reminder schedules
- Root cause: Query was only checking for 'active' status in previous year, missing 'grace_period' status
- Fixed: Updated query to check for both 'active' OR 'grace_period' status in previous year memberships

### Version 3.3
- Fixed bug where administrators were included in scheduled email reminder lists
- Administrators now properly excluded from payment reminder emails
- Uses WordPress's built-in capability checking for more secure filtering

### Version 3.2
- Re-introduced grace period functionality for unpaid memberships
- Unpaid members move to grace_period status on Jan 1 (retain full access until Apr 1)
- Grace period members continue receiving reminder emails
- Grace period members become inactive on Apr 1 and lose access

### Version 3.1.0
- Fixed email scheduling for inactive users
- Users with inactive membership status now correctly receive reminder emails
- Comprehensive email reminder delivery to all unpaid members

### Version 3.0.0
- Fixed Edit button on admin memberships list page
- Improved JavaScript event handling
- Enhanced admin script loading

### Version 2.1.0
- Dynamic email reminder system
- Add/remove reminders functionality
- Flexible timing configuration

### Version 1.0.0
- Initial release
- Stripe payment integration
- Bank transfer support
- Automated email reminders
- Page access control
- Admin dashboard
- Member portal
