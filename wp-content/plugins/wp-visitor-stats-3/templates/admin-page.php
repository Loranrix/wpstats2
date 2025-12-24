<?php
/**
 * Admin settings page
 */
$enabled = get_option('wp_visitor_stats_enabled', true);
$position = get_option('wp_visitor_stats_position', 'header_right');
$format = get_option('wp_visitor_stats_format', 'compact');
$color = get_option('wp_visitor_stats_color', 'blue');
$track_all = get_option('wp_visitor_stats_track_all', false);
$authorized_ips = get_option('wp_visitor_stats_authorized_ips', array());
$cookie_token = get_option('wp_visitor_stats_cookie_token', '');
?>
<div class="wrap">
    <h1><?php _e('WP Visitor Stats Settings', 'wp-visitor-stats'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('wp_visitor_stats_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Enable Statistics', 'wp-visitor-stats'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wp_visitor_stats_enabled" value="1" <?php checked($enabled, true); ?>>
                        <?php _e('Display visitor statistics', 'wp-visitor-stats'); ?>
                    </label>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Display Position', 'wp-visitor-stats'); ?></th>
                <td>
                    <select name="wp_visitor_stats_position">
                        <option value="header_left" <?php selected($position, 'header_left'); ?>><?php _e('Header Left', 'wp-visitor-stats'); ?></option>
                        <option value="header_right" <?php selected($position, 'header_right'); ?>><?php _e('Header Right', 'wp-visitor-stats'); ?></option>
                        <option value="header_center" <?php selected($position, 'header_center'); ?>><?php _e('Header Center', 'wp-visitor-stats'); ?></option>
                        <option value="before_content" <?php selected($position, 'before_content'); ?>><?php _e('Before Content', 'wp-visitor-stats'); ?></option>
                        <option value="footer" <?php selected($position, 'footer'); ?>><?php _e('Footer', 'wp-visitor-stats'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Display Format', 'wp-visitor-stats'); ?></th>
                <td>
                    <select name="wp_visitor_stats_format">
                        <option value="minimal" <?php selected($format, 'minimal'); ?>><?php _e('Minimal', 'wp-visitor-stats'); ?></option>
                        <option value="compact" <?php selected($format, 'compact'); ?>><?php _e('Compact', 'wp-visitor-stats'); ?></option>
                        <option value="detailed" <?php selected($format, 'detailed'); ?>><?php _e('Detailed', 'wp-visitor-stats'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Color Scheme', 'wp-visitor-stats'); ?></th>
                <td>
                    <select name="wp_visitor_stats_color">
                        <option value="blue" <?php selected($color, 'blue'); ?>><?php _e('Blue', 'wp-visitor-stats'); ?></option>
                        <option value="green" <?php selected($color, 'green'); ?>><?php _e('Green', 'wp-visitor-stats'); ?></option>
                        <option value="purple" <?php selected($color, 'purple'); ?>><?php _e('Purple', 'wp-visitor-stats'); ?></option>
                        <option value="red" <?php selected($color, 'red'); ?>><?php _e('Red', 'wp-visitor-stats'); ?></option>
                        <option value="orange" <?php selected($color, 'orange'); ?>><?php _e('Orange', 'wp-visitor-stats'); ?></option>
                        <option value="pink" <?php selected($color, 'pink'); ?>><?php _e('Pink', 'wp-visitor-stats'); ?></option>
                        <option value="indigo" <?php selected($color, 'indigo'); ?>><?php _e('Indigo', 'wp-visitor-stats'); ?></option>
                        <option value="teal" <?php selected($color, 'teal'); ?>><?php _e('Teal', 'wp-visitor-stats'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Track All Visitors', 'wp-visitor-stats'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="wp_visitor_stats_track_all" value="1" <?php checked($track_all, true); ?>>
                        <?php _e('Track all visitors (not just authorized IPs)', 'wp-visitor-stats'); ?>
                    </label>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Authorized IP Addresses', 'wp-visitor-stats'); ?></h2>
        <p><?php _e('Only these IP addresses will see the statistics display.', 'wp-visitor-stats'); ?></p>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Add IP Address', 'wp-visitor-stats'); ?></th>
                <td>
                    <input type="text" name="new_ip" placeholder="192.168.1.1" class="regular-text">
                    <button type="submit" name="wp_visitor_stats_add_ip" class="button"><?php _e('Add IP', 'wp-visitor-stats'); ?></button>
                </td>
            </tr>
        </table>
        
        <?php if (!empty($authorized_ips)): ?>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('IP Address', 'wp-visitor-stats'); ?></th>
                    <th><?php _e('Actions', 'wp-visitor-stats'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($authorized_ips as $ip): ?>
                <tr>
                    <td><?php echo esc_html($ip); ?></td>
                    <td>
                        <form method="post" style="display: inline;">
                            <?php wp_nonce_field('wp_visitor_stats_settings'); ?>
                            <input type="hidden" name="remove_ip" value="<?php echo esc_attr($ip); ?>">
                            <button type="submit" name="wp_visitor_stats_remove_ip" class="button button-small"><?php _e('Remove', 'wp-visitor-stats'); ?></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        
        <h2><?php _e('Cookie Access Link', 'wp-visitor-stats'); ?></h2>
        <p><?php _e('Generate a link that can be opened on a smartphone to grant access via cookie (valid for 1 year).', 'wp-visitor-stats'); ?></p>
        
        <button type="button" id="generate-cookie-link" class="button button-primary"><?php _e('Generate Cookie Link', 'wp-visitor-stats'); ?></button>
        
        <div id="cookie-link-result" style="margin-top: 15px; display: none;">
            <p><strong><?php _e('Cookie Link:', 'wp-visitor-stats'); ?></strong></p>
            <input type="text" id="cookie-link-url" class="large-text" readonly>
            <button type="button" id="copy-cookie-link" class="button"><?php _e('Copy Link', 'wp-visitor-stats'); ?></button>
        </div>
        
        <p class="submit">
            <input type="submit" name="wp_visitor_stats_save" class="button button-primary" value="<?php _e('Save Changes', 'wp-visitor-stats'); ?>">
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#generate-cookie-link').on('click', function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_visitor_stats_generate_cookie',
                nonce: '<?php echo wp_create_nonce('wp_visitor_stats_generate_cookie'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $('#cookie-link-url').val(response.data.url);
                    $('#cookie-link-result').show();
                }
            }
        });
    });
    
    $('#copy-cookie-link').on('click', function() {
        $('#cookie-link-url').select();
        document.execCommand('copy');
        alert('<?php _e('Link copied to clipboard!', 'wp-visitor-stats'); ?>');
    });
});
</script>

