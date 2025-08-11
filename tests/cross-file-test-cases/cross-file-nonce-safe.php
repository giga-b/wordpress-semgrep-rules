<?php
/**
 * Cross-File Nonce Safe Test Case
 * This file demonstrates secure patterns where AJAX actions are registered
 * with proper nonce verification in callback functions
 */

// File 1: AJAX action registration (safe)
// This should NOT be detected by cross-file analysis as vulnerable

// AJAX action registration with proper nonce verification
add_action('wp_ajax_safe_action', 'safe_ajax_handler');
add_action('wp_ajax_nopriv_safe_action', 'safe_ajax_handler');

// AJAX action registration with array-style callback
add_action('wp_ajax_safe_array_action', array('SafeClass', 'safe_method'));

// AJAX action registration with priority
add_action('wp_ajax_safe_priority_action', 'safe_priority_handler', 10);

// AJAX action registration for both logged-in and non-logged-in users
add_action('wp_ajax_safe_both_action', 'safe_both_handler');
add_action('wp_ajax_nopriv_safe_both_action', 'safe_both_handler');

// Nonce creation with specific action name
wp_create_nonce('safe_action_name');

// Nonce creation with wp_nonce_field
wp_nonce_field('safe_action_name');

// Nonce creation with wp_nonce_url
wp_nonce_url('/admin/action.php', 'safe_action_name');

// Nonce creation with wp_nonce_ays
wp_nonce_ays('safe_action_name');
