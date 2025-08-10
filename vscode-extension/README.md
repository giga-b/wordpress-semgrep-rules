# WordPress Semgrep Security - VS Code Extension

A comprehensive VS Code extension for real-time WordPress security scanning using Semgrep.

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

### ⚙️ Configuration Management
- **Custom Rules**: Use custom Semgrep rule sets
- **Severity Levels**: Configure minimum severity for displayed issues
- **Scan Timeouts**: Set custom timeout values for large scans
- **Auto-scan Toggle**: Enable/disable automatic scanning

### 🔧 Easy Setup
- **Automatic Semgrep Installation**: One-click Semgrep installation
- **Cross-platform Support**: Works on Windows, macOS, and Linux
- **Configuration Wizard**: Guided setup for optimal configuration

## Installation

### Prerequisites
- Visual Studio Code 1.74.0 or higher
- Node.js (for development)
- Semgrep (will be installed automatically if not present)

### From VSIX Package
1. Download the `.vsix` package
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

## Usage

### First Time Setup
1. Open a PHP file or workspace
2. The extension will automatically check if Semgrep is installed
3. If not installed, click "Install Semgrep" when prompted
4. Configure your security rules path (optional)

### Basic Commands
- **Scan Current File**: `Ctrl+Shift+P` → "WordPress Semgrep: Scan Current File"
- **Scan Workspace**: `Ctrl+Shift+P` → "WordPress Semgrep: Scan Workspace"
- **Show Problems**: `Ctrl+Shift+P` → "WordPress Semgrep: Show Security Problems"
- **Configure Rules**: `Ctrl+Shift+P` → "WordPress Semgrep: Configure Security Rules"

### Context Menu
- Right-click on a PHP file in the explorer
- Select "Scan with WordPress Semgrep"

### Views
- **Security Issues**: View all detected security problems
- **Status**: Monitor extension status and scan information

## Configuration

### Extension Settings
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
- **enabled**: Enable/disable the extension
- **autoScan**: Automatically scan files on save
- **configPath**: Path to custom Semgrep configuration file
- **rulesPath**: Path to custom Semgrep rules directory
- **severity**: Minimum severity level (error, warning, info)
- **maxProblems**: Maximum number of problems to display
- **timeout**: Scan timeout in seconds

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

## Troubleshooting

### Common Issues

#### Semgrep Not Found
- Run "WordPress Semgrep: Install Semgrep" command
- Ensure Python and pip are installed
- Check PATH environment variable

#### No Issues Found
- Verify the file is a PHP file
- Check severity level settings
- Ensure rules are properly configured

#### Slow Scanning
- Increase timeout setting
- Reduce max problems limit
- Use more specific file patterns

#### False Positives
- Review rule configuration
- Add custom rule exclusions
- Report issues for rule improvement

### Debug Mode
Enable debug logging by adding to VS Code settings:
```json
{
  "wordpressSemgrep.debug": true
}
```

## Development

### Building the Extension
```bash
npm install
npm run compile
npm run watch  # For development
```

### Testing
```bash
npm run test
```

### Packaging
```bash
npm run package
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

MIT License - see LICENSE file for details

## Support

- **Issues**: Report bugs and feature requests on GitHub
- **Documentation**: See the main project documentation
- **Community**: Join the WordPress security community

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
