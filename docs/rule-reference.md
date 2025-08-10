# WordPress Semgrep Rules Reference

This document provides a comprehensive reference for all WordPress security rules included in this project. Use this as a quick lookup guide for understanding rule purposes, severity levels, and CWE classifications.

## 📋 Rule Categories

### 🔐 Nonce Verification Rules

| Rule ID | Severity | CWE | Description |
|---------|----------|-----|-------------|
| `wordpress.nonce.ajax-missing` | ERROR | CWE-352 | AJAX handler missing nonce verification |
| `wordpress.nonce.ajax-weak` | WARNING | CWE-352 | Weak AJAX nonce verification |
| `wordpress.nonce.expired-check` | WARNING | CWE-352 | Nonce expiration not properly handled |
| `wordpress.nonce.hardcoded-action` | WARNING | CWE-352 | Hardcoded nonce action names |
| `wordpress.nonce.insecure-creation` | WARNING | CWE-352 | Insecure nonce creation patterns |
| `wordpress.nonce.missing-nonce-field` | ERROR | CWE-352 | Form missing nonce field |
| `wordpress.nonce.missing-verification` | ERROR | CWE-352 | Missing nonce verification in form processing |
| `wordpress.nonce.rest-missing` | ERROR | CWE-352 | REST API endpoint missing nonce verification |
| `wordpress.nonce.weak-verification` | WARNING | CWE-352 | Weak nonce verification without return value check |
| `wordpress.nonce.wrong-action` | ERROR | CWE-352 | Mismatched action names in nonce creation/verification |

### 🛡️ Capability Check Rules

| Rule ID | Severity | CWE | Description |
|---------|----------|-----|-------------|
| `wordpress.capability.ajax-missing` | ERROR | CWE-285 | AJAX handler missing capability check |
| `wordpress.capability.ajax-weak` | WARNING | CWE-285 | Weak AJAX capability check |
| `wordpress.capability.conditional-missing` | ERROR | CWE-285 | Conditional operation missing capability check |
| `wordpress.capability.content-management-missing` | ERROR | CWE-285 | Content management operation missing capability check |
| `wordpress.capability.db-operation-missing` | ERROR | CWE-285 | Database operation missing capability check |
| `wordpress.capability.file-operation-missing` | ERROR | CWE-285 | File operation missing capability check |
| `wordpress.capability.hardcoded-capability` | WARNING | CWE-285 | Hardcoded capability strings |
| `wordpress.capability.missing-admin-check` | ERROR | CWE-285 | Admin operation missing capability check |
| `wordpress.capability.missing-check` | ERROR | CWE-285 | Missing capability check for sensitive operation |
| `wordpress.capability.missing-nonce` | ERROR | CWE-352 | Capability check without nonce verification |
| `wordpress.capability.multiple-checks` | INFO | CWE-285 | Multiple capability checks that can be optimized |
| `wordpress.capability.multisite-missing` | ERROR | CWE-285 | Multisite operation missing capability check |
| `wordpress.capability.overly-permissive` | WARNING | CWE-285 | Overly permissive capability check |
| `wordpress.capability.plugin-management-missing` | ERROR | CWE-285 | Plugin management operation missing capability check |
| `wordpress.capability.rest-missing` | ERROR | CWE-285 | REST API endpoint missing capability check |
| `wordpress.capability.role-check-instead` | WARNING | CWE-285 | Using role check instead of capability check |
| `wordpress.capability.role-comparison` | ERROR | CWE-285 | Direct role comparison instead of capability check |
| `wordpress.capability.settings-management-missing` | ERROR | CWE-285 | Settings management operation missing capability check |
| `wordpress.capability.user-management-missing` | ERROR | CWE-285 | User management operation missing capability check |
| `wordpress.capability.weak-check` | WARNING | CWE-285 | Weak capability check for sensitive operation |

### 🧹 Sanitization Function Rules

