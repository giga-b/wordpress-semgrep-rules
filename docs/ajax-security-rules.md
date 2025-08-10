# AJAX Security Rules Documentation

## Overview

This document provides comprehensive guidance on WordPress AJAX security rules designed to detect common vulnerabilities in AJAX handlers. These rules help ensure that AJAX endpoints are properly secured against CSRF attacks, unauthorized access, XSS vulnerabilities, and other security threats.

## Rule Categories

### 1. Authentication & Authorization
- Nonce verification for CSRF protection
- Capability checks for authorization
- User authentication validation

### 2. Input Validation & Sanitization
- Input sanitization patterns
- Data validation requirements
- SQL injection prevention

### 3. Output Security
- XSS prevention through proper escaping
- Safe JSON response handling
- Secure error handling

### 4. File Operations
- Secure file upload handling
- Path traversal prevention
- Content-type validation

### 5. Advanced Security
- Rate limiting implementation
- Sensitive data protection
- Direct object reference security

## Detailed Rule Descriptions

### 1. Missing Nonce Verification (`wp-ajax-missing-nonce-verification`)

**Severity**: ERROR  
**CWE**: CWE-352 (Cross-Site Request Forgery)

**Description**: Detects AJAX handlers that lack nonce verification, making them vulnerable to CSRF attacks.

**Vulnerable Pattern**:
```php
add_action('wp_ajax_my_action', 'my_ajax_handler');
function my_ajax_handler() {
    $data = $_POST['data'];
    // Process data without nonce verification
}
```

**Secure Pattern**:
```php
add_action('wp_ajax_my_action', 'my_ajax_handler');
function my_ajax_handler() {
    check_ajax_referer('my_action_nonce', 'security');
    $data = sanitize_text_field($_POST['data']);
    // Process data securely
}
```

**Remediation**:
1. Add `check_ajax_referer()` call at the beginning of the handler
2. Use a specific nonce action name
3. Verify the nonce field name matches your form

### 2. Missing Capability Check (`wp-ajax-missing-capability-check`)

**Severity**: ERROR  
**CWE**: CWE-285 (Improper Authorization)

**Description**: Identifies AJAX handlers that don't verify user capabilities, potentially allowing unauthorized access.

**Vulnerable Pattern**:
```php
add_action('wp_ajax_delete_user', 'delete_user_ajax');
function delete_user_ajax() {
    $user_id = $_POST['user_id'];
    wp_delete_user($user_id); // No capability check
}
```

**Secure Pattern**:
```php
add_action('wp_ajax_delete_user', 'delete_user_ajax');
function delete_user_ajax() {
    if (!current_user_can('delete_users')) {
        wp_die('Unauthorized');
    }
    $user_id = intval($_POST['user_id']);
    wp_delete_user($user_id);
}
```

**Remediation**:
1. Add capability check using `current_user_can()`
2. Use appropriate WordPress capabilities
3. Handle unauthorized access gracefully

### 3. Direct Output XSS (`wp-ajax-direct-output-xss`)

**Severity**: ERROR  
**CWE**: CWE-79 (Cross-Site Scripting)

**Description**: Detects direct output of user data without proper escaping, creating XSS vulnerabilities.

**Vulnerable Pattern**:
```php
add_action('wp_ajax_echo_data', 'echo_ajax_data');
function echo_ajax_data() {
    $data = $_POST['data'];
    echo $data; // XSS vulnerability
}
```

**Secure Pattern**:
```php
add_action('wp_ajax_echo_data', 'echo_ajax_data');
function echo_ajax_data() {
    $data = sanitize_text_field($_POST['data']);
    echo esc_html($data); // Safe output
}
```

**Remediation**:
1. Always sanitize input data
2. Use appropriate escaping functions (`esc_html`, `esc_attr`, etc.)
3. Consider context when choosing escape function

### 4. Weak Nonce Verification (`wp-ajax-weak-nonce-verification`)

**Severity**: WARNING  
**CWE**: CWE-352 (Cross-Site Request Forgery)

**Description**: Identifies nonce verification using generic action names instead of specific ones.

**Vulnerable Pattern**:
```php
check_ajax_referer('nonce', 'security'); // Generic action
```

**Secure Pattern**:
```php
check_ajax_referer('my_specific_action', 'security'); // Specific action
```

**Remediation**:
1. Use specific, descriptive nonce action names
2. Avoid generic names like 'nonce' or 'security'
3. Make action names unique to your functionality

