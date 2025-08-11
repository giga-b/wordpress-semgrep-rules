#!/usr/bin/env python3
"""
Add GamiPress Plugins to Attack Corpus
Part of Task 1.2: Attack Corpus Infrastructure

This script adds local GamiPress plugins to the WordPress security testing corpus.
"""

import os
import sys
import json
import shutil
import hashlib
from pathlib import Path
from datetime import datetime

def calculate_directory_hash(directory):
    """Calculate a hash for an entire directory"""
    sha256_hash = hashlib.sha256()
    
    for file_path in sorted(directory.rglob('*')):
        if file_path.is_file():
            # Add file path and content to hash
            sha256_hash.update(str(file_path.relative_to(directory)).encode())
            with open(file_path, 'rb') as f:
                sha256_hash.update(f.read())
    
    return sha256_hash.hexdigest()

def get_directory_size(directory):
    """Get total size of a directory in bytes"""
    total_size = 0
    for file_path in directory.rglob('*'):
        if file_path.is_file():
            total_size += file_path.stat().st_size
    return total_size

def add_gamipress_plugins():
    """Add GamiPress plugins to the corpus"""
    
    # Paths
    corpus_path = Path("corpus/wordpress-plugins")
    metadata_file = corpus_path / "metadata.json"
    local_plugins_path = Path(r"C:\Users\mobet\Local Sites\code-review\app\public\wp-content\plugins")
    
    # Ensure corpus directory exists
    corpus_path.mkdir(parents=True, exist_ok=True)
    
    # Load existing metadata
    metadata = {}
    if metadata_file.exists():
        with open(metadata_file, 'r') as f:
            metadata = json.load(f)
    
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
    
    print("Adding GamiPress plugins to attack corpus...")
    print("=" * 60)
    
    added_count = 0
    
    for plugin in gamipress_plugins:
        plugin_path = local_plugins_path / plugin
        if plugin_path.exists():
            print(f"Adding plugin: {plugin}")
            
            # Create destination directory
            dest_path = corpus_path / plugin
            if dest_path.exists():
                shutil.rmtree(dest_path)
            
            # Copy the plugin
            shutil.copytree(plugin_path, dest_path)
            
            # Calculate file hash and size
            file_hash = calculate_directory_hash(dest_path)
            file_size = get_directory_size(dest_path)
            
            # Create metadata
            metadata[plugin] = {
                'slug': plugin,
                'name': plugin.replace('-', ' ').title(),
                'version': 'local',
                'download_url': f'local://{plugin_path}',
                'last_updated': datetime.now().isoformat(),
                'requires': 'unknown',
                'tested': 'unknown',
                'rating': 0.0,
                'num_ratings': 0,
                'downloaded_at': datetime.now().isoformat(),
                'file_hash': file_hash,
                'file_size': file_size,
                'source': 'local',
                'type': 'plugin'
            }
            
            print(f"  ✓ Successfully added {plugin}")
            added_count += 1
        else:
            print(f"  ⚠ Plugin not found: {plugin}")
    
    # Save metadata
    with open(metadata_file, 'w') as f:
        json.dump(metadata, f, indent=2)
    
    # Show statistics
    print("\n" + "=" * 60)
    print("Corpus Statistics:")
    print("-" * 30)
    
    total_size = sum(meta['file_size'] for meta in metadata.values())
    total_size_mb = total_size / (1024 * 1024)
    
    print(f"Total components: {len(metadata)}")
    print(f"Total size: {total_size_mb:.2f} MB")
    print(f"Added in this run: {added_count}")
    print(f"Last updated: {datetime.now().isoformat()}")
    
    print("\nCorpus update complete!")
    print("Note: Voxel theme excluded to avoid GitHub secret scanning issues.")

if __name__ == "__main__":
    add_gamipress_plugins()
