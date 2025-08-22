<?php
/**
 * Unsafe unserialize usage
 * Status: Vulnerable (Deserialization)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function deserialization_unsafe_vulnerable_function() {
    // VULNERABILITY: Deserialization - Unsafe unserialize
    $serialized_data = $_POST['serialized_data'] ?? '';
    $data = unserialize($serialized_data); // VULNERABLE: Should validate input
    
    // Safe operations for contrast
    $safe_data = maybe_unserialize($_POST['safe_data'] ?? '');
    if (is_array($safe_data)) {
        $safe_data = array_map('sanitize_text_field', $safe_data);
    }
    
    return $data;
}

add_action('init', 'deserialization_unsafe_vulnerable_function');
