<?php
/**
 * Test script to debug mobile cookie issues
 * Access via: https://loranrix.com/wp-content/plugins/wp-visitor-stats-3/test-mobile-cookie.php
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
    <title>Mobile Cookie Debug Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { color: #46b450; font-weight: bold; }
        .error { color: #dc3232; font-weight: bold; }
        .info { color: #2271b1; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto; }
        code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Mobile Cookie Debug Test</h1>
    
    <?php
    require_once('includes/class-wp-visitor-stats-db.php');
    $db = new WP_Visitor_Stats_DB();
    
    // Test 1: Check if cookie token is set in options
    echo '<div class="section">';
    echo '<h2>Test 1: Cookie Token in Database</h2>';
    $saved_token = get_option('wp_visitor_stats_cookie_token', '');
    if (!empty($saved_token)) {
        echo '<p class="success">✓ Cookie token exists in database</p>';
        echo '<p><code>' . esc_html(substr($saved_token, 0, 20)) . '...</code></p>';
    } else {
        echo '<p class="error">✗ Cookie token NOT found in database</p>';
        echo '<p>You need to generate a cookie access link in the plugin settings.</p>';
    }
    echo '</div>';
    
    // Test 2: Check $_COOKIE
    echo '<div class="section">';
    echo '<h2>Test 2: $_COOKIE Array</h2>';
    if (isset($_COOKIE['wp_visitor_stats_token'])) {
        echo '<p class="success">✓ Cookie found in $_COOKIE</p>';
        echo '<p>Value: <code>' . esc_html($_COOKIE['wp_visitor_stats_token']) . '</code></p>';
        echo '<p>Matches saved token: ' . ($_COOKIE['wp_visitor_stats_token'] === $saved_token ? '<span class="success">YES</span>' : '<span class="error">NO</span>') . '</p>';
    } else {
        echo '<p class="error">✗ Cookie NOT found in $_COOKIE</p>';
    }
    echo '<pre>$_COOKIE = ' . print_r($_COOKIE, true) . '</pre>';
    echo '</div>';
    
    // Test 3: Check $_SERVER['HTTP_COOKIE']
    echo '<div class="section">';
    echo '<h2>Test 3: $_SERVER[\'HTTP_COOKIE\'] Header</h2>';
    if (isset($_SERVER['HTTP_COOKIE'])) {
        echo '<p class="info">✓ HTTP_COOKIE header exists</p>';
        echo '<pre>' . esc_html($_SERVER['HTTP_COOKIE']) . '</pre>';
        
        // Parse manually
        $cookie_parts = explode(';', $_SERVER['HTTP_COOKIE']);
        $found_token = false;
        foreach ($cookie_parts as $cookie_part) {
            $cookie_part = trim($cookie_part);
            if (strpos($cookie_part, 'wp_visitor_stats_token=') === 0) {
                $cookie_value = urldecode(substr($cookie_part, strlen('wp_visitor_stats_token=')));
                echo '<p class="success">✓ Found wp_visitor_stats_token in header</p>';
                echo '<p>Value: <code>' . esc_html($cookie_value) . '</code></p>';
                echo '<p>Matches saved token: ' . ($cookie_value === $saved_token ? '<span class="success">YES</span>' : '<span class="error">NO</span>') . '</p>';
                $found_token = true;
                break;
            }
        }
        if (!$found_token) {
            echo '<p class="error">✗ wp_visitor_stats_token NOT found in HTTP_COOKIE header</p>';
        }
    } else {
        echo '<p class="error">✗ HTTP_COOKIE header NOT found</p>';
    }
    echo '</div>';
    
    // Test 4: Test is_cookie_valid()
    echo '<div class="section">';
    echo '<h2>Test 4: is_cookie_valid() Method</h2>';
    $is_valid = $db->is_cookie_valid();
    if ($is_valid) {
        echo '<p class="success">✓ is_cookie_valid() returns TRUE</p>';
    } else {
        echo '<p class="error">✗ is_cookie_valid() returns FALSE</p>';
    }
    echo '</div>';
    
    // Test 5: Test can_see_stats()
    echo '<div class="section">';
    echo '<h2>Test 5: can_see_stats() Method</h2>';
    $can_see = $db->can_see_stats();
    if ($can_see) {
        echo '<p class="success">✓ can_see_stats() returns TRUE</p>';
    } else {
        echo '<p class="error">✗ can_see_stats() returns FALSE</p>';
        echo '<p>This means stats will NOT be displayed.</p>';
    }
    echo '</div>';
    
    // Test 6: Check track_all setting
    echo '<div class="section">';
    echo '<h2>Test 6: track_all Setting</h2>';
    $track_all = get_option('wp_visitor_stats_track_all', false);
    echo '<p>track_all value: <code>' . var_export($track_all, true) . '</code></p>';
    if ($track_all === true || $track_all === '1' || $track_all === 1) {
        echo '<p class="info">ℹ track_all is enabled - stats should show to everyone</p>';
    } else {
        echo '<p class="info">ℹ track_all is disabled - stats require cookie or IP authorization</p>';
    }
    echo '</div>';
    
    // Test 7: Check enabled setting
    echo '<div class="section">';
    echo '<h2>Test 7: Plugin Enabled Setting</h2>';
    $enabled = get_option('wp_visitor_stats_enabled', true);
    echo '<p>enabled value: <code>' . var_export($enabled, true) . '</code></p>';
    if ($enabled === false || $enabled === '0' || $enabled === 0) {
        echo '<p class="error">✗ Plugin is DISABLED - stats will NOT show</p>';
    } else {
        echo '<p class="success">✓ Plugin is ENABLED</p>';
    }
    echo '</div>';
    
    // Test 8: JavaScript cookie check
    echo '<div class="section">';
    echo '<h2>Test 8: JavaScript Cookie Check</h2>';
    echo '<p>Check the browser console for JavaScript cookie information.</p>';
    echo '<div id="js-cookie-result"></div>';
    ?>
    <script>
    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length === 2) return parts.pop().split(";").shift();
        return null;
    }
    
    var cookieValue = getCookie('wp_visitor_stats_token');
    var resultDiv = document.getElementById('js-cookie-result');
    
    if (cookieValue) {
        resultDiv.innerHTML = '<p class="success">✓ Cookie found via JavaScript: <code>' + cookieValue + '</code></p>';
    } else {
        resultDiv.innerHTML = '<p class="error">✗ Cookie NOT found via JavaScript</p>';
    }
    
    console.log('All cookies:', document.cookie);
    console.log('wp_visitor_stats_token:', cookieValue);
    </script>
    <?php
    echo '</div>';
    
    // Test 9: Generate test cookie link
    echo '<div class="section">';
    echo '<h2>Test 9: Generate Cookie Access Link</h2>';
    if (!empty($saved_token)) {
        $cookie_link = home_url('/?wp_visitor_stats_token=' . urlencode($saved_token));
        echo '<p>Cookie Access Link:</p>';
        echo '<p><a href="' . esc_url($cookie_link) . '" target="_blank" style="background: #2271b1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Click to Set Cookie</a></p>';
        echo '<p><small>After clicking, refresh this page to see if cookie is set.</small></p>';
    } else {
        echo '<p class="error">Cannot generate link - no token in database</p>';
    }
    echo '</div>';
    ?>
    
    <div class="section">
        <h2>Summary</h2>
        <p><strong>For stats to display on mobile:</strong></p>
        <ol>
            <li>Plugin must be enabled</li>
            <li>Either track_all must be enabled, OR cookie must be valid, OR IP must be authorized</li>
            <li>Cookie must be readable by PHP (either via $_COOKIE or HTTP_COOKIE header)</li>
        </ol>
    </div>
</body>
</html>

