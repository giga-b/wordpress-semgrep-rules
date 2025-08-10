# WordPress Semgrep Rules - Auto-fix System Guide

## Overview

The Auto-fix System is a powerful tool that automatically fixes common WordPress security issues detected by Semgrep rules. It can apply fixes for missing nonce verification, unsanitized input, unsafe output, and other security vulnerabilities.

## Features

### 🔧 **Automatic Fixes**
- **Nonce Verification**: Add CSRF protection to forms and AJAX handlers
- **Input Sanitization**: Apply appropriate sanitization to user input
- **Output Escaping**: Add HTML escaping to prevent XSS
- **Database Security**: Convert unsafe queries to prepared statements
- **Capability Checks**: Add user permission verification
- **AJAX Security**: Add nonce verification to AJAX handlers

### 🛡️ **Safety Features**
- **Backup Creation**: Automatic backup of files before applying fixes
- **Dry Run Mode**: Preview fixes without applying them
- **Confidence Scoring**: Each fix has a confidence level
- **Validation**: PHP syntax validation after fixes
- **Detailed Reporting**: Comprehensive reports of applied fixes

### 📊 **Reporting**
- **JSON Reports**: Detailed machine-readable reports
- **HTML Reports**: Human-readable reports with visualizations
- **Fix Statistics**: Summary of fixes by category and file
- **Error Tracking**: Detailed error reporting for failed fixes

## Installation

### Prerequisites
- Python 3.7+
- Virtual environment (recommended)
- Semgrep results JSON file

### Setup
1. **Activate virtual environment**:
   ```bash
   # Windows
   .venv\Scripts\Activate.ps1
   
   # Unix/Linux/macOS
   source .venv/bin/activate
   ```

2. **Install dependencies**:
   ```bash
   pip install pyyaml matplotlib seaborn pandas jinja2
   ```

## Usage

### Basic Usage

#### PowerShell (Windows)
```powershell
# Apply fixes with backup
.\tooling\run-auto-fix.ps1 -Results semgrep-results.json -Backup

# Dry run to see what would be fixed
.\tooling\run-auto-fix.ps1 -Results semgrep-results.json -DryRun -Verbose

# Install dependencies and apply fixes
.\tooling\run-auto-fix.ps1 -Results semgrep-results.json -InstallDependencies -Backup
```

#### Bash (Unix/Linux/macOS)
```bash
# Make script executable
chmod +x tooling/run-auto-fix.sh

# Apply fixes with backup
./tooling/run-auto-fix.sh --results semgrep-results.json --backup

# Dry run to see what would be fixed
./tooling/run-auto-fix.sh --results semgrep-results.json --dry-run --verbose

# Install dependencies and apply fixes
./tooling/run-auto-fix.sh --results semgrep-results.json --install-deps --backup
```

#### Direct Python Usage
```bash
# Basic usage
python tooling/auto_fix.py --results semgrep-results.json

# With backup and custom output
python tooling/auto_fix.py --results semgrep-results.json --backup --output my-report.json

# Dry run with verbose output
python tooling/auto_fix.py --results semgrep-results.json --dry-run --verbose
```

### Command Line Options

| Option | Description | Default |
|--------|-------------|---------|
| `--results` | Path to Semgrep results JSON file | Required |
| `--backup` | Create backups before applying fixes | False |
| `--dry-run` | Show what would be fixed without applying changes | False |
| `--output` | Output report file name | `auto-fix-report.json` |
| `--config` | Path to auto-fix configuration file | `tooling/auto-fix-config.yaml` |
| `--verbose` | Enable verbose output | False |

## Fix Categories

### 1. Nonce Verification (CSRF Protection)
**Confidence**: 0.8
**Description**: Adds nonce verification to prevent CSRF attacks

**Before**:
```php
if (isset($_POST['submit'])) {
    $data = $_POST['data'];
    // Process data
}
```

