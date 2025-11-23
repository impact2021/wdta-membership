# WDTA Membership Plugin Documentation

## Overview

The WDTA Membership plugin is a comprehensive membership management solution for WordPress. It allows users to register and pay for memberships, automatically manages membership status, and sends automated reminder emails.

## Features

- **User Registration with Payment**: Non-logged in users can create an account and pay for current year membership on the same page
- **Membership Renewal**: Logged-in users can pay for next year's membership
- **Automatic Status Management**: Members are either active or inactive (no grace period)
- **Automated Status Updates**: On January 1st, unpaid memberships automatically become inactive
- **Email Notifications**: Configurable reminder emails and inactive users reports
- **Admin Dashboard**: View membership statistics and manage settings
- **Member Management**: View all members and their status

## Installation

1. Upload the `wdta-membership` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to 'WDTA Membership' → 'Settings' to configure the plugin

## Configuration

### General Settings

- **Currency**: Set the currency for membership prices (default: USD)
- **Current Year Membership Price**: Price for new memberships in the current year
- **Next Year Membership Price**: Price for renewals for the next year

### Email Settings

- **From Email**: Email address used for sending notifications
- **From Name**: Name displayed in outgoing emails

### Inactive Users Report

- **Enable Report**: Toggle to enable/disable the inactive users report
- **Recipients**: Email addresses to receive the report (comma-separated)
- **Email Subject**: Subject line for the inactive users report

This report is automatically sent on January 1st and lists all users with inactive memberships.

### Reminder Emails

The plugin supports up to 3 configurable reminder emails. Each reminder can be:

- **Enabled/Disabled**: Use the checkbox to turn each reminder on or off
- **Timing**: Specify the number of days, weeks, or months
- **Period**: Choose "Before" or "After" December 31st
- **Subject**: Customize the email subject line

**Example Configurations:**

- Reminder 1: 30 days before December 31st (sent November 1st)
- Reminder 2: 1 week before December 31st (sent December 24th)
- Reminder 3: 1 week after December 31st (sent January 7th)

## Shortcodes

### [wdta_membership_form]

**Purpose**: Allows non-logged in users to create an account and pay for current year membership.

**Usage**:
```
[wdta_membership_form]
```

**Features**:
- User registration fields (username, email, password, first name, last name)
- Displays current year and membership price
- Processes payment and activates membership
- Automatically logs in the user after successful registration
- If a logged-in user visits the page, displays a message directing them to the renewal form

**Recommended Page Setup**:
1. Create a new page (e.g., "Join WDTA" or "Membership Registration")
2. Add the shortcode `[wdta_membership_form]`
3. Publish the page

### [wdta_membership_renewal_form]

**Purpose**: Allows logged-in users to pay for next year's membership.

**Usage**:
```
[wdta_membership_renewal_form]
```

**Features**:
- Only available to logged-in users
- Displays current year membership status
- Shows next year and renewal price
- Processes payment and activates membership for next year
- If not logged in, displays login prompt
- If already renewed, displays confirmation message

**Recommended Page Setup**:
1. Create a new page (e.g., "Renew Membership")
2. Add the shortcode `[wdta_membership_renewal_form]`
3. Publish the page

## Membership Status

### Active Status
A membership is considered **active** when:
- Payment has been received for the specified year
- The membership year is current or future

### Inactive Status
A membership becomes **inactive** when:
- No payment has been received for the specified year
- On January 1st, all unpaid memberships for the previous year are automatically set to inactive

### Important Notes
- **No Grace Period**: The plugin does not use a grace period system. Members are either active or inactive.
- **Automatic Deactivation**: On January 1st at midnight, the plugin automatically sets all unpaid memberships from the previous year to inactive.
- **Year-Based**: Each membership is tracked by year. A user can have different statuses for different years.

## Email System

### Types of Emails

#### 1. Payment Confirmation
Automatically sent when a payment is successfully processed.

**Contents**:
- Membership year
- Amount paid
- Payment date
- Thank you message

#### 2. Reminder Emails (x3)
Configurable reminders sent before or after December 31st.

**Configurable Options**:
- Enable/disable each reminder
- Timing (X days/weeks/months)
- Period (before/after December 31st)
- Subject line

**Default Configuration**:
- Reminder 1: 30 days before (November 1st)
- Reminder 2: 1 week before (December 24th)
- Reminder 3: 1 week after (January 7th)

#### 3. Inactive Users Report
Sent to administrators on January 1st.

**Contents**:
- Table of all inactive users
- User ID, name, email, and status
- Total count of inactive members

### Email Customization

All emails include:
- Customizable "From" name and email
- Professional HTML formatting
- Responsive design
- Site branding

## Admin Dashboard

### Dashboard Overview

Located at **WDTA Membership** → **Dashboard**

**Statistics**:
- Current year active memberships
- Inactive members needing renewal
- Next year renewals (early renewals)

**Quick Actions**:
- Access settings
- View member list
- Send inactive users report manually

### Settings Page

Located at **WDTA Membership** → **Settings**

Configure all plugin options including:
- Pricing
- Email settings
- Reminder email schedules
- Inactive users report settings

### Members Page

Located at **WDTA Membership** → **Members**

