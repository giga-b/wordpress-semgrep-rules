# WordPress Semgrep Security Rules

[![CI](https://github.com/giga-b/wordpress-semgrep-rules/workflows/CI/badge.svg)](https://github.com/giga-b/wordpress-semgrep-rules/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Semgrep](https://img.shields.io/badge/Semgrep-1.45.0+-blue.svg)](https://semgrep.dev/)

Comprehensive security scanning rules for WordPress plugin and theme development.

## Quick Start

> **Note**: This project is now live on GitHub! Check out the [repository](https://github.com/giga-b/wordpress-semgrep-rules) for the latest updates.

> **⚠️ Security Notice**: Before using the auto-fix system, please review our [Security Considerations](docs/SECURITY-CONSIDERATIONS.md) guide.

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
├── packs/
│   ├── wp-core-security/     # Core WordPress security rules
│   ├── wp-core-quality/      # WordPress code quality rules
│   └── experimental/         # Experimental and plugin-specific rules
├── configs/
│   ├── basic.yaml           # Essential security rules
│   ├── strict.yaml          # Comprehensive security rules
│   └── plugin-development.yaml # Plugin development focus
├── tests/
│   ├── vulnerable-examples/ # Test cases that should trigger rules
│   └── safe-examples/       # Test cases that should NOT trigger rules
├── tooling/
│   ├── generate_rules.py    # Rule generation script
│   ├── run-semgrep.ps1      # Windows runner
│   └── run-semgrep.sh       # Unix runner
└── docs/                    # Documentation
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

## Support

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
