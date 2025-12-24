<?php
/**
 * Admin page - Maintenance Pages
 */
$templates = array(
    'default' => __('Default (Purple Gradient)', 'wp-visitor-stats'),
    'modern' => __('Modern (Dark Grid)', 'wp-visitor-stats')
);
?>
<div class="wrap">
    <h1><?php _e('Maintenance Pages', 'wp-visitor-stats'); ?></h1>
    
    <div style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 20px 0;">
        <h2><?php _e('Create New Maintenance Page', 'wp-visitor-stats'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('wp_visitor_stats_maintenance'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="maintenance_title"><?php _e('Page Title', 'wp-visitor-stats'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="maintenance_title" id="maintenance_title" value="Site Under Maintenance" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="maintenance_template"><?php _e('Template', 'wp-visitor-stats'); ?></label>
                    </th>
                    <td>
                        <select name="maintenance_template" id="maintenance_template">
                            <?php foreach ($templates as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" name="wp_visitor_stats_create_maintenance" class="button button-primary">
                    <?php _e('Create Maintenance Page', 'wp-visitor-stats'); ?>
                </button>
            </p>
        </form>
    </div>
    
    <?php if (!empty($maintenance_pages)): ?>
    <h2><?php _e('Existing Maintenance Pages', 'wp-visitor-stats'); ?></h2>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e('Page Title', 'wp-visitor-stats'); ?></th>
                <th><?php _e('Template', 'wp-visitor-stats'); ?></th>
                <th><?php _e('Actions', 'wp-visitor-stats'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($maintenance_pages as $page): 
                $template = get_post_meta($page->ID, '_wp_visitor_stats_maintenance_template', true);
            ?>
            <tr>
                <td>
                    <strong><?php echo esc_html($page->post_title); ?></strong>
                    <br>
                    <a href="<?php echo get_permalink($page->ID); ?>" target="_blank"><?php echo get_permalink($page->ID); ?></a>
                </td>
                <td>
                    <?php echo isset($templates[$template]) ? esc_html($templates[$template]) : esc_html($template); ?>
                </td>
                <td>
                    <a href="<?php echo get_edit_post_link($page->ID); ?>" class="button button-small"><?php _e('Edit', 'wp-visitor-stats'); ?></a>
                    <a href="<?php echo get_permalink($page->ID); ?>" target="_blank" class="button button-small"><?php _e('View', 'wp-visitor-stats'); ?></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

