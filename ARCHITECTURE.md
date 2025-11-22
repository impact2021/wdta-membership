# WDTA Membership Plugin - Architecture Overview

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    WDTA Membership Plugin                        │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                          Entry Point                              │
│                     wdta-membership.php                           │
│  • Plugin registration                                            │
│  • File includes                                                  │
│  • Activation/deactivation hooks                                  │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Core Classes Layer                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌───────────────────────────────────────────────────────┐      │
│  │  WDTA_Membership (Main Controller)                    │      │
│  │  • Initializes all components                          │      │
│  │  • Manages shortcodes                                  │      │
│  │  • Coordinates between modules                         │      │
│  └───────────────────────────────────────────────────────┘      │
│                              │                                    │
│         ┌────────────────────┼────────────────────┐             │
│         │                    │                    │             │
│         ▼                    ▼                    ▼             │
│  ┌─────────────┐    ┌──────────────┐    ┌───────────────┐     │
│  │  Database   │    │   Access      │    │   Payment     │     │
│  │  Handler    │    │   Control     │    │   Handlers    │     │
│  └─────────────┘    └──────────────┘    └───────────────┘     │
│         │                    │                    │             │
│         │                    │         ┌──────────┴──────────┐ │
│         │                    │         │                     │ │
│         │                    │         ▼                     ▼ │
│         │                    │   ┌──────────┐        ┌──────────┐
│         │                    │   │  Stripe  │        │   Bank   │
│         │                    │   │ Handler  │        │ Transfer │
│         │                    │   └──────────┘        └──────────┘
│         │                    │                                   │
│         ▼                    ▼                                   │
│  ┌────────────────────────────────────────────────────┐        │
│  │           Email Notifications                       │        │
│  │  • Template system                                  │        │
│  │  • Scheduled reminders                              │        │
│  │  • Placeholder replacement                          │        │
│  └────────────────────────────────────────────────────┘        │
│                              │                                   │
│                              ▼                                   │
│  ┌────────────────────────────────────────────────────┐        │
│  │               Cron Manager                          │        │
│  │  • Daily email checks                               │        │
│  │  • Expiry processing                                │        │
│  │  • Event scheduling                                 │        │
│  └────────────────────────────────────────────────────┘        │
│                              │                                   │
│                              ▼                                   │
│  ┌────────────────────────────────────────────────────┐        │
│  │          Admin Interface                            │        │
│  │  • Membership list                                  │        │
│  │  • Settings page                                    │        │
│  │  • AJAX handlers                                    │        │
│  └────────────────────────────────────────────────────┘        │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

## Data Flow

### Payment Flow (Stripe)

```
User                   Frontend              Server                Stripe
 │                        │                    │                     │
 │  Click "Pay Now"       │                    │                     │
 ├────────────────────────►                    │                     │
 │                        │  AJAX Request      │                     │
 │                        ├────────────────────►                     │
 │                        │                    │  Create Session     │
 │                        │                    ├─────────────────────►
 │                        │                    │                     │
 │                        │                    │  Session Data       │
 │                        │                    ◄─────────────────────┤
 │                        │  Redirect URL      │                     │
 │                        ◄────────────────────┤                     │
 │  Redirect to Stripe    │                    │                     │
 ├────────────────────────┼────────────────────┼─────────────────────►
 │                        │                    │                     │
 │  Complete Payment      │                    │                     │
 ├────────────────────────┼────────────────────┼─────────────────────►
 │                        │                    │                     │
 │                        │                    │  Webhook Event      │
 │                        │                    ◄─────────────────────┤
 │                        │                    │                     │
 │                        │                    │  Update Database    │
 │                        │                    ├────►                │
 │                        │                    │                     │
 │                        │                    │  Send Email         │
 │                        │                    ├────►                │
 │                        │                    │                     │
 │  Return to Site        │                    │                     │
 ◄────────────────────────┼────────────────────┼─────────────────────┤
 │                        │                    │                     │
```

### Payment Flow (Bank Transfer)

