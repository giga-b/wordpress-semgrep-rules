# WordPress Nonce Verification Rules

## Overview

This document describes the comprehensive nonce verification rules developed for WordPress security scanning. These rules help identify common nonce-related security vulnerabilities and ensure proper implementation of WordPress nonce security patterns.

## What are Nonces?

Nonces (Number used ONCE) are security tokens used in WordPress to prevent Cross-Site Request Forgery (CSRF) attacks. They ensure that form submissions and AJAX requests come from legitimate sources.

## Rule Categories

### 1. Nonce Creation Rules

#### `wordpress.nonce.insecure-creation`
**Severity**: WARNING  
**CWE**: CWE-352 (Cross-Site Request Forgery)

**Description**: Detects nonce creation with predictable or empty action names.

**Vulnerable Examples**:
```php
// Empty action
$nonce = wp_create_nonce("");

// Generic action names
$nonce = wp_create_nonce("action");
$nonce = wp_create_nonce("nonce");
$nonce = wp_create_nonce("token");

// Variable action (unpredictable)
$nonce = wp_create_nonce($variable);
```

**Safe Examples**:
```php
// Specific, descriptive action names
$nonce = wp_create_nonce('delete_post_123');
$nonce = wp_create_nonce('update_user_profile');
$nonce = wp_create_nonce('save_plugin_settings');
```

### 2. Nonce Verification Rules

#### `wordpress.nonce.missing-verification`
**Severity**: ERROR  
**CWE**: CWE-352 (Cross-Site Request Forgery)

**Description**: Detects form processing without nonce verification.

**Vulnerable Examples**:
```php
if (isset($_POST['submit'])) {
    $data = $_POST['data'];
    // Process data without nonce verification
}
```

**Safe Examples**:
```php
if (isset($_POST['submit'])) {
    if (wp_verify_nonce($_POST['_wpnonce'], 'save_user_data_action')) {
        $data = sanitize_text_field($_POST['data']);
        // Process data safely
    } else {
        wp_die('Security check failed');
    }
}
```

#### `wordpress.nonce.weak-verification`
**Severity**: WARNING  
**CWE**: CWE-352 (Cross-Site Request Forgery)

**Description**: Detects nonce verification without checking the return value.

**Vulnerable Examples**:
```php
wp_verify_nonce($_POST['_wpnonce'], 'action_name');
$data = $_POST['data']; // Process data without checking return value
```

**Safe Examples**:
```php
if (wp_verify_nonce($_POST['_wpnonce'], 'action_name')) {
    $data = sanitize_text_field($_POST['data']);
    // Process data safely
} else {
    wp_die('Security check failed');
}
```

#### `wordpress.nonce.wrong-action`
**Severity**: ERROR  
**CWE**: CWE-352 (Cross-Site Request Forgery)

**Description**: Detects mismatched action names between nonce creation and verification.

**Vulnerable Examples**:
```php
wp_create_nonce('save_post_action');
// ... later in code ...
wp_verify_nonce($_POST['_wpnonce'], 'delete_post_action'); // Wrong action
```

**Safe Examples**:
```php
wp_create_nonce('save_post_action');
// ... later in code ...
if (wp_verify_nonce($_POST['_wpnonce'], 'save_post_action')) {
    // Process safely
}
```

### 3. AJAX Nonce Rules

#### `wordpress.nonce.ajax-missing`
**Severity**: ERROR  
**CWE**: CWE-352 (Cross-Site Request Forgery)

**Description**: Detects AJAX handlers without nonce verification.

**Vulnerable Examples**:
```php
add_action('wp_ajax_my_action', 'my_ajax_handler');
function my_ajax_handler() {
    $data = $_POST['data'];
    wp_send_json_success($data);
}
```

**Safe Examples**:
```php
add_action('wp_ajax_my_action', 'my_ajax_handler');
function my_ajax_handler() {
    check_ajax_referer('my_ajax_nonce_action', 'nonce');
    $data = sanitize_text_field($_POST['data']);
    wp_send_json_success($data);
}
```

#### `wordpress.nonce.ajax-weak`
**Severity**: WARNING  
**CWE**: CWE-352 (Cross-Site Request Forgery)

**Description**: Detects weak AJAX nonce verification.

**Vulnerable Examples**:
```php
check_ajax_referer(""); // Empty action
check_ajax_referer("action"); // Generic action
check_ajax_referer($variable); // Variable action
```

**Safe Examples**:
```php
check_ajax_referer('delete_user_action', 'nonce');
check_ajax_referer('update_settings_action', 'nonce');
```

### 4. REST API Nonce Rules

#### `wordpress.nonce.rest-missing`
**Severity**: ERROR  
**CWE**: CWE-352 (Cross-Site Request Forgery)

**Description**: Detects REST API endpoints without nonce verification.

