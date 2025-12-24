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
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_maintenance_menu'));
        add_action('wp_ajax_wp_visitor_stats_enable_maintenance', array(__CLASS__, 'ajax_enable_maintenance'));
        add_action('wp_ajax_wp_visitor_stats_disable_maintenance', array(__CLASS__, 'ajax_disable_maintenance'));
    }
    
    /**
     * Add maintenance menu
     */
    public static function add_maintenance_menu() {
        add_submenu_page(
            'wp-visitor-stats',
            __('Maintenance Pages', 'wp-visitor-stats'),
            __('Maintenance', 'wp-visitor-stats'),
            'manage_options',
            'wp-visitor-stats-maintenance',
            array(__CLASS__, 'render_maintenance_page')
        );
    }
    
    /**
     * Render maintenance page
     */
    public static function render_maintenance_page() {
        if (isset($_POST['wp_visitor_stats_create_maintenance'])) {
            check_admin_referer('wp_visitor_stats_maintenance');
            self::create_maintenance_page();
        }
        
        $maintenance_pages = get_posts(array(
            'post_type' => 'page',
            'meta_key' => '_wp_visitor_stats_maintenance',
            'meta_value' => '1',
            'posts_per_page' => -1
        ));
        
        include WP_VISITOR_STATS_PLUGIN_DIR . 'templates/admin-maintenance.php';
    }
    
    /**
     * Create maintenance page
     */
    private static function create_maintenance_page() {
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
    private static function get_maintenance_content($template) {
        ob_start();
        include WP_VISITOR_STATS_PLUGIN_DIR . 'templates/maintenance-' . $template . '.php';
        return ob_get_clean();
    }
    
    /**
     * AJAX enable maintenance
     */
    public static function ajax_enable_maintenance() {
        check_ajax_referer('wp_visitor_stats_maintenance', 'nonce');
        $page_id = intval($_POST['page_id']);
        update_option('wp_visitor_stats_maintenance_enabled', true);
        update_option('wp_visitor_stats_maintenance_page_id', $page_id);
        wp_send_json_success();
    }
    
    /**
     * AJAX disable maintenance
     */
    public static function ajax_disable_maintenance() {
        check_ajax_referer('wp_visitor_stats_maintenance', 'nonce');
        update_option('wp_visitor_stats_maintenance_enabled', false);
        wp_send_json_success();
    }
}

WP_Visitor_Stats_Maintenance::init();

