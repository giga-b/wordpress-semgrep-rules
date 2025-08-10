import * as vscode from 'vscode';

export class StatusProvider implements vscode.TreeDataProvider<StatusItem> {
    private _onDidChangeTreeData: vscode.EventEmitter<StatusItem | undefined | null | void> = new vscode.EventEmitter<StatusItem | undefined | null | void>();
    readonly onDidChangeTreeData: vscode.Event<StatusItem | undefined | null | void> = this._onDidChangeTreeData.event;

    private status: string = 'Ready';
    private statusType: 'success' | 'warning' | 'error' | 'info' = 'info';
    private lastScanTime: string = 'Never';
    private totalIssues: number = 0;
    private semgrepVersion: string = 'Unknown';

    updateStatus(status: string, type: 'success' | 'warning' | 'error' | 'info' = 'info'): void {
        this.status = status;
        this.statusType = type;
        this._onDidChangeTreeData.fire();
    }

    updateScanInfo(issues: number, semgrepVersion?: string): void {
        this.totalIssues = issues;
        this.lastScanTime = new Date().toLocaleTimeString();
        if (semgrepVersion) {
            this.semgrepVersion = semgrepVersion;
        }
        this._onDidChangeTreeData.fire();
    }

    getTreeItem(element: StatusItem): vscode.TreeItem {
        return element;
    }

    getChildren(element?: StatusItem): Thenable<StatusItem[]> {
        if (!element) {
            // Root level - show status items
            return Promise.resolve([
                new StatusItem('Status', this.status, this.statusType, 'status'),
                new StatusItem('Last Scan', this.lastScanTime, 'info', 'scan'),
                new StatusItem('Total Issues', this.totalIssues.toString(), 'info', 'issues'),
                new StatusItem('Semgrep Version', this.semgrepVersion, 'info', 'version')
            ]);
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
        public readonly value: string,
        public readonly type: 'success' | 'warning' | 'error' | 'info',
        public readonly contextValue?: string
    ) {
        super(`${label}: ${value}`, vscode.TreeItemCollapsibleState.None);
        
        this.description = value;
        this.iconPath = this.getIconPath(type);
        this.tooltip = `${label}: ${value}`;
    }

    private getIconPath(type: string): vscode.ThemeIcon {
        switch (type) {
            case 'success':
                return new vscode.ThemeIcon('check');
            case 'warning':
                return new vscode.ThemeIcon('warning');
            case 'error':
                return new vscode.ThemeIcon('error');
            case 'info':
            default:
                return new vscode.ThemeIcon('info');
        }
    }
}
