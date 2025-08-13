#!/usr/bin/env python3
"""
Debug Failing Rules Script
Identifies and fixes rules that are failing to scan properly in the corpus.
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

class RuleDebugger:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.corpus_path = self.project_root / "corpus"
        self.results_dir = self.project_root / "results" / "rule-debugging"
        self.results_dir.mkdir(parents=True, exist_ok=True)
        
        # Load quality configuration
        self.quality_config = self.project_root / ".rule-quality.yml"
        with open(self.quality_config, 'r', encoding='utf-8') as f:
            self.config = yaml.safe_load(f)
    
    def find_all_rules(self) -> List[Path]:
        """Find all rule files in the project."""
        rule_files = []
        for pack_dir in ['packs/wp-core-security', 'packs/wp-core-quality', 'packs/experimental']:
            pack_path = self.project_root / pack_dir
            if pack_path.exists():
                rule_files.extend(pack_path.glob('*.yaml'))
        return rule_files
    
    def validate_rule_syntax(self, rule_file: Path) -> Dict:
        """Validate rule syntax using semgrep."""
        try:
            result = subprocess.run([
                'semgrep', '--validate', '--config', str(rule_file)
            ], capture_output=True, text=True, timeout=30)
            
            return {
                'valid': result.returncode == 0,
                'error': result.stderr if result.returncode != 0 else None,
                'output': result.stdout
            }
        except subprocess.TimeoutExpired:
            return {
                'valid': False,
                'error': 'Rule validation timed out',
                'output': ''
            }
        except Exception as e:
            return {
                'valid': False,
                'error': str(e),
                'output': ''
            }
    
    def test_rule_on_corpus(self, rule_file: Path, timeout: int = 30) -> Dict:
        """Test a rule on the corpus with detailed error reporting."""
        try:
            if not self.corpus_path.exists():
                return {
                    'success': False,
                    'error': 'Corpus not available',
                    'findings_count': 0,
                    'execution_time': 0
                }
            
            start_time = time.time()
            
            result = subprocess.run([
                'semgrep', '--config', str(rule_file),
                '--json', '--quiet', '--no-git-ignore',
                str(self.corpus_path)
            ], capture_output=True, text=True, cwd=self.project_root, 
               timeout=timeout, encoding='utf-8', errors='replace')
            
            execution_time = time.time() - start_time
            
            if result.returncode == 0:
                try:
                    findings = json.loads(result.stdout)
                    findings_count = len(findings.get('results', []))
                    return {
                        'success': True,
                        'findings_count': findings_count,
                        'execution_time': execution_time,
                        'findings': findings.get('results', [])
                    }
                except json.JSONDecodeError as e:
                    return {
                        'success': False,
                        'error': f'JSON decode error: {e}',
                        'findings_count': 0,
                        'execution_time': execution_time
                    }
            else:
                return {
                    'success': False,
                    'error': result.stderr,
                    'findings_count': 0,
                    'execution_time': execution_time
                }
                
        except subprocess.TimeoutExpired:
            return {
                'success': False,
                'error': f'Corpus scan timed out after {timeout} seconds',
                'findings_count': 0,
                'execution_time': timeout
            }
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'findings_count': 0,
                'execution_time': 0
            }
    
    def analyze_rule_structure(self, rule_file: Path) -> Dict:
        """Analyze rule structure and identify potential issues."""
        try:
            with open(rule_file, 'r', encoding='utf-8') as f:
                content = yaml.safe_load(f)
            
            analysis = {
                'valid_yaml': True,
                'has_rules': False,
                'rules_count': 0,
                'issues': [],
                'warnings': []
            }
            
            if not content:
                analysis['valid_yaml'] = False
                analysis['issues'].append('Empty file')
                return analysis
            
            if 'rules' not in content:
                analysis['issues'].append('No rules section found')
                return analysis
            
            rules = content['rules']
            if not rules:
                analysis['issues'].append('Empty rules array')
                return analysis
            
            analysis['has_rules'] = True
            analysis['rules_count'] = len(rules)
            
            # Analyze each rule
            for i, rule in enumerate(rules):
                rule_issues = self.analyze_single_rule(rule, i)
                analysis['issues'].extend(rule_issues['issues'])
                analysis['warnings'].extend(rule_issues['warnings'])
            
            return analysis
            
        except yaml.YAMLError as e:
            return {
                'valid_yaml': False,
                'has_rules': False,
                'rules_count': 0,
                'issues': [f'YAML parsing error: {e}'],
                'warnings': []
            }
        except Exception as e:
            return {
                'valid_yaml': False,
                'has_rules': False,
                'rules_count': 0,
                'issues': [f'File parsing error: {e}'],
                'warnings': []
            }
    
    def analyze_single_rule(self, rule: Dict, index: int) -> Dict:
        """Analyze a single rule for potential issues."""
        issues = []
        warnings = []
        
        # Check required fields
        required_fields = ['id', 'message', 'severity', 'languages']
        for field in required_fields:
            if field not in rule:
                issues.append(f"Rule {index}: Missing required field '{field}'")
        
        # Check patterns
        if 'patterns' not in rule:
            warnings.append(f"Rule {index}: No patterns defined")
        else:
            patterns = rule['patterns']
            if not patterns:
                warnings.append(f"Rule {index}: Empty patterns array")
            else:
                # Check for complex patterns that might cause issues
                for j, pattern in enumerate(patterns):
                    if isinstance(pattern, dict):
                        if 'pattern' in pattern:
                            pattern_text = pattern['pattern']
                            if len(pattern_text) > 1000:
                                warnings.append(f"Rule {index}, Pattern {j}: Very long pattern ({len(pattern_text)} chars)")
                            if pattern_text.count('...') > 3:
                                warnings.append(f"Rule {index}, Pattern {j}: Many ellipsis operators (potential performance issue)")
        
        # Check metadata
        if 'metadata' not in rule:
            issues.append(f"Rule {index}: Missing metadata section")
        else:
            metadata = rule['metadata']
            if 'confidence' not in metadata:
                warnings.append(f"Rule {index}: Missing confidence in metadata")
            if 'cwe' not in metadata:
                warnings.append(f"Rule {index}: Missing CWE in metadata")
        
        return {'issues': issues, 'warnings': warnings}
    
    def suggest_fixes(self, rule_file: Path, analysis: Dict, test_result: Dict) -> List[str]:
        """Suggest fixes for identified issues."""
        fixes = []
        
        # YAML syntax issues
        if not analysis['valid_yaml']:
            fixes.append("Fix YAML syntax errors in the rule file")
        
        # Missing required fields
        for issue in analysis['issues']:
            if "Missing required field" in issue:
                field = issue.split("'")[1]
                fixes.append(f"Add required field '{field}' to the rule")
        
        # Pattern issues
        if "No patterns defined" in str(analysis['warnings']):
            fixes.append("Add patterns to the rule for proper detection")
        
        # Performance issues
        if "Very long pattern" in str(analysis['warnings']):
            fixes.append("Consider breaking down long patterns into smaller, more specific patterns")
        
        if "Many ellipsis operators" in str(analysis['warnings']):
            fixes.append("Reduce the number of ellipsis operators to improve performance")
        
        # Test result issues
        if not test_result['success']:
            if 'timeout' in test_result['error'].lower():
                fixes.append("Rule is too complex - consider simplifying patterns or reducing scope")
            elif 'json decode' in test_result['error'].lower():
                fixes.append("Rule produces invalid output - check pattern syntax")
            else:
                fixes.append(f"Fix rule execution error: {test_result['error']}")
        
        return fixes
    
    def debug_rules(self, rule_files: List[Path] = None) -> Dict:
        """Debug all rules and identify issues."""
        if rule_files is None:
            rule_files = self.find_all_rules()
        
        print(f"🔍 Debugging {len(rule_files)} rules...")
        
        results = {
            'total_rules': len(rule_files),
            'valid_rules': 0,
            'invalid_rules': 0,
            'failing_rules': 0,
            'rule_details': [],
            'summary': {}
        }
        
        for i, rule_file in enumerate(rule_files, 1):
            print(f"\n[{i}/{len(rule_files)}] Debugging {rule_file.name}...")
            
            # Validate syntax
            syntax_result = self.validate_rule_syntax(rule_file)
            
            # Analyze structure
            structure_result = self.analyze_rule_structure(rule_file)
            
            # Test on corpus
            test_result = self.test_rule_on_corpus(rule_file)
            
            # Determine overall status
            is_valid = syntax_result['valid'] and structure_result['valid_yaml'] and len(structure_result['issues']) == 0
            is_failing = not test_result['success']
            
            if is_valid:
                results['valid_rules'] += 1
            else:
                results['invalid_rules'] += 1
            
            if is_failing:
                results['failing_rules'] += 1
            
            # Suggest fixes
            fixes = self.suggest_fixes(rule_file, structure_result, test_result)
            
            rule_detail = {
                'file': str(rule_file),
                'name': rule_file.name,
                'valid': is_valid,
                'failing': is_failing,
                'syntax_valid': syntax_result['valid'],
                'structure_valid': structure_result['valid_yaml'],
                'test_success': test_result['success'],
                'findings_count': test_result.get('findings_count', 0),
                'execution_time': test_result.get('execution_time', 0),
                'issues': structure_result['issues'],
                'warnings': structure_result['warnings'],
                'test_error': test_result.get('error'),
                'suggested_fixes': fixes
            }
            
            results['rule_details'].append(rule_detail)
            
            # Print status
            if is_valid and not is_failing:
                print(f"    ✅ {rule_file.name} - VALID")
            elif is_valid and is_failing:
                print(f"    ⚠️  {rule_file.name} - VALID but FAILING")
                print(f"       Error: {test_result.get('error', 'Unknown error')}")
            else:
                print(f"    ❌ {rule_file.name} - INVALID")
                for issue in structure_result['issues']:
                    print(f"       Issue: {issue}")
        
        # Generate summary
        results['summary'] = {
            'valid_rate': results['valid_rules'] / results['total_rules'] if results['total_rules'] > 0 else 0,
            'failing_rate': results['failing_rules'] / results['total_rules'] if results['total_rules'] > 0 else 0,
            'avg_execution_time': statistics.mean([r['execution_time'] for r in results['rule_details'] if r['execution_time'] > 0]) if results['rule_details'] else 0,
            'total_findings': sum(r['findings_count'] for r in results['rule_details'])
        }
        
        return results
    
    def print_report(self, results: Dict):
        """Print a formatted debugging report."""
        print("\n" + "="*80)
        print("WORDPRESS SEMGREP RULES DEBUGGING REPORT")
        print("="*80)
        
        print(f"\nOverall Results:")
        print(f"  Total Rules: {results['total_rules']}")
        print(f"  Valid Rules: {results['valid_rules']}")
        print(f"  Invalid Rules: {results['invalid_rules']}")
        print(f"  Failing Rules: {results['failing_rules']}")
        print(f"  Valid Rate: {results['summary']['valid_rate']:.1%}")
        print(f"  Failing Rate: {results['summary']['failing_rate']:.1%}")
        
        if results['summary']['avg_execution_time'] > 0:
            print(f"  Average Execution Time: {results['summary']['avg_execution_time']:.2f}s")
        print(f"  Total Findings: {results['summary']['total_findings']}")
        
        # Show failing rules
        failing_rules = [r for r in results['rule_details'] if r['failing']]
        if failing_rules:
            print(f"\n❌ Failing Rules ({len(failing_rules)}):")
            for rule in failing_rules:
                print(f"  - {rule['name']}")
                if rule['test_error']:
                    print(f"    Error: {rule['test_error']}")
                if rule['suggested_fixes']:
                    print(f"    Suggested fixes:")
                    for fix in rule['suggested_fixes']:
                        print(f"      • {fix}")
        
        # Show invalid rules
        invalid_rules = [r for r in results['rule_details'] if not r['valid']]
        if invalid_rules:
            print(f"\n⚠️  Invalid Rules ({len(invalid_rules)}):")
            for rule in invalid_rules:
                print(f"  - {rule['name']}")
                for issue in rule['issues']:
                    print(f"    Issue: {issue}")
        
        print("\n" + "="*80)

def main():
    parser = argparse.ArgumentParser(description='Debug failing WordPress Semgrep rules')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--rules', nargs='+', help='Specific rule files to debug')
    parser.add_argument('--output', help='Output file for results')
    
    args = parser.parse_args()
    
    # Initialize rule debugger
    debugger = RuleDebugger(args.project_root)
    
    # Debug rules
    if args.rules:
        rule_files = [Path(r) for r in args.rules]
    else:
        rule_files = None
    
    results = debugger.debug_rules(rule_files)
    
    # Print report
    debugger.print_report(results)
    
    # Save results
    results_file = debugger.results_dir / f"rule-debugging-{int(time.time())}.json"
    with open(results_file, 'w', encoding='utf-8') as f:
        json.dump(results, f, indent=2)
    
    print(f"\n📄 Detailed results saved to: {results_file}")
    
    # Exit with appropriate code
    if results['failing_rules'] > 0 or results['invalid_rules'] > 0:
        print(f"\n⚠️  Found {results['failing_rules']} failing and {results['invalid_rules']} invalid rules")
        sys.exit(1)
    else:
        print(f"\n✅ All rules are valid and working!")
        sys.exit(0)

if __name__ == '__main__':
    main()
