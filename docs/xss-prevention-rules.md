# XSS Prevention Rules Documentation

## Overview

The XSS Prevention Rules are designed to detect Cross-Site Scripting (XSS) vulnerabilities in WordPress applications by identifying unsafe output patterns and enforcing proper escaping and sanitization practices.

## Rule Categories

### 1. HTML Context Rules

#### `wordpress.xss.unsafe-html-output`
- **Description**: Detects direct output of user input in HTML context without proper escaping
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: Direct output of `$_GET`, `$_POST`, or `$_REQUEST` data without escaping
- **Safe Alternative**: Use `esc_html()`, `wp_kses_post()`, or `sanitize_text_field()`

```php
// Vulnerable
$user_input = $_GET['input'];
echo $user_input;

// Safe
$user_input = esc_html($_GET['input']);
echo $user_input;
```

### 2. HTML Attribute Context Rules

#### `wordpress.xss.unsafe-attribute`
- **Description**: Detects user input used in HTML attributes without proper escaping
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: User input in HTML attributes without `esc_attr()`
- **Safe Alternative**: Use `esc_attr()` or `sanitize_text_field()`

```php
// Vulnerable
$value = $_GET['value'];
echo "<input value='" . $value . "'>";

// Safe
$value = esc_attr($_GET['value']);
echo "<input value='" . $value . "'>";
```

### 3. URL Context Rules

#### `wordpress.xss.unsafe-url`
- **Description**: Detects user input used as URLs without proper validation
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: User input in URL attributes without `esc_url()`
- **Safe Alternative**: Use `esc_url()` or `esc_url_raw()`

```php
// Vulnerable
$url = $_GET['url'];
echo "<a href='" . $url . "'>Link</a>";

// Safe
$url = esc_url($_GET['url']);
echo "<a href='" . $url . "'>Link</a>";
```

### 4. JavaScript Context Rules

#### `wordpress.xss.unsafe-javascript`
- **Description**: Detects user input output in JavaScript context without proper escaping
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: User input in JavaScript without `esc_js()` or `wp_json_encode()`
- **Safe Alternative**: Use `esc_js()` or `wp_json_encode()`

```php
// Vulnerable
$data = $_GET['data'];
echo "<script>var data = '" . $data . "';</script>";

// Safe
$data = esc_js($_GET['data']);
echo "<script>var data = '" . $data . "';</script>";
```

### 5. CSS Context Rules

#### `wordpress.xss.unsafe-css`
- **Description**: Detects user input used in CSS context without proper validation
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: User input in CSS without `sanitize_hex_color()` or `esc_attr()`
- **Safe Alternative**: Use `sanitize_hex_color()` or `esc_attr()`

```php
// Vulnerable
$color = $_GET['color'];
echo "<div style='color: " . $color . "'>Content</div>";

// Safe
$color = sanitize_hex_color($_GET['color']);
echo "<div style='color: " . $color . "'>Content</div>";
```

### 6. Form Input Context Rules

#### `wordpress.xss.unsafe-form-value`
- **Description**: Detects user input used as form values without proper escaping
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: User input in form values without `esc_attr()`
- **Safe Alternative**: Use `esc_attr()` or `sanitize_text_field()`

```php
// Vulnerable
$value = $_POST['value'];
echo "<input type='text' value='" . $value . "'>";

// Safe
$value = esc_attr($_POST['value']);
echo "<input type='text' value='" . $value . "'>";
```

### 7. Content Context Rules

#### `wordpress.xss.unsafe-content`
- **Description**: Detects user input output as content without proper sanitization
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: User input in content without `wp_kses_post()` or `esc_html()`
- **Safe Alternative**: Use `wp_kses_post()` or `esc_html()`

```php
// Vulnerable
$content = $_POST['content'];
echo "<div class='content'>" . $content . "</div>";

// Safe
$content = wp_kses_post($_POST['content']);
echo "<div class='content'>" . $content . "</div>";
```

### 8. Database Output Rules

#### `wordpress.xss.unsafe-db-output`
- **Description**: Detects database query results output without proper escaping
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: Database results output without `esc_html()` or `wp_kses_post()`
- **Safe Alternative**: Use `esc_html()` or `wp_kses_post()`

