<?php
/**
 * Dynamic CSS styles based on format and color scheme
 */
$color_schemes = array(
    'blue' => array('primary' => '#3b82f6', 'secondary' => '#1e40af', 'text' => '#ffffff'),
    'green' => array('primary' => '#10b981', 'secondary' => '#059669', 'text' => '#ffffff'),
    'purple' => array('primary' => '#8b5cf6', 'secondary' => '#6d28d9', 'text' => '#ffffff'),
    'red' => array('primary' => '#ef4444', 'secondary' => '#dc2626', 'text' => '#ffffff'),
    'orange' => array('primary' => '#f97316', 'secondary' => '#ea580c', 'text' => '#ffffff'),
    'pink' => array('primary' => '#ec4899', 'secondary' => '#db2777', 'text' => '#ffffff'),
    'indigo' => array('primary' => '#6366f1', 'secondary' => '#4f46e5', 'text' => '#ffffff'),
    'teal' => array('primary' => '#14b8a6', 'secondary' => '#0d9488', 'text' => '#ffffff'),
);
$colors = isset($color_schemes[$color_scheme]) ? $color_schemes[$color_scheme] : $color_schemes['blue'];
?>
<style id="wp-visitor-stats-styles">
.wp-visitor-stats {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    font-size: 13px;
    line-height: 1.4;
}

.wp-visitor-stats-container {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: linear-gradient(135deg, <?php echo esc_attr($colors['primary']); ?> 0%, <?php echo esc_attr($colors['secondary']); ?> 100%);
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    color: <?php echo esc_attr($colors['text']); ?>;
    transition: all 0.3s ease;
}

.wp-visitor-stats-container:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.wp-visitor-stats-item {
    display: flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}

.wp-visitor-stats-icon {
    width: 16px;
    height: 16px;
    opacity: 0.9;
}

.wp-visitor-stats-value {
    font-weight: 600;
    font-size: 14px;
}

.wp-visitor-stats-label {
    font-size: 11px;
    opacity: 0.85;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.wp-visitor-stats-separator {
    width: 1px;
    height: 20px;
    background: rgba(255, 255, 255, 0.3);
}

.wp-visitor-stats-online {
    position: relative;
}

.wp-visitor-stats-online::before {
    content: '';
    position: absolute;
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 2s infinite;
    left: -12px;
    top: 50%;
    transform: translateY(-50%);
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: translateY(-50%) scale(1); }
    50% { opacity: 0.5; transform: translateY(-50%) scale(1.2); }
}

/* Responsive */
@media (max-width: 768px) {
    .wp-visitor-stats-container {
        font-size: 11px;
        padding: 6px 10px;
        gap: 6px;
    }
    
    .wp-visitor-stats-value {
        font-size: 12px;
    }
    
    .wp-visitor-stats-label {
        font-size: 10px;
    }
}
</style>

