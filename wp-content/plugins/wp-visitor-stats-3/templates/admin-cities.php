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
                    try {
                        global $wpdb;
                        $countries_list = $wpdb->get_results("SELECT DISTINCT country_code, country_name FROM {$wpdb->prefix}visitor_stats_locations WHERE country_code IS NOT NULL AND country_code != '' ORDER BY country_name");
                        if (is_array($countries_list)) {
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
            if (empty($cities) || !is_array($cities)) {
                echo '<tr><td colspan="5" style="text-align: center; padding: 20px;">' . __('No data available', 'wp-visitor-stats') . '</td></tr>';
            } else {
                $total_visitors = 0;
                $total_visits = 0;
                foreach ($cities as $city) {
                    if (isset($city->unique_visitors)) {
                        $total_visitors += (int) $city->unique_visitors;
                    }
                    if (isset($city->total_visits)) {
                        $total_visits += (int) $city->total_visits;
                    }
                }
                foreach ($cities as $city): 
            ?>
            <tr>
                <td>
                    <?php 
                    if (!empty($city->country_code)) {
                        $flag_url = 'https://flagcdn.com/w40/' . strtolower(esc_attr($city->country_code)) . '.svg';
                        echo '<img src="' . esc_url($flag_url) . '" alt="' . esc_attr($city->country_code) . '" style="width: 40px; height: auto; border: 1px solid #ddd; border-radius: 2px;" onerror="this.style.display=\'none\';">';
                    } else {
                        echo '<span style="display: inline-block; width: 32px; height: 24px; background: #ddd; border-radius: 2px;"></span>';
                    }
                    ?>
                </td>
                <td>
                    <strong><?php echo esc_html(isset($city->city) ? $city->city : ''); ?></strong>
                    <?php if (!empty($city->region)): ?>
                        <br><small style="color: #666;"><?php echo esc_html($city->region); ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo esc_html(isset($city->country_name) ? $city->country_name : ''); ?>
                </td>
                <td style="text-align: right;">
                    <strong><?php echo number_format(isset($city->unique_visitors) ? (int) $city->unique_visitors : 0); ?></strong>
                </td>
                <td style="text-align: right;">
                    <?php echo number_format(isset($city->total_visits) ? (int) $city->total_visits : 0); ?>
                </td>
            </tr>
            <?php 
                endforeach;
            }
            ?>
        </tbody>
        <?php if (!empty($cities) && is_array($cities)): ?>
        <tfoot>
            <tr>
                <th colspan="3"><?php _e('Total', 'wp-visitor-stats'); ?></th>
                <th style="text-align: right;"><?php echo number_format($total_visitors); ?></th>
                <th style="text-align: right;"><?php echo number_format($total_visits); ?></th>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</div>


