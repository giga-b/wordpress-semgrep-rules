# SQL Injection Taint Analysis Rules Documentation

## Overview

The SQL Injection Taint Analysis Rules provide advanced detection capabilities for SQL injection vulnerabilities in WordPress applications. These rules go beyond simple pattern matching to track data flow from sources (where untrusted data enters) to sinks (where it can cause harm), providing more accurate and comprehensive vulnerability detection.

## Architecture

The SQL injection taint analysis system consists of four main components:

1. **Taint Sources** - Where untrusted data enters the application
2. **Taint Sinks** - Where tainted data can cause SQL injection vulnerabilities
3. **Taint Sanitizers** - Functions that clean tainted data
4. **Flow Detection** - Advanced patterns that detect taint flows

## Taint Sources

### WordPress User Input Sources

#### GET Parameters
```php
$_GET['id']           // User input from GET parameters
$_GET['name']         // User input from GET parameters
$_GET['email']        // User input from GET parameters
```

#### POST Parameters
```php
$_POST['id']          // User input from POST parameters
$_POST['name']        // User input from POST parameters
$_POST['email']       // User input from POST parameters
```

#### REQUEST Parameters
```php
$_REQUEST['id']       // User input from REQUEST parameters
$_REQUEST['name']     // User input from REQUEST parameters
$_REQUEST['email']    // User input from REQUEST parameters
```

#### Cookie Data
```php
$_COOKIE['user_id']   // User input from cookies
$_COOKIE['session']   // User input from cookies
```

### WordPress API Sources

#### REST API Parameters
```php
$request->get_param('id')     // REST API parameter input
$request->get_param('name')   // REST API parameter input
```

#### AJAX Handlers
```php
wp_ajax_my_handler()          // AJAX handler input
wp_ajax_nopriv_my_handler()   // AJAX handler input
```

### WordPress Database Sources

#### Query Results
```php
$wpdb->get_results($query)    // Database query results
$wpdb->get_row($query)        // Database row results
$wpdb->get_var($query)        // Database variable results
```

### WordPress Options and Metadata

#### Options
```php
get_option('user_data')       // WordPress option value
get_option('site_settings')   // WordPress option value
```

#### Metadata
```php
get_post_meta($post_id, 'field', true)    // Post metadata
get_user_meta($user_id, 'field', true)    // User metadata
```

### External API Sources

#### Remote Requests
```php
wp_remote_get('https://api.example.com/data')     // External API response
wp_remote_post('https://api.example.com/data')    // External API response
```

## Taint Sinks

### WordPress Database Query Sinks

#### Direct Queries
```php
$wpdb->query($tainted_data)           // Direct database query
$wpdb->prepare($tainted_data, ...)    // Prepared statement with tainted query
```

#### Query Methods
```php
$wpdb->get_results($tainted_data)     // Get results with tainted query
$wpdb->get_row($tainted_data)         // Get row with tainted query
$wpdb->get_var($tainted_data)         // Get variable with tainted query
$wpdb->get_col($tainted_data)         // Get column with tainted query
```

### WordPress Database Operations

#### CRUD Operations
```php
$wpdb->insert($table, $tainted_data)      // Insert with tainted data
$wpdb->update($table, $tainted_data, $where)  // Update with tainted data
$wpdb->delete($table, $tainted_data)      // Delete with tainted data
$wpdb->replace($table, $tainted_data)     // Replace with tainted data
```

### WordPress Function Sinks

#### WordPress Functions
```php
get_posts($tainted_data)     // Get posts with tainted arguments
get_users($tainted_data)     // Get users with tainted arguments
get_terms($tainted_data)     // Get terms with tainted arguments
```

## Taint Sanitizers

### WordPress SQL Sanitizers

#### SQL Escaping
```php
esc_sql($tainted_data)                   // WordPress SQL escaping
$wpdb->prepare($query, $tainted_data)    // WordPress prepared statement
$wpdb->esc_like($tainted_data)           // WordPress LIKE escaping
```

### WordPress Input Sanitizers