```php
// Vulnerable
$result = $wpdb->get_row("SELECT * FROM posts WHERE id = 1");
echo "<h1>" . $result->title . "</h1>";

// Safe
$result = $wpdb->get_row("SELECT * FROM posts WHERE id = 1");
echo "<h1>" . esc_html($result->title) . "</h1>";
```

### 9. AJAX Response Rules

#### `wordpress.xss.unsafe-ajax-output`
- **Description**: Detects AJAX responses with unescaped user data
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: AJAX responses without `wp_json_encode()` or `esc_html()`
- **Safe Alternative**: Use `wp_json_encode()` or `esc_html()`

```php
// Vulnerable
$data = $_POST['data'];
wp_send_json_success($data);

// Safe
$data = sanitize_text_field($_POST['data']);
wp_send_json_success($data);
```

### 10. REST API Response Rules

#### `wordpress.xss.unsafe-rest-output`
- **Description**: Detects REST API responses with unescaped user data
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: REST responses without `wp_json_encode()` or `esc_html()`
- **Safe Alternative**: Use `wp_json_encode()` or `esc_html()`

```php
// Vulnerable
$content = $_POST['content'];
return new WP_REST_Response(['content' => $content], 200);

// Safe
$content = wp_kses_post($_POST['content']);
return new WP_REST_Response(['content' => $content], 200);
```

### 11. Template Context Rules

#### `wordpress.xss.unsafe-template`
- **Description**: Detects template variables output without proper escaping
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: Template variables without `esc_html()` or `wp_kses_post()`
- **Safe Alternative**: Use `esc_html()` or `wp_kses_post()`

```php
// Vulnerable
$title = get_post_meta($post_id, 'custom_title', true);
echo "<h1>" . $title . "</h1>";

// Safe
$title = esc_html(get_post_meta($post_id, 'custom_title', true));
echo "<h1>" . $title . "</h1>";
```

### 12. Widget Output Rules

#### `wordpress.xss.unsafe-widget`
- **Description**: Detects widget content output without proper escaping
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: Widget content without `esc_html()` or `wp_kses_post()`
- **Safe Alternative**: Use `esc_html()` or `wp_kses_post()`

```php
// Vulnerable
$widget_text = $instance['text'];
echo "<div class='widget-content'>" . $widget_text . "</div>";

// Safe
$widget_text = wp_kses_post($instance['text']);
echo "<div class='widget-content'>" . $widget_text . "</div>";
```

### 13. Shortcode Output Rules

#### `wordpress.xss.unsafe-shortcode`
- **Description**: Detects shortcode content output without proper escaping
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: Shortcode content without `esc_html()` or `wp_kses_post()`
- **Safe Alternative**: Use `esc_html()` or `wp_kses_post()`

```php
// Vulnerable
$content = $atts['content'];
return "<div class='shortcode'>" . $content . "</div>";

// Safe
$content = wp_kses_post($atts['content']);
return "<div class='shortcode'>" . $content . "</div>";
```

### 14. Admin Output Rules

#### `wordpress.xss.unsafe-admin`
- **Description**: Detects admin page content output without proper escaping
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: Admin content without `esc_html()` or `wp_kses_post()`
- **Safe Alternative**: Use `esc_html()` or `wp_kses_post()`

```php
// Vulnerable
$admin_message = $_GET['message'];
echo "<div class='notice'>" . $admin_message . "</div>";

// Safe
$admin_message = esc_html($_GET['message']);
echo "<div class='notice'>" . $admin_message . "</div>";
```

### 15. Email Content Rules

#### `wordpress.xss.unsafe-email`
- **Description**: Detects email content with unescaped user data
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: Email content without `esc_html()` or `wp_kses_post()`
- **Safe Alternative**: Use `esc_html()` or `wp_kses_post()`

```php
// Vulnerable
$email_content = $_POST['email_content'];
wp_mail($to, $subject, $email_content);

// Safe
$email_content = esc_html($_POST['email_content']);
wp_mail($to, $subject, $email_content);
```

### 16. Logging Rules

#### `wordpress.xss.unsafe-logging`
- **Description**: Detects log messages with unescaped user data
- **Severity**: WARNING
- **CWE**: CWE-79
- **Pattern**: Log messages without `esc_html()` or `sanitize_text_field()`
- **Safe Alternative**: Use `esc_html()` or `sanitize_text_field()`

