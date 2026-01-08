# Changelog

All notable changes to the WDTA Membership plugin will be documented in this file.

## [3.3] - 2026-01-08

### Fixed
- **Email recipient list excludes administrators**: Fixed critical bug where administrators were being included in scheduled email reminder lists. Administrators don't pay for membership and should not receive payment reminder emails.
- **Grace period members now included in email lists**: Fixed bug where members in grace_period status were not always being included in the scheduled email reminder lists. Grace period members should continue to receive reminder emails until they pay or their access is revoked on April 1st.

### Changed
- Updated `get_users_without_membership()` function to:
  - Explicitly exclude users with administrator privileges (checked via `manage_options` capability) from email recipient lists
  - Explicitly include users with `status = 'grace_period'` in the query to ensure they receive reminder emails
  - Added improved documentation explaining which user categories should receive emails

### Technical Details
- Modified `get_users_without_membership()` in `includes/class-wdta-database.php` to use WordPress's built-in `user_can()` function for administrator filtering
- Administrator filtering uses `user_can($user->ID, 'manage_options')` which is the standard WordPress way to check for administrator privileges, more secure and efficient than SQL string matching
- Updated SQL Query 2 to explicitly check for `m.status = 'grace_period'` in addition to other conditions
- Version bumped from 3.2 to 3.3

## [3.2] - 2026-01-08

### Added
- **Grace period functionality**: Re-introduced grace period for unpaid memberships
- Added `grace_period_member` user role back to the system
- Grace period members retain full access to restricted content while receiving reminder emails
- 3-month grace period (January 1 - March 31) for members who haven't renewed

### Changed
- **January 1st behavior**: Unpaid members now move to `grace_period` status instead of becoming immediately inactive
- **March 31st behavior**: Grace period members become `inactive` and lose access to restricted content
- Updated access control to allow both `active_member` and `grace_period_member` roles to access restricted pages
- Enhanced role determination logic to handle three states: active, grace_period, and inactive

### Technical Details
- Modified `includes/class-wdta-cron.php`:
  - Added `move_to_grace_period()` function called on January 1st
  - Added `deactivate_grace_period_members()` function called on April 1st
  - Both functions include role synchronization with error handling
- Modified `includes/class-wdta-user-roles.php`:
  - Re-added `grace_period_member` role in `add_custom_roles()`
  - Updated `determine_membership_role()` to handle grace_period status
- Modified `includes/class-wdta-database.php`:
  - Updated `has_active_membership()` to allow grace_period members access

### How It Works
**Timeline:**
1. **Before Dec 31**: Active members with completed payments
2. **Jan 1**: Unpaid active members → `grace_period_member` role (retain full access)
3. **Jan 1 - Mar 31**: Grace period members continue to receive reminder emails and have full access
4. **Apr 1**: Grace period members → `inactive_member` role (lose access to restricted content)

**User States:**
- `active_member`: Paid membership, full access
- `grace_period_member`: Unpaid after Jan 1, full access until Apr 1, continues receiving reminder emails
- `inactive_member`: No access to restricted content

## [3.1.0] - 2026-01-08

### Fixed
- **Email scheduling for inactive users**: Fixed critical bug where users with inactive membership status were not being included in email reminder schedules
- Users with current year membership records that have `status = 'inactive'` or incomplete payments now correctly receive reminder emails
- Ensures that all unpaid members receive "After X days past the deadline" reminder emails regardless of whether they have an inactive membership record

### Changed
- Updated `get_users_without_membership()` function to include two categories of users:
  1. Users who had active membership in previous year but no completed payment in current year (existing behavior)
  2. Users who have current year membership with inactive status or incomplete payment (new behavior)

### Technical Details
- Modified `includes/class-wdta-database.php` to use UNION query that catches both previous year active members and current year inactive members
- Ensures comprehensive email reminder delivery to all members who need to renew

## [3.2.0] - 2025-11-24

### Fixed
- **Payment amount mismatch**: Fixed critical bug where backend charged hardcoded $970.90 while frontend displayed dynamic price from settings
- Users no longer receive "insufficient funds" errors when membership price is changed in admin settings
- Payment amount is now calculated dynamically from `wdta_membership_price` option with proper 2.2% Stripe surcharge

### Changed
- Replaced hardcoded `MEMBERSHIP_AMOUNT_WITH_SURCHARGE` and `MEMBERSHIP_AMOUNT_CENTS` constants with dynamic calculation methods
- Added `get_membership_base_price()`, `get_membership_amount_with_surcharge()`, and `get_membership_amount_cents()` helper methods
- All payment intent creation now uses dynamic amounts consistent with frontend display

### Technical Details
- Modified `includes/class-wdta-payment-stripe.php` to read membership price from WordPress options
- Ensured consistency between frontend display and Stripe API charges
- Proper cent conversion using `intval(round($amount * 100))` to avoid floating-point precision issues

