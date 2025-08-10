<?php
/**
 * Advanced Test Cases Runner
 * 
 * This script runs comprehensive tests against the WordPress Semgrep rules
 * to validate detection of advanced vulnerabilities and edge cases.
 */

// Test configuration
$config = [
    'semgrep_binary' => 'semgrep',
    'rules_path' => '../packs/',
    'test_files' => [
        'vulnerable-examples/advanced-vulnerabilities.php',
        'vulnerable-examples/edge-case-vulnerabilities.php',
        'safe-examples/advanced-vulnerabilities-safe.php'
    ],
    'rule_packs' => [
        'wp-core-security' => [
            'nonce-verification.yaml',
            'capability-checks.yaml',
            'sanitization-functions.yaml',
            'rest-api-security.yaml',
            'ajax-security.yaml',
            'sql-injection.yaml',
            'xss-prevention.yaml'
        ],
        'experimental' => [
            'advanced-obfuscation-rules.yaml',
            'advanced-security-rules.yaml',
            'comprehensive-security-rules.yaml',
            'sql-injection-taint-rules.yaml',
            'xss-taint-rules.yaml',
            'taint-analysis-framework.yaml',
            'vx2-specific-rules.yaml'
        ]
    ]
];

class AdvancedTestRunner {
    private $config;
    private $results = [];
    private $summary = [
        'total_tests' => 0,
        'vulnerabilities_detected' => 0,
        'false_positives' => 0,
        'missed_vulnerabilities' => 0,
        'test_duration' => 0
    ];

    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Run all advanced tests
     */
    public function runAllTests() {
        echo "Starting Advanced Test Cases Runner...\n";
        echo "=====================================\n\n";

        $start_time = microtime(true);

        // Test vulnerable examples
        $this->testVulnerableExamples();
        
        // Test safe examples (should not trigger rules)
        $this->testSafeExamples();
        
        // Test edge cases
        $this->testEdgeCases();

        $this->summary['test_duration'] = microtime(true) - $start_time;
        
        $this->generateReport();
    }

    /**
     * Test vulnerable examples - should trigger security rules
     */
    private function testVulnerableExamples() {
        echo "Testing Vulnerable Examples...\n";
        echo "-----------------------------\n";

        $vulnerable_file = $this->config['test_files'][0];
        
        foreach ($this->config['rule_packs'] as $pack_name => $rules) {
            foreach ($rules as $rule_file) {
                $rule_path = $this->config['rules_path'] . $pack_name . '/' . $rule_file;
                
                if (file_exists($rule_path)) {
                    $this->runSemgrepTest($vulnerable_file, $rule_path, $pack_name, $rule_file);
                }
            }
        }
    }

    /**
     * Test safe examples - should not trigger security rules
     */
    private function testSafeExamples() {
        echo "\nTesting Safe Examples...\n";
        echo "----------------------\n";

        $safe_file = $this->config['test_files'][2];
        
        foreach ($this->config['rule_packs'] as $pack_name => $rules) {
            foreach ($rules as $rule_file) {
                $rule_path = $this->config['rules_path'] . $pack_name . '/' . $rule_file;
                
                if (file_exists($rule_path)) {
                    $this->runSemgrepTest($safe_file, $rule_path, $pack_name, $rule_file, true);
                }
            }
        }
    }

    /**
     * Test edge cases - complex vulnerability patterns
     */
    private function testEdgeCases() {
        echo "\nTesting Edge Cases...\n";
        echo "-------------------\n";

        $edge_case_file = $this->config['test_files'][1];
        
        foreach ($this->config['rule_packs'] as $pack_name => $rules) {
            foreach ($rules as $rule_file) {
                $rule_path = $this->config['rules_path'] . $pack_name . '/' . $rule_file;
                
                if (file_exists($rule_path)) {
                    $this->runSemgrepTest($edge_case_file, $rule_path, $pack_name, $rule_file);
                }
            }
        }
    }

    /**
     * Run Semgrep test against a specific rule
     */
    private function runSemgrepTest($test_file, $rule_path, $pack_name, $rule_file, $expect_no_findings = false) {
        $command = sprintf(
            '%s scan --config %s %s --json --quiet',
            $this->config['semgrep_binary'],
            escapeshellarg($rule_path),
            escapeshellarg($test_file)
        );

        $output = shell_exec($command);
        $results = json_decode($output, true);

        if ($results === null) {
            echo "  ❌ Error running test: $pack_name/$rule_file\n";
            return;
        }

        $findings = $results['results'] ?? [];
        $finding_count = count($findings);

        if ($expect_no_findings) {
            if ($finding_count === 0) {
                echo "  ✅ Safe example passed: $pack_name/$rule_file (0 findings)\n";
                $this->summary['total_tests']++;
            } else {
                echo "  ❌ False positive: $pack_name/$rule_file ($finding_count findings)\n";
                $this->summary['false_positives']++;
                $this->summary['total_tests']++;
            }
        } else {
            if ($finding_count > 0) {
                echo "  ✅ Vulnerability detected: $pack_name/$rule_file ($finding_count findings)\n";
                $this->summary['vulnerabilities_detected']++;
                $this->summary['total_tests']++;
            } else {
                echo "  ❌ Missed vulnerability: $pack_name/$rule_file (0 findings)\n";
                $this->summary['missed_vulnerabilities']++;
                $this->summary['total_tests']++;
            }
        }

        // Store detailed results
        $this->results[] = [
            'test_file' => $test_file,
            'rule_pack' => $pack_name,
            'rule_file' => $rule_file,
            'findings' => $findings,
            'expect_no_findings' => $expect_no_findings
        ];
    }

