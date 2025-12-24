<?php
/**
 * Test file to check if visitor stats are working
 * Access: https://loranrix.com/wp-content/plugins/wp-visitor-stats-3/test-stats.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if plugin is active
if (!class_exists('WP_Visitor_Stats')) {
    die('Plugin WP Visitor Stats is not active');
}

$plugin = WP_Visitor_Stats::get_instance();
$db = $plugin->db;
$display = $plugin->display;

echo '<h1>WP Visitor Stats Test</h1>';

// Check options
echo '<h2>Options:</h2>';
echo '<pre>';
echo 'wp_visitor_stats_enabled: ' . var_export(get_option('wp_visitor_stats_enabled', 'not set'), true) . "\n";
echo 'wp_visitor_stats_track_all: ' . var_export(get_option('wp_visitor_stats_track_all', 'not set'), true) . "\n";
echo 'wp_visitor_stats_position: ' . var_export(get_option('wp_visitor_stats_position', 'not set'), true) . "\n";
echo '</pre>';

// Check if can see stats
echo '<h2>Permissions:</h2>';
echo '<pre>';
echo 'IP Address: ' . $db->get_user_ip() . "\n";
echo 'Is IP Authorized: ' . var_export($db->is_ip_authorized($db->get_user_ip()), true) . "\n";
echo 'Is Cookie Valid: ' . var_export($db->is_cookie_valid(), true) . "\n";
echo 'Can See Stats: ' . var_export($db->can_see_stats(), true) . "\n";
echo '</pre>';

// Get stats
echo '<h2>Stats Data:</h2>';
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
} catch (Exception $e) {
    echo '<p style="color: red;">Error getting stats: ' . $e->getMessage() . '</p>';
}

// Try to display stats
echo '<h2>Stats Display:</h2>';
try {
    $html = $display->get_stats_html();
    if (!empty($html)) {
        echo $html;
    } else {
        echo '<p style="color: orange;">Stats HTML is empty</p>';
    }
} catch (Exception $e) {
    echo '<p style="color: red;">Error displaying stats: ' . $e->getMessage() . '</p>';
}

// Check if tables exist
echo '<h2>Database Tables:</h2>';
global $wpdb;
$tables = array(
    $wpdb->prefix . 'visitor_stats_visits',
    $wpdb->prefix . 'visitor_stats_online',
    $wpdb->prefix . 'visitor_stats_locations'
);
echo '<pre>';
foreach ($tables as $table) {
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");
    echo $table . ': ' . ($exists ? 'EXISTS' : 'NOT EXISTS') . "\n";
}
echo '</pre>';


