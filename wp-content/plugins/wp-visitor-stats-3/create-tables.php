<?php
/**
 * Create database tables for WP Visitor Stats
 */
$wp_load = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
require_once($wp_load);

if (!class_exists('WP_Visitor_Stats')) {
    die('Plugin not active');
}

$plugin = WP_Visitor_Stats::get_instance();
$plugin->db->create_tables();

echo "Tables created/verified successfully!\n";
global $wpdb;
$tables = array(
    $wpdb->prefix . 'visitor_stats_visits',
    $wpdb->prefix . 'visitor_stats_online',
    $wpdb->prefix . 'visitor_stats_locations'
);
foreach ($tables as $table) {
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
    echo "$table: " . ($exists ? "EXISTS" : "MISSING") . "\n";
}