```
User                   Frontend              Admin
 │                        │                    │
 │  Submit Details        │                    │
 ├────────────────────────►                    │
 │                        │  AJAX Request      │
 │                        ├────────────────────►
 │                        │                    │
 │                        │  Save Pending      │
 │                        ├────►DB              │
 │                        │                    │
 │                        │  Email Admin       │
 │                        ├────────────────────►
 │                        │                    │
 │  Confirmation          │                    │
 ◄────────────────────────┤                    │
 │                        │                    │
 │                        │  Verify Payment    │
 │                        │                    ├────►Bank
 │                        │                    │
 │                        │  Approve           │
 │                        │                    ├────►DB
 │                        │                    │
 │  Activation Email      │                    │
 ◄────────────────────────┼────────────────────┤
 │                        │                    │
```

### Access Control Flow

```
User Request
     │
     ▼
┌─────────────────┐
│  Page Request   │
└─────────────────┘
     │
     ▼
┌─────────────────┐      No      ┌──────────────┐
│  Restricted?    ├──────────────►│ Allow Access │
└─────────────────┘               └──────────────┘
     │ Yes
     ▼
┌─────────────────┐      Yes     ┌──────────────┐
│  Is Admin?      ├──────────────►│ Allow Access │
└─────────────────┘               └──────────────┘
     │ No
     ▼
┌─────────────────┐      No      ┌──────────────┐
│  Logged In?     ├──────────────►│ Redirect to  │
└─────────────────┘               │    Login     │
     │ Yes                        └──────────────┘
     ▼
┌─────────────────┐      No      ┌──────────────┐
│  Has Active     ├──────────────►│ Show Access  │
│  Membership?    │               │   Denied     │
└─────────────────┘               └──────────────┘
     │ Yes
     ▼
┌─────────────────┐
│  Allow Access   │
└─────────────────┘
```

## Database Schema

```sql
wp_wdta_memberships
├── id (PK)
├── user_id (FK → wp_users.ID)
├── membership_year
├── payment_amount
├── payment_method (stripe/bank_transfer)
├── payment_status (pending/completed/pending_verification/rejected)
├── payment_date
├── payment_reference
├── stripe_payment_id
├── expiry_date (always March 31st of year)
├── status (pending/active/expired/rejected)
├── created_at
├── updated_at
└── notes

Indexes:
- PRIMARY KEY (id)
- KEY (user_id)
- KEY (membership_year)
- KEY (status)
- KEY (expiry_date)
- UNIQUE KEY (user_id, membership_year)
```

## Email Notification Timeline

```
Timeline for Year 2024 Membership:

2023-12-01  ──┐  "1 month before" reminder
               │  (Dec 1st of previous year)
               │
2023-12-25  ──┤  "1 week before" reminder
               │  (Dec 25th of previous year)
               │
2023-12-31  ──┤  "1 day before" reminder
               │  (Dec 31st of previous year)
               │
2024-01-01  ══╪══ PAYMENT DUE DATE
               │
2024-01-02  ──┤  "1 day overdue" notice
               │
2024-01-08  ──┤  "1 week overdue" notice
               │
2024-01-31  ──┤  "End of January" warning
               │
2024-02-28  ──┤  "End of February" warning
               │  (or Feb 29 on leap year)
               │
2024-03-31  ──┤  "FINAL DEADLINE" notice
               │
2024-04-01  ══╧══ ACCESS REVOKED
```

## Component Responsibilities

### WDTA_Membership (Main Controller)
- Initializes all components
- Registers shortcodes
- Coordinates between modules
- Handles plugin lifecycle

### WDTA_Database
- Creates and manages database tables
- CRUD operations for memberships
- Query methods for reports
- Data validation

### WDTA_Access_Control
- Page access checking
- Content filtering
- Redirect handling
- Settings registration

### WDTA_Payment_Stripe
- Stripe checkout session creation
- Webhook handling
- Payment verification
- Confirmation emails

### WDTA_Payment_Bank
- Bank transfer form processing
- Admin notification
- Manual approval
- Payment verification

### WDTA_Email_Notifications
- Template management
- Placeholder replacement
- Email sending
- Schedule coordination

### WDTA_Cron
- WP-Cron event scheduling
- Daily notification checks
- Expiry processing
- Event cleanup

### WDTA_Admin
- Admin menu registration
- Settings page rendering
- AJAX handlers
- Asset enqueueing

## Security Layers

