<?php
/**
 * WhatsApp alerts functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Visitor_Stats_WhatsApp {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        $this->init();
    }
    
    /**
     * Initialize WhatsApp alerts
     */
    private function init() {
        add_action('admin_menu', array($this, 'add_whatsapp_menu'));
        add_action('wp_ajax_wp_visitor_stats_test_whatsapp', array($this, 'ajax_test_whatsapp'));
        add_action('wp_ajax_wp_visitor_stats_save_whatsapp_settings', array($this, 'ajax_save_settings'));
        
        // Check threshold daily
        add_action('wp_visitor_stats_check_threshold', array($this, 'check_threshold'));
        
        // Schedule daily check if not already scheduled
        if (!wp_next_scheduled('wp_visitor_stats_check_threshold')) {
            wp_schedule_event(time(), 'daily', 'wp_visitor_stats_check_threshold');
        }
    }
    
    /**
     * Add WhatsApp menu
     */
    public function add_whatsapp_menu() {
        add_submenu_page(
            'wp-visitor-stats',
            __('WhatsApp Alerts', 'wp-visitor-stats'),
            __('WhatsApp Alerts', 'wp-visitor-stats'),
            'manage_options',
            'wp-visitor-stats-whatsapp',
            array($this, 'render_whatsapp_page')
        );
    }
    
    /**
     * Render WhatsApp page
     */
    public function render_whatsapp_page() {
        $enabled = get_option('wp_visitor_stats_whatsapp_enabled', false);
        $phone = get_option('wp_visitor_stats_whatsapp_phone', '');
        $threshold = get_option('wp_visitor_stats_whatsapp_threshold', 100);
        $period = get_option('wp_visitor_stats_whatsapp_period', 'today');
        $last_sent = get_option('wp_visitor_stats_whatsapp_last_sent', '');
        
        include WP_VISITOR_STATS_PLUGIN_DIR . 'templates/admin-whatsapp.php';
    }
    
    /**
     * AJAX save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer('wp_visitor_stats_whatsapp', 'nonce');
        
        $enabled = isset($_POST['enabled']) ? (bool) $_POST['enabled'] : false;
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $threshold = isset($_POST['threshold']) ? intval($_POST['threshold']) : 100;
        $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : 'today';
        
        // Validate phone number (remove spaces, dashes, etc.)
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        update_option('wp_visitor_stats_whatsapp_enabled', $enabled);
        update_option('wp_visitor_stats_whatsapp_phone', $phone);
        update_option('wp_visitor_stats_whatsapp_threshold', $threshold);
        update_option('wp_visitor_stats_whatsapp_period', $period);
        
        wp_send_json_success(array('message' => __('Settings saved!', 'wp-visitor-stats')));
    }
    
    /**
     * AJAX test WhatsApp
     */
    public function ajax_test_whatsapp() {
        check_ajax_referer('wp_visitor_stats_whatsapp', 'nonce');
        
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        if (empty($phone)) {
            wp_send_json_error(array('message' => __('Phone number is required', 'wp-visitor-stats')));
        }
        
        $message = __('Test message from WP Visitor Stats plugin. If you receive this, your WhatsApp alerts are configured correctly!', 'wp-visitor-stats');
        
        $result = $this->send_whatsapp_message($phone, $message);
        
        if ($result['success']) {
            wp_send_json_success(array('message' => __('Test message sent! Check your WhatsApp.', 'wp-visitor-stats')));
        } else {
            wp_send_json_error(array('message' => $result['error']));
        }
    }
    
    /**
     * Check threshold and send alert if needed
     */
    public function check_threshold() {
        $enabled = get_option('wp_visitor_stats_whatsapp_enabled', false);
        
        if (!$enabled) {
            return;
        }
        
        $threshold = get_option('wp_visitor_stats_whatsapp_threshold', 100);
        $period = get_option('wp_visitor_stats_whatsapp_period', 'today');
        $phone = get_option('wp_visitor_stats_whatsapp_phone', '');
        
        if (empty($phone)) {
            return;
        }
        
        // Get current visitor count
        $current_count = $this->db->get_unique_visitor_count($period);
        
        // Check if threshold is reached
        if ($current_count >= $threshold) {
            // Check if we already sent today
            $last_sent_date = get_option('wp_visitor_stats_whatsapp_last_sent_date', '');
            $today = current_time('Y-m-d');
            
            if ($last_sent_date === $today) {
                return; // Already sent today
            }
            
            // Send alert
            $period_labels = array(
                'today' => __('today', 'wp-visitor-stats'),
                'week' => __('this week', 'wp-visitor-stats'),
                'month' => __('this month', 'wp-visitor-stats'),
                'year' => __('this year', 'wp-visitor-stats')
            );
            
            $period_label = isset($period_labels[$period]) ? $period_labels[$period] : $period;
            
            $message = sprintf(
                __('🚀 Alert: Your website has reached %d unique visitors %s! Current count: %d visitors.', 'wp-visitor-stats'),
                $threshold,
                $period_label,
                $current_count
            );
            
            $result = $this->send_whatsapp_message($phone, $message);
            
            if ($result['success']) {
                update_option('wp_visitor_stats_whatsapp_last_sent', current_time('mysql'));
                update_option('wp_visitor_stats_whatsapp_last_sent_date', $today);
            }
        }
    }
    
    /**
     * Send WhatsApp message using free API
     * Uses multiple free services as fallback
     */
    private function send_whatsapp_message($phone, $message) {
        // Remove + from phone if present for some APIs
        $phone_clean = ltrim($phone, '+');
        
        // Try CallMeBot API (free, no registration needed)
        $callmebot_url = sprintf(
            'https://api.callmebot.com/whatsapp.php?phone=%s&text=%s&apikey=',
            urlencode($phone_clean),
            urlencode($message)
        );
        
        $response = wp_remote_get($callmebot_url, array(
            'timeout' => 10,
            'sslverify' => false
        ));
        
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            
            // CallMeBot returns "OK" on success
            if (strpos($body, 'OK') !== false || strpos($body, 'Message sent') !== false) {
                return array('success' => true);
            }
        }
        
        // Fallback: Try alternative method using WhatsApp Web API simulation
        // Note: This requires the phone number to be in international format without +
        $alternative_url = sprintf(
            'https://wa.me/%s?text=%s',
            urlencode($phone),
            urlencode($message)
        );
        
        // For automatic sending, we'll use a webhook service
        // Try using a notification service that supports WhatsApp
        $webhook_url = get_option('wp_visitor_stats_whatsapp_webhook', '');
        
        if (!empty($webhook_url)) {
            $webhook_response = wp_remote_post($webhook_url, array(
                'body' => json_encode(array(
                    'phone' => $phone,
                    'message' => $message
                )),
                'headers' => array('Content-Type' => 'application/json'),
                'timeout' => 10
            ));
            
            if (!is_wp_error($webhook_response)) {
                return array('success' => true);
            }
        }
        
        return array(
            'success' => false,
            'error' => __('Failed to send WhatsApp message. Please check your phone number format (use international format: +1234567890) and try again.', 'wp-visitor-stats')
        );
    }
}

