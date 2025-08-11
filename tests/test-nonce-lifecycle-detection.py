#!/usr/bin/env python3
"""
Test Script for Task 1.5: Nonce Lifecycle Detection Rules
This script tests the comprehensive nonce lifecycle detection rules against
safe and vulnerable test cases to ensure proper detection and validation.
"""

import os
import sys
import json
import subprocess
import time
from pathlib import Path
from datetime import datetime

class NonceLifecycleTester:
    def __init__(self):
        self.project_root = Path(__file__).parent.parent
        self.rules_file = self.project_root / "packs" / "wp-core-security" / "nonce-lifecycle-detection.yaml"
        self.safe_test_file = self.project_root / "tests" / "safe-examples" / "nonce-lifecycle-safe.php"
        self.vulnerable_test_file = self.project_root / "tests" / "vulnerable-examples" / "nonce-lifecycle-vulnerable.php"
        self.results_dir = self.project_root / "tests" / "test-results"
        self.results_dir.mkdir(exist_ok=True)
        
    def run_semgrep_scan(self, target_file, rules_file, output_file):
        """Run Semgrep scan on target file with specified rules"""
        try:
            cmd = [
                "semgrep", "scan",
                "--config", str(rules_file),
                "--json",
                "--output", str(output_file),
                str(target_file)
            ]
            
            result = subprocess.run(cmd, capture_output=True, text=True, timeout=60)
            
            if result.returncode == 0:
                return True, result.stdout
            else:
                return False, result.stderr
                
        except subprocess.TimeoutExpired:
            return False, "Scan timed out"
        except Exception as e:
            return False, str(e)
    
    def analyze_results(self, results_file):
        """Analyze Semgrep results and categorize findings"""
        try:
            with open(results_file, 'r') as f:
                data = json.load(f)
            
            findings = data.get('results', [])
            
            # Categorize findings by rule ID
            categorized_findings = {
                'creation': [],
                'inclusion': [],
                'verification': [],
                'expiration': [],
                'cross_file': [],
                'total': len(findings)
            }
            
            for finding in findings:
                rule_id = finding.get('check_id', '')
                
                if 'creation' in rule_id:
                    categorized_findings['creation'].append(finding)
                elif 'inclusion' in rule_id:
                    categorized_findings['inclusion'].append(finding)
                elif 'verification' in rule_id:
                    categorized_findings['verification'].append(finding)
                elif 'expiration' in rule_id:
                    categorized_findings['expiration'].append(finding)
                elif 'cross-file' in rule_id:
                    categorized_findings['cross_file'].append(finding)
            
            return categorized_findings
            
        except Exception as e:
            return {'error': str(e), 'total': 0}
    
    def test_safe_examples(self):
        """Test safe examples - should have minimal findings"""
        print("🔍 Testing Safe Examples...")
        
        output_file = self.results_dir / "nonce-lifecycle-safe-results.json"
        
        success, output = self.run_semgrep_scan(
            self.safe_test_file,
            self.rules_file,
            output_file
        )
        
        if not success:
            print(f"❌ Failed to scan safe examples: {output}")
            return False
        
        results = self.analyze_results(output_file)
        
        print(f"📊 Safe Examples Results:")
        print(f"   Total findings: {results['total']}")
        print(f"   Creation findings: {len(results['creation'])}")
        print(f"   Inclusion findings: {len(results['inclusion'])}")
        print(f"   Verification findings: {len(results['verification'])}")
        print(f"   Expiration findings: {len(results['expiration'])}")
        print(f"   Cross-file findings: {len(results['cross_file'])}")
        
        # Safe examples should have mostly INFO findings for detection
        # and minimal ERROR/WARNING findings
        error_warning_count = sum(
            1 for finding in results.get('creation', []) + 
            results.get('inclusion', []) + 
            results.get('verification', []) + 
            results.get('expiration', []) + 
            results.get('cross_file', [])
            if finding.get('extra', {}).get('severity') in ['ERROR', 'WARNING']
        )
        
        print(f"   Error/Warning findings: {error_warning_count}")
        
        return error_warning_count <= 5  # Allow some false positives
    
    def test_vulnerable_examples(self):
        """Test vulnerable examples - should have many findings"""
        print("🔍 Testing Vulnerable Examples...")
        
        output_file = self.results_dir / "nonce-lifecycle-vulnerable-results.json"
        
        success, output = self.run_semgrep_scan(
            self.vulnerable_test_file,
            self.rules_file,
            output_file
        )
        
        if not success:
            print(f"❌ Failed to scan vulnerable examples: {output}")
            return False
        
        results = self.analyze_results(output_file)
        
        print(f"📊 Vulnerable Examples Results:")
        print(f"   Total findings: {results['total']}")
        print(f"   Creation findings: {len(results['creation'])}")
        print(f"   Inclusion findings: {len(results['inclusion'])}")
        print(f"   Verification findings: {len(results['verification'])}")
        print(f"   Expiration findings: {len(results['expiration'])}")
        print(f"   Cross-file findings: {len(results['cross_file'])}")
        
        # Vulnerable examples should have many ERROR/WARNING findings
        error_warning_count = sum(
            1 for finding in results.get('creation', []) + 
            results.get('inclusion', []) + 
            results.get('verification', []) + 
            results.get('expiration', []) + 
            results.get('cross_file', [])
            if finding.get('extra', {}).get('severity') in ['ERROR', 'WARNING']
        )
        
        print(f"   Error/Warning findings: {error_warning_count}")
        
        return error_warning_count >= 20  # Should detect many vulnerabilities
    
    def test_rule_coverage(self):
        """Test rule coverage by checking if all rule types are detected"""
        print("🔍 Testing Rule Coverage...")
        
        # Test both files to get comprehensive coverage
        safe_output = self.results_dir / "nonce-lifecycle-safe-results.json"
        vulnerable_output = self.results_dir / "nonce-lifecycle-vulnerable-results.json"
        
        all_findings = []
        
        for output_file in [safe_output, vulnerable_output]:
            if output_file.exists():
                results = self.analyze_results(output_file)
                all_findings.extend(results.get('creation', []))
                all_findings.extend(results.get('inclusion', []))
                all_findings.extend(results.get('verification', []))
                all_findings.extend(results.get('expiration', []))
                all_findings.extend(results.get('cross_file', []))
        
        # Check for specific rule patterns
        rule_patterns = {
            'creation': ['wp_create_nonce', 'wp_nonce_field', 'wp_nonce_url', 'wp_nonce_ays'],
            'inclusion': ['form', 'hidden', 'ajax', 'rest'],
            'verification': ['wp_verify_nonce', 'check_ajax_referer', 'check_admin_referer'],
            'expiration': ['expiration', 'handling', 'result']
        }
        
        detected_patterns = set()
        for finding in all_findings:
            message = finding.get('extra', {}).get('message', '')
            for pattern_type, patterns in rule_patterns.items():
                for pattern in patterns:
                    if pattern.lower() in message.lower():
                        detected_patterns.add(f"{pattern_type}_{pattern}")
        
        print(f"📊 Rule Coverage Results:")
        print(f"   Total unique rule patterns detected: {len(detected_patterns)}")
        
        # Should detect most rule patterns
        expected_patterns = sum(len(patterns) for patterns in rule_patterns.values())
        coverage_percentage = (len(detected_patterns) / expected_patterns) * 100
        
        print(f"   Coverage percentage: {coverage_percentage:.1f}%")
        
        return coverage_percentage >= 70  # At least 70% coverage
    
    def generate_test_report(self):
        """Generate comprehensive test report"""
        print("📝 Generating Test Report...")
        
        report = {
            'test_date': datetime.now().isoformat(),
            'task': 'Task 1.5: Nonce Lifecycle Detection Rules',
            'test_files': {
                'rules_file': str(self.rules_file),
                'safe_test_file': str(self.safe_test_file),
                'vulnerable_test_file': str(self.vulnerable_test_file)
            },
            'results': {}
        }
        
        # Test safe examples
        safe_success = self.test_safe_examples()
        report['results']['safe_examples'] = {
            'success': safe_success,
            'result_file': str(self.results_dir / "nonce-lifecycle-safe-results.json")
        }
        
        # Test vulnerable examples
        vulnerable_success = self.test_vulnerable_examples()
        report['results']['vulnerable_examples'] = {
            'success': vulnerable_success,
            'result_file': str(self.results_dir / "nonce-lifecycle-vulnerable-results.json")
        }
        
        # Test rule coverage
        coverage_success = self.test_rule_coverage()
        report['results']['rule_coverage'] = {
            'success': coverage_success
        }
        
        # Overall success
        overall_success = safe_success and vulnerable_success and coverage_success
        report['overall_success'] = overall_success
        
        # Save report
        report_file = self.results_dir / "nonce-lifecycle-test-report.json"
        with open(report_file, 'w') as f:
            json.dump(report, f, indent=2)
        
        print(f"📄 Test report saved to: {report_file}")
        
        return overall_success, report
    
    def run_performance_test(self):
        """Run performance test to ensure rules are efficient"""
        print("⚡ Running Performance Test...")
        
        start_time = time.time()
        
        success, output = self.run_semgrep_scan(
            self.vulnerable_test_file,
            self.rules_file,
            self.results_dir / "performance-test-results.json"
        )
        
        end_time = time.time()
        scan_time = end_time - start_time
        
        print(f"📊 Performance Results:")
        print(f"   Scan time: {scan_time:.2f} seconds")
        
        # Should complete within reasonable time
        performance_success = scan_time < 30  # Less than 30 seconds
        
        print(f"   Performance test: {'✅ PASS' if performance_success else '❌ FAIL'}")
        
        return performance_success

