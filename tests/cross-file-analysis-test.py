#!/usr/bin/env python3
"""
Cross-File Analysis Performance and Accuracy Testing
Tests the cross-file analysis rules for nonce lifecycle detection
"""

import os
import sys
import json
import time
import subprocess
from pathlib import Path
from typing import Dict, List, Tuple

class CrossFileAnalysisTester:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.corpus_path = self.project_root / "corpus" / "wordpress-plugins"
        self.test_path = self.project_root / "tests"
        self.results_path = self.project_root / "results" / "cross-file-analysis"
        self.rules_path = self.project_root / "packs" / "wp-core-security"
        
        # Ensure results directory exists
        self.results_path.mkdir(parents=True, exist_ok=True)
        
        # Test cases for cross-file analysis
        self.test_cases = {
            "vulnerable": [
                "tests/vulnerable-examples/ajax-vulnerable.php",
                "tests/vulnerable-examples/nonce-vulnerable.php"
            ],
            "safe": [
                "tests/safe-examples/ajax-safe.php",
                "tests/safe-examples/nonce-safe.php"
            ],
            "corpus": [
                "corpus/wordpress-plugins/gamipress/includes/ajax-functions.php",
                "corpus/wordpress-plugins/gamipress-leaderboards/includes/ajax-functions.php"
            ]
        }
    
    def run_semgrep_scan(self, config_file: str, target_paths: List[str]) -> Dict:
        """Run Semgrep scan and return results"""
        try:
            cmd = [
                "semgrep",
                "--config", config_file,
                "--json",
                "--no-git-ignore"
            ] + target_paths
            
            start_time = time.time()
            result = subprocess.run(cmd, capture_output=True, text=True, cwd=self.project_root)
            end_time = time.time()
            
            if result.returncode == 0:
                return {
                    "success": True,
                    "results": json.loads(result.stdout),
                    "duration": end_time - start_time,
                    "stderr": result.stderr
                }
            else:
                return {
                    "success": False,
                    "error": result.stderr,
                    "duration": end_time - start_time
                }
        except Exception as e:
            return {
                "success": False,
                "error": str(e),
                "duration": 0
            }
    
    def test_cross_file_rules(self) -> Dict:
        """Test cross-file analysis rules"""
        print("Testing Cross-File Analysis Rules...")
        
        results = {
            "cross_file_nonce_analysis": {},
            "performance_metrics": {},
            "accuracy_metrics": {}
        }
        
        # Test cross-file nonce analysis rule
        config_file = str(self.rules_path / "cross-file-nonce-analysis.yaml")
        
        for test_type, test_paths in self.test_cases.items():
            print(f"Testing {test_type} cases...")
            
            # Convert relative paths to absolute
            absolute_paths = [str(self.project_root / path) for path in test_paths]
            
            scan_result = self.run_semgrep_scan(config_file, absolute_paths)
            
            results["cross_file_nonce_analysis"][test_type] = {
                "scan_result": scan_result,
                "test_paths": test_paths
            }
            
            if scan_result["success"]:
                print(f"  ✓ {test_type}: {len(scan_result['results']['results'])} findings in {scan_result['duration']:.2f}s")
            else:
                print(f"  ✗ {test_type}: Error - {scan_result['error']}")
        
        return results
    
    def test_individual_rules(self) -> Dict:
        """Test individual component rules"""
        print("Testing Individual Component Rules...")
        
        results = {}
        
        # Test AJAX action registration detection
        ajax_config = str(self.rules_path / "ajax-action-registration.yaml")
        ajax_result = self.run_semgrep_scan(ajax_config, [str(self.corpus_path)])
        results["ajax_action_registration"] = ajax_result
        
        # Test callback function detection
        callback_config = str(self.rules_path / "ajax-callback-functions.yaml")
        callback_result = self.run_semgrep_scan(callback_config, [str(self.corpus_path)])
        results["callback_functions"] = callback_result
        
        # Test nonce verification detection
        nonce_config = str(self.rules_path / "nonce-verification-detection.yaml")
        nonce_result = self.run_semgrep_scan(nonce_config, [str(self.corpus_path)])
        results["nonce_verification"] = nonce_result
        
        return results
    
    def calculate_performance_metrics(self, results: Dict) -> Dict:
        """Calculate performance metrics"""
        metrics = {
            "total_scans": 0,
            "total_duration": 0,
            "average_duration": 0,
            "successful_scans": 0,
            "failed_scans": 0
        }
        
        # Process cross-file analysis results
        for test_type, test_result in results["cross_file_nonce_analysis"].items():
            metrics["total_scans"] += 1
            if test_result["scan_result"]["success"]:
                metrics["successful_scans"] += 1
                metrics["total_duration"] += test_result["scan_result"]["duration"]
            else:
                metrics["failed_scans"] += 1
        
        # Process individual rule results
        for rule_name, rule_result in results.get("individual_rules", {}).items():
            metrics["total_scans"] += 1
            if rule_result["success"]:
                metrics["successful_scans"] += 1
                metrics["total_duration"] += rule_result["duration"]
            else:
                metrics["failed_scans"] += 1
        
        if metrics["successful_scans"] > 0:
            metrics["average_duration"] = metrics["total_duration"] / metrics["successful_scans"]
        
        return metrics
    
    def calculate_accuracy_metrics(self, results: Dict) -> Dict:
        """Calculate accuracy metrics"""
        metrics = {
            "total_findings": 0,
            "vulnerable_findings": 0,
            "safe_findings": 0,
            "false_positives": 0,
            "false_negatives": 0,
            "precision": 0,
            "recall": 0
        }
        
        # Count findings by test type
        for test_type, test_result in results["cross_file_nonce_analysis"].items():
            if test_result["scan_result"]["success"]:
                findings_count = len(test_result["scan_result"]["results"]["results"])
                metrics["total_findings"] += findings_count
                
                if test_type == "vulnerable":
                    metrics["vulnerable_findings"] += findings_count
                elif test_type == "safe":
                    metrics["safe_findings"] += findings_count
        
        # Calculate precision and recall (simplified)
        if metrics["vulnerable_findings"] > 0:
            metrics["precision"] = metrics["vulnerable_findings"] / metrics["total_findings"]
        
        # Expected findings based on test cases
        expected_vulnerable = 2  # Based on vulnerable test cases
        if expected_vulnerable > 0:
            metrics["recall"] = metrics["vulnerable_findings"] / expected_vulnerable
        
        return metrics
    
    def generate_report(self, results: Dict) -> str:
        """Generate comprehensive test report"""
        report = []
        report.append("# Cross-File Analysis Test Report")
        report.append(f"Generated: {time.strftime('%Y-%m-%d %H:%M:%S')}")
        report.append("")
        
        # Performance Metrics
        performance_metrics = self.calculate_performance_metrics(results)
        report.append("## Performance Metrics")
        report.append(f"- Total Scans: {performance_metrics['total_scans']}")
        report.append(f"- Successful Scans: {performance_metrics['successful_scans']}")
        report.append(f"- Failed Scans: {performance_metrics['failed_scans']}")
        report.append(f"- Total Duration: {performance_metrics['total_duration']:.2f}s")
        report.append(f"- Average Duration: {performance_metrics['average_duration']:.2f}s")
        report.append("")
        
        # Accuracy Metrics
        accuracy_metrics = self.calculate_accuracy_metrics(results)
        report.append("## Accuracy Metrics")
        report.append(f"- Total Findings: {accuracy_metrics['total_findings']}")
        report.append(f"- Vulnerable Findings: {accuracy_metrics['vulnerable_findings']}")
        report.append(f"- Safe Findings: {accuracy_metrics['safe_findings']}")
        report.append(f"- Precision: {accuracy_metrics['precision']:.2%}")
        report.append(f"- Recall: {accuracy_metrics['recall']:.2%}")
        report.append("")
        
        # Detailed Results
        report.append("## Detailed Results")
        
        for test_type, test_result in results["cross_file_nonce_analysis"].items():
            report.append(f"### {test_type.title()} Test Cases")
            
            if test_result["scan_result"]["success"]:
                findings = test_result["scan_result"]["results"]["results"]
                report.append(f"- Findings: {len(findings)}")
                report.append(f"- Duration: {test_result['scan_result']['duration']:.2f}s")
                
                for finding in findings[:5]:  # Show first 5 findings
                    report.append(f"  - {finding.get('check_id', 'Unknown')}: {finding.get('message', 'No message')}")
                
                if len(findings) > 5:
                    report.append(f"  - ... and {len(findings) - 5} more findings")
            else:
                report.append(f"- Error: {test_result['scan_result']['error']}")
            
            report.append("")
        
        return "\n".join(report)
    
    def save_results(self, results: Dict, report: str):
        """Save test results and report"""
        # Save detailed results
        results_file = self.results_path / "cross-file-analysis-results.json"
        with open(results_file, 'w') as f:
            json.dump(results, f, indent=2, default=str)
        
        # Save report
        report_file = self.results_path / "cross-file-analysis-report.md"
        with open(report_file, 'w') as f:
            f.write(report)
        
        print(f"Results saved to: {results_file}")
        print(f"Report saved to: {report_file}")
    
    def run_full_test_suite(self):
        """Run the complete test suite"""
        print("Starting Cross-File Analysis Test Suite")
        print("=" * 50)
        
        # Test cross-file rules
        cross_file_results = self.test_cross_file_rules()
        
        # Test individual rules
        individual_results = self.test_individual_rules()
        
        # Combine results
        all_results = {
            "cross_file_nonce_analysis": cross_file_results["cross_file_nonce_analysis"],
            "individual_rules": individual_results,
            "performance_metrics": cross_file_results.get("performance_metrics", {}),
            "accuracy_metrics": cross_file_results.get("accuracy_metrics", {})
        }
        
        # Generate report
        report = self.generate_report(all_results)
        
        # Save results
        self.save_results(all_results, report)
        
        # Print summary
        print("\n" + "=" * 50)
        print("Test Suite Complete")
        print("=" * 50)
        print(report)

def main():
    if len(sys.argv) != 2:
        print("Usage: python cross-file-analysis-test.py <project_root>")
        sys.exit(1)
    
    project_root = sys.argv[1]
    tester = CrossFileAnalysisTester(project_root)
    tester.run_full_test_suite()

if __name__ == "__main__":
    main()
