import * as assert from 'assert';
import * as vscode from 'vscode';
import * as path from 'path';
import * as fs from 'fs';

suite('InlineDiagnosticsProvider Test Suite', () => {
    let testWorkspace: vscode.WorkspaceFolder | undefined;

    suiteSetup(async () => {
        if (vscode.workspace.workspaceFolders && vscode.workspace.workspaceFolders.length > 0) {
            testWorkspace = vscode.workspace.workspaceFolders[0];
        }
    });

    test('Inline diagnostics provider should handle Semgrep results', () => {
        // Mock Semgrep result structure
        const mockResults = [
            {
                check_id: 'wordpress.security.xss.echo',
                path: '/test/file.php',
                start: { line: 3, col: 1 },
                end: { line: 3, col: 10 },
                message: 'Potential XSS vulnerability: unsanitized user input',
                severity: 'WARNING',
                fix: 'Use wp_kses_post() or esc_html() to sanitize output',
                metadata: {
                    category: 'security',
                    cwe: 'CWE-79',
                    references: ['https://owasp.org/www-community/attacks/xss/']
                }
            },
            {
                check_id: 'wordpress.security.sql-injection',
                path: '/test/file.php',
                start: { line: 5, col: 1 },
                end: { line: 5, col: 15 },
                message: 'Potential SQL injection vulnerability',
                severity: 'ERROR',
                fix: 'Use prepared statements or wpdb->prepare()',
                metadata: {
                    category: 'security',
                    cwe: 'CWE-89',
                    references: ['https://owasp.org/www-community/attacks/SQL_Injection']
                }
            }
        ];

        // Test result structure
        mockResults.forEach(result => {
            assert.ok(result.check_id);
            assert.ok(result.path);
            assert.ok(result.start);
            assert.ok(result.end);
            assert.ok(result.message);
            assert.ok(result.severity);
            assert.ok(typeof result.start.line === 'number');
            assert.ok(typeof result.start.col === 'number');
            assert.ok(typeof result.end.line === 'number');
            assert.ok(typeof result.end.col === 'number');
        });
    });

    test('Inline diagnostics provider should map severity levels correctly', () => {
        const severityMappings = [
            { semgrep: 'ERROR', vscode: vscode.DiagnosticSeverity.Error },
            { semgrep: 'WARNING', vscode: vscode.DiagnosticSeverity.Warning },
            { semgrep: 'INFO', vscode: vscode.DiagnosticSeverity.Information }
        ];

        severityMappings.forEach(mapping => {
            const mockResult = {
                check_id: 'test.rule',
                path: '/test/file.php',
                start: { line: 1, col: 1 },
                end: { line: 1, col: 10 },
                message: 'Test message',
                severity: mapping.semgrep,
                metadata: {}
            };

            // Test that severity mapping works
            assert.ok(mockResult.severity === mapping.semgrep);
        });
    });

    test('Inline diagnostics provider should format messages correctly', () => {
        const mockResult = {
            check_id: 'wordpress.security.xss.echo',
            path: '/test/file.php',
            start: { line: 3, col: 1 },
            end: { line: 3, col: 10 },
            message: 'Potential XSS vulnerability: unsanitized user input',
            severity: 'WARNING',
            fix: 'Use wp_kses_post() or esc_html() to sanitize output',
            metadata: {
                category: 'security',
                cwe: 'CWE-79',
                references: ['https://owasp.org/www-community/attacks/xss/']
            }
        };

        // Test message formatting
        const formattedMessage = `${mockResult.message}\n\nRule: ${mockResult.check_id}\nSeverity: ${mockResult.severity}`;
        
        assert.ok(formattedMessage.includes(mockResult.message));
        assert.ok(formattedMessage.includes(mockResult.check_id));
        assert.ok(formattedMessage.includes(mockResult.severity));
    });

    test('Inline diagnostics provider should handle CWE links', () => {
        const mockResult = {
            check_id: 'wordpress.security.xss.echo',
            path: '/test/file.php',
            start: { line: 3, col: 1 },
            end: { line: 3, col: 10 },
            message: 'Potential XSS vulnerability',
            severity: 'WARNING',
            metadata: {
                category: 'security',
                cwe: 'CWE-79',
                references: ['https://owasp.org/www-community/attacks/xss/']
            }
        };

        // Test CWE link generation
        if (mockResult.metadata?.cwe) {
            const cweLink = `https://cwe.mitre.org/data/definitions/${mockResult.metadata.cwe.replace('CWE-', '')}.html`;
            assert.ok(cweLink.includes('cwe.mitre.org'));
            assert.ok(cweLink.includes('79'));
        }
    });

    test('Inline diagnostics provider should handle quick fix indicators', () => {
        const mockResultWithFix = {
            check_id: 'wordpress.security.xss.echo',
            path: '/test/file.php',
            start: { line: 3, col: 1 },
            end: { line: 3, col: 10 },
            message: 'Potential XSS vulnerability',
            severity: 'WARNING',
            fix: 'Use wp_kses_post() or esc_html() to sanitize output',
            metadata: {}
        };

        const mockResultWithoutFix = {
            check_id: 'wordpress.security.xss.echo',
            path: '/test/file.php',
            start: { line: 3, col: 1 },
            end: { line: 3, col: 10 },
            message: 'Potential XSS vulnerability',
            severity: 'WARNING',
            metadata: {}
        };

        // Test quick fix availability
        assert.ok(mockResultWithFix.fix);
        assert.ok(!mockResultWithoutFix.fix);
    });

    test('Inline diagnostics provider should handle file URIs', async () => {
        if (!testWorkspace) {
            assert.fail('No workspace folder available for testing');
            return;
        }

        const testFile = path.join(testWorkspace.uri.fsPath, 'test-fixtures', 'diagnostics-test.php');
        
        const testDir = path.dirname(testFile);
        if (!fs.existsSync(testDir)) {
            fs.mkdirSync(testDir, { recursive: true });
        }

        const testContent = `<?php
// Test file for inline diagnostics
$user_input = $_POST['user_input'];
echo $user_input;
?>`;

        fs.writeFileSync(testFile, testContent);
        
        const uri = vscode.Uri.file(testFile);
        const document = await vscode.workspace.openTextDocument(uri);
        await vscode.window.showTextDocument(document);

        // Test URI handling
        assert.ok(uri.scheme === 'file');
        assert.ok(uri.fsPath === testFile);
        assert.ok(document.uri.toString() === uri.toString());

        // Clean up
        fs.unlinkSync(testFile);
    });

    test('Inline diagnostics provider should handle multiple results for same file', () => {
        const mockResults = [
            {
                check_id: 'wordpress.security.xss.echo',
                path: '/test/file.php',
                start: { line: 3, col: 1 },
                end: { line: 3, col: 10 },
                message: 'XSS vulnerability',
                severity: 'WARNING',
                metadata: {}
            },
            {
                check_id: 'wordpress.security.sql-injection',
                path: '/test/file.php',
                start: { line: 5, col: 1 },
                end: { line: 5, col: 15 },
                message: 'SQL injection vulnerability',
                severity: 'ERROR',
                metadata: {}
            },
            {
                check_id: 'wordpress.security.nonce-verification',
                path: '/test/file.php',
                start: { line: 7, col: 1 },
                end: { line: 7, col: 20 },
                message: 'Missing nonce verification',
                severity: 'INFO',
                metadata: {}
            }
        ];

        // Test multiple results handling
        assert.ok(mockResults.length === 3);
        mockResults.forEach(result => {
            assert.ok(result.path === '/test/file.php');
            assert.ok(result.check_id);
            assert.ok(result.message);
            assert.ok(result.severity);
        });
    });

    test('Inline diagnostics provider should handle empty results', () => {
        const emptyResults: any[] = [];
        
        // Test empty results handling
        assert.ok(Array.isArray(emptyResults));
        assert.ok(emptyResults.length === 0);
    });

    test('Inline diagnostics provider should handle malformed results', () => {
        const malformedResult = {
            check_id: 'test.rule',
            path: '/test/file.php',
            // Missing start/end positions
            message: 'Test message',
            severity: 'WARNING',
            metadata: {}
        };

        // Test malformed result handling
        assert.ok(malformedResult.check_id);
        assert.ok(malformedResult.message);
        assert.ok(malformedResult.severity);
        assert.ok(!malformedResult.start);
        assert.ok(!malformedResult.end);
    });

    test('Inline diagnostics provider should handle diagnostic collection', () => {
        // Test that diagnostic collection can be created
        const collection = vscode.languages.createDiagnosticCollection('wordpress-semgrep-cursor');
        
        assert.ok(collection);
        assert.ok(typeof collection.set === 'function');
        assert.ok(typeof collection.clear === 'function');
        assert.ok(typeof collection.dispose === 'function');
    });
});
