<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($this->get_email_subject($period)); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .email-container {
            background: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1e40af;
            margin: 0;
            font-size: 24px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #1e40af;
        }
        .flag {
            width: 30px;
            height: auto;
            vertical-align: middle;
            margin-right: 8px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1><?php echo esc_html(get_bloginfo('name')); ?></h1>
            <p><?php echo esc_html($period_label); ?> - <?php echo esc_html(date_i18n(get_option('date_format'))); ?></p>
        </div>
        
        <h2 style="color: #1e40af; margin-top: 0;"><?php _e('Detailed Visitor Statistics', 'wp-visitor-stats'); ?></h2>
        
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-label"><?php _e('Today', 'wp-visitor-stats'); ?></div>
                <div class="stat-value"><?php echo number_format($stats['today']); ?></div>
            </div>
            
            <div class="stat-box">
                <div class="stat-label"><?php _e('This Week', 'wp-visitor-stats'); ?></div>
                <div class="stat-value"><?php echo number_format($stats['week']); ?></div>
            </div>
            
            <div class="stat-box">
                <div class="stat-label"><?php _e('This Month', 'wp-visitor-stats'); ?></div>
                <div class="stat-value"><?php echo number_format($stats['month']); ?></div>
            </div>
            
            <div class="stat-box">
                <div class="stat-label"><?php _e('This Year', 'wp-visitor-stats'); ?></div>
                <div class="stat-value"><?php echo number_format($stats['year']); ?></div>
            </div>
        </div>
        
        <?php
        // Get top countries
        require_once WP_VISITOR_STATS_PLUGIN_DIR . 'includes/class-wp-visitor-stats-geolocation.php';
        $geolocation = new WP_Visitor_Stats_Geolocation($db_instance);
        $top_countries = $geolocation->get_visitors_by_country($period);
        if (!empty($top_countries)):
        ?>
        <h3 style="color: #1e40af; margin-top: 30px;"><?php _e('Top Countries', 'wp-visitor-stats'); ?></h3>
        <table>
            <thead>
                <tr>
                    <th><?php _e('Country', 'wp-visitor-stats'); ?></th>
                    <th style="text-align: right;"><?php _e('Visitors', 'wp-visitor-stats'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($top_countries, 0, 10) as $country): ?>
                <tr>
                    <td>
                        <img src="https://flagcdn.com/w20/<?php echo strtolower(esc_attr($country->country_code)); ?>.svg" 
                             alt="<?php echo esc_attr($country->country_code); ?>" class="flag" 
                             onerror="this.style.display='none';">
                        <?php echo esc_html($country->country_name); ?>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($country->unique_visitors); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        
        <?php
        // Get top cities
        $top_cities = $geolocation->get_visitors_by_city($period);
        if (!empty($top_cities)):
        ?>
        <h3 style="color: #1e40af; margin-top: 30px;"><?php _e('Top Cities', 'wp-visitor-stats'); ?></h3>
        <table>
            <thead>
                <tr>
                    <th><?php _e('City', 'wp-visitor-stats'); ?></th>
                    <th><?php _e('Country', 'wp-visitor-stats'); ?></th>
                    <th style="text-align: right;"><?php _e('Visitors', 'wp-visitor-stats'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($top_cities, 0, 10) as $city): ?>
                <tr>
                    <td>
                        <img src="https://flagcdn.com/w20/<?php echo strtolower(esc_attr($city->country_code)); ?>.svg" 
                             alt="<?php echo esc_attr($city->country_code); ?>" class="flag"
                             onerror="this.style.display='none';">
                        <?php echo esc_html($city->city); ?>
                    </td>
                    <td><?php echo esc_html($city->country_name); ?></td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($city->unique_visitors); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        
        <div class="footer">
            <p><?php _e('This is an automated detailed report from WP Visitor Stats plugin.', 'wp-visitor-stats'); ?></p>
            <p><?php echo esc_html(get_bloginfo('url')); ?></p>
        </div>
    </div>
</body>
</html>

