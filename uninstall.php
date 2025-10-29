<?php
/**
 * Plugin Uninstall Handler
 *
 * This file runs when the plugin is uninstalled (deleted) from WordPress.
 * It cleans up all plugin data from the database.
 *
 * @package MediaAltTextWorkflowQueue
 */

// Exit if accessed directly or not during uninstall
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all plugin options
delete_option('matwq_settings');
delete_option('matwq_license_key');

// Delete all user meta data
global $wpdb;

// Delete user session data
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'matwq_%'");

// Delete all transients
$wpdb->query(
    "DELETE FROM {$wpdb->options} 
    WHERE option_name LIKE '_transient_matwq_%' 
    OR option_name LIKE '_transient_timeout_matwq_%'"
);

// Optional: Uncomment the following line if you want to delete skipped image data
// Note: By default, we keep this data in case the user reinstalls the plugin
// $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key = 'matwq_skipped_image_ids'");

// Clear any cached data
wp_cache_flush();

