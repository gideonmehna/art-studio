<?php

/**
 * Register Creative Art Emotions Display Block
 */
function register_creative_art_emotions_block() {
    wp_register_script(
        'creative-art-emotions-block',
        ART_STUDIO_PLUGIN_URL . 'assets/js/creative-art-emotions-block.js',
        array('wp-blocks', 'wp-editor', 'wp-components', 'wp-element', 'wp-data'),
        ART_STUDIO_VERSION,
        true
    );

    wp_register_style(
        'creative-art-emotions-block-style',
        ART_STUDIO_PLUGIN_URL . 'assets/css/blocks/creative-art-emotions-block.css',
        array(),
        ART_STUDIO_VERSION
    );

    wp_register_style(
        'creative-art-emotions-block-editor-style',
        ART_STUDIO_PLUGIN_URL . 'assets/css/blocks/creative-art-emotions-block-editor.css',
        array('wp-edit-blocks'),
        ART_STUDIO_VERSION
    );

    register_block_type('custom/creative-art-emotions', array(
        'editor_script' => 'creative-art-emotions-block',
        'style' => 'creative-art-emotions-block-style',
        'editor_style' => 'creative-art-emotions-block-editor-style',
        'category' => 'art-blocks',
        'render_callback' => 'render_creative_art_emotions_block',
        'attributes' => array(
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
            'animationStyle' => array(
                'type' => 'string',
                'default' => 'fade-up',
            ),
        ),
    ));
}
add_action('init', 'register_creative_art_emotions_block');

// /**
//  * Get custom link for emotion or fallback to archive
//  */
// function get_term_link($emotion) {
//     $custom_link = get_term_meta($emotion->term_id, 'custom_emotion_link', true);
    
//     if (!empty($custom_link)) {
//         return esc_url($custom_link);
//     }
    
//     return get_term_link($emotion);
// }

/**
 * Render the Creative Art Emotions Block
 */
