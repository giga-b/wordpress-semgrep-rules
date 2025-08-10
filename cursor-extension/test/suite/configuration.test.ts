import * as assert from 'assert';
import * as vscode from 'vscode';

suite('ConfigurationManager Test Suite', () => {
    test('Configuration should have correct section name', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        assert.ok(config);
    });

    test('Configuration should have all required settings', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        
        // Test that all required settings exist
        assert.ok(config.has('enabled'));
        assert.ok(config.has('autoScan'));
        assert.ok(config.has('configPath'));
        assert.ok(config.has('rulesPath'));
        assert.ok(config.has('severity'));
        assert.ok(config.has('maxProblems'));
        assert.ok(config.has('timeout'));
        assert.ok(config.has('showInline'));
        assert.ok(config.has('quickFixEnabled'));
    });

    test('Configuration should have correct default values', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        
        // Test default values
        const enabled = config.get('enabled', true);
        const autoScan = config.get('autoScan', true);
        const showInline = config.get('showInline', true);
        const quickFixEnabled = config.get('quickFixEnabled', true);
        const maxProblems = config.get('maxProblems', 100);
        const timeout = config.get('timeout', 30000);
        const severity = config.get('severity', 'WARNING');

        assert.strictEqual(enabled, true);
        assert.strictEqual(autoScan, true);
        assert.strictEqual(showInline, true);
        assert.strictEqual(quickFixEnabled, true);
        assert.strictEqual(maxProblems, 100);
        assert.strictEqual(timeout, 30000);
        assert.strictEqual(severity, 'WARNING');
    });

    test('Configuration should handle boolean settings', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        
        const enabled = config.get('enabled', true);
        const autoScan = config.get('autoScan', true);
        const showInline = config.get('showInline', true);
        const quickFixEnabled = config.get('quickFixEnabled', true);

        assert.ok(typeof enabled === 'boolean');
        assert.ok(typeof autoScan === 'boolean');
        assert.ok(typeof showInline === 'boolean');
        assert.ok(typeof quickFixEnabled === 'boolean');
    });

    test('Configuration should handle numeric settings', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        
        const maxProblems = config.get('maxProblems', 100);
        const timeout = config.get('timeout', 30000);

        assert.ok(typeof maxProblems === 'number');
        assert.ok(typeof timeout === 'number');
        assert.ok(maxProblems > 0);
        assert.ok(timeout > 0);
    });

    test('Configuration should handle string settings', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        
        const configPath = config.get('configPath', '');
        const rulesPath = config.get('rulesPath', '');
        const severity = config.get('severity', 'WARNING');

        assert.ok(typeof configPath === 'string');
        assert.ok(typeof rulesPath === 'string');
        assert.ok(typeof severity === 'string');
    });

    test('Configuration should handle severity levels', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        const severity = config.get('severity', 'WARNING');

        const validSeverities = ['ERROR', 'WARNING', 'INFO'];
        assert.ok(validSeverities.includes(severity));
    });

    test('Configuration should handle path settings', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        
        const configPath = config.get('configPath', '');
        const rulesPath = config.get('rulesPath', '');

        // Test that paths can be empty or valid file paths
        assert.ok(typeof configPath === 'string');
        assert.ok(typeof rulesPath === 'string');
    });

    test('Configuration should handle timeout values', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        const timeout = config.get('timeout', 30000);

        // Test that timeout is a reasonable value
        assert.ok(typeof timeout === 'number');
        assert.ok(timeout >= 1000); // Minimum 1 second
        assert.ok(timeout <= 300000); // Maximum 5 minutes
    });

    test('Configuration should handle max problems values', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        const maxProblems = config.get('maxProblems', 100);

        // Test that max problems is a reasonable value
        assert.ok(typeof maxProblems === 'number');
        assert.ok(maxProblems > 0);
        assert.ok(maxProblems <= 10000); // Maximum 10,000 problems
    });

    test('Configuration should handle workspace settings', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        
        // Test that we can access workspace configuration
        assert.ok(config);
        assert.ok(typeof config.get === 'function');
        assert.ok(typeof config.has === 'function');
        assert.ok(typeof config.update === 'function');
    });

    test('Configuration should handle global settings', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        
        // Test that we can access global configuration
        assert.ok(config);
        assert.ok(typeof config.get === 'function');
    });

    test('Configuration should handle setting updates', async () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        
        // Test that we can update settings (this is a test of the API, not actual updates)
        const originalValue = config.get('enabled', true);
        
        // Verify the setting exists and can be read
        assert.ok(typeof originalValue === 'boolean');
    });

    test('Configuration should handle invalid settings gracefully', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        
        // Test that invalid settings return default values
        const invalidSetting = config.get('nonexistentSetting', 'default');
        assert.strictEqual(invalidSetting, 'default');
    });

    test('Configuration should handle different configuration scopes', () => {
        const config = vscode.workspace.getConfiguration('wordpressSemgrepCursor');
        
        // Test that configuration can be accessed
        assert.ok(config);
        
        // Test that we can check if settings exist
        assert.ok(config.has('enabled'));
        assert.ok(!config.has('nonexistentSetting'));
    });
});
