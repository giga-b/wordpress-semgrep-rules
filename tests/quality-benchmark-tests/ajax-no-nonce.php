<?php
/**
 * AJAX handler without nonce
 * Status: Vulnerable (REST/AJAX Security)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function ajax_no_nonce_vulnerable_function() {
    // VULNERABILITY: REST endpoint without permissions
    add_action('rest_api_init', function() {
        register_rest_route('my-plugin/v1', '/data', [
            'methods' => 'POST',
            'callback' => function($request) {
                return rest_ensure_response(['status' => 'success']);
            },
            // VULNERABLE: No permission_callback
        ]);
    });
    
    // Safe operations for contrast
    add_action('rest_api_init', function() {
        register_rest_route('my-plugin/v1', '/safe-data', [
            'methods' => 'POST',
            'callback' => function($request) {
                return rest_ensure_response(['status' => 'success']);
            },
            'permission_callback' => function() {
                return current_user_can('edit_posts');
            }
        ]);
    });
    
    return true;
}

add_action('init', 'ajax_no_nonce_vulnerable_function');
