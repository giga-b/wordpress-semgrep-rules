<?php
/**
 * Taint Analysis Vulnerable Examples
 * 
 * This file contains examples that should trigger taint analysis rules.
 * These demonstrate various taint flow vulnerabilities where untrusted
 * data flows from sources to sinks without proper sanitization.
 */

// =============================================================================
// XSS VULNERABILITIES - Tainted data flows to output
// =============================================================================

// Direct XSS - user input to echo
$user_input = $_GET['name'];
echo $user_input; // Should trigger taint-flow-xss-user-to-output

// XSS via print
$user_input = $_POST['message'];
print $user_input; // Should trigger taint-sink-xss-print

// XSS via printf
$user_input = $_REQUEST['title'];
printf("Title: %s", $user_input); // Should trigger taint-sink-xss-printf

// XSS with concatenation
$user_input = $_GET['search'];
echo "Search results for: " . $user_input; // Should trigger taint-flow-xss-user-to-output

// =============================================================================
// SQL INJECTION VULNERABILITIES - Tainted data flows to queries
// =============================================================================

// Direct SQL injection
$user_id = $_POST['id'];
$wpdb->query("SELECT * FROM users WHERE id = " . $user_id); // Should trigger taint-flow-sql-user-to-query

// SQL injection via prepare with tainted query
$table_name = $_GET['table'];
$wpdb->prepare("SELECT * FROM " . $table_name . " WHERE status = %s", $status); // Should trigger taint-sink-sql-prepare

// SQL injection with concatenation
$search = $_POST['search'];
$wpdb->query("SELECT * FROM posts WHERE title LIKE '%" . $search . "%'"); // Should trigger taint-flow-sql-user-to-query

// =============================================================================
// FILE INCLUSION VULNERABILITIES - Tainted data flows to file operations
// =============================================================================

// File inclusion vulnerability
$file_path = $_GET['file'];
include $file_path; // Should trigger taint-flow-file-user-to-include

// File inclusion via require
$file_path = $_POST['template'];
require $file_path; // Should trigger taint-sink-file-require

// File content vulnerability
$file_path = $_REQUEST['config'];
$content = file_get_contents($file_path); // Should trigger taint-sink-file-get-contents

// =============================================================================
// COMMAND INJECTION VULNERABILITIES - Tainted data flows to commands
// =============================================================================

// Command injection via exec
$command = $_POST['cmd'];
exec($command); // Should trigger taint-flow-command-user-to-exec

// Command injection via system
$command = $_GET['system_cmd'];
system($command); // Should trigger taint-sink-command-system

// Command injection via shell_exec
$command = $_REQUEST['shell_cmd'];
shell_exec($command); // Should trigger taint-sink-command-shell-exec

// =============================================================================
// HEADER INJECTION VULNERABILITIES - Tainted data flows to headers
// =============================================================================

// Header injection
$redirect_url = $_GET['redirect'];
header("Location: " . $redirect_url); // Should trigger taint-sink-header-injection

// =============================================================================
// COMPLEX TAINT FLOWS - Multi-step taint propagation
// =============================================================================

// Taint flow through variables
$user_input = $_POST['data'];
$processed_data = $user_input;
$final_data = $processed_data;
echo $final_data; // Should trigger taint-flow-xss-user-to-output

// Taint flow through function parameters
function process_user_data($data) {
    return $data;
}

$user_input = $_GET['input'];
$result = process_user_data($user_input);
echo $result; // Should trigger taint-flow-xss-user-to-output

// Taint flow through array access
$user_data = $_POST['user_data'];
$name = $user_data['name'];
echo $name; // Should trigger taint-flow-xss-user-to-output

// =============================================================================
// TAINT SOURCES - Various sources of untrusted data
// =============================================================================

// User input sources
$get_data = $_GET['param']; // Should trigger taint-source-user-input
$post_data = $_POST['param']; // Should trigger taint-source-post-input
$request_data = $_REQUEST['param']; // Should trigger taint-source-request-input
$cookie_data = $_COOKIE['param']; // Should trigger taint-source-cookie-input
$server_data = $_SERVER['HTTP_USER_AGENT']; // Should trigger taint-source-server-input

// File content sources
$file_content = file_get_contents('user_upload.txt'); // Should trigger taint-source-file-content
$file_handle = fopen('data.txt', 'r');
$file_data = fread($file_handle, 1024); // Should trigger taint-source-file-read

// Database query sources
$db_results = $wpdb->get_results("SELECT * FROM users"); // Should trigger taint-source-database-query
$db_row = $wpdb->get_row("SELECT * FROM posts WHERE id = 1"); // Should trigger taint-source-database-row

// External API sources
$api_response = wp_remote_get('https://api.example.com/data'); // Should trigger taint-source-external-api
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.example.com/data');
$curl_response = curl_exec($ch); // Should trigger taint-source-curl

// =============================================================================
// TAINT SINKS - Various sinks where tainted data can cause harm
// =============================================================================

// XSS sinks
echo $get_data; // Should trigger taint-sink-xss-echo
print $post_data; // Should trigger taint-sink-xss-print
printf("Data: %s", $request_data); // Should trigger taint-sink-xss-printf

// SQL injection sinks
$wpdb->query($get_data); // Should trigger taint-sink-sql-query
$wpdb->prepare($post_data, $request_data); // Should trigger taint-sink-sql-prepare

// File operation sinks
include $get_data; // Should trigger taint-sink-file-include
require $post_data; // Should trigger taint-sink-file-require
file_get_contents($request_data); // Should trigger taint-sink-file-get-contents

// Command execution sinks
exec($get_data); // Should trigger taint-sink-command-exec
system($post_data); // Should trigger taint-sink-command-system
shell_exec($request_data); // Should trigger taint-sink-command-shell-exec

// Header injection sinks
header($get_data); // Should trigger taint-sink-header-injection

// =============================================================================
// MISSING SANITIZATION - Examples where sanitization should be applied
// =============================================================================

// User input without sanitization
$unsanitized_input = $_GET['unsafe'];
echo $unsanitized_input; // Should trigger taint-flow-xss-user-to-output

// Database input without sanitization
$unsafe_id = $_POST['user_id'];
$wpdb->query("SELECT * FROM users WHERE id = " . $unsafe_id); // Should trigger taint-flow-sql-user-to-query

// File path without sanitization
$unsafe_file = $_GET['file'];
include $unsafe_file; // Should trigger taint-flow-file-user-to-include

// Command without sanitization
$unsafe_command = $_POST['command'];
exec($unsafe_command); // Should trigger taint-flow-command-user-to-exec
