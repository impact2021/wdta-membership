# How It Works - WDTA Membership Plugin

This guide explains the key concepts and workflows of the WDTA Membership plugin so you can understand and effectively manage your membership system.

## Payment Year Cutoff Date

### What is the cutoff date?

The cutoff date determines **which year's membership** members are paying for when they make a payment. This is important because WDTA memberships are annual and expire on December 31st each year.

### How it works:

- **Before the cutoff date**: Payments are for the **current year's** membership (expiring Dec 31 of current year)
- **After the cutoff date**: Payments are for the **next year's** membership (expiring Dec 31 of next year)

### Default Setting:

The default cutoff date is **November 1st**. This means:
- From January 1 to October 31: Members pay for the current year
- From November 1 to December 31: Members pay for the next year

**Example:** On November 15, 2024:
- A member making a payment is purchasing membership for 2025 (expires Dec 31, 2025)
- This allows active members to renew early for the following year

### Changing the cutoff date:

Administrators can change this cutoff date:

1. Go to **WDTA Membership → Settings**
2. Scroll to **Membership Year Settings**
3. Find **"Payment Year Cutoff Date"**
4. Select the desired month and day
5. Click **Save Settings**

**Use cases for changing the cutoff:**
- Set to **October 1st** if you want members to renew 3 months early
- Set to **December 1st** if you want only 1 month advance renewal period
- The cutoff helps prevent confusion about which year members are paying for

---

## Multiple Reminder Emails

### Dynamic Reminder System

The plugin features a powerful, flexible reminder system that lets you create unlimited custom email reminders.

### How to access:

1. Go to **WDTA Membership → Emails**
2. Scroll to the **"Payment Reminder Emails"** section

### Creating reminders:

Each reminder can be configured with:

- **Enable/Disable checkbox**: Turn individual reminders on or off without deleting them
- **Timing**: A number (e.g., 1, 7, 30, 60)
- **Unit**: Days or weeks
- **Period**: BEFORE or AFTER membership expires (December 31)
- **Subject line**: The email subject with placeholder support
- **Email body**: Full HTML content with placeholders

### Adding a new reminder:

1. Click the **"+ Add Another Reminder"** button
2. Configure the timing (e.g., "7 days BEFORE expiry")
3. Write your subject and message
4. Enable the reminder checkbox
5. Click **"Save Email Templates"**

### Removing a reminder:

1. Click the **"Remove Reminder"** button on any reminder
2. Click **"Save Email Templates"**

### Example reminder configurations:

| Timing | Unit | Period | Actual Send Date | Purpose |
|--------|------|--------|------------------|---------|
| 30 | days | BEFORE | December 1 | Early renewal notice |
| 7 | days | BEFORE | December 24 | Last week reminder |
| 1 | days | BEFORE | December 30 | Final notice |
| 1 | days | AFTER | January 1 | Overdue notice |
| 1 | weeks | AFTER | January 7 | First overdue follow-up |
| 2 | weeks | AFTER | January 14 | Second overdue follow-up |
| 1 | months | AFTER | January 31 | Final overdue warning |

### Available placeholders:

Use these placeholders in your email subject and body:

- `{user_name}` - Member's display name
- `{user_email}` - Member's email address  
- `{year}` - Membership year
- `{amount}` - Membership amount ($950.00 AUD)
- `{deadline}` - Payment deadline (December 31)
- `{renewal_url}` - Link to membership renewal page
- `{site_name}` - Your website name

**Example email body:**
```
Dear {user_name},

This is a reminder that your WDTA membership for {year} will expire on {deadline}.

Please renew your membership of ${amount} AUD to continue accessing member benefits.

Renew now: {renewal_url}

Best regards,
{site_name}
```

### CC Recipients for Reminders:

In the **"Payment Reminder Emails"** section, you can specify CC recipients:

- **Default**: marketing@wdta.org.au
- **Customizable**: Add multiple email addresses separated by commas
- All reminder emails will CC these addresses automatically

---

## Manual Membership Management

Administrators have full control to manually adjust memberships when needed.

### Accessing membership management:

1. Go to **WDTA Membership → All Memberships**
2. You'll see a list of all membership records

### Filtering memberships:

- **By Year**: View memberships for a specific year
- **By Status**: Filter by Active, Pending, Expired, or Rejected
- Use the dropdown filters at the top of the list

### Manually adding a membership:

Sometimes you need to manually add a membership (e.g., for complimentary memberships, offline payments, or corrections).

