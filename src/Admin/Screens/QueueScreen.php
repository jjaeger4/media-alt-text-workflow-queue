<?php

namespace JJ\AltTextWorkflowQueue\Admin\Screens;

use JJ\AltTextWorkflowQueue\Plugin;

/**
 * Queue screen for one-by-one alt text editing
 */
class QueueScreen
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
        
        // Register admin_post handlers
        add_action('admin_post_matwq_save_alt_text', [$this, 'handleSaveAltText']);
        add_action('admin_post_matwq_skip_image', [$this, 'handleSkipImage']);
        add_action('admin_post_matwq_restart_session', [$this, 'handleRestartSession']);
    }

    /**
     * Render the queue screen
     */
    public function render()
    {
        $capability_service = $this->plugin->getService('capability');
        
        // Check capability with fallback
        $can_access = true;
        if ($capability_service) {
            $can_access = $capability_service->canAccess();
        } else {
            // Fallback: check if user can edit others' posts
            $can_access = current_user_can('edit_others_posts');
        }
        
        if (!$can_access) {
            wp_die(__('You do not have permission to access this page.', 'media-alt-text-workflow-queue'));
        }

        $this->renderHeader();
        $this->renderQueueInterface();
        $this->renderFooter();
    }

    /**
     * Render page header
     */
    private function renderHeader()
    {
        $menu = $this->plugin->getService('admin_menu');
        if ($menu) {
            $menu->renderPageHeader();
            $menu->renderTabNavigation();
        } else {
            // Fallback header
            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('Alt Text Queue', 'media-alt-text-workflow-queue') . '</h1>';
            echo '</div>';
        }
    }

    /**
     * Render page footer
     */
    private function renderFooter()
    {
        $menu = $this->plugin->getService('admin_menu');
        if ($menu) {
            $menu->renderPageFooter();
        }
    }

    /**
     * Render queue interface
     */
    private function renderQueueInterface()
    {
        $session = $this->plugin->getService('session');
        $finder = $this->plugin->getService('finder');
        $usage_locator = $this->plugin->getService('usage_locator');
        
        if (!$session || !$finder) {
            $this->renderError($session, $finder, $usage_locator);
            return;
        }

        // Get all images with missing alt text
        $all_image_ids = $finder->getMissingAltTextImages();
        $skipped_ids = $session->getSkippedIds();
        
        // Get progress
        $progress = $session->getSessionProgress();
        
        // Initialize progress if needed
        if ($progress['total'] === 0 && count($all_image_ids) > 0) {
            $progress = [
                'completed' => 0,
                'skipped' => 0,
                'total' => count($all_image_ids),
            ];
            $session->updateSessionProgress($progress);
        }
        
        // Find the current image (first one that isn't skipped)
        $current_image = null;
        $current_image_id = null;
        
        foreach ($all_image_ids as $image_id) {
            if (!in_array($image_id, $skipped_ids)) {
                $current_image_id = $image_id;
                $current_image = $finder->getAttachmentDetails($image_id);
                break;
            }
        }
        
        // Check if we're done
        if (!$current_image) {
            $this->renderSessionComplete($progress);
            return;
        }
        
        // Get usage information
        $usage_summary = $usage_locator ? $usage_locator->getUsageSummary($current_image_id, 3) : ['items' => [], 'total' => 0, 'has_more' => false];
        
        ?>
        <div class="matwq-queue-container">
            <div class="matwq-progress-bar">
                <div class="matwq-progress-info">
                    <span class="matwq-progress-text">
                        <?php
                        printf(
                            __('Progress: %d completed, %d skipped of %d total', 'media-alt-text-workflow-queue'),
                            $progress['completed'],
                            $progress['skipped'],
                            $progress['total']
                        );
                        ?>
                    </span>
                    <div class="matwq-progress-bar-fill" style="width: <?php echo esc_attr($this->getProgressPercentage($progress)); ?>%"></div>
                </div>
            </div>
            
            <div class="matwq-image-container">
                <div class="matwq-image-preview">
                    <?php if ($current_image['thumb_url']): ?>
                        <img src="<?php echo esc_url($current_image['thumb_url']); ?>" alt="" class="matwq-thumbnail">
                    <?php endif; ?>
                </div>
                
                <div class="matwq-image-details">
                    <h3><?php echo esc_html($current_image['filename']); ?></h3>
                    <p class="matwq-image-info">
                        <strong><?php esc_html_e('Uploaded:', 'media-alt-text-workflow-queue'); ?></strong>
                        <?php echo esc_html(mysql2date(get_option('date_format'), $current_image['upload_date'])); ?>
                    </p>
                    
                    <?php if (!empty($usage_summary['items'])): ?>
                        <div class="matwq-usage-info">
                            <strong><?php esc_html_e('Used in:', 'media-alt-text-workflow-queue'); ?></strong>
                            <ul>
                                <?php foreach ($usage_summary['items'] as $usage): ?>
                                    <li>
                                        <a href="<?php echo esc_url($usage['edit_url']); ?>" target="_blank">
                                            <?php echo esc_html($usage['post_title']); ?>
                                        </a>
                                        (<?php echo esc_html(isset($usage['post_type_label']) ? $usage['post_type_label'] : $usage['post_type']); ?>)
                                    </li>
                                <?php endforeach; ?>
                                
                                <?php if ($usage_summary['has_more']): ?>
                                    <li class="matwq-more-usage">
                                        <?php
                                        printf(
                                            __('+%d more...', 'media-alt-text-workflow-queue'),
                                            $usage_summary['total'] - count($usage_summary['items'])
                                        );
                                        ?>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <p class="matwq-no-usage"><em><?php esc_html_e('This image is not currently used in any posts.', 'media-alt-text-workflow-queue'); ?></em></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="matwq-alt-text-form">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('matwq_save_alt_' . $current_image_id, 'matwq_nonce'); ?>
                    <input type="hidden" name="action" value="matwq_save_alt_text">
                    <input type="hidden" name="attachment_id" value="<?php echo esc_attr($current_image_id); ?>">
                    <input type="hidden" name="redirect_url" value="<?php echo esc_url(admin_url('upload.php?page=matwq-queue')); ?>">
                    
                    <div class="matwq-form-group">
                        <label for="alt_text"><?php esc_html_e('Alt Text:', 'media-alt-text-workflow-queue'); ?></label>
                        <textarea 
                            id="alt_text" 
                            name="alt_text" 
                            rows="3" 
                            cols="50" 
                            placeholder="<?php esc_attr_e('Describe this image for screen readers...', 'media-alt-text-workflow-queue'); ?>"
                            maxlength="125"
                            required
                        ><?php echo esc_textarea($current_image['alt_text']); ?></textarea>
                        <p class="matwq-help-text">
                            <?php esc_html_e('Describe what the image shows or conveys. Keep it under 125 characters.', 'media-alt-text-workflow-queue'); ?>
                        </p>
                        <div class="matwq-character-count">
                            <span class="matwq-count"><?php echo strlen($current_image['alt_text']); ?></span> / 125 <?php esc_html_e('characters', 'media-alt-text-workflow-queue'); ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($usage_summary['total'])): ?>
                    <div class="matwq-update-existing-option">
                        <label class="matwq-checkbox-label">
                            <input type="checkbox" name="update_existing_uses" value="1" id="update_existing_uses" checked>
                            <?php 
                            printf(
                                _n(
                                    'Also update alt text in the %d post where this image is currently used',
                                    'Also update alt text in the %d posts where this image is currently used',
                                    $usage_summary['total'],
                                    'media-alt-text-workflow-queue'
                                ),
                                $usage_summary['total']
                            );
                            ?>
                        </label>
                        <p class="matwq-help-text-small">
                            <?php esc_html_e('Recommended: This will update the alt text in Image blocks throughout your site.', 'media-alt-text-workflow-queue'); ?>
                            <a href="<?php echo esc_url(admin_url('upload.php?page=matwq-queue&tab=learn#existing-uses')); ?>" target="_blank"><?php esc_html_e('Learn more', 'media-alt-text-workflow-queue'); ?></a>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="matwq-form-actions">
                        <button type="submit" class="button button-primary matwq-save-next">
                            <?php esc_html_e('Save & Next', 'media-alt-text-workflow-queue'); ?>
                        </button>
                        <button type="button" class="button matwq-skip-image" onclick="document.getElementById('matwq-skip-form').submit();">
                            <?php esc_html_e('Skip', 'media-alt-text-workflow-queue'); ?>
                        </button>
                        <button type="button" class="button matwq-restart-session" onclick="if(confirm('<?php echo esc_js(__('Are you sure you want to restart the session? This will clear all skipped items.', 'media-alt-text-workflow-queue')); ?>')) document.getElementById('matwq-restart-form').submit();">
                            <?php esc_html_e('Restart Session', 'media-alt-text-workflow-queue'); ?>
                        </button>
                    </div>
                </form>
                
                <!-- Hidden forms for skip and restart -->
                <form id="matwq-skip-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:none;">
                    <?php wp_nonce_field('matwq_skip_' . $current_image_id, 'matwq_nonce'); ?>
                    <input type="hidden" name="action" value="matwq_skip_image">
                    <input type="hidden" name="attachment_id" value="<?php echo esc_attr($current_image_id); ?>">
                    <input type="hidden" name="redirect_url" value="<?php echo esc_url(admin_url('upload.php?page=matwq-queue')); ?>">
                </form>
                
                <form id="matwq-restart-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:none;">
                    <?php wp_nonce_field('matwq_restart_session', 'matwq_nonce'); ?>
                    <input type="hidden" name="action" value="matwq_restart_session">
                    <input type="hidden" name="redirect_url" value="<?php echo esc_url(admin_url('upload.php?page=matwq-queue')); ?>">
                </form>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var textarea = document.getElementById('alt_text');
            var countDisplay = document.querySelector('.matwq-count');
            
            if (textarea && countDisplay) {
                textarea.addEventListener('input', function() {
                    countDisplay.textContent = this.value.length;
                });
            }
        });
        </script>
        <?php
    }

    /**
     * Get progress percentage
     *
     * @param array $progress Progress data
     * @return float
     */
    private function getProgressPercentage($progress)
    {
        if ($progress['total'] === 0) {
            return 0;
        }
        
        $completed = $progress['completed'] + $progress['skipped'];
        return ($completed / $progress['total']) * 100;
    }

    /**
     * Render session complete message
     */
    private function renderSessionComplete($progress)
    {
        ?>
        <div class="matwq-session-complete">
            <h2><?php esc_html_e('Session Complete!', 'media-alt-text-workflow-queue'); ?></h2>
            <p>
                <?php
                printf(
                    __('Great work! You completed %d images and skipped %d images.', 'media-alt-text-workflow-queue'),
                    $progress['completed'],
                    $progress['skipped']
                );
                ?>
            </p>
            
            <?php if ($progress['skipped'] > 0): ?>
                <p><?php esc_html_e('Click "Start New Session" to revisit the skipped images.', 'media-alt-text-workflow-queue'); ?></p>
            <?php endif; ?>
            
            <div class="matwq-session-actions">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                    <?php wp_nonce_field('matwq_restart_session', 'matwq_nonce'); ?>
                    <input type="hidden" name="action" value="matwq_restart_session">
                    <input type="hidden" name="redirect_url" value="<?php echo esc_url(admin_url('upload.php?page=matwq-queue')); ?>">
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Start New Session', 'media-alt-text-workflow-queue'); ?>
                    </button>
                </form>
                <a href="<?php echo esc_url(admin_url('upload.php?page=matwq-list')); ?>" class="button">
                    <?php esc_html_e('View All Images', 'media-alt-text-workflow-queue'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render error message
     */
    private function renderError($session = null, $finder = null, $usage_locator = null)
    {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('Unable to load queue. Required services are not available.', 'media-alt-text-workflow-queue'); ?></p>
            <p><strong><?php esc_html_e('Debug Info:', 'media-alt-text-workflow-queue'); ?></strong></p>
            <ul>
                <li>Session service: <?php echo $session ? 'Available' : 'NOT AVAILABLE'; ?></li>
                <li>Finder service: <?php echo $finder ? 'Available' : 'NOT AVAILABLE'; ?></li>
                <li>Usage Locator service: <?php echo $usage_locator ? 'Available' : 'NOT AVAILABLE'; ?></li>
                <li>All services: <?php echo implode(', ', array_keys($this->plugin->services)); ?></li>
            </ul>
        </div>
        <?php
    }

    /**
     * Handle save alt text form submission
     */
    public function handleSaveAltText()
    {
        // Verify nonce
        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
        check_admin_referer('matwq_save_alt_' . $attachment_id, 'matwq_nonce');
        
        $capability_service = $this->plugin->getService('capability');
        if (!$capability_service || !$capability_service->canAccess()) {
            wp_die(__('You do not have permission to perform this action.', 'media-alt-text-workflow-queue'));
        }
        
        $alt_text = isset($_POST['alt_text']) ? sanitize_text_field($_POST['alt_text']) : '';
        $update_existing = isset($_POST['update_existing_uses']) && $_POST['update_existing_uses'] === '1';
        $redirect_url = isset($_POST['redirect_url']) ? esc_url_raw($_POST['redirect_url']) : admin_url('upload.php?page=matwq-queue&tab=queue');
        
        $finder = $this->plugin->getService('finder');
        $session = $this->plugin->getService('session');
        
        if ($finder && $session) {
            // Save alt text
            $finder->updateAltText($attachment_id, $alt_text);
            
            // Update existing uses if requested
            $updated_posts_count = 0;
            if ($update_existing) {
                $block_updater = $this->plugin->getService('block_updater');
                if ($block_updater) {
                    $results = $block_updater->updateAltTextInExistingUses($attachment_id, $alt_text);
                    $updated_posts_count = $results['updated_count'];
                }
            }
            
            // Update progress
            $progress = $session->getSessionProgress();
            $progress['completed']++;
            $session->updateSessionProgress($progress);
            
            // Clear cache
            $this->plugin->clearMissingAltTextCountCache();
            $usage_locator = $this->plugin->getService('usage_locator');
            if ($usage_locator) {
                $usage_locator->clearCache($attachment_id);
            }
            
            // Redirect with success message
            $message = $updated_posts_count > 0 ? 'saved_and_updated' : 'saved';
            $redirect_url = add_query_arg([
                'matwq_message' => $message,
                'updated_count' => $updated_posts_count
            ], $redirect_url);
            wp_redirect($redirect_url);
        } else {
            wp_redirect(add_query_arg('matwq_message', 'error', $redirect_url));
        }
        exit;
    }

    /**
     * Handle skip image form submission
     */
    public function handleSkipImage()
    {
        // Verify nonce
        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
        check_admin_referer('matwq_skip_' . $attachment_id, 'matwq_nonce');
        
        $capability_service = $this->plugin->getService('capability');
        if (!$capability_service || !$capability_service->canAccess()) {
            wp_die(__('You do not have permission to perform this action.', 'media-alt-text-workflow-queue'));
        }
        
        $redirect_url = isset($_POST['redirect_url']) ? esc_url_raw($_POST['redirect_url']) : admin_url('upload.php?page=matwq-queue');
        
        $session = $this->plugin->getService('session');
        
        if ($session) {
            // Add to skipped list
            $session->addSkippedId($attachment_id);
            
            // Update progress
            $progress = $session->getSessionProgress();
            $progress['skipped']++;
            $session->updateSessionProgress($progress);
            
            // Redirect
            wp_redirect(add_query_arg('matwq_message', 'skipped', $redirect_url));
        } else {
            wp_redirect(add_query_arg('matwq_message', 'error', $redirect_url));
        }
        exit;
    }

    /**
     * Handle restart session form submission
     */
    public function handleRestartSession()
    {
        // Verify nonce
        check_admin_referer('matwq_restart_session', 'matwq_nonce');
        
        $capability_service = $this->plugin->getService('capability');
        if (!$capability_service || !$capability_service->canAccess()) {
            wp_die(__('You do not have permission to perform this action.', 'media-alt-text-workflow-queue'));
        }
        
        $redirect_url = isset($_POST['redirect_url']) ? esc_url_raw($_POST['redirect_url']) : admin_url('upload.php?page=matwq-queue');
        
        $session = $this->plugin->getService('session');
        
        if ($session) {
            // Reset session
            $session->resetSession();
            
            // Redirect
            wp_redirect(add_query_arg('matwq_message', 'restarted', $redirect_url));
        } else {
            wp_redirect(add_query_arg('matwq_message', 'error', $redirect_url));
        }
        exit;
    }
}
