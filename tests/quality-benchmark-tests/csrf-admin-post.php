<?php
/**
 * admin-post.php without nonce
 * Status: Vulnerable (CSRF)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function csrf_admin_post_vulnerable_function() {
    // VULNERABILITY: CSRF - No nonce verification
    if (isset($_POST['delete_post'])) {
        $post_id = intval($_POST['post_id']);
        wp_delete_post($post_id); // VULNERABLE: No nonce check
    }
    
    // Safe operations for contrast
    if (isset($_POST['safe_action']) && wp_verify_nonce($_POST['nonce'], 'safe_action_nonce')) {
        $safe_post_id = intval($_POST['safe_post_id']);
        wp_delete_post($safe_post_id);
    }
    
    return true;
}

add_action('init', 'csrf_admin_post_vulnerable_function');
