<?php

namespace JJ\AltTextWorkflowQueue\Admin;

use JJ\AltTextWorkflowQueue\Plugin;

/**
 * Admin menu management
 */
class Menu
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
        $this->init();
    }

    /**
     * Initialize admin menu
     */
    private function init()
    {
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_head', [$this, 'addMenuBadge']);
    }

    /**
     * Add admin menu
     */
    public function addAdminMenu()
    {
        $capability_service = $this->plugin->getService('capability');
        
        // Fallback capability if service not available
        $capability = 'edit_others_posts';
        if ($capability_service) {
            $capability = $capability_service->getRequiredCapability();
        }
        
        // Add single submenu page - handles all tabs internally
        add_submenu_page(
            'upload.php',
            __('Alt Text Queue', 'media-alt-text-workflow-queue'),
            __('Alt Text Queue', 'media-alt-text-workflow-queue'),
            $capability,
            'matwq-queue',
            [$this, 'renderPage']
        );
    }
    
    /**
     * Render page based on current tab
     */
    public function renderPage()
    {
        $current_tab = $this->getCurrentTab();
        
        switch ($current_tab) {
            case 'list':
                $this->renderListPage();
                break;
            case 'learn':
                $this->renderLearnPage();
                break;
            case 'reporting':
                $this->renderReportingPage();
                break;
            case 'settings':
                $this->renderSettingsPage();
                break;
            case 'queue':
            default:
                $this->renderQueuePage();
                break;
        }
    }

    /**
     * Add badge to menu item
     */
    public function addMenuBadge()
    {
        global $submenu;
        
        if (!isset($submenu['upload.php'])) {
            return;
        }

        try {
            $missing_count = $this->plugin->getMissingAltTextCount();
            
            if ($missing_count > 0) {
                foreach ($submenu['upload.php'] as $key => $item) {
                    if ($item[2] === 'matwq-queue') {
                        $submenu['upload.php'][$key][0] .= ' <span class="awaiting-mod">' . $missing_count . '</span>';
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            // Silently fail if there's an error getting the count
            // This prevents fatal errors during admin menu rendering
        }
    }

    /**
     * Render queue page
     */
    public function renderQueuePage()
    {
        $queue_screen = $this->plugin->getService('queue_screen');
        if ($queue_screen) {
            $queue_screen->render();
        } else {
            // Fallback if service not available
            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('Alt Text Queue', 'media-alt-text-workflow-queue') . '</h1>';
            echo '<div class="notice notice-error">';
            echo '<p>' . esc_html__('Error: Queue screen service not available.', 'media-alt-text-workflow-queue') . '</p>';
            echo '<p><strong>Debug Info:</strong></p>';
            echo '<ul>';
            echo '<li>Plugin instance: ' . ($this->plugin ? 'Available' : 'Not available') . '</li>';
            echo '<li>Available services: ' . implode(', ', array_keys($this->plugin->services ?? [])) . '</li>';
            echo '<li>Queue screen service: ' . ($queue_screen ? 'Available' : 'Not available') . '</li>';
            echo '</ul>';
            echo '</div>';
            echo '</div>';
        }
    }

    /**
     * Render list page
     */
    public function renderListPage()
    {
        $list_screen = $this->plugin->getService('list_screen');
        if ($list_screen) {
            $list_screen->render();
        } else {
            // Fallback if service not available
            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('Alt Text List', 'media-alt-text-workflow-queue') . '</h1>';
            echo '<div class="notice notice-error">';
            echo '<p>' . esc_html__('Error: List screen service not available.', 'media-alt-text-workflow-queue') . '</p>';
            echo '</div>';
            echo '</div>';
        }
    }

    /**
     * Render learn page
     */
    public function renderLearnPage()
    {
        $menu = $this->plugin->getService('admin_menu');
        
        if ($menu) {
            $menu->renderPageHeader();
            $menu->renderTabNavigation();
        }
        
        ?>
        <div class="matwq-learn-page">
            <div class="matwq-learn-content">
                <div class="matwq-learn-section">
                    <h2><?php esc_html_e('Alt Text Standards & Best Practices', 'media-alt-text-workflow-queue'); ?></h2>
                    
                    <h3><?php esc_html_e('What is Alt Text?', 'media-alt-text-workflow-queue'); ?></h3>
                    <p>
                        <?php esc_html_e('Alt text (alternative text) is a written description of an image that appears when the image cannot be displayed. It serves multiple critical purposes:', 'media-alt-text-workflow-queue'); ?>
                    </p>
                    <ul>
                        <li><strong><?php esc_html_e('Accessibility:', 'media-alt-text-workflow-queue'); ?></strong> <?php esc_html_e('Screen readers read alt text aloud to visually impaired users', 'media-alt-text-workflow-queue'); ?></li>
                        <li><strong><?php esc_html_e('SEO:', 'media-alt-text-workflow-queue'); ?></strong> <?php esc_html_e('Search engines use alt text to understand image content', 'media-alt-text-workflow-queue'); ?></li>
                        <li><strong><?php esc_html_e('Broken Images:', 'media-alt-text-workflow-queue'); ?></strong> <?php esc_html_e('Alt text displays when images fail to load', 'media-alt-text-workflow-queue'); ?></li>
                    </ul>
                    
                    <h3><?php esc_html_e('Writing Effective Alt Text', 'media-alt-text-workflow-queue'); ?></h3>
                    <ul>
                        <li><strong><?php esc_html_e('Be descriptive:', 'media-alt-text-workflow-queue'); ?></strong> <?php esc_html_e('Describe what the image shows, not just what it is', 'media-alt-text-workflow-queue'); ?></li>
                        <li><strong><?php esc_html_e('Be concise:', 'media-alt-text-workflow-queue'); ?></strong> <?php esc_html_e('Aim for 125 characters or less when possible', 'media-alt-text-workflow-queue'); ?></li>
                        <li><strong><?php esc_html_e('Include context:', 'media-alt-text-workflow-queue'); ?></strong> <?php esc_html_e('Consider how the image relates to surrounding content', 'media-alt-text-workflow-queue'); ?></li>
                        <li><strong><?php esc_html_e('Avoid redundancy:', 'media-alt-text-workflow-queue'); ?></strong> <?php esc_html_e('Don\'t start with "image of" or "picture of"', 'media-alt-text-workflow-queue'); ?></li>
                        <li><strong><?php esc_html_e('Include text in images:', 'media-alt-text-workflow-queue'); ?></strong> <?php esc_html_e('If an image contains important text, include it in the alt text', 'media-alt-text-workflow-queue'); ?></li>
                    </ul>
                    
                    <div class="matwq-example-box">
                        <h4><?php esc_html_e('Examples:', 'media-alt-text-workflow-queue'); ?></h4>
                        <p>
                            <span class="matwq-bad">❌ <?php esc_html_e('Bad:', 'media-alt-text-workflow-queue'); ?></span> "dog"<br>
                            <span class="matwq-good">✅ <?php esc_html_e('Good:', 'media-alt-text-workflow-queue'); ?></span> "Golden retriever puppy playing with a tennis ball in a grassy park"
                        </p>
                        <p>
                            <span class="matwq-bad">❌ <?php esc_html_e('Bad:', 'media-alt-text-workflow-queue'); ?></span> "graph"<br>
                            <span class="matwq-good">✅ <?php esc_html_e('Good:', 'media-alt-text-workflow-queue'); ?></span> "Bar chart showing 45% increase in sales from Q1 to Q2 2024"
                        </p>
                    </div>
                </div>
                
                <div class="matwq-learn-section">
                    <h2><?php esc_html_e('How WordPress Handles Alt Text', 'media-alt-text-workflow-queue'); ?></h2>
                    
                    <h3><?php esc_html_e('Two Levels of Alt Text', 'media-alt-text-workflow-queue'); ?></h3>
                    <p><?php esc_html_e('WordPress stores alt text in two different locations:', 'media-alt-text-workflow-queue'); ?></p>
                    
                    <div class="matwq-info-box matwq-info-primary">
                        <h4>1. <?php esc_html_e('Media Library Alt Text (Default)', 'media-alt-text-workflow-queue'); ?></h4>
                        <ul>
                            <li><?php esc_html_e('Set in the Media Library or when editing an attachment', 'media-alt-text-workflow-queue'); ?></li>
                            <li><?php esc_html_e('This plugin edits this level of alt text', 'media-alt-text-workflow-queue'); ?></li>
                            <li><?php esc_html_e('Used as the default when inserting the image', 'media-alt-text-workflow-queue'); ?></li>
                        </ul>
                    </div>
                    
                    <div class="matwq-info-box matwq-info-warning">
                        <h4>2. <?php esc_html_e('Block-Level Alt Text (Override)', 'media-alt-text-workflow-queue'); ?></h4>
                        <ul>
                            <li><?php esc_html_e('Set in the Image block settings within the post editor', 'media-alt-text-workflow-queue'); ?></li>
                            <li><strong><?php esc_html_e('Overrides the Media Library alt text', 'media-alt-text-workflow-queue'); ?></strong></li>
                            <li><?php esc_html_e('Allows context-specific alt text for the same image', 'media-alt-text-workflow-queue'); ?></li>
                        </ul>
                    </div>
                    
                    <h3><?php esc_html_e('Important: Updating Existing Uses', 'media-alt-text-workflow-queue'); ?></h3>
                    <div class="matwq-warning-box">
                        <p>
                            <strong><?php esc_html_e('⚠️ Key Concept:', 'media-alt-text-workflow-queue'); ?></strong>
                            <?php esc_html_e('When you update alt text in the Media Library, it does NOT automatically update images already inserted in posts!', 'media-alt-text-workflow-queue'); ?>
                        </p>
                        <p>
                            <?php esc_html_e('This is because when you insert an image, WordPress copies the alt text to that specific block. After that, the block\'s alt text is independent from the Media Library.', 'media-alt-text-workflow-queue'); ?>
                        </p>
                        <p>
                            <strong><?php esc_html_e('Solution:', 'media-alt-text-workflow-queue'); ?></strong>
                            <?php esc_html_e('This plugin offers an "Update Existing Uses" option when you save alt text, which will update the alt text in all posts where the image is currently used.', 'media-alt-text-workflow-queue'); ?>
                        </p>
                    </div>
                    
                    <h3><?php esc_html_e('When to Use Different Alt Text', 'media-alt-text-workflow-queue'); ?></h3>
                    <p>
                        <?php esc_html_e('Sometimes the same image might need different alt text depending on context:', 'media-alt-text-workflow-queue'); ?>
                    </p>
                    <ul>
                        <li><?php esc_html_e('A product photo in a catalog vs. in a tutorial', 'media-alt-text-workflow-queue'); ?></li>
                        <li><?php esc_html_e('A team photo highlighting different people in different articles', 'media-alt-text-workflow-queue'); ?></li>
                        <li><?php esc_html_e('A graph used to illustrate different points', 'media-alt-text-workflow-queue'); ?></li>
                    </ul>
                    <p>
                        <?php esc_html_e('In these cases, you can edit the alt text directly in the block editor for that specific use.', 'media-alt-text-workflow-queue'); ?>
                    </p>
                </div>
                
                <div class="matwq-learn-section">
                    <h2><?php esc_html_e('Resources', 'media-alt-text-workflow-queue'); ?></h2>
                    <ul class="matwq-resources-list">
                        <li><a href="https://www.w3.org/WAI/tutorials/images/" target="_blank"><?php esc_html_e('W3C Web Accessibility Initiative - Images Tutorial', 'media-alt-text-workflow-queue'); ?></a></li>
                        <li><a href="https://webaim.org/techniques/alttext/" target="_blank"><?php esc_html_e('WebAIM - Alternative Text Guide', 'media-alt-text-workflow-queue'); ?></a></li>
                        <li><a href="https://www.a11yproject.com/posts/alt-text/" target="_blank"><?php esc_html_e('The A11Y Project - Alt Text Guidelines', 'media-alt-text-workflow-queue'); ?></a></li>
                        <li><a href="https://wordpress.org/documentation/article/writing-accessible-content/" target="_blank"><?php esc_html_e('WordPress Accessibility Documentation', 'media-alt-text-workflow-queue'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
        
        if ($menu) {
            $menu->renderPageFooter();
        }
    }
    
    /**
     * Render reporting page
     */
    public function renderReportingPage()
    {
        $menu = $this->plugin->getService('admin_menu');
        
        if ($menu) {
            $menu->renderPageHeader();
            $menu->renderTabNavigation();
        }
        
        ?>
        <div class="matwq-pro-feature-page">
            <div class="matwq-pro-banner">
                <div class="matwq-pro-icon">
                    <span class="dashicons dashicons-chart-bar"></span>
                </div>
                <h2><?php esc_html_e('Reporting & Analytics', 'media-alt-text-workflow-queue'); ?></h2>
                <p class="matwq-pro-tagline"><?php esc_html_e('Coming Soon in Pro Version', 'media-alt-text-workflow-queue'); ?></p>
                
                <div class="matwq-pro-features">
                    <h3><?php esc_html_e('Pro Features Include:', 'media-alt-text-workflow-queue'); ?></h3>
                    <ul>
                        <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Comprehensive accessibility reports', 'media-alt-text-workflow-queue'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Progress tracking over time', 'media-alt-text-workflow-queue'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Export reports to CSV/PDF', 'media-alt-text-workflow-queue'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Team performance metrics', 'media-alt-text-workflow-queue'); ?></li>
                        <li><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Compliance scoring', 'media-alt-text-workflow-queue'); ?></li>
                    </ul>
                </div>
                
                <div class="matwq-pro-cta">
                    <p><?php esc_html_e('Coming Soon!', 'media-alt-text-workflow-queue'); ?></p>
                    <!--<p><?php esc_html_e('Interested in the Pro version?', 'media-alt-text-workflow-queue'); ?></p>
                    <a href="https://jessejaeger.com/media-alt-text-workflow-queue" target="_blank" class="button button-primary button-hero">
                        <?php esc_html_e('Learn More', 'media-alt-text-workflow-queue'); ?>
                    </a>-->
                </div>
            </div>
        </div>
        <?php
        
        if ($menu) {
            $menu->renderPageFooter();
        }
    }
    
    /**
     * Render settings page
     */
    public function renderSettingsPage()
    {
        $settings_screen = $this->plugin->getService('settings');
        if ($settings_screen) {
            $settings_screen->render();
        } else {
            // Fallback if service not available
            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('Alt Text Settings', 'media-alt-text-workflow-queue') . '</h1>';
            echo '<div class="notice notice-error">';
            echo '<p>' . esc_html__('Error: Settings screen service not available.', 'media-alt-text-workflow-queue') . '</p>';
            echo '</div>';
            echo '</div>';
        }
    }

    /**
     * Get current page
     *
     * @return string
     */
    public function getCurrentPage()
    {
        return isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
    }

    /**
     * Get current tab
     *
     * @return string
     */
    public function getCurrentTab()
    {
        // Check for tab parameter first
        if (isset($_GET['tab'])) {
            $tab = sanitize_text_field($_GET['tab']);
            $valid_tabs = ['queue', 'list', 'learn', 'reporting', 'settings'];
            return in_array($tab, $valid_tabs) ? $tab : 'queue';
        }
        
        return 'queue';
    }

    /**
     * Get menu tabs
     *
     * @return array
     */
    public function getMenuTabs()
    {
        $tabs = [
            'queue' => [
                'title' => __('Queue', 'media-alt-text-workflow-queue'),
                'url' => admin_url('upload.php?page=matwq-queue&tab=queue'),
            ],
            'list' => [
                'title' => __('List', 'media-alt-text-workflow-queue'),
                'url' => admin_url('upload.php?page=matwq-queue&tab=list'),
            ],
            'reporting' => [
                'title' => __('Reporting', 'media-alt-text-workflow-queue') . ' <span class="matwq-pro-badge">PRO</span>',
                'url' => admin_url('upload.php?page=matwq-queue&tab=reporting'),
            ],
        ];

        // Add settings tab for users with manage_options capability
        if (current_user_can('manage_options')) {
            $tabs['settings'] = [
                'title' => __('Settings', 'media-alt-text-workflow-queue'),
                'url' => admin_url('upload.php?page=matwq-queue&tab=settings'),
            ];
        }

        // Add Learn tab at the end
        $tabs['learn'] = [
            'title' => __('Learn', 'media-alt-text-workflow-queue'),
            'url' => admin_url('upload.php?page=matwq-queue&tab=learn'),
        ];

        return $tabs;
    }

    /**
     * Render tab navigation
     */
    public function renderTabNavigation()
    {
        $tabs = $this->getMenuTabs();
        $current_tab = $this->getCurrentTab();
        
        echo '<div class="nav-tab-wrapper">';
        
        foreach ($tabs as $tab_key => $tab_data) {
            $class = ($tab_key === $current_tab) ? 'nav-tab nav-tab-active' : 'nav-tab';
            printf(
                '<a href="%s" class="%s">%s</a>',
                esc_url($tab_data['url']),
                esc_attr($class),
                $tab_data['title'] // Allow HTML for PRO badge
            );
        }
        
        echo '</div>';
    }

    /**
     * Get page title
     *
     * @return string
     */
    public function getPageTitle()
    {
        $current_page = $this->getCurrentPage();
        
        $titles = [
            'matwq-queue' => __('Alt Text Queue', 'media-alt-text-workflow-queue'),
            'matwq-list' => __('Alt Text List', 'media-alt-text-workflow-queue'),
            'matwq-settings' => __('Alt Text Settings', 'media-alt-text-workflow-queue'),
        ];
        
        return isset($titles[$current_page]) ? $titles[$current_page] : __('Alt Text Workflow', 'media-alt-text-workflow-queue');
    }

    /**
     * Render page header
     */
    public function renderPageHeader()
    {
        $title = $this->getPageTitle();
        
        echo '<div class="wrap">';
        echo '<h1>' . esc_html($title) . '</h1>';
        
        // Show action messages
        if (isset($_GET['matwq_message'])) {
            $message = sanitize_text_field($_GET['matwq_message']);
            $updated_count = isset($_GET['updated_count']) ? absint($_GET['updated_count']) : 0;
            
            switch ($message) {
                case 'saved':
                    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Alt text saved successfully!', 'media-alt-text-workflow-queue') . '</p></div>';
                    break;
                case 'saved_and_updated':
                    printf(
                        '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                        sprintf(
                            _n(
                                'Alt text saved and updated in %d post!',
                                'Alt text saved and updated in %d posts!',
                                $updated_count,
                                'media-alt-text-workflow-queue'
                            ),
                            $updated_count
                        )
                    );
                    break;
                case 'error':
                    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('An error occurred. Please try again.', 'media-alt-text-workflow-queue') . '</p></div>';
                    break;
            }
        }
        
        try {
            $missing_count = $this->plugin->getMissingAltTextCount();
            
            if ($missing_count > 0) {
                printf(
                    '<div class="notice notice-info"><p>%s</p></div>',
                    sprintf(
                        __('%d images are missing alt text.', 'media-alt-text-workflow-queue'),
                        $missing_count
                    )
                );
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html__('All images have alt text!', 'media-alt-text-workflow-queue') . '</p></div>';
            }
        } catch (Exception $e) {
            // Silently fail if there's an error getting the count
            echo '<div class="notice notice-warning"><p>' . esc_html__('Unable to load image count. Please refresh the page.', 'media-alt-text-workflow-queue') . '</p></div>';
        }
        
        echo '</div>';
    }

    /**
     * Render page footer
     */
    public function renderPageFooter()
    {
        ?>
        <div class="matwq-plugin-footer">
            <div class="matwq-footer-content">
                <p>
                    <?php esc_html_e('Enjoying this plugin?', 'media-alt-text-workflow-queue'); ?>
                    <a href="https://buymeacoffee.com/jessejaeger" target="_blank" class="matwq-donate-link">
                        <span class="dashicons dashicons-coffee"></span>
                        <?php esc_html_e('Buy me a coffee', 'media-alt-text-workflow-queue'); ?>
                    </a>
                </p>
                <p class="matwq-footer-version">
                    <?php printf(esc_html__('Media Alt Text Workflow Queue v%s', 'media-alt-text-workflow-queue'), MATWQ_VERSION); ?>
                </p>
            </div>
        </div>
        <?php
    }
}
