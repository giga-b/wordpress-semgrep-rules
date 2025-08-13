#!/usr/bin/env python3
"""
Basic Quality Check Script for WordPress Semgrep Rules
Validates metadata, structure, and basic quality requirements.
"""

import json
import yaml
import sys
import os
from pathlib import Path
from typing import Dict, List
import argparse
import time

class BasicQualityChecker:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.quality_config = self.project_root / ".rule-quality.yml"
        self.results_dir = self.project_root / "results" / "quality-checks"
        self.results_dir.mkdir(parents=True, exist_ok=True)
        
        # Load quality configuration
        with open(self.quality_config, 'r', encoding='utf-8') as f:
            self.config = yaml.safe_load(f)
    
    def get_target(self, vuln_class: str, key: str) -> float:
        """Get target value for a vulnerability class, fallback to global."""
        try:
            return self.config['class_targets'][vuln_class][key]
        except KeyError:
            return self.config['global_targets'][key]
    
    def validate_rule_structure(self, rule_file: str) -> Dict:
        """Validate basic rule structure and metadata."""
        try:
            with open(rule_file, 'r', encoding='utf-8') as f:
                content = yaml.safe_load(f)
            
            if not content or 'rules' not in content:
                return {
                    'valid': False,
                    'error': 'No rules found in file'
                }
            
            rules = content['rules']
            if not rules:
                return {
                    'valid': False,
                    'error': 'Empty rules array'
                }
            
            validation_results = []
            for i, rule in enumerate(rules):
                rule_result = self.validate_single_rule(rule, i)
                validation_results.append(rule_result)
            
            return {
                'valid': all(r['valid'] for r in validation_results),
                'rules': validation_results,
                'total_rules': len(rules)
            }
            
        except Exception as e:
            return {
                'valid': False,
                'error': f'File parsing error: {e}'
            }
    
    def validate_single_rule(self, rule: Dict, index: int) -> Dict:
        """Validate a single rule."""
        result = {
            'index': index,
            'valid': True,
            'errors': [],
            'warnings': []
        }
        
        # Check required fields
        required_fields = ['id', 'message', 'severity', 'languages']
        for field in required_fields:
            if field not in rule:
                result['valid'] = False
                result['errors'].append(f"Missing required field: {field}")
        
        # Check metadata
        if 'metadata' not in rule:
            result['valid'] = False
            result['errors'].append("Missing metadata section")
        else:
            metadata = rule['metadata']
            metadata_result = self.validate_metadata(metadata)
            result['errors'].extend(metadata_result['errors'])
            result['warnings'].extend(metadata_result['warnings'])
            if not metadata_result['valid']:
                result['valid'] = False
        
        # Check patterns
        if 'patterns' not in rule:
            result['warnings'].append("No patterns defined")
        else:
            patterns = rule['patterns']
            if not patterns:
                result['warnings'].append("Empty patterns array")
        
        return result
    
    def validate_metadata(self, metadata: Dict) -> Dict:
        """Validate metadata section."""
        result = {
            'valid': True,
            'errors': [],
            'warnings': []
        }
        
        # Check required metadata fields
        required_metadata = ['confidence', 'cwe', 'category', 'tags', 'vuln_class']
        for field in required_metadata:
            if field not in metadata:
                result['valid'] = False
                result['errors'].append(f"Missing required metadata field: {field}")
        
        # Validate confidence
        if 'confidence' in metadata:
            confidence = metadata['confidence']
            if confidence not in ['low', 'medium', 'high']:
                result['valid'] = False
                result['errors'].append(f"Invalid confidence: {confidence}")
            elif confidence != 'high':
                result['warnings'].append(f"Confidence is {confidence}, 'high' recommended")
        
        # Validate vuln_class
        if 'vuln_class' in metadata:
            vuln_class = metadata['vuln_class']
            valid_classes = ['xss', 'sqli', 'csrf', 'authz', 'file_upload', 
                           'deserialization', 'secrets_storage', 'rest_ajax', 'other']
            if vuln_class not in valid_classes:
                result['valid'] = False
                result['errors'].append(f"Invalid vuln_class: {vuln_class}")
        
        # Validate CWE format
        if 'cwe' in metadata:
            cwe = metadata['cwe']
            if not cwe.startswith('CWE-'):
                result['warnings'].append(f"CWE should start with 'CWE-': {cwe}")
        
        return result
    
    def run_quality_checks(self, rule_files: List[str] = None) -> Dict:
        """Run quality checks on all rules or specified rules."""
        if rule_files is None:
            # Find all rule files
            rule_files = []
            for pack_dir in ['packs/wp-core-security', 'packs/wp-core-quality', 'packs/experimental']:
                pack_path = self.project_root / pack_dir
                if pack_path.exists():
                    rule_files.extend(pack_path.glob('*.yaml'))
        
        results = {
            'total_files': len(rule_files),
            'valid_files': 0,
            'invalid_files': 0,
            'total_rules': 0,
            'valid_rules': 0,
            'invalid_rules': 0,
            'file_results': [],
            'summary': {}
        }
        
        print(f"Running basic quality checks on {len(rule_files)} files...")
        
        for i, rule_file in enumerate(rule_files, 1):
            rule_path = Path(rule_file) if isinstance(rule_file, str) else rule_file
            print(f"[{i}/{len(rule_files)}] Checking {rule_path.name}...")
            
            # Validate rule structure
            validation_result = self.validate_rule_structure(str(rule_path))
            
            file_result = {
                'file': str(rule_path),
                'valid': validation_result['valid'],
                'total_rules': validation_result.get('total_rules', 0),
                'valid_rules': 0,
                'invalid_rules': 0,
                'errors': [],
                'warnings': []
            }
            
            if validation_result['valid']:
                results['valid_files'] += 1
                file_result['valid_rules'] = validation_result['total_rules']
                results['valid_rules'] += validation_result['total_rules']
                
                # Collect errors and warnings from individual rules
                for rule_result in validation_result.get('rules', []):
                    if not rule_result['valid']:
                        file_result['invalid_rules'] += 1
                        results['invalid_rules'] += 1
                    file_result['errors'].extend(rule_result['errors'])
                    file_result['warnings'].extend(rule_result['warnings'])
                
                print(f"    ✅ {rule_path.name} - VALID ({validation_result['total_rules']} rules)")
            else:
                results['invalid_files'] += 1
                file_result['errors'].append(validation_result.get('error', 'Unknown error'))
                print(f"    ❌ {rule_path.name} - INVALID")
                print(f"       Error: {validation_result.get('error', 'Unknown error')}")
            
            results['total_rules'] += validation_result.get('total_rules', 0)
            results['file_results'].append(file_result)
        
        # Generate summary
        results['summary'] = self.generate_summary(results)
        
        # Save results
        results_file = self.results_dir / f"basic-quality-check-{int(time.time())}.json"
        with open(results_file, 'w', encoding='utf-8') as f:
            json.dump(results, f, indent=2)
        
        return results
    
    def generate_summary(self, results: Dict) -> Dict:
        """Generate summary statistics."""
        if results['total_files'] == 0:
            return {}
        
        return {
            'file_success_rate': results['valid_files'] / results['total_files'],
            'rule_success_rate': results['valid_rules'] / results['total_rules'] if results['total_rules'] > 0 else 0,
            'avg_rules_per_file': results['total_rules'] / results['total_files'] if results['total_files'] > 0 else 0
        }
    
    def print_report(self, results: Dict):
        """Print a formatted quality report."""
        print("\n" + "="*80)
        print("WORDPRESS SEMGREP RULES BASIC QUALITY CHECK REPORT")
        print("="*80)
        
        print(f"\nOverall Results:")
        print(f"  Total Files: {results['total_files']}")
        print(f"  Valid Files: {results['valid_files']}")
        print(f"  Invalid Files: {results['invalid_files']}")
        print(f"  File Success Rate: {results['summary'].get('file_success_rate', 0):.1%}")
        
        print(f"\nRule Results:")
        print(f"  Total Rules: {results['total_rules']}")
        print(f"  Valid Rules: {results['valid_rules']}")
        print(f"  Invalid Rules: {results['invalid_rules']}")
        print(f"  Rule Success Rate: {results['summary'].get('rule_success_rate', 0):.1%}")
        
        if results['summary']:
            print(f"\nStatistics:")
            print(f"  Average Rules per File: {results['summary']['avg_rules_per_file']:.1f}")
        
        print(f"\nDetailed Results:")
        for file_result in results['file_results']:
            status = "✅ VALID" if file_result['valid'] else "❌ INVALID"
            print(f"  {status} {Path(file_result['file']).name}")
            if file_result['errors']:
                for error in file_result['errors']:
                    print(f"    Error: {error}")
            if file_result['warnings']:
                for warning in file_result['warnings']:
                    print(f"    Warning: {warning}")
        
        print("\n" + "="*80)

def main():
    parser = argparse.ArgumentParser(description='Run basic quality checks on WordPress Semgrep rules')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--rules', nargs='+', help='Specific rule files to check')
    parser.add_argument('--output', help='Output file for results')
    
    args = parser.parse_args()
    
    # Initialize quality checker
    checker = BasicQualityChecker(args.project_root)
    
    # Run quality checks
    results = checker.run_quality_checks(args.rules)
    
    # Print report
    checker.print_report(results)
    
    # Exit with appropriate code
    if results['invalid_files'] > 0 or results['invalid_rules'] > 0:
        print(f"\n❌ Quality checks failed for {results['invalid_files']} files and {results['invalid_rules']} rules")
        sys.exit(1)
    else:
        print(f"\n✅ All quality checks passed!")
        sys.exit(0)

if __name__ == '__main__':
    main()
