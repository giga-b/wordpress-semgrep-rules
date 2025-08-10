# SQL Injection Rules Documentation

## Overview

The SQL injection rules in `packs/wp-core-security/sql-injection.yaml` provide comprehensive detection of SQL injection vulnerabilities in WordPress applications. These rules cover various attack vectors and patterns commonly found in WordPress plugins and themes.

## Rule Categories

### 1. Direct SQL Query with User Input
**Rule IDs**: `wordpress.sql-injection.direct-query-1`, `wordpress.sql-injection.direct-query-2`, `wordpress.sql-injection.direct-query-3`

**Description**: Detects direct SQL queries that concatenate user input without proper sanitization.

**Vulnerable Pattern**:
```php
$user_input = $_GET['id'];
$sql = "SELECT * FROM {$wpdb->users} WHERE ID = " . $user_input;
$results = $wpdb->get_results($sql);
```

**Safe Pattern**:
```php
$user_input = intval($_GET['id']);
$results = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID = %d", $user_input)
);
```

### 2. String Concatenation in SQL Queries
**Rule IDs**: `wordpress.sql-injection.string-concatenation-1`, `wordpress.sql-injection.string-concatenation-2`, `wordpress.sql-injection.string-concatenation-3`

**Description**: Detects SQL queries that use string concatenation with user input in quoted strings.

**Vulnerable Pattern**:
```php
$search = $_GET['search'];
$wpdb->query("SELECT * FROM posts WHERE title LIKE '%$search%'");
```

**Safe Pattern**:
```php
$search = sanitize_text_field($_GET['search']);
$wpdb->prepare("SELECT * FROM posts WHERE title LIKE %s", '%' . $wpdb->esc_like($search) . '%');
```

### 3. Dynamic Table Names
**Rule IDs**: `wordpress.sql-injection.dynamic-table-1`, `wordpress.sql-injection.dynamic-table-2`, `wordpress.sql-injection.dynamic-table-3`

**Description**: Detects dynamic table names from user input without proper validation.

**Vulnerable Pattern**:
```php
$table = $_POST['table'];
$wpdb->query("SELECT * FROM " . $table . " WHERE status = 'active'");
```

**Safe Pattern**:
```php
$allowed_tables = array('posts', 'users', 'comments');
if (in_array($_POST['table'], $allowed_tables)) {
    $table = $_POST['table'];
    $wpdb->query("SELECT * FROM " . $table);
}
```

### 4. Misuse of prepare with String Concatenation
**Rule IDs**: `wordpress.sql-injection.prepare-concatenation-1`, `wordpress.sql-injection.prepare-concatenation-2`, `wordpress.sql-injection.prepare-concatenation-3`

**Description**: Detects misuse of `$wpdb->prepare()` where user input is concatenated into the SQL string.

**Vulnerable Pattern**:
```php
$table_name = $_POST['table'];
$wpdb->prepare("SELECT * FROM " . $table_name . " WHERE id = %s", $user_id);
```

**Safe Pattern**:
```php
$wpdb->prepare("SELECT * FROM posts WHERE id = %d", $post_id);
```

### 5. Direct Query Execution
**Rule IDs**: `wordpress.sql-injection.direct-execution-1`, `wordpress.sql-injection.direct-execution-2`, `wordpress.sql-injection.direct-execution-3`

**Description**: Detects direct execution of SQL queries from user input.

**Vulnerable Pattern**:
```php
$sql = $_POST['sql'];
$wpdb->query($sql);
```

**Safe Pattern**:
```php
$wpdb->query("SELECT * FROM posts WHERE status = 'publish'");
```

### 6. Unsafe Database Operations
**Rule IDs**: `wordpress.sql-injection.unsafe-insert-1`, `wordpress.sql-injection.unsafe-update-1`, `wordpress.sql-injection.unsafe-update-2`, `wordpress.sql-injection.unsafe-delete-1`, `wordpress.sql-injection.unsafe-delete-2`

**Description**: Detects unsafe database insert, update, and delete operations with unsanitized user input.

**Vulnerable Pattern**:
```php
$title = $_POST['title'];
$content = $_POST['content'];
$wpdb->insert('posts', array(
    'title' => $title,
    'content' => $content
));
```

**Safe Pattern**:
```php
$title = sanitize_text_field($_POST['title']);
$content = wp_kses_post($_POST['content']);
$wpdb->insert('posts', array(
    'title' => $title,
    'content' => $content
));
```

### 7. Unsafe WHERE Clauses
**Rule IDs**: `wordpress.sql-injection.unsafe-where-1`, `wordpress.sql-injection.unsafe-where-2`

**Description**: Detects unsafe WHERE clauses with unsanitized user input.

**Vulnerable Pattern**:
```php
$status = $_POST['status'];
$wpdb->query("SELECT * FROM posts WHERE status = '$status'");
```

**Safe Pattern**:
```php
$status = sanitize_text_field($_POST['status']);
$wpdb->prepare("SELECT * FROM posts WHERE status = %s", $status);
```

### 8. Unsafe ORDER BY Clauses
**Rule IDs**: `wordpress.sql-injection.unsafe-orderby-1`, `wordpress.sql-injection.unsafe-orderby-2`

**Description**: Detects unsafe ORDER BY clauses with user input.

**Vulnerable Pattern**:
```php
$order = $_GET['order'];
$wpdb->query("SELECT * FROM posts ORDER BY $order");
```

**Safe Pattern**:
```php
$allowed_columns = array('title', 'date', 'author');
if (in_array($_GET['order'], $allowed_columns)) {
    $order = $_GET['order'];
    $wpdb->query("SELECT * FROM posts ORDER BY $order");
}
```

