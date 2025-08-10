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
exports.SemgrepScanner = void 0;
const cp = __importStar(require("child_process"));
const cacheManager_1 = require("./cacheManager");
class SemgrepScanner {
    constructor(configManager) {
        this.cacheManager = (0, cacheManager_1.getCacheManager)();
        this.configManager = configManager;
    }
    async scanFile(filePath, useCache = true) {
        // Check cache first
        if (useCache) {
            const config = this.getScanConfig();
            const cachedResults = await this.cacheManager.get('scan_results', config.configPath || 'default', filePath);
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
        }
        catch (error) {
            console.error('Semgrep scan failed:', error);
            throw new Error(`Semgrep scan failed: ${error}`);
        }
    }
    async scanWorkspace(workspacePath) {
        const config = this.getScanConfig();
        const args = this.buildScanArgs(workspacePath, config);
        try {
            const output = await this.runSemgrep(args);
            return this.filterResults(output.results);
        }
        catch (error) {
            console.error('Semgrep workspace scan failed:', error);
            throw new Error(`Semgrep workspace scan failed: ${error}`);
        }
    }
    getScanConfig() {
        return {
            configPath: this.configManager.getConfigPath(),
            rulesPath: this.configManager.getRulesPath(),
            severity: this.configManager.getSeverity(),
            timeout: this.configManager.getTimeout(),
            maxProblems: this.configManager.getMaxProblems()
        };
    }
    buildScanArgs(targetPath, config) {
        const args = ['--json', '--quiet'];
        // Add configuration file if specified
        if (config.configPath) {
            args.push('--config', config.configPath);
        }
        else {
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
        // Add target path
        args.push(targetPath);
        return args;
    }
    async runSemgrep(args) {
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
                    }
                    catch (error) {
                        reject(new Error(`Failed to parse Semgrep output: ${error}`));
                    }
                }
                else {
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
    filterResults(results) {
        const severity = this.configManager.getSeverity();
        const maxProblems = this.configManager.getMaxProblems();
        // Filter by severity
        const severityLevels = { 'error': 3, 'warning': 2, 'info': 1 };
        const minSeverity = severityLevels[severity];
        const filtered = results.filter(result => {
            const resultSeverity = severityLevels[result.extra.severity.toLowerCase()] || 1;
            return resultSeverity >= minSeverity;
        });
        // Limit number of results
        return filtered.slice(0, maxProblems);
    }
    async isSemgrepInstalled() {
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
    async getSemgrepVersion() {
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
                }
                else {
                    reject(new Error('Failed to get Semgrep version'));
                }
            });
            child.on('error', (error) => {
                reject(new Error(`Failed to run Semgrep: ${error.message}`));
            });
        });
    }
    clearCache() {
        this.cacheManager.invalidate('scan_results');
    }
    clearCacheForFile(filePath) {
        // Invalidate cache for specific file pattern
        this.cacheManager.invalidate('scan_results', filePath);
    }
    getCacheStats() {
        return this.cacheManager.getStats();
    }
    cleanupCache() {
        return this.cacheManager.cleanup();
    }
}
exports.SemgrepScanner = SemgrepScanner;
//# sourceMappingURL=semgrepScanner.js.map