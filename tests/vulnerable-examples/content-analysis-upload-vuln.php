<?php
// Vulnerable: no file content analysis before moving

if (!function_exists('move_uploaded_file')) { function move_uploaded_file($a,$b){ return true; } }
if (!function_exists('add_action')) { function add_action($h,$c){ /* no-op */ } }

add_action('wp_ajax_upload_no_content_check', 'upload_no_content_check');
function upload_no_content_check(){
	if (!isset($_FILES['file'])) { return; }
	$file = $_FILES['file'];
	$allowed_exts = array('jpg','jpeg','png');
	$info = pathinfo($file['name']);
	if (in_array(strtolower($info['extension']), $allowed_exts, true)) {
		// Missing content analysis (no exif_imagetype/getimagesize or byte inspection)
		move_uploaded_file($file['tmp_name'], '/var/www/html/uploads/' . $file['name']);
	}
}