#### Text Sanitization
```php
sanitize_text_field($tainted_data)       // Text field sanitization
sanitize_email($tainted_data)            // Email sanitization
sanitize_url($tainted_data)              // URL sanitization
sanitize_file_name($tainted_data)        // Filename sanitization
```

### WordPress Validation Functions

#### Validation
```php
is_email($tainted_data)                  // Email validation
is_url($tainted_data)                    // URL validation
```

### Type Casting Sanitizers

#### Type Casting
```php
(int)$tainted_data                       // Integer casting
(float)$tainted_data                     // Float casting
(string)$tainted_data                    // String casting
(array)$tainted_data                     // Array casting
```

#### WordPress Type Functions
```php
intval($tainted_data)                    // Integer validation
floatval($tainted_data)                  // Float validation
strval($tainted_data)                    // String validation
```

## Flow Detection Patterns

### Direct Flow Detection

#### Direct User Input to Query
```php
// VULNERABLE: Direct flow from user input to query
$user_input = $_GET['id'];
$wpdb->query($user_input);
```

#### String Concatenation Flow
```php
// VULNERABLE: String concatenation flow
$user_input = $_POST['name'];
$sql = "SELECT * FROM users WHERE name = '$user_input'";
$wpdb->query($sql);
```

#### Dynamic Table Name Flow
```php
// VULNERABLE: Dynamic table name flow
$table_name = $_REQUEST['table'];
$wpdb->query("SELECT * FROM $table_name");
```

### WordPress Function Flow Detection

#### WordPress Function Flow
```php
// VULNERABLE: WordPress function flow
$user_input = $_GET['args'];
get_posts($user_input);
```

## Bypass Detection Patterns

### Obfuscated String Concatenation
```php
// VULNERABLE: Obfuscated concatenation
$user_input = $_GET['id'];
$sql = "SELECT * FROM users WHERE id = " . $user_input . "";
$wpdb->query($sql);
```

### Variable Assignment Bypass
```php
// VULNERABLE: Variable assignment bypass
$user_input = $_POST['name'];
$temp = $user_input;
$wpdb->query("SELECT * FROM users WHERE name = '$temp'");
```

### Function Call Bypass
```php
// VULNERABLE: Function call bypass
$user_input = $_GET['id'];
$processed = some_function($user_input);
$wpdb->query("SELECT * FROM users WHERE id = $processed");
```

## Safe Patterns

### Safe Prepared Statement Usage
```php
// SAFE: Proper prepared statement usage
$user_input = $_GET['id'];
$wpdb->prepare("SELECT * FROM users WHERE id = %d", $user_input);
```

### Safe Sanitization Usage
```php
// SAFE: Proper sanitization before query
$user_input = $_POST['name'];
$safe_input = esc_sql($user_input);
$wpdb->query("SELECT * FROM users WHERE name = '$safe_input'");
```

### Safe Type Casting Usage
```php
// SAFE: Proper type casting before query
$user_input = $_GET['id'];
$safe_input = (int)$user_input;
$wpdb->query("SELECT * FROM users WHERE id = $safe_input");
```

### Safe Validation Usage
```php
// SAFE: Proper validation before query
$user_input = $_POST['email'];
if (is_email($user_input)) {
    $wpdb->query("SELECT * FROM users WHERE email = '$user_input'");
}
```

## Usage

### Basic Configuration

Include the SQL injection taint rules in your configuration:

```yaml
# Include SQL injection taint rules
- configs:
  - packs/experimental/sql-injection-taint-rules.yaml
```

### Rule Categories

The rules are organized into several categories:

#### Source Detection Rules
- `sql-taint-source-*` - Identify SQL injection taint sources
- Severity: INFO
- Purpose: Track data entry points

#### Sink Detection Rules
- `sql-taint-sink-*` - Identify SQL injection taint sinks
- Severity: ERROR
- Purpose: Detect potential SQL injection vulnerabilities

#### Sanitizer Detection Rules
- `sql-taint-sanitizer-*` - Identify SQL injection sanitization functions
- Severity: INFO
- Purpose: Track data cleaning operations

