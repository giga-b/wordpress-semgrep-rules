# WordPress Semgrep Configuration Validator Guide

## Overview

The WordPress Semgrep Configuration Validator is a comprehensive tool that validates WordPress Semgrep configuration files to ensure they are syntactically correct, structurally sound, and follow WordPress security best practices.

## Features

### Validation Levels

The validator performs checks at multiple levels:

1. **Syntax Validation** - Ensures YAML files are properly formatted
2. **Structure Validation** - Validates configuration structure and required fields
3. **Reference Validation** - Checks that included files and directories exist
4. **Rule Validation** - Validates individual rule definitions
5. **Performance Validation** - Identifies potential performance issues

### What Gets Validated

#### Configuration Files
- YAML syntax correctness
- Required configuration sections
- Include/exclude path validity
- Rule filter configurations
- Performance optimization settings

#### Rule Definitions
- Required fields (id, languages, message)
- Rule ID format compliance
- Severity level validation
- Language support verification
- Metadata structure validation
- Pattern syntax validation

#### Cross-References
- File existence for includes
- Directory structure validation
- Circular reference detection
- Rule pattern matching

## Usage

### Basic Usage

```bash
# Validate all configuration files
python tooling/validate-configs.py

# Validate a specific configuration file
python tooling/validate-configs.py configs/basic.yaml

# Validate all configurations and rule files
python tooling/validate-configs.py --all

# Validate rule files only
python tooling/validate-configs.py --validate-rules
```

### Advanced Usage

```bash
# Specify project root directory
python tooling/validate-configs.py --project-root /path/to/project

# Output results in JSON format
python tooling/validate-configs.py --output json

# Output summary only
python tooling/validate-configs.py --output summary
```

### Command Line Options

| Option | Description | Default |
|--------|-------------|---------|
| `config_file` | Specific configuration file to validate | All configs |
| `--all` | Validate all configuration and rule files | False |
| `--validate-rules` | Include rule file validation | False |
| `--project-root` | Project root directory | Current directory |
| `--output` | Output format (text, json, summary) | text |

## Configuration File Structure

### Valid Configuration Keys

```yaml
# Top-level configuration keys
include:          # List of rule files/directories to include
exclude:          # List of patterns to exclude from scanning
rules:            # Inline rule definitions
rule-filters:     # Rule filtering configuration
```

### Rule Structure Requirements

```yaml
rules:
  - id: wordpress.category.rule-name    # Required: Unique rule identifier
    languages: [php]                    # Required: Supported languages
    message: "Rule description"         # Required: Human-readable message
    severity: ERROR                     # Optional: ERROR, WARNING, or INFO
    metadata:                          # Optional: Additional rule information
      category: "security-category"
      cwe: "CWE-XXX"
      references:
        - "https://developer.wordpress.org/..."
    patterns:                          # Required: Detection patterns
      - pattern: "specific pattern"
    pattern-not: "safe pattern"        # Optional: Exclusion patterns
    fix: "suggested fix"               # Optional: Auto-fix suggestion
```

### Valid Rule Categories

- `nonce-verification`
- `capability-checks`
- `sanitization-functions`
- `xss-prevention`
- `sql-injection`
- `ajax-security`
- `rest-api-security`
- `file-operations`
- `authentication`
- `authorization`
- `data-validation`
- `output-encoding`
- `input-validation`
- `session-management`
- `error-handling`
- `logging`
- `cryptography`
- `performance`
- `quality`
- `security`
- `experimental`

### Valid Severity Levels

- `ERROR` - Critical issues that must be fixed
- `WARNING` - Issues that should be addressed
- `INFO` - Informational messages

### Valid Languages

- `php` - PHP code
- `javascript` / `js` - JavaScript code
- `typescript` / `ts` - TypeScript code
- `html` - HTML markup
- `css` - CSS stylesheets

## Error Types and Messages

### Syntax Errors

**YAML Syntax Error**
```
Error (syntax): YAML syntax error: expected <block end>, but found '<scalar>'
File: configs/basic.yaml
Line: 15
Column: 5
```

**Empty Configuration**
```
Error (syntax): Configuration file is empty or contains only comments
File: configs/basic.yaml
```

### Structure Errors

**Missing Required Fields**
```
Error (structure): Rule missing required field: languages
File: configs/basic.yaml
Context: Rule wordpress.test.missing-fields
```

**Invalid Rule ID**
```
Error (structure): Invalid rule ID format: invalid-rule-id
File: configs/basic.yaml
Context: Rule 0
```

**Invalid Severity**
```
Error (structure): Invalid severity level: INVALID
File: configs/basic.yaml
Context: Rule wordpress.test.invalid-severity
```

### Reference Errors

**Missing Include File**
```
Error (references): Include file does not exist: packs/wp-core-security/missing.yaml
File: configs/basic.yaml
```

**Missing Include Directory**
```
Error (references): Include directory does not exist: packs/missing-directory/
File: configs/basic.yaml
```

**Circular Reference**
```
Error (references): Circular include detected: basic.yaml -> strict.yaml
File: configs/basic.yaml
```

### Performance Warnings

**Missing Exclude Patterns**
```
Warning (performance): Configuration 'strict' should include exclude patterns for performance
File: configs/strict.yaml
```

**Missing Rule Filters**
```
Warning (performance): Configuration 'plugin-development' should include rule filters for performance
File: configs/plugin-development.yaml
```

## Best Practices

### Configuration Organization

