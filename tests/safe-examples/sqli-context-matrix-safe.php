<?php
// SQL Injection Context Matrix - Safe Examples

// Minimal WordPress stubs for linting
if (!class_exists('wpdb')) { class wpdb { public $users='wp_users'; public $posts='wp_posts'; public function query($q){return 1;} public function get_results($q){return [];} public function get_var($q){return 0;} public function prepare($q, ...$a){return $q;} public function esc_like($s){return $s;} } }
if (!function_exists('sanitize_text_field')) { function sanitize_text_field($s){ return is_string($s)? trim($s):$s; } }
if (!isset($wpdb)) { $wpdb = new wpdb(); }

// 1. Prepared statement with integer
$id = intval($_GET['id']);
$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID = %d", $id));

// 2. Prepared statement with post id
$post_id = intval($_POST['id']);
$wpdb->query($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID = %d", $post_id));

// 3. COUNT via prepare
$rid = intval($_REQUEST['id']);
$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->users} WHERE ID = %d", $rid));

// 4. LIKE with esc_like and prepare
$search = sanitize_text_field($_GET['search']);
$wpdb->query($wpdb->prepare("SELECT * FROM posts WHERE title LIKE %s", '%' . $wpdb->esc_like($search) . '%'));

// 5. Validated table name (whitelist)
$table = sanitize_text_field($_POST['table']);
$allowed = ['wp_posts','wp_users'];
if (in_array($table, $allowed, true)) {
    $wpdb->query("SELECT * FROM " . $table . " WHERE status = 'active'");
}

// 6. REST param with prepare
function sqli_safe_rest($request){
    global $wpdb;
    $name = sanitize_text_field($request->get_param('name'));
    return $wpdb->query($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE user_login = %s", $name));
}


