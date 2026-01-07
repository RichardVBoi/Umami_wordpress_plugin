<?php
/**
 * Plugin Name: Hamotech Umami Analytics
 * Plugin URI: https://hamotechsolutions.com
 * Description: Advanced Umami Analytics integration for WordPress with custom event tracking, enhanced privacy controls, and comprehensive analytics features.
 * Version: 1.0.0
 * Author: Hamotech Solutions
 * Author URI: https://hamotechsolutions.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hamotech-umami
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HAMOTECH_UMAMI_VERSION', '1.0.0');
define('HAMOTECH_UMAMI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HAMOTECH_UMAMI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HAMOTECH_UMAMI_PLUGIN_BASENAME', plugin_basename(__FILE__));

class Hamotech_Umami_Analytics {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_tracking_script'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        add_filter('plugin_action_links_' . HAMOTECH_UMAMI_PLUGIN_BASENAME, array($this, 'add_settings_link'));
        
        // Event tracking hooks
        add_action('comment_post', array($this, 'track_comment_event'), 10, 3);
        add_action('wp_login', array($this, 'track_login_event'), 10, 2);
        add_action('user_register', array($this, 'track_registration_event'));
        add_action('wp_footer', array($this, 'add_custom_events_script'));
    }
    
    private function load_dependencies() {
        // Load additional files if needed
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('hamotech-umami', false, dirname(HAMOTECH_UMAMI_PLUGIN_BASENAME) . '/languages');
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('Hamotech Umami Analytics', 'hamotech-umami'),
            __('Umami Analytics', 'hamotech-umami'),
            'manage_options',
            'hamotech-umami-settings',
            array($this, 'render_settings_page')
        );
    }
    
    public function register_settings() {
        register_setting('hamotech_umami_settings', 'hamotech_umami_options', array($this, 'sanitize_options'));
        
        // Basic Settings Section
        add_settings_section(
            'hamotech_umami_basic_section',
            __('Basic Configuration', 'hamotech-umami'),
            array($this, 'basic_section_callback'),
            'hamotech-umami-settings'
        );
        
        add_settings_field('enabled', __('Enable Tracking', 'hamotech-umami'), array($this, 'enabled_callback'), 'hamotech-umami-settings', 'hamotech_umami_basic_section');
        add_settings_field('website_id', __('Website ID', 'hamotech-umami'), array($this, 'website_id_callback'), 'hamotech-umami-settings', 'hamotech_umami_basic_section');
        add_settings_field('script_url', __('Script URL', 'hamotech-umami'), array($this, 'script_url_callback'), 'hamotech-umami-settings', 'hamotech_umami_basic_section');
        add_settings_field('host_url', __('Host URL', 'hamotech-umami'), array($this, 'host_url_callback'), 'hamotech-umami-settings', 'hamotech_umami_basic_section');
        
        // Privacy Settings Section
        add_settings_section(
            'hamotech_umami_privacy_section',
            __('Privacy & User Settings', 'hamotech-umami'),
            array($this, 'privacy_section_callback'),
            'hamotech-umami-settings'
        );
        
        add_settings_field('ignore_admins', __('Ignore Admin Users', 'hamotech-umami'), array($this, 'ignore_admins_callback'), 'hamotech-umami-settings', 'hamotech_umami_privacy_section');
        add_settings_field('ignore_logged_in', __('Ignore Logged-in Users', 'hamotech-umami'), array($this, 'ignore_logged_in_callback'), 'hamotech-umami-settings', 'hamotech_umami_privacy_section');
        add_settings_field('respect_dnt', __('Respect Do Not Track', 'hamotech-umami'), array($this, 'respect_dnt_callback'), 'hamotech-umami-settings', 'hamotech_umami_privacy_section');
        
        // Event Tracking Section
        add_settings_section(
            'hamotech_umami_events_section',
            __('Event Tracking', 'hamotech-umami'),
            array($this, 'events_section_callback'),
            'hamotech-umami-settings'
        );
        
        add_settings_field('track_comments', __('Track Comments', 'hamotech-umami'), array($this, 'track_comments_callback'), 'hamotech-umami-settings', 'hamotech_umami_events_section');
        add_settings_field('track_logins', __('Track User Logins', 'hamotech-umami'), array($this, 'track_logins_callback'), 'hamotech-umami-settings', 'hamotech_umami_events_section');
        add_settings_field('track_registrations', __('Track Registrations', 'hamotech-umami'), array($this, 'track_registrations_callback'), 'hamotech-umami-settings', 'hamotech_umami_events_section');
        add_settings_field('track_downloads', __('Track File Downloads', 'hamotech-umami'), array($this, 'track_downloads_callback'), 'hamotech-umami-settings', 'hamotech_umami_events_section');
        add_settings_field('track_outbound', __('Track Outbound Links', 'hamotech-umami'), array($this, 'track_outbound_callback'), 'hamotech-umami-settings', 'hamotech_umami_events_section');
        add_settings_field('track_404', __('Track 404 Errors', 'hamotech-umami'), array($this, 'track_404_callback'), 'hamotech-umami-settings', 'hamotech_umami_events_section');
    }
    
    public function sanitize_options($input) {
        $sanitized = array();
        
        $sanitized['enabled'] = isset($input['enabled']) ? 1 : 0;
        $sanitized['website_id'] = sanitize_text_field($input['website_id'] ?? '');
        $sanitized['script_url'] = esc_url_raw($input['script_url'] ?? '');
        $sanitized['host_url'] = esc_url_raw($input['host_url'] ?? '');
        $sanitized['ignore_admins'] = isset($input['ignore_admins']) ? 1 : 0;
        $sanitized['ignore_logged_in'] = isset($input['ignore_logged_in']) ? 1 : 0;
        $sanitized['respect_dnt'] = isset($input['respect_dnt']) ? 1 : 0;
        $sanitized['track_comments'] = isset($input['track_comments']) ? 1 : 0;
        $sanitized['track_logins'] = isset($input['track_logins']) ? 1 : 0;
        $sanitized['track_registrations'] = isset($input['track_registrations']) ? 1 : 0;
        $sanitized['track_downloads'] = isset($input['track_downloads']) ? 1 : 0;
        $sanitized['track_outbound'] = isset($input['track_outbound']) ? 1 : 0;
        $sanitized['track_404'] = isset($input['track_404']) ? 1 : 0;
        
        return $sanitized;
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (isset($_GET['settings-updated'])) {
            add_settings_error('hamotech_umami_messages', 'hamotech_umami_message', __('Settings Saved', 'hamotech-umami'), 'updated');
        }
        
        settings_errors('hamotech_umami_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <div style="background: #fff; padding: 20px; margin: 20px 0; border-left: 4px solid #2271b1;">
                <p><strong><?php _e('Powered by Hamotech Solutions', 'hamotech-umami'); ?></strong></p>
                <p><?php _e('Professional Umami Analytics integration with advanced event tracking and privacy controls.', 'hamotech-umami'); ?></p>
                <p><a href="https://hamotechsolutions.com" target="_blank"><?php _e('Visit Hamotechsolutions.com', 'hamotech-umami'); ?></a></p>
            </div>
            <form action="options.php" method="post">
                <?php
                settings_fields('hamotech_umami_settings');
                do_settings_sections('hamotech-umami-settings');
                submit_button(__('Save Settings', 'hamotech-umami'));
                ?>
            </form>
        </div>
        <?php
    }
    
    // Section Callbacks
    public function basic_section_callback() {
        echo '<p>' . __('Configure your Umami Analytics connection. Get your Website ID and Script URL from your Umami dashboard.', 'hamotech-umami') . '</p>';
    }
    
    public function privacy_section_callback() {
        echo '<p>' . __('Control who gets tracked and respect user privacy preferences.', 'hamotech-umami') . '</p>';
    }
    
    public function events_section_callback() {
        echo '<p>' . __('Enable automatic tracking for various user interactions and events on your site.', 'hamotech-umami') . '</p>';
    }
    
    // Field Callbacks
    public function enabled_callback() {
        $options = get_option('hamotech_umami_options');
        $checked = isset($options['enabled']) && $options['enabled'] ? 'checked' : '';
        echo '<input type="checkbox" id="enabled" name="hamotech_umami_options[enabled]" value="1" ' . $checked . '>';
        echo '<label for="enabled">' . __('Enable Umami Analytics tracking', 'hamotech-umami') . '</label>';
    }
    
    public function website_id_callback() {
        $options = get_option('hamotech_umami_options');
        $value = $options['website_id'] ?? '';
        echo '<input type="text" id="website_id" name="hamotech_umami_options[website_id]" value="' . esc_attr($value) . '" class="regular-text" required>';
        echo '<p class="description">' . __('Found in your Umami website settings', 'hamotech-umami') . '</p>';
    }
    
    public function script_url_callback() {
        $options = get_option('hamotech_umami_options');
        $value = $options['script_url'] ?? '';
        echo '<input type="url" id="script_url" name="hamotech_umami_options[script_url]" value="' . esc_attr($value) . '" class="regular-text" required>';
        echo '<p class="description">' . __('Example: https://yourdomain.com/script.js', 'hamotech-umami') . '</p>';
    }
    
    public function host_url_callback() {
        $options = get_option('hamotech_umami_options');
        $value = $options['host_url'] ?? '';
        echo '<input type="url" id="host_url" name="hamotech_umami_options[host_url]" value="' . esc_attr($value) . '" class="regular-text">';
        echo '<p class="description">' . __('Optional: Your Umami host URL (leave empty to use default)', 'hamotech-umami') . '</p>';
    }
    
    public function ignore_admins_callback() {
        $options = get_option('hamotech_umami_options');
        $checked = isset($options['ignore_admins']) && $options['ignore_admins'] ? 'checked' : '';
        echo '<input type="checkbox" id="ignore_admins" name="hamotech_umami_options[ignore_admins]" value="1" ' . $checked . '>';
        echo '<label for="ignore_admins">' . __('Don\'t track admin users', 'hamotech-umami') . '</label>';
    }
    
    public function ignore_logged_in_callback() {
        $options = get_option('hamotech_umami_options');
        $checked = isset($options['ignore_logged_in']) && $options['ignore_logged_in'] ? 'checked' : '';
        echo '<input type="checkbox" id="ignore_logged_in" name="hamotech_umami_options[ignore_logged_in]" value="1" ' . $checked . '>';
        echo '<label for="ignore_logged_in">' . __('Don\'t track any logged-in users', 'hamotech-umami') . '</label>';
    }
    
    public function respect_dnt_callback() {
        $options = get_option('hamotech_umami_options');
        $checked = isset($options['respect_dnt']) && $options['respect_dnt'] ? 'checked' : '';
        echo '<input type="checkbox" id="respect_dnt" name="hamotech_umami_options[respect_dnt]" value="1" ' . $checked . '>';
        echo '<label for="respect_dnt">' . __('Respect Do Not Track browser settings', 'hamotech-umami') . '</label>';
    }
    
    public function track_comments_callback() {
        $options = get_option('hamotech_umami_options');
        $checked = isset($options['track_comments']) && $options['track_comments'] ? 'checked' : '';
        echo '<input type="checkbox" id="track_comments" name="hamotech_umami_options[track_comments]" value="1" ' . $checked . '>';
        echo '<label for="track_comments">' . __('Track comment submissions', 'hamotech-umami') . '</label>';
    }
    
    public function track_logins_callback() {
        $options = get_option('hamotech_umami_options');
        $checked = isset($options['track_logins']) && $options['track_logins'] ? 'checked' : '';
        echo '<input type="checkbox" id="track_logins" name="hamotech_umami_options[track_logins]" value="1" ' . $checked . '>';
        echo '<label for="track_logins">' . __('Track user login events', 'hamotech-umami') . '</label>';
    }
    
    public function track_registrations_callback() {
        $options = get_option('hamotech_umami_options');
        $checked = isset($options['track_registrations']) && $options['track_registrations'] ? 'checked' : '';
        echo '<input type="checkbox" id="track_registrations" name="hamotech_umami_options[track_registrations]" value="1" ' . $checked . '>';
        echo '<label for="track_registrations">' . __('Track new user registrations', 'hamotech-umami') . '</label>';
    }
    
    public function track_downloads_callback() {
        $options = get_option('hamotech_umami_options');
        $checked = isset($options['track_downloads']) && $options['track_downloads'] ? 'checked' : '';
        echo '<input type="checkbox" id="track_downloads" name="hamotech_umami_options[track_downloads]" value="1" ' . $checked . '>';
        echo '<label for="track_downloads">' . __('Track file download clicks (.pdf, .zip, .doc, etc.)', 'hamotech-umami') . '</label>';
    }
    
    public function track_outbound_callback() {
        $options = get_option('hamotech_umami_options');
        $checked = isset($options['track_outbound']) && $options['track_outbound'] ? 'checked' : '';
        echo '<input type="checkbox" id="track_outbound" name="hamotech_umami_options[track_outbound]" value="1" ' . $checked . '>';
        echo '<label for="track_outbound">' . __('Track outbound link clicks', 'hamotech-umami') . '</label>';
    }
    
    public function track_404_callback() {
        $options = get_option('hamotech_umami_options');
        $checked = isset($options['track_404']) && $options['track_404'] ? 'checked' : '';
        echo '<input type="checkbox" id="track_404" name="hamotech_umami_options[track_404]" value="1" ' . $checked . '>';
        echo '<label for="track_404">' . __('Track 404 error pages', 'hamotech-umami') . '</label>';
    }
    
    public function enqueue_tracking_script() {
        $options = get_option('hamotech_umami_options');
        
        if (empty($options['enabled']) || empty($options['website_id']) || empty($options['script_url'])) {
            return;
        }
        
        // Check if tracking should be disabled for this user
        if (!empty($options['ignore_admins']) && current_user_can('manage_options')) {
            return;
        }
        
        if (!empty($options['ignore_logged_in']) && is_user_logged_in()) {
            return;
        }
        
        $script_attributes = array(
            'data-website-id' => esc_attr($options['website_id']),
            'defer' => true
        );
        
        if (!empty($options['host_url'])) {
            $script_attributes['data-host-url'] = esc_url($options['host_url']);
        }
        
        if (!empty($options['respect_dnt'])) {
            $script_attributes['data-do-not-track'] = 'true';
        }
        
        // Track 404 pages
        if (!empty($options['track_404']) && is_404()) {
            $script_attributes['data-auto-track'] = 'false';
        }
        
        $attributes_string = '';
        foreach ($script_attributes as $key => $value) {
            if ($value === true) {
                $attributes_string .= ' ' . $key;
            } else {
                $attributes_string .= ' ' . $key . '="' . $value . '"';
            }
        }
        
        wp_enqueue_script(
            'hamotech-umami-tracking',
            esc_url($options['script_url']),
            array(),
            null,
            true
        );
        
        add_filter('script_loader_tag', function($tag, $handle) use ($attributes_string) {
            if ('hamotech-umami-tracking' === $handle) {
                return str_replace(' src=', $attributes_string . ' src=', $tag);
            }
            return $tag;
        }, 10, 2);
    }
    
    public function add_custom_events_script() {
        $options = get_option('hamotech_umami_options');
        
        if (empty($options['enabled'])) {
            return;
        }
        
        ?>
        <script>
        (function() {
            if (typeof umami === 'undefined') return;
            
            <?php if (!empty($options['track_404']) && is_404()): ?>
            // Track 404 error
            umami.track('404-error', {
                path: window.location.pathname,
                referrer: document.referrer
            });
            <?php endif; ?>
            
            <?php if (!empty($options['track_downloads'])): ?>
            // Track file downloads
            document.addEventListener('click', function(e) {
                var target = e.target.closest('a');
                if (!target) return;
                
                var href = target.getAttribute('href');
                if (!href) return;
                
                var fileExtensions = /\.(pdf|zip|rar|doc|docx|xls|xlsx|ppt|pptx|mp3|mp4|avi|mov|jpg|png|gif|svg)$/i;
                if (fileExtensions.test(href)) {
                    var fileName = href.split('/').pop();
                    umami.track('file-download', {
                        file: fileName,
                        url: href
                    });
                }
            });
            <?php endif; ?>
            
            <?php if (!empty($options['track_outbound'])): ?>
            // Track outbound links
            document.addEventListener('click', function(e) {
                var target = e.target.closest('a');
                if (!target) return;
                
                var href = target.getAttribute('href');
                if (!href) return;
                
                var currentDomain = window.location.hostname;
                try {
                    var linkDomain = new URL(href, window.location.href).hostname;
                    if (linkDomain !== currentDomain && (href.startsWith('http://') || href.startsWith('https://'))) {
                        umami.track('outbound-link', {
                            url: href,
                            domain: linkDomain
                        });
                    }
                } catch(e) {}
            });
            <?php endif; ?>
        })();
        </script>
        <?php
    }
    
    public function track_comment_event($comment_id, $approved, $commentdata) {
        $options = get_option('hamotech_umami_options');
        
        if (empty($options['track_comments'])) {
            return;
        }
        
        // Store event data in transient to be tracked on next page load
        set_transient('hamotech_umami_comment_' . $comment_id, array(
            'post_id' => $commentdata['comment_post_ID'],
            'approved' => $approved
        ), 300);
    }
    
    public function track_login_event($user_login, $user) {
        $options = get_option('hamotech_umami_options');
        
        if (empty($options['track_logins'])) {
            return;
        }
        
        set_transient('hamotech_umami_login_' . $user->ID, true, 300);
    }
    
    public function track_registration_event($user_id) {
        $options = get_option('hamotech_umami_options');
        
        if (empty($options['track_registrations'])) {
            return;
        }
        
        set_transient('hamotech_umami_registration_' . $user_id, true, 300);
    }
    
    public function enqueue_admin_scripts($hook) {
        if ('settings_page_hamotech-umami-settings' !== $hook) {
            return;
        }
        
        wp_enqueue_style('hamotech-umami-admin', HAMOTECH_UMAMI_PLUGIN_URL . 'assets/admin.css', array(), HAMOTECH_UMAMI_VERSION);
    }
    
    public function add_dashboard_widget() {
        $options = get_option('hamotech_umami_options');
        
        if (empty($options['enabled']) || empty($options['host_url'])) {
            return;
        }
        
        wp_add_dashboard_widget(
            'hamotech_umami_widget',
            __('Umami Analytics', 'hamotech-umami'),
            array($this, 'render_dashboard_widget')
        );
    }
    
    public function render_dashboard_widget() {
        $options = get_option('hamotech_umami_options');
        $dashboard_url = trailingslashit($options['host_url']) . 'websites/' . $options['website_id'];
        
        echo '<p>' . __('View your analytics dashboard:', 'hamotech-umami') . '</p>';
        echo '<p><a href="' . esc_url($dashboard_url) . '" target="_blank" class="button button-primary">' . __('Open Umami Dashboard', 'hamotech-umami') . '</a></p>';
        echo '<p style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd; color: #666; font-size: 12px;">' . __('Powered by Hamotech Solutions', 'hamotech-umami') . '</p>';
    }
    
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=hamotech-umami-settings') . '">' . __('Settings', 'hamotech-umami') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}

// Initialize the plugin
function hamotech_umami_analytics() {
    return Hamotech_Umami_Analytics::get_instance();
}

hamotech_umami_analytics();