function render_creative_art_emotions_block($attributes) {
    $show_title = isset($attributes['showTitle']) ? $attributes['showTitle'] : true;
    $image_size = isset($attributes['imageSize']) ? $attributes['imageSize'] : 'medium';
    $order_by = isset($attributes['orderBy']) ? $attributes['orderBy'] : 'name';
    $order = isset($attributes['order']) ? $attributes['order'] : 'ASC';
    $show_count = isset($attributes['showCount']) ? $attributes['showCount'] : false;
    $animation_style = isset($attributes['animationStyle']) ? $attributes['animationStyle'] : 'fade-up';

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

    // Predefined positions and sizes for creative layout
    $creative_positions = array(
        array('position' => 'top-left', 'size' => 'large', 'delay' => '0.1s'),
        array('position' => 'top-center', 'size' => 'medium', 'delay' => '0.3s'),
        array('position' => 'top-right', 'size' => 'large', 'delay' => '0.5s'),
        array('position' => 'bottom-left', 'size' => 'medium', 'delay' => '0.7s'),
        array('position' => 'bottom-right', 'size' => 'small', 'delay' => '0.9s'),
    );

    // Randomize sizes while keeping positions
    $sizes = array('small', 'medium', 'large');
    foreach ($creative_positions as &$position) {
        $position['size'] = $sizes[array_rand($sizes)];
    }

    $output = '<div class="creative-art-emotions-container" data-animation="' . esc_attr($animation_style) . '">';
    
    foreach ($emotions as $index => $emotion) {
        $position_data = isset($creative_positions[$index]) ? $creative_positions[$index] : array(
            'position' => 'center', 
            'size' => $sizes[array_rand($sizes)], 
            'delay' => ($index * 0.2) . 's'
        );
        
        $emotion_link = get_term_link($emotion);
        $featured_image_id = get_term_meta($emotion->term_id, 'featured_image', true);
        $post_count = $emotion->count;
        
        $output .= '<div class="creative-emotion-item creative-emotion-' . esc_attr($position_data['position']) . ' creative-emotion-' . esc_attr($position_data['size']) . '" style="animation-delay: ' . esc_attr($position_data['delay']) . ';" data-emotion="' . esc_attr($emotion->slug) . '">';
        $output .= '<a href="' . esc_url($emotion_link) . '" class="creative-emotion-link">';
        
        // Featured image
        if ($featured_image_id) {
            $image_url = wp_get_attachment_image_url($featured_image_id, $image_size);
            $image_alt = get_post_meta($featured_image_id, '_wp_attachment_image_alt', true);
            if (!$image_alt) {
                $image_alt = $emotion->name;
            }

            $output .= '<div class="creative-emotion-image">';
            $output .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" />';
            $output .= '<div class="creative-emotion-caption">' . esc_html($emotion->name) . '</div>';
            $output .= '</div>';
        } else {
            // Fallback placeholder
            $output .= '<div class="creative-emotion-image creative-emotion-placeholder">';
            $output .= '<span class="emotion-initial">' . esc_html(substr($emotion->name, 0, 1)) . '</span>';
            $output .= '<div class="creative-emotion-caption">' . esc_html($emotion->name) . '</div>';
            $output .= '</div>';
        }
        
        // Optional overlay with count
        if ($show_count) {
            $count_text = sprintf(
                _n('%d piece', '%d pieces', $post_count, 'art-studio'),
                $post_count
            );
            $output .= '<div class="creative-emotion-overlay">';
            $output .= '<span class="creative-emotion-count">' . esc_html($count_text) . '</span>';
            $output .= '</div>';
        }
        
        $output .= '</a>';
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    return $output;
}



// /**
//  * Add custom fields to art_emotion taxonomy
//  */
// function add_emotion_custom_link_field($taxonomy) {
//     ? >
//     <div class="form-field">
//         <label for="custom_emotion_link"><?php _e('Custom Link', 'art-studio'); ? ></label>
//         <input type="url" name="custom_emotion_link" id="custom_emotion_link" value="" />
//         <p class="description"><?php _e('Enter a custom URL (e.g., /emotions-gallery#joy). Leave empty to use default archive page.', 'art-studio'); ? ></p>
//     </div>
//     < ?php
// }
// add_action('art_emotion_add_form_fields', 'add_emotion_custom_link_field');

// /**
//  * Add custom fields to art_emotion taxonomy edit form
//  */
// function edit_emotion_custom_link_field($term, $taxonomy) {
//     $custom_link = get_term_meta($term->term_id, 'custom_emotion_link', true);
//     ? >
//     <tr class="form-field">
//         <th scope="row" valign="top">
//             <label for="custom_emotion_link"><?php _e('Custom Link', 'art-studio'); ? ></label>
//         </th>
//         <td>
//             <input type="url" name="custom_emotion_link" id="custom_emotion_link" value="<?php echo esc_attr($custom_link); ? >" />
//             <p class="description"><?php _e('Enter a custom URL (e.g., /emotions-gallery#joy). Leave empty to use default archive page.', 'art-studio'); ? ></p>
//         </td>
//     </tr>
//     <?php
// }
// add_action('art_emotion_edit_form_fields', 'edit_emotion_custom_link_field', 10, 2);

// /**
//  * Save custom link field
//  */
// function save_emotion_custom_link_field($term_id, $tt_id) {
//     if (isset($_POST['custom_emotion_link'])) {
//         $custom_link = sanitize_url($_POST['custom_emotion_link']);
//         update_term_meta($term_id, 'custom_emotion_link', $custom_link);
//     }
// }
// add_action('edited_art_emotion', 'save_emotion_custom_link_field', 10, 2);
// add_action('create_art_emotion', 'save_emotion_custom_link_field', 10, 2);

/**
 * Enqueue creative block assets
 */
function enqueue_creative_art_emotions_block_assets() {
    if (has_block('custom/creative-art-emotions')) {
        wp_enqueue_style('creative-art-emotions-block-style');
    }
}
add_action('wp_enqueue_scripts', 'enqueue_creative_art_emotions_block_assets');

// /**
//  * Add custom link column to admin term list
//  */
// function add_custom_link_column($columns) {
//     $columns['custom_link'] = __('Custom Link', 'art-studio');
//     return $columns;
// }
// add_filter('manage_edit-art_emotion_columns', 'add_custom_link_column');

// /**
//  * Display custom link in admin column
//  */
// function display_custom_link_column($content, $column_name, $term_id) {
//     if ($column_name === 'custom_link') {
//         $custom_link = get_term_meta($term_id, 'custom_emotion_link', true);
//         if (!empty($custom_link)) {
//             return '<a href="' . esc_url($custom_link) . '" target="_blank">' . esc_html($custom_link) . '</a>';
//         } else {
//             return '<span style="color: #999;">' . __('Default archive', 'art-studio') . '</span>';
//         }
//     }
//     return $content;
// }
// add_filter('manage_art_emotion_custom_column', 'display_custom_link_column', 10, 3);

?>