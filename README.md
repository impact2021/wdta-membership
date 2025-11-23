# WDTA Membership Plugin

A comprehensive WordPress membership management plugin for WDTA (Western Dance Teachers Association).

## Features

- **Registration & Payment**: Non-logged users can create an account and pay for current year membership on the same page
- **Membership Renewal**: Logged-in users can renew for next year
- **Status Management**: Active/inactive status only (no grace period)
- **Automatic Deactivation**: Unpaid memberships become inactive on January 1st
- **Email Notifications**: Configurable reminder emails and inactive users reports
- **Admin Dashboard**: Complete membership management interface

## Shortcodes

- `[wdta_membership_form]` - Registration form for non-logged users (current year)
- `[wdta_membership_renewal_form]` - Renewal form for logged-in users (next year)

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through WordPress admin
3. Configure settings at **WDTA Membership → Settings**

## Documentation

Full documentation is available in `/docs/README.md`

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Version

1.0.0
