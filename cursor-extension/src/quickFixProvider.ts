import * as vscode from 'vscode';

export interface SemgrepResult {
    check_id: string;
    path: string;
    start: { line: number; col: number };
    end: { line: number; col: number };
    message: string;
    severity: string;
    fix?: string;
    metadata?: {
        category?: string;
        cwe?: string;
        references?: string[];
    };
}

export class QuickFixProvider implements vscode.CodeActionProvider {
    public static readonly providedCodeActionKinds = [
        vscode.CodeActionKind.QuickFix
    ];

    public provideCodeActions(
        document: vscode.TextDocument,
        range: vscode.Range | vscode.Selection,
        context: vscode.CodeActionContext,
        token: vscode.CancellationToken
    ): vscode.ProviderResult<vscode.CodeAction[]> {
        const actions: vscode.CodeAction[] = [];

        for (const diagnostic of context.diagnostics) {
            if (diagnostic.source === 'WordPress Semgrep') {
                const quickFixes = this.getQuickFixes(document, diagnostic);
                actions.push(...quickFixes);
            }
        }

        return actions;
    }

    private getQuickFixes(
        document: vscode.TextDocument,
        diagnostic: vscode.Diagnostic
    ): vscode.CodeAction[] {
        const actions: vscode.CodeAction[] = [];
        const message = diagnostic.message.toLowerCase();

        // Nonce verification fixes
        if (message.includes('nonce') && message.includes('verification')) {
            actions.push(this.createNonceFix(document, diagnostic));
        }

        // Capability check fixes
        if (message.includes('capability') || message.includes('permission')) {
            actions.push(this.createCapabilityFix(document, diagnostic));
        }

        // Sanitization fixes
        if (message.includes('sanitize') || message.includes('escape')) {
            actions.push(this.createSanitizationFix(document, diagnostic));
        }

        // SQL injection fixes
        if (message.includes('sql') && message.includes('injection')) {
            actions.push(this.createSqlInjectionFix(document, diagnostic));
        }

        // XSS fixes
        if (message.includes('xss') || message.includes('cross-site')) {
            actions.push(this.createXSSFix(document, diagnostic));
        }

        // Generic security fix
        if (actions.length === 0) {
            actions.push(this.createGenericSecurityFix(document, diagnostic));
        }

        return actions;
    }

    private createNonceFix(document: vscode.TextDocument, diagnostic: vscode.Diagnostic): vscode.CodeAction {
        const action = new vscode.CodeAction('Add nonce verification', vscode.CodeActionKind.QuickFix);
        action.diagnostics = [diagnostic];
        action.edit = new vscode.WorkspaceEdit();

        const line = diagnostic.range.start.line;
        const lineText = document.lineAt(line).text;
        const indentation = lineText.match(/^\s*/)?.[0] || '';

        // Add nonce verification before the vulnerable code
        const nonceCode = `${indentation}if (!wp_verify_nonce($_POST['_wpnonce'], 'action_name')) {\n${indentation}    wp_die('Security check failed');\n${indentation}}\n`;

        action.edit.insert(document.uri, new vscode.Position(line, 0), nonceCode);
        action.detail = 'Adds WordPress nonce verification to prevent CSRF attacks';

        return action;
    }

    private createCapabilityFix(document: vscode.TextDocument, diagnostic: vscode.Diagnostic): vscode.CodeAction {
        const action = new vscode.CodeAction('Add capability check', vscode.CodeActionKind.QuickFix);
        action.diagnostics = [diagnostic];
        action.edit = new vscode.WorkspaceEdit();

        const line = diagnostic.range.start.line;
        const lineText = document.lineAt(line).text;
        const indentation = lineText.match(/^\s*/)?.[0] || '';

        // Add capability check before the vulnerable code
        const capabilityCode = `${indentation}if (!current_user_can('manage_options')) {\n${indentation}    wp_die('Insufficient permissions');\n${indentation}}\n`;

        action.edit.insert(document.uri, new vscode.Position(line, 0), capabilityCode);
        action.detail = 'Adds WordPress capability check to ensure proper authorization';

        return action;
    }