#### Flow Detection Rules
- `sql-taint-flow-*` - Detect taint flows from sources to sinks
- Severity: ERROR
- Purpose: Identify actual SQL injection vulnerabilities

#### Bypass Detection Rules
- `sql-taint-bypass-*` - Detect SQL injection bypass attempts
- Severity: ERROR
- Purpose: Identify advanced evasion techniques

#### Safe Pattern Rules
- `sql-taint-safe-*` - Identify safe SQL injection prevention patterns
- Severity: INFO
- Purpose: Track proper security practices

## Vulnerability Examples

### SQL Injection via Direct Flow
```php
// VULNERABLE: Direct flow from user input to query
function vulnerable_direct_flow() {
    $user_input = $_GET['id'];
    $wpdb->query($user_input); // SQL injection vulnerability
}
```

### SQL Injection via String Concatenation
```php
// VULNERABLE: String concatenation in query
function vulnerable_string_concatenation() {
    $user_input = $_POST['name'];
    $sql = "SELECT * FROM users WHERE name = '$user_input'";
    $wpdb->query($sql); // SQL injection vulnerability
}
```

### SQL Injection via Dynamic Table Name
```php
// VULNERABLE: Dynamic table name
function vulnerable_dynamic_table() {
    $table_name = $_REQUEST['table'];
    $wpdb->query("SELECT * FROM $table_name"); // SQL injection vulnerability
}
```

## Safe Examples

### Safe Prepared Statement Usage
```php
// SAFE: Proper prepared statement usage
function safe_prepared_statement() {
    $user_input = $_GET['id'];
    $wpdb->prepare("SELECT * FROM users WHERE id = %d", $user_input); // Safe
}
```

### Safe Sanitization Usage
```php
// SAFE: Proper sanitization before query
function safe_sanitization() {
    $user_input = $_POST['name'];
    $safe_input = esc_sql($user_input);
    $wpdb->query("SELECT * FROM users WHERE name = '$safe_input'"); // Safe
}
```

### Safe Type Casting Usage
```php
// SAFE: Proper type casting before query
function safe_type_casting() {
    $user_input = $_GET['id'];
    $safe_input = (int)$user_input;
    $wpdb->query("SELECT * FROM users WHERE id = $safe_input"); // Safe
}
```

## Testing

### Test Cases

The SQL injection taint rules include comprehensive test cases:

#### Vulnerable Examples
- `tests/vulnerable-examples/sql-injection-taint-vulnerable.php`
- Contains examples that should trigger SQL injection taint analysis rules
- Demonstrates various SQL injection vulnerability patterns

#### Safe Examples
- `tests/safe-examples/sql-injection-taint-safe.php`
- Contains examples that should NOT trigger SQL injection taint analysis rules
- Demonstrates proper SQL injection prevention techniques

### Running Tests

```bash
# Run SQL injection taint analysis on vulnerable examples
semgrep --config packs/experimental/sql-injection-taint-rules.yaml tests/vulnerable-examples/sql-injection-taint-vulnerable.php

# Run SQL injection taint analysis on safe examples (should have minimal findings)
semgrep --config packs/experimental/sql-injection-taint-rules.yaml tests/safe-examples/sql-injection-taint-safe.php
```

## Configuration

### Rule Severity Levels

- **ERROR**: Actual SQL injection vulnerabilities (taint flows from sources to sinks)
- **WARNING**: Potential SQL injection issues (taint sinks without clear sources)
- **INFO**: Informational (taint sources and sanitizers)

### Customization

You can customize the SQL injection taint rules by:

1. **Adding Custom Sources**: Define additional SQL injection taint sources specific to your application
2. **Adding Custom Sinks**: Define additional SQL injection taint sinks for custom functions
3. **Adding Custom Sanitizers**: Define additional SQL injection sanitization functions
4. **Adjusting Severity**: Modify severity levels based on your risk tolerance

### Example Custom Rule

