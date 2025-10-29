<?php

namespace JJ\AltTextWorkflowQueue\Services;

/**
 * Service for managing user capabilities and permissions
 */
class Capability
{
    /**
     * Default required capability
     *
     * @var string
     */
    private $default_capability = 'edit_others_posts';

    /**
     * Get required capability for plugin access
     *
     * @return string
     */
    public function getRequiredCapability()
    {
        /**
         * Filter the required capability for accessing the plugin
         *
         * @param string $capability Required capability
         */
        return apply_filters('matwq_required_capability', $this->default_capability);
    }

    /**
     * Check if current user can access the plugin
     *
     * @return bool
     */
    public function canAccess()
    {
        return current_user_can($this->getRequiredCapability());
    }

    /**
     * Check if current user can manage settings
     *
     * @return bool
     */
    public function canManageSettings()
    {
        return current_user_can('manage_options');
    }

    /**
     * Check if current user can edit a specific attachment
     *
     * @param int $attachment_id Attachment ID
     * @return bool
     */
    public function canEditAttachment($attachment_id)
    {
        $attachment = get_post($attachment_id);
        
        if (!$attachment) {
            return false;
        }

        // Check if user can edit the attachment
        if (!current_user_can('edit_post', $attachment_id)) {
            return false;
        }

        // Check if user can edit others' posts (for unattached media)
        if (!$attachment->post_parent && !current_user_can('edit_others_posts')) {
            return false;
        }

        return true;
    }

    /**
     * Check if current user can save alt text
     *
     * @param int $attachment_id Attachment ID
     * @return bool
     */
    public function canSaveAltText($attachment_id)
    {
        return $this->canAccess() && $this->canEditAttachment($attachment_id);
    }
}