    private createSanitizationFix(document: vscode.TextDocument, diagnostic: vscode.Diagnostic): vscode.CodeAction {
        const action = new vscode.CodeAction('Add input sanitization', vscode.CodeActionKind.QuickFix);
        action.diagnostics = [diagnostic];
        action.edit = new vscode.WorkspaceEdit();

        const range = diagnostic.range;
        const text = document.getText(range);

        // Replace the vulnerable code with sanitized version
        let sanitizedText = text;
        if (text.includes('$_GET') || text.includes('$_POST') || text.includes('$_REQUEST')) {
            sanitizedText = text.replace(/\$_GET\[['"]([^'"]+)['"]\]/g, "sanitize_text_field(\$_GET['$1'])");
            sanitizedText = sanitizedText.replace(/\$_POST\[['"]([^'"]+)['"]\]/g, "sanitize_text_field(\$_POST['$1'])");
            sanitizedText = sanitizedText.replace(/\$_REQUEST\[['"]([^'"]+)['"]\]/g, "sanitize_text_field(\$_REQUEST['$1'])");
        }

        action.edit.replace(document.uri, range, sanitizedText);
        action.detail = 'Adds WordPress sanitization functions to clean user input';

        return action;
    }

    private createSqlInjectionFix(document: vscode.TextDocument, diagnostic: vscode.Diagnostic): vscode.CodeAction {
        const action = new vscode.CodeAction('Use prepared statements', vscode.CodeActionKind.QuickFix);
        action.diagnostics = [diagnostic];
        action.edit = new vscode.WorkspaceEdit();

        const range = diagnostic.range;
        const text = document.getText(range);

        // Replace direct SQL with prepared statement
        let fixedText = text;
        if (text.includes('$wpdb->query')) {
            fixedText = text.replace(
                /\$wpdb->query\s*\(\s*['"]([^'"]+)['"]\s*\)/g,
                '$wpdb->prepare("$1", $args)'
            );
        }

        action.edit.replace(document.uri, range, fixedText);
        action.detail = 'Replaces direct SQL queries with WordPress prepared statements';

        return action;
    }

    private createXSSFix(document: vscode.TextDocument, diagnostic: vscode.Diagnostic): vscode.CodeAction {
        const action = new vscode.CodeAction('Add output escaping', vscode.CodeActionKind.QuickFix);
        action.diagnostics = [diagnostic];
        action.edit = new vscode.WorkspaceEdit();

        const range = diagnostic.range;
        const text = document.getText(range);

        // Wrap output with escaping function
        let escapedText = text;
        if (text.includes('echo') || text.includes('print')) {
            escapedText = text.replace(/(echo|print)\s+(.+);/g, '$1 esc_html($2);');
        }

        action.edit.replace(document.uri, range, escapedText);
        action.detail = 'Adds WordPress escaping functions to prevent XSS attacks';

        return action;
    }

    private createGenericSecurityFix(document: vscode.TextDocument, diagnostic: vscode.Diagnostic): vscode.CodeAction {
        const action = new vscode.CodeAction('Add security check', vscode.CodeActionKind.QuickFix);
        action.diagnostics = [diagnostic];
        action.edit = new vscode.WorkspaceEdit();

        const line = diagnostic.range.start.line;
        const lineText = document.lineAt(line).text;
        const indentation = lineText.match(/^\s*/)?.[0] || '';

        // Add generic security check
        const securityCode = `${indentation}// TODO: Add appropriate security check\n${indentation}// Consider: nonce verification, capability check, input sanitization\n`;

        action.edit.insert(document.uri, new vscode.Position(line, 0), securityCode);
        action.detail = 'Adds a placeholder for security improvements';

        return action;
    }
}
