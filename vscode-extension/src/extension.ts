import * as vscode from 'vscode';
import * as path from 'path';
import * as fs from 'fs';
import { SemgrepScanner } from './semgrepScanner';
import { ProblemProvider } from './problemProvider';
import { StatusProvider } from './statusProvider';
import { ConfigurationManager } from './configurationManager';
import { SemgrepInstaller } from './semgrepInstaller';

// Path validation utilities
function validateFilePath(filePath: string): boolean {
    try {
        // Check for path traversal attempts
        const normalizedPath = path.normalize(filePath);
        if (normalizedPath.includes('..') || normalizedPath.includes('~')) {
            return false;
        }
        
        // Check if file exists and is accessible
        if (!fs.existsSync(filePath)) {
            return false;
        }
        
        // Check if it's a file (not directory)
        const stats = fs.statSync(filePath);
        if (!stats.isFile()) {
            return false;
        }
        
        // Check file extension
        const ext = path.extname(filePath).toLowerCase();
        const allowedExtensions = ['.php', '.inc', '.phtml'];
        if (!allowedExtensions.includes(ext)) {
            return false;
        }
        
        return true;
    } catch (error) {
        console.error('Path validation error:', error);
        return false;
    }
}

function validateWorkspacePath(workspacePath: string): boolean {
    try {
        // Check for path traversal attempts
        const normalizedPath = path.normalize(workspacePath);
        if (normalizedPath.includes('..') || normalizedPath.includes('~')) {
            return false;
        }
        
        // Check if directory exists and is accessible
        if (!fs.existsSync(workspacePath)) {
            return false;
        }
        
        // Check if it's a directory
        const stats = fs.statSync(workspacePath);
        if (!stats.isDirectory()) {
            return false;
        }
        
        return true;
    } catch (error) {
        console.error('Workspace path validation error:', error);
        return false;
    }
}

