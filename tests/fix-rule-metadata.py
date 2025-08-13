#!/usr/bin/env python3
"""
Rule Metadata Fix Script
Automatically adds missing metadata fields to rule files.
"""

import yaml
import json
import sys
from pathlib import Path
from typing import Dict, List, Set
import argparse
import re

class RuleMetadataFixer:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.required_metadata = {
            'confidence': 'high',
            'cwe': 'CWE-200',  # Default CWE
            'category': 'security',
            'tags': [],
            'vuln_class': 'other'
        }
        
        self.valid_confidences = {'low', 'medium', 'high'}
        self.valid_vuln_classes = {
            'xss', 'sqli', 'csrf', 'authz', 'file_upload', 
            'deserialization', 'secrets_storage', 'rest_ajax', 'other'
        }
        
        # Mapping of rule patterns to vulnerability classes
        self.vuln_class_patterns = {
            'xss': [
                r'xss', r'html', r'echo', r'print', r'output', r'display',
                r'esc_html', r'esc_attr', r'esc_url', r'esc_js'
            ],
            'sqli': [
                r'sql', r'query', r'wpdb', r'prepare', r'get_results',
                r'get_var', r'get_row', r'get_col'
            ],
            'csrf': [
                r'nonce', r'csrf', r'wp_verify_nonce', r'wp_create_nonce',
                r'check_ajax_referer', r'wp_nonce_field'
            ],
            'authz': [
                r'current_user_can', r'user_can', r'capability', r'permission',
                r'is_admin', r'is_super_admin', r'check_admin_referer'
            ],
            'file_upload': [
                r'upload', r'file', r'move_uploaded_file', r'path', r'directory',
                r'basename', r'dirname', r'realpath'
            ],
            'deserialization': [
                r'unserialize', r'eval', r'create_function', r'call_user_func',
                r'call_user_func_array', r'exec', r'shell_exec'
            ],
            'secrets_storage': [
                r'add_option', r'update_option', r'get_option', r'delete_option',
                r'password', r'secret', r'key', r'token'
            ],
            'rest_ajax': [
                r'wp_ajax', r'register_rest_route', r'wp_rest', r'ajax',
                r'admin_ajax', r'wp_rest_api'
            ]
        }
        
        # CWE mapping based on vulnerability class
        self.cwe_mapping = {
            'xss': 'CWE-79',
            'sqli': 'CWE-89',
            'csrf': 'CWE-352',
            'authz': 'CWE-285',
            'file_upload': 'CWE-434',
            'deserialization': 'CWE-502',
            'secrets_storage': 'CWE-532',
            'rest_ajax': 'CWE-285',
            'other': 'CWE-200'
        }
        
        self.results = {
            'total_rules': 0,
            'fixed_rules': 0,
            'skipped_rules': 0,
            'errors': []
        }
    
    def detect_vuln_class(self, rule: Dict) -> str:
        """Detect vulnerability class based on rule content."""
        # Check rule ID and message first
        rule_text = f"{rule.get('id', '')} {rule.get('message', '')}".lower()
        
        for vuln_class, patterns in self.vuln_class_patterns.items():
            for pattern in patterns:
                if re.search(pattern, rule_text, re.IGNORECASE):
                    return vuln_class
        
        # Check patterns if available
        if 'patterns' in rule:
            patterns_text = str(rule['patterns']).lower()
            for vuln_class, patterns in self.vuln_class_patterns.items():
                for pattern in patterns:
                    if re.search(pattern, patterns_text, re.IGNORECASE):
                        return vuln_class
        
        return 'other'
    
    def generate_tags(self, rule: Dict, vuln_class: str) -> List[str]:
        """Generate appropriate tags for the rule."""
        tags = [vuln_class, 'security', 'wordpress']
        
        # Add specific tags based on vulnerability class
        if vuln_class == 'xss':
            tags.extend(['xss', 'html', 'escaping'])
        elif vuln_class == 'sqli':
            tags.extend(['sql-injection', 'database', 'wpdb'])
        elif vuln_class == 'csrf':
            tags.extend(['csrf', 'nonce', 'authentication'])
        elif vuln_class == 'authz':
            tags.extend(['authorization', 'capability', 'permissions'])
        elif vuln_class == 'file_upload':
            tags.extend(['file-upload', 'path-traversal', 'validation'])
        elif vuln_class == 'deserialization':
            tags.extend(['deserialization', 'dynamic-execution', 'eval'])
        elif vuln_class == 'secrets_storage':
            tags.extend(['secrets', 'options', 'configuration'])
        elif vuln_class == 'rest_ajax':
            tags.extend(['rest-api', 'ajax', 'endpoints'])
        
        # Add severity-based tags
        severity = rule.get('severity', 'WARNING').lower()
        tags.append(severity)
        
        return list(set(tags))  # Remove duplicates
    
    def fix_rule_metadata(self, rule: Dict) -> Dict:
        """Fix metadata for a single rule."""
        if 'metadata' not in rule:
            rule['metadata'] = {}
        
        metadata = rule['metadata']
        
        # Detect vulnerability class
        vuln_class = self.detect_vuln_class(rule)
        
        # Set default values for missing fields
        if 'confidence' not in metadata:
            metadata['confidence'] = 'high'
        elif metadata['confidence'].upper() in self.valid_confidences:
            metadata['confidence'] = metadata['confidence'].lower()
        
        if 'cwe' not in metadata:
            metadata['cwe'] = self.cwe_mapping.get(vuln_class, 'CWE-200')
        
        if 'category' not in metadata:
            metadata['category'] = 'security'
        
        if 'tags' not in metadata:
            metadata['tags'] = self.generate_tags(rule, vuln_class)
        
        if 'vuln_class' not in metadata:
            metadata['vuln_class'] = vuln_class
        
        return rule
    
    def fix_rule_file(self, rule_file: Path) -> bool:
        """Fix metadata in a single rule file."""
        try:
            with open(rule_file, 'r', encoding='utf-8') as f:
                content = yaml.safe_load(f)
            
            if not content or 'rules' not in content:
                self.results['errors'].append(f"No rules found in {rule_file}")
                return False
            
            rules = content['rules']
            if not rules:
                self.results['errors'].append(f"Empty rules array in {rule_file}")
                return False
            
            # Fix each rule
            fixed = False
            for i, rule in enumerate(rules):
                original_rule = rule.copy()
                fixed_rule = self.fix_rule_metadata(rule)
                
                # Check if any changes were made
                if fixed_rule != original_rule:
                    rules[i] = fixed_rule
                    fixed = True
            
            if fixed:
                # Write back the fixed content
                with open(rule_file, 'w', encoding='utf-8') as f:
                    yaml.dump(content, f, default_flow_style=False, sort_keys=False)
                return True
            
            return False
            
        except yaml.YAMLError as e:
            self.results['errors'].append(f"YAML parsing error in {rule_file}: {e}")
            return False
        except Exception as e:
            self.results['errors'].append(f"Unexpected error in {rule_file}: {e}")
            return False
    
    def find_rule_files(self) -> List[Path]:
        """Find all rule files in the project."""
        rule_files = []
        pack_dirs = ['packs/wp-core-security', 'packs/wp-core-quality', 'packs/experimental']
        
        for pack_dir in pack_dirs:
            pack_path = self.project_root / pack_dir
            if pack_path.exists():
                rule_files.extend(pack_path.glob('*.yaml'))
        
        return rule_files
    
    def run_fixes(self, dry_run: bool = False) -> Dict:
        """Run fixes on all rule files."""
        rule_files = self.find_rule_files()
        self.results['total_rules'] = len(rule_files)
        
        print(f"Found {len(rule_files)} rule files to fix...")
        
        for rule_file in rule_files:
            print(f"Processing {rule_file}...")
            
            if dry_run:
                # Just check what would be fixed
                try:
                    with open(rule_file, 'r', encoding='utf-8') as f:
                        content = yaml.safe_load(f)
                    
                    if content and 'rules' in content:
                        rules = content['rules']
                        needs_fix = False
                        for rule in rules:
                            if 'metadata' not in rule or not all(
                                field in rule.get('metadata', {}) 
                                for field in self.required_metadata.keys()
                            ):
                                needs_fix = True
                                break
                        
                        if needs_fix:
                            print(f"  Would fix: {rule_file.name}")
                            self.results['fixed_rules'] += 1
                        else:
                            print(f"  No fixes needed: {rule_file.name}")
                            self.results['skipped_rules'] += 1
                except Exception as e:
                    print(f"  Error checking: {rule_file.name} - {e}")
                    self.results['errors'].append(f"Error checking {rule_file}: {e}")
            else:
                # Actually fix the file
                if self.fix_rule_file(rule_file):
                    print(f"  ✅ Fixed: {rule_file.name}")
                    self.results['fixed_rules'] += 1
                else:
                    print(f"  ⏭️  Skipped: {rule_file.name}")
                    self.results['skipped_rules'] += 1
        
        return self.results
    
    def print_report(self):
        """Print fix report."""
        print("\n" + "="*80)
        print("RULE METADATA FIX REPORT")
        print("="*80)
        
        print(f"\nOverall Results:")
        print(f"  Total Rules: {self.results['total_rules']}")
        print(f"  Fixed: {self.results['fixed_rules']}")
        print(f"  Skipped: {self.results['skipped_rules']}")
        print(f"  Success Rate: {self.results['fixed_rules']/self.results['total_rules']*100:.1f}%" if self.results['total_rules'] > 0 else "  Success Rate: N/A")
        
        if self.results['errors']:
            print(f"\nErrors ({len(self.results['errors'])}):")
            for error in self.results['errors']:
                print(f"  ❌ {error}")
        
        print("\n" + "="*80)
    
    def save_results(self, output_file: str = None):
        """Save fix results to file."""
        if output_file is None:
            output_file = self.project_root / "results" / "rule-metadata-fix.json"
        
        output_file = Path(output_file)
        output_file.parent.mkdir(parents=True, exist_ok=True)
        
        with open(output_file, 'w') as f:
            json.dump(self.results, f, indent=2)
        
        print(f"Results saved to: {output_file}")

def main():
    parser = argparse.ArgumentParser(description='Fix missing metadata in rule files')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--output', help='Output file for results')
    parser.add_argument('--dry-run', action='store_true', help='Show what would be fixed without making changes')
    
    args = parser.parse_args()
    
    # Initialize fixer
    fixer = RuleMetadataFixer(args.project_root)
    
    # Run fixes
    results = fixer.run_fixes(dry_run=args.dry_run)
    
    # Print report
    fixer.print_report()
    
    # Save results
    fixer.save_results(args.output)
    
    # Exit with appropriate code
    if results['errors']:
        print(f"\n❌ Fix process completed with {len(results['errors'])} errors")
        sys.exit(1)
    else:
        print(f"\n✅ Fix process completed successfully!")
        if args.dry_run:
            print(f"   {results['fixed_rules']} rules would be fixed")
        else:
            print(f"   {results['fixed_rules']} rules were fixed")
        sys.exit(0)

if __name__ == '__main__':
    main()
