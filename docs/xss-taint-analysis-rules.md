# XSS Taint Analysis Rules

## Overview

The XSS Taint Analysis Rules are specialized Semgrep rules designed to detect Cross-Site Scripting (XSS) vulnerabilities in WordPress applications through taint analysis. These rules track tainted data from sources (user input) through the application to sinks (output functions), detecting when user input flows to output without proper sanitization.

## What is Taint Analysis?

Taint analysis is a security analysis technique that tracks data flow from untrusted sources (taint sources) to sensitive sinks (taint sinks) through an application. In the context of XSS, taint analysis helps identify when user input flows to output functions without proper sanitization, which could lead to XSS vulnerabilities.

## Rule Categories

### 1. XSS Taint Sources

Taint sources are locations where untrusted data enters the application. These include:

#### Direct User Input Sources
- `$_GET[...]` - GET parameters
- `$_POST[...]` - POST parameters  
- `$_REQUEST[...]` - REQUEST parameters
- `$_COOKIE[...]` - Cookie data

#### Database Sources
- `$wpdb->get_var(...)` - Database content
- `$wpdb->get_row(...)` - Database row
- `$wpdb->get_results(...)` - Database results

#### WordPress Function Sources
- `get_post_meta(...)` - Post meta data
- `get_option(...)` - WordPress options
- `get_user_meta(...)` - User meta data

#### File Content Sources
- `file_get_contents(...)` - File content
- `wp_remote_get(...)` - Remote content

### 2. XSS Taint Sinks

Taint sinks are output functions where XSS can occur if tainted data is not properly sanitized:

#### Direct Output Sinks
- `echo $tainted_data;` - Direct output
- `print $tainted_data;` - Print output
- `printf($tainted_data, ...);` - Formatted output

#### HTML Output Sinks
- `echo "<... " . $tainted_data . " ...>";` - HTML attributes
- `echo "<...>" . $tainted_data . "</...>";` - HTML content

#### JavaScript Output Sinks
- `echo "<script>..." . $tainted_data . "...</script>";` - JavaScript strings
- `echo "<script>var data = '" . $tainted_data . "';</script>";` - JavaScript variables

#### CSS Output Sinks
- `echo "<... style='..." . $tainted_data . "...'>";` - CSS styles

#### WordPress Output Sinks
- `echo wp_json_encode($tainted_data);` - JSON output
- `wp_send_json($tainted_data);` - AJAX JSON response

### 3. XSS Taint Sanitizers

Sanitizers are functions that clean tainted data, making it safe for output:

#### WordPress Escaping Functions
- `esc_html($tainted_data)` - HTML escaping
- `esc_attr($tainted_data)` - Attribute escaping
- `esc_js($tainted_data)` - JavaScript escaping
- `esc_url($tainted_data)` - URL escaping

#### WordPress Sanitization Functions
- `sanitize_text_field($tainted_data)` - Text field sanitization
- `sanitize_email($tainted_data)` - Email sanitization
- `sanitize_url($tainted_data)` - URL sanitization

#### WordPress Content Filtering
- `wp_kses_post($tainted_data)` - Post content filtering
- `wp_kses($tainted_data, ...)` - Content filtering with allowed tags

#### JSON Encoding
- `wp_json_encode($tainted_data)` - WordPress JSON encoding

#### Type Casting
- `(int)$tainted_data` - Integer casting
- `(float)$tainted_data` - Float casting

### 4. XSS Taint Flow Detection

Flow detection rules identify complete vulnerability paths from sources to sinks:

#### Direct User Input Flows
- User input → echo (without sanitization)
- User input → HTML output (without sanitization)
- User input → HTML attribute (without sanitization)
- User input → JavaScript (without sanitization)

#### Database Content Flows
- Database content → output (without sanitization)
- Post meta → output (without sanitization)
- WordPress option → output (without sanitization)

