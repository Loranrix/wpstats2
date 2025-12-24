<?php
/**
 * Admin page - Excluded IPs
 */
?>
<div class="wrap">
    <h1><?php _e('Excluded IP Addresses', 'wp-visitor-stats'); ?></h1>
    
    <p><?php _e('IP addresses in this list will not be tracked. Useful for excluding your own IP or internal networks.', 'wp-visitor-stats'); ?></p>
    
    <form method="post" action="" style="margin: 20px 0;">
        <?php wp_nonce_field('wp_visitor_stats_excluded'); ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="new_excluded_ip"><?php _e('Add IP Address', 'wp-visitor-stats'); ?></label>
                </th>
                <td>
                    <input type="text" name="new_excluded_ip" id="new_excluded_ip" placeholder="192.168.1.1" class="regular-text">
                    <button type="submit" name="wp_visitor_stats_add_excluded_ip" class="button"><?php _e('Add IP', 'wp-visitor-stats'); ?></button>
                    <p class="description">
                        <?php _e('Your current IP:', 'wp-visitor-stats'); ?> 
                        <strong><?php echo esc_html($this->db->get_user_ip()); ?></strong>
                        <button type="button" class="button button-small" onclick="document.getElementById('new_excluded_ip').value='<?php echo esc_js($this->db->get_user_ip()); ?>'">
                            <?php _e('Use My IP', 'wp-visitor-stats'); ?>
                        </button>
                    </p>
                </td>
            </tr>
        </table>
    </form>
    
    <?php if (!empty($excluded_ips)): ?>
    <h2><?php _e('Excluded IPs', 'wp-visitor-stats'); ?></h2>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e('IP Address', 'wp-visitor-stats'); ?></th>
                <th><?php _e('Actions', 'wp-visitor-stats'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($excluded_ips as $ip): ?>
            <tr>
                <td>
                    <strong><?php echo esc_html($ip); ?></strong>
                    <?php if ($ip === $this->db->get_user_ip()): ?>
                        <span class="dashicons dashicons-yes-alt" style="color: #46b450;" title="<?php _e('Your current IP', 'wp-visitor-stats'); ?>"></span>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" style="display: inline;">
                        <?php wp_nonce_field('wp_visitor_stats_excluded'); ?>
                        <input type="hidden" name="remove_excluded_ip" value="<?php echo esc_attr($ip); ?>">
                        <button type="submit" name="wp_visitor_stats_remove_excluded_ip" class="button button-small">
                            <?php _e('Remove', 'wp-visitor-stats'); ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p><?php _e('No IP addresses excluded.', 'wp-visitor-stats'); ?></p>
    <?php endif; ?>
</div>

