<?php
/**
 * SQL injection in raw query
 * Status: Vulnerable (SQL Injection)
 * Expected Findings: 1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function sqli_basic_query_vulnerable_function() {
    global $wpdb;
    
    // VULNERABILITY: SQL Injection - Direct concatenation
    $user_id = $_GET['user_id'] ?? '';
    $query = "SELECT * FROM {$wpdb->prefix}users WHERE ID = " . $user_id; // VULNERABLE
    $results = $wpdb->get_results($query);
    
    // Safe operations for contrast
    $safe_user_id = intval($_GET['safe_user_id'] ?? 0);
    $safe_results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}users WHERE ID = %d", $safe_user_id)
    );
    
    return $results;
}

add_action('init', 'sqli_basic_query_vulnerable_function');