#### AJAX/REST Flows
- User input → AJAX response (without sanitization)
- User input → REST response (without sanitization)

## Usage

### Basic Configuration

The XSS taint analysis rules are included in the experimental pack:

```yaml
# Include XSS taint analysis rules
- packs/experimental/xss-taint-rules.yaml
```

### Rule Severity Levels

- **ERROR**: Direct XSS vulnerabilities (sources to sinks without sanitization)
- **INFO**: Taint sources, sinks, and sanitizers (for analysis)

### Configuration Comments

The rules include configuration comments for easy identification:

```php
// XSS_TAINT_SOURCES: $_GET, $_POST, $_REQUEST, $_COOKIE, $wpdb->get_*, get_post_meta, get_option, file_get_contents
// XSS_TAINT_SINKS: echo, print, printf, HTML output, JavaScript output, wp_send_json, wp_json_encode
// XSS_TAINT_SANITIZERS: esc_html, esc_attr, esc_js, esc_url, sanitize_text_field, wp_kses_post, wp_json_encode
```

## Examples

### Vulnerable Code (Will Trigger Rules)

```php
// Direct user input to output - VULNERABLE
$user_input = $_GET['input'];
echo $user_input;  // ❌ Triggers xss-taint-flow-user-to-echo

// User input to HTML - VULNERABLE
$user_input = $_POST['data'];
echo "<div>" . $user_input . "</div>";  // ❌ Triggers xss-taint-flow-user-to-html

// User input to JavaScript - VULNERABLE
$user_input = $_REQUEST['content'];
echo "<script>var data = '" . $user_input . "';</script>";  // ❌ Triggers xss-taint-flow-user-to-javascript

// Database content to output - VULNERABLE
$result = $wpdb->get_row("SELECT * FROM posts WHERE id = 1");
echo $result->content;  // ❌ Triggers xss-taint-flow-database-to-output

// AJAX response - VULNERABLE
$user_input = $_POST['ajax_data'];
wp_send_json_success($user_input);  // ❌ Triggers xss-taint-flow-user-to-ajax
```

### Safe Code (Will Not Trigger Rules)

```php
// Direct user input to output - SAFE
$user_input = $_GET['input'];
echo esc_html($user_input);  // ✅ Safe - properly sanitized

// User input to HTML - SAFE
$user_input = $_POST['data'];
echo "<div>" . esc_html($user_input) . "</div>";  // ✅ Safe - properly sanitized

// User input to JavaScript - SAFE
$user_input = $_REQUEST['content'];
echo "<script>var data = '" . esc_js($user_input) . "';</script>";  // ✅ Safe - properly sanitized

// Database content to output - SAFE
$result = $wpdb->get_row("SELECT * FROM posts WHERE id = 1");
echo esc_html($result->content);  // ✅ Safe - properly sanitized

// AJAX response - SAFE
$user_input = $_POST['ajax_data'];
wp_send_json_success(esc_html($user_input));  // ✅ Safe - properly sanitized
```

## Context-Specific Sanitization

Different output contexts require different sanitization methods:

### HTML Context
```php
$user_input = $_GET['input'];
echo "<div>" . esc_html($user_input) . "</div>";
```

### Attribute Context
```php
$user_input = $_POST['input'];
echo "<input value='" . esc_attr($user_input) . "'>";
```

### JavaScript Context
```php
$user_input = $_REQUEST['input'];
echo "<script>var data = '" . esc_js($user_input) . "';</script>";
```

### URL Context
```php
$user_input = $_GET['input'];
echo "<a href='" . esc_url($user_input) . "'>Link</a>";
```

### JSON Context
```php
$user_input = $_POST['input'];
echo wp_json_encode($user_input);
```

## Advanced Patterns

### Complex Variable Assignment
```php
// VULNERABLE
$temp_var = $_GET['input'];
$processed_var = $temp_var;
echo $processed_var;  // ❌ Taint flows through variable assignment

// SAFE
$temp_var = $_GET['input'];
$processed_var = esc_html($temp_var);
echo $processed_var;  // ✅ Sanitized before assignment
```

