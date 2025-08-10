# WordPress Capability Check Rules

## Overview

The WordPress Capability Check Rules are designed to detect security vulnerabilities related to improper or missing user capability checks in WordPress applications. These rules help ensure that sensitive operations are properly protected by appropriate user permissions.

## Rule Categories

### 1. Missing Capability Checks

#### `wordpress.capability.missing-check`
**Severity**: ERROR  
**CWE**: CWE-285 (Improper Authorization)

Detects operations that lack proper capability checks, such as user deletion without verifying permissions.

**Vulnerable Example**:
```php
if (isset($_POST['delete_user'])) {
    wp_delete_user($_POST['user_id']); // Missing capability check
}
```

**Safe Example**:
```php
if (isset($_POST['delete_user'])) {
    if (current_user_can('delete_users')) {
        wp_delete_user(intval($_POST['user_id']));
    } else {
        wp_die('Insufficient permissions');
    }
}
```

#### `wordpress.capability.missing-admin-check`
**Severity**: ERROR  
**CWE**: CWE-285 (Improper Authorization)

Detects admin-only operations that lack proper admin capability checks.

**Vulnerable Example**:
```php
if (isset($_POST['update_settings'])) {
    update_option('sensitive_setting', $_POST['value']); // Missing admin check
}
```

**Safe Example**:
```php
if (isset($_POST['update_settings'])) {
    if (current_user_can('manage_options')) {
        update_option('sensitive_setting', sanitize_text_field($_POST['value']));
    } else {
        wp_die('Insufficient permissions');
    }
}
```

### 2. Weak Capability Checks

#### `wordpress.capability.weak-check`
**Severity**: WARNING  
**CWE**: CWE-285 (Improper Authorization)

Detects overly permissive capability checks that may allow unauthorized access.

**Vulnerable Example**:
```php
if (current_user_can('read')) { // Too permissive for sensitive operation
    wp_delete_user($_POST['user_id']);
}
```

**Safe Example**:
```php
if (current_user_can('delete_users')) { // Specific capability for user deletion
    wp_delete_user($_POST['user_id']);
}
```

#### `wordpress.capability.overly-permissive`
**Severity**: WARNING  
**CWE**: CWE-285 (Improper Authorization)

Detects capability checks that are too permissive for the operation being performed.

**Vulnerable Example**:
```php
if (current_user_can('edit_posts')) { // Too permissive for user deletion
    wp_delete_user($_POST['user_id']);
}
```

**Safe Example**:
```php
if (current_user_can('delete_users')) { // Proper capability for user deletion
    wp_delete_user($_POST['user_id']);
}
```

### 3. Missing Nonce with Capability Check

#### `wordpress.capability.missing-nonce`
**Severity**: ERROR  
**CWE**: CWE-352 (Cross-Site Request Forgery)

Detects capability checks that lack nonce verification for form submissions.

**Vulnerable Example**:
```php
if (current_user_can('manage_options')) {
    if (isset($_POST['submit'])) {
        update_option('setting', $_POST['value']); // Missing nonce check
    }
}
```

**Safe Example**:
```php
if (current_user_can('manage_options')) {
    if (isset($_POST['submit'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'update_settings')) {
            update_option('setting', sanitize_text_field($_POST['value']));
        } else {
            wp_die('Security check failed');
        }
    }
}
```

### 4. AJAX Capability Checks

#### `wordpress.capability.ajax-missing`
**Severity**: ERROR  
**CWE**: CWE-285 (Improper Authorization)

Detects AJAX handlers that lack proper capability checks.

**Vulnerable Example**:
```php
add_action('wp_ajax_delete_user', 'delete_user_callback');
function delete_user_callback() {
    wp_delete_user($_POST['user_id']); // Missing capability check
}
```

**Safe Example**:
```php
add_action('wp_ajax_delete_user', 'delete_user_callback');
function delete_user_callback() {
    if (current_user_can('delete_users')) {
        wp_delete_user(intval($_POST['user_id']));
        wp_send_json_success('User deleted');
    } else {
        wp_send_json_error('Insufficient permissions');
    }
}
```

#### `wordpress.capability.ajax-weak`
**Severity**: WARNING  
**CWE**: CWE-285 (Improper Authorization)

Detects weak capability checks in AJAX handlers.

**Vulnerable Example**:
```php
function ajax_callback() {
    if (current_user_can('read')) { // Too permissive
        // Perform sensitive operation
        wp_delete_user($_POST['user_id']);
    }
}
```

**Safe Example**:
```php
function ajax_callback() {
    if (current_user_can('delete_users')) { // Specific capability for user deletion
        // Perform sensitive operation
        wp_delete_user($_POST['user_id']);
    }
}
```

### 5. REST API Capability Checks

#### `wordpress.capability.rest-missing`
**Severity**: ERROR  
**CWE**: CWE-285 (Improper Authorization)

Detects REST API endpoints that lack proper capability checks.

**Vulnerable Example**:
```php
register_rest_route('my-namespace/v1', '/users', array(
    'methods' => 'DELETE',
    'callback' => 'delete_user_rest',
    'permission_callback' => '__return_true' // Allows anyone
));
```

