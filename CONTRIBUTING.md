# Contributing to WordPress Semgrep Security Rules

Thank you for your interest in contributing to WordPress Semgrep Security Rules! This document provides guidelines for contributing to the project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Rule Development Guidelines](#rule-development-guidelines)
- [Testing Guidelines](#testing-guidelines)
- [Pull Request Process](#pull-request-process)
- [Reporting Bugs](#reporting-bugs)
- [Feature Requests](#feature-requests)

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

- Use the GitHub issue tracker
- Include detailed steps to reproduce the bug
- Provide sample code that triggers the issue
- Include your Semgrep version and configuration

### Suggesting Enhancements

- Use the GitHub issue tracker
- Describe the enhancement clearly
- Explain why this enhancement would be useful
- Provide examples if applicable

### Contributing Code

- Fork the repository
- Create a feature branch
- Make your changes
- Add tests for new rules
- Submit a pull request

## Development Setup

### Prerequisites

1. **Python 3.8+**
2. **Semgrep**: `pip install semgrep`
3. **Git**

### Local Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-username/wordpress-semgrep-rules.git
   cd wordpress-semgrep-rules
   ```

2. **Install dependencies:**
   ```bash
   pip install -r requirements.txt
   ```

3. **Run tests:**
   ```bash
   # Test vulnerable examples
   semgrep scan --config=configs/plugin-development.yaml tests/vulnerable-examples/
   
   # Test safe examples
   semgrep scan --config=configs/plugin-development.yaml tests/safe-examples/
   ```

## Rule Development Guidelines

### Rule Structure

Follow this template for new rules:

```yaml
- id: wordpress-security-rule-name
  languages: [php]
  message: "Clear, actionable message describing the security issue"
  severity: ERROR|WARNING|INFO
  metadata:
    category: "security-category"
    cwe: "CWE-XXX"
    references:
      - "https://developer.wordpress.org/..."
      - "https://owasp.org/..."
    tags:
      - "wordpress"
      - "security"
      - "category"
  patterns:
    - pattern: "specific pattern to match"
  pattern-not: "safe pattern that should not trigger"
  fix: "suggested fix (optional)"
```

### Rule Categories

- **Authorization**: User capability checks, role verification
- **CSRF**: Nonce verification, token validation
- **XSS**: Output escaping, input sanitization
- **SQL Injection**: Query preparation, input validation
- **File Operations**: Path validation, upload security
- **SSRF**: URL validation, external request security
- **Deserialization**: Object injection prevention
- **REST API**: Endpoint security, authentication
- **AJAX**: Request validation, response security

### Rule Quality Standards

1. **Clear Messages**: Provide actionable, specific error messages
2. **CWE Mapping**: Include relevant CWE identifiers
3. **WordPress References**: Link to WordPress documentation
4. **Low False Positives**: Test against WordPress core code
5. **Comprehensive Testing**: Include both vulnerable and safe examples

## Testing Guidelines

### Test Structure

```
tests/
├── vulnerable-examples/
│   ├── capability-vulnerable.php
│   ├── nonce-vulnerable.php
│   └── xss-vulnerable.php
└── safe-examples/
    ├── capability-safe.php
    ├── nonce-safe.php
    └── xss-safe.php
```

### Test Requirements

1. **Vulnerable Examples**: Should trigger the rule
2. **Safe Examples**: Should NOT trigger the rule
3. **Real-world Patterns**: Use realistic WordPress code patterns
4. **Edge Cases**: Include boundary conditions
5. **Documentation**: Comment test cases clearly

### Running Tests

```bash
# Test specific configuration
semgrep scan --config=configs/strict.yaml tests/

# Test specific rule pack
semgrep scan --config=packs/wp-core-security/ tests/

# Generate test report
semgrep scan --config=configs/plugin-development.yaml tests/ --json > test-results.json
```

## Pull Request Process

### Before Submitting

1. **Test Your Changes:**
   - Run tests against vulnerable examples
   - Run tests against safe examples
   - Verify no false positives on WordPress core

2. **Update Documentation:**
   - Update README.md if needed
   - Add rule documentation
   - Update configuration files

3. **Check Code Quality:**
   - Follow YAML formatting standards
   - Use consistent naming conventions
   - Include proper metadata

### Pull Request Guidelines

1. **Title**: Clear, descriptive title
2. **Description**: Detailed description of changes
3. **Tests**: Include test cases for new rules
4. **Documentation**: Update relevant documentation
5. **Screenshots**: Include if UI changes

### Review Process

1. **Automated Checks**: Must pass all CI checks
2. **Code Review**: At least one maintainer approval
3. **Testing**: Verify tests pass locally
4. **Documentation**: Ensure documentation is updated

## Reporting Bugs

### Bug Report Template

```markdown
**Bug Description**
Brief description of the issue

**Steps to Reproduce**
1. Step 1
2. Step 2
3. Step 3

**Expected Behavior**
What should happen

**Actual Behavior**
What actually happens

**Environment**
- Semgrep Version: X.X.X
- Configuration: basic.yaml/strict.yaml/etc.
- Operating System: Windows/Linux/macOS

**Sample Code**
```php
// Code that triggers the issue
```

**Additional Information**
Any other relevant information
```

## Feature Requests

### Feature Request Template

```markdown
**Feature Description**
Clear description of the requested feature

**Use Case**
Why this feature would be useful

**Proposed Implementation**
How you think it should work

**Examples**
Code examples if applicable

**Additional Information**
Any other relevant information
```

## Getting Help

- **GitHub Issues**: For bugs and feature requests
- **Discussions**: For questions and general discussion
- **Documentation**: Check the docs/ directory

## License

By contributing to this project, you agree that your contributions will be licensed under the MIT License.

Thank you for contributing to WordPress security!
