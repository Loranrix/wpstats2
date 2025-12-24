<?php
/**
 * Fix stats after HTTPS migration
 * Run once then delete
 */
require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Unauthorized');
}

// Check current settings
$enabled = get_option('wp_visitor_stats_enabled', true);
$track_all = get_option('wp_visitor_stats_track_all', false);

echo "<h1>Fix Stats After HTTPS</h1>";
echo "<p>Current settings:</p>";
echo "<ul>";
echo "<li>Enabled: " . ($enabled ? 'YES' : 'NO') . "</li>";
echo "<li>Track All: " . ($track_all ? 'YES' : 'NO') . "</li>";
echo "</ul>";

// Option 1: Enable track_all temporarily
if (isset($_GET['enable_track_all'])) {
    update_option('wp_visitor_stats_track_all', true);
    echo "<p style='color: green;'><strong>Track All enabled! Stats should now be visible to everyone.</strong></p>";
    echo "<p><a href='?'>Refresh</a></p>";
    exit;
}

// Option 2: Regenerate cookie token
if (isset($_GET['regenerate_token'])) {
    $new_token = wp_generate_password(32, false);
    update_option('wp_visitor_stats_cookie_token', $new_token);
    $cookie_link = add_query_arg('wp_visitor_stats_token', $new_token, home_url());
    echo "<p style='color: green;'><strong>New cookie token generated!</strong></p>";
    echo "<p><strong>New Cookie Link:</strong></p>";
    echo "<p><a href='" . esc_url($cookie_link) . "' target='_blank'>" . esc_html($cookie_link) . "</a></p>";
    echo "<p><a href='?'>Refresh</a></p>";
    exit;
}

// Show options
echo "<h2>Options:</h2>";
echo "<ol>";
echo "<li><a href='?enable_track_all=1'><strong>Enable Track All</strong> - Show stats to everyone (temporary solution)</a></li>";
echo "<li><a href='?regenerate_token=1'><strong>Regenerate Cookie Token</strong> - Create new cookie link for HTTPS</a></li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>Note:</strong> After HTTPS migration, cookies set in HTTP are not accessible in HTTPS.</p>";
echo "<p>You need to either:</p>";
echo "<ul>";
echo "<li>Enable 'Track All' to show stats to everyone, OR</li>";
echo "<li>Regenerate the cookie token and use the new link in HTTPS</li>";
echo "</ul>";

