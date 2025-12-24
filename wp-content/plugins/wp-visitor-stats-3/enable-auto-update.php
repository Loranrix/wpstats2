<?php
/**
 * Enable auto-update for WP Visitor Stats
 */
$wp_load = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
require_once($wp_load);

if (!class_exists('WP_Visitor_Stats')) {
    die('Plugin not active');
}

$plugin_file = 'wp-visitor-stats-3/wp-visitor-stats.php';
$auto_updates = get_site_option('auto_update_plugins', array());

if (!in_array($plugin_file, $auto_updates)) {
    $auto_updates[] = $plugin_file;
    update_site_option('auto_update_plugins', $auto_updates);
    echo "Auto-update enabled for WP Visitor Stats!\n";
} else {
    echo "Auto-update already enabled for WP Visitor Stats.\n";
}

echo "Plugin file: $plugin_file\n";
echo "In auto-update list: " . (in_array($plugin_file, $auto_updates) ? "YES" : "NO") . "\n";

