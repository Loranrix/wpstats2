<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($email_obj->get_email_subject($period)); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
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
        
        <h2 style="color: #1e40af; margin-top: 0;"><?php _e('Visitor Statistics Summary', 'wp-visitor-stats'); ?></h2>
        
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
                <div class="stat-label"><?php _e('Online Now', 'wp-visitor-stats'); ?></div>
                <div class="stat-value"><?php echo number_format($stats['online']); ?></div>
            </div>
        </div>
        
        <?php if ($period !== 'test'): ?>
        <div style="background: #f0f6fc; padding: 15px; border-radius: 6px; margin-top: 20px;">
            <strong><?php echo esc_html($period_label); ?>:</strong> 
            <span style="font-size: 20px; font-weight: bold; color: #1e40af;">
                <?php echo number_format($period_count); ?> <?php _e('unique visitors', 'wp-visitor-stats'); ?>
            </span>
        </div>
        <?php endif; ?>
        
        <div class="footer">
            <p><?php _e('This is an automated report from WP Visitor Stats plugin.', 'wp-visitor-stats'); ?></p>
            <p><?php echo esc_html(get_bloginfo('url')); ?></p>
        </div>
    </div>
</body>
</html>

