# WDTA Membership Plugin - API Documentation

## Overview

This document provides technical details for developers who want to extend or integrate with the WDTA Membership plugin.

## Database Schema

### Table: wp_wdta_memberships

```sql
CREATE TABLE wp_wdta_memberships (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id bigint(20) UNSIGNED NOT NULL,
    membership_year int(4) NOT NULL,
    status varchar(20) NOT NULL DEFAULT 'inactive',
    payment_date datetime DEFAULT NULL,
    payment_amount decimal(10,2) DEFAULT NULL,
    payment_method varchar(50) DEFAULT NULL,
    transaction_id varchar(255) DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY membership_year (membership_year),
    KEY status (status),
    UNIQUE KEY user_year (user_id, membership_year)
)
```

**Field Descriptions:**
- `id`: Auto-incrementing primary key
- `user_id`: WordPress user ID (foreign key to wp_users)
- `membership_year`: Four-digit year (e.g., 2025)
- `status`: Either 'active' or 'inactive'
- `payment_date`: Timestamp when payment was received
- `payment_amount`: Decimal amount paid
- `payment_method`: Payment method identifier (e.g., 'stripe', 'paypal', 'manual')
- `transaction_id`: External transaction identifier
- `created_at`: Record creation timestamp
- `updated_at`: Last update timestamp

## Classes

### WDTA_Membership_Database

Database operations class.

#### Methods

**get_membership($user_id, $year)**
```php
$membership = WDTA_Membership_Database::get_membership(1, 2025);
// Returns: object or null
```
Retrieves a single membership record.

**update_membership($user_id, $year, $data)**
```php
$data = array(
    'status' => 'active',
    'payment_date' => current_time('mysql'),
    'payment_amount' => 50.00,
    'payment_method' => 'stripe',
    'transaction_id' => 'txn_123456'
);
WDTA_Membership_Database::update_membership(1, 2025, $data);
```
Creates or updates a membership record.

**get_user_memberships($user_id)**
```php
$memberships = WDTA_Membership_Database::get_user_memberships(1);
// Returns: array of objects
```
Gets all memberships for a user, ordered by year descending.

**get_inactive_users($year)**
```php
$inactive = WDTA_Membership_Database::get_inactive_users(2025);
// Returns: array of user objects with status
```
Gets all users with inactive status for a specific year.

**get_users_for_reminder($year)**
```php
$users = WDTA_Membership_Database::get_users_for_reminder(2025);
// Returns: array of user objects
```
Gets users who should receive renewal reminders.

**set_unpaid_to_inactive($year)**
```php
$count = WDTA_Membership_Database::set_unpaid_to_inactive(2024);
// Returns: number of rows updated
```
Sets all non-active memberships for a year to inactive.

### WDTA_Membership_Status

Membership status management class.

#### Methods

**is_member_active($user_id, $year = null)**
```php
if (WDTA_Membership_Status::is_member_active(1, 2025)) {
    // User has active membership
}
// Returns: boolean
```
Checks if user has active membership. Defaults to current year if year not provided.

**activate_membership($user_id, $year, $payment_data = array())**
```php
$payment_data = array(
    'amount' => 50.00,
    'method' => 'stripe',
    'transaction_id' => 'txn_123456'
);
WDTA_Membership_Status::activate_membership(1, 2025, $payment_data);
// Returns: result of database update
```
Activates a membership and records payment details.

**deactivate_membership($user_id, $year)**
```php
WDTA_Membership_Status::deactivate_membership(1, 2025);
// Returns: result of database update
```
Deactivates a membership.

**get_current_year()**
```php
$year = WDTA_Membership_Status::get_current_year();
// Returns: int (e.g., 2025)
```
Gets the current year for new memberships.

**get_next_year()**
```php
$year = WDTA_Membership_Status::get_next_year();
// Returns: int (e.g., 2026)
```
Gets the next year for renewals.

### WDTA_Membership_Email

Email sending and formatting class.

#### Methods

**send_inactive_users_report()**
```php
$sent = WDTA_Membership_Email::send_inactive_users_report();
// Returns: boolean
```
Sends the inactive users report to configured recipients.

**send_reminder_email($reminder_number)**
```php
$count = WDTA_Membership_Email::send_reminder_email(1);
// Returns: int (number of emails sent)
```
Sends a specific reminder email (1, 2, or 3) to all applicable users.

**send_payment_confirmation($user_id, $year, $amount)**
```php
$sent = WDTA_Membership_Email::send_payment_confirmation(1, 2025, 50.00);
// Returns: boolean
```
Sends payment confirmation email to a user.

## WordPress Hooks

### Actions

**wdta_membership_daily_check**
```php
add_action('wdta_membership_daily_check', 'my_custom_daily_task');
function my_custom_daily_task() {
    // Custom code to run daily
}
```
Triggered by cron job once per day.

