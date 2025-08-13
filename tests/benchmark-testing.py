#!/usr/bin/env python3
"""
Benchmark Testing Script for WordPress Semgrep Rules
Measures precision, recall, false positive rate, and other quality metrics.
"""

import json
import yaml
import subprocess
import sys
import os
from pathlib import Path
from typing import Dict, List, Tuple, Optional
import argparse
import statistics
import time
from datetime import datetime

class BenchmarkTester:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.quality_config = self.project_root / ".rule-quality.yml"
        self.results_dir = self.project_root / "results" / "benchmarks"
        self.results_dir.mkdir(parents=True, exist_ok=True)
        
        # Load quality configuration
        with open(self.quality_config, 'r') as f:
            self.config = yaml.safe_load(f)
        
        # Test corpus paths
        self.safe_corpus = self.project_root / "tests" / "safe-examples"
        self.vulnerable_corpus = self.project_root / "tests" / "vulnerable-examples"
        self.wordpress_corpus = self.project_root / "corpus"

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
            from metrics import load_ground_truth, compute_counts, calculate_metrics
            from fp_detection import load_allowlist, collect_fp_candidates
            from perf import start_perf, stop_perf

        self._load_ground_truth = load_ground_truth
        self._compute_counts = compute_counts
        self._calculate_metrics = calculate_metrics
        self._ground_truth_index = self._load_ground_truth(self.project_root)
        self._allowlist = load_allowlist(self.project_root)
    
    def get_target(self, vuln_class: str, key: str) -> float:
        """Get target value for a vulnerability class, fallback to global."""
        try:
            return self.config['class_targets'][vuln_class][key]
        except KeyError:
            return self.config['global_targets'][key]
    
    def run_rule_on_corpus(self, rule_file: str, corpus_path: Path) -> Dict:
        """Run a rule against a corpus and return findings."""
        try:
            # Perf measurement
            perf_t = start_perf()

            result = subprocess.run([
                'semgrep', '--config', rule_file,
                '--json', '--quiet', '--no-git-ignore',
                str(corpus_path)
            ], capture_output=True, text=True, cwd=self.project_root, timeout=300)
            
            if result.returncode == 0:
                findings = json.loads(result.stdout)
                perf = stop_perf(perf_t)
                return {
                    'success': True,
                    'findings_count': len(findings.get('results', [])),
                    'findings': findings.get('results', []),
                    'scan_time': findings.get('time', {}).get('total', perf.get('wall_time_seconds', 0)),
                    'memory_usage': perf.get('process_rss_delta_bytes', 0),
                    'cpu_time': perf.get('process_cpu_time_seconds', 0.0),
                    'cpu_utilization': perf.get('cpu_utilization_estimate', 0.0)
                }
            else:
                perf = stop_perf(perf_t)
                return {
                    'success': False,
                    'error': result.stderr,
                    'findings_count': 0,
                    'findings': [],
                    'scan_time': perf.get('wall_time_seconds', 0),
                    'memory_usage': perf.get('process_rss_delta_bytes', 0),
                    'cpu_time': perf.get('process_cpu_time_seconds', 0.0),
                    'cpu_utilization': perf.get('cpu_utilization_estimate', 0.0)
                }
        except subprocess.TimeoutExpired:
            return {
                'success': False,
                'error': 'Scan timed out after 5 minutes',
                'findings_count': 0,
                'findings': [],
                'scan_time': 300,
                'memory_usage': 0,
                'cpu_time': 0.0,
                'cpu_utilization': 0.0
            }
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'findings_count': 0,
                'findings': [],
                'scan_time': 0
            }
    
    def extract_rule_metadata(self, rule_file: str) -> Dict:
        """Extract metadata from a rule file."""
        try:
            with open(rule_file, 'r') as f:
                content = yaml.safe_load(f)
            
            rule = content.get('rules', [{}])[0]
            metadata = rule.get('metadata', {})
            
            return {
                'id': rule.get('id', ''),
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
                'id': '',
                'error': str(e),
                'vuln_class': 'other',
                'confidence': 'medium'
            }
    
    def run_semgrep_tests(self, rule_file: str) -> Dict:
        """Run semgrep tests on a rule file."""
        try:
            result = subprocess.run(
                ['semgrep', '--test', rule_file],
                capture_output=True,
                text=True,
                cwd=self.project_root,
                timeout=60
            )
            
            # Parse test results
            lines = result.stdout.split('\n')
            test_results = {
                'passed': 0,
                'failed': 0,
                'total': 0
            }
            
            for line in lines:
                if 'test passed' in line.lower():
                    test_results['passed'] += 1
                    test_results['total'] += 1
                elif 'test failed' in line.lower():
                    test_results['failed'] += 1
                    test_results['total'] += 1
            
            return {
                'success': result.returncode == 0,
                'test_results': test_results,
                'stdout': result.stdout,
                'stderr': result.stderr
            }
        except subprocess.TimeoutExpired:
            return {
                'success': False,
                'error': 'Tests timed out after 1 minute',
                'test_results': {'passed': 0, 'failed': 0, 'total': 0}
            }
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'test_results': {'passed': 0, 'failed': 0, 'total': 0}
            }
    
    def calculate_metrics(self, tp: int, fp: int, fn: int) -> Dict[str, float]:
        """Delegate to shared metrics utility for consistency."""
        return self._calculate_metrics(tp, fp, fn)
    
    def estimate_metrics_from_corpus(self, rule_file: str, metadata: Dict) -> Dict:
        """Estimate metrics by running rule on safe and vulnerable corpora."""
        print(f"  Running on safe corpus...")
        safe_results = self.run_rule_on_corpus(rule_file, self.safe_corpus)
        
        print(f"  Running on vulnerable corpus...")
        vulnerable_results = self.run_rule_on_corpus(rule_file, self.vulnerable_corpus)
        
        print(f"  Running on WordPress corpus...")
        wp_results = self.run_rule_on_corpus(rule_file, self.wordpress_corpus)
        
        # Compute counts using optional ground truth if available
        rule_id = metadata.get('id')
        vuln_class = metadata.get('vuln_class', 'other')
        estimated_tp, estimated_fp, estimated_fn = self._compute_counts(
            self.project_root,
            vulnerable_results,
            safe_results,
            rule_id,
            vuln_class,
            ground_truth_index=self._ground_truth_index
        )

        metrics = self.calculate_metrics(estimated_tp, estimated_fp, estimated_fn)

        # False positive candidates (for visibility during benchmarks)
        fp_info = collect_fp_candidates(
            self.project_root,
            safe_results,
            wp_results,
            metadata.get('id', ''),
            metadata.get('vuln_class', 'other'),
            self._ground_truth_index,
            self._allowlist
        )
        
        return {
            'metrics': metrics,
            'corpus_results': {
                'safe': safe_results,
                'vulnerable': vulnerable_results,
                'wordpress': wp_results
            },
            'estimated_counts': {
                'tp': estimated_tp,
                'fp': estimated_fp,
                'fn': estimated_fn
            },
            'false_positive_candidates': fp_info
        }
    
    def evaluate_rule_against_targets(self, rule_file: str, metadata: Dict, 
                                    metrics: Dict, test_results: Dict) -> Dict:
        """Evaluate rule performance against quality targets."""
        vuln_class = metadata.get('vuln_class', 'other')
        
        # Get targets for this vulnerability class
        precision_target = self.get_target(vuln_class, 'precision_min')
        recall_target = self.get_target(vuln_class, 'recall_min')
        fpr_target = self.get_target(vuln_class, 'fp_rate_max')
        fnr_target = self.get_target(vuln_class, 'fn_rate_max')
        
        # Check against targets
        quality_checks = {
            'precision_ok': metrics['precision'] >= precision_target,
            'recall_ok': metrics['recall'] >= recall_target,
            'fpr_ok': metrics['fpr'] <= fpr_target,
            'fnr_ok': metrics['fnr'] <= fnr_target,
            'confidence_ok': metadata.get('confidence', 'medium') == 'high',
            'tests_ok': test_results.get('total', 0) >= 
                       self.config['testing_requirements']['positive_tests_min'] + 
                       self.config['testing_requirements']['negative_tests_min'],
            'performance_ok': True  # Would need performance benchmarks
        }
        
        overall_pass = all(quality_checks.values())
        
        return {
            'rule_id': metadata.get('id', ''),
            'vuln_class': vuln_class,
            'metrics': metrics,
            'targets': {
                'precision_min': precision_target,
                'recall_min': recall_target,
                'fp_rate_max': fpr_target,
                'fn_rate_max': fnr_target
            },
            'quality_checks': quality_checks,
            'overall_pass': overall_pass,
            'test_results': test_results,
            'metadata': metadata
        }
    
    def run_benchmarks(self, rule_files: List[str] = None) -> Dict:
        """Run benchmarks on all rules or specified rules."""
        if rule_files is None:
            # Find all rule files
            rule_files = []
            for pack_dir in ['packs/wp-core-security', 'packs/wp-core-quality', 'packs/experimental']:
                pack_path = self.project_root / pack_dir
                if pack_path.exists():
                    rule_files.extend(pack_path.glob('*.yaml'))
        
        results = {
            'timestamp': datetime.now().isoformat(),
            'total_rules': len(rule_files),
            'passed_rules': 0,
            'failed_rules': 0,
            'rule_results': [],
            'summary': {},
            'performance': {}
        }
        
        start_time = time.time()
        
        for i, rule_file in enumerate(rule_files, 1):
            print(f"\n[{i}/{len(rule_files)}] Benchmarking {rule_file.name}...")
            
            # Extract metadata
            metadata = self.extract_rule_metadata(rule_file)
            
            # Run tests
            print(f"  Running tests...")
            test_results = self.run_semgrep_tests(rule_file)
            
            # Estimate metrics from corpus
            metrics_data = self.estimate_metrics_from_corpus(rule_file, metadata)
            
            # Evaluate against targets
            evaluation = self.evaluate_rule_against_targets(
                rule_file, metadata, metrics_data['metrics'], test_results['test_results']
            )
            
            # Combine results
            rule_result = {
                'file': str(rule_file),
                'metadata': metadata,
                'test_results': test_results,
                'metrics_data': metrics_data,
                'evaluation': evaluation
            }
            
            results['rule_results'].append(rule_result)
            
            if evaluation['overall_pass']:
                results['passed_rules'] += 1
                print(f"✅ {metadata.get('id', 'Unknown')} - PASSED")
            else:
                results['failed_rules'] += 1
                print(f"❌ {metadata.get('id', 'Unknown')} - FAILED")
                failed_checks = [k for k, v in evaluation['quality_checks'].items() if not v]
                print(f"    Failed checks: {', '.join(failed_checks)}")
        
        # Calculate performance metrics
        results['performance']['total_time'] = time.time() - start_time
        results['performance']['avg_time_per_rule'] = results['performance']['total_time'] / len(rule_files) if rule_files else 0
        
        # Generate summary
        results['summary'] = self.generate_summary(results)
        
        # Save results
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        results_file = self.results_dir / f"benchmark-results-{timestamp}.json"
        with open(results_file, 'w') as f:
            json.dump(results, f, indent=2)
        
        print(f"\nResults saved to: {results_file}")
        
        return results
    
    def generate_summary(self, results: Dict) -> Dict:
        """Generate summary statistics."""
        if not results['rule_results']:
            return {}
        
        # Collect metrics
        precisions = [r['evaluation']['metrics']['precision'] for r in results['rule_results']]
        recalls = [r['evaluation']['metrics']['recall'] for r in results['rule_results']]
        fprs = [r['evaluation']['metrics']['fpr'] for r in results['rule_results']]
        fnrs = [r['evaluation']['metrics']['fnr'] for r in results['rule_results']]
        f1_scores = [r['evaluation']['metrics']['f1_score'] for r in results['rule_results']]
        
        # Group by vulnerability class
        vuln_class_stats = {}
        for result in results['rule_results']:
            vuln_class = result['evaluation']['vuln_class']
            if vuln_class not in vuln_class_stats:
                vuln_class_stats[vuln_class] = {
                    'count': 0,
                    'passed': 0,
                    'precisions': [],
                    'recalls': [],
                    'fprs': [],
                    'fnrs': []
                }
            
            vuln_class_stats[vuln_class]['count'] += 1
            if result['evaluation']['overall_pass']:
                vuln_class_stats[vuln_class]['passed'] += 1
            
            vuln_class_stats[vuln_class]['precisions'].append(result['evaluation']['metrics']['precision'])
            vuln_class_stats[vuln_class]['recalls'].append(result['evaluation']['metrics']['recall'])
            vuln_class_stats[vuln_class]['fprs'].append(result['evaluation']['metrics']['fpr'])
            vuln_class_stats[vuln_class]['fnrs'].append(result['evaluation']['metrics']['fnr'])
        
        # Calculate averages for each class
        for vuln_class, stats in vuln_class_stats.items():
            stats['avg_precision'] = statistics.mean(stats['precisions'])
            stats['avg_recall'] = statistics.mean(stats['recalls'])
            stats['avg_fpr'] = statistics.mean(stats['fprs'])
            stats['avg_fnr'] = statistics.mean(stats['fnrs'])
            stats['pass_rate'] = stats['passed'] / stats['count']
        
        return {
            'overall': {
                'avg_precision': statistics.mean(precisions),
                'avg_recall': statistics.mean(recalls),
                'avg_fpr': statistics.mean(fprs),
                'avg_fnr': statistics.mean(fnrs),
                'avg_f1_score': statistics.mean(f1_scores),
                'min_precision': min(precisions),
                'min_recall': min(recalls),
                'max_fpr': max(fprs),
                'max_fnr': max(fnrs),
                'pass_rate': results['passed_rules'] / results['total_rules']
            },
            'by_vuln_class': vuln_class_stats
        }
    
    def print_report(self, results: Dict):
        """Print a formatted benchmark report."""
        print("\n" + "="*80)
        print("WORDPRESS SEMGREP RULES BENCHMARK REPORT")
        print("="*80)
        
        print(f"\nOverall Results:")
        print(f"  Total Rules: {results['total_rules']}")
        print(f"  Passed: {results['passed_rules']}")
        print(f"  Failed: {results['failed_rules']}")
        print(f"  Pass Rate: {results['summary']['overall']['pass_rate']:.1%}")
        print(f"  Total Time: {results['performance']['total_time']:.2f}s")
        print(f"  Avg Time per Rule: {results['performance']['avg_time_per_rule']:.2f}s")
        
        print(f"\nAverage Metrics:")
        summary = results['summary']['overall']
        print(f"  Precision: {summary['avg_precision']:.3f}")
        print(f"  Recall: {summary['avg_recall']:.3f}")
        print(f"  False Positive Rate: {summary['avg_fpr']:.3f}")
        print(f"  False Negative Rate: {summary['avg_fnr']:.3f}")
        print(f"  F1 Score: {summary['avg_f1_score']:.3f}")
        
        print(f"\nBy Vulnerability Class:")
        for vuln_class, stats in results['summary']['by_vuln_class'].items():
            print(f"  {vuln_class.upper()}:")
            print(f"    Count: {stats['count']}")
            print(f"    Pass Rate: {stats['pass_rate']:.1%}")
            print(f"    Avg Precision: {stats['avg_precision']:.3f}")
            print(f"    Avg Recall: {stats['avg_recall']:.3f}")
        
        print(f"\nDetailed Results:")
        for result in results['rule_results']:
            status = "✅ PASS" if result['evaluation']['overall_pass'] else "❌ FAIL"
            rule_id = result['metadata'].get('id', 'Unknown')
            vuln_class = result['evaluation']['vuln_class']
            metrics = result['evaluation']['metrics']
            
            print(f"  {status} {rule_id} ({vuln_class})")
            print(f"    Precision: {metrics['precision']:.3f}, Recall: {metrics['recall']:.3f}")
            
            if not result['evaluation']['overall_pass']:
                failed_checks = [k for k, v in result['evaluation']['quality_checks'].items() if not v]
                print(f"    Failed checks: {', '.join(failed_checks)}")
        
        print("\n" + "="*80)

def main():
    parser = argparse.ArgumentParser(description='Run benchmarks on WordPress Semgrep rules')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--rules', nargs='+', help='Specific rule files to benchmark')
    parser.add_argument('--output', help='Output file for results')
    
    args = parser.parse_args()
    
    # Initialize benchmark tester
    tester = BenchmarkTester(args.project_root)
    
    # Run benchmarks
    results = tester.run_benchmarks(args.rules)
    
    # Print report
    tester.print_report(results)
    
    # Exit with appropriate code
    if results['failed_rules'] > 0:
        print(f"\n❌ Benchmarks failed for {results['failed_rules']} rules")
        sys.exit(1)
    else:
        print(f"\n✅ All rules passed benchmarks!")
        sys.exit(0)

if __name__ == '__main__':
    main()
