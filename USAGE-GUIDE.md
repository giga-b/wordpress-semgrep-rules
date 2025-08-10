# WordPress Semgrep Rules - Usage Guide

This guide explains how to access and use the VS Code extension and HTML dashboard that are included in this project.

## 🚀 VS Code Extension

### What You Get
A fully functional VS Code extension called "WordPress Semgrep Security" that provides:
- Real-time security scanning of PHP files
- Integration with your custom WordPress security rules
- Command palette integration
- Problem panel integration
- Automatic scanning on file save

### How to Install

#### Option 1: Install from Local Package (Recommended)
1. **Download the extension package:**
   ```bash
   # The extension is already packaged and ready to use
   # Location: vscode-extension/wordpress-semgrep-security-1.0.0.vsix
   ```

2. **Install in VS Code:**
   - Open VS Code
   - Go to Extensions (Ctrl+Shift+X)
   - Click the "..." menu (three dots) in the Extensions panel
   - Select "Install from VSIX..."
   - Choose the file: `vscode-extension/wordpress-semgrep-security-1.0.0.vsix`
   - Restart VS Code when prompted

#### Option 2: Install from Source (For Developers)
```bash
cd vscode-extension
npm install
npm run compile
# Then follow Option 1 to install the generated .vsix file
```

### How to Use the Extension

1. **Open a PHP file** in VS Code
2. **Use the Command Palette** (Ctrl+Shift+P):
   - `WordPress Semgrep: Scan Current File` - Scan the current PHP file
   - `WordPress Semgrep: Scan Workspace` - Scan all PHP files in the workspace
   - `WordPress Semgrep: Show Security Problems` - View all security issues
   - `WordPress Semgrep: Configure Security Rules` - Configure rule paths

3. **Automatic Scanning**: The extension will automatically scan files when you save them (if enabled)

4. **View Results**: Security issues will appear in the Problems panel (Ctrl+Shift+M)

### Configuration
The extension can be configured in VS Code settings:
- `wordpressSemgrep.enabled`: Enable/disable the extension
- `wordpressSemgrep.autoScan`: Enable automatic scanning on save
- `wordpressSemgrep.configPath`: Path to your Semgrep configuration file
- `wordpressSemgrep.rulesPath`: Path to your custom rules directory
- `wordpressSemgrep.severity`: Minimum severity level to display

## 📊 HTML Dashboard

### What You Get
A beautiful, interactive web dashboard that displays:
- Security scan metrics and statistics
- Performance data and trends
- Rule effectiveness analysis
- Visual charts and graphs

### How to Access

#### Option 1: Use the Python Server Script (Recommended)
```bash
# Run the dashboard server
python serve-dashboard.py

# Then open your browser to:
# http://localhost:8080
```

#### Option 2: Use Any Web Server
```bash
# Navigate to the dashboard directory
cd dashboard

# Use Python's built-in server
python -m http.server 8080

# Or use any other web server of your choice
```

#### Option 3: Open Directly in Browser
Simply open `dashboard/index.html` in your web browser (some features may not work due to CORS restrictions).

### Dashboard Features
- **Metrics Overview**: Key statistics about your security scans
- **Performance Charts**: Visual representation of scan performance
- **Rule Analysis**: Effectiveness of different security rules
- **Trend Analysis**: Historical performance data

## 🔧 For External Users

### If You Found This Project Online

#### Getting the VS Code Extension:
1. **Clone or download** this repository
2. **Navigate to the extension directory:**
   ```bash
   cd wordpress-semgrep-rules/vscode-extension
   ```
3. **Install the extension** using the `.vsix` file as described above

#### Getting the HTML Dashboard:
1. **Clone or download** this repository
2. **Run the dashboard server:**
   ```bash
   cd wordpress-semgrep-rules
   python serve-dashboard.py
   ```
3. **Open your browser** to `http://localhost:8080`

## 🚀 Publishing to VS Code Marketplace

### To Make the Extension Publicly Available:

1. **Prepare for Publishing:**
   ```bash
   cd vscode-extension
   # Update package.json with your publisher information
   # Add a proper README.md
   # Ensure all dependencies are correct
   ```

2. **Create a Publisher Account:**
   - Go to https://marketplace.visualstudio.com/
   - Sign in with your Microsoft account
   - Create a publisher account

3. **Publish the Extension:**
   ```bash
   # Login to your publisher account
   vsce login <your-publisher-name>
   
   # Publish the extension
   vsce publish
   ```

4. **After Publishing:**
   - Users can install directly from VS Code Extensions marketplace
   - Search for "WordPress Semgrep Security"
   - Click Install

## 📝 Troubleshooting

### VS Code Extension Issues:
- **Extension not loading**: Check VS Code console for errors
- **Rules not found**: Verify the `wordpressSemgrep.rulesPath` setting
- **Semgrep not found**: Run `WordPress Semgrep: Install Semgrep` command

### Dashboard Issues:
- **Page not loading**: Check if the server is running on the correct port
- **Charts not displaying**: Ensure you're accessing via HTTP server, not file:// protocol
- **No data**: The dashboard shows sample data; connect it to your actual scan results

## 🎯 Next Steps

1. **Customize the rules** in the `packs/` directory
2. **Configure the extension** to use your specific rule sets
3. **Integrate the dashboard** with your actual scan results
4. **Publish the extension** to make it available to the WordPress community

## 📞 Support

- **Issues**: Create an issue on the GitHub repository
- **Documentation**: Check the `docs/` directory for detailed guides
- **Configuration**: See `configs/` directory for example configurations
