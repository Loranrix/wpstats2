<?php
/**
 * Minimal display format
 */
?>
<div class="wp-visitor-stats">
    <div class="wp-visitor-stats-container" style="padding: 6px 10px;">
        <div class="wp-visitor-stats-item">
            <span class="wp-visitor-stats-value" data-stat="today"><?php echo number_format($stats['today']); ?></span>
        </div>
        <div class="wp-visitor-stats-separator"></div>
        <div class="wp-visitor-stats-item wp-visitor-stats-online">
            <span class="wp-visitor-stats-value" data-stat="online"><?php echo number_format($stats['online']); ?></span>
        </div>
    </div>
</div>