1. Click **"Add Membership"** button
2. Fill in the form:
   - **User**: Select the member (or create new user first)
   - **Membership Year**: Select the year
   - **Payment Amount**: Enter amount (typically $950.00)
   - **Payment Method**: Choose Stripe, Bank Transfer, or Manual
   - **Payment Status**: Set to Completed for immediate activation
   - **Status**: Set to Active
   - **Notes**: Add any relevant notes for your records
3. Click **"Save Membership"**

### Editing an existing membership:

1. Find the membership in the list
2. Click the **"Edit"** button
3. Modify any fields as needed:
   - Change payment status
   - Update payment amount
   - Change expiry date
   - Add admin notes
4. Click **"Update Membership"**

### Approving bank transfers:

When a member submits bank transfer details:

1. You'll receive an email notification
2. Go to **WDTA Membership → All Memberships**
3. Filter by **Status: Pending**
4. Verify the payment in your bank account
5. Click **"Approve"** button next to the membership
6. The membership is activated and the member receives a confirmation email

### Rejecting payments:

If a bank transfer payment is invalid or insufficient:

1. Find the pending membership
2. Click **"Reject"**
3. Optionally add notes explaining the rejection
4. The member will need to submit a new payment

### Deleting memberships:

Use caution when deleting memberships as this is permanent:

1. Click **"Delete"** button
2. Confirm the deletion
3. The record is permanently removed

**Note**: It's usually better to reject a membership than delete it, as this maintains an audit trail.

---

## Inactive Users Email Report

### What is it?

On January 1st each year, the plugin can automatically send a report to administrators listing all members who don't have an active membership for the current year.

### Purpose:

- **Accountability**: Know which members haven't renewed
- **Follow-up**: Identify members needing personal outreach
- **Planning**: Understand membership retention rates
- **Records**: Document inactive members for your records

### How it works:

1. **Timing**: Automatically runs at midnight on January 1st (using WordPress Cron)
2. **Content**: Lists all users without an active/paid membership for the new year
3. **Recipients**: Sent to configured admin email addresses
4. **Format**: HTML table with User ID, Name, Email, and Role

### Enabling/Disabling:

1. Go to **WDTA Membership → Emails**
2. Find **"Inactive Users Report"** at the top
3. Check/uncheck **"Send inactive users report on January 1st"**
4. Click **"Save Email Templates"**

### Configuring recipients:

1. In the same section, find **"Report Recipients"**
2. Enter comma-separated email addresses
3. Example: `admin@wdta.org.au, treasurer@wdta.org.au, manager@wdta.org.au`
4. All specified addresses will receive the report

### What the report includes:

The email contains a table with:
- **User ID**: WordPress user ID number
- **Name**: Member's display name
- **Email**: Member's email address
- **Role**: WordPress user role (e.g., Member, Subscriber)

Plus a total count of inactive members.

### Manual report generation:

While the report runs automatically on Jan 1st, you can also generate it manually:

1. Install the "WP Crontrol" plugin
2. Go to **Tools → Cron Events**
3. Find the `wdta_daily_email_check` event
4. Click **"Run Now"**
5. The system will send the report if today is January 1st

---

## Signup Email Notifications

### What are signup emails?

These are confirmation emails sent when a member successfully pays and activates their membership.

### Types of signup emails:

1. **Stripe Payment Confirmation**: Sent immediately when Stripe processes a credit card payment
2. **Bank Transfer Approval**: Sent when an admin approves a bank transfer payment

### CC Recipients for Signup Emails:

1. Go to **WDTA Membership → Emails**
2. Find **"Signup Email CC Recipients"** under **Welcome & Confirmation Emails**
3. **Default**: marketing@wdta.org.au, treasurer@wdta.org.au
4. **Customizable**: Add or remove email addresses (comma-separated)
5. All signup/payment confirmation emails will CC these addresses

### Why CC signup emails?

- **Marketing team**: Track new and renewed memberships for campaigns
- **Treasurer**: Monitor payment receipts and revenue
- **Record keeping**: Ensure multiple people have payment confirmations
- **Accountability**: Multiple team members stay informed of signups

---

## Access Control

### How page restriction works:

The plugin can restrict specific WordPress pages to active members only.

### Setting up restricted pages:

1. Go to **WDTA Membership → Settings**
2. Scroll to **"Access Control"**
3. Check the pages that should require active membership
4. Optionally select a custom "Access Denied" page
5. Click **"Save Settings"**

### Access logic:

- **Active members**: Can view restricted pages
- **Inactive/expired members**: Redirected to access denied page or login
- **Non-logged-in users**: Redirected to login page
- **Administrators**: Always have access (for testing)

