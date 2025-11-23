# WDTA Membership Plugin - Installation Guide

## Quick Start

Follow these steps to get the WDTA Membership plugin up and running on your WordPress site.

## Step 1: Install the Plugin

### Option A: Upload via WordPress Admin
1. Log in to your WordPress admin panel
2. Navigate to **Plugins → Add New**
3. Click **Upload Plugin**
4. Choose the `wdta-membership.zip` file
5. Click **Install Now**
6. Click **Activate Plugin**

### Option B: Manual Installation
1. Upload the `wdta-membership` folder to `/wp-content/plugins/`
2. Navigate to **Plugins** in WordPress admin
3. Find "WDTA Membership" and click **Activate**

## Step 2: Configure Settings

1. Navigate to **WDTA Membership → Settings**
2. Configure the following:

### General Settings
- **Current Year Membership Price**: Set the price for new memberships (e.g., 50.00)
- **Next Year Membership Price**: Set the renewal price (e.g., 50.00)

### Email Settings
- **From Email**: Your organization's email (e.g., membership@wdta.org)
- **From Name**: Your organization name (e.g., WDTA)

### Inactive Users Report
- ✓ **Enable Report**: Check this box
- **Recipients**: Enter admin email addresses (comma-separated)
- **Email Subject**: "Inactive WDTA Members Report"

### Reminder Emails
Configure up to 3 reminders:

**Reminder 1** (30 days before deadline):
- ✓ **Enable**: Checked
- **Timing**: 30 days before December 31st
- **Subject**: "WDTA Membership Renewal Reminder"

**Reminder 2** (1 week before deadline):
- ✓ **Enable**: Checked
- **Timing**: 1 week before December 31st
- **Subject**: "WDTA Membership Expires Soon"

**Reminder 3** (1 week after deadline):
- ✓ **Enable**: Checked
- **Timing**: 1 week after December 31st
- **Subject**: "WDTA Membership Has Expired"

3. Click **Save Settings**

## Step 3: Create Membership Pages

### Create Registration Page
1. Navigate to **Pages → Add New**
2. Title: "Join WDTA" or "Membership Registration"
3. In the content area, add:
   ```
   [wdta_membership_form]
   ```
4. Set URL slug to something like `/join/` or `/register/`
5. Click **Publish**

### Create Renewal Page
1. Navigate to **Pages → Add New**
2. Title: "Renew Membership"
3. In the content area, add:
   ```
   [wdta_membership_renewal_form]
   ```
4. Set URL slug to something like `/renew/` or `/renewal/`
5. Click **Publish**

### Create Confirmation Page (Optional)
1. Navigate to **Pages → Add New**
2. Title: "Membership Confirmation"
3. Add content thanking users for their payment
4. Set URL slug to `/membership-confirmation/`
5. Click **Publish**

## Step 4: Add to Navigation Menu

1. Navigate to **Appearance → Menus**
2. Add your new pages to the menu:
   - Join WDTA (for non-members)
   - Renew Membership (for existing members)
3. Click **Save Menu**

## Step 5: Test the Setup

### Test Registration Form
1. Log out of WordPress
2. Visit your registration page (e.g., /join/)
3. Fill out the form with test data
4. Submit and verify:
   - User account is created
   - Membership is marked as active
   - Confirmation email is received

### Test Renewal Form
1. Log in with an existing user account
2. Visit your renewal page (e.g., /renew/)
3. Submit the form
4. Verify:
   - Membership for next year is activated
   - Confirmation email is received

### Test Admin Interface
1. Log in as an administrator
2. Navigate to **WDTA Membership → Dashboard**
3. Verify statistics are displaying correctly
4. Navigate to **WDTA Membership → Members**
5. Verify member list is showing correctly

## Step 6: Verify Cron Jobs

The plugin uses WordPress cron for automated tasks. Verify it's working:

1. Install the **WP Crontrol** plugin (optional but recommended)
2. Navigate to **Tools → Cron Events**
3. Look for `wdta_membership_daily_check` event
4. Verify it's scheduled to run daily

## Common Issues and Solutions

### Emails Not Sending
**Problem**: Confirmation or reminder emails aren't being received.

**Solutions**:
1. Install and configure **WP Mail SMTP** plugin
2. Verify "From Email" in settings is valid
3. Check your spam folder
4. Test with a different email provider

### Cron Jobs Not Running
**Problem**: Automated tasks (like status updates) aren't happening.

**Solutions**:
1. Verify WP Cron is not disabled in `wp-config.php`
2. Check for the line `define('DISABLE_WP_CRON', true);` and remove it
3. Consider setting up a real cron job for better reliability

### Page Not Found (404)
**Problem**: Shortcode pages show 404 error.

**Solutions**:
1. Navigate to **Settings → Permalinks**
2. Click **Save Changes** to flush rewrite rules
3. Try accessing the page again

### Membership Not Activating
**Problem**: After form submission, membership status remains inactive.

**Solutions**:
1. Check PHP error logs for issues
2. Verify database table was created (check phpMyAdmin)
3. Enable WordPress debugging in `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

## Advanced Configuration

### Custom Payment Gateway Integration

To integrate a real payment gateway (Stripe, PayPal, etc.):

1. Open `includes/class-wdta-membership-shortcodes.php`
2. Locate `ajax_register_and_pay()` and `ajax_renew_membership()` methods
3. Replace the manual payment code with your gateway's API calls
4. Update the payment_data array with actual transaction information

### Customizing Email Templates

Email templates are in `includes/class-wdta-membership-email.php`. You can:
1. Modify the HTML structure in `get_email_header()` and `get_email_footer()`
2. Customize message content in individual email methods
3. Add your logo by modifying the header HTML

### Database Customization

The membership table structure is defined in:
`includes/class-wdta-membership-database.php` → `create_tables()` method

## Security Considerations

1. **SSL Certificate**: Always use HTTPS for payment forms
2. **Regular Updates**: Keep WordPress and plugins updated
3. **Strong Passwords**: Enforce strong password policies
4. **Regular Backups**: Backup your database regularly
5. **Security Plugin**: Consider using a security plugin like Wordfence

## Next Steps

1. **Test thoroughly** with test accounts before going live
2. **Import existing members** if migrating from another system
3. **Set up payment gateway** if using real payment processing
4. **Customize email templates** to match your branding
5. **Train staff** on using the admin interface

## Support

For additional help:
- Review the full documentation in `/docs/README.md`
- Check WordPress error logs
- Review the code comments in each class file

## Checklist

Before going live, ensure:

- [ ] Plugin activated successfully
- [ ] Settings configured correctly
- [ ] Registration page created and working
- [ ] Renewal page created and working
- [ ] Navigation menu updated
- [ ] Emails sending properly
- [ ] Cron jobs scheduled
- [ ] Payment processing tested
- [ ] SSL certificate installed
- [ ] Backup system in place
- [ ] Staff trained on admin interface

## Congratulations!

Your WDTA Membership system is now ready to use. Members can register for the current year and renew for the next year, with automated status management and email reminders.
