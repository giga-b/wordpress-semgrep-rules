└── docs  
    └── extensions  
        ├── overview.md.template  
        ├── pre-commit.md  
        ├── semgrep-intellij.md  
        └── semgrep-vs-code.md

/docs/extensions/overview.md.template:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: overview  
 3 | append\_help\_link: true  
 4 | description: \>-  
 5 |   Learn how to use Semgrep in an editor, in pre-commit, and in other tools.  
 6 | \---  
 7 | \<\!-- IMPORTANT: Make sure to edit the .md.template source file, not the  
 8 |      generated .md file \--\>  
 9 |   
10 | import IdeList from "/src/components/reference/\_ide-list.md"  
11 |   
12 | \# Extensions  
13 |   
14 | Several third-party tools include Semgrep extensions.  
15 |   
16 | \#\# Official IDE extensions  
17 |   
18 | \<IdeList /\>  
19 |   
20 | \#\# Use of Language Server Protocol (LSP)  
21 |   
22 | All of the official IDE extensions use the \[Language Server Protocol\](https://microsoft.github.io/language-server-protocol/) to communicate with Semgrep. This allows the team to focus on one codebase that can be shared across most modern editor platforms.  
23 |   
24 | \#\# \`pre-commit\`  
25 |   
26 | Prevent secrets or security issues from entering your Git source control history by running Semgrep as a \[\<i class="fas fa-external-link fa-xs"\>\</i\> pre-commit\](https://pre-commit.com/) hook. See \[\`pre-commit\` documentation\](/extensions/pre-commit) for details.  
27 |   
28 | \#\# Semgrep as an engine  
29 |   
30 | Many other tools have capabilities powered by Semgrep.  
31 | Add yours \[with a pull request\](https://github.com/semgrep/semgrep-docs)\!  
32 |   
33 | \- \[DefectDojo\](https://github.com/DefectDojo/django-DefectDojo/pull/2781)  
34 | \- \[Dracon\](https://github.com/thought-machine/dracon)  
35 | \- \[GitLab SAST\](https://docs.gitlab.com/ee/user/application\_security/sast/\#multi-project-support)  
36 | \- \[GuardDog\](https://github.com/datadog/guarddog)  
37 | \- \[litbsast\](https://github.com/ajinabraham/libsast)  
38 | \- \[mobsfscan\](https://github.com/MobSF/mobsfscan)  
39 | \- \[nodejsscan\](https://github.com/ajinabraham/nodejsscan)  
40 | \- \[ScanMyCode CE (Community Edition)\](https://github.com/marcinguy/scanmycode-ce)  
41 | \- \[SecObserve\](https://github.com/MaibornWolff/SecObserve)  
42 | 

\--------------------------------------------------------------------------------  
/docs/extensions/pre-commit.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: pre-commit  
 3 | title: Run scans on pre-commit  
 4 | hide\_title: true  
 5 | description: Learn to run a Semgrep scan before committing code; this prevents security issues or leaked secrets from entering your source control's history.  
 6 | tags:  
 7 |   \- Extensions  
 8 | \---  
 9 |   
10 | import Login from "/src/components/procedure/\_login-activate.mdx"  
11 |   
12 | \# Run scans on \`pre-commit\`  
13 |   
14 | The \[pre-commit framework\](https://pre-commit.com/) can run \`semgrep\` when you commit changes. This is helpful in preventing secrets and security issues from leaking into your Git history.  
15 |   
16 | \#\# Prerequisites  
17 |   
18 | \[\<i class="fas fa-external-link fa-xs"\>\</i\> The \`pre-commit\` framework\](https://pre-commit.com).  
19 |   
20 | \#\# \`pre-commit\` with Semgrep CE (no login)  
21 |   
22 | Use these instructions to run \`pre-commit\` without logging in. You can still use custom rules or rules from the Semgrep Registry.  
23 |   
24 | Add the following to your \`.pre-commit-config.yaml\` file:  
25 |   
26 | \`\`\`yaml  
27 | repos:  
28 | \- repo: https://github.com/semgrep/pre-commit  
29 |   rev: 'SEMGREP\_VERSION\_LATEST'  
30 |   hooks:  
31 |     \- id: semgrep  
32 |       entry: semgrep  
33 |       \# Replace \<SEMGREP\_RULESET\_URL\> with your custom rule source  
34 |       \# or see https://semgrep.dev/explore to select a ruleset and copy its URL  
35 |       args: \['--config', '\<SEMGREP\_RULESET\_URL\>', '--error', '--skip-unknown-extensions'\]  
36 | \`\`\`  
37 |   
38 | \#\# \`pre-commit\` with your Semgrep AppSec Platform configuration  
39 |   
40 | You  can also run custom rules and rulesets from Semgrep AppSec Platform, similar to running \`semgrep ci\`.  
41 |   
42 | Ensure that you are logged in:  
43 |   
44 | \<Login /\>  
45 |   
46 | Add the following to your \`.pre-commit-config.yaml\` file:  
47 |   
48 | \`\`\`yaml  
49 | repos:  
50 | \- repo: https://github.com/semgrep/pre-commit  
51 |   rev: 'SEMGREP\_VERSION\_LATEST'  
52 |   hooks:  
53 |     \- id:  semgrep-ci  
54 | \`\`\`  
55 |   
56 | For guidance on customizing Semgrep's behavior in pre-commit, see \[Customize Semgrep in pre-commit\](/docs/kb/integrations/customize-semgrep-precommit).  
57 | 

\--------------------------------------------------------------------------------  
/docs/extensions/semgrep-intellij.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: semgrep-intellij  
 3 | title: IntelliJ extension  
 4 | hide\_title: true  
 5 | append\_help\_link: true  
 6 | description: "Learn how to install and use Semgrep's extension for IntelliJ."  
 7 | tags:  
 8 |     \- Extensions  
 9 | \---  
10 |   
11 | import IdeLimitations from "/src/components/reference/\_ide-limitations.md"  
12 | import QuickstartIntelliJ from "/src/components/procedure/\_quickstart-intellij.md"  
13 |   
14 | \# Semgrep IntelliJ extension  
15 |   
16 | \[Semgrep\](https://semgrep.dev/) swiftly scans code and package dependencies for known issues, software vulnerabilities, and detected secrets. Run Semgrep in your developer environment with the IntelliJ extension to catch code issues as you type. By default, the Semgrep IntelliJ extension scans code whenever you change or open files.  
17 |   
18 | \#\# Prerequisites  
19 |   
20 | The Semgrep IntelliJ extension communicates with Semgrep command-line interface (CLI) to run scans. Install Semgrep CLI before you can use the extension. To install Semgrep CLI:  
21 |   
22 | \`\`\`sh  
23 | \# For macOS  
24 | $ brew install semgrep  
25 |   
26 | \# For Ubuntu/WSL/Linux/macOS  
27 | $ python3 \-m pip install semgrep  
28 | \`\`\`  
29 |   
30 | \> Semgrep's IntelliJ extension doesn't currently work on Windows machines.  
31 |   
32 | \#\# Quickstart  
33 |   
34 | \<QuickstartIntelliJ /\>  
35 |   
36 | \#\# Supported Jet Brains products  
37 |   
38 | Semgrep's IDE extension is available in many Jet Brains products:  
39 |   
40 | \- AppCode  
41 | \- Aqua  
42 | \- CLion  
43 | \- DataSpell  
44 | \- DataGrip  
45 | \- GoLand  
46 | \- IntelliJ IDEA Ultimate  
47 | \- PhpStorm  
48 | \- PyCharm Professional  
49 | \- Rider  
50 | \- RubyMine  
51 | \- RustRover  
52 | \- WebStorm  
53 |   
54 | :::caution  
55 |   
56 | IntelliJ extension does not support:  
57 | \- IntelliJ IDEA Community Edition.   
58 |   
59 | Semgrep does not offer an IDE integration with IntelliJ Community Edition because \[this version lacks support for the Language Server Protocol (LSP)\](https://plugins.jetbrains.com/docs/intellij/language-server-protocol.html\#supported-ides), which is essential for enabling Semgrep’s code scanning features. IntelliJ Ultimate, which includes LSP support, is required to use Semgrep's IDE integration.  
60 |   
61 | :::  
62 |   
63 | \#\# Commands  
64 |   
65 | Run Semgrep extension commands through the IntelliJ Command Palette. You can access the Command Palette by pressing \<kbd\>Ctrl+⇧Shift+A\</kbd\> (Windows) or \<kbd\>⌘Command+⇧Shift+A\</kbd\> (macOS) on your keyboard.  
66 |   
67 | \- \`Sign in with Semgrep\`: Sign up or log in to the Semgrep AppSec Platform (this command opens a new window in your browser). Alternatively, you can log in through your command-line interface by running \`semgrep login\`.  
68 | \- \`Sign out of Semgrep\`: Log out of Semgrep AppSec Platform. If you are logged out, you lose access to Semgrep Supply Chain and Semgrep Secrets. Alternatively, you can sign out through your command-line interface by running \`semgrep logout\`.  
69 | \- \`Scan workspace with Semgrep\`: Scan files that have been changed since the last commit in your current workspace.  
70 | \- \`Scan workspace with Semgrep (Including Unmodified Files)\`: Scan all files in the current workspace.  
71 |   
72 | :::tip  
73 | You can also click the Semgrep icon in the IntelliJ toolbar to quickly access all available commands.  
74 | :::  
75 |   
76 | \#\# Features  
77 |   
78 | \#\#\# Automatic scanning  
79 |   
80 | When you open a file, Semgrep scans it right away.  
81 |   
82 | \#\#\# Rule Quick Links  
83 |   
84 | Hover over a match and click the link.  
85 |   
86 | \#\# Support  
87 |   
88 | If you need our support, join the \[Semgrep community Slack workspace\](http://go.semgrep.dev/slack) and tell us about any problems you encountered.  
89 |   
90 | \#\# Limitations  
91 |   
92 | \<IdeLimitations /\>  
93 |   
94 | \#\# License  
95 |   
96 | The Semgrep IntelliJ extension is licensed under the LGPL 2.1 license.  
97 | 

\--------------------------------------------------------------------------------  
/docs/extensions/semgrep-vs-code.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | slug: semgrep-vs-code  
  3 | title: Visual Studio Code extension  
  4 | hide\_title: true  
  5 | description: Learn how to install and use Semgrep's extension for Visual Studio Code.  
  6 | tags:  
  7 |   \- Extensions  
  8 | \---  
  9 |   
 10 | import IdeLimitations from "/src/components/reference/\_ide-limitations.md"  
 11 | import QuickstartVSCode from "/src/components/procedure/\_quickstart-vscode.md"  
 12 |   
 13 | \# Semgrep Visual Studio Code extension  
 14 |   
 15 | \[Semgrep's Visual Studio Code (VS Code) Extension\](https://marketplace.visualstudio.com/items?itemName=Semgrep.semgrep) allows you to scan lines when you open and change files in your workspace. It offers:  
 16 |   
 17 | \- Automatic scans whenever you open a file  
 18 | \- Inline results and problem highlighting, as well as quick links to the definitions of the rules underlying the findings  
 19 | \- Autofix, which allows you to apply Semgrep's suggested resolution for the findings  
 20 |   
 21 | \#\# Prerequisites  
 22 |   
 23 | \- See \[Supported Languages\](/supported-languages) to verify that the extension supports your project.  
 24 | \- Windows users must use Semgrep VS Code extension v1.6.2 or later.  
 25 |   
 26 | \#\# Quickstart  
 27 |   
 28 | \<QuickstartVSCode /\>  
 29 |   
 30 | \#\# Commands  
 31 |   
 32 | Run Semgrep extension commands through the \[Visual Studio Code Command Palette\](https://code.visualstudio.com/docs/getstarted/userinterface\#\_command-palette). You can access the Command Palette using \<kbd\>Ctrl+⇧Shift+P\</kbd\> or \<kbd\>⌘Command+⇧Shift+P\</kbd\> (macOS). The following list includes all available Semgrep extension commands:  
 33 |   
 34 | \- \`Semgrep: Scan all files in a workspace\`: Scan all files in the current workspace.  
 35 | \- \`Semgrep Search: Clear\`: Clear pattern searches from the Primary Side Bar's Semgrep Search view.  
 36 | \- \`Semgrep Search: Focus on Search Results View\`: Bring the Primary Side Bar's Semgrep Search view into focus  
 37 | \- \`Semgrep Restart Language Server\`: Restart the language server  
 38 | \- \`Semgrep: Scan changed files in a workspace\`: Scan files that have been changed since the last commit in your current workspace.  
 39 | \- \`Semgrep: Search by pattern\`: Search for patterns in code using Semgrep pattern syntax. For more information, see \[Pattern syntax\](/docs/writing-rules/pattern-syntax) documentation.  
 40 | \- \`Semgrep: Show Generic AST\`: Show generic AST in a new window  
 41 | \- \`Semgrep: Show named Generic AST\`: Show named AST in a new window  
 42 | \- \`Semgrep: Sign in\`: Sign in or log in to the Semgrep AppSec Platform (this command opens a new window in your browser). When you sign in, you can automatically scan with Semgrep \[Pro rules\](/semgrep-code/pro-rules) and add additional rules to the \[Policies\](https://semgrep.dev/orgs/-/policies) in Semgrep Code. If you are logged in with the command-line interface using \<code\>semgrep\&nbsp;login\</code\>, you are also already signed in with the Visual Studio Code Semgrep extension. Alternatively, you can log in through your command-line interface by running \`semgrep login\`.  
 43 | \- \`Semgrep: Sign out\`: Log out from Semgrep AppSec Platform. Alternatively, you can sign out through your command-line interface by running \`semgrep logout\`.  
 44 | \- \`Semgrep: Update rules\`: For logged-in users. If the rules in the \[Policies\](https://semgrep.dev/orgs/-/policies) or rules included through the \*\*Semgrep › Scan: Configuration\*\* configuration option have been changed, this command loads the new configuration of your rules for your following scan.  
 45 |   
 46 | Tip: You can click the Semgrep icon in the Visual Studio Code to access all available commands quickly.  
 47 |   
 48 | \#\# Additional extension features  
 49 |   
 50 | Use auto-fix to apply code change suggestions from Semgrep to remediate the security issue.  
 51 |   
 52 | \<video src="https://github.com/returntocorp/semgrep-vscode/assets/626337/3b6a730d-57e9-48a4-8065-9fa52388d77a" controls="controls"\>  
 53 | \</video\>  
 54 |   
 55 | Add and update new rules to expand Semgrep extension's capabilities.  
 56 |   
 57 | \<video src="https://github.com/returntocorp/semgrep-vscode/assets/626337/fed6b6ec-e0b5-495b-a488-4f3c805dd58b" controls="controls"\>  
 58 | \</video\>  
 59 |   
 60 | Fine-tune and customize the rules Semgrep uses to improve your scan results:  
 61 |   
 62 | 1\. Go to \[Semgrep Registry\](https://semgrep.dev/explore). Ensure that you are signed in.  
 63 | 2\. Explore the Semgrep Registry, select a rule, and then click \*\*Add to Policy\*\*. You can view and manage your rules in \[Policies\](https://semgrep.dev/orgs/-/policies).  
 64 | 3\. Rescan your code. Use \<kbd\>Ctrl+⇧Shift+P\</kbd\> or \<kbd\>⌘Command+⇧Shift+P\</kbd\> (macOS) to launch the Command Palette, then run \`Semgrep: Update rules\`.  
 65 |   
 66 | \#\# Configure the extension  
 67 |   
 68 | To configure the Semgrep extension, open its \*\*Extension Settings\*\* page:  
 69 |   
 70 | 1\. Use \<kbd\>⇧Shift+Ctrl+X\</kbd\> or \<kbd\>⇧Shift+⌘Command+X\</kbd\> (macOS) to open the \*\*Extensions\*\* view.  
 71 | 2\. Select \*\*Semgrep\*\*.  
 72 | 3\. Click the \*\*gear\*\* and select \*\*Extension Settings\*\*.  
 73 |   
 74 | \#\#\# Configuration options  
 75 |   
 76 | \- \*\*Semgrep › Do Hover\*\*: Enable AST node views when hovering over a finding.  
 77 | \- \*\*Semgrep › Path\*\*: Set the path to the Semgrep executable.  
 78 | \- \*\*Semgrep › Scan: Configuration\*\*: Specify rules or rulesets you want Semgrep to use to scan your code. Each item can be a YAML configuration file, a URL of a configuration file, or a directory of YAML files. Use \`auto\` to automatically obtain rules tailored to your project. Semgrep uses your project URL to log into the Semgrep Registry. See \[Running rules\](/docs/running-rules) for more information. Run \`Semgrep: Update rules\` using the Visual Studio Code Command Palette to update the rules configuration for your following scan whenever you change the rule configuration.  
 79 | \- \*\*Semgrep › Scan: Exclude\*\*: List files and directories that Semgrep should ignore when scanning.  
 80 | \- \*\*Semgrep › Scan: Include\*\*: List files and directories scanned by Semgrep. This option globally overrides the workspace setting. As a result, Semgrep scans all included paths.  
 81 | \- \*\*Semgrep › Scan: Jobs\*\*: Specify how many parallel jobs can run simultaneously. The default number of parallel jobs is one.  
 82 | \- \*\*Semgrep › Scan: Max Memory\*\*: Sets the maximum memory in MB to use.  
 83 | \- \*\*Semgrep › Scan: Max Target Bytes\*\*: Sets the maximum size of the target in bytes to scan.  
 84 | \- \*\*Semgrep › Scan: Only Git Dirty\*\*: Allow Semgrep to scan your code whenever you open a new file and display the findings for lines that have changed since the last commit. On by default.  
 85 | \- \*\*Semgrep › Scan: Pro\_intrafile\*\*: Enable intrafile scanning using the Pro Engine.  
 86 | \- \*\*Semgrep › Scan: Timeout\*\*: Set the maximum run time in seconds before Semgrep times out and stops scanning your code. The default value is 30\.  
 87 | \- \*\*Semgrep › Scan: Timeout Threshold\*\*: Set the maximum number of rules that can timeout on a file before the file is skipped. If set to 0, there will be no limit. Defaults to 3\.  
 88 | \- \*\*Semgrep \> Trace: Server\*\*: This option is useful for debugging. The \*\*messages\*\* option displays communication of the Semgrep Visual Studio Code extension with the LSP server. The default option is \*\*verbose\*\*.  
 89 |   
 90 | \#\#\# Experimental configuration options  
 91 |   
 92 | The following experimental features should only be used upon recommendation by Semgrep:  
 93 |   
 94 | \- \*\*Semgrep \> Ignore CLI Version\*\*: Ignore the CLI Version and enable all extension features.  
 95 |   
 96 | \#\# Limitations  
 97 |   
 98 | \<IdeLimitations /\>  
 99 |   
100 | \#\# License  
101 |   
102 | The Semgrep VS Code extension is licensed under the LGPL 2.1 license.  
103 | 

\--------------------------------------------------------------------------------  
