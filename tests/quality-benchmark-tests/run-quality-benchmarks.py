#!/usr/bin/env python3
"""
Run quality benchmarks against the 100 test files to validate rule accuracy.
"""

import os
import json
import subprocess
import time
import sys
from pathlib import Path
from typing import Dict, List, Tuple, Any

class QualityBenchmarkRunner:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.test_dir = self.project_root / "tests" / "quality-benchmark-tests"
        self.results_dir = self.project_root / "tests" / "quality-benchmark-tests" / "results"
        self.configs_dir = self.project_root / "configs"
        
        # Create results directory
        self.results_dir.mkdir(exist_ok=True)
        
        # Expected findings from test files
        self.expected_findings = self._load_expected_findings()
        
        # Quality benchmark targets
        self.quality_targets = {
            "precision_min": 0.95,
            "recall_min": 0.95,
            "fp_rate_max": 0.05,
            "fn_rate_max": 0.05,
            "test_coverage_min": 1.0,
            "baseline_stability_min": 0.99
        }
    
    def _load_expected_findings(self) -> Dict[str, int]:
        """Load expected findings from test file registry."""
        registry_file = self.test_dir / "TEST_FILE_REGISTRY.md"
        expected = {}
        
        if registry_file.exists():
            with open(registry_file, 'r', encoding='utf-8') as f:
                content = f.read()
                
            # Parse the registry to extract expected findings
            lines = content.split('\n')
            for line in lines:
                if '|' in line and '.php' in line:
                    parts = [p.strip() for p in line.split('|')]
                    if len(parts) >= 4:
                        filename = parts[1]
                        expected_count = parts[3]
                        if filename.endswith('.php') and expected_count.isdigit():
                            expected[filename] = int(expected_count)
        
        return expected
    
    def get_available_configs(self) -> List[str]:
        """Get available Semgrep configuration files."""
        configs = []
        if self.configs_dir.exists():
            for config_file in self.configs_dir.glob("*.yaml"):
                configs.append(config_file.name)
        return configs
    
    def run_semgrep_scan(self, config: str, test_files: List[str]) -> Dict[str, Any]:
        """Run Semgrep scan with specified configuration."""
        config_path = self.configs_dir / config
        
        if not config_path.exists():
            raise FileNotFoundError(f"Configuration file not found: {config_path}")
        
        # Build command
        cmd = [
            "semgrep",
            "--config", str(config_path),
            "--json",
            "--quiet"
        ]
        
        # Add test files
        for test_file in test_files:
            cmd.append(str(self.test_dir / test_file))
        
        print(f"Running Semgrep with config: {config}")
        print(f"Command: {' '.join(cmd)}")
        
        start_time = time.time()
        
        try:
            result = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                cwd=self.project_root,
                timeout=300  # 5 minute timeout
            )
            
            duration = time.time() - start_time
            
            if result.returncode == 0:
                # Parse JSON output
                try:
                    findings = json.loads(result.stdout)
                    return {
                        "success": True,
                        "findings": findings,
                        "duration": duration,
                        "config": config,
                        "files_scanned": len(test_files)
                    }
                except json.JSONDecodeError:
                    return {
                        "success": False,
                        "error": "Failed to parse JSON output",
                        "stdout": result.stdout,
                        "stderr": result.stderr,
                        "duration": duration,
                        "config": config
                    }
            else:
                return {
                    "success": False,
                    "error": f"Semgrep failed with return code {result.returncode}",
                    "stdout": result.stdout,
                    "stderr": result.stderr,
                    "duration": duration,
                    "config": config
                }
                
        except subprocess.TimeoutExpired:
            return {
                "success": False,
                "error": "Semgrep scan timed out",
                "duration": 300,
                "config": config
            }
        except Exception as e:
            return {
                "success": False,
                "error": f"Exception during scan: {str(e)}",
                "duration": time.time() - start_time,
                "config": config
            }
    
    def analyze_findings(self, scan_result: Dict[str, Any]) -> Dict[str, Any]:
        """Analyze scan findings against expected results."""
        if not scan_result["success"]:
            return {
                "analysis_success": False,
                "error": scan_result["error"]
            }
        
        findings = scan_result["findings"]
        results = scan_result["results"] if "results" in scan_result else []
        
        # Count findings per file
        findings_per_file = {}
        total_findings = 0
        
        for result in results:
            file_path = result.get("path", "")
            if file_path:
                filename = os.path.basename(file_path)
                if filename not in findings_per_file:
                    findings_per_file[filename] = 0
                findings_per_file[filename] += 1
                total_findings += 1
        
        # Calculate metrics
        true_positives = 0
        false_positives = 0
        false_negatives = 0
        
        for filename, expected_count in self.expected_findings.items():
            actual_count = findings_per_file.get(filename, 0)
            
            if expected_count > 0:  # Vulnerable file
                if actual_count >= expected_count:
                    true_positives += expected_count
                    if actual_count > expected_count:
                        false_positives += (actual_count - expected_count)
                else:
                    true_positives += actual_count
                    false_negatives += (expected_count - actual_count)
            else:  # Safe file
                if actual_count > 0:
                    false_positives += actual_count
                # No false negatives for safe files
        
        # Calculate precision and recall
        precision = true_positives / (true_positives + false_positives) if (true_positives + false_positives) > 0 else 0
        recall = true_positives / (true_positives + false_negatives) if (true_positives + false_negatives) > 0 else 0
        
        # Calculate F1 score
        f1_score = 2 * (precision * recall) / (precision + recall) if (precision + recall) > 0 else 0
        
        # Calculate false positive and negative rates
        fp_rate = false_positives / (true_positives + false_positives) if (true_positives + false_positives) > 0 else 0
        fn_rate = false_negatives / (true_positives + false_negatives) if (true_positives + false_negatives) > 0 else 0
        
        return {
            "analysis_success": True,
            "total_findings": total_findings,
            "true_positives": true_positives,
            "false_positives": false_positives,
            "false_negatives": false_negatives,
            "precision": precision,
            "recall": recall,
            "f1_score": f1_score,
            "fp_rate": fp_rate,
            "fn_rate": fn_rate,
            "findings_per_file": findings_per_file,
            "quality_targets_met": {
                "precision": precision >= self.quality_targets["precision_min"],
                "recall": recall >= self.quality_targets["recall_min"],
                "fp_rate": fp_rate <= self.quality_targets["fp_rate_max"],
                "fn_rate": fn_rate <= self.quality_targets["fn_rate_max"]
            }
        }
    
    def run_benchmarks(self, configs: List[str] = None) -> Dict[str, Any]:
        """Run quality benchmarks for all configurations."""
        if configs is None:
            configs = self.get_available_configs()
        
        if not configs:
            raise ValueError("No configuration files found")
        
        # Get all test files
        test_files = [f.name for f in self.test_dir.glob("*.php")]
        test_files.sort()
        
        print(f"Found {len(test_files)} test files")
        print(f"Testing {len(configs)} configurations: {', '.join(configs)}")
        
        benchmark_results = {
            "timestamp": time.strftime("%Y-%m-%dT%H:%M:%S"),
            "project_root": str(self.project_root),
            "test_files_count": len(test_files),
            "configs_tested": configs,
            "quality_targets": self.quality_targets,
            "expected_total_findings": sum(self.expected_findings.values()),
            "results": {}
        }
        
        for config in configs:
            print(f"\n{'='*60}")
            print(f"Testing configuration: {config}")
            print(f"{'='*60}")
            
            try:
                # Run scan
                scan_result = self.run_semgrep_scan(config, test_files)
                
                # Analyze results
                analysis = self.analyze_findings(scan_result)
                
                # Store results
                benchmark_results["results"][config] = {
                    "scan_result": scan_result,
                    "analysis": analysis
                }
                
                # Print summary
                if analysis["analysis_success"]:
                    print(f"Scan completed in {scan_result['duration']:.2f} seconds")
                    print(f"Total findings: {analysis['total_findings']}")
                    print(f"True positives: {analysis['true_positives']}")
                    print(f"False positives: {analysis['false_positives']}")
                    print(f"False negatives: {analysis['false_negatives']}")
                    print(f"Precision: {analysis['precision']:.4f} ({'✓' if analysis['quality_targets_met']['precision'] else '✗'})")
                    print(f"Recall: {analysis['recall']:.4f} ({'✓' if analysis['quality_targets_met']['recall'] else '✗'})")
                    print(f"F1 Score: {analysis['f1_score']:.4f}")
                    print(f"FP Rate: {analysis['fp_rate']:.4f} ({'✓' if analysis['quality_targets_met']['fp_rate'] else '✗'})")
                    print(f"FN Rate: {analysis['fn_rate']:.4f} ({'✓' if analysis['quality_targets_met']['fn_rate'] else '✗'})")
                else:
                    print(f"Analysis failed: {analysis['error']}")
                
            except Exception as e:
                print(f"Error testing {config}: {str(e)}")
                benchmark_results["results"][config] = {
                    "error": str(e)
                }
        
        # Save results
        results_file = self.results_dir / f"quality-benchmark-results-{int(time.time())}.json"
        with open(results_file, 'w', encoding='utf-8') as f:
            json.dump(benchmark_results, f, indent=2, default=str)
        
        print(f"\n{'='*60}")
        print(f"Benchmark results saved to: {results_file}")
        print(f"{'='*60}")
        
        return benchmark_results
    
    def generate_report(self, benchmark_results: Dict[str, Any]) -> str:
        """Generate a human-readable report from benchmark results."""
        report = []
        report.append("# Quality Benchmark Report")
        report.append("")
        report.append(f"**Generated**: {benchmark_results['timestamp']}")
        report.append(f"**Project**: {benchmark_results['project_root']}")
        report.append(f"**Test Files**: {benchmark_results['test_files_count']}")
        report.append(f"**Expected Findings**: {benchmark_results['expected_total_findings']}")
        report.append("")
        
        # Quality targets
        report.append("## Quality Targets")
        report.append("")
        for target, value in benchmark_results['quality_targets'].items():
            report.append(f"- **{target}**: {value}")
        report.append("")
        
        # Results summary
        report.append("## Results Summary")
        report.append("")
        
        for config, result in benchmark_results['results'].items():
            report.append(f"### {config}")
            report.append("")
            
            if 'error' in result:
                report.append(f"**Error**: {result['error']}")
                report.append("")
                continue
            
            analysis = result['analysis']
            if not analysis['analysis_success']:
                report.append(f"**Analysis Error**: {analysis['error']}")
                report.append("")
                continue
            
            # Status indicators
            status_indicators = []
            for metric, met in analysis['quality_targets_met'].items():
                status = "✓ PASS" if met else "✗ FAIL"
                status_indicators.append(f"{metric}: {status}")
            
            report.append("**Status**: " + " | ".join(status_indicators))
            report.append("")
            
            # Metrics
            report.append("**Metrics**:")
            report.append(f"- Precision: {analysis['precision']:.4f}")
            report.append(f"- Recall: {analysis['recall']:.4f}")
            report.append(f"- F1 Score: {analysis['f1_score']:.4f}")
            report.append(f"- False Positive Rate: {analysis['fp_rate']:.4f}")
            report.append(f"- False Negative Rate: {analysis['fn_rate']:.4f}")
            report.append("")
            
            # Findings
            report.append("**Findings**:")
            report.append(f"- Total: {analysis['total_findings']}")
            report.append(f"- True Positives: {analysis['true_positives']}")
            report.append(f"- False Positives: {analysis['false_positives']}")
            report.append(f"- False Negatives: {analysis['false_negatives']}")
            report.append("")
        
        return "\n".join(report)

