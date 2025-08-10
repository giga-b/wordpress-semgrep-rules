# VS Code Extension Integration Guide

This document provides comprehensive information about the WordPress Semgrep Security VS Code extension, including installation, configuration, and usage.

## Overview

The WordPress Semgrep Security extension provides real-time security scanning for WordPress development in VS Code. It integrates with the WordPress Semgrep rules repository to detect security vulnerabilities in PHP code.

## Features

### 🔍 Real-time Security Scanning
- **File Scanning**: Scan individual PHP files for security issues
- **Workspace Scanning**: Scan entire workspaces for comprehensive security analysis
- **Auto-scan on Save**: Automatically scan files when they are saved
- **Real-time Feedback**: Instant security issue detection and reporting

### 🛡️ WordPress-Specific Security Rules
- **Nonce Verification**: Detect missing or improper nonce usage
- **Capability Checks**: Identify missing user capability verification
- **Input Sanitization**: Find unsanitized user input usage
- **SQL Injection Prevention**: Detect potential SQL injection vulnerabilities
- **XSS Prevention**: Identify cross-site scripting vulnerabilities
- **REST API Security**: Validate REST API endpoint security
- **AJAX Security**: Check AJAX endpoint security patterns

### 📊 Visual Problem Management
- **Tree View**: Hierarchical display of security issues by file
- **Status Panel**: Real-time status and scan information
- **Severity Filtering**: Filter issues by error, warning, or info level
- **Click-to-Navigate**: Click on issues to jump to the exact line in code

## Installation

### Prerequisites
- Visual Studio Code 1.74.0 or higher
- Node.js (for development)
- Semgrep (will be installed automatically if not present)

### From VSIX Package
1. Download the `.vsix` package from the releases
2. In VS Code, go to Extensions (Ctrl+Shift+X)
3. Click the "..." menu and select "Install from VSIX..."
4. Select the downloaded package
5. Restart VS Code

### From Source
1. Clone the repository
2. Navigate to the `vscode-extension` directory
3. Run `npm install`
4. Run `npm run compile`
5. Press F5 to launch the extension in a new VS Code window

## Configuration

### Extension Settings

The extension can be configured through VS Code settings:

```json
{
  "wordpressSemgrep.enabled": true,
  "wordpressSemgrep.autoScan": true,
  "wordpressSemgrep.configPath": "",
  "wordpressSemgrep.rulesPath": "",
  "wordpressSemgrep.severity": "warning",
  "wordpressSemgrep.maxProblems": 100,
  "wordpressSemgrep.timeout": 30
}
```

### Settings Description

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| `enabled` | boolean | `true` | Enable/disable the extension |
| `autoScan` | boolean | `true` | Automatically scan files on save |
| `configPath` | string | `""` | Path to custom Semgrep configuration file |
| `rulesPath` | string | `""` | Path to custom Semgrep rules directory |
| `severity` | string | `"warning"` | Minimum severity level (error, warning, info) |
| `maxProblems` | number | `100` | Maximum number of problems to display |
| `timeout` | number | `30` | Scan timeout in seconds |

### Configuration Methods

#### Method 1: VS Code Settings UI
1. Open VS Code Settings (Ctrl+,)
2. Search for "WordPress Semgrep"
3. Configure the desired settings

#### Method 2: Settings JSON
1. Open VS Code Settings (Ctrl+,)
2. Click the "Open Settings (JSON)" icon
3. Add the configuration to your settings.json

#### Method 3: Workspace Settings
1. Create a `.vscode/settings.json` file in your workspace
2. Add the configuration for workspace-specific settings

## Usage

### First Time Setup
1. Open a PHP file or workspace
2. The extension will automatically check if Semgrep is installed
3. If not installed, click "Install Semgrep" when prompted
4. Configure your security rules path (optional)

### Commands

#### Available Commands
- **Scan Current File**: `Ctrl+Shift+P` → "WordPress Semgrep: Scan Current File"
- **Scan Workspace**: `Ctrl+Shift+P` → "WordPress Semgrep: Scan Workspace"
- **Show Problems**: `Ctrl+Shift+P` → "WordPress Semgrep: Show Security Problems"
- **Configure Rules**: `Ctrl+Shift+P` → "WordPress Semgrep: Configure Security Rules"
- **Install Semgrep**: `Ctrl+Shift+P` → "WordPress Semgrep: Install Semgrep"

#### Command Palette Usage
1. Press `Ctrl+Shift+P` to open the command palette
2. Type "WordPress Semgrep" to see available commands
3. Select the desired command

#### Context Menu
- Right-click on a PHP file in the explorer
- Select "Scan with WordPress Semgrep"

### Views

