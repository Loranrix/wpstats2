<?php
/**
 * Complete test script for WP Visitor Stats
 * Access via: https://loranrix.com/wp-content/plugins/wp-visitor-stats-3/test-complete.php
 */

// Load WordPress
$wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    $wp_load_path = dirname(dirname(dirname(__FILE__))) . '/wp-load.php';
}
if (!file_exists($wp_load_path)) {
    die('Could not find wp-load.php');
}
require_once($wp_load_path);

// Check if plugin is active
if (!class_exists('WP_Visitor_Stats')) {
    die('ERROR: Plugin WP Visitor Stats is not active');
}

$plugin = WP_Visitor_Stats::get_instance();
$db = $plugin->db;
$display = $plugin->display;
$admin = $plugin->admin;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>WP Visitor Stats - Complete Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-left: 4px solid #0073aa; }
        .success { border-left-color: #46b450; background: #f0f8f0; }
        .error { border-left-color: #dc3232; background: #fef0f0; }
        .warning { border-left-color: #ffb900; background: #fff8e5; }
        pre { background: #fff; padding: 10px; border: 1px solid #ddd; overflow-x: auto; }
        h2 { margin-top: 0; }
    </style>
</head>
<body>
    <h1>WP Visitor Stats - Complete Test Suite</h1>
    
    <?php
    $all_tests_passed = true;
    
    // TEST 1: Plugin loaded
    echo '<div class="test-section success">';
    echo '<h2>✓ Test 1: Plugin Loaded</h2>';
    echo '<p>Plugin is active and loaded correctly.</p>';
    echo '</div>';
    
    // TEST 2: Database tables
    echo '<div class="test-section">';
    echo '<h2>Test 2: Database Tables</h2>';
    global $wpdb;
    $tables = array(
        $wpdb->prefix . 'visitor_stats_visits',
        $wpdb->prefix . 'visitor_stats_online',
        $wpdb->prefix . 'visitor_stats_locations'
    );
    $tables_ok = true;
    foreach ($tables as $table) {
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
        $status = $exists ? '✓ EXISTS' : '✗ NOT EXISTS';
        $class = $exists ? 'success' : 'error';
        echo "<div class='$class'><strong>$table</strong>: $status</div>";
        if (!$exists) {
            $tables_ok = false;
            $all_tests_passed = false;
        }
    }
    echo '</div>';
    
    // TEST 3: Options
    echo '<div class="test-section">';
    echo '<h2>Test 3: Plugin Options</h2>';
    $enabled = get_option('wp_visitor_stats_enabled', true);
    $track_all = get_option('wp_visitor_stats_track_all', false);
    $position = get_option('wp_visitor_stats_position', 'header_right');
    echo '<pre>';
    echo "wp_visitor_stats_enabled: " . var_export($enabled, true) . "\n";
    echo "wp_visitor_stats_track_all: " . var_export($track_all, true) . "\n";
    echo "wp_visitor_stats_position: " . var_export($position, true) . "\n";
    echo '</pre>';
    if (!$enabled && !$track_all) {
        echo '<div class="warning"><strong>Warning:</strong> Stats are disabled and track_all is off. Stats may not display.</div>';
    }
    echo '</div>';
    
    // TEST 4: Permissions
    echo '<div class="test-section">';
    echo '<h2>Test 4: Permissions Check</h2>';
    $ip = $db->get_user_ip();
    $is_authorized = $db->is_ip_authorized($ip);
    $cookie_valid = $db->is_cookie_valid();
    $can_see = $db->can_see_stats();
    echo '<pre>';
    echo "IP Address: $ip\n";
    echo "Is IP Authorized: " . var_export($is_authorized, true) . "\n";
    echo "Is Cookie Valid: " . var_export($cookie_valid, true) . "\n";
    echo "Can See Stats: " . var_export($can_see, true) . "\n";
    echo '</pre>';
    if (!$can_see && !$track_all) {
        echo '<div class="warning"><strong>Warning:</strong> Current IP cannot see stats. Enable "Track All" or add IP to authorized list.</div>';
    }
    echo '</div>';
    
    // TEST 5: Stats Data
    echo '<div class="test-section">';
    echo '<h2>Test 5: Stats Data Retrieval</h2>';
    try {
        $stats = array(
            'today' => $db->get_visitor_count('today'),
            'week' => $db->get_visitor_count('week'),
            'month' => $db->get_visitor_count('month'),
            'year' => $db->get_visitor_count('year'),
            'online' => $db->get_online_count()
        );
        echo '<pre>';
        print_r($stats);
        echo '</pre>';
        echo '<div class="success">✓ Stats data retrieved successfully</div>';
    } catch (Exception $e) {
        echo '<div class="error">✗ Error: ' . esc_html($e->getMessage()) . '</div>';
        $all_tests_passed = false;
    }
    echo '</div>';
    
    // TEST 6: Stats Display HTML
    echo '<div class="test-section">';
    echo '<h2>Test 6: Stats Display HTML Generation</h2>';
    try {
        $html = $display->get_stats_html();
        if (!empty($html)) {
            echo '<div class="success">✓ Stats HTML generated successfully</div>';
            echo '<div style="border: 1px solid #ddd; padding: 10px; margin: 10px 0;">';
            echo '<strong>Generated HTML:</strong><br>';
            echo $html;
            echo '</div>';
        } else {
            echo '<div class="error">✗ Stats HTML is empty</div>';
            $all_tests_passed = false;
        }
    } catch (Exception $e) {
        echo '<div class="error">✗ Error: ' . esc_html($e->getMessage()) . '</div>';
        $all_tests_passed = false;
    }
    echo '</div>';
    
    // TEST 7: Geolocation - By Country
    echo '<div class="test-section">';
    echo '<h2>Test 7: Geolocation - By Country</h2>';
    try {
        require_once WP_VISITOR_STATS_PLUGIN_DIR . 'includes/class-wp-visitor-stats-geolocation.php';
        $geolocation = new WP_Visitor_Stats_Geolocation($db);
        $countries = $geolocation->get_visitors_by_country('all');
        echo '<div class="success">✓ Query executed successfully</div>';
        echo '<pre>Found ' . count($countries) . ' countries</pre>';
        if (count($countries) > 0) {
            echo '<pre>';
            print_r(array_slice($countries, 0, 5));
            echo '</pre>';
        }
    } catch (Exception $e) {
        echo '<div class="error">✗ Error: ' . esc_html($e->getMessage()) . '</div>';
        $all_tests_passed = false;
    }
    echo '</div>';
    
    // TEST 8: Geolocation - By City
    echo '<div class="test-section">';
    echo '<h2>Test 8: Geolocation - By City</h2>';
    try {
        require_once WP_VISITOR_STATS_PLUGIN_DIR . 'includes/class-wp-visitor-stats-geolocation.php';
        $geolocation = new WP_Visitor_Stats_Geolocation($db);
        $cities = $geolocation->get_visitors_by_city('all');
        echo '<div class="success">✓ Query executed successfully</div>';
        echo '<pre>Found ' . count($cities) . ' cities</pre>';
        if (count($cities) > 0) {
            echo '<pre>';
            print_r(array_slice($cities, 0, 5));
            echo '</pre>';
        }
    } catch (Exception $e) {
        echo '<div class="error">✗ Error: ' . esc_html($e->getMessage()) . '</div>';
        $all_tests_passed = false;
    }
    echo '</div>';
    
    // TEST 9: Maintenance class
    echo '<div class="test-section">';
    echo '<h2>Test 9: Maintenance Class</h2>';
    try {
        if (isset($plugin->maintenance) && is_object($plugin->maintenance)) {
            echo '<div class="success">✓ Maintenance class initialized</div>';
        } else {
            echo '<div class="error">✗ Maintenance class not initialized</div>';
            $all_tests_passed = false;
        }
    } catch (Exception $e) {
        echo '<div class="error">✗ Error: ' . esc_html($e->getMessage()) . '</div>';
        $all_tests_passed = false;
    }
    echo '</div>';
    
    // TEST 10: Shortcode
    echo '<div class="test-section">';
    echo '<h2>Test 10: Shortcode [wp_visitor_stats]</h2>';
    try {
        $shortcode_output = do_shortcode('[wp_visitor_stats]');
        if (!empty($shortcode_output)) {
            echo '<div class="success">✓ Shortcode works</div>';
            echo '<div style="border: 1px solid #ddd; padding: 10px; margin: 10px 0;">';
            echo $shortcode_output;
            echo '</div>';
        } else {
            echo '<div class="warning">⚠ Shortcode returns empty (may be normal if no stats)</div>';
        }
    } catch (Exception $e) {
        echo '<div class="error">✗ Error: ' . esc_html($e->getMessage()) . '</div>';
        $all_tests_passed = false;
    }
    echo '</div>';
    
    // Final summary
    echo '<div class="test-section ' . ($all_tests_passed ? 'success' : 'warning') . '">';
    echo '<h2>' . ($all_tests_passed ? '✓' : '⚠') . ' Test Summary</h2>';
    if ($all_tests_passed) {
        echo '<p><strong>All critical tests passed!</strong></p>';
    } else {
        echo '<p><strong>Some tests failed. Please check the errors above.</strong></p>';
    }
    echo '</div>';
    ?>
</body>
</html>

