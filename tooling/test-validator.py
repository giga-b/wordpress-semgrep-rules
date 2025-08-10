#!/usr/bin/env python3
"""
Test script for the WordPress Semgrep Configuration Validator

This script tests the validator with various test cases to ensure it correctly
identifies configuration issues and provides appropriate error messages.
"""

import os
import sys
import tempfile
import yaml
from pathlib import Path

# Add the tooling directory to the path so we can import the validator
sys.path.insert(0, str(Path(__file__).parent))

try:
    from validate_configs import ConfigValidator, ValidationLevel
except ImportError:
    # Try importing from the current directory
    import sys
    sys.path.insert(0, '.')
    from tooling.validate_configs import ConfigValidator, ValidationLevel


def create_test_config(content: str, filename: str = "test.yaml") -> Path:
    """Create a temporary test configuration file."""
    temp_dir = Path(tempfile.mkdtemp())
    config_file = temp_dir / filename
    
    with open(config_file, 'w', encoding='utf-8') as f:
        f.write(content)
    
    return config_file


def test_valid_config():
    """Test with a valid configuration."""
    print("Testing valid configuration...")
    
    valid_config = """
include:
  - packs/wp-core-security/nonce-verification.yaml
  - packs/wp-core-security/capability-checks.yaml

exclude:
  - "**/node_modules/**"
  - "**/vendor/**"

rules:
  - id: wordpress.test.valid-rule
    languages: [php]
    message: "Test rule"
    severity: ERROR
    metadata:
      category: "nonce-verification"
      cwe: "CWE-352"
    patterns:
      - pattern: "test pattern"
    """
    
    config_file = create_test_config(valid_config)
    
    # Create a mock project structure
    project_root = config_file.parent
    packs_dir = project_root / "packs" / "wp-core-security"
    packs_dir.mkdir(parents=True, exist_ok=True)
    
    # Create mock rule files
    (packs_dir / "nonce-verification.yaml").write_text("[]")
    (packs_dir / "capability-checks.yaml").write_text("[]")
    
    validator = ConfigValidator(str(project_root))
    success = validator.validate_config_file(config_file)
    
    print(f"Valid config test: {'✅ PASSED' if success else '❌ FAILED'}")
    if not success:
        for error in validator.errors:
            print(f"  Error: {error.message}")
    
    return success


def test_invalid_yaml():
    """Test with invalid YAML syntax."""
    print("\nTesting invalid YAML...")
    
    invalid_config = """
include:
  - packs/wp-core-security/nonce-verification.yaml
  - packs/wp-core-security/capability-checks.yaml

rules:
  - id: wordpress.test.invalid-rule
    languages: [php]
    message: "Test rule"
    severity: ERROR
    patterns:
      - pattern: "test pattern"
      # Missing closing bracket
    """
    
    config_file = create_test_config(invalid_config)
    validator = ConfigValidator()
    success = validator.validate_config_file(config_file)
    
    print(f"Invalid YAML test: {'✅ PASSED' if not success else '❌ FAILED'}")
    if success:
        print("  Expected validation to fail but it passed")
    else:
        for error in validator.errors:
            print(f"  Error: {error.message}")
    
    return not success  # Should fail


def test_missing_required_fields():
    """Test with missing required rule fields."""
    print("\nTesting missing required fields...")
    
    invalid_config = """
rules:
  - id: wordpress.test.missing-fields
    message: "Test rule"
    # Missing languages field
    severity: ERROR
    patterns:
      - pattern: "test pattern"
    """
    
    config_file = create_test_config(invalid_config)
    validator = ConfigValidator()
    success = validator.validate_config_file(config_file)
    
    print(f"Missing fields test: {'✅ PASSED' if not success else '❌ FAILED'}")
    if success:
        print("  Expected validation to fail but it passed")
    else:
        for error in validator.errors:
            print(f"  Error: {error.message}")
    
    return not success  # Should fail


def test_invalid_rule_id():
    """Test with invalid rule ID format."""
    print("\nTesting invalid rule ID...")
    
    invalid_config = """
rules:
  - id: invalid-rule-id
    languages: [php]
    message: "Test rule"
    severity: ERROR
    patterns:
      - pattern: "test pattern"
    """
    
    config_file = create_test_config(invalid_config)
    validator = ConfigValidator()
    success = validator.validate_config_file(config_file)
    
    print(f"Invalid rule ID test: {'✅ PASSED' if not success else '❌ FAILED'}")
    if success:
        print("  Expected validation to fail but it passed")
    else:
        for error in validator.errors:
            print(f"  Error: {error.message}")
    
    return not success  # Should fail


