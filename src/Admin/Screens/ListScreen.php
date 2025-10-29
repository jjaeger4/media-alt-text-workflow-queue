<?php

namespace JJ\AltTextWorkflowQueue\Admin\Screens;

use JJ\AltTextWorkflowQueue\Plugin;

/**
 * List screen for viewing all images with missing alt text
 */
class ListScreen
{
    /**
     * Plugin instance
     *
     * @var Plugin
     */
    private $plugin;
    
    /**
     * Items per page (default, can be overridden by user)
     *
     * @var int
     */
    private $per_page = 20;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->plugin = Plugin::getInstance();
    }

    /**
     * Render the list screen
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
        $this->renderFilters();
        $this->renderImagesList();
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
            echo '<h1>' . esc_html__('Alt Text List', 'media-alt-text-workflow-queue') . '</h1>';
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
     * Render filter controls
     */
    private function renderFilters()
    {
        $filter_missing = isset($_GET['filter_missing']) ? sanitize_text_field($_GET['filter_missing']) : '1';
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $per_page = isset($_GET['per_page']) ? absint($_GET['per_page']) : 20;
        
        ?>
        <div class="matwq-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="matwq-queue">
                <input type="hidden" name="tab" value="list">
                
                <div class="matwq-filter-row">
                    <div class="matwq-filter-group">
                        <label for="filter_missing"><?php esc_html_e('Show:', 'media-alt-text-workflow-queue'); ?></label>
                        <select name="filter_missing" id="filter_missing">
                            <option value="1" <?php selected($filter_missing, '1'); ?>>
                                <?php esc_html_e('Missing Alt Text Only', 'media-alt-text-workflow-queue'); ?>
                            </option>
                            <option value="0" <?php selected($filter_missing, '0'); ?>>
                                <?php esc_html_e('All Images', 'media-alt-text-workflow-queue'); ?>
                            </option>
                            <option value="has" <?php selected($filter_missing, 'has'); ?>>
                                <?php esc_html_e('Has Alt Text', 'media-alt-text-workflow-queue'); ?>
                            </option>
                        </select>
                    </div>
                    
                    <div class="matwq-filter-group">
                        <label for="orderby"><?php esc_html_e('Sort by:', 'media-alt-text-workflow-queue'); ?></label>
                        <select name="orderby" id="orderby">
                            <option value="date" <?php selected($orderby, 'date'); ?>>
                                <?php esc_html_e('Upload Date', 'media-alt-text-workflow-queue'); ?>
                            </option>
                            <option value="title" <?php selected($orderby, 'title'); ?>>
                                <?php esc_html_e('Filename', 'media-alt-text-workflow-queue'); ?>
                            </option>
                            <option value="modified" <?php selected($orderby, 'modified'); ?>>
                                <?php esc_html_e('Modified Date', 'media-alt-text-workflow-queue'); ?>
                            </option>
                        </select>
                    </div>
                    
                    <div class="matwq-filter-group">
                        <label for="order"><?php esc_html_e('Order:', 'media-alt-text-workflow-queue'); ?></label>
                        <select name="order" id="order">
                            <option value="DESC" <?php selected($order, 'DESC'); ?>>
                                <?php esc_html_e('Descending', 'media-alt-text-workflow-queue'); ?>
                            </option>
                            <option value="ASC" <?php selected($order, 'ASC'); ?>>
                                <?php esc_html_e('Ascending', 'media-alt-text-workflow-queue'); ?>
                            </option>
                        </select>
                    </div>
                    
                    <div class="matwq-filter-group matwq-search-group">
                        <label for="s"><?php esc_html_e('Search:', 'media-alt-text-workflow-queue'); ?></label>
                        <input type="search" 
                               name="s" 
                               id="s" 
                               value="<?php echo esc_attr($search); ?>" 
                               placeholder="<?php esc_attr_e('Search filenames...', 'media-alt-text-workflow-queue'); ?>">
                    </div>
                    
                    <div class="matwq-filter-group">
                        <label for="per_page"><?php esc_html_e('Per Page:', 'media-alt-text-workflow-queue'); ?></label>
                        <select name="per_page" id="per_page">
                            <option value="10" <?php selected($per_page, 10); ?>>10</option>
                            <option value="20" <?php selected($per_page, 20); ?>>20</option>
                            <option value="50" <?php selected($per_page, 50); ?>>50</option>
                            <option value="100" <?php selected($per_page, 100); ?>>100</option>
                        </select>
                    </div>
                    
                    <div class="matwq-filter-actions">
                        <button type="submit" class="button button-primary">
                            <?php esc_html_e('Apply Filters', 'media-alt-text-workflow-queue'); ?>
                        </button>
                        <a href="<?php echo esc_url(admin_url('upload.php?page=matwq-queue&tab=list')); ?>" class="button">
                            <?php esc_html_e('Reset', 'media-alt-text-workflow-queue'); ?>
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Render images list
     */
    private function renderImagesList()
    {
        $finder = $this->plugin->getService('finder');
        $usage_locator = $this->plugin->getService('usage_locator');
        
        if (!$finder) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Unable to load image list.', 'media-alt-text-workflow-queue') . '</p></div>';
            return;
        }
        
        // Get filter parameters
        $filter_missing = isset($_GET['filter_missing']) ? sanitize_text_field($_GET['filter_missing']) : '1';
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date';
        $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'DESC';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $per_page = isset($_GET['per_page']) ? absint($_GET['per_page']) : $this->per_page;
        $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        
        // Validate per_page
        $valid_per_page = [10, 20, 50, 100];
        if (!in_array($per_page, $valid_per_page)) {
            $per_page = $this->per_page;
        }
        
        // Build query args
        $query_args = [
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => $per_page,
            'paged' => $paged,
            'orderby' => $orderby,
            'order' => $order,
            'fields' => 'ids', // Return only IDs, not full post objects
        ];
        
        // Add meta query based on filter
        if ($filter_missing === '1') {
            // Missing alt text
            $query_args['meta_query'] = [
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
            ];
        } elseif ($filter_missing === 'has') {
            // Has alt text
            $query_args['meta_query'] = [
                [
                    'key' => '_wp_attachment_image_alt',
                    'value' => '',
                    'compare' => '!=',
                ],
            ];
        }
        // If filter_missing is '0', don't add meta_query (show all)
        
        // Add search
        if (!empty($search)) {
            $query_args['s'] = $search;
        }
        
        $query = new \WP_Query($query_args);
        $image_ids = $query->posts;
        $total_images = $query->found_posts;
        $total_pages = $query->max_num_pages;
        
        ?>
        <div class="matwq-images-list">
            <?php if (empty($image_ids)): ?>
                <div class="notice notice-info">
                    <p>
                        <?php 
                        if ($filter_missing === '1') {
                            esc_html_e('No images found missing alt text.', 'media-alt-text-workflow-queue');
                        } else {
                            esc_html_e('No images found matching your filters.', 'media-alt-text-workflow-queue');
                        }
                        ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="matwq-list-header">
                    <p class="matwq-list-count">
                        <?php 
                        printf(
                            _n('%d image found', '%d images found', $total_images, 'media-alt-text-workflow-queue'),
                            $total_images
                        );
                        
                        if ($total_pages > 1) {
                            echo ' | ';
                            printf(
                                __('Page %d of %d', 'media-alt-text-workflow-queue'),
                                $paged,
                                $total_pages
                            );
                        }
                        ?>
                    </p>
                </div>
                
                <table class="wp-list-table widefat fixed striped matwq-table">
                    <thead>
                        <tr>
                            <th class="matwq-col-preview"><?php esc_html_e('Preview', 'media-alt-text-workflow-queue'); ?></th>
                            <th class="matwq-col-filename"><?php esc_html_e('Filename', 'media-alt-text-workflow-queue'); ?></th>
                            <th class="matwq-col-alt"><?php esc_html_e('Alt Text', 'media-alt-text-workflow-queue'); ?></th>
                            <th class="matwq-col-usage"><?php esc_html_e('Used In', 'media-alt-text-workflow-queue'); ?></th>
                            <th class="matwq-col-date"><?php esc_html_e('Uploaded', 'media-alt-text-workflow-queue'); ?></th>
                            <th class="matwq-col-actions"><?php esc_html_e('Actions', 'media-alt-text-workflow-queue'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($image_ids as $image_id): 
                            $details = $finder->getAttachmentDetails($image_id);
                            if (!$details) continue;
                            
                            $usage_summary = $usage_locator ? $usage_locator->getUsageSummary($image_id, 2) : ['items' => [], 'total' => 0, 'has_more' => false];
                            $has_alt = !empty($details['alt_text']);
                        ?>
                            <tr class="<?php echo $has_alt ? 'matwq-has-alt' : 'matwq-missing-alt'; ?>">
                                <td class="matwq-col-preview">
                                    <img src="<?php echo esc_url($details['thumb_url']); ?>" 
                                         alt="<?php echo esc_attr($details['alt_text']); ?>" 
                                         class="matwq-list-thumbnail">
                                </td>
                                <td class="matwq-col-filename">
                                    <strong><?php echo esc_html($details['filename']); ?></strong>
                                    <br>
                                    <span class="matwq-file-type"><?php echo esc_html($details['mime_type']); ?></span>
                                </td>
                                <td class="matwq-col-alt">
                                    <?php if ($has_alt): ?>
                                        <span class="matwq-alt-text-preview"><?php echo esc_html($details['alt_text']); ?></span>
                                        <span class="matwq-alt-status matwq-has-alt-badge">✓</span>
                                    <?php else: ?>
                                        <span class="matwq-alt-status matwq-missing-alt-badge"><?php esc_html_e('Missing', 'media-alt-text-workflow-queue'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="matwq-col-usage">
                                    <?php if (!empty($usage_summary['items'])): ?>
                                        <ul class="matwq-usage-list">
                                            <?php foreach ($usage_summary['items'] as $usage): ?>
                                                <li>
                                                    <a href="<?php echo esc_url($usage['edit_url']); ?>" target="_blank">
                                                        <?php echo esc_html($usage['post_title']); ?>
                                                    </a>
                                                    <span class="matwq-post-type">(<?php echo esc_html(isset($usage['post_type_label']) ? $usage['post_type_label'] : $usage['post_type']); ?>)</span>
                                                </li>
                                            <?php endforeach; ?>
                                            <?php if ($usage_summary['has_more']): ?>
                                                <li class="matwq-more-usage">
                                                    <?php printf(__('+%d more', 'media-alt-text-workflow-queue'), $usage_summary['total'] - count($usage_summary['items'])); ?>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span class="matwq-no-usage"><?php esc_html_e('Not used', 'media-alt-text-workflow-queue'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="matwq-col-date">
                                    <?php echo esc_html(mysql2date(get_option('date_format'), $details['upload_date'])); ?>
                                </td>
                                <td class="matwq-col-actions">
                                    <a href="<?php echo esc_url(get_edit_post_link($image_id)); ?>" 
                                       class="button button-small" 
                                       target="_blank">
                                        <?php esc_html_e('Edit', 'media-alt-text-workflow-queue'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <div class="matwq-pagination">
                        <?php
                        $base_url = admin_url('upload.php?page=matwq-queue&tab=list');
                        
                        // Preserve filter parameters
                        $params = [];
                        if ($filter_missing !== '1') $params['filter_missing'] = $filter_missing;
                        if ($orderby !== 'date') $params['orderby'] = $orderby;
                        if ($order !== 'DESC') $params['order'] = $order;
                        if (!empty($search)) $params['s'] = $search;
                        if ($per_page !== 20) $params['per_page'] = $per_page;
                        
                        $base_url = add_query_arg($params, $base_url);
                        
                        echo paginate_links([
                            'base' => add_query_arg('paged', '%#%', $base_url),
                            'format' => '',
                            'current' => $paged,
                            'total' => $total_pages,
                            'prev_text' => '&laquo; ' . __('Previous', 'media-alt-text-workflow-queue'),
                            'next_text' => __('Next', 'media-alt-text-workflow-queue') . ' &raquo;',
                        ]);
                        ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }
}