**plugins_loaded** (initialization)
```php
add_action('plugins_loaded', 'my_membership_init', 20);
function my_membership_init() {
    // Runs after plugin is initialized
}
```

### Filters

**wdta_membership_email_headers** (proposed)
```php
add_filter('wdta_membership_email_headers', 'my_custom_headers');
function my_custom_headers($headers) {
    $headers[] = 'Reply-To: support@example.com';
    return $headers;
}
```

**wdta_membership_email_content** (proposed)
```php
add_filter('wdta_membership_email_content', 'my_custom_content', 10, 2);
function my_custom_content($content, $type) {
    if ($type === 'confirmation') {
        $content .= '<p>Custom footer content</p>';
    }
    return $content;
}
```

## Shortcodes

### [wdta_membership_form]

Registration form for non-logged in users.

**Basic Usage:**
```
[wdta_membership_form]
```

**Renders:**
- Registration form with user fields
- Current year and price display
- Payment section
- Terms and conditions checkbox

**AJAX Handler:** `wdta_register_and_pay`

### [wdta_membership_renewal_form]

Renewal form for logged-in users.

**Basic Usage:**
```
[wdta_membership_renewal_form]
```

**Renders:**
- Renewal form for next year
- Current status display
- Next year and price display
- Payment section

**AJAX Handler:** `wdta_renew_membership`

## AJAX Endpoints

### wdta_register_and_pay

**Action:** `wdta_register_and_pay`
**Method:** POST
**Auth:** None (creates new user)

**Parameters:**
- `username` (string, required)
- `email` (string, required)
- `password` (string, required)
- `first_name` (string, required)
- `last_name` (string, required)
- `membership_year` (int, required)
- `amount` (float, required)
- `nonce` (string, required)

**Response Success:**
```json
{
    "success": true,
    "data": {
        "message": "Account created and membership activated!",
        "redirect": "https://example.com/membership-confirmation/"
    }
}
```

**Response Error:**
```json
{
    "success": false,
    "data": {
        "message": "Error message here"
    }
}
```

### wdta_renew_membership

**Action:** `wdta_renew_membership`
**Method:** POST
**Auth:** Required (must be logged in)

**Parameters:**
- `membership_year` (int, required)
- `amount` (float, required)
- `nonce` (string, required)

**Response Success:**
```json
{
    "success": true,
    "data": {
        "message": "Membership renewed successfully!",
        "redirect": "https://example.com/membership-confirmation/"
    }
}
```

## WordPress Options

All plugin settings are stored as WordPress options:

**General Settings:**
- `wdta_membership_currency` (default: 'USD')
- `wdta_membership_current_year_price` (default: '50.00')
- `wdta_membership_next_year_price` (default: '50.00')
- `wdta_membership_payment_method` (default: 'manual')

**Email Settings:**
- `wdta_membership_from_email`
- `wdta_membership_from_name`

**Inactive Report Settings:**
- `wdta_membership_inactive_email_enabled` ('yes' or 'no')
- `wdta_membership_inactive_email_recipients`
- `wdta_membership_inactive_email_subject`

**Reminder Settings (for each reminder 1-3):**
- `wdta_membership_reminder{N}_enabled` ('yes' or 'no')
- `wdta_membership_reminder{N}_timing` (int)
- `wdta_membership_reminder{N}_unit` ('days', 'weeks', 'months')
- `wdta_membership_reminder{N}_period` ('before', 'after')
- `wdta_membership_reminder{N}_subject` (string)
- `wdta_membership_reminder{N}_last_sent` (date, YYYY-MM-DD)

**Usage:**
```php
$price = get_option('wdta_membership_current_year_price', '50.00');
update_option('wdta_membership_current_year_price', '75.00');
```

## Cron Jobs

### wdta_membership_daily_check

**Schedule:** Daily
**Function:** `WDTA_Membership_Cron::daily_check()`

**Tasks Performed:**
1. Check if January 1st → deactivate unpaid memberships
2. Send inactive users report (if January 1st and enabled)
3. Check and send reminder emails based on configuration

**Manual Trigger:**
```php
if (current_user_can('manage_options')) {
    WDTA_Membership_Cron::daily_check();
}
```

## Payment Gateway Integration

### Stripe Example

Replace the payment logic in `includes/class-wdta-membership-shortcodes.php`:

```php
public static function ajax_register_and_pay() {
    // ... validation code ...
    
    // Stripe integration
    require_once('stripe-php/init.php');
    \Stripe\Stripe::setApiKey('your_secret_key');
    
    try {
        $charge = \Stripe\Charge::create([
            'amount' => $amount * 100, // cents
            'currency' => 'usd',
            'description' => 'WDTA Membership ' . $year,
            'source' => $_POST['stripe_token'],
        ]);
        
        $payment_data = array(
            'amount' => $amount,
            'method' => 'stripe',
            'transaction_id' => $charge->id
        );
        
        WDTA_Membership_Status::activate_membership($user_id, $year, $payment_data);
        
        wp_send_json_success(array('message' => 'Payment successful!'));
        
    } catch(\Stripe\Exception\CardException $e) {
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}
```

