<?php
/**
 * SQL Injection Taint Analysis - Vulnerable Examples
 * 
 * This file contains examples that should trigger SQL injection taint analysis rules.
 * These patterns demonstrate various SQL injection vulnerabilities through taint analysis.
 */

// =============================================================================
// DIRECT FLOW VULNERABILITIES - User input flows directly to SQL queries
// =============================================================================

// Direct user input to query
function vulnerable_direct_flow_1() {
    $user_input = $_GET['id'];
    $wpdb->query($user_input); // VULNERABLE: Direct flow
}

function vulnerable_direct_flow_2() {
    $user_input = $_POST['name'];
    $wpdb->query($user_input); // VULNERABLE: Direct flow
}

function vulnerable_direct_flow_3() {
    $user_input = $_REQUEST['email'];
    $wpdb->query($user_input); // VULNERABLE: Direct flow
}

// =============================================================================
// STRING CONCATENATION VULNERABILITIES - User input concatenated into queries
// =============================================================================

// String concatenation in queries
function vulnerable_string_concatenation_1() {
    $user_input = $_GET['id'];
    $sql = "SELECT * FROM users WHERE id = " . $user_input;
    $wpdb->query($sql); // VULNERABLE: String concatenation
}

function vulnerable_string_concatenation_2() {
    $user_input = $_POST['name'];
    $sql = "SELECT * FROM users WHERE name = '$user_input'";
    $wpdb->query($sql); // VULNERABLE: String concatenation
}

function vulnerable_string_concatenation_3() {
    $user_input = $_REQUEST['email'];
    $sql = "SELECT * FROM users WHERE email = '$user_input'";
    $wpdb->query($sql); // VULNERABLE: String concatenation
}

// =============================================================================
// DYNAMIC TABLE NAME VULNERABILITIES - User input used as table names
// =============================================================================

// Dynamic table names
function vulnerable_dynamic_table_1() {
    $table_name = $_GET['table'];
    $wpdb->query("SELECT * FROM $table_name"); // VULNERABLE: Dynamic table
}

function vulnerable_dynamic_table_2() {
    $table_name = $_POST['table'];
    $wpdb->query("SELECT * FROM $table_name"); // VULNERABLE: Dynamic table
}

function vulnerable_dynamic_table_3() {
    $table_name = $_REQUEST['table'];
    $wpdb->query("SELECT * FROM $table_name"); // VULNERABLE: Dynamic table
}

// =============================================================================
// WORDPRESS FUNCTION VULNERABILITIES - User input flows to WordPress functions
// =============================================================================

// WordPress function flows
function vulnerable_wp_function_1() {
    $user_input = $_GET['args'];
    get_posts($user_input); // VULNERABLE: WordPress function flow
}

function vulnerable_wp_function_2() {
    $user_input = $_POST['args'];
    get_users($user_input); // VULNERABLE: WordPress function flow
}

function vulnerable_wp_function_3() {
    $user_input = $_REQUEST['args'];
    get_terms($user_input); // VULNERABLE: WordPress function flow
}

// =============================================================================
// BYPASS ATTEMPT VULNERABILITIES - Advanced evasion techniques
// =============================================================================

// Obfuscated string concatenation
function vulnerable_bypass_obfuscated_1() {
    $user_input = $_GET['id'];
    $sql = "SELECT * FROM users WHERE id = " . $user_input . "";
    $wpdb->query($sql); // VULNERABLE: Obfuscated concatenation
}

function vulnerable_bypass_obfuscated_2() {
    $user_input = $_POST['name'];
    $sql = "SELECT * FROM users WHERE name = '" . $user_input . "'";
    $wpdb->query($sql); // VULNERABLE: Obfuscated concatenation
}

// Variable assignment bypass
function vulnerable_bypass_variable_1() {
    $user_input = $_GET['id'];
    $temp = $user_input;
    $wpdb->query("SELECT * FROM users WHERE id = $temp"); // VULNERABLE: Variable bypass
}

function vulnerable_bypass_variable_2() {
    $user_input = $_POST['name'];
    $temp = $user_input;
    $wpdb->query("SELECT * FROM users WHERE name = '$temp'"); // VULNERABLE: Variable bypass
}

// Function call bypass
function vulnerable_bypass_function_1() {
    $user_input = $_GET['id'];
    $processed = some_function($user_input);
    $wpdb->query("SELECT * FROM users WHERE id = $processed"); // VULNERABLE: Function bypass
}