### Function Parameter Passing
```php
// VULNERABLE
function display_content($content) {
    echo $content;  // ❌ Taint flows through function parameter
}
$user_input = $_GET['content'];
display_content($user_input);

// SAFE
function display_content($content) {
    echo esc_html($content);  // ✅ Sanitized inside function
}
$user_input = $_GET['content'];
display_content($user_input);
```

### WordPress-Specific Patterns
```php
// Widget output - SAFE
function safe_widget_output($instance) {
    $title = esc_html($instance['title']);
    echo "<h2>" . $title . "</h2>";
}

// Shortcode output - SAFE
function safe_shortcode($atts) {
    $content = wp_kses_post($atts['content']);
    echo "<div>" . $content . "</div>";
}

// AJAX handler - SAFE
function safe_ajax_handler() {
    $user_input = sanitize_text_field($_POST['data']);
    wp_send_json_success($user_input);
}
```

## Testing

### Test Files

The XSS taint analysis rules include comprehensive test cases:

- `tests/vulnerable-examples/xss-taint-vulnerable.php` - Examples that should trigger rules
- `tests/safe-examples/xss-taint-safe.php` - Examples that should not trigger rules

### Running Tests

```bash
# Test vulnerable examples
semgrep --config packs/experimental/xss-taint-rules.yaml tests/vulnerable-examples/xss-taint-vulnerable.php

# Test safe examples
semgrep --config packs/experimental/xss-taint-rules.yaml tests/safe-examples/xss-taint-safe.php
```

## Integration with Other Rules

The XSS taint analysis rules work alongside other security rules:

- **XSS Prevention Rules**: Provide context-aware escaping guidance
- **Taint Analysis Framework**: General taint analysis infrastructure
- **WordPress Security Rules**: WordPress-specific security patterns

## Best Practices

### 1. Always Sanitize User Input
```php
// ❌ Never output user input directly
echo $_GET['input'];

// ✅ Always sanitize before output
echo esc_html($_GET['input']);
```

### 2. Use Context-Appropriate Sanitization
```php
// HTML content
echo esc_html($user_input);

// HTML attributes
echo esc_attr($user_input);

// JavaScript
echo esc_js($user_input);

// URLs
echo esc_url($user_input);
```

### 3. Sanitize Early
```php
// ✅ Sanitize as soon as possible
$user_input = esc_html($_GET['input']);
// ... use $user_input throughout the function

// ❌ Sanitize at the last moment
$user_input = $_GET['input'];
// ... use $user_input throughout the function
echo esc_html($user_input);
```

### 4. Use WordPress Functions
```php
// ✅ Use WordPress sanitization functions
echo sanitize_text_field($user_input);
echo wp_kses_post($user_input);

// ❌ Avoid manual sanitization
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
```

## Troubleshooting

### False Positives

If you encounter false positives:

1. **Check if data is actually user input**: Ensure the flagged variable contains user input
2. **Verify sanitization**: Make sure proper sanitization is applied
3. **Review context**: Ensure the output context matches the sanitization method

### False Negatives

If you encounter false negatives:

1. **Check rule coverage**: Ensure the specific pattern is covered by the rules
2. **Verify taint flow**: Ensure the taint analysis can track the data flow
3. **Review sanitization**: Ensure the sanitization method is recognized

### Performance Considerations

- Taint analysis can be computationally expensive
- Consider using incremental scanning for large codebases
- Use rule filtering to focus on specific vulnerability types

## Related Documentation

- [XSS Prevention Rules](xss-prevention-rules.md)
- [Taint Analysis Framework](taint-analysis-framework.md)
- [WordPress Security Best Practices](https://developer.wordpress.org/plugins/security/)
- [OWASP XSS Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
