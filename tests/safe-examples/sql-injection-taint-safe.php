<?php
/**
 * SQL Injection Taint Analysis - Safe Examples
 * 
 * This file contains examples that should NOT trigger SQL injection taint analysis rules.
 * These patterns demonstrate proper SQL injection prevention techniques.
 */

// =============================================================================
// SAFE PREPARED STATEMENT PATTERNS - Proper prepared statement usage
// =============================================================================

// Safe prepared statement usage
function safe_prepared_statement_1() {
    $user_input = $_GET['id'];
    $wpdb->prepare("SELECT * FROM users WHERE id = %d", $user_input); // SAFE: Prepared statement
}

function safe_prepared_statement_2() {
    $user_input = $_POST['name'];
    $wpdb->prepare("SELECT * FROM users WHERE name = %s", $user_input); // SAFE: Prepared statement
}

function safe_prepared_statement_3() {
    $user_input = $_REQUEST['email'];
    $wpdb->prepare("SELECT * FROM users WHERE email = %s", $user_input); // SAFE: Prepared statement
}

// =============================================================================
// SAFE SANITIZATION PATTERNS - Proper sanitization before queries
// =============================================================================

// Safe sanitization usage
function safe_sanitization_1() {
    $user_input = $_GET['id'];
    $safe_input = esc_sql($user_input);
    $wpdb->query("SELECT * FROM users WHERE id = $safe_input"); // SAFE: SQL escaping
}

function safe_sanitization_2() {
    $user_input = $_POST['name'];
    $safe_input = sanitize_text_field($user_input);
    $wpdb->query("SELECT * FROM users WHERE name = '$safe_input'"); // SAFE: Text sanitization
}

function safe_sanitization_3() {
    $user_input = $_REQUEST['email'];
    $safe_input = sanitize_email($user_input);
    $wpdb->query("SELECT * FROM users WHERE email = '$safe_input'"); // SAFE: Email sanitization
}

// =============================================================================
// SAFE TYPE CASTING PATTERNS - Proper type casting before queries
// =============================================================================

// Safe type casting usage
function safe_type_casting_1() {
    $user_input = $_GET['id'];
    $safe_input = (int)$user_input;
    $wpdb->query("SELECT * FROM users WHERE id = $safe_input"); // SAFE: Integer casting
}

function safe_type_casting_2() {
    $user_input = $_POST['id'];
    $safe_input = intval($user_input);
    $wpdb->query("SELECT * FROM users WHERE id = $safe_input"); // SAFE: Integer validation
}

function safe_type_casting_3() {
    $user_input = $_REQUEST['price'];
    $safe_input = (float)$user_input;
    $wpdb->query("SELECT * FROM products WHERE price = $safe_input"); // SAFE: Float casting
}

// =============================================================================
// SAFE VALIDATION PATTERNS - Proper validation before queries
// =============================================================================

// Safe validation usage
function safe_validation_1() {
    $user_input = $_GET['id'];
    if (is_numeric($user_input)) {
        $wpdb->query("SELECT * FROM users WHERE id = $user_input"); // SAFE: Numeric validation
    }
}

function safe_validation_2() {
    $user_input = $_POST['email'];
    if (is_email($user_input)) {
        $wpdb->query("SELECT * FROM users WHERE email = '$user_input'"); // SAFE: Email validation
    }
}

function safe_validation_3() {
    $user_input = $_REQUEST['url'];
    if (is_url($user_input)) {
        $wpdb->query("SELECT * FROM links WHERE url = '$user_input'"); // SAFE: URL validation
    }
}

// =============================================================================
// SAFE WORDPRESS FUNCTION PATTERNS - Proper WordPress function usage
// =============================================================================

// Safe WordPress function usage
function safe_wp_function_1() {
    $user_input = $_GET['args'];
    $safe_args = array(
        'post_type' => 'post',
        'posts_per_page' => (int)$user_input
    );
    get_posts($safe_args); // SAFE: Proper WordPress function usage
}

function safe_wp_function_2() {
    $user_input = $_POST['role'];
    $safe_args = array(
        'role' => sanitize_text_field($user_input)
    );
    get_users($safe_args); // SAFE: Proper WordPress function usage
}

function safe_wp_function_3() {
    $user_input = $_REQUEST['taxonomy'];
    $safe_args = array(
        'taxonomy' => sanitize_text_field($user_input)
    );
    get_terms($safe_args); // SAFE: Proper WordPress function usage
}

// =============================================================================
// SAFE DATABASE OPERATION PATTERNS - Proper database operations
// =============================================================================

// Safe database operations
function safe_db_operations_1() {
    $user_input = $_GET['data'];
    $safe_data = array(
        'name' => sanitize_text_field($user_input['name']),
        'email' => sanitize_email($user_input['email'])
    );
    $wpdb->insert('users', $safe_data); // SAFE: Sanitized data
}

function safe_db_operations_2() {
    $user_input = $_POST['data'];
    $safe_data = array(
        'content' => sanitize_textarea_field($user_input['content'])
    );
    $wpdb->update('posts', $safe_data, array('id' => (int)$user_input['id'])); // SAFE: Sanitized data
}

