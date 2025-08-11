<?php
/**
 * Vulnerable Nonce Lifecycle Examples
 * These examples demonstrate common nonce lifecycle security vulnerabilities
 * for Task 1.5: Nonce Lifecycle Detection Rules
 */

// ============================================================================
// TASK 1.5.1: VULNERABLE NONCE CREATION EXAMPLES
// ============================================================================

// VULNERABLE: Weak nonce creation with generic action names
function vulnerable_weak_nonce_creation() {
    $nonce = wp_create_nonce(""); // Empty action
    $nonce2 = wp_create_nonce("action"); // Generic action
    $nonce3 = wp_create_nonce("nonce"); // Generic action
    $nonce4 = wp_create_nonce("token"); // Generic action
    $nonce5 = wp_create_nonce("form"); // Generic action
    $nonce6 = wp_create_nonce("submit"); // Generic action
}

// VULNERABLE: Nonce creation with variable action names
function vulnerable_variable_nonce_creation() {
    $action = $_GET['action']; // User-controlled variable
    $nonce = wp_create_nonce($action); // VULNERABLE: Variable action
    
    $action2 = $_POST['action_name']; // User-controlled variable
    $nonce2 = wp_create_nonce($action2); // VULNERABLE: Variable action
    
    $action3 = $variable; // Undefined variable
    $nonce3 = wp_create_nonce($action3); // VULNERABLE: Variable action
}

// VULNERABLE: Nonce field creation with weak actions
function vulnerable_weak_nonce_field_creation() {
    wp_nonce_field(""); // Empty action
    wp_nonce_field("action"); // Generic action
    wp_nonce_field("form"); // Generic action
}

// VULNERABLE: Nonce URL creation with weak actions
function vulnerable_weak_nonce_url_creation() {
    $url = wp_nonce_url('admin.php?page=settings', ''); // Empty action
    $url2 = wp_nonce_url('admin.php?action=delete', 'action'); // Generic action
    $url3 = wp_nonce_url('admin.php?action=export', 'form'); // Generic action
}

// VULNERABLE: Nonce AYS creation with weak actions
function vulnerable_weak_nonce_ays_creation() {
    wp_nonce_ays(""); // Empty action
    wp_nonce_ays("action"); // Generic action
    wp_nonce_ays("delete"); // Generic action
}

// ============================================================================
// TASK 1.5.2: VULNERABLE NONCE INCLUSION EXAMPLES
// ============================================================================

// VULNERABLE: Form without nonce field
function vulnerable_form_without_nonce() {
    ?>
    <form method="post" action="">
        <input type="text" name="user_name" />
        <input type="email" name="user_email" />
        <input type="submit" name="submit" value="Save" />
    </form>
    <?php
}

// VULNERABLE: Form with missing nonce field
function vulnerable_form_missing_nonce_field() {
    ?>
    <form method="post">
        <input type="text" name="data" />
        <input type="submit" value="Submit" />
    </form>
    <?php
}

// VULNERABLE: AJAX without nonce inclusion
function vulnerable_ajax_without_nonce() {
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
                    form_data: $('#my-form').serialize()
                    // Missing nonce
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

// VULNERABLE: REST API without nonce inclusion
function vulnerable_rest_api_without_nonce() {
    ?>
    <script>
    fetch('/wp-json/my-namespace/v1/endpoint', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
            // Missing X-WP-Nonce header
        },
        body: JSON.stringify({
            data: 'test data'
        })
    });
    </script>
    <?php
}

// VULNERABLE: Multiple nonce fields in same form
function vulnerable_multiple_nonce_fields() {
    ?>
    <form method="post">
        <?php wp_nonce_field('action1'); ?>
        <input type="text" name="data1" />
        <?php wp_nonce_field('action2'); ?>
        <input type="text" name="data2" />
        <input type="submit" value="Submit" />
    </form>
    <?php
}

// ============================================================================
// TASK 1.5.3: VULNERABLE NONCE VERIFICATION EXAMPLES
// ============================================================================

// VULNERABLE: Form processing without nonce verification
function vulnerable_form_processing_without_nonce() {
    if (isset($_POST['submit'])) {
        // Process form data without nonce verification
        $user_name = $_POST['user_name'];
        $user_email = $_POST['user_email'];
        update_option('user_data', array(
            'name' => $user_name,
            'email' => $user_email
        ));
        echo 'Data saved successfully!';
    }
}

