import * as vscode from 'vscode';
import * as cp from 'child_process';
import * as os from 'os';
import * as path from 'path';

export class SemgrepInstaller {
    private readonly installScripts = {
        windows: 'https://semgrep.dev/install.ps1',
        unix: 'https://semgrep.dev/install.sh'
    };

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
        const isWindows = platform === 'win32';

        try {
            if (isWindows) {
                await this.installWindows();
            } else {
                await this.installUnix();
            }
        } catch (error) {
            throw new Error(`Failed to install Semgrep: ${error}`);
        }
    }

    private async installWindows(): Promise<void> {
        return new Promise((resolve, reject) => {
            const powershell = cp.spawn('powershell', [
                '-ExecutionPolicy', 'Bypass',
                '-Command', `Invoke-WebRequest -Uri "${this.installScripts.windows}" -OutFile "install-semgrep.ps1"; .\\install-semgrep.ps1`
            ], {
                stdio: ['pipe', 'pipe', 'pipe']
            });

            let stdout = '';
            let stderr = '';

            powershell.stdout.on('data', (data) => {
                stdout += data.toString();
            });

            powershell.stderr.on('data', (data) => {
                stderr += data.toString();
            });

            powershell.on('close', (code) => {
                if (code === 0) {
                    resolve();
                } else {
                    reject(new Error(`Installation failed with code ${code}: ${stderr}`));
                }
            });

            powershell.on('error', (error) => {
                reject(new Error(`Failed to run PowerShell: ${error.message}`));
            });
        });
    }

    private async installUnix(): Promise<void> {
        return new Promise((resolve, reject) => {
            const curl = cp.spawn('curl', [
                '-fsSL', this.installScripts.unix, '|', 'sh'
            ], {
                stdio: ['pipe', 'pipe', 'pipe'],
                shell: true
            });

            let stdout = '';
            let stderr = '';

            curl.stdout.on('data', (data) => {
                stdout += data.toString();
            });

            curl.stderr.on('data', (data) => {
                stderr += data.toString();
            });

            curl.on('close', (code) => {
                if (code === 0) {
                    resolve();
                } else {
                    reject(new Error(`Installation failed with code ${code}: ${stderr}`));
                }
            });

            curl.on('error', (error) => {
                reject(new Error(`Failed to run curl: ${error.message}`));
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

    async update(): Promise<void> {
        try {
            const child = cp.spawn('semgrep', ['--update'], {
                stdio: ['pipe', 'pipe', 'pipe']
            });

            return new Promise((resolve, reject) => {
                child.on('close', (code) => {
                    if (code === 0) {
                        resolve();
                    } else {
                        reject(new Error(`Update failed with code ${code}`));
                    }
                });

                child.on('error', (error) => {
                    reject(new Error(`Failed to update Semgrep: ${error.message}`));
                });
            });
        } catch (error) {
            throw new Error(`Failed to update Semgrep: ${error}`);
        }
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
