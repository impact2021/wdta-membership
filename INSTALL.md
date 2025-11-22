# WDTA Membership Plugin - Installation Guide

## Quick Start

### 1. Installation

**Option A: Upload via WordPress Admin**
1. Download or zip the `wdta-membership` folder
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Choose the zip file and click "Install Now"
4. Click "Activate Plugin"

**Option B: Manual Installation**
1. Upload the `wdta-membership` folder to `/wp-content/plugins/`
2. Go to WordPress Admin → Plugins
3. Find "WDTA Membership" and click "Activate"

### 2. Initial Configuration

After activation, go to **WDTA Membership → Settings**

#### Required Settings:

**Stripe Configuration (for credit card payments):**
1. Sign up at https://stripe.com
2. Go to Stripe Dashboard → Developers → API keys
3. Copy your Publishable key (starts with `pk_`)
4. Copy your Secret key (starts with `sk_`)
5. Enter both keys in the plugin settings

**Stripe Webhook Setup:**
1. In Stripe Dashboard, go to Developers → Webhooks
2. Click "Add endpoint"
3. Enter webhook URL: `https://yoursite.com/wp-json/wdta/v1/stripe-webhook`
4. Select events: `checkout.session.completed` and `payment_intent.succeeded`
5. Copy the Signing secret (starts with `whsec_`)
6. Paste it in the plugin settings

**Bank Transfer Configuration:**
1. Enter your bank name
2. Enter account name
3. Enter BSB number
4. Enter account number
These details will be shown to members who choose bank transfer.

**Email Configuration:**
1. Set "From Name" (e.g., "WDTA")
2. Set "From Email Address" (e.g., "membership@wdta.org")

### 3. Set Up Access Control

1. In Settings, scroll to "Access Control"
2. Select which pages require membership
3. Optionally create and select a custom "Access Denied" page

### 4. Create Member-Facing Pages

**Create Membership Page:**
1. Go to Pages → Add New
2. Title: "Membership"
3. Add shortcode: `[wdta_membership_form]`
4. Publish

**Create Membership Status Page:**
1. Go to Pages → Add New
2. Title: "My Membership"
3. Add shortcode: `[wdta_membership_status]`
4. Publish

**Optional: Access Denied Page:**
1. Go to Pages → Add New
2. Title: "Access Denied"
3. Add custom message about membership requirement
4. Publish
5. Go to WDTA Membership → Settings
6. Select this page as "Access Denied Page"

### 5. Testing

**Test Stripe Payments:**
1. Use Stripe test mode with test keys
2. Test card: `4242 4242 4242 4242`
3. Expiry: Any future date
4. CVC: Any 3 digits

**Test Bank Transfer:**
1. Log in as a test user
2. Go to the membership page
3. Submit bank transfer details
4. Go to WDTA Membership → All Memberships (admin)
5. You should see the pending payment
6. Click "Approve" to activate

**Test Access Control:**
1. Log out
2. Try accessing a restricted page
3. You should be denied access
4. Log in as a member without active membership
5. You should still be denied
6. Approve a test membership
7. Access should be granted

### 6. Email Testing

To test automated emails without waiting for scheduled dates:

**Manual Testing:**
1. Adjust your server date/time temporarily
2. Run WP-Cron manually: `wp cron event run wdta_daily_email_check`
3. Or visit: `https://yoursite.com/wp-cron.php?doing_wp_cron`

**Email Schedule:**
- Dec 1st: 1 month reminder
- Dec 25th: 1 week reminder  
- Dec 31st: 1 day reminder
- Jan 2nd: 1 day overdue
- Jan 8th: 1 week overdue
- Jan 31st: End of month 1
- Feb 28th/29th: End of month 2
- Mar 31st: Final notice

### 7. Going Live

**Before Production:**
1. Switch Stripe from test mode to live mode
2. Update API keys with live keys
3. Update webhook with live signing secret
4. Test a real payment with a small amount first
5. Verify email delivery

**Checklist:**
- [ ] Stripe live keys configured
- [ ] Webhook endpoint verified
- [ ] Bank details correct
- [ ] Email from address verified
- [ ] Restricted pages selected
- [ ] Test membership completed
- [ ] Test emails received
- [ ] Test access control working

## Support

### Common Issues

**Emails not sending:**
- Check WordPress email configuration
- Install WP Mail SMTP plugin
- Verify cron jobs are running: `wp cron event list`

**Stripe webhook failing:**
- Check webhook URL is accessible
- Verify signing secret is correct
- Check WordPress REST API is enabled

**Access control not working:**
- Clear WordPress cache
- Verify pages are selected in settings
- Check user has logged in

**Cron not running:**
- Some hosts disable WP-Cron
- Set up real cron: `*/15 * * * * wget -q -O - https://yoursite.com/wp-cron.php`
- Or use plugin: WP Crontrol

## Advanced Configuration

### Custom Email Templates

Edit email templates in Settings. Available placeholders:
- `{user_name}` - Member's display name
- `{user_email}` - Member's email
- `{year}` - Membership year
- `{amount}` - Membership amount
- `{deadline}` - Payment deadline
- `{renewal_url}` - Link to membership form
- `{site_name}` - Website name

### Database Queries

View memberships directly:
```sql
SELECT * FROM wp_wdta_memberships WHERE membership_year = 2024;
```

### Custom Development

Hooks available for developers:
```php
// When membership activated
do_action('wdta_membership_activated', $user_id, $year);

// When membership expires
do_action('wdta_membership_expired', $user_id, $year);

// Filter membership fee
add_filter('wdta_membership_fee', function($fee) { return $fee; });

// Filter restricted pages
add_filter('wdta_restricted_pages', function($pages) { return $pages; });
```

## Troubleshooting

### Enable Debug Logging

Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check logs at: `wp-content/debug.log`

### Reset Plugin

To completely reset:
1. Deactivate plugin
2. Delete from database:
   ```sql
   DROP TABLE wp_wdta_memberships;
   DELETE FROM wp_options WHERE option_name LIKE 'wdta_%';
   ```
3. Reactivate plugin

## Maintenance

### Annual Tasks (December)

- [ ] Review bank account details
- [ ] Test Stripe connection
- [ ] Verify email templates
- [ ] Check restricted pages
- [ ] Review membership list
- [ ] Test payment flow

### Monthly Tasks

- [ ] Review pending bank transfers
- [ ] Check email delivery
- [ ] Monitor membership numbers

## Security

- Keep WordPress updated
- Keep plugin files secure (don't expose API keys)
- Use strong Stripe webhook secret
- Regular backups of membership database
- Monitor for suspicious payments

## Performance

Plugin is optimized for:
- Up to 10,000 members
- Minimal database queries
- Efficient cron jobs
- Cached page access checks

For larger installations, consider:
- Database indexing
- Object caching (Redis/Memcached)
- CDN for static assets