    /**
     * Generate comprehensive test report
     */
    private function generateReport() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "ADVANCED TEST RESULTS SUMMARY\n";
        echo str_repeat("=", 50) . "\n\n";

        echo "Test Statistics:\n";
        echo "- Total Tests: {$this->summary['total_tests']}\n";
        echo "- Vulnerabilities Detected: {$this->summary['vulnerabilities_detected']}\n";
        echo "- False Positives: {$this->summary['false_positives']}\n";
        echo "- Missed Vulnerabilities: {$this->summary['missed_vulnerabilities']}\n";
        echo "- Test Duration: " . number_format($this->summary['test_duration'], 2) . " seconds\n\n";

        $detection_rate = $this->summary['total_tests'] > 0 ? 
            ($this->summary['vulnerabilities_detected'] / $this->summary['total_tests']) * 100 : 0;
        
        $false_positive_rate = $this->summary['total_tests'] > 0 ? 
            ($this->summary['false_positives'] / $this->summary['total_tests']) * 100 : 0;

        echo "Performance Metrics:\n";
        echo "- Detection Rate: " . number_format($detection_rate, 1) . "%\n";
        echo "- False Positive Rate: " . number_format($false_positive_rate, 1) . "%\n\n";

        // Detailed findings analysis
        $this->analyzeFindings();
        
        // Generate recommendations
        $this->generateRecommendations();
    }

    /**
     * Analyze detailed findings
     */
    private function analyzeFindings() {
        echo "Detailed Findings Analysis:\n";
        echo "-------------------------\n";

        $vulnerability_types = [];
        $rule_performance = [];

        foreach ($this->results as $result) {
            $rule_key = $result['rule_pack'] . '/' . $result['rule_file'];
            
            if (!isset($rule_performance[$rule_key])) {
                $rule_performance[$rule_key] = [
                    'tests' => 0,
                    'detections' => 0,
                    'false_positives' => 0,
                    'missed' => 0
                ];
            }

            $rule_performance[$rule_key]['tests']++;
            
            if ($result['expect_no_findings']) {
                if (count($result['findings']) > 0) {
                    $rule_performance[$rule_key]['false_positives']++;
                }
            } else {
                if (count($result['findings']) > 0) {
                    $rule_performance[$rule_key]['detections']++;
                } else {
                    $rule_performance[$rule_key]['missed']++;
                }
            }

            // Categorize vulnerability types
            foreach ($result['findings'] as $finding) {
                $check_id = $finding['check_id'] ?? 'unknown';
                $parts = explode('-', $check_id);
                $vuln_type = $parts[0] ?? 'unknown';
                
                if (!isset($vulnerability_types[$vuln_type])) {
                    $vulnerability_types[$vuln_type] = 0;
                }
                $vulnerability_types[$vuln_type]++;
            }
        }

        // Rule performance summary
        echo "\nRule Performance:\n";
        foreach ($rule_performance as $rule => $stats) {
            $detection_rate = $stats['tests'] > 0 ? 
                ($stats['detections'] / $stats['tests']) * 100 : 0;
            
            echo "- $rule: " . number_format($detection_rate, 1) . "% detection rate";
            if ($stats['false_positives'] > 0) {
                echo " ({$stats['false_positives']} false positives)";
            }
            if ($stats['missed'] > 0) {
                echo " ({$stats['missed']} missed)";
            }
            echo "\n";
        }

        // Vulnerability type distribution
        echo "\nVulnerability Type Distribution:\n";
        arsort($vulnerability_types);
        foreach ($vulnerability_types as $type => $count) {
            echo "- $type: $count findings\n";
        }
    }

    /**
     * Generate recommendations based on test results
     */
    private function generateRecommendations() {
        echo "\nRecommendations:\n";
        echo "----------------\n";

        if ($this->summary['missed_vulnerabilities'] > 0) {
            echo "⚠️  Consider improving rule coverage for missed vulnerabilities\n";
        }

        if ($this->summary['false_positives'] > 0) {
            echo "⚠️  Review rules with false positives to improve accuracy\n";
        }

        if ($this->summary['vulnerabilities_detected'] > 0) {
            echo "✅ Rules are successfully detecting advanced vulnerabilities\n";
        }

        $detection_rate = $this->summary['total_tests'] > 0 ? 
            ($this->summary['vulnerabilities_detected'] / $this->summary['total_tests']) * 100 : 0;

        if ($detection_rate < 80) {
            echo "⚠️  Detection rate below 80% - consider rule improvements\n";
        } else {
            echo "✅ Good detection rate achieved\n";
        }

        echo "\nNext Steps:\n";
        echo "1. Review missed vulnerabilities and enhance rule patterns\n";
        echo "2. Investigate false positives and refine rule logic\n";
        echo "3. Add more edge case test scenarios\n";
        echo "4. Consider performance optimization for large codebases\n";
    }

    /**
     * Export results to JSON file
     */
    public function exportResults($filename = 'advanced-test-results.json') {
        $export_data = [
            'summary' => $this->summary,
            'results' => $this->results,
            'timestamp' => date('Y-m-d H:i:s'),
            'config' => $this->config
        ];

        file_put_contents($filename, json_encode($export_data, JSON_PRETTY_PRINT));
        echo "\nResults exported to: $filename\n";
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $runner = new AdvancedTestRunner($config);
    $runner->runAllTests();
    $runner->exportResults();
} else {
    echo "This script should be run from the command line.\n";
    echo "Usage: php run-advanced-tests.php\n";
}
?>
