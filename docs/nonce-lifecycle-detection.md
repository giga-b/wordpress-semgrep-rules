# Nonce Lifecycle Detection Rules

## Overview

The Nonce Lifecycle Detection Rules provide comprehensive security analysis for WordPress nonce (number used once) implementation patterns. These rules detect vulnerabilities and security issues throughout the complete nonce lifecycle: creation, inclusion, verification, and expiration handling.

## Security Context

Nonces are critical security mechanisms in WordPress that prevent Cross-Site Request Forgery (CSRF) attacks. A properly implemented nonce lifecycle includes:

1. **Creation**: Generating nonces with specific, unique action names
2. **Inclusion**: Adding nonces to forms, AJAX requests, and REST API calls
3. **Verification**: Checking nonces before processing user actions
4. **Expiration**: Handling expired or invalid nonces appropriately

## Rule Categories

### 1. Nonce Creation Detection (Task 1.5.1)

#### Safe Patterns
- `wp_create_nonce('specific_action_name')`
- `wp_nonce_field('unique_action')`
- `wp_nonce_url('url', 'action_name')`
- `wp_nonce_ays('confirmation_action')`

#### Vulnerable Patterns Detected
- Empty action names: `wp_create_nonce("")`
- Generic action names: `wp_create_nonce("action")`, `wp_create_nonce("nonce")`
- Variable action names: `wp_create_nonce($variable)`

#### Rules
- `wordpress.nonce.lifecycle.creation-wp-create-nonce` - Detects nonce creation
- `wordpress.nonce.lifecycle.creation-wp-nonce-field` - Detects nonce field creation
- `wordpress.nonce.lifecycle.creation-wp-nonce-url` - Detects nonce URL creation
- `wordpress.nonce.lifecycle.creation-wp-nonce-ays` - Detects nonce AYS creation
- `wordpress.nonce.lifecycle.creation-weak-action` - Detects weak action names
- `wordpress.nonce.lifecycle.creation-variable-action` - Detects variable action names

### 2. Nonce Inclusion Detection (Task 1.5.2)

#### Safe Patterns
- Nonce fields in forms: `wp_nonce_field('action')`
- Hidden nonce fields: `<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('action'); ?>" />`
- AJAX nonce inclusion: `nonce: '<?php echo wp_create_nonce("action"); ?>'`
- REST API nonce headers: `'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>'`

#### Vulnerable Patterns Detected
- Forms without nonce fields
- AJAX requests without nonce parameters
- REST API calls without nonce headers

#### Rules
- `wordpress.nonce.lifecycle.inclusion-form-field` - Detects nonce field inclusion
- `wordpress.nonce.lifecycle.inclusion-hidden-field` - Detects hidden nonce fields
- `wordpress.nonce.lifecycle.inclusion-ajax-data` - Detects AJAX nonce inclusion
- `wordpress.nonce.lifecycle.inclusion-missing-field` - Detects missing nonce fields

### 3. Nonce Verification Detection (Task 1.5.3)

#### Safe Patterns
- Form verification: `if (wp_verify_nonce($_POST['_wpnonce'], 'action'))`
- AJAX verification: `check_ajax_referer('action', 'nonce')`
- Admin verification: `check_admin_referer('action')`
- REST API verification: `wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')`

#### Vulnerable Patterns Detected
- Missing nonce verification in form processing
- Missing nonce verification in AJAX handlers
- Weak verification without return value checking
- Wrong action names in verification

#### Rules
- `wordpress.nonce.lifecycle.verification-wp-verify-nonce` - Detects wp_verify_nonce usage
- `wordpress.nonce.lifecycle.verification-check-ajax-referer` - Detects AJAX verification
- `wordpress.nonce.lifecycle.verification-check-admin-referer` - Detects admin verification
- `wordpress.nonce.lifecycle.verification-weak-check` - Detects weak verification
- `wordpress.nonce.lifecycle.verification-missing` - Detects missing verification
- `wordpress.nonce.lifecycle.verification-ajax-missing` - Detects missing AJAX verification

### 4. Nonce Expiration Handling (Task 1.5.4)

#### Safe Patterns
- Proper error handling: `if ($result === false) { wp_die('Nonce expired'); }`
- Basic error handling: `if (!wp_verify_nonce($_POST['_wpnonce'], 'action')) { wp_die('Security check failed'); }`
- User-friendly messages: `wp_redirect(add_query_arg('error', 'expired', wp_get_referer()))`

#### Vulnerable Patterns Detected
- No error handling for verification results
- Generic error messages that don't distinguish between expired and invalid nonces
- Information disclosure in error messages

