<?php
// Safe AJAX handler with nonce and capability checks
add_action('wp_ajax_save_item', function() {
    check_ajax_referer('save_item_nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['error' => 'forbidden'], 403);
        return;
    }
    $val = isset($_POST['val']) ? sanitize_text_field(wp_unslash($_POST['val'])) : '';
    update_option('safe_item', $val);
    wp_send_json_success(['ok' => true]);
});
?>


