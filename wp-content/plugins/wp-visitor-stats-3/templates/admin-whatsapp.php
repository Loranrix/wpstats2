<?php
/**
 * Admin page - WhatsApp Alerts
 */
$periods = array(
    'today' => __('Today', 'wp-visitor-stats'),
    'week' => __('Last 7 Days', 'wp-visitor-stats'),
    'month' => __('Last 30 Days', 'wp-visitor-stats'),
    'year' => __('Last Year', 'wp-visitor-stats')
);

$current_count = $this->db->get_unique_visitor_count($period);
?>
<div class="wrap">
    <h1><?php _e('WhatsApp Alerts', 'wp-visitor-stats'); ?></h1>
    
    <div class="wp-visitor-stats-whatsapp-settings" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 20px 0;">
        <h2><?php _e('Configure WhatsApp Alerts', 'wp-visitor-stats'); ?></h2>
        
        <form id="whatsapp-settings-form">
            <?php wp_nonce_field('wp_visitor_stats_whatsapp', 'whatsapp_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="whatsapp_enabled"><?php _e('Enable Alerts', 'wp-visitor-stats'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="enabled" id="whatsapp_enabled" value="1" <?php checked($enabled, true); ?>>
                            <?php _e('Send WhatsApp alerts when threshold is reached', 'wp-visitor-stats'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="whatsapp_phone"><?php _e('WhatsApp Number', 'wp-visitor-stats'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="phone" id="whatsapp_phone" value="<?php echo esc_attr($phone); ?>" 
                               placeholder="+37120123456" class="regular-text">
                        <p class="description">
                            <?php _e('Enter your WhatsApp number in international format (e.g., +37120123456). Must start with + followed by country code and number (7-15 digits total). No spaces or dashes.', 'wp-visitor-stats'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="callmebot_apikey"><?php _e('CallMeBot API Key', 'wp-visitor-stats'); ?></label>
                    </th>
                    <td>
                        <?php $callmebot_apikey = get_option('wp_visitor_stats_callmebot_apikey', ''); ?>
                        <input type="text" name="callmebot_apikey" id="callmebot_apikey" value="<?php echo esc_attr($callmebot_apikey); ?>" 
                               placeholder="Your API Key" class="regular-text">
                        <p class="description">
                            <?php _e('Optional: Get a free API key by sending "I allow callmebot to send me messages" to +34 623 78 95 80 on WhatsApp. See instructions below.', 'wp-visitor-stats'); ?>
                            <br>
                            <?php _e('Leave empty if using webhook instead.', 'wp-visitor-stats'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="whatsapp_webhook"><?php _e('Webhook URL', 'wp-visitor-stats'); ?></label>
                    </th>
                    <td>
                        <?php $webhook_url = get_option('wp_visitor_stats_whatsapp_webhook', ''); ?>
                        <input type="url" name="whatsapp_webhook" id="whatsapp_webhook" value="<?php echo esc_attr($webhook_url); ?>" 
                               placeholder="https://your-webhook-url.com/whatsapp" class="regular-text">
                        <p class="description">
                            <?php _e('Optional: Alternative to CallMeBot. Provide a webhook URL that accepts POST requests with phone and message fields.', 'wp-visitor-stats'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="whatsapp_threshold"><?php _e('Visitor Threshold', 'wp-visitor-stats'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="threshold" id="whatsapp_threshold" value="<?php echo esc_attr($threshold); ?>" 
                               min="1" class="small-text">
                        <p class="description">
                            <?php _e('Send alert when this number of unique visitors is reached.', 'wp-visitor-stats'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="whatsapp_period"><?php _e('Period', 'wp-visitor-stats'); ?></label>
                    </th>
                    <td>
                        <select name="period" id="whatsapp_period">
                            <?php foreach ($periods as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($period, $key); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php _e('Count visitors for this period.', 'wp-visitor-stats'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="button button-primary"><?php _e('Save Settings', 'wp-visitor-stats'); ?></button>
                <button type="button" id="test-whatsapp" class="button"><?php _e('Send Test Message', 'wp-visitor-stats'); ?></button>
            </p>
        </form>
    </div>
    
    <div class="wp-visitor-stats-whatsapp-status" style="background: #f0f6fc; padding: 20px; border-left: 4px solid #2271b1; margin: 20px 0;">
        <h3><?php _e('Current Status', 'wp-visitor-stats'); ?></h3>
        <table class="form-table">
            <tr>
                <th><?php _e('Current Visitors', 'wp-visitor-stats'); ?>:</th>
                <td><strong><?php echo number_format($current_count); ?></strong> <?php echo esc_html($periods[$period]); ?></td>
            </tr>
            <tr>
                <th><?php _e('Threshold', 'wp-visitor-stats'); ?>:</th>
                <td><strong><?php echo number_format($threshold); ?></strong> <?php _e('visitors', 'wp-visitor-stats'); ?></td>
            </tr>
            <tr>
                <th><?php _e('Status', 'wp-visitor-stats'); ?>:</th>
                <td>
                    <?php if ($current_count >= $threshold): ?>
                        <span style="color: #46b450; font-weight: bold;">✓ <?php _e('Threshold Reached!', 'wp-visitor-stats'); ?></span>
                    <?php else: ?>
                        <span style="color: #f56e28;">
                            <?php printf(__('%d more visitors needed', 'wp-visitor-stats'), $threshold - $current_count); ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if (!empty($last_sent)): ?>
            <tr>
                <th><?php _e('Last Alert Sent', 'wp-visitor-stats'); ?>:</th>
                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_sent))); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <div class="wp-visitor-stats-whatsapp-info" style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
        <h3><?php _e('How It Works', 'wp-visitor-stats'); ?></h3>
        <ul style="margin-left: 20px;">
            <li><?php _e('Alerts are checked daily automatically', 'wp-visitor-stats'); ?></li>
            <li><?php _e('You will receive a WhatsApp message when the threshold is reached', 'wp-visitor-stats'); ?></li>
            <li><?php _e('Only one alert per day will be sent (to avoid spam)', 'wp-visitor-stats'); ?></li>
            <li><?php _e('Configure either CallMeBot API key (free) or a webhook URL to send messages', 'wp-visitor-stats'); ?></li>
            <li><?php _e('Phone number must be in international format: +country code + number (e.g., +37120123456)', 'wp-visitor-stats'); ?></li>
        </ul>
    </div>
    
    <div class="wp-visitor-stats-callmebot-instructions" style="background: #e7f3ff; padding: 20px; border-left: 4px solid #2271b1; margin: 20px 0;">
        <h3><?php _e('How to Get CallMeBot API Key', 'wp-visitor-stats'); ?></h3>
        <ol style="margin-left: 20px; line-height: 2;">
            <li>
                <strong><?php _e('Step 1:', 'wp-visitor-stats'); ?></strong> 
                <?php _e('Add CallMeBot to your WhatsApp contacts:', 'wp-visitor-stats'); ?>
                <br>
                <code style="background: #fff; padding: 5px 10px; border-radius: 3px; font-size: 16px; font-weight: bold;">+34 623 78 95 80</code>
            </li>
            <li>
                <strong><?php _e('Step 2:', 'wp-visitor-stats'); ?></strong> 
                <?php _e('Send this exact message to CallMeBot via WhatsApp:', 'wp-visitor-stats'); ?>
                <br>
                <code style="background: #fff; padding: 5px 10px; border-radius: 3px; font-size: 14px; display: inline-block; margin-top: 5px;">I allow callmebot to send me messages</code>
            </li>
            <li>
                <strong><?php _e('Step 3:', 'wp-visitor-stats'); ?></strong> 
                <?php _e('Wait for CallMeBot to reply with your API key (usually within 2 minutes)', 'wp-visitor-stats'); ?>
            </li>
            <li>
                <strong><?php _e('Step 4:', 'wp-visitor-stats'); ?></strong> 
                <?php _e('Copy the API key and paste it in the "CallMeBot API Key" field above', 'wp-visitor-stats'); ?>
            </li>
        </ol>
        
        <div style="background: #fff; padding: 15px; margin-top: 15px; border-radius: 5px; border: 1px solid #ddd;">
            <strong style="color: #d63638;"><?php _e('⚠️ Troubleshooting:', 'wp-visitor-stats'); ?></strong>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li><?php _e('If you don\'t receive a response within 2 minutes, wait 24 hours and try again', 'wp-visitor-stats'); ?></li>
                <li><?php _e('Make sure you added the correct number: +34 623 78 95 80', 'wp-visitor-stats'); ?></li>
                <li><?php _e('Send the message exactly as shown: "I allow callmebot to send me messages" (case-sensitive)', 'wp-visitor-stats'); ?></li>
                <li><?php _e('Check your internet connection and WhatsApp connection', 'wp-visitor-stats'); ?></li>
                <li><?php _e('If CallMeBot doesn\'t respond, you can use a webhook URL instead (see field below)', 'wp-visitor-stats'); ?></li>
            </ul>
        </div>
        
        <p style="margin-top: 15px;">
            <a href="https://www.callmebot.com/blog/free-api-whatsapp-messages/" target="_blank" style="text-decoration: underline;">
                <?php _e('Visit CallMeBot website for more information', 'wp-visitor-stats'); ?>
            </a>
        </p>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#whatsapp-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'wp_visitor_stats_save_whatsapp_settings',
            nonce: $('#whatsapp_nonce').val(),
            enabled: $('#whatsapp_enabled').is(':checked') ? 1 : 0,
            phone: $('#whatsapp_phone').val(),
            threshold: $('#whatsapp_threshold').val(),
            period: $('#whatsapp_period').val(),
            callmebot_apikey: $('#callmebot_apikey').val(),
            whatsapp_webhook: $('#whatsapp_webhook').val()
        };
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Settings saved!', 'wp-visitor-stats'); ?>');
                    location.reload();
                } else {
                    alert(response.data.message || '<?php _e('Error saving settings', 'wp-visitor-stats'); ?>');
                }
            }
        });
    });
    
    $('#test-whatsapp').on('click', function() {
        var phone = $('#whatsapp_phone').val();
        
        if (!phone) {
            alert('<?php _e('Please enter a phone number first', 'wp-visitor-stats'); ?>');
            return;
        }
        
        if (!confirm('<?php _e('Send a test message to this number?', 'wp-visitor-stats'); ?>')) {
            return;
        }
        
        $(this).prop('disabled', true).text('<?php _e('Sending...', 'wp-visitor-stats'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_visitor_stats_test_whatsapp',
                nonce: $('#whatsapp_nonce').val(),
                phone: phone
            },
            success: function(response) {
                $('#test-whatsapp').prop('disabled', false).text('<?php _e('Send Test Message', 'wp-visitor-stats'); ?>');
                
                if (response.success) {
                    alert(response.data.message || '<?php _e('Test message sent!', 'wp-visitor-stats'); ?>');
                } else {
                    alert(response.data.message || '<?php _e('Failed to send test message', 'wp-visitor-stats'); ?>');
                }
            },
            error: function() {
                $('#test-whatsapp').prop('disabled', false).text('<?php _e('Send Test Message', 'wp-visitor-stats'); ?>');
                alert('<?php _e('Error sending test message', 'wp-visitor-stats'); ?>');
            }
        });
    });
});
</script>

