-- Update WordPress URLs to HTTPS
-- Run this SQL script in your database

USE lora_stats;

-- Update home and siteurl options
UPDATE wp_options SET option_value = 'https://loranrix.com' WHERE option_name = 'home';
UPDATE wp_options SET option_value = 'https://loranrix.com' WHERE option_name = 'siteurl';

-- Update URLs in posts
UPDATE wp_posts SET post_content = REPLACE(post_content, 'http://loranrix.com', 'https://loranrix.com');
UPDATE wp_posts SET post_excerpt = REPLACE(post_excerpt, 'http://loranrix.com', 'https://loranrix.com');
UPDATE wp_posts SET guid = REPLACE(guid, 'http://loranrix.com', 'https://loranrix.com');

-- Update URLs in postmeta
UPDATE wp_postmeta SET meta_value = REPLACE(meta_value, 'http://loranrix.com', 'https://loranrix.com');

-- Update URLs in options
UPDATE wp_options SET option_value = REPLACE(option_value, 'http://loranrix.com', 'https://loranrix.com');

-- Update URLs in comments
UPDATE wp_comments SET comment_content = REPLACE(comment_content, 'http://loranrix.com', 'https://loranrix.com');

-- Update URLs in commentmeta
UPDATE wp_commentmeta SET meta_value = REPLACE(meta_value, 'http://loranrix.com', 'https://loranrix.com');