**After**:
```php
if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'my_action')) {
    $data = $_POST['data'];
    // Process data
}
```

### 2. Input Sanitization
**Confidence**: 0.9
**Description**: Applies appropriate sanitization to user input

**Before**:
```php
$user_input = $_POST['user_input'];
echo $user_input;
```

**After**:
```php
$user_input = sanitize_text_field($_POST['user_input']);
echo esc_html($user_input);
```

### 3. Output Escaping
**Confidence**: 0.85
**Description**: Adds HTML escaping to prevent XSS

**Before**:
```php
echo $user_data;
```

**After**:
```php
echo esc_html($user_data);
```

### 4. Database Security
**Confidence**: 0.7
**Description**: Converts unsafe queries to prepared statements

**Before**:
```php
$wpdb->query("SELECT * FROM posts WHERE title LIKE '%$user_input%'");
```

**After**:
```php
$wpdb->prepare("SELECT * FROM posts WHERE title LIKE %s", '%' . $wpdb->esc_like($user_input) . '%');
```

### 5. Capability Checks
**Confidence**: 0.6
**Description**: Adds user capability verification

**Before**:
```php
function admin_function() {
    // Admin functionality
}
```

**After**:
```php
function admin_function() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    // Admin functionality
}
```

### 6. AJAX Security
**Confidence**: 0.75
**Description**: Adds nonce verification to AJAX handlers

**Before**:
```php
function my_ajax_handler() {
    $data = $_POST['data'];
    // Process data
}
```

**After**:
```php
function my_ajax_handler() {
    check_ajax_referer('my_nonce_action', 'nonce');
    $data = $_POST['data'];
    // Process data
}
```

## Configuration

### Configuration File: `tooling/auto-fix-config.yaml`

The configuration file allows you to customize the auto-fix behavior:

```yaml
# Global settings
settings:
  min_confidence: 0.7          # Minimum confidence for applying fixes
  create_backups: true         # Create backups before applying fixes
  max_fixes_per_file: 10       # Maximum fixes per file
  auto_apply: false            # Require confirmation before applying

# Custom fix rules
fix_rules:
  - rule_id: "custom.rule"
    pattern: "pattern_to_match"
    replacement: "replacement_code"
    conditions: ["php", "custom_context"]
    confidence: 0.8
    description: "Custom fix description"
    category: "custom-category"

# Fix categories and priorities
categories:
  nonce-verification:
    priority: 1
    confidence_threshold: 0.8
```

### Custom Fix Rules

You can add custom fix rules to the configuration:

```yaml
fix_rules:
  - rule_id: "wordpress.custom.unsafe-pattern"
    pattern: "echo\\s+\\$_\\w+\\[\\s*['\"][^'\"]+['\"]\\s*\\];"
    replacement: "echo esc_html($_POST['user_input']);"
    conditions: ["php", "output", "user_input"]
    confidence: 0.9
    description: "Add HTML escaping to unsafe echo statements"
    category: "xss-prevention"
```

## Reports

### JSON Report Structure

```json
{
  "timestamp": "2025-01-09T12:00:00.000000",
  "summary": {
    "total_issues": 25,
    "fixes_applied": 20,
    "fixes_skipped": 3,
    "errors": 2
  },
  "fixes_by_category": {
    "nonce-verification": 8,
    "sanitization": 6,
    "output-escaping": 4,
    "database-security": 2
  },
  "fixes_by_file": {
    "plugin.php": [
      {
        "line": 15,
        "rule_id": "wordpress.nonce.missing-verification",
        "confidence": 0.8,
        "applied": true
      }
    ]
  },
  "detailed_results": [
    {
      "file_path": "plugin.php",
      "line_number": 15,
      "rule_id": "wordpress.nonce.missing-verification",
      "fix_type": "nonce-verification",
      "confidence": 0.8,
      "applied": true,
      "original_code": "if (isset($_POST['submit'])) {",
      "fixed_code": "if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'my_action')) {"
    }
  ]
}
```