### 5. Missing Input Sanitization (`wp-ajax-missing-input-sanitization`)

**Severity**: ERROR  
**CWE**: CWE-20 (Improper Input Validation)

**Description**: Detects AJAX handlers that process user input without proper sanitization.

**Vulnerable Pattern**:
```php
add_action('wp_ajax_process_data', 'process_ajax_data');
function process_ajax_data() {
    $data = $_POST['data'];
    $result = some_function($data); // No sanitization
}
```

**Secure Pattern**:
```php
add_action('wp_ajax_process_data', 'process_ajax_data');
function process_ajax_data() {
    $data = sanitize_text_field($_POST['data']);
    $result = some_function($data); // Sanitized input
}
```

**Remediation**:
1. Use appropriate WordPress sanitization functions
2. Choose sanitization based on expected data type
3. Validate data after sanitization if needed

### 6. SQL Injection Risk (`wp-ajax-sql-injection-risk`)

**Severity**: ERROR  
**CWE**: CWE-89 (SQL Injection)

**Description**: Identifies potential SQL injection vulnerabilities in AJAX handlers.

**Vulnerable Pattern**:
```php
add_action('wp_ajax_search_posts', 'search_ajax_posts');
function search_ajax_posts() {
    global $wpdb;
    $search = $_POST['search'];
    $results = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_title LIKE '%$search%'");
}
```

**Secure Pattern**:
```php
add_action('wp_ajax_search_posts', 'search_ajax_posts');
function search_ajax_posts() {
    global $wpdb;
    $search = sanitize_text_field($_POST['search']);
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $wpdb->posts WHERE post_title LIKE %s",
        '%' . $wpdb->esc_like($search) . '%'
    ));
}
```

**Remediation**:
1. Always use prepared statements with `$wpdb->prepare()`
2. Sanitize input before database operations
3. Use `$wpdb->esc_like()` for LIKE queries

### 7. Unsafe File Upload (`wp-ajax-unsafe-file-upload`)

**Severity**: ERROR  
**CWE**: CWE-434 (Unrestricted Upload of File with Dangerous Type)

**Description**: Detects unsafe file upload handling in AJAX handlers.

**Vulnerable Pattern**:
```php
add_action('wp_ajax_upload_file', 'upload_ajax_file');
function upload_ajax_file() {
    $file = $_FILES['file'];
    move_uploaded_file($file['tmp_name'], $file['name']);
}
```

**Secure Pattern**:
```php
add_action('wp_ajax_upload_file', 'upload_ajax_file');
function upload_ajax_file() {
    $file = $_FILES['file'];
    $upload = wp_handle_upload($file, array('test_form' => false));
    if ($upload['error']) {
        wp_send_json_error($upload['error']);
    }
}
```

**Remediation**:
1. Use `wp_handle_upload()` for file uploads
2. Validate file types and sizes
3. Check upload permissions

### 8. Sensitive Data Exposure (`wp-ajax-sensitive-data-exposure`)

**Severity**: ERROR  
**CWE**: CWE-200 (Information Exposure)

**Description**: Identifies AJAX handlers that expose sensitive data without proper authorization.

**Vulnerable Pattern**:
```php
add_action('wp_ajax_get_user_data', 'get_ajax_user_data');
function get_ajax_user_data() {
    $user = get_user_by('id', $_POST['user_id']);
    echo json_encode(array(
        'password' => $user->user_pass, // Sensitive data
        'email' => $user->user_email
    ));
}
```

**Secure Pattern**:
```php
add_action('wp_ajax_get_user_data', 'get_ajax_user_data');
function get_ajax_user_data() {
    if (!current_user_can('manage_users')) {
        wp_die('Unauthorized');
    }
    $user = get_user_by('id', intval($_POST['user_id']));
    echo json_encode(array(
        'id' => $user->ID,
        'email' => $user->user_email // Only necessary data
    ));
}
```

**Remediation**:
1. Verify user permissions before exposing data
2. Only return necessary information
3. Never expose passwords or sensitive fields

### 9. Missing Error Handling (`wp-ajax-missing-error-handling`)

**Severity**: WARNING  
**CWE**: CWE-209 (Information Exposure Through an Error Message)

**Description**: Detects AJAX handlers that lack proper error handling.

**Vulnerable Pattern**:
```php
add_action('wp_ajax_expensive_operation', 'expensive_ajax_operation');
function expensive_ajax_operation() {
    $result = some_expensive_operation($_POST['data']);
    echo $result; // No error handling
}
```