def main():
    """Main function to run quality benchmarks."""
    if len(sys.argv) < 2:
        print("Usage: python run-quality-benchmarks.py <project_root> [config1 config2 ...]")
        sys.exit(1)
    
    project_root = sys.argv[1]
    configs = sys.argv[2:] if len(sys.argv) > 2 else None
    
    if not os.path.exists(project_root):
        print(f"Project root does not exist: {project_root}")
        sys.exit(1)
    
    try:
        runner = QualityBenchmarkRunner(project_root)
        
        if configs:
            print(f"Testing specified configurations: {', '.join(configs)}")
        else:
            print("Testing all available configurations")
        
        # Run benchmarks
        results = runner.run_benchmarks(configs)
        
        # Generate and save report
        report = runner.generate_report(results)
        report_file = runner.results_dir / f"quality-benchmark-report-{int(time.time())}.md"
        
        with open(report_file, 'w', encoding='utf-8') as f:
            f.write(report)
        
        print(f"\nReport saved to: {report_file}")
        
        # Print summary
        print("\n" + "="*60)
        print("QUALITY BENCHMARK SUMMARY")
        print("="*60)
        
        for config, result in results['results'].items():
            if 'error' in result:
                print(f"{config}: ERROR - {result['error']}")
                continue
            
            analysis = result['analysis']
            if not analysis['analysis_success']:
                print(f"{config}: ANALYSIS ERROR - {analysis['error']}")
                continue
            
            # Check if all targets are met
            all_targets_met = all(analysis['quality_targets_met'].values())
            status = "PASS" if all_targets_met else "FAIL"
            
            print(f"{config}: {status}")
            print(f"  Precision: {analysis['precision']:.4f} (Target: ≥{results['quality_targets']['precision_min']})")
            print(f"  Recall: {analysis['recall']:.4f} (Target: ≥{results['quality_targets']['recall_min']})")
            print(f"  FP Rate: {analysis['fp_rate']:.4f} (Target: ≤{results['quality_targets']['fp_rate_max']})")
            print(f"  FN Rate: {analysis['fn_rate']:.4f} (Target: ≤{results['quality_targets']['fn_rate_max']})")
        
    except Exception as e:
        print(f"Error running benchmarks: {str(e)}")
        sys.exit(1)

if __name__ == '__main__':
    main()
