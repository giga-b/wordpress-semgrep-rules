# WordPress REST API Security Rules

This document provides comprehensive guidance on the REST API security rules included in the WordPress Semgrep Rules project.

## Overview

The REST API security rules are designed to detect common security vulnerabilities in WordPress REST API endpoints. These rules help developers create secure, production-ready REST APIs that follow WordPress security best practices.

## Rule Categories

### 1. Authentication Rules

#### `wordpress.rest-api.missing-authentication`
**Severity**: ERROR  
**CWE**: CWE-287  
**Description**: Detects REST API endpoints that lack proper authentication mechanisms.

**Vulnerable Pattern**:
```php
register_rest_route('my-plugin/v1', '/data', [
    'methods' => 'GET',
    'callback' => 'get_data',
    'permission_callback' => '__return_true' // No authentication!
]);
```

**Secure Pattern**:
```php
register_rest_route('my-plugin/v1', '/data', [
    'methods' => 'GET',
    'callback' => 'get_data',
    'permission_callback' => function($request) {
        return current_user_can('read') && wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest');
    }
]);
```

**Remediation**:
- Always implement a `permission_callback` function
- Use WordPress capability checks (`current_user_can()`)
- Implement nonce verification for state-changing operations
- Consider using application passwords for external applications

#### `wordpress.rest-api.weak-authentication`
**Severity**: WARNING  
**CWE**: CWE-287  
**Description**: Detects REST API endpoints with weak authentication mechanisms.

**Vulnerable Pattern**:
```php
'permission_callback' => function($request) {
    return true; // Always returns true
}
```

**Secure Pattern**:
```php
'permission_callback' => function($request) {
    return current_user_can('manage_options') && wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest');
}
```

### 2. Input Validation Rules

#### `wordpress.rest-api.missing-input-validation`
**Severity**: ERROR  
**CWE**: CWE-20  
**Description**: Detects REST API endpoints that don't validate input parameters.

**Vulnerable Pattern**:
```php
function my_callback($request) {
    $param = $request->get_param('data');
    return $param; // No validation
}
```

**Secure Pattern**:
```php
function my_callback($request) {
    $param = $request->get_param('data');
    if (empty($param) || !is_string($param)) {
        return new WP_Error('invalid_param', 'Invalid parameter', array('status' => 400));
    }
    $param = sanitize_text_field($param);
    return $param;
}
```

**Remediation**:
- Always validate input parameters
- Check data types and required fields
- Use WordPress sanitization functions
- Return appropriate HTTP error codes

#### `wordpress.rest-api.direct-param-output`
**Severity**: ERROR  
**CWE**: CWE-79  
**Description**: Detects REST API endpoints that directly output user input without sanitization.

**Vulnerable Pattern**:
```php
function my_callback($request) {
    $param = $request->get_param('data');
    echo $param; // XSS vulnerability
}
```

**Secure Pattern**:
```php
function my_callback($request) {
    $param = $request->get_param('data');
    $param = sanitize_text_field($param);
    return array('data' => $param);
}
```

### 3. Nonce Verification Rules

#### `wordpress.rest-api.missing-nonce`
**Severity**: ERROR  
**CWE**: CWE-352  
**Description**: Detects state-changing REST API operations without nonce verification.

**Vulnerable Pattern**:
```php
register_rest_route('my-plugin/v1', '/update', [
    'methods' => 'POST',
    'callback' => 'update_data',
    'permission_callback' => function($request) {
        return current_user_can('edit_posts');
    }
]);
```

**Secure Pattern**:
```php
register_rest_route('my-plugin/v1', '/update', [
    'methods' => 'POST',
    'callback' => 'update_data',
    'permission_callback' => function($request) {
        return current_user_can('edit_posts') && wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest');
    }
]);
```

### 4. SQL Injection Prevention

#### `wordpress.rest-api.sql-injection-risk`
**Severity**: ERROR  
**CWE**: CWE-89  
**Description**: Detects potential SQL injection vulnerabilities in REST API endpoints.

**Vulnerable Pattern**:
```php
function my_callback($request) {
    global $wpdb;
    $param = $request->get_param('title');
    $result = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_title = '$param'");
    return $result;
}
```