```php
// Vulnerable
$user_data = $_POST['data'];
error_log("User submitted: " . $user_data);

// Safe
$user_data = esc_html($_POST['data']);
error_log("User submitted: " . $user_data);
```

### 17. File Content Rules

#### `wordpress.xss.unsafe-file-output`
- **Description**: Detects file content output without proper escaping
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: File content without `esc_html()`
- **Safe Alternative**: Use `esc_html()`

```php
// Vulnerable
$file_content = file_get_contents($file_path);
echo "<pre>" . $file_content . "</pre>";

// Safe
$file_content = esc_html(file_get_contents($file_path));
echo "<pre>" . $file_content . "</pre>";
```

### 18. Comment Content Rules

#### `wordpress.xss.unsafe-comment`
- **Description**: Detects comment content output without proper escaping
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: Comment content without `esc_html()` or `wp_kses_post()`
- **Safe Alternative**: Use `esc_html()` or `wp_kses_post()`

```php
// Vulnerable
$comment_content = $comment->comment_content;
echo "<div class='comment'>" . $comment_content . "</div>";

// Safe
$comment_content = wp_kses_post($comment->comment_content);
echo "<div class='comment'>" . $comment_content . "</div>";
```

### 19. Meta Data Rules

#### `wordpress.xss.unsafe-meta`
- **Description**: Detects meta tag content output without proper escaping
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: Meta content without `esc_attr()`
- **Safe Alternative**: Use `esc_attr()`

```php
// Vulnerable
$meta_description = get_post_meta($post_id, 'description', true);
echo "<meta name='description' content='" . $meta_description . "'>";

// Safe
$meta_description = esc_attr(get_post_meta($post_id, 'description', true));
echo "<meta name='description' content='" . $meta_description . "'>";
```

### 20. JSON Output Rules

#### `wordpress.xss.unsafe-json`
- **Description**: Detects JSON output with unescaped user data
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: JSON output without `wp_json_encode()`
- **Safe Alternative**: Use `wp_json_encode()`

```php
// Vulnerable
$data = $_POST['data'];
echo json_encode(['result' => $data]);

// Safe
$data = sanitize_text_field($_POST['data']);
echo wp_json_encode(['result' => $data]);
```

### 21. XML Output Rules

#### `wordpress.xss.unsafe-xml`
- **Description**: Detects XML output with unescaped user data
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: XML output without `esc_html()`
- **Safe Alternative**: Use `esc_html()`

```php
// Vulnerable
$title = $_POST['title'];
echo "<title>" . $title . "</title>";

// Safe
$title = esc_html($_POST['title']);
echo "<title>" . $title . "</title>";
```

### 22. RSS Feed Rules

#### `wordpress.xss.unsafe-rss`
- **Description**: Detects RSS feed output with unescaped user data
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: RSS output without `esc_html()`
- **Safe Alternative**: Use `esc_html()`

```php
// Vulnerable
$rss_title = $_POST['rss_title'];
echo "<title>" . $rss_title . "</title>";

// Safe
$rss_title = esc_html($_POST['rss_title']);
echo "<title>" . $rss_title . "</title>";
```

### 23. API Response Headers Rules

#### `wordpress.xss.unsafe-headers`
- **Description**: Detects HTTP headers with unescaped user data
- **Severity**: ERROR
- **CWE**: CWE-79
- **Pattern**: Headers without `esc_html()` or `sanitize_text_field()`
- **Safe Alternative**: Use `esc_html()` or `sanitize_text_field()`

```php
// Vulnerable
$header_value = $_GET['header'];
header("X-Custom-Header: " . $header_value);

// Safe
$header_value = esc_html($_GET['header']);
header("X-Custom-Header: " . $header_value);
```

### 24. Debug Output Rules

#### `wordpress.xss.unsafe-debug`
- **Description**: Detects debug output with unescaped user data
- **Severity**: WARNING
- **CWE**: CWE-79
- **Pattern**: Debug output without `esc_html()`
- **Safe Alternative**: Use `esc_html()`

```php
// Vulnerable
$debug_data = $_POST['debug'];
var_dump($debug_data);

// Safe
$debug_data = esc_html($_POST['debug']);
var_dump($debug_data);
```

## WordPress Escaping Functions

