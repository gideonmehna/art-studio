<?php
/**
 * Plugin Name: Art Gallery Block
 * Description: Interactive art gallery with filtering and load more functionality
 * Version: 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


// Check if plugin constants are defined
if (!defined('ART_STUDIO_PLUGIN_URL') || !defined('ART_STUDIO_VERSION')) {
    return;
}



class ArtGalleryBlock {
    
    public function __construct() {
        
        add_action('init', array($this, 'register_block'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('wp_ajax_art_studio_load_more_art', array($this, 'load_more_art'));
        add_action('wp_ajax_nopriv_art_studio_load_more_art', array($this, 'load_more_art'));
        add_action('wp_ajax_art_studio_filter_art', array($this, 'filter_art'));
        add_action('wp_ajax_nopriv_art_studio_filter_art', array($this, 'filter_art'));
    }
    
    public function register_block() {
        
        wp_register_script(
            'art-studio-art-gallery-block-editor',
            ART_STUDIO_PLUGIN_URL . 'assets/js/art-gallery/block.js',
            array('wp-blocks', 'wp-element', 'wp-editor'),
            ART_STUDIO_VERSION
        );
        
        wp_register_style(
            'art-studio-art-gallery-block-editor',
            ART_STUDIO_PLUGIN_URL . 'assets/css/art-gallery/editor.css',
            array(),
            ART_STUDIO_VERSION
        );
        
        wp_register_style(
            'art-studio-art-gallery-block-frontend',
            ART_STUDIO_PLUGIN_URL . 'assets/css/art-gallery/style.css',
            array(),
            ART_STUDIO_VERSION
        );
        
        register_block_type('custom/art-gallery', array(
            'editor_script' => 'art-studio-art-gallery-block-editor',
            'editor_style' => 'art-studio-art-gallery-block-editor',
            'style' => 'art-studio-art-gallery-block-frontend',
            'attributes' => array(
                'uploadUrl' => array(
                    'type' => 'string',
                    'default' => ''
                )
            ),
            'render_callback' => array($this, 'render_block')
        ));
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_script(
            'art-studio-art-gallery-frontend',
            ART_STUDIO_PLUGIN_URL . 'assets/js/art-gallery/frontend.js',
            array('jquery'),
            ART_STUDIO_VERSION,
            true
        );
        
        wp_localize_script('art-studio-art-gallery-frontend', 'artGalleryAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('art_studio_nonce'),
            'loading_text' => __('Loading...', 'art-studio'),
            'no_more_text' => __('No more artwork to load', 'art-studio'),
        ));
    }
    
    public function render_block($attributes) {
        $emotions = get_terms(array(
            'taxonomy' => 'art_emotion',
            'hide_empty' => false,
        ));
        
        $artists = $this->get_all_artists();
        $initial_arts = $this->get_art_pieces();
        $upload_url = !empty($attributes['uploadUrl']) ? $attributes['uploadUrl'] : '#';
        
        ob_start();
        ?>
        <!-- Base Gallery Container (Works without JS) -->
        <div class="art-gallery-container" data-has-js="false">
            <div class="art-gallery-filters">
                <div class="emotion-sidebar"></div>
                <p>Filters:</p>
                
                <!-- No-JS version -->
                <form method="get" class="no-js-filters">
                    <div class="filter-group">
                        <select name="artist" class="artist-filter">
                            <option value="">All Artists</option>
                            <?php foreach ($artists as $artist): ?>
                                <option value="<?php echo esc_attr($artist); ?>" 
                                    <?php selected(isset($_GET['artist']) ? $_GET['artist'] : '', $artist); ?>>
                                    <?php echo esc_html($artist); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <input type="number" name="age_min" class="age-min" placeholder="Min age" min="1" max="18" 
                            value="<?php echo isset($_GET['age_min']) ? intval($_GET['age_min']) : ''; ?>">
                        <input type="number" name="age_max" class="age-max" placeholder="Max age" min="1" max="18"
                            value="<?php echo isset($_GET['age_max']) ? intval($_GET['age_max']) : ''; ?>">
                        <button type="submit" class="apply-age-filter">Apply</button>
                    </div>
                </form>
                
                <!-- JS-enhanced version -->
                <div class="js-filters" style="gap: 20px; display: flex;flex-direction: row;flex-wrap: nowrap;">
                    <div class="filter-group">
                        <button class="filter-btn active" data-filter="artist" data-value="">
                            All Artists
                        </button>
                        <select class="artist-filter" style="display: none;">
                            <option value="">All Artists</option>
                            <?php foreach ($artists as $artist): ?>
                                <option value="<?php echo esc_attr($artist); ?>">
                                    <?php echo esc_html($artist); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="filter-toggle" data-target="artist">Artist ▼</button>
                    </div>
                    
                    <div class="filter-group">
                        <button class="filter-btn active" data-filter="age" data-value="">
                            All Ages
                        </button>
                        <div class="age-filter" style="display: none;">
                            <input type="number" class="age-min" placeholder="Min age" min="1" max="18">
                            <input type="number" class="age-max" placeholder="Max age" min="1" max="18">
                            <button class="apply-age-filter">Apply</button>
                        </div>
                        <button class="filter-toggle" data-target="age">Age ▼</button>
                    </div>
                </div>
            </div>
            
            <div class="art-gallery-main">
                <div class="emotion-sidebar">
                    <div class="emotion-categories">
                        <a href="<?php echo esc_url(remove_query_arg('filter_emotion')); ?>" 
                           class="emotion-btn <?php echo !isset($_GET['filter_emotion']) ? 'active' : ''; ?>">
                            <div class="emotion-icon all-emotions">All</div>
                        </a>
                        
                        <?php foreach ($emotions as $emotion): ?>
                            <?php 
                            $featured_image = get_term_meta($emotion->term_id, 'featured_image', true);
                            $image_url = $featured_image ? wp_get_attachment_url($featured_image) : '';
                            $is_active = isset($_GET['filter_emotion']) && $_GET['filter_emotion'] === $emotion->slug;
                            ?>
                            <a href="<?php echo esc_url(add_query_arg('filter_emotion', $emotion->slug)); ?>" 
                               class="emotion-btn <?php echo $is_active ? 'active' : ''; ?>"
                               data-emotion="<?php echo esc_attr($emotion->slug); ?>">
                                <?php if ($image_url): ?>
                                    <img src="<?php echo esc_url($image_url); ?>" 
                                         alt="<?php echo esc_attr($emotion->name); ?>" 
                                         class="emotion-icon">
                                <?php else: ?>
                                    <div class="emotion-icon">
                                        <?php echo esc_html(substr($emotion->name, 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="art-emotion-bar"></div>
                
                <div class="art-content">
                    <div class="art-grid" id="art-grid">
                        <?php 
                        // Get filtered results if parameters exist
                        $filter_args = array();
                        if (!empty($_GET['filter_emotion'])) {
                            $filter_args['emotion'] = sanitize_text_field($_GET['filter_emotion']);
                        }
                        if (!empty($_GET['artist'])) {
                            $filter_args['artist'] = sanitize_text_field($_GET['artist']);
                        }
                        
                        $filtered_arts = !empty($filter_args) ? 
                            $this->get_art_pieces($filter_args) : 
                            $initial_arts;
                        
                        echo $this->render_art_items($filtered_arts['posts']); 
                        ?>
                    </div>
                    
                    <?php if ($filtered_arts['has_more']): ?>
                        <div class="load-more-container js-only">
                            <button id="load-more-btn" class="load-more-btn">
                                Load More Artwork
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="upload-container">
                        <a href="<?php echo esc_url($upload_url); ?>" class="upload-btn">
                            + Upload Artwork
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- JavaScript Enhancement -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const gallery = document.querySelector('.art-gallery-container');
                if (gallery) {
                    gallery.setAttribute('data-has-js', 'true');
                    // Hide no-js form and show JS version
                    const noJsFilters = gallery.querySelector('.no-js-filters');
                    const jsFilters = gallery.querySelector('.js-filters');
                    if (noJsFilters && jsFilters) {
                        noJsFilters.style.display = 'none';
                        jsFilters.style.display = 'flex';
                    }
                }
            });
        </script>
        <?php
        return ob_get_clean();
    }
    
    private function get_all_artists() {
        global $wpdb;
        
        $artists = $wpdb->get_col($wpdb->prepare("
            SELECT DISTINCT meta_value 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = %s 
            AND meta_value != '' 
            ORDER BY meta_value ASC
        ", '_artist_name'));
        
        return $artists;
    }
    
    private function get_art_pieces($args = array()) {
        $defaults = array(
            'post_type' => 'art_piece',
            'posts_per_page' => 6,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'paged' => 1,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        // Handle emotion filter
        if (!empty($args['emotion'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'art_emotion',
                    'field' => 'slug',
                    'terms' => $args['emotion'],
                ),
            );
            unset($args['emotion']);
        }
        
        // Handle artist filter
        if (!empty($args['artist'])) {
            $args['meta_query'][] = array(
                'key' => '_artist_name',
                'value' => $args['artist'],
                'compare' => '=',
            );
            unset($args['artist']);
        }
        
        // Handle age filter
        if (!empty($args['age_min']) || !empty($args['age_max'])) {
            $age_query = array('key' => '_artist_age');
            
            if (!empty($args['age_min']) && !empty($args['age_max'])) {
                $age_query['value'] = array($args['age_min'], $args['age_max']);
                $age_query['compare'] = 'BETWEEN';
                $age_query['type'] = 'NUMERIC';
            } elseif (!empty($args['age_min'])) {
                $age_query['value'] = $args['age_min'];
                $age_query['compare'] = '>=';
                $age_query['type'] = 'NUMERIC';
            } elseif (!empty($args['age_max'])) {
                $age_query['value'] = $args['age_max'];
                $age_query['compare'] = '<=';
                $age_query['type'] = 'NUMERIC';
            }
            
            $args['meta_query'][] = $age_query;
            unset($args['age_min'], $args['age_max']);
        }
        
        $query = new WP_Query($args);
        
        return array(
            'posts' => $query->posts,
            'has_more' => $query->found_posts > ($args['paged'] * $args['posts_per_page']),
            'total' => $query->found_posts,
        );
    }
    
    private function render_art_items($posts) {
        if (empty($posts)) {
            return '<div class="no-art-found">No artwork found.</div>';
        }
        
        $output = '';
        foreach ($posts as $post) {
            $artist_name = get_post_meta($post->ID, '_artist_name', true);
            $artist_age = get_post_meta($post->ID, '_artist_age', true);
            $featured_image = get_the_post_thumbnail_url($post->ID, 'medium');

            // Get post content and tags
            $content = apply_filters('the_content', $post->post_content);
            $tags = get_the_tags($post->ID);
            $emotions = get_the_terms($post->ID, 'art_emotion');
            
            $output .= '<div class="art-item" data-post-id="' . esc_attr($post->ID) . '">';
            $output .= '<div class="art-image">';
            if ($featured_image) {
                $output .= '<img src="' . esc_url($featured_image) . '" alt="' . esc_attr($post->post_title) . '">';
            } else {
                $output .= '<div class="no-image-placeholder"></div>';
            }
            $output .= '</div>';
            $output .= '<div class="art-info">';
            $output .= '<h3 class="art-title">' . esc_html($post->post_title) . '</h3>';
            $output .= '<p class="art-artist">' . esc_html($artist_name) . ', Age ' . esc_html($artist_age) . '</p>';
            $output .= '</div>';
            

            
            // Add modal HTML
            $output .= '<div class="art-modal" id="modal-' . esc_attr($post->ID) . '">';
            $output .= '<div class="art-modal-content">';
            $output .= '<button class="modal-close">&times;</button>';
            $output .= '<div class="modal-grid">';
            
            // Left column - Image
            $output .= '<div class="modal-media">';
            if ($featured_image) {
                $output .= '<img src="' . esc_url($featured_image) . '" alt="' . esc_attr($post->post_title) . '">';
            }
            $output .= '</div>';
            
            // Right column - Content
            $output .= '<div class="modal-content">';
            $output .= '<h2>' . esc_html($post->post_title) . '</h2>';
            $output .= '<p class="modal-artist">' . esc_html($artist_name) . ', Age ' . esc_html($artist_age) . '</p>';
            $output .= '<div class="modal-description">' . $content . '</div>';
            
            // Tags
            if ($tags) {
                $output .= '<div class="modal-tags">';
                $output .= '<span class="tags-label">Tags:</span> ';
                $tag_names = array_map(function($tag) {
                    return esc_html($tag->name);
                }, $tags);
                $output .= implode(', ', $tag_names);
                $output .= '</div>';
            }
            
            // Emotions with images
            if ($emotions) {
                $output .= '<div class="modal-emotions">';
                foreach ($emotions as $emotion) {
                    $featured_image_id = get_term_meta($emotion->term_id, 'featured_image', true);
                    if ($featured_image_id) {
                        $image_url = wp_get_attachment_image_url($featured_image_id, 'thumbnail');
                        $output .= '<div class="emotion-thumbnail">';
                        $output .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($emotion->name) . '">';
                        $output .= '<span class="emotion-name">' . esc_html($emotion->name) . '</span>';
                        $output .= '</div>';
                    }
                }
                $output .= '</div>';
            }
            
            $output .= '</div>'; // End modal-content
            $output .= '</div>'; // End modal-grid
            $output .= '</div>'; // End art-modal-content
            $output .= '</div>'; // End art-modal
            $output .= '</div>'; // End art-item
        }
        
        return $output;
    }
    
    public function load_more_art() {
        
        error_log('=== LOAD MORE ART AJAX CALLED ===');
        error_log('POST data: ' . print_r($_POST, true));
        error_log('REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
        
        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log('ERROR: Not a POST request');
            wp_send_json_error('Method not allowed');
            return;
        }
        
        // Check nonce first
        if (!isset($_POST['nonce'])) {
            error_log('ERROR: No nonce provided');
            wp_send_json_error('No nonce provided');
            return;
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'art_studio_nonce')) {
            error_log('ERROR: Invalid nonce - provided: ' . $_POST['nonce']);
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        error_log('SUCCESS: Nonce verified');
        
        // Check page parameter
        if (!isset($_POST['page'])) {
            error_log('ERROR: No page parameter');
            wp_send_json_error('Missing page parameter');
            return;
        }


        check_ajax_referer('art_studio_nonce', 'nonce');
        
        $page = intval($_POST['page']);
        $filters = $_POST['filters'] ?? array();
        
        $args = array(
            'paged' => $page,
        );
        
        if (!empty($filters['emotion'])) {
            $args['emotion'] = sanitize_text_field($filters['emotion']);
        }
        
        if (!empty($filters['artist'])) {
            $args['artist'] = sanitize_text_field($filters['artist']);
        }
        
        if (!empty($filters['age_min'])) {
            $args['age_min'] = intval($filters['age_min']);
        }
        
        if (!empty($filters['age_max'])) {
            $args['age_max'] = intval($filters['age_max']);
        }
        
        $result = $this->get_art_pieces($args);
        
        wp_send_json_success(array(
            'html' => $this->render_art_items($result['posts']),
            'has_more' => $result['has_more'],
        ));
    }
    
    public function filter_art() {
        
        error_log('=== FILTER ART AJAX CALLED ===');
        error_log('POST data: ' . print_r($_POST, true));
        
        // Same debugging structure as above
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log('ERROR: Not a POST request');
            wp_send_json_error('Method not allowed');
            return;
        }
        
        if (!isset($_POST['nonce'])) {
            error_log('ERROR: No nonce provided');
            wp_send_json_error('No nonce provided');
            return;
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'art_studio_nonce')) {
            error_log('ERROR: Invalid nonce');
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        check_ajax_referer('art_studio_nonce', 'nonce');
        
        $filters = $_POST['filters'] ?? array();
        
        $args = array();
        
        if (!empty($filters['emotion'])) {
            $args['emotion'] = sanitize_text_field($filters['emotion']);
        }
        
        if (!empty($filters['artist'])) {
            $args['artist'] = sanitize_text_field($filters['artist']);
        }
        
        if (!empty($filters['age_min'])) {
            $args['age_min'] = intval($filters['age_min']);
        }
        
        if (!empty($filters['age_max'])) {
            $args['age_max'] = intval($filters['age_max']);
        }
        
        $result = $this->get_art_pieces($args);
        
        wp_send_json_success(array(
            'html' => $this->render_art_items($result['posts']),
            'has_more' => $result['has_more'],
            'total' => $result['total'],
        ));
    }
}


new ArtGalleryBlock();