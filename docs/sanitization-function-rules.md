# WordPress Sanitization Function Rules

This document provides comprehensive guidance on the WordPress sanitization function rules included in the `packs/wp-core-security/sanitization-functions.yaml` file.

## Overview

The sanitization function rules detect common security vulnerabilities related to improper data sanitization in WordPress applications. These rules help ensure that user input is properly sanitized before being used in database operations, output, or other sensitive operations.

## Rule Categories

### 1. Missing Input Sanitization

#### `wordpress.sanitization.missing-input`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects when user input is used directly without sanitization.

**Vulnerable Example**:
```php
$data = $_POST['user_input'];
echo $data; // VULNERABLE: Direct output without sanitization
```

**Safe Example**:
```php
$data = sanitize_text_field($_POST['user_input']);
echo esc_html($data); // SAFE: Properly sanitized and escaped
```

**Remediation**: Always use appropriate WordPress sanitization functions before using user data:
- `sanitize_text_field()` for text input
- `wp_kses_post()` for HTML content
- `esc_html()` for output escaping

#### `wordpress.sanitization.missing-get`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects when GET parameters are used without sanitization.

**Vulnerable Example**:
```php
$param = $_GET['parameter'];
$wpdb->query("SELECT * FROM table WHERE id = '$param'"); // VULNERABLE: SQL injection
```

**Safe Example**:
```php
$param = sanitize_text_field($_GET['parameter']);
$wpdb->prepare("SELECT * FROM table WHERE id = %s", $param); // SAFE: Prepared statement
```

**Remediation**: Use `sanitize_text_field()` for GET parameters and prepared statements for database queries.

#### `wordpress.sanitization.missing-request`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects when REQUEST data is used without sanitization.

**Vulnerable Example**:
```php
$data = $_REQUEST['input'];
update_option('setting', $data); // VULNERABLE: Unsanitized data saved to options
```

**Safe Example**:
```php
$data = sanitize_text_field($_REQUEST['input']);
update_option('setting', $data); // SAFE: Sanitized before saving
```

**Remediation**: Always sanitize REQUEST data before use, especially when saving to options or database.

### 2. Database Operations Without Sanitization

#### `wordpress.sanitization.unsafe-db-query`
**Severity**: ERROR  
**CWE**: CWE-89 (SQL Injection)

**Description**: Detects unsafe database queries with unsanitized input.

**Vulnerable Example**:
```php
$user_input = $_POST['search'];
$wpdb->query("SELECT * FROM posts WHERE title LIKE '%$user_input%'"); // VULNERABLE: SQL injection
```

**Safe Example**:
```php
$user_input = sanitize_text_field($_POST['search']);
$wpdb->prepare("SELECT * FROM posts WHERE title LIKE %s", '%' . $wpdb->esc_like($user_input) . '%'); // SAFE: Prepared with esc_like
```

**Remediation**: Use `$wpdb->prepare()` for all database queries and `$wpdb->esc_like()` for LIKE clauses.

#### `wordpress.sanitization.unsafe-insert`
**Severity**: ERROR  
**CWE**: CWE-89 (SQL Injection)

**Description**: Detects unsafe database inserts with unsanitized data.

**Vulnerable Example**:
```php
$title = $_POST['title'];
$wpdb->insert('posts', array('title' => $title)); // VULNERABLE: Unsanitized insert
```

**Safe Example**:
```php
$title = sanitize_text_field($_POST['title']);
$wpdb->insert('posts', array('title' => $title)); // SAFE: Sanitized before insert
```

**Remediation**: Sanitize all data before database inserts using appropriate WordPress functions.

### 3. Output Without Escaping

#### `wordpress.sanitization.unsafe-output`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects unsafe output without proper escaping.

**Vulnerable Example**:
```php
$user_data = $_POST['content'];
echo $user_data; // VULNERABLE: XSS vulnerability
```

**Safe Example**:
```php
$user_data = wp_kses_post($_POST['content']);
echo $user_data; // SAFE: HTML content properly sanitized
```

**Remediation**: Use appropriate escaping functions:
- `esc_html()` for plain text
- `wp_kses_post()` for HTML content
- `esc_attr()` for HTML attributes

#### `wordpress.sanitization.unsafe-attribute`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects unsafe attribute output without escaping.

**Vulnerable Example**:
```php
$value = $_GET['param'];
echo "<input value='$value'>"; // VULNERABLE: XSS in attribute
```

**Safe Example**:
```php
$value = esc_attr($_GET['param']);
echo "<input value='$value'>"; // SAFE: Attribute properly escaped
```

**Remediation**: Always use `esc_attr()` for HTML attribute values.

### 4. File Operations Without Sanitization

