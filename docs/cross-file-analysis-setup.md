# Cross-File Analysis Setup Guide

## Overview

This guide helps you set up cross-file analysis for the WordPress Semgrep Rules project using Semgrep AppSec Platform.

## Prerequisites

- Semgrep CLI installed
- Semgrep AppSec Platform account (free for up to 10 contributors)
- Internet connection for account setup

## Step-by-Step Setup

### 1. Install Semgrep CLI

**Windows:**
```bash
python -m pip install semgrep
```

**macOS:**
```bash
brew install semgrep
```

**Linux:**
```bash
python3 -m pip install semgrep
```

### 2. Create Semgrep AppSec Platform Account

1. Go to [https://semgrep.dev/login](https://semgrep.dev/login)
2. Click "Sign up" to create a free account
3. Verify your email address
4. Create an organization (free for up to 10 contributors)

### 3. Login to Semgrep

```bash
semgrep login
```

This will open a browser window for authentication.

### 4. Enable Cross-File Analysis

1. Go to [https://semgrep.dev/orgs/-/settings/general/code](https://semgrep.dev/orgs/-/settings/general/code)
2. Find the "Cross-file analysis" section
3. Toggle the switch to "ON"
4. Save the settings

### 5. Test Configuration

Run the test script to verify everything is working:

```bash
python tests/test-cross-file-analysis.py
```

## Troubleshooting

### Common Issues

**"Not logged in" error:**
- Run `semgrep login` and follow the browser authentication

**"Cross-file analysis not enabled" error:**
- Ensure you've enabled cross-file analysis in the web interface
- Check that you're using `semgrep ci` instead of `semgrep scan`

**"Permission denied" error:**
- Ensure you have write permissions in the project directory
- Try running with elevated privileges if necessary

### Getting Help

- [Semgrep Documentation](https://semgrep.dev/docs)
- [Semgrep Community Slack](https://go.semgrep.dev/slack)
- [Project Issues](https://github.com/your-repo/issues)

## Next Steps

After setup, you can:
1. Run cross-file analysis scans with `semgrep ci`
2. Use the Python test scripts for additional validation
3. Customize rules for your specific needs
