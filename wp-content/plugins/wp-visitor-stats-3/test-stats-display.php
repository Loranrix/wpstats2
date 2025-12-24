<?php
/**
 * Test stats display - verify everything works
 */
require_once('../../../wp-load.php');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Stats Display Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .test { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { color: #46b450; font-weight: bold; }
        .error { color: #dc3232; font-weight: bold; }
        .info { color: #2271b1; }
    </style>
</head>
<body>
    <h1>Stats Display Test</h1>
    
    <?php
    require_once('includes/class-wp-visitor-stats-db.php');
    require_once('includes/class-wp-visitor-stats-display.php');
    
    $db = new WP_Visitor_Stats_DB();
    $display = new WP_Visitor_Stats_Display($db);
    
    // Test 1: Settings
    echo '<div class="test">';
    echo '<h2>Test 1: Plugin Settings</h2>';
    $enabled = get_option('wp_visitor_stats_enabled', true);
    $track_all = get_option('wp_visitor_stats_track_all', false);
    
    echo '<p>Enabled: <strong>' . ($enabled ? '<span class="success">YES</span>' : '<span class="error">NO</span>') . '</strong></p>';
    echo '<p>Track All: <strong>' . ($track_all ? '<span class="success">YES</span>' : '<span class="info">NO</span>') . '</strong></p>';
    echo '</div>';
    
    // Test 2: Permissions
    echo '<div class="test">';
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
    echo '<div class="test">';
    echo '<h2>Test 3: Should Display Logic</h2>';
    $should_show = false;
    if ($track_all === true || $track_all === '1' || $track_all === 1) {
        $should_show = true;
        echo '<p class="success">✓ track_all is enabled - stats should display to everyone</p>';
    } elseif ($track_all === false || $track_all === '0' || $track_all === 0) {
        $should_show = $can_see;
        if ($should_show) {
            echo '<p class="success">✓ track_all is disabled but user has permissions - stats should display</p>';
        } else {
            echo '<p class="error">✗ track_all is disabled and user has NO permissions - stats will NOT display</p>';
        }
    } else {
        $should_show = true;
        echo '<p class="info">ℹ track_all is not set (null) - defaulting to show to everyone (backward compatibility)</p>';
    }
    echo '<p><strong>Should Show: ' . ($should_show ? '<span class="success">YES</span>' : '<span class="error">NO</span>') . '</strong></p>';
    echo '</div>';
    
    // Test 4: Stats HTML Generation
    echo '<div class="test">';
    echo '<h2>Test 4: Stats HTML Generation</h2>';
    
    $html = $display->get_stats_html();
    
    if (empty($html)) {
        echo '<p class="error">✗ get_stats_html() returned EMPTY string</p>';
        echo '<p>This means stats will NOT be displayed on the frontend.</p>';
    } else {
        echo '<p class="success">✓ get_stats_html() returned HTML (' . strlen($html) . ' characters)</p>';
        echo '<h3>Generated HTML:</h3>';
        echo '<div style="border: 2px solid #2271b1; padding: 20px; background: #f9f9f9;">' . $html . '</div>';
    }
    echo '</div>';
    
    // Test 5: Protocol Check
    echo '<div class="test">';
    echo '<h2>Test 5: HTTPS Protocol</h2>';
    $is_https = is_ssl() || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    echo '<p>Current Protocol: <strong>' . ($is_https ? '<span class="success">HTTPS</span>' : '<span class="error">HTTP</span>') . '</strong></p>';
    echo '<p>Current URL: <strong>' . esc_html($current_url) . '</strong></p>';
    echo '<p>Home URL: <strong>' . esc_html(home_url()) . '</strong></p>';
    echo '<p>Site URL: <strong>' . esc_html(site_url()) . '</strong></p>';
    echo '</div>';
    
    // Final Summary
    echo '<div class="test">';
    echo '<h2>Final Summary</h2>';
    
    $all_ok = true;
    $issues = array();
    
    if (!$enabled) {
        $all_ok = false;
        $issues[] = 'Plugin is disabled';
    }
    
    if (!$should_show && !$track_all) {
        $all_ok = false;
        $issues[] = 'User does not have permissions and track_all is disabled';
    }
    
    if (empty($html)) {
        $all_ok = false;
        $issues[] = 'get_stats_html() returned empty string';
    }
    
    if (!$is_https) {
        $all_ok = false;
        $issues[] = 'Site is not using HTTPS';
    }
    
    if ($all_ok) {
        echo '<p class="success" style="font-size: 18px;">✓ ALL TESTS PASSED - Stats should display correctly!</p>';
        echo '<p><strong>Expected behavior:</strong></p>';
        echo '<ul>';
        echo '<li>Stats should be visible on the website</li>';
        echo '<li>Stats should work on both PC and mobile</li>';
        echo '<li>Site is using HTTPS</li>';
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