```
┌────────────────────────────────────────┐
│         Request Validation             │
│  • Nonce verification                  │
│  • Capability checks                   │
│  • Input sanitization                  │
└────────────────────────────────────────┘
                  │
                  ▼
┌────────────────────────────────────────┐
│        Data Layer Security             │
│  • Prepared statements                 │
│  • Type casting                        │
│  • Validation rules                    │
└────────────────────────────────────────┘
                  │
                  ▼
┌────────────────────────────────────────┐
│        External API Security           │
│  • Stripe webhook signature            │
│  • HTTPS enforcement                   │
│  • API key encryption                  │
└────────────────────────────────────────┘
```

## Plugin Hooks & Filters

### Actions
```php
// Plugin lifecycle
do_action('wdta_membership_activated', $user_id, $year);
do_action('wdta_membership_expired', $user_id, $year);

// Cron events
do_action('wdta_daily_email_check');
do_action('wdta_daily_expiry_check');
```

### Filters
```php
// Customize behavior
apply_filters('wdta_membership_fee', $fee);
apply_filters('wdta_restricted_pages', $pages);
apply_filters('wdta_email_template', $template, $type);
apply_filters('wdta_access_denied_message', $message, $reason);
```

## File Organization

```
wdta-membership/
│
├── wdta-membership.php          # Plugin entry point
│
├── includes/                    # Core PHP classes
│   ├── class-wdta-membership.php
│   ├── class-wdta-database.php
│   ├── class-wdta-access-control.php
│   ├── class-wdta-payment-stripe.php
│   ├── class-wdta-payment-bank.php
│   ├── class-wdta-email-notifications.php
│   ├── class-wdta-admin.php
│   └── class-wdta-cron.php
│
├── admin/                       # Admin interface templates
│   ├── memberships-list.php
│   └── settings.php
│
├── templates/                   # Frontend templates
│   ├── membership-form.php
│   └── membership-status.php
│
├── assets/                      # Static assets
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
│
└── documentation/               # Documentation files
    ├── README.md
    ├── INSTALL.md
    ├── CHANGELOG.md
    └── ARCHITECTURE.md (this file)
```

## Performance Considerations

### Database Optimization
- Indexed columns for fast queries
- Unique constraints prevent duplicates
- Timestamp fields for audit trails

### Caching Strategy
- WordPress object cache compatible
- Page access checks cached per request
- Settings cached via WordPress options

### Cron Efficiency
- Single daily check (not per-hour)
- Batch email sending
- Conditional processing (only on specific dates)

### Asset Loading
- CSS/JS only loaded on plugin pages
- Minification ready
- CDN compatible

## Extensibility Points

### Custom Payment Gateways
```php
// Add your own payment handler
class Custom_Payment extends WDTA_Payment_Base {
    public function process_payment() {
        // Your implementation
    }
}
```

### Custom Email Templates
```php
// Override default templates
add_filter('wdta_email_template', function($template, $type) {
    if ($type === 'custom_reminder') {
        return 'Your custom template';
    }
    return $template;
}, 10, 2);
```

### Custom Access Rules
```php
// Add custom access logic
add_filter('wdta_check_access', function($has_access, $user_id) {
    // Your custom logic
    return $has_access;
}, 10, 2);
```

## Testing Strategy

### Unit Tests
- Database operations
- Date calculations
- Email template parsing
- Access control logic

### Integration Tests
- Payment workflows
- Email sending
- Cron execution
- Admin interfaces

### Manual Tests
- Stripe test mode
- Bank transfer approval
- Email delivery
- Page restrictions

## Deployment Checklist

- [ ] WordPress requirements met (5.0+, PHP 7.2+)
- [ ] Stripe live keys configured
- [ ] Bank details verified
- [ ] Email settings tested
- [ ] Restricted pages selected
- [ ] Cron jobs scheduled
- [ ] Database backup taken
- [ ] Test payment completed
- [ ] Access control verified
- [ ] Email notifications working

## Maintenance Plan

### Daily
- Monitor payment notifications
- Check email delivery

### Weekly
- Review pending bank transfers
- Check membership status

### Monthly
- Verify Stripe connection
- Review email templates
- Check restricted pages

### Annually
- Update bank details
- Review membership fee
- Test full payment flow
- Update documentation
