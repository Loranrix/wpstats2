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
        $track_all = get_option('wp_visitor_stats_track_all', false);
        
        // If explicitly disabled, don't show
        if ($enabled === false || $enabled === '0' || $enabled === 0) {
            return;
        }
        
        // If track_all is enabled, show stats to everyone
        if ($track_all === true || $track_all === '1' || $track_all === 1) {
            // Show to everyone
        } else {
            // Check if user can see stats (IP authorized or cookie valid)
            if (!$this->db->can_see_stats()) {
                return;
            }
        }
        
        // Add display to header based on position
        $position = get_option('wp_visitor_stats_position', 'header_right');
        
        switch ($position) {
            case 'header_left':
                add_action('wp_head', array($this, 'output_css'), 1);
                add_filter('wp_nav_menu_items', array($this, 'add_to_menu_left'), 10, 2);
                // Fallback if menu filter doesn't work
                add_action('wp_body_open', array($this, 'output_stats_center'), 5);
                break;
            case 'header_right':
                add_action('wp_head', array($this, 'output_css'), 1);
                add_filter('wp_nav_menu_items', array($this, 'add_to_menu_right'), 10, 2);
                // Fallback if menu filter doesn't work
                add_action('wp_body_open', array($this, 'output_stats_center'), 5);
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
        
        // Always add to footer as backup if other methods fail (with lower priority)
        if ($position !== 'footer') {
            add_action('wp_footer', array($this, 'output_stats_footer_backup'), 999);
        }
        
        // Also add to wp_head as last resort (will appear at top of page) - only if track_all is enabled
        if ($track_all === true || $track_all === '1' || $track_all === 1) {
            add_action('wp_head', array($this, 'output_stats_head'), 999);
        }
        
        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Add shortcode for manual display
        add_shortcode('wp_visitor_stats', array($this, 'shortcode_display'));
    }
    
    /**
     * Shortcode to display stats manually
     */
    public function shortcode_display($atts) {
        return $this->get_stats_html();
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
        
        $styles_file = WP_VISITOR_STATS_PLUGIN_DIR . 'templates/styles.php';
        if (file_exists($styles_file)) {
            include $styles_file;
        }
    }
    
    /**
     * Get stats data
     */
    private function get_stats_data() {
        try {
            return array(
                'today' => (int) $this->db->get_visitor_count('today'),
                'week' => (int) $this->db->get_visitor_count('week'),
                'month' => (int) $this->db->get_visitor_count('month'),
                'year' => (int) $this->db->get_visitor_count('year'),
                'online' => (int) $this->db->get_online_count()
            );
        } catch (Exception $e) {
            // Return default values on error
            return array(
                'today' => 0,
                'week' => 0,
                'month' => 0,
                'year' => 0,
                'online' => 0
            );
        }
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
        static $displayed = false;
        // Only display once to avoid duplicates
        if (!$displayed) {
            $html = $this->get_stats_html();
            if (!empty($html)) {
                echo $html;
                $displayed = true;
            }
        }
    }
    
    /**
     * Output stats in footer (backup method)
     */
    public function output_stats_footer_backup() {
        static $displayed = false;
        // Only display if not already displayed by other methods
        if (!$displayed) {
            $html = $this->get_stats_html();
            if (!empty($html)) {
                echo $html;
                $displayed = true;
            }
        }
    }
    
    /**
     * Output stats in head (last resort - will appear at top)
     */
    public function output_stats_head() {
        static $displayed = false;
        // Only display if not already displayed and not in admin
        if (!$displayed && !is_admin()) {
            $html = $this->get_stats_html();
            if (!empty($html)) {
                echo '<div style="position: fixed; top: 0; right: 0; z-index: 999999; background: white; padding: 10px; border: 1px solid #ccc; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">' . $html . '</div>';
                $displayed = true;
            }
        }
    }
    
    /**
     * Get stats HTML (public for shortcode)
     */
    public function get_stats_html() {
        try {
            $stats = $this->get_stats_data();
            $format = get_option('wp_visitor_stats_format', 'compact');
            $color_scheme = get_option('wp_visitor_stats_color', 'blue');
            
            // Ensure stats array has all required keys
            $stats = wp_parse_args($stats, array(
                'today' => 0,
                'week' => 0,
                'month' => 0,
                'year' => 0,
                'online' => 0
            ));
            
            $template_file = WP_VISITOR_STATS_PLUGIN_DIR . 'templates/display-' . $format . '.php';
            
            if (!file_exists($template_file)) {
                $format = 'compact';
                $template_file = WP_VISITOR_STATS_PLUGIN_DIR . 'templates/display-' . $format . '.php';
            }
            
            ob_start();
            if (file_exists($template_file)) {
                include $template_file;
            } else {
                // Fallback simple display
                echo '<div class="wp-visitor-stats"><span>Today: ' . number_format($stats['today']) . ' | Online: ' . number_format($stats['online']) . '</span></div>';
            }
            return ob_get_clean();
        } catch (Exception $e) {
            // Return empty string on error to prevent breaking the page
            return '';
        }
    }
}

