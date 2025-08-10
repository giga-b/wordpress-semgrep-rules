<?php
// This should trigger REST API rule
register_rest_route('my-plugin/v1', '/data', [
    'methods' => 'GET',
    'callback' => 'get_data',
    'permission_callback' => '__return_true' // No authentication!
]);

function get_data($request) {
    return $_GET['data']; // Direct output without sanitization
}

// Another REST API vulnerability
register_rest_route('my-plugin/v1', '/users', [
    'methods' => 'POST',
    'callback' => 'create_user',
    'permission_callback' => function() { return true; } // Always returns true
]);

function create_user($request) {
    $user_data = $request->get_param('user_data');
    return $user_data; // No validation or sanitization
}

// AJAX endpoint without nonce verification
add_action('wp_ajax_my_action', 'my_ajax_handler');
function my_ajax_handler() {
    $data = $_POST['data'];
    echo $data; // XSS vulnerability
}
