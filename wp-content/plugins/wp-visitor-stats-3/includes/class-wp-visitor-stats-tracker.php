<?php
/**
 * Visitor tracking functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Visitor_Stats_Tracker {
    
    private $db;
    private $geolocation;
    
    public function __construct($db) {
        $this->db = $db;
        require_once WP_VISITOR_STATS_PLUGIN_DIR . 'includes/class-wp-visitor-stats-geolocation.php';
        $this->geolocation = new WP_Visitor_Stats_Geolocation($db);
        $this->init();
    }
    
    /**
     * Initialize tracking
     */
    private function init() {
        // Track visits on page load
        add_action('wp', array($this, 'track_visit'));
        
        // AJAX endpoint for online status updates
        add_action('wp_ajax_wp_visitor_stats_ping', array($this, 'ajax_ping'));
        add_action('wp_ajax_nopriv_wp_visitor_stats_ping', array($this, 'ajax_ping'));
    }
    
    /**
     * Track visit
     */
    public function track_visit() {
        // Only track if user can see stats or if tracking is enabled for all
        $track_all = get_option('wp_visitor_stats_track_all', false);
        
        if (!$track_all && !$this->db->can_see_stats()) {
            return;
        }
        
        $ip_address = $this->db->get_user_ip();
        
        // Skip excluded IPs
        if ($this->db->is_ip_excluded($ip_address)) {
            return;
        }
        
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $page_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
                    '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        
        // Get geolocation (async, won't block)
        $this->geolocation->get_location($ip_address);
        
        // Record visit
        $this->db->record_visit($ip_address, $user_agent, $referer, $page_url);
        
        // Update online status
        $this->db->update_online($ip_address, $user_agent, $page_url);
    }
    
    /**
     * AJAX ping for online status
     */
    public function ajax_ping() {
        check_ajax_referer('wp_visitor_stats_ping', 'nonce');
        
        $ip_address = $this->db->get_user_ip();
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $page_url = isset($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : '';
        
        $this->db->update_online($ip_address, $user_agent, $page_url);
        
        wp_send_json_success(array(
            'online_count' => $this->db->get_online_count()
        ));
    }
}

