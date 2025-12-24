<?php
/**
 * Comprehensive test runner - Tests all functionality
 * Run via: https://loranrix.com/wp-content/plugins/wp-visitor-stats-3/run-all-tests.php
 */
define('WP_USE_THEMES', false);
$wp_load = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
if (!file_exists($wp_load)) {
    die(json_encode(array('error' => 'WordPress not found')));
}
require_once($wp_load);

if (!class_exists('WP_Visitor_Stats')) {
    die(json_encode(array('error' => 'Plugin not active')));
}

$plugin = WP_Visitor_Stats::get_instance();
$results = array('timestamp' => date('Y-m-d H:i:s'), 'all_passed' => true);

// Test 1: Plugin initialization
$results['plugin_init'] = array(
    'passed' => isset($plugin->display) && isset($plugin->admin) && isset($plugin->maintenance),
    'display' => isset($plugin->display),
    'admin' => isset($plugin->admin),
    'maintenance' => isset($plugin->maintenance)
);

// Test 2: Stats display
try {
    $html = $plugin->display->get_stats_html();
    $results['stats_display'] = array(
        'passed' => !empty($html),
        'html_length' => strlen($html),
        'has_content' => !empty($html)
    );
    if (empty($html)) $results['all_passed'] = false;
} catch (Exception $e) {
    $results['stats_display'] = array('passed' => false, 'error' => $e->getMessage());
    $results['all_passed'] = false;
}

// Test 3: Geolocation - By Country
try {
    $geo = new WP_Visitor_Stats_Geolocation($plugin->db);
    $countries = $geo->get_visitors_by_country('all');
    $results['geolocation_country'] = array(
        'passed' => is_array($countries),
        'count' => count($countries)
    );
} catch (Exception $e) {
    $results['geolocation_country'] = array('passed' => false, 'error' => $e->getMessage());
    $results['all_passed'] = false;
}

// Test 4: Geolocation - By City
try {
    $geo = new WP_Visitor_Stats_Geolocation($plugin->db);
    $cities = $geo->get_visitors_by_city('all');
    $results['geolocation_city'] = array(
        'passed' => is_array($cities),
        'count' => count($cities)
    );
} catch (Exception $e) {
    $results['geolocation_city'] = array('passed' => false, 'error' => $e->getMessage());
    $results['all_passed'] = false;
}

// Test 5: Auto-update
$plugin_file = plugin_basename(WP_VISITOR_STATS_PLUGIN_FILE);
$auto_updates = get_site_option('auto_update_plugins', array());
$results['auto_update'] = array(
    'passed' => in_array($plugin_file, $auto_updates),
    'plugin_file' => $plugin_file,
    'in_auto_update_list' => in_array($plugin_file, $auto_updates)
);

// Test 6: Options
$results['options'] = array(
    'enabled' => get_option('wp_visitor_stats_enabled', true),
    'track_all' => get_option('wp_visitor_stats_track_all', false),
    'position' => get_option('wp_visitor_stats_position', 'header_right')
);

// Test 7: Database tables
global $wpdb;
$tables = array(
    $wpdb->prefix . 'visitor_stats_visits',
    $wpdb->prefix . 'visitor_stats_online',
    $wpdb->prefix . 'visitor_stats_locations'
);
$tables_status = array();
foreach ($tables as $table) {
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
    $tables_status[$table] = $exists ? 'exists' : 'missing';
    if (!$exists) $results['all_passed'] = false;
}
$results['database_tables'] = $tables_status;

// Test 8: Shortcode
try {
    $shortcode_output = do_shortcode('[wp_visitor_stats]');
    $results['shortcode'] = array(
        'passed' => !empty($shortcode_output),
        'output_length' => strlen($shortcode_output)
    );
} catch (Exception $e) {
    $results['shortcode'] = array('passed' => false, 'error' => $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);


