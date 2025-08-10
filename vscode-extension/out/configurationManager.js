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
exports.ConfigurationManager = void 0;
const vscode = __importStar(require("vscode"));
class ConfigurationManager {
    constructor() {
        this.configSection = 'wordpressSemgrep';
    }
    isEnabled() {
        return vscode.workspace.getConfiguration(this.configSection).get('enabled', true);
    }
    isAutoScanEnabled() {
        return vscode.workspace.getConfiguration(this.configSection).get('autoScan', true);
    }
    getConfigPath() {
        return vscode.workspace.getConfiguration(this.configSection).get('configPath', '');
    }
    setConfigPath(path) {
        vscode.workspace.getConfiguration(this.configSection).update('configPath', path, vscode.ConfigurationTarget.Workspace);
    }
    getRulesPath() {
        return vscode.workspace.getConfiguration(this.configSection).get('rulesPath', '');
    }
    setRulesPath(path) {
        vscode.workspace.getConfiguration(this.configSection).update('rulesPath', path, vscode.ConfigurationTarget.Workspace);
    }
    getSeverity() {
        return vscode.workspace.getConfiguration(this.configSection).get('severity', 'warning');
    }
    setSeverity(severity) {
        vscode.workspace.getConfiguration(this.configSection).update('severity', severity, vscode.ConfigurationTarget.Workspace);
    }
    getMaxProblems() {
        return vscode.workspace.getConfiguration(this.configSection).get('maxProblems', 100);
    }
    setTimeout(timeout) {
        vscode.workspace.getConfiguration(this.configSection).update('timeout', timeout, vscode.ConfigurationTarget.Workspace);
    }
    getTimeout() {
        return vscode.workspace.getConfiguration(this.configSection).get('timeout', 30);
    }
    getAllSettings() {
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
    async showConfigurationDialog() {
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
            if (selection.length === 0)
                return;
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
exports.ConfigurationManager = ConfigurationManager;
//# sourceMappingURL=configurationManager.js.map