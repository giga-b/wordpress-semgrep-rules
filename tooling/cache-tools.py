#!/usr/bin/env python3
"""
WordPress Semgrep Rules - Cache Management Tools

Command-line utilities for managing the WordPress Semgrep cache system.
Provides tools for cache inspection, cleanup, and management.

Usage:
    python cache-tools.py --stats                    # Show cache statistics
    python cache-tools.py --cleanup                  # Clean up expired entries
    python cache-tools.py --clear                    # Clear all cache
    python cache-tools.py --list                     # List cache entries
    python cache-tools.py --invalidate scan_results  # Invalidate specific cache type
    python cache-tools.py --analyze                  # Analyze cache performance
    python cache-tools.py --optimize                 # Optimize cache settings

Author: WordPress Semgrep Rules Team
License: MIT
"""

import argparse
import json
import sys
from pathlib import Path
from typing import Dict, Any, List, Optional
import tempfile
import shutil
from datetime import datetime, timedelta

# Add the tooling directory to the path to import cache_manager
sys.path.insert(0, str(Path(__file__).parent))
from cache_manager import CacheManager, get_cache_manager


class CacheTools:
    """Command-line tools for cache management."""
    
    def __init__(self):
        self.cache_manager = get_cache_manager()
    
    def show_stats(self, verbose: bool = False) -> None:
        """Display cache statistics."""
        stats = self.cache_manager.get_stats()
        
        print("📊 WordPress Semgrep Cache Statistics")
        print("=" * 50)
        
        # Basic stats
        print(f"Total Entries: {stats['total_entries']}")
        print(f"Cache Size: {stats['total_size_mb']} MB")
        print(f"Max Size: {stats['max_size_mb']} MB")
        print(f"Hit Rate: {stats['hit_rate']}%")
        print(f"Hit Count: {stats['hit_count']}")
        print(f"Miss Count: {stats['miss_count']}")
        print(f"Eviction Count: {stats['eviction_count']}")
        print(f"Last Cleanup: {stats['last_cleanup']}")
        print(f"Cache Directory: {stats['cache_dir']}")
        
        if verbose:
            print("\n📈 Performance Analysis:")
            total_requests = stats['hit_count'] + stats['miss_count']
            if total_requests > 0:
                efficiency = (stats['hit_count'] / total_requests) * 100
                print(f"Cache Efficiency: {efficiency:.2f}%")
                
                if efficiency < 50:
                    print("⚠️  Low cache efficiency - consider adjusting TTL settings")
                elif efficiency > 80:
                    print("✅ High cache efficiency")
                
                # Size analysis
                size_usage = (stats['total_size_mb'] / stats['max_size_mb']) * 100
                print(f"Size Usage: {size_usage:.2f}%")
                
                if size_usage > 80:
                    print("⚠️  Cache nearly full - consider increasing max size or cleanup")
                elif size_usage < 20:
                    print("ℹ️  Cache underutilized - consider reducing max size")
    
    def cleanup_cache(self, dry_run: bool = False) -> None:
        """Clean up expired cache entries."""
        if dry_run:
            print("🧹 Dry run - analyzing expired entries...")
            entries = self.cache_manager.list_entries()
            expired = [e for e in entries if not e['is_valid']]
            
            if expired:
                print(f"Found {len(expired)} expired entries:")
                total_size = sum(e['sizeBytes'] for e in expired)
                print(f"Total size to free: {total_size / (1024 * 1024):.2f} MB")
                
                for entry in expired[:5]:  # Show first 5
                    print(f"  - {entry['key']} ({entry['type']}) - {entry['sizeBytes']} bytes")
                if len(expired) > 5:
                    print(f"  ... and {len(expired) - 5} more")
            else:
                print("No expired entries found")
        else:
            print("🧹 Cleaning up expired cache entries...")
            cleanup_stats = self.cache_manager.cleanup()
            
            print("Cleanup completed:")
            print(f"  Expired entries removed: {cleanup_stats['expired_entries']}")
            print(f"  Bytes freed: {cleanup_stats['freed_bytes'] / (1024 * 1024):.2f} MB")
            print(f"  Remaining entries: {cleanup_stats['remaining_entries']}")
            print(f"  Current cache size: {cleanup_stats['cache_size_bytes'] / (1024 * 1024):.2f} MB")
    
    def clear_cache(self, confirm: bool = False) -> None:
        """Clear all cache entries."""
        if not confirm:
            print("⚠️  This will clear ALL cache entries!")
            response = input("Are you sure? (yes/no): ")
            if response.lower() not in ['yes', 'y']:
                print("Cache clear cancelled")
                return
        
        print("🗑️  Clearing all cache entries...")
        count = self.cache_manager.clear()
        print(f"✅ Cleared {count} cache entries")
    
    def list_entries(self, cache_type: Optional[str] = None, limit: int = 20) -> None:
        """List cache entries."""
        entries = self.cache_manager.list_entries(cache_type)
        
        if not entries:
            print("No cache entries found")
            return
        
        print(f"📋 Cache Entries ({len(entries)} total):")
        if cache_type:
            print(f"Filtered by type: {cache_type}")
        print("=" * 80)
        
        # Group by type
        by_type = {}
        for entry in entries:
            entry_type = entry['type']
            if entry_type not in by_type:
                by_type[entry_type] = []
            by_type[entry_type].append(entry)
        
        for entry_type, type_entries in by_type.items():
            print(f"\n📁 {entry_type.upper()} ({len(type_entries)} entries):")
            
            for entry in type_entries[:limit]:
                size_mb = entry['sizeBytes'] / (1024 * 1024)
                status = "✅" if entry['is_valid'] else "❌"
                print(f"  {status} {entry['key']}")
                print(f"     Size: {size_mb:.2f} MB | Created: {entry['createdAt']}")
                print(f"     Expires: {entry['expiresAt']}")
            
            if len(type_entries) > limit:
                print(f"     ... and {len(type_entries) - limit} more entries")
    
    def invalidate_cache(self, cache_type: str, pattern: Optional[str] = None) -> None:
        """Invalidate cache entries."""
        print(f"🔄 Invalidating cache entries...")
        if pattern:
            print(f"Type: {cache_type}, Pattern: {pattern}")
        else:
            print(f"Type: {cache_type}")
        
        count = self.cache_manager.invalidate(cache_type, pattern)
        print(f"✅ Invalidated {count} cache entries")
    
    def analyze_performance(self) -> None:
        """Analyze cache performance and provide recommendations."""
        print("🔍 Cache Performance Analysis")
        print("=" * 50)
        
        stats = self.cache_manager.get_stats()
        entries = self.cache_manager.list_entries()
        
        # Calculate metrics
        total_requests = stats['hit_count'] + stats['miss_count']
        hit_rate = stats['hit_rate']
        size_usage = (stats['total_size_mb'] / stats['max_size_mb']) * 100
        
        # Entry age analysis
        now = datetime.now()
        ages = []
        for entry in entries:
            created = datetime.fromisoformat(entry['createdAt'].replace('Z', '+00:00'))
            age = (now - created).total_seconds() / 3600  # hours
            ages.append(age)
        
        avg_age = sum(ages) / len(ages) if ages else 0
        
        print(f"Cache Hit Rate: {hit_rate:.2f}%")
        print(f"Size Utilization: {size_usage:.2f}%")
        print(f"Average Entry Age: {avg_age:.1f} hours")
        print(f"Total Requests: {total_requests}")
        
        print("\n📊 Recommendations:")
        
        if hit_rate < 50:
            print("⚠️  Low hit rate detected:")
            print("   - Consider increasing TTL for frequently accessed data")
            print("   - Review cache key generation strategy")
            print("   - Check if cache invalidation is too aggressive")
        
        if size_usage > 80:
            print("⚠️  High cache utilization:")
            print("   - Consider increasing max cache size")
            print("   - Review cache eviction policy")
            print("   - Clean up expired entries more frequently")
        
        if avg_age > 24:
            print("ℹ️  Old cache entries detected:")
            print("   - Consider reducing TTL for time-sensitive data")
            print("   - Review if cache is being properly invalidated")
        
        if stats['eviction_count'] > 0:
            print("⚠️  Cache evictions detected:")
            print("   - Consider increasing max cache size")
            print("   - Review cache entry sizes")
            print("   - Optimize data serialization")
        
        # Type analysis
        by_type = {}
        for entry in entries:
            entry_type = entry['type']
            if entry_type not in by_type:
                by_type[entry_type] = {'count': 0, 'size': 0}
            by_type[entry_type]['count'] += 1
            by_type[entry_type]['size'] += entry['sizeBytes']
        
        print(f"\n📁 Cache Type Analysis:")
        for entry_type, data in by_type.items():
            size_mb = data['size'] / (1024 * 1024)
            print(f"   {entry_type}: {data['count']} entries, {size_mb:.2f} MB")
    
    def optimize_cache(self) -> None:
        """Optimize cache settings based on analysis."""
        print("⚡ Cache Optimization")
        print("=" * 50)
        
        stats = self.cache_manager.get_stats()
        entries = self.cache_manager.list_entries()
        
        # Analyze current state
        hit_rate = stats['hit_rate']
        size_usage = (stats['total_size_mb'] / stats['max_size_mb']) * 100
        
        print("Current State:")
        print(f"  Hit Rate: {hit_rate:.2f}%")
        print(f"  Size Usage: {size_usage:.2f}%")
        print(f"  Max Size: {stats['max_size_mb']} MB")
        
        print("\nOptimization Recommendations:")
        
        # Size optimization
        if size_usage > 90:
            new_size = int(stats['max_size_mb'] * 1.5)
            print(f"📈 Increase max cache size to {new_size} MB")
        elif size_usage < 30:
            new_size = int(stats['max_size_mb'] * 0.7)
            print(f"📉 Reduce max cache size to {new_size} MB")
        
        # TTL optimization based on hit rate
        if hit_rate < 40:
            print("⏰ Increase TTL for scan_results (currently 24h)")
            print("   Recommended: 48-72 hours")
        elif hit_rate > 80:
            print("⏰ Consider reducing TTL for scan_results")
            print("   Recommended: 12-18 hours")
        
        # Cleanup frequency
        if stats['eviction_count'] > 0:
            print("🧹 Enable automatic cache cleanup")
            print("   Recommended: Run cleanup every 6-12 hours")
        
        print("\nManual Optimization Steps:")
        print("1. Run cleanup: python cache-tools.py --cleanup")
        print("2. Monitor performance: python cache-tools.py --analyze")
        print("3. Adjust settings based on recommendations")
    
    def export_cache_info(self, output_file: str) -> None:
        """Export cache information to JSON file."""
        print(f"📤 Exporting cache information to {output_file}...")
        
        stats = self.cache_manager.get_stats()
        entries = self.cache_manager.list_entries()
        
        export_data = {
            'exported_at': datetime.now().isoformat(),
            'stats': stats,
            'entries': entries,
            'summary': {
                'total_entries': len(entries),
                'valid_entries': len([e for e in entries if e['is_valid']]),
                'expired_entries': len([e for e in entries if not e['is_valid']]),
                'total_size_mb': stats['total_size_mb'],
                'hit_rate': stats['hit_rate']
            }
        }
        
        with open(output_file, 'w') as f:
            json.dump(export_data, f, indent=2)
        
        print(f"✅ Cache information exported to {output_file}")


