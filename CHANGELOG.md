# Changelog

All notable changes to the WDTA Membership plugin will be documented in this file.

## [3.12] - 2026-01-12

### Changed
- **FPDF Library Version Update**: Updated FPDF library version from 1.85 to 1.86
  - Updated version constant to match latest stable FPDF release
  - Ensures compatibility with modern PDF readers and specifications
  - All PDF generation features remain fully functional

### Technical Details
- Modified `includes/lib-fpdf/fpdf.php`:
  - Updated `FPDF_VERSION` constant from '1.85' to '1.86'
- Updated plugin version from 3.11 to 3.12

## [3.10] - 2026-01-12

### Fixed
- **PDF Generation Error**: Fixed critical error that prevented PDF receipt generation
  - Replaced `die()` calls with proper exception handling in FPDF library
  - Added comprehensive error handling and logging in PDF receipt generation
  - Added validation for logo image files before attempting to load them
  - PDFs now generate successfully even when logo is unavailable
  - Improved error messages to provide clear feedback to administrators
  - Added try-catch blocks throughout PDF generation process
  - Added missing `SetX()` and `SetXY()` methods to FPDF library

### Technical Details
- Modified `includes/lib-fpdf/fpdf.php`:
  - Changed `die()` calls to `throw new Exception()` in `_parseimage()` method
  - Added `@` error suppression to `getimagesize()` to prevent PHP warnings
  - Added `SetX()` and `SetXY()` positioning methods
- Enhanced `includes/class-wdta-pdf-receipt.php`:
  - Added `try-catch` block to entire `generate_receipt()` method
  - Enhanced `get_logo_path()` with better validation and error logging
  - Added image validation using `getimagesize()` before returning cached logo
  - Improved error logging with descriptive messages
  - Added fallback to continue without logo if image loading fails
- Updated `includes/class-wdta-admin.php`:
  - Wrapped PDF generation in `download_receipt()` with try-catch error handling
  - Added detailed error messages for failed PDF generation
  - Improved error logging for debugging

## [3.9] - 2026-01-12

### Added
- **Organization Details Settings**: Added new settings section for organization information displayed on PDF receipts
  - Organization Name (defaults to "Workplace Drug Testing Association")
  - Organization Address (full mailing address)
  - ABN / GST Number (Australian Business Number)
  - Contact Phone
  - Contact Email (defaults to "admin@wdta.org.au")
  - Website URL (defaults to "https://www.wdta.org.au")
  - Logo URL (customizable logo for receipts, cached locally for 7 days)
  - **Location**: WDTA Membership → Settings → Payment Settings → Organization Details (for Receipts)

### Changed
- **Improved PDF Receipt Generation**: Enhanced receipt layout and content
  - Added organization details in header (name, address, phone, email, website, ABN)
  - Added "Date Issued" field showing when receipt was generated
  - Added "Valid From" field showing membership start date (January 1)
  - Improved expiry date display to use actual expiry date from database
  - Organization details now pulled from admin settings instead of hardcoded values
  - Logo URL now configurable via admin settings
  - Logo cache updated to support changing logo URLs (uses MD5 hash of URL)
  - Logo cache refresh reduced from 30 days to 7 days for more frequent updates
  - Footer now uses organization name and website from settings

