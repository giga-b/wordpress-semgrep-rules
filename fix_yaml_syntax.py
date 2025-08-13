#!/usr/bin/env python3
"""
Script to fix YAML syntax issues in Semgrep rule files.
Removes duplicate patterns: lines and fixes indentation.
"""

import os
import re

def fix_yaml_syntax(file_path):
    """Fix YAML syntax in a single file."""
    print(f"Fixing YAML syntax in {file_path}...")
    
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Fix duplicate patterns: lines
    # Replace "patterns:\n  - patterns:" with "patterns:\n  -"
    content = re.sub(r'patterns:\s*\n\s*- patterns:', 'patterns:\n  -', content)
    
    # Fix indentation issues
    # Replace " - pattern:" with "  - pattern:" (proper indentation)
    content = re.sub(r'^\s*- pattern:', '  - pattern:', content, flags=re.MULTILINE)
    
    # Write the fixed content back
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f"✅ Fixed YAML syntax in {file_path}")

def main():
    """Main function to fix all YAML files."""
    # Files that need YAML syntax fixes
    files_to_fix = [
        'packs/wp-core-security/ajax-action-registration.yaml',
        'packs/experimental/advanced-security-rules.yaml'
    ]
    
    print("Fixing YAML syntax in Semgrep rule files...")
    print("=" * 50)
    
    for file_path in files_to_fix:
        if os.path.exists(file_path):
            fix_yaml_syntax(file_path)
        else:
            print(f"⚠️  File not found: {file_path}")
    
    print("=" * 50)
    print("✅ YAML syntax fixing completed!")

if __name__ == "__main__":
    main()
