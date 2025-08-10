import * as vscode from 'vscode';
import { SemgrepResult } from './quickFixProvider';

export class InlineDiagnosticsProvider implements vscode.DiagnosticProvider {
    private diagnosticCollection: vscode.DiagnosticCollection;

    constructor() {
        this.diagnosticCollection = vscode.languages.createDiagnosticCollection('wordpress-semgrep-cursor');
    }

    public updateDiagnostics(uri: vscode.Uri, results: SemgrepResult[]): void {
        const diagnostics: vscode.Diagnostic[] = [];

        for (const result of results) {
            const diagnostic = this.createDiagnostic(result);
            if (diagnostic) {
                diagnostics.push(diagnostic);
            }
        }

        this.diagnosticCollection.set(uri, diagnostics);
    }

    public clearDiagnostics(uri: vscode.Uri): void {
        this.diagnosticCollection.delete(uri);
    }

    public clearAllDiagnostics(): void {
        this.diagnosticCollection.clear();
    }

    private createDiagnostic(result: SemgrepResult): vscode.Diagnostic | null {
        try {
            // Create range from Semgrep result
            const startLine = Math.max(0, result.start.line - 1); // Convert to 0-based
            const startChar = Math.max(0, result.start.col - 1); // Convert to 0-based
            const endLine = Math.max(0, result.end.line - 1); // Convert to 0-based
            const endChar = Math.max(0, result.end.col - 1); // Convert to 0-based

            const range = new vscode.Range(
                new vscode.Position(startLine, startChar),
                new vscode.Position(endLine, endChar)
            );

            // Create diagnostic message
            const message = this.formatMessage(result);

            // Determine severity
            const severity = this.mapSeverity(result.severity);

            // Create diagnostic
            const diagnostic = new vscode.Diagnostic(range, message, severity);
            diagnostic.source = 'WordPress Semgrep';
            diagnostic.code = result.check_id;

            // Add additional information
            if (result.metadata?.cwe) {
                diagnostic.code = {
                    value: result.check_id,
                    target: {
                        scheme: 'https',
                        authority: 'cwe.mitre.org',
                        path: `/data/definitions/${result.metadata.cwe}.html`
                    }
                };
            }

            // Add tags for better categorization
            const tags: vscode.DiagnosticTag[] = [];
            if (result.severity === 'ERROR') {
                tags.push(vscode.DiagnosticTag.Unnecessary);
            }

            if (result.metadata?.category === 'security') {
                tags.push(vscode.DiagnosticTag.Deprecated);
            }

            diagnostic.tags = tags;

            return diagnostic;
        } catch (error) {
            console.error('Error creating diagnostic:', error);
            return null;
        }
    }

    private formatMessage(result: SemgrepResult): string {
        let message = result.message;

        // Add category information if available
        if (result.metadata?.category) {
            message = `[${result.metadata.category.toUpperCase()}] ${message}`;
        }

        // Add CWE reference if available
        if (result.metadata?.cwe) {
            message += ` (CWE-${result.metadata.cwe})`;
        }

        // Add fix suggestion if available
        if (result.fix) {
            message += '\n💡 Quick fix available';
        }

        return message;
    }

    private mapSeverity(semgrepSeverity: string): vscode.DiagnosticSeverity {
        switch (semgrepSeverity.toLowerCase()) {
            case 'error':
                return vscode.DiagnosticSeverity.Error;
            case 'warning':
                return vscode.DiagnosticSeverity.Warning;
            case 'info':
                return vscode.DiagnosticSeverity.Information;
            default:
                return vscode.DiagnosticSeverity.Warning;
        }
    }

    public dispose(): void {
        this.diagnosticCollection.dispose();
    }
}
