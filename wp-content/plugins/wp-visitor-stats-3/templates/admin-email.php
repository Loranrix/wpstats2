<?php
/**
 * Admin page - Email Reports
 */
$frequencies = array(
    'daily' => __('Daily', 'wp-visitor-stats'),
    'weekly' => __('Weekly', 'wp-visitor-stats'),
    'monthly' => __('Monthly', 'wp-visitor-stats')
);

$formats = array(
    'summary' => __('Summary', 'wp-visitor-stats'),
    'detailed' => __('Detailed', 'wp-visitor-stats')
);

$periods = array(
    'today' => __('Today', 'wp-visitor-stats'),
    'week' => __('Last 7 Days', 'wp-visitor-stats'),
    'month' => __('Last 30 Days', 'wp-visitor-stats'),
    'year' => __('Last Year', 'wp-visitor-stats')
);

$current_count = $threshold_enabled ? $this->db->get_unique_visitor_count($threshold_period) : 0;
?>
<div class="wrap">
    <h1><?php _e('Email Reports', 'wp-visitor-stats'); ?></h1>
    
    <div class="wp-visitor-stats-email-settings" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 20px 0;">
        <h2><?php _e('Configure Email Reports', 'wp-visitor-stats'); ?></h2>
        
        <form id="email-settings-form">
            <?php wp_nonce_field('wp_visitor_stats_email', 'email_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="email_enabled"><?php _e('Enable Reports', 'wp-visitor-stats'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="enabled" id="email_enabled" value="1" <?php checked($enabled, true); ?>>
                            <?php _e('Send email reports automatically', 'wp-visitor-stats'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="email_address"><?php _e('Email Address', 'wp-visitor-stats'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="email" id="email_address" value="<?php echo esc_attr($email); ?>" 
                               placeholder="your@email.com" class="regular-text">
                        <p class="description">
                            <?php _e('Email address where reports will be sent.', 'wp-visitor-stats'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="email_frequency"><?php _e('Frequency', 'wp-visitor-stats'); ?></label>
                    </th>
                    <td>
                        <select name="frequency" id="email_frequency">
                            <?php foreach ($frequencies as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($frequency, $key); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php _e('How often to send reports.', 'wp-visitor-stats'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="email_format"><?php _e('Report Format', 'wp-visitor-stats'); ?></label>
                    </th>
                    <td>
                        <select name="format" id="email_format">
                            <?php foreach ($formats as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($format, $key); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php _e('Summary: Key statistics only. Detailed: Full breakdown with countries and cities.', 'wp-visitor-stats'); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="threshold_enabled"><?php _e('Threshold Alert', 'wp-visitor-stats'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="threshold_enabled" id="threshold_enabled" value="1" <?php checked($threshold_enabled, true); ?>>
                            <?php _e('Only send reports when threshold is reached', 'wp-visitor-stats'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr id="threshold-settings" style="<?php echo $threshold_enabled ? '' : 'display: none;'; ?>">
                    <th scope="row">
                        <label for="email_threshold"><?php _e('Visitor Threshold', 'wp-visitor-stats'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="threshold" id="email_threshold" value="<?php echo esc_attr($threshold); ?>" 
                               min="1" class="small-text">
                        <select name="threshold_period" id="email_threshold_period" style="margin-left: 10px;">
                            <?php foreach ($periods as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($threshold_period, $key); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php _e('Send report only when this number of visitors is reached for the selected period.', 'wp-visitor-stats'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="button button-primary"><?php _e('Save Settings', 'wp-visitor-stats'); ?></button>
                <button type="button" id="test-email" class="button"><?php _e('Send Test Email', 'wp-visitor-stats'); ?></button>
            </p>
        </form>
    </div>
    
    <?php if ($threshold_enabled): ?>
    <div class="wp-visitor-stats-email-status" style="background: #f0f6fc; padding: 20px; border-left: 4px solid #2271b1; margin: 20px 0;">
        <h3><?php _e('Threshold Status', 'wp-visitor-stats'); ?></h3>
        <table class="form-table">
            <tr>
                <th><?php _e('Current Visitors', 'wp-visitor-stats'); ?>:</th>
                <td><strong><?php echo number_format($current_count); ?></strong> <?php echo esc_html($periods[$threshold_period]); ?></td>
            </tr>
            <tr>
                <th><?php _e('Threshold', 'wp-visitor-stats'); ?>:</th>
                <td><strong><?php echo number_format($threshold); ?></strong> <?php _e('visitors', 'wp-visitor-stats'); ?></td>
            </tr>
            <tr>
                <th><?php _e('Status', 'wp-visitor-stats'); ?>:</th>
                <td>
                    <?php if ($current_count >= $threshold): ?>
                        <span style="color: #46b450; font-weight: bold;">✓ <?php _e('Threshold Reached - Reports will be sent', 'wp-visitor-stats'); ?></span>
                    <?php else: ?>
                        <span style="color: #f56e28;">
                            <?php printf(__('%d more visitors needed', 'wp-visitor-stats'), $threshold - $current_count); ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($last_sent)): ?>
    <div class="wp-visitor-stats-email-info" style="background: #fff; padding: 15px; border-left: 4px solid #46b450; margin: 20px 0;">
        <p>
            <strong><?php _e('Last Report Sent:', 'wp-visitor-stats'); ?></strong>
            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_sent))); ?>
        </p>
    </div>
    <?php endif; ?>
    
    <div class="wp-visitor-stats-email-info" style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
        <h3><?php _e('How It Works', 'wp-visitor-stats'); ?></h3>
        <ul style="margin-left: 20px;">
            <li><?php _e('Reports are sent automatically based on the selected frequency', 'wp-visitor-stats'); ?></li>
            <li><?php _e('If threshold is enabled, reports are only sent when the threshold is reached', 'wp-visitor-stats'); ?></li>
            <li><?php _e('Uses WordPress built-in email system (wp_mail)', 'wp-visitor-stats'); ?></li>
            <li><?php _e('Make sure your WordPress site can send emails (check with a test email)', 'wp-visitor-stats'); ?></li>
        </ul>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#threshold_enabled').on('change', function() {
        if ($(this).is(':checked')) {
            $('#threshold-settings').show();
        } else {
            $('#threshold-settings').hide();
        }
    });
    
    $('#email-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'wp_visitor_stats_save_email_settings',
            nonce: $('#email_nonce').val(),
            enabled: $('#email_enabled').is(':checked') ? 1 : 0,
            email: $('#email_address').val(),
            frequency: $('#email_frequency').val(),
            format: $('#email_format').val(),
            threshold_enabled: $('#threshold_enabled').is(':checked') ? 1 : 0,
            threshold: $('#email_threshold').val(),
            threshold_period: $('#email_threshold_period').val()
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
    
    $('#test-email').on('click', function() {
        var email = $('#email_address').val();
        
        if (!email) {
            alert('<?php _e('Please enter an email address first', 'wp-visitor-stats'); ?>');
            return;
        }
        
        if (!confirm('<?php _e('Send a test email to this address?', 'wp-visitor-stats'); ?>')) {
            return;
        }
        
        $(this).prop('disabled', true).text('<?php _e('Sending...', 'wp-visitor-stats'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_visitor_stats_test_email',
                nonce: $('#email_nonce').val(),
                email: email
            },
            success: function(response) {
                $('#test-email').prop('disabled', false).text('<?php _e('Send Test Email', 'wp-visitor-stats'); ?>');
                
                if (response.success) {
                    alert(response.data.message || '<?php _e('Test email sent!', 'wp-visitor-stats'); ?>');
                } else {
                    alert(response.data.message || '<?php _e('Failed to send test email', 'wp-visitor-stats'); ?>');
                }
            },
            error: function() {
                $('#test-email').prop('disabled', false).text('<?php _e('Send Test Email', 'wp-visitor-stats'); ?>');
                alert('<?php _e('Error sending test email', 'wp-visitor-stats'); ?>');
            }
        });
    });
});
</script>

