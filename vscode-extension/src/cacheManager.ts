import * as vscode from 'vscode';
import * as fs from 'fs';
import * as path from 'path';
import * as crypto from 'crypto';

export interface CacheEntry<T = any> {
    key: string;
    data: T;
    timestamp: number;
    expiresAt: number;
    sizeBytes: number;
    cacheType: string;
    metadata: Record<string, any>;
}

export interface CacheStats {
    totalEntries: number;
    totalSizeBytes: number;
    hitCount: number;
    missCount: number;
    evictionCount: number;
    lastCleanup: number;
    cacheHitRate: number;
}

export class CacheManager {
    private cacheDir: string;
    private metadataFile: string;
    private statsFile: string;
    private metadata: Map<string, CacheEntry> = new Map();
    private stats!: CacheStats;
    private readonly maxSizeBytes: number;
    private readonly cacheTTL: Record<string, number>;

    constructor(cacheDir?: string, maxSizeMB: number = 500) {
        this.maxSizeBytes = maxSizeMB * 1024 * 1024;
        this.cacheDir = cacheDir || path.join(process.env.TEMP || '/tmp', 'wordpress-semgrep-cache');
        this.metadataFile = path.join(this.cacheDir, 'cache_metadata.json');
        this.statsFile = path.join(this.cacheDir, 'cache_stats.json');

        // Cache TTL in milliseconds
        this.cacheTTL = {
            'scan_results': 24 * 60 * 60 * 1000,      // 24 hours
            'rule_compilation': 7 * 24 * 60 * 60 * 1000, // 7 days
            'config_validation': 60 * 60 * 1000,      // 1 hour
            'performance_data': 24 * 60 * 60 * 1000,  // 24 hours
            'test_results': 12 * 60 * 60 * 1000,      // 12 hours
        };

        // Initialize cache
        this.ensureCacheDir();
        this.loadMetadata();
        this.loadStats();
    }

    private ensureCacheDir(): void {
        if (!fs.existsSync(this.cacheDir)) {
            fs.mkdirSync(this.cacheDir, { recursive: true });
        }
    }

    private loadMetadata(): void {
        try {
            if (fs.existsSync(this.metadataFile)) {
                const data = fs.readFileSync(this.metadataFile, 'utf8');
                const metadata = JSON.parse(data);
                this.metadata = new Map(Object.entries(metadata));
            }
        } catch (error) {
            console.error('Failed to load cache metadata:', error);
            this.metadata = new Map();
        }
    }

    private saveMetadata(): void {
        try {
            const metadata = Object.fromEntries(this.metadata);
            fs.writeFileSync(this.metadataFile, JSON.stringify(metadata, null, 2));
        } catch (error) {
            console.error('Failed to save cache metadata:', error);
        }
    }