**Secure Pattern**:
```php
function my_callback($request) {
    global $wpdb;
    $param = $request->get_param('title');
    $param = sanitize_text_field($param);
    $result = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $wpdb->posts WHERE post_title = %s",
        $param
    ));
    return $result;
}
```

### 5. File Upload Security

#### `wordpress.rest-api.unsafe-file-upload`
**Severity**: ERROR  
**CWE**: CWE-434  
**Description**: Detects unsafe file upload handling in REST API endpoints.

**Vulnerable Pattern**:
```php
function my_callback($request) {
    $file = $_FILES['file'];
    move_uploaded_file($file['tmp_name'], '/path/to/upload/' . $file['name']);
    return 'File uploaded';
}
```

**Secure Pattern**:
```php
function my_callback($request) {
    if (!isset($_FILES['file'])) {
        return new WP_Error('no_file', 'No file uploaded', array('status' => 400));
    }
    
    $file = $_FILES['file'];
    $allowed_types = array('jpg', 'png', 'gif');
    $file_type = wp_check_filetype($file['name']);
    
    if (!in_array($file_type['ext'], $allowed_types)) {
        return new WP_Error('invalid_file_type', 'Invalid file type', array('status' => 400));
    }
    
    $upload = wp_handle_upload($file, array('test_form' => false));
    return array('file' => $upload);
}
```

### 6. Rate Limiting

#### `wordpress.rest-api.missing-rate-limiting`
**Severity**: WARNING  
**CWE**: CWE-770  
**Description**: Detects public REST API endpoints without rate limiting.

**Vulnerable Pattern**:
```php
register_rest_route('my-plugin/v1', '/public/data', [
    'methods' => 'GET',
    'callback' => 'get_public_data',
    'permission_callback' => '__return_true'
]);
```

**Secure Pattern**:
```php
register_rest_route('my-plugin/v1', '/public/data', [
    'methods' => 'GET',
    'callback' => 'get_public_data',
    'permission_callback' => function($request) {
        return check_rate_limit($request);
    }
]);
```

### 7. CORS Security

#### `wordpress.rest-api.unsafe-cors`
**Severity**: WARNING  
**CWE**: CWE-942  
**Description**: Detects unsafe CORS configurations in REST API endpoints.

**Vulnerable Pattern**:
```php
add_action('rest_api_init', function() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
});
```

**Secure Pattern**:
```php
add_action('rest_api_init', function() {
    $allowed_origins = array('https://trusted-site.com');
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, $allowed_origins)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: GET, POST');
    }
});
```

### 8. Error Information Disclosure

#### `wordpress.rest-api.error-disclosure`
**Severity**: WARNING  
**CWE**: CWE-209  
**Description**: Detects REST API endpoints that may disclose sensitive information in error responses.

**Vulnerable Pattern**:
```php
function my_callback($request) {
    try {
        $result = some_operation();
        return $result;
    } catch (Exception $e) {
        return array(
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        );
    }
}
```

**Secure Pattern**:
```php
function my_callback($request) {
    try {
        $result = some_operation();
        return $result;
    } catch (Exception $e) {
        return new WP_Error('operation_failed', 'Operation failed', array('status' => 500));
    }
}
```

### 9. Sensitive Data Exposure

#### `wordpress.rest-api.sensitive-data-exposure`
**Severity**: WARNING  
**CWE**: CWE-200  
**Description**: Detects REST API endpoints that may expose sensitive data.

**Vulnerable Pattern**:
```php
function my_callback($request) {
    $user = get_user_by('id', $request->get_param('user_id'));
    return array(
        'id' => $user->ID,
        'user_login' => $user->user_login,
        'user_pass' => $user->user_pass, // Password hash exposed
        'user_email' => $user->user_email
    );
}
```

**Secure Pattern**:
```php
function my_callback($request) {
    $user = get_user_by('id', $request->get_param('user_id'));
    return array(
        'id' => $user->ID,
        'user_login' => $user->user_login,
        'user_email' => $user->user_email
        // Note: user_pass is intentionally excluded
    );
}
```

### 10. HTTP Method Validation

#### `wordpress.rest-api.unsafe-methods`
**Severity**: WARNING  
**CWE**: CWE-650  
**Description**: Detects REST API endpoints that allow unnecessary HTTP methods.

