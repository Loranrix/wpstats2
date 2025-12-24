<?php
/**
 * Admin page - SEO Optimization
 */
?>
<div class="wrap">
    <h1><?php _e('SEO Optimization Tools', 'wp-visitor-stats'); ?></h1>
    
    <div class="wp-visitor-stats-seo-overview" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 20px 0;">
        <h2><?php _e('SEO Overview', 'wp-visitor-stats'); ?></h2>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 20px 0;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;"><?php echo count($top_pages); ?></div>
                <div style="font-size: 14px; opacity: 0.9;"><?php _e('Tracked Pages', 'wp-visitor-stats'); ?></div>
            </div>
            
            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;"><?php echo count($seo_insights['traffic_sources']); ?></div>
                <div style="font-size: 14px; opacity: 0.9;"><?php _e('Traffic Sources', 'wp-visitor-stats'); ?></div>
            </div>
            
            <div style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 32px; font-weight: bold;"><?php echo count($keyword_suggestions); ?></div>
                <div style="font-size: 14px; opacity: 0.9;"><?php _e('Keywords Found', 'wp-visitor-stats'); ?></div>
            </div>
        </div>
    </div>
    
    <div class="wp-visitor-stats-seo-top-pages" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 20px 0;">
        <h2><?php _e('Top Performing Pages', 'wp-visitor-stats'); ?></h2>
        <p class="description"><?php _e('Pages with the most unique visitors. Focus on optimizing these for better SEO.', 'wp-visitor-stats'); ?></p>
        
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php _e('Page URL', 'wp-visitor-stats'); ?></th>
                    <th style="text-align: right;"><?php _e('Unique Visitors', 'wp-visitor-stats'); ?></th>
                    <th style="text-align: right;"><?php _e('Total Visits', 'wp-visitor-stats'); ?></th>
                    <th><?php _e('Last Visit', 'wp-visitor-stats'); ?></th>
                    <th><?php _e('Actions', 'wp-visitor-stats'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_pages as $page): ?>
                <tr>
                    <td>
                        <a href="<?php echo esc_url($page->page_url); ?>" target="_blank">
                            <?php echo esc_html($page->page_url); ?>
                        </a>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($page->unique_visitors); ?>
                    </td>
                    <td style="text-align: right;">
                        <?php echo number_format($page->total_visits); ?>
                    </td>
                    <td>
                        <?php echo $page->last_visit ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($page->last_visit)) : '-'; ?>
                    </td>
                    <td>
                        <button class="button button-small analyze-page-btn" data-url="<?php echo esc_attr($page->page_url); ?>">
                            <?php _e('Analyze SEO', 'wp-visitor-stats'); ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="wp-visitor-stats-seo-traffic-sources" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 20px 0;">
        <h2><?php _e('Traffic Sources (Last 30 Days)', 'wp-visitor-stats'); ?></h2>
        
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php _e('Source', 'wp-visitor-stats'); ?></th>
                    <th style="text-align: right;"><?php _e('Visitors', 'wp-visitor-stats'); ?></th>
                    <th style="width: 300px;"><?php _e('Percentage', 'wp-visitor-stats'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_source_visitors = array_sum(array_column($seo_insights['traffic_sources'], 'visitors'));
                foreach ($seo_insights['traffic_sources'] as $source): 
                    $percentage = $total_source_visitors > 0 ? ($source->visitors / $total_source_visitors) * 100 : 0;
                ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($source->source); ?></strong>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        <?php echo number_format($source->visitors); ?>
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
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="wp-visitor-stats-seo-keywords" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 20px 0;">
        <h2><?php _e('Keywords from Search Engines', 'wp-visitor-stats'); ?></h2>
        <p class="description"><?php _e('Keywords that brought visitors to your site (extracted from Google referrers).', 'wp-visitor-stats'); ?></p>
        
        <?php if (!empty($keyword_suggestions)): ?>
        <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px;">
            <?php foreach ($keyword_suggestions as $keyword => $count): ?>
            <span style="background: #f0f6fc; border: 1px solid #2271b1; padding: 8px 12px; border-radius: 4px; font-size: 13px;">
                <strong><?php echo esc_html($keyword); ?></strong>
                <span style="color: #666; margin-left: 5px;">(<?php echo number_format($count); ?>)</span>
            </span>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p><?php _e('No keywords found yet. Keywords are extracted from Google search referrers.', 'wp-visitor-stats'); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="wp-visitor-stats-seo-features" style="background: #fff3cd; padding: 20px; border-left: 4px solid #ffc107; margin: 20px 0;">
        <h3><?php _e('SEO Features Enabled', 'wp-visitor-stats'); ?></h3>
        <ul style="margin-left: 20px;">
            <li><?php _e('✓ Structured Data (Schema.org) for articles and breadcrumbs', 'wp-visitor-stats'); ?></li>
            <li><?php _e('✓ Enhanced sitemap based on visitor statistics', 'wp-visitor-stats'); ?></li>
            <li><?php _e('✓ Page performance analysis', 'wp-visitor-stats'); ?></li>
            <li><?php _e('✓ Keyword extraction from search engines', 'wp-visitor-stats'); ?></li>
            <li><?php _e('✓ Traffic source analysis', 'wp-visitor-stats'); ?></li>
        </ul>
        <p style="margin-top: 15px;">
            <strong><?php _e('Enhanced Sitemap:', 'wp-visitor-stats'); ?></strong>
            <a href="<?php echo home_url('/visitor-stats-sitemap.xml'); ?>" target="_blank">
                <?php echo home_url('/visitor-stats-sitemap.xml'); ?>
            </a>
        </p>
    </div>
</div>

<div id="seo-analysis-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100000;">
    <div style="background: white; margin: 50px auto; max-width: 800px; padding: 30px; border-radius: 8px; max-height: 80vh; overflow-y: auto;">
        <h2><?php _e('SEO Analysis', 'wp-visitor-stats'); ?></h2>
        <div id="seo-analysis-content"></div>
        <p style="margin-top: 20px;">
            <button class="button" onclick="document.getElementById('seo-analysis-modal').style.display='none';"><?php _e('Close', 'wp-visitor-stats'); ?></button>
        </p>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.analyze-page-btn').on('click', function() {
        var url = $(this).data('url');
        var $btn = $(this);
        
        $btn.prop('disabled', true).text('<?php _e('Analyzing...', 'wp-visitor-stats'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_visitor_stats_seo_analyze',
                nonce: '<?php echo wp_create_nonce('wp_visitor_stats_seo'); ?>',
                page_url: url
            },
            success: function(response) {
                $btn.prop('disabled', false).text('<?php _e('Analyze SEO', 'wp-visitor-stats'); ?>');
                
                if (response.success) {
                    var data = response.data;
                    var html = '<h3>' + data.url + '</h3>';
                    html += '<table class="widefat" style="margin-top: 15px;">';
                    html += '<tr><th><?php _e('Unique Visitors', 'wp-visitor-stats'); ?>:</th><td><strong>' + data.unique_visitors.toLocaleString() + '</strong></td></tr>';
                    html += '<tr><th><?php _e('Total Visits', 'wp-visitor-stats'); ?>:</th><td>' + data.total_visits.toLocaleString() + '</td></tr>';
                    
                    if (data.title) {
                        html += '<tr><th><?php _e('Page Title', 'wp-visitor-stats'); ?>:</th><td>' + data.title + '</td></tr>';
                    }
                    if (data.word_count) {
                        html += '<tr><th><?php _e('Word Count', 'wp-visitor-stats'); ?>:</th><td>' + data.word_count + '</td></tr>';
                    }
                    
                    html += '</table>';
                    
                    if (data.recommendations && data.recommendations.length > 0) {
                        html += '<h4 style="margin-top: 20px;"><?php _e('SEO Recommendations', 'wp-visitor-stats'); ?>:</h4>';
                        html += '<ul>';
                        data.recommendations.forEach(function(rec) {
                            var icon = rec.type === 'success' ? '✓' : (rec.type === 'warning' ? '⚠' : 'ℹ');
                            html += '<li style="margin: 10px 0;"><strong>' + icon + '</strong> ' + rec.message + '</li>';
                        });
                        html += '</ul>';
                    }
                    
                    $('#seo-analysis-content').html(html);
                    $('#seo-analysis-modal').show();
                } else {
                    alert(response.data.message || '<?php _e('Error analyzing page', 'wp-visitor-stats'); ?>');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('<?php _e('Analyze SEO', 'wp-visitor-stats'); ?>');
                alert('<?php _e('Error analyzing page', 'wp-visitor-stats'); ?>');
            }
        });
    });
});
</script>