    private loadStats(): void {
        try {
            if (fs.existsSync(this.statsFile)) {
                const data = fs.readFileSync(this.statsFile, 'utf8');
                this.stats = JSON.parse(data);
            } else {
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
        } catch (error) {
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

    private saveStats(): void {
        try {
            fs.writeFileSync(this.statsFile, JSON.stringify(this.stats, null, 2));
        } catch (error) {
            console.error('Failed to save cache stats:', error);
        }
    }

    private generateCacheKey(cacheType: string, ...args: any[]): string {
        const keyData = `${cacheType}:${args.join(':')}`;
        const hash = crypto.createHash('sha256').update(keyData).digest('hex');
        return `${cacheType}_${hash.substring(0, 16)}`;
    }

    private getCacheFilePath(key: string): string {
        return path.join(this.cacheDir, `${key}.json`);
    }

    private isCacheValid(entry: CacheEntry): boolean {
        return Date.now() < entry.expiresAt;
    }

    private calculateEntrySize(data: any): number {
        try {
            return Buffer.byteLength(JSON.stringify(data), 'utf8');
        } catch {
            return 0;
        }
    }

    async get<T>(cacheType: string, ...args: any[]): Promise<T | null> {
        const key = this.generateCacheKey(cacheType, ...args);

        if (!this.metadata.has(key)) {
            this.stats.missCount++;
            this.saveStats();
            return null;
        }

        const entry = this.metadata.get(key)!;
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
            const result = JSON.parse(data) as T;

            this.stats.hitCount++;
            this.updateHitRate();
            this.saveStats();

            console.debug(`Cache hit: ${key}`);
            return result;
        } catch (error) {
            console.error(`Failed to load cache entry ${key}:`, error);
            this.removeEntry(key);
            this.stats.missCount++;
            this.saveStats();
            return null;
        }
    }

    async set<T>(cacheType: string, data: T, ttl?: number, ...args: any[]): Promise<boolean> {
        const key = this.generateCacheKey(cacheType, ...args);
        const cacheTTL = ttl || this.cacheTTL[cacheType] || 3600000; // Default 1 hour

        const entry: CacheEntry<T> = {
            key,
            data: null as any, // Data stored separately
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
        } catch (error) {
            console.error(`Failed to save cache entry ${key}:`, error);
            return false;
        }
    }

    private ensureCacheSpace(requiredBytes: number): boolean {
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

    private removeEntry(key: string): void {
        const entry = this.metadata.get(key);
        if (!entry) return;

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

    private updateHitRate(): void {
        const totalRequests = this.stats.hitCount + this.stats.missCount;
        if (totalRequests > 0) {
            this.stats.cacheHitRate = this.stats.hitCount / totalRequests;
        }
    }

    invalidate(cacheType?: string, pattern?: string): number {
        const keysToRemove: string[] = [];

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

    cleanup(): { expiredEntries: number; freedBytes: number; remainingEntries: number; cacheSizeBytes: number } {
        let expiredCount = 0;
        let freedBytes = 0;
        const keysToRemove: string[] = [];

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

    getStats(): Record<string, any> {
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

    clear(): number {
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

    listEntries(cacheType?: string): Array<{
        key: string;
        type: string;
        sizeBytes: number;
        createdAt: string;
        expiresAt: string;
        isValid: boolean;
        metadata: Record<string, any>;
    }> {
        const entries: Array<{
            key: string;
            type: string;
            sizeBytes: number;
            createdAt: string;
            expiresAt: string;
            isValid: boolean;
            metadata: Record<string, any>;
        }> = [];

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

// Global cache manager instance
let globalCacheManager: CacheManager | null = null;

export function getCacheManager(): CacheManager {
    if (!globalCacheManager) {
        globalCacheManager = new CacheManager();
    }
    return globalCacheManager;
}

// Convenience functions for common cache operations
export async function cacheScanResults(configPath: string, scanPath: string, results: any): Promise<boolean> {
    const cacheManager = getCacheManager();
    return cacheManager.set('scan_results', results, undefined, configPath, scanPath);
}

export async function getCachedScanResults(configPath: string, scanPath: string): Promise<any | null> {
    const cacheManager = getCacheManager();
    return cacheManager.get('scan_results', configPath, scanPath);
}

export async function cacheRuleCompilation(ruleFile: string, semgrepVersion: string, compilationData: any): Promise<boolean> {
    const cacheManager = getCacheManager();
    return cacheManager.set('rule_compilation', compilationData, undefined, ruleFile, semgrepVersion);
}

export async function getCachedRuleCompilation(ruleFile: string, semgrepVersion: string): Promise<any | null> {
    const cacheManager = getCacheManager();
    return cacheManager.get('rule_compilation', ruleFile, semgrepVersion);
}

export async function cacheConfigValidation(configFile: string, validationData: any): Promise<boolean> {
    const cacheManager = getCacheManager();
    return cacheManager.set('config_validation', validationData, undefined, configFile);
}

export async function getCachedConfigValidation(configFile: string): Promise<any | null> {
    const cacheManager = getCacheManager();
    return cacheManager.get('config_validation', configFile);
}
