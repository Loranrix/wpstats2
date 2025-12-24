<?php
/**
 * API endpoints for WP Visitor Stats
 */

if (!defined('ABSPATH')) {
    exit;
}

class WP_Visitor_Stats_API {
    
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        $this->init();
    }
    
    /**
     * Initialize API
     */
    private function init() {
        // Handle cookie token
        add_action('init', array($this, 'handle_cookie_token'));
        
        // AJAX endpoint for stats
        add_action('wp_ajax_wp_visitor_stats_get', array($this, 'ajax_get_stats'));
        add_action('wp_ajax_nopriv_wp_visitor_stats_get', array($this, 'ajax_get_stats'));
    }
    
    /**
     * Handle cookie token
     */
    public function handle_cookie_token() {
        if (isset($_GET['wp_visitor_stats_token'])) {
            $token = sanitize_text_field($_GET['wp_visitor_stats_token']);
            $saved_token = get_option('wp_visitor_stats_cookie_token', '');
            
            if ($token === $saved_token && !empty($saved_token)) {
                // Get site domain for cookie
                $site_url = parse_url(home_url());
                $domain = isset($site_url['host']) ? $site_url['host'] : '';
                
                // Remove www. prefix for cookie domain compatibility
                if (strpos($domain, 'www.') === 0) {
                    $domain = substr($domain, 4);
                }
                
                // Set cookie for 1 year with proper mobile support
                // Use SameSite=None for cross-site compatibility (mobile apps, etc.)
                // Secure flag only if HTTPS, otherwise mobile HTTP sites won't work
                $is_https = is_ssl() || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
                
                // Set cookie with SameSite attribute for better mobile support
                $cookie_params = array(
                    'expires' => time() + (365 * 24 * 60 * 60),
                    'path' => '/',
                    'domain' => $domain,
                    'secure' => $is_https,
                    'httponly' => false, // Allow JavaScript access if needed
                    'samesite' => 'Lax' // Better mobile compatibility
                );
                
                // PHP 7.3+ supports array format
                if (PHP_VERSION_ID >= 70300) {
                    setcookie('wp_visitor_stats_token', $token, $cookie_params);
                } else {
                    // Fallback for older PHP versions
                    $cookie_string = 'wp_visitor_stats_token=' . urlencode($token);
                    $cookie_string .= '; expires=' . gmdate('D, d M Y H:i:s \G\M\T', $cookie_params['expires']);
                    $cookie_string .= '; path=' . $cookie_params['path'];
                    if (!empty($cookie_params['domain'])) {
                        $cookie_string .= '; domain=' . $cookie_params['domain'];
                    }
                    if ($cookie_params['secure']) {
                        $cookie_string .= '; secure';
                    }
                    $cookie_string .= '; SameSite=' . $cookie_params['samesite'];
                    header('Set-Cookie: ' . $cookie_string, false);
                }
                
                // Also set via JavaScript for better mobile browser support
                add_action('wp_footer', function() use ($token) {
                    ?>
                    <script>
                    document.cookie = 'wp_visitor_stats_token=<?php echo esc_js($token); ?>; path=/; max-age=<?php echo 365 * 24 * 60 * 60; ?>; SameSite=Lax<?php echo is_ssl() ? '; secure' : ''; ?>';
                    </script>
                    <?php
                }, 999);
                
                // Show confirmation page
                $this->show_cookie_confirmation();
                exit;
            }
        }
    }
    
    /**
     * Show cookie confirmation page
     */
    private function show_cookie_confirmation() {
        // Prevent WordPress from loading the theme
        status_header(200);
        nocache_headers();
        
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php _e('Cookie Access Confirmed', 'wp-visitor-stats'); ?></title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    margin: 0;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                }
                .confirmation-box {
                    background: white;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                    text-align: center;
                    max-width: 400px;
                }
                .ok-icon {
                    font-size: 80px;
                    color: #46b450;
                    margin-bottom: 20px;
                }
                h1 {
                    color: #333;
                    margin: 0 0 10px 0;
                    font-size: 28px;
                }
                p {
                    color: #666;
                    margin: 10px 0;
                    font-size: 16px;
                }
                .success {
                    color: #46b450;
                    font-weight: bold;
                    font-size: 24px;
                }
            </style>
        </head>
        <body>
            <div class="confirmation-box">
                <div class="ok-icon">✓</div>
                <h1><?php _e('OK', 'wp-visitor-stats'); ?></h1>
                <p class="success"><?php _e('Cookie Access Activated!', 'wp-visitor-stats'); ?></p>
                <p><?php _e('You can now see visitor statistics on the website.', 'wp-visitor-stats'); ?></p>
            </div>
        </body>
        </html>
        <?php
    }
    
    /**
     * AJAX get stats
     */
    public function ajax_get_stats() {
        // Check nonce if provided
        if (isset($_POST['nonce'])) {
            check_ajax_referer('wp_visitor_stats_get', 'nonce');
        }
        
        if (!$this->db->can_see_stats()) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $stats = array(
            'today' => $this->db->get_visitor_count('today'),
            'week' => $this->db->get_visitor_count('week'),
            'month' => $this->db->get_visitor_count('month'),
            'year' => $this->db->get_visitor_count('year'),
            'online' => $this->db->get_online_count()
        );
        
        wp_send_json_success($stats);
    }
}

