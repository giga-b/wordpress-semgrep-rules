#!/usr/bin/env python3
"""
Advanced Testing Framework for WordPress Semgrep Rules
Comprehensive testing system that addresses encoding and timeout issues.
"""

import json
import yaml
import subprocess
import sys
import os
from pathlib import Path
from typing import Dict, List, Tuple, Optional
import argparse
import time
from datetime import datetime
import concurrent.futures
import statistics
import psutil
import threading
import signal

# Shared metrics utilities
try:
    from tests._lib.metrics import load_ground_truth, compute_counts, calculate_metrics
    from tests._lib.fp_detection import load_allowlist, collect_fp_candidates
    from tests._lib.perf import start_perf, stop_perf
except Exception:
    # Fallback when executed with cwd=project root and default module path
    from pathlib import Path as _P
    import sys as _sys
    _sys.path.append(str((_P(__file__).parent / '_lib').resolve()))
    try:
        from metrics import load_ground_truth, compute_counts, calculate_metrics
        from fp_detection import load_allowlist, collect_fp_candidates
        from perf import start_perf, stop_perf
    except Exception:
        # Final fallback to absolute imports via package
        _sys.path.append(str((_P(__file__).parent).resolve()))
        from _lib.metrics import load_ground_truth, compute_counts, calculate_metrics
        from _lib.fp_detection import load_allowlist, collect_fp_candidates
        from _lib.perf import start_perf, stop_perf

