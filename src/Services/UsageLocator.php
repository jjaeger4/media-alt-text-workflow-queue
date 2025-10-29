<?php

namespace JJ\AltTextWorkflowQueue\Services;

use JJ\AltTextWorkflowQueue\Plugin;

/**
 * Service for locating where images are used throughout the site
 */
class UsageLocator
{
    /**
     * Plugin instance
     *
     * @var Plugin
     */
    private $plugin;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->plugin = Plugin::getInstance();
    }
    
    /**
     * Get usage summary for display
     *
     * @param int $attachment_id Attachment ID
     * @param int $limit Maximum number of results to show
     * @return array
     */
    public function getUsageSummary($attachment_id, $limit = 3)
    {
        $cache_key = 'matwq_usage_' . $attachment_id;
        $cached = get_transient($cache_key);
        
        if (false !== $cached) {
            return [
                'items' => array_slice($cached, 0, $limit),
                'total' => count($cached),
                'has_more' => count($cached) > $limit,
            ];
        }
        
        $usage = $this->findUsage($attachment_id);
        
        // Cache with configured duration
        $settings = $this->plugin->getSettings();
        $cache_duration = isset($settings['usage_cache_duration']) ? $settings['usage_cache_duration'] : (12 * HOUR_IN_SECONDS);
        set_transient($cache_key, $usage, $cache_duration);
        
        return [
            'items' => array_slice($usage, 0, $limit),
            'total' => count($usage),
            'has_more' => count($usage) > $limit,
        ];
    }
    
    /**
     * Find where an image is used
     *
     * @param int $attachment_id Attachment ID
     * @return array Array of posts where image is used
     */
    private function findUsage($attachment_id)
    {
        global $wpdb;
        
        error_log("MATWQ Usage: Starting search for attachment ID: $attachment_id");
        
        $usage = [];
        
        // Get the attachment URL and filename
        $attachment_url = wp_get_attachment_url($attachment_id);
        if (!$attachment_url) {
            error_log("MATWQ Usage: No URL found for attachment $attachment_id");
            return $usage;
        }
        
        $filename = basename($attachment_url);
        error_log("MATWQ Usage: Searching for filename: $filename");
        error_log("MATWQ Usage: Full URL: $attachment_url");
        
        // Get all public post types (excluding attachments)
        $post_types = get_post_types(['public' => true], 'names');
        unset($post_types['attachment']);
        
        // If no public post types found, default to post and page
        if (empty($post_types)) {
            $post_types = ['post', 'page'];
        }
        
        error_log("MATWQ Usage: Searching post types: " . implode(', ', $post_types));
        
        // Convert to array for SQL IN clause
        $post_types_sql = "'" . implode("','", array_map('esc_sql', $post_types)) . "'";
        
        // Search for posts containing this image
        // Look for:
        // 1. Direct URL in content
        // 2. wp-image-{id} class
        // 3. Gutenberg block references
        
        $search_patterns = [
            '%' . $wpdb->esc_like($filename) . '%',
            '%wp-image-' . $attachment_id . '%',
            '%"id":' . $attachment_id . '%',
        ];
        
        error_log("MATWQ Usage: Search patterns:");
        foreach ($search_patterns as $i => $pattern) {
            error_log("  Pattern " . ($i + 1) . ": $pattern");
        }
        
        $post_ids = [];
        
        foreach ($search_patterns as $index => $pattern) {
            // Note: Using direct SQL with post_types_sql that's already escaped
            $sql = $wpdb->prepare(
                "SELECT DISTINCT ID FROM {$wpdb->posts} 
                WHERE post_content LIKE %s 
                AND post_status = 'publish' 
                AND post_type IN ($post_types_sql)
                LIMIT 10",
                $pattern
            );
            
            error_log("MATWQ Usage: Executing query for pattern " . ($index + 1));
            
            $results = $wpdb->get_col($sql);
            
            if ($results) {
                error_log("MATWQ Usage: Pattern " . ($index + 1) . " found " . count($results) . " posts: " . implode(', ', $results));
                $post_ids = array_merge($post_ids, $results);
            } else {
                error_log("MATWQ Usage: Pattern " . ($index + 1) . " found no results");
            }
        }
        
        // Remove duplicates
        $post_ids = array_unique($post_ids);
        error_log("MATWQ Usage: Total unique posts found: " . count($post_ids));
        
        // Get post details
        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            if ($post) {
                // Get the post type object for better labeling
                $post_type_obj = get_post_type_object($post->post_type);
                $post_type_label = $post_type_obj ? $post_type_obj->labels->singular_name : $post->post_type;
                
                error_log("MATWQ Usage: Found in: {$post->post_title} (ID: $post_id, Type: {$post->post_type})");
                
                $usage[] = [
                    'post_id' => $post_id,
                    'post_title' => $post->post_title,
                    'post_type' => $post->post_type,
                    'post_type_label' => $post_type_label,
                    'post_url' => get_permalink($post_id),
                    'edit_url' => get_edit_post_link($post_id),
                ];
            }
        }
        
        error_log("MATWQ Usage: Final usage count: " . count($usage));
        
        return $usage;
    }
    
    /**
     * Clear usage cache for an attachment
     *
     * @param int $attachment_id Attachment ID
     * @return bool
     */
    public function clearCache($attachment_id)
    {
        $cache_key = 'matwq_usage_' . $attachment_id;
        return delete_transient($cache_key);
    }
}