**Safe Example**:
```php
register_rest_route('my-namespace/v1', '/users', array(
    'methods' => 'DELETE',
    'callback' => 'delete_user_rest',
    'permission_callback' => function() {
        return current_user_can('delete_users');
    }
));
```

### 6. User Role Checks

#### `wordpress.capability.role-check-instead`
**Severity**: WARNING  
**CWE**: CWE-285 (Improper Authorization)

Detects the use of role checks instead of capability checks.

**Vulnerable Example**:
```php
if (current_user_can('administrator')) { // Should use specific capability
    // Admin operation
    update_option('admin_setting', $_POST['value']);
}
```

**Safe Example**:
```php
if (current_user_can('manage_options')) { // Specific capability instead of role
    // Admin operation
    update_option('admin_setting', sanitize_text_field($_POST['value']));
}
```

#### `wordpress.capability.role-comparison`
**Severity**: ERROR  
**CWE**: CWE-285 (Improper Authorization)

Detects insecure direct role comparisons.

**Vulnerable Example**:
```php
$user = wp_get_current_user();
if ($user->roles[0] === 'administrator') { // Insecure role check
    // Admin operation
    wp_delete_user($_POST['user_id']);
}
```

**Safe Example**:
```php
if (current_user_can('delete_users')) { // Use capability instead of role check
    // Admin operation
    wp_delete_user($_POST['user_id']);
}
```

### 7. Conditional Capability Checks

#### `wordpress.capability.conditional-missing`
**Severity**: ERROR  
**CWE**: CWE-285 (Improper Authorization)

Detects missing capability checks in conditional logic.

**Vulnerable Example**:
```php
$action = $_POST['action'];
if ($action === 'delete') {
    wp_delete_user($_POST['user_id']); // Missing capability check
} elseif ($action === 'update') {
    wp_update_user($_POST['user_data']); // Missing capability check
}
```

**Safe Example**:
```php
$action = $_POST['action'];
if ($action === 'delete' && current_user_can('delete_users')) {
    wp_delete_user($_POST['user_id']);
} elseif ($action === 'update' && current_user_can('edit_users')) {
    wp_update_user($_POST['user_data']);
}
```

### 8. File Operation Capability Checks

#### `wordpress.capability.file-operation-missing`
**Severity**: ERROR  
**CWE**: CWE-285 (Improper Authorization)

Detects file operations that lack proper capability checks.

**Vulnerable Example**:
```php
if (isset($_POST['upload_file'])) {
    move_uploaded_file($_FILES['file']['tmp_name'], $destination); // Missing capability check
}
```

**Safe Example**:
```php
if (isset($_POST['upload_file'])) {
    if (current_user_can('upload_files')) {
        move_uploaded_file($_FILES['file']['tmp_name'], $destination);
    }
}
```

### 9. Database Operation Capability Checks

#### `wordpress.capability.db-operation-missing`
**Severity**: ERROR  
**CWE**: CWE-285 (Improper Authorization)

Detects database operations that lack proper capability checks.

**Vulnerable Example**:
```php
if (isset($_POST['delete_option'])) {
    delete_option($_POST['option_name']); // Missing capability check
}
```

**Safe Example**:
```php
if (isset($_POST['delete_option'])) {
    if (current_user_can('manage_options')) {
        delete_option(sanitize_text_field($_POST['option_name']));
    }
}
```

### 10. Plugin/Theme Management Capability Checks

#### `wordpress.capability.plugin-management-missing`
**Severity**: ERROR  
**CWE**: CWE-285 (Improper Authorization)

Detects plugin management operations that lack proper capability checks.

**Vulnerable Example**:
```php
if (isset($_POST['activate_plugin'])) {
    activate_plugin($_POST['plugin_file']); // Missing capability check
}
```

**Safe Example**:
```php
if (isset($_POST['activate_plugin'])) {
    if (current_user_can('activate_plugins')) {
        activate_plugin(sanitize_text_field($_POST['plugin_file']));
    }
}
```

### 11. User Management Capability Checks

#### `wordpress.capability.user-management-missing`
**Severity**: ERROR  
**CWE**: CWE-285 (Improper Authorization)

Detects user management operations that lack proper capability checks.

**Vulnerable Example**:
```php
if (isset($_POST['create_user'])) {
    wp_insert_user($_POST['user_data']); // Missing capability check
}
```

**Safe Example**:
```php
if (isset($_POST['create_user'])) {
    if (current_user_can('create_users')) {
        wp_insert_user($_POST['user_data']);
    }
}
```

### 12. Content Management Capability Checks

#### `wordpress.capability.content-management-missing`
**Severity**: ERROR  
**CWE**: CWE-285 (Improper Authorization)

Detects content management operations that lack proper capability checks.

**Vulnerable Example**:
```php
if (isset($_POST['publish_post'])) {
    wp_publish_post($_POST['post_id']); // Missing capability check
}
```

**Safe Example**:
```php
if (isset($_POST['publish_post'])) {
    if (current_user_can('publish_posts')) {
        wp_publish_post(intval($_POST['post_id']));
    }
}
```

