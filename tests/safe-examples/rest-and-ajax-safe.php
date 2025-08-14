<?php
// Safe REST route and AJAX handler
add_action('rest_api_init', function() {
    register_rest_route('example/v1', '/data', [
        'methods' => 'POST',
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
        'callback' => function($req) {
            $v = sanitize_text_field($req->get_param('v'));
            return rest_ensure_response(['v' => $v]);
        }
    ]);
});

add_action('wp_ajax_action_safe', function() {
    check_ajax_referer('action_safe_nonce');
    if (!current_user_can('edit_posts')) { wp_die('forbidden'); }
    $x = isset($_POST['x']) ? sanitize_text_field(wp_unslash($_POST['x'])) : '';
    echo esc_html($x);
    wp_die();
});
?>


