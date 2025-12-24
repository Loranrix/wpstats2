<?php
/**
 * Display visitor statistics in header
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Visitor_Stats_Display {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        $this->init();
    }
    
    /**
     * Initialize display
     */
    private function init() {
        $enabled = get_option('wp_visitor_stats_enabled', true);
        
        if (!$enabled) {
            return;
        }
        
        // Check if user can see stats
        if (!$this->db->can_see_stats()) {
            return;
        }
        
        // Add display to header based on position
        $position = get_option('wp_visitor_stats_position', 'header_right');
        
        switch ($position) {
            case 'header_left':
                add_action('wp_head', array($this, 'output_css'), 1);
                add_filter('wp_nav_menu_items', array($this, 'add_to_menu_left'), 10, 2);
                break;
            case 'header_right':
                add_action('wp_head', array($this, 'output_css'), 1);
                add_filter('wp_nav_menu_items', array($this, 'add_to_menu_right'), 10, 2);
                break;
            case 'header_center':
                add_action('wp_head', array($this, 'output_css'), 1);
                add_action('wp_body_open', array($this, 'output_stats_center'));
                break;
            case 'before_content':
                add_action('wp_head', array($this, 'output_css'), 1);
                add_action('the_content', array($this, 'output_stats_before_content'), 1);
                break;
            default:
                add_action('wp_head', array($this, 'output_css'), 1);
                add_action('wp_footer', array($this, 'output_stats_footer'));
        }
        
        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'wp-visitor-stats',
            WP_VISITOR_STATS_PLUGIN_URL . 'assets/js/visitor-stats.js',
            array('jquery'),
            WP_VISITOR_STATS_VERSION,
            true
        );
        
        wp_localize_script('wp-visitor-stats', 'wpVisitorStats', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_visitor_stats_ping'),
            'refreshInterval' => 30000 // 30 seconds
        ));
    }
    
    /**
     * Output CSS
     */
    public function output_css() {
        $format = get_option('wp_visitor_stats_format', 'compact');
        $color_scheme = get_option('wp_visitor_stats_color', 'blue');
        
        include WP_VISITOR_STATS_PLUGIN_DIR . 'templates/styles.php';
    }
    
    /**
     * Get stats data
     */
    private function get_stats_data() {
        return array(
            'today' => $this->db->get_visitor_count('today'),
            'week' => $this->db->get_visitor_count('week'),
            'month' => $this->db->get_visitor_count('month'),
            'year' => $this->db->get_visitor_count('year'),
            'online' => $this->db->get_online_count()
        );
    }
    
    /**
     * Add to menu left
     */
    public function add_to_menu_left($items, $args) {
        if ($args->theme_location === 'primary' || $args->theme_location === 'main-menu') {
            $stats_html = $this->get_stats_html();
            $items = $stats_html . $items;
        }
        return $items;
    }
    
    /**
     * Add to menu right
     */
    public function add_to_menu_right($items, $args) {
        if ($args->theme_location === 'primary' || $args->theme_location === 'main-menu') {
            $stats_html = $this->get_stats_html();
            $items = $items . $stats_html;
        }
        return $items;
    }
    
    /**
     * Output stats in center
     */
    public function output_stats_center() {
        echo $this->get_stats_html();
    }
    
    /**
     * Output stats before content
     */
    public function output_stats_before_content($content) {
        return $this->get_stats_html() . $content;
    }
    
    /**
     * Output stats in footer
     */
    public function output_stats_footer() {
        echo $this->get_stats_html();
    }
    
    /**
     * Get stats HTML
     */
    private function get_stats_html() {
        $stats = $this->get_stats_data();
        $format = get_option('wp_visitor_stats_format', 'compact');
        $color_scheme = get_option('wp_visitor_stats_color', 'blue');
        
        ob_start();
        include WP_VISITOR_STATS_PLUGIN_DIR . 'templates/display-' . $format . '.php';
        return ob_get_clean();
    }
}

