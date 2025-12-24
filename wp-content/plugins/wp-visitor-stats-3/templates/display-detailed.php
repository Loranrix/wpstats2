<?php
/**
 * Detailed display format
 */
?>
<div class="wp-visitor-stats">
    <div class="wp-visitor-stats-container">
        <div class="wp-visitor-stats-item">
            <svg class="wp-visitor-stats-icon" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
            </svg>
            <span class="wp-visitor-stats-value" data-stat="today"><?php echo number_format($stats['today']); ?></span>
            <span class="wp-visitor-stats-label">Today</span>
        </div>
        
        <div class="wp-visitor-stats-separator"></div>
        
        <div class="wp-visitor-stats-item">
            <span class="wp-visitor-stats-value" data-stat="week"><?php echo number_format($stats['week']); ?></span>
            <span class="wp-visitor-stats-label">Week</span>
        </div>
        
        <div class="wp-visitor-stats-separator"></div>
        
        <div class="wp-visitor-stats-item">
            <span class="wp-visitor-stats-value" data-stat="month"><?php echo number_format($stats['month']); ?></span>
            <span class="wp-visitor-stats-label">Month</span>
        </div>
        
        <div class="wp-visitor-stats-separator"></div>
        
        <div class="wp-visitor-stats-item">
            <span class="wp-visitor-stats-value" data-stat="year"><?php echo number_format($stats['year']); ?></span>
            <span class="wp-visitor-stats-label">Year</span>
        </div>
        
        <div class="wp-visitor-stats-separator"></div>
        
        <div class="wp-visitor-stats-item wp-visitor-stats-online">
            <svg class="wp-visitor-stats-icon" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="wp-visitor-stats-value" data-stat="online"><?php echo number_format($stats['online']); ?></span>
            <span class="wp-visitor-stats-label">Online</span>
        </div>
    </div>
</div>