class AdvancedTestingFramework:
    def __init__(self, project_root: str, default_timeout: int = 300):
        self.project_root = Path(project_root)
        self.results_dir = self.project_root / "results" / "advanced-testing"
        self.results_dir.mkdir(parents=True, exist_ok=True)
        self.default_timeout = default_timeout
        
        # Load quality configuration
        self.quality_config = self.project_root / ".rule-quality.yml"
        if self.quality_config.exists():
            with open(self.quality_config, 'r', encoding='utf-8') as f:
                self.config = yaml.safe_load(f)
        else:
            self.config = {}
        
        # Performance monitoring
        self.start_memory = psutil.virtual_memory().used
        self.start_time = time.time()
        self.performance_data = []

    def get_minimal_fixture(self, vuln_class: str, is_safe: bool) -> Optional[Path]:
        """Return a minimal fixture file for the given vulnerability class if present."""
        base_dir = self.project_root / "tests" / ("safe-examples" if is_safe else "vulnerable-examples")
        mapping = {
            "xss": base_dir / ("xss-safe-min.php" if is_safe else "xss-vuln-min.php"),
            "sqli": base_dir / ("sqli-safe-min.php" if is_safe else "sqli-vuln-min.php"),
            "csrf": base_dir / ("csrf-safe-min.php" if is_safe else "csrf-vuln-min.php"),
        }
        candidate = mapping.get(vuln_class)
        return candidate if candidate and candidate.exists() else None
    
    def get_rule_files(self) -> List[Path]:
        """Get all rule files for testing."""
        rule_files = []
        
        # Scan all rule packs
        rule_packs = ['packs/wp-core-security', 'packs/wp-core-quality', 'packs/experimental']
        
        for pack in rule_packs:
            pack_path = self.project_root / pack
            if pack_path.exists():
                rule_files.extend(pack_path.glob('*.yaml'))
        
        return rule_files
    
    def extract_rule_metadata(self, rule_file: Path) -> Dict:
        """Extract metadata from a rule file with proper encoding handling."""
        try:
            with open(rule_file, 'r', encoding='utf-8', errors='replace') as f:
                content = yaml.safe_load(f)
            
            if not content or 'rules' not in content:
                return {
                    'id': rule_file.stem,
                    'vuln_class': 'other',
                    'confidence': 'medium'
                }
            
            rule = content['rules'][0]
            metadata = rule.get('metadata', {})
            
            return {
                'id': rule.get('id', rule_file.stem),
                'message': rule.get('message', ''),
                'severity': rule.get('severity', ''),
                'confidence': metadata.get('confidence', 'medium'),
                'cwe': metadata.get('cwe', ''),
                'category': metadata.get('category', ''),
                'vuln_class': metadata.get('vuln_class', 'other'),
                'tags': metadata.get('tags', [])
            }
        except Exception as e:
            return {
                'id': rule_file.stem,
                'error': str(e),
                'vuln_class': 'other',
                'confidence': 'medium'
            }
    
    def run_rule_test_with_timeout(self, rule_file: Path, test_path: Path, timeout: int = 300) -> Dict:
        """Run a rule test with proper timeout and encoding handling."""
        metadata = self.extract_rule_metadata(rule_file)
        
        result = {
            'rule_file': str(rule_file),
            'test_path': str(test_path),
            'rule_id': metadata.get('id', ''),
            'vuln_class': metadata.get('vuln_class', 'other'),
            'confidence': metadata.get('confidence', 'medium'),
            'success': False,
            'findings_count': 0,
            'findings': [],
            'scan_time': 0,
            'memory_usage': 0,
            'error': None,
            'timeout': False,
            'timestamp': datetime.now().isoformat()
        }
        
        try:
            perf_t = start_perf()
            
            # Run Semgrep scan with proper encoding
            # Base command (broad scope for non-heavy rules)
            cmd = [
                'semgrep',
                '--config', str(rule_file),
                '--json',
                '--quiet',
                '--lang', 'php',
                '--no-git-ignore',
                '--exclude', '**/vendor/**',
                '--exclude', '**/node_modules/**',
                '--max-target-bytes', '33554432',
                '--timeout', str(max(1, int(timeout * 0.8))),
                str(test_path)
            ]

            # Narrow scan scope for large corpus to avoid timeouts while keeping coverage
            # Narrow scope only for known heavy rules when scanning the corpus
            try:
                heavy_rule_files = {
                    'callback-function-tracing.yaml',
                    'taint-analysis-framework.yaml'
                }
                corpus_dir = str((self.project_root / 'corpus').resolve())
                target_dir = str(Path(test_path).resolve())
                if target_dir.startswith(corpus_dir) and rule_file.name in heavy_rule_files:
                    cmd = [
                        'semgrep', '--config', str(rule_file), '--json', '--quiet',
                        '--lang', 'php',
                        '--no-git-ignore',
                        '--include', '**/*.php',
                        '--include', '**/includes/**/*.php',
                        '--include', '**/admin/**/*.php',
                        '--include', '**/classes/**/*.php',
                        '--include', '**/src/**/*.php',
                        '--exclude', '**/vendor/**', '--exclude', '**/node_modules/**',
                        '--max-target-bytes', '16777216',
                        '--timeout', str(max(1, int(timeout * 0.8))),
                        str(test_path)
                    ]
            except Exception:
                pass
            
            process = subprocess.run(
                cmd,
                capture_output=True,
                text=True,
                cwd=self.project_root,
                timeout=timeout,
                encoding='utf-8',
                errors='replace'
            )
            
            perf = stop_perf(perf_t)
            result['scan_time'] = perf.get('wall_time_seconds', 0)
            result['memory_usage'] = perf.get('process_rss_delta_bytes', 0)
            result['cpu_time'] = perf.get('process_cpu_time_seconds', 0.0)
            result['cpu_utilization'] = perf.get('cpu_utilization_estimate', 0.0)
            
            # Prefer successful JSON parsing over return code, unless fatal
            stdout_text = process.stdout or ""
            try:
                parsed = json.loads(stdout_text) if stdout_text.strip() else {}
            except json.JSONDecodeError as e:
                parsed = None
                result['error'] = f"Failed to parse Semgrep output: {e}"

            if parsed is not None and isinstance(parsed, dict):
                result['success'] = True
                result['findings_count'] = len(parsed.get('results', []))
                result['findings'] = parsed.get('results', [])
            else:
                # Treat non-fatal non-zero returns as failure with stderr info
                result['error'] = process.stderr or result.get('error') or "Semgrep scan failed"
        
        except subprocess.TimeoutExpired:
            result['error'] = f"Scan timed out after {timeout} seconds"
            result['timeout'] = True
        except Exception as e:
            result['error'] = str(e)
        
        return result
    
    def run_corpus_test(self, rule_file: Path, timeout: Optional[int] = None) -> Dict:
        """Run a rule against the corpus with proper error handling."""
        corpus_path = self.project_root / "corpus"
        if not corpus_path.exists():
            return {
                'rule_file': str(rule_file),
                'success': False,
                'error': 'Corpus not found',
                'findings_count': 0,
                'scan_time': 0
            }
        effective_timeout = timeout if timeout is not None else self.default_timeout
        return self.run_rule_test_with_timeout(rule_file, corpus_path, effective_timeout)
    
    def run_vulnerable_test(self, rule_file: Path, timeout: Optional[int] = None) -> Dict:
        """Run a rule against vulnerable examples."""
        metadata = self.extract_rule_metadata(rule_file)
        targeted = self.get_minimal_fixture(metadata.get('vuln_class', 'other'), is_safe=False)
        vulnerable_path = targeted if targeted else (self.project_root / "tests" / "vulnerable-examples")
        if not vulnerable_path.exists():
            return {
                'rule_file': str(rule_file),
                'success': False,
                'error': 'Vulnerable examples not found',
                'findings_count': 0,
                'scan_time': 0
            }
        effective_timeout = timeout if timeout is not None else self.default_timeout
        return self.run_rule_test_with_timeout(rule_file, vulnerable_path, effective_timeout)
    
    def run_safe_test(self, rule_file: Path, timeout: Optional[int] = None) -> Dict:
        """Run a rule against safe examples."""
        metadata = self.extract_rule_metadata(rule_file)
        targeted = self.get_minimal_fixture(metadata.get('vuln_class', 'other'), is_safe=True)
        safe_path = targeted if targeted else (self.project_root / "tests" / "safe-examples")
        if not safe_path.exists():
            return {
                'rule_file': str(rule_file),
                'success': False,
                'error': 'Safe examples not found',
                'findings_count': 0,
                'scan_time': 0
            }
        effective_timeout = timeout if timeout is not None else self.default_timeout
        return self.run_rule_test_with_timeout(rule_file, safe_path, effective_timeout)
    
    def calculate_precision_recall(self, rule_id: str, vuln_class: str, vulnerable_results: Dict, safe_results: Dict) -> Dict:
        """Calculate precision/recall using shared metrics and optional ground truth."""
        # Load ground truth once and cache on instance
        if not hasattr(self, '_ground_truth_index'):
            self._ground_truth_index = load_ground_truth(self.project_root)

        tp, fp, fn = compute_counts(
            self.project_root,
            vulnerable_results,
            safe_results,
            rule_id,
            vuln_class,
            ground_truth_index=self._ground_truth_index
        )
        metrics = calculate_metrics(tp, fp, fn)
        metrics.update({
            'true_positives': tp,
            'false_positives': fp,
            'false_negatives': fn
        })
        return metrics
    
    def run_comprehensive_test(self, rule_file: Path) -> Dict:
        """Run comprehensive tests for a single rule."""
        print(f"  Testing {rule_file.name}...")
        
        # Run tests against different targets
        corpus_result = self.run_corpus_test(rule_file, timeout=self.default_timeout)
        vulnerable_result = self.run_vulnerable_test(rule_file, timeout=self.default_timeout)
        safe_result = self.run_safe_test(rule_file, timeout=self.default_timeout)
        
        # Calculate metrics
        metrics = self.calculate_precision_recall(
            corpus_result.get('rule_id', ''),
            corpus_result.get('vuln_class', 'other'),
            vulnerable_result,
            safe_result
        )

        # False positive candidates
        if not hasattr(self, '_ground_truth_index'):
            self._ground_truth_index = load_ground_truth(self.project_root)
        if not hasattr(self, '_allowlist'):
            self._allowlist = load_allowlist(self.project_root)
        fp_info = collect_fp_candidates(
            self.project_root,
            safe_result,
            corpus_result,
            corpus_result.get('rule_id', ''),
            corpus_result.get('vuln_class', 'other'),
            self._ground_truth_index,
            self._allowlist
        )
        
        # Compile results
        result = {
            'rule_file': str(rule_file),
            'rule_id': corpus_result.get('rule_id', ''),
            'vuln_class': corpus_result.get('vuln_class', 'other'),
            'confidence': corpus_result.get('confidence', 'medium'),
            'corpus_test': corpus_result,
            'vulnerable_test': vulnerable_result,
            'safe_test': safe_result,
            'metrics': metrics,
            'false_positive_candidates': fp_info,
            'overall_success': (
                corpus_result.get('success', False) and
                vulnerable_result.get('success', False) and
                safe_result.get('success', False)
            ),
            'timestamp': datetime.now().isoformat()
        }
        
        # Print progress
        if result['overall_success']:
            print(f"    ✅ {rule_file.name}: {vulnerable_result.get('findings_count', 0)} findings, {metrics['precision']:.2f} precision")
        else:
            err_parts = []
            if corpus_result.get('error'):
                err_parts.append(f"corpus: {corpus_result['error']}")
            if vulnerable_result.get('error'):
                err_parts.append(f"vulnerable: {vulnerable_result['error']}")
            if safe_result.get('error'):
                err_parts.append(f"safe: {safe_result['error']}")
            err_msg = '; '.join(err_parts) if err_parts else 'Test failed'
            print(f"    ❌ {rule_file.name}: {err_msg}")
        
        return result
    
    def run_parallel_tests(self, max_workers: int = 4) -> List[Dict]:
        """Run tests in parallel with proper resource management."""
        rule_files = self.get_rule_files()
        
        if not rule_files:
            return []
        
        print(f"Running comprehensive tests on {len(rule_files)} rules with {max_workers} workers...")
        
        results = []
        
        with concurrent.futures.ThreadPoolExecutor(max_workers=max_workers) as executor:
            future_to_rule = {
                executor.submit(self.run_comprehensive_test, rule_file): rule_file
                for rule_file in rule_files
            }
            
            for future in concurrent.futures.as_completed(future_to_rule):
                try:
                    result = future.result()
                    results.append(result)
                    
                    # Record performance data
                    self.performance_data.append({
                        'rule_file': result['rule_file'],
                        'scan_time': result['corpus_test'].get('scan_time', 0),
                        'memory_usage': result['corpus_test'].get('memory_usage', 0),
                        'findings_count': result['corpus_test'].get('findings_count', 0)
                    })
                    
                except Exception as e:
                    rule_file = future_to_rule[future]
                    results.append({
                        'rule_file': str(rule_file),
                        'error': str(e),
                        'overall_success': False
                    })
        
        return results
    
    def generate_performance_summary(self) -> Dict:
        """Generate performance summary from collected data."""
        if not self.performance_data:
            return {}
        
        scan_times = [p['scan_time'] for p in self.performance_data if p['scan_time'] > 0]
        memory_usage = [p['memory_usage'] for p in self.performance_data if p['memory_usage'] > 0]
        findings_counts = [p['findings_count'] for p in self.performance_data]
        
        end_time = time.time()
        end_memory = psutil.virtual_memory().used
        
        return {
            'total_rules_tested': len(self.performance_data),
            'total_execution_time': end_time - self.start_time,
            'total_memory_usage': end_memory - self.start_memory,
            'avg_scan_time': statistics.mean(scan_times) if scan_times else 0,
            'min_scan_time': min(scan_times) if scan_times else 0,
            'max_scan_time': max(scan_times) if scan_times else 0,
            'avg_memory_per_scan': statistics.mean(memory_usage) if memory_usage else 0,
            'total_findings': sum(findings_counts),
            'avg_findings_per_rule': statistics.mean(findings_counts) if findings_counts else 0
        }
    
    def generate_quality_report(self, results: List[Dict]) -> Dict:
        """Generate quality assessment report."""
        successful_tests = [r for r in results if r.get('overall_success', False)]
        failed_tests = [r for r in results if not r.get('overall_success', False)]
        
        # Calculate quality metrics
        precision_scores = [r['metrics']['precision'] for r in successful_tests if 'metrics' in r]
        recall_scores = [r['metrics']['recall'] for r in successful_tests if 'metrics' in r]
        
        # Group by vulnerability class
        vuln_class_stats = {}
        for result in successful_tests:
            vuln_class = result.get('vuln_class', 'other')
            if vuln_class not in vuln_class_stats:
                vuln_class_stats[vuln_class] = {
                    'count': 0,
                    'avg_precision': 0,
                    'avg_recall': 0,
                    'total_findings': 0
                }
            
            vuln_class_stats[vuln_class]['count'] += 1
            if 'metrics' in result:
                vuln_class_stats[vuln_class]['avg_precision'] += result['metrics']['precision']
                vuln_class_stats[vuln_class]['avg_recall'] += result['metrics']['recall']
            vuln_class_stats[vuln_class]['total_findings'] += result['corpus_test'].get('findings_count', 0)
        
        # Calculate averages
        for vuln_class in vuln_class_stats:
            count = vuln_class_stats[vuln_class]['count']
            if count > 0:
                vuln_class_stats[vuln_class]['avg_precision'] /= count
                vuln_class_stats[vuln_class]['avg_recall'] /= count
        
        return {
            'total_rules': len(results),
            'successful_tests': len(successful_tests),
            'failed_tests': len(failed_tests),
            'success_rate': len(successful_tests) / len(results) if results else 0,
            'avg_precision': statistics.mean(precision_scores) if precision_scores else 0,
            'avg_recall': statistics.mean(recall_scores) if recall_scores else 0,
            'vuln_class_stats': vuln_class_stats,
            'performance_summary': self.generate_performance_summary()
        }
    
    def save_results(self, results: List[Dict], quality_report: Dict):
        """Save test results and reports."""
        timestamp = int(time.time())
        
        # Save detailed results
        results_file = self.results_dir / f"advanced-testing-results-{timestamp}.json"
        with open(results_file, 'w', encoding='utf-8') as f:
            json.dump({
                'timestamp': datetime.now().isoformat(),
                'results': results,
                'quality_report': quality_report
            }, f, indent=2)
        
        # Save quality report separately
        quality_file = self.results_dir / f"quality-report-{timestamp}.json"
        with open(quality_file, 'w', encoding='utf-8') as f:
            json.dump(quality_report, f, indent=2)
        
        print(f"\nResults saved:")
        print(f"  Detailed Results: {results_file}")
        print(f"  Quality Report: {quality_file}")
        
        return results_file, quality_file
    
    def print_summary(self, results: List[Dict], quality_report: Dict):
        """Print a comprehensive test summary."""
        print("\n" + "="*80)
        print("ADVANCED TESTING FRAMEWORK SUMMARY")
        print("="*80)
        
        print(f"\nTest Execution Summary:")
        print(f"  Total Rules Tested: {quality_report['total_rules']}")
        print(f"  Successful Tests: {quality_report['successful_tests']}")
        print(f"  Failed Tests: {quality_report['failed_tests']}")
        print(f"  Success Rate: {quality_report['success_rate']:.1%}")
        
        print(f"\nQuality Metrics:")
        print(f"  Average Precision: {quality_report['avg_precision']:.3f}")
        print(f"  Average Recall: {quality_report['avg_recall']:.3f}")
        
        print(f"\nPerformance Summary:")
        perf = quality_report.get('performance_summary', {})
        total_exec = perf.get('total_execution_time', 0.0)
        avg_scan = perf.get('avg_scan_time', 0.0)
        total_mem = perf.get('total_memory_usage', 0)
        total_findings = perf.get('total_findings', 0)
        print(f"  Total Execution Time: {total_exec:.2f} seconds")
        print(f"  Average Scan Time: {avg_scan:.2f} seconds")
        print(f"  Total Memory Usage: {total_mem / (1024*1024):.2f} MB")
        print(f"  Total Findings: {total_findings}")
        
        print(f"\nVulnerability Class Breakdown:")
        for vuln_class, stats in quality_report['vuln_class_stats'].items():
            print(f"  {vuln_class.upper()}:")
            print(f"    Rules: {stats['count']}")
            print(f"    Avg Precision: {stats['avg_precision']:.3f}")
            print(f"    Avg Recall: {stats['avg_recall']:.3f}")
            print(f"    Total Findings: {stats['total_findings']}")
        
        print("\n" + "="*80)
    
    def run_advanced_testing(self, max_workers: int = 4) -> Dict:
        """Run the complete advanced testing framework."""
        print("Starting Advanced Testing Framework...")
        print(f"Project Root: {self.project_root}")
        print(f"Max Workers: {max_workers}")
        print(f"Per-test timeout: {self.default_timeout}s")
        
        # Run parallel tests
        results = self.run_parallel_tests(max_workers)
        
        # Generate quality report
        quality_report = self.generate_quality_report(results)
        
        # Save results
        self.save_results(results, quality_report)
        
        # Print summary
        self.print_summary(results, quality_report)
        
        return {
            'success': True,
            'results': results,
            'quality_report': quality_report
        }

def main():
    parser = argparse.ArgumentParser(description='Run advanced testing framework for WordPress Semgrep Rules')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--workers', type=int, default=4, help='Number of parallel workers')
    parser.add_argument('--timeout', type=int, default=300, help='Timeout per test in seconds')
    
    args = parser.parse_args()
    
    # Initialize framework
    framework = AdvancedTestingFramework(args.project_root, default_timeout=args.timeout)
    
    # Run advanced testing
    result = framework.run_advanced_testing(max_workers=args.workers)
    
    # Exit with appropriate code
    if result['success'] and result['quality_report']['success_rate'] > 0.8:
        print(f"\n✅ Advanced testing completed successfully!")
        sys.exit(0)
    else:
        print(f"\n⚠️ Advanced testing completed with issues!")
        sys.exit(1)

if __name__ == '__main__':
    main()