#### `wordpress.sanitization.unsafe-file-path`
**Severity**: ERROR  
**CWE**: CWE-22 (Path Traversal)

**Description**: Detects unsafe file path usage without sanitization.

**Vulnerable Example**:
```php
$filename = $_POST['filename'];
$file = fopen($filename, 'r'); // VULNERABLE: Path traversal
```

**Safe Example**:
```php
$filename = sanitize_file_name($_POST['filename']);
$file = fopen($filename, 'r'); // SAFE: Filename sanitized
```

**Remediation**: Use `sanitize_file_name()` for file operations and validate file paths.

#### `wordpress.sanitization.unsafe-include`
**Severity**: ERROR  
**CWE**: CWE-98 (PHP Object Injection)

**Description**: Detects unsafe include/require with user input.

**Vulnerable Example**:
```php
$page = $_GET['page'];
include($page . '.php'); // VULNERABLE: Remote file inclusion
```

**Safe Example**:
```php
$page = sanitize_text_field($_GET['page']);
$allowed_pages = array('allowed1', 'allowed2');
if (in_array($page, $allowed_pages)) {
    include($page . '.php'); // SAFE: Validated against whitelist
}
```

**Remediation**: Validate file paths against a whitelist before including files.

### 5. URL Operations Without Sanitization

#### `wordpress.sanitization.unsafe-url`
**Severity**: ERROR  
**CWE**: CWE-601 (Open Redirect)

**Description**: Detects unsafe URL usage without validation.

**Vulnerable Example**:
```php
$url = $_POST['redirect_url'];
wp_redirect($url); // VULNERABLE: Open redirect
```

**Safe Example**:
```php
$url = esc_url_raw($_POST['redirect_url']);
if (wp_http_validate_url($url)) {
    wp_redirect($url); // SAFE: URL validated and escaped
}
```

**Remediation**: Use `esc_url_raw()` and `wp_http_validate_url()` for URL operations.

#### `wordpress.sanitization.unsafe-link`
**Severity**: ERROR  
**CWE**: CWE-601 (Open Redirect)

**Description**: Detects unsafe link output without escaping.

**Vulnerable Example**:
```php
$link = $_GET['link'];
echo "<a href='$link'>Click here</a>"; // VULNERABLE: XSS in href
```

**Safe Example**:
```php
$link = esc_url($_GET['link']);
echo "<a href='$link'>Click here</a>"; // SAFE: URL properly escaped
```

**Remediation**: Use `esc_url()` for link href attributes.

### 6. Email Operations Without Sanitization

#### `wordpress.sanitization.unsafe-email`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects unsafe email usage without sanitization.

**Vulnerable Example**:
```php
$email = $_POST['email'];
wp_mail($email, 'Subject', 'Message'); // VULNERABLE: Email injection
```

**Safe Example**:
```php
$email = sanitize_email($_POST['email']);
if (is_email($email)) {
    wp_mail($email, 'Subject', 'Message'); // SAFE: Email validated and sanitized
}
```

**Remediation**: Use `sanitize_email()` and `is_email()` for email operations.

### 7. JSON Operations Without Sanitization

#### `wordpress.sanitization.unsafe-json`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects unsafe JSON output without sanitization.

**Vulnerable Example**:
```php
$data = $_POST['json_data'];
echo json_encode($data); // VULNERABLE: JSON injection
```

**Safe Example**:
```php
$data = sanitize_text_field($_POST['json_data']);
echo wp_json_encode($data); // SAFE: Data sanitized before JSON encoding
```

**Remediation**: Sanitize data before JSON encoding and use `wp_json_encode()`.

### 8. Improper Sanitization Usage

#### `wordpress.sanitization.wrong-function`
**Severity**: WARNING  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects wrong sanitization function for the context.

**Vulnerable Example**:
```php
$html_content = $_POST['content'];
$sanitized = sanitize_text_field($html_content); // VULNERABLE: Strips HTML when it should be preserved
echo $sanitized;
```

**Safe Example**:
```php
$html_content = $_POST['content'];
$sanitized = wp_kses_post($html_content); // SAFE: HTML content preserved with kses
echo $sanitized;
```

**Remediation**: Choose the appropriate sanitization function for your data type and usage context.

#### `wordpress.sanitization.double-sanitization`
**Severity**: WARNING  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects unnecessary double sanitization.

**Vulnerable Example**:
```php
$data = sanitize_text_field($_POST['input']);
$data = sanitize_text_field($data); // VULNERABLE: Unnecessary double sanitization
```

**Safe Example**:
```php
$data = sanitize_text_field($_POST['input']); // SAFE: Single sanitization
return $data;
```

**Remediation**: Avoid sanitizing already sanitized data.

### 9. Missing Validation

