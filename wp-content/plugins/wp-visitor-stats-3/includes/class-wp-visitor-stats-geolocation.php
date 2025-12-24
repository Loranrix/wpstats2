<?php
/**
 * Geolocation functionality for WP Visitor Stats
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Visitor_Stats_Geolocation {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get location data for IP address
     */
    public function get_location($ip_address) {
        global $wpdb;
        
        // Check if already in database
        $cached = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->db->table_locations} WHERE ip_address = %s",
            $ip_address
        ));
        
        // Return cached if less than 7 days old
        if ($cached && strtotime($cached->last_updated) > (time() - 7 * 24 * 60 * 60)) {
            return $cached;
        }
        
        // Fetch from API
        $location_data = $this->fetch_from_api($ip_address);
        
        if ($location_data) {
            $this->save_location($ip_address, $location_data);
            return $location_data;
        }
        
        return $cached; // Return old data if API fails
    }
    
    /**
     * Fetch location from API
     */
    private function fetch_from_api($ip_address) {
        // Skip private/local IPs
        if (!filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }
        
        // Try ip-api.com (free, no key required)
        $url = "http://ip-api.com/json/{$ip_address}?fields=status,message,country,countryCode,city,region,lat,lon,timezone";
        
        $response = wp_remote_get($url, array(
            'timeout' => 5,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            return null;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (isset($data['status']) && $data['status'] === 'success') {
            return array(
                'country_code' => isset($data['countryCode']) ? strtoupper($data['countryCode']) : '',
                'country_name' => isset($data['country']) ? $data['country'] : '',
                'city' => isset($data['city']) ? $data['city'] : '',
                'region' => isset($data['regionName']) ? $data['regionName'] : '',
                'latitude' => isset($data['lat']) ? $data['lat'] : null,
                'longitude' => isset($data['lon']) ? $data['lon'] : null,
                'timezone' => isset($data['timezone']) ? $data['timezone'] : ''
            );
        }
        
        return null;
    }
    
    /**
     * Save location to database
     */
    private function save_location($ip_address, $location_data) {
        global $wpdb;
        
        $now = current_time('mysql');
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->db->table_locations} WHERE ip_address = %s",
            $ip_address
        ));
        
        $data = array(
            'ip_address' => $ip_address,
            'country_code' => $location_data['country_code'],
            'country_name' => $location_data['country_name'],
            'city' => $location_data['city'],
            'region' => $location_data['region'],
            'latitude' => $location_data['latitude'],
            'longitude' => $location_data['longitude'],
            'timezone' => $location_data['timezone'],
            'last_updated' => $now
        );
        
        if ($existing) {
            $wpdb->update(
                $this->db->table_locations,
                $data,
                array('ip_address' => $ip_address),
                array('%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s'),
                array('%s')
            );
        } else {
            $wpdb->insert(
                $this->db->table_locations,
                $data,
                array('%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s')
            );
        }
    }
    
    /**
     * Get visitors by country
     */
    public function get_visitors_by_country($period = 'all') {
        global $wpdb;
        
        try {
            $excluded_ips = $this->db->get_excluded_ips();
            
            $where_date = '';
            if ($period === 'today') {
                $where_date = "AND v.visit_date = CURDATE()";
            } elseif ($period === 'week') {
                $where_date = "AND v.visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            } elseif ($period === 'month') {
                $where_date = "AND v.visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            } elseif ($period === 'year') {
                $where_date = "AND v.visit_date >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
            }
            
            $params = array();
            $excluded_condition = '';
            if (!empty($excluded_ips)) {
                $placeholders = implode(',', array_fill(0, count($excluded_ips), '%s'));
                $excluded_condition = "AND v.ip_address NOT IN ($placeholders)";
                $params = $excluded_ips;
            }
            
            $query = "SELECT 
                l.country_code,
                l.country_name,
                COUNT(DISTINCT v.ip_address) as unique_visitors,
                COUNT(v.id) as total_visits
            FROM {$this->db->table_visits} v
            LEFT JOIN {$this->db->table_locations} l ON v.ip_address = l.ip_address
            WHERE l.country_code IS NOT NULL AND l.country_code != '' $where_date $excluded_condition
            GROUP BY l.country_code, l.country_name
            ORDER BY unique_visitors DESC";
            
            if (!empty($params)) {
                $prepared = $wpdb->prepare($query, $params);
            } else {
                $prepared = $query;
            }
            
            $results = $wpdb->get_results($prepared);
            return $results ? $results : array();
        } catch (Exception $e) {
            error_log('WP Visitor Stats - Error in get_visitors_by_country: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Get visitors by city
     */
    public function get_visitors_by_city($period = 'all', $country_code = '') {
        global $wpdb;
        
        try {
            $excluded_ips = $this->db->get_excluded_ips();
            
            $where_date = '';
            if ($period === 'today') {
                $where_date = "AND v.visit_date = CURDATE()";
            } elseif ($period === 'week') {
                $where_date = "AND v.visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            } elseif ($period === 'month') {
                $where_date = "AND v.visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            } elseif ($period === 'year') {
                $where_date = "AND v.visit_date >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
            }
            
            $params = array();
            $query_parts = array();
            
            if (!empty($country_code)) {
                $query_parts[] = "l.country_code = %s";
                $params[] = $country_code;
            }
            
            if (!empty($excluded_ips)) {
                $placeholders = implode(',', array_fill(0, count($excluded_ips), '%s'));
                $query_parts[] = "v.ip_address NOT IN ($placeholders)";
                $params = array_merge($params, $excluded_ips);
            }
            
            $where_additional = '';
            if (!empty($query_parts)) {
                $where_additional = 'AND ' . implode(' AND ', $query_parts);
            }
            
            $query = "SELECT 
                l.country_code,
                l.country_name,
                l.city,
                COUNT(DISTINCT v.ip_address) as unique_visitors,
                COUNT(v.id) as total_visits
            FROM {$this->db->table_visits} v
            LEFT JOIN {$this->db->table_locations} l ON v.ip_address = l.ip_address
            WHERE l.city IS NOT NULL AND l.city != '' $where_date $where_additional
            GROUP BY l.country_code, l.country_name, l.city
            ORDER BY unique_visitors DESC
            LIMIT 100";
            
            if (!empty($params)) {
                $prepared = $wpdb->prepare($query, $params);
            } else {
                $prepared = $query;
            }
            
            $results = $wpdb->get_results($prepared);
            return $results ? $results : array();
        } catch (Exception $e) {
            error_log('WP Visitor Stats - Error in get_visitors_by_city: ' . $e->getMessage());
            return array();
        }
    }
}

