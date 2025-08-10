# Pre-commit Hook Guide

This guide explains how to set up and use the pre-commit hooks for the WordPress Semgrep Rules project.

## Overview

The pre-commit hooks provide automated security scanning, code quality checks, and validation before each commit. This ensures that all code meets the project's security and quality standards.

## What's Included

The pre-commit configuration includes the following hooks:

### Security Scanning
- **Semgrep Basic Security Scan**: Runs essential security rules with basic configuration
- **Semgrep Plugin Development Scan**: Runs comprehensive security rules with plugin development configuration
- **Secrets Detection**: Detects secrets and sensitive data in code

### Code Quality
- **YAML Lint**: Validates YAML syntax in configuration files
- **PHP Syntax Check**: Checks PHP syntax for errors
- **File Formatting**: Removes trailing whitespace, ensures proper line endings
- **JSON/YAML Validation**: Validates JSON and YAML syntax

### WordPress-Specific Validation
- **WordPress Config Validation**: Validates WordPress Semgrep configuration files
- **WordPress Rule Validation**: Validates Semgrep rule syntax and structure

### File Management
- **Large File Check**: Prevents large files from being committed
- **Case Conflict Check**: Checks for files that would conflict in case-insensitive filesystems
- **Merge Conflict Check**: Detects merge conflict markers

## Quick Setup

### Windows (PowerShell)

```powershell
# Run the setup script
.\setup-pre-commit.ps1

# Or with options
.\setup-pre-commit.ps1 -SkipInstall -PythonVersion python3.11
```

### Unix/Linux/macOS

```bash
# Make script executable (if needed)
chmod +x setup-pre-commit.sh

# Run the setup script
./setup-pre-commit.sh

# Or with options
./setup-pre-commit.sh --skip-install --python python3.11
```

### Manual Setup

If you prefer to set up manually:

1. **Install pre-commit**:
   ```bash
   pip install pre-commit
   ```

2. **Install project dependencies**:
   ```bash
   pip install -r requirements.txt
   ```

3. **Install hooks**:
   ```bash
   pre-commit install
   pre-commit install-hooks
   ```

4. **Validate configuration**:
   ```bash
   pre-commit validate-config
   ```

## Configuration

The pre-commit configuration is defined in `.pre-commit-config.yaml`. Key features:

### Dual Semgrep Scanning
- **Basic Scan**: Uses `configs/basic.yaml` for essential security checks
- **Plugin Development Scan**: Uses `configs/plugin-development.yaml` for comprehensive checks

### File Exclusions
The hooks exclude test files and vulnerable examples:
- `tests/vulnerable-examples/`
- `tests/safe-examples/`
- `vendor/`
- `node_modules/`
- `.git/`
- `.semgrep/`

### Custom Validation Scripts
- `tooling/validate-configs.py`: Validates configuration files
- `tooling/validate-rules.py`: Validates rule files

## Usage

### Automatic Execution
Once installed, hooks run automatically on each commit. If any hook fails, the commit is blocked.

### Manual Execution

```bash
# Run all hooks on all files
pre-commit run --all-files

# Run hooks on staged files only
pre-commit run

# Run a specific hook
pre-commit run semgrep

# Run hooks manually (bypass automatic execution)
pre-commit run --hook-stage manual
```

### Skipping Hooks

To skip hooks for a specific commit:

```bash
git commit -m "Your message" --no-verify
```

**Note**: This should be used sparingly and only when absolutely necessary.

## Hook Details

### Semgrep Security Scanning

**Basic Security Scan**:
- Configuration: `configs/basic.yaml`
- Severity: ERROR
- Output: `.pre-commit-semgrep-basic.json`
- Purpose: Essential security checks with minimal false positives

**Plugin Development Scan**:
- Configuration: `configs/plugin-development.yaml`
- Severity: WARNING
- Output: `.pre-commit-semgrep-plugin.json`
- Purpose: Comprehensive security and quality checks

### Secrets Detection

Uses Yelp's detect-secrets to find:
- API keys
- Passwords
- Private keys
- Other sensitive data

Configuration file: `.secrets.baseline`

### Custom WordPress Validation

**Config Validation** (`tooling/validate-configs.py`):
- Validates YAML syntax
- Checks include paths
- Validates rule structure
- Ensures proper configuration format