// VULNERABLE: AJAX handler without nonce verification
function vulnerable_ajax_handler_without_nonce() {
    add_action('wp_ajax_my_ajax_action', 'my_vulnerable_ajax_handler');
}

function my_vulnerable_ajax_handler() {
    // Process AJAX request without nonce check
    $form_data = $_POST['form_data'];
    wp_send_json_success(array('message' => 'Data processed successfully'));
}

// VULNERABLE: Admin form processing without nonce verification
function vulnerable_admin_form_processing_without_nonce() {
    if (isset($_POST['admin_submit'])) {
        // Process admin form without nonce verification
        $setting_value = $_POST['setting_value'];
        update_option('admin_setting', $setting_value);
        echo 'Admin settings updated successfully!';
    }
}

// VULNERABLE: REST API endpoint without nonce verification
function vulnerable_rest_endpoint_without_nonce() {
    register_rest_route('my-namespace/v1', '/endpoint', array(
        'methods' => 'POST',
        'callback' => 'my_vulnerable_rest_callback',
        'permission_callback' => '__return_true' // Always allow
    ));
}

function my_vulnerable_rest_callback($request) {
    // Process request without nonce verification
    $data = $request->get_param('data');
    return new WP_REST_Response(array('message' => 'Data processed'), 200);
}

// VULNERABLE: Weak nonce verification - return value not checked
function vulnerable_weak_nonce_verification() {
    wp_verify_nonce($_POST['_wpnonce'], 'action_name');
    // Process data without checking return value
    $data = $_POST['data'];
    update_option('processed_data', $data);
}

// VULNERABLE: Wrong action name in verification
function vulnerable_wrong_action_verification() {
    wp_create_nonce('save_post_action');
    // ... later in code ...
    wp_verify_nonce($_POST['_wpnonce'], 'delete_post_action'); // Wrong action
}

// VULNERABLE: Weak AJAX nonce verification
function vulnerable_weak_ajax_nonce_verification() {
    check_ajax_referer(""); // Empty action
    check_ajax_referer("action"); // Generic action
    check_ajax_referer($variable); // Variable action
}

// VULNERABLE: Nonce verification with variable action
function vulnerable_variable_action_verification() {
    $action = $_GET['action']; // User-controlled variable
    wp_verify_nonce($_POST['_wpnonce'], $action); // VULNERABLE: Variable action
}

// ============================================================================
// TASK 1.5.4: VULNERABLE NONCE EXPIRATION HANDLING EXAMPLES
// ============================================================================

// VULNERABLE: No nonce expiration handling
function vulnerable_no_expiration_handling() {
    wp_verify_nonce($_POST['_wpnonce'], 'action_name');
    // Process data without handling verification result
    $data = $_POST['data'];
    update_option('processed_data', $data);
}

// VULNERABLE: Poor nonce expiration handling with generic error
function vulnerable_poor_expiration_handling() {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'action_name')) {
        die('Invalid nonce'); // Generic error message
    }
    // Process data
    $data = $_POST['data'];
    update_option('processed_data', $data);
}

// VULNERABLE: Nonce expiration handling without proper error messages
function vulnerable_basic_expiration_handling() {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'action_name')) {
        echo 'Error'; // Too generic
    } else {
        // Process data
        $data = $_POST['data'];
        update_option('processed_data', $data);
    }
}

// VULNERABLE: AJAX nonce expiration handling without proper error handling
function vulnerable_ajax_expiration_handling() {
    add_action('wp_ajax_my_action', 'my_vulnerable_ajax_expiration_handler');
}

function my_vulnerable_ajax_expiration_handler() {
    check_ajax_referer('my_action', 'nonce');
    // Process AJAX request without handling verification result
    $data = $_POST['data'];
    wp_send_json_success(array('message' => 'Data processed successfully'));
}

// VULNERABLE: Nonce expiration handling with information disclosure
function vulnerable_info_disclosure_expiration_handling() {
    $result = wp_verify_nonce($_POST['_wpnonce'], 'action_name');
    
    if ($result === false) {
        echo 'Nonce expired at: ' . date('Y-m-d H:i:s'); // Information disclosure
    } elseif ($result === 0) {
        echo 'Invalid nonce hash: ' . $_POST['_wpnonce']; // Information disclosure
    } else {
        // Process data
        $data = $_POST['data'];
        update_option('processed_data', $data);
    }
}

// ============================================================================
// VULNERABLE CROSS-FILE NONCE LIFECYCLE EXAMPLES
// ============================================================================

