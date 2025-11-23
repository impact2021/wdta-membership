# WDTA Membership Plugin - User Guide

## For Members

### How to Register (New Members)

1. **Visit the Registration Page**
   - Navigate to the "Join WDTA" or membership registration page
   - You must be logged out to access this form

2. **Fill Out the Registration Form**
   - **Username**: Choose a unique username (cannot be changed later)
   - **Email Address**: Your email address (used for login and notifications)
   - **Password**: Create a strong password
   - **First Name**: Your first name
   - **Last Name**: Your last name

3. **Review Membership Details**
   - The form shows the current year and membership price
   - Review the amount you'll be charged

4. **Accept Terms and Submit**
   - Check the "I agree to terms and conditions" box
   - Click "Create Account & Pay"

5. **Complete Payment**
   - Follow the payment instructions
   - You'll receive a confirmation email upon successful payment

6. **Account Created**
   - You'll be automatically logged in
   - Your membership for the current year is now active

### How to Renew (Existing Members)

1. **Log In**
   - Log in to your account using your username/email and password

2. **Visit the Renewal Page**
   - Navigate to the "Renew Membership" page

3. **Review Renewal Information**
   - The form shows your current membership status
   - See the renewal year and price

4. **Submit Renewal**
   - Check the "I agree to terms and conditions" box
   - Click "Renew Membership"

5. **Complete Payment**
   - Follow the payment instructions
   - You'll receive a confirmation email

6. **Renewal Complete**
   - Your membership for the next year is now active
   - You can verify this by returning to the renewal page

### Important Dates

- **Renewal Period**: You can renew for the next year at any time
- **Expiration Date**: December 31st of each year
- **Grace Period**: None - memberships become inactive on January 1st if not paid
- **Reminder Emails**: You'll receive reminders before December 31st

### Membership Status

**Active**
- You have paid for the current/next year
- You have access to all member benefits
- Status shown as green "Active"

**Inactive**
- Payment has not been received for the current year
- Limited or no access to member benefits
- Status shown as red "Inactive"

### Email Notifications

You will receive emails for:
1. **Payment Confirmation** - Immediately after successful payment
2. **Renewal Reminders** - Before December 31st (typically 30 days and 1 week before)
3. **Expiration Notices** - After December 31st if not renewed

### Frequently Asked Questions

**Q: When should I renew my membership?**
A: You can renew for the next year at any time. We recommend renewing before December 31st to avoid any interruption in service.

**Q: What happens if I don't renew by December 31st?**
A: Your membership will automatically become inactive on January 1st. You can still renew after this date.

**Q: Can I renew for multiple years at once?**
A: Currently, the system only allows renewal for the next year.

**Q: I forgot my password. How do I reset it?**
A: Use the WordPress password reset feature on the login page.

**Q: Can I change my email address?**
A: Yes, update it in your WordPress profile settings.

**Q: I didn't receive my confirmation email. What should I do?**
A: Check your spam folder. If still not received, contact an administrator.

## For Administrators

### Accessing the Admin Dashboard

1. Log in to WordPress as an administrator
2. Navigate to **WDTA Membership** in the admin menu
3. You'll see the main dashboard with statistics

### Dashboard Overview

The dashboard displays:
- **Current Year Active Members**: Count of active memberships for this year
- **Inactive Members**: Count of members who need to renew
- **Next Year Renewals**: Count of early renewals for next year

### Managing Settings

Navigate to **WDTA Membership → Settings**

#### General Settings
- Set membership prices for current and next year
- Configure currency (USD, EUR, etc.)

#### Email Configuration
- Set the "From" email address and name for all outgoing emails
- Configure inactive users report recipients
- Enable/disable and configure up to 3 reminder emails

#### Reminder Email Configuration

For each reminder:
1. **Enable/Disable**: Check/uncheck to turn on/off
2. **Timing**: Set the number (e.g., 30, 1, 7)
3. **Unit**: Choose days, weeks, or months
4. **Period**: Choose before or after December 31st
5. **Subject**: Set the email subject line

**Example Configurations:**
- 30 days before → Sent on November 1st
- 1 week before → Sent on December 24th
- 2 weeks after → Sent on January 14th

### Viewing Members

Navigate to **WDTA Membership → Members**

**Features:**
- View all users and their membership status
- Filter by year using the dropdown
- See payment dates and amounts
- Export data (if needed, use browser print/save as PDF)

**Status Indicators:**
- Green badge = Active membership
- Red badge = Inactive membership

### Manual Actions

