<?php
/**
 * Safe Nonce Examples
 * These examples demonstrate proper nonce security practices
 */

// SAFE: Proper nonce creation and verification
function safe_form_with_nonce() {
    ?>
    <form method="post">
        <?php wp_nonce_field('save_user_data_action'); ?>
        <input type="text" name="user_data" />
        <input type="submit" name="submit" value="Submit" />
    </form>
    <?php
}

function safe_form_processing() {
    if (isset($_POST['submit'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'save_user_data_action')) {
            $user_data = sanitize_text_field($_POST['user_data']);
            update_option('user_data', $user_data);
        } else {
            wp_die('Security check failed');
        }
    }
}

// SAFE: Strong nonce creation with specific action
function safe_strong_nonce() {
    $nonce = wp_create_nonce('delete_post_123');
    $nonce2 = wp_create_nonce('update_user_profile');
    $nonce3 = wp_create_nonce('save_plugin_settings');
}

// SAFE: Proper nonce field with action
function safe_nonce_field() {
    ?>
    <form method="post">
        <?php wp_nonce_field('process_form_action'); ?>
        <input type="text" name="data" />
        <input type="submit" value="Submit" />
    </form>
    <?php
}

// SAFE: Proper nonce verification with return value check
function safe_verification() {
    if (wp_verify_nonce($_POST['_wpnonce'], 'process_form_action')) {
        $data = sanitize_text_field($_POST['data']);
        // Process data safely
    } else {
        wp_die('Security check failed');
    }
}

// SAFE: Matching action names
function safe_matching_actions() {
    wp_create_nonce('save_post_action');
    // ... later in code ...
    if (wp_verify_nonce($_POST['_wpnonce'], 'save_post_action')) {
        // Process safely
    }
}

// SAFE: AJAX with proper nonce verification
function safe_ajax_handler() {
    add_action('wp_ajax_my_action', 'my_safe_ajax_handler');
}

function my_safe_ajax_handler() {
    check_ajax_referer('my_ajax_nonce_action', 'nonce');
    $data = sanitize_text_field($_POST['data']);
    wp_send_json_success($data);
}

// SAFE: Strong AJAX nonce verification
function safe_strong_ajax() {
    check_ajax_referer('delete_user_action', 'nonce');
    check_ajax_referer('update_settings_action', 'nonce');
}

// SAFE: REST API with proper nonce verification
function safe_rest_endpoint() {
    register_rest_route('my-namespace/v1', '/endpoint', array(
        'methods' => 'POST',
        'callback' => 'my_safe_rest_callback',
        'permission_callback' => 'my_permission_callback'
    ));
}

function my_permission_callback($request) {
    return wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest');
}

function my_safe_rest_callback($request) {
    $data = sanitize_text_field($request->get_param('data'));
    return new WP_REST_Response($data, 200);
}

// SAFE: Proper nonce expiration handling
function safe_expiration_handling() {
    $nonce_result = wp_verify_nonce($_POST['_wpnonce'], 'action_name');
    
    if ($nonce_result === false) {
        wp_die('Nonce expired. Please try again.');
    } elseif ($nonce_result === 0) {
        wp_die('Invalid nonce.');
    } else {
        // Process data safely
        $data = sanitize_text_field($_POST['data']);
    }
}

// SAFE: Descriptive action names
function safe_descriptive_actions() {
    wp_create_nonce('delete_user_123_action');
    wp_verify_nonce($_POST['_wpnonce'], 'delete_user_123_action');
    wp_nonce_field('save_plugin_settings_action');
}

// SAFE: Single nonce for entire form
function safe_single_nonce() {
    ?>
    <form method="post">
        <?php wp_nonce_field('process_entire_form_action'); ?>
        <input type="text" name="data1" />
        <input type="text" name="data2" />
        <input type="submit" value="Submit" />
    </form>
    <?php
}

// SAFE: Nonce with capability check
function safe_nonce_with_capability() {
    if (current_user_can('manage_options')) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'admin_action')) {
            // Process admin action safely
        }
    }
}

// SAFE: Nonce in admin pages
function safe_admin_nonce() {
    if (isset($_POST['submit'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'update_plugin_settings')) {
            update_option('plugin_setting', sanitize_text_field($_POST['setting']));
        }
    }
    ?>
    <div class="wrap">
        <h1>Plugin Settings</h1>
        <form method="post">
            <?php wp_nonce_field('update_plugin_settings'); ?>
            <input type="text" name="setting" value="<?php echo esc_attr(get_option('plugin_setting')); ?>" />
            <input type="submit" name="submit" value="Save Settings" />
        </form>
    </div>
    <?php
}
