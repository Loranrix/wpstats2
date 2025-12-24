<?php
/**
 * Verification script - Tests all functionality
 */
$wp_load = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
if (!file_exists($wp_load)) {
    die('WordPress not found');
}
require_once($wp_load);

if (!class_exists('WP_Visitor_Stats')) {
    die('Plugin not active');
}

$plugin = WP_Visitor_Stats::get_instance();
$results = array();

// Test 1: Plugin version
$results['version'] = defined('WP_VISITOR_STATS_VERSION') ? WP_VISITOR_STATS_VERSION : 'unknown';

// Test 2: Options
$results['enabled'] = get_option('wp_visitor_stats_enabled', true);
$results['track_all'] = get_option('wp_visitor_stats_track_all', false);

// Test 3: Stats display
try {
    $html = $plugin->display->get_stats_html();
    $results['display_html'] = !empty($html);
} catch (Exception $e) {
    $results['display_html'] = false;
    $results['display_error'] = $e->getMessage();
}

// Test 4: Geolocation
try {
    $geo = new WP_Visitor_Stats_Geolocation($plugin->db);
    $countries = $geo->get_visitors_by_country('all');
    $cities = $geo->get_visitors_by_city('all');
    $results['geolocation'] = true;
} catch (Exception $e) {
    $results['geolocation'] = false;
    $results['geo_error'] = $e->getMessage();
}

// Test 5: Maintenance
$results['maintenance_class'] = isset($plugin->maintenance) && is_object($plugin->maintenance);

// Test 6: Auto-update
$plugin_file = plugin_basename(WP_VISITOR_STATS_PLUGIN_FILE);
$auto_updates = get_site_option('auto_update_plugins', array());
$results['auto_update_enabled'] = in_array($plugin_file, $auto_updates);

header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);