**Secure Pattern**:
```php
add_action('wp_ajax_expensive_operation', 'expensive_ajax_operation');
function expensive_ajax_operation() {
    try {
        $result = some_expensive_operation($_POST['data']);
        wp_send_json_success($result);
    } catch (Exception $e) {
        wp_send_json_error('Operation failed');
    }
}
```

**Remediation**:
1. Use try-catch blocks for error handling
2. Use `wp_send_json_success()` and `wp_send_json_error()`
3. Don't expose internal error details

### 10. Insecure Direct Object Reference (`wp-ajax-insecure-direct-object-reference`)

**Severity**: ERROR  
**CWE**: CWE-639 (Authorization Bypass Through User-Controlled Key)

**Description**: Identifies AJAX handlers that don't validate object ownership.

**Vulnerable Pattern**:
```php
add_action('wp_ajax_get_post', 'get_ajax_post');
function get_ajax_post() {
    $post_id = $_POST['post_id'];
    $post = get_post($post_id);
    echo json_encode($post); // No ownership validation
}
```

**Secure Pattern**:
```php
add_action('wp_ajax_get_post', 'get_ajax_post');
function get_ajax_post() {
    $post_id = intval($_POST['post_id']);
    $post = get_post($post_id);
    if (!$post || $post->post_author != get_current_user_id()) {
        wp_die('Unauthorized');
    }
    echo json_encode($post);
}
```

**Remediation**:
1. Validate object existence
2. Check ownership or permissions
3. Use appropriate capability checks

### 11. Missing Rate Limiting (`wp-ajax-missing-rate-limiting`)

**Severity**: WARNING  
**CWE**: CWE-770 (Allocation of Resources Without Limits or Throttling)

**Description**: Detects AJAX handlers that lack rate limiting for expensive operations.

**Vulnerable Pattern**:
```php
add_action('wp_ajax_heavy_operation', 'heavy_ajax_operation');
function heavy_ajax_operation() {
    $result = very_expensive_operation($_POST['data']);
    echo json_encode($result); // No rate limiting
}
```

**Secure Pattern**:
```php
add_action('wp_ajax_heavy_operation', 'heavy_ajax_operation');
function heavy_ajax_operation() {
    $user_id = get_current_user_id();
    $rate_key = "rate_limit_heavy_operation_$user_id";
    if (get_transient($rate_key)) {
        wp_die('Rate limit exceeded');
    }
    set_transient($rate_key, true, 60);
    $result = very_expensive_operation($_POST['data']);
    echo json_encode($result);
}
```

**Remediation**:
1. Implement rate limiting using transients
2. Set appropriate time limits
3. Handle rate limit exceeded gracefully

### 12. Unsafe JSON Response (`wp-ajax-unsafe-json-response`)

**Severity**: WARNING  
**CWE**: CWE-79 (Cross-Site Scripting)

**Description**: Detects unsafe JSON response handling in AJAX handlers.

**Vulnerable Pattern**:
```php
add_action('wp_ajax_unsafe_json', 'unsafe_json_handler');
function unsafe_json_handler() {
    $result = array('data' => $_POST['data']);
    echo json_encode($result);
    wp_die();
}
```

**Secure Pattern**:
```php
add_action('wp_ajax_unsafe_json', 'unsafe_json_handler');
function unsafe_json_handler() {
    $result = array('data' => sanitize_text_field($_POST['data']));
    wp_send_json_success($result);
}
```

**Remediation**:
1. Use `wp_send_json_success()` and `wp_send_json_error()`
2. Sanitize data before JSON encoding
3. Avoid manual `wp_die()` calls

### 13. Missing CSRF Protection for Non-Logged Users (`wp-ajax-missing-csrf-protection`)

**Severity**: ERROR  
**CWE**: CWE-352 (Cross-Site Request Forgery)

**Description**: Detects AJAX handlers for non-logged users that lack CSRF protection.

**Vulnerable Pattern**:
```php
add_action('wp_ajax_nopriv_public_action', 'public_ajax_handler');
function public_ajax_handler() {
    $data = $_POST['data'];
    process_public_data($data); // No CSRF protection
}
```

**Secure Pattern**:
```php
add_action('wp_ajax_nopriv_public_action', 'public_ajax_handler');
function public_ajax_handler() {
    check_ajax_referer('public_action_nonce', 'security');
    $data = sanitize_text_field($_POST['data']);
    process_public_data($data);
}
```

