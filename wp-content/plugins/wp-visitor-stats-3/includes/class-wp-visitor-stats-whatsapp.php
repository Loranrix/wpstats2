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
        $callmebot_apikey = isset($_POST['callmebot_apikey']) ? sanitize_text_field($_POST['callmebot_apikey']) : '';
        $webhook_url = isset($_POST['whatsapp_webhook']) ? esc_url_raw($_POST['whatsapp_webhook']) : '';
        
        // Validate and normalize phone number
        $phone_normalized = $this->normalize_phone_number($phone);
        
        if ($phone_normalized === false && !empty($phone)) {
            wp_send_json_error(array(
                'message' => __('Invalid phone number format. Please use international format starting with + (e.g., +37120123456).', 'wp-visitor-stats')
            ));
            return;
        }
        
        $phone = $phone_normalized ? $phone_normalized : '';
        
        update_option('wp_visitor_stats_whatsapp_enabled', $enabled);
        update_option('wp_visitor_stats_whatsapp_phone', $phone);
        update_option('wp_visitor_stats_whatsapp_threshold', $threshold);
        update_option('wp_visitor_stats_whatsapp_period', $period);
        update_option('wp_visitor_stats_callmebot_apikey', $callmebot_apikey);
        update_option('wp_visitor_stats_whatsapp_webhook', $webhook_url);
        
        wp_send_json_success(array('message' => __('Settings saved!', 'wp-visitor-stats')));
    }
    
    /**
     * AJAX test WhatsApp
     */
    public function ajax_test_whatsapp() {
        check_ajax_referer('wp_visitor_stats_whatsapp', 'nonce');
        
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        
        if (empty($phone)) {
            wp_send_json_error(array('message' => __('Phone number is required', 'wp-visitor-stats')));
        }
        
        // Validate and normalize phone number
        $phone_normalized = $this->normalize_phone_number($phone);
        
        if ($phone_normalized === false) {
            wp_send_json_error(array(
                'message' => __('Invalid phone number format. Please use international format starting with + (e.g., +37120123456). The number must start with + followed by country code and number (7-15 digits total).', 'wp-visitor-stats')
            ));
        }
        
        $phone = $phone_normalized;
        
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
     * Validate and normalize phone number to international format
     */
    private function normalize_phone_number($phone) {
        // Remove all non-digit characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // If phone doesn't start with +, try to add it
        if (substr($phone, 0, 1) !== '+') {
            // If it starts with 00, replace with +
            if (substr($phone, 0, 2) === '00') {
                $phone = '+' . substr($phone, 2);
            } else {
                // Assume it's a local number, add + (user should provide country code)
                // For now, we'll require the + to be present
                return false;
            }
        }
        
        // Remove the + for validation
        $digits_only = substr($phone, 1);
        
        // Validate: should have 7-15 digits (international format)
        if (strlen($digits_only) < 7 || strlen($digits_only) > 15) {
            return false;
        }
        
        // Validate: should start with country code (1-3 digits)
        // Country codes are 1-3 digits, so the first 1-3 digits should be the country code
        if (!preg_match('/^\+[1-9]\d{6,14}$/', $phone)) {
            return false;
        }
        
        return $phone;
    }
    
    /**
     * Send WhatsApp message using free API
     * Uses multiple free services as fallback
     */
    private function send_whatsapp_message($phone, $message) {
        // Validate and normalize phone number
        $normalized_phone = $this->normalize_phone_number($phone);
        
        if ($normalized_phone === false) {
            return array(
                'success' => false,
                'error' => __('Invalid phone number format. Please use international format starting with + (e.g., +37120123456). The number must start with + followed by country code and number (7-15 digits total).', 'wp-visitor-stats')
            );
        }
        
        // Remove + from phone for some APIs
        $phone_clean = ltrim($normalized_phone, '+');
        
        // Try CallMeBot API (free, but requires API key for WhatsApp)
        // Note: CallMeBot WhatsApp requires an API key from their website
        $callmebot_apikey = get_option('wp_visitor_stats_callmebot_apikey', '');
        
        if (!empty($callmebot_apikey)) {
            $callmebot_url = sprintf(
                'https://api.callmebot.com/whatsapp.php?phone=%s&text=%s&apikey=%s',
                urlencode($phone_clean),
                urlencode($message),
                urlencode($callmebot_apikey)
            );
            
            $response = wp_remote_get($callmebot_url, array(
                'timeout' => 10,
                'sslverify' => false
            ));
            
            if (!is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $status_code = wp_remote_retrieve_response_code($response);
                
                // CallMeBot returns "OK" on success
                if ($status_code === 200 && (strpos($body, 'OK') !== false || strpos($body, 'Message sent') !== false || stripos($body, 'success') !== false)) {
                    return array('success' => true);
                }
                
                // Log error for debugging
                error_log('WP Visitor Stats - CallMeBot error: ' . $body);
            } else {
                error_log('WP Visitor Stats - CallMeBot request error: ' . $response->get_error_message());
            }
        }
        
        // Try using a webhook service if configured
        $webhook_url = get_option('wp_visitor_stats_whatsapp_webhook', '');
        
        if (!empty($webhook_url)) {
            $webhook_response = wp_remote_post($webhook_url, array(
                'body' => json_encode(array(
                    'phone' => $normalized_phone,
                    'message' => $message
                )),
                'headers' => array('Content-Type' => 'application/json'),
                'timeout' => 10
            ));
            
            if (!is_wp_error($webhook_response)) {
                $status_code = wp_remote_retrieve_response_code($webhook_response);
                if ($status_code >= 200 && $status_code < 300) {
                    return array('success' => true);
                }
            }
        }
        
        // If no API key is configured, provide helpful error message
        if (empty($callmebot_apikey) && empty($webhook_url)) {
            return array(
                'success' => false,
                'error' => __('WhatsApp API not configured. Please configure either CallMeBot API key or a webhook URL in the plugin settings. For CallMeBot, visit https://www.callmebot.com/blog/free-api-whatsapp-messages/ to get your free API key.', 'wp-visitor-stats')
            );
        }
        
        return array(
            'success' => false,
            'error' => sprintf(
                __('Failed to send WhatsApp message to %s. Please verify: 1) Phone number is in international format (+country code + number), 2) API key/webhook is correctly configured, 3) Your phone number is registered with WhatsApp.', 'wp-visitor-stats'),
                esc_html($normalized_phone)
            )
        );
    }
}

