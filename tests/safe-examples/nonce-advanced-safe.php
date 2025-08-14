<?php
/**
 * Advanced Safe Nonce Test Cases
 * - Covers admin-post, default param behaviors, custom nonce names,
 *   URL nonces, localized script usage, and OO callbacks.
 */

// Lightweight WP function stubs for linting in isolated test context
if (!function_exists('add_action')) { function add_action($hook, $callback, $priority = 10, $args = 1) {} }
if (!function_exists('check_admin_referer')) { function check_admin_referer($action) { return true; } }
if (!function_exists('sanitize_text_field')) { function sanitize_text_field($str) { return (string) $str; } }
if (!function_exists('update_option')) { function update_option($key, $value) { return true; } }
if (!function_exists('check_ajax_referer')) { function check_ajax_referer($action, $name = '_ajax_nonce', $die = true) { return true; } }
if (!function_exists('wp_nonce_field')) { function wp_nonce_field($action, $name = '_wpnonce') { echo '<input type="hidden" name="' . $name . '" value="nonce" />'; } }
if (!function_exists('wp_verify_nonce')) { function wp_verify_nonce($nonce, $action) { return 1; } }
if (!function_exists('wp_die')) { function wp_die($msg) { echo $msg; } }
if (!function_exists('admin_url')) { function admin_url($path = '') { return '/wp-admin/' . ltrim($path, '/'); } }
if (!function_exists('wp_nonce_url')) { function wp_nonce_url($url, $action) { return $url . (strpos($url, '?') === false ? '?' : '&') . '_wpnonce=nonce'; } }
if (!function_exists('esc_url')) { function esc_url($url) { return $url; } }
if (!function_exists('wp_enqueue_script')) { function wp_enqueue_script() {} }
if (!function_exists('plugins_url')) { function plugins_url($file, $ref) { return '/plugins/' . basename($file); } }
if (!function_exists('wp_localize_script')) { function wp_localize_script() {} }
if (!function_exists('wp_create_nonce')) { function wp_create_nonce($action) { return 'nonce'; } }
if (!function_exists('wp_send_json_success')) { function wp_send_json_success($data) { echo json_encode(array('success' => true, 'data' => $data)); } }

// SAFE: admin-post handler with proper nonce verification
add_action('admin_post_my_secure_action', 'wprs_safe_admin_post_handler');
function wprs_safe_admin_post_handler() {
    check_admin_referer('my_secure_action');
    $data = sanitize_text_field($_POST['data'] ?? '');
    update_option('wprs_safe_admin_post', $data);
}

// SAFE: admin-post (nopriv) with proper nonce verification
add_action('admin_post_nopriv_my_secure_action', 'wprs_safe_admin_post_nopriv');
function wprs_safe_admin_post_nopriv() {
    check_admin_referer('my_secure_action');
    $data = sanitize_text_field($_POST['data'] ?? '');
    update_option('wprs_safe_admin_post_nopriv', $data);
}

// SAFE: AJAX nonce check using default parameter name (_ajax_nonce)
add_action('wp_ajax_wprs_safe_default_param', function () {
    check_ajax_referer('wprs_default_param'); // uses default _ajax_nonce
    $val = sanitize_text_field($_POST['val'] ?? '');
    update_option('wprs_safe_default_param', $val);
});

// SAFE: Custom nonce field name with matching verification
function wprs_safe_custom_nonce_field_render() {
    ?>
    <form method="post">
        <?php wp_nonce_field('wprs_custom_action', '_wprs_nonce'); ?>
        <input type="text" name="foo" />
        <input type="submit" value="Save" />
    </form>
    <?php
}

function wprs_safe_custom_nonce_field_process() {
    if (!empty($_POST['foo'])) {
        if (wp_verify_nonce($_POST['_wprs_nonce'] ?? '', 'wprs_custom_action')) {
            update_option('wprs_safe_custom_nonce', sanitize_text_field($_POST['foo']));
        } else {
            wp_die('Security check failed');
        }
    }
}

// SAFE: URL nonce creation + admin referrer verification
function wprs_safe_url_nonce_link() {
    $url = wp_nonce_url(admin_url('admin.php?page=wprs'), 'wprs_url_action');
    echo '<a href="' . esc_url($url) . '">Go</a>';
}

add_action('admin_init', function () {
    if (isset($_GET['page']) && $_GET['page'] === 'wprs' && isset($_GET['_wpnonce'])) {
        check_admin_referer('wprs_url_action');
    }
});

// SAFE: Localized script nonce with matching server verification
function wprs_safe_enqueue() {
    wp_enqueue_script('wprs-safe', plugins_url('wprs-safe.js', __FILE__), array('jquery'), '1.0', true);
    wp_localize_script('wprs-safe', 'WPRS_SAFE', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('wprs_localized_action')
    ));
}

add_action('wp_ajax_wprs_localized', function () {
    check_ajax_referer('wprs_localized_action', 'nonce');
    wp_send_json_success(array('ok' => true));
});

// SAFE: OO callback with proper nonce verification
class WPRS_Safe_Handler {
    public static function init() {
        add_action('wp_ajax_wprs_oo', array(__CLASS__, 'handle'));
    }

    public static function handle() {
        check_ajax_referer('wprs_oo_action', 'nonce');
        $msg = sanitize_text_field($_POST['msg'] ?? '');
        wp_send_json_success(array('msg' => $msg));
    }
}

WPRS_Safe_Handler::init();


