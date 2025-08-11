<?php
/**
 * Safe Nonce Lifecycle Examples
 * These examples demonstrate proper nonce lifecycle security practices
 * for Task 1.5: Nonce Lifecycle Detection Rules
 */

// ============================================================================
// TASK 1.5.1: SAFE NONCE CREATION EXAMPLES
// ============================================================================

// SAFE: Strong nonce creation with specific action names
function safe_strong_nonce_creation() {
    $nonce = wp_create_nonce('delete_user_123_action');
    $nonce2 = wp_create_nonce('update_plugin_settings_action');
    $nonce3 = wp_create_nonce('save_post_456_action');
    $nonce4 = wp_create_nonce('process_form_data_action');
}

// SAFE: Nonce field creation with specific actions
function safe_nonce_field_creation() {
    wp_nonce_field('save_user_profile_action');
    wp_nonce_field('update_theme_settings_action');
    wp_nonce_field('delete_comment_789_action');
}

// SAFE: Nonce URL creation with specific actions
function safe_nonce_url_creation() {
    $url = wp_nonce_url('admin.php?page=settings', 'update_settings_action');
    $url2 = wp_nonce_url('admin.php?action=delete&id=123', 'delete_item_action');
    $url3 = wp_nonce_url('admin.php?action=export', 'export_data_action');
}

// SAFE: Nonce AYS creation with specific actions
function safe_nonce_ays_creation() {
    wp_nonce_ays('delete_permanent_action');
    wp_nonce_ays('reset_all_settings_action');
    wp_nonce_ays('uninstall_plugin_action');
}

// ============================================================================
// TASK 1.5.2: SAFE NONCE INCLUSION EXAMPLES
// ============================================================================

// SAFE: Nonce field inclusion in forms
function safe_form_with_nonce_field() {
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('save_form_data_action'); ?>
        <input type="text" name="user_name" />
        <input type="email" name="user_email" />
        <input type="submit" name="submit" value="Save" />
    </form>
    <?php
}

// SAFE: Hidden nonce field inclusion
function safe_hidden_nonce_field() {
    ?>
    <form method="post">
        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('custom_action_name'); ?>" />
        <input type="text" name="data" />
        <input type="submit" value="Submit" />
    </form>
    <?php
}

// SAFE: AJAX nonce inclusion in JavaScript
function safe_ajax_nonce_inclusion() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('#my-form').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'my_ajax_action',
                    nonce: '<?php echo wp_create_nonce("my_ajax_action"); ?>',
                    form_data: $('#my-form').serialize()
                },
                success: function(response) {
                    console.log('Success:', response);
                }
            });
        });
    });
    </script>
    <?php
}

// SAFE: REST API nonce inclusion
function safe_rest_api_nonce_inclusion() {
    ?>
    <script>
    fetch('/wp-json/my-namespace/v1/endpoint', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'
        },
        body: JSON.stringify({
            data: 'test data'
        })
    });
    </script>
    <?php
}

// ============================================================================
// TASK 1.5.3: SAFE NONCE VERIFICATION EXAMPLES
// ============================================================================

// SAFE: Form processing with proper nonce verification
function safe_form_processing() {
    if (isset($_POST['submit'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'save_form_data_action')) {
            $user_name = sanitize_text_field($_POST['user_name']);
            $user_email = sanitize_email($_POST['user_email']);
            update_option('user_data', array(
                'name' => $user_name,
                'email' => $user_email
            ));
            echo 'Data saved successfully!';
        } else {
            wp_die('Security check failed');
        }
    }
}

// SAFE: AJAX handler with proper nonce verification
function safe_ajax_handler() {
    add_action('wp_ajax_my_ajax_action', 'my_safe_ajax_handler');
}

function my_safe_ajax_handler() {
    check_ajax_referer('my_ajax_action', 'nonce');
    $form_data = sanitize_text_field($_POST['form_data']);
    wp_send_json_success(array('message' => 'Data processed successfully'));
}

// SAFE: Admin form processing with proper nonce verification
function safe_admin_form_processing() {
    if (isset($_POST['admin_submit'])) {
        check_admin_referer('update_admin_settings_action');
        $setting_value = sanitize_text_field($_POST['setting_value']);
        update_option('admin_setting', $setting_value);
        echo 'Admin settings updated successfully!';
    }
}

// SAFE: REST API endpoint with proper nonce verification
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
    return new WP_REST_Response(array('message' => 'Data processed'), 200);
}

// SAFE: Multiple nonce verifications in same function
function safe_multiple_nonce_verifications() {
    // First nonce check
    if (wp_verify_nonce($_POST['_wpnonce'], 'delete_action')) {
        // Handle delete action
    }
    
    // Second nonce check
    if (wp_verify_nonce($_POST['_wpnonce2'], 'update_action')) {
        // Handle update action
    }
}

// ============================================================================
// TASK 1.5.4: SAFE NONCE EXPIRATION HANDLING EXAMPLES
// ============================================================================