**Features**:
- View all users and their membership status
- Filter by year
- See payment dates and amounts
- Color-coded status badges (active/inactive)

## Automated Tasks

### Daily Cron Job

The plugin schedules a daily cron job that:
1. Checks if it's January 1st and deactivates unpaid memberships
2. Sends the inactive users report (if enabled)
3. Checks for and sends reminder emails based on configuration

### Cron Schedule

- **Frequency**: Once per day
- **Hook Name**: `wdta_membership_daily_check`
- **Automatic**: Runs automatically via WordPress cron system

## Payment Processing

### Current Implementation

The plugin currently uses a **manual payment** system where:
- Memberships are activated immediately upon form submission
- Transaction IDs are generated automatically
- Payment confirmations are sent

### Future Integration

The plugin is designed to be easily integrated with payment gateways such as:
- Stripe
- PayPal
- Square
- WooCommerce

To integrate a payment gateway, modify the AJAX handlers in `includes/class-wdta-membership-shortcodes.php`:
- `ajax_register_and_pay()` for new registrations
- `ajax_renew_membership()` for renewals

## Database Structure

### Table: wp_wdta_memberships

**Columns**:
- `id`: Primary key
- `user_id`: WordPress user ID
- `membership_year`: Year of membership (e.g., 2025)
- `status`: 'active' or 'inactive'
- `payment_date`: Date payment was received
- `payment_amount`: Amount paid
- `payment_method`: Payment method used
- `transaction_id`: Transaction identifier
- `created_at`: Record creation timestamp
- `updated_at`: Record update timestamp

**Indexes**:
- Primary key on `id`
- Index on `user_id`
- Index on `membership_year`
- Index on `status`
- Unique composite index on `user_id` and `membership_year`

## Best Practices

### Page Setup

1. **Create dedicated pages** for each shortcode
2. **Use descriptive URLs** (e.g., `/join/` and `/renew/`)
3. **Add navigation menu links** to make forms easily accessible
4. **Create a confirmation page** for post-payment redirects

### Email Configuration

1. **Test emails** before going live
2. **Use a professional email address** for the "From" field
3. **Set appropriate reminder timings** based on your renewal cycle
4. **Review email content** in the code if customization is needed

### Membership Management

1. **Regularly review** the members list
2. **Monitor active/inactive ratios** on the dashboard
3. **Check email deliverability** if reminders aren't being received
4. **Backup the database** regularly

## Troubleshooting

### Emails Not Sending

1. Check that reminder emails are enabled in settings
2. Verify the "From" email address is valid
3. Test WordPress email functionality with a plugin like WP Mail SMTP
4. Check that WP Cron is functioning correctly

### Cron Jobs Not Running

1. Verify WordPress cron is not disabled in `wp-config.php`
2. Consider using a real cron job instead of WP Cron for high-traffic sites
3. Use a plugin like WP Crontrol to manually trigger jobs for testing

### Memberships Not Deactivating

1. Ensure the date/time is correct on your server
2. Check that the daily cron job is running
3. Manually trigger the cron job from WP Crontrol

### Payment Gateway Integration Issues

1. Verify API credentials are correct
2. Check for SSL certificate on your site
3. Review gateway webhook settings
4. Enable WordPress debugging to see error messages

## Support and Development

### File Structure

```
wdta-membership/
├── assets/
│   ├── css/
│   │   ├── wdta-membership.css
│   │   └── wdta-admin.css
│   └── js/
│       └── wdta-membership.js
├── includes/
│   ├── class-wdta-membership-activator.php
│   ├── class-wdta-membership-admin.php
│   ├── class-wdta-membership-cron.php
│   ├── class-wdta-membership-database.php
│   ├── class-wdta-membership-email.php
│   ├── class-wdta-membership-shortcodes.php
│   └── class-wdta-membership-status.php
├── docs/
│   └── README.md
└── wdta-membership.php
```

### Key Classes

- **WDTA_Membership_Activator**: Plugin activation and deactivation
- **WDTA_Membership_Database**: Database operations
- **WDTA_Membership_Status**: Membership status management
- **WDTA_Membership_Email**: Email sending and formatting
- **WDTA_Membership_Shortcodes**: Shortcode rendering and AJAX handlers
- **WDTA_Membership_Admin**: Admin interface
- **WDTA_Membership_Cron**: Scheduled tasks

### Hooks and Filters

The plugin provides several hooks for customization:

**Actions**:
- `wdta_membership_daily_check`: Daily cron job
- `wdta_membership_after_activation`: After membership is activated
- `wdta_membership_after_deactivation`: After membership is deactivated

**Filters**:
- `wdta_membership_email_headers`: Modify email headers
- `wdta_membership_email_content`: Modify email content
- `wdta_membership_form_fields`: Modify registration form fields

## Changelog

### Version 1.0.0
- Initial release
- User registration with payment for current year
- Membership renewal for next year
- Active/inactive status only (no grace period)
- Automatic deactivation on January 1st
- Configurable reminder emails (3 reminders)
- Inactive users report
- Admin dashboard and settings
- Member management interface

## License

GPL v2 or later

## Credits

Developed for WDTA (Western Dance Teachers Association)
