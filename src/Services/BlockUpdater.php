<?php

namespace JJ\AltTextWorkflowQueue\Services;

/**
 * Service for updating alt text in blocks/posts
 */
class BlockUpdater
{
    /**
     * Update alt text in all existing uses of an image
     *
     * @param int $attachment_id Attachment ID
     * @param string $new_alt_text New alt text
     * @return array Results array with updated count and post IDs
     */
    public function updateAltTextInExistingUses($attachment_id, $new_alt_text)
    {
        global $wpdb;
        
        $results = [
            'updated_count' => 0,
            'post_ids' => [],
            'errors' => [],
        ];
        
        // Get all public post types
        $post_types = get_post_types(['public' => true], 'names');
        unset($post_types['attachment']);
        
        if (empty($post_types)) {
            $post_types = ['post', 'page'];
        }
        
        $post_types_sql = "'" . implode("','", array_map('esc_sql', $post_types)) . "'";
        
        // Get attachment URL and filename for searching
        $attachment_url = wp_get_attachment_url($attachment_id);
        $filename = basename($attachment_url);
        
        // Search patterns
        $search_patterns = [
            '%' . $wpdb->esc_like($filename) . '%',
            '%wp-image-' . $attachment_id . '%',
            '%"id":' . $attachment_id . '%',
        ];
        
        $post_ids = [];
        
        // Find posts containing this image
        foreach ($search_patterns as $pattern) {
            $sql = $wpdb->prepare(
                "SELECT DISTINCT ID FROM {$wpdb->posts} 
                WHERE post_content LIKE %s 
                AND post_status = 'publish' 
                AND post_type IN ($post_types_sql)",
                $pattern
            );
            
            $ids = $wpdb->get_col($sql);
            if ($ids) {
                $post_ids = array_merge($post_ids, $ids);
            }
        }
        
        $post_ids = array_unique($post_ids);
        
        // Update each post
        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            if (!$post) {
                continue;
            }
            
            $content = $post->post_content;
            $updated_content = $this->updateBlocksInContent($content, $attachment_id, $new_alt_text);
            
            if ($updated_content !== $content) {
                // Use wp_update_post to properly update and clear caches
                // Remove all hooks to prevent infinite loops or unwanted side effects
                remove_all_filters('content_save_pre');
                remove_all_filters('wp_insert_post_data');
                
                $result = wp_update_post([
                    'ID' => $post_id,
                    'post_content' => $updated_content,
                ], true);
                
                if (!is_wp_error($result)) {
                    $results['updated_count']++;
                    $results['post_ids'][] = $post_id;
                    
                    // Explicitly clear post cache
                    clean_post_cache($post_id);
                } else {
                    // Log errors only
                    error_log("MATWQ BlockUpdater: Failed to update post $post_id: " . $result->get_error_message());
                    $results['errors'][] = $post_id;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Update blocks in content
     *
     * @param string $content Post content
     * @param int $attachment_id Attachment ID
     * @param string $new_alt_text New alt text
     * @return string Updated content
     */
    private function updateBlocksInContent($content, $attachment_id, $new_alt_text)
    {
        // Parse blocks
        if (function_exists('parse_blocks')) {
            $blocks = parse_blocks($content);
            $updated = false;
            
            $blocks = $this->updateBlocksRecursive($blocks, $attachment_id, $new_alt_text, $updated);
            
            if ($updated && function_exists('serialize_blocks')) {
                return serialize_blocks($blocks);
            }
        }
        
        // Fallback for classic editor content with <img> tags
        $content = $this->updateClassicEditorImages($content, $attachment_id, $new_alt_text);
        
        return $content;
    }
    
    /**
     * Recursively update blocks (handles nested blocks)
     *
     * @param array $blocks Blocks array
     * @param int $attachment_id Attachment ID
     * @param string $new_alt_text New alt text
     * @param bool &$updated Reference to updated flag
     * @return array Updated blocks
     */
    private function updateBlocksRecursive($blocks, $attachment_id, $new_alt_text, &$updated)
    {
        foreach ($blocks as &$block) {
            // Check for image block
            if ($block['blockName'] === 'core/image') {
                if (isset($block['attrs']['id']) && $block['attrs']['id'] == $attachment_id) {
                    // Update the attribute
                    $block['attrs']['alt'] = $new_alt_text;
                    
                    // Also update the innerHTML which contains the actual <img> tag
                    if (!empty($block['innerHTML'])) {
                        $old_html = $block['innerHTML'];
                        $block['innerHTML'] = $this->updateImgTagAlt($block['innerHTML'], $new_alt_text);
                        error_log("MATWQ BlockUpdater: Updated innerHTML from:\n$old_html\nto:\n{$block['innerHTML']}");
                    }
                    
                    // Also update innerContent array if it exists
                    if (!empty($block['innerContent'])) {
                        foreach ($block['innerContent'] as $key => $content) {
                            if (!empty($content) && is_string($content)) {
                                $block['innerContent'][$key] = $this->updateImgTagAlt($content, $new_alt_text);
                            }
                        }
                    }
                    
                    $updated = true;
                }
            }
            
            // Check for media-text block
            if ($block['blockName'] === 'core/media-text') {
                if (isset($block['attrs']['mediaId']) && $block['attrs']['mediaId'] == $attachment_id) {
                    $block['attrs']['mediaAlt'] = $new_alt_text;
                    $updated = true;
                }
            }
            
            // Check for gallery block
            if ($block['blockName'] === 'core/gallery') {
                if (isset($block['attrs']['images']) && is_array($block['attrs']['images'])) {
                    foreach ($block['attrs']['images'] as &$image) {
                        if (isset($image['id']) && $image['id'] == $attachment_id) {
                            $image['alt'] = $new_alt_text;
                            $updated = true;
                        }
                    }
                }
            }
            
            // Recursively check inner blocks
            if (!empty($block['innerBlocks'])) {
                $block['innerBlocks'] = $this->updateBlocksRecursive($block['innerBlocks'], $attachment_id, $new_alt_text, $updated);
            }
        }
        
        return $blocks;
    }
    
    /**
     * Update alt attribute in an <img> tag
     *
     * @param string $html HTML content containing <img> tag
     * @param string $new_alt_text New alt text
     * @return string Updated HTML
     */
    private function updateImgTagAlt($html, $new_alt_text)
    {
        // Replace or add alt attribute in any <img> tag
        if (preg_match('/<img[^>]*>/i', $html)) {
            $html = preg_replace_callback('/<img([^>]*)>/i', function($matches) use ($new_alt_text) {
                $img_tag = $matches[0];
                
                // Replace or add alt attribute
                if (preg_match('/alt=["\']([^"\']*)["\']/i', $img_tag)) {
                    // Replace existing alt
                    $img_tag = preg_replace('/alt=["\']([^"\']*)["\']/i', 'alt="' . esc_attr($new_alt_text) . '"', $img_tag);
                } else {
                    // Add alt attribute after <img
                    $img_tag = str_replace('<img', '<img alt="' . esc_attr($new_alt_text) . '"', $img_tag);
                }
                
                return $img_tag;
            }, $html);
        }
        
        return $html;
    }
    
    /**
     * Update classic editor <img> tags
     *
     * @param string $content Content
     * @param int $attachment_id Attachment ID
     * @param string $new_alt_text New alt text
     * @return string Updated content
     */
    private function updateClassicEditorImages($content, $attachment_id, $new_alt_text)
    {
        // Match <img> tags with wp-image-{id} class
        $pattern = '/<img([^>]*class=["\'][^"\']*wp-image-' . $attachment_id . '[^"\']*)([^>]*)>/i';
        
        $content = preg_replace_callback($pattern, function($matches) use ($new_alt_text) {
            return $this->updateImgTagAlt($matches[0], $new_alt_text);
        }, $content);
        
        return $content;
    }
}

