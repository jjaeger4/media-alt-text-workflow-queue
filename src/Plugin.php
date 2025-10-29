<?php

namespace JJ\AltTextWorkflowQueue;

/**
 * Main plugin class that handles initialization and service registration
 */
class Plugin
{
    /**
     * Plugin instance
     *
     * @var Plugin
     */
    private static $instance = null;

    /**
     * Services container
     *
     * @var array
     */
    public $services = [];

    /**
     * Get plugin instance
     *
     * @return Plugin
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Boot the plugin
     */
    public function boot()
    {
        
        // Initialize core services early
        $this->initServices();
        
        add_action('init', [$this, 'init']);
        add_action('admin_init', [$this, 'adminInit']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        
        // Initialize admin services on init hook if in admin
        add_action('init', [$this, 'maybeInitAdminServices']);
    }

    /**
     * Initialize plugin
     */
    public function init()
    {
        
        // Load text domain
        load_plugin_textdomain(
            'media-alt-text-workflow-queue',
            false,
            dirname(MATWQ_PLUGIN_BASENAME) . '/languages'
        );
        
    }

    /**
     * Maybe initialize admin services
     */
    public function maybeInitAdminServices()
    {
        
        // Ensure core services are initialized first
        if (!isset($this->services['finder'])) {
            $this->initServices();
        }
        
        // Check if we're in admin area by looking at the current screen or request
        $is_admin_area = is_admin() || (isset($_GET['page']) && strpos($_GET['page'], 'matwq') !== false);
        
        if ($is_admin_area && !isset($this->services['admin_menu'])) {
            $this->initAdminServices();
        } else {
        }
    }

    /**
     * Initialize admin
     */
    public function adminInit()
    {
        if (!is_admin()) {
            return;
        }

        // Register settings
        $this->registerSettings();
        
        // Register admin actions
        add_action('admin_post_matwq_purge_cache', [$this, 'handlePurgeCache']);
    }
    
    /**
     * Handle cache purge request
     */
    public function handlePurgeCache()
    {
        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'media-alt-text-workflow-queue'));
        }
        
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'matwq_purge_cache')) {
            wp_die(__('Security check failed.', 'media-alt-text-workflow-queue'));
        }
        
        global $wpdb;
        
        // Delete all plugin transients
        $deleted = $wpdb->query(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_matwq_%' 
            OR option_name LIKE '_transient_timeout_matwq_%'"
        );
        
        // Redirect back with success message
        $redirect_url = add_query_arg([
            'page' => 'matwq-queue',
            'tab' => 'settings',
            'cache_purged' => '1',
        ], admin_url('upload.php'));
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Register plugin settings
     */
    private function registerSettings()
    {
        register_setting('matwq_settings_group', 'matwq_settings', [
            'sanitize_callback' => [$this, 'sanitizeSettings'],
        ]);
        
        register_setting('matwq_settings_group', 'matwq_license_key', [
            'sanitize_callback' => 'sanitize_text_field',
        ]);
    }
    
    /**
     * Sanitize settings
     *
     * @param array $input Input settings
     * @return array Sanitized settings
     */
    public function sanitizeSettings($input)
    {
        $settings = $this->getSettings();
        
        if (isset($input['items_per_session'])) {
            $settings['items_per_session'] = absint($input['items_per_session']);
            $settings['items_per_session'] = max(1, min(100, $settings['items_per_session']));
        }
        
        if (isset($input['cache_duration'])) {
            $cache_hours = absint($input['cache_duration']);
            $settings['cache_duration'] = max(1, min(24, $cache_hours)) * 3600;
        }
        
        if (isset($input['usage_cache_duration'])) {
            $cache_hours = absint($input['usage_cache_duration']);
            $settings['usage_cache_duration'] = max(1, min(48, $cache_hours)) * 3600;
        }
        
        return $settings;
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     */
    public function enqueueAdminAssets($hook)
    {
        // Only load on our plugin pages
        if (strpos($hook, 'matwq') === false) {
            return;
        }

        wp_enqueue_style(
            'matwq-admin',
            MATWQ_PLUGIN_URL . 'assets/css/admin.css',
            [],
            MATWQ_VERSION
        );

        wp_enqueue_script(
            'matwq-admin',
            MATWQ_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            MATWQ_VERSION,
            true
        );

        // Localize script
        wp_localize_script('matwq-admin', 'matwq', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('matwq_nonce'),
            'strings' => [
                'confirmSkip' => __('Are you sure you want to skip this image?', 'media-alt-text-workflow-queue'),
                'confirmRestart' => __('Are you sure you want to restart the session? This will clear all skipped items.', 'media-alt-text-workflow-queue'),
                'saving' => __('Saving...', 'media-alt-text-workflow-queue'),
                'error' => __('An error occurred. Please try again.', 'media-alt-text-workflow-queue'),
            ],
        ]);
    }

    /**
     * Initialize core services
     */
    private function initServices()
    {
        
        try {
            $this->services['finder'] = new Services\Finder();
            
            $this->services['session'] = new Services\Session();
            
            $this->services['usage_locator'] = new Services\UsageLocator();
            
            $this->services['capability'] = new Services\Capability();
            
            $this->services['block_updater'] = new Services\BlockUpdater();
            
        } catch (Exception $e) {
        }
    }

    /**
     * Initialize admin services
     */
    public function initAdminServices()
    {
        // Debug: Log that this method is being called
        
        try {
            $this->services['admin_menu'] = new Admin\Menu();
            
            $this->services['queue_screen'] = new Admin\Screens\QueueScreen();
            
            $this->services['list_screen'] = new Admin\Screens\ListScreen();
            
            $this->services['settings'] = new Admin\Settings();
            
        } catch (Exception $e) {
            // Log error but don't crash
        }
    }

    /**
     * Get a service
     *
     * @param string $name Service name
     * @return mixed|null
     */
    public function getService($name)
    {
        return isset($this->services[$name]) ? $this->services[$name] : null;
    }

    /**
     * Get plugin settings
     *
     * @return array
     */
    public function getSettings()
    {
        $defaults = [
            'items_per_session' => 50,
            'required_capability' => 'edit_others_posts',
            'cache_duration' => 3600,
            'usage_cache_duration' => 43200,
        ];

        return wp_parse_args(get_option('matwq_settings', []), $defaults);
    }

    /**
     * Update plugin settings
     *
     * @param array $settings Settings to update
     * @return bool
     */
    public function updateSettings($settings)
    {
        $current_settings = $this->getSettings();
        $updated_settings = wp_parse_args($settings, $current_settings);
        
        return update_option('matwq_settings', $updated_settings);
    }

    /**
     * Get missing alt text count
     *
     * @return int
     */
    public function getMissingAltTextCount()
    {
        $cache_key = 'matwq_missing_count';
        $count = get_transient($cache_key);

        if (false === $count) {
            $finder = $this->getService('finder');
            if ($finder) {
                $count = $finder->getMissingAltTextCount();
            } else {
                // Fallback: direct query if service not available
                $count = $this->getMissingAltTextCountDirect();
            }
            set_transient($cache_key, $count, $this->getSettings()['cache_duration']);
        }

        return $count;
    }

    /**
     * Get missing alt text count directly (fallback method)
     *
     * @return int
     */
    private function getMissingAltTextCountDirect()
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
     * Clear missing alt text count cache
     */
    public function clearMissingAltTextCountCache()
    {
        delete_transient('matwq_missing_count');
    }
}
