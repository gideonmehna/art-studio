<?php
/**
 * Enhanced Media Text Block
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
// Check if plugin constants are defined
if (!defined('ART_STUDIO_PLUGIN_URL') || !defined('ART_STUDIO_VERSION')) {
    return;
}
class Enhanced_Media_Text_Block {
    
    public function __construct() {
        add_action('init', array($this, 'register_block'));
    }
    
    public function register_block() {
        // Register the block
        register_block_type('custom/enhanced-media-text', array(
            'editor_script' => 'enhanced-media-text-editor',
            'editor_style' => 'enhanced-media-text-editor-style',
            'category' => 'art-blocks',
            'style' => 'enhanced-media-text-style',
            'render_callback' => array($this, 'render_block'),
            'attributes' => array(
                'mediaId' => array(
                    'type' => 'number',
                    'default' => 0,
                ),
                'mediaUrl' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'mediaAlt' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'thirdImageId' => array(
                    'type' => 'number',
                    'default' => 0,
                ),
                'thirdImageUrl' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'thirdImageAlt' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'thirdImagePosition' => array(
                    'type' => 'string',
                    'default' => 'top',
                ),
                'mediaPosition' => array(
                    'type' => 'string',
                    'default' => 'left',
                ),
                'verticalAlignment' => array(
                    'type' => 'string',
                    'default' => 'center',
                ),
            ),
        ));
        
        // Enqueue editor assets
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));
        
        // Enqueue frontend assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }
    
    public function enqueue_editor_assets() {
        wp_enqueue_script(
            'enhanced-media-text-editor',
            ART_STUDIO_PLUGIN_URL . 'assets/js/enhanced-media-text.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            ART_STUDIO_VERSION
        );
        
        wp_enqueue_style(
            'enhanced-media-text-editor-style',
            ART_STUDIO_PLUGIN_URL . 'assets/css/blocks/enhanced-media-text-editor.css',
            array('wp-edit-blocks'),
            ART_STUDIO_VERSION
        );
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'enhanced-media-text-style',
            ART_STUDIO_PLUGIN_URL . 'assets/css/blocks/enhanced-media-text.css',
            array(),
            ART_STUDIO_VERSION
        );
    }
    
    public function render_block($attributes, $content) {
        $media_id = isset($attributes['mediaId']) ? $attributes['mediaId'] : 0;
        $media_url = isset($attributes['mediaUrl']) ? esc_url($attributes['mediaUrl']) : '';
        $media_alt = isset($attributes['mediaAlt']) ? esc_attr($attributes['mediaAlt']) : '';
        
        $third_image_id = isset($attributes['thirdImageId']) ? $attributes['thirdImageId'] : 0;
        $third_image_url = isset($attributes['thirdImageUrl']) ? esc_url($attributes['thirdImageUrl']) : '';
        $third_image_alt = isset($attributes['thirdImageAlt']) ? esc_attr($attributes['thirdImageAlt']) : '';
        $third_image_position = isset($attributes['thirdImagePosition']) ? $attributes['thirdImagePosition'] : 'top';
        
        $media_position = isset($attributes['mediaPosition']) ? $attributes['mediaPosition'] : 'left';
        $vertical_alignment = isset($attributes['verticalAlignment']) ? $attributes['verticalAlignment'] : 'center';
        
        $classes = array('wp-block-custom-enhanced-media-text');
        $classes[] = 'has-media-on-the-' . $media_position;
        $classes[] = 'is-vertically-aligned-' . $vertical_alignment;
        $classes[] = 'third-image-' . $third_image_position;
        
        if (!empty($third_image_url)) {
            $classes[] = 'has-third-image';
        }
        
        $class_string = implode(' ', $classes);
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr($class_string); ?>">
            <?php if ($media_position === 'left'): ?>
                <div class="wp-block-custom-enhanced-media-text__media">
                    <?php if (!empty($media_url)): ?>
                        <img src="<?php echo $media_url; ?>" alt="<?php echo $media_alt; ?>" />
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="wp-block-custom-enhanced-media-text__content">
                <?php if (!empty($third_image_url) && $third_image_position === 'top'): ?>
                    <div class="wp-block-custom-enhanced-media-text__third-image third-image-top">
                        <img src="<?php echo $third_image_url; ?>" alt="<?php echo $third_image_alt; ?>" />
                    </div>
                <?php endif; ?>
                
                <div class="wp-block-custom-enhanced-media-text__text">
                    <?php echo $content; ?>
                </div>
                
                <?php if (!empty($third_image_url) && $third_image_position === 'bottom'): ?>
                    <div class="wp-block-custom-enhanced-media-text__third-image third-image-bottom">
                        <img src="<?php echo $third_image_url; ?>" alt="<?php echo $third_image_alt; ?>" />
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($third_image_url) && $third_image_position === 'middle'): ?>
                    <div class="wp-block-custom-enhanced-media-text__third-image third-image-middle">
                        <img src="<?php echo $third_image_url; ?>" alt="<?php echo $third_image_alt; ?>" />
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($media_position === 'right'): ?>
                <div class="wp-block-custom-enhanced-media-text__media">
                    <?php if (!empty($media_url)): ?>
                        <img src="<?php echo $media_url; ?>" alt="<?php echo $media_alt; ?>" />
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the block
new Enhanced_Media_Text_Block();