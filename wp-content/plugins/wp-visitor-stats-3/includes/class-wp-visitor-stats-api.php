<?php
/**
 * API endpoints for WP Visitor Stats
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Visitor_Stats_API {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        $this->init();
    }
    
    /**
     * Initialize API
     */
    private function init() {
        // Handle cookie token
        add_action('init', array($this, 'handle_cookie_token'));
        
        // AJAX endpoint for stats
        add_action('wp_ajax_wp_visitor_stats_get', array($this, 'ajax_get_stats'));
        add_action('wp_ajax_nopriv_wp_visitor_stats_get', array($this, 'ajax_get_stats'));
    }
    
    /**
     * Handle cookie token
     */
    public function handle_cookie_token() {
        if (isset($_GET['wp_visitor_stats_token'])) {
            $token = sanitize_text_field($_GET['wp_visitor_stats_token']);
            $saved_token = get_option('wp_visitor_stats_cookie_token', '');
            
            if ($token === $saved_token && !empty($saved_token)) {
                // Set cookie for 1 year
                setcookie('wp_visitor_stats_token', $token, time() + (365 * 24 * 60 * 60), '/', '', is_ssl(), true);
                
                // Redirect to remove token from URL
                wp_redirect(remove_query_arg('wp_visitor_stats_token'));
                exit;
            }
        }
    }
    
    /**
     * AJAX get stats
     */
    public function ajax_get_stats() {
        // Check nonce if provided
        if (isset($_POST['nonce'])) {
            check_ajax_referer('wp_visitor_stats_get', 'nonce');
        }
        
        if (!$this->db->can_see_stats()) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $stats = array(
            'today' => $this->db->get_visitor_count('today'),
            'week' => $this->db->get_visitor_count('week'),
            'month' => $this->db->get_visitor_count('month'),
            'year' => $this->db->get_visitor_count('year'),
            'online' => $this->db->get_online_count()
        );
        
        wp_send_json_success($stats);
    }
}

