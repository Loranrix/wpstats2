<?php
/**
 * Admin page - Visitors by City
 */
$periods = array(
    'all' => __('All Time', 'wp-visitor-stats'),
    'today' => __('Today', 'wp-visitor-stats'),
    'week' => __('Last 7 Days', 'wp-visitor-stats'),
    'month' => __('Last 30 Days', 'wp-visitor-stats'),
    'year' => __('Last Year', 'wp-visitor-stats')
);
?>
<div class="wrap">
    <h1><?php _e('Visitors by City', 'wp-visitor-stats'); ?></h1>
    
    <div class="wp-visitor-stats-filters" style="margin: 20px 0;">
        <form method="get">
            <input type="hidden" name="page" value="wp-visitor-stats-cities">
            <label>
                <?php _e('Period:', 'wp-visitor-stats'); ?>
                <select name="period">
                    <?php foreach ($periods as $key => $label): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($period, $key); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <?php _e('Country:', 'wp-visitor-stats'); ?>
                <select name="country">
                    <option value=""><?php _e('All Countries', 'wp-visitor-stats'); ?></option>
                    <?php
                    global $wpdb;
                    $countries_list = $wpdb->get_results("SELECT DISTINCT country_code, country_name FROM {$wpdb->prefix}visitor_stats_locations WHERE country_code IS NOT NULL ORDER BY country_name");
                    foreach ($countries_list as $c):
                    ?>
                        <option value="<?php echo esc_attr($c->country_code); ?>" <?php selected($country_code, $c->country_code); ?>>
                            <?php echo esc_html($c->country_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="button"><?php _e('Filter', 'wp-visitor-stats'); ?></button>
        </form>
    </div>
    
    <table class="widefat striped">
        <thead>
            <tr>
                <th style="width: 60px;"><?php _e('Flag', 'wp-visitor-stats'); ?></th>
                <th><?php _e('City', 'wp-visitor-stats'); ?></th>
                <th><?php _e('Country', 'wp-visitor-stats'); ?></th>
                <th style="text-align: right;"><?php _e('Unique Visitors', 'wp-visitor-stats'); ?></th>
                <th style="text-align: right;"><?php _e('Total Visits', 'wp-visitor-stats'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_visitors = array_sum(array_column($cities, 'unique_visitors'));
            foreach ($cities as $city): 
            ?>
            <tr>
                <td>
                    <?php echo wp_visitor_stats_get_flag_svg($city->country_code); ?>
                </td>
                <td>
                    <strong><?php echo esc_html($city->city); ?></strong>
                    <?php if (!empty($city->region)): ?>
                        <br><small style="color: #666;"><?php echo esc_html($city->region); ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo esc_html($city->country_name); ?>
                </td>
                <td style="text-align: right;">
                    <strong><?php echo number_format($city->unique_visitors); ?></strong>
                </td>
                <td style="text-align: right;">
                    <?php echo number_format($city->total_visits); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3"><?php _e('Total', 'wp-visitor-stats'); ?></th>
                <th style="text-align: right;"><?php echo number_format($total_visitors); ?></th>
                <th style="text-align: right;"><?php echo number_format(array_sum(array_column($cities, 'total_visits'))); ?></th>
            </tr>
        </tfoot>
    </table>
</div>

<?php
/**
 * Get flag SVG for country code (reuse function from countries page)
 */
if (!function_exists('wp_visitor_stats_get_flag_svg')) {
    function wp_visitor_stats_get_flag_svg($country_code) {
        if (empty($country_code)) {
            return '<span style="display: inline-block; width: 32px; height: 24px; background: #ddd; border-radius: 2px;"></span>';
        }
        
        $flag_url = 'https://flagcdn.com/w40/' . strtolower($country_code) . '.svg';
        
        return sprintf(
            '<img src="%s" alt="%s" style="width: 40px; height: auto; border: 1px solid #ddd; border-radius: 2px;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'inline-block\';">
            <span style="display: none; width: 32px; height: 24px; background: #ddd; border-radius: 2px; text-align: center; line-height: 24px; font-size: 10px;">%s</span>',
            esc_url($flag_url),
            esc_attr($country_code),
            esc_html($country_code)
        );
    }
}
?>

