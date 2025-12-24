<?php
/**
 * WordPress Dashboard Widget for Visitor Stats
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Visitor_Stats_Dashboard {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        $this->init();
    }
    
    /**
     * Initialize dashboard widget
     */
    private function init() {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        add_action('wp_ajax_wp_visitor_stats_dashboard_refresh', array($this, 'ajax_refresh_stats'));
    }
    
    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        // Only show if user can see stats
        if (!$this->db->can_see_stats()) {
            return;
        }
        
        // Add widget with proper context and priority for drag & drop
        wp_add_dashboard_widget(
            'wp_visitor_stats_dashboard',
            __('Visitor Statistics', 'wp-visitor-stats'),
            array($this, 'render_dashboard_widget'),
            null,
            null,
            'normal',
            'high'
        );
        
        // Force widget to be sortable by ensuring it's in the correct meta box structure
        global $wp_meta_boxes;
        $dashboard = $wp_meta_boxes['dashboard']['normal']['high'];
        if (isset($dashboard['wp_visitor_stats_dashboard'])) {
            // Ensure widget has no special args that would prevent sorting
            $wp_meta_boxes['dashboard']['normal']['high']['wp_visitor_stats_dashboard']['args'] = array();
        }
    }
    
    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        try {
            $stats = array(
                'today' => (int) $this->db->get_unique_visitor_count('today'),
                'week' => (int) $this->db->get_unique_visitor_count('week'),
                'month' => (int) $this->db->get_unique_visitor_count('month'),
                'year' => (int) $this->db->get_unique_visitor_count('year'),
                'online' => (int) $this->db->get_online_count()
            );
            
            $top_countries = array();
            $top_cities = array();
            
            try {
                require_once WP_VISITOR_STATS_PLUGIN_DIR . 'includes/class-wp-visitor-stats-geolocation.php';
                $geolocation = new WP_Visitor_Stats_Geolocation($this->db);
                $top_countries = $geolocation->get_visitors_by_country('today');
                $top_cities = $geolocation->get_visitors_by_city('today');
            } catch (Exception $e) {
                error_log('WP Visitor Stats - Error loading geolocation in dashboard: ' . $e->getMessage());
                // Continue without geolocation data
            }
        
        ?>
        <div id="wp-visitor-stats-dashboard-content">
            <style>
                .wp-visitor-stats-dashboard {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                }
                .wp-visitor-stats-dashboard .stats-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 10px;
                    margin: 15px 0;
                }
                .wp-visitor-stats-dashboard .stat-box {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 15px;
                    border-radius: 6px;
                    text-align: center;
                }
                .wp-visitor-stats-dashboard .stat-value {
                    font-size: 24px;
                    font-weight: bold;
                    margin: 5px 0;
                }
                .wp-visitor-stats-dashboard .stat-label {
                    font-size: 11px;
                    opacity: 0.9;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .wp-visitor-stats-dashboard .online-indicator {
                    display: inline-block;
                    width: 8px;
                    height: 8px;
                    background: #10b981;
                    border-radius: 50%;
                    margin-right: 5px;
                    animation: pulse 2s infinite;
                }
                @keyframes pulse {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.5; }
                }
                .wp-visitor-stats-dashboard .countries-list,
                .wp-visitor-stats-dashboard .cities-list {
                    margin-top: 15px;
                    font-size: 12px;
                }
                .wp-visitor-stats-dashboard .countries-list table,
                .wp-visitor-stats-dashboard .cities-list table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .wp-visitor-stats-dashboard .countries-list td,
                .wp-visitor-stats-dashboard .cities-list td {
                    padding: 5px;
                    border-bottom: 1px solid #eee;
                }
                .wp-visitor-stats-dashboard .flag {
                    width: 20px;
                    height: auto;
                    vertical-align: middle;
                    margin-right: 5px;
                }
                .wp-visitor-stats-dashboard .refresh-btn {
                    float: right;
                    margin-top: -30px;
                }
            </style>
            
            <div class="wp-visitor-stats-dashboard">
                <button class="button button-small refresh-btn" id="refresh-stats-btn">
                    <span class="dashicons dashicons-update" style="font-size: 16px; line-height: 1.5;"></span> <?php _e('Refresh', 'wp-visitor-stats'); ?>
                </button>
                
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-label"><?php _e('Today', 'wp-visitor-stats'); ?></div>
                        <div class="stat-value" id="stat-today"><?php echo number_format($stats['today']); ?></div>
                    </div>
                    
                    <div class="stat-box">
                        <div class="stat-label"><?php _e('This Week', 'wp-visitor-stats'); ?></div>
                        <div class="stat-value" id="stat-week"><?php echo number_format($stats['week']); ?></div>
                    </div>
                    
                    <div class="stat-box">
                        <div class="stat-label"><?php _e('This Month', 'wp-visitor-stats'); ?></div>
                        <div class="stat-value" id="stat-month"><?php echo number_format($stats['month']); ?></div>
                    </div>
                    
                    <div class="stat-box">
                        <div class="stat-label">
                            <span class="online-indicator"></span>
                            <?php _e('Online Now', 'wp-visitor-stats'); ?>
                        </div>
                        <div class="stat-value" id="stat-online"><?php echo number_format($stats['online']); ?></div>
                    </div>
                </div>
                
                <?php if (!empty($top_countries)): ?>
                <div class="countries-list">
                    <h3 style="margin: 15px 0 10px 0; font-size: 14px;"><?php _e('Top Countries Today', 'wp-visitor-stats'); ?></h3>
                    <table>
                        <tbody>
                            <?php foreach (array_slice($top_countries, 0, 5) as $country): ?>
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
                </div>
                <?php endif; ?>
                
                <?php if (!empty($top_cities)): ?>
                <div class="cities-list">
                    <h3 style="margin: 15px 0 10px 0; font-size: 14px;"><?php _e('Top Cities Today', 'wp-visitor-stats'); ?></h3>
                    <table>
                        <tbody>
                            <?php foreach (array_slice($top_cities, 0, 5) as $city): ?>
                            <tr>
                                <td>
                                    <img src="https://flagcdn.com/w20/<?php echo strtolower(esc_attr($city->country_code)); ?>.svg" 
                                         alt="<?php echo esc_attr($city->country_code); ?>" class="flag"
                                         onerror="this.style.display='none';">
                                    <?php echo esc_html($city->city); ?>, <?php echo esc_html($city->country_name); ?>
                                </td>
                                <td style="text-align: right; font-weight: bold;">
                                    <?php echo number_format($city->unique_visitors); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
                <p style="margin-top: 15px; text-align: center;">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-visitor-stats')); ?>" class="button button-primary">
                        <?php _e('View Full Statistics', 'wp-visitor-stats'); ?>
                    </a>
                    <br>
                    <small style="margin-top: 5px; display: inline-block;">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-visitor-stats-countries')); ?>"><?php _e('By Country', 'wp-visitor-stats'); ?></a> | 
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-visitor-stats-cities')); ?>"><?php _e('By City', 'wp-visitor-stats'); ?></a>
                    </small>
                </p>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                $('#refresh-stats-btn').on('click', function() {
                    var $btn = $(this);
                    $btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="font-size: 16px; line-height: 1.5; animation: spin 1s linear infinite;"></span> <?php _e('Refreshing...', 'wp-visitor-stats'); ?>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wp_visitor_stats_dashboard_refresh',
                            nonce: '<?php echo wp_create_nonce('wp_visitor_stats_dashboard'); ?>'
                        },
                        success: function(response) {
                            if (response.success && response.data) {
                                $('#stat-today').text(response.data.today.toLocaleString());
                                $('#stat-week').text(response.data.week.toLocaleString());
                                $('#stat-month').text(response.data.month.toLocaleString());
                                $('#stat-online').text(response.data.online.toLocaleString());
                            }
                            $btn.prop('disabled', false).html('<span class="dashicons dashicons-update" style="font-size: 16px; line-height: 1.5;"></span> <?php _e('Refresh', 'wp-visitor-stats'); ?>');
                        },
                        error: function() {
                            $btn.prop('disabled', false).html('<span class="dashicons dashicons-update" style="font-size: 16px; line-height: 1.5;"></span> <?php _e('Refresh', 'wp-visitor-stats'); ?>');
                        }
                    });
                });
            });
            </script>
        </div>
        <?php
        } catch (Exception $e) {
            echo '<div class="error"><p><strong>' . __('Error loading widget:', 'wp-visitor-stats') . '</strong> ' . esc_html($e->getMessage()) . '</p></div>';
            error_log('WP Visitor Stats - Error in render_dashboard_widget: ' . $e->getMessage());
        }
    }
    
    /**
     * AJAX refresh stats
     */
    public function ajax_refresh_stats() {
        check_ajax_referer('wp_visitor_stats_dashboard', 'nonce');
        
        if (!$this->db->can_see_stats()) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $stats = array(
            'today' => $this->db->get_unique_visitor_count('today'),
            'week' => $this->db->get_unique_visitor_count('week'),
            'month' => $this->db->get_unique_visitor_count('month'),
            'year' => $this->db->get_unique_visitor_count('year'),
            'online' => $this->db->get_online_count()
        );
        
        wp_send_json_success($stats);
    }
}

