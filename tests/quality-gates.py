#!/usr/bin/env python3
"""
Quality Gates Testing Script for WordPress Semgrep Rules
Enforces benchmark requirements and quality standards for all rules.
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

class QualityGates:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.quality_config = self.project_root / ".rule-quality.yml"
        self.results_dir = self.project_root / "results" / "quality-gates"
        self.results_dir.mkdir(parents=True, exist_ok=True)
        
        # Load quality configuration
        with open(self.quality_config, 'r') as f:
            self.config = yaml.safe_load(f)
    
    def get_target(self, vuln_class: str, key: str) -> float:
        """Get target value for a vulnerability class, fallback to global."""
        try:
            return self.config['class_targets'][vuln_class][key]
        except KeyError:
            return self.config['global_targets'][key]
    
    def calculate_metrics(self, tp: int, fp: int, fn: int) -> Dict[str, float]:
        """Calculate precision, recall, FPR, FNR from counts."""
        precision = tp / (tp + fp) if (tp + fp) > 0 else 1.0
        recall = tp / (tp + fn) if (tp + fn) > 0 else 0.0
        fpr = fp / (tp + fp) if (tp + fp) > 0 else 0.0
        fnr = fn / (tp + fn) if (tp + fn) > 0 else 0.0
        
        return {
            'precision': precision,
            'recall': recall,
            'fpr': fpr,
            'fnr': fnr
        }
    
    def run_semgrep_tests(self, rule_file: str) -> Dict[str, any]:
        """Run semgrep tests on a rule file and return results."""
        try:
            result = subprocess.run(
                ['semgrep', '--test', rule_file],
                capture_output=True,
                text=True,
                cwd=self.project_root
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
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'test_results': {'passed': 0, 'failed': 0, 'total': 0}
            }
    
    def run_corpus_scan(self, rule_file: str) -> Dict[str, any]:
        """Run rule against corpus and return findings."""
        try:
            corpus_path = self.project_root / "corpus"
            result = subprocess.run([
                'semgrep', '--config', rule_file,
                '--json', '--quiet',
                str(corpus_path)
            ], capture_output=True, text=True, cwd=self.project_root)
            
            if result.returncode == 0:
                findings = json.loads(result.stdout)
                return {
                    'success': True,
                    'findings_count': len(findings.get('results', [])),
                    'findings': findings.get('results', [])
                }
            else:
                return {
                    'success': False,
                    'error': result.stderr,
                    'findings_count': 0
                }
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'findings_count': 0
            }
    
    def extract_rule_metadata(self, rule_file: str) -> Dict[str, any]:
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
    
    def evaluate_rule_quality(self, rule_file: str, test_results: Dict, 
                            corpus_results: Dict, metadata: Dict) -> Dict[str, any]:
        """Evaluate rule quality against benchmarks."""
        vuln_class = metadata.get('vuln_class', 'other')
        
        # Get targets for this vulnerability class
        precision_target = self.get_target(vuln_class, 'precision_min')
        recall_target = self.get_target(vuln_class, 'recall_min')
        fpr_target = self.get_target(vuln_class, 'fp_rate_max')
        fnr_target = self.get_target(vuln_class, 'fn_rate_max')
        
        # For now, use test results to estimate metrics
        # In a real implementation, you'd have actual TP/FP/FN counts
        test_data = test_results.get('test_results', {'total': 0, 'passed': 0, 'failed': 0})
        total_tests = test_data['total']
        passed_tests = test_data['passed']
        
        # Estimate metrics (this is a simplified approach)
        # In practice, you'd need manual review
        estimated_tp = passed_tests
        estimated_fp = 0  # Would need manual review
        estimated_fn = test_data['failed']
        
        metrics = self.calculate_metrics(estimated_tp, estimated_fp, estimated_fn)
        
        # Check against targets
        quality_checks = {
            'precision_ok': metrics['precision'] >= precision_target,
            'recall_ok': metrics['recall'] >= recall_target,
            'fpr_ok': metrics['fpr'] <= fpr_target,
            'fnr_ok': metrics['fnr'] <= fnr_target,
            'confidence_ok': metadata.get('confidence', 'medium') == 'high',
            'tests_ok': total_tests >= self.config['testing_requirements']['positive_tests_min'] + 
                       self.config['testing_requirements']['negative_tests_min'],
            'corpus_findings_ok': corpus_results.get('findings_count', 0) >= 
                                self.config['promotion_criteria']['experimental_to_core']['min_corpus_findings']
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
            'corpus_results': corpus_results,
            'metadata': metadata
        }
    
    def run_quality_gates(self, rule_files: List[str] = None) -> Dict[str, any]:
        """Run quality gates on all rules or specified rules."""
        if rule_files is None:
            # Find all rule files
            rule_files = []
            for pack_dir in ['packs/wp-core-security', 'packs/wp-core-quality', 'packs/experimental']:
                pack_path = self.project_root / pack_dir
                if pack_path.exists():
                    rule_files.extend(pack_path.glob('*.yaml'))
        
        results = {
            'total_rules': len(rule_files),
            'passed_rules': 0,
            'failed_rules': 0,
            'rule_results': [],
            'summary': {}
        }
        
        for rule_file in rule_files:
            print(f"Evaluating {rule_file}...")
            
            # Extract metadata
            metadata = self.extract_rule_metadata(rule_file)
            
            # Run tests
            test_results = self.run_semgrep_tests(rule_file)
            
            # Run corpus scan
            corpus_results = self.run_corpus_scan(rule_file)
            
            # Evaluate quality
            quality_result = self.evaluate_rule_quality(
                rule_file, test_results, corpus_results, metadata
            )
            
            results['rule_results'].append(quality_result)
            
            if quality_result['overall_pass']:
                results['passed_rules'] += 1
                print(f"✅ {metadata.get('id', 'Unknown')} - PASSED")
            else:
                results['failed_rules'] += 1
                print(f"❌ {metadata.get('id', 'Unknown')} - FAILED")
        
        # Generate summary
        results['summary'] = self.generate_summary(results)
        
        # Save results
        results_file = self.results_dir / f"quality-gates-{Path(__file__).stem}.json"
        with open(results_file, 'w') as f:
            json.dump(results, f, indent=2)
        
        return results
    
    def generate_summary(self, results: Dict) -> Dict:
        """Generate summary statistics."""
        if not results['rule_results']:
            return {}
        
        # Collect metrics
        precisions = [r['metrics']['precision'] for r in results['rule_results']]
        recalls = [r['metrics']['recall'] for r in results['rule_results']]
        fprs = [r['metrics']['fpr'] for r in results['rule_results']]
        fnrs = [r['metrics']['fnr'] for r in results['rule_results']]
        
        return {
            'avg_precision': statistics.mean(precisions),
            'avg_recall': statistics.mean(recalls),
            'avg_fpr': statistics.mean(fprs),
            'avg_fnr': statistics.mean(fnrs),
            'min_precision': min(precisions),
            'min_recall': min(recalls),
            'max_fpr': max(fprs),
            'max_fnr': max(fnrs),
            'pass_rate': results['passed_rules'] / results['total_rules']
        }
    
    def print_report(self, results: Dict):
        """Print a formatted quality report."""
        print("\n" + "="*80)
        print("WORDPRESS SEMGREP RULES QUALITY GATES REPORT")
        print("="*80)
        
        print(f"\nOverall Results:")
        print(f"  Total Rules: {results['total_rules']}")
        print(f"  Passed: {results['passed_rules']}")
        print(f"  Failed: {results['failed_rules']}")
        print(f"  Pass Rate: {results['summary'].get('pass_rate', 0):.1%}")
        
        if results['summary']:
            print(f"\nAverage Metrics:")
            print(f"  Precision: {results['summary']['avg_precision']:.3f}")
            print(f"  Recall: {results['summary']['avg_recall']:.3f}")
            print(f"  False Positive Rate: {results['summary']['avg_fpr']:.3f}")
            print(f"  False Negative Rate: {results['summary']['avg_fnr']:.3f}")
        
        print(f"\nDetailed Results:")
        for result in results['rule_results']:
            status = "✅ PASS" if result['overall_pass'] else "❌ FAIL"
            print(f"  {status} {result['rule_id']} ({result['vuln_class']})")
            if not result['overall_pass']:
                failed_checks = [k for k, v in result['quality_checks'].items() if not v]
                print(f"    Failed checks: {', '.join(failed_checks)}")
        
        print("\n" + "="*80)

def main():
    parser = argparse.ArgumentParser(description='Run quality gates on WordPress Semgrep rules')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--rules', nargs='+', help='Specific rule files to test')
    parser.add_argument('--output', help='Output file for results')
    
    args = parser.parse_args()
    
    # Initialize quality gates
    gates = QualityGates(args.project_root)
    
    # Run quality gates
    results = gates.run_quality_gates(args.rules)
    
    # Print report
    gates.print_report(results)
    
    # Exit with appropriate code
    if results['failed_rules'] > 0:
        print(f"\n❌ Quality gates failed for {results['failed_rules']} rules")
        sys.exit(1)
    else:
        print(f"\n✅ All quality gates passed!")
        sys.exit(0)

if __name__ == '__main__':
    main()
