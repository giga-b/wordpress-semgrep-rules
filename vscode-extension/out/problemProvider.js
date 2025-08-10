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
exports.ProblemItem = exports.ProblemProvider = void 0;
const vscode = __importStar(require("vscode"));
class ProblemProvider {
    constructor() {
        this._onDidChangeTreeData = new vscode.EventEmitter();
        this.onDidChangeTreeData = this._onDidChangeTreeData.event;
        this.problems = [];
        this.rootItem = new ProblemItem('No security issues found', vscode.TreeItemCollapsibleState.None);
    }
    updateProblems(problems) {
        this.problems = problems;
        this._onDidChangeTreeData.fire();
    }
    getRoot() {
        return this.rootItem;
    }
    getTreeItem(element) {
        return element;
    }
    getChildren(element) {
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
            return Promise.resolve(fileProblems.map(p => new ProblemItem(`${p.extra.severity}: ${p.extra.message}`, vscode.TreeItemCollapsibleState.None, p, 'problem')));
        }
        return Promise.resolve([]);
    }
    groupProblemsByFile() {
        const fileMap = new Map();
        for (const problem of this.problems) {
            const filePath = problem.path;
            if (!fileMap.has(filePath)) {
                fileMap.set(filePath, []);
            }
            fileMap.get(filePath).push(problem);
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
    getSeverityCounts(problems) {
        const counts = { error: 0, warning: 0, info: 0 };
        for (const problem of problems) {
            const severity = problem.extra.severity.toLowerCase();
            if (severity in counts) {
                counts[severity]++;
            }
        }
        return counts;
    }
    formatSeverityCounts(counts) {
        const parts = [];
        if (counts.error > 0)
            parts.push(`${counts.error} error(s)`);
        if (counts.warning > 0)
            parts.push(`${counts.warning} warning(s)`);
        if (counts.info > 0)
            parts.push(`${counts.info} info`);
        return parts.join(', ');
    }
    getFileName(filePath) {
        const parts = filePath.split(/[\\/]/);
        return parts[parts.length - 1];
    }
    refresh() {
        this._onDidChangeTreeData.fire();
    }
}
exports.ProblemProvider = ProblemProvider;
class ProblemItem extends vscode.TreeItem {
    constructor(label, collapsibleState, problem, contextValue) {
        super(label, collapsibleState);
        this.label = label;
        this.collapsibleState = collapsibleState;
        this.problem = problem;
        this.contextValue = contextValue;
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
                        selection: new vscode.Range(problem.start.line - 1, problem.start.col - 1, problem.end.line - 1, problem.end.col - 1)
                    }
                ]
            };
        }
    }
    createTooltip(problem) {
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
    getIconPath(severity) {
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
exports.ProblemItem = ProblemItem;
//# sourceMappingURL=problemProvider.js.map