#### Send Inactive Users Report Manually
1. Go to **WDTA Membership → Dashboard**
2. Click "Send Inactive Users Report"
3. The report will be sent immediately to configured recipients

#### View Specific Member Details
1. Go to **WDTA Membership → Members**
2. Note the member's ID or name
3. Navigate to **Users → All Users** for full profile

### Email Management

#### Inactive Users Report
- **Sent**: Automatically on January 1st
- **Frequency**: Once per year
- **Recipients**: Configured in settings
- **Content**: Table of all inactive members

#### Reminder Emails
- **Sent**: Based on your configuration
- **Frequency**: Once per configured period
- **Recipients**: All users with inactive memberships for next year
- **Content**: Renewal reminder with link to renewal page

### Status Management

#### Automatic Status Changes
- **January 1st**: All unpaid memberships from previous year → Inactive
- **Upon Payment**: Membership status → Active

#### Manual Status Updates
Currently, status updates are automatic. To manually adjust:
1. Access the database via phpMyAdmin
2. Navigate to `wp_wdta_memberships` table
3. Update the `status` field for the specific user/year

### Troubleshooting

#### Member Reports Not Receiving Emails
1. Check Settings → Email configuration
2. Verify "From Email" is valid
3. Check that reminders are enabled
4. Verify member's email address in WordPress Users

#### Cron Jobs Not Running
1. Install WP Crontrol plugin
2. Check if `wdta_membership_daily_check` is scheduled
3. Manually run the event to test
4. Consider setting up a real server cron job

#### Members Not Showing as Active
1. Go to Members page
2. Verify payment was recorded
3. Check database table `wp_wdta_memberships`
4. Verify `status` field is 'active'

#### Statistics Not Updating
1. Clear any caching plugins
2. Refresh the dashboard page
3. Check if database queries are working

### Best Practices

1. **Regular Monitoring**
   - Check dashboard weekly
   - Review member list monthly
   - Verify emails are sending

2. **Communication**
   - Keep members informed of renewal deadlines
   - Send additional reminders if needed
   - Respond promptly to member inquiries

3. **Data Management**
   - Export member lists periodically
   - Backup database regularly
   - Keep historical records

4. **Email Timing**
   - Test reminder emails before December
   - Adjust timing based on response rates
   - Monitor email deliverability

5. **Payment Processing**
   - Verify payments are recorded correctly
   - Reconcile with financial records
   - Follow up on failed payments

### Advanced Administrative Tasks

#### Bulk Status Updates
For mass updates (e.g., importing from old system):
1. Export users from old system
2. Use phpMyAdmin to bulk insert into `wp_wdta_memberships`
3. Ensure data format matches table structure

#### Custom Email Templates
To customize email templates:
1. Edit `includes/class-wdta-membership-email.php`
2. Modify methods for specific emails
3. Test thoroughly after changes

#### Reporting
To generate custom reports:
1. Use phpMyAdmin to query the database
2. Export results as CSV
3. Use spreadsheet software for analysis

### Security Checklist

- [ ] SSL certificate installed and active
- [ ] Strong passwords enforced
- [ ] Regular WordPress and plugin updates
- [ ] Database backups scheduled
- [ ] Security plugin installed
- [ ] User roles properly configured
- [ ] Admin accounts limited to trusted personnel

### Support Workflow

When members contact you:

1. **Account Issues**
   - Verify their user account exists
   - Check membership status in Members page
   - Reset password if needed

2. **Payment Issues**
   - Check payment record in database
   - Verify transaction ID if available
   - Manually update status if payment confirmed

3. **Email Issues**
   - Verify their email address
   - Check spam folder
   - Resend manually if needed

4. **Technical Issues**
   - Check WordPress error logs
   - Test the affected feature
   - Review recent changes to settings

### Monthly Checklist

- [ ] Review dashboard statistics
- [ ] Check for inactive members
- [ ] Verify email delivery
- [ ] Test registration form
- [ ] Test renewal form
- [ ] Review and respond to support requests
- [ ] Backup database
- [ ] Update plugin if new version available

### Yearly Checklist (December)

- [ ] Verify reminder emails are sending
- [ ] Send additional reminders if needed
- [ ] Prepare for January 1st status changes
- [ ] Review pricing for next year
- [ ] Update any documentation
- [ ] Plan improvements for next year

## Getting Help

### Documentation
- Full technical documentation: `/docs/README.md`
- Installation guide: `/docs/INSTALLATION.md`

### Support Resources
- Check WordPress error logs
- Review plugin code comments
- Contact your web developer

### Contact

For WDTA-specific support, contact your membership administrator.
