└── docs  
    └── kb  
        └── rules  
            ├── changing-rule-severity-and-other-metadata.md  
            ├── ellipsis-metavariables.md  
            ├── exclude\_rule\_for\_certain\_filetypes.md  
            ├── match-absence.md  
            ├── match-comments.md  
            ├── pattern-parse-error.md  
            ├── rule-file-perf-principles.md  
            ├── ruleset-default-mode.md  
            ├── run-all-available-rules.md  
            ├── understand-severities.md  
            ├── using-pattern-not-inside.md  
            └── using-semgrep-rule-schema-in-vscode.md

/docs/kb/rules/changing-rule-severity-and-other-metadata.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | description: Change rule severity and other metadata by forking rules  
 3 | tags:  
 4 |   \- Rules  
 5 |   \- Semgrep Code  
 6 |   \- Semgrep Secrets  
 7 | append\_help\_link: true  
 8 | \---  
 9 | import ForkExistingRule from '/src/components/reference/\_fork\_existing\_rule.md'  
10 |   
11 | \# Change rule severity and other metadata by forking rules  
12 |   
13 | To alter the severity or other metadata of a Semgrep rule, it must be forked and then updated. Forking means to copy or duplicate the rule, thereby creating your own custom version of it. Once this custom version is created, it can then be modified as needed.  
14 |   
15 | :::note  
16 | Only Semgrep Code and Secrets rules can be forked.  
17 | :::  
18 |   
19 | \#\# Fork a rule  
20 |   
21 | \<ForkExistingRule /\>  
22 |   
23 | \#\# Changing the severity  
24 |   
25 | Once you have forked the rule, you can change the \[severity or other metadata\](/docs/writing-rules/rule-syntax\#required) to your liking.   
26 |   
27 | Then, save this custom version of the rule to your organization's rules, making it available to use within your policy as defined in Semgrep AppSec Platform.  
28 |   
29 | \!\[Save a rule in the Editor\](/img/kb/save\_rule\_editor.png)  
30 |   
31 | By default, saving the rule also enables you to search for it in the \[Semgrep Registry\](https://semgrep.dev/r), with visibility limited to your organization.  
32 |   
33 | \!\[Custom rules in registry\](/img/kb/custom\_rules\_in\_editor.png)  
34 |   
35 | 

\--------------------------------------------------------------------------------  
/docs/kb/rules/ellipsis-metavariables.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | description: Ellipsis metavariables can help with matching multiple word tokens.  
 3 | tags:  
 4 |   \- Rules  
 5 |   \- Semgrep Code  
 6 | \---  
 7 |   
 8 |   
 9 |   
10 | \# Matching multiple tokens with ellipsis metavariables  
11 |   
12 | Using ellipsis (\`...\`) to match a sequence of items (for example, arguments, statements, or fields) is one of the most common constructs in Semgrep rules. Likewise, using metavariables ($VAR) to capture values (such as variables, functions, arguments, classes, and methods) is extremely common and powerful for tracking the use of values across a code scope.  
13 |   
14 | \#\# Introduction to ellipsis metavariables  
15 |   
16 | Ellipses can be combined with metavariables to increase matching scope from a single item to a sequence of items, \[while capturing the values for later re-use\](/docs/writing-rules/pattern-syntax/\#ellipsis-metavariables).  
17 |   
18 | Most commonly, ellipsis metavariables like \`$...ARGS\` are used for purposes like matching multiple arguments to a function or items in an array.  
19 |   
20 | However, they can also be used to match multiple word tokens. As part of Semgrep's pattern matching, it separates the analyzed language into tokens, which are single units that make up a larger text. Some tokens, typically alphanumeric tokens, are "words", and some are word separators (like punctuation and whitespace).  
21 |   
22 | Using ellipsis metavariables to match multiple word tokens is especially helpful in \[Generic pattern matching mode\](/docs/writing-rules/generic-pattern-matching). Because this mode is generic, it's not aware of the semantics of any particular language, and that comes with \[caveats and limitations\](/docs/writing-rules/generic-pattern-matching\#caveats-and-limitations-of-generic-mode).  
23 |   
24 | In generic mode, a word token that can be matched by a metavariable is defined as a sequence of characters in the set \`\[A-z0-9\_\]\`. So \`ABC\_DEF\` is one token, and a metavariable such as \`$VAR\` captures the entire sequence. However, \`ABC-DEF\` is two tokens, and a metavariable such as \`$VAR\` does not capture the entire sequence.  
25 |   
26 | \#\# Capturing multiple tokens with ellipsis metavariables  
27 |   
28 | Not all languages you might match using generic mode share the same definition of word tokens. If you're matching patterns in one of these languages, your metavariables might not match as much of a word token as you expect. For example, in HTML, "ABC-DEF" is a single token (perhaps an \`id\` value).  
29 |   
30 | If the language you're working with allows other characters in tokens, using ellipsis metavariables can prevent problems with metavariables matching too little of the pattern.  
31 |   
32 | To match all of \`ABC-DEF\` in \`generic\` mode, use an ellipsis metavariable, like \`$...VAR\`. Here is an example rule:  
33 |   
34 | \<iframe src="https://semgrep.dev/embed/editor?snippet=J6Ro" title="html-ellipsis-metavariable" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
35 |   
36 | If you remove the ellipsis in the \`$...ID\` variable, the second example no longer matches.  
37 |   
38 | \#\# Alternative: try the Aliengrep experiment  
39 |   
40 | To address some of the limitations of generic mode, the team is experimenting with a new mode called \[Aliengrep\](/docs/writing-rules/experiments/aliengrep).  
41 |   
42 | With Aliengrep, you can \[configure what characters are allowed as part of a word token\](/docs/writing-rules/experiments/aliengrep/\#additional-word-characters-captured-by-metavariables), so that you could match the HTML example with a single metavariable. You can also \[have even more fun with ellipses\](/docs/writing-rules/experiments/aliengrep/\#ellipsis-).  
43 |   
44 | Give it a try and share your thoughts\!  
45 | 

\--------------------------------------------------------------------------------  
/docs/kb/rules/exclude\_rule\_for\_certain\_filetypes.md:  
\--------------------------------------------------------------------------------  
 1 | \# How to exclude certain file types for a particular rule  
 2 |   
 3 | Certain filetypes can generate numerous false positives and delay your triage process. This document helps you achieve a selective middle ground:  
 4 |   
 5 | \* Continue to include the file type to scan with other rules  
 6 | \* Reduce time spent triaging false positives  
 7 |   
 8 | \#\# Background  
 9 |   
10 | This article uses a real-life case in scanning \`.svg\` files. \`svg\` files mostly comprise a string of thousands of characters:  
11 |   
12 | \`\`\`  
13 | \<image id="image0" width="2896" height="998" xlink:href="data:image/png;  
14 | base64,iVBORw0KGgoAAAANSUhEUgAAC1AAA\*\*AP6\*mCAYAAABQS58cAAABR2lDQ1BJQ0M  
15 | gUHJvZmlsZQAAKJFjYGASSSwoyGFhYGDIzSspCnJ3UoiIjFJgf8LAzsDIwM1gwqCRmFxc4B  
16 | gQ4ANUwgCjUcG3a0C1QHBZF2SW3AzZBT+7Sn68UphgqTU7fyemehTAlZJanAyk/wBxWnJBU  
17 | QkDA2MKkK1cXlIAYncA2SJFQEcB2XNA7HQIewOInQRhHwGrCQlyBrJvANkCyRmJQDMYXwDZ  
18 | OklI4ulIbKi9IMDj4urjoxBqZG5oEUjAuaSDktSKEhDtnF9QWZSZnlGi4AgMpVQFz7xkPR0F  
19 | IwMjAwYGUJhDVH8OAoclo9g+hFj+EgYGi28MDMwTEWJJUxgYtrcxMEjcQ…..  
20 | \`\`\`  
21 |   
22 | Semgrep’s standard artifactory rule (see in \[Semgrep Registry\](https://semgrep.dev/r?q=generic.secrets.security.detected-artifactory-password.detected-artifactory-password)), for example, reports on:  
23 |   
24 | \`\`\`  
25 | \_\\\# ruleid: detected-artifactory-password\_  
26 |   
27 | \_AP6xxxxxxxxxx\_  
28 |   
29 | \_\\\# ruleid: detected-artifactory-password\_  
30 |   
31 | \_AP2xxxxxxxxxx\_  
32 |   
33 | ...  
34 | \`\`\`  
35 |   
36 | Because \`.svg\` files are made up of thousands of characters, the substring \`AP6\*m...\` in the \`.svg\` snippet creates a false positive finding due to the artifactory rule. It is a false positive because no passwords are leaked by the \`.svg\` file.  
37 |   
38 | \#\# Choosing the appropriate ignoring solution  
39 |   
40 | Semgrep offers many different ways of ignoring false positives:\\u2028  
41 |   
42 | \* \*\*Adding \`nosemgrep\` as a comment on the first line of code in the file.\*\* This would require having to keep track of each new file for this target \`.svg\` file type and editing each file accordingly, requiring constant maintenance.  
43 | \* \*\*Ignore the file entirely, by adding it to a \`.semgrepignore file\`\*\*. This would exclude the file from being scanned with all rules, not just the artifactory rule.  
44 |   
45 | \#\# Achieving a happy medium: creating a custom rule to exclude a file type  
46 |   
47 | You can safely assume \`.svg\` files do not intentionally contain artifactory passwords, so you can exclude this file type from being scanned. The following procedure demonstrates how to create a customized version of the rule that is generating the false positives that excludes the target file type.  
48 |   
49 | 1\. Download the rule generating false positives from the \[Registry\](https://semgrep.dev/r).  
50 | 2\. Modify the rule ID to something custom:  
51 | \`\`\`  
52 |   \\- id: my\_detected-artifactory-password  
53 | \`\`\`  
54 | 3\. Exclude the target filetype in question from the rule through the \[\`path\` field\](/deployment/teams\#user-roles-and-access):   
55 |   
56 | \`\`\`  
57 |  % cat my\_custom\_artifactory.yml   
58 |    
59 | rules:  
60 |   
61 |   \\- id: my\_detected-artifactory-password  
62 |     
63 |     options:  
64 |       
65 |     .  
66 |     .  
67 |     .  
68 |       
69 |     \- metavariable-analysis:  
70 |         analyzer: entropy  
71 |         metavariable: $ITEM   
72 |     paths:  
73 |       
74 |        exclude:  
75 |           \- "\*.svg"   
76 |    languages:  
77 |        \- generic  
78 |     .  
79 |     .  
80 |     .  
81 | \`\`\`  
82 | 4\. Alter the scan command to still scan for the default configuration you have, with the following changes:  
83 |     1\. Exclude the original noisy rule as articulated in the false positive reporting.  
84 |     2\. Include the new custom rule that excludes your target paths.  
85 |   
86 | Thus, your original \`semgrep scan\` command or \`semgrep ci\` command can be similar to the following::  
87 |   
88 | \`\`\`  
89 | % semgrep scan \--config=auto \--config=my\_custom\_artifactory.yml \--exclude-rule generic.secrets.security.detected-artifactory-password.detected-artifactory-password  
90 | \`\`\`  
91 | 

\--------------------------------------------------------------------------------  
/docs/kb/rules/match-absence.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | description: You can approximate this behavior by matching an entire file, but excluding the desired content from the match.  
 3 | tags:  
 4 |   \- Rules  
 5 |   \- Semgrep Registry  
 6 |   \- Semgrep Code  
 7 | \---  
 8 |   
 9 | \# Match the absence of something in a file  
10 |   
11 | Currently, Semgrep does not have a clear way to match the absence of a pattern, rather than the presence of one. However, you can approximate this behavior by matching an entire file with \`pattern-regex\`, and excluding a file that contains the desired content with \`pattern-not-regex\` or other negative patterns.  
12 |   
13 | Here is a simple example:  
14 |   
15 | \`\`\`yml  
16 | rules:  
17 |   \- id: a  
18 |     patterns:  
19 |       \- pattern-regex: |  
20 |           (?s)(.\*)  
21 |       \- pattern-not-regex: .\*YOUR PATTERN TO BLOCK  
22 |     message: match  
23 |     languages:  
24 |       \- generic  
25 |     severity: ERROR  
26 | \`\`\`  
27 |   
28 | :::note Example  
29 | Try this pattern in the \[Semgrep Playground\](https://semgrep.dev/playground/s/vop8).   
30 | :::  
31 |   
32 | The regular expression pattern \`(?s)(.\*)\` uses the \`s\` flag to put the match in "single-line" mode, so that the dot character matches a newline. This allows \`(.\*)\` to match multiple lines, and therefore match an entire file.  
33 |   
34 | If the file contains \`YOUR PATTERN TO BLOCK\`, then the match is negated and the file does not appear as a finding. If the file does not contain \`YOUR PATTERN TO BLOCK\`, the file is flagged as a finding. With this rule, the finding spans the whole file, starting at line 1\.  
35 |   
36 |   
37 | 

\--------------------------------------------------------------------------------  
/docs/kb/rules/match-comments.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | description: Semgrep's generic pattern matching mode can match comments in code files.  
 3 | tags:  
 4 |   \- Rules  
 5 |   \- Semgrep Code  
 6 | \---  
 7 |   
 8 | \# Match comments with Semgrep  
 9 |   
10 | When Semgrep rules target specific languages, they generally do not match comments in the targeted code files. Comments are not part of the semantic and syntactic structure of the document, so in most cases they are ignored.  
11 |   
12 | However, it's sometimes useful to match comments. For example, comments can control the behavior of other linters, such as type checkers. You might also have certain formatting standards for comments, such as requiring that a \`TODO\` comment contains a ticket capturing the required work.  
13 |   
14 | To match comments with Semgrep, use the \`generic\` language target to invoke \[generic pattern matching\](/docs/writing-rules/generic-pattern-matching). (Alternatively you may use \`pattern-regex\` which \[does file-level matching\](/docs/writing-rules/rule-syntax\#pattern-regex) rather than semantic / syntactic matching, which is beyond the scope of this article.)  
15 |   
16 | \#\# Example rule  
17 |   
18 | Suppose that your organization requires all \`TODO\` comments to have an associated Jira ticket. This rule finds TODO lines with no \`atlassian.net\` content and identifies any lines not containing a Jira Cloud ticket link.  
19 |   
20 | \`\`\`yaml  
21 | rules:  
22 |   \- id: no-todo-without-jira  
23 |     patterns:  
24 |       \- pattern: TODO $...ACTION  
25 |       \- pattern-not: TODO ... atlassian.net ...  
26 |     options:  
27 |       generic\_ellipsis\_max\_span: 0  
28 |     message: The TODO comment "$...ACTION" does not contain a Jira ticket to resolve the issue  
29 |     languages:  
30 |       \- generic  
31 |     severity: INFO  
32 |     metadata:  
33 |       category: best-practice  
34 | \`\`\`  
35 |   
36 | :::note  
37 | Try this pattern in the \[Semgrep Playground\](https://semgrep.dev/playground/s/lBDRL).  
38 | :::  
39 |   
40 | This rule also includes the \`generic\_ellipsis\_max\_span\` option, which \[limits the ellipsis to matching on the same line\](/docs/writing-rules/generic-pattern-matching/\#handling-line-based-input) and prevents it from over-matching in this generic context.  
41 |   
42 | \#\# Limiting the match to certain file types  
43 |   
44 | If particular types of comments are only relevant for certain files, you can use the \`paths:\` key to limit the rule to files of that type. For example, \`mypy\` \[type ignores\](https://mypy.readthedocs.io/en/stable/error\_codes.html\#silencing-errors-based-on-error-codes) are only relevant in Python files.  
45 |   
46 | \`\`\`yaml  
47 | ...  
48 | rules:  
49 |   \- id: no-mypy-ignore  
50 |     ...  
51 |     paths:  
52 |       include:  
53 |         \- "\*.py"  
54 | \`\`\`  
55 |   
56 | \#\# Ignoring some comments in generic mode  
57 |   
58 | It is possible to \[ignore comments of particular types\](/docs/writing-rules/generic-pattern-matching\#ignoring-comments) in generic mode using the \`generic\_comment\_style\` option. For example, to ignore C-style comments but match any other style:  
59 |   
60 | \`\`\`yaml  
61 | rules:  
62 |   \- id: css-blue-is-not-allowed  
63 |     pattern: |  
64 |       color: blue  
65 |     options:  
66 |       \# ignore comments of the form /\* ... \*/  
67 |       generic\_comment\_style: c  
68 |     message: |  
69 |       Blue is not allowed.  
70 |     languages:  
71 |       \- generic  
72 |     severity: INFO  
73 | \`\`\`  
74 |   
75 | \#\# Additional resources  
76 |   
77 | \* \[Matching multiple tokens with ellipsis metavariables\](/docs/kb/rules/ellipsis-metavariables)  
78 | \* \[Aliengrep experiment\](/docs/writing-rules/experiments/aliengrep)  
79 | 

\--------------------------------------------------------------------------------  
/docs/kb/rules/pattern-parse-error.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | description: Learn how to implement rule patterns that include the targeted language's reserved words.  
 3 | tags:  
 4 |   \- Rules  
 5 |   \- Semgrep Registry  
 6 |   \- Semgrep Code  
 7 | append\_help\_link: true  
 8 | \---  
 9 |   
10 |   
11 |   
12 | \# Fix pattern parse errors when running rules  
13 |   
14 | When using a targeted language's reserved words in rules, you may see the following error:  
15 |   
16 | \`\`\`console  
17 | \[ERROR\] Pattern parse error in rule  
18 | \`\`\`  
19 |   
20 | \#\# Background  
21 |   
22 | Each programming language has a list of reserved words that cannot be used as identifiers, such as the names of variables or functions. If you write a rule that results in the following error when run, you are triggering a reserved word conflict:  
23 |   
24 | \`\`\`console  
25 | \[ERROR\] Pattern parse error in rule ruleName:  
26 |  Invalid pattern for JavaScript:  
27 | \--- pattern \---  
28 | delete  
29 | \--- end pattern \---  
30 | Pattern error: Stdlib.Parsing.Parse\_error  
31 | \`\`\`  
32 |   
33 | \#\# Resolution  
34 |   
35 | Using a reserved word in your rule leads to parsing errors, so if you see this error, determine if the words cited in the error are reserved words. If they are, you can replace your \`metavariable-pattern\` with \`metavariable-regex\`.  
36 |   
37 | This substitution works because \`metavariable-pattern\` tries to match the pattern within the captured metavariable, which is going to be affected by how reserved keywords are parsed, while \`metavariable-regex\` runs a regex on the text range associated with the metavariable, ignoring how its content would be parsed and bypassing the issue.  
38 |   
39 | \#\#\# Example  
40 |   
41 | The following rule would elicit the "\[ERROR\] Pattern parse error in rule" response:  
42 |   
43 | \`\`\`yaml  
44 | patterns:  
45 | \- pattern-inside: app.$FUNC(...)  
46 | \- pattern-not-regex: .(middleware.csrf.validate).  
47 | \- metavariable-pattern:  
48 |        metavariable: $FUNC  
49 | patterns:  
50 | \- pattern-either:  
51 | \- pattern: post=  
52 | \- pattern: put  
53 | \- pattern: delete  
54 | \- pattern: patch  
55 | \`\`\`  
56 |   
57 | To fix the error, replace  
58 |   
59 | \`\`\`yaml  
60 | \- metavariable-pattern:  
61 |        metavariable: $FUNC  
62 | \`\`\`  
63 |   
64 | with  
65 |   
66 | \`\`\`yaml  
67 | \- metavariable-regex:  
68 |     metavariable: $FUNC  
69 |     regex: ^(post|put|delete|patch)$  
70 | \`\`\`  
71 | 

\--------------------------------------------------------------------------------  
/docs/kb/rules/rule-file-perf-principles.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | description: Learn the rule and file performance principles to abide by when scanning repositories to optimize scan times.  
 3 | tags:  
 4 |   \- Rules  
 5 |   \- Semgrep Registry  
 6 |   \- Semgrep Code  
 7 | \---  
 8 |   
 9 |   
10 |   
11 | \# Performance principles for rules and files to abide by when scanning repositories  
12 |   
13 | \#\# Rules  
14 |   
15 | The amount of time required for rules to run scales better than linearly when  
16 | adding interfile rules, which are those with \`interfile: true\` in the \`options\` key.  
17 | That is, doubling the number of interfile rules increases the runtime, but not  
18 | by double. However, some rules run faster than others, and adding a slow rule  
19 | when all the rest are fast can cause a significant slowdown.  
20 |   
21 | Rules are slower if the sub-patterns, such as \`pattern: \<... $X ...\>\`, result in  
22 | a greater number of matches. When writing rules, pay special attention to the  
23 | problems raised by sub-pattern matches. The most important factor for runtime is  
24 | the time spent adding to various lists or sets.  
25 |   
26 | You can benchmark your rules by adding the \`--time\` flag to your \`semgrep scan\`  
27 | command. When you use this flag, your results return with a timing summary; if  
28 | your output format is JSON, you'll see times for each rule-target pair.  
29 |   
30 | \#\# Files  
31 |   
32 | Generally, the time required to scan files scales linearly with the number of  
33 | files scanned, but file size is still important. Overall, the time taken is  
34 | \*\*time for setup work \+ time for matching\*\*. For setup work, files aren’t  
35 | analyzed alone but in groups of mutually dependent files called strongly  
36 | connected components (SCCs).  
37 |   
38 | The time for setup work is \*\*number of SCCs \* time for each SCC\*\*, where the  
39 | time for each SCC grows, in the worst case, exponentially up to certain limits  
40 | set by Semgrep. This means that making SCCs larger with more mutually dependent  
41 | files affects scan time more negatively than adding more SCCs.  
42 |   
43 | The time for matching is \*\*number of files \* time to match each file\*\*. The time  
44 | to check each file can also grow, in the worst case, exponentially, especially  
45 | when a rule has a lot of matches in subpatterns. However, the default settings  
46 | of \`--timeout 5\` \`--timeout-threshold 3\` means that a file times out if:  
47 |   
48 | \* 5 seconds elapse without the match process completing  
49 | \* 3 rules time out  
50 |   
51 | You can configure these flags to skip long files after a shorter timeout period  
52 | or when a smaller number of rules timeout. Usually, Semgrep matches files pretty  
53 | quickly, but minified Javascript files can cause significant performance issues.  
54 |   
55 | Semgrep sets a limit of 1 MB for each file scanned, but you can modify this  
56 | setting using the \`--max-target-bytes\` flag. For example, if your flag is  
57 | \`--max-target-bytes=1500000\`, Semgrep ignores any larger file. You can get a  
58 | full list of files Semgrep skips by including the \`--verbose\` flag and  
59 | inspecting \`ci.log\`. This information helps you determine the feasibility of  
60 | including those files and whether you should adjust the maximum file size limit  
61 | to scan such files.  
62 | 

\--------------------------------------------------------------------------------  
/docs/kb/rules/ruleset-default-mode.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | description: Change the default mode for a ruleset.  
 3 | tags:  
 4 |   \- Rules  
 5 |   \- Semgrep Registry  
 6 |   \- Semgrep Code  
 7 | append\_help\_link: true  
 8 | \---  
 9 |   
10 | \# Why do new rules keep appearing in Comment or Block mode?  
11 |   
12 | Semgrep AppSec Platform \[Policies\](/docs/semgrep-code/policies) can contain both individual rules and \*\*rulesets\*\*, which are curated groups of rules recommended for particular purposes. All organizations start with two rulesets: the \`default\` ruleset, which is a good starter pack for security teams, and the \`comment\` ruleset, which is a good starter pack for developers.  
13 |   
14 | As Semgrep adds new rules to improve coverage, some of these rules are also added to rulesets. If you add a ruleset to your organization's policies, any new rules added to the ruleset automatically become a part of your policies as well.  
15 |   
16 | The \`default\` and \`comment\` rulesets are initially added in \*\*Monitor\*\* mode, where the findings generated by the rules are primarily intended for security teams to review. You can also \[add new rulesets to your policies\](/docs/semgrep-code/policies\#add-rulesets-to-your-policies-from-the-registry) from the Semgrep Registry.  
17 |   
18 | When you add a ruleset through the registry, you can add it in any policy mode: \*\*Monitor\*\*, \*\*Comment\*\*, or \*\*Block\*\*. The mode you choose will determine the mode for future rules that are added to that ruleset.  
19 |   
20 | Even if you later change some or all rules from a ruleset to a different mode, the default mode for the ruleset does not change. Therefore, when you add new rules to the ruleset, they are added in the original mode.  
21 |   
22 | \#\# Change the default mode for a ruleset  
23 |   
24 | To change the default mode for a ruleset, follow the same process as for \[adding a new ruleset to your policies\](/docs/semgrep-code/policies\#add-rulesets-to-your-policies-from-the-registry) and select the desired default mode.  
25 |   
26 | After adding the ruleset in the default mode, you can then \[change any individual rule modes\](/docs/semgrep-code/policies\#block-a-pr-or-mr-through-rule-modes) for rules that you prefer to keep in a different mode.  
27 | 

\--------------------------------------------------------------------------------  
/docs/kb/rules/run-all-available-rules.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | description: Learn how to run all available rules on your repository.  
 3 | tags:  
 4 |   \- Rules  
 5 |   \- Semgrep Registry  
 6 |   \- Semgrep Code  
 7 | append\_help\_link: true  
 8 | \---  
 9 |   
10 |   
11 |   
12 | \# Run all available rules on a repository  
13 |   
14 | To scan your repository with all of the rules available in the \[Semgrep Registry\](https://semgrep.dev/explore), navigate to the root of your repository and run:  
15 |   
16 | \`\`\`  
17 | semgrep \--config=r/all .  
18 | \`\`\`  
19 |   
20 | If you are \*not\* logged in, \`--config=r/all\` runs all public rules from the Semgrep Registry, including community-authored rules.  
21 |   
22 | If you are logged in, \`--config=r/all\` runs all public rules from the Semgrep Registry, including community-authored rules, plus:  
23 |   
24 | \* Your organization's private rules in the Registry, excluding unlisted private rules  
25 |   \* This excludes unlisted private rules  
26 | \* Semgrep Pro rules, if you have a Team or Enterprise subscription  
27 |   
28 | :::warning  
29 | Running all rules is likely to produce many findings and generate noise in the form of false positives.  
30 | :::  
31 |   
32 | \#\# Error: "invalid configuration file found"  
33 |   
34 | If you encounter the following error, there is a syntax error in one of your custom rules.  
35 |   
36 | \`\`\`console  
37 | \[ERROR\] invalid configuration file found (1 configs were invalid)  
38 | \`\`\`  
39 |   
40 | To work around this error, while you correct the issues in the affected configuration file, run:  
41 |   
42 | \`\`\`  
43 | semgrep \--config r/all . \-d  
44 | semgrep \--config \~/.semgrep/semgrep\_rules.json .  
45 | \`\`\`  
46 |   
47 | The first command creates a cache of rules in \`semgrep\_rules.json\` within the \`.semgrep\` directory in your home folder that omits the invalid rule. The second command runs a Semgrep scan using the local rule cache.  
48 | 

\--------------------------------------------------------------------------------  
/docs/kb/rules/understand-severities.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | description: Understand how rule severity and confidence is determined.  
 3 | tags:  
 4 |  \- Rules  
 5 |  \- Semgrep Registry  
 6 | \---  
 7 |   
 8 | \# How does Semgrep assign severity levels to rules?  
 9 |   
10 | \#\# Semgrep Code and Secrets  
11 |   
12 | Semgrep Code and Secrets rules have one of four severity levels: Critical, High, Medium, and Low. The severity indicates how critical the issues that a rule potentially detects are.  
13 |   
14 | The rule author assigns the rule severity. The severity assignment of custom and third-party rules is the source of truth.  
15 |   
16 | As a best practice, severity for Semgrep Registry rules in the \`security\` category should be assigned by evaluating the combination of \[likelihood\](/docs/contributing/contributing-to-semgrep-rules-repository/\#likelihood) and \[impact\](/docs/contributing/contributing-to-semgrep-rules-repository/\#impact).   
17 |   
18 | \#\# Semgrep Supply Chain   
19 |   
20 | Semgrep Supply Chain rules have one of four severity levels: Critical, High, Medium, or Low. The score assigned to the CVE using the \[Common Vulnerability Scoring System (CVSS) score\](https://nvd.nist.gov/vuln-metrics/cvss), or the severity value set by the GitHub Advisory Database, determines the severity in Semgrep Supply Chain. For example, a vulnerability is assigned Critical if it is given a CVSS score of 9.0 or higher.  
21 |   
22 | In addition to severity, Supply Chain displays an \[Exploit prediction scoring system (EPSS) probability\](https://www.first.org/epss/) for findings. The EPSS score represents the likelihood that the vulnerability will be exploited in the wild in the next 30 days. Its values range from 0% to 100%. The higher the score, the greater the probability the vulnerability will be exploited. Semgrep groups probabilities as follows:  
23 |   
24 | \* \<b\>High\</b\>: 50 \- 100%  
25 | \* \<b\>Medium\</b\>: 10 \- &\#60;50%  
26 | \* \<b\>Low\</b\>: &\#60;10%  
27 |   
28 | \# How are confidence levels assigned to rules?  
29 |   
30 | Confidence level is also set by the rule author, but it is intended to describe the rule, not the vulnerability the rule catches.  
31 |   
32 | The confidence level reflects how confident the rule writer is that the rule patterns capture the vulnerability without generating too many false positive findings. The rule author manually sets the appropriate confidence level. Rules that have more targeted and detailed patterns, such as advanced taint mode rules, typically are given \`HIGH\` confidence.  
33 | 

\--------------------------------------------------------------------------------  
/docs/kb/rules/using-pattern-not-inside.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | description: Learn how to fix issues with \`pattern-not\` when excluding cases in custom rules.  
 3 | tags:  
 4 |   \- Semgrep Community Edition  
 5 |   \- Semgrep Rules  
 6 | append\_help\_link: true  
 7 | \---  
 8 |   
 9 | \# My rule with \`pattern-not\` doesn't work: using \`pattern-not-inside\`  
10 |   
11 | One common issue when writing custom rules involves the unsuccessful exclusion of cases using \`pattern-not\`.  
12 |   
13 | If you are trying to exclude a specific case where a pattern is unacceptable unless it is accompanied by another pattern, try \`pattern-not-inside\` instead of \`pattern-not\`.  
14 |   
15 | \#\# Background  
16 |   
17 | In Semgrep, a pattern that's inside another pattern can mean one of two things:  
18 |   
19 | \* The pattern is wholly within an outer pattern  
20 | \* The pattern is at the same level as another pattern, but includes less code  
21 |   
22 | In other words, using \`pattern-not\` in your rule means that Semgrep expects the matches to be the same "size" (same amount of code), and does not match if that's not the case.  
23 |   
24 | \#\# Example  
25 |   
26 | The \[example rule\](https://semgrep.dev/docs/writing-rules/rule-ideas/\#systematize-project-specific-coding-patterns) \`find-unverified-transactions\` is a good example: \`make\_transaction($T)\` is acceptable only if \`verify\_transaction($T)\` is also present.  
27 |   
28 | To successfully match the target code, the rule uses \`pattern\` and \`pattern-not\`:  
29 |   
30 | \<iframe src="https://semgrep.dev/embed/editor?snippet=Nr3z" title="pattern-not rule for unverified transactions" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
31 |   
32 | But this rule is redundant. Both pattern clauses contain:  
33 |   
34 | \`\`\`yml  
35 | public $RETURN $METHOD(...){  
36 |   ...  
37 | }  
38 | \`\`\`  
39 |   
40 | However, if you refactor the rule by pulling the container out and using \`pattern-inside\`, the rule doesn't work \-- \[try it out\](https://semgrep.dev/playground/s/KZOd?editorMode=advanced) if you like\!  
41 |   
42 | \`\`\`yml  
43 | rules:  
44 |   \- id: find-unverified-transactions-inside  
45 |     patterns:  
46 |       \- pattern-inside: |  
47 |           $RETURN $METHOD(...) {  
48 |             ...  
49 |           }  
50 |       \- pattern: |  
51 |           ...  
52 |           make\_transaction($T);  
53 |           ...  
54 |       \- pattern-not: |  
55 |           ...  
56 |           verify\_transaction($T);  
57 |           ...  
58 |           make\_transaction($T);  
59 |           ...  
60 | \`\`\`  
61 |   
62 | With an understanding of how \`pattern-not\` operates, you can see that this rule fails because the matches are not the same size. The \`pattern-not\` match is at the same level, but it is "larger" (contains more code).  
63 |   
64 | If you switch to \`pattern-not-inside\`:  
65 |   
66 | \`\`\`yml  
67 | \- pattern-not-inside: |  
68 |     ...  
69 |     verify\_transaction($T);  
70 |     ...  
71 |     make\_transaction($T);  
72 |     ...  
73 | \`\`\`  
74 |   
75 | The rule successfully matches the example code.  
76 |   
77 | \#\# Further information  
78 |   
79 | See this video for more information about the difference between \`pattern-not\` and  \`pattern-not-inside\`.  
80 |   
81 | \<iframe class="yt\_embed" width="100%" height="432px" src="https://www.youtube.com/embed/JBEKaTTrhTY?si=aw7Sv1bz8l-a-4ZR" frameborder="0" allowfullscreen\>\</iframe\>  
82 |   
83 | 

\--------------------------------------------------------------------------------  
/docs/kb/rules/using-semgrep-rule-schema-in-vscode.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | description: Use the Semgrep rule schema in VS Code to help make rule writing easier.  
 3 | tags:  
 4 |   \- Rules  
 5 |   \- VS Code  
 6 | \---  
 7 |   
 8 |   
 9 |   
10 | \# Use the Semgrep rule schema to write rules in VS Code  
11 |   
12 | You may already be familiar with writing rules in the \[Semgrep Editor\](/semgrep-code/editor). However, if your IDE of choice is VS Code and you'd like to write Semgrep rules there, using the Semgrep rule schema will provide a richer editing environment, allowing VS Code to understand the shape of your rule's YAML file, including its value sets, defaults, and descriptions (\[reference\](https://marketplace.visualstudio.com/items?itemName=redhat.vscode-yaml\#associating-schemas)).  
13 |   
14 | :::tip  
15 | Writing rules locally in your IDE is also helpful for iteratively testing them against an entire local repository, as opposed to just a snippet of test code.  
16 | :::  
17 |   
18 | When the schema is set up, auto-completion operates in your VS Code IDE just as it does in the Semgrep Editor when writing rules:  
19 |   
20 | \!\[Example Semgrep YAML rule file with auto-complete\](/img/kb/vscode-schema-autocomplete-example.png)  
21 |   
22 | \#\# Add the Semgrep rule schema in VS Code  
23 |   
24 | Adding the Semgrep rule schema in VS Code requires two steps:  
25 |   
26 | 1\. Install the YAML Language Support extension by Red Hat  
27 | 2\. Associate the Semgrep rule schema  
28 |   
29 | \#\#\# Install the YAML Language Support extension by Red Hat  
30 |   
31 | You can install the  "YAML" extension authored by "Red Hat" directly in VS Code or by going to the Visual Studio Marketplace and installing it from there. In VS Code, go to the \*\*Extensions\*\* pane and search for \`yaml\`. This should yield the correction extension as the top result. However, please verify that you are installing the correct extension by ensuring it is the same as \[this one\](https://marketplace.visualstudio.com/items?itemName=redhat.vscode-yaml).  
32 |   
33 | \#\#\# Associate the Semgrep rule schema  
34 |   
35 | Once the extension is installed, associate the Semgrep rule schema with the Semgrep YAML rule definitions you are working on in VS Code using one of following methods:  
36 |   
37 | 1\. Directly in the YAML file  
38 | 2\. Using \`yaml.schemas\` in your VS Code \`settings.json\` file  
39 |   
40 | We recommend taking a look at the \[extension overview section on associating schemas\](https://marketplace.visualstudio.com/items?itemName=redhat.vscode-yaml\#associating-schemas) to gain a preliminary understanding before proceeding.  
41 |   
42 | \#\#\#\# Associate a schema directly in the YAML file  
43 |   
44 | To associate the schema directly within a Semgrep YAML rule file, include the following line at the top of the file:  
45 |   
46 | \`\`\`yaml  
47 | \# yaml-language-server: $schema=https://json.schemastore.org/semgrep.json  
48 | \`\`\`  
49 |   
50 | The drawback to this method is that it must be done independently for each YAML rule file.  
51 |   
52 | \#\#\#\# Associate a schema to a glob pattern via \`yaml.schemas\`  
53 |   
54 | Before proceeding, we recommend reading the \[extension overview\](https://marketplace.visualstudio.com/items?itemName=redhat.vscode-yaml\#associating-a-schema-to-a-glob-pattern-via-yaml.schemas) as a supplement to this article to better understand how YAML schemas are handled by the extension.  
55 |   
56 | To associate the Semgrep rule schema via \`yaml.schemas\` in your VS Code \`settings.json\` file (on macOS), go to:  
57 |   
58 |     Code \-\> Settings \-\> Settings \-\> Extensions \-\> YAML  
59 |   
60 | In the YAML extension settings, scroll down to \`Yaml: Schemas\` and click \`Edit in settings.json\`, as shown below:  
61 |   
62 | \!\[MacOS VS Code YAML extension settings\](/img/kb/vscode-yaml-schemas.png)  
63 |   
64 | This opens the \`settings.json\` file with an empty \`yaml.schemas\` object ready to be defined. For example, consider the following \`yaml.schemas\` definition:  
65 |   
66 | \`\`\`json  
67 | "yaml.schemas": {  
68 |     "https://json.schemastore.org/semgrep.json": "Downloads/semgrep\_rules/\*.yaml"  
69 | }  
70 | \`\`\`  
71 |   
72 | This associates the schema defined on the left side of the colon (\`:\`) with files matching the glob pattern on the right. The glob pattern matches any \`.yaml\` file located in a directory structure that matches \`Downloads/semgrep\_rules/\`. The desired glob pattern differs for varying operating systems and should reflect where you are storing Semgrep YAML rule files.  
73 |   
74 | After completing the configuration for \`yaml.schemas\`, open a Semgrep rule YAML file to verify that a notice shows at the top similar to this one:  
75 |   
76 | \!\[Example Semgrep YAML rule file with schema defined\](/img/kb/vscode-yaml-schema-example-file.png)  
77 |   
78 | This indicates that you've successfully associated the Semgrep rule schema with your Semgrep rule YAML file(s).  
79 | 

\--------------------------------------------------------------------------------  
