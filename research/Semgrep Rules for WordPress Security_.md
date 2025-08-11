

# **A Strategic Framework for WordPress Security Analysis with Semgrep**

## **Introduction: A Strategic Framework for WordPress Security with Semgrep**

Semgrep has emerged as a formidable tool in the Static Application Security Testing (SAST) landscape, distinguished by its speed, flexibility, and a rule syntax that mirrors the source code it analyzes.1 This design makes it uniquely suited to address the significant security challenges posed by the WordPress ecosystem. The platform's immense popularity, powering a substantial portion of the web, is largely due to its extensibility through a vast repository of third-party plugins and themes. However, this extensibility is also its greatest security liability. The quality, security posture, and maintenance level of these third-party components vary dramatically, creating a complex and inconsistent attack surface.3 Common vulnerability patterns, such as Cross-Site Request Forgery (CSRF), SQL Injection (SQLi), and File Inclusion, are rampant, often stemming from developers' failure to correctly implement WordPress's security APIs.5

Effective security analysis of this environment demands a tool that can move beyond generic vulnerability signatures and adapt to the specific nuances of the WordPress core APIs and common plugin architectures. Semgrep's customizable nature allows security teams to codify their domain-specific knowledge of WordPress into precise, high-fidelity rules.

This report provides a definitive, actionable guide to building a robust WordPress SAST program using Semgrep. It is structured to address security priorities in a tiered approach, from critical, high-impact vulnerabilities to the operational policies necessary for a sustainable program. The objective is to equip technical security professionals with the knowledge to move beyond default rulesets, author custom rules for complex WordPress-specific patterns, and implement a strategic, corpus-driven methodology for rule validation and deployment. By following this framework, organizations can transform their WordPress security from a reactive process into a proactive, automated, and highly effective component of their software development lifecycle.

## **Section 1: High-Priority Resources for Critical WordPress Vulnerabilities**

This section addresses the most severe and prevalent security flaws found within the WordPress ecosystem. The focus is on developing and sourcing high-fidelity Semgrep rules capable of detecting complex, multi-stage vulnerabilities that are often missed by generic scanning tools. The methodologies presented here form the technical foundation of a mature WordPress SAST program.

### **Mastering the Nonce Lifecycle: Custom Rules for CSRF Protection**

The primary defense mechanism against Cross-Site Request Forgery (CSRF) attacks in WordPress is the nonce, a unique token intended to verify the origin and intent of a request.7 A successful CSRF attack can allow an adversary to trick an authenticated user's browser into performing unwanted state-changing actions, such as changing settings, deleting content, or, in the case of administrative users, compromising the entire site. The security of the nonce system is therefore paramount, yet its effectiveness is entirely dependent on correct implementation by plugin and theme developers.

The nonce lifecycle consists of three critical stages that must occur in sequence for the protection to be effective:

1. **Creation**: A nonce must be generated on the server for a specific action and user session. This is typically accomplished using the wp\_create\_nonce() function.8  
2. **Inclusion**: The generated nonce must be embedded within the HTML of the web page, either in a hidden form field or as a URL query parameter. WordPress provides helper functions for this, such as wp\_nonce\_field() for forms and wp\_nonce\_url() for links.7  
3. **Verification**: When the user submits the form or clicks the link, the corresponding server-side handler must validate the nonce before executing any sensitive operations. This is the most critical and frequently omitted step. Verification is performed by functions like wp\_verify\_nonce(), check\_admin\_referer(), or, specifically for AJAX requests, check\_ajax\_referer().8

Verifying the integrity of this entire lifecycle presents a classic and significant challenge for static analysis. A finding is only valid if a state-changing action, often registered via an AJAX hook (add\_action('wp\_ajax\_...')), is executed *without* a preceding call to a nonce verification function within its execution path. This requires the analysis engine to connect a hook registration in one file to a function definition in another, and then trace the call graph within that function to determine the absence of a security check.

Standard static analysis tools often struggle with this level of cross-file and cross-functional analysis. The Semgrep Community Edition, while powerful, is primarily designed for analysis within the boundaries of a single function or file, making this type of check difficult to implement reliably.1 While Semgrep Pro offers advanced cross-file dataflow analysis that is better suited for this task, a groundbreaking piece of public research demonstrates a method to approximate this check using the experimental features of the community engine.9

The most comprehensive public resource on this topic is a detailed technical article, "Automating CSRF Detection in WordPress Plugins with Semgrep".10 This work serves as a case study in advanced rule development. The author successfully implements a cross-file check by leveraging Semgrep's experimental

join mode. This mode allows the engine to correlate findings from multiple, simpler rules. The methodology involves defining three distinct patterns:

1. **AJAX Action Registration**: A rule to identify all registrations of AJAX actions via add\_action() where the hook name matches the pattern wp\_ajax\_..., capturing the name of the handler function as a metavariable ($HOOKFUNC).  
2. **Function Call Graph**: A rule that maps function definitions to the functions they call, establishing a caller-callee relationship.  
3. **Nonce Verification Functions**: A rule that simply identifies all calls to the known CSRF protection functions (check\_ajax\_referer, wp\_verify\_nonce, etc.).

The core logic of the join rule is to flag any AJAX action whose handler function ($HOOKFUNC) does *not* appear in a call graph that ultimately leads to a call to one of the nonce verification functions. This effectively performs a "negative reachability" analysis. It is a crucial detail that the author had to locally patch their Semgrep instance to introduce a new \!\! operator to represent this "will not call" logic, highlighting that this advanced technique pushes the boundaries of the standard engine's capabilities.10

The prioritization of a "Complete Nonce Lifecycle Rule" indicates an understanding of the critical nature of CSRF protection in WordPress. The available research demonstrates that while automated verification is achievable, it is not an out-of-the-box capability. It requires a significant investment in custom rule development, a deep understanding of Semgrep's advanced features, and a robust process for triaging the inevitable false positives. The author of the aforementioned article scanned over 2,500 plugins and identified 510 with potential findings, a volume that underscores the necessity of a well-defined triage strategy, which will be discussed in Section 3 of this report.10 The effort is justified by the high prevalence and severe impact of CSRF vulnerabilities across the WordPress plugin ecosystem.11

