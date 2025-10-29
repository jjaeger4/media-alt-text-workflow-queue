# WordPress.org Plugin Submission Guide

## Plugin Ready for Submission! ✅

Your plugin is now ready to be submitted to WordPress.org with all the necessary requirements in place.

## What's Been Prepared

### ✅ Core Files
- **readme.txt** - Official WordPress.org readme with all required sections
- **LICENSE.txt** - GNU GPL v2 license
- **uninstall.php** - Proper cleanup on plugin deletion
- **Plugin headers** - Updated with all WordPress.org fields including Update URI

### ✅ Code Quality
- **Debug logging removed** - All error_log statements cleaned up for production
- **Proper sanitization** - Input sanitization and output escaping throughout
- **Security** - Nonce verification, capability checks, and ABSPATH guards
- **Standards compliant** - Follows WordPress Coding Standards

### ✅ Auto-Update Support
The plugin includes the `Update URI` header which enables WordPress's built-in auto-update system:
```
Update URI: https://wordpress.org/plugins/media-alt-text-workflow-queue/
```

Once approved on WordPress.org, your plugin will automatically support:
- One-click updates from the WordPress admin
- Automatic update notifications
- Version checking against WordPress.org repository

---

## Submission Process

### Step 1: Create WordPress.org Account
1. Go to https://wordpress.org/support/register.php
2. Create an account (use the email you want associated with the plugin)
3. Verify your email address

### Step 2: Prepare Plugin Package
1. Create a **clean directory** with only production files:
   ```
   media-alt-text-workflow-queue/
   ├── assets/
   │   └── css/
   │       └── admin.css
   ├── src/
   │   ├── Admin/
   │   │   ├── Menu.php
   │   │   ├── Settings.php
   │   │   └── Screens/
   │   │       ├── ListScreen.php
   │   │       └── QueueScreen.php
   │   └── Services/
   │       ├── BlockUpdater.php
   │       ├── Capability.php
   │       ├── Finder.php
   │       ├── Session.php
   │       └── UsageLocator.php
   ├── LICENSE.txt
   ├── media-alt-text-workflow-queue.php
   ├── readme.txt
   └── uninstall.php
   ```

2. **DO NOT INCLUDE**:
   - `.git` folder
   - `.gitignore`
   - `node_modules`
   - Development files (`.md` files except readme.txt)
   - IDE files (`.vscode`, `.idea`)
   - `debug.log`
   - Any test/development databases

### Step 3: Create ZIP File
1. Select all plugin files
2. Create a ZIP archive named: `media-alt-text-workflow-queue.zip`
3. Ensure the ZIP contains the plugin folder, not just the files directly

### Step 4: Submit to WordPress.org
1. Go to https://wordpress.org/plugins/developers/add/
2. Upload your ZIP file
3. Fill out the submission form:
   - **Plugin Name**: Media Alt Text Workflow Queue
   - **Plugin URL**: (will be assigned after approval)
   - **Description**: Use the description from readme.txt

4. Agree to the guidelines
5. Click "Upload"

### Step 5: Wait for Review
- Review typically takes **2-14 days** (sometimes longer)
- You'll receive an email when:
  - Plugin is approved
  - Plugin needs changes
  - Plugin is rejected (rare if guidelines followed)

---

## Assets for WordPress.org (Create These Next)

After your plugin is approved, you'll need to create visual assets. These go in a **separate SVN repository** at:
```
https://plugins.svn.wordpress.org/media-alt-text-workflow-queue/assets/
```

### Required Assets:

1. **Plugin Icon** (Required)
   - `icon-128x128.png` - 128x128px
   - `icon-256x256.png` - 256x256px (Retina)
   - Shows in plugin search results and admin

2. **Plugin Banner** (Highly Recommended)
   - `banner-772x250.png` - 772x250px
   - `banner-1544x500.png` - 1544x500px (Retina)
   - Shows at top of plugin page

3. **Screenshots** (Recommended)
   - `screenshot-1.png` - Queue interface
   - `screenshot-2.png` - List view
   - `screenshot-3.png` - Usage tracking
   - `screenshot-4.png` - Settings page
   - `screenshot-5.png` - Learn tab
   - `screenshot-6.png` - Admin menu badge
   
   Note: Screenshots can be any size but recommended 1280x720px or larger

### Design Tips:
- **Icon**: Simple, recognizable at small sizes. Consider an accessibility symbol or image icon
- **Banner**: Professional, clean design with plugin name
- **Colors**: Use accessibility-friendly colors (consider your audience!)
- **Screenshots**: Actual plugin interface, annotated if helpful

---

## After Approval

### 1. SVN Access
You'll receive SVN credentials to manage your plugin:
```
https://plugins.svn.wordpress.org/media-alt-text-workflow-queue/
```

### 2. Initial SVN Setup
```bash
# Checkout your plugin
svn co https://plugins.svn.wordpress.org/media-alt-text-workflow-queue/

# Structure:
# /trunk/        - Development version
# /tags/         - Released versions (1.0.0, 1.0.1, etc.)
# /assets/       - Icons, banners, screenshots
```

### 3. Upload Initial Version
```bash
# Copy plugin files to trunk
cp -r /path/to/plugin/* trunk/

# Add files
svn add trunk/*

# Commit
svn ci -m "Initial commit of version 1.0.0"

# Tag version 1.0.0
svn cp trunk tags/1.0.0
svn ci -m "Tagging version 1.0.0"
```