### Grace period:

**Important**: This plugin uses **binary membership status** - there is **no grace period**.

- On January 1st, any unpaid membership from the previous year becomes inactive
- Members immediately lose access to restricted content
- This is intentional to encourage timely payment

---

## Payment Processing

### Stripe payments:

1. Member clicks **"Pay with Card"**
2. Redirected to secure Stripe Checkout
3. Payment processed by Stripe
4. Webhook confirms payment to your site
5. Membership automatically activated
6. Confirmation email sent (with CC to marketing and treasurer)

**Processing time**: Instant (usually within seconds)

### Bank transfer payments:

1. Member views bank account details on the form
2. Makes the transfer at their bank
3. Submits transfer details via the plugin form
4. Admin receives email notification
5. Admin verifies payment in bank account
6. Admin approves payment in plugin
7. Membership activated
8. Confirmation email sent to member (with CC)

**Processing time**: Manual - depends on when admin approves

### Payment amounts:

- **Stripe**: $970.90 AUD (includes 2.2% card processing surcharge)
  - Membership: $950.00
  - Surcharge: $20.90
- **Bank Transfer**: $950.00 AUD (no surcharge)

---

## Membership Year & Expiry

### Annual cycle:

- **Membership year**: Runs from January 1 to December 31
- **Expiry date**: Always December 31 of the membership year
- **Renewal window**: Starts from the payment year cutoff date (default: Nov 1)

### Status progression:

1. **Active**: Member has paid, membership expires Dec 31
2. **Expired**: After Dec 31, if not renewed for next year
3. **Pending**: Payment submitted but not yet confirmed
4. **Rejected**: Payment was rejected by admin

### Automatic expiry:

On January 1st each year, the plugin automatically:
1. Marks all unpaid previous-year memberships as inactive
2. Removes access to restricted pages
3. Sends inactive users report to admins

---

## WordPress Cron & Scheduling

### How automated emails work:

The plugin uses WordPress Cron to schedule and send emails automatically.

### Scheduled tasks:

- **Daily Email Check**: Runs at midnight to check if any reminders should be sent
- **Daily Expiry Check**: Runs at midnight on Jan 1st to deactivate expired memberships
- **Reminder Emails**: Sent on calculated dates based on your configuration

### Important notes:

- WordPress Cron requires site traffic to trigger
- Low-traffic sites may experience delays
- Consider setting up a real cron job for reliability
- Install "WP Crontrol" plugin to monitor scheduled events

---

## Best Practices

### Email timing:

- Start reminders at least 30 days before expiry
- Send at least 2-3 reminders before expiry date
- Include 1-2 follow-up reminders after expiry
- Space reminders appropriately (not too frequent)

### Member communication:

- Keep email language friendly but clear
- Always include the renewal URL
- Mention the expiry date (December 31)
- Provide both payment options (Stripe and Bank Transfer)

### Admin workflow:

- Check for pending bank transfers daily
- Approve transfers within 24-48 hours
- Add notes to memberships for audit trail
- Review the inactive users report on Jan 1st

### Testing:

- Administrators always have access to test restricted pages
- Test payment flow in Stripe test mode before going live
- Send test emails to verify formatting and content
- Verify webhook endpoint is working correctly

---

## Common Scenarios

### Scenario 1: Member wants to pay for next year early

**When**: November 15, 2024  
**Solution**: The cutoff date handles this automatically. Any payment after November 1st is for 2025 membership.

### Scenario 2: Member paid but doesn't have access

**Check**:
1. Verify membership status is "Active"
2. Check expiry date is in the future  
3. Ensure the page is added to restricted pages
4. Ask member to log out and back in
5. Clear any caching plugins

### Scenario 3: Member missed a bank transfer

**Solution**:
1. Admin marks the original payment as "Rejected"
2. Add a note explaining the issue
3. Member submits a new payment
4. Admin approves the corrected payment

### Scenario 4: Complimentary membership

**Solution**:
1. Go to **Add Membership**
2. Select the user
3. Set payment method to "Manual"
4. Set payment status to "Completed"
5. Set status to "Active"
6. Add note: "Complimentary membership - [reason]"

---

## Support & Troubleshooting

### For detailed troubleshooting:

- See DEVELOPMENT.md for technical debugging
- Check WordPress Cron status with WP Crontrol plugin
- Verify Stripe webhook logs in Stripe Dashboard
- Test email delivery with mail tester plugins

### Need help?

Contact the WDTA development team for support with:
- Configuration questions
- Technical issues
- Feature requests
- Custom modifications
