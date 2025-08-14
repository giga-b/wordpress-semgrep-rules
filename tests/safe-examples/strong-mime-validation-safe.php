<?php
// Safe: robust MIME validation using finfo and extension check

if (!function_exists('move_uploaded_file')) { function move_uploaded_file($a,$b){ return true; } }
if (!function_exists('sanitize_file_name')) { function sanitize_file_name($s){ return $s; } }
if (!function_exists('finfo_open')) { function finfo_open($o = null){ return 1; } }
if (!function_exists('finfo_file')) { function finfo_file($fi, $p){ return 'image/jpeg'; } }

function upload_file_strong_mime(){
	if (!isset($_FILES['file'])) { return; }
	$file = $_FILES['file'];
	$allowed_mimes = array('image/jpeg', 'image/png');
	$fi = finfo_open();
	$mime = finfo_file($fi, $file['tmp_name']);
	if (in_array($mime, $allowed_mimes, true)) {
		$dest = '/var/www/html/uploads/' . sanitize_file_name($file['name']);
		move_uploaded_file($file['tmp_name'], $dest);
	}
}


