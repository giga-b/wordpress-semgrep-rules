<?php
// Safe: move to quarantine (temp) then enqueue async scan

if (!function_exists('move_uploaded_file')) { function move_uploaded_file($a,$b){ return true; } }
if (!function_exists('sanitize_file_name')) { function sanitize_file_name($s){ return $s; } }

function enqueue_scan_task($path) { /* stub */ }

function upload_with_quarantine(){
	if (!isset($_FILES['file'])) { return; }
	$file = $_FILES['file'];
	$quarantine = sys_get_temp_dir() . DIRECTORY_SEPARATOR . sanitize_file_name($file['name']);
	move_uploaded_file($file['tmp_name'], $quarantine);
	enqueue_scan_task($quarantine);
}


