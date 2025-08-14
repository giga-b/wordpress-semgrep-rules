<?php
// SQL Injection Context Matrix - Vulnerable Examples

// Minimal WordPress stubs for linting
if (!class_exists('wpdb')) { class wpdb { public $users='wp_users'; public $posts='wp_posts'; public function query($q){return 1;} public function get_results($q){return [];} public function get_var($q){return 0;} public function prepare($q){return $q;} public function esc_like($s){return $s;} } }
if (!isset($wpdb)) { $wpdb = new wpdb(); }

// 1. Direct concatenation of GET into query
$id = $_GET['id'];
$sql = "SELECT * FROM {$wpdb->users} WHERE ID = " . $id; // vulnerable
$wpdb->get_results($sql);

// 2. Direct concatenation of POST into query
$post_id = $_POST['id'];
$sql2 = "SELECT * FROM {$wpdb->posts} WHERE ID = " . $post_id; // vulnerable
$wpdb->query($sql2);

// 3. REQUEST into get_var
$rid = $_REQUEST['id'];
$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users} WHERE ID = " . $rid); // vulnerable

// 4. LIKE pattern without prepare
$search = $_GET['search'];
$wpdb->query("SELECT * FROM posts WHERE title LIKE '%$search%'"); // vulnerable

// 5. Dynamic table name
$table = $_POST['table'];
$wpdb->query("SELECT * FROM " . $table . " WHERE status = 'active'"); // vulnerable

// 6. REST param into query
function sqli_vuln_rest($request){
    global $wpdb;
    $name = $request->get_param('name');
    return $wpdb->query("SELECT * FROM {$wpdb->users} WHERE user_login = '" . $name . "'"); // vulnerable
}


