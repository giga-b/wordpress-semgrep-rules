<?php
/**
 * Safe Capability Check Examples
 * These examples demonstrate proper capability check security practices
 */

// SAFE: Proper capability check for user deletion
function safe_delete_user() {
    if (isset($_POST['delete_user'])) {
        if (current_user_can('delete_users')) {
            wp_delete_user(intval($_POST['user_id']));
        } else {
            wp_die('Insufficient permissions');
        }
    }
}

// SAFE: Proper admin capability check for settings
function safe_update_settings() {
    if (isset($_POST['update_settings'])) {
        if (current_user_can('manage_options')) {
            update_option('sensitive_setting', sanitize_text_field($_POST['value']));
        } else {
            wp_die('Insufficient permissions');
        }
    }
}

// SAFE: Specific capability check for sensitive operation
function safe_specific_check() {
    if (current_user_can('delete_users')) { // Specific capability for user deletion
        wp_delete_user($_POST['user_id']);
    }
}

// SAFE: Capability check with nonce verification
function safe_capability_with_nonce() {
    if (current_user_can('manage_options')) {
        if (isset($_POST['submit'])) {
            if (wp_verify_nonce($_POST['_wpnonce'], 'update_settings')) {
                update_option('setting', sanitize_text_field($_POST['value']));
            } else {
                wp_die('Security check failed');
            }
        }
    }
}

// SAFE: AJAX handler with proper capability check
function safe_ajax_handler() {
    add_action('wp_ajax_delete_user', 'delete_user_ajax_safe');
}

function delete_user_ajax_safe() {
    if (current_user_can('delete_users')) {
        wp_delete_user(intval($_POST['user_id']));
        wp_send_json_success('User deleted');
    } else {
        wp_send_json_error('Insufficient permissions');
    }
}

// SAFE: Specific AJAX capability check
function safe_specific_ajax() {
    if (current_user_can('delete_users')) { // Specific capability for user deletion
        // Perform sensitive operation
        wp_delete_user($_POST['user_id']);
    }
}

// SAFE: REST API with proper capability check
function safe_rest_endpoint() {
    register_rest_route('my-namespace/v1', '/users', array(
        'methods' => 'DELETE',
        'callback' => 'delete_user_rest_safe',
        'permission_callback' => function() {
            return current_user_can('delete_users');
        }
    ));
}

function delete_user_rest_safe($request) {
    wp_delete_user($request->get_param('user_id'));
    return new WP_REST_Response('User deleted', 200);
}

// SAFE: Strong REST API capability check
function safe_strong_rest() {
    register_rest_route('my-namespace/v1', '/admin', array(
        'methods' => 'POST',
        'callback' => 'admin_action_rest_safe',
        'permission_callback' => function() {
            return current_user_can('manage_options'); // Specific admin capability
        }
    ));
}

// SAFE: Using specific capability instead of role check
function safe_specific_capability() {
    if (current_user_can('manage_options')) { // Specific capability instead of role
        // Admin operation
        update_option('admin_setting', sanitize_text_field($_POST['value']));
    }
}

// SAFE: Proper capability check instead of role comparison
function safe_capability_instead_of_role() {
    if (current_user_can('delete_users')) { // Use capability instead of role check
        // Admin operation
        wp_delete_user($_POST['user_id']);
    }
}

// SAFE: Proper capability check for conditional logic
function safe_conditional_capability() {
    $action = $_POST['action'];
    if ($action === 'delete' && current_user_can('delete_users')) {
        wp_delete_user($_POST['user_id']);
    } elseif ($action === 'update' && current_user_can('edit_users')) {
        wp_update_user($_POST['user_data']);
    }
}

// SAFE: File operation with capability check
function safe_file_operation() {
    if (isset($_POST['upload_file'])) {
        if (current_user_can('upload_files')) {
            move_uploaded_file($_FILES['file']['tmp_name'], $destination);
        }
    }
}

// SAFE: Database operation with capability check
function safe_db_operation() {
    if (isset($_POST['delete_option'])) {
        if (current_user_can('manage_options')) {
            delete_option(sanitize_text_field($_POST['option_name']));
        }
    }
}

// SAFE: Plugin management with capability check
function safe_plugin_management() {
    if (isset($_POST['activate_plugin'])) {
        if (current_user_can('activate_plugins')) {
            activate_plugin(sanitize_text_field($_POST['plugin_file']));
        }
    }
}

// SAFE: User management with capability check
function safe_user_management() {
    if (isset($_POST['create_user'])) {
        if (current_user_can('create_users')) {
            wp_insert_user($_POST['user_data']);
        }
    }
}

// SAFE: Content management with capability check
function safe_content_management() {
    if (isset($_POST['publish_post'])) {
        if (current_user_can('publish_posts')) {
            wp_publish_post(intval($_POST['post_id']));
        }
    }
}

