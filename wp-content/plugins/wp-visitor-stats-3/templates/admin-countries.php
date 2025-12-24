<?php
/**
 * Admin page - Visitors by Country
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
    <h1><?php _e('Visitors by Country', 'wp-visitor-stats'); ?></h1>
    
    <div class="wp-visitor-stats-filters" style="margin: 20px 0;">
        <form method="get">
            <input type="hidden" name="page" value="wp-visitor-stats-countries">
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
            <button type="submit" class="button"><?php _e('Filter', 'wp-visitor-stats'); ?></button>
        </form>
    </div>
    
    <table class="widefat striped">
        <thead>
            <tr>
                <th style="width: 60px;"><?php _e('Flag', 'wp-visitor-stats'); ?></th>
                <th><?php _e('Country', 'wp-visitor-stats'); ?></th>
                <th style="text-align: right;"><?php _e('Unique Visitors', 'wp-visitor-stats'); ?></th>
                <th style="text-align: right;"><?php _e('Total Visits', 'wp-visitor-stats'); ?></th>
                <th style="width: 200px;"><?php _e('Percentage', 'wp-visitor-stats'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (empty($countries) || !is_array($countries)) {
                echo '<tr><td colspan="5" style="text-align: center; padding: 20px;">' . __('No data available', 'wp-visitor-stats') . '</td></tr>';
            } else {
            $total_visitors = array_sum(array_column($countries, 'unique_visitors'));
            foreach ($countries as $country): 
                $percentage = $total_visitors > 0 ? ($country->unique_visitors / $total_visitors) * 100 : 0;
            ?>
            <tr>
                <td>
                    <?php 
                    if (!empty($country->country_code) && strlen($country->country_code) >= 2) {
                        $country_code_lower = strtolower(trim($country->country_code));
                        // Try multiple flag sources for better compatibility
                        $flag_urls = array(
                            'https://flagcdn.com/w40/' . $country_code_lower . '.svg',
                            'https://flagcdn.com/w40/' . $country_code_lower . '.png',
                            'https://flagsapi.com/' . strtoupper($country_code_lower) . '/flat/32.png'
                        );
                        $flag_url = $flag_urls[0]; // Primary source
                        ?>
                        <img src="<?php echo esc_url($flag_url); ?>" 
                             alt="<?php echo esc_attr($country->country_code); ?>" 
                             style="width: 40px; height: auto; border: 1px solid #ddd; border-radius: 2px; display: block;"
                             onerror="this.onerror=null; this.src='<?php echo esc_js($flag_urls[1]); ?>'; this.onerror=function(){this.onerror=null; this.src='<?php echo esc_js($flag_urls[2]); ?>'; this.onerror=function(){this.style.display='none'; this.nextElementSibling.style.display='inline-block';};};">
                        <span style="display: none; width: 40px; height: 30px; background: #ddd; border-radius: 2px; text-align: center; line-height: 30px; font-size: 10px; font-weight: bold;"><?php echo esc_html(strtoupper($country->country_code)); ?></span>
                    <?php 
                    } else {
                        echo '<span style="display: inline-block; width: 40px; height: 30px; background: #ddd; border-radius: 2px; text-align: center; line-height: 30px; font-size: 10px;">?</span>';
                    }
                    ?>
                </td>
                <td>
                    <strong><?php echo esc_html($country->country_name); ?></strong>
                    <br>
                    <small style="color: #666;"><?php echo esc_html($country->country_code); ?></small>
                </td>
                <td style="text-align: right;">
                    <strong><?php echo number_format($country->unique_visitors); ?></strong>
                </td>
                <td style="text-align: right;">
                    <?php echo number_format($country->total_visits); ?>
                </td>
                <td>
                    <div style="background: #f0f0f0; border-radius: 4px; height: 20px; position: relative;">
                        <div style="background: linear-gradient(90deg, #3b82f6, #1e40af); height: 100%; border-radius: 4px; width: <?php echo esc_attr($percentage); ?>%;"></div>
                        <span style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); font-size: 11px; font-weight: 600; color: #333;">
                            <?php echo number_format($percentage, 1); ?>%
                        </span>
                    </div>
                </td>
            </tr>
            <?php 
                endforeach; 
            }
            ?>
        </tbody>
        <?php if (!empty($countries) && is_array($countries)): ?>
        <tfoot>
            <tr>
                <th colspan="2"><?php _e('Total', 'wp-visitor-stats'); ?></th>
                <th style="text-align: right;"><?php echo number_format($total_visitors); ?></th>
                <th style="text-align: right;"><?php echo number_format(array_sum(array_column($countries, 'total_visits'))); ?></th>
                <th>100%</th>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</div>

<?php
/**
 * Get flag SVG for country code
 */
function wp_visitor_stats_get_flag_svg($country_code) {
    if (empty($country_code)) {
        return '<span style="display: inline-block; width: 32px; height: 24px; background: #ddd; border-radius: 2px;"></span>';
    }
    
    // Use flagcdn.com for SVG flags
    $flag_url = 'https://flagcdn.com/w40/' . strtolower($country_code) . '.svg';
    
    return sprintf(
        '<img src="%s" alt="%s" style="width: 40px; height: auto; border: 1px solid #ddd; border-radius: 2px;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'inline-block\';">
        <span style="display: none; width: 32px; height: 24px; background: #ddd; border-radius: 2px; text-align: center; line-height: 24px; font-size: 10px;">%s</span>',
        esc_url($flag_url),
        esc_attr($country_code),
        esc_html($country_code)
    );
}
?>

