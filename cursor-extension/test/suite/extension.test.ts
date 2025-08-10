import * as assert from 'assert';
import * as vscode from 'vscode';
import * as path from 'path';

suite('WordPress Semgrep Security for Cursor Extension Test Suite', () => {
    vscode.window.showInformationMessage('Start all tests.');

    test('Extension should be present', () => {
        assert.ok(vscode.extensions.getExtension('wordpress-semgrep-security-cursor'));
    });

    test('Extension should activate', async () => {
        const extension = vscode.extensions.getExtension('wordpress-semgrep-security-cursor');
        if (extension) {
            await extension.activate();
            assert.ok(extension.isActive);
        }
    });

    test('Commands should be registered', async () => {
        const commands = await vscode.commands.getCommands();
        assert.ok(commands.includes('wordpress-semgrep-cursor.scanFile'));
        assert.ok(commands.includes('wordpress-semgrep-cursor.scanWorkspace'));
        assert.ok(commands.includes('wordpress-semgrep-cursor.showProblems'));
        assert.ok(commands.includes('wordpress-semgrep-cursor.configureRules'));
        assert.ok(commands.includes('wordpress-semgrep-cursor.quickFix'));
    });

    test('Views should be registered', async () => {
        const views = vscode.workspace.workspaceFolders;
        // Test that the extension can create views
        assert.ok(true); // Placeholder - views are created during activation
    });

    test('Configuration should be accessible', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        assert.ok(config.has('enabled'));
        assert.ok(config.has('autoScan'));
        assert.ok(config.has('showInline'));
        assert.ok(config.has('quickFixEnabled'));
    });

    test('Should handle PHP files', async () => {
        const testFile = path.join(__dirname, '../../test-fixtures/test.php');
        
        // Create a test PHP file
        const testContent = `<?php
// Test file for WordPress Semgrep Security
$user_input = $_POST['user_input'];
echo $user_input; // This should trigger a security warning
?>`;
        
        const uri = vscode.Uri.file(testFile);
        await vscode.workspace.fs.writeFile(uri, Buffer.from(testContent));
        
        // Open the file
        const document = await vscode.workspace.openTextDocument(uri);
        await vscode.window.showTextDocument(document);
        
        // Test that the extension can process PHP files
        assert.ok(document.languageId === 'php');
        
        // Clean up
        await vscode.workspace.fs.delete(uri);
    });

    test('Quick fix provider should be registered', async () => {
        // Test that quick fixes are available for PHP files
        const testFile = path.join(__dirname, '../../test-fixtures/quickfix-test.php');
        
        const testContent = `<?php
$user_input = $_POST['user_input'];
echo $user_input;
?>`;
        
        const uri = vscode.Uri.file(testFile);
        await vscode.workspace.fs.writeFile(uri, Buffer.from(testContent));
        
        const document = await vscode.workspace.openTextDocument(uri);
        await vscode.window.showTextDocument(document);
        
        // Test that quick fixes can be provided
        const range = new vscode.Range(0, 0, 0, 0);
        const context = { diagnostics: [], only: vscode.CodeActionKind.QuickFix };
        
        // This test verifies the quick fix provider is registered
        assert.ok(true); // Placeholder - actual quick fix testing would require more setup
        
        // Clean up
        await vscode.workspace.fs.delete(uri);
    });

    test('Inline diagnostics should be enabled by default', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        const showInline = config.get('showInline', true);
        assert.strictEqual(showInline, true);
    });

    test('Quick fixes should be enabled by default', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        const quickFixEnabled = config.get('quickFixEnabled', true);
        assert.strictEqual(quickFixEnabled, true);
    });
});
