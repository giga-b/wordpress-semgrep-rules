# WordPress Semgrep Rules - Incremental Scanning

## Overview

The incremental scanning feature provides intelligent change detection and optimized scanning for WordPress projects. Instead of scanning the entire codebase every time, it only scans files that have changed or are affected by changes, significantly reducing scan time and improving developer productivity.

## Features

### 🔍 Smart Change Detection
- **Git Integration**: Automatically detects changes using git status and diff
- **File System Monitoring**: Tracks file modifications, additions, and deletions
- **Dependency Analysis**: Identifies files affected by changes through include/require statements
- **Cross-Platform Support**: Works on Windows, macOS, and Linux

### ⚡ Performance Optimization
- **Incremental Scans**: Only scans changed and affected files
- **Cache Integration**: Reuses results for unchanged files
- **Smart Full Scan Detection**: Automatically switches to full scan when needed
- **Performance Metrics**: Tracks scan times and optimization effectiveness

### 🛡️ WordPress-Specific Intelligence
- **PHP Dependency Tracking**: Analyzes include, require, and WordPress-specific patterns
- **Configuration Awareness**: Monitors changes to YAML, JSON, and other config files
- **Critical File Detection**: Identifies changes to important files like `wp-config.php`

## How It Works

### 1. Change Detection
The system uses multiple methods to detect changes:

```python
# Git-based detection (preferred)
git status --porcelain
git diff --name-only HEAD~1

# File system monitoring (fallback)
- Tracks file hashes and modification times
- Monitors for new, modified, and deleted files
- Updates file tracking database
```

### 2. Impact Analysis
When changes are detected, the system analyzes their impact:

```python
# PHP dependency analysis
- include/require statements
- WordPress template functions
- Configuration file references

# Configuration dependency analysis
- YAML/JSON rule references
- Composer/Package.json dependencies
- WordPress-specific patterns
```

### 3. Scan Optimization
Based on the analysis, the system determines the optimal scan strategy:

```python
# Incremental scan (default)
- Scan only changed and affected files
- Use cached results for unchanged files
- Generate minimal scan paths

# Full scan (when needed)
- Too many files changed (>50)
- Critical files modified
- Last full scan >24 hours ago
- Cache invalidated
```

## Usage

### Command Line Interface

#### Basic Usage
```bash
# Run incremental scan
python tooling/incremental_runner.py

# With specific configuration
python tooling/incremental_runner.py --config configs/strict.yaml

# Force full scan
python tooling/incremental_runner.py --force-full
```

#### Advanced Options
```bash
# Custom output files
python tooling/incremental_runner.py --output results.json --report report.html

# Disable git detection
python tooling/incremental_runner.py --no-git

# Show statistics
python tooling/incremental_runner.py --stats

# Clean up old data
python tooling/incremental_runner.py --cleanup 30
```

### Integration with Existing Scripts

#### PowerShell (Windows)
```powershell
# Enhanced incremental scanning
.\tooling\run-semgrep.ps1 -Incremental -Config configs\plugin-development.yaml

# With performance monitoring
.\tooling\run-semgrep.ps1 -Incremental -Performance -Cache
```

#### Bash (Linux/macOS)
```bash
# Enhanced incremental scanning
./tooling/run-semgrep.sh --incremental --config configs/plugin-development.yaml

# With performance monitoring
./tooling/run-semgrep.sh --incremental --performance --cache
```

### Programmatic Usage

#### Python API
```python
from tooling.incremental_runner import IncrementalRunner

# Initialize runner
runner = IncrementalRunner(".", "configs/plugin-development.yaml")

# Run scan
results = runner.run_scan(
    use_git=True,
    force_full=False,
    output_file="results.json",
    html_report="report.html"
)

# Check results
if results["success"]:
    print(f"Scan completed in {results['duration']:.2f} seconds")
    print(f"Found {len(results['results']['results'])} issues")
```

## Configuration

### File Tracking
The system maintains tracking files in your project:

```
.semgrep-file-tracker.json    # File change tracking
.semgrep-scan-state.json      # Scan history and statistics
```

### Cache Configuration
Caching is handled by the cache manager:

```python
# Cache settings
cache_ttl = {
    'incremental_scan': 3600,  # 1 hour
    'scan_results': 86400,     # 24 hours
    'rule_compilation': 604800 # 7 days
}
```

### Performance Thresholds
```python
# Full scan triggers
MAX_CHANGED_FILES = 50        # Too many files changed
FULL_SCAN_INTERVAL = 86400    # 24 hours since last full scan
CRITICAL_FILES = [            # Critical file patterns
    "wp-config.php",
    "functions.php",
    "composer.json",
    "package.json"
]
```

## Performance Benefits

### Typical Performance Improvements

| Scenario | Full Scan | Incremental Scan | Improvement |
|----------|-----------|------------------|-------------|
| Single file change | 30s | 2s | 93% faster |
| Small feature (5 files) | 30s | 8s | 73% faster |
| Medium feature (15 files) | 30s | 15s | 50% faster |
| Large changes (>50 files) | 30s | 30s | Full scan |

### Real-World Examples

