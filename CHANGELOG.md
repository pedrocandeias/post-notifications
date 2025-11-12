# Changelog

All notable changes to the Post Notifications plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Custom post type support
- Visual email template editor
- Additional notification triggers
- Per-user notification preferences
- Notification history log

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
- Only tracks standard posts (not pages or custom post types)
- Uses WordPress native `wp_mail()` function

---

## Release Notes

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
2. Go to Settings > Post Notifications
3. Select notification types and recipient roles
4. Save settings and start receiving notifications!

For detailed information, see [README.md](README.md).

---

[Unreleased]: https://github.com/pedrocandeias/post-notifications/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/pedrocandeias/post-notifications/releases/tag/v1.0.0
