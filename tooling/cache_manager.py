#!/usr/bin/env python3
"""
WordPress Semgrep Rules - Cache Manager

This module provides a comprehensive caching system for:
- Scan results caching
- Rule compilation caching
- Cache invalidation and management
- Performance optimization

Author: WordPress Semgrep Rules Team
License: MIT
"""

import os
import json
import hashlib
import time
import shutil
import tempfile
from pathlib import Path
from typing import Dict, Any, Optional, List, Tuple
from dataclasses import dataclass, asdict
from datetime import datetime, timedelta
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


@dataclass
class CacheEntry:
    """Represents a cache entry with metadata."""
    key: str
    data: Any
    timestamp: float
    expires_at: float
    size_bytes: int
    cache_type: str
    metadata: Dict[str, Any]


@dataclass
class CacheStats:
    """Cache statistics and metrics."""
    total_entries: int
    total_size_bytes: int
    hit_count: int
    miss_count: int
    eviction_count: int
    last_cleanup: float
    cache_hit_rate: float


class CacheManager:
    """
    Comprehensive caching system for WordPress Semgrep rules.
    
    Features:
    - Scan results caching
    - Rule compilation caching
    - Automatic cache invalidation
    - Cache statistics and metrics
    - Cross-platform support
    """
    
    def __init__(self, cache_dir: Optional[str] = None, max_size_mb: int = 500):
        """
        Initialize the cache manager.
        
        Args:
            cache_dir: Directory to store cache files (default: system temp)
            max_size_mb: Maximum cache size in megabytes
        """
        self.max_size_bytes = max_size_mb * 1024 * 1024
        self.cache_dir = Path(cache_dir) if cache_dir else Path(tempfile.gettempdir()) / "wordpress-semgrep-cache"
        self.cache_dir.mkdir(parents=True, exist_ok=True)
        
        # Cache metadata
        self.metadata_file = self.cache_dir / "cache_metadata.json"
        self.stats_file = self.cache_dir / "cache_stats.json"
        
        # Initialize cache
        self._load_metadata()
        self._load_stats()
        
        # Cache types and their TTL (time to live) in seconds
        self.cache_ttl = {
            'scan_results': 24 * 60 * 60,  # 24 hours
            'rule_compilation': 7 * 24 * 60 * 60,  # 7 days
            'config_validation': 60 * 60,  # 1 hour
            'performance_data': 24 * 60 * 60,  # 24 hours
            'test_results': 12 * 60 * 60,  # 12 hours
        }
        
        logger.info(f"Cache manager initialized: {self.cache_dir}")
    
    def _load_metadata(self) -> None:
        """Load cache metadata from disk."""
        if self.metadata_file.exists():
            try:
                with open(self.metadata_file, 'r') as f:
                    self.metadata = json.load(f)
            except (json.JSONDecodeError, IOError):
                self.metadata = {}
        else:
            self.metadata = {}
    
    def _save_metadata(self) -> None:
        """Save cache metadata to disk."""
        try:
            with open(self.metadata_file, 'w') as f:
                json.dump(self.metadata, f, indent=2)
        except IOError as e:
            logger.error(f"Failed to save cache metadata: {e}")
    
    def _load_stats(self) -> None:
        """Load cache statistics from disk."""
        if self.stats_file.exists():
            try:
                with open(self.stats_file, 'r') as f:
                    stats_data = json.load(f)
                    self.stats = CacheStats(**stats_data)
            except (json.JSONDecodeError, IOError):
                self.stats = CacheStats(0, 0, 0, 0, 0, time.time(), 0.0)
        else:
            self.stats = CacheStats(0, 0, 0, 0, 0, time.time(), 0.0)
    
    def _save_stats(self) -> None:
        """Save cache statistics to disk."""
        try:
            with open(self.stats_file, 'w') as f:
                json.dump(asdict(self.stats), f, indent=2)
        except IOError as e:
            logger.error(f"Failed to save cache stats: {e}")
    
    def _generate_cache_key(self, cache_type: str, *args: Any) -> str:
        """
        Generate a unique cache key based on cache type and arguments.
        
        Args:
            cache_type: Type of cache entry
            *args: Arguments to hash for key generation
            
        Returns:
            Unique cache key
        """
        # Create a string representation of all arguments
        key_data = f"{cache_type}:{':'.join(str(arg) for arg in args)}"
        
        # Generate SHA256 hash
        key_hash = hashlib.sha256(key_data.encode()).hexdigest()[:16]
        
        return f"{cache_type}_{key_hash}"
    
    def _get_cache_file_path(self, key: str) -> Path:
        """Get the file path for a cache key."""
        return self.cache_dir / f"{key}.json"
    
    def _is_cache_valid(self, entry: CacheEntry) -> bool:
        """Check if a cache entry is still valid."""
        return time.time() < entry.expires_at
    
    def _calculate_entry_size(self, data: Any) -> int:
        """Calculate the size of cache entry data in bytes."""
        try:
            return len(json.dumps(data, separators=(',', ':')).encode())
        except (TypeError, ValueError):
            return 0
    
    def get(self, cache_type: str, *args: Any) -> Optional[Any]:
        """
        Retrieve data from cache.
        
        Args:
            cache_type: Type of cache entry
            *args: Arguments used to generate cache key
            
        Returns:
            Cached data if valid, None otherwise
        """
        key = self._generate_cache_key(cache_type, *args)
        
        if key not in self.metadata:
            self.stats.miss_count += 1
            self._save_stats()
            return None
        
        entry_data = self.metadata[key]
        cache_file = self._get_cache_file_path(key)
        
        if not cache_file.exists():
            # Cache file missing, remove from metadata
            del self.metadata[key]
            self.stats.miss_count += 1
            self._save_stats()
            return None
        
        entry = CacheEntry(**entry_data)
        
        if not self._is_cache_valid(entry):
            # Cache expired, remove it
            self._remove_entry(key)
            self.stats.miss_count += 1
            self._save_stats()
            return None
        
        # Load cached data
        try:
            with open(cache_file, 'r') as f:
                data = json.load(f)
            
            self.stats.hit_count += 1
            self._update_hit_rate()
            self._save_stats()
            
            logger.debug(f"Cache hit: {key}")
            return data
            
        except (json.JSONDecodeError, IOError) as e:
            logger.error(f"Failed to load cache entry {key}: {e}")
            self._remove_entry(key)
            self.stats.miss_count += 1
            self._save_stats()
            return None
    
    def set(self, cache_type: str, data: Any, *args: Any, ttl: Optional[int] = None) -> bool:
        """
        Store data in cache.
        
        Args:
            cache_type: Type of cache entry
            data: Data to cache
            *args: Arguments used to generate cache key
            ttl: Time to live in seconds (overrides default)
            
        Returns:
            True if successfully cached, False otherwise
        """
        key = self._generate_cache_key(cache_type, *args)
        
        # Calculate TTL
        if ttl is None:
            ttl = self.cache_ttl.get(cache_type, 3600)  # Default 1 hour
        
        # Create cache entry
        entry = CacheEntry(
            key=key,
            data=None,  # Data stored separately
            timestamp=time.time(),
            expires_at=time.time() + ttl,
            size_bytes=self._calculate_entry_size(data),
            cache_type=cache_type,
            metadata={
                'args': args,
                'created_at': datetime.now().isoformat()
            }
        )
        
        # Check cache size limit
        if not self._ensure_cache_space(entry.size_bytes):
            logger.warning(f"Cache full, cannot store entry: {key}")
            return False
        
        # Save data to file
        cache_file = self._get_cache_file_path(key)
        try:
            with open(cache_file, 'w') as f:
                json.dump(data, f, separators=(',', ':'))
            
            # Update metadata
            self.metadata[key] = asdict(entry)
            self._save_metadata()
            
            # Update stats
            self.stats.total_entries += 1
            self.stats.total_size_bytes += entry.size_bytes
            self._save_stats()
            
            logger.debug(f"Cache set: {key} ({entry.size_bytes} bytes)")
            return True
            
        except (json.JSONDecodeError, IOError) as e:
            logger.error(f"Failed to save cache entry {key}: {e}")
            return False
    
    def _ensure_cache_space(self, required_bytes: int) -> bool:
        """
        Ensure there's enough space in cache for new entry.
        
        Args:
            required_bytes: Bytes needed for new entry
            
        Returns:
            True if space is available, False otherwise
        """
        current_size = self.stats.total_size_bytes
        available_space = self.max_size_bytes - current_size
        
        if available_space >= required_bytes:
            return True
        
        # Need to evict entries
        logger.info(f"Cache full, evicting entries to make space for {required_bytes} bytes")
        
        # Sort entries by access time (LRU)
        sorted_entries = sorted(
            self.metadata.items(),
            key=lambda x: x[1].get('timestamp', 0)
        )
        
        freed_space = 0
        for key, entry_data in sorted_entries:
            if freed_space >= required_bytes:
                break
            
            entry = CacheEntry(**entry_data)
            freed_space += entry.size_bytes
            self._remove_entry(key)
        
        return freed_space >= required_bytes
    
    def _remove_entry(self, key: str) -> None:
        """Remove a cache entry."""
        if key in self.metadata:
            entry_data = self.metadata[key]
            entry = CacheEntry(**entry_data)
            
            # Remove file
            cache_file = self._get_cache_file_path(key)
            if cache_file.exists():
                cache_file.unlink()
            
            # Update stats
            self.stats.total_entries -= 1
            self.stats.total_size_bytes -= entry.size_bytes
            self.stats.eviction_count += 1
            
            # Remove from metadata
            del self.metadata[key]
            self._save_metadata()
            self._save_stats()
    
    def _update_hit_rate(self) -> None:
        """Update cache hit rate."""
        total_requests = self.stats.hit_count + self.stats.miss_count
        if total_requests > 0:
            self.stats.cache_hit_rate = self.stats.hit_count / total_requests
    
    def invalidate(self, cache_type: Optional[str] = None, pattern: Optional[str] = None) -> int:
        """
        Invalidate cache entries.
        
        Args:
            cache_type: Invalidate only entries of this type
            pattern: Invalidate entries matching this pattern
            
        Returns:
            Number of entries invalidated
        """
        keys_to_remove = []
        
        for key, entry_data in self.metadata.items():
            entry = CacheEntry(**entry_data)
            
            # Check cache type filter
            if cache_type and entry.cache_type != cache_type:
                continue
            
            # Check pattern filter
            if pattern and pattern not in key:
                continue
            
            keys_to_remove.append(key)
        
        # Remove entries
        for key in keys_to_remove:
            self._remove_entry(key)
        
        logger.info(f"Invalidated {len(keys_to_remove)} cache entries")
        return len(keys_to_remove)
    
    def cleanup(self) -> Dict[str, Any]:
        """
        Clean up expired cache entries.
        
        Returns:
            Cleanup statistics
        """
        expired_count = 0
        freed_bytes = 0
        
        keys_to_remove = []
        
        for key, entry_data in self.metadata.items():
            entry = CacheEntry(**entry_data)
            
            if not self._is_cache_valid(entry):
                keys_to_remove.append(key)
                expired_count += 1
                freed_bytes += entry.size_bytes
        
        # Remove expired entries
        for key in keys_to_remove:
            self._remove_entry(key)
        
        # Update last cleanup time
        self.stats.last_cleanup = time.time()
        self._save_stats()
        
        cleanup_stats = {
            'expired_entries': expired_count,
            'freed_bytes': freed_bytes,
            'remaining_entries': self.stats.total_entries,
            'cache_size_bytes': self.stats.total_size_bytes
        }
        
        logger.info(f"Cache cleanup completed: {cleanup_stats}")
        return cleanup_stats
    
    def get_stats(self) -> Dict[str, Any]:
        """Get cache statistics."""
        self._update_hit_rate()
        
        return {
            'total_entries': self.stats.total_entries,
            'total_size_mb': round(self.stats.total_size_bytes / (1024 * 1024), 2),
            'hit_count': self.stats.hit_count,
            'miss_count': self.stats.miss_count,
            'eviction_count': self.stats.eviction_count,
            'hit_rate': round(self.stats.cache_hit_rate * 100, 2),
            'last_cleanup': datetime.fromtimestamp(self.stats.last_cleanup).isoformat(),
            'cache_dir': str(self.cache_dir),
            'max_size_mb': round(self.max_size_bytes / (1024 * 1024), 2)
        }
    
    def clear(self) -> int:
        """
        Clear all cache entries.
        
        Returns:
            Number of entries cleared
        """
        entry_count = len(self.metadata)
        
        # Remove all cache files
        for cache_file in self.cache_dir.glob("*.json"):
            if cache_file.name not in ["cache_metadata.json", "cache_stats.json"]:
                cache_file.unlink()
        
        # Clear metadata
        self.metadata.clear()
        self._save_metadata()
        
        # Reset stats
        self.stats = CacheStats(0, 0, 0, 0, 0, time.time(), 0.0)
        self._save_stats()
        
        logger.info(f"Cache cleared: {entry_count} entries removed")
        return entry_count
    
    def list_entries(self, cache_type: Optional[str] = None) -> List[Dict[str, Any]]:
        """
        List cache entries.
        
        Args:
            cache_type: Filter by cache type
            
        Returns:
            List of cache entry information
        """
        entries = []
        
        for key, entry_data in self.metadata.items():
            entry = CacheEntry(**entry_data)
            
            if cache_type and entry.cache_type != cache_type:
                continue
            
            entries.append({
                'key': key,
                'type': entry.cache_type,
                'size_bytes': entry.size_bytes,
                'created_at': datetime.fromtimestamp(entry.timestamp).isoformat(),
                'expires_at': datetime.fromtimestamp(entry.expires_at).isoformat(),
                'is_valid': self._is_cache_valid(entry),
                'metadata': entry.metadata
            })
        
        return sorted(entries, key=lambda x: x['created_at'], reverse=True)


