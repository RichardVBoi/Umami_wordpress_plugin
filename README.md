# Hamotech Umami Analytics

**Professional Umami Analytics Integration for WordPress**

Developed by [Hamotech Solutions](https://hamotechsolutions.com)

## Description

Hamotech Umami Analytics is an advanced WordPress plugin that seamlessly integrates Umami Analytics into your website. Built with privacy and performance in mind, this plugin offers comprehensive tracking features beyond basic pageview analytics.

### Key Features

#### 🎯 Advanced Event Tracking
- **Custom Event Triggers**: Automatically track user interactions
- **Comment Tracking**: Monitor comment submissions with metadata
- **Login Events**: Track user authentication events
- **Registration Tracking**: Monitor new user signups
- **File Downloads**: Automatic tracking of PDF, ZIP, DOC, and other file downloads
- **Outbound Links**: Track clicks on external links
- **404 Error Pages**: Monitor broken links and missing pages

#### 🔒 Privacy-First Approach
- **Respect Do Not Track**: Honor browser DNT settings
- **Granular User Control**: Choose who gets tracked
- **Admin Exclusion**: Optionally exclude admin users
- **Logged-in User Control**: Option to exclude all authenticated users
- **GDPR Compliant**: Privacy-focused analytics

#### ⚡ Performance & Integration
- **Lightweight**: Minimal impact on site performance
- **Dashboard Widget**: Quick access to your analytics
- **Settings Link**: Easy access from plugins page
- **Self-hosted Ready**: Works with your own Umami instance
- **Auto-configuration**: Simple setup process

## Installation

### Automatic Installation
1. Download the plugin ZIP file
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" and select the ZIP file
4. Activate the plugin
5. Navigate to Settings → Umami Analytics

### Manual Installation
1. Upload the `hamotech-umami-analytics` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu
3. Configure settings at Settings → Umami Analytics

## Configuration

### Basic Setup

1. **Enable Tracking**: Toggle the main switch to activate analytics
2. **Website ID**: Enter your Umami website ID (found in your Umami dashboard)
3. **Script URL**: Enter your tracking script URL (e.g., `https://yourdomain.com/script.js`)
4. **Host URL** (Optional): Your Umami host URL for dashboard access

### Privacy Settings

- **Ignore Admin Users**: Exclude site administrators from tracking
- **Ignore Logged-in Users**: Exclude all authenticated users
- **Respect Do Not Track**: Honor browser privacy settings

### Event Tracking Configuration

Enable any combination of automatic event tracking:

- ✅ Comment submissions
- ✅ User logins
- ✅ New registrations
- ✅ File downloads
- ✅ Outbound link clicks
- ✅ 404 error pages

## Features in Detail

### Custom Event Tracking

The plugin automatically tracks various user interactions without additional code:

```javascript
// Events are automatically tracked when enabled:
- file-download: Tracks PDF, ZIP, DOC, etc.
- outbound-link: Tracks external link clicks
- 404-error: Tracks page not found errors
- comment-submit: Tracks comment submissions
- user-login: Tracks authentication events
- user-registration: Tracks new user signups
```

### Dashboard Widget

Quick access widget shows:
- Direct link to your Umami dashboard
- Current tracking status
- Branding information

### Developer-Friendly

Built with WordPress best practices:
- Clean, documented code
- Action and filter hooks
- Transient-based event storage
- Secure data sanitization
- Translation-ready

## Requirements

- **WordPress**: 6.0 or higher
- **PHP**: 7.4 or higher
- **Umami**: Self-hosted Umami instance or Umami Cloud account

## Frequently Asked Questions

### Where do I get my Website ID and Script URL?

1. Log into your Umami dashboard
2. Go to Settings → Websites
3. Select your website
4. Copy the Website ID
5. Find the tracking code - the script URL is shown there

### Does this work with Umami Cloud?

Yes! This plugin works with both self-hosted Umami instances and Umami Cloud.

### Will this slow down my website?

No. The tracking script is loaded asynchronously with the `defer` attribute, ensuring it doesn't block page rendering.

### Is this GDPR compliant?

Yes. Umami is privacy-focused and doesn't use cookies. This plugin includes additional privacy controls like DNT respect and user exclusion options.

### Can I track custom events?

Yes! While the plugin includes automatic tracking for common events, you can also use the Umami JavaScript API to track custom events:

```javascript
umami.track('custom-event', { property: 'value' });
```

### Does this track admin users?

By default, yes. But you can enable the "Ignore Admin Users" option in settings to exclude them.

## Changelog

### 1.0.0
- Initial release
- Basic Umami integration
- Advanced event tracking system
- Comment tracking with metadata
- Login and registration tracking
- File download tracking
- Outbound link tracking
- 404 error tracking
- Privacy controls (DNT, user exclusions)
- Dashboard widget
- Admin settings interface
- Translation ready

## Support

For support, please visit [Hamotechsolutions.com](https://hamotechsolutions.com)

## Credits

**Developed by**: Hamotech Solutions  
**Website**: [https://hamotechsolutions.com](https://hamotechsolutions.com)  
**License**: GPL v2 or later

Built with ❤️ for the WordPress community

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2025 Hamotech Solutions

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

## Contributing

We welcome contributions! This plugin is designed to serve the WordPress community with professional-grade analytics integration.

---

**Made with precision by Hamotech Solutions**
