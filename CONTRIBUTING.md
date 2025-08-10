# Contributing to WordPress Semgrep Security Rules

Thank you for your interest in contributing to WordPress Semgrep Security Rules! This document provides comprehensive guidelines for contributing to the project.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Rule Development Guidelines](#rule-development-guidelines)
- [Testing Guidelines](#testing-guidelines)
- [Pull Request Process](#pull-request-process)
- [Reporting Bugs](#reporting-bugs)
- [Feature Requests](#feature-requests)
- [Community Guidelines](#community-guidelines)
- [Release Process](#release-process)
- [Security Reporting](#security-reporting)
- [Getting Help](#getting-help)

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

### Our Standards

- **Be respectful** - Treat all contributors with respect
- **Be collaborative** - Work together to improve the project
- **Be constructive** - Provide helpful, actionable feedback
- **Be inclusive** - Welcome contributors from all backgrounds
- **Be professional** - Maintain professional communication

### Enforcement

Violations of the Code of Conduct will be addressed by project maintainers. Serious violations may result in temporary or permanent exclusion from the project.

## How Can I Contribute?

### Types of Contributions

#### 🐛 Bug Reports
- Report issues with existing rules
- Identify false positives or false negatives
- Report performance problems
- Document configuration issues

#### ✨ Feature Requests
- Suggest new security rules
- Propose improvements to existing rules
- Request new configuration options
- Suggest tooling enhancements

#### 🔧 Code Contributions
- Implement new security rules
- Fix bugs in existing rules
- Improve test coverage
- Enhance documentation

#### 📚 Documentation
- Improve existing documentation
- Add examples and tutorials
- Create guides for specific use cases
- Translate documentation

#### 🧪 Testing
- Create test cases for new rules
- Improve existing test coverage
- Report test failures
- Validate rule performance

### Contribution Levels

#### 🥉 Beginner (Good First Issues)
- Documentation improvements
- Simple bug fixes
- Test case creation
- Configuration validation

#### 🥈 Intermediate
- New security rules
- Rule improvements
- Test infrastructure
- Performance optimizations

#### 🥇 Advanced
- Complex security patterns
- Taint analysis rules
- Advanced tooling
- Architecture improvements

## Development Setup

### Prerequisites

1. **Python 3.8+**
2. **Semgrep**: `pip install semgrep`
3. **Git**
4. **Node.js 16+** (for VS Code/Cursor extensions)

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

3. **Set up pre-commit hooks:**
   ```bash
   # Windows
   .\setup-pre-commit.ps1
   
   # Unix/Linux/macOS
   ./setup-pre-commit.sh
   ```

4. **Run tests:**
   ```bash
   # Test vulnerable examples
   semgrep scan --config=configs/plugin-development.yaml tests/vulnerable-examples/
   
   # Test safe examples
   semgrep scan --config=configs/plugin-development.yaml tests/safe-examples/
   
   # Run automated tests
   python tests/run-automated-tests.py
   ```

### Development Environment

#### VS Code Setup
1. Install the VS Code extension from `vscode-extension/`
2. Configure the extension with your preferred settings
3. Use the integrated terminal for running tests

#### Cursor Setup
1. Install the Cursor extension from `cursor-extension/`
2. Configure the extension settings
3. Use the integrated development tools

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
    confidence: HIGH|MEDIUM|LOW
    impact: HIGH|MEDIUM|LOW
  patterns:
    - pattern: "specific pattern to match"
  pattern-not: "safe pattern that should not trigger"
  fix: "suggested fix (optional)"
  fix-regex:
    regex: "regex pattern"
    replacement: "replacement text"
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
- **Options/Settings**: Configuration security
- **Secrets Management**: API key protection, credential security

### Rule Quality Standards

1. **Clear Messages**: Provide actionable, specific error messages
2. **CWE Mapping**: Include relevant CWE identifiers
3. **WordPress References**: Link to WordPress documentation
4. **Low False Positives**: Test against WordPress core code
5. **Comprehensive Testing**: Include both vulnerable and safe examples
6. **Performance**: Ensure rules don't significantly impact scan time
7. **Maintainability**: Use clear, readable patterns
8. **Documentation**: Include detailed explanations and examples

### Rule Development Process

1. **Research**: Understand the vulnerability and WordPress context
2. **Design**: Plan the rule pattern and test cases
3. **Implement**: Create the rule with proper metadata
4. **Test**: Validate against vulnerable and safe examples
5. **Optimize**: Ensure performance and accuracy
6. **Document**: Add comprehensive documentation
7. **Review**: Submit for code review

## Testing Guidelines

### Test Structure

```
tests/
├── vulnerable-examples/
│   ├── capability-vulnerable.php
│   ├── nonce-vulnerable.php
│   └── xss-vulnerable.php
├── safe-examples/
│   ├── capability-safe.php
│   ├── nonce-safe.php
│   └── xss-safe.php
└── test-results/
    └── automated-test-report.json
```

### Test Requirements

1. **Vulnerable Examples**: Should trigger the rule
2. **Safe Examples**: Should NOT trigger the rule
3. **Real-world Patterns**: Use realistic WordPress code patterns
4. **Edge Cases**: Include boundary conditions
5. **Documentation**: Comment test cases clearly
6. **Coverage**: Test all rule variations
7. **Performance**: Measure rule performance impact

### Running Tests

```bash
# Test specific configuration
semgrep scan --config=configs/strict.yaml tests/

# Test specific rule pack
semgrep scan --config=packs/wp-core-security/ tests/

# Generate test report
semgrep scan --config=configs/plugin-development.yaml tests/ --json > test-results.json

# Run automated test suite
python tests/run-automated-tests.py

# Run performance benchmarks
python tests/performance-benchmarks.py
```

### Test Validation

1. **Rule Detection**: Verify rules catch intended vulnerabilities
2. **False Positive Check**: Ensure no false positives on safe code
3. **Performance Check**: Measure scan time impact
4. **Coverage Check**: Ensure comprehensive test coverage
5. **Regression Check**: Verify existing rules still work

## Pull Request Process

### Before Submitting

1. **Test Your Changes:**
   - Run tests against vulnerable examples
   - Run tests against safe examples
   - Verify no false positives on WordPress core
   - Check performance impact

2. **Update Documentation:**
   - Update README.md if needed
   - Add rule documentation
   - Update configuration files
   - Update changelog

3. **Check Code Quality:**
   - Follow YAML formatting standards
   - Use consistent naming conventions
   - Include proper metadata
   - Run pre-commit hooks

### Pull Request Guidelines

1. **Title**: Clear, descriptive title using conventional commits
2. **Description**: Detailed description of changes
3. **Tests**: Include test cases for new rules
4. **Documentation**: Update relevant documentation
5. **Screenshots**: Include if UI changes
6. **Related Issues**: Link to related issues
7. **Breaking Changes**: Clearly mark breaking changes

### Conventional Commits

Use conventional commit format for PR titles:

```
feat: add new XSS prevention rule
fix: resolve false positive in capability check
docs: update rule documentation
test: add test cases for SQL injection rule
refactor: improve rule performance
```

### Review Process

1. **Automated Checks**: Must pass all CI checks
2. **Code Review**: At least one maintainer approval
3. **Testing**: Verify tests pass locally
4. **Documentation**: Ensure documentation is updated
5. **Performance**: Check for performance regressions

### Review Checklist

- [ ] Code follows project standards
- [ ] Tests are comprehensive and pass
- [ ] Documentation is updated
- [ ] No breaking changes (or clearly marked)
- [ ] Performance impact is acceptable
- [ ] Security implications are considered

## Reporting Bugs

### Bug Report Template

Use the GitHub issue template for bug reports, or include:

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
- Python Version: X.X.X

**Sample Code**
```php
// Code that triggers the issue
```

**Additional Information**
Any other relevant information
```

### Bug Report Guidelines

1. **Be Specific**: Provide exact steps to reproduce
2. **Include Code**: Share the code that triggers the issue
3. **Environment Details**: Specify versions and configurations
4. **Expected vs Actual**: Clearly describe the difference
5. **Screenshots**: Include if visual issues
6. **Related Issues**: Check for existing similar issues

## Feature Requests

### Feature Request Template

Use the GitHub issue template for feature requests, or include:

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

### Feature Request Guidelines

1. **Clear Description**: Explain what you want to achieve
2. **Use Case**: Describe why this is needed
3. **Examples**: Provide concrete examples
4. **Alternatives**: Consider existing solutions
5. **Impact**: Explain the benefit to users

## Community Guidelines

### Communication

- **Be Respectful**: Treat all contributors with respect
- **Be Helpful**: Provide constructive feedback
- **Be Patient**: Allow time for responses
- **Be Clear**: Use clear, concise language
- **Be Inclusive**: Welcome diverse perspectives

### Discussion Guidelines

- **Stay On Topic**: Keep discussions relevant to the project
- **Be Constructive**: Focus on solutions, not problems
- **Respect Decisions**: Accept final decisions gracefully
- **Share Knowledge**: Help others learn and grow

### Recognition

Contributors will be recognized through:

- **Contributors List**: Added to project contributors
- **Release Notes**: Acknowledged in release notes
- **Documentation**: Credited in relevant documentation
- **Community**: Highlighted in community discussions

## Release Process

### Release Types

- **Patch**: Bug fixes and minor improvements
- **Minor**: New features, backward compatible
- **Major**: Breaking changes

### Release Checklist

- [ ] All tests pass
- [ ] Documentation is updated
- [ ] Changelog is updated
- [ ] Version is bumped
- [ ] Release notes are prepared
- [ ] Security review is completed

### Release Process

1. **Prepare Release**: Update version and changelog
2. **Create Release Branch**: Branch from main
3. **Final Testing**: Run comprehensive tests
4. **Security Review**: Review for security issues
5. **Create Release**: Tag and publish release
6. **Announce**: Notify community of release

## Security Reporting

### Security Issues

For security vulnerabilities:

1. **DO NOT** create public issues
2. **DO** email security@project-domain.com
3. **DO** include detailed vulnerability information
4. **DO** allow time for assessment and fix

### Security Response

- **Acknowledgment**: You'll receive acknowledgment within 48 hours
- **Assessment**: Security team will assess the issue
- **Fix Development**: Fix will be developed if confirmed
- **Release**: Fix will be released with appropriate disclosure
- **Credit**: You'll be credited in security advisories

## Getting Help

### Resources

- **Documentation**: Check the `docs/` directory
- **GitHub Issues**: For bugs and feature requests
- **Discussions**: For questions and general discussion
- **Wiki**: For community-maintained content

### Support Channels

- **GitHub Issues**: Technical problems and bugs
- **GitHub Discussions**: Questions and general help
- **Email**: Security issues only
- **Community Chat**: Real-time help (if available)

### Before Asking for Help

1. **Check Documentation**: Review relevant documentation
2. **Search Issues**: Look for existing similar issues
3. **Try Solutions**: Attempt common troubleshooting steps
4. **Provide Context**: Include relevant details in your question

## License

By contributing to this project, you agree that your contributions will be licensed under the MIT License.

## Thank You

Thank you for contributing to WordPress security! Your contributions help make the WordPress ecosystem safer for everyone.

---

*This document is living and will be updated as the project evolves. Please check back regularly for the latest guidelines.*
