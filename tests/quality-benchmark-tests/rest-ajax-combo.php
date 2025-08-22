<?php
/**
 * REST and AJAX without security
 * Status: Vulnerable (rest_ajax, rest_ajax)
 * Expected Findings: 2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function rest_ajax_combo_vulnerable_function() {
    global $wpdb;
    
    // VULNERABILITY: REST endpoint without permissions
    register_rest_route('v1', '/data', ['methods' => 'POST', 'callback' => 'process_data']); // VULNERABLE
    // VULNERABILITY: REST endpoint without permissions
    register_rest_route('v1', '/data', ['methods' => 'POST', 'callback' => 'process_data']); // VULNERABLE
    
    // Safe operations for contrast
    $safe_input = sanitize_text_field($_POST['safe_input'] ?? '');
    echo esc_html($safe_input);
    $safe_results = $wpdb->get_results($wpdb->prepare('SELECT * FROM {$wpdb->prefix}users WHERE ID = %d', intval($_GET['safe_id'])));
    if (wp_verify_nonce($_POST['safe_nonce'], 'safe_action') && current_user_can('manage_options')) {
        update_option('safe_setting', sanitize_text_field($_POST['safe_setting']));
    }
    
    return true;
}

add_action('init', 'rest_ajax_combo_vulnerable_function');
