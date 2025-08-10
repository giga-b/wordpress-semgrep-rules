import * as assert from 'assert';
import * as vscode from 'vscode';

suite('Extension Test Suite', () => {
	vscode.window.showInformationMessage('Start all tests.');

	test('Extension should be present', () => {
		assert.ok(vscode.extensions.getExtension('wordpress-semgrep-security'));
	});

	test('Should activate', async () => {
		const ext = vscode.extensions.getExtension('wordpress-semgrep-security');
		await ext?.activate();
		assert.ok(ext?.isActive);
	});

	test('Should register commands', async () => {
		const commands = await vscode.commands.getCommands();
		assert.ok(commands.includes('wordpress-semgrep.scanFile'));
		assert.ok(commands.includes('wordpress-semgrep.scanWorkspace'));
		assert.ok(commands.includes('wordpress-semgrep.showProblems'));
		assert.ok(commands.includes('wordpress-semgrep.configureRules'));
		assert.ok(commands.includes('wordpress-semgrep.installSemgrep'));
	});
});