1. **Use Include Directories** - Organize rules into logical directories
   ```yaml
   include:
     - packs/wp-core-security/
     - packs/wp-core-quality/
   ```

2. **Exclude Unnecessary Files** - Improve performance by excluding irrelevant files
   ```yaml
   exclude:
     - "**/node_modules/**"
     - "**/vendor/**"
     - "**/*.min.js"
     - "**/*.min.css"
   ```

3. **Use Rule Filters** - Control which rules are active
   ```yaml
   rule-filters:
     - include: "wordpress.security.*"
     - exclude: "wordpress.performance.*"
   ```

### Rule Development

1. **Follow Naming Conventions** - Use consistent rule ID format
   ```yaml
   id: wordpress.category.rule-name
   ```

2. **Provide Clear Messages** - Write actionable error messages
   ```yaml
   message: "Nonce verification is missing. Use wp_verify_nonce() to verify the nonce before processing form data."
   ```

3. **Include Metadata** - Add context and references
   ```yaml
   metadata:
     category: "nonce-verification"
     cwe: "CWE-352"
     references:
       - "https://developer.wordpress.org/plugins/security/nonces/"
   ```

4. **Use Appropriate Severity** - Match severity to issue importance
   ```yaml
   severity: ERROR  # For security vulnerabilities
   severity: WARNING  # For best practice violations
   severity: INFO  # For informational messages
   ```

## Integration

### CI/CD Integration

Add validation to your CI/CD pipeline:

```yaml
# GitHub Actions example
- name: Validate Semgrep Configurations
  run: |
    python tooling/validate-configs.py --all
```

### Pre-commit Hooks

Validate configurations before committing:

```bash
# .pre-commit-config.yaml
- repo: local
  hooks:
    - id: validate-semgrep-configs
      name: Validate Semgrep Configurations
      entry: python tooling/validate-configs.py
      language: system
      files: \.(yaml|yml)$
```

### IDE Integration

Configure your IDE to run validation:

```json
// VS Code settings.json
{
  "python.linting.enabled": true,
  "python.linting.pylintEnabled": false,
  "python.linting.flake8Enabled": true,
  "python.linting.mypyEnabled": true,
  "python.testing.pytestEnabled": true,
  "python.testing.unittestEnabled": false,
  "python.testing.pytestArgs": [
    "tooling/test-validator.py"
  ]
}
```

## Troubleshooting

### Common Issues

1. **Missing Dependencies**
   ```bash
   pip install pyyaml
   ```

2. **Permission Errors**
   ```bash
   chmod +x tooling/validate-configs.py
   ```

3. **Path Issues**
   ```bash
   # Use absolute paths or ensure correct working directory
   python tooling/validate-configs.py --project-root /absolute/path/to/project
   ```

### Debug Mode

For detailed debugging information:

```bash
# Enable Python debug output
python -v tooling/validate-configs.py

# Check file permissions
ls -la tooling/validate-configs.py
```

### Getting Help

1. **Check Documentation** - Review this guide and related documentation
2. **Run Tests** - Execute the test suite to verify functionality
   ```bash
   python tooling/test-validator.py
   ```
3. **Review Examples** - Examine existing configuration files
4. **Check Logs** - Look for detailed error messages in output

## Examples

### Basic Configuration

```yaml
# configs/basic.yaml
include:
  - packs/wp-core-security/nonce-verification.yaml
  - packs/wp-core-security/capability-checks.yaml

exclude:
  - "**/node_modules/**"
  - "**/vendor/**"

rules:
  - id: wordpress.nonce.missing-verification
    languages: [php]
    message: "Nonce verification is missing. Use wp_verify_nonce() to verify the nonce before processing form data."
    severity: ERROR
    metadata:
      category: "nonce-verification"
      cwe: "CWE-352"
    patterns:
      - pattern: |
          if (isset($_POST['submit'])) {
            $data = $_POST['data'];
          }
      - pattern-not: |
          if (isset($_POST['submit'])) {
            if (wp_verify_nonce($_POST['_wpnonce'], 'action_name')) {
              $data = $_POST['data'];
            }
          }
```

### Strict Configuration

```yaml
# configs/strict.yaml
include:
  - packs/wp-core-security/
  - packs/wp-core-quality/
  - packs/experimental/

exclude:
  - "**/node_modules/**"
  - "**/vendor/**"
  - "**/*.min.js"
  - "**/*.min.css"

rule-filters:
  - include: "wordpress.security.*"
  - include: "wordpress.quality.*"
  - include: "wordpress.performance.*"
```

### Plugin Development Configuration

```yaml
# configs/plugin-development.yaml
include:
  - packs/wp-core-security/
  - packs/wp-core-quality/
  - packs/experimental/

exclude:
  - "**/node_modules/**"
  - "**/vendor/**"
  - "**/tests/**"
  - "**/*.min.js"
  - "**/*.min.css"

rule-filters:
  - exclude: "wordpress.performance.*"
  - exclude: "wordpress.quality.*"
```

## Conclusion

The WordPress Semgrep Configuration Validator helps ensure your security rules are properly configured and follow best practices. Regular validation helps catch issues early and maintains the quality of your security scanning setup.

For more information, see:
- [WordPress Security Best Practices](https://developer.wordpress.org/plugins/security/)
- [Semgrep Documentation](https://semgrep.dev/docs/)
- [OWASP Top Ten](https://owasp.org/www-project-top-ten/)
