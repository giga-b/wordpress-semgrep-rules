#!/usr/bin/env python3
"""
Simple Rule Metadata Fix Script
Directly adds missing metadata fields to rule files.
"""

import yaml
import json
import sys
from pathlib import Path
from typing import Dict, List
import argparse
import re

def detect_vuln_class(rule_text: str) -> str:
    """Detect vulnerability class based on rule content."""
    rule_text_lower = rule_text.lower()
    
    # Mapping of patterns to vulnerability classes
    vuln_patterns = {
        'xss': [r'xss', r'html', r'echo', r'print', r'output', r'display', r'esc_html', r'esc_attr', r'esc_url', r'esc_js'],
        'sqli': [r'sql', r'query', r'wpdb', r'prepare', r'get_results', r'get_var', r'get_row', r'get_col'],
        'csrf': [r'nonce', r'csrf', r'wp_verify_nonce', r'wp_create_nonce', r'check_ajax_referer', r'wp_nonce_field'],
        'authz': [r'current_user_can', r'user_can', r'capability', r'permission', r'is_admin', r'is_super_admin'],
        'file_upload': [r'upload', r'file', r'move_uploaded_file', r'path', r'directory', r'basename', r'dirname'],
        'deserialization': [r'unserialize', r'eval', r'create_function', r'call_user_func', r'exec', r'shell_exec'],
        'secrets_storage': [r'add_option', r'update_option', r'get_option', r'delete_option', r'password', r'secret'],
        'rest_ajax': [r'wp_ajax', r'register_rest_route', r'wp_rest', r'ajax', r'admin_ajax', r'wp_rest_api']
    }
    
    for vuln_class, patterns in vuln_patterns.items():
        for pattern in patterns:
            if re.search(pattern, rule_text_lower):
                return vuln_class
    
    return 'other'

def generate_tags(rule: Dict, vuln_class: str) -> List[str]:
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

def get_cwe_for_vuln_class(vuln_class: str) -> str:
    """Get CWE for vulnerability class."""
    cwe_mapping = {
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
    return cwe_mapping.get(vuln_class, 'CWE-200')

def fix_rule_metadata(rule: Dict) -> Dict:
    """Fix metadata for a single rule."""
    # Create metadata section if it doesn't exist
    if 'metadata' not in rule:
        rule['metadata'] = {}
    
    metadata = rule['metadata']
    
    # Detect vulnerability class from rule content
    rule_text = f"{rule.get('id', '')} {rule.get('message', '')}"
    vuln_class = detect_vuln_class(rule_text)
    
    # Set missing fields
    if 'confidence' not in metadata:
        metadata['confidence'] = 'high'
    elif isinstance(metadata['confidence'], str) and metadata['confidence'].upper() in ['LOW', 'MEDIUM', 'HIGH']:
        metadata['confidence'] = metadata['confidence'].lower()
    
    if 'cwe' not in metadata:
        metadata['cwe'] = get_cwe_for_vuln_class(vuln_class)
    
    if 'category' not in metadata:
        metadata['category'] = 'security'
    
    if 'tags' not in metadata:
        metadata['tags'] = generate_tags(rule, vuln_class)
    
    if 'vuln_class' not in metadata:
        metadata['vuln_class'] = vuln_class
    
    return rule

def fix_rule_file(file_path: Path) -> bool:
    """Fix metadata in a single rule file."""
    try:
        # Read the file
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Parse YAML
        data = yaml.safe_load(content)
        
        if not data or 'rules' not in data:
            print(f"  No rules found in {file_path.name}")
            return False
        
        rules = data['rules']
        if not rules:
            print(f"  Empty rules array in {file_path.name}")
            return False
        
        # Fix each rule
        fixed = False
        for i, rule in enumerate(rules):
            # Check if rule needs fixing
            needs_fix = False
            if 'metadata' not in rule:
                needs_fix = True
            else:
                metadata = rule['metadata']
                required_fields = ['confidence', 'cwe', 'category', 'tags', 'vuln_class']
                for field in required_fields:
                    if field not in metadata:
                        needs_fix = True
                        break
            
            if needs_fix:
                fixed_rule = fix_rule_metadata(rule)
                rules[i] = fixed_rule
                fixed = True
        
        if fixed:
            # Write back the fixed content
            with open(file_path, 'w', encoding='utf-8') as f:
                yaml.dump(data, f, default_flow_style=False, sort_keys=False, allow_unicode=True)
            return True
        
        return False
        
    except Exception as e:
        print(f"  Error processing {file_path.name}: {e}")
        return False

def main():
    parser = argparse.ArgumentParser(description='Fix missing metadata in rule files')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--dry-run', action='store_true', help='Show what would be fixed without making changes')
    
    args = parser.parse_args()
    project_root = Path(args.project_root)
    
    # Find rule files
    rule_files = []
    pack_dirs = ['packs/wp-core-security', 'packs/wp-core-quality', 'packs/experimental']
    
    for pack_dir in pack_dirs:
        pack_path = project_root / pack_dir
        if pack_path.exists():
            rule_files.extend(pack_path.glob('*.yaml'))
    
    print(f"Found {len(rule_files)} rule files to process...")
    
    fixed_count = 0
    skipped_count = 0
    
    for rule_file in rule_files:
        print(f"Processing {rule_file}...")
        
        if args.dry_run:
            # Just check what would be fixed
            try:
                with open(rule_file, 'r', encoding='utf-8') as f:
                    content = f.read()
                data = yaml.safe_load(content)
                
                if data and 'rules' in data:
                    rules = data['rules']
                    needs_fix = False
                    for rule in rules:
                        if 'metadata' not in rule or not all(
                            field in rule.get('metadata', {}) 
                            for field in ['confidence', 'cwe', 'category', 'tags', 'vuln_class']
                        ):
                            needs_fix = True
                            break
                    
                    if needs_fix:
                        print(f"  Would fix: {rule_file.name}")
                        fixed_count += 1
                    else:
                        print(f"  No fixes needed: {rule_file.name}")
                        skipped_count += 1
            except Exception as e:
                print(f"  Error checking: {rule_file.name} - {e}")
        else:
            # Actually fix the file
            if fix_rule_file(rule_file):
                print(f"  ✅ Fixed: {rule_file.name}")
                fixed_count += 1
            else:
                print(f"  ⏭️  Skipped: {rule_file.name}")
                skipped_count += 1
    
    print(f"\n{'='*80}")
    print("RULE METADATA FIX REPORT")
    print(f"{'='*80}")
    print(f"\nOverall Results:")
    print(f"  Total Rules: {len(rule_files)}")
    print(f"  Fixed: {fixed_count}")
    print(f"  Skipped: {skipped_count}")
    print(f"  Success Rate: {fixed_count/len(rule_files)*100:.1f}%" if len(rule_files) > 0 else "  Success Rate: N/A")
    print(f"\n{'='*80}")
    
    if args.dry_run:
        print(f"\n✅ Dry run completed!")
        print(f"   {fixed_count} rules would be fixed")
    else:
        print(f"\n✅ Fix process completed successfully!")
        print(f"   {fixed_count} rules were fixed")
    
    return 0 if fixed_count > 0 or args.dry_run else 1

if __name__ == '__main__':
    sys.exit(main())
