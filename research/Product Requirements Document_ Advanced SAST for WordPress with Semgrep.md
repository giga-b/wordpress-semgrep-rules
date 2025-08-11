# **Product Requirements Document: Advanced SAST for WordPress with Semgrep**

|  |  |
| :---- | :---- |
| **Document Status:** | Draft |
| **Author:** | Product Management |
| **Last Updated:** | August 11, 2025 |
| **Target Release:** | Phased Rollout (See Section 7\) |

## **1\. Introduction**

WordPress powers over 40% of the web, but its ecosystem of plugins and themes is a significant source of security vulnerabilities. Generic Static Application Security Testing (SAST) tools often fail to understand the nuances of the WordPress framework, leading to two critical problems: **1\) missed vulnerabilities**, particularly complex ones like Cross-Site Request Forgery (CSRF), and **2\) high false-positive rates**, which cause alert fatigue and erode developer trust.

This document outlines the requirements for building a high-fidelity, scalable SAST program specifically for the WordPress ecosystem using Semgrep. The goal is to move beyond generic checks and create a targeted, intelligent scanning capability that finds real, high-impact vulnerabilities with precision.

## **2\. Product Goals and Objectives**

The primary objective is to create a best-in-class SAST solution for WordPress that is both effective and trusted by developers.

* **Goal 1: Drastically Improve Critical Vulnerability Detection.**  
  * **Objective:** Achieve \>90% detection for common, high-impact WordPress vulnerabilities, including incomplete nonce validation (CSRF) and insecure file handling (Path Traversal, Arbitrary File Upload).  
* **Goal 2: Build a Scalable and Maintainable Rule Architecture.**  
  * **Objective:** Structure Semgrep rules into tiered packs that support both blocking CI/CD builds on high-confidence findings and performing deeper offline audits on more experimental rules.  
* **Goal 3: Reduce Noise and Foster Developer Trust.**  
  * **Objective:** Ensure the primary, developer-facing ruleset maintains a false-positive rate of \<5% when tested against a large corpus of real-world code.  
* **Goal 4: Establish Clear Operational Governance.**  
  * **Objective:** Implement and document a formal policy for suppressing and triaging findings to ensure the program's long-term health and effectiveness.

## **3\. User Personas**

* **Priyanka, the Security Engineer (Primary Persona):** Priyanka is responsible for the security of all WordPress applications. She needs a tool that can find complex, framework-specific bugs automatically. She spends her time writing and tuning custom rules, validating their accuracy, and ensuring the SAST program can scale across dozens of projects without overwhelming developers.  
* **David, the Developer (Secondary Persona):** David builds and maintains WordPress plugins and themes. He wants to ship features quickly and securely. He needs security feedback that is fast, accurate, and actionable. He trusts tools that give him clear findings with low noise and gets frustrated by generic alerts that aren't relevant to his code.

## **4\. Features & Requirements**

This project is broken down into three priority tiers, starting with the most critical security gaps.

### **P0: Critical Vulnerability Detection (Implement First)**

#### **Feature 1.1: Complete Nonce Lifecycle Analysis for CSRF**

* **User Story:** As Priyanka, I want to automatically detect every instance where a WordPress AJAX action is registered without a corresponding, correctly implemented nonce verification check, so I can eliminate CSRF vulnerabilities at scale.  
* **Requirements:**  
  * **1.1.1:** Develop a custom Semgrep rule using join mode to perform cross-file analysis.  
  * **1.1.2:** The rule must identify all wp\_ajax\_ and wp\_ajax\_nopriv\_ action hooks as taint sources.  
  * **1.1.3:** The rule must trace the execution path from the hook registration to its callback function and confirm the presence of a wp\_verify\_nonce() or check\_admin\_referer() call.  
  * **1.1.4:** The nonce verification call must use the same action name defined in the nonce creation step.  
* **Acceptance Criteria:**  
  * The rule correctly flags a plugin where a nonce is created on the frontend but its verification is missing in the backend AJAX callback.  
  * The rule does **not** flag a plugin where the full create-include-verify lifecycle is correctly implemented.

#### **Feature 1.2: Secure File Handling & Archive Extraction Rules**

* **User Story:** As Priyanka, I want to automatically detect when a plugin uses insecure file upload or archive extraction patterns, so I can prevent path traversal and arbitrary file upload attacks.  
* **Requirements:**  
  * **1.2.1:** Integrate and customize the doyensec/Unsafe-Unpacking Semgrep ruleset to detect path traversal vulnerabilities during archive extraction.  
  * **1.2.2:** Develop a custom taint-tracking rule that flags any data from the $\_FILES superglobal that reaches a sensitive file system sink (e.g., move\_uploaded\_file, file\_put\_contents) without being sanitized or validated.  
* **Acceptance Criteria:**  
  * The ruleset successfully identifies known-vulnerable code patterns for path traversal using unzip\_file.  
  * The taint-tracking rule flags code where $\_FILES\['userfile'\]\['name'\] is used directly to construct a file path.

#### **Feature 1.3: Attack Corpus & Rule Validation Pipeline**