| Rule ID | Severity | CWE | Description |
|---------|----------|-----|-------------|
| `wordpress.sanitization.ajax-missing` | ERROR | CWE-79 | AJAX handler missing sanitization |
| `wordpress.sanitization.comment-missing` | ERROR | CWE-79 | Comment data without sanitization |
| `wordpress.sanitization.cookie-missing` | ERROR | CWE-79 | Cookie data without sanitization |
| `wordpress.sanitization.double-sanitization` | WARNING | CWE-79 | Unnecessary double sanitization |
| `wordpress.sanitization.missing-get` | ERROR | CWE-79 | GET parameter without sanitization |
| `wordpress.sanitization.missing-input` | ERROR | CWE-79 | User input without sanitization |
| `wordpress.sanitization.missing-request` | ERROR | CWE-79 | REQUEST data without sanitization |
| `wordpress.sanitization.missing-validation` | WARNING | CWE-20 | Sanitization without validation |
| `wordpress.sanitization.options-missing` | ERROR | CWE-79 | Options update without sanitization |
| `wordpress.sanitization.postmeta-missing` | ERROR | CWE-79 | Post meta update without sanitization |
| `wordpress.sanitization.rest-missing` | ERROR | CWE-79 | REST API endpoint missing sanitization |
| `wordpress.sanitization.search-missing` | ERROR | CWE-79 | Search query without sanitization |
| `wordpress.sanitization.unsafe-attribute` | ERROR | CWE-79 | Unsafe attribute output |
| `wordpress.sanitization.unsafe-db-query` | ERROR | CWE-89 | Unsafe database query |
| `wordpress.sanitization.unsafe-email` | ERROR | CWE-79 | Unsafe email usage |
| `wordpress.sanitization.unsafe-file-path` | ERROR | CWE-22 | Unsafe file path usage |
| `wordpress.sanitization.unsafe-include` | ERROR | CWE-98 | Unsafe include/require with user input |
| `wordpress.sanitization.unsafe-insert` | ERROR | CWE-89 | Unsafe database insert |
| `wordpress.sanitization.unsafe-json` | ERROR | CWE-79 | Unsafe JSON output |
| `wordpress.sanitization.unsafe-link` | ERROR | CWE-601 | Unsafe link output |
| `wordpress.sanitization.unsafe-output` | ERROR | CWE-79 | Unsafe output without escaping |
| `wordpress.sanitization.unsafe-url` | ERROR | CWE-601 | Unsafe URL usage |
| `wordpress.sanitization.upload-missing` | ERROR | CWE-434 | File upload without proper validation |
| `wordpress.sanitization.usermeta-missing` | ERROR | CWE-79 | User meta update without sanitization |
| `wordpress.sanitization.wrong-function` | WARNING | CWE-79 | Wrong sanitization function for context |

## 🎯 Severity Levels

### ERROR
Critical security vulnerabilities that must be fixed immediately. These represent actual security risks that could lead to exploitation.

### WARNING
Security concerns that should be addressed. These may not be immediately exploitable but represent potential security issues.

### INFO
Best practice recommendations. These help improve code quality and security posture but are not security vulnerabilities.

## 🔗 CWE Classifications

### CWE-20: Improper Input Validation
Rules that detect when input data is not properly validated before use.

### CWE-22: Path Traversal
Rules that detect unsafe file path handling that could lead to directory traversal attacks.

### CWE-79: Cross-site Scripting (XSS)
Rules that detect when user input is output without proper escaping, potentially allowing XSS attacks.

### CWE-89: SQL Injection
Rules that detect when user input is used in database queries without proper sanitization or prepared statements.

### CWE-98: PHP Remote File Inclusion
Rules that detect when user input is used in include/require statements without validation.

### CWE-285: Improper Authorization
Rules that detect when operations are performed without proper user capability checks.

### CWE-352: Cross-Site Request Forgery (CSRF)
Rules that detect when nonce verification is missing or improperly implemented.

### CWE-434: Unrestricted Upload of File with Dangerous Type
Rules that detect when file uploads are not properly validated.

### CWE-601: URL Redirection to Untrusted Site
Rules that detect when URLs are used without proper validation, potentially leading to open redirects.

## 📊 Rule Statistics

- **Total Rules**: 56
- **Nonce Rules**: 10
- **Capability Rules**: 20
- **Sanitization Rules**: 26
- **Error Severity**: 35 rules
- **Warning Severity**: 18 rules
- **Info Severity**: 3 rules

## 🔧 Rule Configuration

### Basic Configuration
Includes essential security rules with minimal false positives:
- All ERROR severity rules
- Critical WARNING severity rules
- Fast scanning performance

### Strict Configuration
Includes comprehensive security coverage:
- All rules (ERROR, WARNING, INFO)
- Quality and best practice rules
- Thorough scanning with detailed reporting

### Plugin Development Configuration
Includes plugin-specific patterns:
- WordPress coding standards
- Plugin development best practices
- Integration with WordPress core

## 📝 Usage Examples

### Command Line Usage
```bash
# Scan with specific rule category
semgrep scan --config=packs/wp-core-security/nonce-verification.yaml /path/to/code

# Scan with all security rules
semgrep scan --config=packs/wp-core-security/ /path/to/code

# Scan with specific configuration
semgrep scan --config=configs/strict.yaml /path/to/code
```

### CI/CD Integration
```bash
# GitHub Actions with error on findings
semgrep scan --config=configs/basic.yaml --error-on-findings

# Generate JSON report
semgrep scan --config=configs/strict.yaml --json --output results.json
```

## 🤝 Contributing New Rules

When adding new rules:

1. **Choose appropriate category**: nonce-verification, capability-checks, or sanitization-functions
2. **Set appropriate severity**: ERROR for vulnerabilities, WARNING for concerns, INFO for recommendations
3. **Assign CWE classification**: Use the most specific CWE that applies
4. **Write clear description**: Explain what the rule detects and why it's important
5. **Add test cases**: Include both vulnerable and safe examples
6. **Update this reference**: Add the new rule to the appropriate table

---

**Last Updated**: January 2025  
**Total Rules**: 56  
**Coverage**: WordPress Core Security, Plugin Security, PHP Security