function safe_db_operations_3() {
    $user_input = $_REQUEST['id'];
    $safe_where = array('id' => (int)$user_input);
    $wpdb->delete('users', $safe_where); // SAFE: Sanitized where clause
}

// =============================================================================
// SAFE REST API PATTERNS - Proper REST API handling
// =============================================================================

// Safe REST API usage
function safe_rest_api_1() {
    $user_input = $request->get_param('id');
    $safe_id = (int)$user_input;
    $wpdb->prepare("SELECT * FROM users WHERE id = %d", $safe_id); // SAFE: Prepared statement
}

function safe_rest_api_2() {
    $user_input = $request->get_param('name');
    $safe_name = sanitize_text_field($user_input);
    $wpdb->prepare("SELECT * FROM users WHERE name = %s", $safe_name); // SAFE: Prepared statement
}

// =============================================================================
// SAFE AJAX PATTERNS - Proper AJAX handling
// =============================================================================

// Safe AJAX usage
function wp_ajax_safe_handler() {
    $user_input = $_POST['data'];
    $safe_data = sanitize_text_field($user_input);
    $wpdb->prepare("SELECT * FROM users WHERE data = %s", $safe_data); // SAFE: Prepared statement
}

// =============================================================================
// SAFE EXTERNAL DATA PATTERNS - Proper external data handling
// =============================================================================

// Safe external API usage
function safe_external_api_1() {
    $response = wp_remote_get('https://api.example.com/data');
    $data = wp_remote_retrieve_body($response);
    $safe_data = sanitize_text_field($data);
    $wpdb->prepare("INSERT INTO data VALUES (%s)", $safe_data); // SAFE: Prepared statement
}

function safe_external_api_2() {
    $response = wp_remote_post('https://api.example.com/data');
    $data = wp_remote_retrieve_body($response);
    $safe_data = sanitize_textarea_field($data);
    $wpdb->prepare("UPDATE data SET content = %s", $safe_data); // SAFE: Prepared statement
}

// =============================================================================
// SAFE METADATA PATTERNS - Proper metadata handling
// =============================================================================

// Safe metadata usage
function safe_metadata_1() {
    $user_input = get_option('user_data');
    $safe_data = sanitize_text_field($user_input);
    $wpdb->prepare("SELECT * FROM users WHERE data = %s", $safe_data); // SAFE: Prepared statement
}

function safe_metadata_2() {
    $user_input = get_post_meta(1, 'custom_field', true);
    $safe_data = sanitize_text_field($user_input);
    $wpdb->prepare("SELECT * FROM posts WHERE meta = %s", $safe_data); // SAFE: Prepared statement
}

function safe_metadata_3() {
    $user_input = get_user_meta(1, 'custom_field', true);
    $safe_data = sanitize_text_field($user_input);
    $wpdb->prepare("SELECT * FROM users WHERE meta = %s", $safe_data); // SAFE: Prepared statement
}

// =============================================================================
// SAFE COMPLEX PATTERNS - Proper complex data handling
// =============================================================================

// Safe complex patterns
function safe_complex_1() {
    $user_input = $_GET['id'];
    $safe_input = (int)$user_input;
    $temp1 = $safe_input;
    $temp2 = $temp1;
    $temp3 = $temp2;
    $wpdb->prepare("SELECT * FROM users WHERE id = %d", $temp3); // SAFE: Sanitized complex flow
}

function safe_complex_2() {
    $user_input = $_POST['name'];
    $safe_input = sanitize_text_field($user_input);
    $processed = process_data_safe($safe_input);
    $final = finalize_data_safe($processed);
    $wpdb->prepare("SELECT * FROM users WHERE name = %s", $final); // SAFE: Sanitized complex flow
}

// =============================================================================
// SAFE WHITELIST PATTERNS - Proper whitelist validation
// =============================================================================

// Safe whitelist patterns
function safe_whitelist_1() {
    $user_input = $_GET['table'];
    $allowed_tables = array('users', 'posts', 'comments');
    if (in_array($user_input, $allowed_tables)) {
        $wpdb->query("SELECT * FROM $user_input"); // SAFE: Whitelist validation
    }
}

function safe_whitelist_2() {
    $user_input = $_POST['action'];
    $allowed_actions = array('insert', 'update', 'delete');
    if (in_array($user_input, $allowed_actions)) {
        $wpdb->query("$user_input FROM users"); // SAFE: Whitelist validation
    }
}

// =============================================================================
// SAFE CONSTANT PATTERNS - Using constants and hardcoded values
// =============================================================================

// Safe constant patterns
function safe_constant_1() {
    $table_name = 'users'; // SAFE: Hardcoded table name
    $wpdb->query("SELECT * FROM $table_name");
}

function safe_constant_2() {
    $user_id = 1; // SAFE: Hardcoded user ID
    $wpdb->query("SELECT * FROM users WHERE id = $user_id");
}

function safe_constant_3() {
    $sql = "SELECT * FROM users WHERE status = 'active'"; // SAFE: Hardcoded query
    $wpdb->query($sql);
}

// =============================================================================
// HELPER FUNCTIONS (for safe examples)
// =============================================================================

function process_data_safe($data) {
    return sanitize_text_field($data); // Returns sanitized data
}

function finalize_data_safe($data) {
    return esc_sql($data); // Returns SQL escaped data
}
