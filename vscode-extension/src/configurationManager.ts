import * as vscode from 'vscode';

export class ConfigurationManager {
    private readonly configSection = 'wordpressSemgrep';

    isEnabled(): boolean {
        return vscode.workspace.getConfiguration(this.configSection).get('enabled', true);
    }

    isAutoScanEnabled(): boolean {
        return vscode.workspace.getConfiguration(this.configSection).get('autoScan', true);
    }

    getConfigPath(): string {
        return vscode.workspace.getConfiguration(this.configSection).get('configPath', '');
    }

    setConfigPath(path: string): void {
        vscode.workspace.getConfiguration(this.configSection).update('configPath', path, vscode.ConfigurationTarget.Workspace);
    }

    getRulesPath(): string {
        return vscode.workspace.getConfiguration(this.configSection).get('rulesPath', '');
    }

    setRulesPath(path: string): void {
        vscode.workspace.getConfiguration(this.configSection).update('rulesPath', path, vscode.ConfigurationTarget.Workspace);
    }

    getSeverity(): string {
        return vscode.workspace.getConfiguration(this.configSection).get('severity', 'warning');
    }

    setSeverity(severity: string): void {
        vscode.workspace.getConfiguration(this.configSection).update('severity', severity, vscode.ConfigurationTarget.Workspace);
    }

    getMaxProblems(): number {
        return vscode.workspace.getConfiguration(this.configSection).get('maxProblems', 100);
    }

    setTimeout(timeout: number): void {
        vscode.workspace.getConfiguration(this.configSection).update('timeout', timeout, vscode.ConfigurationTarget.Workspace);
    }

    getTimeout(): number {
        return vscode.workspace.getConfiguration(this.configSection).get('timeout', 30);
    }

    getAllSettings(): any {
        return {
            enabled: this.isEnabled(),
            autoScan: this.isAutoScanEnabled(),
            configPath: this.getConfigPath(),
            rulesPath: this.getRulesPath(),
            severity: this.getSeverity(),
            maxProblems: this.getMaxProblems(),
            timeout: this.getTimeout()
        };
    }

    async showConfigurationDialog(): Promise<void> {
        const settings = this.getAllSettings();
        
        const quickPick = vscode.window.createQuickPick();
        quickPick.items = [
            { label: 'Enable/Disable Extension', description: `Currently: ${settings.enabled ? 'Enabled' : 'Disabled'}` },
            { label: 'Auto-scan on Save', description: `Currently: ${settings.autoScan ? 'Enabled' : 'Disabled'}` },
            { label: 'Configure Rules Path', description: settings.rulesPath || 'Not set' },
            { label: 'Set Severity Level', description: `Currently: ${settings.severity}` },
            { label: 'Set Max Problems', description: `Currently: ${settings.maxProblems}` },
            { label: 'Set Timeout', description: `Currently: ${settings.timeout}s` }
        ];

        quickPick.onDidChangeSelection(async (selection) => {
            if (selection.length === 0) return;

            const item = selection[0];
            
            switch (item.label) {
                case 'Enable/Disable Extension':
                    const enabled = await vscode.window.showQuickPick(['Enable', 'Disable'], {
                        placeHolder: 'Select option'
                    });
                    if (enabled) {
                        vscode.workspace.getConfiguration(this.configSection).update('enabled', enabled === 'Enable', vscode.ConfigurationTarget.Workspace);
                    }
                    break;

                case 'Auto-scan on Save':
                    const autoScan = await vscode.window.showQuickPick(['Enable', 'Disable'], {
                        placeHolder: 'Select option'
                    });
                    if (autoScan) {
                        vscode.workspace.getConfiguration(this.configSection).update('autoScan', autoScan === 'Enable', vscode.ConfigurationTarget.Workspace);
                    }
                    break;

                case 'Configure Rules Path':
                    const rulesPath = await vscode.window.showInputBox({
                        prompt: 'Enter path to custom rules directory',
                        value: settings.rulesPath,
                        placeHolder: '/path/to/rules'
                    });
                    if (rulesPath !== undefined) {
                        this.setRulesPath(rulesPath);
                    }
                    break;

                case 'Set Severity Level':
                    const severity = await vscode.window.showQuickPick(['error', 'warning', 'info'], {
                        placeHolder: 'Select severity level'
                    });
                    if (severity) {
                        this.setSeverity(severity);
                    }
                    break;

                case 'Set Max Problems':
                    const maxProblems = await vscode.window.showInputBox({
                        prompt: 'Enter maximum number of problems to display',
                        value: settings.maxProblems.toString(),
                        validateInput: (value) => {
                            const num = parseInt(value);
                            return isNaN(num) || num < 1 ? 'Please enter a valid number greater than 0' : null;
                        }
                    });
                    if (maxProblems) {
                        vscode.workspace.getConfiguration(this.configSection).update('maxProblems', parseInt(maxProblems), vscode.ConfigurationTarget.Workspace);
                    }
                    break;

                case 'Set Timeout':
                    const timeout = await vscode.window.showInputBox({
                        prompt: 'Enter timeout in seconds',
                        value: settings.timeout.toString(),
                        validateInput: (value) => {
                            const num = parseInt(value);
                            return isNaN(num) || num < 1 ? 'Please enter a valid number greater than 0' : null;
                        }
                    });
                    if (timeout) {
                        this.setTimeout(parseInt(timeout));
                    }
                    break;
            }
            
            quickPick.hide();
        });

        quickPick.show();
    }
}
