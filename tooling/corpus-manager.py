#!/usr/bin/env python3
"""
WordPress Plugin Corpus Manager
Part of Task 1.2: Attack Corpus Infrastructure

This tool manages the download and organization of WordPress plugins for security testing.
It provides automated plugin downloading, metadata management, and corpus versioning.
"""

import os
import sys
import json
import subprocess
import requests
import zipfile
import hashlib
from pathlib import Path
from typing import Dict, List, Optional, Tuple
from dataclasses import dataclass
from datetime import datetime
import logging

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)

@dataclass
class PluginMetadata:
    """Represents metadata for a WordPress plugin"""
    slug: str
    name: str
    version: str
    download_url: str
    last_updated: str
    requires: str
    tested: str
    rating: float
    num_ratings: int
    downloaded_at: str
    file_hash: str
    file_size: int

class WordPressCorpusManager:
    """Manages WordPress plugin corpus for security testing"""
    
    def __init__(self, corpus_path: str = "corpus/wordpress-plugins"):
        self.corpus_path = Path(corpus_path)
        self.corpus_path.mkdir(parents=True, exist_ok=True)
        self.metadata_file = self.corpus_path / "metadata.json"
        self.metadata = self._load_metadata()
        
    def _load_metadata(self) -> Dict:
        """Load existing plugin metadata"""
        if self.metadata_file.exists():
            with open(self.metadata_file, 'r') as f:
                return json.load(f)
        return {}
    
    def _save_metadata(self):
        """Save plugin metadata to file"""
        with open(self.metadata_file, 'w') as f:
            json.dump(self.metadata, f, indent=2)
    
    def get_popular_plugins(self, count: int = 2000) -> List[Dict]:
        """Get list of popular WordPress plugins from WordPress.org API"""
        logger.info(f"Fetching {count} popular plugins from WordPress.org")
        
        # WordPress.org API endpoint for popular plugins
        api_url = "https://api.wordpress.org/plugins/info/1.2/"
        
        try:
            # Get popular plugins (this is a simplified approach)
            # In a real implementation, you might want to use the WordPress.org API
            # or scrape the plugin directory
            
            # For now, we'll create a sample list of popular plugins
            popular_plugins = [
                {
                    "slug": "woocommerce",
                    "name": "WooCommerce",
                    "version": "8.5.2",
                    "download_url": "https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip",
                    "last_updated": "2024-01-15",
                    "requires": "6.0",
                    "tested": "6.4",
                    "rating": 4.5,
                    "num_ratings": 50000
                },
                {
                    "slug": "elementor",
                    "name": "Elementor",
                    "version": "3.18.3",
                    "download_url": "https://downloads.wordpress.org/plugin/elementor.latest-stable.zip",
                    "last_updated": "2024-01-10",
                    "requires": "5.0",
                    "tested": "6.4",
                    "rating": 4.6,
                    "num_ratings": 45000
                },
                {
                    "slug": "contact-form-7",
                    "name": "Contact Form 7",
                    "version": "5.8.1",
                    "download_url": "https://downloads.wordpress.org/plugin/contact-form-7.latest-stable.zip",
                    "last_updated": "2024-01-12",
                    "requires": "5.4",
                    "tested": "6.4",
                    "rating": 4.3,
                    "num_ratings": 35000
                }
            ]
            
            # For demonstration, we'll return a limited set
            # In production, this would fetch from the actual API
            return popular_plugins[:min(count, len(popular_plugins))]
            
        except Exception as e:
            logger.error(f"Failed to fetch popular plugins: {e}")
            return []
    
    def download_plugin(self, plugin_info: Dict) -> Optional[PluginMetadata]:
        """Download a single plugin and extract metadata"""
        slug = plugin_info['slug']
        plugin_dir = self.corpus_path / slug
        
        if plugin_dir.exists():
            logger.info(f"Plugin {slug} already exists, skipping")
            return self.metadata.get(slug)
        
        try:
            logger.info(f"Downloading plugin: {slug}")
            
            # Download plugin
            response = requests.get(plugin_info['download_url'], timeout=60)
            response.raise_for_status()
            
            # Save zip file
            zip_path = self.corpus_path / f"{slug}.zip"
            with open(zip_path, 'wb') as f:
                f.write(response.content)
            
            # Extract plugin
            with zipfile.ZipFile(zip_path, 'r') as zip_ref:
                zip_ref.extractall(plugin_dir)
            
            # Calculate file hash
            file_hash = hashlib.sha256(response.content).hexdigest()
            file_size = len(response.content)
            
            # Create metadata
            metadata = PluginMetadata(
                slug=slug,
                name=plugin_info['name'],
                version=plugin_info['version'],
                download_url=plugin_info['download_url'],
                last_updated=plugin_info['last_updated'],
                requires=plugin_info['requires'],
                tested=plugin_info['tested'],
                rating=plugin_info['rating'],
                num_ratings=plugin_info['num_ratings'],
                downloaded_at=datetime.now().isoformat(),
                file_hash=file_hash,
                file_size=file_size
            )
            
            # Save metadata
            self.metadata[slug] = metadata.__dict__
            self._save_metadata()
            
            # Clean up zip file
            zip_path.unlink()
            
            logger.info(f"Successfully downloaded plugin: {slug}")
            return metadata
            
        except Exception as e:
            logger.error(f"Failed to download plugin {slug}: {e}")
            return None
    
    def download_corpus(self, max_plugins: int = 2000, force_update: bool = False):
        """Download the complete plugin corpus"""
        logger.info(f"Starting corpus download (max: {max_plugins} plugins)")
        
        # Get popular plugins
        plugins = self.get_popular_plugins(max_plugins)
        
        if not plugins:
            logger.error("No plugins found to download")
            return
        
        # Download plugins
        successful_downloads = 0
        failed_downloads = 0
        
        for i, plugin_info in enumerate(plugins, 1):
            logger.info(f"Processing plugin {i}/{len(plugins)}: {plugin_info['slug']}")
            
            if self.download_plugin(plugin_info):
                successful_downloads += 1
            else:
                failed_downloads += 1
            
            # Progress update
            if i % 10 == 0:
                logger.info(f"Progress: {i}/{len(plugins)} plugins processed")
        
        logger.info(f"Corpus download complete:")
        logger.info(f"  Successful: {successful_downloads}")
        logger.info(f"  Failed: {failed_downloads}")
        logger.info(f"  Total: {len(plugins)}")
    
    def get_plugin_paths(self) -> List[Path]:
        """Get list of all downloaded plugin paths"""
        plugin_paths = []
        for item in self.corpus_path.iterdir():
            if item.is_dir() and item.name != "__pycache__":
                plugin_paths.append(item)
        return plugin_paths
    
    def get_corpus_stats(self) -> Dict:
        """Get statistics about the corpus"""
        plugin_paths = self.get_plugin_paths()
        total_size = sum(p.stat().st_size for p in plugin_paths if p.is_dir())
        
        return {
            "total_plugins": len(plugin_paths),
            "total_size_bytes": total_size,
            "total_size_mb": total_size / (1024 * 1024),
            "last_updated": datetime.now().isoformat(),
            "metadata_file": str(self.metadata_file)
        }
    
    def cleanup_old_plugins(self, days_old: int = 30):
        """Remove plugins older than specified days"""
        logger.info(f"Cleaning up plugins older than {days_old} days")
        
        cutoff_date = datetime.now().timestamp() - (days_old * 24 * 60 * 60)
        removed_count = 0
        
        for plugin_path in self.get_plugin_paths():
            if plugin_path.stat().st_mtime < cutoff_date:
                try:
                    import shutil
                    shutil.rmtree(plugin_path)
                    slug = plugin_path.name
                    if slug in self.metadata:
                        del self.metadata[slug]
                    removed_count += 1
                    logger.info(f"Removed old plugin: {slug}")
                except Exception as e:
                    logger.error(f"Failed to remove plugin {plugin_path.name}: {e}")
        
        self._save_metadata()
        logger.info(f"Cleanup complete: {removed_count} plugins removed")
    
    def validate_corpus(self) -> Dict:
        """Validate the integrity of the corpus"""
        logger.info("Validating corpus integrity")
        
        validation_results = {
            "total_plugins": 0,
            "valid_plugins": 0,
            "invalid_plugins": 0,
            "missing_metadata": 0,
            "errors": []
        }
        
        for plugin_path in self.get_plugin_paths():
            slug = plugin_path.name
            validation_results["total_plugins"] += 1
            
            # Check if plugin has main PHP file
            main_files = list(plugin_path.glob("*.php"))
            if not main_files:
                validation_results["invalid_plugins"] += 1
                validation_results["errors"].append(f"Plugin {slug} has no main PHP file")
                continue
            
            # Check metadata
            if slug not in self.metadata:
                validation_results["missing_metadata"] += 1
                validation_results["errors"].append(f"Plugin {slug} missing metadata")
                continue
            
            validation_results["valid_plugins"] += 1
        
        logger.info(f"Validation complete:")
        logger.info(f"  Total plugins: {validation_results['total_plugins']}")
        logger.info(f"  Valid plugins: {validation_results['valid_plugins']}")
        logger.info(f"  Invalid plugins: {validation_results['invalid_plugins']}")
        logger.info(f"  Missing metadata: {validation_results['missing_metadata']}")
        
        return validation_results

