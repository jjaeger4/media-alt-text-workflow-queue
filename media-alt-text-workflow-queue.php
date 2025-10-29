<?php
/**
 * Plugin Name: Media Alt Text Workflow Queue
 * Plugin URI: https://jessejaeger.com/media-alt-text-workflow-queue
 * Description: Streamline your website accessibility by processing images missing alt text in an organized workflow queue. Improve SEO and meet WCAG standards.
 * Version: 1.0.0
 * Author: Jesse Jaeger
 * Author URI: https://jessejaeger.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: media-alt-text-workflow-queue
 * Domain Path: /languages
 * Requires at least: 5.8
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * Network: false
 * Update URI: https://wordpress.org/plugins/media-alt-text-workflow-queue/
 *
 * @package MediaAltTextWorkflowQueue
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MATWQ_VERSION', '1.0.0');
define('MATWQ_PLUGIN_FILE', __FILE__);
define('MATWQ_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MATWQ_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MATWQ_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'JJ\\AltTextWorkflowQueue\\';
    $base_dir = MATWQ_PLUGIN_DIR . 'src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
add_action('plugins_loaded', function () {
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('Media Alt Text Workflow Queue requires PHP 7.4 or higher.', 'media-alt-text-workflow-queue');
            echo '</p></div>';
        });
        return;
    }
    
    if (version_compare(get_bloginfo('version'), '6.0', '<')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('Media Alt Text Workflow Queue requires WordPress 6.0 or higher.', 'media-alt-text-workflow-queue');
            echo '</p></div>';
        });
        return;
    }
    
    // Initialize the plugin using singleton
    \JJ\AltTextWorkflowQueue\Plugin::getInstance()->boot();
});

// Activation hook
register_activation_hook(__FILE__, function () {
    // Set default options
    $default_settings = [
        'items_per_session' => 50,
        'required_capability' => 'edit_others_posts',
        'cache_duration' => 3600, // 1 hour
        'usage_cache_duration' => 43200, // 12 hours
    ];
    
    add_option('matwq_settings', $default_settings);
});

// Deactivation hook
register_deactivation_hook(__FILE__, function () {
    // Clean up transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_matwq_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_matwq_%'");
});