def test_invalid_severity():
    """Test with invalid severity level."""
    print("\nTesting invalid severity...")
    
    invalid_config = """
rules:
  - id: wordpress.test.invalid-severity
    languages: [php]
    message: "Test rule"
    severity: INVALID
    patterns:
      - pattern: "test pattern"
    """
    
    config_file = create_test_config(invalid_config)
    validator = ConfigValidator()
    success = validator.validate_config_file(config_file)
    
    print(f"Invalid severity test: {'✅ PASSED' if not success else '❌ FAILED'}")
    if success:
        print("  Expected validation to fail but it passed")
    else:
        for error in validator.errors:
            print(f"  Error: {error.message}")
    
    return not success  # Should fail


def test_missing_include_files():
    """Test with missing include files."""
    print("\nTesting missing include files...")
    
    invalid_config = """
include:
  - packs/wp-core-security/nonce-verification.yaml
  - packs/wp-core-security/missing-file.yaml
    """
    
    config_file = create_test_config(invalid_config)
    
    # Create a mock project structure with only one file
    project_root = config_file.parent
    packs_dir = project_root / "packs" / "wp-core-security"
    packs_dir.mkdir(parents=True, exist_ok=True)
    
    # Create only one of the referenced files
    (packs_dir / "nonce-verification.yaml").write_text("[]")
    
    validator = ConfigValidator(str(project_root))
    success = validator.validate_config_file(config_file)
    
    print(f"Missing include files test: {'✅ PASSED' if not success else '❌ FAILED'}")
    if success:
        print("  Expected validation to fail but it passed")
    else:
        for error in validator.errors:
            print(f"  Error: {error.message}")
    
    return not success  # Should fail


def test_invalid_metadata():
    """Test with invalid metadata."""
    print("\nTesting invalid metadata...")
    
    invalid_config = """
rules:
  - id: wordpress.test.invalid-metadata
    languages: [php]
    message: "Test rule"
    severity: ERROR
    metadata:
      category: "invalid-category"
      cwe: "INVALID-CWE"
      references: "not-a-list"
    patterns:
      - pattern: "test pattern"
    """
    
    config_file = create_test_config(invalid_config)
    validator = ConfigValidator()
    success = validator.validate_config_file(config_file)
    
    print(f"Invalid metadata test: {'✅ PASSED' if not success else '❌ FAILED'}")
    if success:
        print("  Expected validation to fail but it passed")
    else:
        for error in validator.errors:
            print(f"  Error: {error.message}")
        for warning in validator.warnings:
            print(f"  Warning: {warning.message}")
    
    return not success  # Should fail


def test_performance_warnings():
    """Test performance-related warnings."""
    print("\nTesting performance warnings...")
    
    config_without_excludes = """
include:
  - packs/wp-core-security/
    """
    
    config_file = create_test_config(config_without_excludes, "strict.yaml")
    
    # Create a mock project structure
    project_root = config_file.parent
    packs_dir = project_root / "packs" / "wp-core-security"
    packs_dir.mkdir(parents=True, exist_ok=True)
    (packs_dir / "test.yaml").write_text("[]")
    
    validator = ConfigValidator(str(project_root))
    success = validator.validate_config_file(config_file)
    
    print(f"Performance warnings test: {'✅ PASSED' if success else '❌ FAILED'}")
    if success:
        warnings_found = any(
            "should include exclude patterns for performance" in warning.message
            for warning in validator.warnings
        )
        if warnings_found:
            print("  ✅ Performance warning correctly identified")
        else:
            print("  ❌ Expected performance warning not found")
    else:
        for error in validator.errors:
            print(f"  Error: {error.message}")
    
    return success


def run_all_tests():
    """Run all validation tests."""
    print("Running WordPress Semgrep Configuration Validator Tests")
    print("=" * 60)
    
    tests = [
        test_valid_config,
        test_invalid_yaml,
        test_missing_required_fields,
        test_invalid_rule_id,
        test_invalid_severity,
        test_missing_include_files,
        test_invalid_metadata,
        test_performance_warnings
    ]
    
    passed = 0
    total = len(tests)
    
    for test in tests:
        try:
            if test():
                passed += 1
        except Exception as e:
            print(f"❌ Test failed with exception: {e}")
    
    print("\n" + "=" * 60)
    print(f"Test Results: {passed}/{total} tests passed")
    
    if passed == total:
        print("✅ All tests passed!")
        return True
    else:
        print("❌ Some tests failed!")
        return False


if __name__ == '__main__':
    success = run_all_tests()
    sys.exit(0 if success else 1)