**Rule Validation** (`tooling/validate-rules.py`):
- Validates rule syntax
- Checks required fields
- Validates metadata
- Ensures proper rule format

## Troubleshooting

### Common Issues

**Hook Installation Fails**:
```bash
# Clean and reinstall
pre-commit clean
pre-commit install
```

**Python Version Issues**:
```bash
# Use specific Python version
./setup-pre-commit.sh --python python3.11
```

**Semgrep Not Found**:
```bash
# Install Semgrep manually
pip install semgrep==1.75.0
```

**Permission Issues (Unix)**:
```bash
# Make scripts executable
chmod +x setup-pre-commit.sh
chmod +x tooling/*.py
```

### Performance Issues

**Slow Hook Execution**:
- Hooks run on staged files only by default
- Use `--all-files` sparingly
- Consider excluding large directories in `.pre-commit-config.yaml`

**Memory Issues**:
- Increase Python memory limit if needed
- Use `--skip-install` if pre-commit is already installed

### False Positives

**Semgrep False Positives**:
- Review and adjust rule patterns
- Add `pattern-not` clauses to rules
- Update test cases

**Secrets Detection False Positives**:
- Update `.secrets.baseline` file
- Add exclusions for test files
- Use `--baseline` flag to update baseline

## Customization

### Adding New Hooks

To add a new hook, edit `.pre-commit-config.yaml`:

```yaml
repos:
  - repo: https://github.com/example/hook-repo
    rev: v1.0.0
    hooks:
      - id: example-hook
        name: Example Hook
        description: "Description of the hook"
        files: \.(php|js)$
```

### Modifying Existing Hooks

To modify hook behavior:

1. Edit the hook configuration in `.pre-commit-config.yaml`
2. Update validation scripts in `tooling/`
3. Test changes with `pre-commit run --all-files`

### Excluding Files

Add exclusions in `.pre-commit-config.yaml`:

```yaml
exclude: |
  (?x)^(
      path/to/exclude/|
      another/path/
  )$
```

## Best Practices

### Development Workflow

1. **Install hooks early**: Set up pre-commit hooks when starting development
2. **Run hooks frequently**: Use `pre-commit run` before committing
3. **Fix issues promptly**: Address hook failures immediately
4. **Update baselines**: Keep secrets baseline and test files current

### Rule Development

1. **Test rules thoroughly**: Use test cases to validate rule behavior
2. **Minimize false positives**: Tune rules to reduce false positives
3. **Document changes**: Update documentation when modifying rules
4. **Version control**: Track rule changes in version control

### Team Collaboration

1. **Standardize setup**: Use the same pre-commit configuration across the team
2. **Document exceptions**: Document when hooks should be skipped
3. **Regular updates**: Keep hooks and dependencies updated
4. **Training**: Ensure team members understand hook behavior

## Integration

### CI/CD Integration

The pre-commit hooks can be integrated into CI/CD pipelines:

```yaml
# GitHub Actions example
- name: Run pre-commit hooks
  run: |
    pip install pre-commit
    pre-commit run --all-files
```

### IDE Integration

Many IDEs support pre-commit hooks:

- **VS Code**: Install pre-commit extension
- **PyCharm**: Configure external tools
- **Vim/Emacs**: Use terminal integration

## Maintenance

### Regular Tasks

1. **Update dependencies**: Keep pre-commit and hook versions current
2. **Review baselines**: Update secrets baseline as needed
3. **Test configuration**: Validate configuration changes
4. **Monitor performance**: Track hook execution times

### Updates

To update pre-commit hooks:

```bash
# Update pre-commit
pip install --upgrade pre-commit

# Update hook versions
pre-commit autoupdate

# Reinstall hooks
pre-commit install
```

## Support

For issues and questions:

1. **Check documentation**: Review this guide and project docs
2. **Test configuration**: Use `pre-commit validate-config`
3. **Review logs**: Check hook output for error details
4. **Create issues**: Report problems in the project repository

## Resources

- [Pre-commit Documentation](https://pre-commit.com/)
- [Semgrep Documentation](https://semgrep.dev/docs/)
- [Detect-secrets Documentation](https://github.com/Yelp/detect-secrets)
- [WordPress Security Best Practices](https://developer.wordpress.org/plugins/security/)
