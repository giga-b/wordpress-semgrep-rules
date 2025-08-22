<?php
/**
 * Combined authorization and CSRF issues
 * Status: Vulnerable (authz, csrf)
 * Expected Findings: 2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function authz_csrf_combo_vulnerable_function() {
    global $wpdb;
    
    // VULNERABILITY: Authorization - No capability check
    update_option('admin_setting', $_POST['setting']); // VULNERABLE
    // VULNERABILITY: CSRF - No nonce verification
    if (isset($_POST['action'])) { wp_delete_post($_POST['post_id']); } // VULNERABLE
    
    // Safe operations for contrast
    $safe_input = sanitize_text_field($_POST['safe_input'] ?? '');
    echo esc_html($safe_input);
    $safe_results = $wpdb->get_results($wpdb->prepare('SELECT * FROM {$wpdb->prefix}users WHERE ID = %d', intval($_GET['safe_id'])));
    if (wp_verify_nonce($_POST['safe_nonce'], 'safe_action') && current_user_can('manage_options')) {
        update_option('safe_setting', sanitize_text_field($_POST['safe_setting']));
    }
    
    return true;
}

add_action('init', 'authz_csrf_combo_vulnerable_function');
