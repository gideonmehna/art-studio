<?php

/**
 * Plugin Name: Art Studio
 * Plugin URI: https://elyownsoftware.com/
 * Description: A comprehensive plugin for managing children's art pieces with custom post types, taxonomies, and Gutenberg blocks for showcasing artwork.
 * Version: 1.1.3
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
define('ART_STUDIO_VERSION', '1.1.3');
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
        'taxonomies' => array('art_emotion', 'art_category'),
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
 * Register Custom Taxonomy for Art Categories (e.g. General, Pro)
 */
function register_art_category_taxonomy()
{
    $labels = array(
        'name'          => _x('Art Categories', 'Taxonomy General Name', 'art-studio'),
        'singular_name' => _x('Art Category', 'Taxonomy Singular Name', 'art-studio'),
        'menu_name'     => __('Art Categories', 'art-studio'),
        'all_items'     => __('All Art Categories', 'art-studio'),
        'new_item_name' => __('New Art Category Name', 'art-studio'),
        'add_new_item'  => __('Add New Art Category', 'art-studio'),
        'edit_item'     => __('Edit Art Category', 'art-studio'),
        'update_item'   => __('Update Art Category', 'art-studio'),
        'search_items'  => __('Search Art Categories', 'art-studio'),
        'not_found'     => __('Not Found', 'art-studio'),
        'no_terms'      => __('No art categories', 'art-studio'),
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => false,      // No public archive URLs
        'show_ui'           => true,       // Visible in admin
        'show_admin_column' => true,       // Column on Art Pieces list
        'show_in_nav_menus' => false,
        'show_tagcloud'     => false,
        'show_in_rest'      => true,       // Required for block editor SelectControl
        'rest_base'         => 'art-categories',
        'rewrite'           => false,
    );

    register_taxonomy('art_category', array('art_piece'), $args);
}
add_action('init', 'register_art_category_taxonomy', 0);


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

        // Category filter
        $selected_category = isset($_GET['art_category']) ? $_GET['art_category'] : '';
        $categories = get_terms(array(
            'taxonomy'   => 'art_category',
            'hide_empty' => false,
        ));

        if ($categories && !is_wp_error($categories)) {
            echo '<select name="art_category">';
            echo '<option value="">' . __('All Categories', 'art-studio') . '</option>';
            foreach ($categories as $category) {
                echo '<option value="' . esc_attr($category->slug) . '"' . selected($selected_category, $category->slug, false) . '>' . esc_html($category->name) . '</option>';
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

        // Category filter
        if (isset($_GET['art_category']) && $_GET['art_category'] !== '') {
            $query->set('art_category', sanitize_text_field($_GET['art_category']));
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
    register_art_category_taxonomy();
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
add_action('forminator_form_after_save_entry', 'create_artwork_post_from_forminator', 10, 3);


function create_artwork_post_from_forminator($form_id, $response, $form_fields = null)
{
    error_log('[ART STUDIO] Forminator hook fired for form ID: ' . $form_id);

    // STEP 1: Category map gate — form must be registered here to process at all.
    $form_category_map = get_option('art_studio_form_category_map', array());
    $active_form_ids   = array_keys($form_category_map);

    if (empty($active_form_ids) || !in_array($form_id, $active_form_ids)) {
        error_log('[ART STUDIO] Form ' . $form_id . ' not in category map — skipping.');
        return;
    }

    $assigned_category = !empty($form_category_map[$form_id]) ? $form_category_map[$form_id] : '';

    // STEP 2: Load the field map for this form.
    // If none is configured yet, bail — prevents malformed posts during setup.
    $all_field_maps = get_option('art_studio_field_map', array());
    $field_map      = isset($all_field_maps[$form_id]) ? $all_field_maps[$form_id] : array();

    if (empty($field_map)) {
        error_log('[ART STUDIO] No field map configured for form ' . $form_id . '. Go to Art Pieces → Form Settings to set up field mappings.');
        return;
    }

    error_log('[ART STUDIO] POST data: ' . print_r($_POST, true));
    error_log('[ART STUDIO] FILES data: ' . print_r($_FILES, true));

    // STEP 3: Build post data buckets from the field map.
    $processed          = art_studio_build_post_from_field_map($field_map, $_POST);
    $post_title         = $processed['post_title'];
    $taxonomy_terms     = $processed['taxonomy_terms'];
    $upload_field       = $processed['upload_field'];
    $notify_artist_name = $processed['notify_name'];

    // STEP 4: Require a post title at minimum.
    if (empty($post_title)) {
        error_log('[ART STUDIO] No post_title resolved from field map for form ' . $form_id . '. Check Field Mapper settings.');
        return;
    }

    // STEP 5: Insert the art_piece post.
    $post_data = array(
        'post_title'   => $post_title,
        'post_content' => $processed['post_content'],
        'post_type'    => 'art_piece',
        'post_status'  => 'pending',
        'meta_input'   => $processed['meta_input'],
    );

    $post_id = wp_insert_post($post_data);

    if (is_wp_error($post_id)) {
        error_log('[ART STUDIO] wp_insert_post failed: ' . $post_id->get_error_message());
        return;
    }

    error_log('[ART STUDIO] Created art_piece post ID: ' . $post_id);

    // STEP 6: Assign art_category from the form→category map (unchanged behavior).
    if (!empty($assigned_category)) {
        $cat_result = wp_set_object_terms($post_id, $assigned_category, 'art_category');
        if (is_wp_error($cat_result)) {
            error_log('[ART STUDIO] Failed to set art_category: ' . $cat_result->get_error_message());
        } else {
            error_log('[ART STUDIO] art_category set to: ' . $assigned_category);
        }
    }

    // STEP 7: Assign all taxonomy terms collected from the field map.
    foreach ($taxonomy_terms as $taxonomy => $terms) {
        if (empty($terms)) {
            continue;
        }
        // post_tag appends so multiple fields mapping to tags stack correctly.
        $append = ($taxonomy === 'post_tag');
        $result = wp_set_object_terms($post_id, $terms, $taxonomy, $append);
        if (is_wp_error($result)) {
            error_log('[ART STUDIO] Failed to set taxonomy "' . $taxonomy . '": ' . $result->get_error_message());
        } else {
            error_log('[ART STUDIO] Set taxonomy "' . $taxonomy . '" terms: ' . implode(', ', $terms));
        }
    }

    // STEP 8: Handle file upload / sideload (upload logic is identical; only $upload_field is now dynamic).
    $attachment_id    = false;
    $upload_error_msg = '';

    if (!empty($upload_field)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        // Fast path: media_handle_upload when PHP considers the file "uploaded".
        if (!empty($_FILES[$upload_field]) && empty($_FILES[$upload_field]['error'])) {
            error_log('[ART STUDIO] Fast path - attempting media_handle_upload for ' . $upload_field);
            $attach_try = media_handle_upload($upload_field, $post_id);
            if (!is_wp_error($attach_try)) {
                $attachment_id = $attach_try;
                error_log('[ART STUDIO] media_handle_upload succeeded, attachment ID ' . $attachment_id);
            } else {
                $upload_error_msg = $attach_try->get_error_message();
                error_log('[ART STUDIO] media_handle_upload failed: ' . $upload_error_msg);
            }
        } else {
            error_log('[ART STUDIO] No valid $_FILES entry for ' . $upload_field);
        }

        // Deterministic fallback: search Forminator upload folders for a matching file.
        if (!$attachment_id && !empty($_FILES[$upload_field]['name'])) {
            $original_basename = sanitize_file_name($_FILES[$upload_field]['name']);
            $uploads = wp_upload_dir();
            if (isset($uploads['basedir'])) {
                $forminator_base = trailingslashit($uploads['basedir']) . 'forminator';
                error_log('[ART STUDIO] Fallback - searching Forminator folders for: ' . $original_basename . ' in ' . $forminator_base);

                if (is_dir($forminator_base)) {
                    $candidates = glob($forminator_base . '/*/uploads/*' . $original_basename, GLOB_NOSORT);
                    $found = false;
                    if (!empty($candidates)) {
                        foreach ($candidates as $candidate) {
                            if (str_ends_with(basename($candidate), $original_basename)) {
                                $found = $candidate;
                                break;
                            }
                        }
                    }

                    if ($found) {
                        error_log('[ART STUDIO] Found candidate file at: ' . $found);
                        $tmp_path = wp_tempnam($found);
                        if ($tmp_path && copy($found, $tmp_path)) {
                            $file_array = array(
                                'name'     => basename($found),
                                'tmp_name' => $tmp_path,
                            );
                            $attach_id = media_handle_sideload($file_array, $post_id);
                            if (file_exists($tmp_path)) {
                                @unlink($tmp_path);
                            }
                            if (is_wp_error($attach_id)) {
                                error_log('[ART STUDIO] media_handle_sideload failed: ' . $attach_id->get_error_message());
                            } else {
                                $attachment_id = $attach_id;
                                error_log('[ART STUDIO] media_handle_sideload succeeded, attachment ID ' . $attachment_id);
                            }
                        } else {
                            error_log('[ART STUDIO] Failed to copy candidate file to temp for sideload.');
                        }
                    } else {
                        error_log('[ART STUDIO] No matching file found in Forminator folders for ' . $original_basename);
                    }
                } else {
                    error_log('[ART STUDIO] Forminator upload base not found: ' . $forminator_base);
                }
            } else {
                error_log('[ART STUDIO] wp_upload_dir did not return a basedir.');
            }
        }

        if ($attachment_id && !is_wp_error($attachment_id)) {
            if (set_post_thumbnail($post_id, $attachment_id)) {
                error_log('[ART STUDIO] Featured image set, attachment ID: ' . $attachment_id);
            } else {
                error_log('[ART STUDIO] set_post_thumbnail failed for attachment ID: ' . $attachment_id);
            }
        } else {
            error_log('[ART STUDIO] No attachment created. Last upload error: ' . ($upload_error_msg ?: 'none'));
        }
    }

    // STEP 9: Send admin notification email.
    $admin_email = 'it.ccdmp@utoronto.ca';
    $subject     = 'New Artwork Submission: ' . $post_title;
    $message     = "New artwork submission requires review:\n\n" .
                   'Title: '  . $post_title . "\n" .
                   'Artist: ' . $notify_artist_name . "\n\n" .
                   'View submission: ' . get_edit_post_link($post_id);

    wp_mail($admin_email, $subject, $message);
    error_log('[ART STUDIO] Notification email sent to ' . $admin_email);
}



// Art Gallery block
require_once ART_STUDIO_PLUGIN_PATH . 'inc/field-map-processor.php';
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


/**
 * =========================================================
 * Form → Category Mapping Settings
 * Admin page: Art Pieces → Form Settings
 * =========================================================
 */

/**
 * Register the Form Settings submenu under Art Pieces
 */
function art_studio_register_form_settings_page()
{
    add_submenu_page(
        'edit.php?post_type=art_piece',
        __('Form Settings', 'art-studio'),
        __('Form Settings', 'art-studio'),
        'manage_options',
        'art-studio-form-settings',
        'art_studio_form_settings_page'
    );
}
add_action('admin_menu', 'art_studio_register_form_settings_page');


/**
 * AJAX handler: save the form → category map
 */
function art_studio_save_form_map()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'art_studio_form_map_nonce')) {
        wp_send_json_error('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    $raw = isset($_POST['form_map']) ? json_decode(stripslashes($_POST['form_map']), true) : array();
    $map = array();

    if (is_array($raw)) {
        foreach ($raw as $entry) {
            $form_id  = absint($entry['form_id'] ?? 0);
            $category = sanitize_text_field($entry['category'] ?? '');
            if ($form_id > 0 && !empty($category)) {
                $map[$form_id] = $category;
            }
        }
    }

    update_option('art_studio_form_category_map', $map);
    wp_send_json_success(__('Mappings saved successfully.', 'art-studio'));
}
add_action('wp_ajax_art_studio_save_form_map', 'art_studio_save_form_map');


/**
 * AJAX handler: return Forminator form fields as JSON for the Field Mapper UI
 */
function art_studio_get_form_fields()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'art_studio_field_map_nonce')) {
        wp_send_json_error('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    $form_id = absint(isset($_POST['form_id']) ? $_POST['form_id'] : 0);
    if ($form_id < 1) {
        wp_send_json_error('Invalid form ID');
    }

    if (!class_exists('Forminator_API')) {
        wp_send_json_error('Forminator is not active');
    }

    // Try the public API first; fall back to raw post meta if the method signature changes.
    $fields = null;
    if (method_exists('Forminator_API', 'get_form_fields')) {
        $fields = Forminator_API::get_form_fields($form_id);
    }

    if (is_wp_error($fields) || is_null($fields)) {
        // Fallback: read raw post meta directly (Forminator stores form data in 'forminator_form_meta')
        $raw_meta = get_post_meta($form_id, 'forminator_form_meta', true);
        $raw      = !empty($raw_meta['fields']) ? $raw_meta['fields'] : array();
        if (!empty($raw) && is_array($raw)) {
            $fields = $raw;
        } else {
            wp_send_json_error('Could not read form fields. Ensure the form exists and Forminator is active.');
        }
    }

    $skip_types = array('html', 'section', 'page-break', 'captcha', 'hidden', 'divider');
    $output     = array();

    foreach ($fields as $field) {
        $parsed = art_studio_parse_forminator_field($field);
        if (empty($parsed) || in_array($parsed['type'], $skip_types, true)) {
            continue;
        }
        $output[] = $parsed;
    }

    wp_send_json_success($output);
}
add_action('wp_ajax_art_studio_get_form_fields', 'art_studio_get_form_fields');


/**
 * AJAX handler: save field mappings for a given form to the art_studio_field_map option
 */
function art_studio_save_field_map()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'art_studio_field_map_nonce')) {
        wp_send_json_error('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }

    $form_id = absint(isset($_POST['form_id']) ? $_POST['form_id'] : 0);
    if ($form_id < 1) {
        wp_send_json_error('Invalid form ID');
    }

    $raw_mappings = isset($_POST['field_map'])
        ? json_decode(stripslashes($_POST['field_map']), true)
        : array();

    $sanitized = array();

    if (is_array($raw_mappings)) {
        foreach ($raw_mappings as $entry) {
            $element_id     = sanitize_text_field(isset($entry['element_id']) ? $entry['element_id'] : '');
            $sanitized_entry = art_studio_sanitize_field_map_entry($entry);
            if ($sanitized_entry !== null) {
                $sanitized[$element_id] = $sanitized_entry;
            }
        }
    }

    $all_maps           = get_option('art_studio_field_map', array());
    $all_maps[$form_id] = $sanitized;
    update_option('art_studio_field_map', $all_maps);

    wp_send_json_success(__('Field mappings saved.', 'art-studio'));
}
add_action('wp_ajax_art_studio_save_field_map', 'art_studio_save_field_map');


/**
 * Admin page callback: render the Form → Category mapping UI
 */
function art_studio_form_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // Ensure option row exists
    if (false === get_option('art_studio_form_category_map')) {
        update_option('art_studio_form_category_map', array());
    }

    $current_map = get_option('art_studio_form_category_map', array());

    // Fetch Forminator forms
    $forminator_forms = array();
    if (class_exists('Forminator_API')) {
        $result = Forminator_API::get_forms(null, 1, 200, 'publish');
        if (is_array($result)) {
            $forminator_forms = $result;
        }
    }

    // Fetch art_category terms
    $categories = get_terms(array(
        'taxonomy'   => 'art_category',
        'hide_empty' => false,
    ));
    if (is_wp_error($categories)) {
        $categories = array();
    }

    // Build select HTML snippets to reuse in PHP and inject into JS template
    $form_options_html = '<option value="">' . esc_html__('— Select a Form —', 'art-studio') . '</option>';
    foreach ($forminator_forms as $form) {
        $form_options_html .= sprintf(
            '<option value="%d">%s (ID: %d)</option>',
            absint($form->id),
            esc_html(get_the_title($form->id)),
            absint($form->id)
        );
    }

    $cat_options_html = '<option value="">' . esc_html__('— Select a Category —', 'art-studio') . '</option>';
    foreach ($categories as $cat) {
        $cat_options_html .= sprintf(
            '<option value="%s">%s</option>',
            esc_attr($cat->slug),
            esc_html($cat->name)
        );
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Form → Category Mappings', 'art-studio'); ?></h1>
        <p><?php esc_html_e('Associate each Forminator form with an Art Category. When a form is submitted, the created art piece will automatically receive the mapped category.', 'art-studio'); ?></p>

        <?php if (empty($forminator_forms)): ?>
            <div class="notice notice-warning">
                <p><?php esc_html_e('Forminator is not active or has no published forms. Activate Forminator and create at least one form to use this feature.', 'art-studio'); ?></p>
            </div>
        <?php endif; ?>

        <?php if (empty($categories)): ?>
            <div class="notice notice-warning">
                <p>
                    <?php esc_html_e('No Art Categories found.', 'art-studio'); ?>
                    <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=art_category&post_type=art_piece')); ?>">
                        <?php esc_html_e('Create categories here.', 'art-studio'); ?>
                    </a>
                </p>
            </div>
        <?php endif; ?>

        <table id="art-studio-form-map-table" style="border-collapse:collapse;width:100%;max-width:700px;">
            <thead>
                <tr>
                    <th style="text-align:left;padding:8px 12px;border-bottom:1px solid #ddd;"><?php esc_html_e('Forminator Form', 'art-studio'); ?></th>
                    <th style="text-align:left;padding:8px 12px;border-bottom:1px solid #ddd;"><?php esc_html_e('Art Category', 'art-studio'); ?></th>
                    <th style="padding:8px 12px;border-bottom:1px solid #ddd;"></th>
                </tr>
            </thead>
            <tbody id="art-studio-form-map-rows">
                <?php foreach ($current_map as $form_id => $cat_slug): ?>
                <tr class="form-map-row">
                    <td style="padding:8px 12px;">
                        <select name="form_id" style="width:100%;">
                            <?php
                            foreach ($forminator_forms as $form) {
                                printf(
                                    '<option value="%d"%s>%s (ID: %d)</option>',
                                    absint($form->id),
                                    selected(absint($form_id), absint($form->id), false),
                                    esc_html(get_the_title($form->id)),
                                    absint($form->id)
                                );
                            }
                            ?>
                        </select>
                    </td>
                    <td style="padding:8px 12px;">
                        <select name="category" style="width:100%;">
                            <?php
                            foreach ($categories as $cat) {
                                printf(
                                    '<option value="%s"%s>%s</option>',
                                    esc_attr($cat->slug),
                                    selected($cat_slug, $cat->slug, false),
                                    esc_html($cat->name)
                                );
                            }
                            ?>
                        </select>
                    </td>
                    <td style="padding:8px 12px;">
                        <button type="button" class="button remove-row" style="color:#b32d2e;">✕ <?php esc_html_e('Remove', 'art-studio'); ?></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p style="margin-top:16px;">
            <button type="button" id="add-form-map-row" class="button">+ <?php esc_html_e('Add Mapping', 'art-studio'); ?></button>
            &nbsp;&nbsp;
            <button type="button" id="save-form-map" class="button button-primary"><?php esc_html_e('Save Mappings', 'art-studio'); ?></button>
            <span id="form-map-save-status" style="margin-left:12px;"></span>
        </p>
    </div>

    <hr style="margin:32px 0;" />

    <div class="wrap">
        <h2><?php esc_html_e('Field Mapper', 'art-studio'); ?></h2>
        <p><?php esc_html_e(
            'Select a form and click "Load Fields" — every field in that form will appear below. Map each one to where its value should go in the art piece post. You do not need to know field IDs in advance; they are read directly from Forminator.',
            'art-studio'
        ); ?></p>

        <table style="border-collapse:collapse;margin-bottom:16px;">
            <tr>
                <td style="padding:0 12px 0 0;">
                    <label for="art-studio-field-map-form-select">
                        <strong><?php esc_html_e('Form', 'art-studio'); ?></strong>
                    </label>
                </td>
                <td>
                    <select id="art-studio-field-map-form-select">
                        <option value=""><?php esc_html_e('— Select a Form —', 'art-studio'); ?></option>
                        <?php foreach ($forminator_forms as $form): ?>
                        <option value="<?php echo absint($form->id); ?>">
                            <?php echo esc_html(get_the_title($form->id)); ?> (ID: <?php echo absint($form->id); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td style="padding:0 0 0 12px;">
                    <button type="button" id="art-studio-load-fields" class="button">
                        <?php esc_html_e('Load Fields', 'art-studio'); ?>
                    </button>
                </td>
            </tr>
        </table>

        <p id="art-studio-load-status-line" style="min-height:22px;margin:8px 0 0;">
            <span id="art-studio-load-status"></span>
        </p>

        <div id="art-studio-field-map-container" style="display:none;">
            <table id="art-studio-field-map-table" style="border-collapse:collapse;width:100%;max-width:900px;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:8px 12px;border-bottom:2px solid #ddd;width:28%;"><?php esc_html_e('Field Label', 'art-studio'); ?></th>
                        <th style="text-align:left;padding:8px 12px;border-bottom:2px solid #ddd;width:18%;color:#666;"><?php esc_html_e('Field ID', 'art-studio'); ?></th>
                        <th style="text-align:left;padding:8px 12px;border-bottom:2px solid #ddd;width:22%;"><?php esc_html_e('Maps To', 'art-studio'); ?></th>
                        <th style="text-align:left;padding:8px 12px;border-bottom:2px solid #ddd;"><?php esc_html_e('Destination', 'art-studio'); ?></th>
                    </tr>
                </thead>
                <tbody id="art-studio-field-map-rows">
                    <!-- Populated by JS after "Load Fields" -->
                </tbody>
            </table>

            <p style="margin-top:16px;">
                <button type="button" id="art-studio-save-field-map" class="button button-primary">
                    <?php esc_html_e('Save Field Mappings', 'art-studio'); ?>
                </button>
                <span id="art-studio-field-map-status" style="margin-left:12px;"></span>
            </p>
        </div>
    </div>

    <script>
    (function($) {
        var config = <?php echo wp_json_encode(array(
            // Existing form-category map config
            'ajaxUrl'       => admin_url('admin-ajax.php'),
            'nonce'         => wp_create_nonce('art_studio_form_map_nonce'),
            'formOptions'   => $form_options_html,
            'catOptions'    => $cat_options_html,
            // Field mapper additions
            'fieldMapNonce' => wp_create_nonce('art_studio_field_map_nonce'),
            'existingMaps'  => get_option('art_studio_field_map', array()),
            'taxonomies'    => array(
                array('slug' => 'art_emotion',  'label' => 'Art Emotion'),
                array('slug' => 'art_category', 'label' => 'Art Category'),
                array('slug' => 'post_tag',     'label' => 'Tags'),
            ),
        )); ?>;

        // =====================================================
        // SECTION A: Form → Category map (unchanged)
        // =====================================================
        function buildNewRow() {
            return $('<tr class="form-map-row">' +
                '<td style="padding:8px 12px;"><select name="form_id" style="width:100%;">' + config.formOptions + '</select></td>' +
                '<td style="padding:8px 12px;"><select name="category" style="width:100%;">' + config.catOptions + '</select></td>' +
                '<td style="padding:8px 12px;"><button type="button" class="button remove-row" style="color:#b32d2e;">&#x2715; <?php echo esc_js(__('Remove', 'art-studio')); ?></button></td>' +
            '</tr>');
        }

        $('#add-form-map-row').on('click', function() {
            $('#art-studio-form-map-rows').append(buildNewRow());
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        $('#save-form-map').on('click', function() {
            var $status = $('#form-map-save-status');
            var rows = [];

            $('#art-studio-form-map-rows .form-map-row').each(function() {
                var formId   = $(this).find('select[name="form_id"]').val();
                var category = $(this).find('select[name="category"]').val();
                if (formId && category) {
                    rows.push({ form_id: formId, category: category });
                }
            });

            $status.text('<?php echo esc_js(__('Saving\u2026', 'art-studio')); ?>').css('color', '#666');

            $.post(config.ajaxUrl, {
                action:   'art_studio_save_form_map',
                nonce:    config.nonce,
                form_map: JSON.stringify(rows)
            }, function(response) {
                if (response.success) {
                    $status.text(response.data).css('color', 'green');
                } else {
                    $status.text(response.data || '<?php echo esc_js(__('Error saving.', 'art-studio')); ?>').css('color', 'red');
                }
            }).fail(function() {
                $status.text('<?php echo esc_js(__('Server error.', 'art-studio')); ?>').css('color', 'red');
            });
        });

        // =====================================================
        // SECTION B: Field Mapper
        // =====================================================

        var destTypeOptions =
            '<option value="ignore"><?php echo esc_js(__('Ignore', 'art-studio')); ?></option>' +
            '<option value="post_title"><?php echo esc_js(__('Post Title', 'art-studio')); ?></option>' +
            '<option value="post_content"><?php echo esc_js(__('Post Content', 'art-studio')); ?></option>' +
            '<option value="meta"><?php echo esc_js(__('Meta (custom key)', 'art-studio')); ?></option>' +
            '<option value="taxonomy"><?php echo esc_js(__('Taxonomy', 'art-studio')); ?></option>' +
            '<option value="featured_image"><?php echo esc_js(__('Featured Image', 'art-studio')); ?></option>' +
            '<option value="name_concat"><?php echo esc_js(__('Concatenated Name (first + last)', 'art-studio')); ?></option>';

        // Build taxonomy dropdown HTML
        var taxOptions = '<option value=""><?php echo esc_js(__('\u2014 Select Taxonomy \u2014', 'art-studio')); ?></option>';
        $.each(config.taxonomies, function(i, tax) {
            taxOptions += '<option value="' + escAttr(tax.slug) + '">' + tax.label + '</option>';
        });

        function escAttr(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        /**
         * Build the Destination cell content based on destination type.
         */
        function buildDestCell(destType, savedKey) {
            var $td = $('<td style="padding:8px 12px;">');
            switch (destType) {
                case 'meta':
                case 'name_concat':
                    $td.append(
                        $('<input type="text" class="dest-key-input" style="width:100%;max-width:220px;" />')
                            .attr('placeholder', '<?php echo esc_js(__('e.g. _artist_name', 'art-studio')); ?>')
                            .val(savedKey || '')
                    );
                    break;
                case 'taxonomy':
                    var $sel = $('<select class="dest-key-select" style="width:100%;max-width:220px;">' + taxOptions + '</select>');
                    if (savedKey) { $sel.val(savedKey); }
                    $td.append($sel);
                    break;
                default:
                    $td.html('<span style="color:#999;">n/a</span>');
                    break;
            }
            return $td;
        }

        /**
         * Build a single field-map table row.
         */
        function buildFieldRow(field, savedMapping) {
            var destType = savedMapping ? savedMapping.destination_type : 'ignore';
            var destKey  = savedMapping ? savedMapping.destination_key  : '';

            // Auto-suggest sensible defaults on first load
            if (!savedMapping) {
                if (field.type === 'name') {
                    destType = 'name_concat';
                    destKey  = '_' + field.element_id.replace(/-/g, '_');
                } else if (field.type === 'upload') {
                    destType = 'featured_image';
                }
            }

            // Pre-fill meta key suggestion from field ID if not already set
            if ((destType === 'meta' || destType === 'name_concat') && !destKey) {
                destKey = '_' + field.element_id.replace(/-/g, '_');
            }

            var $destTypeSel = $('<select class="dest-type-select" style="width:100%;max-width:200px;">' + destTypeOptions + '</select>').val(destType);
            var $tr = $('<tr class="field-map-row">').attr('data-element-id', field.element_id);

            $tr.append($('<td style="padding:8px 12px;">').text(field.label || field.element_id));
            $tr.append($('<td style="padding:8px 12px;font-family:monospace;font-size:12px;color:#666;">').text(field.element_id));
            $tr.append($('<td style="padding:8px 12px;">').append($destTypeSel));
            $tr.append(buildDestCell(destType, destKey));

            return $tr;
        }

        // Re-render destination cell when "Maps To" changes
        $(document).on('change', '.dest-type-select', function() {
            var $row     = $(this).closest('tr');
            var destType = $(this).val();
            var fieldId  = $row.data('element-id') || '';
            var suggest  = '_' + String(fieldId).replace(/-/g, '_');
            $row.find('td').eq(3).replaceWith(buildDestCell(destType, suggest));
        });

        // "Load Fields" button
        $('#art-studio-load-fields').on('click', function() {
            var formId = $('#art-studio-field-map-form-select').val();
            if (!formId) {
                alert('<?php echo esc_js(__('Please select a form first.', 'art-studio')); ?>');
                return;
            }

            var $btn        = $(this).prop('disabled', true).text('<?php echo esc_js(__('Loading\u2026', 'art-studio')); ?>');
            var $loadStatus = $('#art-studio-load-status').text('').css('color', '');

            $.post(config.ajaxUrl, {
                action:  'art_studio_get_form_fields',
                nonce:   config.fieldMapNonce,
                form_id: formId
            }, function(response) {
                $btn.prop('disabled', false).text('<?php echo esc_js(__('Load Fields', 'art-studio')); ?>');

                if (!response.success) {
                    $loadStatus.text(response.data || '<?php echo esc_js(__('Error loading fields.', 'art-studio')); ?>').css('color', 'red');
                    return;
                }

                var fields    = response.data;
                var savedMaps = config.existingMaps[String(formId)] || config.existingMaps[parseInt(formId)] || {};
                var $tbody    = $('#art-studio-field-map-rows').empty();

                if (!fields.length) {
                    $loadStatus.text('<?php echo esc_js(__('No mappable fields found in this form.', 'art-studio')); ?>').css('color', '#666');
                    return;
                }

                $.each(fields, function(i, field) {
                    $tbody.append(buildFieldRow(field, savedMaps[field.element_id] || null));
                });

                $('#art-studio-field-map-container').show();
                $loadStatus.text('').css('color', '');
            }).fail(function() {
                $btn.prop('disabled', false).text('<?php echo esc_js(__('Load Fields', 'art-studio')); ?>');
                $loadStatus.text('<?php echo esc_js(__('Server error. Check that WordPress AJAX is reachable.', 'art-studio')); ?>').css('color', 'red');
            });
        });

        // "Save Field Mappings" button
        $('#art-studio-save-field-map').on('click', function() {
            var formId  = $('#art-studio-field-map-form-select').val();
            var $status = $('#art-studio-field-map-status');

            if (!formId) {
                $status.text('<?php echo esc_js(__('No form selected.', 'art-studio')); ?>').css('color', 'red');
                return;
            }

            var mappings = [];
            $('#art-studio-field-map-rows .field-map-row').each(function() {
                var elementId = $(this).data('element-id');
                var destType  = $(this).find('.dest-type-select').val();
                var destKey   = '';

                var $keyInput  = $(this).find('.dest-key-input');
                var $keySelect = $(this).find('.dest-key-select');
                if ($keyInput.length)  { destKey = $keyInput.val().trim(); }
                else if ($keySelect.length) { destKey = $keySelect.val() || ''; }

                mappings.push({ element_id: elementId, destination_type: destType, destination_key: destKey });
            });

            $status.text('<?php echo esc_js(__('Saving\u2026', 'art-studio')); ?>').css('color', '#666');

            $.post(config.ajaxUrl, {
                action:    'art_studio_save_field_map',
                nonce:     config.fieldMapNonce,
                form_id:   formId,
                field_map: JSON.stringify(mappings)
            }, function(response) {
                if (response.success) {
                    // Update in-memory cache so re-loading the page pre-fills correctly
                    if (!config.existingMaps[formId]) { config.existingMaps[formId] = {}; }
                    $.each(mappings, function(i, m) {
                        config.existingMaps[formId][m.element_id] = {
                            destination_type: m.destination_type,
                            destination_key:  m.destination_key
                        };
                    });
                    $status.text(response.data).css('color', 'green');
                } else {
                    $status.text(response.data || '<?php echo esc_js(__('Error saving.', 'art-studio')); ?>').css('color', 'red');
                }
            }).fail(function() {
                $status.text('<?php echo esc_js(__('Server error.', 'art-studio')); ?>').css('color', 'red');
            });
        });

    })(jQuery);
    </script>
    <?php
}