### 13. Settings Management Capability Checks

#### `wordpress.capability.settings-management-missing`
**Severity**: ERROR  
**CWE**: CWE-285 (Improper Authorization)

Detects settings management operations that lack proper capability checks.

**Vulnerable Example**:
```php
if (isset($_POST['update_settings'])) {
    update_option('site_title', $_POST['title']);
    update_option('site_description', $_POST['description']); // Missing capability check
}
```

**Safe Example**:
```php
if (isset($_POST['update_settings'])) {
    if (current_user_can('manage_options')) {
        update_option('site_title', sanitize_text_field($_POST['title']));
        update_option('site_description', sanitize_text_field($_POST['description']));
    }
}
```

### 14. Multisite Capability Checks

#### `wordpress.capability.multisite-missing`
**Severity**: ERROR  
**CWE**: CWE-285 (Improper Authorization)

Detects multisite operations that lack proper capability checks.

**Vulnerable Example**:
```php
if (isset($_POST['create_site'])) {
    wpmu_create_blog($_POST['domain'], $_POST['path'], $_POST['title']); // Missing capability check
}
```

**Safe Example**:
```php
if (isset($_POST['create_site'])) {
    if (current_user_can('manage_sites')) {
        wpmu_create_blog(
            sanitize_text_field($_POST['domain']), 
            sanitize_text_field($_POST['path']), 
            sanitize_text_field($_POST['title'])
        );
    }
}
```

### 15. Security Best Practices

#### `wordpress.capability.hardcoded-capability`
**Severity**: INFO  
**CWE**: CWE-285 (Improper Authorization)

Detects hardcoded capability strings that could be improved with constants.

**Vulnerable Example**:
```php
if (current_user_can("manage_options")) { // Hardcoded string
    // Admin operation
}
```

**Safe Example**:
```php
define('ADMIN_CAPABILITY', 'manage_options');
if (current_user_can(ADMIN_CAPABILITY)) { // Using constant
    // Admin operation
}
```

#### `wordpress.capability.multiple-checks`
**Severity**: INFO  
**CWE**: CWE-285 (Improper Authorization)

Detects multiple capability checks that could be optimized.

**Vulnerable Example**:
```php
if (current_user_can('edit_posts') && current_user_can('publish_posts')) { // Could be optimized
    // Operation
}
```

**Safe Example**:
```php
if (current_user_can('edit_posts') || current_user_can('publish_posts')) { // Optimized with OR
    // Operation
}
```

## Common WordPress Capabilities

### Admin Capabilities
- `manage_options` - Manage site options and settings
- `activate_plugins` - Activate plugins
- `install_plugins` - Install plugins
- `switch_themes` - Switch themes
- `edit_theme_options` - Edit theme options

### User Management Capabilities
- `create_users` - Create new users
- `edit_users` - Edit existing users
- `delete_users` - Delete users
- `list_users` - List users

### Content Management Capabilities
- `edit_posts` - Edit posts
- `publish_posts` - Publish posts
- `delete_posts` - Delete posts
- `edit_pages` - Edit pages
- `publish_pages` - Publish pages
- `delete_pages` - Delete pages

### Media Capabilities
- `upload_files` - Upload files
- `edit_files` - Edit files

### Multisite Capabilities
- `manage_sites` - Manage sites in multisite
- `manage_network` - Manage network settings
- `create_sites` - Create new sites

## Best Practices

1. **Use Specific Capabilities**: Always use the most specific capability for the operation being performed.

2. **Combine with Nonce Verification**: Always verify nonces for form submissions, even when capability checks are present.

3. **Use Constants**: Define capability constants for better maintainability.

4. **Proper Error Handling**: Provide clear error messages when capability checks fail.

5. **Sanitize Input**: Always sanitize user input, even after capability checks.

6. **Test Thoroughly**: Test capability checks with different user roles to ensure they work correctly.

## Testing

The capability check rules include comprehensive test cases:

- **Vulnerable Examples**: `tests/vulnerable-examples/capability-vulnerable.php`
- **Safe Examples**: `tests/safe-examples/capability-safe.php`

Run the tests to verify rule functionality:

```bash
# Test vulnerable examples (should trigger findings)
semgrep scan --config=packs/wp-core-security/capability-checks.yaml tests/vulnerable-examples/capability-vulnerable.php

# Test safe examples (should not trigger findings)
semgrep scan --config=packs/wp-core-security/capability-checks.yaml tests/safe-examples/capability-safe.php
```

## Integration

These rules are part of the `wp-core-security` pack and can be used with the basic configuration:

```yaml
# configs/basic.yaml
rules:
  - packs/wp-core-security/capability-checks.yaml
```

## References

- [WordPress Capabilities](https://developer.wordpress.org/reference/functions/current_user_can/)
- [WordPress User Roles and Capabilities](https://wordpress.org/support/article/roles-and-capabilities/)
- [OWASP Improper Authorization](https://owasp.org/www-project-top-ten/2017/A5_2017-Broken_Access_Control)
