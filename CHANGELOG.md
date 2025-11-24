# Changelog

All notable changes to the WDTA Membership plugin will be documented in this file.

## Version Status Note

**Current Version: 2.0.0** (as of 2024-11-24)

This release consolidates features from versions 1.0.0 through 1.2.0 into the official 2.0.0 release. The version number was updated to reflect the significant feature additions including:
- Inline registration with payment
- Configurable year cutoff settings
- Admin membership editing capabilities
- Customizable email templates with WYSIWYG editor
- Settings page tabs restoration
- Binary membership status (active/inactive)
- December 31st expiry date change

**Version 3.0 Status:** There is currently no Version 3.0 planned or in development. Future enhancements are outlined in the roadmap section below.

---

## [2.0.0] - 2024-11-24

### Major Release
This is a major version release that consolidates all features from v1.0.0 through v1.2.0.

### Features Included
- Complete membership management system
- Stripe and bank transfer payment processing
- Automated email notification system (8 configurable reminders)
- Page access control
- Inline registration with payment integration
- Admin membership editing
- Settings page with tabs (Payment, Access Control, Email, Documentation)
- WYSIWYG email template editor
- Configurable year cutoff for membership payments
- Custom login page at `/member-login/`
- Binary membership status (active/inactive - no grace period)

### Changed from Previous Versions
- **Expiry date**: Memberships now expire on December 31st (changed from March 31st)
- **Automatic deactivation**: Unpaid memberships become inactive on January 1st
- **Version numbering**: Consolidated to 2.0.0 to reflect maturity of the plugin

### Technical Details
- WordPress 5.0+ compatible
- PHP 7.2+ required
- MySQL 5.6+ required
- Annual membership fee: $950 AUD
- Stripe processing surcharge: 2.2% ($20.90)

---

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

**Note:** Version 2.0.0 has been released. The items previously planned for versions 1.1.0, 1.2.0, and 2.0.0 are now being considered for future releases (2.1.0+). There is currently **no Version 3.0 planned**.

### Potential Future Features (Version TBD)
The following features may be considered for future releases based on user feedback and requirements:

#### Enhanced Data Management
- [ ] Export membership data to CSV
- [ ] Import members from CSV
- [ ] Advanced reporting and analytics
- [ ] Activity logging

#### Payment & Membership Options
- [ ] PayPal payment integration
- [ ] Recurring payment support
- [ ] Multiple membership tiers/levels
- [ ] Membership renewal discount codes
- [ ] Multi-year memberships
- [ ] Family/group memberships
- [ ] Partial payment plans

#### Member Features
- [ ] Member directory
- [ ] Member dashboard widget
- [ ] Member communication tools
- [ ] Member benefits management
- [ ] Custom member fields

#### Admin & System Features
- [ ] Email notification preview in admin
- [ ] Test mode for email system
- [ ] Automatic currency conversion
- [ ] Integration with popular membership plugins

#### Advanced Features
- [ ] Event registration integration
- [ ] Mobile app API
- [ ] White-label options

---

## Version History

| Version | Release Date | Notes |
|---------|--------------|-------|
| 2.0.0   | 2024-11-24  | Major release - consolidates all v1.x features |
| 1.2.0   | 2024-11-23  | Binary membership status, Dec 31st expiry |
| 1.1.4   | 2024-11-21  | Documentation improvements |
| 1.1.3   | 2024-11-21  | WYSIWYG email editor |
| 1.1.2   | 2024-11-21  | Access control fixes, date formatting |
| 1.1.1   | 2024-11-21  | Smart year selector |
| 1.1.0   | 2024-11-21  | Custom login page, Stripe surcharge |
| 1.0.0   | 2024-11-21  | Initial release |

---

## Upgrade Notes

### Upgrading to 2.0.0
If you're running any v1.x version, no database changes are required. Version 2.0.0 is primarily a version number consolidation that reflects the mature state of the plugin with all features from v1.0.0-1.2.0 included.

**Important changes to note:**
- Membership expiry is December 31st (changed from March 31st in early versions)
- No grace period - membership status is binary (active/inactive)
- All existing settings, memberships, and payment data are preserved

### Upgrading to 1.0.0
This is the initial release. No upgrade path needed.

### General Upgrade Guidelines
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
