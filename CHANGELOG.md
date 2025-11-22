# Changelog

All notable changes to the WDTA Membership plugin will be documented in this file.

## [1.1.8] - 2024-11-22

### Added
- New `[wdta_logout_link]` shortcode for adding logout links in menus and content
- Shortcode supports optional attributes:
  - `text`: Custom link text (default: "Log Out")
  - `redirect`: URL to redirect to after logout (default: home page)
  - `class`: Custom CSS class for styling
- Link only displays when user is logged in (hidden for non-logged-in users)

### Usage Examples
- Basic: `[wdta_logout_link]`
- Custom text: `[wdta_logout_link text="Sign Out"]`
- Custom redirect: `[wdta_logout_link redirect="/login"]`
- With CSS class: `[wdta_logout_link class="menu-item"]`

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
