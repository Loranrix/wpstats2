<?php
/**
 * Maintenance pages functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Visitor_Stats_Maintenance {
    
    /**
     * Initialize maintenance
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_maintenance_menu'));
        add_action('wp_ajax_wp_visitor_stats_enable_maintenance', array($this, 'ajax_enable_maintenance'));
        add_action('wp_ajax_wp_visitor_stats_disable_maintenance', array($this, 'ajax_disable_maintenance'));
    }
    
    /**
     * Initialize maintenance (static method for backward compatibility)
     */
    public static function init() {
        // This method is kept for backward compatibility but is no longer used
        // The class is now instantiated in the main plugin class
    }
    
    /**
     * Add maintenance menu
     */
    public function add_maintenance_menu() {
        add_submenu_page(
            'wp-visitor-stats',
            __('Maintenance Pages', 'wp-visitor-stats'),
            __('Maintenance', 'wp-visitor-stats'),
            'manage_options',
            'wp-visitor-stats-maintenance',
            array($this, 'render_maintenance_page')
        );
    }
    
    /**
     * Render maintenance page
     */
    public function render_maintenance_page() {
        if (isset($_POST['wp_visitor_stats_create_maintenance'])) {
            check_admin_referer('wp_visitor_stats_maintenance');
            $this->create_maintenance_page();
        }
        
        $maintenance_pages = get_posts(array(
            'post_type' => 'page',
            'meta_key' => '_wp_visitor_stats_maintenance',
            'meta_value' => '1',
            'posts_per_page' => -1
        ));
        
        $template_path = WP_VISITOR_STATS_PLUGIN_DIR . 'templates/admin-maintenance.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<div class="wrap"><h1>' . __('Maintenance Pages', 'wp-visitor-stats') . '</h1>';
            echo '<p>' . __('Template file not found.', 'wp-visitor-stats') . '</p></div>';
        }
    }
    
    /**
     * Create maintenance page
     */
    private function create_maintenance_page() {
        $title = isset($_POST['maintenance_title']) ? sanitize_text_field($_POST['maintenance_title']) : 'Site Under Maintenance';
        $template = isset($_POST['maintenance_template']) ? sanitize_text_field($_POST['maintenance_template']) : 'default';
        
        $page_id = wp_insert_post(array(
            'post_title' => $title,
            'post_content' => self::get_maintenance_content($template),
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => 'maintenance'
        ));
        
        if ($page_id) {
            update_post_meta($page_id, '_wp_visitor_stats_maintenance', '1');
            update_post_meta($page_id, '_wp_visitor_stats_maintenance_template', $template);
            echo '<div class="notice notice-success"><p>' . __('Maintenance page created!', 'wp-visitor-stats') . '</p></div>';
        }
    }
    
    /**
     * Get maintenance content by template
     */
    private function get_maintenance_content($template) {
        $template_file = WP_VISITOR_STATS_PLUGIN_DIR . 'templates/maintenance-' . $template . '.php';
        if (!file_exists($template_file)) {
            $template = 'default';
            $template_file = WP_VISITOR_STATS_PLUGIN_DIR . 'templates/maintenance-' . $template . '.php';
        }
        ob_start();
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            echo '<!DOCTYPE html><html><head><title>Site Under Maintenance</title></head><body><h1>Site Under Maintenance</h1><p>We will be back shortly.</p></body></html>';
        }
        return ob_get_clean();
    }
    
    /**
     * AJAX enable maintenance
     */
    public function ajax_enable_maintenance() {
        check_ajax_referer('wp_visitor_stats_maintenance', 'nonce');
        $page_id = intval($_POST['page_id']);
        update_option('wp_visitor_stats_maintenance_enabled', true);
        update_option('wp_visitor_stats_maintenance_page_id', $page_id);
        wp_send_json_success();
    }
    
    /**
     * AJAX disable maintenance
     */
    public function ajax_disable_maintenance() {
        check_ajax_referer('wp_visitor_stats_maintenance', 'nonce');
        update_option('wp_visitor_stats_maintenance_enabled', false);
        wp_send_json_success();
    }
}

// Maintenance class is now instantiated in the main plugin class
// WP_Visitor_Stats_Maintenance::init();