* **User Story:** As Priyanka, I need a repeatable, automated way to test my custom rules against thousands of real-world plugins, so I can confidently measure their accuracy and fix false positives before deploying them to developers.  
* **Requirements:**  
  * **1.3.1:** Create an automated script, leveraging a tool like johnbillion/slurpetta, to download the top 2,000 most popular plugins from the WordPress.org repository.  
  * **1.3.2:** Build a CI/CD pipeline (e.g., GitHub Actions) that is triggered manually or on a schedule.  
  * **1.3.3:** The pipeline must run the custom Semgrep rules against the entire downloaded corpus and generate a summary report detailing findings per rule.  
* **Acceptance Criteria:**  
  * The pipeline successfully downloads the plugins and runs a scan within a reasonable time frame.  
  * The output report is clear and allows Priyanka to quickly identify noisy or ineffective rules.

### **P1: SAST Program Scalability (Implement Next)**

#### **Feature 2.1: Tiered Rule Pack Architecture**

* **User Story:** As David, I only want my build to be blocked by security findings that are highly confident and directly actionable, so I can maintain development velocity.  
* **Requirements:**  
  * **2.1.1:** Create and maintain two distinct Semgrep configurations.  
  * **2.1.2:** **ci-blocking.yml:** A curated, high-precision ruleset containing only rules with a near-zero false positive rate. This configuration will be used to comment on pull requests and block builds.  
  * **2.1.3:** **audit.yml:** A comprehensive ruleset that includes all rules from the blocking pack plus more experimental or informational rules intended for offline review by the security team.  
* **Acceptance Criteria:**  
  * The CI/CD system can invoke either configuration independently.  
  * A clear process is documented for promoting a rule from audit.yml to ci-blocking.yml.

#### **Feature 2.2: Custom Rules for Options & Settings APIs**

* **User Story:** As Priyanka, I want to automatically detect when a plugin saves un-sanitized data via the WordPress Options or Settings APIs, so I can prevent a common source of Stored XSS.  
* **Requirements:**  
  * **2.2.1:** Develop a pattern-based Semgrep rule that finds all calls to register\_setting(). The rule must flag any call that does **not** provide a sanitization callback function as its third argument.  
  * **2.2.2:** Develop a taint-tracking rule where user input ($\_POST, $\_GET) is the source and update\_option() is the sink, flagging any data flow that does not pass through a known sanitization function.  
* **Acceptance Criteria:**  
  * The rules successfully identify missing sanitization callbacks and tainted data being passed directly to update\_option().

### **P2: Operational Governance (Implement Last)**

#### **Feature 3.1: Formalized Suppression & Triage Policy**

* **User Story:** As David, when I encounter a false positive, I need a simple and approved way to suppress it for a specific line of code, so it doesn't reappear on every scan.  
* **Requirements:**  
  * **3.1.1:** Create and publish a formal policy document outlining the approved hierarchy for ignoring Semgrep findings.  
  * **3.1.2:** The hierarchy must be (from most to least preferred):  
    1. **Inline // nosemgrep comment:** For a specific, justified false positive. Requires a comment explaining the reason.  
    2. **.semgrepignore file:** For ignoring third-party libraries, test files, or entire directories not under active development.  
    3. **Platform-level Triage (Semgrep AppSec Platform):** For triaging a finding across all branches. Requires justification and is managed by the Security Team.  
    4. **Rule Removal/Demotion:** For rules that prove to be globally too noisy. Requires Security Team review and approval.  
* **Acceptance Criteria:**  
  * A policy document is published in a central knowledge base (e.g., Confluence, Notion).  
  * The policy is communicated to all engineering teams.

## **5\. Metrics & Success Criteria**

| Metric | Current Benchmark | Target |
| :---- | :---- | :---- |
| **Critical Vulnerability Detection Rate** | \< 40% (with generic rules) | \> 90% (on corpus) |
| **False Positive Rate (ci-blocking.yml)** | \~25% (with generic rules) | \< 5% (on corpus) |
| **Mean Time to Remediate (MTTR) for Criticals** | 30+ days | \< 7 days |
| **Developer Trust Score (Survey)** | 2/5 | 4/5 |

## **6\. Out of Scope for this Initiative**

* Dynamic Application Security Testing (DAST).  
* Auto-remediation of findings using Semgrep's fix key (can be explored as a fast-follow).  
* Expanding custom rules to cover other complex WordPress APIs (e.g., WP\_Query for SQLi, Shortcodes for XSS) will be handled in a subsequent project.

## **7\. Phased Release Plan**

* **Phase 1 (Q4 2025): Foundational Detection.**  
  * Develop and validate P0 features (Nonce, File Handling, Corpus).  
  * Run the audit.yml ruleset in a non-blocking, audit-only mode.  
  * **Goal:** Gather baseline data and refine critical rules.  
* **Phase 2 (Q1 2026): Scalability & Pilot.**  
  * Develop and validate P1 features (Tiered Packs, Options/Settings rules).  
  * Roll out the ci-blocking.yml configuration to a pilot group of developers for feedback.  
  * **Goal:** Test the developer experience and CI/CD integration.  
* **Phase 3 (Q2 2026): Full Rollout & Governance.**  
  * Implement P2 feature (Suppression Policy).  
  * Roll out the full program, including blocking builds, to all WordPress development teams.  
  * **Goal:** Fully operationalize the advanced SAST program.