#!/usr/bin/env python3
"""
Test script for WordPress Semgrep Incremental Scanning

This script tests the incremental scanning functionality with various scenarios:
- Git-based change detection
- File system monitoring
- Dependency analysis
- Cache integration
- Performance optimization

Author: WordPress Semgrep Rules Team
License: MIT
"""

import os
import sys
import json
import time
import tempfile
import shutil
import subprocess
from pathlib import Path
from typing import Dict, List, Any

# Add tooling directory to path
sys.path.insert(0, str(Path(__file__).parent.parent / "tooling"))

from incremental_scanner import IncrementalScanner, detect_changes_and_prepare_scan
from incremental_runner import IncrementalRunner


class IncrementalScanningTester:
    """Test suite for incremental scanning functionality."""
    
    def __init__(self, test_dir: str = None):
        """Initialize the tester."""
        self.test_dir = Path(test_dir) if test_dir else Path(tempfile.mkdtemp())
        self.test_dir.mkdir(parents=True, exist_ok=True)
        
        # Test results
        self.results = {
            "passed": 0,
            "failed": 0,
            "tests": []
        }
        
        print(f"🧪 Testing incremental scanning in: {self.test_dir}")
    
    def setup_test_environment(self):
        """Set up test environment with sample files."""
        print("📁 Setting up test environment...")
        
        # Create sample PHP files
        self._create_sample_php_files()
        
        # Create configuration files
        self._create_config_files()
        
        # Initialize git repository
        self._init_git_repo()
        
        print("✅ Test environment ready")
    
    def _create_sample_php_files(self):
        """Create sample PHP files for testing."""
        files = {
            "functions.php": """
<?php
// WordPress functions file
function my_plugin_function() {
    $user_input = $_POST['data'];
    echo $user_input; // Vulnerable - no sanitization
}

function safe_function() {
    $user_input = sanitize_text_field($_POST['data']);
    echo $user_input; // Safe - sanitized
}

include_once 'includes/helper.php';
require_once 'includes/config.php';
""",
            "includes/helper.php": """
<?php
// Helper functions
function process_data($data) {
    return wp_kses_post($data);
}

function get_user_data() {
    return get_user_meta(get_current_user_id(), 'custom_field', true);
}
""",
            "includes/config.php": """
<?php
// Configuration file
define('PLUGIN_VERSION', '1.0.0');
define('DEBUG_MODE', false);

$config = array(
    'api_key' => 'secret_key_here',
    'debug' => true
);
""",
            "admin/admin-page.php": """
<?php
// Admin page
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

$action = $_GET['action'];
switch ($action) {
    case 'save':
        update_option('my_plugin_setting', $_POST['setting']);
        break;
}
""",
            "public/public-functions.php": """
<?php
// Public functions
function display_content($content) {
    return wp_kses_post($content);
}

function get_public_data() {
    global $wpdb;
    $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}posts WHERE ID = %d", $_GET['id']);
    return $wpdb->get_results($query);
}
"""
        }
        
        for file_path, content in files.items():
            full_path = self.test_dir / file_path
            full_path.parent.mkdir(parents=True, exist_ok=True)
            full_path.write_text(content.strip())
    
    def _create_config_files(self):
        """Create configuration files for testing."""
        configs = {
            "configs/test-config.yaml": """
rules:
  - id: test-xss
    pattern: echo $...;
    message: "Potential XSS vulnerability"
    severity: ERROR
    languages: [php]
""",
            "composer.json": """
{
    "name": "test/wordpress-plugin",
    "version": "1.0.0",
    "description": "Test plugin for incremental scanning"
}
""",
            "package.json": """
{
    "name": "test-wordpress-plugin",
    "version": "1.0.0",
    "scripts": {
        "test": "echo 'Testing...'"
    }
}
"""
        }
        
        for file_path, content in configs.items():
            full_path = self.test_dir / file_path
            full_path.parent.mkdir(parents=True, exist_ok=True)
            full_path.write_text(content.strip())
    
    def _init_git_repo(self):
        """Initialize git repository for testing."""
        try:
            subprocess.run(["git", "init"], cwd=self.test_dir, check=True, capture_output=True)
            subprocess.run(["git", "add", "."], cwd=self.test_dir, check=True, capture_output=True)
            subprocess.run(["git", "commit", "-m", "Initial commit"], cwd=self.test_dir, check=True, capture_output=True)
            print("✅ Git repository initialized")
        except subprocess.CalledProcessError as e:
            print(f"⚠️  Git initialization failed: {e}")
    
    def run_tests(self):
        """Run all tests."""
        print("\n🧪 Running incremental scanning tests...")
        
        tests = [
            ("test_initial_scan", "Test initial scan detection"),
            ("test_file_modification", "Test file modification detection"),
            ("test_new_file_detection", "Test new file detection"),
            ("test_dependency_analysis", "Test dependency analysis"),
            ("test_git_integration", "Test git integration"),
            ("test_cache_integration", "Test cache integration"),
            ("test_performance_optimization", "Test performance optimization"),
            ("test_error_handling", "Test error handling")
        ]
        
        for test_method, description in tests:
            print(f"\n📋 {description}...")
            try:
                getattr(self, test_method)()
                self._record_test_result(test_method, True, "Passed")
                print(f"✅ {description} - PASSED")
            except Exception as e:
                self._record_test_result(test_method, False, str(e))
                print(f"❌ {description} - FAILED: {e}")
        
        self._print_summary()
    
    def test_initial_scan(self):
        """Test initial scan detection."""
        scanner = IncrementalScanner(str(self.test_dir))
        
        # Detect changes
        changes = scanner.detect_changes(use_git=True)
        
        # Should detect all PHP files as new
        php_files = [f for f in changes if f.file_path.endswith('.php')]
        assert len(php_files) >= 5, f"Expected at least 5 PHP files, got {len(php_files)}"
        
        # Analyze impact
        affected_files = scanner.analyze_impact(changes)
        assert len(affected_files) >= 5, f"Expected at least 5 affected files, got {len(affected_files)}"
    
    def test_file_modification(self):
        """Test file modification detection."""
        scanner = IncrementalScanner(str(self.test_dir))
        
        # Modify a file
        test_file = self.test_dir / "functions.php"
        original_content = test_file.read_text()
        test_file.write_text(original_content + "\n// Modified for testing\n")
        
        # Detect changes
        changes = scanner.detect_changes(use_git=False)  # Use file system monitoring
        
        # Should detect the modification
        modified_files = [f for f in changes if f.change_type == "modified"]
        assert len(modified_files) >= 1, "Expected at least 1 modified file"
        
        # Check if our file is in the changes
        modified_file_paths = [f.file_path for f in modified_files]
        assert "functions.php" in modified_file_paths, "functions.php should be detected as modified"
    
    def test_new_file_detection(self):
        """Test new file detection."""
        scanner = IncrementalScanner(str(self.test_dir))
        
        # Create a new file
        new_file = self.test_dir / "new-file.php"
        new_file.write_text("<?php\n// New file for testing\n")
        
        # Detect changes
        changes = scanner.detect_changes(use_git=False)
        
        # Should detect the new file
        new_files = [f for f in changes if f.change_type == "added"]
        assert len(new_files) >= 1, "Expected at least 1 new file"
        
        # Check if our file is in the changes
        new_file_paths = [f.file_path for f in new_files]
        assert "new-file.php" in new_file_paths, "new-file.php should be detected as new"
    
    def test_dependency_analysis(self):
        """Test dependency analysis."""
        scanner = IncrementalScanner(str(self.test_dir))
        
        # Create a change in a file with dependencies
        changes = [
            scanner.FileChange(
                file_path="functions.php",
                change_type="modified"
            )
        ]
        
        # Analyze impact
        affected_files = scanner.analyze_impact(changes)
        
        # Should include dependencies
        assert "functions.php" in affected_files, "Changed file should be affected"
        assert "includes/helper.php" in affected_files, "Dependency should be affected"
        assert "includes/config.php" in affected_files, "Dependency should be affected"
    
    def test_git_integration(self):
        """Test git integration."""
        scanner = IncrementalScanner(str(self.test_dir))
        
        # Modify a file and commit it
        test_file = self.test_dir / "admin/admin-page.php"
        test_file.write_text(test_file.read_text() + "\n// Git test modification\n")
        
        subprocess.run(["git", "add", "admin/admin-page.php"], cwd=self.test_dir, check=True, capture_output=True)
        subprocess.run(["git", "commit", "-m", "Test modification"], cwd=self.test_dir, check=True, capture_output=True)
        
        # Detect changes using git
        changes = scanner.detect_changes(use_git=True)
        
        # Should detect changes
        assert len(changes) >= 1, "Expected at least 1 change detected via git"
    
    def test_cache_integration(self):
        """Test cache integration."""
        runner = IncrementalRunner(str(self.test_dir), "configs/test-config.yaml")
        
        # Run first scan
        results1 = runner.run_scan(use_git=False, force_full=True)
        assert results1["success"], "First scan should succeed"
        
        # Run second scan (should use cache)
        results2 = runner.run_scan(use_git=False, force_full=False)
        assert results2["success"], "Second scan should succeed"
        assert results2.get("from_cache"), "Second scan should use cache"
    
    def test_performance_optimization(self):
        """Test performance optimization."""
        scanner = IncrementalScanner(str(self.test_dir))
        
        # Test full scan decision logic
        small_changes = [
            scanner.FileChange(file_path="functions.php", change_type="modified")
        ]
        
        large_changes = [
            scanner.FileChange(file_path=f"file{i}.php", change_type="modified")
            for i in range(60)  # More than 50 files
        ]
        
        # Small changes should not trigger full scan
        should_full_small = scanner.should_perform_full_scan(small_changes)
        assert not should_full_small, "Small changes should not trigger full scan"
        
        # Large changes should trigger full scan
        should_full_large = scanner.should_perform_full_scan(large_changes)
        assert should_full_large, "Large changes should trigger full scan"
    
    def test_error_handling(self):
        """Test error handling."""
        # Test with non-existent directory
        try:
            scanner = IncrementalScanner("/non/existent/path")
            # Should not raise exception
            changes = scanner.detect_changes()
            assert isinstance(changes, list), "Should return empty list for non-existent path"
        except Exception as e:
            assert False, f"Should handle non-existent path gracefully: {e}"
        
        # Test with invalid configuration
        try:
            runner = IncrementalRunner(str(self.test_dir), "non/existent/config.yaml")
            results = runner.run_scan(use_git=False, force_full=True)
            # Should handle gracefully
            assert isinstance(results, dict), "Should return results dict even with invalid config"
        except Exception as e:
            assert False, f"Should handle invalid config gracefully: {e}"
    
    def _record_test_result(self, test_name: str, passed: bool, message: str):
        """Record test result."""
        self.results["tests"].append({
            "name": test_name,
            "passed": passed,
            "message": message,
            "timestamp": time.time()
        })
        
        if passed:
            self.results["passed"] += 1
        else:
            self.results["failed"] += 1
    
    def _print_summary(self):
        """Print test summary."""
        print(f"\n📊 Test Summary:")
        print(f"  Total Tests: {len(self.results['tests'])}")
        print(f"  Passed: {self.results['passed']}")
        print(f"  Failed: {self.results['failed']}")
        print(f"  Success Rate: {(self.results['passed'] / len(self.results['tests']) * 100):.1f}%")
        
        if self.results["failed"] > 0:
            print(f"\n❌ Failed Tests:")
            for test in self.results["tests"]:
                if not test["passed"]:
                    print(f"  • {test['name']}: {test['message']}")
        
        # Save results
        results_file = self.test_dir / "test-results.json"
        with open(results_file, 'w') as f:
            json.dump(self.results, f, indent=2)
        
        print(f"\n📄 Test results saved to: {results_file}")
    
    def cleanup(self):
        """Clean up test environment."""
        if self.test_dir.exists():
            shutil.rmtree(self.test_dir)
            print(f"🧹 Cleaned up test directory: {self.test_dir}")


def main():
    """Main test function."""
    import argparse
    
    parser = argparse.ArgumentParser(description="Test WordPress Semgrep Incremental Scanning")
    parser.add_argument("--keep-files", action="store_true", help="Keep test files after testing")
    parser.add_argument("--test-dir", help="Use specific test directory")
    
    args = parser.parse_args()
    
    # Create tester
    tester = IncrementalScanningTester(args.test_dir)
    
    try:
        # Set up environment
        tester.setup_test_environment()
        
        # Run tests
        tester.run_tests()
        
        # Clean up
        if not args.keep_files:
            tester.cleanup()
        
        # Exit with appropriate code
        if tester.results["failed"] > 0:
            print(f"\n❌ {tester.results['failed']} test(s) failed")
            sys.exit(1)
        else:
            print(f"\n✅ All {tester.results['passed']} test(s) passed")
            sys.exit(0)
            
    except KeyboardInterrupt:
        print("\n⚠️  Testing interrupted by user")
        if not args.keep_files:
            tester.cleanup()
        sys.exit(1)
    except Exception as e:
        print(f"\n❌ Testing failed with error: {e}")
        if not args.keep_files:
            tester.cleanup()
        sys.exit(1)


if __name__ == "__main__":
    main()
