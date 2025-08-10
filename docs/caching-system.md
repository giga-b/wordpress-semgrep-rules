# WordPress Semgrep Rules - Caching System

## Overview

The WordPress Semgrep Rules project includes a comprehensive caching system designed to improve performance by storing scan results, rule compilation data, and other frequently accessed information. This system reduces scan times and improves the overall developer experience.

## Features

### Core Capabilities

- **Persistent File-based Caching**: Cache data is stored on disk for persistence across sessions
- **Automatic Cache Invalidation**: Entries expire based on configurable TTL (Time To Live)
- **LRU Eviction Policy**: Least Recently Used entries are evicted when cache is full
- **Cache Statistics**: Comprehensive metrics and performance monitoring
- **Cross-platform Support**: Works on Windows, macOS, and Linux
- **Multiple Cache Types**: Different TTL settings for different types of data

### Cache Types

| Cache Type | TTL | Description |
|------------|-----|-------------|
| `scan_results` | 24 hours | Semgrep scan results for files/directories |
| `rule_compilation` | 7 days | Compiled rule data and metadata |
| `config_validation` | 1 hour | Configuration file validation results |
| `performance_data` | 24 hours | Performance metrics and benchmarks |
| `test_results` | 12 hours | Test execution results |

## Architecture

### Cache Manager

The core caching system is implemented in `tooling/cache_manager.py` and provides:

- **CacheEntry**: Represents a single cache entry with metadata
- **CacheStats**: Tracks cache performance metrics
- **CacheManager**: Main class for cache operations

### Storage Structure

```
wordpress-semgrep-cache/
├── cache_metadata.json    # Cache entry metadata
├── cache_stats.json       # Performance statistics
├── scan_results_*.json    # Scan result cache files
├── rule_compilation_*.json # Rule compilation cache files
└── config_validation_*.json # Config validation cache files
```

### Cache Key Generation

Cache keys are generated using SHA256 hashing of:
- Cache type
- Configuration path
- Target path or other identifying parameters

Example: `scan_results_a1b2c3d4e5f6g7h8.json`

## Usage

### Python API

```python
from tooling.cache_manager import get_cache_manager

# Get cache manager instance
cache_manager = get_cache_manager()

# Store data in cache
success = cache_manager.set('scan_results', scan_data, ttl=None, config_path, file_path)

# Retrieve data from cache
cached_data = cache_manager.get('scan_results', config_path, file_path)

# Invalidate specific cache type
count = cache_manager.invalidate('scan_results')

# Get cache statistics
stats = cache_manager.get_stats()

# Clean up expired entries
cleanup_stats = cache_manager.cleanup()
```

### Convenience Functions

```python
from tooling.cache_manager import (
    cache_scan_results,
    get_cached_scan_results,
    cache_rule_compilation,
    get_cached_rule_compilation
)

# Cache scan results
cache_scan_results(config_path, scan_path, results)

# Get cached scan results
results = get_cached_scan_results(config_path, scan_path)
```

### Command Line Tools

The `tooling/cache-tools.py` script provides command-line access to cache management:

```bash
# Show cache statistics
python tooling/cache-tools.py --stats

# Clean up expired entries
python tooling/cache-tools.py --cleanup

# Clear all cache
python tooling/cache-tools.py --clear

# List cache entries
python tooling/cache-tools.py --list

# Analyze cache performance
python tooling/cache-tools.py --analyze

# Get optimization recommendations
python tooling/cache-tools.py --optimize

# Export cache information
python tooling/cache-tools.py --export cache-report.json
```

### IDE Extensions

Both VS Code and Cursor extensions include integrated caching:

```typescript
// VS Code/Cursor Extension API
const scanner = new SemgrepScanner(configManager);

// Scan with caching (default)
const results = await scanner.scanFile(filePath, true);

// Scan without caching
const results = await scanner.scanFile(filePath, false);

// Cache management
scanner.clearCache();
scanner.clearCacheForFile(filePath);
const stats = scanner.getCacheStats();
const cleanup = scanner.cleanupCache();
```

## Configuration

### Cache Settings

The cache system can be configured through the CacheManager constructor:

```python
# Custom cache directory and size
cache_manager = CacheManager(
    cache_dir="/custom/cache/path",
    max_size_mb=1000  # 1GB max cache size
)
```

### TTL Configuration

Time To Live settings can be customized:

```python
# Override default TTL for specific operations
cache_manager.set('scan_results', data, ttl=3600)  # 1 hour
cache_manager.set('scan_results', data, ttl=86400)  # 24 hours
```

### Environment Variables

- `TEMP`: Cache directory (defaults to system temp directory)
- `WORDPRESS_SEMGREP_CACHE_DIR`: Custom cache directory
- `WORDPRESS_SEMGREP_CACHE_SIZE`: Max cache size in MB

## Performance Optimization

### Cache Hit Rate

Monitor cache hit rate to optimize performance:

```bash
python tooling/cache-tools.py --stats --verbose
```

**Target Hit Rates:**
- **>80%**: Excellent - cache is working well
- **50-80%**: Good - consider adjusting TTL
- **<50%**: Poor - review cache strategy

### Size Management

Monitor cache size utilization:

```bash
python tooling/cache-tools.py --analyze
```

**Size Optimization:**
- **>90%**: Increase max cache size
- **<30%**: Consider reducing max cache size
- **Regular cleanup**: Run cleanup every 6-12 hours

