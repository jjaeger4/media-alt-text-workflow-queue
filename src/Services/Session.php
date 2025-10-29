<?php

namespace JJ\AltTextWorkflowQueue\Services;

/**
 * Service for managing user session state
 */
class Session
{
    /**
     * Get current user ID
     *
     * @return int
     */
    private function getCurrentUserId()
    {
        return get_current_user_id();
    }

    /**
     * Get skipped IDs for current user
     *
     * @return array
     */
    public function getSkippedIds()
    {
        $user_id = $this->getCurrentUserId();
        if (!$user_id) {
            return [];
        }

        $skipped = get_user_meta($user_id, 'matwq_skipped_ids', true);
        return is_array($skipped) ? $skipped : [];
    }

    /**
     * Add ID to skipped list
     *
     * @param int $attachment_id Attachment ID
     * @return bool
     */
    public function addSkippedId($attachment_id)
    {
        $user_id = $this->getCurrentUserId();
        if (!$user_id) {
            return false;
        }

        $skipped = $this->getSkippedIds();
        if (!in_array($attachment_id, $skipped)) {
            $skipped[] = $attachment_id;
            return update_user_meta($user_id, 'matwq_skipped_ids', $skipped);
        }

        return true;
    }

    /**
     * Clear all skipped IDs for current user
     *
     * @return bool
     */
    public function clearSkippedIds()
    {
        $user_id = $this->getCurrentUserId();
        if (!$user_id) {
            return false;
        }

        return delete_user_meta($user_id, 'matwq_skipped_ids');
    }

    /**
     * Get session progress
     *
     * @return array
     */
    public function getSessionProgress()
    {
        $user_id = $this->getCurrentUserId();
        if (!$user_id) {
            return ['completed' => 0, 'skipped' => 0, 'total' => 0];
        }

        $progress = get_user_meta($user_id, 'matwq_session_progress', true);
        $defaults = ['completed' => 0, 'skipped' => 0, 'total' => 0];
        
        return is_array($progress) ? wp_parse_args($progress, $defaults) : $defaults;
    }

    /**
     * Update session progress
     *
     * @param array $progress Progress data
     * @return bool
     */
    public function updateSessionProgress($progress)
    {
        $user_id = $this->getCurrentUserId();
        if (!$user_id) {
            return false;
        }

        $current_progress = $this->getSessionProgress();
        $updated_progress = wp_parse_args($progress, $current_progress);
        
        return update_user_meta($user_id, 'matwq_session_progress', $updated_progress);
    }

    /**
     * Clear session progress
     *
     * @return bool
     */
    public function clearSessionProgress()
    {
        $user_id = $this->getCurrentUserId();
        if (!$user_id) {
            return false;
        }

        return delete_user_meta($user_id, 'matwq_session_progress');
    }

    /**
     * Reset entire session for current user
     *
     * @return bool
     */
    public function resetSession()
    {
        $this->clearSkippedIds();
        $this->clearSessionProgress();
        
        return true;
    }
}
