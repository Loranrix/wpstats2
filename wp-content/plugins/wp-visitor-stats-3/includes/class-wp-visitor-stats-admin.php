<?php
/**
 * Admin panel for WP Visitor Stats
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Visitor_Stats_Admin {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        $this->init();
    }
    
    /**
     * Initialize admin
     */
    private function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_wp_visitor_stats_dismiss_cache_warning', array($this, 'dismiss_cache_warning'));
        add_action('wp_ajax_wp_visitor_stats_generate_cookie', array($this, 'generate_cookie_link'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Visitor Stats', 'wp-visitor-stats'),
            __('Visitor Stats', 'wp-visitor-stats'),
            'manage_options',
            'wp-visitor-stats',
            array($this, 'render_admin_page'),
            'dashicons-chart-line',
            30
        );
        
        add_submenu_page(
            'wp-visitor-stats',
            __('Settings', 'wp-visitor-stats'),
            __('Settings', 'wp-visitor-stats'),
            'manage_options',
            'wp-visitor-stats',
            array($this, 'render_admin_page')
        );
        
        add_submenu_page(
            'wp-visitor-stats',
            __('By Country', 'wp-visitor-stats'),
            __('By Country', 'wp-visitor-stats'),
            'manage_options',
            'wp-visitor-stats-countries',
            array($this, 'render_countries_page')
        );
        
        add_submenu_page(
            'wp-visitor-stats',
            __('By City', 'wp-visitor-stats'),
            __('By City', 'wp-visitor-stats'),
            'manage_options',
            'wp-visitor-stats-cities',
            array($this, 'render_cities_page')
        );
        
        add_submenu_page(
            'wp-visitor-stats',
            __('Excluded IPs', 'wp-visitor-stats'),
            __('Excluded IPs', 'wp-visitor-stats'),
            'manage_options',
            'wp-visitor-stats-excluded',
            array($this, 'render_excluded_page')
        );
        
        add_submenu_page(
            'wp-visitor-stats',
            __('Cache Configuration', 'wp-visitor-stats'),
            __('Cache Configuration', 'wp-visitor-stats'),
            'manage_options',
            'wp-visitor-stats-cache-help',
            array($this, 'render_cache_help_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wp_visitor_stats_settings', 'wp_visitor_stats_enabled');
        register_setting('wp_visitor_stats_settings', 'wp_visitor_stats_position');
        register_setting('wp_visitor_stats_settings', 'wp_visitor_stats_format');
        register_setting('wp_visitor_stats_settings', 'wp_visitor_stats_color');
        register_setting('wp_visitor_stats_settings', 'wp_visitor_stats_authorized_ips');
        register_setting('wp_visitor_stats_settings', 'wp_visitor_stats_track_all');
        register_setting('wp_visitor_stats_settings', 'wp_visitor_stats_cookie_token');
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_wp-visitor-stats') {
            return;
        }
        
        wp_enqueue_style(
            'wp-visitor-stats-admin',
            WP_VISITOR_STATS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WP_VISITOR_STATS_VERSION
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (isset($_POST['wp_visitor_stats_save'])) {
            check_admin_referer('wp_visitor_stats_settings');
            $this->save_settings();
        }
        
        if (isset($_POST['wp_visitor_stats_add_ip'])) {
            check_admin_referer('wp_visitor_stats_settings');
            $this->add_authorized_ip();
        }
        
        if (isset($_POST['wp_visitor_stats_remove_ip'])) {
            check_admin_referer('wp_visitor_stats_settings');
            $this->remove_authorized_ip();
        }
        
        include WP_VISITOR_STATS_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    /**
     * Render countries page
     */
    public function render_countries_page() {
        try {
            require_once WP_VISITOR_STATS_PLUGIN_DIR . 'includes/class-wp-visitor-stats-geolocation.php';
            $geolocation = new WP_Visitor_Stats_Geolocation($this->db);
            
            $period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : 'all';
            $countries = $geolocation->get_visitors_by_country($period);
            
            $template_file = WP_VISITOR_STATS_PLUGIN_DIR . 'templates/admin-countries.php';
            if (file_exists($template_file)) {
                include $template_file;
            } else {
                echo '<div class="wrap"><h1>' . __('By Country', 'wp-visitor-stats') . '</h1>';
                echo '<p>' . __('Template file not found.', 'wp-visitor-stats') . '</p></div>';
            }
        } catch (Exception $e) {
            echo '<div class="wrap"><h1>' . __('By Country', 'wp-visitor-stats') . '</h1>';
            echo '<div class="notice notice-error"><p>' . __('Error: ', 'wp-visitor-stats') . esc_html($e->getMessage()) . '</p></div></div>';
            error_log('WP Visitor Stats - Error in render_countries_page: ' . $e->getMessage());
        }
    }
    
    /**
     * Render cities page
     */
    public function render_cities_page() {
        try {
            require_once WP_VISITOR_STATS_PLUGIN_DIR . 'includes/class-wp-visitor-stats-geolocation.php';
            $geolocation = new WP_Visitor_Stats_Geolocation($this->db);
            
            $period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : 'all';
            $country_code = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
            
            // Ensure cities is always an array
            $cities = $geolocation->get_visitors_by_city($period, $country_code);
            if (!is_array($cities)) {
                $cities = array();
            }
            
            $template_file = WP_VISITOR_STATS_PLUGIN_DIR . 'templates/admin-cities.php';
            if (file_exists($template_file)) {
                include $template_file;
            } else {
                echo '<div class="wrap"><h1>' . __('By City', 'wp-visitor-stats') . '</h1>';
                echo '<p>' . __('Template file not found.', 'wp-visitor-stats') . '</p></div>';
            }
        } catch (Exception $e) {
            echo '<div class="wrap"><h1>' . __('By City', 'wp-visitor-stats') . '</h1>';
            echo '<div class="notice notice-error"><p>' . __('Error: ', 'wp-visitor-stats') . esc_html($e->getMessage()) . '</p></div></div>';
            error_log('WP Visitor Stats - Error in render_cities_page: ' . $e->getMessage());
        } catch (Error $e) {
            echo '<div class="wrap"><h1>' . __('By City', 'wp-visitor-stats') . '</h1>';
            echo '<div class="notice notice-error"><p>' . __('Fatal Error: ', 'wp-visitor-stats') . esc_html($e->getMessage()) . '</p></div></div>';
            error_log('WP Visitor Stats - Fatal Error in render_cities_page: ' . $e->getMessage());
        }
    }
    
    /**
     * Render excluded IPs page
     */
    public function render_excluded_page() {
        if (isset($_POST['wp_visitor_stats_add_excluded_ip'])) {
            check_admin_referer('wp_visitor_stats_excluded');
            $this->add_excluded_ip();
        }
        
        if (isset($_POST['wp_visitor_stats_remove_excluded_ip'])) {
            check_admin_referer('wp_visitor_stats_excluded');
            $this->remove_excluded_ip();
        }
        
        $excluded_ips = $this->db->get_excluded_ips();
        include WP_VISITOR_STATS_PLUGIN_DIR . 'templates/admin-excluded.php';
    }
    
    /**
     * Add excluded IP
     */
    private function add_excluded_ip() {
        $ip = sanitize_text_field($_POST['new_excluded_ip']);
        
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $excluded_ips = $this->db->get_excluded_ips();
            if (!in_array($ip, $excluded_ips)) {
                $excluded_ips[] = $ip;
                update_option('wp_visitor_stats_excluded_ips', $excluded_ips);
                echo '<div class="notice notice-success"><p>' . __('IP address excluded!', 'wp-visitor-stats') . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . __('IP address already excluded!', 'wp-visitor-stats') . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>' . __('Invalid IP address!', 'wp-visitor-stats') . '</p></div>';
        }
    }
    
    /**
     * Remove excluded IP
     */
    private function remove_excluded_ip() {
        $ip = sanitize_text_field($_POST['remove_excluded_ip']);
        $excluded_ips = $this->db->get_excluded_ips();
        $excluded_ips = array_diff($excluded_ips, array($ip));
        update_option('wp_visitor_stats_excluded_ips', array_values($excluded_ips));
        echo '<div class="notice notice-success"><p>' . __('IP address removed from exclusion list!', 'wp-visitor-stats') . '</p></div>';
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        update_option('wp_visitor_stats_enabled', isset($_POST['wp_visitor_stats_enabled']));
        update_option('wp_visitor_stats_position', sanitize_text_field($_POST['wp_visitor_stats_position']));
        update_option('wp_visitor_stats_format', sanitize_text_field($_POST['wp_visitor_stats_format']));
        update_option('wp_visitor_stats_color', sanitize_text_field($_POST['wp_visitor_stats_color']));
        update_option('wp_visitor_stats_track_all', isset($_POST['wp_visitor_stats_track_all']));
        
        echo '<div class="notice notice-success"><p>' . __('Settings saved!', 'wp-visitor-stats') . '</p></div>';
    }
    
    /**
     * Add authorized IP
     */
    private function add_authorized_ip() {
        $ip = sanitize_text_field($_POST['new_ip']);
        
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $authorized_ips = get_option('wp_visitor_stats_authorized_ips', array());
            if (!in_array($ip, $authorized_ips)) {
                $authorized_ips[] = $ip;
                update_option('wp_visitor_stats_authorized_ips', $authorized_ips);
                echo '<div class="notice notice-success"><p>' . __('IP address added!', 'wp-visitor-stats') . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . __('IP address already exists!', 'wp-visitor-stats') . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>' . __('Invalid IP address!', 'wp-visitor-stats') . '</p></div>';
        }
    }
    
    /**
     * Remove authorized IP
     */
    private function remove_authorized_ip() {
        $ip = sanitize_text_field($_POST['remove_ip']);
        $authorized_ips = get_option('wp_visitor_stats_authorized_ips', array());
        $authorized_ips = array_diff($authorized_ips, array($ip));
        update_option('wp_visitor_stats_authorized_ips', array_values($authorized_ips));
        echo '<div class="notice notice-success"><p>' . __('IP address removed!', 'wp-visitor-stats') . '</p></div>';
    }
    
    /**
     * Dismiss cache warning
     */
    public function dismiss_cache_warning() {
        check_ajax_referer('wp_visitor_stats_dismiss', 'nonce');
        update_option('wp_visitor_stats_cache_warning_dismissed', true);
        wp_send_json_success();
    }
    
    /**
     * Generate cookie link
     */
    public function generate_cookie_link() {
        check_ajax_referer('wp_visitor_stats_generate_cookie', 'nonce');
        
        $token = bin2hex(random_bytes(32));
        update_option('wp_visitor_stats_cookie_token', $token);
        
        $site_url = home_url();
        $cookie_url = add_query_arg(array(
            'wp_visitor_stats_token' => $token
        ), $site_url);
        
        wp_send_json_success(array(
            'url' => $cookie_url,
            'token' => $token
        ));
    }
    
    /**
     * Render cache help page
     */
    public function render_cache_help_page() {
        // Try new comprehensive cache config file first, fallback to old LiteSpeed file
        $help_file = WP_VISITOR_STATS_PLUGIN_DIR . 'CACHE-CONFIG.md';
        
        if (!file_exists($help_file)) {
            // Fallback to old file name
            $help_file = WP_VISITOR_STATS_PLUGIN_DIR . 'CONFIG-LITESPEED.md';
        }
        
        if (!file_exists($help_file)) {
            echo '<div class="wrap"><h1>' . __('Cache Configuration', 'wp-visitor-stats') . '</h1>';
            echo '<div class="notice notice-error"><p>' . __('Help file not found.', 'wp-visitor-stats') . '</p></div></div>';
            return;
        }
        
        $content = file_get_contents($help_file);
        
        // Convert markdown to HTML (basic conversion)
        $content = $this->markdown_to_html($content);
        
        ?>
        <div class="wrap">
            <h1><?php _e('Cache Configuration', 'wp-visitor-stats'); ?></h1>
            <div class="wp-visitor-stats-help-content" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 20px 0; max-width: 1200px;">
                <?php echo wp_kses_post($content); ?>
            </div>
        </div>
        <style>
            .wp-visitor-stats-help-content h1 {
                border-bottom: 2px solid #2271b1;
                padding-bottom: 10px;
                margin-top: 0;
            }
            .wp-visitor-stats-help-content h2 {
                color: #2271b1;
                margin-top: 30px;
                border-bottom: 1px solid #ddd;
                padding-bottom: 5px;
            }
            .wp-visitor-stats-help-content h3 {
                color: #555;
                margin-top: 20px;
            }
            .wp-visitor-stats-help-content code {
                background: #f5f5f5;
                padding: 2px 6px;
                border-radius: 3px;
                font-family: 'Courier New', monospace;
                font-size: 13px;
            }
            .wp-visitor-stats-help-content pre {
                background: #f5f5f5;
                padding: 15px;
                border-left: 4px solid #2271b1;
                overflow-x: auto;
                border-radius: 3px;
            }
            .wp-visitor-stats-help-content pre code {
                background: none;
                padding: 0;
            }
            .wp-visitor-stats-help-content ul, .wp-visitor-stats-help-content ol {
                margin-left: 20px;
                line-height: 1.8;
            }
            .wp-visitor-stats-help-content li {
                margin-bottom: 8px;
            }
            .wp-visitor-stats-help-content strong {
                color: #2271b1;
            }
            .wp-visitor-stats-help-content a {
                color: #2271b1;
                text-decoration: underline;
            }
            .wp-visitor-stats-help-content a:hover {
                color: #135e96;
            }
        </style>
        <?php
    }
    
    /**
     * Convert basic markdown to HTML
     */
    private function markdown_to_html($markdown) {
        // Headers
        $markdown = preg_replace('/^# (.*)$/m', '<h1>$1</h1>', $markdown);
        $markdown = preg_replace('/^## (.*)$/m', '<h2>$1</h2>', $markdown);
        $markdown = preg_replace('/^### (.*)$/m', '<h3>$1</h3>', $markdown);
        
        // Code blocks
        $markdown = preg_replace('/```(\w+)?\n(.*?)```/s', '<pre><code>$2</code></pre>', $markdown);
        
        // Inline code
        $markdown = preg_replace('/`([^`]+)`/', '<code>$1</code>', $markdown);
        
        // Bold
        $markdown = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $markdown);
        
        // Links
        $markdown = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2" target="_blank">$1</a>', $markdown);
        
        // Lists
        $markdown = preg_replace('/^\* (.+)$/m', '<li>$1</li>', $markdown);
        $markdown = preg_replace('/^(\d+)\. (.+)$/m', '<li>$2</li>', $markdown);
        
        // Wrap consecutive list items in ul tags
        $markdown = preg_replace('/(<li>.*<\/li>\n?)+/s', '<ul>$0</ul>', $markdown);
        
        // Paragraphs (lines that don't start with HTML tags)
        $markdown = preg_replace('/^(?!<[h|u|o|p|d|s])(.+)$/m', '<p>$1</p>', $markdown);
        
        // Clean up empty paragraphs
        $markdown = preg_replace('/<p><\/p>/', '', $markdown);
        $markdown = preg_replace('/<p>(<[h|u|o|d|s])/', '$1', $markdown);
        $markdown = preg_replace('/(<\/[h|u|o|d|s]>)<\/p>/', '$1', $markdown);
        
        return $markdown;
    }
}