**Remediation**:
1. Always use nonce verification for public endpoints
2. Use specific nonce action names
3. Sanitize all input data

### 14. Unsafe Redirect (`wp-ajax-unsafe-redirect`)

**Severity**: ERROR  
**CWE**: CWE-601 (URL Redirection to Untrusted Site)

**Description**: Detects unsafe redirect handling in AJAX handlers.

**Vulnerable Pattern**:
```php
add_action('wp_ajax_redirect', 'redirect_ajax_handler');
function redirect_ajax_handler() {
    $url = $_POST['redirect_url'];
    wp_redirect($url); // Open redirect vulnerability
    exit;
}
```

**Secure Pattern**:
```php
add_action('wp_ajax_redirect', 'redirect_ajax_handler');
function redirect_ajax_handler() {
    $url = esc_url_raw($_POST['redirect_url']);
    if (wp_http_validate_url($url)) {
        wp_redirect($url);
        exit;
    }
}
```

**Remediation**:
1. Validate URLs before redirecting
2. Use `wp_http_validate_url()` for validation
3. Escape URLs with `esc_url_raw()`

### 15. Missing Content-Type Validation (`wp-ajax-missing-content-type-validation`)

**Severity**: WARNING  
**CWE**: CWE-434 (Unrestricted Upload of File with Dangerous Type)

**Description**: Detects file upload handlers that lack content-type validation.

**Vulnerable Pattern**:
```php
add_action('wp_ajax_upload_image', 'upload_ajax_image');
function upload_ajax_image() {
    $file = $_FILES['image'];
    $upload = wp_handle_upload($file, array('test_form' => false));
}
```

**Secure Pattern**:
```php
add_action('wp_ajax_upload_image', 'upload_ajax_image');
function upload_ajax_image() {
    $file = $_FILES['image'];
    $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
    if (!in_array($file['type'], $allowed_types)) {
        wp_die('Invalid file type');
    }
    $upload = wp_handle_upload($file, array('test_form' => false));
}
```

**Remediation**:
1. Validate file content types
2. Use whitelist approach for allowed types
3. Combine with file extension validation

## Best Practices

### 1. Security Checklist
- [ ] Always verify nonces for CSRF protection
- [ ] Check user capabilities before sensitive operations
- [ ] Sanitize all input data
- [ ] Escape all output data
- [ ] Use prepared statements for database queries
- [ ] Implement proper error handling
- [ ] Validate file uploads thoroughly
- [ ] Add rate limiting for expensive operations
- [ ] Use WordPress security functions
- [ ] Test thoroughly with various inputs

### 2. Implementation Guidelines
1. **Start with Security**: Begin every AJAX handler with nonce verification and capability checks
2. **Sanitize Input**: Always sanitize user input before processing
3. **Escape Output**: Escape all data before outputting to prevent XSS
4. **Use WordPress Functions**: Leverage WordPress security functions instead of custom implementations
5. **Handle Errors Gracefully**: Implement proper error handling without exposing sensitive information
6. **Test Extensively**: Test with various input types and edge cases

### 3. Common Pitfalls to Avoid
- Using generic nonce action names
- Skipping capability checks for "simple" operations
- Direct output of user data
- Manual SQL query construction
- Insufficient file upload validation
- Exposing sensitive data in responses
- Missing error handling
- No rate limiting for expensive operations

## Testing

### Running Tests
```bash
# Test vulnerable examples
semgrep --config packs/wp-core-security/ajax-security.yaml tests/vulnerable-examples/ajax-vulnerable.php

# Test safe examples (should not trigger rules)
semgrep --config packs/wp-core-security/ajax-security.yaml tests/safe-examples/ajax-safe.php
```

### Expected Results
- Vulnerable examples should trigger appropriate rules
- Safe examples should not trigger false positives
- Rules should provide clear, actionable messages

## References

- [WordPress AJAX Documentation](https://codex.wordpress.org/AJAX_in_Plugins)
- [WordPress Security Best Practices](https://developer.wordpress.org/plugins/security/)
- [OWASP AJAX Security Guidelines](https://owasp.org/www-project-ajax-security-guidelines/)
- [WordPress Nonce Documentation](https://developer.wordpress.org/plugins/security/nonces/)
- [WordPress Capability System](https://developer.wordpress.org/plugins/security/checking-user-capabilities/)