**Vulnerable Examples**:
```php
register_rest_route('my-namespace/v1', '/endpoint', array(
    'methods' => 'POST',
    'callback' => 'my_rest_callback',
    'permission_callback' => '__return_true'
));
function my_rest_callback($request) {
    $data = $request->get_param('data');
    return new WP_REST_Response($data, 200);
}
```

**Safe Examples**:
```php
register_rest_route('my-namespace/v1', '/endpoint', array(
    'methods' => 'POST',
    'callback' => 'my_rest_callback',
    'permission_callback' => 'my_permission_callback'
));
function my_permission_callback($request) {
    return wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest');
}
function my_rest_callback($request) {
    $data = sanitize_text_field($request->get_param('data'));
    return new WP_REST_Response($data, 200);
}
```

### 5. Nonce Lifecycle Rules

#### `wordpress.nonce.expired-check`
**Severity**: WARNING  
**CWE**: CWE-352 (Cross-Site Request Forgery)

**Description**: Detects poor handling of nonce expiration.

**Vulnerable Examples**:
```php
if (!wp_verify_nonce($_POST['_wpnonce'], 'action_name')) {
    die('Invalid nonce'); // No distinction between expired and invalid
}
```

**Safe Examples**:
```php
$nonce_result = wp_verify_nonce($_POST['_wpnonce'], 'action_name');

if ($nonce_result === false) {
    wp_die('Nonce expired. Please try again.');
} elseif ($nonce_result === 0) {
    wp_die('Invalid nonce.');
} else {
    // Process data safely
    $data = sanitize_text_field($_POST['data']);
}
```

### 6. Security Best Practices

#### `wordpress.nonce.hardcoded-action`
**Severity**: INFO  
**CWE**: CWE-352 (Cross-Site Request Forgery)

**Description**: Detects hardcoded action names that should be more descriptive.

**Examples**:
```php
wp_create_nonce("action");
wp_verify_nonce($_POST['_wpnonce'], "action");
wp_nonce_field("action");
```

**Recommendation**: Use descriptive, unique action names:
```php
wp_create_nonce('delete_user_123_action');
wp_verify_nonce($_POST['_wpnonce'], 'delete_user_123_action');
wp_nonce_field('save_plugin_settings_action');
```

#### `wordpress.nonce.missing-nonce-field`
**Severity**: ERROR  
**CWE**: CWE-352 (Cross-Site Request Forgery)

**Description**: Detects form processing without nonce field verification.

**Vulnerable Examples**:
```php
if (isset($_POST['submit'])) {
    $data = $_POST['data'];
    // Process form data without nonce verification
}
```

**Safe Examples**:
```php
if (isset($_POST['submit'])) {
    if (wp_verify_nonce($_POST['_wpnonce'], 'action_name')) {
        $data = sanitize_text_field($_POST['data']);
        // Process data safely
    }
}
```

## Implementation Guidelines

### 1. Form Security
- Always use `wp_nonce_field()` in forms
- Verify nonces before processing form data
- Use descriptive action names

### 2. AJAX Security
- Use `check_ajax_referer()` in AJAX handlers
- Pass nonce via POST data or headers
- Use specific action names

### 3. REST API Security
- Implement proper permission callbacks
- Verify nonces in permission callbacks
- Use WordPress REST API nonce headers

### 4. Error Handling
- Distinguish between expired and invalid nonces
- Provide user-friendly error messages
- Log security violations

## Testing

The rules include comprehensive test cases:

- **Vulnerable Examples**: `tests/vulnerable-examples/nonce-vulnerable.php`
- **Safe Examples**: `tests/safe-examples/nonce-safe.php`

Run tests with:
```bash
semgrep scan --config=packs/wp-core-security/nonce-verification.yaml tests/
```

## References

- [WordPress Nonces Documentation](https://developer.wordpress.org/plugins/security/nonces/)
- [WordPress Codex: Nonces](https://codex.wordpress.org/WordPress_Nonces)
- [OWASP CSRF Prevention](https://owasp.org/www-community/attacks/csrf)
- [CWE-352: Cross-Site Request Forgery](https://cwe.mitre.org/data/definitions/352.html)

## Rule Configuration

These rules are included in the basic configuration:
```yaml
rules:
  - packs/wp-core-security/nonce-verification.yaml
```

For custom configurations, reference the rule IDs:
- `wordpress.nonce.insecure-creation`
- `wordpress.nonce.missing-verification`
- `wordpress.nonce.weak-verification`
- `wordpress.nonce.wrong-action`
- `wordpress.nonce.ajax-missing`
- `wordpress.nonce.ajax-weak`
- `wordpress.nonce.rest-missing`
- `wordpress.nonce.expired-check`
- `wordpress.nonce.hardcoded-action`
- `wordpress.nonce.missing-nonce-field`
