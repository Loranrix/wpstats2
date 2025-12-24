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
                // For better mobile compatibility, don't set domain explicitly
                // This allows the cookie to work on all subdomains and mobile browsers
                $is_https = is_ssl() || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
                
                // Set cookie for 1 year with proper mobile support
                // Don't set domain to ensure it works on mobile browsers
                $cookie_params = array(
                    'expires' => time() + (365 * 24 * 60 * 60),
                    'path' => '/',
                    'secure' => $is_https,
                    'httponly' => false, // Allow JavaScript access
                    'samesite' => 'Lax' // Better mobile compatibility than None
                );
                
                // PHP 7.3+ supports array format
                if (PHP_VERSION_ID >= 70300) {
                    $cookie_set = setcookie('wp_visitor_stats_token', $token, $cookie_params);
                    // If setcookie fails, try header method as fallback
                    if (!$cookie_set) {
                        $cookie_string = 'wp_visitor_stats_token=' . urlencode($token);
                        $cookie_string .= '; expires=' . gmdate('D, d M Y H:i:s \G\M\T', $cookie_params['expires']);
                        $cookie_string .= '; path=' . $cookie_params['path'];
                        if ($cookie_params['secure']) {
                            $cookie_string .= '; secure';
                        }
                        $cookie_string .= '; SameSite=' . $cookie_params['samesite'];
                        header('Set-Cookie: ' . $cookie_string, false);
                    }
                } else {
                    // Fallback for older PHP versions
                    $cookie_string = 'wp_visitor_stats_token=' . urlencode($token);
                    $cookie_string .= '; expires=' . gmdate('D, d M Y H:i:s \G\M\T', $cookie_params['expires']);
                    $cookie_string .= '; path=' . $cookie_params['path'];
                    if ($cookie_params['secure']) {
                        $cookie_string .= '; secure';
                    }
                    $cookie_string .= '; SameSite=' . $cookie_params['samesite'];
                    header('Set-Cookie: ' . $cookie_string, false);
                }
                
                // Log for debugging (only if WP_DEBUG is enabled)
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('WP Visitor Stats: Cookie set via PHP. Token: ' . substr($token, 0, 10) . '... HTTPS: ' . ($is_https ? 'yes' : 'no'));
                }
                
                // Show confirmation page with JavaScript to set cookie
                // We include JS directly since we exit() before wp_footer
                $this->show_cookie_confirmation($token, $is_https);
                exit;
            }
        }
    }
    
    /**
     * Show cookie confirmation page
     */
    private function show_cookie_confirmation($token = '', $is_https = false) {
        // Prevent WordPress from loading the theme
        status_header(200);
        nocache_headers();
        
        // If token provided, we need to set cookie via JavaScript
        $js_token = !empty($token) ? $token : '';
        
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
                .loading {
                    color: #666;
                    font-size: 14px;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="confirmation-box">
                <div class="ok-icon">✓</div>
                <h1><?php _e('OK', 'wp-visitor-stats'); ?></h1>
                <p class="success"><?php _e('Cookie Access Activated!', 'wp-visitor-stats'); ?></p>
                <p><?php _e('You can now see visitor statistics on the website.', 'wp-visitor-stats'); ?></p>
                <p class="loading" id="cookie-status"><?php _e('Setting cookie...', 'wp-visitor-stats'); ?></p>
            </div>
            
            <?php if (!empty($js_token)): ?>
            <script>
            (function() {
                // Set cookie via JavaScript for mobile compatibility
                // This ensures the cookie is set even if PHP setcookie had issues
                function setCookie(name, value, days, secure) {
                    var expires = new Date();
                    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
                    var cookieString = name + '=' + encodeURIComponent(value) + 
                        '; expires=' + expires.toUTCString() + 
                        '; path=/' + 
                        '; SameSite=Lax';
                    if (secure) {
                        cookieString += '; secure';
                    }
                    document.cookie = cookieString;
                }
                
                function getCookie(name) {
                    var value = "; " + document.cookie;
                    var parts = value.split("; " + name + "=");
                    if (parts.length === 2) return decodeURIComponent(parts.pop().split(";").shift());
                    return null;
                }
                
                // Set the cookie
                var token = '<?php echo esc_js($js_token); ?>';
                var isSecure = <?php echo $is_https ? 'true' : 'false'; ?>;
                setCookie('wp_visitor_stats_token', token, 365, isSecure);
                
                // Verify cookie was set
                var statusEl = document.getElementById('cookie-status');
                var cookieValue = getCookie('wp_visitor_stats_token');
                
                if (cookieValue === token) {
                    statusEl.innerHTML = '<?php _e('Cookie set successfully!', 'wp-visitor-stats'); ?>';
                    statusEl.style.color = '#46b450';
                    
                    // Redirect to homepage after 1 second to show stats
                    setTimeout(function() {
                        // Remove token from URL if present
                        var currentUrl = window.location.href;
                        var newUrl = currentUrl.split('?')[0].split('#')[0];
                        window.location.href = newUrl;
                    }, 1000);
                } else {
                    statusEl.innerHTML = '<?php _e('Cookie may not have been set. Please check your browser settings.', 'wp-visitor-stats'); ?>';
                    statusEl.style.color = '#dc3232';
                }
            })();
            </script>
            <?php endif; ?>
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

