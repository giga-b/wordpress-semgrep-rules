#!/usr/bin/env python3
"""
Add Local WordPress Components to Attack Corpus
Part of Task 1.2: Attack Corpus Infrastructure

This script adds local GamiPress plugins to the WordPress security testing corpus.
Note: Voxel theme excluded due to GitHub secret scanning issues.
"""

import sys
import os
from pathlib import Path

# Add the tooling directory to the path so we can import corpus-manager
sys.path.append(str(Path(__file__).parent))

import importlib.util
spec = importlib.util.spec_from_file_location("corpus_manager", "tooling/corpus-manager.py")
corpus_manager = importlib.util.module_from_spec(spec)
spec.loader.exec_module(corpus_manager)
WordPressCorpusManager = corpus_manager.WordPressCorpusManager

def main():
    """Add local WordPress components to the corpus"""
    
    # Change to the project root directory
    project_root = Path(__file__).parent.parent
    os.chdir(project_root)
    
    # Initialize corpus manager
    manager = WordPressCorpusManager()
    
    # Local WordPress installation paths
    local_plugins_path = r"C:\Users\mobet\Local Sites\code-review\app\public\wp-content\plugins"
    
    # GamiPress plugins to add
    gamipress_plugins = [
        "gamipress",
        "gamipress-coupons", 
        "gamipress-expirations",
        "gamipress-leaderboards",
        "gamipress-points-types-pro",
        "gamipress-rest-api-extended",
        "gamipress-restrict-content",
        "gamipress-time-based-rewards"
    ]
    
    print("Adding local WordPress components to attack corpus...")
    print("=" * 60)
    
    # Add GamiPress plugins
    print("\nAdding GamiPress plugins:")
    print("-" * 30)
    
    for plugin in gamipress_plugins:
        plugin_path = os.path.join(local_plugins_path, plugin)
        if os.path.exists(plugin_path):
            print(f"Adding plugin: {plugin}")
            result = manager.add_local_component(plugin_path, "plugin")
            if result:
                print(f"  ✓ Successfully added {plugin}")
            else:
                print(f"  ✗ Failed to add {plugin}")
        else:
            print(f"  ⚠ Plugin not found: {plugin}")
    
    # Show updated corpus statistics
    print("\n" + "=" * 60)
    print("Updated Corpus Statistics:")
    print("-" * 30)
    
    stats = manager.get_corpus_stats()
    print(f"Total components: {stats['total_plugins']}")
    print(f"Total size: {stats['total_size_mb']:.2f} MB")
    print(f"Last updated: {stats['last_updated']}")
    
    # Show breakdown by source
    print("\nComponent breakdown by source:")
    print("-" * 35)
    
    wordpress_org_count = 0
    local_count = 0
    
    for slug, metadata in manager.metadata.items():
        source = metadata.source
        if source == 'wordpress.org':
            wordpress_org_count += 1
        elif source == 'local':
            local_count += 1
    
    print(f"WordPress.org plugins: {wordpress_org_count}")
    print(f"Local components: {local_count}")
    
    print("\nCorpus update complete!")
    print("Note: Voxel theme excluded due to GitHub secret scanning issues.")

if __name__ == "__main__":
    main()