function vulnerable_bypass_function_2() {
    $user_input = $_POST['name'];
    $processed = another_function($user_input);
    $wpdb->query("SELECT * FROM users WHERE name = '$processed'"); // VULNERABLE: Function bypass
}

// =============================================================================
// WORDPRESS DATABASE OPERATION VULNERABILITIES
// =============================================================================

// WordPress database operations
function vulnerable_wp_db_operations_1() {
    $user_input = $_GET['data'];
    $wpdb->insert('users', $user_input); // VULNERABLE: Insert with tainted data
}

function vulnerable_wp_db_operations_2() {
    $user_input = $_POST['data'];
    $wpdb->update('users', $user_input, array('id' => 1)); // VULNERABLE: Update with tainted data
}

function vulnerable_wp_db_operations_3() {
    $user_input = $_REQUEST['data'];
    $wpdb->delete('users', $user_input); // VULNERABLE: Delete with tainted data
}

function vulnerable_wp_db_operations_4() {
    $user_input = $_GET['data'];
    $wpdb->replace('users', $user_input); // VULNERABLE: Replace with tainted data
}

// =============================================================================
// WORDPRESS API VULNERABILITIES - REST API and AJAX
// =============================================================================

// REST API vulnerabilities
function vulnerable_rest_api_1() {
    $user_input = $request->get_param('id');
    $wpdb->query("SELECT * FROM users WHERE id = $user_input"); // VULNERABLE: REST API input
}

function vulnerable_rest_api_2() {
    $user_input = $request->get_param('name');
    $wpdb->query("SELECT * FROM users WHERE name = '$user_input'"); // VULNERABLE: REST API input
}

// AJAX vulnerabilities
function wp_ajax_vulnerable_handler() {
    $user_input = $_POST['data'];
    $wpdb->query("SELECT * FROM users WHERE data = '$user_input'"); // VULNERABLE: AJAX input
}

// =============================================================================
// EXTERNAL DATA SOURCE VULNERABILITIES
// =============================================================================

// External API vulnerabilities
function vulnerable_external_api_1() {
    $response = wp_remote_get('https://api.example.com/data');
    $data = wp_remote_retrieve_body($response);
    $wpdb->query("INSERT INTO data VALUES ('$data')"); // VULNERABLE: External API data
}

function vulnerable_external_api_2() {
    $response = wp_remote_post('https://api.example.com/data');
    $data = wp_remote_retrieve_body($response);
    $wpdb->query("UPDATE data SET content = '$data'"); // VULNERABLE: External API data
}

// =============================================================================
// WORDPRESS METADATA VULNERABILITIES
// =============================================================================

// WordPress metadata vulnerabilities
function vulnerable_metadata_1() {
    $user_input = get_option('user_data');
    $wpdb->query("SELECT * FROM users WHERE data = '$user_input'"); // VULNERABLE: Option data
}

function vulnerable_metadata_2() {
    $user_input = get_post_meta(1, 'custom_field', true);
    $wpdb->query("SELECT * FROM posts WHERE meta = '$user_input'"); // VULNERABLE: Post meta
}

function vulnerable_metadata_3() {
    $user_input = get_user_meta(1, 'custom_field', true);
    $wpdb->query("SELECT * FROM users WHERE meta = '$user_input'"); // VULNERABLE: User meta
}

// =============================================================================
// COMPLEX FLOW VULNERABILITIES - Multi-step taint flows
// =============================================================================

// Complex multi-step flows
function vulnerable_complex_flow_1() {
    $user_input = $_GET['id'];
    $temp1 = $user_input;
    $temp2 = $temp1;
    $temp3 = $temp2;
    $wpdb->query("SELECT * FROM users WHERE id = $temp3"); // VULNERABLE: Complex flow
}

function vulnerable_complex_flow_2() {
    $user_input = $_POST['name'];
    $processed = process_data($user_input);
    $final = finalize_data($processed);
    $wpdb->query("SELECT * FROM users WHERE name = '$final'"); // VULNERABLE: Complex flow
}

// =============================================================================
// HELPER FUNCTIONS (for bypass examples)
// =============================================================================

function some_function($data) {
    return $data; // Returns tainted data unchanged
}

function another_function($data) {
    return $data; // Returns tainted data unchanged
}

function process_data($data) {
    return $data; // Returns tainted data unchanged
}

function finalize_data($data) {
    return $data; // Returns tainted data unchanged
}
