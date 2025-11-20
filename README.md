# WP Site Notifications

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.0%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPLv2%2B-green.svg)](LICENSE)

A comprehensive WordPress plugin for sending customizable email notifications. Track post status changes, monitor administrative actions, and configure custom SMTP settings for reliable email delivery.

---

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Translation](#translation)
- [Customization](#customization)
- [Requirements](#requirements)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Support](#support)
- [License](#license)

---

## Features

### 📧 Post Notifications

Track all important post status changes:

- **Post submitted for review** (Pending) - When authors submit content for approval
- **Post published** - When content goes live
- **Post saved as draft** - When work is saved
- **Post scheduled** - When future publications are set
- **Published post updated** - When live content is modified
- **Post moved to trash** - When content is deleted

### 🔐 Admin Notifications

Monitor critical administrative actions grouped by category:

#### User Management
- New user registration
- User deleted
- User role changed

#### Plugin Management
- Plugin activated
- Plugin deactivated
- Plugin updated

#### Theme Management
- Theme switched
- Theme updated

#### Core Updates
- WordPress core updated

#### Security
- Failed login attempts (rate-limited)
- Password reset requests

Each notification group has its own email recipient, allowing you to route different types of alerts to different team members.

### 📮 SMTP Configuration

Send emails through your own mail server with advanced features:

- **Server settings**: Host, port, encryption (SSL/TLS)
- **Multiple email accounts**: Configure different sender emails with their own credentials
- **Account selection**: Automatic credential matching based on sender email
- **Test functionality**: Send test emails to verify configuration

### 👥 Role Management

- Select any WordPress role to receive notifications
- **Automatic custom role detection** - Works with roles from plugins/themes
- User count display for each role
- Visual distinction between built-in and custom roles
- Individual user selection in addition to roles
- Duplicate prevention (users with multiple roles get one email)

### 🎨 User Experience

- Clean, HTML-formatted emails with details and action links
- Prevents spam from autosave and revisions
- Rate limiting on update notifications (max 1 per hour)
- Support for custom post types

### 🌍 Translation Ready

- **Fully internationalized** and translation-ready
- Includes **Portuguese (pt_PT)** and **Spanish (es_ES)** translations
- See [TRANSLATION.md](TRANSLATION.md) for adding your language

## Installation

### Method 1: Manual Installation

1. Download the latest release from the [Releases page](../../releases)
2. Upload the `wp-site-notifications` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress
4. Go to **Settings > WP Notifications** to configure

### Method 2: WordPress Admin

1. Go to **Plugins > Add New** in your WordPress admin
2. Search for "WP Site Notifications"
3. Click **Install Now** and then **Activate**
4. Go to **Settings > WP Notifications** to configure

### Method 3: WP-CLI

```bash
wp plugin install wp-site-notifications --activate
```

### First-Time Setup

Upon activation, default settings are automatically configured:
- Enabled notifications: Pending, Published, Scheduled
- Default recipients: Administrators only

You can customize these in **Settings > WP Notifications**.

## Configuration

Navigate to **Settings > WP Notifications** in your WordPress admin panel.

### Posts Tab

Configure post-related notifications:

#### Notification Types

| Notification Type | When It's Sent |
|------------------|----------------|
| Post submitted for review | Author submits a post (Pending status) |
| Post published | Post goes live for the first time |
| Post saved as draft | Post is saved as draft |
| Post scheduled | Post is scheduled for future publication |
| Published post updated | Changes are made to an already published post |
| Post moved to trash | Post is deleted |

#### Recipient Roles

Choose which user roles receive notifications:

**Built-in WordPress Roles**
- Administrator
- Editor
- Author
- Contributor
- Subscriber

**Custom Roles (Automatically Detected)**
The plugin automatically detects roles added by plugins, themes, or custom code.

#### Individual Users

Select specific users to receive notifications regardless of their role.

#### Post Types

Choose which post types trigger notifications (posts, pages, custom post types).

### Admin Tab

Configure administrative notifications with dedicated email addresses for each group:

| Group | Notifications | Email Field |
|-------|--------------|-------------|
| User Management | Registration, deletion, role changes | Separate email |
| Plugin Management | Activation, deactivation, updates | Separate email |
| Theme Management | Switching, updates | Separate email |
| Core Updates | WordPress updates | Separate email |
| Security | Failed logins, password resets | Separate email |

This allows routing different alert types to different team members (e.g., security alerts to security@company.com).

### SMTP Tab

Configure email delivery through your SMTP server:

#### Server Settings
- **Enable SMTP**: Toggle SMTP on/off
- **SMTP Host**: Your mail server (e.g., smtp.gmail.com)
- **SMTP Port**: Common ports - 25, 465 (SSL), 587 (TLS)
- **Encryption**: None, SSL, or TLS
- **Authentication**: Enable/disable SMTP auth

#### Email Accounts

Add multiple email accounts, each with:
- **From Email**: Sender address
- **From Name**: Display name
- **SMTP Username**: Account credentials
- **SMTP Password**: Account password

The system automatically uses the correct credentials based on the sender email address.

#### Default Account

Select which account to use when no specific sender is configured.

#### Test Email

Send a test email to verify your SMTP configuration is working correctly.

## Usage

### What's Included in Notification Emails

Each email contains:

- **Site information**: Name and link to your WordPress site
- **Event details**: What happened and when
- **Relevant data**: User info, post details, plugin names, etc.
- **Action links**: Quick access to relevant admin pages
- **Clean HTML formatting**: Professional, readable layout

### Example Post Notification

```
[Your Site Name]

Post Status Change

Title: "10 Tips for Better Content"
Author: John Doe
Status: Published

[View Post] [Edit Post]
```

### Example Admin Notification

```
[Your Site Name]

Plugin Activated

Plugin: WooCommerce
Version: 8.0.0
Date: 2025-01-15 10:30:00

[View Plugins]
```

## Customization

### Developer Hooks

The plugin provides filter hooks for advanced customization:

#### Modify Email Subject

```php
add_filter('post_notifications_email_subject', function($subject, $notification_type, $post) {
    return "Custom: " . $subject;
}, 10, 3);
```

#### Modify Email Body

```php
add_filter('post_notifications_email_message', function($message, $notification_type, $post, $author_name) {
    return $message . "\n\nCustom footer text";
}, 10, 4);
```

#### Modify Recipients List

```php
add_filter('post_notifications_recipients', function($recipients, $notification_type, $post) {
    // Add a specific user
    $extra_user = get_user_by('email', 'custom@example.com');
    if ($extra_user) {
        $recipients[] = $extra_user;
    }
    return $recipients;
}, 10, 3);
```

### Technical Details

| Aspect | Implementation |
|--------|----------------|
| **Post Hook** | `transition_post_status` |
| **Admin Hooks** | Various WordPress action hooks |
| **Email Function** | WordPress native `wp_mail()` with optional SMTP |
| **Rate Limiting** | Failed logins (5 min), post updates (1 hour) |
| **Security** | CSRF protection, XSS prevention, input sanitization |

## Translation

The plugin is **fully internationalized** and ready for translation.

### Available Languages

| Language | Code | Status |
|----------|------|--------|
| English | en_US | Default |
| Portuguese (Portugal) | pt_PT | Complete |
| Spanish (Spain) | es_ES | Complete |

### Add Your Language

Want to translate to your language? See [TRANSLATION.md](TRANSLATION.md) for step-by-step instructions.

Contributions are welcome! Submit your translations via pull request.

## Requirements

| Requirement | Minimum Version |
|-------------|-----------------|
| WordPress | 5.0+ |
| PHP | 7.0+ |

---

## Changelog

### Version 2.0.0

#### New Features
- **Admin Notifications**: Monitor user management, plugins, themes, core updates, and security events
- **SMTP Support**: Configure custom mail server with multiple email accounts
- **Multiple SMTP Accounts**: Different credentials for different sender emails
- **Enhanced Post Notifications**: Support for custom post types and individual user selection

#### Improvements
- Renamed plugin from "Post Notifications" to "WP Site Notifications"
- Reorganized settings into tabbed interface (Posts, Admin, SMTP)
- Group-based email routing for admin notifications
- Better UI for notification configuration

### Version 1.0.0 (Initial Release)

#### Features
- 6 notification types for post status changes
- Role-based recipient selection (including custom roles)
- HTML-formatted email templates
- Full translation support (pt_PT, es_ES included)
- Developer hooks for extensibility

#### Security
- CSRF token protection
- XSS prevention
- SQL injection hardening
- Input sanitization and validation

#### Performance
- Rate limiting (1 update notification per hour)
- Autosave/revision filtering
- Duplicate notification prevention

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

---

## Contributing

Contributions are welcome! Here's how you can help:

### Report Bugs

Found a bug? [Open an issue](../../issues/new) with:
- WordPress version
- PHP version
- Plugin version
- Steps to reproduce
- Expected vs actual behavior

### Suggest Features

Have an idea? [Open a feature request](../../issues/new) describing:
- The problem you're trying to solve
- Your proposed solution
- Any alternative solutions considered

### Submit Pull Requests

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Include inline documentation
- Test thoroughly before submitting

---

## Support

### Documentation

- [Custom Roles Guide](CUSTOM-ROLES.md)
- [Translation Guide](TRANSLATION.md)
- [Changelog](CHANGELOG.md)

### Get Help

- **Bug reports**: [GitHub Issues](../../issues)
- **Feature requests**: [GitHub Issues](../../issues)
- **Questions**: [GitHub Discussions](../../discussions)

### Show Your Support

If this plugin helped you, please:
- Star this repository
- Share it on social media
- Write a review

---

## License

This plugin is licensed under the **GNU General Public License v2.0 or later**.

```
WP Site Notifications - WordPress Plugin
Copyright (C) 2025

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

See [LICENSE](LICENSE) file for full license text.

---

<div align="center">

**Made with care for the WordPress community**

[Report Bug](../../issues) · [Request Feature](../../issues) · [Documentation](../../wiki)

</div>
