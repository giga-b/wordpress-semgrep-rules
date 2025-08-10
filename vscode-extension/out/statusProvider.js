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
exports.StatusItem = exports.StatusProvider = void 0;
const vscode = __importStar(require("vscode"));
class StatusProvider {
    constructor() {
        this._onDidChangeTreeData = new vscode.EventEmitter();
        this.onDidChangeTreeData = this._onDidChangeTreeData.event;
        this.status = 'Ready';
        this.statusType = 'info';
        this.lastScanTime = 'Never';
        this.totalIssues = 0;
        this.semgrepVersion = 'Unknown';
    }
    updateStatus(status, type = 'info') {
        this.status = status;
        this.statusType = type;
        this._onDidChangeTreeData.fire();
    }
    updateScanInfo(issues, semgrepVersion) {
        this.totalIssues = issues;
        this.lastScanTime = new Date().toLocaleTimeString();
        if (semgrepVersion) {
            this.semgrepVersion = semgrepVersion;
        }
        this._onDidChangeTreeData.fire();
    }
    getTreeItem(element) {
        return element;
    }
    getChildren(element) {
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
    refresh() {
        this._onDidChangeTreeData.fire();
    }
}
exports.StatusProvider = StatusProvider;
class StatusItem extends vscode.TreeItem {
    constructor(label, value, type, contextValue) {
        super(`${label}: ${value}`, vscode.TreeItemCollapsibleState.None);
        this.label = label;
        this.value = value;
        this.type = type;
        this.contextValue = contextValue;
        this.description = value;
        this.iconPath = this.getIconPath(type);
        this.tooltip = `${label}: ${value}`;
    }
    getIconPath(type) {
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
exports.StatusItem = StatusItem;
//# sourceMappingURL=statusProvider.js.map