# Taint Analysis Framework Documentation

## Overview

The Taint Analysis Framework is a comprehensive security analysis system designed to detect data flow vulnerabilities in WordPress applications. It identifies when untrusted data (tainted data) flows from sources to sinks without proper sanitization, potentially leading to security vulnerabilities.

## Architecture

The framework consists of three main components:

1. **Taint Sources** - Where untrusted data enters the application
2. **Taint Sinks** - Where tainted data can cause harm
3. **Taint Sanitizers** - Functions that clean tainted data

### Taint Sources

Taint sources are entry points where untrusted data enters the application:

#### User Input Sources
- `$_GET[...]` - GET parameters
- `$_POST[...]` - POST parameters
- `$_REQUEST[...]` - REQUEST parameters
- `$_COOKIE[...]` - Cookie data
- `$_SERVER[...]` - Server variables

#### File Content Sources
- `file_get_contents(...)` - File content
- `fread(...)` - File read operations

#### Database Query Sources
- `$wpdb->get_results(...)` - Database query results
- `$wpdb->get_row(...)` - Database row data

#### External API Sources
- `wp_remote_get(...)` - External API calls
- `curl_exec(...)` - cURL operations

### Taint Sinks

Taint sinks are locations where tainted data can cause security vulnerabilities:

#### XSS Sinks
- `echo $tainted_data` - Output functions
- `print $tainted_data` - Print statements
- `printf($tainted_data, ...)` - Formatted output

#### SQL Injection Sinks
- `$wpdb->query($tainted_data)` - Database queries
- `$wpdb->prepare($tainted_data, ...)` - Prepared statements

#### File Operation Sinks
- `include $tainted_data` - File inclusion
- `require $tainted_data` - File requirement
- `file_get_contents($tainted_data)` - File content reading

#### Command Execution Sinks
- `exec($tainted_data)` - Command execution
- `system($tainted_data)` - System commands
- `shell_exec($tainted_data)` - Shell execution

#### Header Injection Sinks
- `header($tainted_data)` - HTTP headers

### Taint Sanitizers

Taint sanitizers are functions that clean tainted data:

#### WordPress Sanitization Functions
- `sanitize_text_field($tainted_data)` - Text field sanitization
- `sanitize_email($tainted_data)` - Email sanitization
- `sanitize_url($tainted_data)` - URL sanitization
- `sanitize_file_name($tainted_data)` - Filename sanitization
- `esc_html($tainted_data)` - HTML escaping
- `esc_attr($tainted_data)` - Attribute escaping
- `esc_js($tainted_data)` - JavaScript escaping
- `esc_sql($tainted_data)` - SQL escaping

#### Type Casting Sanitizers
- `(int)$tainted_data` - Integer casting
- `(float)$tainted_data` - Float casting
- `(string)$tainted_data` - String casting
- `(array)$tainted_data` - Array casting

#### Validation Functions
- `is_numeric($tainted_data)` - Numeric validation
- `is_email($tainted_data)` - Email validation
- `is_url($tainted_data)` - URL validation

## Usage

### Basic Configuration

The taint analysis framework is included in the experimental pack:

```yaml
# Include taint analysis rules
- configs:
  - packs/experimental/taint-analysis-framework.yaml
```

### Rule Categories

The framework includes several rule categories:

#### Source Detection Rules
- `taint-source-*` - Identify taint sources
- Severity: INFO
- Purpose: Track data entry points

#### Sink Detection Rules
- `taint-sink-*` - Identify taint sinks
- Severity: ERROR
- Purpose: Detect potential vulnerabilities

#### Sanitizer Detection Rules
- `taint-sanitizer-*` - Identify sanitization functions
- Severity: INFO
- Purpose: Track data cleaning operations

#### Flow Detection Rules
- `taint-flow-*` - Detect taint flows from sources to sinks
- Severity: ERROR
- Purpose: Identify actual vulnerabilities

## Vulnerability Types

### XSS (Cross-Site Scripting)
**CWE**: CWE-79
**Description**: Tainted data flows to output without proper escaping
**Example**:
```php
$user_input = $_GET['name'];
echo $user_input; // Vulnerable
```

**Safe Example**:
```php
$user_input = $_GET['name'];
echo esc_html($user_input); // Safe
```

### SQL Injection
**CWE**: CWE-89
**Description**: Tainted data flows to database queries without proper preparation
**Example**:
```php
$user_id = $_POST['id'];
$wpdb->query("SELECT * FROM users WHERE id = " . $user_id); // Vulnerable
```

**Safe Example**:
```php
$user_id = $_POST['id'];
$wpdb->prepare("SELECT * FROM users WHERE id = %d", $user_id); // Safe
```

### File Inclusion
**CWE**: CWE-98
**Description**: Tainted data flows to file operations without proper validation
**Example**:
```php
$file_path = $_GET['file'];
include $file_path; // Vulnerable
```

**Safe Example**:
```php
$file_path = $_GET['file'];
$safe_path = sanitize_file_name($file_path);
if (file_exists($safe_path) && strpos($safe_path, '../') === false) {
    include $safe_path; // Safe
}
```

### Command Injection
**CWE**: CWE-78
**Description**: Tainted data flows to command execution without proper validation
**Example**:
```php
$command = $_POST['cmd'];
exec($command); // Vulnerable
```

