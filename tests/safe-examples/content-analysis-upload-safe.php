<?php
// Safe: validate content using exif_imagetype before moving

if (!function_exists('move_uploaded_file')) { function move_uploaded_file($a,$b){ return true; } }
if (!function_exists('sanitize_file_name')) { function sanitize_file_name($s){ return $s; } }
if (!function_exists('exif_imagetype')) { function exif_imagetype($p){ return IMAGETYPE_JPEG; } }
if (!defined('IMAGETYPE_JPEG')) { define('IMAGETYPE_JPEG', 2); }
if (!defined('IMAGETYPE_PNG')) { define('IMAGETYPE_PNG', 3); }

function upload_with_content_check(){
	if (!isset($_FILES['file'])) { return; }
	$file = $_FILES['file'];
	$allowed_magic = array(IMAGETYPE_JPEG, IMAGETYPE_PNG);
	$type = exif_imagetype($file['tmp_name']);
	if (in_array($type, $allowed_magic, true)) {
		$dest = '/var/www/html/uploads/' . sanitize_file_name($file['name']);
		move_uploaded_file($file['tmp_name'], $dest);
	}
}


