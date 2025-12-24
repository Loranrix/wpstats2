<?php
/**
 * Update WordPress URLs to HTTPS
 * Run once then delete
 */
require_once('wp-load.php');

if (!current_user_can('manage_options')) {
    die('Unauthorized');
}

global $wpdb;

// Update home and siteurl options
update_option('home', 'https://loranrix.com');
update_option('siteurl', 'https://loranrix.com');

// Update URLs in database
$old_url = 'http://loranrix.com';
$new_url = 'https://loranrix.com';

$tables = array(
    $wpdb->posts => array('post_content', 'post_excerpt', 'guid'),
    $wpdb->postmeta => array('meta_value'),
    $wpdb->options => array('option_value'),
    $wpdb->comments => array('comment_content'),
    $wpdb->commentmeta => array('meta_value'),
);

$updated = 0;

foreach ($tables as $table => $columns) {
    foreach ($columns as $column) {
        $query = $wpdb->prepare(
            "UPDATE {$table} SET {$column} = REPLACE({$column}, %s, %s)",
            $old_url,
            $new_url
        );
        $result = $wpdb->query($query);
        if ($result !== false) {
            $updated += $result;
        }
    }
}

echo "Updated {$updated} rows. URLs changed from {$old_url} to {$new_url}";
echo "<br><br><strong>IMPORTANT: Delete this file after running it!</strong>";

