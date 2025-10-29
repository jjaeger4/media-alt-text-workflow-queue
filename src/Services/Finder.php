<?php

namespace JJ\AltTextWorkflowQueue\Services;

/**
 * Service for finding images with missing alt text
 */
class Finder
{
    /**
     * Get count of images with missing alt text
     *
     * @return int
     */
    public function getMissingAltTextCount()
    {
        $query_args = [
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_wp_attachment_image_alt',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => '_wp_attachment_image_alt',
                    'value' => '',
                    'compare' => '=',
                ],
            ],
        ];

        $query = new \WP_Query($query_args);
        return $query->found_posts;
    }

    /**
     * Get images with missing alt text
     *
     * @param array $args Query arguments
     * @return array Array of attachment IDs
     */
    public function getMissingAltTextImages($args = [])
    {
        $defaults = [
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => '_wp_attachment_image_alt',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => '_wp_attachment_image_alt',
                    'value' => '',
                    'compare' => '=',
                ],
            ],
        ];

        $query_args = wp_parse_args($args, $defaults);

        $query = new \WP_Query($query_args);
        return $query->posts;
    }

    /**
     * Get attachment details for display
     *
     * @param int $attachment_id Attachment ID
     * @return array|null
     */
    public function getAttachmentDetails($attachment_id)
    {
        $attachment = get_post($attachment_id);
        
        if (!$attachment || $attachment->post_type !== 'attachment') {
            return null;
        }

        $file_url = wp_get_attachment_url($attachment_id);
        $thumb_url = wp_get_attachment_image_url($attachment_id, 'medium');
        $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        
        return [
            'id' => $attachment_id,
            'title' => $attachment->post_title,
            'filename' => basename($file_url),
            'file_url' => $file_url,
            'thumb_url' => $thumb_url ?: $file_url,
            'upload_date' => $attachment->post_date,
            'mime_type' => $attachment->post_mime_type,
            'alt_text' => $alt_text,
        ];
    }

    /**
     * Update alt text for an image
     *
     * @param int $attachment_id Attachment ID
     * @param string $alt_text Alt text
     * @return bool|int
     */
    public function updateAltText($attachment_id, $alt_text)
    {
        $alt_text = sanitize_text_field($alt_text);
        return update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
    }
}
