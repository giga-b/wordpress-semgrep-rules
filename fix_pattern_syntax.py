#!/usr/bin/env python3
"""
Script to fix pattern syntax in Semgrep rule files.
Converts 'pattern:' to 'patterns:' with proper YAML structure.
"""

import os
import re
import glob

def fix_pattern_syntax(file_path):
    """Fix pattern syntax in a single file."""
    print(f"Fixing {file_path}...")
    
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Find all pattern: entries and convert them to patterns: structure
    # This regex matches pattern: followed by a quoted string or multiline pattern
    pattern_regex = r'(\s+)pattern:\s*(.+?)(?=\n\s*(?:pattern-not:|metadata:|id:|message:|severity:|languages:|confidence:|cwe:|category:|tags:|vuln_class:|references:))'
    
    def replace_pattern(match):
        indent = match.group(1)
        pattern_content = match.group(2).strip()
        
        # Handle different pattern formats
        if pattern_content.startswith('"') and pattern_content.endswith('"'):
            # Simple quoted pattern
            return f"{indent}patterns:\n{indent}- pattern: {pattern_content}"
        elif pattern_content.startswith("'") and pattern_content.endswith("'"):
            # Simple quoted pattern
            return f"{indent}patterns:\n{indent}- pattern: {pattern_content}"
        elif '\\n' in pattern_content:
            # Multiline pattern with escaped newlines
            # Convert to YAML multiline format
            lines = pattern_content.split('\\n')
            yaml_pattern = f"{indent}patterns:\n{indent}- pattern: |\n"
            for line in lines:
                yaml_pattern += f"{indent}    {line}\n"
            return yaml_pattern.rstrip()
        else:
            # Simple pattern
            return f"{indent}patterns:\n{indent}- pattern: {pattern_content}"
    
    # Apply the replacement
    new_content = re.sub(pattern_regex, replace_pattern, content, flags=re.DOTALL)
    
    # Write the fixed content back
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(new_content)
    
    print(f"✅ Fixed {file_path}")

def main():
    """Main function to fix all YAML files."""
    # Files to fix (based on our earlier analysis)
    files_to_fix = [
        'packs/wp-core-security/ajax-action-registration.yaml',
        'packs/wp-core-security/ajax-callback-functions.yaml',
        'packs/wp-core-security/ajax-security.yaml',
        'packs/wp-core-security/callback-function-tracing.yaml',
        'packs/wp-core-security/cross-file-nonce-analysis-working.yaml',
        'packs/wp-core-security/cross-file-nonce-analysis.yaml',
        'packs/wp-core-security/nonce-creation-detection.yaml',
        'packs/wp-core-security/nonce-lifecycle-detection.yaml',
        'packs/wp-core-security/nonce-verification-detection.yaml',
        'packs/experimental/advanced-obfuscation-rules.yaml',
        'packs/experimental/advanced-security-rules.yaml',
        'packs/experimental/comprehensive-security-rules.yaml',
        'packs/experimental/simple-advanced-rules.yaml',
        'packs/experimental/taint-analysis-framework.yaml',
        'packs/experimental/vx2-specific-rules.yaml',
        'packs/experimental/xss-taint-rules.yaml'
    ]
    
    print("Fixing pattern syntax in Semgrep rule files...")
    print("=" * 50)
    
    for file_path in files_to_fix:
        if os.path.exists(file_path):
            fix_pattern_syntax(file_path)
        else:
            print(f"⚠️  File not found: {file_path}")
    
    print("=" * 50)
    print("✅ Pattern syntax fixing completed!")

if __name__ == "__main__":
    main()