#### `wordpress.sanitization.missing-validation`
**Severity**: WARNING  
**CWE**: CWE-20 (Improper Input Validation)

**Description**: Detects sanitization without validation.

**Vulnerable Example**:
```php
$email = sanitize_email($_POST['email']);
wp_mail($email, 'Subject', 'Message'); // VULNERABLE: No email validation
```

**Safe Example**:
```php
$email = sanitize_email($_POST['email']);
if (is_email($email)) {
    wp_mail($email, 'Subject', 'Message'); // SAFE: Email validated after sanitization
}
```

**Remediation**: Validate data after sanitization for better security.

### 10. AJAX and REST API Sanitization

#### `wordpress.sanitization.ajax-missing`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects AJAX handlers missing sanitization.

**Vulnerable Example**:
```php
add_action('wp_ajax_my_action', 'my_ajax_handler');
function my_ajax_handler() {
    $data = $_POST['data'];
    echo $data; // VULNERABLE: AJAX without sanitization
}
```

**Safe Example**:
```php
add_action('wp_ajax_my_action', 'my_ajax_handler');
function my_ajax_handler() {
    $data = sanitize_text_field($_POST['data']);
    echo esc_html($data); // SAFE: AJAX data properly sanitized and escaped
}
```

**Remediation**: Always sanitize AJAX input data and escape output.

#### `wordpress.sanitization.rest-missing`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects REST API endpoints missing sanitization.

**Vulnerable Example**:
```php
register_rest_route('myplugin/v1', '/data', array(
    'callback' => 'my_rest_callback',
    'methods' => 'POST'
));
function my_rest_callback($request) {
    $data = $request->get_param('data');
    return $data; // VULNERABLE: REST API without sanitization
}
```

**Safe Example**:
```php
register_rest_route('myplugin/v1', '/data', array(
    'callback' => 'my_rest_callback',
    'methods' => 'POST'
));
function my_rest_callback($request) {
    $data = sanitize_text_field($request->get_param('data'));
    return $data; // SAFE: REST API data properly sanitized
}
```

**Remediation**: Sanitize all input parameters in REST API callbacks.

### 11. WordPress-Specific Operations

#### `wordpress.sanitization.options-missing`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects options updates without sanitization.

**Vulnerable Example**:
```php
if (isset($_POST['save_settings'])) {
    update_option('my_setting', $_POST['setting_value']); // VULNERABLE: Unsanitized options
}
```

**Safe Example**:
```php
if (isset($_POST['save_settings'])) {
    $setting_value = sanitize_text_field($_POST['setting_value']);
    update_option('my_setting', $setting_value); // SAFE: Options sanitized before saving
}
```

**Remediation**: Always sanitize data before saving to WordPress options.

#### `wordpress.sanitization.usermeta-missing`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects user meta updates without sanitization.

**Vulnerable Example**:
```php
$user_id = get_current_user_id();
update_user_meta($user_id, 'custom_field', $_POST['value']); // VULNERABLE: Unsanitized user meta
```

**Safe Example**:
```php
$user_id = get_current_user_id();
$value = sanitize_text_field($_POST['value']);
update_user_meta($user_id, 'custom_field', $value); // SAFE: User meta sanitized
```

**Remediation**: Sanitize data before updating user meta.

#### `wordpress.sanitization.postmeta-missing`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects post meta updates without sanitization.

**Vulnerable Example**:
```php
$post_id = $_POST['post_id'];
update_post_meta($post_id, 'custom_field', $_POST['value']); // VULNERABLE: Unsanitized post meta
```

**Safe Example**:
```php
$post_id = intval($_POST['post_id']);
$value = sanitize_text_field($_POST['value']);
update_post_meta($post_id, 'custom_field', $value); // SAFE: Post meta sanitized
```

**Remediation**: Sanitize data before updating post meta.

#### `wordpress.sanitization.comment-missing`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects comment data without proper sanitization.

**Vulnerable Example**:
```php
$comment_data = array(
    'comment_content' => $_POST['comment'],
    'comment_author' => $_POST['author']
);
wp_insert_comment($comment_data); // VULNERABLE: Unsanitized comment data
```

**Safe Example**:
```php
$comment_data = array(
    'comment_content' => wp_filter_comment($_POST['comment']),
    'comment_author' => sanitize_text_field($_POST['author'])
);
wp_insert_comment($comment_data); // SAFE: Comment data properly sanitized
```

**Remediation**: Use `wp_filter_comment()` for comment content and `sanitize_text_field()` for comment author.

#### `wordpress.sanitization.search-missing`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects search queries without sanitization.

**Vulnerable Example**:
```php
$search_term = $_GET['s'];
$query = new WP_Query(array('s' => $search_term)); // VULNERABLE: Unsanitized search
```

