import * as vscode from 'vscode';
import { SemgrepScanner } from './semgrepScanner';
import { ProblemProvider } from './problemProvider';
import { StatusProvider } from './statusProvider';
import { ConfigurationManager } from './configurationManager';
import { SemgrepInstaller } from './semgrepInstaller';
import { QuickFixProvider } from './quickFixProvider';
import { InlineDiagnosticsProvider } from './inlineDiagnosticsProvider';

export async function activate(context: vscode.ExtensionContext) {
    console.log('WordPress Semgrep Security for Cursor extension is now active!');

    // Initialize components
    const configManager = new ConfigurationManager();
    const semgrepInstaller = new SemgrepInstaller();
    const scanner = new SemgrepScanner(configManager);
    const problemProvider = new ProblemProvider();
    const statusProvider = new StatusProvider();
    const quickFixProvider = new QuickFixProvider();
    const inlineDiagnosticsProvider = new InlineDiagnosticsProvider();

    // Register problem provider
    const problemsView = vscode.window.createTreeView('wordpress-semgrep-cursor-problems', {
        treeDataProvider: problemProvider
    });

    // Register status provider
    const statusView = vscode.window.createTreeView('wordpress-semgrep-cursor-status', {
        treeDataProvider: statusProvider
    });

    // Register quick fix provider
    const quickFixDisposable = vscode.languages.registerCodeActionsProvider(
        { language: 'php' },
        quickFixProvider,
        {
            providedCodeActionKinds: [vscode.CodeActionKind.QuickFix]
        }
    );

    // Register inline diagnostics provider
    const inlineDiagnosticsDisposable = vscode.languages.registerDiagnosticsProvider(
        { language: 'php' },
        inlineDiagnosticsProvider
    );

    // Register commands
    const scanFileCommand = vscode.commands.registerCommand('wordpress-semgrep-cursor.scanFile', async () => {
        const editor = vscode.window.activeTextEditor;
        if (!editor) {
            vscode.window.showWarningMessage('No active editor found');
            return;
        }

        if (editor.document.languageId !== 'php') {
            vscode.window.showWarningMessage('This command only works with PHP files');
            return;
        }

        try {
            vscode.window.withProgress({
                location: vscode.ProgressLocation.Notification,
                title: '🔒 Scanning file for security issues...',
                cancellable: false
            }, async (progress) => {
                progress.report({ increment: 0, message: 'Running Semgrep scan...' });
                const results = await scanner.scanFile(editor.document.uri.fsPath);
                
                progress.report({ increment: 50, message: 'Processing results...' });
                problemProvider.updateProblems(results);
                inlineDiagnosticsProvider.updateDiagnostics(editor.document.uri, results);
                statusProvider.updateStatus('File scanned successfully', 'success');
                
                const issueCount = results.length;
                if (issueCount === 0) {
                    vscode.window.showInformationMessage('✅ No security issues found in this file');
                } else {
                    vscode.window.showWarningMessage(`⚠️ Found ${issueCount} security issue(s) in this file`);
                }
            });
        } catch (error) {
            vscode.window.showErrorMessage(`❌ Scan failed: ${error}`);
            statusProvider.updateStatus('Scan failed', 'error');
        }
    });

    const scanWorkspaceCommand = vscode.commands.registerCommand('wordpress-semgrep-cursor.scanWorkspace', async () => {
        const workspaceFolders = vscode.workspace.workspaceFolders;
        if (!workspaceFolders) {
            vscode.window.showWarningMessage('No workspace folder found');
            return;
        }

        try {
            vscode.window.withProgress({
                location: vscode.ProgressLocation.Notification,
                title: '🔒 Scanning workspace for security issues...',
                cancellable: false
            }, async (progress) => {
                progress.report({ increment: 0, message: 'Running Semgrep scan...' });
                const results = await scanner.scanWorkspace(workspaceFolders[0].uri.fsPath);
                
                progress.report({ increment: 50, message: 'Processing results...' });
                problemProvider.updateProblems(results);
                statusProvider.updateStatus('Workspace scanned successfully', 'success');
                
                const issueCount = results.length;
                if (issueCount === 0) {
                    vscode.window.showInformationMessage('✅ No security issues found in workspace');
                } else {
                    vscode.window.showWarningMessage(`⚠️ Found ${issueCount} security issue(s) in workspace`);
                }
            });
        } catch (error) {
            vscode.window.showErrorMessage(`❌ Workspace scan failed: ${error}`);
            statusProvider.updateStatus('Workspace scan failed', 'error');
        }
    });

    const showProblemsCommand = vscode.commands.registerCommand('wordpress-semgrep-cursor.showProblems', () => {
        problemsView.reveal(problemProvider.getRoot());
    });

    const configureRulesCommand = vscode.commands.registerCommand('wordpress-semgrep-cursor.configureRules', async () => {
        const configPath = await vscode.window.showInputBox({
            prompt: 'Enter path to Semgrep configuration file',
            value: configManager.getConfigPath(),
            placeHolder: '/path/to/semgrep-config.yaml'
        });

        if (configPath) {
            configManager.setConfigPath(configPath);
            vscode.window.showInformationMessage('⚙️ Configuration updated');
        }
    });

    const installSemgrepCommand = vscode.commands.registerCommand('wordpress-semgrep-cursor.installSemgrep', async () => {
        try {
            vscode.window.withProgress({
                location: vscode.ProgressLocation.Notification,
                title: '📦 Installing Semgrep...',
                cancellable: false
            }, async (progress) => {
                await semgrepInstaller.install();
                vscode.window.showInformationMessage('✅ Semgrep installed successfully');
                statusProvider.updateStatus('Semgrep ready', 'success');
            });
        } catch (error) {
            vscode.window.showErrorMessage(`❌ Failed to install Semgrep: ${error}`);
            statusProvider.updateStatus('Semgrep installation failed', 'error');
        }
    });

    const quickFixCommand = vscode.commands.registerCommand('wordpress-semgrep-cursor.quickFix', async () => {
        const editor = vscode.window.activeTextEditor;
        if (!editor || editor.document.languageId !== 'php') {
            vscode.window.showWarningMessage('Quick fix is only available for PHP files');
            return;
        }

        const position = editor.selection.active;
        const diagnostics = vscode.languages.getDiagnostics(editor.document.uri);
        const diagnosticAtPosition = diagnostics.find(d => 
            d.range.contains(position) && d.source === 'WordPress Semgrep'
        );

        if (!diagnosticAtPosition) {
            vscode.window.showInformationMessage('No security issue found at cursor position');
            return;
        }

        const quickFixes = await quickFixProvider.provideCodeActions(
            editor.document,
            new vscode.Range(position, position),
            { diagnostics: [diagnosticAtPosition] },
            {} as any
        );

        if (quickFixes && quickFixes.length > 0) {
            const selectedFix = await vscode.window.showQuickPick(
                quickFixes.map(fix => ({
                    label: fix.title,
                    description: fix.detail,
                    fix: fix
                })),
                {
                    placeHolder: 'Select a quick fix to apply'
                }
            );

            if (selectedFix) {
                await selectedFix.fix.execute();
                vscode.window.showInformationMessage('🔧 Quick fix applied successfully');
            }
        } else {
            vscode.window.showInformationMessage('No quick fixes available for this issue');
        }
    });

    // Auto-scan on save with enhanced features
    const saveListener = vscode.workspace.onDidSaveTextDocument(async (document) => {
        if (!configManager.isAutoScanEnabled() || document.languageId !== 'php') {
            return;
        }

        try {
            const results = await scanner.scanFile(document.uri.fsPath);
            problemProvider.updateProblems(results);
            inlineDiagnosticsProvider.updateDiagnostics(document.uri, results);
            
            const issueCount = results.length;
            if (issueCount > 0) {
                statusProvider.updateStatus(`${issueCount} issue(s) found`, 'warning');
                
                // Show notification for new issues
                if (configManager.isInlineEnabled()) {
                    vscode.window.showInformationMessage(
                        `🔒 Found ${issueCount} security issue(s) - check inline diagnostics`,
                        'View Issues'
                    ).then(selection => {
                        if (selection === 'View Issues') {
                            problemsView.reveal(problemProvider.getRoot());
                        }
                    });
                }
            } else {
                statusProvider.updateStatus('No issues found', 'success');
            }
        } catch (error) {
            console.error('Auto-scan failed:', error);
        }
    });

    // Real-time scanning on document change (throttled)
    let scanTimeout: NodeJS.Timeout | undefined;
    const changeListener = vscode.workspace.onDidChangeTextDocument(async (event) => {
        if (!configManager.isAutoScanEnabled() || event.document.languageId !== 'php') {
            return;
        }

        // Clear existing timeout
        if (scanTimeout) {
            clearTimeout(scanTimeout);
        }

        // Set new timeout for throttled scanning
        scanTimeout = setTimeout(async () => {
            try {
                const results = await scanner.scanFile(event.document.uri.fsPath);
                inlineDiagnosticsProvider.updateDiagnostics(event.document.uri, results);
            } catch (error) {
                console.error('Real-time scan failed:', error);
            }
        }, 2000); // 2 second delay
    });

    // Add commands to context
    context.subscriptions.push(
        scanFileCommand,
        scanWorkspaceCommand,
        showProblemsCommand,
        configureRulesCommand,
        installSemgrepCommand,
        quickFixCommand,
        saveListener,
        changeListener,
        problemsView,
        statusView,
        quickFixDisposable,
        inlineDiagnosticsDisposable
    );

    // Check if Semgrep is installed
    if (!await semgrepInstaller.isInstalled()) {
        vscode.window.showWarningMessage(
            'Semgrep is not installed. Run "WordPress Security: Install Semgrep" to install it.',
            'Install Semgrep'
        ).then(selection => {
            if (selection === 'Install Semgrep') {
                vscode.commands.executeCommand('wordpress-semgrep-cursor.installSemgrep');
            }
        });
    } else {
        statusProvider.updateStatus('Semgrep ready', 'success');
    }

    // Show welcome message
    vscode.window.showInformationMessage(
        '🔒 WordPress Semgrep Security for Cursor is ready! Use Ctrl+Shift+S to scan files.',
        'View Commands'
    ).then(selection => {
        if (selection === 'View Commands') {
            vscode.commands.executeCommand('workbench.action.showCommands');
        }
    });
}

export function deactivate() {
    console.log('WordPress Semgrep Security for Cursor extension is now deactivated!');
}
