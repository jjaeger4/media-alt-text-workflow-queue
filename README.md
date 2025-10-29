# Media Alt Text Workflow Queue

A WordPress plugin that streamlines the process of adding alt text to images through an organized workflow queue.

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://www.php.net/)

## 🎯 Features

- **Queue-Based Workflow** - Process images one by one in an organized manner
- **Smart Detection** - Automatically identifies all images missing alt text
- **Usage Tracking** - Shows where each image is currently used on your site
- **Update Existing Uses** - Automatically updates alt text in existing posts and pages (Gutenberg blocks)
- **List View** - Browse, filter, sort, and search all media
- **Educational Resources** - Built-in guide for writing effective alt text
- **Session Management** - Resume where you left off with automatic progress tracking
- **Configurable Caching** - Performance optimization with customizable cache duration

## 📦 Installation

### From WordPress.org (Recommended)
1. Go to **Plugins → Add New** in your WordPress admin
2. Search for "Media Alt Text Workflow Queue"
3. Click **"Install Now"** and then **"Activate"**

### Manual Installation
1. Download the latest release from [WordPress.org](https://wordpress.org/plugins/media-alt-text-workflow-queue/) or [GitHub Releases](https://github.com/jessejaeger/media-alt-text-workflow-queue/releases)
2. Upload to `/wp-content/plugins/media-alt-text-workflow-queue/`
3. Activate through the WordPress **Plugins** menu

### From Source
```bash
git clone https://github.com/jessejaeger/media-alt-text-workflow-queue.git
cd media-alt-text-workflow-queue
# No build step required - pure PHP plugin!
```

## 🚀 Usage

1. Navigate to **Media → Alt Text Queue** in your WordPress admin
2. View the badge showing how many images need alt text
3. **Queue Tab** - Process images one by one
   - View image preview and metadata
   - See where the image is used
   - Write appropriate alt text
   - Choose to update existing uses automatically
4. **List Tab** - Browse all media with filters
   - Filter by missing/has alt text
   - Sort by date, title, or file size
   - Search by filename
   - View usage information
5. **Learn Tab** - Best practices and standards
6. **Settings Tab** - Configure cache duration and purge cache (admin only)

## 🎨 Screenshots

> Screenshots will be added after WordPress.org approval

## 🔧 Requirements

- **WordPress**: 5.8 or higher
- **PHP**: 7.4 or higher
- **Tested up to**: WordPress 6.8

## 🛠️ Development & Building

### Building for WordPress.org

Create a production-ready ZIP file:

```bash
npm run build
```

**Output**: `media-alt-text-workflow-queue-1.0.0.zip` (~33 KB)

The build script automatically:
- ✅ Copies only production files (16 files total)
- ✅ Excludes development docs and build tools
- ✅ Creates proper ZIP structure (files at root)
- ✅ Ready for WordPress.org submission!

**What's included in the ZIP:**
- `src/` - All PHP classes (10 files)
- `assets/` - CSS and JS files (2 files)
- `LICENSE.txt` - GPL v2 license
- `readme.txt` - WordPress.org readme
- `uninstall.php` - Database cleanup script
- `media-alt-text-workflow-queue.php` - Main plugin file

**What's excluded:**
- Development documentation (`README.md`, `*.md` files)
- Git files (`.git/`, `.gitignore`)
- Build tools (`build.js`, `package.json`)
- IDE configurations (`.vscode/`, `.idea/`)

### Updating Version Numbers

When releasing a new version:

1. Update `build.js`:
   ```javascript
   const VERSION = '1.0.1';
   ```

2. Update `media-alt-text-workflow-queue.php`:
   ```php
   * Version: 1.0.1
   ```

3. Update `readme.txt`:
   ```
   Stable tag: 1.0.1
   ```

4. Add changelog entry to `readme.txt`

5. Build: `npm run build`

### Testing Before Submission

**Installation Test:**
1. Build fresh ZIP: `npm run build`
2. Upload to test WordPress site (Plugins → Add New → Upload)
3. Activate and test all features
4. Check for PHP errors in `debug.log`

**Core Features to Test:**
- ✅ Queue workflow (save alt text, skip, restart)
- ✅ List view (filters, sorting, search, pagination)
- ✅ Update existing uses feature
- ✅ Settings (save, purge cache)
- ✅ Learn tab content
- ✅ Usage tracking accuracy
- ✅ Admin menu badge count

**Compatibility Testing:**
- ✅ PHP 7.4, 8.0, 8.1, 8.2
- ✅ WordPress 5.8+ through 6.8
- ✅ Default WordPress themes
- ✅ Common plugin conflicts

**Uninstall Test:**
1. Deactivate plugin
2. Delete plugin
3. Verify database cleanup:
   ```sql
   SELECT * FROM wp_options WHERE option_name LIKE 'matwq%';
   SELECT * FROM wp_usermeta WHERE meta_key LIKE 'matwq%';
   ```
   Should return 0 rows.

### Common Build Issues

**"node: command not found"**
- Install Node.js from https://nodejs.org/

**"Could not copy file" during WordPress installation**
- Rebuild ZIP: `npm run build`
- Ensure using the fresh ZIP file
- Verify ZIP structure (files at root, not in subdirectory)

**ZIP structure verification:**
```bash
# Extract and verify
unzip -l media-alt-text-workflow-queue-1.0.0.zip
# Should show files at root, not in subdirectory
```

### Project Structure

```
media-alt-text-workflow-queue/
├── src/
│   ├── Admin/
│   │   ├── Menu.php              # Admin menu & tab navigation
│   │   ├── Settings.php          # Settings page
│   │   └── Screens/
│   │       ├── QueueScreen.php   # Queue workflow interface
│   │       └── ListScreen.php    # List view with filters
│   ├── Services/
│   │   ├── BlockUpdater.php      # Updates alt text in Gutenberg blocks
│   │   ├── Capability.php        # User capability management
│   │   ├── Finder.php            # Finds images missing alt text
│   │   ├── Session.php           # Session & progress tracking
│   │   └── UsageLocator.php      # Finds where images are used
│   └── Plugin.php                # Main plugin class (service container)
├── assets/
│   └── css/
│       └── admin.css             # Admin interface styles
├── build.js                      # Build script for WordPress.org
├── package.json                  # Node.js dependencies
├── LICENSE.txt                   # GPL v2 license
├── readme.txt                    # WordPress.org readme
├── README.md                     # This file (GitHub readme)
├── uninstall.php                 # Database cleanup on deletion
└── media-alt-text-workflow-queue.php  # Main plugin file
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 👤 Author

**Jesse Jaeger**
- Website: [jessejaeger.com](https://jessejaeger.com)
- GitHub: [@jessejaeger](https://github.com/jessejaeger)
- Support Development: [Buy Me a Coffee](https://buymeacoffee.com/jessejaeger)

## 📋 Changelog

### 1.0.0 - 2025-10-29
- 🎉 Initial release
- ✅ Queue-based workflow for processing images
- ✅ Smart image usage detection across all post types
- ✅ Update existing uses feature for Gutenberg blocks
- ✅ List view with filtering, sorting, and search
- ✅ Session management with skip functionality
- ✅ Configurable caching system
- ✅ Educational resources for writing effective alt text
- ✅ Admin menu badge with missing count

For detailed changelog, see [readme.txt](readme.txt).

## 🐛 Bug Reports

Found a bug? Please [open an issue](https://github.com/jessejaeger/media-alt-text-workflow-queue/issues) with:
- WordPress version
- PHP version
- Steps to reproduce
- Expected vs actual behavior
- Screenshots (if applicable)

## 💡 Feature Requests

Have an idea for improvement? [Open an issue](https://github.com/jessejaeger/media-alt-text-workflow-queue/issues) with the "enhancement" label!

## 🙏 Acknowledgments

- Built with ❤️ for the WordPress community
- Inspired by the need for better accessibility tooling
- Special thanks to all contributors and testers

## 📚 Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [WebAIM Alt Text Guide](https://webaim.org/techniques/alttext/)
- [WordPress Accessibility](https://make.wordpress.org/accessibility/)

---

**⭐ If you find this plugin helpful, please consider leaving a review on [WordPress.org](https://wordpress.org/plugins/media-alt-text-workflow-queue/)!**
