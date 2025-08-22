<?php
/**
 * Three security issues combined
 * Status: Vulnerable (rest_ajax, csrf, authz)
 * Expected Findings: 3
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function ajax_nonce_csrf_authz_combo_vulnerable_function() {
    global $wpdb;
    
    // VULNERABILITY: REST endpoint without permissions
    register_rest_route('v1', '/data', ['methods' => 'POST', 'callback' => 'process_data']); // VULNERABLE
    // VULNERABILITY: CSRF - No nonce verification
    if (isset($_POST['action'])) { wp_delete_post($_POST['post_id']); } // VULNERABLE
    // VULNERABILITY: Authorization - No capability check
    update_option('admin_setting', $_POST['setting']); // VULNERABLE
    
    // Safe operations for contrast
    $safe_input = sanitize_text_field($_POST['safe_input'] ?? '');
    echo esc_html($safe_input);
    $safe_results = $wpdb->get_results($wpdb->prepare('SELECT * FROM {$wpdb->prefix}users WHERE ID = %d', intval($_GET['safe_id'])));
    if (wp_verify_nonce($_POST['safe_nonce'], 'safe_action') && current_user_can('manage_options')) {
        update_option('safe_setting', sanitize_text_field($_POST['safe_setting']));
    }
    
    return true;
}

add_action('init', 'ajax_nonce_csrf_authz_combo_vulnerable_function');
