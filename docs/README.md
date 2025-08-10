# WordPress Semgrep Rules Documentation

Welcome to the comprehensive documentation for the WordPress Semgrep Security Rules project. This documentation provides detailed information about all security rules, their usage, and best practices for WordPress development.

## 📚 Documentation Index

### Core Documentation
- **[Product Requirements Document](PRD-WordPress-Semgrep-Rules-Development.md)** - Complete project overview, requirements, and specifications
- **[Development Guide](DEVELOPMENT-GUIDE.md)** - Guide for developers contributing to the project

### Security Rule Documentation
- **[Nonce Verification Rules](nonce-verification-rules.md)** - Comprehensive guide to nonce security patterns
- **[Capability Check Rules](capability-check-rules.md)** - User authorization and permission patterns
- **[Sanitization Function Rules](sanitization-function-rules.md)** - Input sanitization and data validation patterns

## 🚀 Quick Start Guide

### 1. Installation
```bash
# Install Semgrep
pip install semgrep

# Clone the repository
git clone https://github.com/giga-b/wordpress-semgrep-rules.git
cd wordpress-semgrep-rules
```

### 2. Basic Usage
```bash
# Scan your WordPress plugin/theme
semgrep scan --config=configs/basic.yaml /path/to/your/wordpress-project

# Use strict configuration for comprehensive security audit
semgrep scan --config=configs/strict.yaml /path/to/your/wordpress-project

# Plugin development specific scanning
semgrep scan --config=configs/plugin-development.yaml /path/to/your/plugin
```

### 3. Test the Rules
```bash
# Test against vulnerable examples (should find issues)
semgrep scan --config=packs/wp-core-security/ tests/vulnerable-examples/

# Test against safe examples (should find no issues)
semgrep scan --config=packs/wp-core-security/ tests/safe-examples/
```

## 📋 Rule Categories Overview

### 🔐 Nonce Verification Rules
**Purpose**: Prevent Cross-Site Request Forgery (CSRF) attacks
**Coverage**: Form submissions, AJAX requests, REST API endpoints
**Key Rules**:
- Missing nonce creation
- Weak nonce verification
- AJAX without nonce checks
- REST API security

### 🛡️ Capability Check Rules
**Purpose**: Ensure proper user authorization
**Coverage**: Admin operations, user management, sensitive functions
**Key Rules**:
- Missing capability checks
- Weak authorization patterns
- Role-based security issues
- Multisite security

### 🧹 Sanitization Function Rules
**Purpose**: Prevent XSS, SQL injection, and data corruption
**Coverage**: Input validation, output escaping, database operations
**Key Rules**:
- Missing input sanitization
- Unsafe database queries
- Output without escaping
- File operation security

## 🎯 Configuration Types

### Basic Configuration (`configs/basic.yaml`)
- Essential security rules only
- Minimal false positives
- Fast scanning performance
- Suitable for CI/CD integration

### Strict Configuration (`configs/strict.yaml`)
- Comprehensive security coverage
- Includes quality rules
- Thorough scanning with detailed reporting
- Suitable for security audits

### Plugin Development Configuration (`configs/plugin-development.yaml`)
- Plugin-specific security patterns
- WordPress coding standards
- Best practices for plugin development
- Integration with WordPress core

## 🔧 Advanced Usage

### Custom Rule Generation
```bash
# Generate custom rules for specific categories
python tooling/generate_rules.py --categories wordpress-core,php-security --output custom-rules.yaml

# Generate rules for specific vulnerability types
python tooling/generate_rules.py --vulnerabilities xss,sql-injection --output xss-sql-rules.yaml
```

### Integration with CI/CD
```bash
# GitHub Actions example
semgrep scan --config=configs/plugin-development.yaml --json --output semgrep-results.json

# Pre-commit hook
semgrep scan --config=configs/basic.yaml --error-on-findings
```

### IDE Integration
- **VS Code**: Use the Semgrep extension
- **Cursor**: Built-in Semgrep support
- **Other IDEs**: Configure Semgrep as an external tool

## 📊 Understanding Results

### Severity Levels
- **ERROR**: Critical security vulnerability that must be fixed
- **WARNING**: Security concern that should be addressed
- **INFO**: Best practice recommendation

### Common False Positives
- WordPress core functions (when used correctly)
- Third-party library calls (when properly validated)
- Custom sanitization functions (when documented)

### Remediation Guidance
Each rule includes:
- Detailed explanation of the vulnerability
- Vulnerable code examples
- Safe code examples
- Step-by-step remediation instructions

## 🤝 Contributing

### Adding New Rules
1. Create rule in appropriate pack (`packs/wp-core-security/`)
2. Add test cases in `tests/vulnerable-examples/` and `tests/safe-examples/`
3. Update configuration files
4. Document the rule in appropriate documentation file

### Reporting Issues
- Use GitHub Issues for bug reports
- Include code examples and expected behavior
- Provide context about your WordPress environment

### Requesting Features
- Submit feature requests via GitHub Issues
- Include use case and expected benefits
- Consider contributing the implementation

## 📞 Support

### Getting Help
- Check the [Development Guide](DEVELOPMENT-GUIDE.md) for technical details
- Review rule-specific documentation for detailed explanations
- Search existing GitHub Issues for similar problems

### Community
- GitHub Discussions for general questions
- GitHub Issues for bug reports and feature requests
- Pull requests for contributions

## 📄 License

This project is licensed under the MIT License. See [LICENSE](../LICENSE) for details.

---

**Last Updated**: January 2025  
**Version**: 1.0.0  
**Semgrep Version**: 1.45.0+
