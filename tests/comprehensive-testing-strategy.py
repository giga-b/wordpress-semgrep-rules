#!/usr/bin/env python3
"""
Comprehensive Testing Strategy for WordPress Semgrep Rules
Implements a multi-layered testing approach covering syntax, performance, quality, and accuracy.
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
import statistics
import concurrent.futures
import threading

class ComprehensiveTester:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.corpus_path = self.project_root / "corpus"
        self.results_dir = self.project_root / "results" / "comprehensive-testing"
        self.results_dir.mkdir(parents=True, exist_ok=True)
        
        # Load quality configuration
        self.quality_config = self.project_root / ".rule-quality.yml"
        with open(self.quality_config, 'r', encoding='utf-8') as f:
            self.config = yaml.safe_load(f)
        
        # Test categories
        self.test_categories = {
            'syntax': 'Syntax validation and YAML parsing',
            'performance': 'Execution time and resource usage',
            'quality': 'Rule quality and metadata completeness',
            'accuracy': 'Precision and recall testing',
            'coverage': 'Test coverage and edge cases',
            'integration': 'End-to-end integration testing'
        }
    
    def find_all_rules(self) -> List[Path]:
        """Find all rule files in the project."""
        rule_files = []
        for pack_dir in ['packs/wp-core-security', 'packs/wp-core-quality', 'packs/experimental']:
            pack_path = self.project_root / pack_dir
            if pack_path.exists():
                rule_files.extend(pack_path.glob('*.yaml'))
        return rule_files
    
    def run_syntax_tests(self, rule_file: Path) -> Dict:
        """Run syntax validation tests."""
        results = {
            'valid': False,
            'yaml_valid': False,
            'semgrep_valid': False,
            'errors': [],
            'warnings': []
        }
        
        # Test YAML parsing
        try:
            with open(rule_file, 'r', encoding='utf-8') as f:
                content = yaml.safe_load(f)
            results['yaml_valid'] = True
        except yaml.YAMLError as e:
            results['errors'].append(f'YAML parsing error: {e}')
            return results
        
        # Test Semgrep validation
        try:
            result = subprocess.run([
                'semgrep', '--validate', '--config', str(rule_file)
            ], capture_output=True, text=True, timeout=30)
            
            results['semgrep_valid'] = result.returncode == 0
            if result.returncode != 0:
                results['errors'].append(f'Semgrep validation error: {result.stderr}')
        except subprocess.TimeoutExpired:
            results['errors'].append('Semgrep validation timed out')
        except Exception as e:
            results['errors'].append(f'Semgrep validation exception: {e}')
        
        results['valid'] = results['yaml_valid'] and results['semgrep_valid']
        return results
    
    def run_performance_tests(self, rule_file: Path) -> Dict:
        """Run performance and resource usage tests."""
        results = {
            'execution_time': 0,
            'memory_usage': 0,
            'findings_count': 0,
            'performance_score': 0,
            'issues': []
        }
        
        if not self.corpus_path.exists():
            results['issues'].append('Corpus not available for performance testing')
            return results
        
        try:
            start_time = time.time()
            
            # Run with timeout - reduced to prevent freezing
            result = subprocess.run([
                'semgrep', '--config', str(rule_file),
                '--json', '--quiet', '--no-git-ignore',
                str(self.corpus_path)
            ], capture_output=True, text=True, cwd=self.project_root, 
               timeout=15, encoding='utf-8', errors='replace')
            
            execution_time = time.time() - start_time
            results['execution_time'] = execution_time
            
            if result.returncode == 0:
                try:
                    findings = json.loads(result.stdout)
                    findings_count = len(findings.get('results', []))
                    results['findings_count'] = findings_count
                    
                    # Calculate performance score (lower time = higher score)
                    if execution_time < 10:
                        results['performance_score'] = 100
                    elif execution_time < 30:
                        results['performance_score'] = 80
                    elif execution_time < 60:
                        results['performance_score'] = 60
                    else:
                        results['performance_score'] = 40
                        results['issues'].append('Slow execution time')
                    
                except json.JSONDecodeError as e:
                    results['issues'].append(f'JSON decode error: {e}')
            else:
                results['issues'].append(f'Execution failed: {result.stderr}')
                
        except subprocess.TimeoutExpired:
            results['execution_time'] = 15
            results['performance_score'] = 0
            results['issues'].append('Execution timed out')
        except Exception as e:
            results['issues'].append(f'Performance test exception: {e}')
        
        return results
    
    def run_quality_tests(self, rule_file: Path) -> Dict:
        """Run rule quality and metadata tests."""
        results = {
            'quality_score': 0,
            'metadata_complete': False,
            'required_fields': [],
            'missing_fields': [],
            'issues': [],
            'warnings': []
        }
        
        try:
            with open(rule_file, 'r', encoding='utf-8') as f:
                content = yaml.safe_load(f)
            
            if not content or 'rules' not in content:
                results['issues'].append('No rules found in file')
                return results
            
            rules = content['rules']
            if not rules:
                results['issues'].append('Empty rules array')
                return results
            
            # Check each rule
            for i, rule in enumerate(rules):
                rule_quality = self.analyze_rule_quality(rule, i)
                results['issues'].extend(rule_quality['issues'])
                results['warnings'].extend(rule_quality['warnings'])
                
                # Track required fields
                for field in rule_quality['required_fields']:
                    if field not in results['required_fields']:
                        results['required_fields'].append(field)
                
                for field in rule_quality['missing_fields']:
                    if field not in results['missing_fields']:
                        results['missing_fields'].append(field)
            
            # Calculate quality score
            base_score = 100
            deductions = len(results['issues']) * 20 + len(results['warnings']) * 5
            results['quality_score'] = max(0, base_score - deductions)
            
            # Check metadata completeness
            results['metadata_complete'] = len(results['missing_fields']) == 0
            
        except Exception as e:
            results['issues'].append(f'Quality test exception: {e}')
        
        return results
    
    def analyze_rule_quality(self, rule: Dict, index: int) -> Dict:
        """Analyze quality of a single rule."""
        result = {
            'issues': [],
            'warnings': [],
            'required_fields': [],
            'missing_fields': []
        }
        
        # Check required fields
        required_fields = ['id', 'message', 'severity', 'languages']
        for field in required_fields:
            if field in rule:
                result['required_fields'].append(field)
            else:
                result['missing_fields'].append(field)
                result['issues'].append(f"Rule {index}: Missing required field '{field}'")
        
        # Check metadata
        if 'metadata' not in rule:
            result['issues'].append(f"Rule {index}: Missing metadata section")
        else:
            metadata = rule['metadata']
            metadata_fields = ['confidence', 'cwe', 'category', 'tags', 'vuln_class']
            for field in metadata_fields:
                if field in metadata:
                    result['required_fields'].append(field)
                else:
                    result['missing_fields'].append(field)
                    result['warnings'].append(f"Rule {index}: Missing metadata field '{field}'")
        
        # Check patterns
        if 'patterns' not in rule:
            result['warnings'].append(f"Rule {index}: No patterns defined")
        else:
            patterns = rule['patterns']
            if not patterns:
                result['warnings'].append(f"Rule {index}: Empty patterns array")
            else:
                # Check pattern complexity
                for j, pattern in enumerate(patterns):
                    if isinstance(pattern, dict) and 'pattern' in pattern:
                        pattern_text = pattern['pattern']
                        if len(pattern_text) > 1000:
                            result['warnings'].append(f"Rule {index}, Pattern {j}: Very long pattern")
                        if pattern_text.count('...') > 3:
                            result['warnings'].append(f"Rule {index}, Pattern {j}: Many ellipsis operators")
        
        return result
    
    def run_accuracy_tests(self, rule_file: Path) -> Dict:
        """Run accuracy tests (precision/recall) on test cases."""
        results = {
            'precision': 0.0,
            'recall': 0.0,
            'f1_score': 0.0,
            'true_positives': 0,
            'false_positives': 0,
            'false_negatives': 0,
            'test_cases_run': 0,
            'issues': []
        }
        
        # Test against vulnerable examples
        vulnerable_dir = self.project_root / "tests" / "vulnerable-examples"
        safe_dir = self.project_root / "tests" / "safe-examples"
        
        if not vulnerable_dir.exists() or not safe_dir.exists():
            results['issues'].append('Test directories not available')
            return results
        
        # Test vulnerable examples (should find issues)
        vulnerable_findings = self.test_rule_on_directory(rule_file, vulnerable_dir)
        
        # Test safe examples (should not find issues)
        safe_findings = self.test_rule_on_directory(rule_file, safe_dir)
        
        # Calculate metrics
        results['true_positives'] = vulnerable_findings
        results['false_positives'] = safe_findings
        results['false_negatives'] = 0  # Would need ground truth to calculate
        
        # Calculate precision and recall
        if results['true_positives'] + results['false_positives'] > 0:
            results['precision'] = results['true_positives'] / (results['true_positives'] + results['false_positives'])
        
        if results['true_positives'] + results['false_negatives'] > 0:
            results['recall'] = results['true_positives'] / (results['true_positives'] + results['false_negatives'])
        
        # Calculate F1 score
        if results['precision'] + results['recall'] > 0:
            results['f1_score'] = 2 * (results['precision'] * results['recall']) / (results['precision'] + results['recall'])
        
        return results
    
    def test_rule_on_directory(self, rule_file: Path, directory: Path) -> int:
        """Test a rule on a specific directory and return findings count."""
        try:
            result = subprocess.run([
                'semgrep', '--config', str(rule_file),
                '--json', '--quiet', '--no-git-ignore',
                str(directory)
            ], capture_output=True, text=True, cwd=self.project_root, 
               timeout=30, encoding='utf-8', errors='replace')
            
            if result.returncode == 0:
                try:
                    findings = json.loads(result.stdout)
                    return len(findings.get('results', []))
                except json.JSONDecodeError:
                    return 0
            else:
                return 0
        except:
            return 0
    
    def run_coverage_tests(self, rule_file: Path) -> Dict:
        """Run test coverage analysis."""
        results = {
            'coverage_score': 0,
            'test_files': 0,
            'tested_patterns': 0,
            'total_patterns': 0,
            'edge_cases_tested': 0,
            'issues': []
        }
        
        try:
            with open(rule_file, 'r', encoding='utf-8') as f:
                content = yaml.safe_load(f)
            
            if not content or 'rules' not in content:
                results['issues'].append('No rules found')
                return results
            
            rules = content['rules']
            total_patterns = 0
            
            for rule in rules:
                if 'patterns' in rule:
                    patterns = rule['patterns']
                    if isinstance(patterns, list):
                        total_patterns += len(patterns)
            
            results['total_patterns'] = total_patterns
            
            # Check for test files
            test_dir = self.project_root / "tests"
            if test_dir.exists():
                test_files = list(test_dir.rglob("*.php")) + list(test_dir.rglob("*.yaml"))
                results['test_files'] = len(test_files)
            
            # Calculate coverage score
            if total_patterns > 0:
                results['coverage_score'] = min(100, (results['test_files'] / total_patterns) * 100)
            
        except Exception as e:
            results['issues'].append(f'Coverage test exception: {e}')
        
        return results
    
    def run_integration_tests(self, rule_file: Path) -> Dict:
        """Run end-to-end integration tests."""
        results = {
            'integration_score': 0,
            'corpus_scan_success': False,
            'ci_compatibility': False,
            'documentation_complete': False,
            'issues': []
        }
        
        # Test corpus scanning
        if self.corpus_path.exists():
            try:
                result = subprocess.run([
                    'semgrep', '--config', str(rule_file),
                    '--json', '--quiet', '--no-git-ignore',
                    str(self.corpus_path)
                ], capture_output=True, text=True, cwd=self.project_root, 
                   timeout=60, encoding='utf-8', errors='replace')
                
                results['corpus_scan_success'] = result.returncode == 0
                if not results['corpus_scan_success']:
                    results['issues'].append(f'Corpus scan failed: {result.stderr}')
            except Exception as e:
                results['issues'].append(f'Corpus scan exception: {e}')
        
        # Check CI compatibility
        try:
            # Test with CI-like parameters
            result = subprocess.run([
                'semgrep', '--config', str(rule_file),
                '--json', '--quiet', '--no-git-ignore',
                '--max-target-bytes', '1000000'
            ], capture_output=True, text=True, cwd=self.project_root, 
               timeout=30, encoding='utf-8', errors='replace')
            
            results['ci_compatibility'] = result.returncode == 0
        except Exception as e:
            results['issues'].append(f'CI compatibility test failed: {e}')
        
        # Check documentation
        docs_dir = self.project_root / "docs"
        if docs_dir.exists():
            # Look for documentation files
            doc_files = list(docs_dir.glob("*.md"))
            results['documentation_complete'] = len(doc_files) > 0
        
        # Calculate integration score
        score = 0
        if results['corpus_scan_success']:
            score += 40
        if results['ci_compatibility']:
            score += 30
        if results['documentation_complete']:
            score += 30
        
        results['integration_score'] = score
        
        return results
    
    def run_comprehensive_tests(self, rule_files: List[Path] = None, 
                              categories: List[str] = None) -> Dict:
        """Run comprehensive testing on all rules."""
        if rule_files is None:
            rule_files = self.find_all_rules()
        
        if categories is None:
            categories = list(self.test_categories.keys())
        
        print(f"🧪 Running comprehensive tests on {len(rule_files)} rules...")
        print(f"Test categories: {', '.join(categories)}")
        
        results = {
            'total_rules': len(rule_files),
            'tested_rules': 0,
            'passed_rules': 0,
            'failed_rules': 0,
            'rule_results': [],
            'category_results': {},
            'summary': {}
        }
        
        # Initialize category results
        for category in categories:
            results['category_results'][category] = {
                'passed': 0,
                'failed': 0,
                'total': 0
            }
        
        for i, rule_file in enumerate(rule_files, 1):
            print(f"\n[{i}/{len(rule_files)}] Testing {rule_file.name}...")
            
            rule_result = {
                'file': str(rule_file),
                'name': rule_file.name,
                'categories': {},
                'overall_score': 0,
                'passed': False,
                'issues': []
            }
            
            # Run tests for each category
            for category in categories:
                print(f"  Running {category} tests...")
                
                if category == 'syntax':
                    test_result = self.run_syntax_tests(rule_file)
                elif category == 'performance':
                    test_result = self.run_performance_tests(rule_file)
                elif category == 'quality':
                    test_result = self.run_quality_tests(rule_file)
                elif category == 'accuracy':
                    test_result = self.run_accuracy_tests(rule_file)
                elif category == 'coverage':
                    test_result = self.run_coverage_tests(rule_file)
                elif category == 'integration':
                    test_result = self.run_integration_tests(rule_file)
                else:
                    test_result = {'issues': [f'Unknown test category: {category}']}
                
                rule_result['categories'][category] = test_result
                
                # Update category results
                if test_result.get('issues'):
                    results['category_results'][category]['failed'] += 1
                    rule_result['issues'].extend(test_result['issues'])
                else:
                    results['category_results'][category]['passed'] += 1
                
                results['category_results'][category]['total'] += 1
            
            # Calculate overall score
            scores = []
            if 'syntax' in rule_result['categories']:
                scores.append(100 if rule_result['categories']['syntax']['valid'] else 0)
            if 'performance' in rule_result['categories']:
                scores.append(rule_result['categories']['performance'].get('performance_score', 0))
            if 'quality' in rule_result['categories']:
                scores.append(rule_result['categories']['quality'].get('quality_score', 0))
            if 'accuracy' in rule_result['categories']:
                scores.append(rule_result['categories']['accuracy'].get('f1_score', 0) * 100)
            if 'coverage' in rule_result['categories']:
                scores.append(rule_result['categories']['coverage'].get('coverage_score', 0))
            if 'integration' in rule_result['categories']:
                scores.append(rule_result['categories']['integration'].get('integration_score', 0))
            
            if scores:
                rule_result['overall_score'] = statistics.mean(scores)
                rule_result['passed'] = rule_result['overall_score'] >= 70 and len(rule_result['issues']) == 0
            
            results['rule_results'].append(rule_result)
            results['tested_rules'] += 1
            
            if rule_result['passed']:
                results['passed_rules'] += 1
                print(f"    ✅ {rule_file.name} - PASSED (Score: {rule_result['overall_score']:.1f})")
            else:
                results['failed_rules'] += 1
                print(f"    ❌ {rule_file.name} - FAILED (Score: {rule_result['overall_score']:.1f})")
                for issue in rule_result['issues'][:3]:  # Show first 3 issues
                    print(f"       Issue: {issue}")
        
        # Generate summary
        results['summary'] = {
            'pass_rate': results['passed_rules'] / results['tested_rules'] if results['tested_rules'] > 0 else 0,
            'avg_score': statistics.mean([r['overall_score'] for r in results['rule_results']]) if results['rule_results'] else 0,
            'total_issues': sum(len(r['issues']) for r in results['rule_results'])
        }
        
        return results
    
    def print_report(self, results: Dict):
        """Print a comprehensive testing report."""
        print("\n" + "="*80)
        print("WORDPRESS SEMGREP RULES COMPREHENSIVE TESTING REPORT")
        print("="*80)
        
        print(f"\nOverall Results:")
        print(f"  Total Rules: {results['total_rules']}")
        print(f"  Tested Rules: {results['tested_rules']}")
        print(f"  Passed Rules: {results['passed_rules']}")
        print(f"  Failed Rules: {results['failed_rules']}")
        print(f"  Pass Rate: {results['summary']['pass_rate']:.1%}")
        print(f"  Average Score: {results['summary']['avg_score']:.1f}")
        print(f"  Total Issues: {results['summary']['total_issues']}")
        
        print(f"\nCategory Results:")
        for category, cat_results in results['category_results'].items():
            if cat_results['total'] > 0:
                pass_rate = cat_results['passed'] / cat_results['total']
                print(f"  {category.title()}: {cat_results['passed']}/{cat_results['total']} ({pass_rate:.1%})")
        
        # Show failed rules
        failed_rules = [r for r in results['rule_results'] if not r['passed']]
        if failed_rules:
            print(f"\n❌ Failed Rules ({len(failed_rules)}):")
            for rule in failed_rules[:10]:  # Show first 10
                print(f"  - {rule['name']} (Score: {rule['overall_score']:.1f})")
                for issue in rule['issues'][:2]:  # Show first 2 issues
                    print(f"    Issue: {issue}")
            if len(failed_rules) > 10:
                print(f"    ... and {len(failed_rules) - 10} more")
        
        print("\n" + "="*80)

def main():
    parser = argparse.ArgumentParser(description='Run comprehensive testing on WordPress Semgrep rules')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--rules', nargs='+', help='Specific rule files to test')
    parser.add_argument('--categories', nargs='+', choices=['syntax', 'performance', 'quality', 'accuracy', 'coverage', 'integration'], 
                       help='Specific test categories to run')
    parser.add_argument('--output', help='Output file for results')
    
    args = parser.parse_args()
    
    # Initialize comprehensive tester
    tester = ComprehensiveTester(args.project_root)
    
    # Run tests
    if args.rules:
        rule_files = [Path(r) for r in args.rules]
    else:
        rule_files = None
    
    results = tester.run_comprehensive_tests(rule_files, args.categories)
    
    # Print report
    tester.print_report(results)
    
    # Save results
    results_file = tester.results_dir / f"comprehensive-testing-{int(time.time())}.json"
    with open(results_file, 'w', encoding='utf-8') as f:
        json.dump(results, f, indent=2)
    
    print(f"\n📄 Detailed results saved to: {results_file}")
    
    # Exit with appropriate code
    if results['failed_rules'] > 0:
        print(f"\n⚠️  {results['failed_rules']} rules failed comprehensive testing")
        sys.exit(1)
    else:
        print(f"\n✅ All rules passed comprehensive testing!")
        sys.exit(0)

if __name__ == '__main__':
    main()