def main():
    """Main command-line interface."""
    parser = argparse.ArgumentParser(
        description="WordPress Semgrep Cache Management Tools",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  python cache-tools.py --stats                    # Show cache statistics
  python cache-tools.py --cleanup                  # Clean up expired entries
  python cache-tools.py --clear                    # Clear all cache
  python cache-tools.py --list                     # List all entries
  python cache-tools.py --list --type scan_results # List scan result entries
  python cache-tools.py --invalidate scan_results  # Invalidate scan results
  python cache-tools.py --analyze                  # Analyze performance
  python cache-tools.py --optimize                 # Get optimization tips
  python cache-tools.py --export cache-report.json # Export cache info
        """
    )
    
    # Action arguments
    parser.add_argument('--stats', action='store_true', help='Show cache statistics')
    parser.add_argument('--cleanup', action='store_true', help='Clean up expired entries')
    parser.add_argument('--clear', action='store_true', help='Clear all cache entries')
    parser.add_argument('--list', action='store_true', help='List cache entries')
    parser.add_argument('--invalidate', metavar='TYPE', help='Invalidate cache entries by type')
    parser.add_argument('--analyze', action='store_true', help='Analyze cache performance')
    parser.add_argument('--optimize', action='store_true', help='Get optimization recommendations')
    parser.add_argument('--export', metavar='FILE', help='Export cache information to JSON file')
    
    # Options
    parser.add_argument('--verbose', '-v', action='store_true', help='Verbose output')
    parser.add_argument('--dry-run', action='store_true', help='Dry run for cleanup (show what would be done)')
    parser.add_argument('--confirm', action='store_true', help='Skip confirmation prompts')
    parser.add_argument('--type', help='Filter by cache type (for list command)')
    parser.add_argument('--limit', type=int, default=20, help='Limit number of entries shown (default: 20)')
    parser.add_argument('--pattern', help='Pattern for invalidation (with --invalidate)')
    
    args = parser.parse_args()
    
    # Check if any action is specified
    if not any([args.stats, args.cleanup, args.clear, args.list, args.invalidate, 
                args.analyze, args.optimize, args.export]):
        parser.print_help()
        return
    
    try:
        tools = CacheTools()
        
        if args.stats:
            tools.show_stats(args.verbose)
        
        elif args.cleanup:
            tools.cleanup_cache(args.dry_run)
        
        elif args.clear:
            tools.clear_cache(args.confirm)
        
        elif args.list:
            tools.list_entries(args.type, args.limit)
        
        elif args.invalidate:
            tools.invalidate_cache(args.invalidate, args.pattern)
        
        elif args.analyze:
            tools.analyze_performance()
        
        elif args.optimize:
            tools.optimize_cache()
        
        elif args.export:
            tools.export_cache_info(args.export)
    
    except KeyboardInterrupt:
        print("\n❌ Operation cancelled by user")
        sys.exit(1)
    except Exception as e:
        print(f"❌ Error: {e}")
        if args.verbose:
            import traceback
            traceback.print_exc()
        sys.exit(1)


if __name__ == "__main__":
    main()