#### WordPress Plugin Development
```bash
# Developer makes small change to admin page
$ git status
M admin/admin-page.php

# Incremental scan
$ python tooling/incremental_runner.py
🔍 Detecting changes and preparing scan context...
📊 Scan Analysis:
  Scan Type: incremental
  Changed Files: 1
  Affected Files: 3
  Scan Paths: 3
🚀 Performing incremental scan...
✅ Scan completed successfully in 2.3 seconds
```

#### Theme Development
```bash
# Developer modifies template file
$ git diff --name-only
templates/header.php
templates/footer.php

# Incremental scan
$ python tooling/incremental_runner.py
🔍 Detecting changes and preparing scan context...
📊 Scan Analysis:
  Scan Type: incremental
  Changed Files: 2
  Affected Files: 4
  Scan Paths: 4
🚀 Performing incremental scan...
✅ Scan completed successfully in 4.1 seconds
```

## Monitoring and Statistics

### View Scan Statistics
```bash
python tooling/incremental_runner.py --stats
```

Example output:
```json
{
  "file_tracker": {
    "total_files": 45,
    "last_scan": 1640995200.0
  },
  "scan_state": {
    "last_full_scan": 1640908800.0,
    "last_incremental_scan": 1640995200.0,
    "total_scans": 25,
    "performance_metrics": {
      "incremental": {
        "last_duration": 2.3,
        "avg_duration": 3.1,
        "total_scans": 20
      },
      "full": {
        "last_duration": 28.5,
        "avg_duration": 29.2,
        "total_scans": 5
      }
    }
  }
}
```

### Performance Metrics
- **Scan Duration**: Time taken for each scan type
- **Cache Hit Rate**: Percentage of scans using cached results
- **File Change Patterns**: Most frequently changed files
- **Optimization Effectiveness**: Time saved through incremental scanning

## Troubleshooting

### Common Issues

#### Git Repository Not Found
```
⚠️  Git repository not found, performing full scan
```
**Solution**: Initialize git repository or use `--no-git` flag

#### Cache Issues
```
❌ Cache check failed: [error message]
```
**Solution**: Clear cache or check cache directory permissions

#### Performance Issues
```
❌ Semgrep scan timed out
```
**Solution**: 
- Increase timeout in configuration
- Check for large files or complex rules
- Consider using `--force-full` for problematic scans

### Debug Mode
```bash
# Enable debug logging
export SEMGREP_DEBUG=1
python tooling/incremental_runner.py --config configs/strict.yaml
```

### Clean Up
```bash
# Clean old scan data
python tooling/incremental_runner.py --cleanup 30

# Clear all cache
python -c "from tooling.cache_manager import get_cache_manager; get_cache_manager().clear()"
```

## Best Practices

### 1. Regular Full Scans
- Schedule weekly full scans to catch issues in unchanged files
- Use `--force-full` after major refactoring
- Monitor scan statistics for optimization opportunities

### 2. Git Workflow Integration
- Commit changes before running incremental scans
- Use feature branches for isolated testing
- Integrate with pre-commit hooks for automatic scanning

### 3. Cache Management
- Monitor cache size and performance
- Clean up old data periodically
- Adjust cache TTL based on project needs

### 4. Configuration Optimization
- Use appropriate configuration files for different contexts
- Customize performance thresholds for your project
- Monitor false positive rates and adjust rules

## Integration Examples

### GitHub Actions
```yaml
name: Security Scan
on: [push, pull_request]
jobs:
  incremental-scan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Run Incremental Scan
        run: |
          python tooling/incremental_runner.py \
            --config configs/plugin-development.yaml \
            --output scan-results.json
```

### Pre-commit Hook
```yaml
# .pre-commit-config.yaml
repos:
  - repo: local
    hooks:
      - id: semgrep-incremental
        name: Semgrep Incremental Scan
        entry: python tooling/incremental_runner.py
        language: python
        files: \.(php|inc)$
```

### VS Code Integration
```json
// .vscode/tasks.json
{
  "version": "2.0.0",
  "tasks": [
    {
      "label": "Semgrep Incremental Scan",
      "type": "shell",
      "command": "python",
      "args": ["tooling/incremental_runner.py", "--config", "configs/plugin-development.yaml"],
      "group": "build",
      "presentation": {
        "echo": true,
        "reveal": "always",
        "focus": false,
        "panel": "shared"
      }
    }
  ]
}
```

## Future Enhancements

### Planned Features
- **Real-time Monitoring**: File system watchers for instant change detection
- **Distributed Scanning**: Support for large repositories across multiple machines
- **Advanced Dependency Analysis**: More sophisticated PHP dependency tracking
- **Machine Learning**: Predictive scanning based on change patterns
- **Cloud Integration**: Remote caching and scanning capabilities

### Contributing
To contribute to incremental scanning improvements:

1. Review the test suite in `tests/test_incremental_scanning.py`
2. Add new test cases for your scenarios
3. Submit pull requests with comprehensive testing
4. Update documentation for new features

## Conclusion

Incremental scanning provides significant performance improvements for WordPress security scanning while maintaining comprehensive coverage. By intelligently detecting changes and optimizing scan paths, it reduces scan times by 50-90% in typical development scenarios.

The system integrates seamlessly with existing workflows and provides detailed monitoring and statistics to help optimize your security scanning process.
