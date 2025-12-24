<?php
/**
 * Final comprehensive test
 */
$wp_load = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
require_once($wp_load);

if (!class_exists('WP_Visitor_Stats')) {
    die(json_encode(array('error' => 'Plugin not active')));
}

$plugin = WP_Visitor_Stats::get_instance();
$test_results = array('all_passed' => true, 'tests' => array());

// Test 1: Display stats HTML
try {
    $html = $plugin->display->get_stats_html();
    $test_results['tests']['display_html'] = array(
        'passed' => !empty($html),
        'html_length' => strlen($html)
    );
    if (empty($html)) $test_results['all_passed'] = false;
} catch (Exception $e) {
    $test_results['tests']['display_html'] = array('passed' => false, 'error' => $e->getMessage());
    $test_results['all_passed'] = false;
}

// Test 2: Geolocation By Country
try {
    $geo = new WP_Visitor_Stats_Geolocation($plugin->db);
    $countries = $geo->get_visitors_by_country('all');
    $test_results['tests']['geolocation_country'] = array(
        'passed' => is_array($countries),
        'count' => count($countries)
    );
} catch (Exception $e) {
    $test_results['tests']['geolocation_country'] = array('passed' => false, 'error' => $e->getMessage());
    $test_results['all_passed'] = false;
}

// Test 3: Geolocation By City
try {
    $geo = new WP_Visitor_Stats_Geolocation($plugin->db);
    $cities = $geo->get_visitors_by_city('all');
    $test_results['tests']['geolocation_city'] = array(
        'passed' => is_array($cities),
        'count' => count($cities)
    );
} catch (Exception $e) {
    $test_results['tests']['geolocation_city'] = array('passed' => false, 'error' => $e->getMessage());
    $test_results['all_passed'] = false;
}

// Test 4: Maintenance class
$test_results['tests']['maintenance'] = array(
    'passed' => isset($plugin->maintenance) && is_object($plugin->maintenance)
);
if (!$test_results['tests']['maintenance']['passed']) $test_results['all_passed'] = false;

// Test 5: Auto-update
$plugin_file = plugin_basename(WP_VISITOR_STATS_PLUGIN_FILE);
$auto_updates = get_site_option('auto_update_plugins', array());
$test_results['tests']['auto_update'] = array(
    'passed' => in_array($plugin_file, $auto_updates),
    'plugin_file' => $plugin_file,
    'in_list' => in_array($plugin_file, $auto_updates)
);

// Test 6: Dashboard widget
$test_results['tests']['dashboard'] = array(
    'passed' => isset($plugin->dashboard) && is_object($plugin->dashboard)
);

header('Content-Type: application/json');
echo json_encode($test_results, JSON_PRETTY_PRINT);

