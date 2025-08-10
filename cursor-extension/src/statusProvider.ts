import * as vscode from 'vscode';

export class StatusProvider implements vscode.TreeDataProvider<StatusItem> {
    private _onDidChangeTreeData: vscode.EventEmitter<StatusItem | undefined | null | void> = new vscode.EventEmitter<StatusItem | undefined | null | void>();
    readonly onDidChangeTreeData: vscode.Event<StatusItem | undefined | null | void> = this._onDidChangeTreeData.event;

    private statusItems: StatusItem[] = [
        new StatusItem('🔒 WordPress Semgrep Security', vscode.TreeItemCollapsibleState.None, 'status'),
        new StatusItem('📦 Semgrep: Checking...', vscode.TreeItemCollapsibleState.None, 'semgrep'),
        new StatusItem('⚙️ Configuration: Default', vscode.TreeItemCollapsibleState.None, 'config'),
        new StatusItem('🔄 Auto-scan: Enabled', vscode.TreeItemCollapsibleState.None, 'autoscan'),
        new StatusItem('👁️ Inline Diagnostics: Enabled', vscode.TreeItemCollapsibleState.None, 'inline'),
        new StatusItem('🔧 Quick Fixes: Enabled', vscode.TreeItemCollapsibleState.None, 'quickfix')
    ];

    constructor() {
        this.updateStatus('Initializing...', 'info');
    }

    updateStatus(message: string, type: 'success' | 'error' | 'warning' | 'info' = 'info'): void {
        const statusItem = this.statusItems.find(item => item.contextValue === 'status');
        if (statusItem) {
            statusItem.label = `🔒 WordPress Semgrep Security - ${message}`;
            statusItem.iconPath = this.getStatusIcon(type);
        }
        this._onDidChangeTreeData.fire();
    }

    updateSemgrepStatus(status: string, type: 'success' | 'error' | 'warning' | 'info' = 'info'): void {
        const semgrepItem = this.statusItems.find(item => item.contextValue === 'semgrep');
        if (semgrepItem) {
            semgrepItem.label = `📦 Semgrep: ${status}`;
            semgrepItem.iconPath = this.getStatusIcon(type);
        }
        this._onDidChangeTreeData.fire();
    }

    updateConfigStatus(config: string): void {
        const configItem = this.statusItems.find(item => item.contextValue === 'config');
        if (configItem) {
            configItem.label = `⚙️ Configuration: ${config}`;
        }
        this._onDidChangeTreeData.fire();
    }

    updateAutoScanStatus(enabled: boolean): void {
        const autoscanItem = this.statusItems.find(item => item.contextValue === 'autoscan');
        if (autoscanItem) {
            autoscanItem.label = `🔄 Auto-scan: ${enabled ? 'Enabled' : 'Disabled'}`;
            autoscanItem.iconPath = enabled ? new vscode.ThemeIcon('check') : new vscode.ThemeIcon('circle-outline');
        }
        this._onDidChangeTreeData.fire();
    }

    updateInlineStatus(enabled: boolean): void {
        const inlineItem = this.statusItems.find(item => item.contextValue === 'inline');
        if (inlineItem) {
            inlineItem.label = `👁️ Inline Diagnostics: ${enabled ? 'Enabled' : 'Disabled'}`;
            inlineItem.iconPath = enabled ? new vscode.ThemeIcon('check') : new vscode.ThemeIcon('circle-outline');
        }
        this._onDidChangeTreeData.fire();
    }

    updateQuickFixStatus(enabled: boolean): void {
        const quickfixItem = this.statusItems.find(item => item.contextValue === 'quickfix');
        if (quickfixItem) {
            quickfixItem.label = `🔧 Quick Fixes: ${enabled ? 'Enabled' : 'Disabled'}`;
            quickfixItem.iconPath = enabled ? new vscode.ThemeIcon('check') : new vscode.ThemeIcon('circle-outline');
        }
        this._onDidChangeTreeData.fire();
    }

    private getStatusIcon(type: 'success' | 'error' | 'warning' | 'info'): vscode.ThemeIcon {
        switch (type) {
            case 'success':
                return new vscode.ThemeIcon('check');
            case 'error':
                return new vscode.ThemeIcon('error');
            case 'warning':
                return new vscode.ThemeIcon('warning');
            case 'info':
            default:
                return new vscode.ThemeIcon('info');
        }
    }

    getTreeItem(element: StatusItem): vscode.TreeItem {
        return element;
    }

    getChildren(element?: StatusItem): Thenable<StatusItem[]> {
        if (!element) {
            return Promise.resolve(this.statusItems);
        }
        return Promise.resolve([]);
    }

    refresh(): void {
        this._onDidChangeTreeData.fire();
    }
}

export class StatusItem extends vscode.TreeItem {
    constructor(
        public readonly label: string,
        public readonly collapsibleState: vscode.TreeItemCollapsibleState,
        public readonly contextValue?: string
    ) {
        super(label, collapsibleState);
        this.contextValue = contextValue;
    }
}
