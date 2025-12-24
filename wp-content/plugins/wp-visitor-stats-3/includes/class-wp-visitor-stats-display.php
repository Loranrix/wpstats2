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
        
        // Default behavior: if enabled but track_all not set, show to everyone (backward compatibility)
        // If track_all is explicitly enabled, show stats to everyone
        if ($track_all === true || $track_all === '1' || $track_all === 1) {
            // Show to everyone - continue
        } elseif ($track_all === false || $track_all === '0' || $track_all === 0) {
            // track_all is explicitly disabled, check permissions
            if (!$this->db->can_see_stats()) {
                return;
            }
        } else {
            // track_all option not set (null), default to showing to everyone if enabled
            // This ensures backward compatibility
        }
        
        // Add display to header based on position
        $position = get_option('wp_visitor_stats_position', 'footer');
        
        // Always add CSS
        add_action('wp_head', array($this, 'output_css'), 1);
        
        // Add display based on position
        switch ($position) {
            case 'header_left':
                add_filter('wp_nav_menu_items', array($this, 'add_to_menu_left'), 10, 2);
                // Fallback if menu filter doesn't work
                add_action('wp_footer', array($this, 'output_stats_footer'), 999);
                break;
            case 'header_right':
                add_filter('wp_nav_menu_items', array($this, 'add_to_menu_right'), 10, 2);
                // Fallback if menu filter doesn't work
                add_action('wp_footer', array($this, 'output_stats_footer'), 999);
                break;
            case 'header_center':
                add_action('wp_body_open', array($this, 'output_stats_center'));
                // Fallback if wp_body_open not supported
                add_action('wp_footer', array($this, 'output_stats_footer'), 999);
                break;
            case 'before_content':
                add_action('the_content', array($this, 'output_stats_before_content'), 1);
                // Fallback if no content
                add_action('wp_footer', array($this, 'output_stats_footer'), 999);
                break;
            case 'footer':
            default:
                add_action('wp_footer', array($this, 'output_stats_footer'));
                break;
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
        static $displayed = false;
        if (!$displayed && !is_admin()) {
            $html = $this->get_stats_html();
            if (!empty($html)) {
                echo $html;
                $displayed = true;
            }
        }
    }
    
    /**
     * Output stats before content
     */
    public function output_stats_before_content($content) {
        static $displayed = false;
        if (!$displayed && !is_admin()) {
            $html = $this->get_stats_html();
            if (!empty($html)) {
                $content = $html . $content;
                $displayed = true;
            }
        }
        return $content;
    }
    
    /**
     * Output stats in footer
     */
    public function output_stats_footer() {
        static $displayed = false;
        // Only display once to avoid duplicates
        if (!$displayed && !is_admin()) {
            $html = $this->get_stats_html();
            if (!empty($html)) {
                // Add mobile-friendly wrapper with inline styles for better compatibility
                // Also add JavaScript to verify cookie and force display if needed
                echo '<div id="wp-visitor-stats-wrapper" style="display: block !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 9999 !important; width: 100%;">' . $html . '</div>';
                
                // Add JavaScript to verify cookie on client side and ensure visibility
                $saved_token = get_option('wp_visitor_stats_cookie_token', '');
                if (!empty($saved_token)) {
                    ?>
                    <script>
                    (function() {
                        // Verify cookie exists
                        function getCookie(name) {
                            var value = "; " + document.cookie;
                            var parts = value.split("; " + name + "=");
                            if (parts.length === 2) return parts.pop().split(";").shift();
                            return null;
                        }
                        
                        var cookieValue = getCookie('wp_visitor_stats_token');
                        var savedToken = '<?php echo esc_js($saved_token); ?>';
                        
                        // If cookie doesn't exist but should, try to set it from URL parameter
                        if (!cookieValue && savedToken) {
                            var urlParams = new URLSearchParams(window.location.search);
                            var tokenParam = urlParams.get('wp_visitor_stats_token');
                            if (tokenParam === savedToken) {
                                // Set cookie via JavaScript
                                var expires = new Date();
                                expires.setTime(expires.getTime() + (365 * 24 * 60 * 60 * 1000));
                                document.cookie = 'wp_visitor_stats_token=' + encodeURIComponent(savedToken) + '; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax<?php echo is_ssl() ? '; secure' : ''; ?>';
                            }
                        }
                        
                        // Ensure stats wrapper is visible
                        var wrapper = document.getElementById('wp-visitor-stats-wrapper');
                        if (wrapper) {
                            wrapper.style.display = 'block';
                            wrapper.style.visibility = 'visible';
                            wrapper.style.opacity = '1';
                        }
                    })();
                    </script>
                    <?php
                }
                $displayed = true;
            }
        }
    }
    
    
    /**
     * Get stats HTML (public for shortcode)
     */
    public function get_stats_html() {
        try {
            // Double-check permissions here (in case cookie was set via JavaScript)
            $track_all = get_option('wp_visitor_stats_track_all', false);
            if ($track_all !== true && $track_all !== '1' && $track_all !== 1) {
                // track_all is disabled, need to check permissions
                if (!$this->db->can_see_stats()) {
                    // Still return empty, but add a hidden div that JavaScript can check
                    // This allows JavaScript to detect and potentially refresh the page
                    $saved_token = get_option('wp_visitor_stats_cookie_token', '');
                    if (!empty($saved_token)) {
                        // Add a data attribute that JavaScript can check
                        return '<div id="wp-visitor-stats-hidden" data-token-check="' . esc_attr($saved_token) . '" style="display: none;"></div>';
                    }
                    return '';
                }
            }
            
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