// SAFE: Proper nonce expiration handling with specific error messages
function safe_proper_expiration_handling() {
    $result = wp_verify_nonce($_POST['_wpnonce'], 'action_name');
    
    if ($result === false) {
        wp_die('Nonce expired. Please refresh the page and try again.');
    } elseif ($result === 0) {
        wp_die('Invalid nonce. Security check failed.');
    } else {
        // Process data safely
        $data = sanitize_text_field($_POST['data']);
        update_option('processed_data', $data);
        echo 'Data processed successfully!';
    }
}

// SAFE: Basic nonce expiration handling with wp_die
function safe_basic_expiration_handling() {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'action_name')) {
        wp_die('Security check failed. Please try again.');
    }
    
    // Process data safely
    $data = sanitize_text_field($_POST['data']);
    update_option('processed_data', $data);
}

// SAFE: Nonce expiration handling with user-friendly messages
function safe_user_friendly_expiration_handling() {
    $nonce_result = wp_verify_nonce($_POST['_wpnonce'], 'user_action');
    
    if ($nonce_result === false) {
        wp_redirect(add_query_arg('error', 'expired', wp_get_referer()));
        exit;
    } elseif ($nonce_result === 0) {
        wp_redirect(add_query_arg('error', 'invalid', wp_get_referer()));
        exit;
    } else {
        // Process user action safely
        $user_action = sanitize_text_field($_POST['user_action']);
        process_user_action($user_action);
        wp_redirect(add_query_arg('success', '1', wp_get_referer()));
        exit;
    }
}

// SAFE: AJAX nonce expiration handling
function safe_ajax_expiration_handling() {
    add_action('wp_ajax_my_action', 'my_ajax_expiration_handler');
}

function my_ajax_expiration_handler() {
    $nonce_result = check_ajax_referer('my_action', 'nonce', false);
    
    if ($nonce_result === false) {
        wp_send_json_error(array('message' => 'Nonce expired. Please refresh the page.'));
    } elseif ($nonce_result === 0) {
        wp_send_json_error(array('message' => 'Invalid nonce. Security check failed.'));
    } else {
        // Process AJAX request safely
        $data = sanitize_text_field($_POST['data']);
        wp_send_json_success(array('message' => 'Data processed successfully'));
    }
}

// ============================================================================
// SAFE CROSS-FILE NONCE LIFECYCLE EXAMPLES
// ============================================================================

// SAFE: Matching nonce creation and verification across files
function safe_matching_nonce_lifecycle() {
    // In form file
    wp_nonce_field('save_user_data_action');
    
    // In processing file
    if (wp_verify_nonce($_POST['_wpnonce'], 'save_user_data_action')) {
        // Process safely
    }
}

// SAFE: AJAX nonce lifecycle with matching actions
function safe_ajax_nonce_lifecycle() {
    // In JavaScript file
    $nonce = wp_create_nonce('ajax_action');
    
    // In AJAX handler file
    check_ajax_referer('ajax_action', 'nonce');
}

// SAFE: REST API nonce lifecycle
function safe_rest_nonce_lifecycle() {
    // In frontend
    $nonce = wp_create_nonce('wp_rest');
    
    // In REST endpoint
    wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest');
}

// ============================================================================
// SAFE NONCE LIFECYCLE BEST PRACTICES
// ============================================================================

// SAFE: Nonce with capability checks
function safe_nonce_with_capability() {
    if (current_user_can('manage_options')) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'admin_action')) {
            // Process admin action safely
        }
    }
}

// SAFE: Nonce with user-specific actions
function safe_user_specific_nonce() {
    $user_id = get_current_user_id();
    $nonce = wp_create_nonce('user_' . $user_id . '_action');
    
    if (wp_verify_nonce($_POST['_wpnonce'], 'user_' . $user_id . '_action')) {
        // Process user-specific action safely
    }
}

// SAFE: Nonce with time-based actions
function safe_time_based_nonce() {
    $timestamp = time();
    $nonce = wp_create_nonce('time_based_' . $timestamp . '_action');
    
    if (wp_verify_nonce($_POST['_wpnonce'], 'time_based_' . $timestamp . '_action')) {
        // Process time-based action safely
    }
}

// SAFE: Nonce with context-specific actions
function safe_context_specific_nonce() {
    $context = 'edit_post_' . get_the_ID();
    $nonce = wp_create_nonce($context);
    
    if (wp_verify_nonce($_POST['_wpnonce'], $context)) {
        // Process context-specific action safely
    }
}

// SAFE: Nonce with multiple security layers
function safe_multi_layer_nonce() {
    // Layer 1: Capability check
    if (!current_user_can('edit_posts')) {
        wp_die('Insufficient permissions');
    }
    
    // Layer 2: Nonce verification
    if (!wp_verify_nonce($_POST['_wpnonce'], 'edit_post_action')) {
        wp_die('Security check failed');
    }
    
    // Layer 3: Data validation
    $post_id = intval($_POST['post_id']);
    if (!$post_id || !current_user_can('edit_post', $post_id)) {
        wp_die('Invalid post or insufficient permissions');
    }
    
    // Process safely
    $post_title = sanitize_text_field($_POST['post_title']);
    wp_update_post(array(
        'ID' => $post_id,
        'post_title' => $post_title
    ));
}