# Convenience functions for common cache operations
def get_scan_cache_key(config_path: str, scan_path: str) -> str:
    """Generate cache key for scan results."""
    return f"scan_results_{hashlib.sha256(f'{config_path}:{scan_path}'.encode()).hexdigest()[:16]}"


def get_rule_cache_key(rule_file: str, semgrep_version: str) -> str:
    """Generate cache key for rule compilation."""
    return f"rule_compilation_{hashlib.sha256(f'{rule_file}:{semgrep_version}'.encode()).hexdigest()[:16]}"


def get_config_cache_key(config_file: str) -> str:
    """Generate cache key for configuration validation."""
    return f"config_validation_{hashlib.sha256(config_file.encode()).hexdigest()[:16]}"


# Global cache manager instance
_cache_manager: Optional[CacheManager] = None


def get_cache_manager() -> CacheManager:
    """Get the global cache manager instance."""
    global _cache_manager
    if _cache_manager is None:
        _cache_manager = CacheManager()
    return _cache_manager


def cache_scan_results(config_path: str, scan_path: str, results: Dict[str, Any]) -> bool:
    """Cache scan results."""
    cache_manager = get_cache_manager()
    return cache_manager.set('scan_results', results, config_path, scan_path)


def get_cached_scan_results(config_path: str, scan_path: str) -> Optional[Dict[str, Any]]:
    """Get cached scan results."""
    cache_manager = get_cache_manager()
    return cache_manager.get('scan_results', config_path, scan_path)


