import * as vscode from 'vscode';
import * as cp from 'child_process';
import * as path from 'path';
import { ConfigurationManager } from './configurationManager';
import { getCacheManager, cacheScanResults, getCachedScanResults } from './cacheManager';

export interface SemgrepResult {
    check_id: string;
    path: string;
    start: {
        line: number;
        col: number;
    };
    end: {
        line: number;
        col: number;
    };
    message: string;
    severity: 'ERROR' | 'WARNING' | 'INFO';
    fix?: string;
    metadata?: {
        category?: string;
        cwe?: string;
        references?: string[];
    };
}

export interface SemgrepOutput {
    results: SemgrepResult[];
    errors: any[];
}

export class SemgrepScanner {
    private configManager: ConfigurationManager;
    private cacheManager = getCacheManager();

    constructor(configManager: ConfigurationManager) {
        this.configManager = configManager;
    }

    async scanFile(filePath: string, useCache: boolean = true): Promise<SemgrepResult[]> {
        // Check cache first
        if (useCache) {
            const config = this.getScanConfig();
            const cachedResults = await this.cacheManager.get<SemgrepResult[]>('scan_results', config.configPath || 'default', filePath);
            if (cachedResults) {
                return cachedResults;
            }
        }

        const config = this.getScanConfig();
        const args = this.buildScanArgs(filePath, config);
        
        try {
            const output = await this.runSemgrep(args);
            const results = this.filterResults(output.results);
            
            // Cache results
            await this.cacheManager.set('scan_results', results, undefined, config.configPath || 'default', filePath);
            
            return results;
        } catch (error) {
            console.error('Semgrep scan failed:', error);
            throw new Error(`Semgrep scan failed: ${error}`);
        }
    }

    async scanWorkspace(workspacePath: string): Promise<SemgrepResult[]> {
        const config = this.getScanConfig();
        const args = this.buildScanArgs(workspacePath, config);
        
        try {
            const output = await this.runSemgrep(args);
            return this.filterResults(output.results);
        } catch (error) {
            console.error('Semgrep workspace scan failed:', error);
            throw new Error(`Semgrep workspace scan failed: ${error}`);
        }
    }

    async scanIncremental(changedFiles: string[]): Promise<Map<string, SemgrepResult[]>> {
        const results = new Map<string, SemgrepResult[]>();
        
        // Scan only changed files
        for (const filePath of changedFiles) {
            try {
                const fileResults = await this.scanFile(filePath, false); // Don't use cache for incremental
                results.set(filePath, fileResults);
            } catch (error) {
                console.error(`Failed to scan ${filePath}:`, error);
                results.set(filePath, []);
            }
        }
        
        return results;
    }

    private getScanConfig() {
        return {
            configPath: this.configManager.getConfigPath(),
            rulesPath: this.configManager.getRulesPath(),
            severity: this.configManager.getSeverity(),
            timeout: this.configManager.getTimeout(),
            maxProblems: this.configManager.getMaxProblems()
        };
    }

    private buildScanArgs(targetPath: string, config: any): string[] {
        const args = ['--json', '--quiet', '--no-git-ignore'];

        // Add configuration file if specified
        if (config.configPath) {
            args.push('--config', config.configPath);
        } else {
            // Use default WordPress rules
            args.push('--config', 'p/wordpress');
        }

        // Add custom rules path if specified
        if (config.rulesPath) {
            args.push('--config', config.rulesPath);
        }

        // Add timeout
        if (config.timeout) {
            args.push('--timeout', config.timeout.toString());
        }

        // Add performance optimizations
        args.push('--max-memory', '4096'); // 4GB max memory
        args.push('--jobs', 'auto'); // Use all available cores

        // Add target path
        args.push(targetPath);

        return args;
    }

    private async runSemgrep(args: string[]): Promise<SemgrepOutput> {
        return new Promise((resolve, reject) => {
            const timeout = this.configManager.getTimeout() * 1000;
            
            const child = cp.spawn('semgrep', args, {
                timeout: timeout,
                stdio: ['pipe', 'pipe', 'pipe']
            });

            let stdout = '';
            let stderr = '';

            child.stdout.on('data', (data) => {
                stdout += data.toString();
            });

            child.stderr.on('data', (data) => {
                stderr += data.toString();
            });

            child.on('close', (code) => {
                if (code === 0 || code === 1) { // Semgrep returns 1 when issues are found
                    try {
                        const output = JSON.parse(stdout);
                        resolve(output);
                    } catch (error) {
                        reject(new Error(`Failed to parse Semgrep output: ${error}`));
                    }
                } else {
                    reject(new Error(`Semgrep failed with code ${code}: ${stderr}`));
                }
            });

            child.on('error', (error) => {
                reject(new Error(`Failed to run Semgrep: ${error.message}`));
            });

            child.on('timeout', () => {
                child.kill();
                reject(new Error('Semgrep scan timed out'));
            });
        });
    }

    private filterResults(results: SemgrepResult[]): SemgrepResult[] {
        const severity = this.configManager.getSeverity();
        const maxProblems = this.configManager.getMaxProblems();

        // Filter by severity
        const severityLevels = { 'error': 3, 'warning': 2, 'info': 1 };
        const minSeverity = severityLevels[severity as keyof typeof severityLevels];

        const filtered = results.filter(result => {
            const resultSeverity = severityLevels[result.severity.toLowerCase() as keyof typeof severityLevels] || 1;
            return resultSeverity >= minSeverity;
        });

        // Limit number of results
        return filtered.slice(0, maxProblems);
    }

    async isSemgrepInstalled(): Promise<boolean> {
        return new Promise((resolve) => {
            const child = cp.spawn('semgrep', ['--version'], {
                stdio: ['pipe', 'pipe', 'pipe']
            });

            child.on('close', (code) => {
                resolve(code === 0);
            });

            child.on('error', () => {
                resolve(false);
            });
        });
    }

    async getSemgrepVersion(): Promise<string> {
        return new Promise((resolve, reject) => {
            const child = cp.spawn('semgrep', ['--version'], {
                stdio: ['pipe', 'pipe', 'pipe']
            });

            let stdout = '';

            child.stdout.on('data', (data) => {
                stdout += data.toString();
            });

            child.on('close', (code) => {
                if (code === 0) {
                    resolve(stdout.trim());
                } else {
                    reject(new Error('Failed to get Semgrep version'));
                }
            });

            child.on('error', (error) => {
                reject(new Error(`Failed to run Semgrep: ${error.message}`));
            });
        });
    }

    clearCache(): void {
        this.cacheManager.invalidate('scan_results');
    }

    clearCacheForFile(filePath: string): void {
        // Invalidate cache for specific file pattern
        this.cacheManager.invalidate('scan_results', filePath);
    }

    getCacheStats(): Record<string, any> {
        return this.cacheManager.getStats();
    }

    cleanupCache(): { expiredEntries: number; freedBytes: number; remainingEntries: number; cacheSizeBytes: number } {
        return this.cacheManager.cleanup();
    }
}
