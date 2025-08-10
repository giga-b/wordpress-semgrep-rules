"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.getCachedConfigValidation = exports.cacheConfigValidation = exports.getCachedRuleCompilation = exports.cacheRuleCompilation = exports.getCachedScanResults = exports.cacheScanResults = exports.getCacheManager = exports.CacheManager = void 0;
const fs = __importStar(require("fs"));
const path = __importStar(require("path"));
const crypto = __importStar(require("crypto"));
class CacheManager {
    constructor(cacheDir, maxSizeMB = 500) {
        this.metadata = new Map();
        this.maxSizeBytes = maxSizeMB * 1024 * 1024;
        this.cacheDir = cacheDir || path.join(process.env.TEMP || '/tmp', 'wordpress-semgrep-cache');
        this.metadataFile = path.join(this.cacheDir, 'cache_metadata.json');
        this.statsFile = path.join(this.cacheDir, 'cache_stats.json');
        // Cache TTL in milliseconds
        this.cacheTTL = {
            'scan_results': 24 * 60 * 60 * 1000,
            'rule_compilation': 7 * 24 * 60 * 60 * 1000,
            'config_validation': 60 * 60 * 1000,
            'performance_data': 24 * 60 * 60 * 1000,
            'test_results': 12 * 60 * 60 * 1000, // 12 hours
        };
        // Initialize cache
        this.ensureCacheDir();
        this.loadMetadata();
        this.loadStats();
    }
    ensureCacheDir() {
        if (!fs.existsSync(this.cacheDir)) {
            fs.mkdirSync(this.cacheDir, { recursive: true });
        }
    }
    loadMetadata() {
        try {
            if (fs.existsSync(this.metadataFile)) {
                const data = fs.readFileSync(this.metadataFile, 'utf8');
                const metadata = JSON.parse(data);
                this.metadata = new Map(Object.entries(metadata));
            }
        }
        catch (error) {
            console.error('Failed to load cache metadata:', error);
            this.metadata = new Map();
        }
    }
    saveMetadata() {
        try {
            const metadata = Object.fromEntries(this.metadata);
            fs.writeFileSync(this.metadataFile, JSON.stringify(metadata, null, 2));
        }
        catch (error) {
            console.error('Failed to save cache metadata:', error);
        }
    }
    loadStats() {
        try {
            if (fs.existsSync(this.statsFile)) {
                const data = fs.readFileSync(this.statsFile, 'utf8');
                this.stats = JSON.parse(data);
            }
            else {
                this.stats = {
                    totalEntries: 0,
                    totalSizeBytes: 0,
                    hitCount: 0,
                    missCount: 0,
                    evictionCount: 0,
                    lastCleanup: Date.now(),
                    cacheHitRate: 0
                };
            }
        }
        catch (error) {
            console.error('Failed to load cache stats:', error);
            this.stats = {
                totalEntries: 0,
                totalSizeBytes: 0,
                hitCount: 0,
                missCount: 0,
                evictionCount: 0,
                lastCleanup: Date.now(),
                cacheHitRate: 0
            };
        }
    }
    saveStats() {
        try {
            fs.writeFileSync(this.statsFile, JSON.stringify(this.stats, null, 2));
        }
        catch (error) {
            console.error('Failed to save cache stats:', error);
        }
    }
    generateCacheKey(cacheType, ...args) {
        const keyData = `${cacheType}:${args.join(':')}`;
        const hash = crypto.createHash('sha256').update(keyData).digest('hex');
        return `${cacheType}_${hash.substring(0, 16)}`;
    }
    getCacheFilePath(key) {
        return path.join(this.cacheDir, `${key}.json`);
    }
    isCacheValid(entry) {
        return Date.now() < entry.expiresAt;
    }
    calculateEntrySize(data) {
        try {
            return Buffer.byteLength(JSON.stringify(data), 'utf8');
        }
        catch {
            return 0;
        }
    }
    async get(cacheType, ...args) {
        const key = this.generateCacheKey(cacheType, ...args);
        if (!this.metadata.has(key)) {
            this.stats.missCount++;
            this.saveStats();
            return null;
        }
        const entry = this.metadata.get(key);
        const cacheFile = this.getCacheFilePath(key);
        if (!fs.existsSync(cacheFile)) {
            this.metadata.delete(key);
            this.stats.missCount++;
            this.saveStats();
            return null;
        }
        if (!this.isCacheValid(entry)) {
            this.removeEntry(key);
            this.stats.missCount++;
            this.saveStats();
            return null;
        }
        try {
            const data = fs.readFileSync(cacheFile, 'utf8');
            const result = JSON.parse(data);
            this.stats.hitCount++;
            this.updateHitRate();
            this.saveStats();
            console.debug(`Cache hit: ${key}`);
            return result;
        }
        catch (error) {
            console.error(`Failed to load cache entry ${key}:`, error);
            this.removeEntry(key);
            this.stats.missCount++;
            this.saveStats();
            return null;
        }
    }
    async set(cacheType, data, ttl, ...args) {
        const key = this.generateCacheKey(cacheType, ...args);
        const cacheTTL = ttl || this.cacheTTL[cacheType] || 3600000; // Default 1 hour
        const entry = {
            key,
            data: null,
            timestamp: Date.now(),
            expiresAt: Date.now() + cacheTTL,
            sizeBytes: this.calculateEntrySize(data),
            cacheType,
            metadata: {
                args,
                createdAt: new Date().toISOString()
            }
        };
        if (!this.ensureCacheSpace(entry.sizeBytes)) {
            console.warn(`Cache full, cannot store entry: ${key}`);
            return false;
        }
        const cacheFile = this.getCacheFilePath(key);
        try {
            fs.writeFileSync(cacheFile, JSON.stringify(data));
            this.metadata.set(key, entry);
            this.saveMetadata();
            this.stats.totalEntries++;
            this.stats.totalSizeBytes += entry.sizeBytes;
            this.saveStats();
            console.debug(`Cache set: ${key} (${entry.sizeBytes} bytes)`);
            return true;
        }
        catch (error) {
            console.error(`Failed to save cache entry ${key}:`, error);
            return false;
        }
    }
    ensureCacheSpace(requiredBytes) {
        const currentSize = this.stats.totalSizeBytes;
        const availableSpace = this.maxSizeBytes - currentSize;
        if (availableSpace >= requiredBytes) {
            return true;
        }
        console.info(`Cache full, evicting entries to make space for ${requiredBytes} bytes`);
        // Sort entries by timestamp (LRU)
        const sortedEntries = Array.from(this.metadata.entries())
            .sort(([, a], [, b]) => a.timestamp - b.timestamp);
        let freedSpace = 0;
        for (const [key, entry] of sortedEntries) {
            if (freedSpace >= requiredBytes) {
                break;
            }
            freedSpace += entry.sizeBytes;
            this.removeEntry(key);
        }
        return freedSpace >= requiredBytes;
    }
    removeEntry(key) {
        const entry = this.metadata.get(key);
        if (!entry)
            return;
        const cacheFile = this.getCacheFilePath(key);
        if (fs.existsSync(cacheFile)) {
            fs.unlinkSync(cacheFile);
        }
        this.stats.totalEntries--;
        this.stats.totalSizeBytes -= entry.sizeBytes;
        this.stats.evictionCount++;
        this.metadata.delete(key);
        this.saveMetadata();
        this.saveStats();
    }
    updateHitRate() {
        const totalRequests = this.stats.hitCount + this.stats.missCount;
        if (totalRequests > 0) {
            this.stats.cacheHitRate = this.stats.hitCount / totalRequests;
        }
    }
    invalidate(cacheType, pattern) {
        const keysToRemove = [];
        for (const [key, entry] of this.metadata.entries()) {
            if (cacheType && entry.cacheType !== cacheType) {
                continue;
            }
            if (pattern && !key.includes(pattern)) {
                continue;
            }
            keysToRemove.push(key);
        }
        for (const key of keysToRemove) {
            this.removeEntry(key);
        }
        console.info(`Invalidated ${keysToRemove.length} cache entries`);
        return keysToRemove.length;
    }
    cleanup() {
        let expiredCount = 0;
        let freedBytes = 0;
        const keysToRemove = [];
        for (const [key, entry] of this.metadata.entries()) {
            if (!this.isCacheValid(entry)) {
                keysToRemove.push(key);
                expiredCount++;
                freedBytes += entry.sizeBytes;
            }
        }
        for (const key of keysToRemove) {
            this.removeEntry(key);
        }
        this.stats.lastCleanup = Date.now();
        this.saveStats();
        const cleanupStats = {
            expiredEntries: expiredCount,
            freedBytes,
            remainingEntries: this.stats.totalEntries,
            cacheSizeBytes: this.stats.totalSizeBytes
        };
        console.info('Cache cleanup completed:', cleanupStats);
        return cleanupStats;
    }
    getStats() {
        this.updateHitRate();
        return {
            totalEntries: this.stats.totalEntries,
            totalSizeMB: Math.round(this.stats.totalSizeBytes / (1024 * 1024) * 100) / 100,
            hitCount: this.stats.hitCount,
            missCount: this.stats.missCount,
            evictionCount: this.stats.evictionCount,
            hitRate: Math.round(this.stats.cacheHitRate * 100 * 100) / 100,
            lastCleanup: new Date(this.stats.lastCleanup).toISOString(),
            cacheDir: this.cacheDir,
            maxSizeMB: Math.round(this.maxSizeBytes / (1024 * 1024) * 100) / 100
        };
    }
    clear() {
        const entryCount = this.metadata.size;
        // Remove all cache files
        const files = fs.readdirSync(this.cacheDir);
        for (const file of files) {
            if (file.endsWith('.json') && !['cache_metadata.json', 'cache_stats.json'].includes(file)) {
                fs.unlinkSync(path.join(this.cacheDir, file));
            }
        }
        // Clear metadata
        this.metadata.clear();
        this.saveMetadata();
        // Reset stats
        this.stats = {
            totalEntries: 0,
            totalSizeBytes: 0,
            hitCount: 0,
            missCount: 0,
            evictionCount: 0,
            lastCleanup: Date.now(),
            cacheHitRate: 0
        };
        this.saveStats();
        console.info(`Cache cleared: ${entryCount} entries removed`);
        return entryCount;
    }
    listEntries(cacheType) {
        const entries = [];
        for (const [key, entry] of this.metadata.entries()) {
            if (cacheType && entry.cacheType !== cacheType) {
                continue;
            }
            entries.push({
                key,
                type: entry.cacheType,
                sizeBytes: entry.sizeBytes,
                createdAt: new Date(entry.timestamp).toISOString(),
                expiresAt: new Date(entry.expiresAt).toISOString(),
                isValid: this.isCacheValid(entry),
                metadata: entry.metadata
            });
        }
        return entries.sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime());
    }
}
exports.CacheManager = CacheManager;
// Global cache manager instance
let globalCacheManager = null;
function getCacheManager() {
    if (!globalCacheManager) {
        globalCacheManager = new CacheManager();
    }
    return globalCacheManager;
}
exports.getCacheManager = getCacheManager;
// Convenience functions for common cache operations
async function cacheScanResults(configPath, scanPath, results) {
    const cacheManager = getCacheManager();
    return cacheManager.set('scan_results', results, undefined, configPath, scanPath);
}
exports.cacheScanResults = cacheScanResults;
async function getCachedScanResults(configPath, scanPath) {
    const cacheManager = getCacheManager();
    return cacheManager.get('scan_results', configPath, scanPath);
}
exports.getCachedScanResults = getCachedScanResults;
async function cacheRuleCompilation(ruleFile, semgrepVersion, compilationData) {
    const cacheManager = getCacheManager();
    return cacheManager.set('rule_compilation', compilationData, undefined, ruleFile, semgrepVersion);
}
exports.cacheRuleCompilation = cacheRuleCompilation;
async function getCachedRuleCompilation(ruleFile, semgrepVersion) {
    const cacheManager = getCacheManager();
    return cacheManager.get('rule_compilation', ruleFile, semgrepVersion);
}
exports.getCachedRuleCompilation = getCachedRuleCompilation;
async function cacheConfigValidation(configFile, validationData) {
    const cacheManager = getCacheManager();
    return cacheManager.set('config_validation', validationData, undefined, configFile);
}
exports.cacheConfigValidation = cacheConfigValidation;
async function getCachedConfigValidation(configFile) {
    const cacheManager = getCacheManager();
    return cacheManager.get('config_validation', configFile);
}
exports.getCachedConfigValidation = getCachedConfigValidation;
//# sourceMappingURL=cacheManager.js.map