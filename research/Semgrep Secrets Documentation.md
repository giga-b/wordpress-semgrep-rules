└── docs  
    └── semgrep-secrets  
        ├── conceptual-overview.md  
        ├── generic-secrets.md  
        ├── getting-started.md  
        ├── glossary.md  
        ├── historical-scanning.md  
        ├── policies.md  
        ├── rules.md  
        ├── validators.md  
        └── view-triage.md

/docs/semgrep-secrets/conceptual-overview.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | slug: /semgrep-secrets/conceptual-overview  
  3 | append\_help\_link: true  
  4 | title: Overview  
  5 | hide\_title: true  
  6 | description: Learn how Semgrep Secrets detects leaked secrets and helps you prioritize what keys to rotate.  
  7 | tags:  
  8 |   \- Semgrep Secrets  
  9 | \---  
 10 |   
 11 | import ValidationStates from '/src/components/reference/\_validation-states.mdx'  
 12 |   
 13 | \# Semgrep Secrets overview  
 14 |   
 15 | \*\*Semgrep Secrets\*\* scans code to detect exposed API keys, passwords, and other  
 16 | credentials. When exposed, these can be used by malicious actors to leak data  
 17 | or gain access to sensitive systems. Semgrep Secrets allows you to determine:  
 18 |   
 19 | \* What secrets have been committed to your repository.  
 20 | \* The validation status of the secret; for example, \*\*valid\*\* secrets are those that have been tested against a web service and  
 21 | confirmed to successfully grant resources or authentication. They are actively  
 22 | in use.  
 23 | \* For GitHub repositories: if there are credentials in public or private repositories.  
 24 |   
 25 | Semgrep saves security engineers time and effort by prioritizing valid leaked secrets and informs developers of valid secrets in their PRs and MRs by posting comments directly.  
 26 |   
 27 | \#\# How Semgrep Secrets works  
 28 |   
 29 | To ensure that findings are high-signal, comprehensive, and easy for users to  
 30 | prioritize, a Semgrep Secrets scan performs the following:  
 31 |   
 32 | \* Search using regex  
 33 | \* Semantic analysis  
 34 | \* Validation  
 35 | \* Entropy analysis  
 36 |   
 37 | The following sections explain how each step works.  
 38 |   
 39 | \#\#\# Detect secrets through regex  
 40 |   
 41 | Semgrep Secrets uses a regex language detector to find secrets in various file types. This is done by detecting a commonly defined prefix and then searching for the secret using its expected length and format.  
 42 |   
 43 | To reduce the number of false positives this process raises, Semgrep uses and combines as many of the following processes with its search using regex when possible:  
 44 |   
 45 | \- Removal of results that are likely to be false positives  
 46 | \- Validation  
 47 | \- Entropy analysis  
 48 |   
 49 | \#\#\# Detect secrets through semantic analysis  
 50 |   
 51 | Semantic analysis refers to Semgrep Secrets' ability to understand how data is  
 52 | used within your code. This differentiates Semgrep Secrets from regex-based  
 53 | detectors that simply define a pattern to match a piece of code.  
 54 |   
 55 | Semgrep Secrets uses several mechanisms to perform semantic analysis. It uses  
 56 | \[\<i class="fa-regular fa-file-lines"\>\</i\> data-flow  
 57 | analysis\](/writing-rules/data-flow/data-flow-overview) and \[\<i  
 58 | class="fa-regular fa-file-lines"\>\</i\> constant  
 59 | propagation\](/writing-rules/data-flow/constant-propagation) which means that it  
 60 | is able to track data, such as variables, and the flow of that data across files  
 61 | and functions in your codebase.  
 62 |   
 63 | Performing semantic analysis is encapsulated in \[\<i class="fa-regular  
 64 | fa-file-lines"\>\</i\> rules\](/running-rules). By running these rules, Semgrep  
 65 | Secrets is able to detect if a variable is renamed,  
 66 | reassigned, or used in a function in such a way that a secret is exposed.  
 67 |   
 68 | \<\!-- TODO, rewrite this to be more relevant and use a better example--\>  
 69 | See the following rule and JavaScript test code for an example.  
 70 |   
 71 | \<iframe title="AWS hardcoded access key" src="https://semgrep.dev/embed/editor?snippet=EPj5" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
 72 | \<br /\>  
 73 |   
 74 | \<\!--  
 75 | The rule detects hardcoded AWS secret access keys. The test code defines an access key in the variable \`secret\`. Click \*\*\<i class="fa-solid fa-play"\>\</i\> Run\*\* to see the true positives.  
 76 | \--\>  
 77 |   
 78 | \<\!-- Some differences between Semgrep Secrets and regex-based scanners include: \--\>  
 79 |   
 80 | \<\!--\* \*\*Line 2:\*\* Both can detect the variable name \`secret\` and its value (token)  
 81 |   in line 2\. \* A regex-based scanner may generate a noisy finding from line 2  
 82 |   even though \`secret\` has not been passed to any function. \* Semgrep Secrets  
 83 |   doesn't generate a finding because the token hasn't been passed as a  
 84 |   \`secretAccessKey\` or similar.  
 85 | \* \*\*Line 7:\*\* Both can detect \*\*line 6\*\*, in which the plain-text secret is  
 86 |   passed to the \`AWS.config.update\` function.  
 87 | \* \*\*Line 17:\*\* Both can detect \*\*line 14\*\*, in which \`secret\` is passed.  
 88 | \* \*\*Line 26:\*\* Semgrep Secrets correctly skips \`conf.secret\` in \*\*line 21\*\*.  
 89 |   Regex-based scanners simply looking for matches of the string \`secret\`  
 90 |   generate a false positive. \--\>  
 91 |   
 92 | \#\#\# Validate secrets  
 93 |   
 94 | After scanning your codebase, Semgrep Secrets uses a proprietary  
 95 | \*\*validator\*\* to determine if a secret is actively being used or some other state if there is a validator defined in the rule used.  
 96 |   
 97 | :::info  
 98 | All validations, such as API calls, are done \*\*locally\*\* in your environment. No tokens are sent to Semgrep servers.  
 99 | :::  
100 |   
101 | 1\. The validator detects the service, such as Slack or AWS, that the secret  
102 |    is used for.  
103 | 2\. If the validator doesn't support the service that the secret is used  
104 |    for, Semgrep notes that there is \*\*No validator\*\* finding for the secret.  
105 | 3\. Semgrep Secrets performs an API  
106 |   call if the validator supports the service. The following outcomes can occur:  
107 |    \<ValidationStates /\>  
108 |   
109 | By performing this validation check, you can prioritize and triage the most  
110 | high-priority, active findings.  
111 |   
112 | :::note  
113 | \- For a list of all supported detectors that Semgrep offers, see the \[Policies\](/semgrep-secrets/policies) page in your deployment.  
114 | \- See \[Validators\](/semgrep-secrets/validators) for syntax and examples.  
115 | :::  
116 |   
117 | \#\#\# Fine-tune findings through entropy analysis  
118 |   
119 | Entropy is the measure of a \*\*string's randomness\*\*. It's used to measure how  
120 | likely a string is random. If a string is highly entropic, it's highly  
121 | random. For certain types of secrets, such as API keys, randomness indicates  
122 | that a string could be a secret. By performing entropy analysis, Semgrep Secrets  
123 | can reduce false positives and produce more true positives.  
124 |   
125 | Examples of high-entropy (random) strings:  
126 |   
127 | \`\`\`  
128 | VERZVs+/nd56Z+/Qxy1mzEqqBwUS1l9D4YbqmPoOß  
129 | ghp\_J2YfbObjXcaT8Bfpa3kxe5iiY0TkwS1uNnDa  
130 | \`\`\`  
131 |   
132 | Examples of low-entropy strings:  
133 |   
134 | \`\`\`  
135 | XXXXXX  
136 | txtPassword1  
137 | \`\`\`  
138 |   
139 | \#\# Next steps  
140 |   
141 | See \[\<i class="fa-regular fa-file-lines"\>\</i\> Scan for secrets\](/semgrep-secrets/getting-started) to learn how to:  
142 | \* Enable secrets scanning for your repositories  
143 | \* Manage the rules in your \[policy\](/semgrep-secrets/policies) to control how your scan runs.  
144 | \* View and triage secrets-related findings  
145 | \* Receive notifications and post tickets whenever Semgrep Secrets identifies issues  
146 | \* Write \[custom rules\](/semgrep-secrets/rules) with \[validators\](/semgrep-secrets/validators) to find bespoke secrets  
147 | 

\--------------------------------------------------------------------------------  
/docs/semgrep-secrets/generic-secrets.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: generic-secrets  
 3 | title: Scan for generic secrets  
 4 | hide\_title: true  
 5 | description: Use Semgrep to identify generic secrets in your code.  
 6 | tags:  
 7 |  \- Semgrep Assistant  
 8 |  \- Semgrep Secrets  
 9 | \---  
10 |   
11 | import PL from '@site/src/components/Placeholder';  
12 |   
13 | \# Generic secrets AI  
14 |   
15 | Like Semgrep Secrets, which scans for specific secrets, \*\*generic secrets AI\*\* scans your code for the inadvertent inclusion of credentials, such as API keys, passwords, and access tokens using rules. However, AI-powered generic secrets detection looks for common keywords, such as auth, key, or passwords, and flags anything nearby that appears to be a secret. It then analyzes the results to eliminate false positives, so you only see high-signal results likely to be true positives.  
16 |   
17 | \#\# Prerequisites  
18 |   
19 | To scan your code for generic secrets, you must have the following:  
20 |   
21 | \- Access to \[Semgrep Secrets\](/semgrep-secrets/getting-started).  
22 | \- \[Semgrep Assistant\](/semgrep-assistant/getting-started) enabled.  
23 | \- Semgrep CLI version \`1.86.0\` or higher running in your CI environment.  
24 |   
25 | Generic secrets does \*not\* work with local scans initiated by running the \`semgrep ci\` command, because Semgrep Assistant requires code access.  
26 |   
27 | \#\# Enable generic secrets  
28 |   
29 | 1\. Sign in to \[\<i class="fas fa-external-link fa-xs"\>\</i\> Semgrep AppSec Platform\](https://semgrep.dev/login).  
30 | 2\. Go to \*\*Settings \> General \> Secrets\*\*.  
31 | 3\. Click the \*\*Generic secrets\*\* \<i class="fa-solid fa-toggle-large-on"\>\</i\> toggle to turn on generic secrets.  
32 |   
33 | Once you have enabled generic secrets, your subsequent Semgrep Secrets scans automatically run with generic secrets rules. You can confirm that this is the case by looking for the following confirmation message in the CLI output:  
34 |   
35 | \`\`\`console  
36 | SECRETS RULES  
37 | \-------------  
38 | AI augmented rules are active for secrets detection.  
39 | \`\`\`  
40 |   
41 | If there are findings, Semgrep returns the following CLI message:   
42 |   
43 | \`\`\`console  
44 | Your deployment has generic secrets enabled. X potential line locations  
45 | will be uploaded to the Semgrep platform and then analyzed by Semgrep Assistant.  
46 | Any findings that appear actionable will be available in the Semgrep Platform.  
47 | You can view the secrets analyzed by Assistant at URL  
48 | \`\`\`  
49 |   
50 | \#\# View findings  
51 |   
52 | 1\. Sign in to \[\<i class="fas fa-external-link fa-xs"\>\</i\> Semgrep AppSec Platform\](https://semgrep.dev/login).  
53 | 1\. Go to \[\*\*Secrets\*\*\](https://semgrep.dev/orgs/-/secrets?validation\_state=confirmed\_valid%2Cvalidation\_error%2Cno\_validator\&tab=open\&last\_opened=All+time\&type=AI-detected+secret+(beta)) to see a list of all findings identified by Semgrep Secrets.   
54 | 1\. Filter for generic secrets findings by setting the \*\*Secret type\*\* filter to \*\*Generic Secret (AI)\*\*.  
55 |   
56 | \!\[Generic secrets findings in Semgrep AppSec Platform\](/img/generic-secrets.png\#md-width)  
57 | \_\*\*Figure\*\*. Generic secrets findings in Semgrep AppSec Platform.\_  
58 |   
59 | \#\# Disable generic secrets  
60 |   
61 | 1\. Sign in to \[\<i class="fas fa-external-link fa-xs"\>\</i\> Semgrep AppSec Platform\](https://semgrep.dev/login).  
62 | 2\. Go to \*\*Settings \> Deployment\*\* and navigate to the \*\*Secrets\*\* section.  
63 | 3\. Click the \*\*Generic secrets\*\* \<i class="fa-solid fa-toggle-large-on"\>\</i\> toggle to turn off generic secrets.  
64 |   
65 | Once disabled, all of your generic secrets findings will be removed from Semgrep AppSec Platform after the following scan.  
66 | 

\--------------------------------------------------------------------------------  
/docs/semgrep-secrets/getting-started.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | slug: /semgrep-secrets/getting-started  
  3 | append\_help\_link: true  
  4 | title: Scan for secrets  
  5 | hide\_title: true  
  6 | description: Set up secrets scanning to find and rotate valid leaked secrets.  
  7 | tags:  
  8 |   \- Semgrep Secrets  
  9 | \---  
 10 |   
 11 | import ValidationStates from '/src/components/reference/\_validation-states.mdx'  
 12 |   
 13 | \# Scan for secrets  
 14 |   
 15 | Semgrep Secrets allows you to detect and triage leaked secrets and credentials  
 16 | and save time by prioritizing which secrets to rotate based on whether they're active and in use.  
 17 |   
 18 | \!\[Semgrep Secrets page\](/img/secrets-page.png\#md-width)  
 19 |   
 20 | This document guides you through:  
 21 |   
 22 | 1\. Enabling Semgrep Secrets and scanning your repository  
 23 | 2\. Configuring your ignore files  
 24 | 3\. Upgrading your Semgrep Code rules to Semgrep Secrets rules  
 25 |   
 26 | :::info  
 27 | To access Semgrep Secrets, contact your Semgrep account executive or \[Support\](/support) for a trial license.  
 28 | :::  
 29 |   
 30 | \#\# Language and environment support  
 31 |   
 32 | Semgrep Secrets can scan repositories using \*\*any programming language\*\* and supports the posting of PR and MR comments to GitHub, GitLab, and Bitbucket.  
 33 |   
 34 | \#\# Enable Semgrep Secrets  
 35 |   
 36 | :::info Prerequisite  
 37 | You have completed a \[Semgrep core deployment\](/deployment/core-deployment).  
 38 | :::  
 39 |   
 40 | 1\. Sign in to \[\<i class="fas fa-external-link fa-xs"\>\</i\> Semgrep AppSec Platform\](https://semgrep.dev/login).  
 41 | 2\. Go to \*\*Settings \> General \> Secrets\*\*.  
 42 | 3\. Click the \*\*\<i class="fa-solid fa-toggle-large-on"\>\</i\> Secrets scans\*\* toggle to enable.  
 43 |   
 44 | Once you've enabled Secrets for your organization, all Semgrep scans include secret scanning.  
 45 |   
 46 | \#\# Scan your repository  
 47 |   
 48 | After you've enabled Semgrep Secrets, you can:  
 49 |   
 50 | \* Manually trigger a full scan of your repository through your CI provider  
 51 | \* Start a scan from the CLI (Semgrep recommends that you run CLI scans only on feature branches, not main branches)  
 52 | \* Wait for your scheduled Semgrep full scan  
 53 | \* Open a pull request or merge request and wait for Semgrep to scan the branch automatically  
 54 |   
 55 | \#\# View your findings  
 56 |   
 57 | You can use Semgrep AppSec Platform's \*\*Secrets\*\* page to view the findings generated by Semgrep Secrets after it scans your codebase. To access the \[\*\*Secrets\*\* page\](https://semgrep.dev/orgs/-/secrets):  
 58 |   
 59 | 1\. Sign in to \[Semgrep AppSec Platform\](https://semgrep.dev/login).  
 60 | 2\. Click \*\*\[Secrets\](https://semgrep.dev/orgs/-/secrets)\*\* to navigate to your results.  
 61 |   
 62 | \#\#\# Secrets page structure  
 63 |   
 64 | The \*\*Secrets\*\* page consists of:  
 65 |   
 66 | \- The \*\*filter panel\*\*, which you can use to group and filter for specific findings  
 67 | \- Information about findings identified by Semgrep Secrets. Each finding in the list includes:  
 68 |   \- When the finding was created  
 69 |   \- The type of secret found and where in the code it is located, including its Project and branch information  
 70 |   \- Its severity level  
 71 |   \- Whether it has been validated by a Semgrep validator  
 72 |   \- Whether it is a \[historical finding\](/semgrep-secrets/historical-scanning)  
 73 |   \- For users of the Semgrep Jira integration: whether there is a Jira ticket that accompanies that finding  
 74 |   \- A link to the commit where the finding was first identified  
 75 |   \- A link to the lines of code where the finding was most recently seen  
 76 |   
 77 | \#\#\# Group findings  
 78 |   
 79 | By default, Semgrep groups all of the findings by the rule Semgrep used to match the code. This view is called the \*\*Group by rule\*\* view.  
 80 |   
 81 | Semgrep sorts findings by severity. For a given severity, Semgrep further sorts findings as follows:  
 82 |   
 83 | 1\. Findings generated by custom rules  
 84 | 2\. Issue count in descending order  
 85 | 3\. Findings ID in ascending order  
 86 |   
 87 | \!\[Findings grouped by rule\](/img/secrets-findings.png\#md-width)  
 88 |   
 89 | To view findings individually, toggle \*\*Group by Rule\*\* to \*\*No grouping\*\* using the drop-down menu in the header. Findings are displayed based on the date they were found, with the most recent finding listed at the top.  
 90 |   
 91 | \!\[Group by Rule option\](/img/cloud-platform-findings-no-grouping.png\#md-width)  
 92 |   
 93 | \#\#\# Filter findings  
 94 |   
 95 | Use filters to narrow down your results. The following criteria are available for filtering:  
 96 |   
 97 |   
 98 | | Filter                    | Description  |  
 99 | | \------------------------  | \------------ |  
100 | | \*\*Projects\*\*              | Filter by repositories connected to Semgrep AppSec Platform. |  
101 | | \*\*Branches\*\*              | Filter by findings in different Git branches. |  
102 | | \*\*Teams\*\*                 | Filter for findings in projects to which the specified teams are associated with. Available only to organizations with \[Semgrep Teams\](/deployment/teams\#teams-beta) enabled. |  
103 | | \*\*Tags\*\*                  | Filter for findings based on the tags associated with the project. |  
104 | | \*\*Status\*\*                | Filter the triage state of a finding. Refer to \[Triage statuses\](\#triage-status) to understand triage states. |  
105 | | \*\*Severity\*\*              | Filter by the \[severity\](\#severity) of a finding. Severity is computed based on the values assigned for \[Likelihood\](/contributing/contributing-to-semgrep-rules-repository/\#likelihood) and \[Impact\](/contributing/contributing-to-semgrep-rules-repository/\#impact) by the rule's author. Possible values: \<ul\>\<li\>Low\</li\>\<li\>Medium\</li\>\<li\>High\</li\>\<li\>Critical\</li\>\</ul\> |  
106 | | \*\*Validation state\*\*      | Filter by \[whether the secret is actively in use or not\](\#validation). Semgrep Secrets rules include validators, which can check whether the secret is valid for the service with which it is associated. |  
107 | | \*\*Repository visibility\*\* | Filter by whether the repository's \[visibility\](\#repository-visibility) status. |  
108 | | \*\*Secret type\*\*           | Filter by the type of secret, such as \*\*private key\*\*, or the web service that makes use of the secret, such as \*\*Sendgrid\*\* or \*\*Stripe\*\*. |  
109 | | \*\*Component\*\*          | Filter by \[Semgrep Assistant component tags\](/semgrep-assistant/overview\#component-tags). Semgrep Assistant uses AI to categorize the file where the finding was identified based on its function, such as payments, user authentication, and infrastructure. |  
110 | | \*\*Historical findings\*\*   | Filter for findings that are valid, leaked secrets in previous Git commits. |  
111 |   
112 | \#\#\#\# Triage status  
113 |   
114 | | Status | Description |  
115 | | \-----------  | \------------ |  
116 | | \*\*Open\*\* | Findings are open by default. A finding is open if it was present the last time Semgrep scanned the code and it has not been ignored. An open finding represents a match between the code and a rule that is enabled in the repository. Open findings require action, such as rewriting the code to eliminate the detected vulnerability. |  
117 | | \*\*Ignored\*\* | Findings that are ignored are present in the code, but have been labeled as unimportant. Ignore findings that are false positives or deprioritized issues. You can filter findings with a status of \*\*Ignored\*\* further by reason:  \*\*False positive\*\*, \*\*Acceptable risk\*\*, \*\*No time to fix\*\*, or \*\*No triage reason\*\*. |  
118 | | \*\*Fixed\*\* | Fixed findings were detected in a previous scan, but are no longer detected in the most recent scan of that same branch due to changes in the code. |  
119 |   
120 | \#\#\#\# Severity  
121 |   
122 | Severity is assigned based on how sensitive or crucial the exposed web service is. Possible values include:  
123 |   
124 | \* Critical  
125 | \* High  
126 | \* Medium  
127 | \* Low  
128 |   
129 | \#\#\#\# Validation  
130 |   
131 | Refers to whether or not a secret is active and can be used to grant resources or authentication, or if a secret is inactive.  
132 |   
133 | \<ValidationStates /\>  
134 |   
135 | \#\#\#\# Repository visibility  
136 |   
137 | Refers to whether or not the repository is a public repository or private. This is detected through your source code manager.  
138 |   
139 | | Repository visibility | Description |  
140 | | \-----------  | \------------ |  
141 | | Public | Repository access doesn't require authentication; at a minimum, it can be viewed by anyone. |  
142 | | Private | Repository access requires authentication. |  
143 | | Unknown | Semgrep Secrets is unable to detect your repository visibility. This is typically assigned to: \<ul\>\<li\>Scans from local developer machines.\</li\>\<li\>Scans from any non-GitHub source code manager, such as GitLab.\</li\>\</ul\> |  
144 |   
145 | :::info  
146 | Semgrep supports visibility detection only for GitHub repositories of any plan.  
147 | :::  
148 |   
149 | \#\# Configure files to ignore  
150 |   
151 | Semgrep Secrets scans all files, even those specified in a local \`.semgrepignore\` file, since secrets can often be found in files that aren't relevant for code scanning. To specify files that Semgrep Secrets should ignore:  
152 |   
153 | 1\. Sign in to \[Semgrep AppSec Platform\](https://semgrep.dev/login?return\_path=/manage/projects).  
154 | 2\. From the Dashboard Sidebar, select \*\*\[Projects\](https://semgrep.dev/orgs/-/projects)\*\* \> \*\*\[Project name\]\*\*.  
155 | 3\. Click \*\*Secrets\*\* to expand and display the \*\*Path Ignores\*\* box.  
156 | 4\. Enter files and folders to ignore in the \*\*Path Ignores\*\* box.  
157 | 5\. Click \*\*Save changes\*\*.  
158 |   
159 | \#\# Upgrade your rules  
160 |   
161 | If you're using Semgrep Code rules to identify leaked credentials, you'll see prompts in Semgrep AppSec Platform indicating that there's an improved version that uses Semgrep Secrets' feature set, primarily its validators, which can validate whether the detected credential is active, and improvements in detecting and hiding false positives.  
162 |   
163 | You can see individual findings for which there is a Semgrep Secrets rule upgrade in Semgrep AppSec Platform's \*\*Findings\*\* page. The findings are tagged with a label that says \`Secrets version available\! Click to see rule(s)\`.  
164 |   
165 | \!\[Finding tagged as having a Secrets rule available\](/img/superseded-rules-finding.png\#md-width)  
166 |   
167 | To see the rules you're using for which there is a Secrets rule upgrade in Semgrep AppSec Platform:  
168 |   
169 | 1\. Sign in to Semgrep AppSec Platform.  
170 | 2\. Go to \*\*Rules\*\* \> \*\*Policies\*\* \> \*\*Code\*\*.  
171 | 3\. Under \*\*Available rule upgrades\*\*, ensure that you've selected \*\*Secrets\*\*.  
172 |   
173 | \!\[Filter to find rules for which there is a rule upgrade\](/img/superseded-rules-policies.png\#md-width)  
174 | \#\# Next steps  
175 |   
176 | \* Learn how to \[view and triage secrets in Semgrep AppSec Platform\](/semgrep-secrets/view-triage)  
177 |   
178 | \#\#\# Additional information  
179 |   
180 | \* Learn more about the \[structure of rules for Semgrep Secrets\](/semgrep-secrets/rules), as well as how to \[manage your rules using Semgrep AppSec Platform\](/semgrep-secrets/policies).  
181 | \* Learn how to \[write custom validators\](/semgrep-secrets/validators) for your Semgrep Secrets rules.  
182 | 

\--------------------------------------------------------------------------------  
/docs/semgrep-secrets/glossary.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: glossary  
 3 | description: Definitions of Semgrep Secrets product-specific terms.  
 4 | tags:  
 5 |     \- Semgrep Secrets  
 6 | title: Glossary  
 7 | hide\_title: true  
 8 | \---  
 9 |   
10 | import ValidationStates from '/src/components/reference/\_validation-states.mdx'  
11 |   
12 | import ScanTarget from '/src/components/reference/\_scan-target.mdx'  
13 | import PolicyDefinition from '/src/components/reference/\_policy-definition.mdx'  
14 |   
15 | \# Semgrep Secrets glossary  
16 |   
17 | The terms and definitions provided here are specific to Semgrep Secrets.  
18 |   
19 | \#\# Entropy analysis  
20 |   
21 | Entropy, the measure of a string's randomness, measures how likely it is that a given string is random. If a string is highly entropic, it's highly random. Entropy analysis, therefore, can provide insight into whether a given string is a secret, reducing false positives.  
22 |   
23 | \#\# Expiration  
24 |   
25 | Some secrets are time-limited. This means that the secret is only valid for the period set by the creator. Expired secrets can pose fewer problems, so findings involving expired secrets can be deprioritized.   
26 |   
27 | \#\# Historical scan  
28 |   
29 | A scan of your Git commit history to see if there are valid secrets publicly available in your repository's Git history.  
30 |   
31 | \#\# Policy  
32 |   
33 | \<PolicyDefinition /\>  
34 |   
35 | \#\# Registry (Semgrep Registry)  
36 |   
37 | A \[\<i class="fas fa-external-link fa-xs"\>\</i\> collection of rules\](https://semgrep.dev/r) that you can download. Semgrep offers a   
38 | \<i class="fas fa-external-link fa-xs"\>\</i\> \[Secrets-specific ruleset\](https://semgrep.dev/p/secrets).  
39 |   
40 | \#\#\# Sources of rules  
41 |   
42 | The Semgrep Registry contains rules imported from various repositories, including non-Semgrep individuals or groups, such as Trail of Bits and GitLab. You can view a rule's \`license\` key to ensure the license meets your needs.  
43 |   
44 | \#\# Revocation  
45 |   
46 | Revoking a secret makes it inactive. This is done when a secret isn't required anymore or if a secret becomes compromised.  
47 |   
48 | \#\# Rotation  
49 |   
50 | Rotating secrets is the process of updating a secret regularly. If a secret is leaked, regular rotation can ensure that the credential is valid only for a limited time. Rotating secrets can also minimize risk due to the reuse of secrets.  
51 |   
52 | \#\# Ruleset  
53 |   
54 | Rulesets are rules related through a programming language, OWASP category, or framework. Rulesets are curated by the team at Semgrep and updated as new rules are added to the Semgrep Registry.  
55 |   
56 | \<ScanTarget /\>  
57 |   
58 | \#\# Secret  
59 |   
60 | Secrets are pieces of sensitive information crucial for securing applications and their data. This information can include API keys, access credentials, SSH keys, certificates, and more. If secrets are stored in source code, they can be "leaked," allowing internal and external malicious actors to use this information for unauthorized access.  
61 |   
62 | \#\# Semantic analysis  
63 |   
64 | Semantic analysis refers to Semgrep Secrets' ability to understand how data is used in your code. Semgrep Secrets uses several mechanisms to perform semantic analysis, including \[\<i class="fa-regular fa-file-lines"\>\</i\> data-flow analysis\](/writing-rules/data-flow/data-flow-overview) and \[\<i class="fa-regular fa-file-lines"\>\</i\> constant propagation\](/writing-rules/data-flow/constant-propagation), allowing Secrets to track data, such as variables, and the flow of that data across files and functions in your codebase.  
65 |   
66 | \#\# Validation state  
67 |   
68 | The validation state of a secret provides information on whether a secret, if leaked, poses an immediate security threat. Current Semgrep validation states for a secret include:  
69 |   
70 | \<ValidationStates /\>  
71 |   
72 | \#\# Validator  
73 |   
74 | Semgrep Secrets rules include validators, which help determine if a secret is actively used. Validators define behavior, such as API calls, that determine whether an identified secret is valid and whether it can be successfully used to access a resource.  
75 |   
76 | \#\# Vault  
77 |   
78 | A secure, centralized storage solution for your sensitive data, including access tokens, API keys, certificates, passwords, and more. A secrets vault can make it easier to store your data securely and allows you to control who accesses the data. The vault may also offer features like auditing, such as who accesses what secret and when or when a secret expires, and rotation of secrets.

\--------------------------------------------------------------------------------  
/docs/semgrep-secrets/historical-scanning.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | slug: historical-scanning  
  3 | append\_help\_link: true  
  4 | title: Scan your Git history (beta)  
  5 | hide\_title: true  
  6 | description: Detect valid, leaked secrets in previous Git commits through a historical scan.  
  7 | tags:  
  8 |   \- Semgrep Secrets  
  9 |   \- Semgrep AppSec Platform  
 10 | \---  
 11 |   
 12 | \# Scan your Git history (beta)  
 13 |   
 14 | Detect valid, leaked secrets in previous Git commits through a \*\*historical scan\*\*.  
 15 |   
 16 | You can perform one-time historical scans or enable historical scans for full Secrets scans. Detecting valid secrets in your Git history is a step towards reducing your repository's attack surface.  
 17 |   
 18 | You can run historical scans in the CLI and in your Semgrep deployment, which enables you to track and triage these secrets.  
 19 |   
 20 | \#\# Feature maturity  
 21 |   
 22 | \- This feature is in \*\*public beta\*\*. See \[Limitations\](\#limitations) for more information.  
 23 | \- All Semgrep Secrets customers can enable this feature.  
 24 | \- Currently, only rules that perform HTTP validation are incorporated during historical scanning. Findings that have been verified as valid are surfaced.  
 25 | \- Please leave feedback either by reaching out to your technical account manager (TAM) or through the \*\*\<i class="fa-solid fa-bullhorn"\>\</i\> Feedback\*\* form in Semgrep AppSec Platform's navigation bar.  
 26 |   
 27 |   
 28 | \#\# Run historical scans  
 29 |   
 30 | You can enable historical scans for your full scans, perform one-time historical scans on the CLI, or create an on-demand CI job. Historical scans display \*\*valid, leaked secrets\*\* to ensure a high true positive rate. Diff-aware scans do \*\*not\*\* perform historical scans.  
 31 |   
 32 | \#\#\# Prerequisites  
 33 |   
 34 | \- \*\*CLI tool\*\*: Historical scanning requires at least Semgrep \*\*v1.65.0\*\*. See \[Update\](/update/) for instructions.  
 35 |   
 36 | \#\#\# Enable historical scans for full Secrets scans  
 37 |   
 38 | :::tip  
 39 | If possible, \[test historical scans locally\](\#run-a-local-test-scan) to create a benchmark of performance and scan times before adding historical scans to your formal security process.  
 40 | :::  
 41 |   
 42 | 1\. Sign in to Semgrep AppSec Platform.  
 43 | 1\. Go to \*\*Settings \> General \> Secrets\*\*.  
 44 | 2\. Click the \*\*\<i class="fa-solid fa-toggle-large-on"\>\</i\> Historical scanning\*\* toggle.  
 45 | \!\[Historical scanning settings toggle\](/img/historical-scanning-settings.png\#md-width)  
 46 |   
 47 | Subsequent Semgrep full scans now include historical scanning.  
 48 |   
 49 | \#\#\# Run a one-off historical scan  
 50 |   
 51 | To run a one-off or on-demand historical scan, you can create a specific CI job and then manually start the job as needed.  
 52 |   
 53 | The general steps are:  
 54 |   
 55 | 1\. Copy your current full scan CI job configuration file, or use \[a template\](/semgrep-ci/sample-ci-configs/).  
 56 | 1\. Look for the \`semgrep ci\` command.  
 57 | 1\. Append the \`--historical-secrets\` flag:  
 58 |     \`\`\`  
 59 |     semgrep ci \--historical-secrets  
 60 |     \`\`\`  
 61 | 1\. Depending on your CI provider, you may have to perform additional steps to enable the job to run manually. For example, GitHub Actions requires the \`workflow\_dispatch\` event to be added to your CI job.  
 62 |   
 63 | \#\#\# Run a local test scan  
 64 |   
 65 | You can run a historical scan locally without sending the scan results to Semgrep AppSec Platform. This can help you determine the time it takes for Semgrep Secrets to run on your repository's Git commit history.  
 66 |   
 67 | To run a test scan, enter the following command:  
 68 |   
 69 | \`\`\`bash  
 70 | semgrep ci \--secrets \--historical-secrets \--dry-run  
 71 | \`\`\`  
 72 |   
 73 | The historical scan results appear in the \*\*Secrets Historical Scan\*\* section:  
 74 |   
 75 | \!\[Historical scan section in the CLI\](/img/historical-scans-cli.png\#md-width)  
 76 |   
 77 | \#\# View or hide historical findings  
 78 |   
 79 | 1\. Sign in to \[\<i class="fas fa-external-link fa-xs"\>\</i\> Semgrep AppSec Platform\](https://semgrep.dev/login).  
 80 | 2\. Click \*\*\<i class="fa-solid fa-key"\>\</i\> Secrets\*\*. Historical findings are labeled as shown in the following screenshot:  
 81 |    \!\[Secrets finding labeled as historical finding\](/img/historical-findings.png\#md-width)  
 82 | 3\. On the filter panel, select \*\*Include historical findings\*\* to toggle on the display of historical findings.  
 83 |   
 84 | \#\# Scope of findings  
 85 |   
 86 | \- Historical scans display \*\*valid\*\* Secrets findings. These secrets have been \[validated through authentication or a similar function\](/semgrep-secrets/conceptual-overview/\#validate-secrets).  
 87 | \- Historical scans do \*\*not\*\* display the following finding types:  
 88 |     \- Invalid Secrets findings  
 89 |     \- Secrets findings without validator functions  
 90 |     \- Secrets findings with validation errors  
 91 | \- Findings from historical scans are generated through \*\*Generic\*\* (regex-based) rules only.  
 92 |     \- Navigate to \*\*\[\<i class="fas fa-external-link fa-xs"\>\</i\> Semgrep AppSec Platform \> Policies \> Secrets\](https://semgrep.dev/orgs/-/policies/secrets?analysis-method=generic)\*\* and click \*\*Generic\*\* under \*\*Analysis method\*\* to view these rules.  
 93 |   
 94 | For more information on the types of findings by validation, see \[Semgrep Secrets overview\](/semgrep-secrets/conceptual-overview/\#validate-secrets).  
 95 |   
 96 | \#\# Triage process  
 97 |   
 98 | Historical scan findings are not automatically marked as \*\*Fixed\*\*. To triage a historical finding, you must:  
 99 |   
100 | 1\. Manually rotate the secret.  
101 | 1\. In Semgrep AppSec Platform, click \*\*Secrets\*\*.  
102 | 1\. Toggle the \*\*Hide historical\*\* button if it is enabled. This displays all historical findings.  
103 | 1\. Select all the checkboxes for secrets you want to triage, then click \*\*Triage \> Ignore\*\*, optionally including a comment in the provided text box.  
104 |   
105 | \#\# Limitations  
106 |   
107 | \- Historical scanning can slow down scan times. Depending on the size of your repository history, scans can finish in less than 5 minutes or may take more than 60 minutes.  
108 | \- Within Semgrep AppSec Platform, historical scan findings are not automatically marked as \*\*Fixed\*\*. Findings can only exist in two states: \`Open\` or \`Ignored\`. Because Semgrep scans do not automatically detect historical findings as fixed, you must manually rotate and triage the secret as \`Ignored\`.  
109 | \- With historical scans enabled, the CLI output displays secrets still present in the current version of the code twice: once at the commit where they were initially added and once at the current commit from the standard Secrets scan. Semgrep AppSec Platform deduplicates the two findings and displays the secret as a current rather than a historical one.  
110 |   
111 | \#\#\# Size of commit history  
112 |   
113 | \- Semgrep Secrets scans up to \*\*5 GiB\*\* of uncompressed blobs. This ranges from around \*\*10,000 to 50,000\*\* previous commits depending on the average size of the commit.  
114 | \- For repositories with more than 5 GiB of history, Semgrep Secrets is still able to complete the scan, but the scan scope will not cover the older commits beyond 5 GiB.  
115 | \- The size of the commit history affects the speed of the scan. Larger repositories take longer to complete.  
116 | \- Semgrep Secrets scans the whole commit history every time a full scan is run. This guarantees that your Git history is also scanned using the \*\*latest Secrets rules\*\*.  
117 | 

\--------------------------------------------------------------------------------  
/docs/semgrep-secrets/policies.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | slug: policies  
  3 | append\_help\_link: true  
  4 | title: Manage rules and policies  
  5 | hide\_title: true  
  6 | description: The Policies page is a visual representation of the rules that Semgrep Secrets uses to scan code.  
  7 | tags:  
  8 |   \- Semgrep Secrets  
  9 |   \- Semgrep AppSec Platform  
 10 | \---  
 11 |   
 12 | import RuleModes from "/src/components/reference/\_rule-modes.md"  
 13 |   
 14 | \# Manage Semgrep Secrets rules using the policies page  
 15 |   
 16 | \!\[Overview of Semgrep Secrets policies view\](/img/secrets-policies-page.png)  
 17 |   
 18 | To access the policies page for Semgrep Secrets:  
 19 |   
 20 | 1\. Log in to Semgrep AppSec Platform and navigate to \*\*Rules\*\* \> \*\*Policies\*\*.  
 21 | 2\. Click \*\*Secrets\*\*.  
 22 |   
 23 |   
 24 | \#\# Global rule behavior   
 25 | The \*\*Global rule behavior\*\* page visually represents the rules Semgrep Secrets uses for scanning.  
 26 |   
 27 | \!\[Overview of Semgrep Secrets policies view\](/img/secrets-rules-management.png)  
 28 |   
 29 | \#\#\# Page Structure   
 30 | The page consists of the following elements:  
 31 |   
 32 | \<dl\>  
 33 |     \<dt\>Filter pane\</dt\>  
 34 |         \<dd\>  
 35 |            Displays filters to select and perform operations on rules in bulk quickly. See \<a href="\#filters"\>Filters\</a\> for more information.  
 36 |         \</dd\>  
 37 |     \<dt\>Rules pane\</dt\>  
 38 |         \<dd\>  
 39 |             The rules pane displays the rules that Semgrep scans use to detect leaked secrets  
 40 |              and allows you to edit their assigned rule modes. You can make these edits either one by one or through the bulk editing of many rules. You can also use the \<strong\>Search for rule names or ids\</strong\> box. See \<a href="\#filters"\>Filters\</a\> for more information.  
 41 |         \</dd\>  
 42 | \</dl\>  
 43 |   
 44 | \#\#\# Filters  
 45 |   
 46 | The filter pane displays filters to select and perform operations on rules in bulk. The following filters are available:  
 47 |   
 48 | | Filter | Description |  
 49 | | \- | \- |  
 50 | | Modes | Filter by the workflow action Semgrep performs when a rule detects a finding. An additional filter, \*\*Disabled\*\*, is provided for rules you have turned off and are no longer included for scanning. |  
 51 | | Validation | Filter by whether the rule includes a validator or not. |  
 52 | | Type | Filter by the type of secret the rule addresses. Examples: AWS, Adobe, DigitalOcean, GitHub, GitLab. |  
 53 | | Source | Filter by Pro rules (authored by Semgrep) or by Custom rules (rules created by your organization) |  
 54 | | Severities | Filter by the severity level of the secret: \<ul\>\<li\>\*\*Low\*\*: low privilege; for example, write-only access like a webhook\</li\>\<li\>\*\*Medium\*\*: may have read and write access depending on what scope the account has\</li\>\<li\>\*\*High\*\* and \*\*Critical\*\*: has access to critical resources or full account access\</li\>\</ul\> |  
 55 | | Analysis method | Filter based on whether Semgrep used \*\*Semantic\*\* or \*\*Generic\*\* analysis |  
 56 |   
 57 | \#\#\# Rule entry reference  
 58 |   
 59 | This section defines the columns of the rule entries in the Policies page:  
 60 |   
 61 | | Filter | Description |  
 62 | | \-------  | \------ |  
 63 | | Rule name  | Name of the rule Semgrep Secret uses for scanning. |  
 64 | | Labels  | Metadata describing the rule, including the service for which the rule is applicable. |  
 65 | | Open findings  | The number of open findings the rule detected across all scans.  |  
 66 | | Fix rate  | The percentage of findings that are fixed through changes to the code.  |  
 67 | | Severity  | The higher the severity, the more critical the issues that a rule detects.      |  
 68 | | Confidence  | Indicates confidence of the rule to detect true positives.      |  
 69 | | Source  | Indicates the origin of a rule. \<ul\>\<li\>\*\*Pro:\*\* Authored by Semgrep.\</li\>\<li\>\*\*Custom:\*\* Rules created within your Semgrep organization.\</li\>\</ul\> |  
 70 | | Ruleset  | The name of the ruleset the rule belongs to. |  
 71 | | Mode  | Specifies what workflow action Semgrep performs when a rule detects a finding. An additional filter, \*\*Disabled\*\*, is provided for rules you have turned off and are no longer included for scanning. | See \[Rule modes\](\#rule-modes) documentation. |  
 72 |   
 73 | \#\#\# Rule modes  
 74 |   
 75 | Semgrep Secrets provides three rule modes. These can be used to trigger \*\*workflow options\*\* whenever Semgrep Secrets identifies a finding based on the rule:  
 76 |   
 77 | \<RuleModes /\>  
 78 |   
 79 | If you're encountering issues getting PR comments for Semgrep Secrets:  
 80 |   
 81 | \* Make sure the rule is in \*\*Comment\*\* or \*\*Block\*\* mode  
 82 | \* Review the \[PR or MR comments guide for your SCM\](/docs/category/pr-or-mr-comments)  
 83 | \* Explore \[other reasons you may not see PR or MR comments\](/docs/kb/semgrep-appsec-platform/missing-pr-comments)  
 84 |   
 85 | \#\#\# Block a PR or MR through rule modes  
 86 |   
 87 | Semgrep enables you to set a \*\*workflow action\*\* based on the presence of a finding. Workflow actions include:  
 88 |   
 89 | \* Failing a CI job. Semgrep returns exit code \`1\`, and you can use this result to set up additional checks to enforce a block on a PR or MR.  
 90 | \* Leaving a \[PR or MR comment\](/category/pr-or-mr-comments).  
 91 | \* \[Notifying select channels\](/semgrep-appsec-platform/notifications), such as private Slack channels or webhooks.  
 92 |   
 93 | You can trigger these actions based on the \[rule mode\](\#rule-modes) set for the rule.  
 94 |   
 95 | \#\#\# Add custom rules  
 96 |   
 97 | To add custom rules, use the Semgrep Editor. See \[\<i class="fa-regular fa-file-lines"\>\</i\>Semgrep Secrets rule structure and sample\](/semgrep-secrets/rules).  
 98 |   
 99 | \#\#\# Disable rules  
100 |   
101 | To disable rules:  
102 |   
103 | 1\. On the \*\*Policies\*\* page, select either:  
104 |     \- The top \*\*\<span className="placeholder"\>Number\</span\> Matching Rules\*\* checkbox to select all rules.  
105 |     \- Select individual checkboxes next to a rule to disable rules one by one.  
106 | 2\. Click \*\*Change modes(\<span className="placeholder"\>Number\</span\>)\*\*, and then click \*\*Disabled\*\*.  
107 |   
108 | You can also select individual rules under the \*\*Mode\*\* column and disable them individually.  
109 |   
110 | \#\# Invalid findings   
111 | You can define how Semgrep handles findings that it categorizes as invalid. Invalid findings include secrets that, during validation, were identified as revoked or were never functional.  
112 |   
113 | \#\# Validation errors  
114 | You can define how Semgrep handles findings that result in a validation error. Validation errors occur when there are difficulties reaching the secrets provider or when Semgrep receives an unexpected response from the API.  
115 |   
116 | \#\# Manage Policies  
117 |   
118 | Once you are ready to notify developers of Secrets findings on Slack, define a \*\*Secrets policy\*\*. This feature helps you manage noise and ensures that developers are only notified based on the conditions you set.   
119 |   
120 | This feature enables you to configure the following:  
121 |   
122 | \- \*\*Scope\*\*: These are the projects (repositories) that are affected by the policy.  
123 | \- \*\*Conditions\*\*: The conditions under which \*\*actions\*\* are performed. These conditions are typically attributes of a finding, such as severity or validation.   
124 | \- \*\*Actions\*\*: Actions that are performed on the defined scope when conditions are met.  
125 |   
126 | You can create as many policies as you need.  
127 |   
128 | \#\#\# Prerequisites  
129 |   
130 | This feature requires the \`semgrep:latest\` Docker image or at least version 1.101.0 of the Semgrep CLI.  
131 |   
132 | \#\#\# View your policies   
133 |   
134 | Only \*\*admins\*\* can view, create, edit, or delete policies. Your policies are arranged as cards.   
135 |   
136 | \!\[Policies \> Semgrep Secrets\](/img/secrets-policies-card.png)  
137 | \_\*\*Figure\*\*. A single card within the Semgrep Secrets Policies page.\_  
138 |   
139 | \- To view and edit an existing policy, click its \*\*name\*\* or \*\*the three-dot ellipsis (\<i class="fas fa-ellipsis-h"\>\</i\>) \> Edit policy\*\*.  
140 | \- View a popup of a policy's \*\*scope\*\* (affected projects or tags) or a summary of its \*\*actions and conditions\*\* by clicking on the two summary links beside the policy name.  
141 |   
142 | \#\#\# Create a policy  
143 |   
144 | 1\. From the Slack Notification Policies section, Click \*\*\<i class="fa-solid fa-plus"\>\</i\> Create policy\*\*.  
145 | 1\. Provide a \*\*Policy name\*\*.  
146 | 1\. Define the scope of the policy:  
147 |     1\. Click the drop-down box to select between \*\*All Projects\*\*, \*\*Project\*\*, or \*\*Project tag\*\*. Note that you can only select either a scope based on projects or tags, but not both.  
148 |     1\. For \*\*Project\*\* or \*\*Project tag\*\* values, a second drop-down box appears. Choose the \*\*projects\*\* or \*\*project tags\*\* to finish defining the scope.  
149 | 1\. Define the conditions of the policy. See the \[Policy conditions\](\#policy-conditions) section for more information. You can create more than one condition by clicking \*\*Add condition\*\*.  
150 |     \- For each condition, you can select multiple values by clicking on the \*\*plus sign (\<i class="fa-solid fa-plus"\>\</i\>)\*\* on the same row. The policy is applied when \*\*any\*\* of those values are met (\`OR\`).  
151 |     \- Each additional condition is additive. The policy is applied when \*\*all\*\* conditions are met (\`AND\`).  
152 |   
153 |       \!\[Policies \> Semgrep Secrets\](/img/secrets-policies-many-conditions.png)  
154 | 1\. Define the actions of the policy. You can choose to \*\*Post in Slack channel(s)\*\*. Select which channels should receive notifications when this policy is triggered.    
155 |     \- This list is populated by the channels you have subscribed to. To change this list, follow the steps listed in \[Receive Slack notifications\](/semgrep-appsec-platform/slack-notifications\#secrets)   
156 |   
157 |     \!\[Policies \> Semgrep Secrets\](/img/secrets-policies-choose-channel.png)  
158 |       
159 | 1\. Click \*\*Save\*\*. This brings you back to the Secrets policies tab.  
160 | 1\. After creating a policy, it is \*\*not\*\* automatically enabled. Click the \*\*\<i class="fa-solid fa-toggle-large-on"\>\</i\> toggle\*\* to enable a policy. This applies the policy to future scans.  
161 |   
162 |   
163 | \#\#\# Policy scopes  
164 |   
165 | A policy's scope can consist of tags or projects, but not both. If you need to create a policy with both tags and projects, simply make another policy.  
166 |   
167 | If a project or project tag that's included in a policy scope gets deleted, it is \*\*removed from the policy scope\*\*. If all projects or all project tags are deleted for a given policy, you must edit the policy for it to be applied to a valid scope.  
168 |   
169 |   
170 | \#\#\# Policy conditions  
171 |   
172 | The following table lists available conditions and their values:  
173 |   
174 | | Condition | Values|  
175 | | \-------  | \------ |  
176 | | Severity      | \<ul\>\<li\>Critical\</li\>\<li\>High\</li\>\<li\>Medium\</li\> \<li\>Low\</li\> \</ul\>       |  
177 | | \[Validation\](/semgrep-secrets/glossary\#validation-state)         | \<ul\>\<li\>Confirmed valid\</li\>\<li\>Confirmed invalid\</li\>\<li\>Validation error\</li\>\<li\>No validator\</li\>  \</ul\>      |  
178 | | Repository Visibility         | \<ul\> \<li\>Public\</li\> \<li\>Private\</li\> \<li\>Unknown\</li\> \</ul\> Note: Repository Visibility is only available for GitHub repositories. |  
179 | | Secret type | Manually provide a Secret type or choose from a list of values. The values listed are generated from findings identified by Semgrep Secrets. |  
180 |   
181 | \#\#\# Other operations  
182 |   
183 | \#\#\#\# Edit a policy  
184 |   
185 | 1\. From the Secrets policies tab, click the \*\*three-dot (...) button \> Edit policy\*\* for the policy you want to edit. This takes you to the specific policy page.  
186 | 1\. Make your changes.  
187 | 1\. Click \*\*Save\*\*.  
188 |   
189 | \#\#\#\# Disable or enable a policy  
190 |   
191 | From the Secrets policies tab, click the toggle for the policy you want to edit.  
192 |   
193 | You can also disable or enable a policy from the policy's page:  
194 |   
195 | 1\. From the Secrets policies tab, click the \*\*three-dot (...) button \> Edit policy\*\*.  
196 | 1\. Turn off or on the \*\*Enable policy\*\* toggle.  
197 | 1\. Click \*\*Save\*\*.  
198 |   
199 | \#\#\#\# Delete a policy  
200 |   
201 | From the Secrets policies tab, click the \*\*three-dot (...) button \> Delete policy\*\*, then click \*\*Remove\*\*.  
202 |   
203 | Note that this does not remove existing notifications.   
204 | 

\--------------------------------------------------------------------------------  
/docs/semgrep-secrets/rules.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | slug: rules  
  3 | append\_help\_link: true  
  4 | title: Custom rules  
  5 | hide\_title: true  
  6 | description: Learn about Semgrep Secrets rules.  
  7 | tags:  
  8 |   \- Semgrep Secrets  
  9 |   \- Rule writing  
 10 | \---  
 11 |   
 12 | \# Semgrep Secrets rule structure and sample  
 13 |   
 14 | This article walks you through writing, publishing, and using Semgrep Secrets rules. It also demonstrates what a sample Semgrep Secrets rule looks like, with subsequent sections describing the key-value pairs in the context of a Semgrep Secrets rule.  
 15 |   
 16 | \#\# Write a rule  
 17 |   
 18 | There are two ways to write a rule for Semgrep Secrets:  
 19 |   
 20 | 1\. Create a YAML file.  
 21 | 2\. Use the Semgrep editor.  
 22 |   
 23 | \#\#\# Create a YAML file  
 24 |   
 25 | If you're familiar with Semgrep's rules syntax, including the \[validator syntax\](/semgrep-secrets/validators), you can create a YAML file containing your rules. When you're done, \[publish your rules for use with your organization\](/writing-rules/private-rules/).  
 26 |   
 27 | If you want to keep your rules file local, you must pass in the \`--allow-untrusted-validators\` flag when calling \`semgrep ci\` from the CLI.  
 28 |   
 29 | \#\#\# Use Semgrep Editor  
 30 |   
 31 | The Semgrep Editor, available in Semgrep AppSec Platform, can help you write custom Semgrep Secrets rules. To pull up a sample rule that you can modify:  
 32 |   
 33 | 1\. Sign in to Semgrep AppSec Platform.  
 34 | 2\. Go to \*\*Rules \> Editor\*\*.  
 35 | 3\. Click the \*\*+\*\* icon and, under \*\*Secrets\*\*, select \*\*HTTP validators\*\*.  
 36 |   
 37 | Semgrep Editor allows you to modify the sample rule and run it against test code to ensure it functions as expected. When you finish making changes, click \*\*Save\*\* to proceed.  
 38 |   
 39 | :::info  
 40 | Custom validator rules are private to your organization. They are not available to the Semgrep Community.  
 41 | :::  
 42 |   
 43 | To run a specific rule when invoking Semgrep from the CLI:  
 44 |   
 45 | 1\. Sign in to Semgrep AppSec Platform.  
 46 | 2\. Go to \*\*Rules \> Editor\*\*.  
 47 | 3\. Open up your rule.  
 48 | 4\. Click \*\*Add to Policy\*\* and select your mode: Monitor, Comment, or Blocking.  
 49 | 5\. In the CLI, start a scan by running \`semgrep ci\`.  
 50 |   
 51 | \#\# Sample rule  
 52 |   
 53 | The following sample rule detects a leaked GitHub personal access token (PAT):  
 54 |   
 55 | \`\`\`yaml  
 56 | rules:  
 57 | \- id: github\_example  
 58 |   message: \>-  
 59 |     This is an example rule, that performs validation against github.com  
 60 |   severity: WARNING  
 61 |   languages:  
 62 |   \- regex  
 63 |   validators:  
 64 |   \- http:  
 65 |       request:  
 66 |         headers:  
 67 |           Authorization: Bearer $REGEX  
 68 |           Host: api.github.com  
 69 |           User-Agent: Semgrep  
 70 |         method: GET  
 71 |         url: https://api.github.com/user  
 72 |       response:  
 73 |       \- match:  
 74 |         \- status-code: 200  
 75 |         result:  
 76 |           validity: valid  
 77 |       \- match:  
 78 |         \- status-code: 401  
 79 |         result:  
 80 |           validity: invalid  
 81 |   patterns:  
 82 |   \- patterns:  
 83 |     \- pattern-regex: (?\<REGEX\>\\b((ghp|gho|ghu|ghs|ghr|github\_pat)\_\[a-zA-Z0-9\_\]{36,255})\\b)  
 84 |     \- focus-metavariable: $REGEX  
 85 |     \- metavariable-analysis:  
 86 |         analyzer: entropy  
 87 |         metavariable: $REGEX  
 88 | \`\`\`  
 89 |   
 90 | This can also be done in any Semgrep-supported language:  
 91 |   
 92 | \`\`\`yaml  
 93 | rules:  
 94 | \- id: github\_example  
 95 |   message: \>-  
 96 |     This is an example rule that performs validation against github.com  
 97 |   severity: WARNING  
 98 |   languages:  
 99 |   \- javascript  
100 |   \- typescript  
101 |   validators:  
102 |   \- http:  
103 |       request:  
104 |         headers:  
105 |           Authorization: Bearer $REGEX  
106 |           Host: api.github.com  
107 |           User-Agent: Semgrep  
108 |         method: GET  
109 |         url: https://api.github.com/user  
110 |       response:  
111 |       \- match:  
112 |         \- status-code: 200  
113 |         result:  
114 |           validity: valid  
115 |       \- match:  
116 |         \- status-code: 401  
117 |         result:  
118 |           validity: invalid  
119 |   patterns:  
120 |   \- patterns:  
121 |     \- pattern: |  
122 |         "$R"  
123 |     \- metavariable-pattern:  
124 |         metavariable: $R  
125 |         patterns:  
126 |           \- pattern-regex: (?\<REGEX\>\\b((ghp|gho|ghu|ghs|ghr|github\_pat)\_\[a-zA-Z0-9\_\]{36,255})\\b)  
127 |     \- focus-metavariable: $REGEX  
128 |     \- metavariable-analysis:  
129 |         analyzer: entropy  
130 |         metavariable: $REGEX  
131 | \`\`\`  
132 |   
133 | \#\#\# Subkeys under the \`metadata\` key  
134 |   
135 | These subkeys provide context to both you and other end-users, as well as to  
136 | Semgrep.  
137 |   
138 | \`\`\`yaml  
139 |   ...  
140 |   metadata:  
141 |     ...  
142 |     secret\_type: GitHub  
143 |     technology:  
144 |     \- secrets  
145 |   ...  
146 | \`\`\`  
147 | | Key | Description |  
148 | | \-------  | \------ |  
149 | | \`secret\_type\`  | Defines the name of the service or the type of secret. When writing a custom validator, set this value to a descriptive name to help identify it when triaging secrets. Examples of secret types include "Slack," "Asana," and other common service names. |  
150 | | \`technology\` | Set this to \`secrets\` to identify the rule as a Secrets rule. |  
151 |   
152 | \#\#\# Subkeys under the \`patterns\` key  
153 |   
154 | These subkeys identify the token to analyze in a given match.  
155 |   
156 | \`\`\`yaml  
157 |   ...  
158 |   patterns:  
159 |   ...  
160 |   \- pattern-regex: (?\<REGEX\>\\b((ghp|gho|ghu|ghs|ghr|github\_pat)\_\[a-zA-Z0-9\_\]{36,255})\\b)  
161 |   \- focus-metavariable: $REGEX  
162 |   \- metavariable-analysis:  
163 |       analyzer: entropy  
164 |       metavariable: $REGEX  
165 |   ..  
166 | \`\`\`  
167 |   
168 | | Key | Description |  
169 | | \-------  | \------ |  
170 | | \`pattern-regex\`  | Searches for a regular expression and assigns it to the named capture group regex, which is then used as $REGEX. |  
171 | | \`focus\_metavariable\`  | This key enables the rule to define a metavariable upon which Semgrep can perform further analysis, such as entropy analysis. |  
172 | | \`metavariable\_analysis\`  | Under \`metavariable\_analysis\`, you can define additional keys: \`analyzer\` and \`metavariable\`. These specify the kind of analysis Semgrep performs and on what variable.  |  
173 |   
174 | :::tip  
175 | For more information, see the rule syntax for \[\<i class="fa-regular fa-file-lines"\>\</i\> Focus  
176 | metavariable\](/writing-rules/rule-syntax/\#focus-metavariable).  
177 | :::  
178 |   
179 | \#\#\# Subkeys under the \`validators\` and \`http\` keys  
180 |   
181 | The \`validators\` key uses a list of keys to define the validator function. In  
182 | particular, the \`http\` key defines how the rule forms a request object and what  
183 | response is expected for valid and invalid states. Although some rules do not use a \`validators\` key, most Secrets rules use it.  
184 |   
185 | \`\`\`yaml  
186 |   ...  
187 |   validators:  
188 |   \- http:  
189 |       request:  
190 |         headers:  
191 |           Authorization: Bearer $REGEX  
192 |           Host: api.github.com  
193 |           User-Agent: Semgrep  
194 |         method: GET  
195 |         url: https://api.github.com/user  
196 |       response:  
197 |         \- match:  
198 |           \- status-code: '200'  
199 |           result:  
200 |             validity: valid  
201 |         \- match:  
202 |           \- status-code: '404'  
203 |           result:  
204 |             validity: invalid  
205 | \`\`\`  
206 |   
207 | | Key | Description |  
208 | | \-------  | \------ |  
209 | | \`request\`  | This key and its subkeys describe the request object and the URL to send the request object to. |  
210 | | \`response\`  | This key and its subkeys determine \*\*validation status\*\*. Semgrep Secrets identifies a validation status through HTTP status code \*\*and\*\* other key-value pairs. For example, a rule may require a 200 status code \*\*and\*\* a \`"message": "ok"\` in the response body for the matching secret to be considered \*\*Confirmed valid\*\*. |  
211 |   
212 | :::tip  
213 | See \[\<i class="fa-regular fa-file-lines"\>\</i\> Validators\](/semgrep-secrets/validators) for more information.  
214 | :::  
215 |   
216 | \#\# Metavariable binding  
217 |   
218 | Semgrep Secrets can use metavariables. Metavariables allow Semgrep Secrets to reuse matched information from your code in its validators. An example of a metavariable is as follows:  
219 |   
220 | \<iframe title="Message displays metavariable content" src="https://semgrep.dev/embed/editor?snippet=JDzRR" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
221 | \<br /\>  
222 |   
223 | When you click \*\*Run\*\*, the content from the metavariable \`$HELLO\` displays as \`This content is now reusable in validators\`. If this were a Secrets rule, Semgrep Secrets could use this to call the appropriate service to determine if the secret is active.  
224 |   
225 | \#\# Differences between Semgrep Secrets rules and Semgrep Registry rules  
226 |   
227 | The Semgrep Registry includes SAST rules that can detect secrets to a certain  
228 | extent. You can run these rules in Semgrep Code (Semgrep's SAST analyzer), or  
229 | even write your own custom secret-detecting SAST rules, but with the following  
230 | differences:  
231 |   
232 | \* Semgrep Code does not run a validator function against these rules, resulting in less accurate results.  
233 |     \* Because the results are less accurate, these rules are not suitable as criteria to block a PR or MR.  
234 | \* The UI for Semgrep Code is tailored to SAST triage and does not include filtering functions for valid or invalid tokens.  
235 | \* Existing Semgrep Pro rules that detect secrets are transitioning from Semgrep Code to Semgrep Secrets. By transitioning these rules, improvements, such as validator functions, can be added to the rules when they are run in Semgrep Secrets.  
236 | \* You can write your own custom validator functions and run them in Semgrep Secrets for custom services or use cases.  
237 | 

\--------------------------------------------------------------------------------  
/docs/semgrep-secrets/validators.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | slug: validators  
  3 | title: Custom validators  
  4 | hide\_title: true  
  5 | description: Learn about validators used in Semgrep Secrets rules.  
  6 | tags:  
  7 |   \- Semgrep Secrets  
  8 |   \- Rule writing  
  9 | \---  
 10 |   
 11 |   
 12 | \# Write custom validators  
 13 |   
 14 | \[Semgrep Secrets\](/semgrep-secrets/conceptual-overview) uses proprietary \*\*validators\*\* to determine if a secret is  
 15 | actively being used. Validators are included in the  
 16 | \[rules\](/semgrep-secrets/rules) that Semgrep Secrets uses.  
 17 |   
 18 | This article walks you through the syntax required to write your own custom  
 19 | validators.  
 20 |   
 21 |   
 22 | :::note  
 23 | \- The syntax for Semgrep Secrets validators is experimental and subject to change.  
 24 | \- Semgrep currently supports validation using HTTP and HTTPS.  
 25 | :::  
 26 |   
 27 | \#\# Sample validator  
 28 |   
 29 | \`\`\`yaml  
 30 | validators:  
 31 | \- http:  
 32 |     request:  
 33 |       headers:  
 34 |         Authorization: Bearer $REGEX  
 35 |         Host: api.semgrep.dev  
 36 |         User-Agent: Semgrep  
 37 |       method: GET  
 38 |       url: https://api.semgrep.dev/user  
 39 |     response:  
 40 |     \- match:  
 41 |       \- status-code: 200  
 42 |       result:  
 43 |         validity: valid  
 44 |     \- match:  
 45 |       \- status-code: 401  
 46 |       result:  
 47 |         validity: invalid  
 48 | \`\`\`  
 49 |   
 50 | \<details\>  
 51 | \<summary\>See a validator in the context of a full rule.\</summary\>  
 52 |   
 53 | \`\`\`yaml  
 54 | rules:  
 55 | \- id: exampleCo\_example  
 56 |   message: \>-  
 57 |     This is an example rule that performs validation against semgrep.dev  
 58 |   severity: WARNING  
 59 |   metadata:  
 60 |     product: secrets  
 61 |     secret\_type: exampleCo  
 62 |   languages:  
 63 |   \- regex  
 64 |   validators:  
 65 |   \- http:  
 66 |       request:  
 67 |         headers:  
 68 |           Authorization: Bearer $REGEX  
 69 |           Host: api.semgrep.dev  
 70 |           User-Agent: Semgrep  
 71 |         method: GET  
 72 |         url: https://api.semgrep.dev/user  
 73 |       response:  
 74 |       \- match:  
 75 |         \- status-code: 200  
 76 |         result:  
 77 |           validity: valid  
 78 |       \- match:  
 79 |         \- status-code: 401  
 80 |         result:  
 81 |           validity: invalid  
 82 |   patterns:  
 83 |   \- patterns:  
 84 |     \- pattern-regex: (?\<REGEX\>\\b(someprefix\_someRegex\[0-9A-Z\]{32})\\b)  
 85 |     \- focus-metavariable: $REGEX  
 86 |     \- metavariable-analysis:  
 87 |         analyzer: entropy  
 88 |         metavariable: $REGEX  
 89 | \`\`\`  
 90 |   
 91 | \</details\>  
 92 |   
 93 | \#\# Syntax  
 94 |   
 95 | \#\#\# validator  
 96 |   
 97 | | Key | Required | Description |  
 98 | | \- | \- | \- |  
 99 | | validator | Yes | Used to define a list of validators within a Semgrep rule. |  
100 |   
101 | \#\#\# type  
102 |   
103 | \<\!-- vale off \--\>  
104 |   
105 | | Key | Required | Description |  
106 | | \- | \- | \- |  
107 | | http | Yes | Indicates that the request type is \`http\`. |  
108 |   
109 | \<\!-- vale on \--\>  
110 |   
111 | :::note  
112 | Semgrep only supports web services with HTTP(S).  
113 | :::  
114 |   
115 | \#\#\# request  
116 |   
117 | \<\!-- vale off \--\>  
118 |   
119 | | Key | Required | Description |  
120 | | \- | \- | \- |  
121 | | request | Yes | Describes the request object and the URL to which the request object should be sent |  
122 | | method | Yes | The HTTP method Semgrep uses to make the call. Accepted values: \`GET\`, \`POST\`, \`PUT\`, \`DELETE\`, \`OPTIONS\`, \`PATCH\` |  
123 | | url | Yes | The URL to which the call is made |  
124 | | headers | Yes | The headers to include with the call |  
125 | | body | No | The body used with \`POST\`, \`PUT\`, and \`PATCH\` requests |  
126 |   
127 | \<\!-- vale on \--\>  
128 |   
129 | \#\#\#\# Subkeys for \`headers\`  
130 |   
131 | The following keys are for use with \`headers\`:  
132 |   
133 | | Key | Required | Description |  
134 | | \- | \- | \- |  
135 | | Host | No | The host to which the call is made. Only the \`url\` field is required, but you can override the host if needed  |  
136 | | Other-values | No | The request header. Accepts all values, including \`Authorization\`, \`Content-Type\`, \`User-Agent\`, and so on  |  
137 |   
138 | \#\#\#\# Example  
139 |   
140 | \`\`\`yaml  
141 | request:  
142 |   headers:  
143 |     Authorization: Bearer $REGEX  
144 |     Host: api.semgrep.dev  
145 |     User-Agent: Semgrep  
146 |   method: GET  
147 |   url: https://api.semgrep.dev/user  
148 | \`\`\`  
149 |   
150 | \#\#\# response  
151 |   
152 | The response key is used to determine the validation state. It accepts a list of objects with the Subkeys \`match\` and \`result\`.  
153 |   
154 | | Key | Required | Description |  
155 | | \- | \- | \- |  
156 | | match | Yes | Defines the list of match conditions. |  
157 | | result | Yes | Defines the validity. Accepted values: \`Valid\`, \`Invalid\` |  
158 |   
159 | \#\#\#\# Subkeys for \`match\`  
160 |   
161 | Match accepts a list of objects. No specific key is required, but at least one key must be present.  
162 |   
163 | | Key | Description |  
164 | | \- | \- |  
165 | | status-code | The HTTP status code expected by Semgrep Secrets for it to consider the secret a match |  
166 | | content | The response body; you can inspect it for a specific value to determine if the request is valid. An example of where this is useful is when both invalid and valid responses return the same status code |  
167 | | headers | Accepts a list of objects with the keys name/value they must be exact values |  
168 |   
169 |   
170 | \#\#\#\# Subkeys for \`result\`  
171 |   
172 | | Key | Required | Description |  
173 | | \- | \- | \- |  
174 | | validity | Yes | Sets the validity based on the HTTP status code received. Accepted values: \`valid\` and \`invalid\` |  
175 | | message | No | Used to override the rule message based on the secret's validity state |  
176 | | metadata | No | Used to override existing metadata fields or add new metadata fields based on the secret's validity state |  
177 | | severity |  No | Used to override the existing rule severity based on the validity state |  
178 |   
179 | \#\#\#\# Subkeys for \`content\`  
180 |   
181 | | Key | Required | Description |  
182 | | \- | \- | \- |  
183 | | language | Yes | Indicates the pattern language to use; this must be \`regex\` or \`generic\`|  
184 | | pattern-regex | Yes | Defines the regex used to search the response body. Alternatively, you can use the \`patterns\` key and \[define patterns as you would for rules\](/semgrep-secrets/rules/\#subkeys-under-the-patterns-key) |  
185 |   
186 | \#\#\#\# Example  
187 |   
188 | \`\`\`yaml  
189 | response:  
190 | \- match:  
191 |   \- status-code: 200  
192 |   \- content:  
193 |       language: regex  
194 |       pattern-regex: (\\"ok\\":true)  
195 |     status-code: 200  
196 | \`\`\`  
197 |   
198 | \#\# Sample rules with validators  
199 |   
200 | \<details\>  
201 | \<summary\>Sample POST request\</summary\>  
202 |   
203 | \`\`\`yaml  
204 | rules:  
205 | \- id: exampleCo\_example  
206 |   message: \>-  
207 |     This is an example rule that performs validation against semgrep.dev  
208 |   severity: WARNING  
209 |   metadata:  
210 |     product: secrets  
211 |     secret\_type: exampleCo  
212 |   languages:  
213 |   \- regex  
214 |   validators:  
215 |   \- http:  
216 |       request:  
217 |         headers:  
218 |           Host: api.semgrep.dev  
219 |           User-Agent: Semgrep  
220 |         method: POST  
221 |         body: |  
222 |           {"key": "$REGEX"}  
223 |         url: https://api.semgrep.dev/user  
224 |       response:  
225 |       \- match:  
226 |         \- status-code: 200  
227 |         result:  
228 |           validity: valid  
229 |       \- match:  
230 |         \- status-code: 401  
231 |         result:  
232 |           validity: invalid  
233 |   patterns:  
234 |   \- patterns:  
235 |     \- pattern-regex: (?\<REGEX\>\\b(someprefix\_someRegex\[0-9A-Z\]{32})\\b)  
236 |     \- focus-metavariable: $REGEX  
237 |     \- metavariable-analysis:  
238 |         analyzer: entropy  
239 |         metavariable: $REGEX  
240 | \`\`\`  
241 |   
242 | \</details\>  
243 |   
244 | \<details\>  
245 | \<summary\>All fields\</summary\>  
246 |   
247 | \`\`\`yaml  
248 | rules:  
249 | \- id: exampleCo\_example  
250 |   message: \>-  
251 |     This is an example rule that performs validation against semgrep.dev  
252 |   severity: WARNING  
253 |   metadata:  
254 |     product: secrets  
255 |     secret\_type: exampleCo  
256 |   languages:  
257 |   \- regex  
258 |   validators:  
259 |   \- http:  
260 |       request:  
261 |         headers:  
262 |           Host: api.semgrep.dev  
263 |           User-Agent: Semgrep  
264 |         method: POST  
265 |         body: |  
266 |           {"key": "$REGEX"}  
267 |         url: https://api.semgrep.dev/user  
268 |       response:  
269 |       \- match:  
270 |         \- status-code: 200  
271 |         \- content:  
272 |             language: regex  
273 |             pattern-regex: (\\"role\\":admin)  
274 |         result:  
275 |           validity: valid  
276 |           severity: ERROR  
277 |           message: \>-  
278 |             The token exposed is for an admin user, and this should be fixed immediately\!  
279 |             See https://howtorotate.com/docs/introduction/key-rotation-101/ on how to  
280 |             rotate secrets and https://blog.gitguardian.com/what-to-do-if-you-expose-a-secret/  
281 |             on how to look for suspicious activity.  
282 |           metadata:  
283 |             context:  
284 |               \- admin: true  
285 |       \- match:  
286 |         \- status-code: 200  
287 |         result:  
288 |           validity: invalid  
289 |   patterns:  
290 |   \- patterns:  
291 |     \- pattern-regex: (?\<REGEX\>\\b(someprefix\_someRegex\[0-9A-Z\]{32})\\b)  
292 |     \- focus-metavariable: $REGEX  
293 |     \- metavariable-analysis:  
294 |         analyzer: entropy  
295 |         metavariable: $REGEX  
296 | \`\`\`  
297 |   
298 | \</details\>  
299 |   
300 |   
301 | \#\#\# Base64 encoding  
302 |   
303 | You can use Base64 encoding by leveraging the \`\_\_semgrep\_internal\_encode\_64(...)\` utility. Base64 encoding can be applied to the following fields:  
304 |   
305 | \- \`url\`  
306 | \- \`body\`  
307 | \- \`header\` values  
308 |   
309 | :::note  
310 | The Base64 encoding of fields is experimental and can change at any time.  
311 | :::  
312 |   
313 | \<details\>  
314 | \<summary\>Sample Semgrep rule with validator using Base64 encoding\</summary\>  
315 |   
316 | \`\`\`yaml  
317 | rules:  
318 | \- id: exampleCo\_example  
319 |   message: \>-  
320 |     This is an example rule that performs validation against semgrep.dev  
321 |   severity: WARNING  
322 |   metadata:  
323 |     product: secrets  
324 |     secret\_type: exampleCo  
325 |   languages:  
326 |   \- regex  
327 |   validators:  
328 |   \- http:  
329 |       request:  
330 |         headers:  
331 |           Authorization: Basic \_\_semgrep\_internal\_encode\_64($REGEX:)  
332 |           Host: api.semgrep.dev  
333 |           User-Agent: Semgrep  
334 |         method: GET  
335 |         url: https://api.semgrep.dev/user  
336 |       response:  
337 |       \- match:  
338 |         \- status-code: 200  
339 |         result:  
340 |           validity: valid  
341 |       \- match:  
342 |         \- status-code: 401  
343 |         result:  
344 |           validity: invalid  
345 |   patterns:  
346 |   \- patterns:  
347 |     \- pattern-regex: (?\<REGEX\>\\b(someprefix\_someRegex\[0-9A-Z\]{32})\\b)  
348 |     \- focus-metavariable: $REGEX  
349 |     \- metavariable-analysis:  
350 |         analyzer: entropy  
351 |         metavariable: $REGEX  
352 | \`\`\`  
353 | \</details\>  
354 | 

\--------------------------------------------------------------------------------  
/docs/semgrep-secrets/view-triage.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: view-triage  
 3 | append\_help\_link: true  
 4 | title: Triage and remediation  
 5 | hide\_title: true  
 6 | description: Learn how to triage findings identified by Semgrep Secrets.  
 7 | tags:  
 8 |     \- Semgrep Secrets  
 9 |     \- Semgrep AppSec Platform  
10 | \---  
11 |   
12 | import TimePeriodFilters from "/src/components/concept/\_time-period-filters.md"  
13 | import ExportFindingsCsv from "/src/components/procedure/\_export-findings-csv.md"  
14 |   
15 | \# Triage secrets findings in Semgrep AppSec Platform  
16 |   
17 | After each scan, your findings are displayed in Semgrep AppSec Platform's  
18 | \*\*Secrets\*\* page. The filters provided allow you to manage and triage your findings.  
19 |   
20 | :::note Local scans  
21 | Findings from local scans are differentiated from their remote counterparts through their slugs. Remote repositories are identified as \<span className="placeholder"\>  ACCOUNT\_NAME/REPOSITORY\_NAME\</span\>, while local repositories are identified as \<span className="placeholder"\>local\_scan/REPOSITORY\_NAME\</span\>.  
22 | :::  
23 |   
24 | \#\# Default Secrets page view and branch logic  
25 |   
26 | In Semgrep, a \*\*single\*\* finding may appear in several branches. These appearances are called \*\*instances\*\* of a finding. In Semgrep Secrets, the \*\*latest instance\*\*, or the finding from the most recent branch scanned, is displayed by default. This is because, if a Secrets finding is present in \*\*any branch\*\*, even a non-primary (default) branch, it is considered \[valid\](/semgrep-secrets/conceptual-overview\#validate-secrets).  
27 |   
28 | \#\#\# Time period and triage  
29 |   
30 | \<TimePeriodFilters /\>  
31 |   
32 | \#\#\# Export findings  
33 |   
34 | \<ExportFindingsCsv /\>  
35 |   
36 | \#\# Triage findings  
37 |   
38 | You can triage secrets-related findings in Semgrep AppSec Platform on the \*\*Secrets\*\* page. By default, all findings are displayed. A common triage workflow includes the following tasks:  
39 |   
40 | 1\. Filtering for a particular characteristic of a finding, such as its \*\*Validation status\*\*, \*\*Repository or Branch\*\*, or \*\*Type\*\*.  
41 | 2\. Analyzing if the findings are true or false positives.  
42 | 3\. Applying a \*\*triage state\*\* to the filtered findings based on the analysis in step 2\.  
43 |     1\. Setting a finding as \*\*Ignored\*\* means that no action is undertaken and the finding is closed. Subsequent scans won't include this finding.  
44 |     2\. Setting or retaining a finding as \*\*Open\*\*, \*\*Reviewing\*\*, or \*\*Fixing\*\* means that the finding is a true positive and needs to be fixed or resolved.  
45 |         1\. Optional: You can \[create a ticket in Jira\](/semgrep-appsec-platform/jira) to assign a developer to fix findings.  
46 |   
47 | When commits are added to the PR or MR, Semgrep re-scans the PR or MR and detects if a finding is fixed, or if the secret is no longer valid. The finding changes status automatically upon scanning. Users do not need to set a finding as \*\*Fixed\*\* manually.  
48 |   
49 | \#\# Common filtering use cases  
50 |   
51 | You can find and perform bulk operations through filtering; \[all filter operations\](/semgrep-secrets/getting-started\#filter-findings) are available to you on the \*\*Secrets\*\* page.  
52 |   
53 | | Task | Steps |  
54 | | \---- | \----- |  
55 | | Viewing valid findings | Under \*\*Validation\*\*, click \*\*⚠️Confirmed valid\*\*. |  
56 | | View findings in a specific project or branch |1. Under \*\*Projects\*\*, select a repository from the drop-down menu. \<br /\> 2\. Under \*\*Branches\*\*, select a branch from the drop-down menu. |  
57 | | View findings of a specific type of secret, such as \*\*personal token\*\* or \*\*password\*\*. | Under \*\*Type\*\*, select a type of secret.  
58 | | View findings of a specific severity | Under \*\*Severity\*\*, select a value. |  
59 |   
60 | \!\[Secrets page and relevant triaging elements.\](/img/secrets-triage.png)  
61 | \*\*\_Figure.\_\*\* Secrets page and relevant triaging elements: (a) All available filters; (b) Bulk selection toggle; (c) Bulk triage button.  
62 |   
63 | You can triage findings in bulk by performing the following steps:  
64 |   
65 | 1\. Begin by ensuring that you display all \*\*Open\*\* findings.  
66 | 2\. Apply filters with as much specificity as possible. You may have to perform bulk triage several times. By starting with the most specific cases, and closing the findings from those specific cases, you are able to narrow down findings as you work from specific to broad filter criteria.  
67 | 3\. Click the bulk select check box.  
68 | 4\. Click \*\*Triage\*\*, then your selected triage state, such as \*\*Reviewing\*\* or \*\*Ignored\*\*.  
69 | 5\. Optional: Repeat this procedure to triage all open findings.  
70 |   
71 |   
72 | \#\# Receive findings through PR and MR comments  
73 |   
74 | In addition to viewing your results in Semgrep AppSec Platform, you can set up PR or MR comments from Semgrep, which allows you to view findings-related information directly in your pull requests and merge requests.  
75 |   
76 | To receive PR or MR comments, ensure that:  
77 |   
78 | \* You have set up \[comments\](/category/pr-or-mr-comments) as part of your core deployment.  
79 | \* You have defined which rules and validation states should be in Allow, Comment, or Block mode in the \[Policies\](/semgrep-secrets/policies) page.  
80 |   
81 | \!\[Semgrep Secrets finding in a PR comment\](/img/secrets-pr-comment.png)  
82 | \*\*\_Figure.\_\*\* Semgrep Secrets finding in a PR comment.  
83 |   
84 | :::info  
85 | Define which rules and validation states should be in Allow, Comment, or Block mode in the \[Policies\](/semgrep-secrets/policies) page.  
86 | :::  
87 | 

\--------------------------------------------------------------------------------  
