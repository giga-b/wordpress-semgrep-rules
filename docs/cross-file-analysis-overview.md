# Cross-File Analysis Overview

## Introduction

This document explains the cross-file analysis capabilities available for the WordPress Semgrep Rules project. **Important**: Due to limitations with Semgrep AppSec Platform's cross-file analysis for custom local rules, the **Python-based cross-file analysis is the recommended solution** for this project.

## Cross-File Analysis Options

### 1. Python-Based Cross-File Analysis (Recommended)

**Status**: ✅ **RECOMMENDED FOR THIS PROJECT**

**Why it's recommended**:
- Works with your custom local WordPress rules
- No account required
- Always available
- Simulates cross-file analysis by post-processing results
- Maintains full control over your rule development

**How it works**:
- Runs `semgrep scan` with your local rules
- Post-processes results to identify cross-file patterns
- Provides enhanced detection without cloud dependencies

**Usage**:
```bash
python tests/test-cross-file-analysis.py
```

### 2. Semgrep AppSec Platform Cross-File Analysis (Limited Use)

**Status**: ⚠️ **NOT PRACTICAL FOR CUSTOM RULES**

**Why it's limited**:
- Requires rules to be configured on `semgrep.dev`
- Cannot use local custom rules with cross-file analysis
- Would require uploading your custom WordPress rules to the cloud
- Loses the benefit of local rule development and customization

**When it might be useful**:
- If you want to use Semgrep's built-in rules with cross-file analysis
- For general security scanning (not WordPress-specific)
- If you're willing to move your rules to the cloud platform

## Comparison

| Feature | Python-Based | Semgrep AppSec Platform |
|---------|--------------|-------------------------|
| **Custom Rules Support** | ✅ Full support | ❌ Limited (cloud rules only) |
| **Local Development** | ✅ Maintained | ❌ Requires cloud upload |
| **Account Required** | ❌ No | ✅ Yes |
| **Cross-File Detection** | ✅ Simulated | ✅ Native |
| **WordPress-Specific** | ✅ Optimized | ❌ Generic |
| **Setup Complexity** | ✅ Simple | ⚠️ Complex |
| **Control** | ✅ Full | ❌ Limited |

## Quick Start Guide

### For This Project (Recommended)

1. **Use local scanning with custom rules**:
   ```bash
   semgrep scan --config packs/wp-core-security/nonce-lifecycle-detection.yaml tests/safe-examples/nonce-lifecycle-safe.php
   ```

2. **Use Python-based cross-file analysis**:
   ```bash
   python tests/test-cross-file-analysis.py
   ```

3. **Combine both approaches** for maximum coverage

### For General Security (Optional)

If you want to use Semgrep's built-in rules with cross-file analysis:

1. Create a Semgrep AppSec Platform account
2. Run `semgrep login`
3. Use `semgrep ci` (but this won't use your custom WordPress rules)

## Technical Details

### Why Semgrep AppSec Platform Cross-File Analysis Doesn't Work for Custom Rules

The fundamental limitation is:

1. **Cross-file analysis requires `semgrep ci`** (cloud-based command)
2. **`semgrep ci` cannot use local config files** when logged in
3. **Your custom WordPress rules are local** and specific to your project
4. **Cross-file analysis only works with rules on `semgrep.dev`**

This creates a catch-22: you can't use cross-file analysis with your custom rules because they're local, and you can't move your rules to the cloud without losing local development benefits.

### Python-Based Cross-File Analysis Implementation

The Python-based solution works by:

1. **Running local scans** with your custom rules
2. **Collecting results** from multiple files
3. **Post-processing** to identify cross-file patterns
4. **Simulating cross-file analysis** through result correlation

This approach maintains all the benefits of local rule development while providing enhanced detection capabilities.

## Best Practices

### For WordPress Security Analysis

1. **Primary**: Use `semgrep scan` with your custom rules
2. **Enhanced**: Use Python-based cross-file analysis
3. **Avoid**: Semgrep AppSec Platform cross-file analysis (not suitable for custom rules)

### Rule Development

1. **Develop rules locally** for maximum control
2. **Test with Python cross-file analysis** for enhanced validation
3. **Maintain rule specificity** for WordPress security patterns

## Troubleshooting

### Common Issues

**"Cross-file analysis not working with custom rules"**:
- This is expected - use Python-based cross-file analysis instead
- Semgrep AppSec Platform cross-file analysis only works with cloud rules

**"Python cross-file analysis not finding expected patterns"**:
- Check that your local rules are working correctly
- Verify test files contain the expected patterns
- Review the post-processing logic in the Python script

## Conclusion

For the WordPress Semgrep Rules project, **the Python-based cross-file analysis is the recommended solution**. It provides enhanced detection capabilities while maintaining full control over your custom WordPress security rules.

The Semgrep AppSec Platform cross-file analysis, while technically available, is not practical for projects with custom local rules like this one.

## Next Steps

1. **Use the Python-based cross-file analysis** for enhanced detection
2. **Continue developing custom WordPress rules** locally
3. **Ignore Semgrep AppSec Platform cross-file analysis** for this project
4. **Focus on local `semgrep scan`** with your custom rules
