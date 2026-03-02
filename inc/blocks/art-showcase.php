<?php
/**
 * Art Showcase Block
 * Horizontal scrolling gallery for front page
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if plugin constants are defined
if (!defined('ART_STUDIO_PLUGIN_URL') || !defined('ART_STUDIO_VERSION')) {
    return;
}

/**
 * Register the Art Showcase block
 */
function art_studio_register_showcase_block()
{
    wp_register_script(
        'art-showcase-block-editor',
        ART_STUDIO_PLUGIN_URL . 'assets/js/art-showcase-block.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-api-fetch'),
        ART_STUDIO_VERSION
    );

    wp_register_style(
        'art-showcase-block-editor',
        ART_STUDIO_PLUGIN_URL . 'assets/css/blocks/art-showcase-block-editor.css',
        array(),
        ART_STUDIO_VERSION
    );

    wp_register_style(
        'art-showcase-block-frontend',
        ART_STUDIO_PLUGIN_URL . 'assets/css/blocks/art-showcase-block.css',
        array(),
        ART_STUDIO_VERSION
    );

    register_block_type('custom/art-showcase', array(
        'editor_script' => 'art-showcase-block-editor',
        'editor_style' => 'art-showcase-block-editor',
        'style' => 'art-showcase-block-frontend',
        'render_callback' => 'art_studio_render_showcase_block',
        'category' => 'art-blocks',
        'attributes' => array(
            'numberOfItems' => array(
                'type' => 'number',
                'default' => 8
            ),
            'showTitle' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'title' => array(
                'type' => 'string',
                'default' => __('Featured Artwork', 'art-studio')
            ),
            'artCategory' => array(
                'type'    => 'string',
                'default' => ''
            ),
        )
    ));
}
add_action('init', 'art_studio_register_showcase_block');

/**
 * Render the Art Showcase block
 */
function art_studio_render_showcase_block($attributes)
{
    $number_of_items = isset($attributes['numberOfItems']) ? intval($attributes['numberOfItems']) : 8;
    $show_title = isset($attributes['showTitle']) ? $attributes['showTitle'] : true;
    $title = isset($attributes['title']) ? $attributes['title'] : __('Featured Artwork', 'art-studio');
    $art_category = isset($attributes['artCategory']) ? sanitize_text_field($attributes['artCategory']) : '';

    // Build query — optionally scope to a specific art_category
    $query_args = array(
        'post_type'      => 'art_piece',
        'posts_per_page' => $number_of_items,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    if (!empty($art_category)) {
        $query_args['tax_query'] = array(
            array(
                'taxonomy' => 'art_category',
                'field'    => 'slug',
                'terms'    => $art_category,
            ),
        );
    }

    // Get art pieces
    $art_pieces = get_posts($query_args);

    if (empty($art_pieces)) {
        return '<div class="art-showcase-empty">' . __('No artwork found.', 'art-studio') . '</div>';
    }

    ob_start();
    ?>
                <div class="art-showcase-container">
                    <?php if ($show_title): ?>
                                    <h2 class="art-showcase-title"><?php echo esc_html($title); ?></h2>
                    <?php endif; ?>
        
                    <div class="art-showcase-wrapper" tabindex="0">
                        <div class="art-showcase-scroll" id="art-showcase-scroll">
                            <div class="art-showcase-grid">
                                <?php foreach ($art_pieces as $art_piece): ?>
                                                <?php
                                                $artist_name = get_post_meta($art_piece->ID, '_artist_name', true) ?: __('Unknown Artist', 'art-studio');
                                                $artist_age = get_post_meta($art_piece->ID, '_artist_age', true) ?: '';
                                                $featured_image = get_the_post_thumbnail_url($art_piece->ID, 'medium');
                                                ?>
                                                <div class="art-showcase-item">
                                                    <div class="art-showcase-image">
                                                        <?php if ($featured_image): ?>
                                                                        <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($art_piece->post_title); ?>" loading="lazy">
                                                        <?php else: ?>
                                                                        <div class="art-showcase-placeholder"></div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="art-showcase-info">
                                                        <h3 class="art-showcase-artwork-title"><?php echo esc_html($art_piece->post_title); ?></h3>
                                                        <p class="art-showcase-artist"><?php echo esc_html($artist_name); ?><?php if ($artist_age): ?>, <?php echo __('Age', 'art-studio') . ' ' . esc_html($artist_age); ?><?php endif; ?></p>
                                                    </div>
                            
                                                    <!-- Add Modal Structure -->
                                                    <div class="art-modal" id="modal-<?php echo esc_attr($art_piece->ID); ?>">
                                                        <div class="art-modal-content">
                                                            <button class="art-showcase-modal-close">&times;</button>
                                                            <div class="modal-grid">
                                                                <div class="modal-media">
                                                                    <?php if ($featured_image): ?>
                                                                                    <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($art_piece->post_title); ?>">
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="modal-content">
                                                                    <h2><?php echo esc_html($art_piece->post_title); ?></h2>
                                                                    <p class="modal-artist"><?php echo esc_html($artist_name); ?>, Age <?php echo esc_html($artist_age); ?></p>
                                                                    <div class="modal-description"><?php echo apply_filters('the_content', $art_piece->post_content); ?></div>
                                            
                                                                    <?php
                                                                    $tags = get_the_tags($art_piece->ID);
                                                                    if ($tags): ?>
                                                                                    <div class="modal-tags">
                                                                                        <span class="tags-label">Tags:</span> 
                                                                                        <?php echo implode(', ', array_map(function ($tag) {
                                                                                            return esc_html($tag->name);
                                                                                        }, $tags)); ?>
                                                                                    </div>
                                                                    <?php endif; ?>

                                                                    <?php
                                                                    $emotions = get_the_terms($art_piece->ID, 'art_emotion');
                                                                    if ($emotions): ?>
                                                                                    <div class="modal-emotions">
                                                                                        <?php foreach ($emotions as $emotion):
                                                                                            $featured_image_id = get_term_meta($emotion->term_id, 'featured_image', true);
                                                                                            if ($featured_image_id):
                                                                                                $image_url = wp_get_attachment_image_url($featured_image_id, 'thumbnail');
                                                                                                ?>
                                                                                                                        <div class="emotion-thumbnail">
                                                                                                                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($emotion->name); ?>">
                                                                                                                            <span class="emotion-name"><?php echo esc_html($emotion->name); ?></span>
                                                                                                                        </div>
                                                                                                        <?php endif; ?>
                                                                                        <?php endforeach; ?>
                                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="art-showcase-nav-row" aria-hidden="false">
                            <button class="art-showcase-nav art-showcase-nav-left disabled" aria-label="<?php echo esc_attr(__('Previous artwork', 'art-studio')); ?>">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </button>

                            <button class="art-showcase-nav art-showcase-nav-right" aria-label="<?php echo esc_attr(__('Next artwork', 'art-studio')); ?>">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </button>
                        </div>

                    </div>
                </div>
                <?php
                return ob_get_clean();
}

/**
 * Enqueue frontend scripts
 */
function art_studio_enqueue_showcase_scripts()
{
    if (has_block('custom/art-showcase')) {
        wp_enqueue_script(
            'art-showcase-frontend',
            ART_STUDIO_PLUGIN_URL . 'assets/js/art-showcase-frontend.js',
            array('jquery'),
            ART_STUDIO_VERSION,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'art_studio_enqueue_showcase_scripts');

