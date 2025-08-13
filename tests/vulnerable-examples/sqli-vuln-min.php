<?php
	global $wpdb;
	$id = $_GET['id'];
	$wpdb->query( "SELECT * FROM wp_users WHERE ID = " . $id );

