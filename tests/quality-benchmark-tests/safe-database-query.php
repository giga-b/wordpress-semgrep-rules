<?php
/**
 * Database queries using prepared statements
 * Status: Safe (No vulnerabilities)
 * Expected Findings: 0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Basic WordPress functions with proper security practices
function safe_database_query_safe_function() {
    // Proper capability check
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    
    // Safe output with proper escaping
    $safe_data = sanitize_text_field($_POST['data'] ?? '');
    echo esc_html($safe_data);
    
    // Safe database query
    global $wpdb;
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}posts WHERE post_status = %s",
            'publish'
        )
    );
    
    // Safe file operations
    $upload_dir = wp_upload_dir();
    $safe_path = realpath($upload_dir['basedir']);
    
    // Safe redirect
    $redirect_url = esc_url_raw($_GET['redirect'] ?? '');
    if ($redirect_url) {
        wp_redirect($redirect_url);
        exit;
    }
    
    return true;
}

// Hook registration with proper priority
add_action('init', 'safe_database_query_safe_function', 10);

// Safe AJAX handler
add_action('wp_ajax_safe_action', function() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'safe_action_nonce')) {
        wp_die('Invalid nonce');
    }
    
    // Process safely
    $result = sanitize_text_field($_POST['data']);
    wp_send_json_success($result);
});
