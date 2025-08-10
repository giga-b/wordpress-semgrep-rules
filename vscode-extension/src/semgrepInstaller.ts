import * as vscode from 'vscode';
import * as cp from 'child_process';
import * as os from 'os';
import * as path from 'path';

export class SemgrepInstaller {
    async isInstalled(): Promise<boolean> {
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

    async install(): Promise<void> {
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
        } catch (error) {
            throw new Error(`Failed to install Semgrep: ${error}`);
        }
    }

    private async installOnWindows(): Promise<void> {
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
                } else {
                    reject(new Error(`Installation failed with code ${code}: ${stderr}`));
                }
            });

            child.on('error', (error) => {
                reject(new Error(`Failed to run installation command: ${error.message}`));
            });
        });
    }

    private async installOnMacOS(): Promise<void> {
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
                } else {
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

    private async installOnLinux(): Promise<void> {
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
                } else {
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

    private async installWithPip(): Promise<void> {
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
                } else {
                    reject(new Error(`Pip installation failed with code ${code}: ${stderr}`));
                }
            });

            child.on('error', (error) => {
                reject(new Error(`Failed to run pip: ${error.message}`));
            });
        });
    }

    async getVersion(): Promise<string> {
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

    async checkInstallation(): Promise<{ installed: boolean; version?: string; error?: string }> {
        try {
            const installed = await this.isInstalled();
            if (installed) {
                const version = await this.getVersion();
                return { installed: true, version };
            } else {
                return { installed: false, error: 'Semgrep is not installed' };
            }
        } catch (error) {
            return { installed: false, error: error instanceof Error ? error.message : 'Unknown error' };
        }
    }
}