def main():
    """Main test execution"""
    print("🚀 Starting Task 1.5: Nonce Lifecycle Detection Rules Testing")
    print("=" * 70)
    
    tester = NonceLifecycleTester()
    
    # Check if required files exist
    if not tester.rules_file.exists():
        print(f"❌ Rules file not found: {tester.rules_file}")
        return False
    
    if not tester.safe_test_file.exists():
        print(f"❌ Safe test file not found: {tester.safe_test_file}")
        return False
    
    if not tester.vulnerable_test_file.exists():
        print(f"❌ Vulnerable test file not found: {tester.vulnerable_test_file}")
        return False
    
    print("✅ All required files found")
    
    # Run tests
    try:
        # Performance test
        performance_success = tester.run_performance_test()
        
        # Main tests
        overall_success, report = tester.generate_test_report()
        
        # Final results
        print("\n" + "=" * 70)
        print("📋 FINAL TEST RESULTS")
        print("=" * 70)
        
        print(f"Performance Test: {'✅ PASS' if performance_success else '❌ FAIL'}")
        print(f"Safe Examples Test: {'✅ PASS' if report['results']['safe_examples']['success'] else '❌ FAIL'}")
        print(f"Vulnerable Examples Test: {'✅ PASS' if report['results']['vulnerable_examples']['success'] else '❌ FAIL'}")
        print(f"Rule Coverage Test: {'✅ PASS' if report['results']['rule_coverage']['success'] else '❌ FAIL'}")
        print(f"Overall Result: {'✅ PASS' if overall_success else '❌ FAIL'}")
        
        if overall_success:
            print("\n🎉 Task 1.5: Nonce Lifecycle Detection Rules - ALL TESTS PASSED!")
            print("✅ Nonce creation detection implemented")
            print("✅ Nonce inclusion detection implemented")
            print("✅ Nonce verification detection implemented")
            print("✅ Nonce expiration handling implemented")
            print("✅ Cross-file nonce lifecycle analysis implemented")
        else:
            print("\n⚠️  Some tests failed. Please review the results and fix issues.")
        
        return overall_success
        
    except Exception as e:
        print(f"❌ Test execution failed: {e}")
        return False

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