## [3.0.0] - 2024-11-24

### Fixed
- **Edit button functionality**: Fixed Edit button on admin memberships list page not responding to clicks
- Improved JavaScript event handling using event delegation for more robust behavior
- Enhanced script loading checks to support various WordPress admin page hook formats
- Added comprehensive debug logging to help diagnose script loading issues

### Changed
- Refactored all admin JavaScript event handlers to use event delegation
- Improved `enqueue_admin_scripts` hook checking with explicit page list and fallback patterns
- Added validation check for `wdtaAdmin` object before executing AJAX calls

### Technical Details
- Event handlers now use `$(document).on()` instead of direct element binding
- Admin page hooks now explicitly checked: `toplevel_page_wdta-memberships`, `membership_page_wdta-*`
- Added fallback checks for both `wdta-` and `wdta_` patterns in hook names
- Console logging added for debugging (to be removed in production)

## [2.1.0] - 2024-11-24

### Added - Dynamic Email Reminder System
- **Dynamic reminder configuration**: Admins can now add/remove email reminders dynamically
- **Flexible timing**: Configure reminders to be sent X days or weeks BEFORE or AFTER membership expiry
- **Add/Remove reminders**: "Add Another Reminder" button to create unlimited custom reminders
- **Individual enable/disable**: Each reminder has its own checkbox to enable or disable it
- **Default configuration**: New installations start with 1 reminder (30 days before expiry)

### Changed
- Replaced 8 hardcoded reminder emails with dynamic, configurable system
- Reminders now stored in WordPress options as serialized array
- Cron job updated to process dynamic reminders based on configuration
- Email templates page completely redesigned for dynamic reminders

### Migration
- Automatic migration of existing hardcoded reminders to new dynamic system
- Runs once on plugin initialization
- Preserves all existing reminder settings (enabled/disabled, subject, body)
- Falls back to default 1 reminder if no existing configuration found

### Technical Details
- New option: `wdta_email_reminders` stores array of reminder configurations
- Each reminder contains: id, enabled, timing, unit, period, subject, body
- JavaScript handles add/remove functionality in admin interface
- Enhanced CSS styling for reminder sections

## [1.2.0] - 2024-11-23

### Changed - BREAKING CHANGES
- **Removed grace period**: Membership status is now binary (active/inactive only)
- **Changed expiry date**: Memberships now expire on December 31st instead of March 31st
- **Automatic deactivation**: Unpaid memberships become inactive on January 1st (previously April 1st)
- Removed `grace_period_member` user role

### Added
- **Inactive users report**: New email sent on January 1st listing all inactive members
- **Email configuration**: Enable/disable checkboxes for all 8 reminder emails
- Recipients configuration for inactive users report (comma-separated emails)
- Admin controls to disable specific reminder emails

### Notes
- All 8 email reminders (3 before Dec 31, 5 after) can now be individually enabled/disabled
- Payment information, Stripe/Bank settings, and access control preserved from v1.1.x
- User roles and restricted pages functionality maintained

## [1.1.4] - 2024-11-21

### Documentation
- Added comprehensive troubleshooting section to README
- Added clear instructions for flushing permalinks after plugin installation
- Documented common issues and solutions for custom login page 404 errors
- Clarified that permalinks must be flushed once after installing via symlink or Git Updater

### Notes
- WDTA = Workplace Drug Testing Australia
- Custom login page uses WordPress rewrite rules that require permalink flush

## [1.1.3] - 2024-11-21

### Added
- WYSIWYG editor (TinyMCE) for all 8 email templates
- Rich text formatting in email editor: bold, italic, underline, lists, links, alignment
- Visual email composition without HTML knowledge required

### Documentation
- Added custom login page documentation to README
- Documented `/member-login/` URL and lost password functionality
- Updated usage instructions for member login

### Fixed
- Organization name clarified: WDTA = Workplace Drug Testing Australia (not dental)

## [1.1.2] - 2024-11-21

### Fixed
- Access control bug where approved bank transfer payments weren't granting access to restricted pages
- Improved membership verification logic to correctly check both Stripe and bank transfer payments

### Added
- Date format standardization to dd/mm/yyyy (Australian format) throughout entire plugin
- Email template editor with customizable subjects and content for all 8 automated emails
- Available email placeholders: {user_name}, {user_email}, {year}, {amount}, {deadline}, {renewal_url}, {site_name}

### Changed
- All dates now display in dd/mm/yyyy format sitewide
- Membership expiry dates, payment dates, admin dashboard, email notifications all use dd/mm/yyyy

## [1.1.1] - 2024-11-21

### Changed
- Smart year selector logic: current members only see next year payment option from November onwards
- Members with active current year membership no longer see option to repay current year
- Cleaner, more intuitive user experience for year selection

## [1.1.0] - 2024-11-21