// SAFE: Settings management with capability check
function safe_settings_management() {
    if (isset($_POST['update_settings'])) {
        if (current_user_can('manage_options')) {
            update_option('site_title', sanitize_text_field($_POST['title']));
            update_option('site_description', sanitize_text_field($_POST['description']));
        }
    }
}

// SAFE: Multisite operation with capability check
function safe_multisite_operation() {
    if (isset($_POST['create_site'])) {
        if (current_user_can('manage_sites')) {
            wpmu_create_blog(
                sanitize_text_field($_POST['domain']), 
                sanitize_text_field($_POST['path']), 
                sanitize_text_field($_POST['title'])
            );
        }
    }
}

// SAFE: Using constants for capabilities
function safe_capability_constants() {
    define('ADMIN_CAPABILITY', 'manage_options');
    define('DELETE_USERS_CAPABILITY', 'delete_users');
    
    if (current_user_can(ADMIN_CAPABILITY)) { // Using constant
        // Admin operation
    }
    if (current_user_can(DELETE_USERS_CAPABILITY)) { // Using constant
        // Delete operation
    }
}

// SAFE: Optimized multiple capability checks
function safe_optimized_checks() {
    if (current_user_can('edit_posts') || current_user_can('publish_posts')) { // Optimized with OR
        // Operation
    }
}

// SAFE: Proper capability for specific operation
function safe_proper_capability() {
    if (current_user_can('delete_users')) { // Proper capability for user deletion
        wp_delete_user($_POST['user_id']);
    }
    if (current_user_can('manage_options')) { // Proper capability for admin settings
        update_option('admin_setting', sanitize_text_field($_POST['value']));
    }
}

// SAFE: File upload with capability check
function safe_file_upload() {
    if (isset($_FILES['file'])) {
        if (current_user_can('upload_files')) {
            $upload_dir = wp_upload_dir();
            move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir['path'] . '/' . sanitize_file_name($_FILES['file']['name']));
        }
    }
}

// SAFE: Theme activation with capability check
function safe_theme_activation() {
    if (isset($_POST['activate_theme'])) {
        if (current_user_can('switch_themes')) {
            switch_theme(sanitize_text_field($_POST['theme_name']));
        }
    }
}

// SAFE: Plugin installation with capability check
function safe_plugin_installation() {
    if (isset($_POST['install_plugin'])) {
        if (current_user_can('install_plugins')) {
            // Plugin installation logic with capability check
            $plugin_file = sanitize_text_field($_POST['plugin_file']);
            // Install plugin...
        }
    }
}

// SAFE: User role change with capability check
function safe_user_role_change() {
    if (isset($_POST['change_role'])) {
        if (current_user_can('edit_users')) {
            $user = get_user_by('id', intval($_POST['user_id']));
            $user->set_role(sanitize_text_field($_POST['new_role']));
        }
    }
}

// SAFE: Site options with capability check
function safe_site_options() {
    if (isset($_POST['update_site_option'])) {
        if (current_user_can('manage_sites')) {
            update_site_option(
                sanitize_text_field($_POST['option_name']), 
                sanitize_text_field($_POST['option_value'])
            );
        }
    }
}

// SAFE: Network operations with capability check
function safe_network_operations() {
    if (isset($_POST['create_network_user'])) {
        if (current_user_can('create_users')) {
            wpmu_create_user(
                sanitize_user($_POST['username']), 
                $_POST['password'], 
                sanitize_email($_POST['email'])
            );
        }
    }
}

// SAFE: Combined capability and nonce check
function safe_combined_security() {
    if (current_user_can('manage_options')) {
        if (isset($_POST['submit'])) {
            if (wp_verify_nonce($_POST['_wpnonce'], 'update_admin_settings')) {
                update_option('admin_setting', sanitize_text_field($_POST['value']));
                wp_redirect(admin_url('admin.php?page=settings&updated=true'));
                exit;
            } else {
                wp_die('Security check failed');
            }
        }
    }
}

// SAFE: Capability check with proper error handling
function safe_capability_with_errors() {
    if (!current_user_can('delete_users')) {
        wp_die('You do not have sufficient permissions to delete users.');
    }
    
    if (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        if (wp_delete_user($user_id)) {
            wp_redirect(admin_url('users.php?deleted=1'));
            exit;
        } else {
            wp_die('Failed to delete user');
        }
    }
}

// SAFE: Multiple capability checks for different operations
function safe_multiple_operations() {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'delete_user':
            if (current_user_can('delete_users')) {
                wp_delete_user(intval($_POST['user_id']));
            }
            break;
        case 'update_settings':
            if (current_user_can('manage_options')) {
                update_option('setting', sanitize_text_field($_POST['value']));
            }
            break;
        case 'publish_post':
            if (current_user_can('publish_posts')) {
                wp_publish_post(intval($_POST['post_id']));
            }
            break;
        default:
            wp_die('Invalid action');
    }
}
