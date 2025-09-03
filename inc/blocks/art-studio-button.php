<?php
/**
 * Custom Button Block for Art-Studio Plugin
 */


// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if plugin constants are defined
if (!defined('ART_STUDIO_PLUGIN_URL') || !defined('ART_STUDIO_VERSION')) {
    return;
}
// Register the custom button block
function art_studio_register_custom_button_block() {
    // Register block script
    wp_register_script(
        'art-studio-custom-button-block',
        ART_STUDIO_PLUGIN_URL . 'assets/js/custom-button-block.js',
        array('wp-blocks', 'wp-editor', 'wp-components', 'wp-element'),
        ART_STUDIO_VERSION
    );

    // Register block style
    wp_register_style(
        'art-studio-custom-button-style',
        ART_STUDIO_PLUGIN_URL . 'assets/css/blocks/custom-button-style.css',
        array(),
        ART_STUDIO_VERSION
    );

    // Register the block
    register_block_type('art-studio/custom-button', array(
        'editor_script' => 'art-studio-custom-button-block',
        'style' => 'art-studio-custom-button-style',
        'render_callback' => 'art_studio_render_custom_button',
        'category' => 'art-blocks',
        'attributes' => array(
            'buttonText' => array(
                'type' => 'string',
                'default' => 'Learn more'
            ),
            'buttonUrl' => array(
                'type' => 'string',
                'default' => '#'
            ),
            'showArrow' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'underline' => array(
                'type' => 'boolean',
                'default' => false
            ),
            'openInNewTab' => array(
                'type' => 'boolean',
                'default' => false
            )
        )
    ));
}
add_action('init', 'art_studio_register_custom_button_block');

// Render callback for the button
function art_studio_render_custom_button($attributes) {
    $button_text = !empty($attributes['buttonText']) ? esc_html($attributes['buttonText']) : 'Learn more';
    $button_url = !empty($attributes['buttonUrl']) ? esc_url($attributes['buttonUrl']) : '#';
    $show_arrow = isset($attributes['showArrow']) ? $attributes['showArrow'] : true;
    $open_in_new_tab = isset($attributes['openInNewTab']) ? $attributes['openInNewTab'] : false;
    
    $target = $open_in_new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
    
    $arrow_svg = '<svg class="art-studio-button-arrow" width="44" height="16" viewBox="0 0 44 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1 8H42M42 8L35 1M42 8L35 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>';
    
    ob_start();
    ?>
    <div class="art-studio-custom-button-wrapper">
        <a href="<?php echo $button_url; ?>" class="art-studio-custom-button"<?php echo $target; ?>>
            <span class="art-studio-button-text"><?php echo $button_text; ?></span>
            <?php if ($show_arrow): ?>
                <?php echo $arrow_svg; ?>
            <?php endif; ?>
        </a>
    </div>
    <?php
    return ob_get_clean();
}

// Enqueue admin styles and scripts
function art_studio_enqueue_admin_assets() {
    wp_enqueue_script('art-studio-custom-button-block');
    wp_enqueue_style('art-studio-custom-button-style');
}
add_action('enqueue_block_editor_assets', 'art_studio_enqueue_admin_assets');
?>