<?php
/**
 * Test script to check if stats HTML is generated correctly
 * Access via: https://loranrix.com/wp-content/plugins/wp-visitor-stats-3/test-mobile-display.php
 */

// Load WordPress
require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Unauthorized');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Display Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { color: #46b450; font-weight: bold; }
        .error { color: #dc3232; font-weight: bold; }
        .info { color: #2271b1; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .stats-preview { border: 2px solid #2271b1; padding: 20px; margin: 20px 0; background: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Mobile Display Test</h1>
    
    <?php
    require_once('includes/class-wp-visitor-stats-db.php');
    require_once('includes/class-wp-visitor-stats-display.php');
    
    $db = new WP_Visitor_Stats_DB();
    $display = new WP_Visitor_Stats_Display($db);
    
    // Test 1: Check settings
    echo '<div class="section">';
    echo '<h2>Test 1: Plugin Settings</h2>';
    $enabled = get_option('wp_visitor_stats_enabled', true);
    $track_all = get_option('wp_visitor_stats_track_all', false);
    $position = get_option('wp_visitor_stats_position', 'footer');
    
    echo '<p>Enabled: <strong>' . ($enabled ? 'YES' : 'NO') . '</strong></p>';
    echo '<p>Track All: <strong>' . ($track_all ? 'YES' : 'NO') . '</strong></p>';
    echo '<p>Position: <strong>' . esc_html($position) . '</strong></p>';
    echo '</div>';
    
    // Test 2: Check if can_see_stats
    echo '<div class="section">';
    echo '<h2>Test 2: Permission Check</h2>';
    $can_see = $db->can_see_stats();
    echo '<p>can_see_stats(): <strong>' . ($can_see ? 'TRUE' : 'FALSE') . '</strong></p>';
    echo '</div>';
    
    // Test 3: Get stats HTML
    echo '<div class="section">';
    echo '<h2>Test 3: Stats HTML Generation</h2>';
    
    // Use reflection to access private method
    $reflection = new ReflectionClass($display);
    $method = $reflection->getMethod('get_stats_html');
    $method->setAccessible(true);
    $html = $method->invoke($display);
    
    if (empty($html)) {
        echo '<p class="error">✗ get_stats_html() returned EMPTY string</p>';
        echo '<p>This means stats will NOT be displayed.</p>';
    } else {
        echo '<p class="success">✓ get_stats_html() returned HTML (' . strlen($html) . ' characters)</p>';
        echo '<h3>Generated HTML:</h3>';
        echo '<div class="stats-preview">' . $html . '</div>';
        echo '<h3>Raw HTML:</h3>';
        echo '<pre>' . esc_html($html) . '</pre>';
    }
    echo '</div>';
    
    // Test 4: Check if stats data is available
    echo '<div class="section">';
    echo '<h2>Test 4: Stats Data</h2>';
    $stats_method = $reflection->getMethod('get_stats_data');
    $stats_method->setAccessible(true);
    $stats = $stats_method->invoke($display);
    echo '<pre>' . print_r($stats, true) . '</pre>';
    echo '</div>';
    
    // Test 5: Simulate footer output
    echo '<div class="section">';
    echo '<h2>Test 5: Simulated Footer Output</h2>';
    echo '<p>This is what would be output in wp_footer:</p>';
    echo '<div style="border: 2px dashed #666; padding: 20px; margin: 20px 0; background: #fff;">';
    $display->output_stats_footer();
    echo '</div>';
    echo '</div>';
    
    // Test 6: Check CSS
    echo '<div class="section">';
    echo '<h2>Test 6: CSS Styles</h2>';
    $format = get_option('wp_visitor_stats_format', 'compact');
    $color_scheme = get_option('wp_visitor_stats_color', 'blue');
    $styles_file = WP_VISITOR_STATS_PLUGIN_DIR . 'templates/styles.php';
    if (file_exists($styles_file)) {
        echo '<p class="success">✓ Styles file exists</p>';
        echo '<p>Format: ' . esc_html($format) . '</p>';
        echo '<p>Color: ' . esc_html($color_scheme) . '</p>';
    } else {
        echo '<p class="error">✗ Styles file NOT found</p>';
    }
    echo '</div>';
    ?>
    
    <div class="section">
        <h2>Summary</h2>
        <p><strong>For stats to display:</strong></p>
        <ol>
            <li>Plugin must be enabled ✓</li>
            <li>Either track_all enabled OR can_see_stats() returns true</li>
            <li>get_stats_html() must return non-empty HTML</li>
            <li>CSS styles must be loaded</li>
            <li>JavaScript must not hide the wrapper</li>
        </ol>
    </div>
</body>
</html>

