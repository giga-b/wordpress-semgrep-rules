# Testing Guide for WordPress Semgrep Security Cursor Extension

## Overview

This document provides comprehensive testing information for the WordPress Semgrep Security Cursor Extension, including test structure, running tests, and test coverage.

## Test Structure

The test suite is organized into the following structure:

```
cursor-extension/
├── test/
│   ├── runTests.js              # Main test runner
│   ├── suite/
│   │   ├── index.ts             # Test suite entry point
│   │   ├── extension.test.ts    # Core extension tests
│   │   ├── scanner.test.ts      # SemgrepScanner tests
│   │   ├── quickfix.test.ts     # QuickFixProvider tests
│   │   ├── inline-diagnostics.test.ts  # InlineDiagnosticsProvider tests
│   │   └── configuration.test.ts # ConfigurationManager tests
│   └── test-fixtures/           # Test files and data
│       ├── sample-vulnerable.php
│       └── sample-safe.php
```

## Test Categories

### 1. Core Extension Tests (`extension.test.ts`)
- Extension activation and presence
- Command registration
- View registration
- Configuration accessibility
- PHP file handling
- Quick fix provider registration
- Default settings validation

### 2. Scanner Tests (`scanner.test.ts`)
- File path handling
- File extension validation
- Configuration settings
- Scan argument construction
- Result parsing
- Caching mechanisms
- Error handling

### 3. Quick Fix Tests (`quickfix.test.ts`)
- Nonce verification fixes
- Capability check fixes
- Sanitization fixes
- SQL injection fixes
- XSS prevention fixes
- Code action creation
- Severity level handling
- Empty document handling

### 4. Inline Diagnostics Tests (`inline-diagnostics.test.ts`)
- Semgrep result handling
- Severity level mapping
- Message formatting
- CWE link generation
- Quick fix indicators
- File URI handling
- Multiple results handling
- Diagnostic collection management

### 5. Configuration Tests (`configuration.test.ts`)
- Configuration section access
- Required settings validation
- Default values verification
- Boolean settings handling
- Numeric settings handling
- String settings handling
- Severity level validation
- Path settings handling
- Timeout and max problems validation

## Running Tests

### Prerequisites

1. Install dependencies:
```bash
npm install
```

2. Compile the extension:
```bash
npm run compile
```

### Test Commands

#### Run All Tests
```bash
npm test
```

#### Run Unit Tests Only
```bash
npm run test:unit
```

#### Run Integration Tests Only
```bash
npm run test:integration
```

#### Run Specific Test Suite
```bash
node ./out/test/runTests.js --suite=scanner
node ./out/test/runTests.js --suite=quickfix
node ./out/test/runTests.js --suite=configuration
```

### Test Environment

The tests run in a VS Code test environment with:
- Disabled extensions to avoid conflicts
- Isolated workspace for each test
- Automatic cleanup of test files
- Mock Semgrep results for consistent testing

## Test Fixtures

### Sample Vulnerable File (`sample-vulnerable.php`)
Contains intentional security vulnerabilities for testing:
- XSS vulnerabilities (unsanitized user input)
- SQL injection vulnerabilities
- Missing nonce verification
- Missing capability checks
- Unsanitized HTML output
- File operation vulnerabilities
- Unvalidated redirects

### Sample Safe File (`sample-safe.php`)
Contains properly secured code for testing:
- Properly sanitized user input
- SQL injection prevention with prepared statements
- Nonce verification
- Capability checks
- Sanitized HTML output
- Safe file operations
- Validated redirects

## Test Coverage

### Core Functionality
- ✅ Extension activation and deactivation
- ✅ Command registration and execution
- ✅ Configuration management
- ✅ File scanning capabilities
- ✅ Result parsing and display

### Security Features
- ✅ XSS vulnerability detection
- ✅ SQL injection detection
- ✅ Nonce verification checks
- ✅ Capability check validation
- ✅ Input sanitization verification
- ✅ File operation security

### UI/UX Features
- ✅ Quick fix suggestions
- ✅ Inline diagnostics display
- ✅ Problem view integration
- ✅ Status provider functionality
- ✅ Configuration dialog

### Performance Features
- ✅ Caching mechanisms
- ✅ Incremental scanning
- ✅ Timeout handling
- ✅ Memory management

## Continuous Integration

The test suite is designed to run in CI/CD environments:

1. **Automated Testing**: All tests run automatically on code changes
2. **Coverage Reporting**: Test coverage is tracked and reported
3. **Quality Gates**: Tests must pass before merging
4. **Performance Monitoring**: Test execution time is monitored

## Troubleshooting

### Common Issues

1. **Test Failures Due to Missing Dependencies**
   ```bash
   npm install
   npm run compile
   ```

2. **VS Code Test Environment Issues**
   - Ensure VS Code is properly installed
   - Check that @vscode/test-electron is installed
   - Verify TypeScript compilation

3. **File Permission Issues**
   - Ensure write permissions for test directories
   - Check that test fixtures can be created/deleted

4. **Semgrep Not Found**
   - Tests use mock data, so Semgrep installation is not required
   - Real Semgrep testing is done in integration tests

### Debug Mode

To run tests in debug mode:
```bash
npm run test -- --debug
```

This will provide additional logging and debugging information.

## Best Practices

1. **Test Isolation**: Each test should be independent and not rely on other tests
2. **Cleanup**: Always clean up test files and resources
3. **Mocking**: Use mock data for external dependencies
4. **Assertions**: Use specific assertions with clear error messages
5. **Documentation**: Document complex test scenarios

## Future Enhancements

1. **Performance Testing**: Add benchmarks for scan performance
2. **Memory Testing**: Add memory leak detection tests
3. **Stress Testing**: Add tests for large files and workspaces
4. **Integration Testing**: Add tests with real Semgrep installation
5. **UI Testing**: Add automated UI interaction tests

## Contributing

When adding new tests:

1. Follow the existing test structure
2. Use descriptive test names
3. Include both positive and negative test cases
4. Add appropriate cleanup
5. Update this documentation

## Support

For test-related issues:
1. Check the troubleshooting section
2. Review test logs for specific error messages
3. Ensure all dependencies are properly installed
4. Verify TypeScript compilation is successful
