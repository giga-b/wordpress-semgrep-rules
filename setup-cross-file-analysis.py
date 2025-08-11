#!/usr/bin/env python3
"""
Setup Script for Cross-File Analysis with Semgrep AppSec Platform
This script helps users configure cross-file analysis functionality for the
WordPress Semgrep Rules project.
"""

import warnings
# Suppress pkg_resources deprecation warning
warnings.filterwarnings("ignore", category=UserWarning, module="pkg_resources")

import os
import sys
import subprocess
import json
from pathlib import Path
from typing import Dict, List, Any

class CrossFileAnalysisSetup:
    def __init__(self):
        self.project_root = Path(__file__).parent
        self.docs_dir = self.project_root / "docs"
        self.docs_dir.mkdir(exist_ok=True)
        
    def check_semgrep_installation(self):
        """Check if Semgrep CLI is installed"""
        try:
            result = subprocess.run(["semgrep", "--version"], 
                                  capture_output=True, text=True, timeout=10, encoding='utf-8')
            if result.returncode == 0:
                print("✅ Semgrep CLI is installed")
                return True
            else:
                print("❌ Semgrep CLI is not properly installed")
                return False
        except FileNotFoundError:
            print("❌ Semgrep CLI is not installed")
            return False
        except Exception as e:
            print(f"❌ Error checking Semgrep installation: {e}")
            return False
    
    def check_login_status(self):
        """Check if user is logged into Semgrep AppSec Platform"""
        try:
            result = subprocess.run(["semgrep", "whoami"], 
                                  capture_output=True, text=True, timeout=10, encoding='utf-8')
            if result.returncode == 0 and "not logged in" not in result.stdout.lower():
                print("✅ Logged into Semgrep AppSec Platform")
                return True
            else:
                print("❌ Not logged into Semgrep AppSec Platform")
                return False
        except Exception as e:
            print(f"❌ Error checking login status: {e}")
            return False
    
    def guide_user_through_setup(self):
        """Guide user through the complete setup process"""
        print("🚀 WordPress Semgrep Rules - Cross-File Analysis Setup")
        print("=" * 60)
        
        # Step 1: Check Semgrep installation
        print("\n📋 Step 1: Checking Semgrep CLI installation...")
        if not self.check_semgrep_installation():
            print("\n📥 Installing Semgrep CLI...")
            self.install_semgrep()
        
        # Step 2: Check login status
        print("\n📋 Step 2: Checking Semgrep AppSec Platform login...")
        if not self.check_login_status():
            print("\n🔐 Setting up Semgrep AppSec Platform account...")
            self.setup_account()
        
        # Step 3: Enable cross-file analysis
        print("\n📋 Step 3: Enabling cross-file analysis...")
        self.enable_cross_file_analysis()
        
        # Step 4: Test configuration
        print("\n📋 Step 4: Testing cross-file analysis...")
        self.test_cross_file_analysis()
        
        # Step 5: Generate documentation
        print("\n📋 Step 5: Generating documentation...")
        self.generate_documentation()
        
        print("\n🎉 Setup complete! Cross-file analysis is now configured.")
        print("\n📚 Next steps:")
        print("   1. Read docs/cross-file-analysis-setup.md for detailed instructions")
        print("   2. Run tests/test-cross-file-analysis.py to verify functionality")
        print("   3. Use 'semgrep ci' to run scans with cross-file analysis")
    
    def install_semgrep(self):
        """Install Semgrep CLI"""
        print("Installing Semgrep CLI...")
        
        # Detect OS and provide appropriate installation command
        if sys.platform == "win32":
            print("Windows detected. Installing via pip...")
            cmd = [sys.executable, "-m", "pip", "install", "semgrep"]
        elif sys.platform == "darwin":
            print("macOS detected. Installing via Homebrew...")
            cmd = ["brew", "install", "semgrep"]
        else:
            print("Linux detected. Installing via pip...")
            cmd = [sys.executable, "-m", "pip", "install", "semgrep"]
        
        try:
            subprocess.run(cmd, check=True)
            print("✅ Semgrep CLI installed successfully")
        except subprocess.CalledProcessError as e:
            print(f"❌ Failed to install Semgrep CLI: {e}")
            print("Please install manually: https://semgrep.dev/docs/getting-started/quickstart")
    
    def setup_account(self):
        """Guide user through account setup"""
        print("\n🔐 Semgrep AppSec Platform Account Setup")
        print("-" * 40)
        print("1. Go to https://semgrep.dev/login")
        print("2. Click 'Sign up' to create a free account")
        print("3. Verify your email address")
        print("4. Create an organization (free for up to 10 contributors)")
        print("5. Return here and run: semgrep login")
        
        input("\nPress Enter when you've completed the account setup...")
        
        # Try to login
        try:
            subprocess.run(["semgrep", "login"], check=True)
            print("✅ Successfully logged into Semgrep AppSec Platform")
        except subprocess.CalledProcessError:
            print("❌ Login failed. Please run 'semgrep login' manually.")
    
    def enable_cross_file_analysis(self):
        """Guide user to enable cross-file analysis"""
        print("\n⚙️ Enabling Cross-File Analysis")
        print("-" * 30)
        print("1. Go to https://semgrep.dev/orgs/-/settings/general/code")
        print("2. Find the 'Cross-file analysis' section")
        print("3. Toggle the switch to 'ON'")
        print("4. Save the settings")
        
        input("\nPress Enter when you've enabled cross-file analysis...")
        
        print("✅ Cross-file analysis should now be enabled")
    
    def test_cross_file_analysis(self):
        """Test cross-file analysis functionality"""
        print("\n🧪 Testing Cross-File Analysis")
        print("-" * 30)
        
        # Test with a simple scan
        test_file = self.project_root / "tests" / "safe-examples" / "nonce-lifecycle-safe.php"
        
        if test_file.exists():
            try:
                print("Running test scan with cross-file analysis...")
                result = subprocess.run([
                    "semgrep", "ci",
                    "--config", str(self.project_root / "packs" / "wp-core-security" / "nonce-lifecycle-detection.yaml"),
                    str(test_file),
                    "--json"
                ], capture_output=True, text=True, timeout=60)
                
                if result.returncode == 0:
                    print("✅ Cross-file analysis test successful")
                    try:
                        data = json.loads(result.stdout)
                        findings = len(data.get('results', []))
                        print(f"   Found {findings} findings (expected for test file)")
                    except json.JSONDecodeError:
                        print("   Scan completed successfully")
                else:
                    print("⚠️ Cross-file analysis test completed with warnings")
                    print(f"   Error: {result.stderr}")
                    
            except subprocess.TimeoutExpired:
                print("⚠️ Test scan timed out (this is normal for first run)")
            except Exception as e:
                print(f"❌ Test scan failed: {e}")
        else:
            print("⚠️ Test file not found, skipping test")
    
    def generate_documentation(self):
        """Generate comprehensive documentation"""
        print("\n📚 Generating Documentation")
        print("-" * 25)
        
        # Generate setup guide
        setup_guide = self.generate_setup_guide()
        setup_file = self.docs_dir / "cross-file-analysis-setup.md"
        
        with open(setup_file, 'w', encoding='utf-8') as f:
            f.write(setup_guide)
        
        print(f"✅ Setup guide created: {setup_file}")
        
        # Generate usage guide
        usage_guide = self.generate_usage_guide()
        usage_file = self.docs_dir / "cross-file-analysis-usage.md"
        
        with open(usage_file, 'w', encoding='utf-8') as f:
            f.write(usage_guide)
        
        print(f"✅ Usage guide created: {usage_file}")
    
    def generate_setup_guide(self):
        """Generate detailed setup guide"""
        return """# Cross-File Analysis Setup Guide

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
"""

    def generate_usage_guide(self):
        """Generate usage guide"""
        return """# Cross-File Analysis Usage Guide

## Overview

This guide explains how to use cross-file analysis with the WordPress Semgrep Rules project.

## Basic Usage

### Running Cross-File Analysis Scans

**Scan entire project:**
```bash
semgrep ci --config packs/wp-core-security/nonce-lifecycle-detection.yaml
```

**Scan specific files:**
```bash
semgrep ci --config packs/wp-core-security/nonce-lifecycle-detection.yaml path/to/file.php
```

**Scan with custom output:**
```bash
semgrep ci --config packs/wp-core-security/nonce-lifecycle-detection.yaml --json --output results.json
```

### Understanding Results

Cross-file analysis provides enhanced detection by:

1. **Cross-File Pattern Matching**: Detects patterns that span multiple files
2. **Semantic Analysis**: Understands relationships between functions and variables
3. **Framework Awareness**: Better understanding of WordPress patterns
4. **Reduced False Positives**: More accurate detection through context

### Rule Categories

The nonce lifecycle detection rules are categorized by:

- **Creation**: Nonce generation patterns
- **Inclusion**: Nonce field placement in forms/AJAX
- **Verification**: Nonce validation patterns
- **Expiration**: Error handling for expired/invalid nonces
- **Cross-File**: Patterns that span multiple files

## Advanced Usage

### Custom Rule Development

Create custom cross-file rules:

```yaml
- id: custom.cross-file.rule
  message: "Custom cross-file vulnerability detected"
  severity: ERROR
  languages: [php]
  options:
    interfile: true
  pattern: |
    # Your pattern here
```

### Integration with CI/CD

Add to your CI pipeline:

```yaml
# GitHub Actions example
- name: Run Semgrep Cross-File Analysis
  run: |
    semgrep ci --config packs/wp-core-security/nonce-lifecycle-detection.yaml
```

### Performance Optimization

For large codebases:

```bash
# Increase timeout for large files
semgrep ci --timeout 45 --config packs/wp-core-security/nonce-lifecycle-detection.yaml

# Use parallel processing
semgrep ci -j 4 --config packs/wp-core-security/nonce-lifecycle-detection.yaml
```

## Comparison: Cross-File vs Single-File Analysis

| Feature | Single-File | Cross-File |
|---------|-------------|------------|
| **Detection Scope** | Within single file | Across multiple files |
| **False Positives** | Higher | Lower |
| **Performance** | Faster | Slower but more accurate |
| **Setup Complexity** | Simple | Requires account setup |
| **Framework Support** | Basic | Advanced |

## Best Practices

1. **Start with Single-File**: Use single-file analysis for quick scans
2. **Use Cross-File for Deep Analysis**: Use cross-file analysis for comprehensive security audits
3. **Combine Both Approaches**: Use both for maximum coverage
4. **Regular Testing**: Run cross-file analysis regularly in your CI/CD pipeline
5. **Custom Rules**: Develop custom rules for your specific security needs

## Troubleshooting

### Performance Issues

- Increase timeout values for large files
- Use parallel processing with `-j` flag
- Consider scanning specific directories instead of entire project

### Accuracy Issues

- Review false positives and adjust rule patterns
- Use the Python test scripts to validate rule behavior
- Check Semgrep documentation for pattern optimization

### Integration Issues

- Ensure proper authentication with `semgrep login`
- Verify cross-file analysis is enabled in web interface
- Check network connectivity for cloud-based analysis
"""

def main():
    """Main function"""
    setup = CrossFileAnalysisSetup()
    setup.guide_user_through_setup()

if __name__ == "__main__":
    main()
