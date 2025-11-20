# Changelog

All notable changes to the WP Site Notifications plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Visual email template editor
- Per-user notification preferences
- Notification history log

---

## [2.0.0] - 2025-11-20

### Added
- **Admin Notifications**: New notification system for administrative actions
  - User Management: registration, deletion, role changes
  - Plugin Management: activation, deactivation, updates
  - Theme Management: switching, updates
  - Core Updates: WordPress core updates
  - Security: failed login attempts, password reset requests
- **SMTP Configuration**: Custom mail server support
  - Server settings: host, port, encryption (SSL/TLS)
  - Authentication toggle
  - Multiple email accounts with individual credentials
  - Automatic credential matching based on sender email
  - Default account selection
  - Test email functionality
- **Enhanced Post Notifications**:
  - Custom post type support
  - Individual user selection (in addition to roles)
- **Tabbed Settings Interface**: Organized settings into Posts, Admin, and SMTP tabs
- **Group-based Email Routing**: Each admin notification group has its own email recipient

### Changed
- Renamed plugin from "Post Notifications" to "WP Site Notifications"
- Updated text domain from `post-notifications` to `wp-site-notifications`
- Changed menu slug from `post-notifications` to `wp-site-notifications`
- Reorganized settings page with tabbed navigation
- Updated plugin URI to reflect new name

### Technical Details
- New files added:
  - `includes/class-admin-notifications-handler.php`
  - `includes/class-smtp.php`
  - `includes/admin/views/tab-smtp.php`
- Modified settings structure to support admin notifications and SMTP accounts
- Added rate limiting for failed login notifications (5 minutes per username)

---

## [1.0.0] - 2025-11-12

### Added
- Initial release of Post Notifications plugin
- Email notifications for 6 post status changes:
  - Post submitted for review (Pending)
  - Post published
  - Post saved as draft
  - Post scheduled for future publication
  - Published post updated
  - Post moved to trash
- Role-based recipient selection:
  - Support for all built-in WordPress roles
  - Automatic detection of custom roles from plugins/themes
  - User count display for each role
  - Visual distinction between built-in and custom roles
- HTML-formatted email templates with:
  - Site name and link
  - Post title and author
  - Current status
  - Action links (view, edit, review)
- Security features:
  - CSRF token protection
  - XSS prevention
  - SQL injection hardening
  - Input sanitization and validation
- Performance optimizations:
  - Rate limiting (1 update notification per hour per post)
  - Autosave/revision filtering
  - Duplicate notification prevention
- Translation support:
  - Full internationalization (i18n) ready
  - Portuguese (Portugal) - pt_PT translation included
  - Spanish (Spain) - es_ES translation included
- Developer extensibility:
  - `post_notifications_email_subject` filter hook
  - `post_notifications_email_message` filter hook
  - `post_notifications_recipients` filter hook
- Documentation:
  - Comprehensive README.md
  - Quick start guide (QUICK-START.md)
  - Translation instructions
  - Custom roles documentation

### Technical Details
- Minimum WordPress version: 5.0
- Minimum PHP version: 7.0
- Uses WordPress `transition_post_status` hook
- Uses WordPress native `wp_mail()` function

---

## Release Notes

### Version 2.0.0
This major release transforms Post Notifications into WP Site Notifications, adding comprehensive admin monitoring and SMTP support.

**Highlights:**
- Monitor critical administrative actions (users, plugins, themes, core, security)
- Route different notification types to different email addresses
- Configure custom SMTP server with multiple email accounts
- Support for custom post types
- Better organized settings with tabbed interface

**Upgrade Notes:**
- The plugin will be renamed in your plugins list
- Menu location changes from "Post Notifications" to "WP Notifications"
- Existing post notification settings will be preserved
- You'll need to configure admin notifications and SMTP settings separately

**Getting Started with New Features:**
1. Go to Settings > WP Notifications
2. Configure Admin notifications in the Admin tab
3. Set up SMTP in the SMTP tab (optional)
4. Save settings

### Version 1.0.0
This is the initial stable release of Post Notifications. The plugin has been thoroughly tested and is production-ready.

**Highlights:**
- Complete notification system for all major post status changes
- Flexible role-based targeting with automatic custom role detection
- Professional HTML email templates
- Built with security and performance in mind
- Fully translated to Portuguese and Spanish
- Developer-friendly with extensible hooks

**Getting Started:**
1. Install and activate the plugin
2. Go to Settings > WP Notifications
3. Select notification types and recipient roles
4. Save settings and start receiving notifications!

For detailed information, see [README.md](README.md).

---

[Unreleased]: https://github.com/pedrocandeias/wp-site-notifications/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/pedrocandeias/wp-site-notifications/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/pedrocandeias/wp-site-notifications/releases/tag/v1.0.0
