<?php
	global $wpdb;
	$user_input = $_GET['id'];
	$sql = "SELECT * FROM {$wpdb->users} WHERE ID = " . $user_input;
	$results = $wpdb->get_results($sql);

