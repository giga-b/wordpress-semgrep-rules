<?php
/**
 * Test Case: Nonce Verification Detection
 * 
 * This file contains test cases for WordPress nonce verification patterns.
 * It includes both vulnerable and secure examples for testing rule detection.
 */

// VULNERABLE: Missing nonce verification in AJAX handler
add_action('wp_ajax_my_plugin_action', 'my_plugin_ajax_handler');

function my_plugin_ajax_handler() {
    // Missing nonce verification - this should be detected
    $user_input = $_POST['data'];
    echo $user_input; // Potential XSS vulnerability
}

// SECURE: Proper nonce verification
add_action('wp_ajax_my_secure_action', 'my_secure_ajax_handler');

function my_secure_ajax_handler() {
    // Proper nonce verification
    if (!wp_verify_nonce($_POST['nonce'], 'my_secure_action')) {
        wp_die('Security check failed');
    }
    
    $user_input = sanitize_text_field($_POST['data']);
    echo esc_html($user_input);
}

// VULNERABLE: Nonce verification with wrong action
add_action('wp_ajax_wrong_action', 'wrong_action_handler');

function wrong_action_handler() {
    // Nonce verification with mismatched action
    if (!wp_verify_nonce($_POST['nonce'], 'different_action')) {
        wp_die('Security check failed');
    }
    
    $user_input = $_POST['data'];
    echo $user_input; // Still vulnerable due to wrong action
}

// VULNERABLE: Using check_ajax_referer without proper action
add_action('wp_ajax_check_referer_test', 'check_referer_handler');

function check_referer_handler() {
    // Using check_ajax_referer without specifying action
    check_ajax_referer('my_action'); // Missing second parameter
    
    $user_input = $_POST['data'];
    echo $user_input;
}

// SECURE: Proper check_ajax_referer usage
add_action('wp_ajax_proper_referer', 'proper_referer_handler');

function proper_referer_handler() {
    // Proper check_ajax_referer with action
    check_ajax_referer('proper_referer', 'nonce');
    
    $user_input = sanitize_text_field($_POST['data']);
    echo esc_html($user_input);
}