### Added
- Version tracking system - plugin version updated with each change
- 2.2% Stripe card processing surcharge clearly displayed
- Custom login page at `/member-login/` with professional gradient design
- Lost password functionality at `/member-login/lost-password/`
- AJAX-powered login (no page refresh required)
- Remember me checkbox
- Auto-redirect from wp-login.php to custom branded page

### Changed
- Pricing breakdown now shows: Membership fee $950.00 + Card processing fee (2.2%) $20.90 = Total $970.90 AUD
- Surcharge displayed on payment form and in email confirmations
- Bank transfer remains $950.00 (no surcharge)

## [1.0.0] - 2024-11-21

### Added
- Initial release of WDTA Membership plugin
- Stripe payment integration for $950 AUD annual membership fee
- Bank transfer payment support with admin approval workflow
- Automated email notification system with 8 scheduled reminders
- Page access control system for member-only content
- Admin dashboard for membership management
- Settings page for Stripe, bank account, email, and access control configuration
- Member portal with shortcodes: `[wdta_membership_form]` and `[wdta_membership_status]`
- Custom database table for membership tracking
- WordPress Cron integration for scheduled tasks
- Payment approval/rejection system for bank transfers
- Comprehensive documentation (README.md and INSTALL.md)

### Features

#### Payment Processing
- Credit card payments via Stripe with secure webhook handling
- Bank transfer payments with admin verification
- Payment history tracking
- Multiple payment method support per user

#### Access Control
- Configurable page restrictions
- Automatic access revocation after March 31st deadline
- Custom access denied pages
- Admin bypass for testing
- Login requirement checks

#### Email Notifications
Eight automated email reminders sent on schedule:
- December 1st: 1 month before due date
- December 25th: 1 week before due date
- December 31st: 1 day before due date
- January 2nd: 1 day after due date
- January 8th: 1 week after due date
- January 31st: End of first month
- February 28th/29th: End of second month
- March 31st: Final deadline notice

#### Admin Features
- Membership list with filtering by year and status
- Payment approval interface
- Stripe and bank account configuration
- Email template customization
- Restricted page selection
- Access denied page configuration

#### Database
- Custom table: `wp_wdta_memberships`
- Tracks payment status, method, dates, and references
- Unique constraint per user per year
- Expiry date tracking
- Status management (pending, active, expired, rejected)

### Technical Details
- WordPress 5.0+ compatible
- PHP 7.2+ required
- MySQL 5.6+ required
- REST API endpoint for Stripe webhooks
- AJAX-powered admin interface
- Secure with nonces and prepared statements
- No security vulnerabilities (CodeQL verified)

### File Structure
- 18 files total
- 2,138 lines of code
- Modular class-based architecture
- Separation of concerns (payment, email, access control, admin)
- Template system for frontend display
- Asset management (CSS/JS)

### Developer Notes
- Uses WordPress singleton pattern
- Hooks for extensibility
- Filters for customization
- Well-documented code
- PSR-compatible naming conventions

### Security
- AJAX nonce verification
- Database prepared statements
- Input sanitization and validation
- Stripe webhook signature verification
- Admin capability checks
- No exposed sensitive data

---

## Future Enhancements (Roadmap)

### Planned for 1.1.0
- [ ] Export membership data to CSV
- [ ] Import members from CSV
- [ ] Membership renewal discount codes
- [ ] Multiple membership tiers/levels
- [ ] Automatic currency conversion
- [ ] Member dashboard widget
- [ ] Email notification preview in admin
- [ ] Test mode for email system

### Planned for 1.2.0
- [ ] PayPal payment integration
- [ ] Recurring payment support
- [ ] Member directory
- [ ] Activity logging
- [ ] Advanced reporting
- [ ] Member communication tools
- [ ] Custom member fields
- [ ] Integration with popular membership plugins

### Planned for 2.0.0
- [ ] Multi-year memberships
- [ ] Family/group memberships
- [ ] Partial payment plans
- [ ] Member benefits management
- [ ] Event registration integration
- [ ] Mobile app API
- [ ] Advanced analytics
- [ ] White-label options

---

## Version History

| Version | Release Date | Notes |
|---------|--------------|-------|
| 1.0.0   | 2024-11-21  | Initial release |

---

## Upgrade Notes

### Upgrading to 1.0.0
This is the initial release. No upgrade path needed.

### Future Upgrades
Always backup your database before upgrading. The plugin will automatically:
- Update database schema if needed
- Preserve existing membership data
- Maintain payment history
- Keep all settings

---

## Support & Contributions

For bug reports, feature requests, or contributions:
- Create an issue on GitHub
- Contact WDTA development team
- Submit pull requests for review

---

## Credits

Developed for WDTA (Workplace Drug Testing Australia)
Built on WordPress platform
Stripe payment integration
Follows WordPress coding standards

---

## License

GPL v2 or later
