<?php
// REST route without permission_callback validation
add_action('rest_api_init', function() {
    register_rest_route('insecure/v1', '/update', [
        'methods' => 'POST',
        // Missing permission_callback
        'callback' => function($req) {
            $v = isset($_POST['val']) ? $_POST['val'] : '';
            update_option('insecure_val', $v);
            return rest_ensure_response(['ok' => true]);
        }
    ]);
});
?>


