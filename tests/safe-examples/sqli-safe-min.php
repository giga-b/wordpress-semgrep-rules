<?php
	global $wpdb;
	$id = (int) $_GET['id'];
	$wpdb->prepare( 'SELECT * FROM wp_users WHERE ID = %d', $id );