```yaml
- id: custom-sql-taint-source
  message: "Custom SQL injection taint source - API response"
  severity: INFO
  languages: [php]
  pattern: |
    custom_api_call(...)
  metadata:
    category: "sql-injection-taint"
    type: "source"
    description: "Custom API response data"
```

## Integration

### IDE Integration

The SQL injection taint rules integrate with:

- **VS Code**: Real-time scanning with Semgrep extension
- **Cursor**: Built-in Semgrep support
- **Other IDEs**: Via Semgrep CLI

### CI/CD Integration

```yaml
# GitHub Actions example
- name: Run SQL Injection Taint Analysis
  run: |
    semgrep --config packs/experimental/sql-injection-taint-rules.yaml \
            --json --output sql-injection-taint-results.json
```

### WordPress Integration

The rules are specifically designed for WordPress applications and include:

- WordPress-specific sanitization functions
- WordPress database operations
- WordPress API functions
- WordPress metadata functions

## Performance Considerations

### Optimization

- Use incremental scanning for large codebases
- Cache rule compilation results
- Filter rules based on your specific needs
- Use parallel processing for multiple files

### Memory Usage

- Monitor memory usage during large scans
- Use streaming for large files
- Implement memory limits for CI/CD environments

## Troubleshooting

### Common Issues

1. **False Positives**: Adjust rule patterns or add custom sanitizers
2. **Performance Issues**: Use incremental scanning and caching
3. **Missing Vulnerabilities**: Add custom sources and sinks
4. **Rule Conflicts**: Review rule priorities and dependencies

### Debugging

Enable debug mode for detailed analysis:

```bash
semgrep --config packs/experimental/sql-injection-taint-rules.yaml \
        --debug --verbose \
        your-file.php
```

## Best Practices

### 1. Always Use Prepared Statements
```php
// Good
$wpdb->prepare("SELECT * FROM users WHERE id = %d", $_GET['id']);

// Bad
$wpdb->query("SELECT * FROM users WHERE id = " . $_GET['id']);
```

### 2. Sanitize User Input
```php
// Good
$safe_input = esc_sql($_POST['name']);
$wpdb->query("SELECT * FROM users WHERE name = '$safe_input'");

// Bad
$wpdb->query("SELECT * FROM users WHERE name = '$_POST[name]'");
```

### 3. Validate Data Types
```php
// Good
$user_id = (int)$_GET['id'];
$wpdb->query("SELECT * FROM users WHERE id = $user_id");

// Bad
$wpdb->query("SELECT * FROM users WHERE id = $_GET[id]");
```

### 4. Use Whitelist Validation
```php
// Good
$allowed_tables = array('users', 'posts', 'comments');
if (in_array($_GET['table'], $allowed_tables)) {
    $wpdb->query("SELECT * FROM $_GET[table]");
}

// Bad
$wpdb->query("SELECT * FROM $_GET[table]");
```

### 5. Avoid Dynamic Table Names
```php
// Good
$wpdb->query("SELECT * FROM users");

// Bad
$wpdb->query("SELECT * FROM $_GET[table]");
```

## Future Enhancements

### Planned Features

1. **Advanced Taint Tracking**: Multi-step taint propagation
2. **Context-Aware Analysis**: Different rules for different contexts
3. **Custom Sanitizer Detection**: Automatic detection of custom sanitization functions
4. **Performance Optimization**: Improved scanning speed and memory usage
5. **Integration APIs**: Programmatic access to taint analysis results

### Contributing

To contribute to the SQL injection taint rules:

1. Review existing rules and test cases
2. Add new SQL injection vulnerability patterns
3. Improve documentation
4. Submit pull requests with comprehensive tests

## Conclusion

The SQL Injection Taint Analysis Rules provide comprehensive SQL injection vulnerability detection for WordPress applications. By identifying data flow vulnerabilities, they help developers write more secure code and prevent common SQL injection issues.

For more information, see:
- [WordPress Security Best Practices](https://developer.wordpress.org/plugins/security/)
- [OWASP SQL Injection](https://owasp.org/www-community/attacks/SQL_Injection)
- [Semgrep Documentation](https://semgrep.dev/docs/)
