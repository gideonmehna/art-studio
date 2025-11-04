<?php

/**
 * Plugin Name: Art Studio
 * Plugin URI: https://elyownsoftware.com/
 * Description: A comprehensive plugin for managing children's art pieces with custom post types, taxonomies, and Gutenberg blocks for showcasing artwork.
 * Version: 1.0.0
 * Author: Gideon Mehna
 * Author URI: https://elyownsoftware.com/
 * Text Domain: art-studio
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ART_STUDIO_VERSION', '1.0.0');
define('ART_STUDIO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ART_STUDIO_PLUGIN_PATH', plugin_dir_path(__FILE__));


// Hook into WordPress’s front-end enqueue action
add_action('wp_enqueue_scripts', 'art_studio_enqueue_frontend_styles');

function art_studio_enqueue_frontend_styles()
{
    // Unique handle, path to CSS, dependencies, version, media type
    wp_enqueue_style(
        'art_studio-frontend', // Handle
        ART_STUDIO_PLUGIN_URL . 'assets/css/style.css', // File URL
        array(), // Dependencies
        ART_STUDIO_VERSION, // Version based on file time
        'all' // Media
    );
}
/**
 * Check minimum WordPress and PHP versions
 */
function art_studio_check_requirements()
{
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('Art Studio requires PHP version 7.4 or higher.', 'art-studio'));
    }

    if (version_compare(get_bloginfo('version'), '5.0', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('Art Studio requires WordPress version 5.0 or higher.', 'art-studio'));
    }
}
register_activation_hook(__FILE__, 'art_studio_check_requirements');

/**
 * Load plugin textdomain for translations
 */
