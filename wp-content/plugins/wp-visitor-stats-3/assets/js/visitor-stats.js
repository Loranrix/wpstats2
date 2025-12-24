/**
 * WP Visitor Stats - Frontend JavaScript
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Update online count periodically
        if (typeof wpVisitorStats !== 'undefined') {
            setInterval(function() {
                updateOnlineCount();
            }, wpVisitorStats.refreshInterval);
        }
        
        // Ping server to update online status
        pingServer();
    });
    
    /**
     * Ping server to update online status
     */
    function pingServer() {
        if (typeof wpVisitorStats === 'undefined') {
            return;
        }
        
        $.ajax({
            url: wpVisitorStats.ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_visitor_stats_ping',
                nonce: wpVisitorStats.nonce,
                page_url: window.location.href
            },
            success: function(response) {
                if (response.success && response.data.online_count !== undefined) {
                    updateOnlineDisplay(response.data.online_count);
                }
            },
            error: function() {
                // Silently fail
            }
        });
    }
    
    /**
     * Update online count
     */
    function updateOnlineCount() {
        if (typeof wpVisitorStats === 'undefined') {
            return;
        }
        
        $.ajax({
            url: wpVisitorStats.ajaxurl,
            type: 'POST',
            data: {
                action: 'wp_visitor_stats_get',
                nonce: wpVisitorStats.nonce || ''
            },
            success: function(response) {
                if (response.success && response.data) {
                    updateStatsDisplay(response.data);
                }
            },
            error: function() {
                // Silently fail
            }
        });
    }
    
    /**
     * Update online display
     */
    function updateOnlineDisplay(count) {
        $('.wp-visitor-stats-value[data-stat="online"]').each(function() {
            $(this).text(formatNumber(count));
        });
    }
    
    /**
     * Update stats display
     */
    function updateStatsDisplay(stats) {
        if (stats.today !== undefined) {
            $('.wp-visitor-stats-value[data-stat="today"]').text(formatNumber(stats.today));
        }
        if (stats.week !== undefined) {
            $('.wp-visitor-stats-value[data-stat="week"]').text(formatNumber(stats.week));
        }
        if (stats.month !== undefined) {
            $('.wp-visitor-stats-value[data-stat="month"]').text(formatNumber(stats.month));
        }
        if (stats.year !== undefined) {
            $('.wp-visitor-stats-value[data-stat="year"]').text(formatNumber(stats.year));
        }
        if (stats.online !== undefined) {
            $('.wp-visitor-stats-value[data-stat="online"]').text(formatNumber(stats.online));
        }
    }
    
    /**
     * Format number with commas
     */
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
})(jQuery);

