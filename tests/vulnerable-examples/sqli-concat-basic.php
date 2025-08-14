<?php
// Basic SQL injection via string concatenation
global $wpdb;
$id = isset($_GET['id']) ? $_GET['id'] : '0';
$sql = "SELECT * FROM {$wpdb->users} WHERE ID = " . $id;
$wpdb->get_results($sql);
?>


