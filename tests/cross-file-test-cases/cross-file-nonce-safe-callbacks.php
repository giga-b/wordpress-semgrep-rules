<?php
/**
 * Cross-File Nonce Safe Test Case - Callback Functions
 * This file contains the callback functions with proper nonce verification
 */

// Safe AJAX handler with proper nonce verification
function safe_ajax_handler() {
    check_ajax_referer('safe_action_name', 'nonce');
    $data = sanitize_text_field($_POST['data']);
    echo esc_html($data); // Properly sanitized and escaped
}

// Safe class with safe method
class SafeClass {
    public function safe_method() {
        check_ajax_referer('safe_action_name', 'nonce');
        $data = sanitize_text_field($_POST['data']);
        echo esc_html($data); // Properly sanitized and escaped
    }
}

// Safe priority handler with proper nonce verification
function safe_priority_handler() {
    check_ajax_referer('safe_action_name', 'nonce');
    $data = sanitize_text_field($_POST['data']);
    echo esc_html($data); // Properly sanitized and escaped
}

// Safe both handler with proper nonce verification
function safe_both_handler() {
    check_ajax_referer('safe_action_name', 'nonce');
    $data = sanitize_text_field($_POST['data']);
    echo esc_html($data); // Properly sanitized and escaped
}

// Safe nonce verification with correct action name
function safe_nonce_correct_handler() {
    check_ajax_referer('safe_action_name', 'nonce'); // Correct action name
    $data = sanitize_text_field($_POST['data']);
    echo esc_html($data);
}

// Safe nonce verification with wp_verify_nonce and correct action
function safe_wp_verify_correct_handler() {
    if (wp_verify_nonce($_POST['nonce'], 'safe_action_name')) { // Correct action name
        $data = sanitize_text_field($_POST['data']);
        echo esc_html($data);
    } else {
        wp_die('Invalid nonce');
    }
}

// Safe nonce verification with specific action name
function safe_specific_nonce_handler() {
    check_ajax_referer('specific_action_name', 'security');
    $data = sanitize_text_field($_POST['data']);
    echo esc_html($data);
}

// Safe nonce verification with proper error handling
function safe_nonce_with_error_handler() {
    if (!check_ajax_referer('safe_action_name', 'nonce', false)) {
        wp_die('Invalid nonce');
    }
    $data = sanitize_text_field($_POST['data']);
    echo esc_html($data);
}