### TTL Optimization

Adjust TTL based on usage patterns:

```python
# For frequently changing code
cache_manager.set('scan_results', data, ttl=1800)  # 30 minutes

# For stable codebases
cache_manager.set('scan_results', data, ttl=86400)  # 24 hours
```

## Best Practices

### 1. Cache Strategy

- **Scan Results**: Cache for 24 hours (default) or adjust based on code change frequency
- **Rule Compilation**: Cache for 7 days (rules change infrequently)
- **Config Validation**: Cache for 1 hour (configs may change during development)

### 2. Cache Invalidation

```python
# Invalidate when rules change
cache_manager.invalidate('rule_compilation')

# Invalidate when config changes
cache_manager.invalidate('config_validation')

# Invalidate specific file patterns
cache_manager.invalidate('scan_results', 'vulnerable-examples')
```

### 3. Monitoring

Regular monitoring helps optimize cache performance:

```bash
# Daily monitoring
python tooling/cache-tools.py --stats

# Weekly analysis
python tooling/cache-tools.py --analyze

# Monthly optimization
python tooling/cache-tools.py --optimize
```

### 4. Cleanup Schedule

Set up automated cleanup:

```bash
# Add to crontab for daily cleanup
0 2 * * * cd /path/to/wordpress-semgrep-rules && python tooling/cache-tools.py --cleanup

# Add to CI/CD pipeline
python tooling/cache-tools.py --cleanup --confirm
```

## Troubleshooting

### Common Issues

#### Low Cache Hit Rate

**Symptoms:**
- Hit rate < 50%
- Frequent cache misses

**Solutions:**
1. Increase TTL for frequently accessed data
2. Review cache key generation
3. Check for aggressive cache invalidation

#### Cache Full

**Symptoms:**
- Cache size > 90% of max
- Frequent evictions

**Solutions:**
1. Increase max cache size
2. Run cleanup more frequently
3. Optimize data serialization

#### Cache Corruption

**Symptoms:**
- JSON parsing errors
- Missing cache files

**Solutions:**
1. Clear cache: `python tooling/cache-tools.py --clear`
2. Check disk space
3. Verify file permissions

### Debug Mode

Enable verbose logging for debugging:

```bash
python tooling/cache-tools.py --stats --verbose
python tooling/cache-tools.py --analyze --verbose
```

### Cache Export

Export cache information for analysis:

```bash
python tooling/cache-tools.py --export cache-debug.json
```

## Integration

### CI/CD Integration

Add cache management to CI/CD pipelines:

```yaml
# GitHub Actions example
- name: Cache cleanup
  run: python tooling/cache-tools.py --cleanup --confirm

- name: Cache analysis
  run: python tooling/cache-tools.py --analyze

- name: Export cache report
  run: python tooling/cache-tools.py --export cache-report.json
```

### IDE Integration

The caching system is automatically integrated into VS Code and Cursor extensions:

- **Automatic caching**: Scan results are cached by default
- **Cache management**: Built-in commands for cache operations
- **Performance monitoring**: Cache statistics in status bar

### Pre-commit Hooks

Add cache cleanup to pre-commit hooks:

```bash
# .pre-commit-config.yaml
- repo: local
  hooks:
    - id: cache-cleanup
      name: Clean WordPress Semgrep Cache
      entry: python tooling/cache-tools.py --cleanup --confirm
      language: system
```

## Metrics and Monitoring

### Key Metrics

- **Hit Rate**: Percentage of cache hits vs misses
- **Cache Size**: Current cache size in MB
- **Eviction Count**: Number of entries evicted due to size limits
- **Entry Age**: Average age of cache entries

### Performance Impact

Typical performance improvements:

- **First Scan**: No improvement (cache miss)
- **Subsequent Scans**: 70-90% faster (cache hit)
- **Rule Compilation**: 80-95% faster (cached rules)
- **Config Validation**: 90-99% faster (cached validation)

### Monitoring Dashboard

Create a monitoring dashboard using exported cache data:

```python
# Export cache data for monitoring
python tooling/cache-tools.py --export cache-metrics.json

# Process metrics for dashboard
import json
with open('cache-metrics.json') as f:
    data = json.load(f)
    hit_rate = data['stats']['hit_rate']
    cache_size = data['stats']['total_size_mb']
    # Send to monitoring system
```

## Future Enhancements

### Planned Features

1. **Distributed Caching**: Share cache across multiple machines
2. **Compression**: Compress cache data to reduce storage
3. **Cache Warming**: Pre-populate cache with frequently accessed data
4. **Advanced Analytics**: Detailed performance analytics and recommendations
5. **Cloud Integration**: Store cache in cloud storage for team sharing

### Performance Targets

- **Cache Hit Rate**: >85% for typical workflows
- **Cache Size**: <500MB for most projects
- **Scan Time Reduction**: >80% for cached scans
- **Memory Usage**: <100MB for cache manager

## Conclusion

The WordPress Semgrep caching system provides significant performance improvements while maintaining data integrity and providing comprehensive monitoring capabilities. By following best practices and regularly monitoring cache performance, developers can achieve optimal scanning performance for their WordPress security analysis workflows.

For more information, see:
- [Cache Manager API](tooling/cache_manager.py)
- [Cache Tools CLI](tooling/cache-tools.py)
- [Performance Optimization](docs/performance-optimization.md)
- [IDE Integration](docs/VS-CODE-INTEGRATION.md)