### Fixed
- **PDF Receipt Content**: Ensured all required information is included in receipts:
  - Membership year clearly displayed
  - Payment method (Credit Card or Bank Transfer)
  - Payment date (when payment was received)
  - Expiry date (membership validity period)
  - Receipt number (format: WDTA-YEAR-######)
  - Complete payment breakdown with fees
  - Organization contact information

### Technical Details
- Modified `admin/settings.php` to add organization details fields
- Updated `includes/class-wdta-admin.php` to save new organization options
- Enhanced `includes/class-wdta-pdf-receipt.php`:
  - Replaced hardcoded LOGO_URL constant with `get_logo_url()` method
  - Updated `get_logo_path()` to support dynamic logo URLs with MD5-based caching
  - Completely rewrote `generate_receipt()` method with improved layout
  - Added organization header section to PDF
  - Added "Date Issued" and "Valid From" fields
  - Improved footer with dynamic organization details

### Notes
- Existing installations will use default values for new organization settings
- Logo cache files are stored in wp-content/uploads/wdta-receipts/
- PDF receipts are automatically sent when members become active (both Stripe and Bank Transfer)
- Admins can resend receipts using the "Resend Email" button on the memberships list page

## [3.8] - 2026-01-12

### Added
- **Receipts Admin Page**: Added a new "Receipts" page in the admin menu to view and download PDF receipts for completed membership payments.
  - **Location**: WDTA Membership → Receipts
  - **Features**:
    - View all receipts for completed payments
    - Filter receipts by year
    - Download PDF receipts for any completed payment
    - Displays receipt number, member name, email, payment method, amount, and payment date
  - **PDF Generation**: Receipts are generated on-demand using the existing WDTA_PDF_Receipt class
  - **Receipt Format**: Professional PDF format with WDTA logo, member information, and payment breakdown

### Changed
- Updated version number from 3.7 to 3.8

### Technical Details
- Added `receipts_page()` method in `class-wdta-admin.php`
- Added `download_receipt()` AJAX endpoint in `class-wdta-admin.php`
- Created `admin/receipts.php` template file
- Added JavaScript handler for PDF download functionality in `assets/js/admin.js`
- Updated admin page hooks to include receipts page

## [3.6] - 2026-01-08

### Changed
- **Cleaned up Scheduled Emails page**: Simplified the scheduled emails page for better usability
  - Removed Debug/Sync button and debug panel
  - Removed "Automatic Processing" notification about hourly cron jobs
  - Removed "Note" about recipient list changes
  - Replaced verbose description with single concise sentence: "Scheduled emails that will be sent within the next 90 days"
  - Maintained all core functionality (viewing scheduled emails, Send Now buttons, recipient lists)

## [3.5] - 2026-01-08

### Added
- **Debug / Sync Button on Scheduled Emails Page**: Added powerful diagnostic tool to troubleshoot why scheduled emails may not be appearing in the list.
  - **Location**: WDTA Membership → Scheduled Emails → Click "Debug / Sync" button in top right
  - **Purpose**: Provides comprehensive diagnostic information to identify configuration issues, data problems, or filtering logic errors
  - **What it shows**:
    - User counts (total, admins, recipients by year)
    - Membership statistics (breakdown by status and payment status for each year)
    - Reminder configuration (enabled/disabled, timing details)
    - Expected scheduled emails (what should show and why)
    - Sent reminders tracking (batch and individual user level)
    - Administrator users (excluded from emails)
    - Sample user analysis (membership status and reminder eligibility)
    - Current date/time and timezone information
  - **Benefits**: 
    - No more guessing why emails aren't showing
    - Clear explanations of filtering logic
    - Identifies configuration errors immediately
    - Shows exactly which users should receive which reminders
    - Helps verify grace period members are included correctly

### Technical Details
- Added `debug_scheduled_emails()` AJAX endpoint in `class-wdta-admin.php`
- Added `calculate_expected_scheduled_emails()` helper method to predict what should display
- Added debug UI panel to `admin/scheduled-emails.php` with collapsible sections
- JavaScript handles AJAX call and formats comprehensive HTML output
- Debug report includes color-coded status indicators and detailed tables
- All diagnostic queries use same logic as actual scheduled email display
- Provides "Refresh Page" button after reviewing debug information

### Documentation
- Added "Troubleshooting Scheduled Emails" section to HOW-IT-WORKS.md
- Documented common issues and their solutions
- Provided step-by-step guide on using the debug output
- Explained what each section of the debug report means

## [3.4] - 2026-01-08

### Fixed
- **CRITICAL FIX: Grace period members now correctly appear in scheduled email lists**: Fixed the actual root cause bug that prevented grace period members from appearing in scheduled email reminder lists.
  - **What was broken**: On January 1st, unpaid members from the previous year are moved from `status = 'active'` to `status = 'grace_period'`. However, Query 1 in `get_users_without_membership()` was only looking for users with `status = 'active'` in the previous year, so it missed all the grace_period members!
  - **What was fixed**: Updated Query 1 to include BOTH `status = 'active' OR status = 'grace_period'` when looking at previous year memberships.
  - **Impact**: Grace period members will now correctly appear in the scheduled email list and receive reminder emails.

### Technical Details
- Modified Query 1 in `get_users_without_membership()` function (`includes/class-wdta-database.php`):
  - Changed: `WHERE m.membership_year = %d AND m.status = 'active'`
  - To: `WHERE m.membership_year = %d AND (m.status = 'active' OR m.status = 'grace_period')`
  - This ensures that users who were moved to grace_period on Jan 1st are still included in reminder emails for the current year
- Updated inline documentation to clarify the query logic

### Summary of What Was Wrong and How It Was Fixed

**The Problem:**
The scheduled email list page was correctly excluding administrators (fixed in v3.3), but grace period unpaid members were STILL NOT SHOWING in the list. This was causing unpaid members who should be receiving reminder emails to be missed.

**Root Cause:**
The `get_users_without_membership()` function has two SQL queries that are UNIONed together:
1. Query 1: Users with previous year membership but no current year payment
2. Query 2: Users with current year membership that is unpaid/grace_period/inactive

On January 1st, the system runs a cron job that moves unpaid members to `grace_period` status (in their PREVIOUS year membership record). However, Query 1 was only checking for `status = 'active'` in the previous year, completely missing all the `grace_period` members!

**The Fix:**
Changed Query 1 to check for `(status = 'active' OR status = 'grace_period')` so it includes users who:
- Had active membership last year and haven't renewed (status still 'active')
- Had active membership last year, didn't renew, and were moved to 'grace_period' on Jan 1st

**Verification:**
After this fix, grace period members correctly appear in the scheduled email lists on the admin page at `/wp-admin/admin.php?page=wdta-scheduled-emails`.

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
