<?php
/**
 * Admin page - Main Statistics Overview
 */
?>
<div class="wrap">
    <h1><?php _e('Visitor Statistics', 'wp-visitor-stats'); ?></h1>
    
    <div class="wp-visitor-stats-overview" style="margin: 20px 0;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 12px; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                    <?php _e('Today', 'wp-visitor-stats'); ?>
                </div>
                <div style="font-size: 32px; font-weight: bold;">
                    <?php echo number_format($stats['today']); ?>
                </div>
            </div>
            
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 12px; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                    <?php _e('This Week', 'wp-visitor-stats'); ?>
                </div>
                <div style="font-size: 32px; font-weight: bold;">
                    <?php echo number_format($stats['week']); ?>
                </div>
            </div>
            
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 12px; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                    <?php _e('This Month', 'wp-visitor-stats'); ?>
                </div>
                <div style="font-size: 32px; font-weight: bold;">
                    <?php echo number_format($stats['month']); ?>
                </div>
            </div>
            
            <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 12px; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                    <?php _e('This Year', 'wp-visitor-stats'); ?>
                </div>
                <div style="font-size: 32px; font-weight: bold;">
                    <?php echo number_format($stats['year']); ?>
                </div>
            </div>
            
            <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 12px; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                    <span style="display: inline-block; width: 8px; height: 8px; background: #10b981; border-radius: 50%; margin-right: 5px; animation: pulse 2s infinite;"></span>
                    <?php _e('Online Now', 'wp-visitor-stats'); ?>
                </div>
                <div style="font-size: 32px; font-weight: bold;">
                    <?php echo number_format($stats['online']); ?>
                </div>
            </div>
            
            <div style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 12px; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                    <?php _e('All Time', 'wp-visitor-stats'); ?>
                </div>
                <div style="font-size: 32px; font-weight: bold;">
                    <?php echo number_format($stats['all']); ?>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px;">
        <?php if (!empty($top_countries)): ?>
        <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h2 style="margin-top: 0;"><?php _e('Top Countries', 'wp-visitor-stats'); ?></h2>
            <table class="widefat striped" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th style="width: 40px;"><?php _e('Flag', 'wp-visitor-stats'); ?></th>
                        <th><?php _e('Country', 'wp-visitor-stats'); ?></th>
                        <th style="text-align: right;"><?php _e('Visitors', 'wp-visitor-stats'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($top_countries, 0, 10) as $country): ?>
                    <tr>
                        <td>
                            <img src="https://flagcdn.com/w20/<?php echo strtolower(esc_attr($country->country_code)); ?>.svg" 
                                 alt="<?php echo esc_attr($country->country_code); ?>" 
                                 style="width: 20px; height: auto; vertical-align: middle;"
                                 onerror="this.style.display='none';">
                        </td>
                        <td><?php echo esc_html($country->country_name); ?></td>
                        <td style="text-align: right; font-weight: bold;">
                            <?php echo number_format($country->unique_visitors); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="text-align: center; margin-top: 15px;">
                <a href="<?php echo admin_url('admin.php?page=wp-visitor-stats-countries'); ?>" class="button">
                    <?php _e('View All Countries', 'wp-visitor-stats'); ?>
                </a>
            </p>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($top_cities)): ?>
        <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
            <h2 style="margin-top: 0;"><?php _e('Top Cities', 'wp-visitor-stats'); ?></h2>
            <table class="widefat striped" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th style="width: 40px;"><?php _e('Flag', 'wp-visitor-stats'); ?></th>
                        <th><?php _e('City', 'wp-visitor-stats'); ?></th>
                        <th style="text-align: right;"><?php _e('Visitors', 'wp-visitor-stats'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($top_cities, 0, 10) as $city): ?>
                    <tr>
                        <td>
                            <img src="https://flagcdn.com/w20/<?php echo strtolower(esc_attr($city->country_code)); ?>.svg" 
                                 alt="<?php echo esc_attr($city->country_code); ?>" 
                                 style="width: 20px; height: auto; vertical-align: middle;"
                                 onerror="this.style.display='none';">
                        </td>
                        <td>
                            <?php echo esc_html($city->city); ?>
                            <?php if (!empty($city->country_name)): ?>
                                <br><small style="color: #666;"><?php echo esc_html($city->country_name); ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; font-weight: bold;">
                            <?php echo number_format($city->unique_visitors); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p style="text-align: center; margin-top: 15px;">
                <a href="<?php echo admin_url('admin.php?page=wp-visitor-stats-cities'); ?>" class="button">
                    <?php _e('View All Cities', 'wp-visitor-stats'); ?>
                </a>
            </p>
        </div>
        <?php endif; ?>
    </div>
    
    <div style="background: #f0f6fc; padding: 20px; border-left: 4px solid #2271b1; margin: 30px 0;">
        <h3 style="margin-top: 0;"><?php _e('Quick Links', 'wp-visitor-stats'); ?></h3>
        <p>
            <a href="<?php echo admin_url('admin.php?page=wp-visitor-stats-countries'); ?>" class="button">
                <?php _e('View by Country', 'wp-visitor-stats'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=wp-visitor-stats-cities'); ?>" class="button">
                <?php _e('View by City', 'wp-visitor-stats'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=wp-visitor-stats-settings'); ?>" class="button">
                <?php _e('Settings', 'wp-visitor-stats'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=wp-visitor-stats-excluded'); ?>" class="button">
                <?php _e('Excluded IPs', 'wp-visitor-stats'); ?>
            </a>
        </p>
    </div>
</div>

