#!/usr/bin/env python3
"""
Quick Rule Debug Script
Lightweight debugging that focuses on syntax and structure validation without full corpus scanning.
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

class QuickRuleDebugger:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.results_dir = self.project_root / "results" / "quick-debug"
        self.results_dir.mkdir(parents=True, exist_ok=True)
    
    def find_all_rules(self) -> List[Path]:
        """Find all rule files in the project."""
        rule_files = []
        for pack_dir in ['packs/wp-core-security', 'packs/wp-core-quality', 'packs/experimental']:
            pack_path = self.project_root / pack_dir
            if pack_path.exists():
                rule_files.extend(pack_path.glob('*.yaml'))
        return rule_files
    
    def validate_rule_syntax(self, rule_file: Path) -> Dict:
        """Validate rule syntax using semgrep with short timeout."""
        try:
            result = subprocess.run([
                'semgrep', '--validate', '--config', str(rule_file)
            ], capture_output=True, text=True, timeout=10)  # Short timeout
            
            return {
                'valid': result.returncode == 0,
                'error': result.stderr if result.returncode != 0 else None,
                'output': result.stdout
            }
        except subprocess.TimeoutExpired:
            return {
                'valid': False,
                'error': 'Rule validation timed out (10s)',
                'output': ''
            }
        except Exception as e:
            return {
                'valid': False,
                'error': str(e),
                'output': ''
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
                'warnings': [],
                'complexity_score': 0
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
                rule_analysis = self.analyze_single_rule(rule, i)
                analysis['issues'].extend(rule_analysis['issues'])
                analysis['warnings'].extend(rule_analysis['warnings'])
                analysis['complexity_score'] += rule_analysis['complexity_score']
            
            return analysis
            
        except yaml.YAMLError as e:
            return {
                'valid_yaml': False,
                'has_rules': False,
                'rules_count': 0,
                'issues': [f'YAML parsing error: {e}'],
                'warnings': [],
                'complexity_score': 0
            }
        except Exception as e:
            return {
                'valid_yaml': False,
                'has_rules': False,
                'rules_count': 0,
                'issues': [f'File parsing error: {e}'],
                'warnings': [],
                'complexity_score': 0
            }
    
    def analyze_single_rule(self, rule: Dict, index: int) -> Dict:
        """Analyze a single rule for potential issues."""
        issues = []
        warnings = []
        complexity_score = 0
        
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
                            complexity_score += len(pattern_text) / 100  # Base complexity
                            
                            if len(pattern_text) > 1000:
                                warnings.append(f"Rule {index}, Pattern {j}: Very long pattern ({len(pattern_text)} chars)")
                                complexity_score += 10
                            
                            ellipsis_count = pattern_text.count('...')
                            if ellipsis_count > 3:
                                warnings.append(f"Rule {index}, Pattern {j}: Many ellipsis operators ({ellipsis_count})")
                                complexity_score += ellipsis_count * 2
                            
                            # Check for potential performance issues
                            if pattern_text.count('.*') > 5:
                                warnings.append(f"Rule {index}, Pattern {j}: Many wildcard operators")
                                complexity_score += 5
        
        # Check metadata
        if 'metadata' not in rule:
            issues.append(f"Rule {index}: Missing metadata section")
        else:
            metadata = rule['metadata']
            if 'confidence' not in metadata:
                warnings.append(f"Rule {index}: Missing confidence in metadata")
            if 'cwe' not in metadata:
                warnings.append(f"Rule {index}: Missing CWE in metadata")
        
        return {
            'issues': issues, 
            'warnings': warnings, 
            'complexity_score': complexity_score
        }
    
    def suggest_fixes(self, rule_file: Path, analysis: Dict) -> List[str]:
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
        
        if "Many wildcard operators" in str(analysis['warnings']):
            fixes.append("Consider using more specific patterns instead of wildcards")
        
        # Complexity issues
        if analysis['complexity_score'] > 50:
            fixes.append("Rule is very complex - consider simplifying patterns or breaking into multiple rules")
        
        return fixes
    
    def debug_rules(self, rule_files: List[Path] = None) -> Dict:
        """Debug all rules and identify issues."""
        if rule_files is None:
            rule_files = self.find_all_rules()
        
        print(f"🔍 Quick debugging {len(rule_files)} rules...")
        
        results = {
            'total_rules': len(rule_files),
            'valid_rules': 0,
            'invalid_rules': 0,
            'high_complexity_rules': 0,
            'rule_details': [],
            'summary': {}
        }
        
        for i, rule_file in enumerate(rule_files, 1):
            print(f"[{i}/{len(rule_files)}] Debugging {rule_file.name}...")
            
            # Validate syntax
            syntax_result = self.validate_rule_syntax(rule_file)
            
            # Analyze structure
            structure_result = self.analyze_rule_structure(rule_file)
            
            # Determine overall status
            is_valid = syntax_result['valid'] and structure_result['valid_yaml'] and len(structure_result['issues']) == 0
            is_high_complexity = structure_result['complexity_score'] > 50
            
            if is_valid:
                results['valid_rules'] += 1
            else:
                results['invalid_rules'] += 1
            
            if is_high_complexity:
                results['high_complexity_rules'] += 1
            
            # Suggest fixes
            fixes = self.suggest_fixes(rule_file, structure_result)
            
            rule_detail = {
                'file': str(rule_file),
                'name': rule_file.name,
                'valid': is_valid,
                'high_complexity': is_high_complexity,
                'syntax_valid': syntax_result['valid'],
                'structure_valid': structure_result['valid_yaml'],
                'complexity_score': structure_result['complexity_score'],
                'rules_count': structure_result['rules_count'],
                'issues': structure_result['issues'],
                'warnings': structure_result['warnings'],
                'syntax_error': syntax_result.get('error'),
                'suggested_fixes': fixes
            }
            
            results['rule_details'].append(rule_detail)
            
            # Print status
            if is_valid and not is_high_complexity:
                print(f"    ✅ {rule_file.name} - VALID")
            elif is_valid and is_high_complexity:
                print(f"    ⚠️  {rule_file.name} - VALID but HIGH COMPLEXITY")
            else:
                print(f"    ❌ {rule_file.name} - INVALID")
                for issue in structure_result['issues'][:2]:  # Show first 2 issues
                    print(f"       Issue: {issue}")
        
        # Generate summary
        results['summary'] = {
            'valid_rate': results['valid_rules'] / results['total_rules'] if results['total_rules'] > 0 else 0,
            'high_complexity_rate': results['high_complexity_rules'] / results['total_rules'] if results['total_rules'] > 0 else 0,
            'avg_complexity': sum(r['complexity_score'] for r in results['rule_details']) / len(results['rule_details']) if results['rule_details'] else 0
        }
        
        return results
    
    def print_report(self, results: Dict):
        """Print a formatted debugging report."""
        print("\n" + "="*80)
        print("WORDPRESS SEMGREP RULES QUICK DEBUGGING REPORT")
        print("="*80)
        
        print(f"\nOverall Results:")
        print(f"  Total Rules: {results['total_rules']}")
        print(f"  Valid Rules: {results['valid_rules']}")
        print(f"  Invalid Rules: {results['invalid_rules']}")
        print(f"  High Complexity Rules: {results['high_complexity_rules']}")
        print(f"  Valid Rate: {results['summary']['valid_rate']:.1%}")
        print(f"  High Complexity Rate: {results['summary']['high_complexity_rate']:.1%}")
        print(f"  Average Complexity Score: {results['summary']['avg_complexity']:.1f}")
        
        # Show invalid rules
        invalid_rules = [r for r in results['rule_details'] if not r['valid']]
        if invalid_rules:
            print(f"\n❌ Invalid Rules ({len(invalid_rules)}):")
            for rule in invalid_rules[:10]:  # Show first 10
                print(f"  - {rule['name']}")
                if rule['syntax_error']:
                    print(f"    Syntax Error: {rule['syntax_error']}")
                for issue in rule['issues'][:2]:  # Show first 2 issues
                    print(f"    Issue: {issue}")
                if rule['suggested_fixes']:
                    print(f"    Suggested fixes:")
                    for fix in rule['suggested_fixes'][:3]:  # Show first 3 fixes
                        print(f"      • {fix}")
            if len(invalid_rules) > 10:
                print(f"    ... and {len(invalid_rules) - 10} more")
        
        # Show high complexity rules
        high_complexity_rules = [r for r in results['rule_details'] if r['high_complexity']]
        if high_complexity_rules:
            print(f"\n⚠️  High Complexity Rules ({len(high_complexity_rules)}):")
            for rule in high_complexity_rules[:10]:  # Show first 10
                print(f"  - {rule['name']} (Complexity: {rule['complexity_score']:.1f})")
                if rule['suggested_fixes']:
                    print(f"    Suggested: {rule['suggested_fixes'][0] if rule['suggested_fixes'] else 'None'}")
            if len(high_complexity_rules) > 10:
                print(f"    ... and {len(high_complexity_rules) - 10} more")
        
        print("\n" + "="*80)

def main():
    parser = argparse.ArgumentParser(description='Quick debug WordPress Semgrep rules')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--rules', nargs='+', help='Specific rule files to debug')
    parser.add_argument('--output', help='Output file for results')
    
    args = parser.parse_args()
    
    # Initialize quick debugger
    debugger = QuickRuleDebugger(args.project_root)
    
    # Debug rules
    if args.rules:
        rule_files = [Path(r) for r in args.rules]
    else:
        rule_files = None
    
    results = debugger.debug_rules(rule_files)
    
    # Print report
    debugger.print_report(results)
    
    # Save results
    results_file = debugger.results_dir / f"quick-debug-{int(time.time())}.json"
    with open(results_file, 'w', encoding='utf-8') as f:
        json.dump(results, f, indent=2)
    
    print(f"\n📄 Detailed results saved to: {results_file}")
    
    # Exit with appropriate code
    if results['invalid_rules'] > 0:
        print(f"\n⚠️  Found {results['invalid_rules']} invalid rules")
        sys.exit(1)
    else:
        print(f"\n✅ All rules are valid!")
        sys.exit(0)

if __name__ == '__main__':
    main()
