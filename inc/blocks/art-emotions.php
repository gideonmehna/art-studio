<?php



/**
 * Register Art Emotions Display Block
 */
function register_art_emotions_block() {
    wp_register_script(
        'art-emotions-block',
        ART_STUDIO_PLUGIN_URL . 'assets/js/art-emotions-block.js',
        array('wp-blocks', 'wp-editor', 'wp-components', 'wp-element', 'wp-data'),
        ART_STUDIO_VERSION,
        true
    );

    wp_register_style(
        'art-emotions-block-style',
        ART_STUDIO_PLUGIN_URL . 'assets/css/blocks/art-emotions-block.css',
        array(),
        ART_STUDIO_VERSION
    );

    wp_register_style(
        'art-emotions-block-editor-style',
        ART_STUDIO_PLUGIN_URL . 'assets/css/blocks/art-emotions-block-editor.css',
        array('wp-edit-blocks'),
        ART_STUDIO_VERSION
    );

    register_block_type('custom/art-emotions', array(
        'editor_script' => 'art-emotions-block',
        'style' => 'art-emotions-block-style',
        'editor_style' => 'art-emotions-block-editor-style',
        'category' => 'art-blocks',
        'render_callback' => 'render_art_emotions_block',
        'attributes' => array(
            'columns' => array(
                'type' => 'number',
                'default' => 3,
            ),
            'showTitle' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'imageSize' => array(
                'type' => 'string',
                'default' => 'medium',
            ),
            'orderBy' => array(
                'type' => 'string',
                'default' => 'name',
            ),
            'order' => array(
                'type' => 'string',
                'default' => 'ASC',
            ),
            'showCount' => array(
                'type' => 'boolean',
                'default' => false,
            ),
        ),
    ));
}
add_action('init', 'register_art_emotions_block');


/**
 * Render the Art Emotions Block
 */

function render_art_emotions_block($attributes) {
    $columns = isset($attributes['columns']) ? $attributes['columns'] : 3;
    $show_title = isset($attributes['showTitle']) ? $attributes['showTitle'] : true;
    $image_size = isset($attributes['imageSize']) ? $attributes['imageSize'] : 'medium';
    $order_by = isset($attributes['orderBy']) ? $attributes['orderBy'] : 'name';
    $order = isset($attributes['order']) ? $attributes['order'] : 'ASC';
    $show_count = isset($attributes['showCount']) ? $attributes['showCount'] : false;

    // Get all art emotions
    $emotions = get_terms(array(
        'taxonomy' => 'art_emotion',
        'hide_empty' => false,
        'orderby' => $order_by,
        'order' => $order,
    ));

    if (empty($emotions) || is_wp_error($emotions)) {
        return '<p>' . __('No art emotions found.', 'art-studio') . '</p>';
    }

    $output = '<div class="art-emotions-grid" style="--columns: ' . esc_attr($columns) . ';">';
    
    foreach ($emotions as $emotion) {
        $featured_image_id = get_term_meta($emotion->term_id, 'featured_image', true);
        $emotion_link = get_term_link($emotion); // Use the new function
        $post_count = $emotion->count;
        
        $output .= '<div class="art-emotion-item">';
        $output .= '<a href="' . esc_url($emotion_link) . '" class="art-emotion-link">';
        
        // Featured image
        if ($featured_image_id) {
            $image_url = wp_get_attachment_image_url($featured_image_id, $image_size);
            $image_alt = get_post_meta($featured_image_id, '_wp_attachment_image_alt', true);
            if (!$image_alt) {
                $image_alt = $emotion->name;
            }

            $output .= '<div class="art-emotion-image">';
            $output .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" />';
            $output .= '<div class="image-caption">' . esc_html($emotion->name) . '</div>';
            $output .= '</div>';
        } else {
            // Fallback placeholder
            $output .= '<div class="art-emotion-image art-emotion-placeholder">';
            $output .= '<span class="emotion-initial">' . esc_html(substr($emotion->name, 0, 1)) . '</span>';
            $output .= '</div>';
        }
        
        // Title and count overlay
        if ($show_title || $show_count) {
            $output .= '<div class="art-emotion-overlay">';
            if ($show_title) {
                $output .= '<h3 class="art-emotion-title">' . esc_html($emotion->name) . '</h3>';
            }
            if ($show_count) {
                $count_text = sprintf(
                    _n('%d piece', '%d pieces', $post_count, 'art-studio'),
                    $post_count
                );
                $output .= '<span class="art-emotion-count">' . esc_html($count_text) . '</span>';
            }
            $output .= '</div>';
        }
        
        $output .= '</a>';
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    return $output;
}


/**
 * Enqueue block assets
 */
function enqueue_art_emotions_block_assets() {
    if (has_block('custom/art-emotions')) {
        wp_enqueue_style('art-emotions-block-style');
    }
}


add_action('wp_enqueue_scripts', 'enqueue_art_emotions_block_assets');