def cache_rule_compilation(rule_file: str, semgrep_version: str, compilation_data: Dict[str, Any]) -> bool:
    """Cache rule compilation data."""
    cache_manager = get_cache_manager()
    return cache_manager.set('rule_compilation', compilation_data, rule_file, semgrep_version)


def get_cached_rule_compilation(rule_file: str, semgrep_version: str) -> Optional[Dict[str, Any]]:
    """Get cached rule compilation data."""
    cache_manager = get_cache_manager()
    return cache_manager.get('rule_compilation', rule_file, semgrep_version)


def cache_config_validation(config_file: str, validation_data: Dict[str, Any]) -> bool:
    """Cache configuration validation results."""
    cache_manager = get_cache_manager()
    return cache_manager.set('config_validation', validation_data, config_file)


def get_cached_config_validation(config_file: str) -> Optional[Dict[str, Any]]:
    """Get cached configuration validation results."""
    cache_manager = get_cache_manager()
    return cache_manager.get('config_validation', config_file)


if __name__ == "__main__":
    # Example usage and testing
    import argparse
    
    parser = argparse.ArgumentParser(description="WordPress Semgrep Cache Manager")
    parser.add_argument("--stats", action="store_true", help="Show cache statistics")
    parser.add_argument("--cleanup", action="store_true", help="Clean up expired entries")
    parser.add_argument("--clear", action="store_true", help="Clear all cache entries")
    parser.add_argument("--list", action="store_true", help="List cache entries")
    parser.add_argument("--type", help="Filter by cache type")
    
    args = parser.parse_args()
    
    cache_manager = get_cache_manager()
    
    if args.stats:
        stats = cache_manager.get_stats()
        print("Cache Statistics:")
        for key, value in stats.items():
            print(f"  {key}: {value}")
    
    elif args.cleanup:
        cleanup_stats = cache_manager.cleanup()
        print("Cleanup completed:")
        for key, value in cleanup_stats.items():
            print(f"  {key}: {value}")
    
    elif args.clear:
        count = cache_manager.clear()
        print(f"Cleared {count} cache entries")
    
    elif args.list:
        entries = cache_manager.list_entries(args.type)
        print(f"Cache Entries ({len(entries)}):")
        for entry in entries:
            print(f"  {entry['key']} ({entry['type']}) - {entry['size_bytes']} bytes")
            print(f"    Created: {entry['created_at']}")
            print(f"    Expires: {entry['expires_at']}")
            print(f"    Valid: {entry['is_valid']}")
    
    else:
        parser.print_help()
