import * as vscode from 'vscode';
import { SemgrepResult } from './semgrepScanner';

export class ProblemProvider implements vscode.TreeDataProvider<ProblemItem> {
    private _onDidChangeTreeData: vscode.EventEmitter<ProblemItem | undefined | null | void> = new vscode.EventEmitter<ProblemItem | undefined | null | void>();
    readonly onDidChangeTreeData: vscode.Event<ProblemItem | undefined | null | void> = this._onDidChangeTreeData.event;

    private problems: SemgrepResult[] = [];
    private rootItem: ProblemItem;

    constructor() {
        this.rootItem = new ProblemItem('No security issues found', vscode.TreeItemCollapsibleState.None);
    }

    updateProblems(problems: SemgrepResult[]): void {
        this.problems = problems;
        this._onDidChangeTreeData.fire();
    }

    getRoot(): ProblemItem {
        return this.rootItem;
    }

    getTreeItem(element: ProblemItem): vscode.TreeItem {
        return element;
    }

    getChildren(element?: ProblemItem): Thenable<ProblemItem[]> {
        if (!element) {
            // Root level - show summary or individual problems
            if (this.problems.length === 0) {
                return Promise.resolve([new ProblemItem('No security issues found', vscode.TreeItemCollapsibleState.None)]);
            }

            // Group problems by file
            const fileGroups = this.groupProblemsByFile();
            return Promise.resolve(fileGroups);
        }

        if (element.contextValue === 'file') {
            // Show problems for a specific file
            const fileProblems = this.problems.filter(p => p.path === element.resourceUri?.fsPath);
            return Promise.resolve(fileProblems.map(p => new ProblemItem(
                `${p.extra.severity}: ${p.extra.message}`,
                vscode.TreeItemCollapsibleState.None,
                p,
                'problem'
            )));
        }

        return Promise.resolve([]);
    }

    private groupProblemsByFile(): ProblemItem[] {
        const fileMap = new Map<string, SemgrepResult[]>();
        
        for (const problem of this.problems) {
            const filePath = problem.path;
            if (!fileMap.has(filePath)) {
                fileMap.set(filePath, []);
            }
            fileMap.get(filePath)!.push(problem);
        }

        return Array.from(fileMap.entries()).map(([filePath, problems]) => {
            const severityCounts = this.getSeverityCounts(problems);
            const label = `${this.getFileName(filePath)} (${problems.length} issues)`;
            const item = new ProblemItem(label, vscode.TreeItemCollapsibleState.Collapsed, undefined, 'file');
            item.resourceUri = vscode.Uri.file(filePath);
            item.description = this.formatSeverityCounts(severityCounts);
            item.tooltip = filePath;
            return item;
        });
    }

    private getSeverityCounts(problems: SemgrepResult[]): { [key: string]: number } {
        const counts = { error: 0, warning: 0, info: 0 };
        for (const problem of problems) {
            const severity = problem.extra.severity.toLowerCase();
            if (severity in counts) {
                counts[severity as keyof typeof counts]++;
            }
        }
        return counts;
    }

    private formatSeverityCounts(counts: { [key: string]: number }): string {
        const parts = [];
        if (counts.error > 0) parts.push(`${counts.error} error(s)`);
        if (counts.warning > 0) parts.push(`${counts.warning} warning(s)`);
        if (counts.info > 0) parts.push(`${counts.info} info`);
        return parts.join(', ');
    }

    private getFileName(filePath: string): string {
        const parts = filePath.split(/[\\/]/);
        return parts[parts.length - 1];
    }

    refresh(): void {
        this._onDidChangeTreeData.fire();
    }
}

export class ProblemItem extends vscode.TreeItem {
    constructor(
        public readonly label: string,
        public readonly collapsibleState: vscode.TreeItemCollapsibleState,
        public readonly problem?: SemgrepResult,
        public readonly contextValue?: string
    ) {
        super(label, collapsibleState);

        if (problem) {
            this.tooltip = this.createTooltip(problem);
            this.description = `Line ${problem.start.line}`;
            this.iconPath = this.getIconPath(problem.extra.severity);
            this.command = {
                command: 'vscode.open',
                title: 'Open File',
                arguments: [
                    vscode.Uri.file(problem.path),
                    {
                        selection: new vscode.Range(
                            problem.start.line - 1,
                            problem.start.col - 1,
                            problem.end.line - 1,
                            problem.end.col - 1
                        )
                    }
                ]
            };
        }
    }

    private createTooltip(problem: SemgrepResult): string {
        let tooltip = `**${problem.extra.severity}**: ${problem.extra.message}\n\n`;
        tooltip += `**File**: ${problem.path}\n`;
        tooltip += `**Line**: ${problem.start.line}\n`;
        tooltip += `**Rule**: ${problem.check_id}\n`;
        
        if (problem.extra.metadata?.category) {
            tooltip += `**Category**: ${problem.extra.metadata.category}\n`;
        }
        
        if (problem.extra.metadata?.cwe) {
            tooltip += `**CWE**: ${problem.extra.metadata.cwe}\n`;
        }
        
        if (problem.extra.metadata?.references && problem.extra.metadata.references.length > 0) {
            tooltip += `**References**:\n${problem.extra.metadata.references.map(ref => `- ${ref}`).join('\n')}`;
        }
        
        return tooltip;
    }

    private getIconPath(severity: string): vscode.ThemeIcon | undefined {
        const iconName = severity.toLowerCase();
        switch (iconName) {
            case 'error':
                return new vscode.ThemeIcon('error');
            case 'warning':
                return new vscode.ThemeIcon('warning');
            case 'info':
                return new vscode.ThemeIcon('info');
            default:
                return new vscode.ThemeIcon('circle-outline');
        }
    }
}
