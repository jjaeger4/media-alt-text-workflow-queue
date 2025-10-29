=== Media Alt Text Workflow Queue ===
Contributors: jessejaeger
Tags: accessibility, alt text, images, media library, workflow
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Streamline your website accessibility by processing images missing alt text in an organized workflow queue.

== Description ==

Media Alt Text Workflow Queue helps you identify and add alt text to images in your WordPress Media Library through an intuitive, workflow-based interface. This plugin is essential for improving your website's accessibility and SEO.

= Key Features =

* **Queue-Based Workflow** - Process images one by one in an organized queue
* **Smart Detection** - Automatically identifies all images missing alt text
* **Usage Tracking** - Shows where each image is currently used on your site
* **Batch Processing** - Skip images and restart sessions as needed
* **Update Existing Uses** - Automatically update alt text in existing posts and pages
* **List View** - Browse and filter all media with sorting and search
* **Educational Resources** - Built-in guide for writing effective alt text
* **Session Management** - Resume where you left off with automatic progress tracking

= Why Alt Text Matters =

Alt text (alternative text) is crucial for:
* **Accessibility** - Screen readers use alt text to describe images to visually impaired users
* **SEO** - Search engines use alt text to understand image content
* **User Experience** - Alt text displays when images fail to load
* **Legal Compliance** - Many jurisdictions require accessible websites (ADA, WCAG)

= How It Works =

1. The plugin scans your Media Library for images without alt text
2. View the queue count in your admin menu badge
3. Process images one-by-one, seeing where each is used
4. Write appropriate alt text with character count guidance
5. Optionally update all existing uses of the image automatically
6. Skip images you want to handle later
7. Track your progress throughout the session

= Perfect For =

* Content managers maintaining website accessibility
* SEO specialists optimizing image metadata
* Web agencies managing client sites
* Anyone committed to creating accessible web content

= Pro Features (Coming Soon) =

* Advanced reporting and analytics
* Bulk import/export of alt text
* AI-powered alt text suggestions
* Team collaboration features
* Priority support

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins → Add New
3. Search for "Media Alt Text Workflow Queue"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin zip file
2. Log in to your WordPress admin panel
3. Navigate to Plugins → Add New → Upload Plugin
4. Choose the downloaded zip file and click "Install Now"
5. After installation, click "Activate Plugin"

= After Activation =

1. Navigate to Media → Alt Text Queue in your WordPress admin
2. The badge will show how many images need alt text
3. Start processing images in the Queue tab
4. View all media in the List tab
5. Adjust settings in the Settings tab (admin only)
6. Learn best practices in the Learn tab

== Frequently Asked Questions ==

= What happens if I update alt text in the Media Library directly? =

The plugin will automatically detect the change and remove that image from the missing alt text queue.

= Does this work with Gutenberg blocks? =

Yes! The plugin works with both the Classic Editor and Gutenberg Block Editor. When you enable "Update existing uses," it updates alt text in Image blocks, Media & Text blocks, Gallery blocks, and classic `<img>` tags.

= Can I skip images I don't want to process right now? =

Absolutely! Click the "Skip" button to temporarily skip an image. You can restart your session at any time to clear the skip list and start fresh.

= Will this slow down my website? =

No. The plugin uses smart caching to minimize database queries. Cache durations are configurable in the Settings tab.

= What post types does the "Usage Tracking" feature support? =

The plugin searches all public post types on your site, including Posts, Pages, and any custom post types registered by themes or plugins.

= Does updating alt text in the queue update it everywhere on my site? =

By default, it updates the alt text in your Media Library. If you check the "Update existing uses" option, it will also update the alt text in all posts and pages where that image is currently used.

= Can I undo changes? =

WordPress doesn't have a built-in revision system for attachment metadata. We recommend keeping a backup of your database before bulk updates, or using a plugin like WP Rollback for additional safety.

= What if an image shouldn't have alt text? =

Some decorative images don't need alt text. You can either skip them or enter an empty alt text (which is semantically correct for decorative images).

= Does this plugin collect any data? =

No. This plugin does not collect, transmit, or store any data outside your WordPress installation. All processing happens locally on your server.

== Screenshots ==

1. Queue interface showing current image with usage information and alt text input
2. List view with filtering, sorting, and pagination options
3. Usage tracking showing where images are used across your site
4. Settings page with cache management and configuration options
5. Learn tab with alt text best practices and WordPress-specific guidance
6. Admin menu badge showing count of images missing alt text

== Changelog ==

= 1.0.0 - 2025-10-29 =
* Initial release
* Queue-based workflow for processing images
* Smart image usage detection across all post types
* Update existing uses feature for Gutenberg blocks
* List view with filtering, sorting, and search
* Session management with skip functionality
* Configurable caching system
* Educational resources for writing effective alt text
* Admin menu badge with missing count
* Support for Posts, Pages, and custom post types

== Upgrade Notice ==

= 1.0.0 =
Initial release of Media Alt Text Workflow Queue. Start improving your website's accessibility today!

== Privacy Policy ==

This plugin does not collect, store, or transmit any personal data or user information. All data processing occurs locally within your WordPress installation.

== Support ==

For support, feature requests, or bug reports, please visit:
* Plugin Support Forum: https://wordpress.org/support/plugin/media-alt-text-workflow-queue/
* GitHub Repository: https://github.com/jessejaeger/media-alt-text-workflow-queue/

If you find this plugin helpful, please consider:
* Leaving a 5-star review
* Supporting development: https://buymeacoffee.com/jessejaeger

== Credits ==

Developed by Jesse Jaeger
* Website: https://jessejaeger.com
* Support: https://buymeacoffee.com/jessejaeger

== License ==

This plugin is licensed under the GPL v2 or later.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