function sanitizePath(inputPath: string): string {
    // Remove any potentially dangerous characters
    return inputPath.replace(/[<>:"|?*]/g, '');
}

export async function activate(context: vscode.ExtensionContext) {
    console.log('WordPress Semgrep Security extension is now active!');

    // Initialize components
    const configManager = new ConfigurationManager();
    const semgrepInstaller = new SemgrepInstaller();
    const scanner = new SemgrepScanner(configManager);
    const problemProvider = new ProblemProvider();
    const statusProvider = new StatusProvider();

    // Register problem provider
    const problemsView = vscode.window.createTreeView('wordpress-semgrep-problems', {
        treeDataProvider: problemProvider
    });

    // Register status provider
    const statusView = vscode.window.createTreeView('wordpress-semgrep-status', {
        treeDataProvider: statusProvider
    });

    // Register commands
    const scanFileCommand = vscode.commands.registerCommand('wordpress-semgrep.scanFile', async () => {
        const editor = vscode.window.activeTextEditor;
        if (!editor) {
            vscode.window.showWarningMessage('No active editor found');
            return;
        }

        if (editor.document.languageId !== 'php') {
            vscode.window.showWarningMessage('This command only works with PHP files');
            return;
        }

        // Enhanced path validation
        const filePath = editor.document.uri.fsPath;
        const sanitizedPath = sanitizePath(filePath);
        
        if (!validateFilePath(sanitizedPath)) {
            vscode.window.showErrorMessage('Invalid or unsafe file path detected');
            statusProvider.updateStatus('Path validation failed', 'error');
            return;
        }

        try {
            vscode.window.withProgress({
                location: vscode.ProgressLocation.Notification,
                title: 'Scanning file for security issues...',
                cancellable: false
            }, async (progress) => {
                const results = await scanner.scanFile(sanitizedPath);
                problemProvider.updateProblems(results);
                statusProvider.updateStatus('File scanned successfully', 'success');
                
                const issueCount = results.length;
                if (issueCount === 0) {
                    vscode.window.showInformationMessage('No security issues found in this file');
                } else {
                    vscode.window.showWarningMessage(`Found ${issueCount} security issue(s) in this file`);
                }
            });
        } catch (error) {
            vscode.window.showErrorMessage(`Scan failed: ${error}`);
            statusProvider.updateStatus('Scan failed', 'error');
        }
    });

    const scanWorkspaceCommand = vscode.commands.registerCommand('wordpress-semgrep.scanWorkspace', async () => {
        const workspaceFolders = vscode.workspace.workspaceFolders;
        if (!workspaceFolders) {
            vscode.window.showWarningMessage('No workspace folder found');
            return;
        }

        // Enhanced workspace path validation
        const workspacePath = workspaceFolders[0].uri.fsPath;
        const sanitizedPath = sanitizePath(workspacePath);
        
        if (!validateWorkspacePath(sanitizedPath)) {
            vscode.window.showErrorMessage('Invalid or unsafe workspace path detected');
            statusProvider.updateStatus('Workspace path validation failed', 'error');
            return;
        }

        try {
            vscode.window.withProgress({
                location: vscode.ProgressLocation.Notification,
                title: 'Scanning workspace for security issues...',
                cancellable: false
            }, async (progress) => {
                const results = await scanner.scanWorkspace(sanitizedPath);
                problemProvider.updateProblems(results);
                statusProvider.updateStatus('Workspace scanned successfully', 'success');
                
                const issueCount = results.length;
                if (issueCount === 0) {
                    vscode.window.showInformationMessage('No security issues found in workspace');
                } else {
                    vscode.window.showWarningMessage(`Found ${issueCount} security issue(s) in workspace`);
                }
            });
        } catch (error) {
            vscode.window.showErrorMessage(`Workspace scan failed: ${error}`);
            statusProvider.updateStatus('Workspace scan failed', 'error');
        }
    });

    const showProblemsCommand = vscode.commands.registerCommand('wordpress-semgrep.showProblems', () => {
        problemsView.reveal(problemProvider.getRoot());
    });

    const configureRulesCommand = vscode.commands.registerCommand('wordpress-semgrep.configureRules', async () => {
        const configPath = await vscode.window.showInputBox({
            prompt: 'Enter path to Semgrep configuration file',
            value: configManager.getConfigPath(),
            placeHolder: '/path/to/semgrep-config.yaml'
        });

        if (configPath) {
            // Enhanced config path validation
            const sanitizedPath = sanitizePath(configPath);
            
            // Validate config file path
            try {
                if (!fs.existsSync(sanitizedPath)) {
                    vscode.window.showErrorMessage('Configuration file does not exist');
                    return;
                }
                
                const stats = fs.statSync(sanitizedPath);
                if (!stats.isFile()) {
                    vscode.window.showErrorMessage('Configuration path must be a file');
                    return;
                }
                
                // Check file extension
                const ext = path.extname(sanitizedPath).toLowerCase();
                if (ext !== '.yaml' && ext !== '.yml') {
                    vscode.window.showErrorMessage('Configuration file must be a YAML file');
                    return;
                }
                
                configManager.setConfigPath(sanitizedPath);
                vscode.window.showInformationMessage('Configuration updated successfully');
                statusProvider.updateStatus('Configuration updated', 'success');
                
            } catch (error) {
                vscode.window.showErrorMessage(`Configuration validation failed: ${error}`);
                statusProvider.updateStatus('Configuration validation failed', 'error');
            }
        }
    });

    const installSemgrepCommand = vscode.commands.registerCommand('wordpress-semgrep.installSemgrep', async () => {
        try {
            vscode.window.withProgress({
                location: vscode.ProgressLocation.Notification,
                title: 'Installing Semgrep...',
                cancellable: false
            }, async (progress) => {
                await semgrepInstaller.install();
                vscode.window.showInformationMessage('Semgrep installed successfully');
                statusProvider.updateStatus('Semgrep ready', 'success');
            });
        } catch (error) {
            vscode.window.showErrorMessage(`Failed to install Semgrep: ${error}`);
            statusProvider.updateStatus('Semgrep installation failed', 'error');
        }
    });

    // Auto-scan on save
    const saveListener = vscode.workspace.onDidSaveTextDocument(async (document) => {
        if (!configManager.isAutoScanEnabled() || document.languageId !== 'php') {
            return;
        }

        try {
            const results = await scanner.scanFile(document.uri.fsPath);
            problemProvider.updateProblems(results);
            
            const issueCount = results.length;
            if (issueCount > 0) {
                statusProvider.updateStatus(`${issueCount} issue(s) found`, 'warning');
            }
        } catch (error) {
            console.error('Auto-scan failed:', error);
        }
    });

    // Add commands to context
    context.subscriptions.push(
        scanFileCommand,
        scanWorkspaceCommand,
        showProblemsCommand,
        configureRulesCommand,
        installSemgrepCommand,
        saveListener,
        problemsView,
        statusView
    );

    // Check if Semgrep is installed
    if (!await semgrepInstaller.isInstalled()) {
        vscode.window.showWarningMessage(
            'Semgrep is not installed. Run "WordPress Semgrep: Install Semgrep" to install it.',
            'Install Semgrep'
        ).then(selection => {
            if (selection === 'Install Semgrep') {
                vscode.commands.executeCommand('wordpress-semgrep.installSemgrep');
            }
        });
    } else {
        statusProvider.updateStatus('Semgrep ready', 'success');
    }
}

export function deactivate() {
    console.log('WordPress Semgrep Security extension is now deactivated!');
}