def main():
    """Main entry point for the corpus manager"""
    import argparse
    
    parser = argparse.ArgumentParser(description='WordPress Plugin Corpus Manager')
    parser.add_argument('--download', type=int, metavar='COUNT', 
                       help='Download plugins (specify max count)')
    parser.add_argument('--stats', action='store_true', 
                       help='Show corpus statistics')
    parser.add_argument('--validate', action='store_true', 
                       help='Validate corpus integrity')
    parser.add_argument('--cleanup', type=int, metavar='DAYS', 
                       help='Clean up plugins older than DAYS')
    parser.add_argument('--corpus-path', default='corpus/wordpress-plugins',
                       help='Path to corpus directory')
    
    args = parser.parse_args()
    
    manager = WordPressCorpusManager(args.corpus_path)
    
    if args.download:
        manager.download_corpus(args.download)
    elif args.stats:
        stats = manager.get_corpus_stats()
        print("Corpus Statistics:")
        print(f"  Total plugins: {stats['total_plugins']}")
        print(f"  Total size: {stats['total_size_mb']:.2f} MB")
        print(f"  Last updated: {stats['last_updated']}")
    elif args.validate:
        results = manager.validate_corpus()
        print("Validation Results:")
        print(f"  Valid plugins: {results['valid_plugins']}/{results['total_plugins']}")
        if results['errors']:
            print("  Errors:")
            for error in results['errors'][:10]:  # Show first 10 errors
                print(f"    - {error}")
    elif args.cleanup:
        manager.cleanup_old_plugins(args.cleanup)
    else:
        parser.print_help()

if __name__ == "__main__":
    main()
