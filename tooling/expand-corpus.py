#!/usr/bin/env python3
"""
Expand Corpus with Diverse WordPress Plugins
Downloads and integrates additional WordPress plugins to improve rule testing coverage.
"""

import os
import sys
import json
import shutil
import subprocess
import requests
from pathlib import Path
from typing import Dict, List, Optional
import argparse
import time
import zipfile
import tempfile

class CorpusExpander:
    def __init__(self, project_root: str):
        self.project_root = Path(project_root)
        self.corpus_dir = self.project_root / "corpus" / "wordpress-plugins"
        self.corpus_dir.mkdir(parents=True, exist_ok=True)
        
        # List of diverse plugins to add
        self.target_plugins = [
            # Form Builders
            {"name": "ws-form", "slug": "ws-form", "type": "form_builder"},
            {"name": "fluent-form", "slug": "fluentform", "type": "form_builder"},
            
            # CRM & Marketing
            {"name": "fluent-crm", "slug": "fluent-crm", "type": "crm"},
            {"name": "monster-insights", "slug": "google-analytics-for-wordpress", "type": "analytics"},
            
            # GraphQL & API
            {"name": "gato-graphql", "slug": "gato-graphql", "type": "api"},
            
            # Page Builders & UI
            {"name": "ui-press", "slug": "ui-press", "type": "page_builder"},
            {"name": "block-visibility", "slug": "block-visibility", "type": "blocks"},
            
            # Event Management
            {"name": "event-manager", "slug": "events-manager", "type": "events"},
            
            # Publishing & Content
            {"name": "publish-press", "slug": "publishpress", "type": "publishing"},
            {"name": "code-snippets", "slug": "code-snippets", "type": "development"},
            
            # Security & Monitoring
            {"name": "malcare", "slug": "malcare-security", "type": "security"},
            
            # Automation & Workflow
            {"name": "flowmattic", "slug": "flowmattic", "type": "automation"},
            
            # Admin & Utilities
            {"name": "admin-site-enhancements", "slug": "admin-site-enhancements", "type": "admin"},
            {"name": "if-so", "slug": "if-so", "type": "conditional_content"}
        ]
        
        # Load existing metadata
        self.metadata_file = self.corpus_dir / "metadata.json"
        self.load_metadata()
    
    def load_metadata(self):
        """Load existing corpus metadata."""
        if self.metadata_file.exists():
            with open(self.metadata_file, 'r', encoding='utf-8') as f:
                self.metadata = json.load(f)
                # Ensure required fields exist
                if "components" not in self.metadata:
                    self.metadata["components"] = []
                if "total_components" not in self.metadata:
                    self.metadata["total_components"] = len(self.metadata["components"])
                if "total_size_mb" not in self.metadata:
                    self.metadata["total_size_mb"] = 0.0
                if "last_updated" not in self.metadata:
                    self.metadata["last_updated"] = ""
                if "source" not in self.metadata:
                    self.metadata["source"] = "mixed"
        else:
            self.metadata = {
                "total_components": 0,
                "total_size_mb": 0.0,
                "components": [],
                "last_updated": "",
                "source": "mixed"
            }
    
    def save_metadata(self):
        """Save updated metadata."""
        with open(self.metadata_file, 'w', encoding='utf-8') as f:
            json.dump(self.metadata, f, indent=2)
    
    def download_plugin(self, plugin_info: Dict) -> Optional[Path]:
        """Download a WordPress plugin from the repository."""
        try:
            plugin_name = plugin_info["name"]
            plugin_slug = plugin_info["slug"]
            
            print(f"Downloading {plugin_name}...")
            
            # Create plugin directory
            plugin_dir = self.corpus_dir / plugin_name
            if plugin_dir.exists():
                print(f"  ⚠️  {plugin_name} already exists, skipping...")
                return plugin_dir
            
            # Download from WordPress.org API
            api_url = f"https://api.wordpress.org/plugins/info/1.0/{plugin_slug}.json"
            response = requests.get(api_url, timeout=30)
            
            if response.status_code != 200:
                print(f"  ❌ Failed to get plugin info for {plugin_name}")
                return None
            
            plugin_data = response.json()
            download_url = plugin_data.get('download_link')
            
            if not download_url:
                print(f"  ❌ No download link found for {plugin_name}")
                return None
            
            # Download the plugin
            print(f"  Downloading from {download_url}...")
            response = requests.get(download_url, timeout=60)
            
            if response.status_code != 200:
                print(f"  ❌ Failed to download {plugin_name}")
                return None
            
            # Save to temporary file
            with tempfile.NamedTemporaryFile(suffix='.zip', delete=False) as tmp_file:
                tmp_file.write(response.content)
                tmp_path = Path(tmp_file.name)
            
            # Extract to plugin directory
            with zipfile.ZipFile(tmp_path, 'r') as zip_ref:
                zip_ref.extractall(plugin_dir)
            
            # Clean up temporary file
            tmp_path.unlink()
            
            # Get main plugin file
            main_file = None
            for file in plugin_dir.rglob("*.php"):
                if file.name.endswith(".php") and not file.name.startswith("."):
                    with open(file, 'r', encoding='utf-8', errors='ignore') as f:
                        content = f.read(1000)
                        if "Plugin Name:" in content or "WordPress Plugin" in content:
                            main_file = file
                            break
            
            if main_file:
                # Extract plugin info
                plugin_info.update({
                    "main_file": str(main_file.relative_to(plugin_dir)),
                    "version": plugin_data.get('version', 'unknown'),
                    "description": plugin_data.get('description', ''),
                    "author": plugin_data.get('author', ''),
                    "size_mb": self.get_directory_size(plugin_dir) / (1024 * 1024)
                })
                
                print(f"  ✅ {plugin_name} downloaded successfully ({plugin_info['size_mb']:.2f} MB)")
                return plugin_dir
            else:
                print(f"  ❌ Could not find main plugin file for {plugin_name}")
                shutil.rmtree(plugin_dir, ignore_errors=True)
                return None
                
        except Exception as e:
            print(f"  ❌ Error downloading {plugin_name}: {str(e)}")
            return None
    
    def get_directory_size(self, directory: Path) -> int:
        """Calculate directory size in bytes."""
        total_size = 0
        for dirpath, dirnames, filenames in os.walk(directory):
            for filename in filenames:
                filepath = Path(dirpath) / filename
                try:
                    total_size += filepath.stat().st_size
                except (OSError, IOError):
                    pass
        return total_size
    
    def update_metadata(self, plugin_info: Dict):
        """Update corpus metadata with new plugin."""
        # Check if plugin already exists
        existing_plugin = None
        for component in self.metadata["components"]:
            if component["name"] == plugin_info["name"]:
                existing_plugin = component
                break
        
        if existing_plugin:
            # Update existing plugin
            existing_plugin.update(plugin_info)
            print(f"  📝 Updated metadata for {plugin_info['name']}")
        else:
            # Add new plugin
            self.metadata["components"].append(plugin_info)
            self.metadata["total_components"] += 1
            self.metadata["total_size_mb"] += plugin_info.get("size_mb", 0)
            print(f"  📝 Added {plugin_info['name']} to metadata")
        
        self.metadata["last_updated"] = time.strftime("%Y-%m-%d %H:%M:%S")
        self.metadata["source"] = "mixed"
    
    def expand_corpus(self, max_plugins: int = None) -> Dict:
        """Expand the corpus with new plugins."""
        print("🚀 Expanding WordPress plugin corpus...")
        print(f"Target plugins: {len(self.target_plugins)}")
        
        results = {
            "total_attempted": len(self.target_plugins),
            "successful_downloads": 0,
            "failed_downloads": 0,
            "skipped_downloads": 0,
            "downloaded_plugins": [],
            "failed_plugins": []
        }
        
        # Limit downloads if specified
        plugins_to_download = self.target_plugins
        if max_plugins:
            plugins_to_download = self.target_plugins[:max_plugins]
        
        for i, plugin_info in enumerate(plugins_to_download, 1):
            print(f"\n[{i}/{len(plugins_to_download)}] Processing {plugin_info['name']}...")
            
            # Check if already exists
            plugin_dir = self.corpus_dir / plugin_info["name"]
            if plugin_dir.exists():
                print(f"  ⏭️  {plugin_info['name']} already exists, skipping...")
                results["skipped_downloads"] += 1
                continue
            
            # Download plugin
            downloaded_dir = self.download_plugin(plugin_info)
            
            if downloaded_dir:
                results["successful_downloads"] += 1
                results["downloaded_plugins"].append(plugin_info["name"])
                self.update_metadata(plugin_info)
            else:
                results["failed_downloads"] += 1
                results["failed_plugins"].append(plugin_info["name"])
        
        # Save updated metadata
        self.save_metadata()
        
        # Print summary
        print(f"\n📊 Corpus Expansion Summary:")
        print(f"  Total attempted: {results['total_attempted']}")
        print(f"  Successful downloads: {results['successful_downloads']}")
        print(f"  Failed downloads: {results['failed_downloads']}")
        print(f"  Skipped downloads: {results['skipped_downloads']}")
        print(f"  Total corpus size: {self.metadata['total_size_mb']:.2f} MB")
        print(f"  Total components: {self.metadata['total_components']}")
        
        if results["downloaded_plugins"]:
            print(f"\n✅ Successfully downloaded:")
            for plugin in results["downloaded_plugins"]:
                print(f"  - {plugin}")
        
        if results["failed_plugins"]:
            print(f"\n❌ Failed to download:")
            for plugin in results["failed_plugins"]:
                print(f"  - {plugin}")
        
        return results

def main():
    parser = argparse.ArgumentParser(description='Expand WordPress plugin corpus with diverse plugins')
    parser.add_argument('--project-root', default='.', help='Project root directory')
    parser.add_argument('--max-plugins', type=int, help='Maximum number of plugins to download')
    parser.add_argument('--output', help='Output file for results')
    
    args = parser.parse_args()
    
    # Initialize corpus expander
    expander = CorpusExpander(args.project_root)
    
    # Expand corpus
    results = expander.expand_corpus(args.max_plugins)
    
    # Save results if output file specified
    if args.output:
        with open(args.output, 'w', encoding='utf-8') as f:
            json.dump(results, f, indent=2)
    
    # Exit with appropriate code
    if results["failed_downloads"] > 0:
        print(f"\n⚠️  Some plugins failed to download")
        sys.exit(1)
    else:
        print(f"\n✅ Corpus expansion completed successfully!")
        sys.exit(0)

if __name__ == '__main__':
    main()
