<?php
/**
 * Force display test - verify stats appear in footer
 */
define('WP_USE_THEMES', true);
require_once('../../../wp-load.php');

// Force clear any caches
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
}

// Check if plugin is active
if (!is_plugin_active('wp-visitor-stats-3/wp-visitor-stats.php')) {
    die('Plugin not active');
}

// Get stats HTML directly
require_once('includes/class-wp-visitor-stats-db.php');
require_once('includes/class-wp-visitor-stats-display.php');

$db = new WP_Visitor_Stats_DB();
$display = new WP_Visitor_Stats_Display($db);

$html = $display->get_stats_html();

echo "=== FORCE DISPLAY TEST ===\n\n";
echo "HTML Generated: " . (empty($html) ? "NO" : "YES (" . strlen($html) . " chars)") . "\n";
echo "HTML Preview: " . substr($html, 0, 200) . "...\n\n";

// Test footer hook
ob_start();
do_action('wp_footer');
$footer_output = ob_get_clean();

echo "Footer Hook Called: YES\n";
echo "Stats in Footer: " . (strpos($footer_output, 'wp-visitor-stats') !== false ? "YES" : "NO") . "\n";
if (strpos($footer_output, 'wp-visitor-stats') !== false) {
    echo "Footer Stats Preview: " . substr($footer_output, strpos($footer_output, 'wp-visitor-stats'), 200) . "...\n";
}