### Primary Escaping Functions

1. **`esc_html()`** - Escapes HTML special characters
   - Use for: HTML content, text output
   - Converts: `<`, `>`, `&`, `"`, `'` to HTML entities

2. **`esc_attr()`** - Escapes HTML attributes
   - Use for: HTML attribute values
   - Converts: `<`, `>`, `&`, `"`, `'` to HTML entities

3. **`esc_url()`** - Validates and escapes URLs
   - Use for: URLs in HTML attributes
   - Validates URL format and escapes special characters

4. **`esc_js()`** - Escapes JavaScript strings
   - Use for: JavaScript string literals
   - Escapes quotes and special characters

5. **`wp_kses_post()`** - Allows safe HTML tags
   - Use for: Content that should allow some HTML
   - Strips unsafe tags and attributes

### Sanitization Functions

1. **`sanitize_text_field()`** - Sanitizes text input
   - Use for: Single line text input
   - Removes HTML tags and normalizes whitespace

2. **`sanitize_email()`** - Sanitizes email addresses
   - Use for: Email input validation
   - Validates email format

3. **`sanitize_url()`** - Sanitizes URLs
   - Use for: URL input validation
   - Validates URL format

4. **`sanitize_hex_color()`** - Sanitizes hex colors
   - Use for: CSS color values
   - Validates hex color format

5. **`sanitize_file_name()`** - Sanitizes file names
   - Use for: File upload names
   - Removes unsafe characters

### JSON and Data Functions

1. **`wp_json_encode()`** - Safe JSON encoding
   - Use for: JSON responses
   - Handles encoding and escaping

2. **`wp_kses()`** - Custom HTML filtering
   - Use for: Custom HTML allowance
   - Allows specific tags and attributes

## Best Practices

### 1. Always Escape Output
- Never trust user input
- Always escape data before output
- Use context-appropriate escaping functions

### 2. Validate Input
- Validate input data types and formats
- Use WordPress sanitization functions
- Implement proper input validation

### 3. Use Prepared Statements
- Use `$wpdb->prepare()` for database queries
- Avoid string concatenation in SQL
- Parameterize all user input

### 4. Content Security Policy
- Implement CSP headers
- Restrict script sources
- Use nonces for forms

### 5. Regular Security Audits
- Regularly scan for XSS vulnerabilities
- Test with malicious input
- Keep WordPress and plugins updated

## Testing

### Test Cases
- **Vulnerable Examples**: `tests/vulnerable-examples/xss-vulnerable.php`
- **Safe Examples**: `tests/safe-examples/xss-safe.php`

### Manual Testing
1. Test with script tags: `<script>alert('xss')</script>`
2. Test with event handlers: `" onmouseover="alert('xss')`
3. Test with JavaScript URLs: `javascript:alert('xss')`
4. Test with CSS expressions: `expression(alert('xss'))`

### Automated Testing
- Run Semgrep scans on test files
- Verify vulnerable patterns are detected
- Verify safe patterns are not flagged

## Configuration

### Rule Severity Levels
- **ERROR**: Critical security issues
- **WARNING**: Potential security issues
- **INFO**: Informational messages

### Customization
- Modify rule patterns for specific needs
- Add custom escaping functions
- Adjust severity levels as needed

## Integration

### CI/CD Pipeline
- Integrate with GitHub Actions
- Run scans on pull requests
- Block merges with critical issues

### IDE Integration
- Real-time scanning in editors
- Inline error highlighting
- Quick-fix suggestions

## Troubleshooting

### Common Issues
1. **False Positives**: Adjust pattern specificity
2. **Missed Vulnerabilities**: Add additional patterns
3. **Performance**: Optimize rule complexity

### Debugging
1. Check rule syntax
2. Verify pattern matching
3. Test with sample code
4. Review rule documentation

## References

- [WordPress Data Validation](https://developer.wordpress.org/plugins/security/data-validation/)
- [WordPress Data Sanitization](https://developer.wordpress.org/plugins/security/data-sanitization/)
- [WordPress Data Escaping](https://developer.wordpress.org/plugins/security/data-escaping/)
- [OWASP XSS Prevention](https://owasp.org/www-project-cheat-sheets/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html)
- [CWE-79: Cross-site Scripting](https://cwe.mitre.org/data/definitions/79.html)