### PayPal Example

Similar approach for PayPal:

```php
// PayPal REST API integration
$payment_data = array(
    'amount' => $amount,
    'method' => 'paypal',
    'transaction_id' => $paypal_transaction_id
);

WDTA_Membership_Status::activate_membership($user_id, $year, $payment_data);
```

## Custom Queries

### Get All Active Members for Current Year

```php
global $wpdb;
$table = $wpdb->prefix . 'wdta_memberships';
$year = date('Y');

$results = $wpdb->get_results($wpdb->prepare(
    "SELECT u.*, m.* 
    FROM {$wpdb->users} u 
    INNER JOIN $table m ON u.ID = m.user_id 
    WHERE m.membership_year = %d AND m.status = 'active'",
    $year
));
```

### Get Revenue by Year

```php
global $wpdb;
$table = $wpdb->prefix . 'wdta_memberships';

$revenue = $wpdb->get_results(
    "SELECT membership_year, 
            SUM(payment_amount) as total_revenue,
            COUNT(*) as member_count
    FROM $table 
    WHERE status = 'active'
    GROUP BY membership_year
    ORDER BY membership_year DESC"
);
```

### Get Members Expiring Soon

```php
global $wpdb;
$table = $wpdb->prefix . 'wdta_memberships';
$current_year = date('Y');

$expiring = $wpdb->get_results($wpdb->prepare(
    "SELECT u.ID, u.user_email, u.display_name
    FROM {$wpdb->users} u
    LEFT JOIN $table m ON u.ID = m.user_id AND m.membership_year = %d
    WHERE m.status IS NULL OR m.status = 'inactive'",
    $current_year + 1
));
```

## Constants

```php
WDTA_MEMBERSHIP_VERSION         // Plugin version
WDTA_MEMBERSHIP_PLUGIN_DIR      // Full path to plugin directory
WDTA_MEMBERSHIP_PLUGIN_URL      // URL to plugin directory
```

**Usage:**
```php
$css_url = WDTA_MEMBERSHIP_PLUGIN_URL . 'assets/css/custom.css';
require_once WDTA_MEMBERSHIP_PLUGIN_DIR . 'includes/custom-functions.php';
```

## Security

### Nonce Verification

All AJAX requests verify nonces:

```php
if (!wp_verify_nonce($_POST['nonce'], 'wdta_membership_nonce')) {
    wp_send_json_error(array('message' => 'Security check failed'));
}
```

### Capability Checks

Admin functions check capabilities:

```php
if (!current_user_can('manage_options')) {
    return;
}
```

### Data Sanitization

All input is sanitized:

```php
$username = sanitize_user($_POST['username']);
$email = sanitize_email($_POST['email']);
$amount = floatval($_POST['amount']);
```

## Testing

### Unit Test Example

```php
class WDTA_Membership_Tests extends WP_UnitTestCase {
    
    public function test_membership_activation() {
        $user_id = $this->factory->user->create();
        $year = 2025;
        
        $result = WDTA_Membership_Status::activate_membership($user_id, $year, array(
            'amount' => 50.00,
            'method' => 'test'
        ));
        
        $this->assertTrue(WDTA_Membership_Status::is_member_active($user_id, $year));
    }
}
```

## Debugging

Enable WordPress debugging:

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Add custom logging:

```php
if (WP_DEBUG_LOG) {
    error_log('WDTA Membership: User ' . $user_id . ' activated for year ' . $year);
}
```

## Performance Optimization

### Database Indexes

The plugin creates indexes on:
- `user_id` - Fast user lookup
- `membership_year` - Fast year filtering
- `status` - Fast status filtering
- `user_id + membership_year` - Unique constraint and fast combined lookup

### Caching Recommendations

```php
// Cache active member count
$count = wp_cache_get('wdta_active_members_' . $year);
if ($count === false) {
    // Query database
    $count = /* ... */;
    wp_cache_set('wdta_active_members_' . $year, $count, '', 3600);
}
```

## Extending the Plugin

### Add Custom Fields to Registration

1. Modify `membership_form_shortcode()` to add fields
2. Update `ajax_register_and_pay()` to save additional data
3. Use WordPress user meta to store extra information

### Add Custom Email Types

1. Create new method in `WDTA_Membership_Email` class
2. Add settings in `WDTA_Membership_Admin`
3. Trigger email in appropriate location

### Add Custom Status Types

1. Modify database status validation
2. Update admin interface to display new statuses
3. Create logic for status transitions

## Version History

### 1.0.0
- Initial release with all core features
- Registration and renewal forms
- Automated status management
- Configurable email system
- Admin dashboard and member management