#### Rules
- `wordpress.nonce.lifecycle.expiration-proper-handling` - Detects proper expiration handling
- `wordpress.nonce.lifecycle.expiration-basic-handling` - Detects basic expiration handling
- `wordpress.nonce.lifecycle.expiration-poor-handling` - Detects poor error handling
- `wordpress.nonce.lifecycle.expiration-no-handling` - Detects missing error handling

### 5. Cross-File Analysis

#### Safe Patterns
- Matching action names across creation and verification
- Consistent nonce usage patterns

#### Vulnerable Patterns Detected
- Action name mismatches between creation and verification
- Nonces created but never verified

#### Rules
- `wordpress.nonce.lifecycle.mismatch-detection` - Detects potential action mismatches

## Usage

### Running the Rules

```bash
# Scan a single file
semgrep scan --config packs/wp-core-security/nonce-lifecycle-detection.yaml file.php

# Scan a directory
semgrep scan --config packs/wp-core-security/nonce-lifecycle-detection.yaml src/

# Scan with JSON output
semgrep scan --config packs/wp-core-security/nonce-lifecycle-detection.yaml --json src/
```

### Integration with CI/CD

```yaml
# GitHub Actions example
- name: Run Nonce Security Scan
  uses: returntocorp/semgrep-action@v1
  with:
    config: packs/wp-core-security/nonce-lifecycle-detection.yaml
    paths: src/
    outputFormat: json
    outputFile: nonce-security-results.json
```

## Test Results

### Safe Examples
- **Total Findings**: 47
- **Creation Findings**: 18
- **Inclusion Findings**: 8
- **Verification Findings**: 12
- **Expiration Findings**: 9
- **Cross-file Findings**: 0

### Vulnerable Examples
- **Total Findings**: 84
- **Creation Findings**: 32
- **Inclusion Findings**: 12
- **Verification Findings**: 24
- **Expiration Findings**: 16
- **Cross-file Findings**: 0

## Security Benefits

### 1. CSRF Prevention
- Ensures all forms and AJAX requests include nonce verification
- Detects missing nonce fields and verification
- Prevents unauthorized form submissions

### 2. Action-Specific Security
- Enforces unique, descriptive action names
- Detects generic or predictable action names
- Prevents nonce reuse across different actions

### 3. Proper Error Handling
- Ensures expired nonces are handled gracefully
- Detects information disclosure in error messages
- Promotes user-friendly security feedback

### 4. Cross-File Consistency
- Detects mismatched action names across files
- Ensures nonce creation and verification consistency
- Prevents verification failures due to action mismatches

## Best Practices

### 1. Action Naming
```php
// Good: Specific, descriptive action names
wp_create_nonce('delete_user_123_action');
wp_create_nonce('update_plugin_settings_action');

// Bad: Generic action names
wp_create_nonce('action');
wp_create_nonce('nonce');
```

### 2. Form Security
```php
// Good: Complete nonce lifecycle
<form method="post">
    <?php wp_nonce_field('save_data_action'); ?>
    <input type="text" name="data" />
    <input type="submit" value="Submit" />
</form>

<?php
if (isset($_POST['submit'])) {
    if (wp_verify_nonce($_POST['_wpnonce'], 'save_data_action')) {
        // Process data safely
    } else {
        wp_die('Security check failed');
    }
}
?>
```

### 3. AJAX Security
```php
// Good: AJAX with nonce verification
add_action('wp_ajax_my_action', 'my_ajax_handler');

function my_ajax_handler() {
    check_ajax_referer('my_action', 'nonce');
    $data = sanitize_text_field($_POST['data']);
    wp_send_json_success($data);
}
```

### 4. Error Handling
```php
// Good: Proper expiration handling
$result = wp_verify_nonce($_POST['_wpnonce'], 'action_name');

if ($result === false) {
    wp_die('Nonce expired. Please refresh the page and try again.');
} elseif ($result === 0) {
    wp_die('Invalid nonce. Security check failed.');
} else {
    // Process data safely
}
```

## Limitations

1. **Static Analysis**: These rules perform static code analysis and may not detect runtime issues
2. **False Positives**: Some legitimate patterns may trigger warnings
3. **Cross-File Analysis**: Limited to single-file analysis due to Semgrep constraints
4. **Dynamic Content**: May not detect nonces generated dynamically

## Future Enhancements

1. **Advanced Cross-File Analysis**: Implement more sophisticated cross-file detection
2. **Custom Action Validation**: Add rules for custom action name validation
3. **Performance Optimization**: Optimize rule patterns for better performance
4. **Integration**: Integrate with WordPress coding standards tools

## References

- [WordPress Nonces Documentation](https://developer.wordpress.org/plugins/security/nonces/)
- [CSRF Protection Best Practices](https://owasp.org/www-community/attacks/csrf)
- [Semgrep Documentation](https://semgrep.dev/docs/)
- [WordPress Security Handbook](https://developer.wordpress.org/plugins/security/)
