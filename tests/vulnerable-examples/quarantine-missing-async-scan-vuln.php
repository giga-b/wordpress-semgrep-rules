<?php
// Vulnerable: async scan enqueued after moving file to final public directory (no quarantine)

if (!function_exists('move_uploaded_file')) { function move_uploaded_file($a,$b){ return true; } }

function enqueue_scan_task($path) { /* stub */ }

function upload_async_scan_no_quarantine(){
	if (!isset($_FILES['file'])) { return; }
	$file = $_FILES['file'];
	$dest = '/var/www/html/wp-content/uploads/' . $file['name'];
	move_uploaded_file($file['tmp_name'], $dest);
	enqueue_scan_task($dest);
}


