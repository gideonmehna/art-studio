<?php
/**
 * Plugin Name: Art Studio Custom Menu
 * Description: Custom navigation menu with frontend editing capabilities for Art Studio
 * Version: 1.0.0
 * Author: Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ART_STUDIO_MENU_VERSION', '1.0.0');
define('ART_STUDIO_MENU_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ART_STUDIO_MENU_PLUGIN_URL', plugin_dir_url(__FILE__));

class ArtStudioCustomMenu {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_head', array($this, 'add_menu_to_head'));
        add_action('wp_ajax_save_menu_items', array($this, 'save_menu_items'));
        add_action('wp_ajax_nopriv_save_menu_items', array($this, 'save_menu_items'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_shortcode('art_studio_menu', array($this, 'display_menu_shortcode'));
        
        // Hook to display menu automatically (you can customize this)
        add_action('wp_body_open', array($this, 'display_menu'));
    }
    
    public function init() {
        // Initialize default menu items if they don't exist
        if (!get_option('art_studio_menu_items')) {
            $default_items = array(
                array('label' => 'Home', 'url' => home_url('/'), 'is_home' => true),
                array('label' => 'Gallery', 'url' => home_url('/gallery'), 'is_home' => false),
                array('label' => 'Emotions', 'url' => home_url('/emotions'), 'is_home' => false),
                array('label' => 'Submissions', 'url' => home_url('/submissions'), 'is_home' => false),
                array('label' => 'Back to Main Page', 'url' => home_url('/'), 'is_home' => false)
            );
            update_option('art_studio_menu_items', $default_items);
        }
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('art-studio-menu-style', ART_STUDIO_MENU_PLUGIN_URL . 'assets/css/menu.css', array(), ART_STUDIO_MENU_VERSION);
        wp_enqueue_script('art-studio-menu-script', ART_STUDIO_MENU_PLUGIN_URL . 'assets/js/menu.js', array('jquery'), ART_STUDIO_MENU_VERSION, true);
        
        // Localize script for AJAX
        wp_localize_script('art-studio-menu-script', 'artStudioMenu', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('art_studio_menu_nonce'),
            'is_admin' => current_user_can('manage_options')
        ));
    }
    
    public function add_menu_to_head() {
        // Add the menu trigger button to head/header area
        echo '<div id="art-studio-menu-container">';
        $this->display_menu();
        echo '</div>';
    }
    
    public function display_menu() {
        $menu_items = get_option('art_studio_menu_items', array());
        $is_admin = current_user_can('manage_options');
        
        ?>
        <div id="art-studio-custom-menu">
            <!-- Menu Trigger Button -->
            <div id="primary-menu-trigger" class="toggle-menu-modified">
                <span class="actAsDiv menu-line" id="line1"></span>
                <span class="actAsDiv menu-line" id="line2"></span>
                <span class="actAsDiv menu-line" id="line3"></span>
            </div>
            
            <!-- Menu Content -->
            <nav id="art-studio-navigation" class="art-studio-main-navigation">
                <ul id="art-studio-primary-menu" class="art-studio-menu">
                    <?php if (!empty($menu_items)): ?>
                        <?php foreach ($menu_items as $index => $item): ?>
                            <li class="menu-item" data-index="<?php echo $index; ?>">
                                <?php if ($item['is_home']): ?>
                                    <a href="<?php echo esc_url($item['url']); ?>">
                                        <svg class="menu-home-icon" width="30" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3 9.5L12 2L21 9.5V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V9.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M9 21V12H15V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
                                <?php endif; ?>
                                
                                <?php if ($is_admin): ?>
                                    <div class="menu-item-controls">
                                        <button class="edit-menu-item" data-index="<?php echo $index; ?>">Edit</button>
                                        <button class="delete-menu-item" data-index="<?php echo $index; ?>">Delete</button>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <?php if (!$item['is_home'] && $index < count($menu_items) - 1): ?>
                                <span class="menu-spacer"></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                
                <?php if ($is_admin): ?>
                    <div class="menu-admin-controls">
                        <button id="add-menu-item">Add New Item</button>
                        <button id="save-menu-order">Save Order</button>
                    </div>
                <?php endif; ?>
            </nav>
            
            <?php if ($is_admin): ?>
                <!-- Edit Menu Item Modal -->
                <div id="menu-item-modal" class="menu-modal" style="display: none;">
                    <div class="menu-modal-content">
                        <span class="menu-modal-close">&times;</span>
                        <h3>Edit Menu Item</h3>
                        <form id="menu-item-form">
                            <input type="hidden" id="edit-item-index" value="">
                            <div class="form-group">
                                <label for="item-label">Label:</label>
                                <input type="text" id="item-label" name="label" required>
                            </div>
                            <div class="form-group">
                                <label for="item-url">URL:</label>
                                <input type="url" id="item-url" name="url" required>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="item-is-home" name="is_home"> 
                                    This is the home icon (will show icon instead of text)
                                </label>
                            </div>
                            <div class="form-actions">
                                <button type="submit">Save</button>
                                <button type="button" class="cancel-edit">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function display_menu_shortcode($atts) {
        ob_start();
        $this->display_menu();
        return ob_get_clean();
    }
    
    public function save_menu_items() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'art_studio_menu_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $menu_items = json_decode(stripslashes($_POST['menu_items']), true);
        
        if (is_array($menu_items)) {
            update_option('art_studio_menu_items', $menu_items);
            wp_send_json_success('Menu items saved successfully');
        } else {
            wp_send_json_error('Invalid menu data');
        }
    }
    
    public function add_admin_menu() {
        add_options_page(
            'Art Studio Menu Settings',
            'Art Studio Menu',
            'manage_options',
            'art-studio-menu',
            array($this, 'admin_page')
        );
    }
    
    public function admin_page() {
        $menu_items = get_option('art_studio_menu_items', array());
        ?>
        <div class="wrap">
            <h1>Art Studio Menu Settings</h1>
            <p>You can edit menu items directly on the frontend when logged in as an administrator, or use the form below:</p>
            
            <div id="menu-items-admin">
                <h3>Current Menu Items</h3>
                <div id="admin-menu-items">
                    <?php foreach ($menu_items as $index => $item): ?>
                        <div class="admin-menu-item" data-index="<?php echo $index; ?>">
                            <input type="text" name="label[]" value="<?php echo esc_attr($item['label']); ?>" placeholder="Label">
                            <input type="url" name="url[]" value="<?php echo esc_url($item['url']); ?>" placeholder="URL">
                            <label>
                                <input type="checkbox" name="is_home[]" <?php checked($item['is_home']); ?>> Home Icon
                            </label>
                            <button type="button" class="remove-item">Remove</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-admin-item">Add Item</button>
                <button type="button" id="save-admin-menu">Save Menu</button>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#add-admin-item').click(function() {
                var newItem = `
                    <div class="admin-menu-item">
                        <input type="text" name="label[]" placeholder="Label">
                        <input type="url" name="url[]" placeholder="URL">
                        <label>
                            <input type="checkbox" name="is_home[]"> Home Icon
                        </label>
                        <button type="button" class="remove-item">Remove</button>
                    </div>
                `;
                $('#admin-menu-items').append(newItem);
            });
            
            $(document).on('click', '.remove-item', function() {
                $(this).parent().remove();
            });
            
            $('#save-admin-menu').click(function() {
                var menuItems = [];
                $('.admin-menu-item').each(function() {
                    var item = {
                        label: $(this).find('input[name="label[]"]').val(),
                        url: $(this).find('input[name="url[]"]').val(),
                        is_home: $(this).find('input[name="is_home[]"]').is(':checked')
                    };
                    if (item.label && item.url) {
                        menuItems.push(item);
                    }
                });
                
                $.post(ajaxurl, {
                    action: 'save_menu_items',
                    menu_items: JSON.stringify(menuItems),
                    nonce: '<?php echo wp_create_nonce('art_studio_menu_nonce'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Menu saved successfully!');
                        location.reload();
                    } else {
                        alert('Error saving menu: ' + response.data);
                    }
                });
            });
        });
        </script>
        
        <style>
        .admin-menu-item {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .admin-menu-item input[type="text"],
        .admin-menu-item input[type="url"] {
            width: 200px;
            margin-right: 10px;
        }
        .remove-item {
            background: #dc3232;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }
        #add-admin-item, #save-admin-menu {
            background: #0073aa;
            color: white;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
            border-radius: 3px;
            margin-right: 10px;
        }
        </style>
        <?php
    }
}

// Initialize the plugin
new ArtStudioCustomMenu();

// Create plugin folder structure on activation
register_activation_hook(__FILE__, 'art_studio_menu_activate');

function art_studio_menu_activate() {
    // Create assets directory if it doesn't exist
    $upload_dir = wp_upload_dir();
    $plugin_dir = ART_STUDIO_MENU_PLUGIN_DIR . 'assets/';
    
    if (!file_exists($plugin_dir)) {
        wp_mkdir_p($plugin_dir);
    }
}
?>