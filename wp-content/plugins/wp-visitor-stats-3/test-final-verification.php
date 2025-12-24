<?php
/**
 * Final verification test - Check if stats display correctly
 * Access via: https://loranrix.com/wp-content/plugins/wp-visitor-stats-3/test-final-verification.php
 */

// Load WordPress
require_once('../../../wp-load.php');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Verification Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { color: #46b450; font-weight: bold; }
        .error { color: #dc3232; font-weight: bold; }
        .info { color: #2271b1; }
        .stats-preview { border: 2px solid #2271b1; padding: 20px; margin: 20px 0; background: #f9f9f9; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Final Verification Test - WP Visitor Stats</h1>
    
    <?php
    require_once('includes/class-wp-visitor-stats-db.php');
    require_once('includes/class-wp-visitor-stats-display.php');
    
    $db = new WP_Visitor_Stats_DB();
    $display = new WP_Visitor_Stats_Display($db);
    
    // Test 1: Settings
    echo '<div class="section">';
    echo '<h2>Test 1: Plugin Settings</h2>';
    $enabled = get_option('wp_visitor_stats_enabled', true);
    $track_all = get_option('wp_visitor_stats_track_all', false);
    $position = get_option('wp_visitor_stats_position', 'footer');
    
    echo '<p>Enabled: <strong>' . ($enabled ? '<span class="success">YES</span>' : '<span class="error">NO</span>') . '</strong></p>';
    echo '<p>Track All: <strong>' . ($track_all ? '<span class="success">YES</span>' : '<span class="info">NO</span>') . '</strong></p>';
    echo '<p>Position: <strong>' . esc_html($position) . '</strong></p>';
    echo '</div>';
    
    // Test 2: Permissions
    echo '<div class="section">';
    echo '<h2>Test 2: User Permissions</h2>';
    $can_see = $db->can_see_stats();
    $is_cookie_valid = $db->is_cookie_valid();
    $user_ip = $db->get_user_ip();
    $is_ip_authorized = $db->is_ip_authorized($user_ip);
    
    echo '<p>User IP: <strong>' . esc_html($user_ip) . '</strong></p>';
    echo '<p>IP Authorized: <strong>' . ($is_ip_authorized ? '<span class="success">YES</span>' : '<span class="error">NO</span>') . '</strong></p>';
    echo '<p>Cookie Valid: <strong>' . ($is_cookie_valid ? '<span class="success">YES</span>' : '<span class="error">NO</span>') . '</strong></p>';
    echo '<p>Can See Stats: <strong>' . ($can_see ? '<span class="success">YES</span>' : '<span class="error">NO</span>') . '</strong></p>';
    echo '</div>';
    
    // Test 3: Should Display Logic
    echo '<div class="section">';
    echo '<h2>Test 3: Should Display Logic</h2>';
    $should_display = false;
    if ($track_all === true || $track_all === '1' || $track_all === 1) {
        $should_display = true;
        echo '<p class="success">✓ track_all is enabled - stats should display to everyone</p>';
    } elseif ($track_all === false || $track_all === '0' || $track_all === 0) {
        $should_display = $can_see;
        if ($should_display) {
            echo '<p class="success">✓ track_all is disabled but user has permissions - stats should display</p>';
        } else {
            echo '<p class="error">✗ track_all is disabled and user has NO permissions - stats will NOT display</p>';
        }
    } else {
        $should_display = true;
        echo '<p class="info">ℹ track_all is not set (null) - defaulting to show to everyone (backward compatibility)</p>';
    }
    echo '</div>';
    
    // Test 4: Stats HTML Generation
    echo '<div class="section">';
    echo '<h2>Test 4: Stats HTML Generation</h2>';
    
    $html = $display->get_stats_html();
    
    if (empty($html)) {
        echo '<p class="error">✗ get_stats_html() returned EMPTY string</p>';
        echo '<p>This means stats will NOT be displayed on the frontend.</p>';
    } else {
        echo '<p class="success">✓ get_stats_html() returned HTML (' . strlen($html) . ' characters)</p>';
        echo '<h3>Generated HTML Preview:</h3>';
        echo '<div class="stats-preview">' . $html . '</div>';
    }
    echo '</div>';
    
    // Test 5: Stats Data
    echo '<div class="section">';
    echo '<h2>Test 5: Stats Data</h2>';
    $reflection = new ReflectionClass($display);
    $stats_method = $reflection->getMethod('get_stats_data');
    $stats_method->setAccessible(true);
    $stats = $stats_method->invoke($display);
    echo '<pre>' . print_r($stats, true) . '</pre>';
    echo '</div>';
    
    // Test 6: CSS Styles
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
    
    // Test 7: Template Files
    echo '<div class="section">';
    echo '<h2>Test 7: Template Files</h2>';
    $template_file = WP_VISITOR_STATS_PLUGIN_DIR . 'templates/display-' . $format . '.php';
    if (file_exists($template_file)) {
        echo '<p class="success">✓ Template file exists: ' . esc_html(basename($template_file)) . '</p>';
    } else {
        echo '<p class="error">✗ Template file NOT found: ' . esc_html($template_file) . '</p>';
    }
    echo '</div>';
    
    // Final Summary
    echo '<div class="section">';
    echo '<h2>Final Summary</h2>';
    
    $all_ok = true;
    $issues = array();
    
    if (!$enabled) {
        $all_ok = false;
        $issues[] = 'Plugin is disabled';
    }
    
    if (!$should_display && !$track_all) {
        $all_ok = false;
        $issues[] = 'User does not have permissions and track_all is disabled';
    }
    
    if (empty($html)) {
        $all_ok = false;
        $issues[] = 'get_stats_html() returned empty string';
    }
    
    if (!file_exists($styles_file)) {
        $all_ok = false;
        $issues[] = 'CSS styles file missing';
    }
    
    if (!file_exists($template_file)) {
        $all_ok = false;
        $issues[] = 'Template file missing';
    }
    
    if ($all_ok) {
        echo '<p class="success" style="font-size: 18px;">✓ ALL TESTS PASSED - Stats should display correctly!</p>';
        echo '<p><strong>Expected behavior:</strong></p>';
        echo '<ul>';
        echo '<li>Stats should be visible in the footer (or selected position)</li>';
        echo '<li>Stats should work on both PC and mobile</li>';
        if ($track_all) {
            echo '<li>Stats are visible to everyone (track_all enabled)</li>';
        } else {
            echo '<li>Stats are visible only to authorized users (IP or cookie)</li>';
        }
        echo '</ul>';
    } else {
        echo '<p class="error" style="font-size: 18px;">✗ ISSUES FOUND:</p>';
        echo '<ul>';
        foreach ($issues as $issue) {
            echo '<li>' . esc_html($issue) . '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
    ?>
</body>
</html>

