<?php
// Safe output and safe DB query
global $wpdb;
$msg = isset($_GET['msg']) ? sanitize_text_field(wp_unslash($_GET['msg'])) : '';
echo esc_html($msg);

$username = isset($_GET['user']) ? sanitize_user(wp_unslash($_GET['user'])) : '';
// Use prepare with correct placeholders
$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE user_login = %s", $username));
if ($row) {
    echo esc_html($row->user_email);
}
?>


