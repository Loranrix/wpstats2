<?php
/**
 * Simple check - verify stats display logic
 */
require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Unauthorized');
}

header('Content-Type: text/plain; charset=utf-8');

require_once('includes/class-wp-visitor-stats-db.php');
require_once('includes/class-wp-visitor-stats-display.php');

$db = new WP_Visitor_Stats_DB();
$display = new WP_Visitor_Stats_Display($db);

echo "=== SIMPLE CHECK ===\n\n";

$enabled = get_option('wp_visitor_stats_enabled', true);
$track_all = get_option('wp_visitor_stats_track_all', false);

echo "Enabled: " . ($enabled ? 'YES' : 'NO') . "\n";
echo "Track All: " . ($track_all ? 'YES' : 'NO') . "\n";
echo "Can See Stats: " . ($db->can_see_stats() ? 'YES' : 'NO') . "\n\n";

$html = $display->get_stats_html();

if (empty($html)) {
    echo "ERROR: get_stats_html() returned EMPTY\n";
    echo "Stats will NOT display!\n";
} else {
    echo "SUCCESS: get_stats_html() returned HTML (" . strlen($html) . " chars)\n";
    echo "Stats SHOULD display!\n\n";
    echo "HTML Preview:\n";
    echo substr($html, 0, 200) . "...\n";
}