| Name/Title | URL | Summary of Function | Reviews/Ratings |
| :---- | :---- | :---- | :---- |
| Automating CSRF Detection in WordPress Plugins with Semgrep | [https://noob3xploiter.medium.com/automating-csrf-detection-in-wordpress-plugins-with-semgrep-52ece2c212b7](https://noob3xploiter.medium.com/automating-csrf-detection-in-wordpress-plugins-with-semgrep-52ece2c212b7) | A detailed technical walkthrough of creating a custom, cross-file Semgrep rule using join mode to detect missing nonce verification in WordPress AJAX actions. Includes the full YAML rule definition. | Highly influential in the WP security research community. Cited as inspiration by other tool developers.12 No formal rating system, but its methodology is sound and advanced. |
| WordPress CVE Scanner | [https://github.com/Michele0303/wordpress-cve-scanner](https://github.com/Michele0303/wordpress-cve-scanner) | An experimental project that uses custom Semgrep rules to find vulnerabilities in WordPress plugins. It explicitly cites the "Automating CSRF Detection" article as a key inspiration for its rule development. | This is a tool/project, not a standalone rule. Its value is as a practical application and potential source of further custom rules. No formal reviews. |
| wp-ajax-no-auth-and-auth-hooks-audit | [https://semgrep.dev/playground/r/jQT0JK/php.wordpress-plugins.security.audit.wp-ajax-no-auth-and-auth-hooks-audit.wp-ajax-no-auth-and-auth-hooks-audit](https://semgrep.dev/playground/r/jQT0JK/php.wordpress-plugins.security.audit.wp-ajax-no-auth-and-auth-hooks-audit.wp-ajax-no-auth-and-auth-hooks-audit) | A basic Semgrep Registry rule that identifies the registration of AJAX hooks (wp\_ajax\_... and wp\_ajax\_nopriv\_...). This serves as a foundational pattern for building more complex CSRF rules. | N/A (Official Semgrep Registry rule). |
| Understanding and Using WordPress Nonces Properly | [https://developer.wordpress.org/news/2023/08/understand-and-use-wordpress-nonces-properly/](https://developer.wordpress.org/news/2023/08/understand-and-use-wordpress-nonces-properly/) | Official WordPress developer documentation explaining the purpose, creation, and verification of nonces. Essential background reading for writing accurate rules. | N/A (Official documentation). |

### **Securing File Handling: Rules for Uploads and Archive Extraction**

Insecure file handling operations represent one of the most direct vectors for server compromise in the WordPress ecosystem. Vulnerabilities in this category allow attackers to place and execute malicious code, bypass access controls, or exfiltrate sensitive data. These flaws typically manifest in two primary classes:

1. **Arbitrary File Upload**: This vulnerability occurs when a plugin allows a user to upload a file but fails to properly validate its type or contents. An attacker can exploit this to upload a PHP web shell or other executable script, granting them the ability to run arbitrary commands on the server.13  
2. **Path Traversal / File Inclusion**: This class of vulnerability allows an attacker to manipulate file paths to read, delete, or execute files outside of the intended directory. A common and particularly dangerous variant involves the insecure extraction of archive files (such as ZIP), where a malicious archive containing file paths with traversal sequences (e.g., ../../shell.php) is unpacked, leading to arbitrary file writes.14

The Semgrep Registry provides baseline rules that can detect simple instances of these vulnerabilities. The p/phpcs-security-audit ruleset, for example, contains a generic file-inclusion rule that flags the use of non-constant variables within include, require, include\_once, or require\_once statements.16 A more tailored rule,

wp-file-inclusion-audit, exists in the Semgrep Playground and is specifically designed to find these patterns in a WordPress context.18

While these rules are valuable for identifying basic Local File Inclusion (LFI) and Remote File Inclusion (RFI) patterns, they are often insufficient for detecting more subtle vulnerabilities embedded in complex file operations. Modern WordPress vulnerabilities frequently occur not in simple include statements, but within the logic of custom file downloaders or, more critically, in archive extraction routines.14

The research conducted by Doyensec on "Unsafe Unpacking" provides a critical set of resources for addressing the threat of path traversal via archive extraction.15 Their work highlights that archive files can be crafted to contain malicious file paths. If a plugin's extraction logic does not sanitize the name of each file within the archive before writing it to the filesystem, an attacker can write a file to any location on the server that the web user has permissions for. To combat this, Doyensec developed and published a dedicated GitHub repository containing a comprehensive set of Semgrep rules designed to detect these unsafe patterns across multiple languages, including PHP.

The existence of this specialized rule pack underscores a crucial point in modern SAST strategy: generic rulesets are necessary but not sufficient. The complexity of vulnerabilities like unsafe archive unpacking requires rules that possess a semantic understanding of the specific APIs used for those operations (e.g., PHP's ZipArchive class and its methods). Relying solely on general-purpose file inclusion rules would completely miss this entire class of high-impact vulnerabilities. Security teams must therefore actively seek out, evaluate, and integrate these specialized, third-party rule packs. The Doyensec repository is a prime example of how deep security research into a specific bug class can yield a dedicated, high-value Semgrep pack that significantly outperforms generic rulesets.

| Name/Title | URL | Summary of Function | Reviews/Ratings |
| :---- | :---- | :---- | :---- |
| Unsafe-Unpacking GitHub Repository | [https://github.com/doyensec/Unsafe-Unpacking](https://github.com/doyensec/Unsafe-Unpacking) | A comprehensive repository by Doyensec containing Semgrep rules, lab code, and secure implementation guides for detecting unsafe archive extraction (Path Traversal) vulnerabilities across multiple languages, including PHP. | Highly regarded within the security community. The associated blog post 15 provides deep technical detail. This is a critical, high-quality resource. |
| wp-file-inclusion-audit | [https://semgrep.dev/playground/r/php.wordpress-plugins.security.audit.wp-file-inclusion-audit.wp-file-inclusion-audit](https://semgrep.dev/playground/r/php.wordpress-plugins.security.audit.wp-file-inclusion-audit.wp-file-inclusion-audit) | A community-provided Semgrep rule in the Playground specifically designed to audit for file inclusion patterns common in WordPress plugins, targeting functions like require, require\_once, include, and include\_once. | N/A (Playground rule). It is a valuable, WordPress-specific starting point. |
| p/phpcs-security-audit Ruleset | [https://semgrep.dev/p/phpcs-security-audit](https://semgrep.dev/p/phpcs-security-audit) | A Semgrep ruleset ported from the popular phpcs-security-audit tool. It includes a generic file-inclusion rule that detects non-constant file inclusions, which is a good baseline check. | N/A (Official Semgrep Registry ruleset). |

### **Establishing a Security Baseline: Attack Corpus and Scanning Strategy**

A security baseline is a comprehensive snapshot of the security posture across a large, representative body of code. For the WordPress ecosystem, this involves scanning thousands of real-world plugins and themes to understand the prevalence of various vulnerabilities and coding anti-patterns.3 Establishing such a baseline serves two critical strategic purposes:

1. **Rule Validation and Tuning**: Before a Semgrep rule is deployed into a CI/CD pipeline where it might block developer merges, it must be tested against a massive corpus of code. This process allows the security team to identify rules that are overly "noisy" (produce a high rate of false positives) and refine their patterns for better precision.  
2. **Threat Intelligence and Prioritization**: Analyzing the results of a baseline scan reveals which vulnerability classes and insecure coding practices are most common across the ecosystem. This data-driven insight is invaluable for prioritizing the development of new custom rules.

The foundational step in this process is building the code corpus. The **Slurpetta** tool is an excellent resource for this task.22 It is a command-line script that efficiently downloads the latest stable versions of all WordPress plugins with at least 10,000 active installations and all themes with at least 1,000 active installations. This targeted approach is far more efficient than attempting to download the entire WordPress repository, which contains over 100,000 items, many of which are outdated or have a negligible user base.22 Other projects, such as the experimental WordPress CVE Scanner, also utilize this mass-download methodology, affirming its validity as a research technique.12

Once the corpus is assembled, the baseline scan can be executed. The Slurpetta documentation provides the precise Semgrep command for scanning the entire downloaded plugin directory with the official WordPress ruleset: semgrep \--config "p/wordpress" \--no-git-ignore plugins.22 The expected outcome is a large volume of findings that will require careful analysis and triage. The author of Slurpetta notes that the

p/wordpress ruleset is relatively low-noise, yielding approximately 50 results at the time of writing. In contrast, running the broader p/php ruleset produces a "very large number" of findings, highlighting the importance of starting with a framework-specific configuration.22

The strategy of building and scanning a local corpus is not merely an academic exercise; it is a critical, practical step for any organization seeking to operationalize SAST for WordPress effectively. Running a broad ruleset directly in a CI/CD pipeline without prior tuning is a common failure pattern for SAST programs. The resulting flood of false positives leads to developer friction, alert fatigue, and an erosion of trust in the security tooling. By performing this validation offline against a representative corpus, a security team can transform its rule management process from a reactive, "whack-a-mole" effort into a proactive, data-driven methodology. This approach is the key to curating a high-confidence, low-noise ruleset that is suitable for enforcement—such as blocking builds—within a CI/CD environment, thereby maximizing security impact while minimizing disruption to development workflows.

| Name/Title | URL | Summary of Function | Reviews/Ratings |
| :---- | :---- | :---- | :---- |
| Slurpetta | [https://github.com/johnbillion/slurpetta](https://github.com/johnbillion/slurpetta) | A command-line script to download the most popular WordPress plugins and themes. Essential for creating a local "attack corpus" for testing and validating Semgrep rules at scale. | Highly practical and well-documented. The author provides explicit examples of how to use it with Semgrep. |
| WordPress CVE Scanner | [https://github.com/Michele0303/wordpress-cve-scanner](https://github.com/Michele0303/wordpress-cve-scanner) | An experimental project that combines a plugin downloader with custom Semgrep rules. Serves as a conceptual example of a complete corpus-driven scanning pipeline. | N/A (Experimental project). |
| Building a Vulnerable WordPress Test Environment | (Synthesized from multiple sources) | Guidance on setting up a local lab environment using tools like Docker or LocalWP to dynamically test, debug, and verify potential vulnerabilities identified during the baseline scan.23 | N/A (Synthesized guidance). |

## **Section 2: Medium-Priority Configurations and Customizations**

With a foundation for detecting critical vulnerabilities established, the next priority is to structure the Semgrep rules and configurations for scalability and to extend coverage to framework-specific APIs that lack public rules. This section details how to organize rule packs and outlines a methodology for developing custom rules for the WordPress Options and Settings APIs.

### **Structuring Rule Packs for Frameworks and Projects**

Effective management of a SAST program at scale requires a deliberate and organized approach to rule management. Semgrep facilitates this through the concept of "packs" or "rulesets," which are curated collections of rules designed for a specific language, framework, or vulnerability class.29 The Semgrep Registry follows a standard namespacing convention for these packs, typically

\<language\>/\<framework\>/\<category\>/..., which allows for logical grouping and discovery.31

A **Framework Pack** is designed to provide broad, general-purpose coverage for a specific technology. The p/wordpress ruleset is the canonical example for this ecosystem.22 These packs are maintained by Semgrep and the broader security community and serve as the essential starting point for any scan. A related concept is the

p/auto configuration, which is a meta-ruleset that intelligently selects the appropriate framework packs to run based on an analysis of the scanned project's files and dependencies.32 For a WordPress project, this would almost certainly include the

p/wordpress pack.

While framework packs provide a crucial baseline, a mature SAST program requires a **Project-Specific Pack**. These are custom, internal rulesets developed to address the unique context of an organization's codebase. The need for such a pack arises from several factors:

* **Enforcing Internal Standards**: To codify and automate the enforcement of internal secure coding guidelines.  
* **Auditing Proprietary APIs**: To detect the misuse of internal libraries or APIs that are not covered by public rules.  
* **Reducing Noise**: To create high-confidence, hardened versions of public rules that may be too noisy for direct use in a blocking CI/CD gate.

The fullstorydev/semgrep-rules repository serves as an excellent public model for a project-specific pack.33 The authors explicitly state that while many of their rules are tailored to their internal codebase, they publish those that are broadly applicable, demonstrating a best practice of contributing back to the community.

A recommended structure for an internal, project-specific rules repository includes a root directory containing subdirectories organized by language or vulnerability class. Each rule should be in its own .yaml file, accompanied by a corresponding test file (e.g., my-rule.yaml and my-rule.php). This test file is critical and must contain at least one true positive test case (annotated with // ruleid: my-rule-id) and one true negative test case (annotated with // ok: my-rule-id) to ensure the rule behaves as expected.31

The true power of Semgrep's configuration system lies in its ability to combine multiple rulesets in a single scan. A security team can run the official WordPress pack alongside its own custom rules with a single command: semgrep scan \--config="p/wordpress" \--config="path/to/my-rules/"..29

This capability enables an optimal, tiered rule strategy that balances broad coverage with low developer friction. Relying solely on a generic framework pack like p/wordpress will miss organization-specific logic and may be too noisy for use as a strict quality gate. Conversely, relying only on project-specific rules means forfeiting the extensive coverage provided by the community and Semgrep's security research team. The most effective approach is therefore a hybrid:

* **Tier 1 (Broad Monitoring)**: Employ broad, community-driven framework packs like p/wordpress and p/phpcs-security-audit. These rules should be configured in a non-blocking mode, such as "monitor" or "comment," within the CI/CD pipeline. They serve to cast a wide net, identifying a broad range of potential issues for offline review by the security team.  
* **Tier 2 (Strict Blocking)**: Develop and maintain a highly curated, low-noise, project-specific pack. This pack should contain rules for internal APIs and hardened versions of public rules that have been rigorously tested against the organization's code corpus. This ruleset is run in "blocking" mode, failing the CI/CD build if any findings are detected.

This tiered strategy maximizes vulnerability coverage while ensuring that developers are only blocked by high-confidence findings, a crucial factor for the successful adoption and long-term viability of any SAST program.

| Name/Title | URL | Summary of Function | Reviews/Ratings |
| :---- | :---- | :---- | :---- |
| p/wordpress Ruleset | [https://github.com/johnbillion/slurpetta](https://github.com/johnbillion/slurpetta) (via usage example) | The official Semgrep Registry ruleset for WordPress vulnerabilities. It is the foundational pack for any WordPress scan. The direct registry URL is implicitly semgrep.dev/p/wordpress. | N/A (Official ruleset). The Slurpetta author notes it is relatively low-noise. |
| fullstorydev/semgrep-rules | [https://github.com/fullstorydev/semgrep-rules](https://github.com/fullstorydev/semgrep-rules) | An excellent public example of a well-structured, project-specific rules repository that includes custom rules for various languages. A model for creating an internal pack. | N/A. |
| Semgrep Rule Syntax Documentation | [https://semgrep.dev/docs/writing-rules/rule-syntax](https://semgrep.dev/docs/writing-rules/rule-syntax) | The official documentation detailing the YAML structure of a Semgrep rule, including all required and optional fields. Essential for writing any custom rule. | N/A (Official documentation). |
| Semgrep Structure Mode | [https://semgrep.dev/blog/2024/structure-mode-never-write-an-invalid-semgrep-rule/](https://semgrep.dev/blog/2024/structure-mode-never-write-an-invalid-semgrep-rule/) | A UI-based approach in the Semgrep Playground that simplifies rule writing by guiding users, preventing syntax errors, and offering features like match badges and drag-and-drop.34 | N/A (Tool feature). Highly recommended for new rule writers. |

### **Auditing the WordPress Options and Settings APIs**

The WordPress Options and Settings APIs provide the core functionality for plugins and themes to store persistent configuration data in the wp\_options database table.36 This mechanism is fundamental to WordPress's operation, but it also represents a critical and often under-audited attack surface. If a plugin insecurely saves user-controlled input into an option that is later rendered on a page without proper escaping, it can lead to severe vulnerabilities such as persistent Cross-Site Scripting (XSS).

There are two primary sets of functions that interact with this system:

* **Settings API**: This is a higher-level API used to create standardized settings pages within the WordPress admin dashboard. The key functions are register\_setting(), add\_settings\_section(), and add\_settings\_field().36 From a security perspective, the most important parameter is the optional sanitization callback that can be passed to  
  register\_setting(). This callback function is responsible for validating and cleaning the data before it is saved to the database. The absence of this callback is a significant security anti-pattern.  
* **Options API**: These are lower-level functions that provide direct access to the wp\_options table. The primary functions are add\_option(), update\_option(), and get\_option().40 Security vulnerabilities often arise from direct calls to  
  update\_option() where the value being saved comes from user input (e.g., $\_POST) without being passed through a sanitization function first.

A deep search for existing Semgrep resources revealed a significant gap: there are no publicly available, pre-written rules specifically designed to audit the secure usage of the WordPress Settings or Options APIs. This is a common scenario in advanced SAST, where generic rulesets cover common vulnerability classes but lack the domain-specific knowledge to audit framework-specific APIs. Consequently, this is an area that requires custom rule development.

The process of writing effective rules for these APIs demonstrates the necessity of combining framework expertise with SAST tool proficiency. A security engineer must first become an expert on the target API, building a threat model by identifying which functions are sensitive, which parameters are security-critical, and what constitutes "good" versus "bad" usage patterns. Only then can this threat model be translated into precise Semgrep rule logic.

For the WordPress Options and Settings APIs, two custom rules would provide significant security value:

1. **A Taint Analysis Rule for update\_option()**: This rule would use Semgrep's taint-tracking mode to detect insecure data flows.  
   * **Sources**: User-controlled superglobals in PHP, such as $\_POST, $\_GET, and $\_REQUEST.42  
   * **Sinks**: The second argument of the update\_option() function, which represents the value to be saved. The pattern would be update\_option($OPTION\_NAME, $TAINTED\_VALUE);.  
   * **Sanitizers**: The suite of WordPress data validation and sanitization functions, such as sanitize\_text\_field(), esc\_html(), absint(), and wp\_kses().37  
   * **Logic**: The rule would raise an alert whenever a data path is traced from a source to the sink without first passing through a sanitizer function.  
2. **A Search-Based Rule for register\_setting()**: This rule would enforce the best practice of always providing a sanitization callback.  
   * **Pattern**: A call to register\_setting() with only two arguments: register\_setting($GROUP, $NAME);.  
   * **Pattern-Not**: A call to register\_setting() with three or more arguments: register\_setting($GROUP, $NAME,...);.  
   * **Logic**: The rule flags any instance where the simpler, two-argument version of the function is used, as this indicates a missing sanitization callback.

Developing these types of framework-specific rules is what elevates a SAST program from a generic bug-finding tool to a truly effective, context-aware security control.

| Name/Title | URL | Summary of Function | Reviews/Ratings |
| :---- | :---- | :---- | :---- |
| WordPress Codex: Settings API | [https://codex.wordpress.org/Settings\_API](https://codex.wordpress.org/Settings_API) | The official, comprehensive documentation for the Settings API, listing all relevant functions and their parameters. The primary source for understanding the API before writing rules. | N/A (Official documentation). |
| WordPress Developer: Options API | [https://developer.wordpress.org/apis/options/](https://developer.wordpress.org/apis/options/) | The official documentation for the lower-level Options API functions like update\_option. | N/A (Official documentation). |
| Semgrep Taint Mode Tutorial | [https://blog.smarttecs.com/posts/2024-006-semgrep/](https://blog.smarttecs.com/posts/2024-006-semgrep/) | A practical tutorial explaining how to write a taint-tracking rule in Semgrep for PHP, defining sources, sinks, and sanitizers. This methodology is directly applicable to creating an update\_option rule.42 | N/A. |

## **Section 3: Lower-Priority Operational Guidance**

While the development of high-quality rules is the technical core of a SAST program, its long-term success hinges on effective operational management. This section provides strategic guidance on implementing a robust policy for suppressing findings, ensuring that the program remains effective, scalable, and trusted by developers.

### **Implementing a Strict and Effective Suppression Policy**

Even with meticulously tuned rules, any SAST tool will inevitably produce findings that are either false positives or represent a level of risk that the organization deems acceptable.43 Without a clear and consistently enforced policy for managing these findings, a SAST program is destined to fail due to alert fatigue, developer friction, and a loss of confidence in the tool's results.43 A suppression (or "ignore") policy is therefore not an afterthought, but a critical component of SAST governance.

Semgrep offers a flexible and multi-layered set of mechanisms for suppressing findings, which can be tailored to fit various workflows:

1. **Inline Comments**: A finding can be ignored at its source by adding a // nosemgrep comment on the line of the finding or the line immediately preceding it. For greater precision, a specific rule ID can be provided (e.g., // nosemgrep: php.wordpress.security.nonce-verification-missing). This is the most granular method of suppression.45  
2. **.semgrepignore File**: Similar to a .gitignore file, a .semgrepignore file at the root of a repository can be used to exclude entire files or directories from scanning. This is ideal for ignoring third-party vendor code, test fixtures, minified assets, or auto-generated files where fixing findings is not feasible or desirable.45  
3. **Command-Line Flags**: For ad-hoc or exploratory scans, the \--exclude and \--exclude-rule flags can be used to temporarily ignore specific paths or rules.45  
4. **Semgrep AppSec Platform Policies**: Within the Semgrep UI, rules can be managed globally across all projects. A rule can be completely disabled, or its mode can be changed from Block (which fails CI builds) to Monitor (which makes findings visible only to the security team) or Comment (which posts non-blocking comments on pull requests).43  
5. **UI-Based Triage**: Individual findings can be marked as "Ignored" directly in the Semgrep AppSec Platform's findings dashboard. This action is recorded and can be accompanied by a reason, such as "False Positive" or "Acceptable Risk".43

The variety of these mechanisms can lead to an inconsistent and unauditable suppression landscape if not properly governed. A common failure mode for SAST programs is the proliferation of blanket // nosemgrep comments, which can silently disable important future security checks. An effective program requires a formal policy that balances developer autonomy with security oversight.

A recommended strict suppression strategy should be built on the following principles:

* **Principle of Least Privilege**: Always use the most specific and targeted suppression method available.  
* **Guideline 1: Mandate Rule-Specific Ignores**: The use of a blanket // nosemgrep comment should be forbidden. Instead, developers must specify the exact rule ID they intend to suppress (e.g., // nosemgrep: my-rule-id). This prevents the comment from accidentally suppressing a different, valid finding that may be introduced in the same code block in the future.46  
* **Guideline 2: Require Justification for All Inline Ignores**: Every nosemgrep comment must be accompanied by a second, human-readable comment that explains *why* the finding is being ignored. This creates a crucial audit trail within the code itself, allowing future developers and security reviewers to understand the context of the suppression.  
* **Guideline 3: Centralize Global Rule Management**: The decision to disable a rule for the entire organization is a significant one and should be the sole responsibility of the security team. This action should be performed exclusively through the Semgrep AppSec Platform Policies page, creating a centralized point of control and visibility.  
* **Guideline 4: Use .semgrepignore Aggressively for Non-Application Code**: To reduce scan times and eliminate irrelevant noise, the .semgrepignore file should be used liberally to exclude all code that is not directly authored by the development team, such as third-party libraries checked into the repository, build artifacts, and extensive test data.

A suppression policy is more than just a set of technical configurations; it is a governance framework. The strategy outlined above establishes this framework by creating clear guidelines that balance the need for developers to efficiently manage false positives with the security team's need for control and visibility. This disciplined approach is essential for maintaining the integrity, health, and long-term effectiveness of the SAST program.

| Name/Title | URL | Summary of Function | Reviews/Ratings |
| :---- | :---- | :---- | :---- |
| Ignore files, folders, and code | [https://semgrep.dev/docs/ignoring-files-folders-code](https://semgrep.dev/docs/ignoring-files-folders-code) | The official Semgrep documentation detailing all methods for ignoring findings, including nosemgrep comments and .semgrepignore files. | N/A (Official documentation). |
| Triage and remediate findings | [https://semgrep.dev/docs/semgrep-code/triage-remediation](https://semgrep.dev/docs/semgrep-code/triage-remediation) | The official documentation for managing findings within the Semgrep AppSec Platform, including disabling rules and triaging individual findings as "Ignored". | N/A (Official documentation). |
| Stack Overflow: How to ignore a single rule globally | [https://stackoverflow.com/questions/75862330/how-to-ignore-a-single-rule-globally-with-semgrep](https://stackoverflow.com/questions/75862330/how-to-ignore-a-single-rule-globally-with-semgrep) | A concise community discussion that summarizes the five primary methods for ignoring rules, providing a practical overview. | N/A (Community discussion). |

## **Conclusion and Next Steps**

The successful implementation of Semgrep for securing the WordPress ecosystem is a sophisticated endeavor that extends far beyond running a default set of rules. It requires a strategic, research-oriented approach that leverages the tool's deep customization capabilities to address the unique challenges of WordPress's vast and varied landscape of plugins and themes.

The analysis has revealed several key principles for building a mature and effective program. First, for complex and critical vulnerability classes like incomplete nonce lifecycle verification, custom rule development is not optional, but essential. Public research provides a blueprint, but implementation requires a deep understanding of advanced Semgrep features like join mode. Second, the threat of subtle bugs, such as path traversal in archive extraction, necessitates the integration of specialized, third-party rule packs that encapsulate deep security research, as generic rules often lack the required specificity. Third, the most critical operational practice is the adoption of a corpus-driven methodology for rule validation. By testing and tuning rules against a large, representative set of real-world plugins, security teams can curate a high-confidence, low-noise ruleset suitable for CI/CD integration. Finally, the long-term health of the program depends on a strict governance framework for managing findings, particularly a suppression policy that favors specificity and requires justification.

To translate these principles into action, the following roadmap is recommended:

1. **Establish a Test Corpus**: Utilize the Slurpetta tool to download a local copy of the most popular WordPress plugins and themes. This corpus will serve as the foundation for all subsequent rule development and validation.  
2. **Conduct a Baseline Scan**: Run the official p/wordpress ruleset against the entire corpus to establish an initial security baseline and to familiarize the team with the types and volume of findings.  
3. **Integrate Specialized Rules**: Augment the baseline ruleset by integrating the Doyensec "Unsafe-Unpacking" rule pack to provide critical coverage for archive extraction vulnerabilities.  
4. **Develop Custom Rules**: Begin the process of custom rule development, prioritizing the highest-impact areas identified in this report: a cross-file rule for nonce verification and taint-tracking rules for the WordPress Options and Settings APIs. Use the local corpus to test and refine these rules iteratively.  
5. **Define a Suppression Policy**: Before deploying any scans that will be visible to developers, formally document and communicate a strict suppression policy based on the principles of specificity and justification.  
6. **Deploy a Tiered Strategy**: Roll out Semgrep into the CI/CD pipeline using a tiered approach. Use broad, community rulesets in a non-blocking "monitor" mode for maximum visibility, and deploy the custom-developed, high-confidence ruleset in a "blocking" mode to act as a definitive security gate.

Ultimately, Semgrep provides an exceptionally powerful and flexible static analysis engine. Its successful application to a complex ecosystem like WordPress is a direct reflection of the strategic discipline, domain expertise, and research-oriented mindset of the security team that wields it.

#### **Works cited**

1. semgrep/semgrep: Lightweight static analysis for many languages. Find bug variants with patterns that look like source code. \- GitHub, accessed August 11, 2025, [https://github.com/semgrep/semgrep](https://github.com/semgrep/semgrep)  
2. semgrep/README.md at develop \- GitHub, accessed August 11, 2025, [https://github.com/returntocorp/semgrep/blob/develop/README.md](https://github.com/returntocorp/semgrep/blob/develop/README.md)  
3. How Attackers Gain Access to WordPress Sites \- Wordfence, accessed August 11, 2025, [https://www.wordfence.com/blog/2016/03/attackers-gain-access-wordpress-sites/](https://www.wordfence.com/blog/2016/03/attackers-gain-access-wordpress-sites/)  
4. WordPress for Security Audit \- Synacktiv, accessed August 11, 2025, [https://www.synacktiv.com/en/publications/wordpress-for-security-audit](https://www.synacktiv.com/en/publications/wordpress-for-security-audit)  
5. Top tips to prevent a WordPress hack \- Acunetix, accessed August 11, 2025, [https://www.acunetix.com/websitesecurity/preventing-wordpress-hack/](https://www.acunetix.com/websitesecurity/preventing-wordpress-hack/)  
6. WordPress hacked: effective steps to restore and protect your website, accessed August 11, 2025, [https://www.hostinger.com/tutorials/hacked-wordpress](https://www.hostinger.com/tutorials/hacked-wordpress)  
7. WordPress Nonce – All You Need To Know About It \- MalCare, accessed August 11, 2025, [https://www.malcare.com/blog/wordpress-nonce/](https://www.malcare.com/blog/wordpress-nonce/)  
8. Understand and use WordPress nonces properly, accessed August 11, 2025, [https://developer.wordpress.org/news/2023/08/understand-and-use-wordpress-nonces-properly/](https://developer.wordpress.org/news/2023/08/understand-and-use-wordpress-nonces-properly/)  
9. Pro rules \- Semgrep, accessed August 11, 2025, [https://semgrep.dev/products/semgrep-code/pro-rules/](https://semgrep.dev/products/semgrep-code/pro-rules/)  
10. Automating CSRF Detection in WordPress Plugins with Semgrep ..., accessed August 11, 2025, [https://noob3xploiter.medium.com/automating-csrf-detection-in-wordpress-plugins-with-semgrep-52ece2c212b7](https://noob3xploiter.medium.com/automating-csrf-detection-in-wordpress-plugins-with-semgrep-52ece2c212b7)  
11. wp-plugin-vulnerabilities/vulnerabilities.yaml at master \- GitHub, accessed August 11, 2025, [https://github.com/FernleafSystems/wp-plugin-vulnerabilities/blob/master/vulnerabilities.yaml](https://github.com/FernleafSystems/wp-plugin-vulnerabilities/blob/master/vulnerabilities.yaml)  
12. Michele0303/wordpress-cve-scanner: Tool for WordPress plugin security. \- GitHub, accessed August 11, 2025, [https://github.com/Michele0303/wordpress-cve-scanner](https://github.com/Michele0303/wordpress-cve-scanner)  
13. CSRF And Unsafe Arbitrary File Upload In NextGEN Gallery Plugin (2.0.77.0) For WordPress \- LRQA, accessed August 11, 2025, [https://www.lrqa.com/en/cyber-labs/crsf-and-unsafe-arbitrary-file-upload-in-nextgen-gallery-plugin-for-wordpress/](https://www.lrqa.com/en/cyber-labs/crsf-and-unsafe-arbitrary-file-upload-in-nextgen-gallery-plugin-for-wordpress/)  
14. CVE-2024-9047 Detail \- NVD, accessed August 11, 2025, [https://nvd.nist.gov/vuln/detail/cve-2024-9047](https://nvd.nist.gov/vuln/detail/cve-2024-9047)  
15. Unsafe Archive Unpacking: Labs and Semgrep Rules \- Doyensec's Blog, accessed August 11, 2025, [https://blog.doyensec.com/2024/12/16/unsafe-unpacking.html](https://blog.doyensec.com/2024/12/16/unsafe-unpacking.html)  
16. phpcs-security-audit ruleset \- Semgrep, accessed August 11, 2025, [https://semgrep.dev/p/phpcs-security-audit](https://semgrep.dev/p/phpcs-security-audit)  
17. file-inclusion | Semgrep, accessed August 11, 2025, [https://semgrep.dev/r/php.lang.security.file-inclusion.file-inclusion](https://semgrep.dev/r/php.lang.security.file-inclusion.file-inclusion)  
18. wp-file-inclusion-audit \- Playground | Semgrep, accessed August 11, 2025, [https://semgrep.dev/playground/r/php.wordpress-plugins.security.audit.wp-file-inclusion-audit.wp-file-inclusion-audit?editorMode=advanced](https://semgrep.dev/playground/r/php.wordpress-plugins.security.audit.wp-file-inclusion-audit.wp-file-inclusion-audit?editorMode=advanced)  
19. doyensec/Unsafe-Unpacking: Unsafe Unpacking ... \- GitHub, accessed August 11, 2025, [https://github.com/doyensec/Unsafe-Unpacking](https://github.com/doyensec/Unsafe-Unpacking)  
20. Unsafe Archive Unpacking: Labs and Semgrep Rules \- Doyensec's Blog, accessed August 11, 2025, [https://blog.doyensec.com/page3/](https://blog.doyensec.com/page3/)  
21. Hardening WordPress – Advanced Administration Handbook | Developer.WordPress.org, accessed August 11, 2025, [https://developer.wordpress.org/advanced-administration/security/hardening/](https://developer.wordpress.org/advanced-administration/security/hardening/)  
22. johnbillion/slurpetta: Slurps down the most popular plugins ... \- GitHub, accessed August 11, 2025, [https://github.com/johnbillion/slurpetta](https://github.com/johnbillion/slurpetta)  
23. Detailed Guide to WordPress Penetration Testing \- Astra Security, accessed August 11, 2025, [https://www.getastra.com/blog/security-audit/wordpress-penetration-testing/](https://www.getastra.com/blog/security-audit/wordpress-penetration-testing/)  
24. WordPress Security Research Series: Setting Up Your Research Lab, accessed August 11, 2025, [https://www.wordfence.com/blog/2025/05/wordpress-security-research-series-setting-up-your-research-lab/](https://www.wordfence.com/blog/2025/05/wordpress-security-research-series-setting-up-your-research-lab/)  
25. The Ultimate WordPress Security Guide \- Step by Step (2025) \- WPBeginner, accessed August 11, 2025, [https://www.wpbeginner.com/wordpress-security/](https://www.wpbeginner.com/wordpress-security/)  
26. WordPress Pentesting \- Medium, accessed August 11, 2025, [https://medium.com/@far00t01/wordpress-pentesting-c57f4c11f6f1](https://medium.com/@far00t01/wordpress-pentesting-c57f4c11f6f1)  
27. D\*mn Vulnerable WordPress (DVWP) Setup for Pentesting in Kali Linux \- YouTube, accessed August 11, 2025, [https://www.youtube.com/watch?v=XdDsmiyYW7U](https://www.youtube.com/watch?v=XdDsmiyYW7U)  
28. Penetration Testing Your WordPress Website \- Wordfence, accessed August 11, 2025, [https://www.wordfence.com/learn/penetration-testing-your-wordpress-website/](https://www.wordfence.com/learn/penetration-testing-your-wordpress-website/)  
29. Run rules \- Semgrep, accessed August 11, 2025, [https://semgrep.dev/docs/running-rules](https://semgrep.dev/docs/running-rules)  
30. Explore \- Semgrep, accessed August 11, 2025, [https://semgrep.dev/explore](https://semgrep.dev/explore)  
31. Contribute rules to the Semgrep Registry, accessed August 11, 2025, [https://semgrep.dev/docs/contributing/contributing-to-semgrep-rules-repository](https://semgrep.dev/docs/contributing/contributing-to-semgrep-rules-repository)  
32. auto ruleset \- Semgrep, accessed August 11, 2025, [https://semgrep.dev/p/auto](https://semgrep.dev/p/auto)  
33. fullstorydev/semgrep-rules \- GitHub, accessed August 11, 2025, [https://github.com/fullstorydev/semgrep-rules](https://github.com/fullstorydev/semgrep-rules)  
34. Structure Mode: Never write an invalid Semgrep rule again | Semgrep, accessed August 11, 2025, [https://semgrep.dev/blog/2024/structure-mode-never-write-an-invalid-semgrep-rule/](https://semgrep.dev/blog/2024/structure-mode-never-write-an-invalid-semgrep-rule/)  
35. Structure Mode is now available in the Playground \- Semgrep, accessed August 11, 2025, [https://semgrep.dev/products/product-updates/structure-mode-is-now-available-in-the-playground-editor-rule-writing/](https://semgrep.dev/products/product-updates/structure-mode-is-now-available-in-the-playground-editor-rule-writing/)  
36. Settings API \- WordPress Codex, accessed August 11, 2025, [https://codex.wordpress.org/Settings\_API](https://codex.wordpress.org/Settings_API)  
37. The WordPress Settings API \- Konstantin Kovshenin, accessed August 11, 2025, [https://konstantin.blog/2012/the-wordpress-settings-api/](https://konstantin.blog/2012/the-wordpress-settings-api/)  
38. Settings API Explained \- Press Coders, accessed August 11, 2025, [https://presscoders.com/wordpress-settings-api-explained/](https://presscoders.com/wordpress-settings-api-explained/)  
39. WordPress Settings API Tutorial, accessed August 11, 2025, [https://ottopress.com/2009/wordpress-settings-api-tutorial/comment-page-1/](https://ottopress.com/2009/wordpress-settings-api-tutorial/comment-page-1/)  
40. Options – Common APIs Handbook \- WordPress Developer Resources, accessed August 11, 2025, [https://developer.wordpress.org/apis/options/](https://developer.wordpress.org/apis/options/)  
41. WordPress Settings API: Creating Custom Theme Options Yourself \- TemplateToaster Blog, accessed August 11, 2025, [https://blog.templatetoaster.com/wordpress-settings-api-creating-theme-options/](https://blog.templatetoaster.com/wordpress-settings-api-creating-theme-options/)  
42. Code Security with Semgrep, accessed August 11, 2025, [https://blog.smarttecs.com/posts/2024-006-semgrep/](https://blog.smarttecs.com/posts/2024-006-semgrep/)  
43. Triage and remediate findings \- Semgrep, accessed August 11, 2025, [https://semgrep.dev/docs/semgrep-code/triage-remediation](https://semgrep.dev/docs/semgrep-code/triage-remediation)  
44. Semgrep Guide for a Security Engineer (Part 6 of 6\) | Caesar Creek Software, accessed August 11, 2025, [https://cc-sw.com/semgrep-guide-for-a-security-engineer-part-6-of-6/](https://cc-sw.com/semgrep-guide-for-a-security-engineer-part-6-of-6/)  
45. how to ignore a single rule globally with semgrep \- Stack Overflow, accessed August 11, 2025, [https://stackoverflow.com/questions/75862330/how-to-ignore-a-single-rule-globally-with-semgrep](https://stackoverflow.com/questions/75862330/how-to-ignore-a-single-rule-globally-with-semgrep)  
46. Ignore files, folders, and code \- Semgrep, accessed August 11, 2025, [https://semgrep.dev/docs/ignoring-files-folders-code](https://semgrep.dev/docs/ignoring-files-folders-code)  
47. How to ignore the semgrep check in this condition \- Stack Overflow, accessed August 11, 2025, [https://stackoverflow.com/questions/76928733/how-to-ignore-the-semgrep-check-in-this-condition](https://stackoverflow.com/questions/76928733/how-to-ignore-the-semgrep-check-in-this-condition)  
48. Advanced usage \- Testing Handbook, accessed August 11, 2025, [https://appsec.guide/docs/static-analysis/semgrep/advanced/](https://appsec.guide/docs/static-analysis/semgrep/advanced/)  
49. Can't suppress with // nosemgrep because of autoformatter · Issue \#3521 \- GitHub, accessed August 11, 2025, [https://github.com/returntocorp/semgrep/issues/3521](https://github.com/returntocorp/semgrep/issues/3521)  
50. Why am I getting findings in files that should be ignored? \- Semgrep, accessed August 11, 2025, [https://semgrep.dev/docs/kb/semgrep-code/semgrepignore-ignored](https://semgrep.dev/docs/kb/semgrep-code/semgrepignore-ignored)  
51. Ability to exclude rules from rulesets on command line · Issue \#2530 \- GitHub, accessed August 11, 2025, [https://github.com/returntocorp/semgrep/issues/2530](https://github.com/returntocorp/semgrep/issues/2530)  
52. Manage rules and policies \- Semgrep, accessed August 11, 2025, [https://semgrep.dev/docs/semgrep-code/policies](https://semgrep.dev/docs/semgrep-code/policies)  
53. Triage and remediation | Semgrep, accessed August 11, 2025, [https://semgrep.dev/docs/semgrep-code/triage-remediation/](https://semgrep.dev/docs/semgrep-code/triage-remediation/)  
54. Resolve findings through the Semgrep web app, accessed August 11, 2025, [https://semgrep.dev/docs/for-developers/resolve-findings-through-app](https://semgrep.dev/docs/for-developers/resolve-findings-through-app)