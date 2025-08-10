const path = require('path');
const { runTests } = require('@vscode/test-electron');

async function main() {
    try {
        // The folder containing the Extension Manifest package.json
        const extensionDevelopmentPath = path.resolve(__dirname, '../');

        // The path to test runner
        const extensionTestsPath = path.resolve(__dirname, './suite/index');

        // Get command line arguments
        const args = process.argv.slice(2);
        const suiteArg = args.find(arg => arg.startsWith('--suite='));
        const suite = suiteArg ? suiteArg.split('=')[1] : 'all';

        console.log(`Running ${suite} tests...`);

        // Download VS Code, unzip it and run the integration test
        await runTests({
            extensionDevelopmentPath,
            extensionTestsPath,
            launchArgs: ['--disable-extensions'],
            extensionTestsEnv: {
                TEST_SUITE: suite
            }
        });
    } catch (err) {
        console.error('Failed to run tests');
        console.error(err);
        process.exit(1);
    }
}

main();
