<?php

namespace JJ\AltTextWorkflowQueue\Admin;

use JJ\AltTextWorkflowQueue\Plugin;

/**
 * Settings page
 */
class Settings
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
     * Render settings page
     */
    public function render()
    {
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to access this page.', 'media-alt-text-workflow-queue'));
        }

        $this->renderHeader();
        $this->renderSettings();
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
     * Render settings form
     */
    private function renderSettings()
    {
        $settings = $this->plugin->getSettings();
        $license_key = get_option('matwq_license_key', '');
        
        // Check for success message
        if (isset($_GET['cache_purged']) && $_GET['cache_purged'] === '1') {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('All caches have been successfully cleared!', 'media-alt-text-workflow-queue'); ?></p>
            </div>
            <?php
        }
        
        ?>
        <div class="matwq-settings-container">
            <form method="post" action="options.php" class="matwq-settings-form">
                <?php settings_fields('matwq_settings_group'); ?>
                
                <div class="matwq-settings-section">
                    <h2><?php esc_html_e('General Settings', 'media-alt-text-workflow-queue'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="matwq_items_per_session">
                                    <?php esc_html_e('Items per Queue Session', 'media-alt-text-workflow-queue'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="matwq_items_per_session" 
                                       name="matwq_settings[items_per_session]" 
                                       value="<?php echo esc_attr($settings['items_per_session']); ?>" 
                                       min="1" 
                                       max="100" 
                                       class="small-text">
                                <p class="description">
                                    <?php esc_html_e('Number of images to process in one queue session.', 'media-alt-text-workflow-queue'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="matwq_cache_duration">
                                    <?php esc_html_e('Badge Cache Duration', 'media-alt-text-workflow-queue'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="matwq_cache_duration" 
                                       name="matwq_settings[cache_duration]" 
                                       value="<?php echo esc_attr($settings['cache_duration'] / 3600); ?>" 
                                       min="1" 
                                       max="24" 
                                       class="small-text">
                                <span><?php esc_html_e('hours', 'media-alt-text-workflow-queue'); ?></span>
                                <p class="description">
                                    <?php esc_html_e('How long to cache the missing alt text count shown in the admin menu badge.', 'media-alt-text-workflow-queue'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="matwq_usage_cache_duration">
                                    <?php esc_html_e('Usage Cache Duration', 'media-alt-text-workflow-queue'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="matwq_usage_cache_duration" 
                                       name="matwq_settings[usage_cache_duration]" 
                                       value="<?php echo esc_attr($settings['usage_cache_duration'] / 3600); ?>" 
                                       min="1" 
                                       max="48" 
                                       class="small-text">
                                <span><?php esc_html_e('hours', 'media-alt-text-workflow-queue'); ?></span>
                                <p class="description">
                                    <?php esc_html_e('How long to cache where images are used (the "Used In" column).', 'media-alt-text-workflow-queue'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="matwq-settings-section matwq-pro-section">
                    <h2>
                        <?php esc_html_e('Pro License', 'media-alt-text-workflow-queue'); ?>
                        <span class="matwq-pro-badge-large">PRO</span>
                    </h2>
                    
                    <div class="matwq-pro-license-overlay">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="matwq_license_key">
                                        <?php esc_html_e('License Key', 'media-alt-text-workflow-queue'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="matwq_license_key" 
                                           name="matwq_license_key" 
                                           value="<?php echo esc_attr($license_key); ?>" 
                                           class="regular-text" 
                                           placeholder="<?php esc_attr_e('Enter your license key...', 'media-alt-text-workflow-queue'); ?>"
                                           disabled>
                                    <button type="button" 
                                            class="button button-secondary" 
                                            disabled>
                                        <?php esc_html_e('Verify License', 'media-alt-text-workflow-queue'); ?>
                                    </button>
                                    <p class="description">
                                        <?php esc_html_e('Enter your Pro license key to unlock premium features.', 'media-alt-text-workflow-queue'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                        
                        <div class="matwq-pro-overlay-message">
                            <span class="dashicons dashicons-lock"></span>
                            <p><?php esc_html_e('Pro Feature', 'media-alt-text-workflow-queue'); ?></p>
                            <a href="https://jessejaeger.com/media-alt-text-workflow-queue" target="_blank" class="button button-primary">
                                <?php esc_html_e('Upgrade to Pro', 'media-alt-text-workflow-queue'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php submit_button(__('Save Settings', 'media-alt-text-workflow-queue')); ?>
            </form>
            
            <!-- Cache Management Section (separate form) -->
            <div class="matwq-settings-section">
                <h2><?php esc_html_e('Cache Management', 'media-alt-text-workflow-queue'); ?></h2>
                
                <div class="matwq-cache-section">
                    <p class="description">
                        <?php esc_html_e('The plugin caches certain data to improve performance, including:', 'media-alt-text-workflow-queue'); ?>
                    </p>
                    <ul class="matwq-cache-info-list">
                        <li><?php esc_html_e('Missing alt text counts (shown in the admin menu badge)', 'media-alt-text-workflow-queue'); ?></li>
                        <li><?php esc_html_e('Image usage information (where images appear in posts/pages)', 'media-alt-text-workflow-queue'); ?></li>
                    </ul>
                    <p class="description">
                        <strong><?php esc_html_e('When to purge the cache:', 'media-alt-text-workflow-queue'); ?></strong><br>
                        <?php esc_html_e('If you\'ve recently added or updated images, changed post content, or notice outdated information in the "Used In" column, purging the cache will force a fresh scan of all data. The cache will automatically rebuild as needed.', 'media-alt-text-workflow-queue'); ?>
                    </p>
                    
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 15px;">
                        <input type="hidden" name="action" value="matwq_purge_cache">
                        <?php wp_nonce_field('matwq_purge_cache'); ?>
                        <button type="submit" class="button button-secondary">
                            <span class="dashicons dashicons-update" style="vertical-align: middle; margin-top: 4px;"></span>
                            <?php esc_html_e('Purge All Caches', 'media-alt-text-workflow-queue'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}