#### Security Issues View
- Located in the activity bar under "WordPress Security"
- Shows all detected security problems
- Problems are grouped by file
- Click on a problem to navigate to the exact line

#### Status View
- Shows extension status and scan information
- Displays last scan time and total issues
- Shows Semgrep version information

### Auto-scanning
When enabled, the extension will automatically scan PHP files when they are saved. This provides real-time feedback on security issues.

## Security Rules

The extension uses the WordPress Semgrep rules repository, which includes:

### Core Security Rules
- **Nonce Verification**: `wp-nonce-verification`
- **Capability Checks**: `wp-capability-checks`
- **Input Sanitization**: `wp-sanitization`
- **SQL Injection**: `wp-sql-injection`
- **XSS Prevention**: `wp-xss-prevention`

### Advanced Rules
- **REST API Security**: `wp-rest-api-security`
- **AJAX Security**: `wp-ajax-security`
- **File Upload Security**: `wp-file-upload-security`
- **Authentication Bypass**: `wp-auth-bypass`

### Custom Rules
You can use custom Semgrep rules by:
1. Setting the `rulesPath` configuration
2. Pointing to a directory containing your custom rules
3. Rules should be in YAML format following Semgrep conventions

## Troubleshooting

### Common Issues

#### Semgrep Not Found
**Problem**: Extension shows "Semgrep is not installed"
**Solution**:
1. Run "WordPress Semgrep: Install Semgrep" command
2. Ensure Python and pip are installed
3. Check PATH environment variable
4. Restart VS Code after installation

#### No Issues Found
**Problem**: Extension doesn't detect any issues
**Solution**:
1. Verify the file is a PHP file
2. Check severity level settings
3. Ensure rules are properly configured
4. Check if the file contains actual security issues

#### Slow Scanning
**Problem**: Scans take too long
**Solution**:
1. Increase timeout setting
2. Reduce max problems limit
3. Use more specific file patterns
4. Exclude large directories

#### False Positives
**Problem**: Extension reports issues that aren't actually problems
**Solution**:
1. Review rule configuration
2. Add custom rule exclusions
3. Report issues for rule improvement
4. Adjust severity levels

### Debug Mode
Enable debug logging by adding to VS Code settings:
```json
{
  "wordpressSemgrep.debug": true
}
```

### Logs
Extension logs can be found in:
- **Windows**: `%APPDATA%\Code\logs`
- **macOS**: `~/Library/Application Support/Code/logs`
- **Linux**: `~/.config/Code/logs`

## Development

### Building the Extension
```bash
cd vscode-extension
npm install
npm run compile
```

### Testing
```bash
npm run test
```

### Debugging
1. Open the extension in VS Code
2. Press F5 to launch a new VS Code window with the extension
3. Use the debug console to see extension logs

### Packaging
```bash
npm install -g @vscode/vsce
vsce package
```

## Integration with CI/CD

The extension can be integrated with CI/CD pipelines:

### GitHub Actions
```yaml
name: Security Scan
on: [push, pull_request]
jobs:
  security-scan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Install Semgrep
        run: pip install semgrep
      - name: Run Security Scan
        run: semgrep --config p/wordpress .
```

### GitLab CI
```yaml
security-scan:
  stage: test
  script:
    - pip install semgrep
    - semgrep --config p/wordpress .
```

## Best Practices

### For Developers
1. **Enable Auto-scan**: Keep auto-scan enabled for real-time feedback
2. **Review Issues**: Regularly review and address security issues
3. **Custom Rules**: Create custom rules for project-specific patterns
4. **Severity Levels**: Use appropriate severity levels for your project

### For Teams
1. **Consistent Configuration**: Use workspace settings for consistent configuration
2. **Code Reviews**: Include security scanning in code review processes
3. **Training**: Train team members on security best practices
4. **Documentation**: Document custom rules and configurations

### For Organizations
1. **Policy Enforcement**: Use the extension to enforce security policies
2. **Compliance**: Ensure compliance with security standards
3. **Monitoring**: Monitor security issue trends over time
4. **Integration**: Integrate with existing security tools and processes

## Support

### Getting Help
- **Issues**: Report bugs and feature requests on GitHub
- **Documentation**: See the main project documentation
- **Community**: Join the WordPress security community

### Contributing
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## Changelog

### 1.0.0
- Initial release
- Real-time security scanning
- WordPress-specific rules integration
- Visual problem management
- Cross-platform Semgrep installation
- Configuration management
- Auto-scan capabilities

## Acknowledgments

- [Semgrep](https://semgrep.dev/) for the static analysis engine
- [WordPress Security Team](https://make.wordpress.org/security/) for security guidance
- [OWASP](https://owasp.org/) for security best practices
- [VS Code Extension API](https://code.visualstudio.com/api) for the extension framework
