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
import shutil
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
    """Metadata for a WordPress plugin"""
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
    source: str = "wordpress.org"
    type: str = "plugin"

class WordPressCorpusManager:
    """Manages WordPress plugin corpus for security testing"""
    
    def __init__(self, corpus_path: str = "corpus/wordpress-plugins"):
        self.corpus_path = Path(corpus_path)
        self.metadata_file = self.corpus_path / "metadata.json"
        self.metadata: Dict[str, PluginMetadata] = {}
        
        # Ensure corpus directory exists
        self.corpus_path.mkdir(parents=True, exist_ok=True)
        
        # Load existing metadata
        self._load_metadata()
    
    def _load_metadata(self):
        """Load metadata from JSON file"""
        if self.metadata_file.exists():
            try:
                with open(self.metadata_file, 'r') as f:
                    data = json.load(f)
                    for slug, meta in data.items():
                        self.metadata[slug] = PluginMetadata(**meta)
                logger.info(f"Loaded metadata for {len(self.metadata)} plugins")
            except Exception as e:
                logger.error(f"Error loading metadata: {e}")
                self.metadata = {}
        else:
            logger.info("No existing metadata found, starting fresh")
    
    def _save_metadata(self):
        """Save metadata to JSON file"""
        try:
            data = {}
            for slug, meta in self.metadata.items():
                data[slug] = {
                    'slug': meta.slug,
                    'name': meta.name,
                    'version': meta.version,
                    'download_url': meta.download_url,
                    'last_updated': meta.last_updated,
                    'requires': meta.requires,
                    'tested': meta.tested,
                    'rating': meta.rating,
                    'num_ratings': meta.num_ratings,
                    'downloaded_at': meta.downloaded_at,
                    'file_hash': meta.file_hash,
                    'file_size': meta.file_size,
                    'source': meta.source,
                    'type': meta.type
                }
            
            with open(self.metadata_file, 'w') as f:
                json.dump(data, f, indent=2)
            logger.info(f"Saved metadata for {len(self.metadata)} plugins")
        except Exception as e:
            logger.error(f"Error saving metadata: {e}")
    
    def _calculate_file_hash(self, file_path: Path) -> str:
        """Calculate SHA256 hash of a file"""
        sha256_hash = hashlib.sha256()
        with open(file_path, "rb") as f:
            for chunk in iter(lambda: f.read(4096), b""):
                sha256_hash.update(chunk)
        return sha256_hash.hexdigest()
    
    def _get_file_size(self, file_path: Path) -> int:
        """Get file size in bytes"""
        return file_path.stat().st_size
    
    def add_local_component(self, local_path: str, component_type: str = "plugin") -> bool:
        """Add a local WordPress component to the corpus"""
        try:
            local_path = Path(local_path)
            if not local_path.exists():
                logger.error(f"Local path does not exist: {local_path}")
                return False
            
            # Use the directory name as the slug
            slug = local_path.name
            
            # Create destination directory
            dest_path = self.corpus_path / slug
            if dest_path.exists():
                shutil.rmtree(dest_path)
            
            # Copy the component
            shutil.copytree(local_path, dest_path)
            
            # Calculate file hash (hash the entire directory)
            file_hash = self._calculate_directory_hash(dest_path)
            file_size = self._get_directory_size(dest_path)
            
            # Create metadata
            metadata = PluginMetadata(
                slug=slug,
                name=slug.replace('-', ' ').title(),
                version="local",
                download_url=f"local://{local_path}",
                last_updated=datetime.now().isoformat(),
                requires="unknown",
                tested="unknown",
                rating=0.0,
                num_ratings=0,
                downloaded_at=datetime.now().isoformat(),
                file_hash=file_hash,
                file_size=file_size,
                source="local",
                type=component_type
            )
            
            # Add to metadata
            self.metadata[slug] = metadata
            self._save_metadata()
            
            logger.info(f"Successfully added local {component_type}: {slug}")
            return True
            
        except Exception as e:
            logger.error(f"Error adding local component {local_path}: {e}")
            return False
    
    def _calculate_directory_hash(self, directory: Path) -> str:
        """Calculate a hash for an entire directory"""
        sha256_hash = hashlib.sha256()
        
        for file_path in sorted(directory.rglob('*')):
            if file_path.is_file():
                # Add file path and content to hash
                sha256_hash.update(str(file_path.relative_to(directory)).encode())
                with open(file_path, 'rb') as f:
                    sha256_hash.update(f.read())
        
        return sha256_hash.hexdigest()
    
    def _get_directory_size(self, directory: Path) -> int:
        """Get total size of a directory in bytes"""
        total_size = 0
        for file_path in directory.rglob('*'):
            if file_path.is_file():
                total_size += file_path.stat().st_size
        return total_size
    
    def get_corpus_stats(self) -> Dict:
        """Get corpus statistics"""
        total_size = sum(meta.file_size for meta in self.metadata.values())
        total_size_mb = total_size / (1024 * 1024)
        
        return {
            'total_plugins': len(self.metadata),
            'total_size_bytes': total_size,
            'total_size_mb': total_size_mb,
            'last_updated': datetime.now().isoformat()
        }

def main():
    """Main function for corpus management"""
    manager = WordPressCorpusManager()
    
    # Sample popular plugins to download
    popular_plugins = [
        "woocommerce",
        "elementor", 
        "contact-form-7"
    ]
    
    print("WordPress Plugin Corpus Manager")
    print("=" * 40)
    
    # Show current stats
    stats = manager.get_corpus_stats()
    print(f"Current corpus: {stats['total_plugins']} plugins, {stats['total_size_mb']:.2f} MB")
    
    print("\nCorpus management complete!")

if __name__ == "__main__":
    main()