// VULNERABLE: Nonce created but never verified
function vulnerable_nonce_created_never_verified() {
    // In form file
    wp_nonce_field('save_user_data_action');
    
    // In processing file - no verification
    if (isset($_POST['submit'])) {
        $data = $_POST['data'];
        update_option('user_data', $data);
    }
}

// VULNERABLE: Nonce verified but never created
function vulnerable_nonce_verified_never_created() {
    // In form file - no nonce field
    ?>
    <form method="post">
        <input type="text" name="data" />
        <input type="submit" value="Submit" />
    </form>
    <?php
    
    // In processing file - verification without creation
    if (wp_verify_nonce($_POST['_wpnonce'], 'save_user_data_action')) {
        $data = $_POST['data'];
        update_option('user_data', $data);
    }
}

// VULNERABLE: Mismatched nonce actions across files
function vulnerable_mismatched_nonce_actions() {
    // In form file
    wp_nonce_field('save_user_data_action');
    
    // In processing file - different action
    if (wp_verify_nonce($_POST['_wpnonce'], 'delete_user_data_action')) {
        $data = $_POST['data'];
        update_option('user_data', $data);
    }
}

// VULNERABLE: AJAX nonce lifecycle mismatch
function vulnerable_ajax_nonce_lifecycle_mismatch() {
    // In JavaScript file
    $nonce = wp_create_nonce('ajax_action');
    
    // In AJAX handler file - different action
    check_ajax_referer('different_ajax_action', 'nonce');
}

// ============================================================================
// VULNERABLE NONCE LIFECYCLE PATTERNS
// ============================================================================

// VULNERABLE: Nonce with predictable action names
function vulnerable_predictable_nonce_actions() {
    $user_id = get_current_user_id();
    $nonce = wp_create_nonce('user_action'); // Predictable action
    
    if (wp_verify_nonce($_POST['_wpnonce'], 'user_action')) {
        // Process action
    }
}

// VULNERABLE: Nonce with time-based predictable actions
function vulnerable_time_based_predictable_nonce() {
    $timestamp = time();
    $nonce = wp_create_nonce('action_' . $timestamp); // Predictable pattern
    
    if (wp_verify_nonce($_POST['_wpnonce'], 'action_' . $timestamp)) {
        // Process action
    }
}

// VULNERABLE: Nonce with insufficient entropy
function vulnerable_insufficient_entropy_nonce() {
    $nonce = wp_create_nonce('action'); // Generic action with low entropy
    
    if (wp_verify_nonce($_POST['_wpnonce'], 'action')) {
        // Process action
    }
}

// VULNERABLE: Nonce with user-controlled actions
function vulnerable_user_controlled_nonce_actions() {
    $action = $_GET['action']; // User-controlled
    $nonce = wp_create_nonce($action);
    
    if (wp_verify_nonce($_POST['_wpnonce'], $action)) {
        // Process action based on user input
        $action_type = $_GET['action'];
        if ($action_type === 'delete') {
            delete_user($_POST['user_id']);
        }
    }
}

// VULNERABLE: Nonce bypass attempts
function vulnerable_nonce_bypass_attempts() {
    // Attempt to bypass nonce verification
    if (isset($_POST['_wpnonce']) || isset($_GET['_wpnonce'])) {
        // Process without proper verification
        $data = $_POST['data'];
        update_option('bypassed_data', $data);
    }
}

// VULNERABLE: Nonce with weak validation
function vulnerable_weak_nonce_validation() {
    $nonce = $_POST['_wpnonce'];
    if (!empty($nonce)) {
        // Weak validation - just check if nonce exists
        $data = $_POST['data'];
        update_option('weakly_validated_data', $data);
    }
}

// VULNERABLE: Nonce with race conditions
function vulnerable_nonce_race_condition() {
    $nonce = wp_create_nonce('action');
    
    // Simulate race condition - nonce used multiple times
    if (wp_verify_nonce($_POST['_wpnonce'], 'action')) {
        // Process action
    }
    
    // Same nonce used again (race condition)
    if (wp_verify_nonce($_POST['_wpnonce'], 'action')) {
        // Process action again
    }
}

// VULNERABLE: Nonce with session fixation
function vulnerable_nonce_session_fixation() {
    // Nonce tied to session that can be fixed
    $session_id = $_COOKIE['PHPSESSID'];
    $nonce = wp_create_nonce('session_' . $session_id);
    
    if (wp_verify_nonce($_POST['_wpnonce'], 'session_' . $session_id)) {
        // Process action
    }
}
