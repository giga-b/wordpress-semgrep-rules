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
exports.SemgrepInstaller = void 0;
const cp = __importStar(require("child_process"));
const os = __importStar(require("os"));
class SemgrepInstaller {
    async isInstalled() {
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
    async install() {
        const platform = os.platform();
        try {
            switch (platform) {
                case 'win32':
                    await this.installOnWindows();
                    break;
                case 'darwin':
                    await this.installOnMacOS();
                    break;
                case 'linux':
                    await this.installOnLinux();
                    break;
                default:
                    throw new Error(`Unsupported platform: ${platform}`);
            }
        }
        catch (error) {
            throw new Error(`Failed to install Semgrep: ${error}`);
        }
    }
    async installOnWindows() {
        return new Promise((resolve, reject) => {
            // Use PowerShell to install Semgrep
            const command = 'powershell';
            const args = [
                '-Command',
                'pip install semgrep'
            ];
            const child = cp.spawn(command, args, {
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
                if (code === 0) {
                    resolve();
                }
                else {
                    reject(new Error(`Installation failed with code ${code}: ${stderr}`));
                }
            });
            child.on('error', (error) => {
                reject(new Error(`Failed to run installation command: ${error.message}`));
            });
        });
    }
    async installOnMacOS() {
        return new Promise((resolve, reject) => {
            // Use Homebrew to install Semgrep
            const command = 'brew';
            const args = ['install', 'semgrep'];
            const child = cp.spawn(command, args, {
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
                if (code === 0) {
                    resolve();
                }
                else {
                    // Fallback to pip if Homebrew fails
                    this.installWithPip().then(resolve).catch(reject);
                }
            });
            child.on('error', (error) => {
                // Fallback to pip if Homebrew is not available
                this.installWithPip().then(resolve).catch(reject);
            });
        });
    }
    async installOnLinux() {
        return new Promise((resolve, reject) => {
            // Try to install using package manager first
            const command = 'apt-get';
            const args = ['install', '-y', 'semgrep'];
            const child = cp.spawn(command, args, {
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
                if (code === 0) {
                    resolve();
                }
                else {
                    // Fallback to pip if package manager fails
                    this.installWithPip().then(resolve).catch(reject);
                }
            });
            child.on('error', (error) => {
                // Fallback to pip if package manager is not available
                this.installWithPip().then(resolve).catch(reject);
            });
        });
    }
    async installWithPip() {
        return new Promise((resolve, reject) => {
            const command = 'pip3';
            const args = ['install', 'semgrep'];
            const child = cp.spawn(command, args, {
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
                if (code === 0) {
                    resolve();
                }
                else {
                    reject(new Error(`Pip installation failed with code ${code}: ${stderr}`));
                }
            });
            child.on('error', (error) => {
                reject(new Error(`Failed to run pip: ${error.message}`));
            });
        });
    }
    async getVersion() {
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
    async checkInstallation() {
        try {
            const installed = await this.isInstalled();
            if (installed) {
                const version = await this.getVersion();
                return { installed: true, version };
            }
            else {
                return { installed: false, error: 'Semgrep is not installed' };
            }
        }
        catch (error) {
            return { installed: false, error: error instanceof Error ? error.message : 'Unknown error' };
        }
    }
}
exports.SemgrepInstaller = SemgrepInstaller;
//# sourceMappingURL=semgrepInstaller.js.map