**Vulnerable Pattern**:
```php
register_rest_route('my-plugin/v1', '/resource', [
    'methods' => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
    'callback' => 'handle_resource'
]);
```

**Secure Pattern**:
```php
register_rest_route('my-plugin/v1', '/resource', [
    'methods' => 'GET, POST',
    'callback' => 'handle_resource'
]);
```

### 11. Namespace Security

#### `wordpress.rest-api.weak-namespace`
**Severity**: INFO  
**CWE**: CWE-200  
**Description**: Detects REST API endpoints using generic namespaces.

**Vulnerable Pattern**:
```php
register_rest_route('api/v1', '/data', $args);
```

**Secure Pattern**:
```php
register_rest_route('my-plugin/v1', '/data', $args);
```

### 12. Parameter Sanitization

#### `wordpress.rest-api.missing-sanitization`
**Severity**: ERROR  
**CWE**: CWE-20  
**Description**: Detects REST API endpoints that don't sanitize input parameters.

**Vulnerable Pattern**:
```php
function my_callback($request) {
    $param = $request->get_param('data');
    $result = process_data($param);
    return $result;
}
```

**Secure Pattern**:
```php
function my_callback($request) {
    $param = $request->get_param('data');
    $param = sanitize_text_field($param);
    $result = process_data($param);
    return $result;
}
```

### 13. Security Headers

#### `wordpress.rest-api.missing-security-headers`
**Severity**: INFO  
**CWE**: CWE-693  
**Description**: Detects REST API endpoints missing security headers.

**Vulnerable Pattern**:
```php
function my_callback($request) {
    $data = get_data();
    return $data;
}
```

**Secure Pattern**:
```php
function my_callback($request) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    
    $data = get_data();
    return $data;
}
```

## Best Practices

### 1. Authentication
- Always implement proper authentication for REST API endpoints
- Use WordPress capability checks (`current_user_can()`)
- Implement nonce verification for state-changing operations
- Consider using application passwords for external applications

### 2. Input Validation
- Validate all input parameters
- Check data types and required fields
- Use WordPress sanitization functions
- Return appropriate HTTP error codes

### 3. Output Sanitization
- Never directly output user input
- Use appropriate escaping functions
- Sanitize data before returning responses

### 4. Database Security
- Always use prepared statements
- Validate and sanitize all database inputs
- Use WordPress database functions when possible

### 5. File Uploads
- Validate file types and sizes
- Use WordPress upload functions
- Implement proper file permissions
- Scan uploaded files for malware

### 6. Rate Limiting
- Implement rate limiting for public endpoints
- Use WordPress transients for tracking
- Set appropriate limits based on endpoint usage

### 7. CORS Configuration
- Restrict allowed origins
- Limit allowed methods
- Implement proper headers

### 8. Error Handling
- Don't expose sensitive information in errors
- Use WordPress error handling functions
- Log errors appropriately

### 9. Data Exposure
- Only expose necessary data
- Filter sensitive information
- Implement proper access controls

### 10. HTTP Methods
- Only allow necessary HTTP methods
- Implement proper method handling
- Use appropriate status codes

## Testing

The REST API security rules include comprehensive test cases:

- **Vulnerable Examples**: `tests/vulnerable-examples/rest-api-vulnerable.php`
- **Safe Examples**: `tests/safe-examples/rest-api-safe.php`

Run the tests to verify rule functionality:

```bash
# Run Semgrep on vulnerable examples
semgrep --config configs/basic.yaml tests/vulnerable-examples/rest-api-vulnerable.php

# Run Semgrep on safe examples (should have no findings)
semgrep --config configs/basic.yaml tests/safe-examples/rest-api-safe.php
```

## Configuration

The REST API security rules are included in all configuration files:

- **Basic Configuration**: Essential REST API security rules
- **Strict Configuration**: All REST API security rules
- **Plugin Development Configuration**: All REST API security rules plus experimental rules

## References

- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [WordPress REST API Security](https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/)
- [OWASP REST Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/REST_Security_Cheat_Sheet.html)
- [WordPress Security Best Practices](https://developer.wordpress.org/plugins/security/)
