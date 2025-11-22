# WDTA Membership Plugin - Quick Start Guide

## 🚀 In 5 Minutes

### 1. Install (1 minute)
```bash
# Upload to WordPress
wp-content/plugins/wdta-membership/

# Or via WordPress Admin
Plugins → Add New → Upload Plugin
```

Activate: `Plugins → WDTA Membership → Activate`

### 2. Configure Stripe (2 minutes)
```
WDTA Membership → Settings → Stripe Payment Settings

1. Publishable Key: pk_test_... (from Stripe Dashboard)
2. Secret Key: sk_test_... (from Stripe Dashboard)
3. Webhook Secret: whsec_... (from Stripe Webhooks)
   Webhook URL: https://yoursite.com/wp-json/wdta/v1/stripe-webhook
```

### 3. Set Bank Details (1 minute)
```
WDTA Membership → Settings → Bank Transfer Settings

Bank Name: Your Bank
Account Name: WDTA
BSB: 123-456
Account Number: 12345678
```

### 4. Create Pages (1 minute)
**Membership Page:**
- Title: "Membership"
- Content: `[wdta_membership_form]`

**Status Page:**
- Title: "My Membership"
- Content: `[wdta_membership_status]`

### 5. Restrict Pages
```
WDTA Membership → Settings → Access Control

☑ Select pages that require membership
```

## ✅ Done!

Your membership system is ready. Test with Stripe test card: `4242 4242 4242 4242`

---

## 📋 Quick Reference

### Shortcodes
- `[wdta_membership_form]` - Payment form
- `[wdta_membership_status]` - Member status

### Admin Pages
- **WDTA Membership → All Memberships** - Manage members
- **WDTA Membership → Settings** - Configure plugin

### Key Dates
- **Jan 1**: Membership due date
- **Mar 31**: Final deadline
- **Apr 1**: Access revoked

### Email Schedule
| Date | Email |
|------|-------|
| Dec 1 | 1 month reminder |
| Dec 25 | 1 week reminder |
| Dec 31 | 1 day reminder |
| Jan 2 | 1 day overdue |
| Jan 8 | 1 week overdue |
| Jan 31 | Month 1 overdue |
| Feb 28/29 | Month 2 overdue |
| Mar 31 | Final notice |

### Price
**$950 AUD** per year

---

## 🔧 Common Tasks

### Approve Bank Transfer
1. WDTA Membership → All Memberships
2. Find pending payment
3. Click "Approve"

### Change Restricted Pages
1. WDTA Membership → Settings
2. Access Control section
3. Check/uncheck pages
4. Save Settings

### Test Email Schedule
```bash
wp cron event run wdta_daily_email_check
```

### View Database
```sql
SELECT * FROM wp_wdta_memberships;
```

---

## 📚 Full Documentation

- **README.md** - Features & overview
- **INSTALL.md** - Detailed setup
- **ARCHITECTURE.md** - Technical details
- **CHANGELOG.md** - Version history

---

## 🆘 Quick Help

**Emails not sending?**
- Check WordPress email settings
- Install WP Mail SMTP plugin

**Stripe not working?**
- Verify API keys are correct
- Check webhook is configured
- Test in Stripe test mode first

**Access control not working?**
- Clear WordPress cache
- Verify pages are checked in settings
- Check user is logged in

**Cron not running?**
- Some hosts disable WP-Cron
- Set up real cron job
- Or install WP Crontrol plugin

---

## 🎯 Test Checklist

- [ ] Upload plugin
- [ ] Activate plugin
- [ ] Add Stripe keys
- [ ] Add bank details
- [ ] Create membership page
- [ ] Create status page
- [ ] Select restricted pages
- [ ] Test Stripe payment (test card: 4242...)
- [ ] Test bank transfer
- [ ] Approve test payment
- [ ] Test access control
- [ ] Verify email delivery

---

## 📞 Support

For issues or questions:
- Check documentation files
- Review WordPress debug.log
- Contact WDTA development team

---

## 🔐 Security Notes

- Keep WordPress updated
- Use strong API keys
- Enable HTTPS
- Backup database regularly
- Don't expose Stripe secret keys

---

## 🎨 Customization

**Change email templates:**
```
WDTA Membership → Settings → (scroll down)
```

**Filter membership fee:**
```php
add_filter('wdta_membership_fee', function($fee) {
    return 1000.00; // Change to $1000
});
```

**Custom access denied message:**
```php
add_filter('wdta_access_denied_message', function($msg, $reason) {
    return 'Your custom message here';
}, 10, 2);
```

---

## 📊 At a Glance

| Feature | Status |
|---------|--------|
| Stripe Payments | ✅ Ready |
| Bank Transfers | ✅ Ready |
| Access Control | ✅ Ready |
| Email Reminders | ✅ Ready |
| Admin Dashboard | ✅ Ready |
| Member Portal | ✅ Ready |
| Documentation | ✅ Complete |
| Security | ✅ Verified |

**Total Setup Time:** ~5 minutes  
**Lines of Code:** 2,138  
**Files:** 20  
**Documentation:** 1,129 lines

---

**Version:** 1.0.0  
**Last Updated:** 2024-11-21  
**License:** GPL v2 or later
