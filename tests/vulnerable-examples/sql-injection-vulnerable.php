<?php
// This should trigger SQL injection rule
$user_input = $_GET['id'];
$sql = "SELECT * FROM {$wpdb->users} WHERE ID = " . $user_input;
$results = $wpdb->get_results($sql);

// Another SQL injection pattern
$table = $_POST['table'];
$wpdb->query("SELECT * FROM " . $table . " WHERE status = 'active'");

// Direct query without prepare
$search = $_GET['search'];
$wpdb->query("SELECT * FROM posts WHERE title LIKE '%$search%'");

// Misuse of prepare with string concatenation
$table_name = $_POST['table'];
$wpdb->prepare("SELECT * FROM " . $table_name . " WHERE id = %s", $user_id);