**Safe Example**:
```php
$command = $_POST['cmd'];
$allowed_commands = ['ls', 'pwd', 'whoami'];
if (in_array($command, $allowed_commands)) {
    exec($command); // Safe
}
```

### Header Injection
**CWE**: CWE-113
**Description**: Tainted data flows to HTTP headers without proper validation
**Example**:
```php
$redirect_url = $_GET['redirect'];
header("Location: " . $redirect_url); // Vulnerable
```

**Safe Example**:
```php
$redirect_url = $_GET['redirect'];
$safe_url = sanitize_url($redirect_url);
if (filter_var($safe_url, FILTER_VALIDATE_URL)) {
    header("Location: " . $safe_url); // Safe
}
```

## Best Practices

### 1. Always Sanitize User Input
```php
// Bad
$user_input = $_GET['data'];
echo $user_input;

// Good
$user_input = $_GET['data'];
echo esc_html($user_input);
```

### 2. Use Prepared Statements for Database Queries
```php
// Bad
$wpdb->query("SELECT * FROM users WHERE id = " . $_POST['id']);

// Good
$wpdb->prepare("SELECT * FROM users WHERE id = %d", $_POST['id']);
```

### 3. Validate File Paths
```php
// Bad
include $_GET['file'];

// Good
$file_path = sanitize_file_name($_GET['file']);
if (file_exists($file_path) && strpos($file_path, '../') === false) {
    include $file_path;
}
```

### 4. Use Whitelisting for Commands
```php
// Bad
exec($_POST['command']);

// Good
$allowed_commands = ['backup', 'cleanup', 'optimize'];
if (in_array($_POST['command'], $allowed_commands)) {
    exec($_POST['command']);
}
```

### 5. Validate URLs
```php
// Bad
header("Location: " . $_GET['redirect']);

// Good
$safe_url = sanitize_url($_GET['redirect']);
if (filter_var($safe_url, FILTER_VALIDATE_URL)) {
    header("Location: " . $safe_url);
}
```

## Testing

### Test Cases

The framework includes comprehensive test cases:

#### Vulnerable Examples
- `tests/vulnerable-examples/taint-analysis-vulnerable.php`
- Contains examples that should trigger taint analysis rules
- Demonstrates various vulnerability patterns

#### Safe Examples
- `tests/safe-examples/taint-analysis-safe.php`
- Contains examples that should NOT trigger taint analysis rules
- Demonstrates proper sanitization patterns

### Running Tests

```bash
# Run taint analysis on vulnerable examples
semgrep --config packs/experimental/taint-analysis-framework.yaml tests/vulnerable-examples/taint-analysis-vulnerable.php

# Run taint analysis on safe examples (should have minimal findings)
semgrep --config packs/experimental/taint-analysis-framework.yaml tests/safe-examples/taint-analysis-safe.php
```

## Configuration

### Rule Severity Levels

- **ERROR**: Actual vulnerabilities (taint flows from sources to sinks)
- **WARNING**: Potential issues (taint sinks without clear sources)
- **INFO**: Informational (taint sources and sanitizers)

### Customization

You can customize the framework by:

1. **Adding Custom Sources**: Define additional taint sources specific to your application
2. **Adding Custom Sinks**: Define additional taint sinks for custom functions
3. **Adding Custom Sanitizers**: Define additional sanitization functions
4. **Adjusting Severity**: Modify severity levels based on your risk tolerance

### Example Custom Rule

```yaml
- id: custom-taint-source
  message: "Custom taint source - API response"
  severity: INFO
  languages: [php]
  pattern: |
    custom_api_call(...)
  metadata:
    category: "taint-analysis"
    type: "source"
    description: "Custom API response data"
```

## Integration

### IDE Integration

The taint analysis framework integrates with:

- **VS Code**: Real-time scanning with Semgrep extension
- **Cursor**: Built-in Semgrep support
- **Other IDEs**: Via Semgrep CLI

### CI/CD Integration

```yaml
# GitHub Actions example
- name: Run Taint Analysis
  run: |
    semgrep --config packs/experimental/taint-analysis-framework.yaml \
            --json --output taint-analysis-results.json
```

### WordPress Integration

The framework is specifically designed for WordPress applications and includes:

- WordPress-specific sanitization functions
- WordPress database operations
- WordPress file operations
- WordPress API functions

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
semgrep --config packs/experimental/taint-analysis-framework.yaml \
        --debug --verbose \
        your-file.php
```

## Future Enhancements

### Planned Features

1. **Advanced Taint Tracking**: Multi-step taint propagation
2. **Context-Aware Analysis**: Different rules for different contexts
3. **Custom Sanitizer Detection**: Automatic detection of custom sanitization functions
4. **Performance Optimization**: Improved scanning speed and memory usage
5. **Integration APIs**: Programmatic access to taint analysis results

### Contributing

To contribute to the taint analysis framework:

1. Review existing rules and test cases
2. Add new vulnerability patterns
3. Improve documentation
4. Submit pull requests with comprehensive tests

## Conclusion

The Taint Analysis Framework provides comprehensive security analysis for WordPress applications. By identifying data flow vulnerabilities, it helps developers write more secure code and prevent common security issues.

For more information, see:
- [WordPress Security Best Practices](https://developer.wordpress.org/plugins/security/)
- [OWASP Top Ten](https://owasp.org/www-project-top-ten/)
- [Semgrep Documentation](https://semgrep.dev/docs/)
