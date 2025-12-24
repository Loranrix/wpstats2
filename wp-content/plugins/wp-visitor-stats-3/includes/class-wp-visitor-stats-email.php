<?php
/**
 * Email reports functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Visitor_Stats_Email {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        $this->init();
    }
    
    /**
     * Initialize email reports
     */
    private function init() {
        add_action('admin_menu', array($this, 'add_email_menu'));
        add_action('wp_ajax_wp_visitor_stats_test_email', array($this, 'ajax_test_email'));
        add_action('wp_ajax_wp_visitor_stats_save_email_settings', array($this, 'ajax_save_settings'));
        
        // Schedule email reports
        add_action('wp_visitor_stats_send_daily_report', array($this, 'send_daily_report'));
        add_action('wp_visitor_stats_send_weekly_report', array($this, 'send_weekly_report'));
        add_action('wp_visitor_stats_send_monthly_report', array($this, 'send_monthly_report'));
        
        // Schedule events if enabled
        $this->schedule_events();
    }
    
    /**
     * Schedule email report events
     */
    private function schedule_events() {
        $enabled = get_option('wp_visitor_stats_email_enabled', false);
        $frequency = get_option('wp_visitor_stats_email_frequency', 'daily');
        
        if (!$enabled) {
            wp_clear_scheduled_hook('wp_visitor_stats_send_daily_report');
            wp_clear_scheduled_hook('wp_visitor_stats_send_weekly_report');
            wp_clear_scheduled_hook('wp_visitor_stats_send_monthly_report');
            return;
        }
        
        // Clear all first
        wp_clear_scheduled_hook('wp_visitor_stats_send_daily_report');
        wp_clear_scheduled_hook('wp_visitor_stats_send_weekly_report');
        wp_clear_scheduled_hook('wp_visitor_stats_send_monthly_report');
        
        // Schedule based on frequency
        if ($frequency === 'daily' && !wp_next_scheduled('wp_visitor_stats_send_daily_report')) {
            wp_schedule_event(time(), 'daily', 'wp_visitor_stats_send_daily_report');
        } elseif ($frequency === 'weekly' && !wp_next_scheduled('wp_visitor_stats_send_weekly_report')) {
            wp_schedule_event(time(), 'weekly', 'wp_visitor_stats_send_weekly_report');
        } elseif ($frequency === 'monthly' && !wp_next_scheduled('wp_visitor_stats_send_monthly_report')) {
            wp_schedule_event(time(), 'monthly', 'wp_visitor_stats_send_monthly_report');
        }
    }
    
    /**
     * Add email menu
     */
    public function add_email_menu() {
        add_submenu_page(
            'wp-visitor-stats',
            __('Email Reports', 'wp-visitor-stats'),
            __('Email Reports', 'wp-visitor-stats'),
            'manage_options',
            'wp-visitor-stats-email',
            array($this, 'render_email_page')
        );
    }
    
    /**
     * Render email page
     */
    public function render_email_page() {
        $enabled = get_option('wp_visitor_stats_email_enabled', false);
        $email = get_option('wp_visitor_stats_email_address', '');
        $frequency = get_option('wp_visitor_stats_email_frequency', 'daily');
        $format = get_option('wp_visitor_stats_email_format', 'summary');
        $threshold_enabled = get_option('wp_visitor_stats_email_threshold_enabled', false);
        $threshold = get_option('wp_visitor_stats_email_threshold', 100);
        $threshold_period = get_option('wp_visitor_stats_email_threshold_period', 'today');
        $last_sent = get_option('wp_visitor_stats_email_last_sent', '');
        
        include WP_VISITOR_STATS_PLUGIN_DIR . 'templates/admin-email.php';
    }
    
    /**
     * AJAX save settings
     */
    public function ajax_save_settings() {
        check_ajax_referer('wp_visitor_stats_email', 'nonce');
        
        $enabled = isset($_POST['enabled']) ? (bool) $_POST['enabled'] : false;
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $frequency = isset($_POST['frequency']) ? sanitize_text_field($_POST['frequency']) : 'daily';
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'summary';
        $threshold_enabled = isset($_POST['threshold_enabled']) ? (bool) $_POST['threshold_enabled'] : false;
        $threshold = isset($_POST['threshold']) ? intval($_POST['threshold']) : 100;
        $threshold_period = isset($_POST['threshold_period']) ? sanitize_text_field($_POST['threshold_period']) : 'today';
        
        if ($enabled && !is_email($email)) {
            wp_send_json_error(array('message' => __('Invalid email address', 'wp-visitor-stats')));
        }
        
        update_option('wp_visitor_stats_email_enabled', $enabled);
        update_option('wp_visitor_stats_email_address', $email);
        update_option('wp_visitor_stats_email_frequency', $frequency);
        update_option('wp_visitor_stats_email_format', $format);
        update_option('wp_visitor_stats_email_threshold_enabled', $threshold_enabled);
        update_option('wp_visitor_stats_email_threshold', $threshold);
        update_option('wp_visitor_stats_email_threshold_period', $threshold_period);
        
        // Reschedule events
        $this->schedule_events();
        
        wp_send_json_success(array('message' => __('Settings saved!', 'wp-visitor-stats')));
    }
    
    /**
     * AJAX test email
     */
    public function ajax_test_email() {
        check_ajax_referer('wp_visitor_stats_email', 'nonce');
        
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array('message' => __('Invalid email address', 'wp-visitor-stats')));
        }
        
        $result = $this->send_email_report($email, 'test');
        
        if ($result) {
            wp_send_json_success(array('message' => __('Test email sent! Check your inbox.', 'wp-visitor-stats')));
        } else {
            wp_send_json_error(array('message' => __('Failed to send test email. Check your WordPress email configuration.', 'wp-visitor-stats')));
        }
    }
    
    /**
     * Send daily report
     */
    public function send_daily_report() {
        $this->send_scheduled_report('today');
    }
    
    /**
     * Send weekly report
     */
    public function send_weekly_report() {
        $this->send_scheduled_report('week');
    }
    
    /**
     * Send monthly report
     */
    public function send_monthly_report() {
        $this->send_scheduled_report('month');
    }
    
    /**
     * Send scheduled report
     */
    private function send_scheduled_report($period) {
        $enabled = get_option('wp_visitor_stats_email_enabled', false);
        $threshold_enabled = get_option('wp_visitor_stats_email_threshold_enabled', false);
        
        if (!$enabled) {
            return;
        }
        
        $email = get_option('wp_visitor_stats_email_address', '');
        if (empty($email)) {
            return;
        }
        
        // Check threshold if enabled
        if ($threshold_enabled) {
            $threshold = get_option('wp_visitor_stats_email_threshold', 100);
            $threshold_period = get_option('wp_visitor_stats_email_threshold_period', 'today');
            $current_count = $this->db->get_unique_visitor_count($threshold_period);
            
            if ($current_count < $threshold) {
                return; // Don't send if threshold not reached
            }
        }
        
        $this->send_email_report($email, $period);
    }
    
    /**
     * Send email report
     */
    private function send_email_report($email, $period = 'today') {
        $format = get_option('wp_visitor_stats_email_format', 'summary');
        
        $subject = $this->get_email_subject($period);
        $message = $this->get_email_content($period, $format);
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $sent = wp_mail($email, $subject, $message, $headers);
        
        if ($sent) {
            update_option('wp_visitor_stats_email_last_sent', current_time('mysql'));
        }
        
        return $sent;
    }
    
    /**
     * Get email content
     */
    private function get_email_content($period, $format) {
        require_once WP_VISITOR_STATS_PLUGIN_DIR . 'includes/class-wp-visitor-stats-geolocation.php';
        $geolocation = new WP_Visitor_Stats_Geolocation($this->db);
        
        $stats = array(
            'today' => $this->db->get_unique_visitor_count('today'),
            'week' => $this->db->get_unique_visitor_count('week'),
            'month' => $this->db->get_unique_visitor_count('month'),
            'year' => $this->db->get_unique_visitor_count('year'),
            'online' => $this->db->get_online_count()
        );
        
        $period_labels = array(
            'test' => __('Test Period', 'wp-visitor-stats'),
            'today' => __('Today', 'wp-visitor-stats'),
            'week' => __('Last 7 Days', 'wp-visitor-stats'),
            'month' => __('Last 30 Days', 'wp-visitor-stats')
        );
        
        $period_label = isset($period_labels[$period]) ? $period_labels[$period] : $period;
        $period_count = isset($stats[$period]) ? $stats[$period] : 0;
        
        // Make variables available to template
        $email_obj = $this;
        $db_instance = $this->db;
        
        ob_start();
        include WP_VISITOR_STATS_PLUGIN_DIR . 'templates/email-report-' . $format . '.php';
        return ob_get_clean();
    }
    
    /**
     * Get email subject (public for template access)
     */
    public function get_email_subject($period) {
        $site_name = get_bloginfo('name');
        $periods = array(
            'test' => __('Test Report', 'wp-visitor-stats'),
            'today' => __('Daily Visitor Report', 'wp-visitor-stats'),
            'week' => __('Weekly Visitor Report', 'wp-visitor-stats'),
            'month' => __('Monthly Visitor Report', 'wp-visitor-stats')
        );
        
        $period_label = isset($periods[$period]) ? $periods[$period] : $period;
        return sprintf(__('%s - %s', 'wp-visitor-stats'), $site_name, $period_label);
    }
}

