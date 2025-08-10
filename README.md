# PressGuard - WordPress Security Scanner

[![CI](https://github.com/giga-b/wordpress-semgrep-rules/workflows/CI/badge.svg)](https://github.com/giga-b/wordpress-semgrep-rules/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Semgrep](https://img.shields.io/badge/Semgrep-1.45.0+-blue.svg)](https://semgrep.dev/)

**PressGuard** is a comprehensive WordPress security scanning solution that combines the power of Semgrep static analysis with advanced dashboard analytics. Designed specifically for WordPress plugin and theme developers, it provides automated security scanning, detailed vulnerability reporting, and interactive dashboards for monitoring security posture.

## 🛡️ Key Features

- **Comprehensive Security Rules**: 15+ specialized WordPress security rules covering nonce verification, capability checks, SQL injection, XSS prevention, and more
- **Interactive Dashboards**: Real-time security scan results with detailed findings, fix suggestions, and performance metrics
- **Auto-Fix Capabilities**: Intelligent code suggestions with multi-layer validation for common security issues
- **Performance Optimization**: Configurable scan profiles optimized for different development stages
- **IDE Integration**: VS Code and Cursor extensions for real-time security feedback
- **CI/CD Ready**: GitHub Actions integration for automated security scanning
- **Advanced Analytics**: Detailed metrics on scan performance, false positive rates, and rule effectiveness

## 🎯 Target Users

- **WordPress Plugin Developers**: Ensure your plugins follow WordPress security best practices
- **Theme Developers**: Build secure themes with automated security validation
- **Security Auditors**: Comprehensive security analysis tools for WordPress codebases
- **Development Teams**: Integrate security scanning into your development workflow

## 📊 Dashboard Overview

PressGuard provides powerful dashboards for monitoring security scan results and performance metrics:

### User Dashboard
![PressGuard User Dashboard](dashboard/user-dashboard.png)
*Interactive security scan results with detailed findings, fix suggestions, and export capabilities*

### Admin Dashboard  
![PressGuard Admin Dashboard](dashboard/admin-dashboard.png)
*Comprehensive metrics dashboard showing scan performance, rule effectiveness, and trend analysis*

## Quick Start

> **Note**: This project is now live on GitHub! Check out the [repository](https://github.com/giga-b/wordpress-semgrep-rules) for the latest updates.

> **⚠️ Security Notice**: Before using the auto-fix system, please review our [Security Considerations](docs/SECURITY-CONSIDERATIONS.md) guide.

> **🔄 CI/CD Status**: GitHub Actions workflows are configured for automated security scanning. The workflow uses the latest `actions/upload-artifact@v4` to avoid deprecation warnings.

## 🚀 Dashboard Access

PressGuard includes interactive dashboards for monitoring security scans:

### User Dashboard
Access the user dashboard to view detailed security scan results:
```bash
# Start the dashboard server
python serve-dashboard.py

# Open in browser
http://localhost:8000/dashboard/user-dashboard.html
```

### Admin Dashboard
Access the admin dashboard for comprehensive metrics and analytics:
```bash
# Start the dashboard server
python serve-dashboard.py

# Open in browser  
http://localhost:8000/dashboard/index.html
```

### 📸 Capturing Dashboard Screenshots
To capture screenshots for documentation:
```bash
# Install dependencies
pip install selenium

# Capture screenshots
python dashboard/capture-screenshots.py
```

1. **Install Semgrep:**
   ```bash
   pip install semgrep
   ```

2. **Run security scan:**
   ```bash
   semgrep scan --config=configs/plugin-development.yaml tests/vulnerable-examples/
   ```

3. **Generate custom rules:**
   ```bash
   python tooling/generate_rules.py --categories wordpress-core,php-security --output custom-rules.yaml
   ```

4. **Use auto-fix system (with caution):**
   ```bash
   python tooling/auto_fix.py --results semgrep-results.json --dry-run
   ```

## Project Structure

```
wordpress-semgrep-rules/
├── dashboard/                    # Interactive dashboards
│   ├── user-dashboard.html      # Security scan results interface
│   ├── index.html              # Admin metrics dashboard
│   ├── favicon.svg             # Dashboard favicon
│   └── capture-screenshots.py  # Dashboard screenshot automation
├── packs/                       # Security rule packs
│   ├── wp-core-security/       # Core WordPress security rules
│   ├── wp-core-quality/        # WordPress code quality rules
│   └── experimental/           # Experimental and advanced rules
├── configs/                     # Scan configurations
│   ├── basic.yaml              # Essential security rules
│   ├── strict.yaml             # Comprehensive security rules
│   ├── plugin-development.yaml # Plugin development focus
│   ├── optimized-15s.yaml      # Performance optimized (15s)
│   └── optimized-30s.yaml      # Performance optimized (30s)
├── tests/                       # Test suites
│   ├── vulnerable-examples/    # Test cases that should trigger rules
│   ├── safe-examples/          # Test cases that should NOT trigger rules
│   ├── run-automated-tests.py  # Automated test runner
│   └── test-results/           # Test execution results
├── tooling/                     # Development tools
│   ├── generate_rules.py       # Rule generation script
│   ├── auto_fix.py             # Auto-fix system
│   ├── run-semgrep.ps1         # Windows runner
│   ├── run-semgrep.sh          # Unix runner
│   └── metrics_dashboard.py    # Metrics dashboard generator
├── results/                     # Scan and test results
│   ├── performance/            # Performance test results
│   └── test-results/           # Test execution results
├── docs/                        # Documentation
│   ├── development/            # Development documentation
│   ├── API-REFERENCE.md        # API documentation
│   ├── SECURITY-CONSIDERATIONS.md # Security guidelines
│   └── PRODUCTION-DEPLOYMENT-GUIDE.md # Deployment guide
├── cursor-extension/           # Cursor IDE extension
├── vscode-extension/           # VS Code extension
└── .github/                    # GitHub Actions workflows
```

## Configuration Types

- **basic.yaml**: Essential WordPress security rules
- **strict.yaml**: Comprehensive security rules for production
- **plugin-development.yaml**: Plugin-specific security patterns

## Rule Categories

### WordPress Core Security
- Nonce verification patterns
- Capability checks
- Sanitization function usage
- WordPress hook security

### Plugin Security
- Admin interface security
- AJAX endpoint security
- Plugin activation/deactivation
- Settings page security

### PHP Security
- SQL injection prevention
- XSS protection
- File operation security
- Deserialization safety

## Security

### Security Considerations
This project includes automated security scanning and fixing capabilities. Please review our comprehensive [Security Considerations](docs/SECURITY-CONSIDERATIONS.md) guide before using these features.

### Key Security Features
- **Enhanced Auto-fix Validation**: Multi-layer validation system for generated fixes
- **Path Validation**: Comprehensive path sanitization and validation
- **Configuration Hardening**: Secure default settings and validation
- **Error Handling**: Robust error handling with rollback capabilities
- **Backup System**: Automatic backup creation before any changes

### Security Best Practices
- Always test auto-fixes in development environment first
- Use preview mode to review proposed changes
- Enable backup creation before applying fixes
- Monitor logs for security events
- Regular security audits and updates

## Testing

Run tests against vulnerable examples:
```bash
semgrep scan --config=configs/plugin-development.yaml tests/vulnerable-examples/
```

Run tests against safe examples:
```bash
semgrep scan --config=configs/plugin-development.yaml tests/safe-examples/
```

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create a feature branch
3. Add new rules to the appropriate category in `packs/`
4. Create test cases in `tests/vulnerable-examples/`
5. Update configuration files as needed
6. Test thoroughly before committing
7. Submit a pull request

## Issues and Feature Requests

- [Bug Reports](https://github.com/giga-b/wordpress-semgrep-rules/issues/new?template=bug_report.md)
- [Feature Requests](https://github.com/giga-b/wordpress-semgrep-rules/issues/new?template=feature_request.md)

## Integration

### Pre-commit Hooks
Automatically scan staged files before commits.

### CI/CD Pipeline
Integrated security scanning in GitHub Actions.

### IDE Integration
Configure your IDE to use these rules for real-time scanning.

## Documentation

### Production Documentation
- [Production Deployment Guide](docs/PRODUCTION-DEPLOYMENT-GUIDE.md) - Complete deployment instructions for production environments
- [Production User Guide](docs/PRODUCTION-USER-GUIDE.md) - User guide for production workflows and integrations
- [API Reference](docs/API-REFERENCE.md) - Complete API documentation for all tools and interfaces

### Technical Documentation
- [WordPress Security Best Practices](https://developer.wordpress.org/plugins/security/)
- [Semgrep Documentation](https://semgrep.dev/docs/)
- [OWASP Top Ten](https://owasp.org/www-project-top-ten/)

## Repository Organization

This repository has been organized for better maintainability:

### 📁 Organized Structure
- **`results/`**: Contains all scan and test results
  - `performance/`: Performance test results and benchmarks
  - `test-results/`: Test execution results and reports
- **`docs/development/`**: Development documentation and summaries
- **`.gitignore`**: Updated to exclude development files and temporary results

### 🧹 Cleaned Up Files
- Removed development tracking files (`tasks.json`, `metrics.db`)
- Moved performance and test results to organized folders
- Fixed bogus dates in documentation files
- Updated GitHub Actions to use latest artifact upload version

### 📸 Dashboard Images
- Generated placeholder dashboard images for documentation
- Actual screenshots can be captured using the provided automation script

## Support

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
