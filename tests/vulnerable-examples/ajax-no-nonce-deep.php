<?php
// AJAX handler without nonce verification buried behind indirection

add_action('wp_ajax_my_action', 'my_action_handler');
add_action('wp_ajax_nopriv_my_action', 'my_action_handler');

function get_payload() {
    return isset($_POST['data']) ? $_POST['data'] : '';
}

function my_action_handler() {
    // Missing: check_ajax_referer or wp_verify_nonce
    $data = get_payload();
    // Capability check is also missing
    update_option('my_action_data', $data);
    wp_send_json_success(['ok' => true, 'data' => $data]);
}
?>


