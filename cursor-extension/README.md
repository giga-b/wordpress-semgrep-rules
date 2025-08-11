# WordPress Semgrep Security for Cursor

🔒 **Real-time WordPress security scanning with Semgrep for Cursor IDE**

This extension provides comprehensive WordPress security scanning directly in Cursor IDE, helping developers identify and fix security vulnerabilities in their WordPress plugins and themes.

## Features

### 🔍 **Real-time Security Scanning**
- **File Scanning**: Scan individual PHP files for security issues
- **Workspace Scanning**: Scan entire workspace for comprehensive security analysis
- **Auto-scan on Save**: Automatically scan files when they are saved
- **Real-time Diagnostics**: Inline security issue display with live updates

### 🛠️ **Quick Fixes**
- **Automatic Fixes**: Apply security fixes with one click
- **Context-aware Suggestions**: Intelligent fix recommendations based on issue type
- **WordPress Best Practices**: Follow WordPress security guidelines

### 📊 **Enhanced UI**
- **Security Problems Panel**: Dedicated panel for viewing all security issues
- **Status Dashboard**: Real-time status of Semgrep and extension features
- **Inline Diagnostics**: Security issues displayed directly in the editor
- **Severity-based Filtering**: Filter issues by error, warning, or info level

### ⚙️ **Configuration**
- **Custom Rules**: Use your own Semgrep rules
- **Severity Levels**: Configure minimum severity for displayed issues
- **Performance Settings**: Adjust timeout and memory limits
- **Feature Toggles**: Enable/disable specific features

## Installation

### Prerequisites
- Cursor IDE
- Node.js (for development)
- Semgrep (will be installed automatically if not present)

### Install Extension
1. Clone this repository
2. Navigate to the `cursor-extension` directory
3. Run `npm install` to install dependencies
4. Run `npm run compile` to build the extension
5. Install the extension in Cursor

### Install Semgrep
The extension will automatically detect if Semgrep is installed and offer to install it if needed.

## Usage

### Basic Commands

| Command | Shortcut | Description |
|---------|----------|-------------|
| Scan Current File | `Ctrl+Shift+S` | Scan the currently open PHP file |
| Scan Workspace | - | Scan all PHP files in the workspace |
| Show Problems | - | Open the security problems panel |
| Quick Fix | `Ctrl+Shift+F` | Apply quick fix at cursor position |
| Configure Rules | - | Open configuration dialog |

### Configuration

Access configuration through:
- Command Palette: `WordPress Security: Configure Rules`
- Settings: Search for "WordPress Semgrep" in settings

#### Key Settings

- **Enabled**: Enable/disable the extension
- **Auto-scan**: Automatically scan files on save
- **Show Inline**: Display security issues inline in the editor
- **Quick Fix Enabled**: Enable automatic quick fixes
- **Config Path**: Path to custom Semgrep configuration
- **Rules Path**: Path to custom Semgrep rules
- **Severity**: Minimum severity level (error, warning, info)
- **Max Problems**: Maximum number of issues to display
- **Timeout**: Scan timeout in seconds

### Security Issue Types

The extension detects various WordPress security issues:

#### 🔐 **Authentication & Authorization**
- Missing nonce verification
- Insufficient capability checks
- Improper user role validation

#### 🛡️ **Input Validation & Sanitization**
- Unsanitized user input
- Missing input validation
- Improper data sanitization

#### 💉 **SQL Injection**
- Direct SQL queries with user input
- Missing prepared statements
- Unsafe database operations

#### 🌐 **Cross-Site Scripting (XSS)**
- Unescaped output
- Missing output sanitization
- Unsafe HTML rendering

#### 🔗 **CSRF Protection**
- Missing nonce tokens
- Improper token validation
- Unsafe form handling

## Quick Fixes

The extension provides automatic fixes for common security issues:

### Nonce Verification
```php
// Before
if (isset($_POST['submit'])) {
    // Process form
}

// After (Quick Fix)
if (!wp_verify_nonce($_POST['_wpnonce'], 'action_name')) {
    wp_die('Security check failed');
}
if (isset($_POST['submit'])) {
    // Process form
}
```

### Capability Check
```php
// Before
function admin_function() {
    // Admin functionality
}

// After (Quick Fix)
function admin_function() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    // Admin functionality
}
```

### Input Sanitization
```php
// Before
$user_input = $_POST['user_data'];

// After (Quick Fix)
$user_input = sanitize_text_field($_POST['user_data']);
```

## Development

### Building the Extension

```bash
# Install dependencies
npm install

# Compile TypeScript
npm run compile

# Watch for changes
npm run watch

# Run tests
npm test

# Package extension
npm run package
```

### Project Structure

```
cursor-extension/
├── src/
│   ├── extension.ts              # Main extension entry point
│   ├── semgrepScanner.ts         # Semgrep integration
│   ├── problemProvider.ts        # Security issues display
│   ├── statusProvider.ts         # Status dashboard
│   ├── configurationManager.ts   # Settings management
│   ├── semgrepInstaller.ts       # Semgrep installation
│   ├── quickFixProvider.ts       # Automatic fixes
│   └── inlineDiagnosticsProvider.ts # Inline issue display
├── resources/
│   ├── icon-128.png              # Extension icon (128x128)
│   └── icon.svg                  # Extension icon (SVG)
├── package.json                  # Extension manifest
└── README.md                     # This file
```

### Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## Troubleshooting

### Common Issues

#### Semgrep Not Found
- Run "WordPress Security: Install Semgrep" from command palette
- Ensure Semgrep is in your system PATH
- Check Semgrep installation: `semgrep --version`

#### No Issues Found
- Verify you're scanning PHP files
- Check Semgrep configuration
- Ensure rules are properly configured
- Check severity settings

#### Performance Issues
- Increase timeout in settings
- Reduce max problems limit
- Disable auto-scan for large files
- Use workspace scanning instead of file scanning

#### Extension Not Working
- Check Cursor IDE version compatibility
- Restart Cursor IDE
- Check extension logs in Developer Tools
- Verify configuration settings

### Debug Mode

Enable debug mode to see detailed logs:

1. Open Command Palette (`Ctrl+Shift+P`)
2. Run "Developer: Toggle Developer Tools"
3. Check Console tab for extension logs

## Security

This extension:
- Runs Semgrep locally on your machine
- Does not send code to external servers
- Respects your privacy and data
- Uses only official Semgrep rules and configurations

## License

MIT License - see LICENSE file for details.

## Support

- **Issues**: Report bugs and feature requests on GitHub
- **Documentation**: Check the main project documentation
- **Community**: Join the WordPress security community

## Changelog

### v1.0.0
- Initial release
- Real-time security scanning
- Quick fixes for common issues
- Inline diagnostics
- Status dashboard
- Configuration management
- Auto-scan on save
- Workspace scanning

---

🔒 **Secure your WordPress code with confidence!**