**Safe Example**:
```php
$search_term = sanitize_text_field($_GET['s']);
$query = new WP_Query(array('s' => $search_term)); // SAFE: Search term sanitized
```

**Remediation**: Use `sanitize_text_field()` for search terms.

### 12. File Upload Security

#### `wordpress.sanitization.upload-missing`
**Severity**: ERROR  
**CWE**: CWE-434 (Unrestricted Upload of File with Dangerous Type)

**Description**: Detects file uploads without proper validation.

**Vulnerable Example**:
```php
$uploaded_file = $_FILES['file'];
move_uploaded_file($uploaded_file['tmp_name'], $uploaded_file['name']); // VULNERABLE: Unsafe upload
```

**Safe Example**:
```php
$uploaded_file = $_FILES['file'];
$upload_overrides = array('test_form' => false);
$moved_file = wp_handle_upload($uploaded_file, $upload_overrides); // SAFE: WordPress upload handler
```

**Remediation**: Use `wp_handle_upload()` for file uploads and validate file types.

### 13. Session and Cookie Security

#### `wordpress.sanitization.cookie-missing`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects cookie data usage without sanitization.

**Vulnerable Example**:
```php
$cookie_value = $_COOKIE['user_preference'];
echo $cookie_value; // VULNERABLE: Unsanitized cookie data
```

**Safe Example**:
```php
$cookie_value = sanitize_text_field($_COOKIE['user_preference']);
echo esc_html($cookie_value); // SAFE: Cookie data sanitized and escaped
```

**Remediation**: Always sanitize cookie data before use.

#### `wordpress.sanitization.session-missing`
**Severity**: ERROR  
**CWE**: CWE-79 (Cross-site Scripting)

**Description**: Detects session data usage without sanitization.

**Vulnerable Example**:
```php
session_start();
$session_data = $_SESSION['user_data'];
echo $session_data; // VULNERABLE: Unsanitized session data
```

**Safe Example**:
```php
session_start();
$session_data = sanitize_text_field($_SESSION['user_data']);
echo esc_html($session_data); // SAFE: Session data sanitized and escaped
```

**Remediation**: Always sanitize session data before use.

## Best Practices

### 1. Choose the Right Sanitization Function

- **`sanitize_text_field()`**: For plain text input
- **`wp_kses_post()`**: For HTML content that should preserve safe HTML
- **`sanitize_email()`**: For email addresses
- **`sanitize_file_name()`**: For file names
- **`esc_html()`**: For output escaping
- **`esc_attr()`**: For HTML attributes
- **`esc_url()`**: For URLs

### 2. Validate After Sanitization

```php
$email = sanitize_email($_POST['email']);
if (is_email($email)) {
    // Use the email
}
```

### 3. Use Prepared Statements

```php
$user_input = sanitize_text_field($_POST['search']);
$wpdb->prepare("SELECT * FROM posts WHERE title LIKE %s", '%' . $wpdb->esc_like($user_input) . '%');
```

### 4. Combine Security Measures

```php
// Verify nonce
if (!wp_verify_nonce($_POST['_wpnonce'], 'action_name')) {
    wp_die('Security check failed');
}

// Check capability
if (!current_user_can('manage_options')) {
    wp_die('Insufficient permissions');
}

// Sanitize input
$data = sanitize_text_field($_POST['data']);

// Use the data safely
update_option('my_option', $data);
```

## Testing

The sanitization rules include comprehensive test cases:

- **Vulnerable Examples**: `tests/vulnerable-examples/sanitization-vulnerable.php`
- **Safe Examples**: `tests/safe-examples/sanitization-safe.php`

Run the tests to verify rule accuracy:

```bash
# Test vulnerable examples (should find issues)
semgrep scan --config=packs/wp-core-security/sanitization-functions.yaml tests/vulnerable-examples/sanitization-vulnerable.php

# Test safe examples (should find no issues)
semgrep scan --config=packs/wp-core-security/sanitization-functions.yaml tests/safe-examples/sanitization-safe.php
```

## Integration

These rules are included in the WordPress Core Security pack and can be used with:

- **Basic Configuration**: Essential sanitization rules
- **Strict Configuration**: All sanitization rules with additional checks
- **Plugin Development Configuration**: Comprehensive sanitization coverage

## References

- [WordPress Data Validation](https://developer.wordpress.org/plugins/security/data-validation/)
- [WordPress Data Sanitization](https://developer.wordpress.org/plugins/security/data-sanitization-escaping/)
- [WordPress Escaping](https://developer.wordpress.org/plugins/security/securing-output/)
- [OWASP XSS Prevention](https://owasp.org/www-project-cheat-sheets/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
- [OWASP SQL Injection Prevention](https://owasp.org/www-project-cheat-sheets/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html)
