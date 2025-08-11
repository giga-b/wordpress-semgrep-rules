#!/usr/bin/env python3
"""
Enhanced Cross-File Analysis Test Script
Tests both Python-based cross-file analysis and true Semgrep cross-file analysis
"""

import warnings
# Suppress pkg_resources deprecation warning
warnings.filterwarnings("ignore", category=UserWarning, module="pkg_resources")

import os
import sys
import json
import time
import subprocess
from pathlib import Path
from typing import Dict, List, Tuple, Optional

class EnhancedCrossFileAnalysisTester:
    def __init__(self, project_root: str = None):
        self.project_root = Path(project_root) if project_root else Path(__file__).parent.parent
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
                "tests/vulnerable-examples/nonce-vulnerable.php",
                "tests/cross-file-test-cases/cross-file-nonce-vulnerable-callbacks.php"
            ],
            "safe": [
                "tests/safe-examples/ajax-safe.php",
                "tests/safe-examples/nonce-safe.php",
                "tests/cross-file-test-cases/cross-file-nonce-safe.php"
            ],
            "corpus": [
                "corpus/wordpress-plugins/gamipress/includes/ajax-functions.php",
                "corpus/wordpress-plugins/gamipress-leaderboards/includes/ajax-functions.php"
            ]
        }
    
    def check_semgrep_availability(self) -> Dict[str, bool]:
        """Check availability of different Semgrep analysis modes"""
        availability = {
            "cli_installed": False,
            "logged_in": False,
            "cross_file_enabled": False,
            "pro_features": False
        }
        
        # Check if Semgrep CLI is installed
        try:
            result = subprocess.run(["semgrep", "--version"], 
                                  capture_output=True, text=True, timeout=10, encoding='utf-8')
            availability["cli_installed"] = result.returncode == 0
        except (FileNotFoundError, subprocess.TimeoutExpired):
            pass
        
        # Check if logged into Semgrep AppSec Platform
        if availability["cli_installed"]:
            try:
                result = subprocess.run(["semgrep", "whoami"], 
                                      capture_output=True, text=True, timeout=10, encoding='utf-8')
                availability["logged_in"] = (
                    result.returncode == 0 and 
                    "not logged in" not in result.stdout.lower()
                )
            except (subprocess.TimeoutExpired, Exception):
                pass
        
        # Test cross-file analysis capability
        if availability["logged_in"]:
            availability["cross_file_enabled"] = self.test_cross_file_capability()
            availability["pro_features"] = availability["cross_file_enabled"]
        
        return availability
    
    def test_cross_file_capability(self) -> bool:
        """Test if cross-file analysis is working"""
        try:
            test_file = self.project_root / "tests" / "safe-examples" / "nonce-lifecycle-safe.php"
            if not test_file.exists():
                return False
            
            result = subprocess.run([
                "semgrep", "ci",
                "--config", str(self.project_root / "packs" / "wp-core-security" / "nonce-lifecycle-detection.yaml"),
                str(test_file),
                "--json"
            ], capture_output=True, text=True, timeout=30, encoding='utf-8')
            
            return result.returncode == 0
        except Exception:
            return False
    
    def run_semgrep_scan(self, config_file: str, target_paths: List[str], 
                        use_cross_file: bool = False) -> Dict:
        """Run Semgrep scan and return results"""
        try:
            cmd = ["semgrep"]
            
            if use_cross_file:
                cmd.append("ci")  # Use ci for cross-file analysis
            else:
                cmd.extend(["--config", config_file])
            
            cmd.extend(["--json"])
            
            if not use_cross_file:
                cmd.extend(["--config", config_file])
            
            cmd.extend(target_paths)
            
            start_time = time.time()
            result = subprocess.run(cmd, capture_output=True, text=True, 
                                  cwd=self.project_root, timeout=120, encoding='utf-8')
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
    
    def test_python_based_analysis(self) -> Dict:
        """Test Python-based cross-file analysis (existing functionality)"""
        print("Testing Python-based Cross-File Analysis...")
        
        results = {
            "python_analysis": {},
            "performance_metrics": {},
            "accuracy_metrics": {}
        }
        
        # Test with cross-file nonce analysis rule
        config_file = str(self.rules_path / "cross-file-nonce-analysis.yaml")
        
        for test_type, test_paths in self.test_cases.items():
            print(f"  Testing {test_type} cases...")
            
            # Convert relative paths to absolute
            absolute_paths = [str(self.project_root / path) for path in test_paths]
            
            scan_result = self.run_semgrep_scan(config_file, absolute_paths, use_cross_file=False)
            
            results["python_analysis"][test_type] = {
                "scan_result": scan_result,
                "test_paths": test_paths
            }
        
        return results
    
    def test_semgrep_cross_file_analysis(self) -> Dict:
        """Test true Semgrep cross-file analysis"""
        print("Testing Semgrep Cross-File Analysis...")
        
        results = {
            "semgrep_cross_file": {},
            "performance_metrics": {},
            "accuracy_metrics": {}
        }
        
        # Test with enhanced nonce lifecycle detection rules
        config_file = str(self.rules_path / "nonce-lifecycle-detection.yaml")
        
        for test_type, test_paths in self.test_cases.items():
            print(f"  Testing {test_type} cases...")
            
            # Convert relative paths to absolute
            absolute_paths = [str(self.project_root / path) for path in test_paths]
            
            scan_result = self.run_semgrep_scan(config_file, absolute_paths, use_cross_file=True)
            
            results["semgrep_cross_file"][test_type] = {
                "scan_result": scan_result,
                "test_paths": test_paths
            }
        
        return results
    
    def compare_analysis_methods(self, python_results: Dict, semgrep_results: Dict) -> Dict:
        """Compare Python-based vs Semgrep cross-file analysis"""
        print("Comparing Analysis Methods...")
        
        comparison = {
            "python_findings": 0,
            "semgrep_findings": 0,
            "performance_comparison": {},
            "accuracy_comparison": {},
            "recommendations": []
        }
        
        # Count findings
        for test_type in self.test_cases.keys():
            python_scan = python_results.get("python_analysis", {}).get(test_type, {})
            semgrep_scan = semgrep_results.get("semgrep_cross_file", {}).get(test_type, {})
            
            if python_scan.get("scan_result", {}).get("success"):
                python_findings = len(python_scan["scan_result"].get("results", []))
                comparison["python_findings"] += python_findings
            
            if semgrep_scan.get("scan_result", {}).get("success"):
                semgrep_findings = len(semgrep_scan["scan_result"].get("results", []))
                comparison["semgrep_findings"] += semgrep_findings
        
        # Performance comparison
        python_duration = sum(
            scan.get("scan_result", {}).get("duration", 0)
            for scan in python_results.get("python_analysis", {}).values()
        )
        semgrep_duration = sum(
            scan.get("scan_result", {}).get("duration", 0)
            for scan in semgrep_results.get("semgrep_cross_file", {}).values()
        )
        
        comparison["performance_comparison"] = {
            "python_duration": python_duration,
            "semgrep_duration": semgrep_duration,
            "speed_improvement": (python_duration - semgrep_duration) / python_duration * 100 if python_duration > 0 else 0
        }
        
        # Generate recommendations
        if comparison["semgrep_findings"] > comparison["python_findings"]:
            comparison["recommendations"].append(
                "Semgrep cross-file analysis found more vulnerabilities - use for comprehensive security audits"
            )
        
        if comparison["performance_comparison"]["speed_improvement"] > 0:
            comparison["recommendations"].append(
                f"Semgrep cross-file analysis is {comparison['performance_comparison']['speed_improvement']:.1f}% faster"
            )
        else:
            comparison["recommendations"].append(
                "Python-based analysis is faster for quick scans"
            )
        
        comparison["recommendations"].append(
            "Use both methods: Python-based for quick scans, Semgrep cross-file for deep analysis"
        )
        
        return comparison
    
    def generate_comprehensive_report(self, availability: Dict, python_results: Dict, 
                                    semgrep_results: Dict, comparison: Dict) -> str:
        """Generate comprehensive test report"""
        report = []
        report.append("# Enhanced Cross-File Analysis Test Report")
        report.append(f"Generated: {time.strftime('%Y-%m-%d %H:%M:%S')}")
        report.append("")
        
        # System Availability
        report.append("## System Availability")
        report.append("")
        report.append(f"- Semgrep CLI Installed: {'✅' if availability['cli_installed'] else '❌'}")
        report.append(f"- Logged into Semgrep AppSec Platform: {'✅' if availability['logged_in'] else '❌'}")
        report.append(f"- Cross-File Analysis Enabled: {'✅' if availability['cross_file_enabled'] else '❌'}")
        report.append(f"- Pro Features Available: {'✅' if availability['pro_features'] else '❌'}")
        report.append("")
        
        # Analysis Results
        report.append("## Analysis Results")
        report.append("")
        
        # Python-based analysis results
        report.append("### Python-Based Cross-File Analysis")
        report.append("")
        for test_type, test_result in python_results.get("python_analysis", {}).items():
            scan_result = test_result.get("scan_result", {})
            if scan_result.get("success"):
                findings = len(scan_result.get("results", []))
                duration = scan_result.get("duration", 0)
                report.append(f"- **{test_type.title()}**: {findings} findings in {duration:.2f}s")
            else:
                report.append(f"- **{test_type.title()}**: Failed - {scan_result.get('error', 'Unknown error')}")
        report.append("")
        
        # Semgrep cross-file analysis results
        if availability["cross_file_enabled"]:
            report.append("### Semgrep Cross-File Analysis")
            report.append("")
            for test_type, test_result in semgrep_results.get("semgrep_cross_file", {}).items():
                scan_result = test_result.get("scan_result", {})
                if scan_result.get("success"):
                    findings = len(scan_result.get("results", []))
                    duration = scan_result.get("duration", 0)
                    report.append(f"- **{test_type.title()}**: {findings} findings in {duration:.2f}s")
                else:
                    report.append(f"- **{test_type.title()}**: Failed - {scan_result.get('error', 'Unknown error')}")
            report.append("")
        
        # Comparison
        report.append("## Comparison")
        report.append("")
        report.append(f"- **Python-Based Findings**: {comparison['python_findings']}")
        if availability["cross_file_enabled"]:
            report.append(f"- **Semgrep Cross-File Findings**: {comparison['semgrep_findings']}")
            report.append(f"- **Performance**: {comparison['performance_comparison']['speed_improvement']:.1f}% improvement with Semgrep")
        report.append("")
        
        # Recommendations
        report.append("## Recommendations")
        report.append("")
        for recommendation in comparison.get("recommendations", []):
            report.append(f"- {recommendation}")
        report.append("")
        
        # Setup Instructions
        report.append("## Setup Instructions")
        report.append("")
        if not availability["cli_installed"]:
            report.append("### Install Semgrep CLI")
            report.append("```bash")
            report.append("python -m pip install semgrep")
            report.append("```")
            report.append("")
        
        if not availability["logged_in"]:
            report.append("### Create Semgrep AppSec Platform Account")
            report.append("1. Go to https://semgrep.dev/login")
            report.append("2. Sign up for a free account (up to 10 contributors)")
            report.append("3. Run: `semgrep login`")
            report.append("")
        
        if not availability["cross_file_enabled"]:
            report.append("### Enable Cross-File Analysis")
            report.append("1. Go to https://semgrep.dev/orgs/-/settings/general/code")
            report.append("2. Toggle 'Cross-file analysis' to ON")
            report.append("3. Save settings")
            report.append("")
        
        report.append("## Usage")
        report.append("")
        report.append("### Python-Based Analysis (Always Available)")
        report.append("```bash")
        report.append("python tests/cross-file-analysis-test.py")
        report.append("```")
        report.append("")
        
        if availability["cross_file_enabled"]:
            report.append("### Semgrep Cross-File Analysis")
            report.append("```bash")
            report.append("semgrep ci --config packs/wp-core-security/nonce-lifecycle-detection.yaml")
            report.append("```")
            report.append("")
        
        return "\n".join(report)
    
    def save_results(self, availability: Dict, python_results: Dict, 
                    semgrep_results: Dict, comparison: Dict, report: str):
        """Save test results and report"""
        # Save JSON results
        results_data = {
            "availability": availability,
            "python_results": python_results,
            "semgrep_results": semgrep_results,
            "comparison": comparison,
            "timestamp": time.strftime('%Y-%m-%d %H:%M:%S')
        }
        
        results_file = self.results_path / "enhanced-cross-file-analysis-results.json"
        with open(results_file, 'w', encoding='utf-8') as f:
            json.dump(results_data, f, indent=2)
        
        # Save report
        report_file = self.results_path / "enhanced-cross-file-analysis-report.md"
        with open(report_file, 'w', encoding='utf-8') as f:
            f.write(report)
        
        print(f"✅ Results saved to: {results_file}")
        print(f"✅ Report saved to: {report_file}")
    
    def run_full_test_suite(self):
        """Run the complete enhanced cross-file analysis test suite"""
        print("🚀 Enhanced Cross-File Analysis Test Suite")
        print("=" * 50)
        
        # Check system availability
        print("\n📋 Checking system availability...")
        availability = self.check_semgrep_availability()
        
        # Test Python-based analysis
        print("\n📋 Testing Python-based cross-file analysis...")
        python_results = self.test_python_based_analysis()
        
        # Test Semgrep cross-file analysis (if available)
        semgrep_results = {}
        if availability["cross_file_enabled"]:
            print("\n📋 Testing Semgrep cross-file analysis...")
            semgrep_results = self.test_semgrep_cross_file_analysis()
        else:
            print("\n⚠️ Semgrep cross-file analysis not available - skipping")
        
        # Compare methods
        print("\n📋 Comparing analysis methods...")
        comparison = self.compare_analysis_methods(python_results, semgrep_results)
        
        # Generate report
        print("\n📋 Generating comprehensive report...")
        report = self.generate_comprehensive_report(availability, python_results, 
                                                  semgrep_results, comparison)
        
        # Save results
        print("\n📋 Saving results...")
        self.save_results(availability, python_results, semgrep_results, comparison, report)
        
        # Print summary
        print("\n🎉 Test suite completed!")
        print(f"📊 Python-based findings: {comparison['python_findings']}")
        if availability["cross_file_enabled"]:
            print(f"📊 Semgrep cross-file findings: {comparison['semgrep_findings']}")
        print(f"📈 Performance improvement: {comparison['performance_comparison']['speed_improvement']:.1f}%")
        
        print("\n📚 Recommendations:")
        for recommendation in comparison.get("recommendations", []):
            print(f"   • {recommendation}")

def main():
    """Main function"""
    if len(sys.argv) > 1:
        project_root = sys.argv[1]
    else:
        project_root = None
    
    tester = EnhancedCrossFileAnalysisTester(project_root)
    tester.run_full_test_suite()

if __name__ == "__main__":
    main()