### HTML Report

The HTML report provides a visual interface showing:
- Fix summary statistics
- Fixes by category with charts
- Detailed fix information
- File-by-file breakdown
- Confidence scores and recommendations

## Best Practices

### 1. Always Use Dry Run First
```bash
# Preview fixes before applying
./tooling/run-auto-fix.sh --results results.json --dry-run --verbose
```

### 2. Create Backups
```bash
# Always create backups when applying fixes
./tooling/run-auto-fix.sh --results results.json --backup
```

### 3. Review Generated Code
- Always review the generated code after applying fixes
- Test the fixed code thoroughly
- Ensure the fixes don't break existing functionality

### 4. Use Appropriate Confidence Levels
- High confidence (0.8-0.9): Safe to apply automatically
- Medium confidence (0.6-0.7): Review before applying
- Low confidence (0.5 and below): Manual review required

### 5. Validate After Fixes
- Check PHP syntax after applying fixes
- Run your test suite
- Verify that security issues are resolved

## Troubleshooting

### Common Issues

#### 1. "No fixable issues found"
**Cause**: The Semgrep results don't contain issues that match the auto-fix rules.
**Solution**: Check that your Semgrep results contain WordPress security issues.

#### 2. "Failed to apply fix"
**Cause**: The code structure doesn't match the expected pattern.
**Solution**: Review the original code and consider manual fixes for complex cases.

#### 3. "Configuration file not found"
**Cause**: The auto-fix configuration file is missing.
**Solution**: Use the default configuration or create a custom one.

#### 4. "Python dependencies missing"
**Cause**: Required Python packages are not installed.
**Solution**: Use the `--install-deps` flag or install manually:
```bash
pip install pyyaml matplotlib seaborn pandas jinja2
```

### Debug Mode

Enable verbose output for detailed debugging:
```bash
./tooling/run-auto-fix.sh --results results.json --verbose
```

### Manual Fix Review

For complex cases, review the generated code manually:
1. Use dry run mode to see proposed fixes
2. Review the confidence scores
3. Apply fixes selectively
4. Test thoroughly after each fix

## Integration

### CI/CD Integration

Add auto-fix to your CI/CD pipeline:

```yaml
# GitHub Actions example
- name: Run Semgrep
  run: |
    semgrep --config=configs/strict.yaml --json > semgrep-results.json

- name: Apply Auto-fixes
  run: |
    ./tooling/run-auto-fix.sh --results semgrep-results.json --backup --dry-run

- name: Apply Fixes (if dry run successful)
  run: |
    ./tooling/run-auto-fix.sh --results semgrep-results.json --backup
```

### IDE Integration

Configure your IDE to run auto-fix:
1. Set up a custom build task
2. Configure keyboard shortcuts
3. Add to your project's development workflow

## Security Considerations

### Limitations
- Auto-fix cannot replace manual code review
- Complex security issues may require manual intervention
- Always test fixes in a development environment first

### Recommendations
- Use auto-fix as part of a comprehensive security strategy
- Combine with manual code review and security testing
- Keep backups of original code
- Monitor and validate all applied fixes

## Support

For issues and questions:
1. Check the troubleshooting section
2. Review the configuration documentation
3. Examine the generated reports for details
4. Create an issue with detailed information

## Examples

### Complete Workflow Example

```bash
# 1. Run Semgrep scan
semgrep --config=configs/strict.yaml --json > semgrep-results.json

# 2. Preview fixes
./tooling/run-auto-fix.sh --results semgrep-results.json --dry-run --verbose

# 3. Apply fixes with backup
./tooling/run-auto-fix.sh --results semgrep-results.json --backup

# 4. Review the report
./tooling/run-auto-fix.sh --results semgrep-results.json --open-report

# 5. Test the fixed code
php -l plugin.php
```

This comprehensive auto-fix system provides a powerful tool for automatically addressing common WordPress security issues while maintaining code quality and safety.
