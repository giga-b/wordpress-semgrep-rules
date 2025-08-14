<?php
// Vulnerable: weak MIME validation using client-provided type

if (!function_exists('move_uploaded_file')) { function move_uploaded_file($a,$b){ return true; } }
if (!function_exists('add_action')) { function add_action($h,$c){ /* no-op */ } }

add_action('wp_ajax_upload_file_weak_mime', 'upload_file_weak_mime');
function upload_file_weak_mime(){
	if (!isset($_FILES['file'])) { return; }
	$file = $_FILES['file'];
	$allowed_types = array('image/jpeg', 'image/png');
	// Weak: relies on client-provided type
	if (in_array($file['type'], $allowed_types, true)) {
		move_uploaded_file($file['tmp_name'], '/var/www/html/uploads/' . $file['name']);
	}
}


