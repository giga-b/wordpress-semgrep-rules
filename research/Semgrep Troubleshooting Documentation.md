└── docs  
    └── troubleshooting  
        ├── rules.md  
        ├── semgrep-app.md  
        └── semgrep.md

/docs/troubleshooting/rules.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: rules  
 3 | tags:  
 4 |   \- Troubleshooting  
 5 |   \- Rule writing  
 6 | description: "Follow these troubleshooting steps when your pattern fails to parse, your rule doesn't match its intended code, and other rule writing pitfalls."  
 7 | \---  
 8 |   
 9 |   
10 | \# Troubleshooting rules  
11 |   
12 | This page intends to help rule authors fix common mistakes when writing Semgrep rules. If you have a problem while running a rule you didn't write yourself, please \[open a GitHub issue in the Semgrep Registry\](https://github.com/semgrep/semgrep-rules/issues/new/choose) repository.  
13 |   
14 |   
15 | \#\# If your pattern can’t be parsed  
16 |   
17 | This error means your pattern does not look like complete source code in the selected language.  
18 |   
19 | "Complete source code" means that the Semgrep pattern must look like a valid, complete expression or statement on its own.  
20 |   
21 | To illustrate with an example, Python isn't able to parse \`if 4 \< 5\` as a line of code, because it's missing the code block on the right hand side.  
22 |   
23 | \`\`\`python  
24 | \>\>\> if 4 \< 5  
25 |   File "\<stdin\>", line 1  
26 |     if 4 \< 5  
27 |             ^  
28 | SyntaxError: invalid syntax  
29 | \>\>\>  
30 | \`\`\`  
31 |   
32 | To get Python to parse this, you need to add a colon and a code block:  
33 |   
34 | \`\`\`python  
35 | \>\>\> if 4 \< 5: print("it works\!")  
36 | ...  
37 | it works\!  
38 | \>\>\>  
39 | \`\`\`  
40 |   
41 | The same way Python's parser cannot parse partial statements or expressions, Semgrep cannot either.  
42 |   
43 | The Semgrep pattern \`if $X \< 5\` is invalid, and needs to be changed to a complete statement with a wildcard: \`if $X \< 5: ...\`  
44 |   
45 | While the most common reason for pattern parse errors is the above, other things to check would be:  
46 |   
47 | \- Make sure the correct language is selected  
48 | \- If your pattern uses a metavariable, make sure it's all uppercase and does not start with a number. Valid metavariable names include \`$X\`, \`$NAME\`, and \`$\_VAR\_2\`. Invalid metavariable names include \`$name\`, \`$1stvar\` and \`$VAR-WITH-DASHES\`.  
49 |   
50 | \#\# If your rule doesn't match where it should  
51 |   
52 | In general, it helps to test the patterns within your rule in isolation. If you scan for the patterns one by one and they each find what you expect, the issue is with the Boolean logic within your rule. Review the \[rule syntax\](/writing-rules/rule-syntax) to make sure the operators are meant to behave like you expect. If you managed to find a pattern that behaves incorrectly, continue debugging with the section below.  
53 |   
54 | \#\# If your pattern doesn't match where it should  
55 |   
56 | If you isolated the issue to one specific pattern, here are some common issues to look out for:  
57 |   
58 | \- When referencing something imported from a module, you need to fully qualify the import path. To match \`import google.metrics; metrics.send(foo)\` in Python, your pattern needs to be \`google.metrics.send(...)\` instead of \`metrics.send(...)\`.  
59 | \- If your pattern uses a metavariable, make sure it's all uppercase and does not start with a number. Valid metavariable names include \`$X\`, \`$NAME\`, and \`$\_VAR\_2\`. Invalid metavariable names include \`$name\`, \`$1stvar\` and \`$VAR-WITH-DASHES\`.  
60 |   
61 | \#\# If a regex pattern doesn't match where it should  
62 |   
63 | \- When using \`metavariable-regex\`, the regex will match against all characters of the found metavariable. This means that if the metavariable matches a \`"foo"\` string in your code, the \`metavariable-regex\` pattern will run against a five character string with the quote characters at either end.  
64 | \- Note that using the pipe (\`|\`) character will append a newline to your regex\! If you are writing \`pattern-regex: |\` and then a newline with the regex, you almost certainly want the \`|-\` operator as in \`pattern-regex: |-\` to remove that trailing newline.  
65 | 

\--------------------------------------------------------------------------------  
/docs/troubleshooting/semgrep-app.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | slug: semgrep-app  
  3 | description: "Not seeing what you expect in Semgrep AppSec Platform? Follow these troubleshooting steps or find out how to get one-on-one help."  
  4 | title: Troubleshooting CI  
  5 | hide\_title: true  
  6 | tags:  
  7 |     \- Deployment  
  8 |     \- Troubleshooting  
  9 |     \- Semgrep AppSec Platform  
 10 | \---  
 11 |   
 12 | import RetrieveGhaLogs from "/src/components/procedure/\_retrieve-gha-logs.mdx"  
 13 |   
 14 | \# Troubleshooting CI scans  
 15 |   
 16 | This document outlines troubleshooting steps for issues related to \*\*Semgrep scans\*\* in a CI environment. Refer to the following sections if you're seeing results reported on files that have not changed since the last scan, frequent timeouts, or other issues.  
 17 |   
 18 | For issues on \*\*deployment or CI configuration\*\*, such as adding repositories, see the knowledge base articles in \[\<i class="fa-regular fa-file-lines"\>\</i\> Semgrep in CI\](/kb/semgrep-ci).  
 19 |   
 20 | \#\# Reproducing the issue locally  
 21 |   
 22 | To aid in debugging, you can reproduce some aspects of your Semgrep CI job locally. This enables you to inspect the logs and behavior through your terminal rather than in your CI provider's interface. Perform the following steps:  
 23 |   
 24 | 1\. Run the following command in your terminal:  
 25 |     \`\`\`  
 26 |     semgrep login  
 27 |     \`\`\`  
 28 | 1\. After logging in, return to the CLI and enter the following: \<pre class="language-bash"\>SEMGREP\_REPO\_NAME=\<span className="placeholder"\>your-organization\</span\>/\<span className="placeholder"\>repository-name\</span\> semgrep ci\</pre\>  
 29 |     For example, given a GitHub repository \`vulncorp/juice-shop\`, the full command would be:  
 30 |     \`\`\`  
 31 |     SEMGREP\_REPO\_NAME=vulncorp/juice-shop semgrep ci  
 32 |     \`\`\`  
 33 |   
 34 | \<br /\>  
 35 | When running \`semgrep ci\`, Semgrep fetches rules and any other configurations specific to your CI environment. Setting \`SEMGREP\_REPO\_NAME\` is optional, but ensures that:  
 36 | \- Results are sent to the same project (either a repository or folder in a monorepo) in Semgrep AppSec Platform.  
 37 | \- Any project-specific configurations, such as file ignores, are also respected.  
 38 |   
 39 | \#\# Troubleshooting GitHub  
 40 |   
 41 | The first piece of information that the team at Semgrep uses are the \*\*GitHub Actions logs\*\*.  
 42 |   
 43 | \<RetrieveGhaLogs /\>  
 44 |   
 45 | \<\!-- Commenting out this but keeping it in the docs because of the package-logs and semgrep ci \--verbose steps  
 46 | \`\`\`yaml  
 47 | name: Semgrep  
 48 | on:  
 49 |   workflow\_dispatch: {}  
 50 |   pull\_request: {}  
 51 |   push:  
 52 |     branches:  
 53 |       \- main  
 54 |       \- master  
 55 |     paths:  
 56 |       \- .github/workflows/semgrep.yml  
 57 |   schedule:  
 58 |     \# random HH:MM to avoid a load spike on GitHub Actions at 00:00  
 59 |     \- cron: '57 2 \* \* \*'  
 60 | jobs:  
 61 |   semgrep:  
 62 |     name: semgrep/ci  
 63 |     runs-on: ubuntu-latest  
 64 |     env:  
 65 |       SEMGREP\_APP\_TOKEN: ${{ secrets.SEMGREP\_APP\_TOKEN }}  
 66 |     container:  
 67 |       image: semgrep/semgrep  
 68 |     if: (github.actor \!= 'dependabot\[bot\]')  
 69 |     steps:  
 70 |       \- uses: actions/checkout@v3  
 71 |       \# Use this command for the verbose level of debugging.  
 72 |       \- run: semgrep ci \--verbose &\> semgrep.log  
 73 |       \# Use this command for the Semgrep's highest logging level, \--debug.  
 74 |       \# This command may take longer to run.  
 75 |       \# \- run: semgrep ci \--debug &\> semgrep.log  
 76 |       \- name: package-logs  
 77 |         if: always()  
 78 |         run: tar czf logs.tgz semgrep.log  
 79 |       \- name: upload-logs  
 80 |         if: always()  
 81 |         uses: actions/upload-artifact@v3  
 82 |         with:  
 83 |           name: logs.tgz  
 84 |           path: logs.tgz  
 85 |           retention-days: 1  
 86 | \`\`\`  
 87 | \--\>  
 88 |   
 89 | \#\# Troubleshooting GitLab SAST  
 90 |   
 91 | GitLab SAST includes and maintains a Semgrep integration called \[\`semgrep-sast\`\](https://gitlab.com/gitlab-org/security-products/analyzers/semgrep) for vulnerability finding.  
 92 |   
 93 | :::tip  
 94 | Please visit \[GitLab’s SAST troubleshooting guide\](https://docs.gitlab.com/ee/user/application\_security/sast/\#troubleshooting) for help with general GitLab SAST issues.  
 95 | :::  
 96 |   
 97 | \#\#\# The \`semgrep-sast\` CI job is slow  
 98 |   
 99 | The \`semgrep-sast\` job should take less than a minute to scan a large project with 50k lines of Python and TypeScript code. If you see worse performance, please \[reach out\](/support) to the Semgrep maintainers for help with tracking down the cause. Long runtimes are typically caused by just one rule or source code file taking too long. You can also try these solutions:  
100 |   
101 | \#\#\#\# Review global CI job configuration  
102 |   
103 | You might be creating large files or directories in your GitLab CI config's \`before\_script:\`, \`cache:\`, or similar sections. The \`semgrep-sast\` job scans all files available to it, not just the source code committed to Git, so if for example you have a cache configuration of  
104 |   
105 | \`\`\`yaml  
106 | cache:  
107 |   paths:  
108 |   \- node\_modules/  
109 | \`\`\`  
110 |   
111 | you should prevent those files from being scanned by \[disabling caching\](https://docs.gitlab.com/ee/ci/caching/\#disable-cache-on-specific-jobs) for the \`semgrep-sast\` job like this:  
112 |   
113 | \`\`\`yaml  
114 | semgrep-sast:  
115 |   cache: {}  
116 | \`\`\`  
117 |   
118 | \#\#\#\# Exclude large paths  
119 |   
120 | If you know which large files might be taking too long to scan, you can use \[GitLab SAST's path exclusion feature\](https://docs.gitlab.com/ee/user/application\_security/sast/\#vulnerability-filters) to skip files or directories matching given patterns.  
121 |   
122 | \- \`SAST\_EXCLUDED\_PATHS: "\*.py"\` will ignore the paths at:  
123 |   \`foo.py\`, \`src/foo.py\`, \`foo.py/bar.sh\`.  
124 | \- \`SAST\_EXCLUDED\_PATHS: "tests"\` will ignore  
125 |   \`tests/foo.py\` as well as \`a/b/tests/c/foo.py\`.  
126 |   
127 | You can use a comma separated list to ignore multiple patterns: \`SAST\_EXCLUDED\_PATHS: "\*.py, tests"\` will ignore all of the preceding paths.  
128 |   
129 | \#\#\# \`semgrep-sast\` reports false positives or false negatives  
130 |   
131 | If you're not getting results where you should, or you get too many results, the problem might be with the patterns Semgrep scans for.  
132 |   
133 | You can review the search patterns in the \[rules directory of the \`semgrep-sast\` analyzer\](https://gitlab.com/gitlab-org/security-products/analyzers/semgrep/-/tree/main/rules) and report issues to the GitLab team. Refer to the \[Semgrep rule writing tutorial\](https://semgrep.dev/learn) to help better understand these rule files. You can also refer to the \[Semgrep Registry\](https://semgrep.dev/explore) which is a collection of 2,000+ Semgrep rules curated by Semgrep, Inc.  
134 |   
135 | \#\#\# \`semgrep-sast\` crashes, fails, or is otherwise broken  
136 |   
137 | Semgrep prints an error message to explain what went wrong upon crashes, and often also what to do to fix it.  
138 |   
139 | The output of Semgrep is hidden by default, but \[GitLab provides a way\](https://docs.gitlab.com/ee/user/application\_security/sast/\#sast-debug-logging) to see it by setting an environment variable:  
140 |   
141 | \`\`\`yaml  
142 | variables:  
143 |   SECURE\_LOG\_LEVEL: "debug"  
144 | \`\`\`  
145 |   
146 | \#\#\# How to get GitLab assistance  
147 |   
148 | If you’re a GitLab customer and suspect there’s an issue with GitLab, please \[contact GitLab support\](https://about.gitlab.com/support/) and open a support ticket. Users of GitLab’s free plans should open a thread in the \[GitLab Community Forum\](https://forum.gitlab.com).  
149 |   
150 | \#\# Project-specific issues  
151 |   
152 | A \*\*project\*\* is any repository you have added to Semgrep Cloud Platform for scanning. Refer to the following sections for issues in the \*\*Semgrep AppSec Platform \> Projects\*\* page.  
153 |   
154 | \#\#\# If a project reports the last scan "Never started"  
155 |   
156 | This status means that your CI job never authenticated to Semgrep AppSec Platform.  
157 |   
158 | Check your CI provider (such as GitHub Actions) for the latest Semgrep job execution.  
159 |   
160 | \#\#\#\# If you can’t find a Semgrep CI job  
161 |   
162 | The issue is likely with the CI configuration.  
163 |   
164 | \- Make sure that the branch you committed a CI job to is included in the list of branches the job is triggered on.  
165 | \- Make sure that the CI configuration file has valid syntax. Most providers have a tool for checking the syntax of configuration files.  
166 |   
167 | \#\#\#\# If a Semgrep CI job exists  
168 |   
169 | Check the log output for any hints about what the issue is.  
170 |   
171 | \- If the logs mention a missing token or an authentication failure, you can get a new token from the \[\*\*Settings \> Tokens\*\* page of Semgrep AppSec Platform\](https://semgrep.dev/orgs/-/settings/tokens), and set it as \`SEMGREP\_APP\_TOKEN\` in your CI provider's secret management UI.  
172 | \- Alternatively, if this is the first scan after adding a new GitHub repository, and the repository is a fork, check your Actions tab to see if workflows are enabled:  
173 |   \!\[Screenshot of GitHub's Actions tab with workflows disabled\](/img/github-workflows-disabled.png)  
174 |   \- Enable workflows by clicking \*\*I understand my workflows, go ahead and enable them\*\* to allow Semgrep to scan.  
175 |   
176 | \#\#\# If a project reports a scan 'Never finished'  
177 |   
178 | Most often, this status means that the job started and authenticated correctly, but failed or was canceled before completion. Check your CI provider (such as GitHub Actions) for the log output of the latest Semgrep job execution. In most cases, you will see an error message with detailed instructions on what to do.  
179 |   
180 | Sometimes, this status may be shown when the scan has been running for a long time (more than an hour) and is still in progress. Scans that eventually produce results will be accepted by Semgrep AppSec Platform, even if this message is shown.  
181 |   
182 | \#\#\#\# If the job is aborted due to taking too long  
183 |   
184 | Many CI providers have a time limit for how long a job can run. If your CI scans regularly take too long and fail to complete:  
185 |   
186 | \- Please \[reach out\](/support) to the Semgrep team for help with tracking down the cause. Semgrep scans most projects with hundreds of rules within a few minutes, and long run times are often caused by just one rule or source code file taking too long.  
187 | \- To optimize run times, use Semgrep's diff-aware scanning in pull requests and merge requests to skip scanning unchanged files. For more details, see \[Semgrep's behavior\](/deployment/customize-ci-jobs).  
188 | \- Skip scanning large and complex source code files (such as minified JS or generated code) if you know their path by adding a \`.semgrepignore\` file. See \[how to ignore files & directories in Semgrep CI\](/ignoring-files-folders-code).  
189 | 

\--------------------------------------------------------------------------------  
/docs/troubleshooting/semgrep.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: semgrep  
 3 | description: "Get more information when Semgrep hangs, crashes, times out, or runs very slowly."  
 4 | title: Troubleshooting the CLI  
 5 | hide\_title: true  
 6 | append\_help\_link: true  
 7 | tags:  
 8 |   \- Troubleshooting  
 9 |   \- CLI  
10 | \---  
11 |   
12 | \# Troubleshooting Semgrep CLI  
13 |   
14 | \#\# Semgrep exited with code \-11 (or \-9)  
15 |   
16 | This can happen when Semgrep crashes, usually as a result of memory exhaustion. \`-11\` and \`-9\` are the POSIX signals raised to cause the crash.  
17 |   
18 | Review troubleshooting steps for memory exhaustion at \[Semgrep scan troubleshooting: Memory usage issues\](/docs/kb/semgrep-code/semgrep-scan-troubleshooting/\#memory-usage-issues-oom-errors).  
19 |   
20 | \#\# Semgrep is too slow  
21 |   
22 | Semgrep records runtimes for each file and rule. This information is displayed when you include the \`--time\` flag when running Semgrep. How you choose to interact with the \`--time\` output depends on your goals.  
23 |   
24 | \#\#\# I want Semgrep to run faster  
25 |   
26 | Review troubleshooting steps for slow scans at \[Semgrep scan troubleshooting: Slow scans\](/docs/kb/semgrep-code/semgrep-scan-troubleshooting/\#slow-scans).  
27 |   
28 | \#\#\# I am a contributor who wants to improve Semgrep's engine  
29 |   
30 | Thank you\! Check out the \[Contributing docs\](/docs/contributing/contributing) to get started.  
31 |   
32 | The section \[Explore results from a slow run of Semgrep\](/contributing/semgrep-core-contributing\#explore-results-from-a-slow-run-of-semgrep) is helpful if you haven't previously investigated Semgrep performance.  
33 | 

\--------------------------------------------------------------------------------  