### 9. Unsafe LIMIT Clauses
**Rule IDs**: `wordpress.sql-injection.unsafe-limit-1`, `wordpress.sql-injection.unsafe-limit-2`

**Description**: Detects unsafe LIMIT clauses with unsanitized user input.

**Vulnerable Pattern**:
```php
$limit = $_GET['limit'];
$wpdb->query("SELECT * FROM posts LIMIT $limit");
```

**Safe Pattern**:
```php
$limit = intval($_GET['limit']);
$wpdb->prepare("SELECT * FROM posts LIMIT %d", $limit);
```

### 10. Unsafe LIKE Patterns
**Rule IDs**: `wordpress.sql-injection.unsafe-like-1`, `wordpress.sql-injection.unsafe-like-2`

**Description**: Detects unsafe LIKE patterns with unsanitized user input.

**Vulnerable Pattern**:
```php
$search = $_GET['search'];
$wpdb->query("SELECT * FROM posts WHERE title LIKE '%$search%'");
```

**Safe Pattern**:
```php
$search = sanitize_text_field($_GET['search']);
$wpdb->prepare("SELECT * FROM posts WHERE title LIKE %s", '%' . $wpdb->esc_like($search) . '%');
```

### 11. Unsafe IN Clauses
**Rule IDs**: `wordpress.sql-injection.unsafe-in-1`, `wordpress.sql-injection.unsafe-in-2`

**Description**: Detects unsafe IN clauses with unsanitized user input.

**Vulnerable Pattern**:
```php
$ids = $_POST['ids'];
$wpdb->query("SELECT * FROM posts WHERE ID IN ($ids)");
```

**Safe Pattern**:
```php
$ids = array_map('intval', $_POST['ids']);
$placeholders = implode(',', array_fill(0, count($ids), '%d'));
$wpdb->prepare("SELECT * FROM posts WHERE ID IN ($placeholders)", $ids);
```

### 12. Unsafe Subqueries
**Rule IDs**: `wordpress.sql-injection.unsafe-subquery-1`, `wordpress.sql-injection.unsafe-subquery-2`

**Description**: Detects unsafe subqueries with unsanitized user input.

**Vulnerable Pattern**:
```php
$user_id = $_GET['user_id'];
$wpdb->query("SELECT * FROM posts WHERE author IN (SELECT ID FROM users WHERE ID = $user_id)");
```

**Safe Pattern**:
```php
$user_id = intval($_GET['user_id']);
$wpdb->prepare("SELECT * FROM posts WHERE author IN (SELECT ID FROM users WHERE ID = %d)", $user_id);
```

### 13. Missing Type Casting for Numeric Input
**Rule IDs**: `wordpress.sql-injection.missing-type-cast-1`, `wordpress.sql-injection.missing-type-cast-2`

**Description**: Detects numeric user input that is not type cast before database operations.

**Vulnerable Pattern**:
```php
$id = $_GET['id'];
$wpdb->prepare("SELECT * FROM posts WHERE ID = %d", $id);
```

**Safe Pattern**:
```php
$id = intval($_GET['id']);
$wpdb->prepare("SELECT * FROM posts WHERE ID = %d", $id);
```

### 14. Unsafe Database Schema Operations
**Rule IDs**: `wordpress.sql-injection.unsafe-schema-1`, `wordpress.sql-injection.unsafe-schema-2`, `wordpress.sql-injection.unsafe-schema-3`

**Description**: Detects unsafe database schema operations with user input.

**Vulnerable Pattern**:
```php
$column = $_POST['column'];
$wpdb->query("ALTER TABLE posts ADD COLUMN $column VARCHAR(255)");
```

### 15. Unsafe Database Functions
**Rule IDs**: `wordpress.sql-injection.unsafe-functions-1`, `wordpress.sql-injection.unsafe-functions-2`

**Description**: Detects unsafe database functions with unsanitized user input.

**Vulnerable Pattern**:
```php
$value = $_POST['value'];
$wpdb->query("SELECT COUNT(*) FROM posts WHERE title = '$value'");
```

**Safe Pattern**:
```php
$value = sanitize_text_field($_POST['value']);
$wpdb->prepare("SELECT COUNT(*) FROM posts WHERE title = %s", $value);
```

## Best Practices

1. **Always use `$wpdb->prepare()`** for any user input in SQL queries
2. **Use appropriate placeholders**: `%s` for strings, `%d` for integers, `%f` for floats
3. **Type cast numeric input** using `intval()` or `floatval()`
4. **Use `$wpdb->esc_like()`** for LIKE patterns
5. **Validate table names** against a whitelist
6. **Sanitize user input** before database operations
7. **Avoid dynamic schema operations** with user input

## Testing

The rules have been tested against:
- **Vulnerable examples**: `tests/vulnerable-examples/sql-injection-vulnerable.php` (5 findings detected)
- **Safe examples**: `tests/safe-examples/sql-safe.php` (0 false positives)

## Integration

These rules are included in:
- `configs/basic.yaml` - Essential security rules
- `configs/strict.yaml` - Comprehensive security coverage
- `configs/plugin-development.yaml` - Plugin development configuration

## References

- [WordPress Database API](https://developer.wordpress.org/reference/classes/wpdb/)
- [WordPress Security Best Practices](https://developer.wordpress.org/plugins/security/securing-input/)
- [OWASP SQL Injection Prevention](https://owasp.org/www-community/attacks/SQL_Injection)
