<?php
/**
 * Database operations for WP Visitor Stats
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Visitor_Stats_DB {
    
    public $table_visits;
    public $table_online;
    public $table_locations;
    
    public function __construct() {
        global $wpdb;
        $this->table_visits = $wpdb->prefix . 'visitor_stats_visits';
        $this->table_online = $wpdb->prefix . 'visitor_stats_online';
        $this->table_locations = $wpdb->prefix . 'visitor_stats_locations';
        
        // Ensure tables exist
        $this->create_tables();
    }
    
    /**
     * Create database tables
     */
    public function create_tables() {
        // Check if tables already exist to avoid unnecessary operations
        global $wpdb;
        $visits_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_visits}'");
        $online_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_online}'");
        $locations_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_locations}'");
        
        if ($visits_exists && $online_exists && $locations_exists) {
            return; // Tables already exist
        }
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table for storing visits
        $sql_visits = "CREATE TABLE IF NOT EXISTS {$this->table_visits} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            ip_address varchar(45) NOT NULL,
            user_agent text,
            referer text,
            page_url text,
            visit_date date NOT NULL,
            visit_time datetime NOT NULL,
            visit_count int(11) DEFAULT 1,
            PRIMARY KEY (id),
            KEY ip_address (ip_address),
            KEY visit_date (visit_date),
            KEY visit_time (visit_time)
        ) $charset_collate;";
        
        // Table for online users
        $sql_online = "CREATE TABLE IF NOT EXISTS {$this->table_online} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            ip_address varchar(45) NOT NULL,
            user_agent text,
            page_url text,
            last_seen datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY ip_address (ip_address),
            KEY last_seen (last_seen)
        ) $charset_collate;";
        
        // Table for IP locations (country, city)
        $sql_locations = "CREATE TABLE IF NOT EXISTS {$this->table_locations} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            ip_address varchar(45) NOT NULL,
            country_code varchar(2) DEFAULT NULL,
            country_name varchar(100) DEFAULT NULL,
            city varchar(100) DEFAULT NULL,
            region varchar(100) DEFAULT NULL,
            latitude decimal(10,8) DEFAULT NULL,
            longitude decimal(11,8) DEFAULT NULL,
            timezone varchar(50) DEFAULT NULL,
            last_updated datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY ip_address (ip_address),
            KEY country_code (country_code),
            KEY city (city)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_visits);
        dbDelta($sql_online);
        dbDelta($sql_locations);
        
        // Set version
        update_option('wp_visitor_stats_db_version', '1.0.2');
        
        // Verify tables were created
        $visits_created = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_visits}'");
        $online_created = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_online}'");
        $locations_created = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_locations}'");
        
        if (!$visits_created || !$online_created || !$locations_created) {
            error_log('WP Visitor Stats - Failed to create some tables');
        }
    }
    
    /**
     * Record a visit
     */
    public function record_visit($ip_address, $user_agent = '', $referer = '', $page_url = '') {
        global $wpdb;
        
        // Normalize page URL (remove query strings for better grouping)
        $normalized_url = $this->normalize_url($page_url);
        
        $today = current_time('Y-m-d');
        $now = current_time('mysql');
        
        // Check if visit already recorded today for this IP and page
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id, visit_count FROM {$this->table_visits} 
            WHERE ip_address = %s AND visit_date = %s AND page_url = %s
            ORDER BY visit_time DESC LIMIT 1",
            $ip_address,
            $today,
            $normalized_url
        ));
        
        if ($existing) {
            // Update visit count
            $wpdb->update(
                $this->table_visits,
                array(
                    'visit_count' => $existing->visit_count + 1,
                    'visit_time' => $now,
                    'referer' => $referer
                ),
                array('id' => $existing->id),
                array('%d', '%s', '%s'),
                array('%d')
            );
        } else {
            // Insert new visit
            $wpdb->insert(
                $this->table_visits,
                array(
                    'ip_address' => $ip_address,
                    'user_agent' => $user_agent,
                    'referer' => $referer,
                    'page_url' => $normalized_url,
                    'visit_date' => $today,
                    'visit_time' => $now,
                    'visit_count' => 1
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%d')
            );
        }
    }
    
    /**
     * Normalize URL for better tracking
     */
    private function normalize_url($url) {
        if (empty($url)) {
            return '';
        }
        
        // Remove common tracking parameters
        $url = preg_replace('/[?&](utm_[^&]*|fbclid|gclid|ref)=[^&]*/', '', $url);
        $url = rtrim($url, '?&');
        
        // Remove trailing slash for consistency
        $url = rtrim($url, '/');
        
        return $url;
    }
    
    /**
     * Update online status
     */
    public function update_online($ip_address, $user_agent = '', $page_url = '') {
        global $wpdb;
        
        // Don't track excluded IPs
        if ($this->is_ip_excluded($ip_address)) {
            // Remove from online if already there
            $wpdb->delete($this->table_online, array('ip_address' => $ip_address), array('%s'));
            return;
        }
        
        $now = current_time('mysql');
        
        // Check if already online
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_online} WHERE ip_address = %s",
            $ip_address
        ));
        
        if ($existing) {
            // Update last seen
            $wpdb->update(
                $this->table_online,
                array(
                    'last_seen' => $now,
                    'page_url' => $page_url,
                    'user_agent' => $user_agent
                ),
                array('ip_address' => $ip_address),
                array('%s', '%s', '%s'),
                array('%s')
            );
        } else {
            // Insert new online user
            $wpdb->insert(
                $this->table_online,
                array(
                    'ip_address' => $ip_address,
                    'user_agent' => $user_agent,
                    'page_url' => $page_url,
                    'last_seen' => $now
                ),
                array('%s', '%s', '%s', '%s')
            );
        }
    }
    
    /**
     * Clean old online users (older than 5 minutes)
     */
    public function clean_online_users() {
        global $wpdb;
        
        // Clean old users
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_online} 
            WHERE last_seen < DATE_SUB(NOW(), INTERVAL %d MINUTE)",
            5
        ));
        
        // Also remove excluded IPs from online table
        $excluded_ips = $this->get_excluded_ips();
        if (!empty($excluded_ips)) {
            $placeholders = implode(',', array_fill(0, count($excluded_ips), '%s'));
            $query = "DELETE FROM {$this->table_online} WHERE ip_address IN ($placeholders)";
            $prepared = $wpdb->prepare($query, $excluded_ips);
            $wpdb->query($prepared);
        }
    }
    
    /**
     * Get visitor count for period (uses unique visitors)
     */
    public function get_visitor_count($period = 'today') {
        return $this->get_unique_visitor_count($period);
    }
    
    /**
     * Get online users count
     */
    public function get_online_count() {
        global $wpdb;
        
        // Clean old users first
        $this->clean_online_users();
        
        // Get excluded IPs
        $excluded_ips = $this->get_excluded_ips();
        
        if (empty($excluded_ips)) {
            return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_online}");
        }
        
        // Count excluding excluded IPs
        $placeholders = implode(',', array_fill(0, count($excluded_ips), '%s'));
        $query = "SELECT COUNT(*) FROM {$this->table_online} WHERE ip_address NOT IN ($placeholders)";
        $prepared = $wpdb->prepare($query, $excluded_ips);
        
        return (int) $wpdb->get_var($prepared);
    }
    
    /**
     * Check if IP is authorized
     */
    public function is_ip_authorized($ip_address) {
        $authorized_ips = get_option('wp_visitor_stats_authorized_ips', array());
        return in_array($ip_address, $authorized_ips);
    }
    
    /**
     * Check if cookie is valid
     * Enhanced for mobile compatibility
     */
    public function is_cookie_valid() {
        $token = get_option('wp_visitor_stats_cookie_token', '');
        if (empty($token)) {
            return false;
        }
        
        $cookie_value = null;
        
        // Method 1: Check $_COOKIE (standard)
        if (isset($_COOKIE['wp_visitor_stats_token'])) {
            $cookie_value = $_COOKIE['wp_visitor_stats_token'];
        }
        
        // Method 2: Check $_SERVER['HTTP_COOKIE'] header (for mobile browsers)
        if (empty($cookie_value) && isset($_SERVER['HTTP_COOKIE'])) {
            $cookie_parts = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookie_parts as $cookie_part) {
                $cookie_part = trim($cookie_part);
                // Try both with and without URL encoding
                if (strpos($cookie_part, 'wp_visitor_stats_token=') === 0) {
                    $cookie_value = substr($cookie_part, strlen('wp_visitor_stats_token='));
                    // Try URL decode
                    $cookie_value = urldecode($cookie_value);
                    break;
                }
            }
        }
        
        // Method 3: Check $_REQUEST (some mobile browsers put cookies here)
        if (empty($cookie_value) && isset($_REQUEST['wp_visitor_stats_token'])) {
            $cookie_value = sanitize_text_field($_REQUEST['wp_visitor_stats_token']);
        }
        
        // Method 4: Check all $_COOKIE keys (case-insensitive search)
        if (empty($cookie_value)) {
            foreach ($_COOKIE as $key => $value) {
                if (strtolower($key) === 'wp_visitor_stats_token') {
                    $cookie_value = $value;
                    break;
                }
            }
        }
        
        // Validate cookie value
        if (!empty($cookie_value)) {
            // Trim whitespace and compare
            $cookie_value = trim($cookie_value);
            if ($cookie_value === $token) {
                return true;
            }
            // Also try URL decoded version
            $decoded = urldecode($cookie_value);
            if ($decoded === $token) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if user can see stats
     */
    public function can_see_stats() {
        $ip_address = $this->get_user_ip();
        
        // Check IP authorization
        if ($this->is_ip_authorized($ip_address)) {
            return true;
        }
        
        // Check cookie
        if ($this->is_cookie_valid()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get user IP address
     */
    public function get_user_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip);
                    $ip = trim($ip[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
    
    /**
     * Get excluded IPs
     */
    public function get_excluded_ips() {
        return get_option('wp_visitor_stats_excluded_ips', array());
    }
    
    /**
     * Check if IP is excluded
     */
    public function is_ip_excluded($ip_address) {
        $excluded_ips = $this->get_excluded_ips();
        return in_array($ip_address, $excluded_ips);
    }
    
    /**
     * Get unique visitor count (one IP = one visitor)
     */
    public function get_unique_visitor_count($period = 'today') {
        global $wpdb;
        
        $excluded_ips = $this->get_excluded_ips();
        $excluded_ips_placeholders = implode(',', array_fill(0, count($excluded_ips), '%s'));
        
        $where = '';
        switch ($period) {
            case 'today':
                $where = "visit_date = CURDATE()";
                break;
            case 'week':
                $where = "visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $where = "visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
            case 'year':
                $where = "visit_date >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
                break;
            default:
                $where = "1=1";
        }
        
        $excluded_condition = '';
        if (!empty($excluded_ips)) {
            $excluded_condition = "AND ip_address NOT IN ($excluded_ips_placeholders)";
        }
        
        $query = "SELECT COUNT(DISTINCT ip_address) FROM {$this->table_visits} WHERE $where $excluded_condition";
        
        // Prepare query if we have excluded IPs
        if (!empty($excluded_ips)) {
            $prepared = $wpdb->prepare($query, $excluded_ips);
            return (int) $wpdb->get_var($prepared);
        }
        
        return (int) $wpdb->get_var($query);
    }
}