### 4. Upload Assets
```bash
# Copy assets
cp icon-*.png assets/
cp banner-*.png assets/
cp screenshot-*.png assets/

# Commit assets
svn add assets/*
svn ci -m "Adding plugin assets"
```

---

## Releasing Updates

### Version Numbering
Follow Semantic Versioning (https://semver.org/):
- **1.0.0** → **1.0.1** - Bug fixes
- **1.0.0** → **1.1.0** - New features (backward compatible)
- **1.0.0** → **2.0.0** - Breaking changes

### Update Process

1. **Update version numbers** in:
   - `media-alt-text-workflow-queue.php` (Plugin header)
   - `MATWQ_VERSION` constant
   - `readme.txt` (Stable tag)

2. **Update readme.txt changelog**:
   ```
   == Changelog ==
   
   = 1.0.1 - 2025-11-15 =
   * Fixed: Issue with cache clearing
   * Improved: Performance optimization
   ```

3. **Commit to SVN trunk**:
   ```bash
   svn ci -m "Version 1.0.1 updates"
   ```

4. **Create new tag**:
   ```bash
   svn cp trunk tags/1.0.1
   svn ci -m "Tagging version 1.0.1"
   ```

5. **WordPress.org auto-updates**:
   - Users with auto-updates enabled will get your update automatically
   - Others will see update notification in their admin
   - Update typically appears within 15 minutes

---

## WordPress.org Guidelines Compliance

### ✅ Your Plugin Meets These Requirements:

1. **GPL Compatible License** ✅ (GPL v2)
2. **No "sponsored" or affiliate links** ✅
3. **No obfuscated code** ✅
4. **No tracking/phone home** ✅
5. **Proper security** ✅ (nonces, capabilities, sanitization)
6. **Proper escaping** ✅ (esc_html, esc_attr, etc.)
7. **Internationalization ready** ✅ (text domain, translations)
8. **No PHP errors** ✅
9. **unique prefix/namespace** ✅ (MATWQ / JJ\AltTextWorkflowQueue)
10. **Proper uninstall** ✅ (uninstall.php)

### Additional Guidelines:
- **No "plugin-check" or "theme-check" required**, but recommended
- **Test on latest WordPress version** (currently 6.8)
- **Test on PHP 7.4+**
- **Responsive design** ✅ (Admin CSS is responsive)

---

## Support & Maintenance

### WordPress.org Support Forum
After approval, you'll have a support forum:
```
https://wordpress.org/support/plugin/media-alt-text-workflow-queue/
```

**Best Practices**:
- Respond to support requests within 2-7 days
- Mark resolved threads as "Resolved"
- Be professional and helpful
- Link to documentation when applicable

### Plugin Statistics
You'll get access to:
- Download counts
- Active installations
- Version distribution
- User ratings and reviews

---

## Marketing Your Plugin

### In WordPress.org
1. **Clear readme.txt** ✅ Already done
2. **Quality screenshots** (To be added)
3. **Respond to reviews** (Thank positive, address negative professionally)
4. **Keep plugin updated** (Shows you're active)

### External Promotion
- Blog post announcing the plugin
- Social media promotion
- Your website (link already in plugin)
- Developer communities

---

## Pro Version Considerations

Your plugin mentions "Pro" features. Here's how to handle this correctly:

### ✅ Allowed:
- Mentioning Pro features exist
- Link to external site for Pro version
- Upsell in admin (like you have)
- Separate Pro plugin

### ❌ Not Allowed:
- Requiring payment for features in free plugin
- "Freemium" model within same plugin
- Disabling free features to upsell

### Your Current Implementation: **PERFECT** ✅
- Free plugin is fully functional
- Pro features clearly marked as coming soon
- No aggressive upselling
- Free version provides real value

---

## Quick Reference

### Important Links
- **Developer Handbook**: https://developer.wordpress.org/plugins/
- **Plugin Guidelines**: https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/
- **SVN Tutorial**: https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/
- **Plugin Check Tool**: https://wordpress.org/plugins/plugin-check/

### Support Resources
- **WordPress.org Forums**: https://wordpress.org/support/
- **Make WordPress Slack**: https://make.wordpress.org/chat/
- **WP Plugin Developer FB Group**: Search Facebook

---

## Final Checklist Before Submission

- [ ] Test plugin on fresh WordPress install
- [ ] Test plugin with default theme (Twenty Twenty-Four)
- [ ] Test with PHP 7.4, 8.0, 8.1, 8.2
- [ ] Test with WordPress 5.8 (minimum) and 6.8 (current)
- [ ] Verify no PHP errors or warnings
- [ ] Test uninstall.php (delete plugin and check database)
- [ ] Verify all links in readme.txt work
- [ ] Spell check readme.txt
- [ ] Review plugin description for clarity
- [ ] Ensure LICENSE.txt is included
- [ ] Remove all development/debug code ✅
- [ ] Create plugin ZIP file
- [ ] Test the ZIP by installing it fresh

---

## You're Ready! 🎉

Your plugin is well-built, follows best practices, and meets all WordPress.org requirements. The auto-update functionality will work automatically once your plugin is live on WordPress.org.

**Good luck with your submission!**

Questions? Feel free to ask in the WordPress.org plugin developer forums or contact the WordPress.org plugin team.

---

## Contact & Credits

**Plugin Author**: Jesse Jaeger
- Website: https://jessejaeger.com
- Support Development: https://buymeacoffee.com/jessejaeger

**Version**: 1.0.0
**Last Updated**: October 29, 2025

