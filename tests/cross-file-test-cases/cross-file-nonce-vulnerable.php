<?php
/**
 * Cross-File Nonce Vulnerability Test Case
 * This file demonstrates a vulnerable pattern where AJAX actions are registered
 * but the callback functions lack proper nonce verification
 */

// File 1: AJAX action registration (vulnerable)
// This should be detected by cross-file analysis

// AJAX action registration without proper nonce verification
add_action('wp_ajax_vulnerable_action', 'vulnerable_ajax_handler');
add_action('wp_ajax_nopriv_vulnerable_action', 'vulnerable_ajax_handler');

// AJAX action registration with array-style callback
add_action('wp_ajax_vulnerable_array_action', array('VulnerableClass', 'vulnerable_method'));

// AJAX action registration with anonymous function
add_action('wp_ajax_vulnerable_anonymous_action', function() {
    $data = $_POST['data'];
    echo $data; // XSS vulnerability
});

// AJAX action registration with priority
add_action('wp_ajax_vulnerable_priority_action', 'vulnerable_priority_handler', 10);

// AJAX action registration for both logged-in and non-logged-in users
add_action('wp_ajax_vulnerable_both_action', 'vulnerable_both_handler');
add_action('wp_ajax_nopriv_vulnerable_both_action', 'vulnerable_both_handler');

// Nonce creation with one action name
wp_create_nonce('create_action_name');

// Nonce creation with wp_nonce_field
wp_nonce_field('create_action_name');

// Nonce creation with wp_nonce_url
wp_nonce_url('/admin/action.php', 'create_action_name');

// Nonce creation with wp_nonce_ays
wp_nonce_ays('create_action_name');