function art_studio_load_textdomain()
{
    load_plugin_textdomain('art-studio', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'art_studio_load_textdomain');

/**
 * Register Custom Post Type for Art Pieces
 */
function register_art_pieces_post_type()
{
    $labels = array(
        'name' => _x('Art Pieces', 'Post Type General Name', 'art-studio'),
        'singular_name' => _x('Art Piece', 'Post Type Singular Name', 'art-studio'),
        'menu_name' => __('Art Pieces', 'art-studio'),
        'name_admin_bar' => __('Art Piece', 'art-studio'),
        'archives' => __('Art Piece Archives', 'art-studio'),
        'attributes' => __('Art Piece Attributes', 'art-studio'),
        'parent_item_colon' => __('Parent Art Piece:', 'art-studio'),
        'all_items' => __('All Art Pieces', 'art-studio'),
        'add_new_item' => __('Add New Art Piece', 'art-studio'),
        'add_new' => __('Add New', 'art-studio'),
        'new_item' => __('New Art Piece', 'art-studio'),
        'edit_item' => __('Edit Art Piece', 'art-studio'),
        'update_item' => __('Update Art Piece', 'art-studio'),
        'view_item' => __('View Art Piece', 'art-studio'),
        'view_items' => __('View Art Pieces', 'art-studio'),
        'search_items' => __('Search Art Pieces', 'art-studio'),
        'not_found' => __('Not found', 'art-studio'),
        'not_found_in_trash' => __('Not found in Trash', 'art-studio'),
        'featured_image' => __('Featured Image', 'art-studio'),
        'set_featured_image' => __('Set featured image', 'art-studio'),
        'remove_featured_image' => __('Remove featured image', 'art-studio'),
        'use_featured_image' => __('Use as featured image', 'art-studio'),
        'insert_into_item' => __('Insert into art piece', 'art-studio'),
        'uploaded_to_this_item' => __('Uploaded to this art piece', 'art-studio'),
        'items_list' => __('Art pieces list', 'art-studio'),
        'items_list_navigation' => __('Art pieces list navigation', 'art-studio'),
        'filter_items_list' => __('Filter art pieces list', 'art-studio'),
    );

    $args = array(
        'label' => __('Art Piece', 'art-studio'),
        'description' => __('Art pieces created by children', 'art-studio'),
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'taxonomies' => array('art_emotion'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-art',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true, // Essential for block editor and FSE
        'rest_base' => 'art-pieces',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'rewrite' => array('slug' => 'art-pieces'),
    );

    register_post_type('art_piece', $args);
}
add_action('init', 'register_art_pieces_post_type', 5);

/**
 * Register Custom Taxonomy for Art Emotions
 */
function register_art_emotion_taxonomy()
{
    $labels = array(
        'name' => _x('Art Emotions', 'Taxonomy General Name', 'art-studio'),
        'singular_name' => _x('Art Emotion', 'Taxonomy Singular Name', 'art-studio'),
        'menu_name' => __('Art Emotions', 'art-studio'),
        'all_items' => __('All Art Emotions', 'art-studio'),
        'parent_item' => __('Parent Art Emotion', 'art-studio'),
        'parent_item_colon' => __('Parent Art Emotion:', 'art-studio'),
        'new_item_name' => __('New Art Emotion Name', 'art-studio'),
        'add_new_item' => __('Add New Art Emotion', 'art-studio'),
        'edit_item' => __('Edit Art Emotion', 'art-studio'),
        'update_item' => __('Update Art Emotion', 'art-studio'),
        'view_item' => __('View Art Emotion', 'art-studio'),
        'separate_items_with_commas' => __('Separate art emotions with commas', 'art-studio'),
        'add_or_remove_items' => __('Add or remove art emotions', 'art-studio'),
        'choose_from_most_used' => __('Choose from the most used', 'art-studio'),
        'popular_items' => __('Popular Art Emotions', 'art-studio'),
        'search_items' => __('Search Art Emotions', 'art-studio'),
        'not_found' => __('Not Found', 'art-studio'),
        'no_terms' => __('No art emotions', 'art-studio'),
        'items_list' => __('Art emotions list', 'art-studio'),
        'items_list_navigation' => __('Art emotions list navigation', 'art-studio'),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => true, // Like categories
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud' => true,
        'show_in_rest' => true, // Essential for block editor and FSE
        'rest_base' => 'art-emotions',
        'rest_controller_class' => 'WP_REST_Terms_Controller',
        'rewrite' => array('slug' => 'art-emotion'),
    );

    register_taxonomy('art_emotion', array('art_piece'), $args);
}
add_action('init', 'register_art_emotion_taxonomy', 0);


/**
 * Add Custom Meta Boxes for Art Pieces
 */
function add_art_piece_meta_boxes()
{
    add_meta_box(
        'art_piece_details',
        __('Art Piece Details', 'art-studio'),
        'art_piece_meta_box_callback',
        'art_piece',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_art_piece_meta_boxes');


/**
 * Meta box callback function
 */
function art_piece_meta_box_callback($post)
{
    wp_nonce_field('art_piece_meta_box', 'art_piece_meta_box_nonce');
    $artist_name = get_post_meta($post->ID, '_artist_name', true);
    $artist_age = get_post_meta($post->ID, '_artist_age', true);
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th><label for="artist_name">' . __('Artist Name', 'art-studio') . '</label></th>';
    echo '<td><input type="text" id="artist_name" name="artist_name" value="' . esc_attr($artist_name) . '" size="30" /></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th><label for="artist_age">' . __('Artist Age', 'art-studio') . '</label></th>';
    echo '<td><input type="number" id="artist_age" name="artist_age" value="' . esc_attr($artist_age) . '" min="1" max="18" /></td>';
    echo '</tr>';
    echo '</table>';
}

/**
 * Save meta box data
 */
function save_art_piece_meta_box($post_id)
{
    // Check if nonce is valid
    if (!isset($_POST['art_piece_meta_box_nonce']) || !wp_verify_nonce($_POST['art_piece_meta_box_nonce'], 'art_piece_meta_box')) {
        return;
    }

    // Check if user has permission to edit
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check if not an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Save artist name
    if (isset($_POST['artist_name'])) {
        update_post_meta($post_id, '_artist_name', sanitize_text_field($_POST['artist_name']));
    }

    // Save artist age
    if (isset($_POST['artist_age'])) {
        update_post_meta($post_id, '_artist_age', absint($_POST['artist_age']));
    }
}
add_action('save_post', 'save_art_piece_meta_box');

/**
 * Add custom columns to the art pieces admin list
 */
function add_art_piece_columns($columns)
{
    $columns['artist_name'] = __('Artist Name', 'art-studio');
    $columns['artist_age'] = __('Artist Age', 'art-studio');
    $columns['art_emotion'] = __('Art Emotion', 'art-studio');
    return $columns;
}
add_filter('manage_art_piece_posts_columns', 'add_art_piece_columns');

/**
 * Display custom column content
 */
function display_art_piece_columns($column, $post_id)
{
    switch ($column) {
        case 'artist_name':
            echo esc_html(get_post_meta($post_id, '_artist_name', true));
            break;
        case 'artist_age':
            echo esc_html(get_post_meta($post_id, '_artist_age', true));
            break;
        case 'art_emotion':
            $emotions = get_the_terms($post_id, 'art_emotion');
            if ($emotions && !is_wp_error($emotions)) {
                $emotion_names = array();
                foreach ($emotions as $emotion) {
                    $emotion_names[] = $emotion->name;
                }
                echo implode(', ', $emotion_names);
            }
            break;
    }
}
add_action('manage_art_piece_posts_custom_column', 'display_art_piece_columns', 10, 2);

/**
 * Make custom columns sortable
 */
function make_art_piece_columns_sortable($columns)
{
    $columns['artist_name'] = 'artist_name';
    $columns['artist_age'] = 'artist_age';
    return $columns;
}
add_filter('manage_edit-art_piece_sortable_columns', 'make_art_piece_columns_sortable');

/**
 * Handle sorting for custom columns
 */
function art_piece_column_orderby($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $orderby = $query->get('orderby');

    if ('artist_name' === $orderby) {
        $query->set('meta_key', '_artist_name');
        $query->set('orderby', 'meta_value');
    } elseif ('artist_age' === $orderby) {
        $query->set('meta_key', '_artist_age');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'art_piece_column_orderby');

/**
 * Add filter dropdowns to admin list
 */
function add_art_piece_admin_filters()
{
    global $typenow;

    if ($typenow === 'art_piece') {
        // Age filter
        $selected_age = isset($_GET['artist_age']) ? $_GET['artist_age'] : '';
        echo '<select name="artist_age">';
        echo '<option value="">' . __('All Ages', 'art-studio') . '</option>';
        for ($i = 1; $i <= 18; $i++) {
            echo '<option value="' . $i . '"' . selected($selected_age, $i, false) . '>' . $i . ' years old</option>';
        }
        echo '</select>';

        // Emotion filter
        $selected_emotion = isset($_GET['art_emotion']) ? $_GET['art_emotion'] : '';
        $emotions = get_terms(array(
            'taxonomy' => 'art_emotion',
            'hide_empty' => false,
        ));

        if ($emotions) {
            echo '<select name="art_emotion">';
            echo '<option value="">' . __('All Emotions', 'art-studio') . '</option>';
            foreach ($emotions as $emotion) {
                echo '<option value="' . $emotion->slug . '"' . selected($selected_emotion, $emotion->slug, false) . '>' . $emotion->name . '</option>';
            }
            echo '</select>';
        }
    }
}
add_action('restrict_manage_posts', 'add_art_piece_admin_filters');

/**
 * Handle admin filtering
 */
function handle_art_piece_admin_filtering($query)
{
    global $pagenow;

    if (is_admin() && $pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'art_piece') {
        // Age filter
        if (isset($_GET['artist_age']) && $_GET['artist_age'] !== '') {
            $query->set('meta_key', '_artist_age');
            $query->set('meta_value', $_GET['artist_age']);
        }

        // Emotion filter
        if (isset($_GET['art_emotion']) && $_GET['art_emotion'] !== '') {
            $query->set('art_emotion', $_GET['art_emotion']);
        }
    }
}
add_action('pre_get_posts', 'handle_art_piece_admin_filtering');

/**
 * Plugin activation hook
 */
function art_studio_activate()
{
    register_art_pieces_post_type();
    register_art_emotion_taxonomy();
    // Initialize custom menu assets
    // art_studio_menu_activate();
    flush_rewrite_rules();

}
register_activation_hook(__FILE__, 'art_studio_activate');

/**
 * Plugin deactivation hook
 */
function art_studio_deactivate()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'art_studio_deactivate');


/**
 * Register featured image meta field for REST API and Gutenberg
 */
function register_art_emotion_featured_image_meta()
{

    register_term_meta('art_emotion', 'featured_image', array(
        'type' => 'integer',
        'description' => 'Featured image attachment ID for art emotion',
        'single' => true,
        'show_in_rest' => true, // This makes it available in Gutenberg
        'sanitize_callback' => 'absint',
        'auth_callback' => function () {
            return current_user_can('edit_posts');
        }
    ));
}
add_action('init', 'register_art_emotion_featured_image_meta');

/**
 * Add featured image field to add new art emotion form
 */
function add_art_emotion_featured_image_field()
{
    ?>
                        <div class="form-field">
                            <label for="art_emotion_featured_image"><?php _e('Featured Image', 'art-studio'); ?></label>
                            <div id="art_emotion_featured_image_container">
                                <input type="hidden" id="art_emotion_featured_image" name="featured_image" value="" />
                                <div id="art_emotion_featured_image_preview" style="margin-bottom: 10px;"></div>
                                <button type="button" class="button" id="art_emotion_featured_image_button">
                                    <?php _e('Select Featured Image', 'art-studio'); ?>
                                </button>
                                <button type="button" class="button" id="art_emotion_remove_featured_image_button" style="display: none;">
                                    <?php _e('Remove Featured Image', 'art-studio'); ?>
                                </button>
                            </div>
                            <p class="description"><?php _e('Choose a featured image for this art emotion. This will also be available in the Gutenberg editor.', 'art-studio'); ?></p>
                        </div>
                        <?php
}
add_action('art_emotion_add_form_fields', 'add_art_emotion_featured_image_field');

/**
 * Add featured image field to edit art emotion form
 */
function edit_art_emotion_featured_image_field($term)
{
    $featured_image_id = get_term_meta($term->term_id, 'featured_image', true);
    $featured_image_url = '';
    if ($featured_image_id) {
        $featured_image_url = wp_get_attachment_image_url($featured_image_id, 'thumbnail');
    }
    ?>
                        <tr class="form-field">
                            <th scope="row" valign="top">
                                <label for="art_emotion_featured_image"><?php _e('Featured Image', 'art-studio'); ?></label>
                            </th>
                            <td>
                                <div id="art_emotion_featured_image_container">
                                    <input type="hidden" id="art_emotion_featured_image" name="featured_image" value="<?php echo esc_attr($featured_image_id); ?>" />
                                    <div id="art_emotion_featured_image_preview" style="margin-bottom: 10px;">
                                        <?php if ($featured_image_url): ?>
                                                                <img src="<?php echo esc_url($featured_image_url); ?>" style="max-width: 150px; height: auto;" />
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="button" id="art_emotion_featured_image_button">
                                        <?php _e($featured_image_id ? 'Change Featured Image' : 'Select Featured Image', 'art-studio'); ?>
                                    </button>
                                    <button type="button" class="button" id="art_emotion_remove_featured_image_button" <?php echo $featured_image_id ? '' : 'style="display: none;"'; ?>>
                                        <?php _e('Remove Featured Image', 'art-studio'); ?>
                                    </button>
                                </div>
                                <p class="description"><?php _e('Choose a featured image for this art emotion. This will also be available in the Gutenberg editor.', 'art-studio'); ?></p>
                            </td>
                        </tr>
                        <?php
}
add_action('art_emotion_edit_form_fields', 'edit_art_emotion_featured_image_field');

/**
 * Save featured image when creating new art emotion
 */
function save_art_emotion_featured_image_create($term_id)
{
    if (isset($_POST['featured_image']) && !empty($_POST['featured_image'])) {
        add_term_meta($term_id, 'featured_image', absint($_POST['featured_image']));
    }
}
add_action('created_art_emotion', 'save_art_emotion_featured_image_create');

/**
 * Save featured image when editing art emotion
 */
function save_art_emotion_featured_image_edit($term_id)
{
    if (isset($_POST['featured_image'])) {
        if (!empty($_POST['featured_image'])) {
            update_term_meta($term_id, 'featured_image', absint($_POST['featured_image']));
        } else {
            delete_term_meta($term_id, 'featured_image');
        }
    }
}
add_action('edited_art_emotion', 'save_art_emotion_featured_image_edit');

/**
 * Add featured image column to art emotion admin list
 */
function add_art_emotion_featured_image_column($columns)
{
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['featured_image'] = __('Featured Image', 'art-studio');
    $new_columns['name'] = $columns['name'];
    $new_columns['description'] = $columns['description'];
    $new_columns['slug'] = $columns['slug'];
    $new_columns['posts'] = $columns['posts'];

    return $new_columns;
}
add_filter('manage_edit-art_emotion_columns', 'add_art_emotion_featured_image_column');

/**
 * Display featured image in art emotion admin list
 */
function display_art_emotion_featured_image_column($content, $column_name, $term_id)
{
    if ($column_name === 'featured_image') {
        $featured_image_id = get_term_meta($term_id, 'featured_image', true);
        if ($featured_image_id) {
            $featured_image_url = wp_get_attachment_image_url($featured_image_id, 'thumbnail');
            if ($featured_image_url) {
                return '<img src="' . esc_url($featured_image_url) . '" style="width: 50px; height: 50px; object-fit: cover;" />';
            }
        }
        return '—';
    }
    return $content;
}
add_filter('manage_art_emotion_custom_column', 'display_art_emotion_featured_image_column', 10, 3);

/**
 * Enqueue admin scripts for media uploader
 */
function enqueue_art_emotion_admin_scripts($hook)
{
    if ($hook === 'edit-tags.php' || $hook === 'term.php') {
        $screen = get_current_screen();
        if ($screen && $screen->taxonomy === 'art_emotion') {
            wp_enqueue_media();
            wp_enqueue_script(
                'art-emotion-featured-image',
                ART_STUDIO_PLUGIN_URL . 'assets/js/art-emotion-featured-image.js',
                array('jquery'),
                '1.0.0',
                true
            );
        }
    }
}
add_action('admin_enqueue_scripts', 'enqueue_art_emotion_admin_scripts');

/**
 * Enqueue Gutenberg editor scripts and styles
 */
function enqueue_art_emotion_gutenberg_assets()
{
    wp_enqueue_script(
        'art-emotion-gutenberg',
        ART_STUDIO_PLUGIN_URL . 'assets/js/art-emotion-gutenberg.js',
        array('wp-blocks', 'wp-editor', 'wp-components', 'wp-element', 'wp-data'),
        '1.0.0',
        true
    );
    wp_enqueue_style(
        'art-emotion-gutenberg-style',
        ART_STUDIO_PLUGIN_URL . 'assets/css/blocks/art-emotion-gutenberg.css',
        array(),
        '1.0.0'
    );
}
add_action('enqueue_block_editor_assets', 'enqueue_art_emotion_gutenberg_assets');

/**
 * Helper function to get art emotion featured image
 */
function get_art_emotion_featured_image($term_id, $size = 'full')
{
    $featured_image_id = get_term_meta($term_id, 'featured_image', true);

    if ($featured_image_id) {
        return wp_get_attachment_image($featured_image_id, $size);
    }

    return false;
}

/**
 * Helper function to get art emotion featured image URL
 */
function get_art_emotion_featured_image_url($term_id, $size = 'full')
{
    $featured_image_id = get_term_meta($term_id, 'featured_image', true);

    if ($featured_image_id) {
        return wp_get_attachment_image_url($featured_image_id, $size);
    }

    return false;
}

/**
 * Helper function to get art emotion featured image ID
 */
function get_art_emotion_featured_image_id($term_id)
{
    return get_term_meta($term_id, 'featured_image', true);
}

/**
 * Add REST API endpoint for easier Gutenberg integration
 */
function register_art_emotion_rest_fields()
{
    register_rest_field('art_emotion', 'featured_image_data', array(
        'get_callback' => function ($term) {
            $featured_image_id = get_term_meta($term['id'], 'featured_image', true);
            if ($featured_image_id) {
                return array(
                    'id' => $featured_image_id,
                    'url' => wp_get_attachment_image_url($featured_image_id, 'full'),
                    'thumbnail' => wp_get_attachment_image_url($featured_image_id, 'thumbnail'),
                    'medium' => wp_get_attachment_image_url($featured_image_id, 'medium'),
                    'alt' => get_post_meta($featured_image_id, '_wp_attachment_image_alt', true)
                );
            }
            return null;
        },
        'schema' => array(
            'description' => __('Featured image data for art emotion', 'art-studio'),
            'type' => 'object'
        )
    ));
}
add_action('rest_api_init', 'register_art_emotion_rest_fields');

/**
 * Add custom link field to art emotion form
 */
function add_art_emotion_custom_link_field()
{
    ?>
                        <div class="form-field">
                            <label for="custom_link"><?php _e('Custom Link', 'art-studio'); ?></label>
                            <input type="url" name="custom_link" id="custom_link" value="" />
                            <p class="description">
                                <?php _e('Enter a custom URL for this emotion (e.g., /gallery#happy). Leave empty to use default archive page.', 'art-studio'); ?>
                            </p>
                        </div>
                        <?php
}
add_action('art_emotion_add_form_fields', 'add_art_emotion_custom_link_field');

/**
 * Add custom link field to edit form
 */
function edit_art_emotion_custom_link_field($term)
{
    $custom_link = get_term_meta($term->term_id, 'custom_link', true);
    ?>
                        <tr class="form-field">
                            <th scope="row">
                                <label for="custom_link"><?php _e('Custom Link', 'art-studio'); ?></label>
                            </th>
                            <td>
                                <input type="url" name="custom_link" id="custom_link" value="<?php echo esc_attr($custom_link); ?>" />
                                <p class="description">
                                    <?php _e('Enter a custom URL for this emotion (e.g., /gallery#happy)', 'art-studio'); ?>
                                </p>
                            </td>
                        </tr>
                        <?php
}
add_action('art_emotion_edit_form_fields', 'edit_art_emotion_custom_link_field');

/**
 * Save custom link field
 */
function save_art_emotion_custom_link($term_id)
{
    if (isset($_POST['custom_link'])) {
        $custom_link = sanitize_url($_POST['custom_link']);
        update_term_meta($term_id, 'custom_link', $custom_link);
    }
}
add_action('created_art_emotion', 'save_art_emotion_custom_link');
add_action('edited_art_emotion', 'save_art_emotion_custom_link');

/**
 * Add custom link column to admin list
 */
function add_art_emotion_custom_link_column($columns)
{
    $columns['custom_link'] = __('Custom Link', 'art-studio');
    return $columns;
}
add_filter('manage_edit-art_emotion_columns', 'add_art_emotion_custom_link_column');

/**
 * Display custom link in admin column
 */
function display_art_emotion_custom_link_column($content, $column_name, $term_id)
{
    if ($column_name === 'custom_link') {
        $custom_link = get_term_meta($term_id, 'custom_link', true);
        return $custom_link ? esc_url($custom_link) : '—';
    }
    return $content;
}
add_filter('manage_art_emotion_custom_column', 'display_art_emotion_custom_link_column', 10, 3);

/**
 * Modify term link if custom link exists
 */
function modify_art_emotion_link($url, $term, $taxonomy)
{
    if ($taxonomy === 'art_emotion') {
        $custom_link = get_term_meta($term->term_id, 'custom_link', true);
        if (!empty($custom_link)) {
            return $custom_link;
        }
    }
    return $url;
}
add_filter('term_link', 'modify_art_emotion_link', 10, 3);

/**
 * Add block category for custom blocks
 */
function add_art_blocks_category($categories)
{
    return array_merge(
        array(
            array(
                'slug' => 'art-blocks',
                'title' => __('Art Blocks', 'art-studio'),
                'icon' => 'art',
            ),
        ),
        $categories
    );
}
add_filter('block_categories_all', 'add_art_blocks_category');

/**
 * Handle Forminator form submission to create an art piece post
 */
// define('ART_SUBMISSION_FORM_ID', 259); 
// Your Forminator form ID

// Define an array of form IDs instead of individual constants
define('ART_SUBMISSION_FORM_IDS', [259, 358]);

add_action('forminator_form_after_save_entry', 'create_artwork_post_from_forminator', 10, 3);


function create_artwork_post_from_forminator($form_id, $response, $form_fields = null)
{
    error_log('[ART STUDIO Forminator Plugin] Fired');
    error_log('Art Studio: Form ID - ' . $form_id);
    error_log('Art Studio: Response - ' . print_r($response, true));
    error_log('Art Studio: Form Fields - ' . print_r($form_fields, true));
    // if ($form_id != 259) return; 
    if (!in_array($form_id, ART_SUBMISSION_FORM_IDS)) {
        return;
    }
    // Only run for the correct form

    error_log('Art Studio: Raw POST data: ' . print_r($_POST, true));
    error_log('Art Studio: Raw FILES data: ' . print_r($_FILES, true));

    // Map form fields to meaningful keys
    $data = [
        'artist_name' => sanitize_text_field($_POST['name-1'] ?? ''),
        'artist_age' => absint($_POST['number-1'] ?? 0),
        'guardian_name' => sprintf('%s %s', sanitize_text_field($_POST['name-2-first-name'] ?? ''), sanitize_text_field($_POST['name-2-last-name'] ?? '')),
        'guardian_email' => sanitize_email($_POST['email-2'] ?? ''),
        'title_of_artwork' => sanitize_text_field($_POST['text-1'] ?? ''),
        'description' => sanitize_textarea_field($_POST['textarea-1'] ?? ''),
        'artwork_emotion' => isset($_POST['checkbox-1']) ? array_map('sanitize_text_field', (array) $_POST['checkbox-1']) : [],
        'consent' => 'yes' // Assuming consent is implied by submission
    ];

    // Validate required fields
    foreach (['artist_name', 'artist_age', 'guardian_name', 'guardian_email', 'title_of_artwork', 'description'] as $field) {
        if (empty($data[$field])) {
            error_log("Art Studio: Missing required field - {$field}");
            return;
        }
    }

    // Create the post
    $post_data = [
        'post_title' => $data['title_of_artwork'],
        'post_content' => $data['description'],
        'post_type' => 'art_piece',
        'post_status' => 'pending',
        'meta_input' => [
            '_artist_name' => $data['artist_name'],
            '_artist_age' => $data['artist_age'],
            '_guardian_name' => $data['guardian_name'],
            '_guardian_email' => $data['guardian_email']
        ]
    ];

    $post_id = wp_insert_post($post_data);

    if (is_wp_error($post_id)) {
        error_log("Art Studio: Failed to create post - " . $post_id->get_error_message());
        return;
    }

    error_log("Art Studio: Successfully created post with ID - {$post_id}");

    // Set taxonomy terms and tags
    if (!empty($data['artwork_emotion'])) {
        $tags = array();

        // Set emotion taxonomy terms and add them as tags
        $term_result = wp_set_object_terms($post_id, $data['artwork_emotion'], 'art_emotion');
        if (is_wp_error($term_result)) {
            error_log("Art Studio: Failed to set emotion terms - " . $term_result->get_error_message());
        }

        // Add selected taxonomy terms as tags
        foreach ($data['artwork_emotion'] as $emotion) {
            $tags[] = $emotion;
        }

        // Add additional emotions from text-3 as tags
        if (!empty($_POST['text-3'])) {
            $custom_emotions = array_map('trim', explode(',', sanitize_text_field($_POST['text-3'])));
            if (!empty($custom_emotions)) {
                $tags = array_merge($tags, $custom_emotions);
            }
        }

        // Set all tags
        if (!empty($tags)) {
            $tag_result = wp_set_post_tags($post_id, $tags, true);
            if (is_wp_error($tag_result)) {
                error_log("Art Studio: Failed to set tags - " . $tag_result->get_error_message());
            }
        }
    }

    // ...existing code...
    error_log('Art Studio: Raw FILES data: ' . print_r($_FILES, true));

    // Polished upload handler: try media_handle_upload, then deterministic Forminator-folder sideload.
    $attachment_id = false;
    $upload_field = 'upload-1';

    // Load media helpers once
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    // Fast path: media_handle_upload when PHP considers the file "uploaded"
    if (!empty($_FILES[$upload_field]) && empty($_FILES[$upload_field]['error'])) {
        error_log('Art Studio: Fast path - attempting media_handle_upload for ' . $upload_field);
        $attach_try = media_handle_upload($upload_field, $post_id);
        if (!is_wp_error($attach_try)) {
            $attachment_id = $attach_try;
            error_log("Art Studio: media_handle_upload succeeded, attachment ID {$attachment_id}");
        } else {
            $upload_error_msg = $attach_try->get_error_message();
            error_log('Art Studio: media_handle_upload failed - ' . $upload_error_msg);
        }
    } else {
        error_log('Art Studio: No valid $_FILES entry for ' . $upload_field);
    }

    // Deterministic fallback: search the Forminator upload folders for a file that ends with the original basename
    if (!$attachment_id && !empty($_FILES[$upload_field]['name'])) {
        $original_basename = sanitize_file_name($_FILES[$upload_field]['name']);
        $uploads = wp_upload_dir();
        if (isset($uploads['basedir'])) {
            $forminator_base = trailingslashit($uploads['basedir']) . 'forminator';
            error_log('Art Studio: Fallback - searching Forminator folders for: ' . $original_basename . ' in ' . $forminator_base);

            if (is_dir($forminator_base)) {
                // List all candidates under */uploads/
                $candidates = glob($forminator_base . '/*/uploads/*' . $original_basename, GLOB_NOSORT);
                $found = false;
                if (!empty($candidates)) {
                    // pick the first candidate whose basename ends exactly with original basename (avoid partial matches)
                    foreach ($candidates as $candidate) {
                        if (str_ends_with(basename($candidate), $original_basename)) {
                            $found = $candidate;
                            break;
                        }
                    }
                }

                if ($found) {
                    error_log('Art Studio: Found candidate file at: ' . $found);
                    // create a temp copy and sideload it
                    $tmp_path = wp_tempnam($found);
                    if ($tmp_path && copy($found, $tmp_path)) {
                        $file_array = array(
                            'name' => basename($found),
                            'tmp_name' => $tmp_path,
                        );
                        $attach_id = media_handle_sideload($file_array, $post_id);
                        // remove temp copy
                        if (file_exists($tmp_path)) {
                            @unlink($tmp_path);
                        }
                        if (is_wp_error($attach_id)) {
                            error_log('Art Studio: media_handle_sideload failed for candidate - ' . $attach_id->get_error_message());
                        } else {
                            $attachment_id = $attach_id;
                            error_log("Art Studio: media_handle_sideload succeeded from Forminator folder, attachment ID {$attachment_id}");
                        }
                    } else {
                        error_log('Art Studio: Failed to copy candidate file to temp for sideload.');
                    }
                } else {
                    error_log('Art Studio: No exact matching file found in Forminator folders for ' . $original_basename);
                }
            } else {
                error_log('Art Studio: Forminator upload base not found: ' . $forminator_base);
            }
        } else {
            error_log('Art Studio: wp_upload_dir did not return a basedir.');
        }
    }

    // Finalize: set featured image if attachment created
    if ($attachment_id && !is_wp_error($attachment_id)) {
        if (set_post_thumbnail($post_id, $attachment_id)) {
            error_log("Art Studio: Successfully set featured image with ID - {$attachment_id}");
        } else {
            error_log("Art Studio: set_post_thumbnail failed for attachment ID - {$attachment_id}");
        }
    } else {
        error_log('Art Studio: No attachment created; skipping featured image. Last upload error: ' . ($upload_error_msg ?? 'none'));
    }


    // Send notification
    // $admin_email = get_option('admin_email');
    $admin_email = 'it.ccdmp@utoronto.ca';
    $subject = "New Artwork Submission: {$data['title_of_artwork']}";
    $message = "New artwork submission requires review:\n\n" .
        "Title: {$data['title_of_artwork']}\n" .
        "Artist: {$data['artist_name']} (Age: {$data['artist_age']})\n" .
        "Guardian: {$data['guardian_name']}\n\n" .
        "View submission: " . get_edit_post_link($post_id);

    wp_mail($admin_email, $subject, $message);
}



// Art Gallery block
require_once ART_STUDIO_PLUGIN_PATH . 'inc/blocks/art-gallery/init.php';

// Art Showcase block
require_once ART_STUDIO_PLUGIN_PATH . 'inc/blocks/art-showcase.php';

// Art Emotions Block
require_once ART_STUDIO_PLUGIN_PATH . 'inc/blocks/art-emotions.php';

// Art Studio Button 
require_once ART_STUDIO_PLUGIN_PATH . 'inc/blocks/art-studio-button.php';

// Enhanced Media Text
require_once ART_STUDIO_PLUGIN_PATH . 'inc/blocks/enhanced-media-text.php';

// Creative Art Emotions Block
require_once ART_STUDIO_PLUGIN_PATH . 'inc/blocks/creative-art-emotions.php';

// Include custom menu
require_once ART_STUDIO_PLUGIN_PATH . 'inc/templates/custom-menu.php';

// Initialize custom menu
function init_art_studio_custom_menu()
{
    error_log('Art Studio: Initializing custom menu');
    // Register nav menus for classic themes
    if (!wp_is_block_theme()) {
        register_nav_menus(array(
            'primary' => __('Primary Menu', 'art-studio'),
            'header-menu' => __('Header Menu', 'art-studio')
        ));
    }
    global $art_studio_custom_menu;
    $art_studio_custom_menu = ArtStudioCustomMenu::get_instance();
    error_log('Art Studio: Custom menu initialized');
}
add_action('after_setup_theme', 'init_art_studio_custom_menu', 5);