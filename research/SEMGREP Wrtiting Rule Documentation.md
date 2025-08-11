└── docs  
    └── writing-rules  
        ├── autofix.md  
        ├── data-flow  
            ├── constant-propagation.md  
            ├── data-flow-overview.md  
            ├── status.md  
            └── taint-mode.md  
        ├── experiments  
            ├── aliengrep.md  
            ├── deprecated-experiments.md  
            ├── display-propagated-metavariable.md  
            ├── introduction.md  
            ├── join-mode  
            │   ├── overview.md  
            │   └── recursive-joins.md  
            ├── metavariable-type.md  
            ├── multiple-focus-metavariables.md  
            ├── pattern-syntax.md  
            ├── project-depends-on.md  
            └── symbolic-propagation.md  
        ├── generic-pattern-matching.md  
        ├── glossary.md  
        ├── metavariable-analysis.md  
        ├── overview.md  
        ├── pattern-examples.md  
        ├── pattern-syntax.mdx  
        ├── private-rules.md  
        ├── rule-ideas.md  
        ├── rule-syntax.md  
        └── testing-rules.md

/docs/writing-rules/autofix.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | append\_help\_link: true  
  3 | tags:  
  4 |   \- Rule writing  
  5 | \---  
  6 |   
  7 | \# Autofix  
  8 |   
  9 | Autofix is a Semgrep feature where rules contain suggested fixes to resolve findings.  
 10 |   
 11 | Semgrep's rule format supports a \`fix:\` key that supports the replacement of metavariables and regex matches with potential fixes. This allows for value capture and rewriting. With rules that make use of the autofix capability, you can resolve findings as part of your code review workflow. Semgrep suggests these fixes through GitHub PR or GitLab MR comments.  
 12 |   
 13 | You can apply the autofix directly to the file using the \`--autofix\` flag. To test the autofix before applying it, use both the \`--autofix\` and \`--dryrun\` flags.  
 14 |   
 15 | \`\`\`tip  
 16 | Rule-based autofix is deterministic and separate from the \[Semgrep Assistant autofix feature\](/semgrep-assistant/overview\#autofix). The Assistant autofix feature uses AI to generate a suggested code fix.  
 17 | \`\`\`  
 18 |   
 19 | \#\# Example autofix snippet  
 20 |   
 21 | Sample autofix (view in \[Playground\](https://semgrep.dev/s/R6g)):  
 22 |   
 23 | \`\`\`yaml  
 24 | rules:  
 25 | \- id: use-sys-exit  
 26 |   languages:  
 27 |   \- python  
 28 |   message: |  
 29 |     Use \`sys.exit\` over the python shell \`exit\` built-in. \`exit\` is a helper  
 30 |     for the interactive shell and is not be available on all Python implementations.  
 31 |     https://stackoverflow.com/a/6501134  
 32 |   pattern: exit($X)  
 33 |   fix: sys.exit($X)  
 34 |   severity: WARNING  
 35 | \`\`\`  
 36 |   
 37 | \#\# Create autofix rules  
 38 |   
 39 | See how to create an autofix rule in \*\*Transforming code with Semgrep autofixes\*\* video:  
 40 |   
 41 | \<iframe class="yt\_embed" width="100%" height="432px" src="https://www.youtube.com/embed/8jfjWixmtvo" frameborder="0" allowfullscreen\>\</iframe\>  
 42 |   
 43 | \#\# Autofix with regular expression replacement  
 44 |   
 45 | A variant on the \`fix\` key is \`fix-regex\`, which applies regular expression replacements (think \`sed\`) to matches found by Semgrep.  
 46 |   
 47 | \`fix-regex\` has two required fields:  
 48 |   
 49 | \- \`regex\` specifies the regular expression to replace within the match found by Semgrep  
 50 | \- \`replacement\` specifies what to replace the regular expression with.  
 51 |   
 52 | \`fix-regex\` also takes an optional \`count\` field, which specifies how many occurrences of \`regex\` to replace with \`replacement\`, from left-to-right and top-to-bottom. By default, \`fix-regex\` will replace all occurrences of \`regex\`. If \`regex\` does not match anything, no replacements are made.  
 53 |   
 54 | The replacement behavior is identical to the \`re.sub\` function in Python. See these \[Python docs\](https://docs.python.org/3/library/re.html\#re.sub) for more information.  
 55 |   
 56 | An example rule with \`fix-regex\` is shown below. \`regex\` uses a capture group to greedily capture everything up to the final parenthesis in the match found by Semgrep. \`replacement\` replaces this with everything in the capture group (\`\\1\`), a comma, \`timeout=30\`, and a closing parenthesis. Effectively, this adds \`timeout=30\` to the end of every match.  
 57 |   
 58 | \`\`\`yaml  
 59 | rules:  
 60 | \- id: python.requests.best-practice.use-timeout.use-timeout  
 61 |   patterns:  
 62 |   \- pattern-not: requests.$W(..., timeout=$N, ...)  
 63 |   \- pattern-not: requests.$W(..., \*\*$KWARGS)  
 64 |   \- pattern-either:  
 65 |     \- pattern: requests.request(...)  
 66 |     \- pattern: requests.get(...)  
 67 |     \- pattern: requests.post(...)  
 68 |     \- pattern: requests.put(...)  
 69 |     \- pattern: requests.delete(...)  
 70 |     \- pattern: requests.head(...)  
 71 |     \- pattern: requests.patch(...)  
 72 |   fix-regex:  
 73 |     regex: '(.\*)\\)'  
 74 |     replacement: '\\1, timeout=30)'  
 75 |   message: |  
 76 |     'requests' calls default to waiting until the connection is closed.  
 77 |     This means a 'requests' call without a timeout will hang the program  
 78 |     if a response is never received. Consider setting a timeout for all  
 79 |     'requests'.  
 80 |   languages: \[python\]  
 81 |   severity: WARNING  
 82 | \`\`\`  
 83 |   
 84 | \#\# Remove a code detected by a rule  
 85 |   
 86 | Improve your code quality by cleaning up stale code automatically. Remove code that an autofix rule detected by adding the \`fix\` key with \`""\`, an empty string.  
 87 |   
 88 | For example:  
 89 |   
 90 | \`\`\`yaml  
 91 |  \- id: python-typing  
 92 |    pattern: from typing import $X  
 93 |    fix: ""  
 94 |    languages: \[ python \]  
 95 |    message: found one  
 96 |    severity: ERROR  
 97 | \`\`\`  
 98 |   
 99 | When an autofix is applied, this rule removes the detected code.  
100 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/data-flow/constant-propagation.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | slug: constant-propagation  
  3 | append\_help\_link: true  
  4 | description: \>-  
  5 |   Semgrep performs flow-sensitive constant folding and this information is used by the matching engine.  
  6 | tags:  
  7 |     \- Rule writing  
  8 | \---  
  9 |   
 10 | \# Constant propagation  
 11 |   
 12 | This analysis tracks whether a variable \_must\_ carry a constant value at a given point in the program. Semgrep then performs constant folding when matching literal patterns. Semgrep can track Boolean, numeric, and string constants.  
 13 |   
 14 | Semgrep AppSec Platform supports interprocedural (cross function), interfile (cross file) constant propagation. Semgrep Community Edition (CE) supports intrafile (single file) constant propagation.  
 15 |   
 16 | For example:  
 17 |   
 18 | \<iframe src="https://semgrep.dev/embed/editor?snippet=Gw7z" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
 19 |   
 20 | \#\# \`metavariable-comparison\`  
 21 |   
 22 | Using constant propagation, the \[\`metavariable-comparison\`\](/writing-rules/rule-syntax/\#metavariable-comparison) operator works with any constant variable, instead of just literals.  
 23 |   
 24 | For example:  
 25 |   
 26 | \<iframe src="https://semgrep.dev/embed/editor?snippet=Dyzd" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
 27 |   
 28 | \#\# Mutable objects  
 29 |   
 30 | In general, Semgrep assumes that constant objects are immutable and won't be modified by function calls. This may lead to false positives, especially in languages where strings are mutable such as C and Ruby.  
 31 |   
 32 | The only exceptions are method calls whose returning value is ignored. In these cases, Semgrep assumes that the method call may be mutating the callee object. This helps reducing false positives in Ruby. For example:  
 33 |   
 34 | \<iframe src="https://semgrep.dev/embed/editor?snippet=08yB" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
 35 |   
 36 | If constant propagation doesn't seem to work, consider whether the constant may be unexpectedly mutable. For example, given the following rule designed to taint the \`REGEX\` class variable:  
 37 |   
 38 | \`\`\`yaml  
 39 | rules:  
 40 |   \- id: redos-detection  
 41 |     message: Potential ReDoS vulnerability detected with $REGEX  
 42 |     severity: ERROR  
 43 |     languages:  
 44 |       \- java  
 45 |     mode: taint  
 46 |     options:  
 47 |       symbolic\_propagation: true  
 48 |     pattern-sources:  
 49 |       \- patterns:  
 50 |           \- pattern: $REDOS  
 51 |           \- metavariable-analysis:  
 52 |               analyzer: redos  
 53 |               metavariable: $REDOS  
 54 |     pattern-sinks:  
 55 |       \- pattern: Pattern.compile(...)  
 56 | \`\`\`  
 57 |   
 58 | Semgrep fails to match its use in \`Test2\` when presented with the following code:  
 59 |   
 60 | \`\`\`java  
 61 | import java.util.regex.Pattern;  
 62 |   
 63 | public String REGEX \= "(a+)+  
quot;;  
 64 |   
 65 | public class Test2 {  
 66 |     public static void main(String\[\] args) {  
 67 |         Pattern pattern \= Pattern.compile(REGEX);  
 68 |     }  
 69 | }  
 70 | \`\`\`  
 71 |   
 72 | However, if you change the variable from \`public\` to \`private\`, Semgrep does return a match:  
 73 |   
 74 | \`\`\`java  
 75 | import java.util.regex.Pattern;  
 76 |   
 77 | private String REGEX \= "(a+)+  
quot;;  
 78 |   
 79 | public class Test2 {  
 80 |     public static void main(String\[\] args) {  
 81 |         Pattern pattern \= Pattern.compile(REGEX);  
 82 |     }  
 83 | }  
 84 | \`\`\`  
 85 |   
 86 | Because \`REGEX\` is public in the first code snippet, Semgrep doesn't propagate its value to other classes on the assumption that it could have mutated. However, in the second example, Semgrep understands that \`REGEX\` is private and is only assigned to once. Therefore, Semgrep assumes it to be immutable.  
 87 |   
 88 | The rule would also work with:  
 89 |   
 90 | \`\`\`java  
 91 | ...  
 92 | public final String REGEX \= "(a+)+  
quot;;  
 93 | ...  
 94 | \`\`\`  
 95 |   
 96 | \#\# Disable constant propagation  
 97 |   
 98 | You can disable constant propagation in a per-rule basis using rule \[\`options:\`\](/writing-rules/rule-syntax/\#options) by setting \`constant\_propagation: false\`.  
 99 |   
100 | \<iframe src="https://semgrep.dev/embed/editor?snippet=jwvn" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
101 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/data-flow/data-flow-overview.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: data-flow-overview  
 3 | append\_help\_link: true  
 4 | description: \>-  
 5 |   Semgrep can run data-flow analyses on your code, this is used for constant propagation and for taint tracking.  
 6 | sidebar\_label: Engine overview  
 7 | tags:  
 8 |   \- Rule writing  
 9 | \---  
10 |   
11 | import DataFlowStatus from "/src/components/concept/\_data-flow-status.mdx"  
12 |   
13 | \# Data-flow analysis engine overview  
14 |   
15 | Semgrep provides an intra-procedural data-flow analysis engine that opens various Semgrep capabilities. Semgrep provides the following data-flow analyses:  
16 | \- \[Constant propagation\](/writing-rules/data-flow/constant-propagation) allows Semgrep to, for example, match \`return 42\` against \`return x\` when \`x\` can be reduced to \`42\` by constant folding. There is also a specific experimental feature of \[Constant propagation\](/writing-rules/data-flow/constant-propagation), called \[Symbolic propagation\](/writing-rules/experiments/symbolic-propagation).  
17 | \- \[Taint tracking (known also as taint analysis)\](/writing-rules/data-flow/taint-mode/) enables you to write simple rules that catch complex \[injection bugs\](https://owasp.org/www-community/Injection\_Flaws), such as those that can result in \[cross-site scripting (XSS)\](https://owasp.org/www-community/attacks/xss/).  
18 |   
19 | In principle, all data flow related features are available for any of Semgrep's \[supported languages\](/supported-languages). Interfile (cross-file) analysis also supports data-flow analysis. For more details, see \[\<i class="fa-regular fa-file-lines"\>\</i\> Perform cross-file analysis\](/semgrep-code/semgrep-pro-engine-intro) documentation.  
20 |   
21 | :::info  
22 | Ensure that you understand the \[design trade-offs\](\#design-trade-offs) and limitations of the data-flow engine. For further details, see also the \[data-flow status\](\#data-flow-status).  
23 | :::  
24 |   
25 | Semgrep provides no user-friendly way of specifying a new data-flow analysis. Please \[let us know if you have suggestions\](https://github.com/semgrep/semgrep/issues/new/choose). If you can code in OCaml, your contribution is welcome. See \[Contributing\](/contributing/contributing) documentation for more details.  
26 |   
27 | \#\# Design trade-offs  
28 |   
29 | Semgrep strives for simplicity and delivers a lightweight, and fast static analysis. In addition to being intra-procedural, here are some other trade-offs:  
30 |   
31 | \- No path sensitivity: All \_potential\_ execution paths are considered, despite that some may not be feasible.  
32 | \- No pointer or shape analysis: \_Aliasing\_ that happens in non-trivial ways may not be detected, such as through arrays or pointers. Individual elements in arrays or other data structures are not tracked. The dataflow engine supports limited field sensitivity for taint tracking, but not yet for constant propagation.  
33 | \- No soundness guarantees: Semgrep ignores the effects of \`eval\`-like functions on the program state. It doesn’t make worst-case sound assumptions, but rather "reasonable" ones.  
34 |   
35 | Expect both false positives and false negatives. You can remove false positives in different ways, for example, using \[pattern-not\](/writing-rules/rule-syntax\#pattern-not) and \[pattern-not-inside\](/writing-rules/rule-syntax\#pattern-not-inside). We want to provide you with a way of eliminating false positives, so \[create an issue\](https://github.com/semgrep/semgrep/issues/new/choose) if run into any problems. We are happy to trade false negatives for simplicity and fewer false positives, but you are welcome to open a feature request if Semgrep misses some difficult bug you want to catch.  
36 |   
37 | \#\# Data-flow status  
38 |   
39 | \<DataFlowStatus /\>  
40 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/data-flow/status.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: status  
 3 | append\_help\_link: true  
 4 | tags:  
 5 |     \- Rule writing  
 6 | description: \>-  
 7 |   The status of the data-flow analysis.  
 8 | \---  
 9 |   
10 | import DataFlowStatus from "/src/components/concept/\_data-flow-status.mdx"  
11 |   
12 | \# Data-flow status  
13 |   
14 | \<DataFlowStatus /\>  
15 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/data-flow/taint-mode.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | slug: taint-mode  
  3 | append\_help\_link: true  
  4 | tags:  
  5 |     \- Rule writing  
  6 | description: \>-  
  7 |   Taint mode allows you to write simple rules that catch complex injection bugs thanks to taint analysis.  
  8 | \---  
  9 |   
 10 | \# Taint analysis  
 11 |   
 12 | Semgrep supports \[taint analysis\](https://en.wikipedia.org/wiki/Taint\_checking) (or taint tracking) through taint rules (specified by adding \`mode: taint\` to your rule). Taint analysis is a data-flow analysis that tracks the flow of untrusted, or \*\*tainted\*\* data throughout the body of a function or method. Tainted data originate from tainted \*\*sources\*\*. If tainted data is not transformed or checked accordingly (\*\*sanitized\*\*), taint analysis reports a finding whenever tainted data reach a vulnerable function, called a \*\*sink\*\*. Tainted data flow from sources to sinks through \*\*propagators\*\*, such as assignments, or function calls.  
 13 |   
 14 | The following video provides a quick overview of taint mode:  
 15 | \<iframe class="yt\_embed" width="100%" height="432px" src="https://www.youtube.com/embed/6MxMhFPkZlU" frameborder="0" allowfullscreen\>\</iframe\>  
 16 |   
 17 | \#\# Getting started  
 18 |   
 19 | Taint tracking rules must specify \`mode: taint\`, which enables the following operators:  
 20 |   
 21 | \- \`pattern-sources\` (required)  
 22 | \- \`pattern-propagators\` (optional)  
 23 | \- \`pattern-sanitizers\` (optional)  
 24 | \- \`pattern-sinks\` (required)  
 25 |   
 26 | These operators (which act as \`pattern-either\` operators) take a list of patterns that specify what is considered a source, a propagator, a sanitizer, or a sink. Note that you can use \*\*any\*\* pattern operator and you have the same expressive power as in a \`mode: search\` rule.  
 27 |   
 28 | For example:  
 29 |   
 30 | \<iframe src="https://semgrep.dev/embed/editor?snippet=xG6g" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
 31 |   
 32 | Here Semgrep tracks the data returned by \`get\_user\_input()\`, which is the source of taint. Think of Semgrep running the pattern \`get\_user\_input(...)\` on your code, finding all places where \`get\_user\_input\` gets called, and labeling them as tainted. That is exactly what is happening under the hood\!  
 33 |   
 34 | The rule specifies the sanitizer \`sanitize\_input(...)\`, so any expression that matches that pattern is considered sanitized. In particular, the expression \`sanitize\_input(data)\` is labeled as sanitized. Even if \`data\` is tainted, as it occurs inside a piece of sanitized code, it does not produce any findings.  
 35 |   
 36 | Finally, the rule specifies that anything matching either \`html\_output(...)\` or \`eval(...)\` should be regarded as a sink. There are two calls \`html\_output(data)\` that are both labeled as sinks. The first one in \`route1\` is not reported because \`data\` is sanitized before reaching the sink, whereas the second one in \`route2\` is reported because the \`data\` that reaches the sink is still tainted.  
 37 |   
 38 | You can find more examples of taint rules in the \[Semgrep Registry\](https://semgrep.dev/r?owasp=injection%2Cxss), for instance: \[express-sandbox-code-injection\](https://semgrep.dev/editor?registry=javascript.express.security.express-sandbox-injection.express-sandbox-code-injection).  
 39 |   
 40 | :::info  
 41 | \[Metavariables\](/writing-rules/pattern-syntax\#metavariables) used in \`pattern-sources\` are considered \_different\_ from those used in \`pattern-sinks\`, even if they have the same name\! See \[Metavariables, rule message, and unification\](\#metavariables-rule-message-and-unification) for further details.  
 42 | :::  
 43 |   
 44 | \#\# Sources  
 45 |   
 46 | A taint source is specified by a pattern. Like in a search-mode rule, you can start this pattern with one of the following keys: \`pattern\`, \`patterns\`, \`pattern-either\`, \`pattern-regex\`. Note that \*\*any\*\* subexpression that is matched by this pattern will be regarded as a source of taint.  
 47 |   
 48 | In addition, taint sources accept the following options:  
 49 |   
 50 | | Option            | Type                      | Default | Description                                                            |  
 51 | | :-----------------|:------------------------- | :------ | :--------------------------------------------------------------------- |  
 52 | | \`exact\`           | {\`false\`, \`true\`}         | \`false\` | See \[\_Exact sources\_\](\#exact-sources).                                 |  
 53 | | \`by-side-effect\`  | {\`false\`, \`true\`, \`only\`} | \`false\` | See \[\_Sources by side-effect\_\](\#sources-by-side-effect).               |  
 54 | | \`control\` (Pro) 🧪 | {\`false\`, \`true\`}         | \`false\` | See \[\_Control sources\_\](\#control-sources-pro-).                           |  
 55 |   
 56 | Example:  
 57 |   
 58 | \`\`\`yaml  
 59 | pattern-sources:  
 60 | \- pattern: source(...)  
 61 | \`\`\`  
 62 |   
 63 | \#\#\# Exact sources  
 64 |   
 65 | Given the source specification below, and a piece of code such as \`source(sink(x))\`, the call \`sink(x)\` is reported as a tainted sink.  
 66 |   
 67 | \`\`\`yaml  
 68 | pattern-sources:  
 69 | \- pattern: source(...)  
 70 | \`\`\`  
 71 |   
 72 | The reason is that the pattern \`source(...)\` matches all of \`source(sink(x))\`, and that makes Semgrep consider every subexpression in that piece of code as being a source. In particular, \`x\` is a source, and it is being passed into \`sink\`\!  
 73 |   
 74 | \<iframe src="https://semgrep.dev/embed/editor?snippet=eqYN8" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
 75 |   
 76 | This is the default for historical reasons, but it may change in the future.  
 77 |   
 78 | It is possible to instruct Semgrep to only consider as taint sources the "exact" matches of a source pattern by setting \`exact: true\`:  
 79 |   
 80 | \`\`\`yaml  
 81 | pattern-sources:  
 82 | \- pattern: source(...)  
 83 |   exact: true  
 84 | \`\`\`  
 85 |   
 86 | Once the source is "exact," Semgrep will no longer consider subexpressions as taint sources, and \`sink(x)\` inside \`source(sink(x))\` will not be reported as a tainted sink (unless \`x\` is tainted in some other way).  
 87 |   
 88 | \<iframe src="https://semgrep.dev/embed/editor?snippet=Zq5ow" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
 89 |   
 90 | For many rules this distinction is not very meaningful because it does not always make sense that a sink occurs inside the arguments of a source function.  
 91 |   
 92 | :::note  
 93 | If one of your rules relies on non-exact matching of sources, we advice you to make it explicit with \`exact: false\`, even if it is the current default, so that your rule does not break if the default changes.  
 94 | :::  
 95 |   
 96 | \#\#\# Sources by side-effect  
 97 |   
 98 | Consider the following hypothetical Python code, where \`make\_tainted\` is a function that makes its argument tainted by side-effect:  
 99 |   
100 | \`\`\`python  
101 | make\_tainted(my\_set)  
102 | sink(my\_set)  
103 | \`\`\`  
104 |   
105 | This kind of source can be specified by setting \`by-side-effect: true\`:  
106 |   
107 | \`\`\`yaml  
108 | pattern-sources:  
109 |   \- patterns:  
110 |       \- pattern: make\_tainted($X)  
111 |       \- focus-metavariable: $X  
112 |     by-side-effect: true  
113 | \`\`\`  
114 |   
115 | When this option is enabled, and the source specification matches a variable (or in general, an \[l-value\](https://en.wikipedia.org/wiki/Value\_(computer\_science)\#lrvalue)) exactly, then Semgrep assumes that the variable (or l-value) becomes tainted by side-effect at the precise places where the source specification produces a match.  
116 |   
117 | \<iframe src="https://semgrep.dev/embed/editor?snippet=5r400" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
118 |   
119 | The matched occurrences themselves are considered tainted; that is, the occurrence of \`x\` in \`make\_tainted(x)\` is itself tainted too. If you do not want this to be the case, then set \`by-side-effect: only\` instead.  
120 |   
121 | :::note  
122 | You must use \`focus-metavariable: $X\` to focus the match on the l-value that you want to taint, otherwise \`by-side-effect\` does not work.  
123 | :::  
124 |   
125 | If the source does not set \`by-side-effect\`, then only the very occurrence of \`x\` in \`make\_tainted(x)\` will be tainted, but not the occurrence of \`x\` in \`sink(x)\`. The source specification matches only the first occurrence and, without \`by-side-effect: true\`, Semgrep does not know that \`make\_tainted\` is updating the variable \`x\` by side-effect. Thus, a taint rule using such a specification does not produce any finding.  
126 |   
127 | :::info  
128 | You could be tempted to write a source specification as the following example (and this was the official workaround before \`by-side-effect\`):  
129 |   
130 | \`\`\`yaml  
131 | pattern-sources:  
132 | \- patterns:  
133 |   \- pattern-inside: |  
134 |       make\_tainted($X)  
135 |       ...  
136 |   \- pattern: $X  
137 | \`\`\`  
138 |   
139 | This tells Semgrep that \*\*every\*\* occurrence of \`$X\` after \`make\_tainted($X)\` must be considered a source.  
140 |   
141 | This approach has two main limitations. First, it overrides any sanitization that can be performed on the code matched by \`$X\`. In the example code below, the call \`sink(x)\` is reported as tainted despite \`x\` having been sanitized\!  
142 |   
143 | \`\`\`python  
144 | make\_tainted(x)  
145 | x \= sanitize(x)  
146 | sink(x) \# false positive  
147 | \`\`\`  
148 |   
149 | Note also that \[\`...\` ellipses operator\](/writing-rules/pattern-syntax/\#ellipses-and-statement-blocks) has limitations. For example, in the code below Semgrep does not match any finding if such source specification is in use:  
150 |   
151 | \`\`\`python  
152 | if cond:  
153 |     make\_tainted(x)  
154 | sink(x) \# false negative  
155 | \`\`\`  
156 |   
157 | The \`by-side-effect\` option was added precisely \[to address those limitations\](https://semgrep.dev/playground/s/JDv4y). However, that kind of workaround can still be useful in other situations\!  
158 | :::  
159 |   
160 | \#\#\# Function arguments as sources  
161 |   
162 | To specify that an argument of a function must be considered a taint source, simply write a pattern that matches that argument:  
163 |   
164 | \`\`\`yaml  
165 | pattern-sources:  
166 |   \- patterns:  
167 |     \- pattern-inside: |  
168 |         def foo($X, ...):  
169 |           ...  
170 |     \- focus-metavariable: $X  
171 | \`\`\`  
172 |   
173 | Note that the use of \`focus-metavariable: $X\` is very important, and using \`pattern: $X\` is \*\*not\*\* equivalent. With \`focus-metavariable: $X\`, Semgrep matches the formal parameter exactly. Click "Open in Playground" below and use "Inspect Rule" to visualize what the source is matching.  
174 |   
175 | \<iframe src="https://semgrep.dev/embed/editor?snippet=L1vJ6" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
176 |   
177 | The following example does the same with this other taint rule that uses \`pattern: $X\`. The \`pattern: $X\` does not match the formal parameter itself, but matches all its uses inside the function definition. Even if \`x\` is sanitized via \`x \= sanitize(x)\`, the occurrence of \`x\` inside \`sink(x)\` is a taint source itself (due to \`pattern: $X\`) and so \`sink(x)\` is tainted\!  
178 |   
179 | \<iframe src="https://semgrep.dev/embed/editor?snippet=Qr3Y4" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
180 |   
181 | \#\#\# Control sources (Pro) 🧪  
182 |   
183 | \*\*Control taint sources is a Semgrep Pro feature.\*\*  
184 |   
185 | Typically taint analysis tracks the flow of tainted \_data\_, but taint sources can also track the flow of tainted \_control\_ by setting \`control: true\`.  
186 |   
187 | \`\`\`yaml  
188 | pattern-sources:  
189 | \- pattern: source(...)  
190 |   control: true  
191 | \`\`\`  
192 |   
193 | This is useful for checking \_reachability\_, that is to check if from a given code location the control-flow can reach another code location, regardless of whether there is any flow of data between them. In the following example we check whether \`foo()\` could be followed by \`bar()\`:  
194 |   
195 | \<iframe src="https://semgrep.dev/embed/editor?snippet=yyjrx" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
196 |   
197 | By using a control source, you can define a context from which Semgrep detects if a call to some other code, such as a sink, can be reached.  
198 |   
199 | :::note  
200 | Use \[taint labels\](\#taint-labels-pro-) to combine both data and control sources in the same rule.  
201 | :::  
202 |   
203 | \#\# Sanitizers  
204 |   
205 | A taint sanitizer is specified by a pattern. Like in a search-mode rule, you can start this pattern with one of the following keys: \`pattern\`, \`patterns\`, \`pattern-either\`, \`pattern-regex\`. Note that \*\*any\*\* subexpression that is matched by this pattern will be regarded as sanitized.  
206 |   
207 | In addition, taint sanitizers accept the following options:  
208 |   
209 | | Option            | Type                      | Default | Description                                                            |  
210 | | :-----------------|:------------------------- | :------ | :--------------------------------------------------------------------- |  
211 | | \`exact\`           | {\`false\`, \`true\`}         | \`false\` | See \[\_Exact sanitizers\_\](\#exact-sanitizers).                              |  
212 | | \`by-side-effect\`  | {\`false\`, \`true\`, \`only\`} | \`false\` | See \[\_Sanitizers by side-effect\_\](\#sanitizers-by-side-effect).         |  
213 |   
214 | Example:  
215 |   
216 | \`\`\`yaml  
217 | pattern-sanitizers:  
218 | \- pattern: sanitize(...)  
219 | \`\`\`  
220 |   
221 | \#\#\# Exact sanitizers  
222 |   
223 | Given the sanitizer specification below, and a piece of code such as \`sanitize(sink("taint"))\`, the call \`sink("taint")\` is \*\*not\*\* reported.  
224 |   
225 | \`\`\`yaml  
226 | pattern-sanitizers:  
227 | \- pattern: sanitize(...)  
228 | \`\`\`  
229 |   
230 | The reason is that the pattern \`sanitize(...)\` matches all of \`sanitize(sink("taint"))\`, and that makes Semgrep consider every subexpression in that piece of code as being sanitized. In particular, \`"taint"\` is considered to be sanitized\!  
231 |   
232 | \<iframe src="https://semgrep.dev/embed/editor?snippet=v83Rb" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
233 |   
234 | This is the default for historical reasons, but it may change in the future.  
235 |   
236 | It is possible to instruct Semgrep to only consider as sanitized the "exact" matches of a sanitizer pattern by setting \`exact: true\`:  
237 |   
238 | \`\`\`yaml  
239 | pattern-sanitizers:  
240 | \- pattern: sanitize(...)  
241 |   exact: true  
242 | \`\`\`  
243 |   
244 | Once the source is "exact," Semgrep will no longer consider subexpressions as sanitized, and \`sink("taint")\` inside \`sanitize(sink("taint"))\` will be reported as a tainted sink.  
245 |   
246 | \<iframe src="https://semgrep.dev/embed/editor?snippet=Zqz8o" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
247 |   
248 | For many rules this distinction is not very meaningful because it does not always make sense that a sink occurs inside the arguments of a sanitizer function.  
249 |   
250 | :::note  
251 | If one of your rules relies on non-exact matching of sanitizers, We at Semgrep advise you to make it explicit with \`exact: false\`, even if it is the current default, so that your rule does not break if the default changes.  
252 | :::  
253 |   
254 | \#\#\# Sanitizers by side-effect  
255 |   
256 | Consider the following hypothetical Python code, where it is guaranteed that after \`check\_if\_safe(x)\`, the value of \`x\` must be a safe one.  
257 |   
258 | \`\`\`python  
259 | x \= source()  
260 | check\_if\_safe(x)  
261 | sink(x)  
262 | \`\`\`  
263 |   
264 | This kind of sanitizer can be specified by setting \`by-side-effect: true\`:  
265 |   
266 | \`\`\`yaml  
267 | pattern-sanitizers:  
268 |   \- patterns:  
269 |       \- pattern: check\_if\_safe($X)  
270 |       \- focus-metavariable: $X  
271 |     by-side-effect: true  
272 | \`\`\`  
273 | When this option is enabled, and the sanitizer specification matches a variable (or in general, an l-value) exactly, then Semgrep assumes that the variable (or l-value) is sanitized by side-effect at the precise places where the sanitizer specification produces a match.  
274 |   
275 | \<iframe src="https://semgrep.dev/embed/editor?snippet=4bvGz" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
276 |   
277 | :::note  
278 | It is important to use \`focus-metavariable: $X\` to focus the match on the l-value that we want to sanitize, otherwise \`by-side-effect\` does not work as expected.  
279 | :::  
280 |   
281 | If the sanitizer does not set \`by-side-efect\`, then only the very occurrence of \`x\` in \`check\_if\_safe(x)\` will be sanitized, but not the occurrence of \`x\` in \`sink(x)\`. The sanitizer specification matches only the first occurrence and, without \`by-side-effect: true\`, Semgrep does not know that \`check\_if\_safe\` is updating/sanitizing the variable \`x\` by side-effect. Thus, a taint rule using such specification does produce a finding for \`sink(x)\` in the example above.  
282 |   
283 | :::info  
284 | You can be tempted to write a sanitizer specification as the one below (and this was the official workaround before \`by-side-effect\`):  
285 |   
286 | \`\`\`yaml  
287 | pattern-sanitizers:  
288 | \- patterns:  
289 |   \- pattern-inside: |  
290 |       check\_if\_safe($X)  
291 |       ...  
292 |   \- pattern: $X  
293 | \`\`\`  
294 |   
295 | This tells Semgrep that \*\*every\*\* occurrence of \`$X\` after \`check\_if\_safe($X)\` must be considered sanitized.  
296 |   
297 | This approach has two main limitations. First, it overrides any further tainting that can be performed on the code matched by \`$X\`.  In the example code below, the call \`sink(x)\` is  \*\*not\*\* reported as tainted despite \`x\` having been tainted\!  
298 |   
299 | \`\`\`python  
300 | check\_if\_safe(x)  
301 | x \= source()  
302 | sink(x) \# false negative  
303 | \`\`\`  
304 |   
305 | Note also that \[\`...\` ellipses operator\](/writing-rules/pattern-syntax/\#ellipses-and-statement-blocks) has limitations. For example, in the code below Semgrep still matches despite \`x\` having been sanitized in both branches:  
306 |   
307 | \`\`\`python  
308 | if cond:  
309 |     check\_if\_safe(x)  
310 | else  
311 |     check\_if\_safe(x)  
312 | sink(x) \# false positive  
313 | \`\`\`  
314 |   
315 | The \`by-side-effect\` option was added precisely \[to address those limitations\](https://semgrep.dev/playground/s/PeB3W). However, that kind of workaround can still be useful in other situations\!  
316 | :::  
317 |   
318 | \#\# Sinks  
319 |   
320 | A taint sink is specified by a pattern. Like in a search-mode rule, you can start this pattern with one of the following keys: \`pattern\`, \`patterns\`, \`pattern-either\`, \`pattern-regex\`. Unlike sources and sanitizers, by default Semgrep does not consider the subexpressions of the matched expressions as sinks.  
321 |   
322 | In addition, taint sinks accept the following options:  
323 |   
324 | | Option    | Type              | Default | Description                                                            |  
325 | | :---------| :-----------------| :------ | :--------------------------------------------------------------------- |  
326 | | \`exact\`   | {\`false\`, \`true\`} | \`true\`  | See \[\_Non-exact sinks\_\](\#non-exact-sinks).                             |  
327 | | \`at-exit\` (Pro) 🧪 | {\`false\`, \`true\`} | \`false\` | See \[\_At-exit sinks\_\](\#at-exit-sinks-pro-).                   |  
328 |   
329 | Example:  
330 |   
331 | \`\`\`yaml  
332 | pattern-sinks:  
333 | \- pattern: sink(...)  
334 | \`\`\`  
335 |   
336 | \#\#\# Non-exact sinks  
337 |   
338 | Given the sink specification below, a piece of code such as \`sink("foo" if tainted else "bar")\` will \*\*not\*\* be reported as a tainted sink.  
339 |   
340 | \`\`\`yaml  
341 | pattern-sources:  
342 | \- pattern: sink(...)  
343 | \`\`\`  
344 |   
345 | This is because Semgrep considers that the sink is the argument of the \`sink\` function, and the actual argument being passed is \`"foo" if tainted else "bar"\` that evaluates to either \`"foo"\` or \`"bar"\`, and neither of them are tainted.  
346 |   
347 | \<iframe src="https://semgrep.dev/embed/editor?snippet=KxJ17" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
348 |   
349 | It is possible to instruct Semgrep to consider as a taint sink any of the subexpressions matching the sink pattern, by setting \`exact: false\`:  
350 |   
351 | \`\`\`yaml  
352 | pattern-sinks:  
353 | \- pattern: sink(...)  
354 |   exact: false  
355 | \`\`\`  
356 |   
357 | Once the sink is "non-exact" Semgrep will consider subexpressions as taint sinks, and \`tainted\` inside \`sink("foo" if tainted else "bar")\` will then be reported as a tainted sink.  
358 |   
359 | \<iframe src="https://semgrep.dev/embed/editor?snippet=qNwez" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
360 |   
361 | \#\#\# Function arguments as sinks  
362 |   
363 | We can specify that only one (or a subset) of the arguments of a function is the actual sink by using \`focus-metavariable\`:  
364 |   
365 | \`\`\`javascript  
366 | pattern-sinks:  
367 |   \- patterns:  
368 |     \- pattern: sink($SINK, ...)  
369 |     \- focus-metavariable: $SINK  
370 | \`\`\`  
371 |   
372 | This rule causes Semgrep to only annotate the first parameter passed to \`sink\` as the sink, rather than the function \`sink\` itself. If taint goes into any other parameter of \`sink\`, then that is not considered a problem.  
373 |   
374 | \<iframe src="https://semgrep.dev/embed/editor?snippet=v83Nl" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
375 |   
376 | Anything that you can match with Semgrep can be made into a sink, like the index in an array access:  
377 |   
378 | \`\`\`javascript  
379 | pattern-sinks:  
380 |   \- patterns:  
381 |     \- pattern-inside: $ARRAY\[$SINK\]  
382 |     \- focus-metavariable: $SINK  
383 | \`\`\`  
384 |   
385 | :::note  
386 | If you specify a sink such as \`sink(...)\` then any tainted data passed to \`sink\`, through any of its arguments, results in a finding.  
387 |   
388 | \<iframe src="https://semgrep.dev/embed/editor?snippet=OrAAe" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
389 | :::  
390 |   
391 | \#\#\# At-exit sinks (Pro) 🧪  
392 |   
393 | \*\*At-exit taint sinks is a Semgrep Pro feature.\*\*  
394 |   
395 | At-exit sinks are meant to facilitate writing leak-detection rules using taint mode. By setting \`at-exit: true\` you can restrict a sink specification to only match at "exit" statements, that is statements after which the control-flow will exit the function being analyzed.  
396 |   
397 | \`\`\`  
398 | pattern-sinks:  
399 | \- pattern-either:  
400 |   \- pattern: return ...  
401 |   \- pattern: $F(...)  
402 |   at-exit: true  
403 | \`\`\`  
404 |   
405 | The above sink pattern matches either \`return\` statements (which are always "exit" statements), or function calls occurring as "exit" statements.  
406 |   
407 | Unlike regular sinks, at-exit sinks trigger a finding if any tainted l-value reaches the location of the sink. For example, the at-exit sink specification above will trigger a finding at a \`return 0\` statement if some tainted l-value reaches the \`return\`, even if \`return 0\` itself is not tainted. The location itself is the sink rather than the code that is at that location.  
408 |   
409 | You can use this, for example, to check that file descriptors are being closed within the same function where they were opened.  
410 |   
411 | \<iframe src="https://semgrep.dev/embed/editor?snippet=OrAzB" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
412 |   
413 | The \`print(content)\` statement is reported because the control flow exits the function at that point, and the file has not been closed.  
414 |   
415 | \#\# Propagators (Pro)  
416 |   
417 | \*\*Custom taint propagators is a Semgrep Pro feature.\*\*  
418 |   
419 | By default, tainted data automatically propagates through assignments, operators, and function calls (from inputs to output). However, there are other ways in which taint can propagate, which can require language or library-specific knowledge that Semgrep does not have built-in.  
420 |   
421 | A taint propagator requires a pattern to be specified. Like in a search-mode rule, you can start this pattern with one of the following keys: \`pattern\`, \`patterns\`, \`pattern-either\`, \`pattern-regex\`.  
422 |   
423 | A propagator also needs to specify the origin (\`from\`) and the destination (\`to\`) of the taint to be propagated.  
424 |   
425 | | Field      | Type                       | Description                                                            |  
426 | | :----------|:------------------------- | :--------------------------------------------------------------------- |  
427 | | \`from\`     | metavariable              | Source of propagation. |  
428 | | \`to\`       | metavariable              | Destination of propagation. |  
429 |   
430 | In addition, taint propagators accept the following options:  
431 |   
432 | | Option            | Type                      | Default | Description                                                            |  
433 | | :-----------------|:------------------------- | :------ | :--------------------------------------------------------------------- |  
434 | | \`by-side-effect\`  | {\`false\`, \`true\`} | \`true\` | See \[\_Propagation without side-effect\_\](\#propagation-without-side-effect).               |  
435 |   
436 | For example, given the following propagator, if taint goes into the second argument of \`strcpy\`, its first argument will get the same taint:  
437 |   
438 | \`\`\`yaml  
439 | pattern-propagators:  
440 | \- pattern: strcpy($DST, $SRC)  
441 |   from: $SRC  
442 |   to: $DST  
443 | \`\`\`  
444 |   
445 | :::info  
446 | Taint propagators only work intra-procedurally, that is, within a function or method. You cannot use taint propagators to propagate taint across different functions/methods. Use \[inter-procedural analysis\](\#inter-procedural-analysis-pro).  
447 | :::  
448 |   
449 | \#\#\# Understanding custom propagators  
450 |   
451 | Consider the following Python code where an unsafe \`user\_input\` is stored into a \`set\` data structure. A random element from \`set\` is then passed into a \`sink\` function. This random element can be \`user\_input\` itself, leading to an injection vulnerability\!  
452 |   
453 | \`\`\`python  
454 | def test(s):  
455 |     x \= user\_input  
456 |     s \= set(\[\])  
457 |     s.add(x)  
458 |     \#ruleid: test  
459 |     sink(s.pop())  
460 | \`\`\`  
461 |   
462 | The following rule cannot find the above-described issue. The reason is that Semgrep is not aware that executing \`s.add(x)\` makes \`x\` one of the elements in the set data structure \`s\`.  
463 |   
464 | \`\`\`yaml  
465 | mode: taint  
466 | pattern-sources:  
467 | \- pattern: user\_input  
468 | pattern-sinks:  
469 | \- pattern: sink(...)  
470 | \`\`\`  
471 |   
472 | The use of \*\*taint propagators\*\* enables Semgrep to propagate taint in this and other scenarios.  
473 | Taint propagators are specified under the \`pattern-propagators\` key:  
474 |   
475 | \`\`\`yaml  
476 | pattern-propagators:  
477 | \- pattern: $S.add($E)  
478 |   from: $E  
479 |   to: $S  
480 | \`\`\`  
481 |   
482 | In the example above, Semgrep finds the pattern \`$S.add($E)\`, and it checks whether the code matched by \`$E\` is tainted. If it is tainted, Semgrep propagates that same taint to the code matched by \`$S\`. Thus, adding tainted data to a set marks the set itself as tainted.  
483 |   
484 | \<iframe src="https://semgrep.dev/embed/editor?snippet=dGRE" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
485 |   
486 | Note that \`s\` becomes tainted \_by side-effect\_ after \`s.add(x)\`, this is due to \`by-side-effect: true\` being the default for propagators, and because \`s\` is an l-value.  
487 |   
488 | In general, a taint propagator must specify:  
489 | 1\. A pattern containing \*\*two\*\* metavariables. These two metavariables specify where taint is propagated \*\*from\*\* and \*\*to\*\*.  
490 | 2\. The \`to\` and \`from\` metavariables. These metavariables should match an \*\*expression\*\*.  
491 |     \- The \`from\` metavariable specifies the entry point of the taint.  
492 |     \- The \`to\` metavariable specifies where the tainted data is propagated to, typically an object or data structure. If option \`by-side-effect\` is enabled (as it is by default) and the \`to\` metavariable matches an l-value, the propagation is side-effectful.  
493 |   
494 | In the example above, pattern \`$S.add($E)\` includes two metavariables \`$S\` and \`$E\`. Given \`from: $E\` and \`to: $S\`, and with \`$E\` matching \`x\` and \`$S\` matching \`s\`, when \`x\` is tainted then \`s\` becomes tainted (by side-effect) with the same taint as \`x\`.  
495 |   
496 | Another situation where taint propagators can be useful is to specify in Java that, when iterating a collection that is tainted, the individual elements must also be considered tainted:  
497 |   
498 | \`\`\`yaml  
499 | pattern-propagators:  
500 | \- pattern: $C.forEach(($X) \-\> ...)  
501 |   from: $C  
502 |   to: $X  
503 | \`\`\`  
504 |   
505 | \#\#\# Propagation without side-effect  
506 |   
507 | Taint propagators can be used in very imaginative ways, and in some cases you may not want taint to propagate by side-effect. This can be achieved by disabling \`by-side-effect\`, which is enabled by default.  
508 |   
509 | For example:  
510 |   
511 | \`\`\`yaml  
512 | pattern-propagators:  
513 |   \- patterns:  
514 |     \- pattern: |  
515 |         if something($FROM):  
516 |           ...  
517 |           $TO()  
518 |           ...  
519 |     from: $FROM  
520 |     to: $TO  
521 |     by-side-effect: false  
522 | \`\`\`  
523 |   
524 | The propagator above specifies that inside an \`if\` block, where the condition is \`something($FROM)\`, we want to propagate taint from \`$FROM\` to any function that is being called without arguments, \`$TO()\`.  
525 |   
526 | \<iframe src="https://semgrep.dev/embed/editor?snippet=4bv6x" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
527 |   
528 | Because the rule disables \`by-side-effect\`, the \`sink\` occurrence that is inside the \`if\` block is tainted, but this does not affect the \`sink\` occurrence outside the \`if\` block.  
529 |   
530 | \#\# Findings  
531 |   
532 | Taint findings are accompanied by a taint trace that explains how the taint flows from source to sink.  
533 |   
534 | \<\!-- \<iframe src="https://semgrep.dev/embed/editor?snippet=KxJRL" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\> \--\>  
535 |   
536 | \#\#\# Deduplication of findings  
537 |   
538 | Semgrep tracks all the possible ways that taint can reach a sink, but at present it only reports one taint trace among the possible ones. Click "Open in Playground" in the example below, run the example to get one finding, and then ask the Playground to visualize the dataflow of the finding. Even though \`sink\` can be tainted via \`x\` or via \`y\`, the trace will only show you one of these possibilities. If you replace \`x \= user\_input\` with \`x \= "safe"\`, then Semgrep will then report the taint trace via \`y\`.  
539 |   
540 | \<iframe src="https://semgrep.dev/embed/editor?snippet=WAYzL" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
541 |   
542 | \#\#\# Report findings on the sources (Pro)  
543 |   
544 | \*\*Reporting findings on the source of taint is a Semgrep Pro feature.\*\*  
545 |   
546 | By default Semgrep reports taint findings at the location of the sink being matched. You must look at the taint trace to identify where the taint is coming from. It is also possible to make Semgrep report the findings at the location of the taint sources, by setting the \[rule-level option\](/writing-rules/rule-syntax/\#options) \`taint\_focus\_on\` to \`source\`. Then  
547 |   
548 | \`\`\`yaml  
549 | options:  
550 |   taint\_focus\_on: source  
551 | \`\`\`  
552 |   
553 | \<iframe src="https://semgrep.dev/embed/editor?snippet=JDPGP" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
554 |   
555 | The \[deduplication of findings\](\#deduplication-of-findings) still applies in this case. While Semgrep will now report all the taint sources, if a taint source can reach multiple sinks, the taint trace will only inform you about one of them.  
556 |   
557 | \#\# Minimizing false positives  
558 |   
559 | The following \[rule options\](/writing-rules/rule-syntax/\#options) can be used to minimize false positives:  
560 |   
561 | | Rule option                   | Default | Description                                                            |  
562 | | :-----------------------------| :------ | :--------------------------------------------------------------------- |  
563 | | \`taint\_assume\_safe\_booleans\`  | \`false\` | Boolean data is never considered tainted (works better with type annotations). |  
564 | | \`taint\_assume\_safe\_numbers\`   | \`false\` | Numbers (integers, floats) are never considered tainted (works better with type annotations). |  
565 | | \`taint\_assume\_safe\_indexes\`   | \`false\` | An index expression \`I\` tainted does not make an access expression \`E\[I\]\` tainted (it is only tainted if \`E\` is tainted). |  
566 | | \`taint\_assume\_safe\_functions\` | \`false\` | A function call like \`F(E)\` is not considered tainted even if \`E\` is tainted. (When using Pro's \[inter-procedural taint analysis\](\#inter-procedural-analysis-pro), this only applies to functions for which Semgrep cannot find a definition.)  |  
567 | | \`taint\_only\_propagate\_through\_assignments\` 🧪 | \`false\` | Disables all implicit taint propagation except for assignments. |  
568 |   
569 | \#\#\# Restrict taint by type (Pro)  
570 |   
571 | By enabling \`taint\_assume\_safe\_booleans\` Semgrep automatically sanitizes Boolean expressions when it can infer that the expression resolves to Boolean.  
572 |   
573 | For example, comparing a tainted string against a constant string will not be considered a tainted expression:  
574 |   
575 | \<iframe src="https://semgrep.dev/embed/editor?snippet=6JvzK" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
576 |   
577 | Similarly, enabling \`taint\_assume\_safe\_numbers\` Semgrep will automatically sanitize numeric expressions when it can infer that the expression is numeric.  
578 |   
579 | \<iframe src="https://semgrep.dev/embed/editor?snippet=oqjgX" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
580 |   
581 | You could define explicit sanitizers that clean the taint from Boolean or numeric expressions, but these options are more convenient and also more efficient.  
582 |   
583 | :::note  
584 | Semgrep Pro's ability to infer types for expressions varies depending on the language. For example, in Python type annotations are not always present, and the \`+\` operator can also be used to concatenate strings. Semgrep also ignores the types of functions and classes coming from third-party libraries.  
585 |   
586 | \<iframe src="https://semgrep.dev/embed/editor?snippet=zdjnn" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
587 | :::  
588 |   
589 | \#\#\# Assume tainted indexes are safe  
590 |   
591 | By default, Semgrep assumes that accessing an array-like object with a tainted index (that is, \`obj\[tainted\]\`) is itself a tainted \*\*expression\*\*, even if the \*\*object\*\* itself is not tainted. Setting \`taint\_assume\_safe\_indexes: true\` makes Semgrep assume that these expressions are safe.  
592 |   
593 | \<iframe src="https://semgrep.dev/embed/editor?snippet=X56pj" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
594 |   
595 | \#\#\# Assume function calls are safe  
596 |   
597 | :::note  
598 | We refer to a function call as \_opaque\_ when Semgrep does not have access to its definition, to examine it and determine its "taint behavior" (for example, whether the function call propagates or not any taint that comes through its inputs). In Semgrep Community Edition (CE), where taint analysis is intra-procedural, all function calls are opaque. In Semgrep Pro, with \[inter-procedural taint analysis\](\#inter-procedural-analysis-pro), an opaque function could be one coming from a third-party library.  
599 | :::  
600 |   
601 | By default Semgrep considers that an \_opaque\_ function call propagates any taint passed through any of its arguments to its output.  
602 |   
603 | For example, in the code below, \`some\_safe\_function\` receives tainted data as input, so Semgrep assumes that it also returns tainted data as output. As a result, a finding is produced.  
604 |   
605 | \`\`\`javascript  
606 | var x \= some\_safe\_function(tainted);  
607 | sink(x); // undesired finding here  
608 | \`\`\`  
609 |   
610 | This can generate false positives, and for certain rules on certain codebases it can produce a high amount of noise.  
611 |   
612 | Setting \`taint\_assume\_safe\_functions: true\` makes Semgrep assume that opaque function calls are safe and do not propagate any taint. If it is desired that specific functions do propagate taint, then that can be achieved via custom propagators:  
613 |   
614 | \<iframe src="https://semgrep.dev/embed/editor?snippet=gBD0" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
615 |   
616 | \#\#\# Propagate only through assignments 🧪  
617 |   
618 | Setting \`taint\_only\_propagate\_through\_assignments: true\` makes Semgrep to only propagate taint through trivial assignments of the form \`\<l-value\> \= \<tainted-expression\>\`. It requires the user to be explicit about any other kind of taint propagation that is to be performed.  
619 |   
620 | For example, neither \`unsafe\_function(tainted)\` nor \`tainted\_string \+ "foo"\` will be considered tainted expressions:  
621 |   
622 | \<iframe src="https://semgrep.dev/embed/editor?snippet=bwekv" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
623 |   
624 | \#\# Metavariables, rule message, and unification  
625 |   
626 | The patterns specified by \`pattern-sources\` and \`pattern-sinks\` (and \`pattern-sanitizers\`) are all independent of each other. If a metavariable used in \`pattern-sources\` has the same name as a metavariable used in \`pattern-sinks\`, these are still different metavariables.  
627 |   
628 | In the message of a taint-mode rule, you can refer to any metavariable bound by \`pattern-sinks\`, as well as to any metavariable bound by \`pattern-sources\` that does not conflict with a metavariable bound by \`pattern-sinks\`.  
629 |   
630 | Semgrep can also treat metavariables with the same name as the \_same\_ metavariable, simply set \`taint\_unify\_mvars: true\` using rule \`options\`. Unification enforces that whatever a metavariable binds to in each of these operators is, syntactically speaking, the \*\*same\*\* piece of code. For example, if a metavariable binds to a code variable \`x\` in the source match, it must bind to the same code variable \`x\` in the sink match. In general, unless you know what you are doing, avoid metavariable unification between sources and sinks.  
631 |   
632 | The following example demonstrates the use of source and sink metavariable unification:  
633 |   
634 | \<iframe src="https://semgrep.dev/embed/editor?snippet=G652" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
635 |   
636 | \#\# Inter-procedural analysis (Pro)  
637 |   
638 | \*\*Inter-procedural taint analysis is a Semgrep Pro feature.\*\*  
639 |   
640 | \[Semgrep\](/semgrep-pro-vs-oss/) can perform inter-procedural taint analysis, that is, to track taint across multiple functions.  
641 |   
642 | In the example below, \`user\_input\` is passed to \`foo\` as input and, from there, flows to the sink at line 3, through a call chain involving three functions. Semgrep is able to track this and report the sink as tainted. Semgrep also provides an inter-procedural taint trace that explains how exactly \`user\_input\` reaches the \`sink(z)\` statement (click "Open in Playground" then click "dataflow" in the "Matches" panel).  
643 |   
644 | \<iframe src="https://semgrep.dev/embed/editor?snippet=PeBXv" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
645 |   
646 | Using the CLI option \`--pro-intrafile\`, Semgrep will perform inter-procedural (across functions) \_intra\_-file (within one file) analysis. That is, it will track taint across functions, but it will not cross file boundaries. This is supported for essentially every language, and performance is very close to that of intra-procedural taint analysis.  
647 |   
648 | Using the CLI option \`--pro\`, Semgrep will perform inter-procedural (across functions) as well as \*inter\*-file (across files) analysis. Inter-file analysis is only supported for \[a subset of languages\](/supported-languages\#language-maturity-summary). For a rule to run interfile it also needs to set \`interfile: true\`:  
649 |   
650 | \`\`\`yaml  
651 | options:  
652 |   interfile: true  
653 | \`\`\`  
654 |   
655 | \*\*Memory requirements for inter-file analysis:\*\*  
656 | While interfile analysis is more powerful, it also demands more memory resources. The Semgrep team advises a minimum of 4 GB of memory per core, but \*\*recommend 8 GB per core or more\*\*. The amount of memory needed depends on the codebase and on the number of interfile rules being run.  
657 |   
658 | \#\# Taint mode sensitivity  
659 |   
660 | \#\#\# Field sensitivity  
661 |   
662 | The taint engine provides basic field sensitivity support. It can:  
663 |   
664 | \- Track that \`x.a.b\` is tainted, but \`x\` or \`x.a\` is  \*\*not\*\* tainted. If \`x.a.b\` is tainted, any extension of \`x.a.b\` (such as \`x.a.b.c\`) is considered tainted by default.  
665 | \- Track that \`x.a\` is tainted, but remember that \`x.a.b\` has been sanitized. Thus the engine records that \`x.a.b\` is \*\*not\*\* tainted, but \`x.a\` or \`x.a.c\` are still tainted.  
666 |   
667 | :::note  
668 | The taint engine does track taint \*\*per variable\*\* and not \*\*per object in memory\*\*. The taint engine does not track aliasing at present.  
669 | :::  
670 |   
671 | \<iframe src="https://semgrep.dev/embed/editor?snippet=5rvkj" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
672 |   
673 | \#\#\# Index sensitivity (Pro)  
674 |   
675 | \*\*Index sensitivity is a Semgrep Pro feature.\*\*  
676 |   
677 | Semgrep Pro has basic index sensitivity support:  
678 | \- Only for accesses using the built-in \`a\[E\]\` syntax.  
679 | \- Works for \_statically constant\_ indexes that may be either integers (e.g. \`a\[42\]\`) or strings (e.g. \`a\["foo"\]\`).  
680 | \- If an arbitrary index \`a\[i\]\` is sanitized, then every index becomes clean of taint.  
681 |   
682 | \<iframe src="https://semgrep.dev/embed/editor?snippet=GdoK6" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
683 |   
684 | \#\# Taint labels (Pro) 🧪  
685 |   
686 | Taint labels increase the expressiveness of taint analysis by allowing you to specify and track different kinds of tainted data in one rule using labels. This functionality has various uses, for example, when data becomes dangerous in several steps that are hard to specify through single pair of source and sink.  
687 |   
688 | \<iframe class="yt\_embed" width="100%" height="432px" src="https://www.youtube.com/embed/lAbJdzMUR4k" frameborder="0" allowfullscreen\>\</iframe\>  
689 |   
690 | To include taint labels into a taint mode rule, follow these steps:  
691 |   
692 | 1\. Attach a \`label\` key to the taint source. For example, \`label: TAINTED\` or \`label: INPUT\`. See the example below:  
693 |     \`\`\`yaml  
694 |       pattern-sources:  
695 |         \- pattern: user\_input  
696 |           label: INPUT  
697 |     \`\`\`  
698 |     Semgrep accepts any valid Python identifier as a label.  
699 |   
700 | 2\. Restrict a taint source to a subset of labels using the \`requires\` key. Extending the previous example, see the \`requires: INPUT\` below:  
701 |     \`\`\`yaml  
702 |         pattern-sources:  
703 |           \- pattern: user\_input  
704 |             label: INPUT  
705 |           \- pattern: evil(...)  
706 |             requires: INPUT  
707 |             label: EVIL  
708 |     \`\`\`  
709 |     Combine labels using the \`requires\` key. To combine labels, use Python Boolean operators. For example: \`requires: LABEL1 and not LABEL2\`.  
710 |   
711 | 3\. Use the \`requires\` key to restrict a taint sink in the same way as source:  
712 |     \`\`\`yaml  
713 |         pattern-sinks:  
714 |           \- pattern: sink(...)  
715 |             requires: EVIL  
716 |     \`\`\`  
717 |   
718 | :::info  
719 | \- Semgrep accepts valid Python identifiers as labels.  
720 | \- Restrict a source to a subset of labels using the \`requires\` key. You can combine more labels in the \`requires\` key using Python Boolean operators. For example: \`requires: LABEL1 and not LABEL2\`.  
721 | \- Restrict a sink also. The extra taint is only produced if the source itself is tainted and satisfies the \`requires\` formula.  
722 | :::  
723 |   
724 | In the example below, let's say that \`user\_input\` is dangerous but only when it passes through the \`evil\` function. This can be specified with taint labels as follows:  
725 |   
726 | \<iframe src="https://semgrep.dev/embed/editor?snippet=PwKY" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
727 |   
728 | \<\!--  
729 | TODO: For some reason the embedded editor doesn't like the rule, even though the Playground can run it.  
730 |   
731 | Interestingly, you can (ab)use taint labels to write some \[typestate analyses\](https://en.wikipedia.org/wiki/Typestate\_analysis)\!  
732 |   
733 | \<iframe src="https://semgrep.dev/embed/editor?snippet=DYxo" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
734 | \--\>  
735 |   
736 | \#\#\# Multiple \`requires\` expressions in taint labels  
737 |   
738 | You can assign an independent \`requires\` expression to each metavariable matched by a sink. Given \`$OBJ.foo($ARG)\` you can easily require that \`$OBJ\` has some label \`XYZ\` and \`$ARG\` has some label TAINTED, and at the same time \`focus-metavariable: $ARG\`:  
739 |   
740 | \`\`\`  
741 | pattern-sinks:  
742 |   \- patterns:  
743 |       \- pattern: $OBJ.foo($SINK, $ARG)  
744 |       \- focus-metavariable: $SINK  
745 |     requires:  
746 |       \- $SINK: BAD  
747 |       \- $OBJ: AAA  
748 |       \- $ARG: BBB  
749 | \`\`\`  
750 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/experiments/aliengrep.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | slug: aliengrep  
  3 | append\_help\_link: true  
  4 | description: "Aliengrep is a variant of the generic mode that is more configurable than spacegrep."  
  5 | title: Aliengrep  
  6 | hide\_title: true  
  7 | \---  
  8 |   
  9 |   
 10 |   
 11 | \# Aliengrep  
 12 |   
 13 | :::caution  
 14 | This is an experimental matching mode for Semgrep Community Edition (CE). Many of the features described in this document are subject to change. Your feedback is important and helps us, the Semgrep team, to make desirable adjustments. You can file an issue in our \[Semgrep CE GitHub repository\](https://github.com/semgrep/semgrep/issues) or ask us anything in \<a href="https://go.semgrep.dev/slack"\>Semgrep Community Slack group\</a\>.  
 15 | :::  
 16 |   
 17 | Aliengrep is an alternative to the \[generic pattern-matching engine\](/writing-rules/generic-pattern-matching) for analyzing files written in any language. The pattern syntax resembles the usual Semgrep pattern syntax. This document provides a reference to the syntactic features that Aliengrep supports.  
 18 |   
 19 | \#\# Minimal example  
 20 |   
 21 | Specify that a rule uses the Aliengrep engine by setting \`options.generic\_engine: aliengrep\`. See the Semgrep rule example below:  
 22 |   
 23 | \`\`\`yaml  
 24 | rules:  
 25 | \- id: example  
 26 |   severity: WARNING  
 27 |   languages: \[generic\]  
 28 |   options:  
 29 |     generic\_engine: aliengrep  
 30 |   message: "found the word 'hello'"  
 31 |   pattern: "hello"  
 32 | \`\`\`  
 33 |   
 34 | :::note  
 35 | We are considering a dedicated field \`analyzer: aliengrep\` instead of \`options.generic\_engine: aliengrep\`.  
 36 | :::  
 37 |   
 38 | \#\# Pattern syntax  
 39 |   
 40 | The following sections provide descriptions and examples of operators that Aliengrep uses in YAML rule files.  
 41 |   
 42 | \#\#\# Whitespace  
 43 |   
 44 | The whitespace between lexical elements is ignored. By default, whitespace includes spaces, tabs, and newlines. The single-line mode restricts whitespace to only spaces and tabs (see \[Single-line mode\](\#single-line-mode) section below).  
 45 |   
 46 | Lexical elements in target input are:  
 47 |   
 48 | \* words (configurable)  
 49 | \* brace pairs (configurable)  
 50 | \* single non-word characters  
 51 |   
 52 | \#\#\# Metavariables  
 53 |   
 54 | A metavariable captures a single word in the target input. By default, the set of word characters is \`\[A-Za-z\_0-9\]\`. The pattern \`$THING\` matches a whole word such as \`hello\` or \`world\` if the target input is \`hello, world.\`.  
 55 |   
 56 | \`\`\`yaml  
 57 | rules:  
 58 | \- id: example  
 59 |   severity: WARNING  
 60 |   languages: \[generic\]  
 61 |   options:  
 62 |     generic\_engine: aliengrep  
 63 |   message: "found a word"  
 64 |   pattern: "$THING"  
 65 | \`\`\`  
 66 |   
 67 | Repeating a metavariable (back-reference) requires a match of the same sequence that was matched by the first occurrence of the metavariable. For example, the pattern \`$A ... $A\` matches \`a x y a\`, assigning \`a\` to the metavariable \`A\`. It does not match \`a x b\`.  
 68 |   
 69 | \#\#\# Ellipsis (\`...\`)  
 70 |   
 71 | In Semgrep rule syntax, an ellipsis is a specific pattern written as three dots \`...\`. Ellipsis matches a sequence of any lexical elements. Matching ellipses is lazy or shortest-match-first. For example, the pattern \`a ... b\` matches \`a x b\` rather than \`a x b b\` if the target input is \`a x b b c\`.  
 72 |   
 73 | Ellipses at the beginning or at the end of a pattern are anchored. For example, ellipses must match the beginning or the end of the target input, respectively. For example, \`...\` alone matches the whole input and \`a ...\` matches the whole input starting from the first occurrence of the word \`a\`.  
 74 |   
 75 | \#\#\# Ellipsis metavariable (capturing ellipsis)  
 76 |   
 77 | An ellipsis metavariable \`$...X\` matches the same contents as an ordinary ellipsis \`...\` but additionally captures the contents and assigns them to the metavariable \`X\`.  
 78 |   
 79 | Repeating a metavariable ellipsis such as in \`$...A, $...A\` requires the same contents to be matched by each repetition, including the same whitespace. This is an unfortunate limitation of the implementation. For example, \`$...A, $...A\` matches \`1 2, 1 2\` and \`1   2, 1   2\` but it doesn't match \`1 2, 1   2\`.  
 80 |   
 81 | \#\#\# Single-line mode  
 82 |   
 83 | Se the single-line mode with \`options.generic\_multiline: false\` in rule files:  
 84 |   
 85 | \`\`\`yaml  
 86 | rules:  
 87 | \- id: single-line-example  
 88 |   severity: WARNING  
 89 |   languages: \[generic\]  
 90 |   options:  
 91 |     generic\_engine: aliengrep  
 92 |     generic\_multiline: false  
 93 |   message: "found a password field"  
 94 |   pattern: "password: ..."  
 95 | \`\`\`  
 96 |   
 97 | Now instead of matching everything until the end of the target input file, the pattern \`password: ...\` stops the match at the end of the line. In single-line mode, a regular ellipsis \`...\` or its named variant \`$...X\` cannot span multiple lines.  
 98 |   
 99 | Another feature of the single-line mode is that newlines in rule patterns must match literally. For example, the following YAML rule contains a two-line pattern:  
100 |   
101 | \`\`\`yaml  
102 | rules:  
103 | \- id: single-line-example2  
104 |   severity: WARNING  
105 |   languages: \[generic\]  
106 |   options:  
107 |     generic\_engine: aliengrep  
108 |     generic\_multiline: false  
109 |   message: "found a password field"  
110 |   pattern: "a\\nb"  
111 | \`\`\`  
112 |   
113 | The pattern \`"a\\nb"\` in the YAML rule file matches the following code:  
114 |   
115 | \`\`\`  
116 | x a  
117 | b x  
118 | \`\`\`  
119 |   
120 | The pattern does not match if there is another number of newlines between \`a\` and \`b\`. The single-line mode does not match the following target input:  
121 |   
122 | \`\`\`  
123 | x a b x  
124 | \`\`\`  
125 |   
126 | It does however match in the default multiline mode of Aliengrep.  
127 |   
128 | :::caution  
129 | YAML syntax makes it easy to introduce significant newline characters in patterns without realizing it. When in doubt and for better clarity, use the quoted string syntax \`"a\\nb"\` as we did in the preceding example. This ensures no trailing newline is added accidentally when using the single-line mode.  
130 | :::  
131 |   
132 | \#\#\# Long ellipsis (\`....\`)  
133 |   
134 | A long ellipsis (written as four dots, \`....\`) and its capturing variant \`$....X\` matches a sequence of any lexical elements even in single-line mode. It's useful for skipping any number of lines in single-line mode.  
135 |   
136 | In multiline mode, a regular ellipsis (three dots \`...\`) has the same behavior as a long ellipsis (four dots \`....\`).  
137 |   
138 | :::note  
139 | We wonder if the visual difference between \`...\` and \`....\` is too subtle. Let us know if you have ideas for a better syntax than four dots \`....\`.  
140 | :::  
141 |   
142 | \#\#\# Additional word characters captured by metavariables  
143 |   
144 | In the generic modes, a metavariable captures a word. The default pattern followed by a word is \`\[A-Za-z\_0-9\]+\` (a sequence of one or more alphanumeric characters or underscores). The set of characters that comprise a word can be configured as an option in the Semgrep rule as follows:  
145 |   
146 | \`\`\`yaml  
147 | rules:  
148 | \- id: custom-word-chars  
149 |   severity: WARNING  
150 |   languages: \[generic\]  
151 |   options:  
152 |     generic\_engine: aliengrep  
153 |     generic\_extra\_word\_characters: \["+", "/", "="\]  
154 |   message: "found something"  
155 |   pattern: "data \= $DATA;"  
156 | \`\`\`  
157 |   
158 | The preceding example allows matching Base64-encoded data such as in the following target input:  
159 |   
160 | \`\`\`  
161 | data \= bGlnaHQgd29yaw==;  
162 | \`\`\`  
163 |   
164 | There's currently no option to remove word characters from the default  
165 | set.  
166 |   
167 | \#\#\# Custom brackets  
168 |   
169 | The Aliengrep engine performs brace matching as expected in English text. The default brace pairs are parentheses (\`()\`), square brackets (\`\[\]\`), and curly braces (\`{}\`). In single-line mode, ASCII single quotes and double quotes are also treated like brace pairs by default. The following rule demonstrates the addition of \`\<\>\` as an extra pair of braces by specifying \`options.generic\_extra\_braces\`:  
170 |   
171 | \`\`\`yaml  
172 | rules:  
173 | \- id: edgy-brackets  
174 |   severity: WARNING  
175 |   languages: \[generic\]  
176 |   options:  
177 |     generic\_engine: aliengrep  
178 |     generic\_extra\_braces: \[\["\<", "\>"\]\]  
179 |   message: "found something"  
180 |   pattern: "x ... x"  
181 | \`\`\`  
182 |   
183 | This pattern matches the \`x \<x\> x\` in the following target input:  
184 | \`\`\`  
185 | a x \<x\> x a  
186 | \`\`\`  
187 |   
188 | Without declaring \`\<\>\` as braces, the rule would match only \`x \<x\`.  
189 |   
190 | The set of brace pairs can be completely replaced by using the field \`options.generic\_braces\` as follows:  
191 |   
192 | \`\`\`yaml  
193 | rules:  
194 | \- id: edgy-brackets-only  
195 |   severity: WARNING  
196 |   languages: \[generic\]  
197 |   options:  
198 |     generic\_engine: aliengrep  
199 |     generic\_braces: \[\["\<", "\>"\]\]  
200 |   message: "found something"  
201 |   pattern: "x ... x"  
202 | \`\`\`  
203 |   
204 | \#\#\# Case-insensitive matching  
205 |   
206 | Some languages are case-insensitive according to Unicode rules (UTF-8 encoding). To deal with this, Aliengrep offers an option for case-insensitive matching \`options.generic\_caseless: true\`.  
207 |   
208 | \`\`\`yaml  
209 | rules:  
210 | \- id: caseless  
211 |   severity: WARNING  
212 |   languages: \[generic\]  
213 |   options:  
214 |     generic\_engine: aliengrep  
215 |     generic\_multiline: false  
216 |     generic\_caseless: true  
217 |   message: "found something"  
218 |   pattern: "Content-Type: $...CT"  
219 | \`\`\`  
220 |   
221 | This rule matches \`Content-Type: text/html\` but also \`content-type: text/html\` or \`CONTENT-TyPe: text/HTML\` among all the possible variants.  
222 |   
223 | :::caution  
224 | Back-referencing a metavariable requires an exact repeat of the text captured by the metavariable, even in caseless mode. For example, \`$X $X\` matches \`ab ab\` and \`AB AB\` but not \`ab AB\`.  
225 | :::  
226 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/experiments/deprecated-experiments.md:  
\--------------------------------------------------------------------------------  
  1 | \# Deprecated experiments  
  2 |   
  3 | \#\# Equivalences  
  4 |   
  5 | :::note  
  6 | This feature was deprecated in Semgrep v0.61.0.  
  7 | :::  
  8 |   
  9 | Equivalences enable defining equivalent code patterns (i.e. a commutative property: \`$X \+ $Y \<==\> $Y \+ $X\`). Equivalence rules use the \`equivalences\` top-level key and one \`equivalence\` key for each equivalence.  
 10 |   
 11 | For example:  
 12 |   
 13 | \<iframe src="https://semgrep.dev/embed/editor?snippet=jNnn" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
 14 |   
 15 |   
 16 | \#\# Extract mode  
 17 |   
 18 | :::danger Deprecation notice  
 19 | As of Semgrep 1.65.0, extract mode has been deprecated and removed from Semgrep. This feature may return in the future.  
 20 | :::  
 21 |   
 22 | Extract mode enables you to run existing rules on subsections of files where the rule language is different than the language of the file. For example, running a JavaScript rule on code contained inside of script tags in an HTML document.  
 23 |   
 24 | \#\#\# Example of extract mode  
 25 |   
 26 | Without extract mode, writing rules to validate template, Markdown or configuration files which contain code in another language can be burdensome and require significant rule duplication.  
 27 |   
 28 | Let's take the following Bash rule as an example (a simplified version of the \[\`curl-eval\`\](https://github.com/semgrep/semgrep-rules/blob/release/bash/curl/security/curl-eval.yaml) rule from the Semgrep Registry):  
 29 |   
 30 | \`\`\`yaml  
 31 | rules:  
 32 |   \- id: curl-eval  
 33 |     severity: WARNING  
 34 |     languages:  
 35 |       \- bash  
 36 |     message: Evaluating data from a \`curl\` command is unsafe.  
 37 |     mode: taint  
 38 |     pattern-sources:  
 39 |       \- pattern: |  
 40 |           $(curl ...)  
 41 |       \- pattern: |  
 42 |           \`curl ...\`  
 43 |     pattern-sinks:  
 44 |       \- pattern: eval ...  
 45 | \`\`\`  
 46 |   
 47 | Usually, Semgrep uses this rule only against Bash files. However, a project might contain Dockerfiles or Python scripts that invoke Bash commands\&mdash;without an extract mode rule, Semgrep does \*\*not\*\* run any Bash rules against commands contained in files of different languages.  
 48 |   
 49 | However, with extract mode, you can provide Semgrep with instructions on how to extract any Bash commands used in a Docker \`RUN\` instruction or as an argument to Python's \`os.system\` standard library function.  
 50 |   
 51 | \`\`\`yaml  
 52 | rules:  
 53 |   \- id: extract-docker-run-to-bash  
 54 |     mode: extract  
 55 |     languages:  
 56 |       \- dockerfile  
 57 |     pattern: RUN $...CMD  
 58 |     extract: $...CMD  
 59 |     dest-language: bash  
 60 |   \- id: extract-python-os-system-to-bash  
 61 |     mode: extract  
 62 |     languages:  
 63 |       \- python  
 64 |     pattern: os.system("$CMD")  
 65 |     extract: $CMD  
 66 |     dest-language: bash  
 67 | \`\`\`  
 68 |   
 69 | By adding the extract mode rules as shown in the previous code snippet, Semgrep matches Bash code contained in the following Python file and reports the contained Bash as matching against the \`curl-eval\` rule.  
 70 |   
 71 | \`\`\`python  
 72 | from os import system  
 73 |   
 74 | if system('eval \`curl \-s "http://www.very-secure-website.net"\`'):  
 75 |     print("Command failed\!")  
 76 | else:  
 77 |     print("Success")  
 78 | \`\`\`  
 79 |   
 80 | Likewise, if a query included a Dockerfile with an equivalent Bash command, Semgrep reports the contained Bash as matching against the \`curl-eval\` rule. See the following Dockerfile example that contains a Bash command:  
 81 |   
 82 | \`\`\`dockerfile  
 83 | FROM fedora  
 84 | RUN dnf install \-y unzip zip curl which  
 85 | RUN eval \`curl \-s "http://www.very-secure-website.net"\`  
 86 | \`\`\`  
 87 |   
 88 | \#\#\# Extract mode rule schema  
 89 |   
 90 | Extract mode rules \*\*require\*\* the following \[usual Semgrep rule keys\](/writing-rules/rule-syntax/\#required):  
 91 |   \- \`id\`  
 92 |   \- \`languages\`  
 93 |   \- One of \`pattern\`, \`patterns\`, \`pattern-either\`, or \`pattern-regex\`  
 94 |   
 95 | Extract mode rules \*\*also require\*\* two additional fields:  
 96 |   \- \`extract\`  
 97 |   \- \`dest-language\`  
 98 |   
 99 | Extract mode has two \*\*optional\*\* fields:  
100 |   \- \`reduce\`  
101 |   \- \`json\`  
102 |   
103 | The fields specific to extract mode are further explained in the sections below.  
104 |   
105 | \#\#\#\# \`extract\`  
106 |   
107 | The \`extract\` key is required in extract mode. The value must be a metavariable appearing in your pattern(s). Semgrep uses the code bound to the metavariable for subsequent queries of non-extract mode rules targeting \`dest-language\`.  
108 |   
109 | \#\#\#\# \`dest-language\`  
110 |   
111 | The \`dest-language\` key is required in extract mode. The value must be a \[language tag\](/writing-rules/rule-syntax/\#language-extensions-and-languages-key-values).  
112 |   
113 | \#\#\#\# \`transform\`  
114 |   
115 | The \`transform\` is an optional key in the extract mode. The value of this key specifies whether the extracted content is parsed as raw source code or as a JSON array.  
116 |   
117 | The value of \`transform\` key must be one of the following:  
118 | \<dl\>  
119 |     \<dt\>\<code\>no\_transform\</code\>\</dt\>  
120 |     \<dd\>\<p\>Extract the matched content as raw source code. This is the \<b\>default\</b\> value.\</p\>\</dd\>  
121 |     \<dt\>\<code\>concat\_json\_string\_array\</code\>\</dt\>  
122 |     \<dd\>\<p\>Extract the matched content as a JSON array. Each element of the array correspond to a line the resulting source code. This value is useful in extracting code from JSON formats such as Jupyter Notebooks.\</p\>\</dd\>  
123 | \</dl\>  
124 |   
125 | \#\#\#\# \`reduce\`  
126 |   
127 | The \`reduce\` key is optional in extract mode. The value of this key specifies a method to combine the ranges extracted by a single rule within a file.  
128 |   
129 | The value of \`reduce\` key must be one of the following:  
130 | \<dl\>  
131 |     \<dt\>\<code\>separate\</code\>\</dt\>  
132 |     \<dd\>\<p\>Treat all matched ranges as separate units for subsequent queries. This is the \<b\>default\</b\> value.\</p\>\</dd\>  
133 |     \<dt\>\<code\>concat\</code\>\</dt\>  
134 |     \<dd\>\<p\>Concatenate all matched ranges together and treat this result as a single unit for subsequent queries.\</p\>\</dd\>  
135 | \</dl\>  
136 |   
137 |   
138 | \#\#\# Limitations of extract mode  
139 |   
140 | Although extract mode supports JSON array decoding with the \`json\` key, it does not support other additional processing for the extracted text, such as unescaping strings.  
141 |   
142 | While extract mode can help to enable rules which try and track taint across a language boundary within a file, taint rules cannot have a source and sink split across the original file and extracted text.  
143 |   
144 | \#\# Turbo Mode  
145 |   
146 | :::note   
147 | As of June 16th, 2025, Turbo Mode has been deprecated and removed from the Semgrep Playground.   
148 | :::  
149 |   
150 | Turbo Mode was a feature in Semgrep Editor that automatically ran your rule against Semgrep CE after every keystroke or change to the rule.   
151 |   
152 |   
153 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/experiments/display-propagated-metavariable.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: display-propagated-metavariable  
 3 | append\_help\_link: true  
 4 | description: "This document provides information about experimental syntax addition to \[Displaying matched metavariable in rule message\](/writing-rules/pattern-syntax/\#display-matched-metavariable-in-rule-message). Semgrep enables you to display values of matched metavariables in rule messages. However, in some cases, the matched value of the metavariable is not the real value you were looking for."  
 5 | \---  
 6 |   
 7 | \# Displaying propagated value of metavariables  
 8 |   
 9 | This document provides information about experimental syntax supplement to \[Display matched metavariables in rule messages\](/writing-rules/pattern-syntax\#display-matched-metavariables-in-rule-messages). Semgrep enables you to display values of matched metavariables in rule messages. However, in some cases, the matched value of the metavariable is not the real value you were looking for.  
10 |   
11 | See the following rule message and part of a Semgrep rule (formula):  
12 |   
13 | \`\`\`yaml  
14 | \- message: \>-  
15 |   Creating a buffer using $X  
16 | \- patterns:  
17 |    \- pattern: byte\[\] buf \= new byte\[$X\];  
18 |    \- metavariable-comparison:  
19 |         metavariable: $X  
20 |         comparison: $X \< 2048  
21 | \`\`\`  
22 |   
23 | Testing code:  
24 |   
25 | \`\`\`java  
26 | int size \= 512;  
27 | byte\[\] buf \= new byte\[size\];  
28 | \`\`\`  
29 |   
30 | Semgrep matches this code because it performs constant propagation. Therefore, Semgrep recognizes that the value of \`size\` is \`512\`. Consequently, Semgrep evaluates that the buffer size is less than \`2048\`. But what is the value of \`$X\`?  
31 |   
32 | If the rule message states \`Creating a buffer using $X\`, the resulting message output is not helpful in this particular case:  
33 |   
34 | \`\`\`  
35 | Creating a buffer using size  
36 | \`\`\`  
37 |   
38 | This is caused by the value of \`$X\` within the code, which is \`size\`. However, the underlying value of \`size\` is \`512\`. The goal of the rule message is to access this underlying value in our message.  
39 |   
40 | To retrieve the correct value in the case described above, use \`value($X)\` in the rule message (for example (\`Creating a buffer using value($X)\`). Semgrep replaces the \`value($X)\` with the underlying propagated value of the metavariable \`$X\` if it computes one (otherwise, Semgrep uses the matched value).  
41 |   
42 | :::info  
43 | Regular Semgrep syntax for displaying matched metavariables in rule messages is for example \`$X\`. For specific propagated values, use experimental syntax \`value($X)\` instead. For more information about the standard syntax, see \[Displaying matched metavariables in rule messages\](/writing-rules/pattern-syntax\#display-matched-metavariables-in-rule-messages).  
44 | :::  
45 |   
46 | Run the following example in Semgrep Playground to see the message (click \*\*Open in Editor\*\*, and then \*\*Run\*\*, unroll the \*\*1 Match\*\* to see the message):  
47 |   
48 | \<iframe title="Metavariable value in message example" src="https://semgrep.dev/embed/editor?snippet=Dr0G" width="100%" height="432" frameborder="0"\>\</iframe\>  
49 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/experiments/introduction.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | id: introduction  
 3 | slug: introduction  
 4 | title: Introduction  
 5 | hide\_title: true  
 6 | append\_help\_link: true  
 7 | description: "Introduction of Semgrep experiments that also documents that some experiments can sunset or become GA, which means that particular documents can change their position in docs also."  
 8 | \---  
 9 |   
10 | \# Introduction to Semgrep experiments  
11 |   
12 | The experiments category documents experimental features and the way you can use them. In the future, as it is the nature of experiments, some of these experiments can become deprecated, and others can become generally available (GA), meaning that GA features are fully supported parts of Semgrep. If a feature is deprecated, its documentation is moved to the \[Deprecated experiments\](/writing-rules/experiments/deprecated-experiments) document. If a feature becomes GA, its docs are moved to a relevant category outside of the experiments section.  
13 |   
14 | Enjoy the experiments, tweak the code, and most importantly share your thoughts\! If you see any issues with the experimental features, please \[file a bug\](https://github.com/semgrep/semgrep/issues/new/choose).  
15 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/experiments/join-mode/overview.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | id: overview  
  3 | append\_help\_link: true  
  4 | description: "Join mode runs several Semgrep rules at once and only returns results if certain conditions on the results are met."  
  5 | \---  
  6 |   
  7 | \# Join mode overview  
  8 |   
  9 | Join mode runs several Semgrep rules at once and only returns results if certain conditions on the results are met. Join mode is an experimental mode that lets you cross file boundaries, allowing you to write rules for whole code bases instead of individual files. As the name implies, this was inspired by join clauses in SQL queries.  
 10 |   
 11 | Think of join mode like this: distinct Semgrep rules are used to gather information about a code base. Then, the conditions you define are used to select specific results from these rules, and the selected results are reported by Semgrep. You can join results on metavariable contents or on the result's file path.  
 12 |   
 13 | :::info  
 14 | You can also use cross-file (interfile) analysis. For more information, see \[\<i class="fa-regular fa-file-lines"\>\</i\> Perform cross-file analysis\](/semgrep-code/semgrep-pro-engine-intro). Cross-file analysis is preferred over join mode where either of the two are feasible. Neither is currently available in Semgrep CE.  
 15 | :::  
 16 |   
 17 | \#\# Example  
 18 |   
 19 | Here’s an example join mode rule that detects a cross-site scripting (XSS) vulnerability with high precision.  
 20 |   
 21 | \`\`\`yaml  
 22 | rules:  
 23 | \- id: flask-likely-xss  
 24 |   mode: join  
 25 |   join:  
 26 |     refs:  
 27 |       \- rule: flask-user-input.yaml  
 28 |         as: user-input  
 29 |       \- rule: unescaped-template-extension.yaml  
 30 |         as: unescaped-extensions  
 31 |       \- rule: any-template-var.yaml  
 32 |         renames:  
 33 |         \- from: '$...EXPR'  
 34 |           to: '$VAR'  
 35 |         as: template-vars  
 36 |     on:  
 37 |     \- 'user-input.$VAR \== unescaped-extensions.$VALUE'  
 38 |     \- 'unescaped-extensions.$VAR \== template-vars.$VAR'  
 39 |     \- 'unescaped-extensions.$PATH \> template-vars.path'  
 40 |   message: |  
 41 |     Detected a XSS vulnerability: '$VAR' is rendered  
 42 |     unsafely in '$PATH'.  
 43 |   severity: ERROR  
 44 | \`\`\`  
 45 |   
 46 | Let's explore how this works. First, some background on the vulnerability. Second, we'll walk through the join mode rule.  
 47 |   
 48 | \*\*Vulnerability background\*\*  
 49 |   
 50 | In Flask, templates are only HTML-escaped if the \[template file ends with the \`.html\` extension\](https://flask.palletsprojects.com/en/2.0.x/templating/\#jinja-setup). Therefore, detecting these two conditions present in a Flask application is a high indicator of  
 51 |   
 52 | 1\. User input directly enters a template without the \`.html\` extension  
 53 | 2\. The user input is directly rendered in the template  
 54 |   
 55 | \*\*Join mode rule explanation\*\*  
 56 |   
 57 | Now, let's turn these conditions into the join mode rule.  We need to find three code patterns:  
 58 |   
 59 | 1\. User input  
 60 | 2\. Templates without the \`.html\` extension  
 61 | 3\. Variables rendered in a template  
 62 |   
 63 | We can write individual Semgrep rules for each of these code patterns.  
 64 |   
 65 | \`\`\`yaml  
 66 | rules:  
 67 | \- id: flask-user-input  
 68 |   languages: \[python\]  
 69 |   severity: INFO  
 70 |   message: $VAR  
 71 |   pattern: '$VAR \= flask.request.$SOMETHING.get(...)'  
 72 | \`\`\`  
 73 |   
 74 | \`\`\`yaml  
 75 | rules:  
 76 | \- id: unescaped-template-extension  
 77 |   message: |  
 78 |     Flask does not automatically escape Jinja templates unless they have  
 79 |     .html as an extension. This could lead to XSS attacks.  
 80 |   patterns:  
 81 |   \- pattern: flask.render\_template("$PATH", ..., $VAR=$VALUE, ...)  
 82 |   \- metavariable-pattern:  
 83 |       metavariable: $PATH  
 84 |       language: generic  
 85 |       patterns:  
 86 |       \- pattern-not-regex: .\*\\.html$  
 87 |   languages: \[python\]  
 88 |   severity: WARNING  
 89 | \`\`\`  
 90 |   
 91 | \`\`\`yaml  
 92 | rules:  
 93 | \- id: any-template-var  
 94 |   languages: \[generic\]  
 95 |   severity: INFO  
 96 |   message: '$...EXPR'  
 97 |   pattern: '{{ $...EXPR }}'  
 98 | \`\`\`  
 99 |   
100 | Finally, we want to "join" the results from these together. Below are the join conditions, in plain language.  
101 |   
102 | 1\. The variable \`$VAR\` from \`flask-user-input\` has the same content as the value \`$VALUE\` from \`unescaped-template-extension\`  
103 | 2\. The keyword argument \`$VAR\` from \`unescaped-template-extension\` has the same content as \`$...EXPR\` from \`any-template-var\`  
104 | 3\. The template file name \`$PATH\` from \`unescaped-template-extension\` is a substring of the file path of a result from \`any-template-var\`  
105 |   
106 | We can translate these roughly into the following condition statements.  
107 |   
108 | \`\`\`  
109 | \- 'user-input.$VAR \== unescaped-extensions.$VALUE'  
110 | \- 'unescaped-extensions.$VAR \== template-vars.$VAR'  
111 | \- 'unescaped-extensions.$PATH \> template-vars.path'  
112 | \`\`\`  
113 |   
114 | Combining the three code pattern Semgrep rules and the three conditions gives us the join rule at the top of this section. This rule matches the code displayed below.  
115 |   
116 |   
117 | \!\[Screenshot of code the join rule matches\](/img/join-mode-example.png)  
118 |   
119 |   
120 | \`\`\`bash  
121 | \> semgrep \-f flask-likely-xss.yaml  
122 | running 1 rules...  
123 | running 3 rules...  
124 | ran 3 rules on 16 files: 14 findings  
125 | matching...  
126 | matching done.  
127 | ./templates/launch.htm.j2  
128 | severity:error rule:flask-likely-xss: Detected a XSS vulnerability: '$VAR' is rendered unsafely in '$PATH'.  
129 | 9:	\<li\>person\_name\_full is \<b\>{{ person\_name\_full }}\</b\>\</li\>  
130 | \`\`\`  
131 |   
132 | \*\*Helpers\*\*  
133 |   
134 | For convenience, when writing a join mode rule, you can use the \`renames\` and \`as\` keys.  
135 |   
136 | The \`renames\` key lets you rename metavariables from one rule to something else in your conditions. \*\*This is necessary for named expressions, e.g., \`$...EXPR\`.\*\*  
137 |   
138 | The \`as\` key behaves similarly to \`AS\` clauses in SQL. This lets you rename the result set for use in the conditions. If the \`as\` key is not specified, the result set uses the \*\*rule ID\*\*.  
139 |   
140 | \#\# Syntax  
141 |   
142 | \#\#\# \`join\`  
143 |   
144 | The \`join\` key is required when in join mode. This is just a top-level key that groups the join rule parts together.  
145 |   
146 | \#\#\#\# Inline rule example  
147 |   
148 | The following rule attempts to detect cross-site scripting in a Flask application by checking whether a template variable is rendered unsafely through Python code.  
149 |   
150 | \`\`\`yaml  
151 | rules:  
152 | \- id: flask-likely-xss  
153 |   mode: join  
154 |   join:  
155 |     rules:  
156 |       \- id: user-input  
157 |         pattern: |  
158 |           $VAR \= flask.request.$SOMETHING.get(...)  
159 |         languages: \[python\]  
160 |       \- id: unescaped-extensions  
161 |         languages: \[python\]  
162 |         patterns:  
163 |         \- pattern: |  
164 |             flask.render\_template("$TEMPLATE", ..., $KWARG=$VAR, ...)  
165 |         \- metavariable-pattern:  
166 |             metavariable: $TEMPLATE  
167 |             language: generic  
168 |             patterns:  
169 |             \- pattern-not-regex: .\*\\.html$  
170 |       \- id: template-vars  
171 |         languages: \[generic\]  
172 |         pattern: |  
173 |           {{ $VAR }}  
174 |     on:  
175 |     \- 'user-input.$VAR \== unescaped-extensions.$VAR'  
176 |     \- 'unescaped-extensions.$KWARG \== template-vars.$VAR'  
177 |     \- 'unescaped-extensions.$TEMPLATE \< template-vars.path'  
178 |   message: |  
179 |     Detected a XSS vulnerability: '$VAR' is rendered  
180 |     unsafely in '$TEMPLATE'.  
181 |   severity: ERROR  
182 | \`\`\`  
183 |   
184 | The required fields under the \`rules\` key are the following:  
185 | \- \`id\`  
186 | \- \`languages\`  
187 | \- A set of \`pattern\` clauses.  
188 |   
189 | The optional fields under the \`rules\` key are the following:  
190 | \- \`message\`  
191 | \- \`severity\`  
192 |   
193 | :::note  
194 | Refer to the metavariables captured by the rule in the \`on\` conditions by the rule \`id\`. For inline rules, aliases do \*\*not\*\* work.  
195 | :::  
196 |   
197 | \#\#\# \`refs\`  
198 |   
199 | Short for references, \`refs\` is a list of external rules that make up your code patterns. Each entry in \`refs\` is an object with the required key \`rule\` and optional keys \`renames\` and \`as\`.  
200 |   
201 | \#\#\# \`rule\`  
202 |   
203 | Used with \`refs\`, \`rule\` points to an external rule location to use in this join rule. Even though Semgrep rule files can typically contain multiple rules under the \`rules\` key, join mode \*\*only uses the first rule in the provided file\*\*.  
204 |   
205 | Anything that works with \`semgrep \--config \<here\>\` also works as the value for \`rule\`.  
206 |   
207 | \#\#\# \`renames\`  
208 |   
209 | An optional key for an object in \`refs\`, \`renames\` renames the metavariables from the associated \`rule\`. The value of \`renames\` is a list of objects whose keys are \`from\` and \`to\`. The \`from\` key specifies the metavariable to rename, and the \`to\` key specifies the new name of the metavariable.  
210 |   
211 | :::warning  
212 | Renaming is necessary for named expressions, e.g., \`$...EXPR\`.  
213 | :::  
214 |   
215 | \#\#\# \`as\`  
216 |   
217 | An optional key for an object in \`refs\`, \`as\` lets you specify an alias for the results collected by this rule for use in the \`on\` conditions. Without the \`as\` key, the default name for the results collected by this rule is the rule ID of the rule in \`rule\`. If you use \`as\`, the results can be referenced using the alias specified by \`as\`.  
218 |   
219 | \#\#\# \`on\`  
220 |   
221 | The \`on\` key is required in join mode. This is where the join conditions are listed. The value of \`on\` is a list of strings which have the format:  
222 |   
223 | \`\`\`  
224 | \<result\_set\>.\<property\> \<operator\> \<result\_set\>.\<property\>  
225 | \`\`\`  
226 |   
227 | \`result\_set\` is the name of the result set produced by one of the \`refs\`. See the \`as\` key for more information.  
228 |   
229 | \`property\` is either a metavariable, such as \`$VAR\`, or the keyword \`path\`, which returns the path of the finding.  
230 |   
231 | \`operator\` is one of the following.  
232 |   
233 | | Operator | Example | Description |  
234 | | \-------- | \------- | \----------- |  
235 | | \`==\`   |  \`secret-env-var.$VALUE \== log-statement.$FORMATVAR\` | Matches when the contents of both sides are exactly equal. |  
236 | | \`\!=\`   | \`url-allowlist.$URL \!= get-request.$URL\` | Matches when the contents of both sides are not equal. |  
237 | | \`\<\`    | \`template-var.path \< unsafe-template.$PATH\` | Matches when the right-hand side is a substring of the left-hand side.  
238 | | \`\>\`    | \`unsafe-template.$PATH \> template-var.path\` | Matches when the left-hand side is a substring of the right-hand side. |  
239 |   
240 | \#\# Limitations  
241 |   
242 | Join mode \*\*is not taint mode\*\*\! While it can look on the surface like join mode is "connecting" things together, it is actually just creating sets for each Semgrep rule and returning all the results that meet the conditions. This means some false positives will occur if unrelated metavariable contents happen to have the same value.  
243 |   
244 | To use join mode with \`refs\`, you must define your individual Semgrep rules in independent locations. This can be anything that works with \`semgrep \--config \<here\>\`, such as a file, a URL, or a Semgrep registry pointer like \`r/java.lang.security.some.rule.id\`.  
245 |   
246 | Join mode requires login, and does not work in the Semgrep Playground or Semgrep Editor, as it is an experimental feature.  
247 |   
248 | Currently, join mode only reports the code location of the \*\*last finding that matches the conditions\*\*. Join mode parses the conditions from top-to-bottom, left-to-right. This means that findings from the "bottom-right" condition become the reported code location.  
249 |   
250 | \#\# More ideas  
251 |   
252 | Join mode effectively lets you ask questions of entire code bases. Here are some examples of the kinds of questions you can use join mode to answer.  
253 |   
254 | \- Do any of my dependencies use \`dangerouslySetInnerHTML\`, and do I directly import that dependency?  
255 | \- Does a key in this JSON file have a dangerous value, and do I load this JSON file and use the key in a dangerous function?  
256 | \- Is an unsafe variable rendered in an HTML template?  
257 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/experiments/join-mode/recursive-joins.md:  
\--------------------------------------------------------------------------------  
  1 | \# Recursive joins  
  2 |   
  3 | Join mode is an extension of Semgrep that runs multiple rules at once and only returns results if certain conditions are met. This is an experimental mode that enables you to cross file boundaries, allowing you to write rules for whole codebases instead of individual files. More information is available in \[Join mode overview\](/writing-rules/experiments/join-mode/overview).  
  4 |   
  5 | Recursive join mode has a recursive operator, \`--\>\`, which executes a recursive query on the given condition. This recursive operator allows you to write a Semgrep rule that effectively crawls the codebase on a condition you specify, letting you build chains such as function call chains or class inheritance chains.  
  6 |   
  7 | \#\# Understanding recursive join mode  
  8 |   
  9 | In the background, join rules turn captured metavariables into database table columns. For example, a rule with $FUNCTIONNAME, $FUNCTIONCALLED, and $PARAMETER is a table similar to the following:  
 10 |   
 11 | | $FUNCTIONNAME | $FUNCTIONCALLED | $PARAMETER   |  
 12 | |---------------|-----------------|--------------|  
 13 | | getName       | writeOutput     | user         |  
 14 | | getName       | lookupUser      | uid          |  
 15 | | lookupUser    | databaseQuery   | uid          |  
 16 |   
 17 | The join conditions then join various tables together and return a result if any rows match the criteria.  
 18 |   
 19 | Recursive join mode conditions use \[recursive joins\](https://www.sqlite.org/lang\_with.html\#recursive\_common\_table\_expressions) to construct a table that recursively joins with itself. For example, you can use a Semgrep rule that gets all function calls and join them recursively to approximate a callgraph.  
 20 |   
 21 | Consider the following Python script and rule.  
 22 |   
 23 | \`\`\`python  
 24 | def function\_1():  
 25 |     print("hello")  
 26 |     function\_2()  
 27 |   
 28 | def function\_2():  
 29 |     function\_4()  
 30 |   
 31 | def function\_3():  
 32 |     function\_5()  
 33 |   
 34 | def function\_4():  
 35 |     function\_5()  
 36 |   
 37 | def function\_5():  
 38 |     print("goodbye")  
 39 | \`\`\`  
 40 |   
 41 | \`\`\`yaml  
 42 | rules:  
 43 | \- id: python-callgraph  
 44 |   message: python callgraph  
 45 |   languages: \[python\]  
 46 |   severity: INFO  
 47 |   pattern: |  
 48 |     def $CALLER(...):  
 49 |       ...  
 50 |       $CALLEE(...)  
 51 | \`\`\`  
 52 |   
 53 | A join condition such as the following: \`python-callgraph.$CALLER \--\> python-callgraph.$CALLEE\` produces a table below. Notice how \`function\_1\` appears with \`function\_4\` and \`function\_5\` as callees, even though it is not directly called.  
 54 |   
 55 | | $CALLER  | $CALLEE  |  
 56 | |----------|----------|  
 57 | |function\_1|function\_2|  
 58 | |function\_1|function\_4|  
 59 | |function\_1|function\_5|  
 60 | |function\_1|print     |  
 61 | |function\_2|function\_4|  
 62 | |function\_2|function\_5|  
 63 | |function\_3|function\_5|  
 64 | |function\_4|function\_5|  
 65 | |function\_5|print     |  
 66 |   
 67 | \#\# Example rule  
 68 |   
 69 | It's important to think of a join mode rule as "asking questions about the whole project", rather than looking for a single pattern. For example, to find an SQL injection, you need to understand a few things about the project:  
 70 |   
 71 | 1\. Is there any user input?  
 72 | 1\. Do any functions manually build an SQL string using function input?  
 73 | 1\. Can the user input reach the function that manually builds the SQL string?  
 74 |   
 75 | Now, you can write individual Semgrep rules that gather information about each of these questions. This example uses \[Vulnado\](https://github.com/ScaleSec/vulnado) for finding an SQL injection. Vulnado is a Spring application.  
 76 |   
 77 | The first rule searches for user input into the Spring application. This rule also captures sinks that use a user-inputtable parameter as an argument.  
 78 |   
 79 | \`\`\`yaml  
 80 | rules:  
 81 | \- id: java-spring-user-input  
 82 |   message: user input  
 83 |   languages: \[java\]  
 84 |   severity: INFO  
 85 |   mode: taint  
 86 |   pattern-sources:  
 87 |   \- pattern: |  
 88 |       @RequestMapping(...)  
 89 |       $RETURNTYPE $USERINPUTMETHOD(..., $TYPE $PARAMETER, ...) {  
 90 |         ...  
 91 |       }  
 92 |   pattern-sinks:  
 93 |   \- patterns:  
 94 |     \- pattern: $OBJ.$SINK(...)  
 95 |     \- pattern: $PARAMETER  
 96 | \`\`\`  
 97 |   
 98 | A second rule looks for all methods in the application that build an SQL string with a method parameter.  
 99 |   
100 | \`\`\`yaml  
101 | rules:  
102 | \- id: method-parameter-formatted-sql  
103 |   message: method uses parameter for sql string  
104 |   languages: \[java\]  
105 |   severity: INFO  
106 |   patterns:  
107 |   \- pattern-inside: |  
108 |       $RETURNTYPE $METHODNAME(..., $TYPE $PARAMETER, ...) {  
109 |         ...  
110 |       }  
111 |   \- patterns:  
112 |     \- pattern-either:  
113 |       \- pattern: |  
114 |           "$SQLSTATEMENT" \+ $PARAMETER  
115 |       \- pattern: |  
116 |           String.format("$SQLSTATEMENT", ..., $PARAMETER, ...)  
117 |     \- metavariable-regex:  
118 |         metavariable: $SQLSTATEMENT  
119 |         regex: (?i)(select|delete|insert).\*  
120 | \`\`\`  
121 |   
122 | Finally, the third rule is used to construct a pseudo-callgraph:  
123 |   
124 | \`\`\`yaml  
125 | rules:  
126 | \- id: java-callgraph  
127 |   languages: \[java\]  
128 |   severity: INFO  
129 |   message: $CALLER calls $OBJ.$CALLEE  
130 |   patterns:  
131 |   \- pattern-inside: |  
132 |       $TYPE $CALLER(...) {  
133 |         ...  
134 |       }  
135 |   \- pattern: $OBJ.$CALLEE(...)  
136 | \`\`\`  
137 |   
138 | The join rule, is displayed as follows:  
139 |   
140 | \`\`\`yaml  
141 | rules:  
142 | \- id: spring-sql-injection  
143 |   message: SQLi  
144 |   severity: ERROR  
145 |   mode: join  
146 |   join:  
147 |     refs:  
148 |     \- rule: rule\_parts/java-spring-user-input.yaml  
149 |       as: user-input  
150 |     \- rule: rule\_parts/method-parameter-formatted-sql.yaml  
151 |       as: formatted-sql  
152 |     \- rule: rule\_parts/java-callgraph.yaml  
153 |       as: callgraph  
154 |     on:  
155 |     \- 'callgraph.$CALLER \--\> callgraph.$CALLEE'  
156 |     \- 'user-input.$SINK \== callgraph.$CALLER'  
157 |     \- 'callgraph.$CALLEE \== formatted-sql.$METHODNAME'  
158 | \`\`\`  
159 |   
160 | The \`on:\` conditions, in order, read as follows:  
161 | \- Recursively generate a pseudo callgraph on $CALLER to $CALLEE.  
162 | \- Match when a method with user input has a $SINK that is the $CALLER in the pseudo-callgraph.  
163 | \- Match when the $CALLEE is the $METHODNAME of a method that uses a parameter to construct an SQL string.  
164 |   
165 | Running this on Vulnado produces tables that look like this:  
166 |   
167 | |$RETURNTYPE |$USERINPUTMETHOD |$TYPE      |$PARAMETER  |$OBJ     |$SINK       |  
168 | |------------|-----------------|-----------|------------|---------|------------|  
169 | |...         |...              |...        | ...        |...      |...         |  
170 | |LoginResponse|login           |LoginRequest|input      |user     |token       |  
171 | |LoginResponse|login           |LoginRequest|input      |User     |getUser     |  
172 | |...         |...              |...        | ...        |...      |...         |  
173 |   
174 |   
175 | |$RETURNTYPE |$METHODNAME |$TYPE      |$PARAMETER  |$SQLSTATEMENT |  
176 | |------------|------------|-----------|------------|--------------|  
177 | |...         |...         |...        | ...        |...           |  
178 | |User        |fetch       |String     |un          |select \* from users where username \= '|  
179 | |...         |...         |...        | ...        |...           |  
180 |   
181 | |$CALLER    |$CALLEE    |  
182 | |-----------|-----------|  
183 | |...        |...        |  
184 | |login      |getUser    |  
185 | |login      |fetch      |  
186 | |getUser    |fetch      |  
187 | |...        |...        |  
188 |   
189 | The join conditions select rows which meet the conditions.  
190 |   
191 | \- Match when a method with user input has a $SINK that is the $CALLER in the pseudo-callgraph.  
192 |   
193 | |... |user-input.$SINK    |== |callgraph.$CALLER   |... |  
194 | |----|---------|---|----------|----|  
195 | |... |getUser  |== |getUser   |... |  
196 |   
197 | \- Match when the $CALLEE is the $METHODNAME of a method that uses a parameter to construct an SQL string.  
198 |   
199 | |...|callgraph.$CALLEE  |== |formatted-sql.$METHODNAME|...|  
200 | |---|---------|---|-----------|---|  
201 | |...|fetch    |== |fetch      |...|  
202 |   
203 | \`\`\`console  
204 | (semgrep) ➜  join\_mode\_demo semgrep \-f vulnado-sqli.yaml vulnado  
205 | Running 1 rules...  
206 | Running 3 rules...  
207 | 100%|██████████████████████████|3/3  
208 | ran 3 rules on 11 files: 158 findings  
209 | vulnado/src/main/java/com/scalesec/vulnado/User.java  
210 | rule:spring-sql-injection: SQLi  
211 | 55:      String query \= "select \* from users where username \= '" \+ un \+ "' limit 1";  
212 | ran 0 rules on 0 files: 1 findings  
213 | \`\`\`  
214 |   
215 | \#\# Limitations  
216 |   
217 | Join mode only works on the metavariable contents, which means it's fundamentally operating with text strings and not code constructs. There will be some false positives if similarly-named metavariables are extracted.  
218 |   
219 | \#\# Use cases  
220 |   
221 | \- Approximating callgraphs in a project  
222 | \- Approximating class inheritance  
223 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/experiments/metavariable-type.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: metavariable-type  
 3 | append\_help\_link: true  
 4 | description: "With this experimental field, Semgrep matches captured metavariables with specific types"  
 5 | \---  
 6 |   
 7 | \# Match captured metavariables with specific types  
 8 |   
 9 | The \`metavariable-type\` operator is used to compare metavariables against their types. It utilizes the \`type\` key to specify the string representation of the type expression in the target language. For example, you can use \`String\` for Java's String type and \`string\` for Go's string type. Optionally, the \`language\` key can be used to manually indicate the target language of the type expression.  
10 |   
11 | \`metavariable-type\` provides several advantages over typed metavariables. Firstly, it removes the requirement for users to memorize special syntax for defining typed metavariables in various target languages. Moreover, \`metavariable-type\` enables users to extract type expressions from the pattern expression and include them in other conditional filters for metavariables. This improves the readability of rules and promotes better organization of the code.  
12 |   
13 | For instance, the following rule that identifies potentially unsafe usage of the referential equality operator when comparing String objects in Java:  
14 | \`\`\`yaml  
15 | rules:  
16 |   \- id: no-string-eqeq  
17 |     severity: WARNING  
18 |     message: Avoid using the referential equality operator when comparing String objects  
19 |     languages:  
20 |       \- java  
21 |     patterns:  
22 |       \- pattern-not: null \== (String $Y)  
23 |       \- pattern: $X \== (String $Y)  
24 | \`\`\`  
25 |   
26 | can be modified to the following rule:  
27 | \`\`\`yaml  
28 | rules:  
29 |   \- id: no-string-eqeq  
30 |     severity: WARNING  
31 |     message: Avoid using the referential equality operator when comparing String objects  
32 |     languages:  
33 |       \- java  
34 |     patterns:  
35 |       \- pattern-not: null \== $Y  
36 |       \- pattern: $X \== $Y  
37 |       \- metavariable-type:  
38 |           metavariable: $Y  
39 |           type: String  
40 | \`\`\`  
41 |   
42 | \#\# Supported languages  
43 |   
44 | The \`metavariable-type\` operator can be used for the following languages:  
45 |   
46 | \- C  
47 | \- C\#  
48 | \- C++  
49 | \- Go  
50 | \- Java   
51 | \- Julia  
52 | \- Kotlin  
53 | \- Move On Aptos  
54 | \- Move On Sui  
55 | \- PHP  
56 | \- Python   
57 | \- Rust  
58 | \- Scala  
59 | \- TypeScript  
60 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/experiments/multiple-focus-metavariables.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: multiple-focus-metavariables  
 3 | append\_help\_link: true  
 4 | description: "With this rule, Semgrep matches all pieces of code captured by the focus metavariables."  
 5 | \---  
 6 |   
 7 | \# Including multiple focus metavariables using set union semantics  
 8 |   
 9 | Semgrep matches all pieces of code captured by focus metavariables when you specify them in a rule. Specify the metavariables you want to focus on in a YAML list format.  
10 |   
11 | :::info  
12 | This feature is using \`focus-metavariable\`, see \[\`focus-metavariable\`\](/writing-rules/rule-syntax/\#focus-metavariable) documentation for more information.  
13 | :::  
14 |   
15 | There are two ways in which you can include multiple focus metavariables:  
16 |   
17 | \- \*\*Set union\*\*: Experimental feature described below in the section \[Set union\](\#set-union). This feature returns the union of all matches of the specified metavariables.  
18 | \- \*\*Set intersection\*\*: Only matches the overlapping region of all the focused code. For more information, see \[Including more focus metavariables using set intersection semantics\](/writing-rules/rule-syntax/\#including-multiple-focus-metavariables-using-set-intersection-semantics).  
19 |   
20 | \#\# Set union  
21 |   
22 | For example, there is a pattern that binds several metavariables. You want to produce matches focused on two or more of these metavariables. If you specify a list of metavariables under \`focus-metavariable\`, each focused metavariable matches code independently of the others.  
23 |   
24 | \`\`\`yaml  
25 |     patterns:  
26 |       \- pattern: foo($X, ..., $Y)  
27 |       \- focus-metavariable:   
28 |         \- $X  
29 |         \- $Y  
30 | \`\`\`  
31 |   
32 | This syntax enables Semgrep to match these metavariables regardless of their position in code. See the following example:  
33 |   
34 | \<iframe src="https://semgrep.dev/embed/editor?snippet=D602" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
35 |   
36 | :::tip  
37 | Among many use cases, the \*\*set union\*\* syntax allows you to simplify taint analysis rule writing. For example, see the following rule:  
38 | \<iframe src="https://semgrep.dev/embed/editor?snippet=w6Qx" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
39 | :::  
40 |   
41 | \<\!-- Once this feature is no longer experimental, move the text under the \#\#\# \`focus-metavariable\` to docs/writing-rules/rule-syntax.md and change the \# Using multiple focus metavariables header to level 4 (\#\#\#\#) \--\>  
42 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/experiments/pattern-syntax.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | slug: pattern-syntax  
  3 | title: Pattern syntax (Experimental)  
  4 | hide\_title: true  
  5 | description: Learn how to use Semgrep's experimental pattern syntax to search code for a specific code pattern.  
  6 | tags:  
  7 |   \- Rules  
  8 |   \- Semgrep Code  
  9 | \---  
 10 |   
 11 |   
 12 | \#\# Pattern syntax (experimental)  
 13 |   
 14 | Patterns are the expressions Semgrep uses to match code when it scans for vulnerabilities. This article describes the new syntax for Semgrep pattern operators. See \[Pattern syntax\](/writing-rules/pattern-syntax) for information on the existing pattern syntax.  
 15 |   
 16 | There is often a one-to-one translation from the existing syntax to the experimental syntax. These changes are marked with \<i class= "fa-solid fa-diamond"\>\</i\>. However, some changes are quite different. These changes are marked with \<i class="fa-solid fa-exclamation"\>\</i\>  
 17 |   
 18 | :::warning  
 19 | \* These patterns are \*\*experimental\*\* and subject to change.  
 20 | \* You can't mix and match existing pattern syntax with the experimental syntax.  
 21 | :::  
 22 |   
 23 | \#\# \<i class="fa-solid fa-exclamation"\>\</i\> \`pattern\`  
 24 |   
 25 | The \`pattern\` operator looks for code matching its expression in the existing syntax. However, \`pattern\` is no longer required when using the experimental syntax. For example, you can use \`...\` wherever \`pattern: "...\`\`\` appears. For example, you can omit \`pattern\` and write the following:  
 26 |   
 27 | \`\`\`yaml  
 28 | any:  
 29 |   \- "badthing1"  
 30 |   \- "badthing2"  
 31 |   \- "badthing3"  
 32 | \`\`\`  
 33 |   
 34 | or, for multi-line patterns  
 35 |   
 36 | \`\`\`yaml  
 37 | any:  
 38 |   \- |  
 39 |       manylines(  
 40 |         badthinghere($A)  
 41 |       )  
 42 |   \- |  
 43 |       orshort()  
 44 | \`\`\`  
 45 |   
 46 | You don't need double quotes for a single-line pattern when omitting the \`pattern\` key, but note that this can cause YAML parsing issues.  
 47 |   
 48 | As an example, the following YAML parses:  
 49 |   
 50 | \`\`\`yaml  
 51 | any:  
 52 |   \- "def foo(): ..."  
 53 | \`\`\`  
 54 |   
 55 | This, however, causes problems since \`:\` is also used to denote a YAML dictionary:  
 56 |   
 57 | \`\`\`yaml  
 58 | any:  
 59 |   \- def foo(): ...  
 60 | \`\`\`  
 61 |   
 62 | \#\#\# \<i class="fa-solid fa-diamond"\>\</i\> \`any\`  
 63 |   
 64 | Replaces \[pattern-either\](/writing-rules/rule-syntax/\#pattern-either). Matches any of the patterns specified.  
 65 |   
 66 | \`\`\`yaml  
 67 | any:  
 68 |   \- \<pat1\>  
 69 |   \- \<pat2\>  
 70 |     ...  
 71 |   \- \<patn\>  
 72 | \`\`\`  
 73 |   
 74 | \#\#\# \<i class="fa-solid fa-diamond"\>\</i\> \`all\`  
 75 |   
 76 | Replaces \[patterns\](/writing-rules/rule-syntax/\#patterns). Matches all of the patterns specified.  
 77 |   
 78 | \`\`\`yaml  
 79 | all:  
 80 |   \- \<pat1\>  
 81 |   \- \<pat2\>  
 82 |     ...  
 83 |   \- \<patn\>  
 84 | \`\`\`  
 85 |   
 86 | \#\#\# \<i class="fa-solid fa-diamond"\>\</i\> \`inside\`  
 87 |   
 88 | Replaces \[pattern-inside\](/writing-rules/rule-syntax/\#pattern-inside). Match any of the sub-patterns inside of the primary pattern.  
 89 |   
 90 | \`\`\`yaml  
 91 | inside:  
 92 |   any:  
 93 |     \- \<pat1\>  
 94 |     \- \<pat2\>  
 95 | \`\`\`  
 96 |   
 97 | Alternatively:  
 98 |   
 99 | \`\`\`yaml  
100 | any:  
101 |   \- inside: \<pat1\>  
102 |   \- inside: \<pat2\>  
103 | \`\`\`  
104 |   
105 | \#\#\# \<i class="fa-solid fa-diamond"\>\</i\> \`not\`  
106 |   
107 | Replaces \[pattern-not\](/writing-rules/rule-syntax/\#pattern-not). Accepts any pattern and does \*\*not\*\* match on those patterns.  
108 |   
109 | \`\`\`yaml  
110 | not:  
111 |   any:  
112 |     \- \<pat1\>  
113 |     \- \<pat2\>  
114 | \`\`\`  
115 |   
116 | Alternatively:  
117 |   
118 | \`\`\`yaml  
119 | all:  
120 |   \- not: \<pat1\>  
121 |   \- not: \<pat2\>  
122 | \`\`\`  
123 |   
124 | \#\#\# \<i class="fa-solid fa-diamond"\>\</i\> \`regex\`  
125 |   
126 | Replaces \[pattern-regex\](/writing-rules/rule-syntax/\#pattern-regex) Matches based on the regex provided.  
127 |   
128 | \`\`\`yaml  
129 | regex: "(.\*)"  
130 | \`\`\`  
131 |   
132 | \#\# Metavariables  
133 |   
134 | Metavariables are an abstraction to match code when you don't know the value or contents beforehand. They're similar to \[capture groups\](https://regexone.com/lesson/capturing\_groups) in regular expressions and can track values across a specific code scope. This  
135 | includes variables, functions, arguments, classes, object methods, imports,  
136 | exceptions, and more.  
137 |   
138 | Metavariables begin with a \`

      
    

      
    

      
      
      
    

      
      
      
    

     
                 Format: HTML                 Format: JSON                 Format: YAML                 Format: Text             

    

    

    

   

         and can only contain uppercase characters, \`\_\`, or digits. Names like \`$x\` or \`$some\_value\` are invalid. Examples of valid metavariables include \`$X\`, \`$WIDGET\`, or \`$USERS\_2\`.  
139 |   
140 | \#\#\# \<i class="fa-solid fa-exclamation"\>\</i\> \`where\`  
141 |   
142 | Unlike Semgrep's existing pattern syntax, the following operators no longer occur under \`pattern\` or \`all\`:  
143 |   
144 | \- \`metavariable-pattern\`  
145 | \- \`metavariable-regex\`  
146 | \- \`metavariable-comparison\`  
147 | \- \`metavariable-analysis\`  
148 | \- \`focus-metavariable\`  
149 |   
150 | These operators must occur within a \`where\` clause.  
151 |   
152 | A \`where\` clause is required in a pattern where you're using metavariable operators. It indicates that Semgrep should match based on the pattern if all the conditions are true.  
153 |   
154 | As an example, take a look at the following example:  
155 |   
156 | \`\`\`yaml  
157 | all:  
158 |   \- inside: |  
159 |       def $FUNC(...):  
160 |         ...  
161 |   \- |  
162 |       eval($X)  
163 | where:  
164 |   \- \<condition\>  
165 | \`\`\`  
166 |   
167 | Because the \`where\` clause is on the same indentation level as \`all\`, Semgrep understands that everything under \`where\` must be paired with the entire \`all\` pattern. As such, the results of the ranges matched by the \`all\` pattern are modified by the \`where\` pattern, and the output includes some final set of ranges that are matched.  
168 |   
169 | \#\#\# \<i class="fa-solid fa-diamond"\>\</i\> \`metavariable\`  
170 |   
171 | Replaces:  
172 |   
173 | \- \[metavariable-regex\](/writing-rules/rule-syntax/\#metavariable-regex)  
174 | \- \[metavariable-pattern\](/writing-rules/rule-syntax/\#metavariable-pattern)  
175 | \- \[metavariable-analysis\](/writing-rules/metavariable-analysis)  
176 |   
177 | This operator looks inside the metavariable for a match.  
178 |   
179 | \`\`\`yaml  
180 | ...  
181 | where:  
182 |   \- metavariable: $A  
183 |     regex: "(.\*)  
184 |   \- metavariable: $B  
185 |     patterns: |  
186 |       \- "foo($C)"  
187 |   \- metavariable: $D  
188 |     analyzer: entropy  
189 | \`\`\`  
190 |   
191 | \#\#\# \<i class="fa-solid fa-diamond"\>\</i\> \`comparison\`  
192 |   
193 | Replaces \[metavariable-comparison\](/writing-rules/rule-syntax/\#metavariable-comparison). Compares metavariables against a basic \[Python comparison\](https://docs.python.org/3/reference/expressions.html\#comparisons) expression.  
194 |   
195 | \`\`\`yaml  
196 | ...  
197 | where:  
198 |   \- comparison: $A \== $B  
199 | \`\`\`  
200 |   
201 | \#\#\# \<i class="fa-solid fa-diamond"\>\</i\> \`focus\`  
202 |   
203 | Replaces \[focus-metavariable\](/writing-rules/rule-syntax/\#focus-metavariable). Puts focus on the code region matched by a single metavariable or a list of metavariables.  
204 |   
205 | \`\`\`yaml  
206 | ...  
207 | where:  
208 |   \- focus: $A  
209 | \`\`\`  
210 |   
211 | \#\# \<i class="fa-solid fa-exclamation"\>\</i\> \`as-metavariable\`  
212 |   
213 | \> \`as-metavariable\` is only available in the new syntax.  
214 |   
215 | \`as-metavariable\` is a rule-writing feature that bridges the gap between metavariables and matches. Metavariables get access to things like \`metavariable-comparison\`, \`metavariable-regex\`, and \`metavariable-pattern\`, but you can’t use them on arbitrary matches. However, the \`as\` operator lets you embed arbitrary matches into metavariables, or bind arbitrary matches to a name.  
216 |   
217 | The syntax is as follows:  
218 |   
219 | \`\`\`yaml  
220 | all:  
221 |   \- pattern: |  
222 |     @decorator  
223 |     def $FUNC(...):  
224 |       ...  
225 |   as: $DECORATED\_FUNC  
226 | \`\`\`  
227 |   
228 | Since \`as\` appears in the same indentation as the \`pattern\`, Semgrep couples the two. This augmented \`pattern\` operator matches the enclosed pattern, but produces an environment where \`$DECORATED\_FUNC\` is bound to the match it corresponds to. So for instance, the following rule:  
229 |   
230 | \`\`\`yaml  
231 | match:  
232 |   pattern: |  
233 |     @decorator  
234 |     def $FUNC(...):  
235 |       ...  
236 |   as: $DECORATED\_FUNC  
237 | fix: |  
238 |   @another\_decorator  
239 |   $DECORATED\_FUNC  
240 | \`\`\`  
241 |   
242 | Allows you to capture the decorated function. You can then use it in, for example, autofix's metavariable or metavariable ellipses interpolation, where you express something like "rewrite X, but with Y."  
243 |   
244 | \#\# \<i class="fa-solid fa-exclamation"\>\</i\> Syntax search mode  
245 |   
246 | New syntax search mode rules must be nested underneath a top-level \`match\` key. For example:  
247 |   
248 | \`\`\`yaml  
249 | rules:  
250 |   \- id: find-bad-stuff  
251 |     severity: ERROR  
252 |     languages: \[python\]  
253 |     message: |  
254 |       Don't put bad stuff\!  
255 |     match:  
256 |       any:  
257 |         \- |  
258 |             eval(input())  
259 |         \- all:  
260 |             \- inside: |  
261 |                 def $FUNC(..., $X, ...):  
262 |                   ...  
263 |             \- |  
264 |                 eval($X)  
265 | \`\`\`  
266 |   
267 | \#\# \<i class="fa-solid fa-exclamation"\>\</i\> Taint mode  
268 |   
269 | The new syntax supports taint mode, and such roles no longer require \`mode: taint\` in the rule. Instead, everything must be nested under a top-level \`taint\` key.  
270 |   
271 | \`\`\`yaml  
272 | rules:  
273 |   \- id: find-bad-stuff  
274 |     severity: ERROR  
275 |     languages: \[python\]  
276 |     message: |  
277 |       Don't put bad stuff\!  
278 |     taint:  
279 |       sources:  
280 |         \- input()  
281 |       sinks:  
282 |         \- eval(...)  
283 |       propagators:  
284 |         \- pattern: |  
285 |             $X \= $Y  
286 |           from: $Y  
287 |           to: $X  
288 |       sanitizers:  
289 |         \- magiccleanfunction(...)  
290 | \`\`\`  
291 |   
292 | \#\#\# \<i class="fa-solid fa-diamond"\>\</i\> Taint mode key names  
293 |   
294 | The key names for the new syntax taint rules are as follows:  
295 |   
296 | \- \`pattern-sources\` \--\> sources  
297 | \- \`pattern-sinks\` \--\> sinks  
298 | \- \`pattern-propagators\` \--\> propagators  
299 | \- \`pattern-sanitizers\` \--\> sanitizers  
300 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/experiments/project-depends-on.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: r2c-internal-project-depends-on  
 3 | append\_help\_link: true  
 4 | description: "r2c-internal-project-depends-on lets Semgrep rules only return results if the project depends on a specific version of a third-party package."  
 5 | \---  
 6 |   
 7 | \# r2c-internal-project-depends-on  
 8 |   
 9 | This Semgrep rules key allows specifying third-party dependencies along with the semver (semantic version) range that should trigger the rule. The \`r2c-internal-project-depends-on\` filters the rule unless one of the children is matched by a manifest file or lockfile.   
10 |   
11 | We welcome external contributors to try out the key, but keep in mind there's no expectation of stability across releases yet. \*\*The API and behavior of this feature is subject to change\*\*.  
12 |   
13 | In the rules.yaml, specify \`r2c-internal-project-depends-on\` key either as a dependency, or a sequence of dependencies with \`depends-on-either\` key (see the example below).  
14 |   
15 | A dependency consists of three keys:  
16 |   
17 | \* \`namespace\`: The package registry where the third-party dependency is found.  
18 | \* \`package\`: The name of the third-party dependency as it appears in the manifest file or lockfile.  
19 | \* \`version\`: A semantic version range. Uses \[Python packaging specifiers\](https://packaging.pypa.io/en/latest/specifiers.html) which support almost all NPM operators, except for \`^\`.  
20 |   
21 | So a \`r2c-internal-project-depends-on\` key will either look like this:  
22 | \`\`\`yaml  
23 | r2c-internal-project-depends-on:  
24 |   namespace: ...  
25 |   package: ...  
26 |   version: ...  
27 | \`\`\`  
28 |   
29 | Or it can have the following layout with \`depends-on-either\`:  
30 |   
31 | \`\`\`yaml  
32 | r2c-internal-project-depends-on:  
33 |   depends-on-either:  
34 |     \- namespace: ...  
35 |       package: ...  
36 |       version: ...  
37 |     \- namespace: ...  
38 |       package: ...  
39 |       version: ...  
40 |     ...  
41 | \`\`\`  
42 |   
43 | \#\# Example  
44 |   
45 | Here is an example \`r2c-internal-project-depends-on\` rule that searches for a known vulnerable version of the AWS CLI from April 2017, but only reports the vulnerability if the \`s3\` module (where the vulnerability is located) is actually used:  
46 |   
47 | \`\`\`yaml  
48 | rules:  
49 | \- id: vulnerable-awscli-apr-2017  
50 |   severity: WARNING  
51 |   pattern-either:  
52 |   \- pattern: boto3.resource('s3', ...)  
53 |   \- pattern: boto3.client('s3', ...)  
54 |   r2c-internal-project-depends-on:  
55 |     namespace: pypi  
56 |     package: awscli  
57 |     version: "\<= 1.11.82"  
58 |   message: this version of awscli is subject to a directory traversal vulnerability in the s3 module  
59 |   languages: \[python\]  
60 | \`\`\`  
61 |   
62 | \#\# Findings of r2c-internal-project-depends-on  
63 |   
64 | Findings produced by rules with the \`r2c-internal-project-depends-on\` can be of two types: \_reachable\_ and \_nonreachable\_.  
65 |   
66 | \- A \_reachable\_ finding is one with both a dependency match and a pattern match: a vulnerable dependency was found and the vulnerable part of the dependency (according to the patterns in the rule) is used somewhere in the code.  
67 | \- An \_unreachable\_ finding is one with only a dependency match. Reachable findings are reported as coming from the code that was pattern matched. Unreachable findings are reported as coming from the manifest file or lockfile that was dependency matched. For both types of findings, Semgrep specifies whether they are unreachable or reachable along with all matched dependencies, in the \`extra\` field of Semgrep's JSON output, using the \`dependency\_match\_only\` and \`dependency\_matches\` fields, respectively.  
68 |   
69 | A finding is only considered reachable if the file containing the pattern match actually depends on the dependencies in the manifest file or lockfile containing the dependency match. A file depends on a manifest file or lockfile if it is the nearest manifest file or lockfile going up the directory tree.  
70 |   
71 | \#\# r2c-internal-project-depends-on language support   
72 |   
73 | | Language   | Namespace  | Scans dependencies from                                       |  
74 | |:---------- |:-----------|:--------------------------------------------------------------|  
75 | | C\#         | nuget      | \`packages.lock.json\`                                          |  
76 | | Dart       | pub        | \`pubspec.lock\`                                                |  
77 | | Elixir     | hex        | \`mix.lock\`                                                    |  
78 | | Go         | gomod      | \`go.mod\`                                                      |  
79 | | Java       | maven      | \`pom.xml\`                                                     |  
80 | | JavaScript | npm        | \`yarn.lock\`, \`package-lock.json\`, \`pnpm-lock.yaml\`            |  
81 | | PHP        | composer   | \`composer.lock\`                                               |  
82 | | Python     | pypi       | \`\*requirement\*.txt\`, \`Pipfile.lock\`, \`poetry.lock\`, \`uv.lock\` |  
83 | | Ruby       | gem        | \`Gemfile.lock\`                                                |  
84 | | Rust       | cargo      | \`Cargo.lock\`                                                  |  
85 | | Swift      | swiftpm    | package.swift                                                 |  
86 |   
87 | \#\# Limitations  
88 |   
89 | Dependency resolution uses the source of dependency information with the \*least amount of ambiguity\* available. For all supported languages except Java, the \*least amount of ambiguity\* provides a manifest file or lockfile, which lists exact version information for each dependency that a project uses. Dependency resolution does not scan, for example, \`package.json\` files, because they can contain version ranges. In the case of Java, Maven does not support the creation of manifest files, so \`pom.xml\` is the least ambiguous source of information we have, and we consider only dependencies listed with exact versions.  
90 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/experiments/symbolic-propagation.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: symbolic-propagation  
 3 | append\_help\_link: true  
 4 | description: "Symbolic propagation allows Semgrep to perform matching modulo variable assignments."  
 5 | \---  
 6 |   
 7 | \# Symbolic propagation  
 8 |   
 9 | Symbolic propagation allows Semgrep to perform matching modulo variable assignments. Consider the following Python code:  
10 |   
11 | \`\`\`python  
12 | import pandas  
13 |   
14 | def test1():  
15 |     \# ruleid: test  
16 |     pandas.DataFrame(x).index.set\_value(a, b, c)  
17 |   
18 | def test2():  
19 |     df \= pandas.DataFrame(x)  
20 |     ix \= df.index  
21 |     \# ruleid: test  
22 |     ix.set\_value(a, b, c)  
23 | \`\`\`  
24 |   
25 | If we tried to match the pattern \`pandas.DataFrame(...).index.set\_value(...)\` against the above code, Semgrep would normally match \`test1\` but not \`test2\`. It does not match \`test2\` because there are intermediate assignments, and Semgrep does not know that \`ix\` is equals to \`df.index\` or that \`df\` is equals to \`pandas.DataFrame(x)\`. If we wanted Semgrep to match such code, we had to be explicit about it.  
26 |   
27 | Symbolic propagation is a generalization of \[constant propagation\](/writing-rules/data-flow/constant-propagation) that addresses this limitation. It enables Semgrep to perform matching modulo variable assignments. Thus, Semgrep is then able to match both \`test1\` and \`test2\` with the same simple pattern. This feature needs to be enabled explicitly via rule \`options:\` by setting \`symbolic\_propagation: true\`.  
28 |   
29 | \<iframe src="https://semgrep.dev/embed/editor?snippet=JeBP" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
30 |   
31 | \#\# Limitations of symbolic propagation  
32 |   
33 | Currently, symbolic propagation does not cross branching boundaries, such as \`if\` clauses or loops. Consider the following Python code, adapted from the example shown above:  
34 |   
35 | \`\`\`python  
36 | import pandas  
37 |   
38 | def test1():  
39 |     \# ruleid: test  
40 |     pandas.DataFrame(x).index.set\_value(a, b, c)  
41 |   
42 | def test2():  
43 |     if (x \< 5):  
44 |         df \= pandas.DataFrame(x)  
45 |         pass  
46 |     ix \= df.index  
47 |     \# ruleid: test  
48 |     ix.set\_value(a, b, c)  
49 | \`\`\`  
50 |   
51 | In this case, even if \`symbolic\_propagation: true\` is used, Semgrep does not match \`test2\`, because the assignment of \`df\` to \`pandas.DataFrame(x)\` is not propagated over the conditional to the final two lines.  
52 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/generic-pattern-matching.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | append\_help\_link: true  
  3 | description: "Semgrep can match generic patterns in languages that it doesn’t support yet. You can use generic pattern matching for languages that do \*\*not\*\* have a parser, configuration files, or other structured data such as XML."  
  4 | tags:  
  5 |   \- Rule writing  
  6 | \---  
  7 |   
  8 | \# Generic pattern matching  
  9 |   
 10 | \<\!-- If you ever need to replace the examples below, a good way is to look  
 11 |      into the semgrep-rules repo under "generic" for an existing rule  
 12 |      that makes sense. \--\>  
 13 |   
 14 | \#\# Introduction  
 15 |   
 16 | Semgrep can match generic patterns in languages that it does \*\*not\*\* yet support. Use generic pattern matching for languages that do not have a parser, configuration files, or other structured data such as XML. Generic pattern matching can also be useful in files containing multiple languages even if the languages are otherwise supported, such as HTML with embedded JavaScript or PHP code. In those cases you can also consider \[Extract mode (experimental)\](/writing-rules/experiments/deprecated-experiments\#extract-mode), but generic patterns may be simpler and still effective.  
 17 |   
 18 | As an example of generic matching, consider this rule:  
 19 | \`\`\`yaml  
 20 | rules:  
 21 |   \- id: dynamic-proxy-scheme  
 22 |     pattern: proxy\_pass $SCHEME:// ...;  
 23 |     paths:  
 24 |       include:  
 25 |         \- "\*.conf"  
 26 |         \- "\*.vhost"  
 27 |         \- sites-available/\*  
 28 |         \- sites-enabled/\*  
 29 |     languages:  
 30 |       \- generic  
 31 |     severity: WARNING  
 32 |     message: \>-  
 33 |       The protocol scheme for this proxy is dynamically determined.  
 34 |       This can be dangerous if the scheme is injected by an  
 35 |       attacker because it may forcibly alter the connection scheme.  
 36 |       Consider hardcoding a scheme for this proxy.  
 37 |     metadata:  
 38 |       references:  
 39 |         \- https://github.com/yandex/gixy/blob/master/docs/en/plugins/ssrf.md  
 40 |       category: security  
 41 |       technology:  
 42 |         \- nginx  
 43 |       confidence: MEDIUM  
 44 | \`\`\`  
 45 |   
 46 | The above rule \[matches\](https://semgrep.dev/playground/r/generic.nginx.security.dynamic-proxy-scheme.dynamic-proxy-scheme) this code snippet:  
 47 |   
 48 | \`\`\`  
 49 | server {  
 50 |   listen              443 ssl;  
 51 |   server\_name         www.example.com;  
 52 |   keepalive\_timeout   70;  
 53 |   
 54 |   ssl\_certificate     www.example.com.crt;  
 55 |   ssl\_certificate\_key www.example.com.key;  
 56 |   
 57 |   location \~ /proxy/(.\*)/(.\*)/(.\*)$ {  
 58 |     \# ruleid: dynamic-proxy-scheme  
 59 |     proxy\_pass $1://$2/$3;  
 60 |   }  
 61 |   
 62 |   location \~\* ^/internal-proxy/(?\<proxy\_proto\>https?)/(?\<proxy\_host\>.\*?)/(?\<proxy\_path\>.\*)$ {  
 63 |     internal;  
 64 |   
 65 |     \# ruleid: dynamic-proxy-scheme  
 66 |     proxy\_pass $proxy\_proto://$proxy\_host/$proxy\_path ;  
 67 |     proxy\_set\_header Host $proxy\_host;  
 68 | }  
 69 |   
 70 |   location \~ /proxy/(.\*)/(.\*)/(.\*)$ {  
 71 |     \# ok: dynamic-proxy-scheme  
 72 |     proxy\_pass http://$1/$2/$3;  
 73 |   }  
 74 |   
 75 |   location \~ /proxy/(.\*)/(.\*)/(.\*)$ {  
 76 |     \# ok: dynamic-proxy-scheme  
 77 |     proxy\_pass https://$1/$2/$3;  
 78 |   }  
 79 | }  
 80 | \`\`\`  
 81 |   
 82 | Generic pattern matching has the following properties:  
 83 |   
 84 | \* A document is interpreted as a nested sequence of ASCII words, ASCII punctuation, and other bytes.  
 85 | \* \`...\` (ellipsis operator) allows skipping non-matching elements, up to 10 lines down the last match.  
 86 | \* \`$X\` (metavariable) matches any word.  
 87 | \* \`$...X\` (ellipsis metavariable) matches a sequence of words, up to 10 lines down the last match.  
 88 | \* Indentation determines primary nesting in the document.  
 89 | \* Common ASCII braces \`()\`, \`\[\]\`, and \`{}\` introduce secondary nesting but only within single lines. Therefore, misinterpreted or mismatched braces don't disturb the structure of the rest of document.  
 90 | \* The document must be at least as indented as the pattern: any indentation specified in the pattern must be honored in the document.  
 91 |   
 92 | \#\# Caveats and limitations of generic mode  
 93 |   
 94 | Semgrep can reliably understand the syntax of natively \[supported languages\](/supported-languages). The generic mode is useful for unsupported languages, and consequently brings specific limitations.  
 95 |   
 96 | :::caution  
 97 | The quality of results in the generic mode can vary depending on the language you use it for.  
 98 | :::  
 99 |   
100 | The generic mode works fine with any human-readable text, as long as it is primarily based on ASCII symbols. Since the generic mode does not understand the syntax of the language you are scanning, the quality of the result may differ from language to language or even depend on specific code. As a consequence, the generic mode works well for some languages, but it does not always give consistent results. Generally, it's possible or even easy to write code in weird ways that prevent generic mode from matching.  
101 |   
102 | \*\*Example\*\*: In XML, one can write \`&\#x48;&\#x65;&\#x6C;&\#x6C;&\#x6F\` instead of \`Hello\`. If a rule pattern in generic mode is \`Hello\`, Semgrep is unable to match the \`&\#x48;&\#x65;&\#x6C;&\#x6C;&\#x6F\`, unlike if it had full XML support.  
103 |   
104 | With respect to Semgrep operators and features:  
105 |   
106 | \* metavariable support is limited to capturing a single “word”, which is a token of the form \[A-Za-z0-9\_\]+. They can’t capture sequences of tokens such as hello, world (in this case there are 3 tokens: \`hello\`, \`,\`, and \`world\`).  
107 | \* the ellipsis operator is supported and spans at most 10 lines  
108 | \* pattern operators like either/not/inside are supported  
109 | \* inline regular expressions for strings (\`"=\~/word.\*/"\`) are not supported  
110 |   
111 | \#\# Troubleshooting  
112 |   
113 | \#\#\# Common pitfall \#1: not enough \`...\`  
114 |   
115 | Rule of thumb:  
116 | \> If the pattern commonly matches many lines, use \`... ...\` (20 lines), or \`... ... ...\` (30 lines) etc. to make sure to match all the lines.  
117 |   
118 | Here's an innocuous pattern that should match the call to a function \`f()\`:  
119 | \`\`\`  
120 | f(...)  
121 | \`\`\`  
122 | It matches the following code \[just fine\](https://semgrep.dev/s/9v9R):  
123 | \`\`\`  
124 | f(  
125 |   1,  
126 |   2,  
127 |   3,  
128 |   4,  
129 |   5,  
130 |   6,  
131 |   7,  
132 |   8,  
133 |   9  
134 | )  
135 | \`\`\`  
136 |   
137 | But it will \[fail\](https://semgrep.dev/s/1z6Q) here because the function arguments span more than 10 lines:  
138 | \`\`\`  
139 | f(  
140 |   1,  
141 |   2,  
142 |   3,  
143 |   4,  
144 |   5,  
145 |   6,  
146 |   7,  
147 |   8,  
148 |   9,  
149 |   10  
150 | )  
151 | \`\`\`  
152 |   
153 | The \[solution\](https://semgrep.dev/s/9v9R) is to use multiple \`...\` in the pattern:  
154 | \`\`\`  
155 | f(... ...)  
156 | \`\`\`  
157 |   
158 | \#\#\# Common pitfall \#2: not enough indentation  
159 |   
160 | Rule of thumb:  
161 | \> If the target code is always indented, use indentation in the pattern.  
162 |   
163 | In the following example, we want to match the \`system\` sections containing a \`name\` field:  
164 | \`\`\`  
165 | \# match here  
166 | \[system\]  
167 |   name \= "Debian"  
168 |   
169 | \# DON'T match here  
170 | \[system\]  
171 |   max\_threads \= 2  
172 | \[user\]  
173 |   name \= "Admin Overlord"  
174 | \`\`\`  
175 |   
176 | ❌ This pattern will \[incorrectly\](https://semgrep.dev/s/ry1A) catch the \`name\` field in the \`user\` section:  
177 | \`\`\`  
178 | \[system\]  
179 | ...  
180 | name \= ...  
181 | \`\`\`  
182 |   
183 | ✅ This pattern will catch \[only\](https://semgrep.dev/s/bXAr) the \`name\` field in the \`system\` section:  
184 | \`\`\`  
185 | \[system\]  
186 |   ...  
187 |   name \= ...  
188 | \`\`\`  
189 |   
190 | \#\#\# Handling line-based input  
191 |   
192 | This section explains how to use Semgrep's generic mode to match  
193 | single lines of code using an ellipsis metavariable. Many simple  
194 | configuration formats are collections of key and value pairs delimited  
195 | by newlines. For example, to extract the \`password\` value from the  
196 | following made-up input:  
197 |   
198 | \`\`\`  
199 | username \= bob  
200 | password \= p@$w0rd  
201 | server \= example.com  
202 | \`\`\`  
203 |   
204 | Unfortunately, the following pattern does not match the whole line. In generic mode, metavariables only capture a single word (alphanumeric sequence):  
205 |   
206 | \`\`\`  
207 | password \= $PASSWORD  
208 | \`\`\`  
209 |   
210 | This pattern matches the input file but does not assign the value \`p\` to \`$PASSWORD\` instead of the full value \`p@$w0rd\`.  
211 |   
212 | To match an arbitrary sequence of items and capture their value in the example:  
213 |   
214 | 1\. Use a named ellipsis, by changing the pattern to the following:  
215 |   
216 |     \`\`\`yaml  
217 |     password \= $...PASSWORD  
218 |     \`\`\`  
219 |   
220 |     This still leads Semgrep to capture too much information. The value assigned to \`$...PASSWORD\` are now \`p@$w0rd\` and\<br /\>  
221 |     \`server \= example.com\`. In generic mode, an ellipsis extends until the end of the current block or up to 10 lines below, whichever comes first. To prevent this behavior, continue with the next step.  
222 |   
223 | 2\. In the Semgrep rule, specify the following key:  
224 |   
225 |     \`\`\`yaml  
226 |     generic\_ellipsis\_max\_span: 0  
227 |     \`\`\`  
228 |   
229 |     This option forces the ellipsis operator to match patterns within a single line.  
230 |     Example of the \[resulting rule\](https://semgrep.dev/playground/s/KPzn):  
231 |   
232 |     \`\`\`yaml  
233 |     id: password-in-config-file  
234 |     pattern: |  
235 |       password \= $...PASSWORD  
236 |     options:  
237 |       \# prevent ellipses from matching multiple lines  
238 |       generic\_ellipsis\_max\_span: 0  
239 |     message: |  
240 |       password found in config file: $...PASSWORD  
241 |     languages:  
242 |       \- generic  
243 |     severity: WARNING  
244 |     \`\`\`  
245 |   
246 | \#\#\# Ignoring comments  
247 |   
248 | By default, the generic mode does \*\*not\*\* know about comments or code  
249 | that can be ignored. In the following example, we are  
250 | scanning for CSS code that sets the text color to blue. The target code  
251 | is the following:  
252 |   
253 | \`\`\`  
254 | color: /\* my fave color \*/ blue;  
255 | \`\`\`  
256 |   
257 | Use the \[\`options.generic\_comment\_style\`\](/writing-rules/rule-syntax/\#options)  
258 | to ignore C-style comments as it is the case in our example.  
259 | Our simple Semgrep rule is:  
260 |   
261 | \`\`\`yaml  
262 | id: css-blue-is-ugly  
263 | pattern: |  
264 |   color: blue  
265 | options:  
266 |   \# ignore comments of the form /\* ... \*/  
267 |   generic\_comment\_style: c  
268 | message: |  
269 |   Blue is ugly.  
270 | languages:  
271 |   \- generic  
272 | severity: WARNING  
273 | \`\`\`  
274 |   
275 | \#\# Command line example  
276 |   
277 | Sample pattern: \`exec(...)\`  
278 |   
279 | Sample target file \`exec.txt\` contains:  
280 | \`\`\`bash  
281 | import exec as safe\_function  
282 | safe\_function(user\_input)  
283 |   
284 | exec("ls")  
285 |   
286 | exec(some\_var)  
287 |   
288 | some\_exec(foo)  
289 |   
290 | exec (foo)  
291 |   
292 | exec (  
293 |     bar  
294 | )  
295 |   
296 | \# exec(foo)  
297 |   
298 | print("exec(bar)")  
299 | \`\`\`  
300 |   
301 | Output:  
302 | \`\`\`bash  
303 | $ semgrep \-l generic \-e 'exec(...)\` exec.text  
304 | 7:exec("ls")  
305 | \--------------------------------------------------------------------------------  
306 | 11:exec(some\_var)  
307 | \--------------------------------------------------------------------------------  
308 | 19:exec (foo)  
309 | \--------------------------------------------------------------------------------  
310 | 23:exec (  
311 | 24:128  
312 | 25:    bar  
313 | 26:129  
314 | 27:)  
315 | \--------------------------------------------------------------------------------  
316 | 31:\# exec(foo)  
317 | \--------------------------------------------------------------------------------  
318 | 35:print("exec(bar)")  
319 | ran 1 rules on 1 files: 6 findings  
320 | \`\`\`  
321 |   
322 | \#\# Semgrep Registry rules for generic pattern matching  
323 | You can peruse \[existing generic rules\](https://semgrep.dev/r?lang=generic\&sev=ERROR,WARNING,INFO\&tag=dgryski.semgrep-go,hazanasec.semgrep-rules,ajinabraham.njsscan,best-practice,security,java-spring,go-stdlib,ruby-stdlib,java-stdlib,js-node,nodejsscan,owasp,dlint,react,performance,compatibility,portability,correctness,maintainability,security,mongodb,experimental,caching,robots-denied,missing-noreferrer,missing-noopener) in the Semgrep registry. In general, short patterns on structured data will perform the best.  
324 |   
325 | \#\# Cheat sheet  
326 | Some examples of what will and will not match on the \`generic\` tab of the Semgrep cheat sheet below:  
327 |   
328 | \<iframe src="https://semgrep.dev/embed/cheatsheet" scrolling="0" width="100%" height="800"  frameBorder="0"\>\</iframe\>  
329 | \<br /\>  
330 |   
331 | \#\# Hidden bonus  
332 | In the Semgrep code the generic pattern matching implementation is called \*\*spacegrep\*\* because it tokenizes based on whitespace (and because it sounds cool 😎).  
333 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/glossary.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | slug: glossary  
  3 | title: SAST and rule-writing glossary  
  4 | hide\_title: true  
  5 | description: Definitions of static analysis and Semgrep rule-writing terms.  
  6 | tags:  
  7 |   \- Rule writing  
  8 | \---  
  9 |   
 10 | import DefCrossFile from "/src/components/concept/\_def-cross-file.mdx"  
 11 | import DefCrossFunction from "/src/components/concept/\_def-cross-function.mdx"  
 12 |   
 13 | \# Static analysis and rule-writing glossary  
 14 |   
 15 | The definitions provided here are specific to Semgrep.  
 16 |   
 17 | \#\# Constant propagation  
 18 |   
 19 | Constant propagation is a type of analysis where values known to be constant are substituted in later uses, allowing the value to be used to detect matches. Semgrep can perform constant propagation across files, unless you are running Semgrep Community Edition (CE), which can only propagate within a file.  
 20 |   
 21 | Constant propagation is applied to all rules unless \[it is disabled\](/writing-rules/data-flow/constant-propagation\#disable-constant-propagation).  
 22 |   
 23 | For example, given the following pattern:  
 24 | \`\`\`yaml  
 25 | ...  
 26 | patterns:  
 27 | \- pattern: console.log(2)  
 28 | \`\`\`  
 29 | And the following code snippet:  
 30 | \`\`\`javascript showLineNumbers  
 31 | const x \= 2;  
 32 | //highlight-next-line  
 33 | console.log(x);  
 34 | \`\`\`  
 35 |   
 36 | The pattern operator \`pattern: print(2)\` tells Semgrep to match line 2 because it propagates the value \`2\` from the assignment in line 1 to the \`console.log()\` function in line.  
 37 |   
 38 | Constant propagation is one of the many analyses that differentiate Semgrep from grep.  
 39 |   
 40 | \#\# Cross-file analysis  
 41 |   
 42 | \<DefCrossFile /\>  
 43 |   
 44 | Within Semgrep, cross-file \*\*and\*\* cross-function analysis is simply referred to as cross-file analysis.  
 45 |   
 46 | Semgrep CE is limited to per-file analysis.  
 47 |   
 48 | \#\# Cross-function analysis  
 49 |   
 50 | \<DefCrossFunction /\>  
 51 |   
 52 | Within Semgrep documentation, cross-function analysis implies intrafile or per-file analysis. Each file is still analyzed as a standalone block, but within the file it takes into account how information flows between functions.  
 53 |   
 54 | Also known as \*\*interprocedural\*\* analysis.  
 55 |   
 56 | \#\# Error matrix  
 57 |   
 58 | An error matrix is a 2x2 table that visualizes the findings of a Semgrep rule in relation to the vulnerable lines of code it does or doesn't detect. It has two axes:  
 59 |   
 60 | \- Positive and negative  
 61 | \- True or false  
 62 |   
 63 | These yield the following combinations:  
 64 |   
 65 | \<dl\>  
 66 | \<dt\>True positive\</dt\>  
 67 | \<dd\>The rule detected a piece of code it was intended to find.\</dd\>  
 68 | \<dt\>False positive\</dt\>  
 69 | \<dd\>The rule detected a piece of code it was not intended to find.\</dd\>  
 70 | \<dt\>True negative\</dt\>  
 71 | \<dd\>The rule correctly skipped over a piece of code it wasn't meant to find.\</dd\>  
 72 | \<dt\>False negative\</dt\>  
 73 | \<dd\>The rule failed to detect a piece of code it should have found.\</dd\>  
 74 | \</dl\>  
 75 |   
 76 | Not to be confused with \*\*risk matrices\*\*.  
 77 |   
 78 | \#\# Finding  
 79 |   
 80 | A finding is the core result of Semgrep's analysis. Findings are generated when a Semgrep rule matches a piece of code. Findings can be security issues, bugs, or code that doesn't follow coding conventions.  
 81 |   
 82 | \#\# Fully qualified name  
 83 |   
 84 | A \*\*fully qualified name\*\* refers to a name which uniquely identifies a class, method, type, or module. Languages such as C\# and Ruby use \`::\` to distinguish between fully qualified names and regular names.  
 85 |   
 86 | Not to be confused with \*\*tokens\*\*.  
 87 |   
 88 | \#\# l-value (left-, or location-value)  
 89 |   
 90 | An expression that denotes an object in memory; a memory location, something that you can use in the left-hand side (LHS) of an assignment. For example, \`x\` and \`array\[2\]\` are l-values, but \`2+2\` is not.  
 91 |   
 92 | \#\# Metavariable  
 93 |   
 94 | A metavariable is an abstraction that lets you match something even when you don't know exactly what it is you want to match. It is similar to capture groups in regular expressions. All metavariables begin with a \`

      
    

      
    

      
      
      
    

      
      
      
    

   

                 Format: HTML                 Format: JSON                 Format: YAML                 Format: Text             

    

    

    

   

         and can only contain uppercase characters, digits, and underscores.  
 95 |   
 96 | \#\# Propagator  
 97 |   
 98 | A propagator is any code that alters a piece of data as the data moves across the program. This includes functions, reassignments, and so on.  
 99 |   
100 | When you write rules that perform taint analysis, propagators are pieces of code that you specify through the \`pattern-propagator\` key as code that always passes tainted data. This is especially relevant when Semgrep performs intraprocedural taint analysis, as there is no way for Semgrep to infer which function calls propagate taint. Thus, explicitly listing propagators is the only way for Semgrep to know if tainted data could be passed within your function.  
101 |   
102 | \#\# Rule (Semgrep rule)  
103 |   
104 | A rule is a specification of the patterns that Semgrep must match to the code to generate a finding. Rules are written in YAML. Without a rule, the engine has no instructions on how to match code.  
105 |   
106 | Rules can be run on either Semgrep or its OSS Engine. Only proprietary Semgrep can perform \[interfile analysis\](\#cross-file-analysis).  
107 |   
108 | There are two types of rules: \*\*search\*\* and \*\*taint\*\*.  
109 |   
110 | \<dl\>  
111 |   \<dt\>Search rules\</dt\>  
112 |   \<dd\>  
113 |     Rules default to this type. Search rules detect matches based on the patterns described by a rule. There are several semantic analyses that search rules perform, such as:  
114 |     \<ul\>  
115 |       \<li\>Interpreting syntactically different code as semantically equivalent\</li\>  
116 |       \<li\>Constant propagation\</li\>  
117 |       \<li\>Matching a fully qualified name to its reference in the code, even when not fully qualified\</li\>  
118 |       \<li\>Type inference, particularly when using typed metavariables\</li\>  
119 |     \</ul\>  
120 |   \</dd\>  
121 |   \<dt\>Taint rules\</dt\>  
122 |   \<dd\>Taint rules make use of Semgrep's taint analysis in addition to default search functionalities. Taint rules are able to specify sources, sinks, and propagators of data as well as sanitizers of that data. For more information, see \<a href="/writing-rules/data-flow/taint-mode/"\>Taint analysis documentation\</a\>.\</dd\>  
123 | \</dl\>  
124 |   
125 | \<\!-- how can we say that search rules are semantic if no analysis is performed on the value of data, such as variables? Or are there levels of semantic understanding that semgrep can perform? \--\>  
126 |   
127 | \#\# Sanitizers  
128 |   
129 | A sanitizer is any piece of code, such as a function or \[a cast\](https://learn.microsoft.com/en-us/dotnet/csharp/programming-guide/types/casting-and-type-conversions\#explicit-conversions), that can clean untrusted or tainted data. Data from untrusted sources, such as user inputs, may be tainted with unsafe characters. Sanitizers ensure that unsafe characters are removed or stripped from the input.  
130 |   
131 | An example of a sanitizer is the \[\<i class="fas fa-external-link fa-xs"\>\</i\> \`DOMPurify.sanitize(dirty);\`\](https://github.com/cure53/DOMPurify) function from the  DOMPurify package in JavaScript.  
132 |   
133 | \#\# Per-file analysis  
134 |   
135 | Also known as intrafile analysis. In per-file analysis, information can only be traced or tracked within a single file. It cannot be traced if it flows to another file.  
136 |   
137 | Per-file analysis can include cross-function analysis, aka tracing the flow of information between functions. When discussing the capabilities of pro analysis, per-file analysis implies cross-function analysis.  
138 |   
139 | \#\# Per-function analysis  
140 |   
141 | Also known as intraprocedural analysis. In per-function analysis, information can only be traced or tracked within a single function.  
142 |   
143 | \#\# Sink  
144 |   
145 | In taint analysis, a sink is any vulnerable function that is called with potentially tainted or unsafe data.  
146 |   
147 | \#\# Source  
148 |   
149 | In taint analysis, a source is any piece of code that assigns or sets tainted data, typically user input.  
150 |   
151 | \#\# Taint analysis  
152 |   
153 | Taint analysis tracks and traces the flow of untrusted or unsafe data. Data coming from sources such as user inputs could be unsafe and used as an attack vector if these inputs are not sanitized. Taint analysis provides a means of tracing that data as it moves through the program from untrusted sources to vulnerable functions.  
154 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/metavariable-analysis.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: metavariable-analysis  
 3 | append\_help\_link: true  
 4 | description: "metavariable-analysis allows Semgrep users to check metavariables for common problematic properties, such as RegEx denial of service (ReDoS) and high-entropy values."  
 5 | tags:  
 6 |   \- Rule writing  
 7 | \---  
 8 |   
 9 | \# Metavariable analysis  
10 |   
11 | Metavariable analysis was created to support some metavariable inspection techniques that are difficult to express with existing rules but have "simple" binary classifier behavior. Currently, this syntax supports two analyzers: \`redos\` and \`entropy\`  
12 |   
13 | \#\# ReDoS  
14 |   
15 | \`\`\`yaml  
16 | metavariable-analysis:  
17 |     analyzer: redos  
18 |     metavariable: $VARIABLE  
19 | \`\`\`  
20 | RegEx denial of service is caused by poorly constructed regular expressions that exhibit exponential runtime when fed specifically crafted inputs. The \`redos\` analyzer uses known RegEx antipatterns to determine if the target expression is potentially vulnerable to catastrophic backtracking.  
21 |   
22 | \<iframe src="https://semgrep.dev/embed/editor?snippet=2Aoj" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
23 |   
24 | \#\# Entropy  
25 |   
26 | \`\`\`yaml  
27 | metavariable-analysis:  
28 |     analyzer: entropy  
29 |     metavariable: $VARIABLE  
30 | \`\`\`  
31 | Entropy is a common approach for detecting secret strings \- many existing tools leverage a combination of entropy calculations and RegEx for secret detection. This analyzer returns \`true\` if a metavariable has high entropy (randomness) relative to the English language.  
32 |   
33 | \<iframe src="https://semgrep.dev/embed/editor?snippet=GgZG" border="0" frameBorder="0" width="100%" height="432"\>\</iframe\>  
34 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/overview.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | id: overview  
 3 | displayed\_sidebar: rulewritingSidebar  
 4 | description: \>-  
 5 |   Learn how to use Semgrep’s intuitive syntax to write rules specific to your codebase. You can write and share rules directly from your browser using the Semgrep Playground, or write rules in your terminal and run them on the command line.  
 6 | title: Overview  
 7 | hide\_title: true  
 8 | tags:  
 9 |   \- Rule writing  
10 | \---  
11 |   
12 | \# Writing rules  
13 |   
14 | \#\#\# Tutorial  
15 |   
16 | If you want the best introduction to writing Semgrep rules, use the interactive, example-based \[Semgrep rule tutorial\](https://semgrep.dev/learn).  
17 |   
18 | \#\#\# Do it live\!  
19 |   
20 | Write and share rules directly from the \[Playground\](https://semgrep.dev/editor). You can also write rules in your terminal and run them with the Semgrep command line tool.  
21 |   
22 | You can write rules that:  
23 |   
24 | \- Automate code review comments  
25 | \- Identify secure coding violations  
26 | \- Scan configuration files  
27 | \- See more use cases in \[Rule ideas\](rule-ideas.md).  
28 |   
29 | This rule detects the use of \`is\` when comparing Python strings. \`is\` checks reference equality, not value equality, and can exhibit nondeterministic behavior.  
30 |   
31 | \<iframe title="Semgrep example Python is comparison" src="https://semgrep.dev/embed/editor?snippet=Ppde" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
32 |   
33 | \#\#\# Next steps  
34 |   
35 | The following articles guide you through rule writing basics or can provide you with needed references:  
36 |   
37 | \- \[Pattern syntax\](/writing-rules/pattern-syntax) describes what Semgrep patterns can do in detail, and provides example use cases of the ellipsis operator, metavariables.  
38 | \- \[Rule syntax\](rule-syntax.md) describes Semgrep YAML rule files, which can have multiple patterns, detailed output messages, and autofixes. The syntax allows the composition of individual patterns with Boolean operators.  
39 | \- \[Contributing rules\](/contributing/contributing-to-semgrep-rules-repository) gives you an overview of where and how you can contribute to Semgrep Registry rules. This document also provides some information about tests and appropriate metadata information you may use for your rules.  
40 |   
41 | Looking for ideas on what rules to write? See \[Rule ideas\](/writing-rules/rule-ideas) for common use cases and prompts to help you start writing rules from scratch.  
42 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/pattern-examples.md:  
\--------------------------------------------------------------------------------  
 1 | \---  
 2 | slug: pattern-examples  
 3 | tags:  
 4 |   \- Rule writing  
 5 | \---  
 6 |   
 7 |   
 8 |   
 9 | \# Pattern examples  
10 |   
11 | This section is automatically generated from the unit test suite inside Semgrep. Per-language references are also available within the \[Playground\](https://semgrep.dev/editor).  
12 |   
13 | \<iframe src="https://semgrep.dev/embed/cheatsheet" scrolling="0" width="100%" height="800"  frameBorder="0"\>\</iframe\>  
14 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/pattern-syntax.mdx:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | append\_help\_link: true  
  3 | slug: pattern-syntax  
  4 | description: "Learn Semgrep's pattern syntax to search code for a given code pattern. If you're just getting started writing Semgrep rules, check out the Semgrep Tutorial at https://semgrep.dev/learn"  
  5 | tags:  
  6 |   \- Rule writing  
  7 | \---  
  8 |   
  9 | \# Pattern syntax  
 10 |   
 11 | :::tip  
 12 | Getting started with rule writing? Try the \[Semgrep Tutorial\](https://semgrep.dev/learn) 🎓  
 13 | :::  
 14 |   
 15 | This document describes Semgrep’s pattern syntax. You can also see pattern \[examples by language\](/writing-rules/pattern-examples). In the command line, patterns are specified with the flag \`--pattern\` (or \`-e\`). Multiple  
 16 | coordinating patterns may be specified in a configuration file. See  
 17 | \[rule syntax\](/writing-rules/rule-syntax) for more information.  
 18 |   
 19 | \#\# Pattern matching  
 20 |   
 21 | Pattern matching searches code for a given pattern. For example, the  
 22 | expression pattern \`1 \+ func(42)\` can match a full expression or be  
 23 | part of a subexpression:  
 24 |   
 25 | \`\`\`python  
 26 | foo(1 \+ func(42)) \+ bar()  
 27 | \`\`\`  
 28 |   
 29 | In the same way, the statement pattern \`return 42\` can match a top  
 30 | statement in a function or any nested statement:  
 31 |   
 32 | \`\`\`python  
 33 | def foo(x):  
 34 |   if x \> 1:  
 35 |      if x \> 2:  
 36 |        return 42  
 37 |   return 42  
 38 | \`\`\`  
 39 |   
 40 | \#\# Ellipsis operator  
 41 |   
 42 | The \`...\` ellipsis operator abstracts away a sequence of zero or more  
 43 | items such as arguments, statements, parameters, fields, characters.  
 44 |   
 45 | The \`...\` ellipsis can also match any single item that is not part of  
 46 | a sequence when the context allows it.  
 47 |   
 48 | See the use cases in the subsections below.  
 49 |   
 50 | \#\#\# Function calls  
 51 |   
 52 | Use the ellipsis operator to search for function calls or  
 53 | function calls with specific arguments. For example, the pattern \`insecure\_function(...)\` finds calls regardless of its arguments.  
 54 |   
 55 | \`\`\`python  
 56 | insecure\_function("MALICIOUS\_STRING", arg1, arg2)  
 57 | \`\`\`  
 58 |   
 59 | Functions and classes can be referenced by their fully qualified name, e.g.,  
 60 |   
 61 | \- \`django.utils.safestring.mark\_safe(...)\` or \`mark\_safe(...)\`  
 62 | \- \`System.out.println(...)\` or \`println(...)\`  
 63 |   
 64 | You can also search for calls with arguments after a match. The pattern \`func(1, ...)\` will match both:  
 65 |   
 66 | \`\`\`python  
 67 | func(1, "extra stuff", False)  
 68 | func(1)  \# Matches no arguments as well  
 69 | \`\`\`  
 70 |   
 71 | Or find calls with arguments before a match with \`func(..., 1)\`:  
 72 |   
 73 | \`\`\`python  
 74 | func("extra stuff", False, 1\)  
 75 | func(1)  \# Matches no arguments as well  
 76 | \`\`\`  
 77 |   
 78 | The pattern \`requests.get(..., verify=False, ...)\` finds calls where an argument appears anywhere:  
 79 |   
 80 | \`\`\`python  
 81 | requests.get(verify=False, url=URL)  
 82 | requests.get(URL, verify=False, timeout=3)  
 83 | requests.get(URL, verify=False)  
 84 | \`\`\`  
 85 |   
 86 | Match the keyword argument value with the pattern \`$FUNC(..., $KEY=$VALUE, ...)\`.  
 87 |   
 88 | \#\#\# Method calls  
 89 |   
 90 | The ellipsis operator can also be used to search for method calls.  
 91 | For example, the pattern \`$OBJECT.extractall(...)\` matches:  
 92 |   
 93 | \`\`\`python  
 94 | tarball.extractall('/path/to/directory')  \# Oops, potential arbitrary file overwrite  
 95 | \`\`\`  
 96 |   
 97 | You can also use the ellipsis in chains of method calls. For example,  
 98 | the pattern \`$O.foo(). ... .bar()\` will match:  
 99 |   
100 | \`\`\`python  
101 | obj \= MakeObject()  
102 | obj.foo().other\_method(1,2).again(3,4).bar()  
103 |   
104 | \`\`\`  
105 |   
106 | \#\#\# Function definitions  
107 |   
108 | The ellipsis operator can be used in function parameter lists or in the function  
109 | body. To find function definitions with \[mutable default arguments\](https://docs.python-guide.org/writing/gotchas/\#mutable-default-arguments):  
110 |   
111 | \`\`\`text  
112 | pattern: |  
113 |   def $FUNC(..., $ARG={}, ...):  
114 |       ...  
115 | \`\`\`  
116 |   
117 | \`\`\`python  
118 | def parse\_data(parser, data={}):  \# Oops, mutable default arguments  
119 |     pass  
120 | \`\`\`  
121 |   
122 | :::tip  
123 | The YAML \`|\` operator allows for \[multiline strings\](https://yaml-multiline.info/).  
124 | :::  
125 |   
126 | The ellipsis operator can match the function name.  
127 | Match any function definition:  
128 | Regular functions, methods, and also anonymous functions (such as lambdas).  
129 | To match named or anonymous functions use an ellipsis \`...\` in place of the name of the function.  
130 | For example, in JavaScript the pattern \`function ...($X) { ... }\` matches  
131 | any function with one parameter:  
132 |   
133 | \`\`\`javascript  
134 | function foo(a) {  
135 |   return a;  
136 | }  
137 | var bar \= function (a) {  
138 |   return a;  
139 | };  
140 | \`\`\`  
141 |   
142 | \#\#\# Class definitions  
143 |   
144 | The ellipsis operator can be used in class definitions. To find classes that  
145 | inherit from a certain parent:  
146 |   
147 | \`\`\`text  
148 | pattern: |  
149 |   class $CLASS(InsecureBaseClass):  
150 |       ...  
151 | \`\`\`  
152 |   
153 | \`\`\`python  
154 | class DataRetriever(InsecureBaseClass):  
155 |     def \_\_init\_\_(self):  
156 |         pass  
157 | \`\`\`  
158 |   
159 | :::tip  
160 | The YAML \`|\` operator allows for \[multiline strings\](https://yaml-multiline.info/).  
161 | :::  
162 |   
163 | \#\#\#\# Ellipsis operator scope  
164 |   
165 | The \`...\` ellipsis operator matches everything in its current scope. The current scope of this operator is defined by the patterns that precede \`...\` in a rule. See the following example:  
166 |   
167 | \<iframe  
168 |   src="https://semgrep.dev/embed/editor?snippet=zZx0"  
169 |   border="0"  
170 |   frameBorder="0"  
171 |   width="100%"  
172 |   height="432"  
173 | \>\</iframe\>  
174 |   
175 | Semgrep matches the first occurrence of \`bar\` and \`baz\` in the test code as these objects fall under the scope of \`foo\` and \`...\`. The ellipsis operator does not match the second occurrence of \`bar\` and \`baz\` as they are not inside of the function definition, therefore these objects in their second occurrence are not inside the scope of the ellipsis operator.  
176 |   
177 | \#\#\# Strings  
178 |   
179 | The ellipsis operator can be used to search for strings containing any data. The pattern \`crypto.set\_secret\_key("...")\` matches:  
180 |   
181 | \`\`\`python  
182 | crypto.set\_secret\_key("HARDCODED SECRET")  
183 | \`\`\`  
184 |   
185 | This also works with \[constant propagation\](\#constants).  
186 |   
187 | In languages where regular expressions use a special syntax  
188 | (for example JavaScript), the pattern \`/.../\` will match  
189 | any regular expression construct:  
190 |   
191 | \`\`\`javascript  
192 | re1 \= /foo|bar/;  
193 | re2 \= /a.\*b/;  
194 | \`\`\`  
195 |   
196 | \#\#\# Binary operations  
197 |   
198 | The ellipsis operator can match any number of arguments to binary operations. The pattern \`$X \= 1 \+ 2 \+ ...\` matches:  
199 |   
200 | \`\`\`python  
201 | foo \= 1 \+ 2 \+ 3 \+ 4  
202 | \`\`\`  
203 |   
204 | \#\#\# Containers  
205 |   
206 | The ellipsis operator can match inside container data structures like lists, arrays, and key-value stores.  
207 |   
208 | The pattern \`user\_list \= \[..., 10\]\` matches:  
209 |   
210 | \`\`\`python  
211 | user\_list \= \[8, 9, 10\]  
212 | \`\`\`  
213 |   
214 | The pattern \`user\_dict \= {...}\` matches:  
215 |   
216 | \`\`\`python  
217 | user\_dict \= {'username': 'password'}  
218 | \`\`\`  
219 |   
220 | The pattern \`user\_dict \= {..., $KEY: $VALUE, ...}\` matches the following and allows for further metavariable queries:  
221 |   
222 | \`\`\`python  
223 | user\_dict \= {'username': 'password', 'address': 'zipcode'}  
224 | \`\`\`  
225 |   
226 | You can also match just a key-value pair in  
227 | a container, for example in JSON the pattern \`"foo": $X\` matches  
228 | just a single line in:  
229 |   
230 | \`\`\`json  
231 | { "bar": True,  
232 |   "name": "self",  
233 |   "foo": 42  
234 | }  
235 | \`\`\`  
236 |   
237 | \#\#\# Conditionals and loops  
238 |   
239 | The ellipsis operator can be used inside conditionals or loops. The pattern:  
240 |   
241 | \`\`\`text  
242 | pattern: |  
243 |   if $CONDITION:  
244 |       ...  
245 | \`\`\`  
246 |   
247 | :::tip  
248 | The YAML \`|\` operator allows for \[multiline strings\](https://yaml-multiline.info/).  
249 | :::  
250 |   
251 | matches:  
252 |   
253 | \`\`\`python  
254 | if can\_make\_request:  
255 |     check\_status()  
256 |     make\_request()  
257 |     return  
258 | \`\`\`  
259 |   
260 | A metavariable can match a conditional or loop body if the body statement information is re-used later. The pattern:  
261 |   
262 | \`\`\`text  
263 | pattern: |  
264 |   if $CONDITION:  
265 |       $BODY  
266 | \`\`\`  
267 |   
268 | matches:  
269 |   
270 | \`\`\`python  
271 | if can\_make\_request:  
272 |     single\_request\_statement()  
273 | \`\`\`  
274 |   
275 | :::tip  
276 | Half or partial statements can't be matches; both of the examples above must specify the contents of the condition’s body (e.g., \`$BODY\` or \`...\`), otherwise they are not valid patterns.  
277 | :::  
278 |   
279 | \#\#\# Matching single items with an ellipsis  
280 |   
281 | Ellipsis \`...\` is generally used to match sequences of similar elements.  
282 | However, you can also match single item using ellipsis \`...\` operator.  
283 | The following pattern is valid in languages with a C-like  
284 | syntax even though \`...\` matches a single Boolean value rather  
285 | than a sequence:  
286 |   
287 | \`\`\`java  
288 | if (...)  
289 |   return 42;  
290 | \`\`\`  
291 |   
292 | Another example where a single expression is matched by an ellipsis is  
293 | the right-hand side of assignments:  
294 |   
295 | \`\`\`java  
296 | foo \= ...;  
297 | \`\`\`  
298 |   
299 | However, matching a sequence of items remains the default meaning of an  
300 | ellipsis. For example, the pattern \`bar(...)\` matches \`bar(a)\`,  
301 | but also \`bar(a, b)\` and \`bar()\`. To force a match on a single item,  
302 | use a metavariable as in \`bar($X)\`.  
303 |   
304 | \#\# Metavariables  
305 |   
306 | Metavariables are an abstraction to match code when you don’t know the value or contents ahead of time, similar to \[capture groups\](https://regexone.com/lesson/capturing\_groups) in regular expressions.  
307 |   
308 | Metavariables can be used to track values across a specific code scope. This  
309 | includes variables, functions, arguments, classes, object methods, imports,  
310 | exceptions, and more.  
311 |   
312 | Metavariables look like \`$X\`, \`$WIDGET\`, or \`$USERS\_2\`. They begin with a \`

      
    

      
    

      
      
      
    

      
      
      
    

   

                 Format: HTML                 Format: JSON                 Format: YAML                 Format: Text             

    

    

    

   

         and can only  
313 | contain uppercase characters, \`\_\`, or digits. Names like \`$x\` or \`$some\_value\` are invalid.  
314 |   
315 | \#\#\# Expression metavariables  
316 |   
317 | The pattern \`$X \+ $Y\` matches the following code examples:  
318 |   
319 | \`\`\`python  
320 | foo() \+ bar()  
321 | \`\`\`  
322 |   
323 | \`\`\`python  
324 | current \+ total  
325 | \`\`\`  
326 |   
327 | \#\#\# Import metavariables  
328 |   
329 | Metavariables can also be used to match imports. For example, \`import $X\` matches:  
330 |   
331 | \`\`\`python  
332 | import random  
333 | \`\`\`  
334 |   
335 | \#\#\# Reoccurring metavariables  
336 |   
337 | Re-using metavariables shows their true power. Detect useless assignments:  
338 |   
339 | \`\`\`text  
340 | pattern: |  
341 |   $X \= $Y  
342 |   $X \= $Z  
343 | \`\`\`  
344 |   
345 | Useless assignment detected:  
346 |   
347 | \`\`\`python  
348 | initial\_value \= 10  \# Oops, useless assignment  
349 | initial\_value \= get\_initial\_value()  
350 | \`\`\`  
351 |   
352 | :::tip  
353 | The YAML \`|\` operator allows for \[multiline strings\](https://yaml-multiline.info/).  
354 | :::  
355 |   
356 | \#\#\# Literal Metavariables  
357 |   
358 | You can use \`"$X"\` to match any string literal. This is similar  
359 | to using \`"..."\`, but the content of the string is stored in the  
360 | metavariable \`$X\`, which can then be used in a message  
361 | or in a \[\`metavariable-regex\`\](/writing-rules/rule-syntax/\#metavariable-regex).  
362 |   
363 | You can also use \`/$X/\` and \`:$X\` to respectively match  
364 | any regular expressions or atoms (in languages that support  
365 | those constructs, e.g., Ruby).  
366 |   
367 | :::info  
368 | Because literal metavariables bind to strings that may not be valid code, if you want to match them in more detail with a \[\`metavariable-pattern\`\](/writing-rules/rule-syntax/\#metavariable-pattern), you must \[specify \`generic\` language\](/writing-rules/rule-syntax\#metavariable-pattern-with-nested-language) inside the \`metavariable-pattern\`. For example:  
369 |   
370 | \`\`\`  
371 | rules:  
372 |   \- id: match-literal-string  
373 |     languages:  
374 |       \- python  
375 |     severity: INFO  
376 |     message: Found "$STRING"  
377 |     patterns:  
378 |       \- pattern: '"$STRING"'  
379 |       \- metavariable-pattern:  
380 |           language: generic  
381 |           metavariable: $STRING  
382 |           pattern: "literal string contents"  
383 | \`\`\`  
384 | :::  
385 |   
386 | \#\#\# Typed metavariables  
387 |   
388 | \#\#\#\# Syntax  
389 |   
390 | Typed metavariables only match a metavariable if it’s declared as a specific type.  
391 |   
392 | \#\#\#\#\# Java:  
393 |   
394 | For example, to look for calls to the \`log\` method on \`Logger\` objects.  
395 | A simple pattern for this purpose could use a metavariable for the Logger object.  
396 |   
397 | \`\`\`text  
398 | pattern: $LOGGER.log(...)  
399 | \`\`\`  
400 |   
401 | But if we are concerned about finding calls to the \`Math.log()\` method as well, we can use a typed metavariable to put a type constraint on the \`$LOGGER\` metavariable.  
402 |   
403 | \`\`\`text  
404 | pattern: (java.util.logging.Logger $LOGGER).log(...)  
405 | \`\`\`  
406 |   
407 | Alternatively, if we want to capture more logger types, for example custom logger types, we could instead add a constraint to the type of the argument in this method call instead.  
408 |   
409 | \`\`\`text  
410 | pattern: $LOGGER.log(java.util.logging.LogRecord $RECORD)  
411 | \`\`\`  
412 |   
413 | \#\#\#\#\# C:  
414 |   
415 | In this example in C, we want to capture all cases where something is compared to a char array.  
416 | We start with a simple pattern that looks for comparison between two variables.  
417 |   
418 | \`\`\`text  
419 | pattern: $X \== $Y  
420 | \`\`\`  
421 |   
422 | We can then put a type constraint on one of the metavariables used in this pattern by turning it into a typed metavariable.  
423 |   
424 | \`\`\`text  
425 | pattern: $X \== (char \*$Y)  
426 | \`\`\`  
427 |   
428 | \`\`\`c  
429 | int main() {  
430 |     char \*a \= "Hello";  
431 |     int b \= 1;  
432 |   
433 |     // Matched  
434 |     if (a \== "world") {  
435 |         return 1;  
436 |     }  
437 |   
438 |     // Not matched  
439 |     if (b \== 2\) {  
440 |         return \-1;  
441 |     }  
442 |   
443 |     return 0;  
444 | }  
445 | \`\`\`  
446 |   
447 | \#\#\#\#\# Go:  
448 |   
449 | The syntax for a typed metavariable in Go looks different from the syntax for Java.  
450 | In this Go example we look for calls to the \`Open\` function, but only on an object of the \`zip.Reader\` type.  
451 |   
452 | \`\`\`text  
453 | pattern: |  
454 |     ($READER : \*zip.Reader).Open($INPUT)  
455 | \`\`\`  
456 |   
457 | \`\`\`go  
458 | func read\_file(reader \*zip.Reader, filename) {  
459 |   
460 | 	// Matched  
461 | 	reader.Open(filename)  
462 |   
463 |     dir := http.Dir("/")  
464 |   
465 | 	// Not matched  
466 | 	f, err := dir.Open(c.Param("file"))  
467 | }  
468 | \`\`\`  
469 |   
470 | :::caution  
471 | For Go, Semgrep currently does not recognize the type of all variables that are declared on the same line. That is, the following will not take both \`a\` and \`b\` as \`int\`s: \`var a, b \= 1, 2\`  
472 | :::  
473 |   
474 | \#\#\#\#\# TypeScript:  
475 |   
476 | In this example, we want to look for uses of the DomSanitizer function.  
477 |   
478 | \`\`\`text  
479 | pattern: ($X: DomSanitizer).sanitize(...)  
480 | \`\`\`  
481 |   
482 | \`\`\`typescript  
483 | constructor(  
484 |   private \_activatedRoute: ActivatedRoute,  
485 |   private sanitizer: DomSanitizer,  
486 | ) { }  
487 |   
488 | ngOnInit() {  
489 |     // Not matched  
490 |     this.sanitizer.bypassSecurityTrustHtml(DOMPurify.sanitize(this.\_activatedRoute.snapshot.queryParams\['q'\]))  
491 |   
492 |     // Matched  
493 |     this.sanitizer.bypassSecurityTrustHtml(this.sanitizer.sanitize(this.\_activatedRoute.snapshot.queryParams\['q'\]))  
494 | }  
495 | \`\`\`  
496 |   
497 | \#\#\#\# Using typed metavariables  
498 |   
499 | Type inference applies to the entire file\! One common way to use typed metavariables is to check for a function called on a specific type of object. For example, let's say you're looking for calls to a potentially unsafe logger in a class like this:  
500 |   
501 | \`\`\`  
502 | class Test {  
503 |     static Logger logger;  
504 |   
505 |     public static void run\_test(String input, int num) {  
506 |         logger.log("Running a test with " \+ input);  
507 |   
508 |         test(input, Math.log(num));  
509 |     }  
510 | }  
511 | \`\`\`  
512 |   
513 | If you searched for \`$X.log(...)\`, you can also match \`Math.log(num)\`. Instead, you can search for \`(Logger $X).log(...)\` which gives you the call to \`logger\`. See the rule \[\`logger\_search\`\](https://semgrep.dev/playground/s/lgAo).  
514 |   
515 | :::caution  
516 | Since matching happens within a single file, this is only guaranteed to work for local variables and arguments. Additionally, Semgrep currently understands types on a shallow level. For example, if you have \`int\[\] A\`, it will not recognize \`A\[0\]\` as an integer. If you have a class with fields, you will not be able to use typechecking on field accesses, and it will not recognize the class’s field as the expected type. Literal types are understood to a limited extent. Expanded type support is under active development.  
517 | :::  
518 |   
519 | \#\#\# Ellipsis metavariables  
520 |   
521 | You can combine ellipses and metavariables to match a sequence  
522 | of arguments and store the matched sequence in a metavariable.  
523 | For example the pattern \`foo($...ARGS, 3, $...ARGS)\` will  
524 | match:  
525 |   
526 | \`\`\`python  
527 | foo(1,2,3,1,2)  
528 | \`\`\`  
529 |   
530 | When referencing an ellipsis metavariable in a rule message or \[metavariable-pattern\](/writing-rules/rule-syntax\#metavariable-pattern), include the ellipsis:  
531 |   
532 | \`\`\`yaml  
533 | \- message: Call to foo($...ARGS)  
534 | \`\`\`  
535 |   
536 | \#\#\# Anonymous metavariables  
537 |   
538 | Anonymous metavariables are used to specify that a metavariable exists in the pattern you want to capture.  
539 |   
540 | An anonymous metavariable always takes the form \`$\_\`. Variables such as \`$\_1\` or \`$\_2\` are \*\*not\*\* anonymous. You can use more than one anonymous metavariable in a rule definition.  
541 |   
542 | For example, if you want to specify that a function should \*\*always\*\* have 3 arguments, then you can use anonymous metavariables:  
543 |   
544 | \`\`\`yaml  
545 | \- pattern: def function($\_, $\_, $\_)  
546 | \`\`\`  
547 |   
548 | An anonymous metavariable does not produce any binding to the code it matched. This means it does not enforce that it matches the same code at each place it is used. The pattern:  
549 |   
550 | \`\`\`yaml  
551 | \- pattern: def function($A, $B, $C)  
552 | \`\`\`  
553 |   
554 | is not equivalent to the former example, as \`$A\`, \`$B\`, and \`$C\` bind to the code that matched the pattern. You can then use \`$A\` or any other metavariable in your rule definition to specify that specific code. Anonymous metavariables cannot be used this way.  
555 |   
556 | Anonymous metavariables also communicate to the reader that their values are not relevant, but rather their occurrence in the pattern.  
557 |   
558 | \#\#\# Metavariable unification  
559 |   
560 | For search mode rules, metavariables with the same name are treated as the same metavariable within the \`patterns\` operator. This is called metavariable unification.  
561 |   
562 | For taint mode rules, patterns defined \*\*within\*\* \`pattern-sinks\` and \`pattern-sources\` still unify. However, metavariable unification \*\*between\*\* \`pattern-sinks\` and \`pattern-sources\` is \*\*not\*\* enabled by default.  
563 |   
564 | To enforce unification, set \`taint\_unify\_mvars: true\` under the rule \`options\` key. When \`taint\_unify\_mvars: true\` is set, a metavariable defined in \`pattern-sinks\` and \`pattern-sources\` with the same name is treated as the same metavariable. See \[Metavariables, rule message, and unification\](/writing-rules/data-flow/taint-mode\#metavariables-rule-message-and-unification) for more information.  
565 |   
566 | \#\#\# Display matched metavariables in rule messages  
567 |   
568 | Display values of matched metavariables in rule messages. Add a metavariable to the rule message (for example \`Found $X\`) and Semgrep replaces it with the value of the detected metavariable.  
569 |   
570 | To display matched metavariable in a rule message, add the same metavariable as you are searching for in your rule to the rule message.  
571 |   
572 | 1\. Find the metavariable used in the Semgrep rule. See the following example of a part Semgrep rule (formula):  
573 |    \`\`\`yaml  
574 |    \- pattern: $MODEL.set\_password(…)  
575 |    \`\`\`  
576 |    This formula uses \`$MODEL\` as a metavariable.  
577 | 2\. Insert the metavariable to rule message:  
578 |    \`\`\`yaml  
579 |    \- message: Setting a password on $MODEL  
580 |    \`\`\`  
581 | 3\. Use the formula displayed above against the following code:  
582 |    \`\`\`python  
583 |    user.set\_password(new\_password)  
584 |    \`\`\`  
585 |   
586 | The resulting message is:  
587 |   
588 | \`\`\`  
589 | Setting a password on user  
590 | \`\`\`  
591 |   
592 | Run the following example in Semgrep Playground to see the message (click \*\*Open in Editor\*\*, and then \*\*Run\*\*, unroll the \*\*1 Match\*\* to see the message):  
593 |   
594 | \<iframe  
595 |   title="Metavariable value in message example"  
596 |   src="https://semgrep.dev/embed/editor?snippet=6KpK"  
597 |   width="100%"  
598 |   height="432"  
599 |   frameborder="0"  
600 | \>\</iframe\>  
601 |   
602 | :::info  
603 | If you're using Semgrep's advanced dataflow features, see documentation of experimental feature \[Displaying propagated value of metavariable\](/writing-rules/experiments/display-propagated-metavariable).  
604 | :::  
605 |   
606 | \#\# Equivalences  
607 |   
608 | Semgrep automatically searches for code that is semantically equivalent.  
609 |   
610 | \#\#\# Imports  
611 |   
612 | Equivalent imports using aliasing or submodules are matched.  
613 |   
614 | The pattern \`subprocess.Popen(...)\` matches:  
615 |   
616 | \`\`\`python  
617 | import subprocess.Popen as sub\_popen  
618 | sub\_popen('ls')  
619 | \`\`\`  
620 |   
621 | The pattern \`foo.bar.baz.qux(...)\` matches:  
622 |   
623 | \`\`\`python  
624 | from foo.bar import baz  
625 | baz.qux()  
626 | \`\`\`  
627 |   
628 | \#\#\# Constants  
629 |   
630 | Semgrep performs constant propagation.  
631 |   
632 | The pattern \`set\_password("password")\` matches:  
633 |   
634 | \`\`\`python  
635 | HARDCODED\_PASSWORD \= "password"  
636 |   
637 | def update\_system():  
638 |     set\_password(HARDCODED\_PASSWORD)  
639 | \`\`\`  
640 |   
641 | Basic constant propagation support like in the example above is a stable feature.  
642 | Experimentally, Semgrep also supports \[intra-procedural flow-sensitive constant propagation\](/writing-rules/data-flow/constant-propagation).  
643 |   
644 | The pattern \`set\_password("...")\` also matches:  
645 |   
646 | \`\`\`python  
647 | def update\_system():  
648 |     if cond():  
649 |         password \= "abc"  
650 |     else:  
651 |         password \= "123"  
652 |     set\_password(password)  
653 | \`\`\`  
654 |   
655 | :::tip  
656 | It is possible to disable constant propagation in a per-rule basis via the \[\`options\` rule field\](/writing-rules/rule-syntax\#options).  
657 | :::  
658 |   
659 | \#\#\# Associative and commutative operators  
660 |   
661 | Semgrep performs associative-commutative (AC) matching. For example, \`... && B && C\` will match both \`B && C\` and \`(A && B) && C\` (i.e., \`&&\` is associative). Also, \`A | B | C\` will match \`A | B | C\`, and \`B | C | A\`, and \`C | B | A\`, and any other permutation (i.e., \`|\` is associative and commutative).  
662 |   
663 | Under AC-matching metavariables behave similarly to \`...\`. For example, \`A | $X\` can match \`A | B | C\` in four different ways (\`$X\` can bind to \`B\`, or \`C\`, or \`B | C\`). In order to avoid a combinatorial explosion, Semgrep will only perform AC-matching with metavariables if the number of potential matches is \_small\_, otherwise it will produce just one match (if possible) where each metavariable is bound to a single operand.  
664 |   
665 | Using \[\`options\`\](/writing-rules/rule-syntax\#options) it is possible to entirely disable AC-matching. It is also possible to treat Boolean AND and OR operators (e.g., \`&&\` in \`||\` in C-family languages) as commutative, which can be useful despite not being semantically accurate.  
666 |   
667 | \#\# Deep expression operator  
668 |   
669 | Use the deep expression operator \`\<... \[your\_pattern\] ...\>\` to match an expression that could be deeply nested within another expression. An example is looking for a pattern anywhere within an \`if\` statement. The deep expression operator matches your pattern in the current expression context and recursively in any subexpressions.  
670 |   
671 | For example, this pattern:  
672 |   
673 | \`\`\`yaml  
674 | pattern: |  
675 |   if \<... $USER.is\_admin() ...\>:  
676 |     ...  
677 | \`\`\`  
678 |   
679 | matches:  
680 |   
681 | \`\`\`python  
682 | if user.authenticated() and user.is\_admin() and user.has\_group(gid):  
683 |   \[ CONDITIONAL BODY \]  
684 | \`\`\`  
685 |   
686 | The deep expression operator works in:  
687 |   
688 | \- \`if\` statements: \`if \<... $X ...\>:\`  
689 | \- nested calls: \`sql.query(\<... $X ...\>)\`  
690 | \- operands of a binary expression: \`"..." \+ \<... $X ...\>\`  
691 | \- any other expression context  
692 |   
693 | \#\# Limitations  
694 |   
695 | \#\#\# Statements types  
696 |   
697 | Semgrep handles some statement types differently than others, particularly when searching for fragments inside statements. For example, the pattern \`foo\` will match these statements:  
698 |   
699 | \`\`\`python  
700 | x \+= foo()  
701 | return bar \+ foo  
702 | foo(1, 2\)  
703 | \`\`\`  
704 |   
705 | But \`foo\` will not match the following statement (\`import foo\` will match it though):  
706 |   
707 | \`\`\`python  
708 | import foo  
709 | \`\`\`  
710 |   
711 | \#\#\#\# Statements as expressions  
712 |   
713 | Many programming languages differentiate between expressions and statements. Expressions can appear inside if conditions, in function call arguments, etc. Statements can not appear everywhere; they are sequence of operations (in many languages using \`;\` as a separator/terminator) or special control flow constructs (if, while, etc.).  
714 |   
715 | \`foo()\` is an expression (in most languages).  
716 |   
717 | \`foo();\` is a statement (in most languages).  
718 |   
719 | If your search pattern is a statement, Semgrep will automatically try to search for it as \_both\_ an expression and a statement.  
720 |   
721 | When you write the expression \`foo()\` in a pattern, Semgrep will visit every expression and sub-expression in your program and try to find a match.  
722 |   
723 | Many programmers don't really see the difference between \`foo()\` and \`foo();\`. This is why when one looks for \`foo()\`; Semgrep thinks the user wants to match statements like \`a \= foo();\`, or \`print(foo());\`.  
724 |   
725 | :::info  
726 | Note that in some programming languages such as Python, which does not use semicolons as a separator or terminator, the difference between expressions and statements is even more confusing. Indentation in Python matters, and a newline after \`foo()\` is really the same than \`foo();\` in other programming languages such as C.  
727 | :::  
728 |   
729 | \#\#\# Partial expressions  
730 |   
731 | Partial expressions are not valid patterns. For example, the following is invalid:  
732 |   
733 | \`\`\`text  
734 | pattern: 1+  
735 | \`\`\`  
736 |   
737 | A complete expression is needed (like \`1 \+ $X\`)  
738 |   
739 | \#\#\# Ellipses and statement blocks  
740 |   
741 | The \[ellipsis operator\](\#ellipsis-operator) does \_not\_ jump from inner to outer statement blocks.  
742 |   
743 | For example, this pattern:  
744 |   
745 | \`\`\`text  
746 | foo()  
747 | ...  
748 | bar()  
749 | \`\`\`  
750 |   
751 | matches:  
752 |   
753 | \`\`\`python  
754 | foo()  
755 | baz()  
756 | bar()  
757 | \`\`\`  
758 |   
759 | and also matches:  
760 |   
761 | \`\`\`python  
762 | foo()  
763 | baz()  
764 | if cond:  
765 |     bar()  
766 | \`\`\`  
767 |   
768 | but it does \_not\_ match:  
769 |   
770 | \`\`\`python  
771 | if cond:  
772 |     foo()  
773 | baz()  
774 | bar()  
775 | \`\`\`  
776 |   
777 | because \`...\` cannot jump from the inner block where \`foo()\` is, to the outer block where \`bar()\` is.  
778 |   
779 | \#\#\# Partial statements  
780 |   
781 | Partial statements are partially supported. For example,  
782 | you can just match the header of a conditional with \`if ($E)\`,  
783 | or just the try part of an exception statement with \`try { ... }\`.  
784 |   
785 | This is especially useful when used in a  
786 | \[pattern-inside\](/writing-rules/rule-syntax\#pattern-inside) to restrict the  
787 | context in which to search for other things.  
788 |   
789 | \#\#\# Other partial constructs  
790 |   
791 | It is possible to just match the header of a function (without its body),  
792 | for example \`int foo(...)\` to match just the header part of the  
793 | function \`foo\`. In the same way, you can just match a class header  
794 | (e.g., with \`class $A\`).  
795 |   
796 | \#\# Deprecated features  
797 |   
798 | \#\#\# String matching  
799 |   
800 | :::warning  
801 | String matching has been deprecated. You should use \[\`metavariable-regex\`\](/writing-rules/rule-syntax\#metavariable-regex) instead.  
802 | :::  
803 |   
804 | Search string literals within code with \[Perl Compatible Regular Expressions (PCRE)\](https://learnxinyminutes.com/docs/pcre/).  
805 |   
806 | The pattern \`requests.get("=\~/dev\\./i")\` matches:  
807 |   
808 | \`\`\`python  
809 | requests.get("api.dev.corp.com")  \# Oops, development API left in  
810 | \`\`\`  
811 |   
812 | To search for specific strings, use the syntax \`"=\~/\<regexp\>/"\`. Advanced regexp features are available, such as case-insensitive regexps with \`'/i'\` (e.g., \`"=\~/foo/i"\`). Matching occurs anywhere in the string unless the regexp \`^\` anchor character is used: \`"=\~/^foo.\*/"\` checks if a string begins with \`foo\`.  
813 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/private-rules.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | slug: private-rules  
  3 | description: "Semgrep Code users can publish rules to the Semgrep Registry that are not visible to others outside their organization. This can be useful for organizations where rules may contain code-sensitive information or legal requirements prevent using a public registry."  
  4 | tags:  
  5 |   \- Rule writing  
  6 | \---  
  7 |   
  8 |   
  9 | import DeleteCustomRule from "/src/components/procedure/\_delete-custom-rule.mdx"  
 10 |   
 11 | \# Private rules  
 12 |   
 13 | Users of the \[Team or Enterprise tier\](https://semgrep.dev/pricing) of Semgrep Code can publish rules to the \[Semgrep Registry\](https://semgrep.dev/explore) as private rules that are not visible to others outside their organization. Maintaining the rules' privacy allows you the benefits of using the Semgrep Registry while keeping sensitive code or information internal.  
 14 |   
 15 | \#\# Creating private rules  
 16 |   
 17 | Create private rules the same way you create other custom rules. Private rules are stored in Semgrep Registry but they are not visible outside your organization. The two sections below can help you to create and save your private rules.  
 18 |   
 19 | :::info Prerequisite  
 20 | \[Team or Enterprise tier\](https://semgrep.dev/pricing) of Semgrep Code.  
 21 | :::  
 22 |   
 23 | \#\#\# Creating private rules through Semgrep AppSec Platform  
 24 |   
 25 | To publish private rules through the Semgrep AppSec Platform:  
 26 |   
 27 | 1\. Go to \[Semgrep Editor\](https://semgrep.dev/orgs/-/editor).  
 28 | 1\. Click \<i className="fa-solid fa-file-plus-minus inline\_svg"\>\</i\> \*\*Create New Rule\*\*.  
 29 | 1\. Choose one of the following:  
 30 |     \- Create a new rule and test code by clicking \<i class="fa-solid fa-circle-plus"\>\</i\> \*\*plus\*\* icon, select \*\*New rule\*\*, and then click \<i className="fa-solid fa-floppy-disk inline\_svg"\>\</i\> \*\*Save\*\*.  
 31 |     \- In the \<i class="fa-solid fa-server"\>\</i\> \*\*Library\*\* panel, select a rule from a category in \*\*Semgrep Registry\*\*. Click \<i className="fa-solid fa-code-branch inline\_svg"\>\</i\> \*\*Fork\*\*, modify the rule or test code, and then click \<i className="fa-solid fa-floppy-disk inline\_svg"\>\</i\> \*\*Save\*\*.  
 32 | 1\. Click \<i className="fa-solid fa-earth-americas inline\_svg"\>\</i\> \*\*Share\*\*.  
 33 | 1\. Click \<i className="fa-solid fa-lock inline\_svg"\>\</i\> \*\*Private\*\*.  
 34 |   
 35 | Your private rule has been created and added to the Registry, visible only to logged in users of your organization. Its private status is reflected by the \*\*Share\*\* button displaying a \<i className="fa-solid fa-lock inline\_svg"\>\</i\> icon.  
 36 |   
 37 | Private rules are stored in the folder with the same name as your Semgrep AppSec Platform organization.  
 38 |   
 39 | \#\#\# Creating private rules through the command line  
 40 |   
 41 | To create private rules through the \[Semgrep CLI\](/getting-started/quickstart), :  
 42 |   
 43 | 1\. Interactively login to Semgrep:  
 44 |   
 45 |     \`\`\`sh  
 46 |     semgrep login  
 47 |     \`\`\`  
 48 | 1\. Create your rule. For more information, see \[Contributing rules\](/contributing/contributing-to-semgrep-rules-repository) documentation.  
 49 | 1\. Publish your rule from the command line with \`semgrep publish\` command followed by the path to your private rules:  
 50 |   
 51 |     \`\`\`sh  
 52 |     semgrep publish myrules/  
 53 |     \`\`\`  
 54 |   
 55 | If the rules are in the directory you publish from, you can use \`semgrep publish .\` to refer to the current directory. You must provide the directory specification.  
 56 | If the directory contains test cases for the rules, Semgrep uploads them as well (see \[testing Semgrep rules\](/writing-rules/testing-rules)).  
 57 |   
 58 | You can also change the visibility of the rules. For instance, to publish the rules as unlisted (which does not require authentication but will not be displayed in the public registry):  
 59 |   
 60 | \`\`\`sh  
 61 | semgrep publish \--visibility=unlisted myrules/  
 62 | \`\`\`  
 63 |   
 64 | For more details, run \`semgrep publish \--help\`.  
 65 |   
 66 | \#\# Viewing and using private rules  
 67 |   
 68 | View your rule in the \[editor\](https://semgrep.dev/orgs/-/editor) under the folder corresponding to your organization name.  
 69 |   
 70 | You can also find it in the \[registry\](https://semgrep.dev/explore) by searching for \[organization-id\].\[rule-id\]. For example: \`r2c.test-rule-id\`.  
 71 |   
 72 | To enforce the rule on new scans, add the rule in the \[registry\](https://semgrep.dev/explore) to an existing policy.  
 73 |   
 74 | \#\# Automatically publishing rules  
 75 |   
 76 | This section provides examples of how to automatically publish your private rules so they are accessible within your private organization. "Publishing" your private rules in this manner does not make them public. In the following examples, the private rules are stored in \`private\_rule\_dir\`, which is a subdirectory of the repository root. If your rules are in the root of your repository, you can replace the command with \`semgrep publish \--visibility=org\_private .\` to refer to the repository root. You must provide the directory specification.  
 77 |   
 78 | The following sample of the GitHub Actions workflow publishes rules from a private Git repository after a merge to the \`main\`, \`master\`, or \`develop\` branches.  
 79 |   
 80 | 1\. Make sure that \`SEMGREP\_APP\_TOKEN\` is defined in your GitHub project or organization's secrets.  
 81 | 2\. Create the following file at \`.github/workflows/semgrep-publish.yml\`:  
 82 |     \`\`\`yaml  
 83 |     name: semgrep-publish  
 84 |   
 85 |     on:  
 86 |       push:  
 87 |         branches:  
 88 |         \- main  
 89 |         \- master  
 90 |         \- develop  
 91 |   
 92 |     jobs:  
 93 |       publish:  
 94 |         name: publish-private-semgrep-rules  
 95 |         runs-on: ubuntu-latest  
 96 |         container:  
 97 |           image: semgrep/semgrep  
 98 |         steps:  
 99 |         \- uses: actions/checkout@v4  
100 |         \- name: publish private semgrep rules  
101 |           run: semgrep publish \--visibility=org\_private ./private\_rule\_dir  
102 |           env:  
103 |             SEMGREP\_APP\_TOKEN: ${{ secrets.SEMGREP\_APP\_TOKEN }}  
104 |     \`\`\`  
105 |   
106 |     A sample job for GitLab CI/CD:  
107 |   
108 |     \`\`\`yaml  
109 |     semgrep-publish:  
110 |       image: semgrep/semgrep  
111 |       script: semgrep publish \--visibility=org\_private ./private\_rule\_dir  
112 |   
113 |     rules:  
114 |       \- if: $CI\_COMMIT\_BRANCH \== $CI\_DEFAULT\_BRANCH  
115 |   
116 |     variables:  
117 |       SEMGREP\_APP\_TOKEN: $SEMGREP\_APP\_TOKEN  
118 |     \`\`\`  
119 |   
120 |     Ensure that \`SEMGREP\_APP\_TOKEN\` is defined in your GitLab project's CI/CD variables.  
121 |   
122 | \#\# Deleting private rules  
123 |   
124 | \<DeleteCustomRule /\>  
125 |   
126 | \#\# Appendix  
127 |   
128 | \#\#\# Visibility of private rules  
129 |   
130 | Private rules are only visible to logged-in members of your organization.  
131 |   
132 | \#\#\# Publishing a rule with the same rule ID  
133 |   
134 | Rules have unique IDs. If you publish a rule with the same ID as an existing rule, the new rule overwrites the previous one.  
135 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/rule-ideas.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | append\_help\_link: true  
  3 | slug: rule-ideas  
  4 | tags:  
  5 |   \- Rule writing  
  6 | \---  
  7 |   
  8 | \# Custom rule examples  
  9 |   
 10 | Not sure what to write a rule for? Below are some common questions, ideas, and topics to spur your imagination. Happy hacking\! 💡  
 11 |   
 12 | \#\# Use cases  
 13 |   
 14 | \#\#\# Automate code review comments  
 15 |   
 16 | \_Time to write this rule: \*\*5 minutes\*\*\_  
 17 |   
 18 | You can use Semgrep and its GitHub integration to \[automate PR comments\](/semgrep-appsec-platform/notifications) that you frequently make in code reviews. Writing a custom rule for the code pattern you want to target is usually straightforward. If you want to understand the Semgrep syntax, see the \[documentation\](/writing-rules/pattern-syntax) or try the \[tutorial\](https://semgrep.dev/learn).  
 19 |   
 20 | \!\[A reviewer writes a Semgrep rule and adds it to an organization-wide policy\](/img/semgrep-ci.gif)  
 21 | \<br /\>  
 22 | A reviewer writes a Semgrep rule and adds it to an organization-wide policy.  
 23 |   
 24 |   
 25 | \#\#\# Ban dangerous APIs  
 26 |   
 27 | \_Time to write this rule: \*\*5 minutes\*\*\_  
 28 |   
 29 | Semgrep can detect dangerous APIs in code. If integrated into CI/CD pipelines, you can use Semgrep to block merges or flag for review when someone adds such dangerous APIs to the code. For example, a rule that detects React's \`dangerouslySetInnerHTML\` looks like this.  
 30 |   
 31 | \<iframe src="https://semgrep.dev/embed/editor?snippet=zEXn" title="Ban dangerous APIs with Semgrep" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
 32 |   
 33 | \#\#\# Exempting special cases of dangerous APIs  
 34 |   
 35 | \_Time to write this rule: \*\*5 minutes\*\*\_  
 36 |   
 37 | If you have a legitimate use case for a dangerous API, you can exempt a specific use of the API using a \`nosemgrep\` comment. The rule below checks for React's \`dangerouslySetInnerHTML\`, but the code is annotated with a \`nosemgrep\` comment. Semgrep will not detect this line. This allows Semgrep to continuously check for future uses of \`dangerouslySetInnerHTML\` while allowing for this specific use.  
 38 |   
 39 | \<iframe src="https://semgrep.dev/embed/editor?snippet=2B3r" title="Exempt special cases of dangerous APIs with Semgrep" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
 40 |   
 41 | \#\#\# Detect tainted data flowing into a dangerous sink  
 42 |   
 43 | \_Time to write this rule: \*\*5 minutes\*\*\_  
 44 |   
 45 | Semgrep's \[dataflow engine with support for taint tracking\](/writing-rules/data-flow/data-flow-overview) can be used to detect when data flows from a user-provided value into a security-sensitive function.  
 46 |   
 47 | This rule detects when a user of the ExpressJS framework passes user data into the \`run()\` method of a sandbox.  
 48 |   
 49 | \<iframe src="https://semgrep.dev/embed/editor?snippet=jEGP" title="ExpressJS dataflow to sandbox.run" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
 50 |   
 51 |   
 52 | \#\#\# Detect security violations  
 53 |   
 54 | \_Time to write this rule: \*\*5 minutes\*\*\_  
 55 |   
 56 | Use Semgrep to flag specific uses of APIs too, not just their presence in code. We jokingly call these the "security off" buttons and make extensive use of Semgrep to detect them.  
 57 |   
 58 | This rule detects when HTML auto escaping is explicitly disabled for a Django template.  
 59 |   
 60 | \<iframe src="https://semgrep.dev/embed/editor?snippet=9Yjy" title="Detect security violations in code with Semgrep" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
 61 |   
 62 |   
 63 | \#\#\# Scan configuration files using JSON, YAML, or Generic pattern matching  
 64 |   
 65 | \_Time to write this rule: \*\*10 minutes\*\*\_  
 66 |   
 67 | Semgrep \[natively supports JSON and YAML\](../supported-languages.md) and can be used to write rules for configuration files. This rule checks for skipped TLS verification in Kubernetes clusters.  
 68 |   
 69 | \<iframe src="https://semgrep.dev/embed/editor?snippet=rEqJ" title="Match configuration files with Semgrep" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
 70 |   
 71 | The \[Generic pattern matching\](/writing-rules/generic-pattern-matching) mode is for languages and file formats that Semgrep does not natively support. For example, you can write rules for Dockerfiles using the generic mode. The Dockerfile rule below checks for invalid port numbers.  
 72 |   
 73 | \<iframe src="https://semgrep.dev/embed/editor?snippet=NGXN" title="Match Dockerfiles with Semgrep" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
 74 |   
 75 |   
 76 | \#\#\# Enforce authentication patterns  
 77 |   
 78 | \_Time to write this rule: \*\*15 minutes\*\*\_  
 79 |   
 80 | If a project has a "correct" way of doing authentication, Semgrep can be used to enforce this so that authentication mishaps do not happen. In the example below, this Flask app requires an authentication decorator on all routes. The rule detects routes that are missing authentication decorators. If deployed in CI/CD pipelines, Semgrep can block undecorated routes or flag a security member for further investigation.  
 81 |   
 82 | \<iframe src="https://semgrep.dev/embed/editor?snippet=wEQd" title="Enforce authentication patterns in code with Semgrep" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
 83 |   
 84 |   
 85 | \#\#\# Systematize project-specific coding patterns  
 86 |   
 87 | \_Time to write this rule: \*\*10 minutes\*\*\_  
 88 |   
 89 | Automate institutional knowledge using Semgrep. This has several benefits, including teaching new members about coding patterns in an automatic way and keeping a project up-to-date with coding patterns. If you keep coding guidelines in a document, converting these into Semgrep rules is a great way to free developers from having to remember all the guidelines.  
 90 |   
 91 | In this example, a legacy API requires calling \`verify\_transaction(t)\` before calling \`make\_transaction(t)\`. The Semgrep rule below detects when these methods are not called correctly.  
 92 |   
 93 | \<iframe src="https://semgrep.dev/embed/editor?snippet=Nr3z" title="Systematize project-specific coding patterns with Semgrep" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
 94 |   
 95 |   
 96 | \#\#\# Extract information with metavariables  
 97 |   
 98 | \_Time to write this rule: \*\*15 minutes\*\*\_  
 99 |   
100 | Semgrep metavariables can be used as output in the \`message\` key. This can be used to extract and collate information about a codebase. Click through to \[this example\](https://semgrep.dev/s/ORpk) which extracts Java Spring routes. This can be used to quickly see all the exposed routes of an application.  
101 |   
102 |   
103 | \#\#\# Burn down deprecated APIs  
104 |   
105 | \_Time to write this rule: \*\*5 minutes\*\*\_  
106 |   
107 | Semgrep can detect deprecated APIs just as easily as dangerous APIs. Identifying deprecated API calls can help an application migrate to current or future versions.  
108 |   
109 | This rule example detects a function that is deprecated as of Django 4.0.  
110 |   
111 | \<iframe src="https://semgrep.dev/embed/editor?snippet=vEQ0" title="Burn down deprecated APIs with Semgrep" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
112 |   
113 |   
114 | \#\#\# Promote secure alternatives  
115 |   
116 | \_Time to write this rule: \*\*5 minutes\*\*\_  
117 |   
118 | Some libraries or APIs have safe alternatives, such as \[Google's \`re2\`\](https://github.com/google/re2), an implementation of the standard \`re\` interface that ships with Python that is resistant to regular expression denial-of-service. This rule detects the use of \`re\` and recommends \`re2\` as a safe alternative with the same interface.  
119 |   
120 | \<iframe src="https://semgrep.dev/embed/editor?snippet=ZoA4" title="Promote secure alternatives with Semgrep" width="100%" height="432px" frameBorder="0"\>\</iframe\>  
121 |   
122 |   
123 | \#\# Prompts for writing custom rules  
124 |   
125 | Try answering these questions to uncover important rules for your project.  
126 |   
127 | 1\. From recent post mortems: what code issues contributed to it?  
128 | 1\. \[XYZ\] is a (security, performance, other) library that everyone should use, but they don’t consistently.  
129 | 1\. When you review code, what changes do you frequently ask for?  
130 | 1\. What vulnerability classes from bug bounty submissions reoccur (or appear in different places of the codebase)?  
131 | 1\. Are there engineering or performance patterns? Consistent exception handlers?  
132 | 1\. What issues were caused by misconfigurations in Infrastructure-as-Code files (JSON)?  
133 | 1\. What are some “invariants” that should hold about your code \- things that should always or never be true (e.g. every admin route checks if user is admin)?  
134 | 1\. What methods/APIs are deprecated and you’re trying to move away from?  
135 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/rule-syntax.md:  
\--------------------------------------------------------------------------------  
   1 | \---  
   2 | append\_help\_link: true  
   3 | slug: rule-syntax  
   4 | description: "This document describes the YAML rule syntax of Semgrep including required and optional fields. Just getting started with Semgrep rule writing? Check out the Semgrep Tutorial at https://semgrep.dev/learn"  
   5 | tags:  
   6 |   \- Rule writing  
   7 | \---  
   8 |   
   9 |   
  10 | import LanguageExtensionsLanguagesKeyValues from '/src/components/reference/\_language-extensions-languages-key-values.mdx'  
  11 | import RequiredRuleFields from "/src/components/reference/\_required-rule-fields.mdx"  
  12 |   
  13 | \# Rule syntax  
  14 |   
  15 | :::tip  
  16 | Getting started with rule writing? Try the \[Semgrep Tutorial\](https://semgrep.dev/learn) 🎓  
  17 | :::  
  18 |   
  19 | This document describes the YAML rule syntax of Semgrep.  
  20 |   
  21 | \#\# Schema  
  22 |   
  23 | \#\#\# Required  
  24 |   
  25 | \<RequiredRuleFields /\>  
  26 |   
  27 | \#\#\#\# Language extensions and languages key values  
  28 |   
  29 | \<LanguageExtensionsLanguagesKeyValues /\>  
  30 |   
  31 | \#\#\# Optional  
  32 |   
  33 | | Field      | Type     | Description                         |  
  34 | | :--------- | :------- | :---------------------------------- |  
  35 | | \[\`options\`\](\#options)   | \`object\` | Options object to enable/disable certain matching features |  
  36 | | \[\`fix\`\](\#fix)           | \`object\` | Simple search-and-replace autofix functionality  |  
  37 | | \[\`metadata\`\](\#metadata) | \`object\` | Arbitrary user-provided data; attach data to rules without affecting Semgrep behavior |  
  38 | | \[\`min-version\`\](\#min-version-and-max-version) | \`string\` | Minimum Semgrep version compatible with this rule |  
  39 | | \[\`max-version\`\](\#min-version-and-max-version) | \`string\` | Maximum Semgrep version compatible with this rule |  
  40 | | \[\`paths\`\](\#paths)       | \`object\` | Paths to include or exclude when running this rule |  
  41 |   
  42 | The below optional fields must reside underneath a \`patterns\` or \`pattern-either\` field.  
  43 |   
  44 | | Field                | Type     | Description              |  
  45 | | :------------------- | :------- | :----------------------- |  
  46 | | \[\`pattern-inside\`\](\#pattern-inside)             | \`string\` | Keep findings that lie inside this pattern                                                                              |  
  47 |   
  48 | The below optional fields must reside underneath a \`patterns\` field.  
  49 |   
  50 | \<\!-- markdown-link-check-disable \--\>  
  51 |   
  52 | | Field            | Type     | Description           |  
  53 | | :--------------- | :------- | :-------------------- |  
  54 | | \[\`metavariable-regex\`\](\#metavariable-regex)         | \`map\` | Search metavariables for \[Python \`re\`\](https://docs.python.org/3/library/re.html\#re.match) compatible expressions; regex matching is \*\*left anchored\*\* |  
  55 | | \[\`metavariable-pattern\`\](\#metavariable-pattern)     | \`map\` | Matches metavariables with a pattern formula |  
  56 | | \[\`metavariable-comparison\`\](\#metavariable-comparison) | \`map\` | Compare metavariables against basic \[Python expressions\](https://docs.python.org/3/reference/expressions.html\#comparisons) |  
  57 | | \[\`metavariable-name\`\](\#metavariable-name) | \`map\` | Matches metavariables against constraints on what they name |  
  58 | | \[\`pattern-not\`\](\#pattern-not) | \`string\` | Logical NOT \- remove findings matching this expression |  
  59 | | \[\`pattern-not-inside\`\](\#pattern-not-inside) | \`string\` | Keep findings that do not lie inside this pattern |  
  60 | | \[\`pattern-not-regex\`\](\#pattern-not-regex) | \`string\` | Filter results using a \[PCRE2\](https://www.pcre.org/current/doc/html/pcre2pattern.html)-compatible pattern in multiline mode |  
  61 |   
  62 | \<\!-- markdown-link-check-enable \--\>  
  63 |   
  64 | \#\# Operators  
  65 |   
  66 | \#\#\# \`pattern\`  
  67 |   
  68 | The \`pattern\` operator looks for code matching its expression. This can be basic expressions like \`$X \== $X\` or unwanted function calls like \`hashlib.md5(...)\`.  
  69 |   
  70 | \`\`\`yaml  
  71 | rules:  
  72 |   \- id: md5-usage  
  73 |     languages:  
  74 |       \- python  
  75 |     message: Found md5 usage  
  76 |     pattern: hashlib.md5(...)  
  77 |     severity: ERROR  
  78 | \`\`\`  
  79 |   
  80 | The pattern immediately above matches the following:  
  81 |   
  82 | \`\`\`python  
  83 | import hashlib  
  84 | \# ruleid: md5-usage  
  85 | \# highlight-next-line  
  86 | digest \= hashlib.md5(b"test")  
  87 | \# ok: md5-usage  
  88 | digest \= hashlib.sha256(b"test")  
  89 | \`\`\`  
  90 |   
  91 | \#\#\# \`patterns\`  
  92 |   
  93 | The \`patterns\` operator performs a logical AND operation on one or more child patterns. This is useful for chaining multiple patterns together that all must be true.  
  94 |   
  95 | \`\`\`yaml  
  96 | rules:  
  97 |   \- id: unverified-db-query  
  98 |     patterns:  
  99 |       \- pattern: db\_query(...)  
 100 |       \- pattern-not: db\_query(..., verify=True, ...)  
 101 |     message: Found unverified db query  
 102 |     severity: ERROR  
 103 |     languages:  
 104 |       \- python  
 105 | \`\`\`  
 106 |   
 107 | The pattern immediately above matches the following:  
 108 |   
 109 | \`\`\`python  
 110 | \# ruleid: unverified-db-query  
 111 | \# highlight-next-line  
 112 | db\_query("SELECT \* FROM ...")  
 113 | \# ok: unverified-db-query  
 114 | db\_query("SELECT \* FROM ...", verify=True, env="prod")  
 115 | \`\`\`  
 116 |   
 117 | \#\#\#\# \`patterns\` operator evaluation strategy  
 118 |   
 119 | Note that the order in which the child patterns are declared in a \`patterns\` operator has no effect on the final result. A \`patterns\` operator is always evaluated in the same way:  
 120 |   
 121 | 1\. Semgrep evaluates all \_positive\_ patterns, that is \[\`pattern-inside\`\](\#pattern-inside)s, \[\`pattern\`\](\#pattern)s, \[\`pattern-regex\`\](\#pattern-regex)es, and \[\`pattern-either\`\](\#pattern-either)s. Each range matched by each one of these patterns is intersected with the ranges matched by the other operators. The result is a set of \_positive\_ ranges. The positive ranges carry \_metavariable bindings\_. For example, in one range \`$X\` can be bound to the function call \`foo()\`, and in another range \`$X\` can be bound to the expression \`a \+ b\`.  
 122 | 2\. Semgrep evaluates all \_negative\_ patterns, that is \[\`pattern-not-inside\`\](\#pattern-not-inside)s, \[\`pattern-not\`\](\#pattern-not)s, and \[\`pattern-not-regex\`\](\#pattern-not-regex)es. This gives a set of \_negative ranges\_ which are used to filter the positive ranges. This results in a strict subset of the positive ranges computed in the previous step.  
 123 | 3\. Semgrep evaluates all \_conditionals\_, that is \[\`metavariable-regex\`\](\#metavariable-regex)es, \[\`metavariable-pattern\`\](\#metavariable-pattern)s and \[\`metavariable-comparison\`\](\#metavariable-comparison)s. These conditional operators can only examine the metavariables bound in the positive ranges in step 1, that passed through the filter of negative patterns in step 2\. Note that metavariables bound by negative patterns are \_not\_ available here.  
 124 | 4\. Semgrep applies all \[\`focus-metavariable\`\](\#focus-metavariable)s, by computing the intersection of each positive range with the range of the metavariable on which we want to focus. Again, the only metavariables available to focus on are those bound by positive patterns.  
 125 |   
 126 | \<\!-- TODO: Add example to illustrate all of the above \--\>  
 127 |   
 128 | \#\#\# \`pattern-either\`  
 129 |   
 130 | The \`pattern-either\` operator performs a logical OR operation on one or more child patterns. This is useful for chaining multiple patterns together where any may be true.  
 131 |   
 132 | \`\`\`yaml  
 133 | rules:  
 134 |   \- id: insecure-crypto-usage  
 135 |     pattern-either:  
 136 |       \- pattern: hashlib.sha1(...)  
 137 |       \- pattern: hashlib.md5(...)  
 138 |     message: Found insecure crypto usage  
 139 |     languages:  
 140 |       \- python  
 141 |     severity: ERROR  
 142 | \`\`\`  
 143 |   
 144 | The pattern immediately above matches the following:  
 145 |   
 146 | \`\`\`python  
 147 | import hashlib  
 148 | \# ruleid: insecure-crypto-usage  
 149 | \# highlight-next-line  
 150 | digest \= hashlib.md5(b"test")  
 151 | \# ruleid: insecure-crypto-usage  
 152 | \# highlight-next-line  
 153 | digest \= hashlib.sha1(b"test")  
 154 | \# ok: insecure-crypto-usage  
 155 | digest \= hashlib.sha256(b"test")  
 156 | \`\`\`  
 157 |   
 158 | This rule looks for usage of the Python standard library functions \`hashlib.md5\` or \`hashlib.sha1\`. Depending on their usage, these hashing functions are \[considered insecure\](https://shattered.io/).  
 159 |   
 160 | \#\#\# \`pattern-regex\`  
 161 |   
 162 | \<\!-- markdown-link-check-disable \--\>  
 163 | The \`pattern-regex\` operator searches files for substrings matching the given \[PCRE2\](https://www.pcre.org/current/doc/html/pcre2pattern.html) pattern. This is useful for migrating existing regular expression code search functionality to Semgrep. Perl-Compatible Regular Expressions (PCRE) is a full-featured regex library that is widely compatible with Perl, but also with the respective regex libraries of Python, JavaScript, Go, Ruby, and Java. Patterns are compiled in multiline mode, for example \`^\` and \`

      
    

      
    

      
      
      
    

      
      
      
    

   

                 Format: HTML                 Format: JSON                 Format: YAML                 Format: Text             

    

    

    

   

         matches at the beginning and end of lines respectively in addition to the beginning and end of input.  
 164 |   
 165 | :::caution  
 166 | PCRE2 supports \[some Unicode character properties, but not some Perl properties\](https://www.pcre.org/current/doc/html/pcre2pattern.html\#uniextseq). For example, \`\\p{Egyptian\_Hieroglyphs}\` is supported but \`\\p{InMusicalSymbols}\` isn't.  
 167 | :::  
 168 |   
 169 | \<\!-- markdown-link-check-enable \--\>  
 170 | \#\#\#\# Example: \`pattern-regex\` combined with other pattern operators  
 171 |   
 172 | \`\`\`yaml  
 173 | rules:  
 174 |   \- id: boto-client-ip  
 175 |     patterns:  
 176 |       \- pattern-inside: boto3.client(host="...")  
 177 |       \- pattern-regex: \\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}  
 178 |     message: boto client using IP address  
 179 |     languages:  
 180 |       \- python  
 181 |     severity: ERROR  
 182 | \`\`\`  
 183 |   
 184 | The pattern immediately above matches the following:  
 185 |   
 186 | \`\`\`python  
 187 | import boto3  
 188 | \# ruleid: boto-client-ip  
 189 | \# highlight-next-line  
 190 | client \= boto3.client(host="192.168.1.200")  
 191 | \# ok: boto-client-ip  
 192 | client \= boto3.client(host="dev.internal.example.com")  
 193 | \`\`\`  
 194 |   
 195 | \#\#\#\# Example: \`pattern-regex\` used as a standalone, top-level operator  
 196 | \`\`\`yaml  
 197 | rules:  
 198 |   \- id: legacy-eval-search  
 199 |     pattern-regex: eval\\(  
 200 |     message: Insecure code execution  
 201 |     languages:  
 202 |       \- javascript  
 203 |     severity: ERROR  
 204 | \`\`\`  
 205 |   
 206 | The pattern immediately above matches the following:  
 207 |   
 208 | \`\`\`python  
 209 | \# ruleid: legacy-eval-search  
 210 | \# highlight-next-line  
 211 | eval('var a \= 5')  
 212 | \`\`\`  
 213 |   
 214 | :::info  
 215 | Single (\`'\`) and double (\`"\`) quotes \[behave differently\](https://docs.octoprint.org/en/master/configuration/yaml.html\#scalars) in YAML syntax. Single quotes are typically preferred when using backslashes (\`\\\`) with \`pattern-regex\`.  
 216 | :::  
 217 |   
 218 | Note that you may bind a section of a regular expression to a metavariable, by using \[named capturing groups\](https://www.regular-expressions.info/named.html). In  
 219 | this case, the name of the capturing group must be a valid metavariable name.  
 220 |   
 221 | \`\`\`yaml  
 222 | rules:  
 223 |   \- id: my\_pattern\_id-copy  
 224 |     patterns:  
 225 |       \- pattern-regex: a(?P\<FIRST\>.\*)b(?P\<SECOND\>.\*)  
 226 |     message: Semgrep found a match, with $FIRST and $SECOND  
 227 |     languages:  
 228 |       \- regex  
 229 |     severity: WARNING  
 230 | \`\`\`  
 231 |   
 232 | The pattern immediately above matches the following:  
 233 |   
 234 | \`\`\`python  
 235 | \# highlight-next-line  
 236 | acbd  
 237 | \`\`\`  
 238 |   
 239 | \#\#\# \`pattern-not-regex\`  
 240 |   
 241 | \<\!-- markdown-link-check-disable \--\>  
 242 |   
 243 | The \`pattern-not-regex\` operator filters results using a \[PCRE2\](https://www.pcre.org/current/doc/html/pcre2pattern.html) regular expression in multiline mode. This is most useful when combined with regular-expression only rules, providing an easy way to filter findings without having to use negative lookaheads. \`pattern-not-regex\` works with regular \`pattern\` clauses, too.  
 244 |   
 245 | \<\!-- markdown-link-check-enable \--\>  
 246 |   
 247 | The syntax for this operator is the same as \`pattern-regex\`.  
 248 |   
 249 | This operator filters findings that have \_any overlap\_ with the supplied regular expression. For example, if you use \`pattern-regex\` to detect \`Foo==1.1.1\` and it also detects \`Foo-Bar==3.0.8\` and \`Bar-Foo==3.0.8\`, you can use \`pattern-not-regex\` to filter the unwanted findings.  
 250 |   
 251 | \`\`\`yaml  
 252 | rules:  
 253 |   \- id: detect-only-foo-package  
 254 |     languages:  
 255 |       \- regex  
 256 |     message: Found foo package  
 257 |     patterns:  
 258 |       \- pattern-regex: foo  
 259 |       \- pattern-not-regex: foo-  
 260 |       \- pattern-not-regex: \-foo  
 261 |     severity: ERROR  
 262 | \`\`\`  
 263 |   
 264 | The pattern immediately above matches the following:  
 265 |   
 266 | \`\`\`python  
 267 | \# ruleid: detect-only-foo-package  
 268 | \# highlight-next-line  
 269 | foo==1.1.1  
 270 | \# ok: detect-only-foo-package  
 271 | foo-bar==3.0.8  
 272 | \# ok: detect-only-foo-package  
 273 | bar-foo==3.0.8  
 274 | \`\`\`  
 275 |   
 276 | \#\#\# \`focus-metavariable\`  
 277 |   
 278 | The \`focus-metavariable\` operator puts the focus, or \_zooms in\_, on the code region matched by a single metavariable or a list of metavariables. For example, to find all functions arguments annotated with the type \`bad\` you may write the following pattern:  
 279 |   
 280 | \`\`\`yaml  
 281 | pattern: |  
 282 |   def $FUNC(..., $ARG : bad, ...):  
 283 |     ...  
 284 | \`\`\`  
 285 |   
 286 | This works but it matches the entire function definition. Sometimes, this is not desirable. If the definition spans hundreds of lines they are all matched. In particular, if you are using \[Semgrep AppSec Platform\](https://semgrep.dev/login) and you have triaged a finding generated by this pattern, the same finding shows up again as new if you make any change to the definition of the function\!  
 287 |   
 288 | To specify that you are only interested in the code matched by a particular metavariable, in our example \`$ARG\`, use \`focus-metavariable\`.  
 289 |   
 290 | \`\`\`yaml  
 291 | rules:  
 292 |   \- id: find-bad-args  
 293 |     patterns:  
 294 |       \- pattern: |  
 295 |           def $FUNC(..., $ARG : bad, ...):  
 296 |             ...  
 297 |       \- focus-metavariable: $ARG  
 298 |     message: |  
 299 |       \`$ARG' has a "bad" type\!  
 300 |     languages:  
 301 |       \- python  
 302 |     severity: WARNING  
 303 | \`\`\`  
 304 |   
 305 | The pattern immediately above matches the following:  
 306 |   
 307 | \`\`\`python  
 308 | \# highlight-next-line  
 309 | def f(x : bad):  
 310 |     return x  
 311 | \`\`\`  
 312 |   
 313 | Note that \`focus-metavariable: $ARG\` is not the same as \`pattern: $ARG\`\! Using \`pattern: $ARG\` finds all the uses of the parameter \`x\` which is not what we want\! (Note that \`pattern: $ARG\` does not match the formal parameter declaration, because in this context \`$ARG\` only matches expressions.)  
 314 |   
 315 | \`\`\`yaml  
 316 | rules:  
 317 |   \- id: find-bad-args  
 318 |     patterns:  
 319 |       \- pattern: |  
 320 |           def $FUNC(..., $ARG : bad, ...):  
 321 |             ...  
 322 |       \- pattern: $ARG  
 323 |     message: |  
 324 |       \`$ARG' has a "bad" type\!  
 325 |     languages:  
 326 |       \- python  
 327 |     severity: WARNING  
 328 | \`\`\`  
 329 |   
 330 | The pattern immediately above matches the following:  
 331 |   
 332 | \`\`\`python  
 333 | def f(x : bad):  
 334 | \# highlight-next-line  
 335 |     return x  
 336 | \`\`\`  
 337 |   
 338 | In short, \`focus-metavariable: $X\` is not a pattern in itself, it does not perform any matching, it only focuses the matching on the code already bound to \`$X\` by other patterns. Whereas \`pattern: $X\` matches \`$X\` against your code (and in this context, \`$X\` only matches expressions)\!  
 339 |   
 340 | \#\#\#\# Including multiple focus metavariables using set intersection semantics  
 341 |   
 342 | Include more \`focus-metavariable\` keys with different metavariables under the \`pattern\` to match results \*\*only\*\* for the overlapping region of all the focused code:  
 343 |   
 344 | \`\`\`yaml  
 345 |     patterns:  
 346 |       \- pattern: foo($X, ..., $Y)  
 347 |       \- focus-metavariable:  
 348 |         \- $X  
 349 |         \- $Y  
 350 | \`\`\`  
 351 |   
 352 | \`\`\`yaml  
 353 | rules:  
 354 |   \- id: intersect-focus-metavariable  
 355 |     patterns:  
 356 |       \- pattern-inside: foo($X, ...)  
 357 |       \- focus-metavariable: $X  
 358 |       \- pattern: $Y \+ ...  
 359 |       \- focus-metavariable: $Y  
 360 |       \- pattern: "1"  
 361 |     message: Like set intersection, only the overlapping region is highilighted  
 362 |     languages:  
 363 |       \- python  
 364 |     severity: ERROR  
 365 | \`\`\`  
 366 |   
 367 | The pattern immediately above matches the following:  
 368 |   
 369 | \`\`\`python  
 370 | \# ruleid: intersect-focus-metavariable  
 371 | foo (  
 372 | \# highlight-next-line  
 373 |     1  
 374 |     \+  
 375 |     2,  
 376 |     1  
 377 | )  
 378 |   
 379 | \# OK: test  
 380 | foo (2+ 1, 1\)  
 381 | \`\`\`  
 382 |   
 383 | :::info  
 384 | To make a list of multiple focus metavariables using set union semantics that matches the metavariables regardless of their position in code, see \[Including multiple focus metavariables using set union semantics\](/writing-rules/experiments/multiple-focus-metavariables) documentation.  
 385 | :::  
 386 |   
 387 | \#\#\# \`metavariable-regex\`  
 388 |   
 389 | \<\!-- markdown-link-check-disable \--\>  
 390 |   
 391 | The \`metavariable-regex\` operator searches metavariables for a \[PCRE2\](https://www.pcre.org/current/doc/html/pcre2pattern.html) regular expression. This is useful for filtering results based on a \[metavariable’s\](pattern-syntax.mdx\#metavariables) value. It requires the \`metavariable\` and \`regex\` keys and can be combined with other pattern operators.  
 392 |   
 393 | \<\!-- markdown-link-check-enable \--\>  
 394 |   
 395 | \`\`\`yaml  
 396 | rules:  
 397 |   \- id: insecure-methods  
 398 |     patterns:  
 399 |       \- pattern: module.$METHOD(...)  
 400 |       \- metavariable-regex:  
 401 |           metavariable: $METHOD  
 402 |           regex: (insecure)  
 403 |     message: module using insecure method call  
 404 |     languages:  
 405 |       \- python  
 406 |     severity: ERROR  
 407 | \`\`\`  
 408 |   
 409 | The pattern immediately above matches the following:  
 410 |   
 411 | \`\`\`python  
 412 | \# ruleid: insecure-methods  
 413 | \# highlight-next-line  
 414 | module.insecure1("test")  
 415 | \# ruleid: insecure-methods  
 416 | \# highlight-next-line  
 417 | module.insecure2("test")  
 418 | \# ruleid: insecure-methods  
 419 | \# highlight-next-line  
 420 | module.insecure3("test")  
 421 | \# ok: insecure-methods  
 422 | module.secure("test")  
 423 | \`\`\`  
 424 |   
 425 | Regex matching is \*\*left anchored\*\*. To allow prefixes, use \`.\*\` at the beginning of the regex. To match the end of a string, use \`

      
    

      
    

      
      
      
    

      
      
      
    

   

                 Format: HTML                 Format: JSON                 Format: YAML                 Format: Text             

    

    

    

   

        . The next example, using the same expression as above but anchored on the right, finds no matches:  
 426 |   
 427 | \`\`\`yaml  
 428 | rules:  
 429 |   \- id: insecure-methods  
 430 |     patterns:  
 431 |       \- pattern: module.$METHOD(...)  
 432 |       \- metavariable-regex:  
 433 |           metavariable: $METHOD  
 434 |           regex: (insecure$)  
 435 |     message: module using insecure method call  
 436 |     languages:  
 437 |       \- python  
 438 |     severity: ERROR  
 439 | \`\`\`  
 440 |   
 441 | The following example matches all of the function calls in the same code sample, returning a false positive on the \`module.secure\` call:  
 442 |   
 443 | \`\`\`yaml  
 444 | rules:  
 445 |   \- id: insecure-methods  
 446 |     patterns:  
 447 |       \- pattern: module.$METHOD(...)  
 448 |       \- metavariable-regex:  
 449 |           metavariable: $METHOD  
 450 |           regex: (.\*secure)  
 451 |     message: module using insecure method call  
 452 |     languages:  
 453 |       \- python  
 454 |     severity: ERROR  
 455 | \`\`\`  
 456 |   
 457 | :::info  
 458 | Include quotes in your regular expression when using \`metavariable-regex\` to search string literals. For more details, see \[include-quotes\](https://semgrep.dev/playground/s/EbDB) code snippet.  
 459 | :::  
 460 |   
 461 | \#\#\# \`metavariable-pattern\`  
 462 |   
 463 | The \`metavariable-pattern\` operator matches metavariables with a pattern formula. This is useful for filtering results based on a \[metavariable’s\](pattern-syntax.mdx\#metavariables) value. It requires the \`metavariable\` key, and exactly one key of \`pattern\`, \`patterns\`, \`pattern-either\`, or \`pattern-regex\`. This operator can be nested as well as combined with other operators.  
 464 |   
 465 | For example, the \`metavariable-pattern\` can be used to filter out matches that do \*\*not\*\* match certain criteria:  
 466 |   
 467 | \`\`\`yaml  
 468 | rules:  
 469 |   \- id: disallow-old-tls-versions2  
 470 |     languages:  
 471 |       \- javascript  
 472 |     message: Match found  
 473 |     patterns:  
 474 |       \- pattern: |  
 475 |           $CONST \= require('crypto');  
 476 |           ...  
 477 |           $OPTIONS \= $OPTS;  
 478 |           ...  
 479 |           https.createServer($OPTIONS, ...);  
 480 |       \- metavariable-pattern:  
 481 |           metavariable: $OPTS  
 482 |           patterns:  
 483 |             \- pattern-not: \>  
 484 |                 {secureOptions: $CONST.SSL\_OP\_NO\_SSLv2 | $CONST.SSL\_OP\_NO\_SSLv3  
 485 |                 | $CONST.SSL\_OP\_NO\_TLSv1}  
 486 |     severity: WARNING  
 487 | \`\`\`  
 488 |   
 489 | The pattern immediately above matches the following:  
 490 |   
 491 | \`\`\`python  
 492 | function bad() {  
 493 |     // ruleid:disallow-old-tls-versions2  
 494 |     \# highlight-next-line  
 495 |     var constants \= require('crypto');  
 496 |     \# highlight-next-line  
 497 |     var sslOptions \= {  
 498 |     \# highlight-next-line  
 499 |     key: fs.readFileSync('/etc/ssl/private/private.key'),  
 500 |     \# highlight-next-line  
 501 |     secureProtocol: 'SSLv23\_server\_method',  
 502 |     \# highlight-next-line  
 503 |     secureOptions: constants.SSL\_OP\_NO\_SSLv2 | constants.SSL\_OP\_NO\_SSLv3  
 504 |     \# highlight-next-line  
 505 |     };  
 506 |     \# highlight-next-line  
 507 |     https.createServer(sslOptions);  
 508 | }  
 509 | \`\`\`  
 510 |   
 511 | :::info  
 512 | In this case it is possible to start a \`patterns\` AND operation with a \`pattern-not\`, because there is an implicit \`pattern: ...\` that matches the content of the metavariable.  
 513 | :::  
 514 |   
 515 | The \`metavariable-pattern\` is also useful in combination with \`pattern-either\`:  
 516 |   
 517 | \`\`\`yaml  
 518 | rules:  
 519 |   \- id: open-redirect  
 520 |     languages:  
 521 |       \- python  
 522 |     message: Match found  
 523 |     patterns:  
 524 |       \- pattern-inside: |  
 525 |           def $FUNC(...):  
 526 |             ...  
 527 |             return django.http.HttpResponseRedirect(..., $DATA, ...)  
 528 |       \- metavariable-pattern:  
 529 |           metavariable: $DATA  
 530 |           patterns:  
 531 |             \- pattern-either:  
 532 |                 \- pattern: $REQUEST  
 533 |                 \- pattern: $STR.format(..., $REQUEST, ...)  
 534 |                 \- pattern: $STR % $REQUEST  
 535 |                 \- pattern: $STR \+ $REQUEST  
 536 |                 \- pattern: f"...{$REQUEST}..."  
 537 |             \- metavariable-pattern:  
 538 |                 metavariable: $REQUEST  
 539 |                 patterns:  
 540 |                   \- pattern-either:  
 541 |                       \- pattern: request.$W  
 542 |                       \- pattern: request.$W.get(...)  
 543 |                       \- pattern: request.$W(...)  
 544 |                       \- pattern: request.$W\[...\]  
 545 |                   \- metavariable-regex:  
 546 |                       metavariable: $W  
 547 |                       regex: (?\!get\_full\_path)  
 548 |     severity: WARNING  
 549 | \`\`\`  
 550 |   
 551 | The pattern immediately above matches the following:  
 552 |   
 553 | \`\`\`python  
 554 | from django.http import HttpResponseRedirect  
 555 | \# highlight-next-line  
 556 | def unsafe(request):  
 557 |     \# ruleid:open-redirect  
 558 |     \# highlight-next-line  
 559 |     return HttpResponseRedirect(request.POST.get("url"))  
 560 | \`\`\`  
 561 |   
 562 | :::tip  
 563 | It is possible to nest \`metavariable-pattern\` inside \`metavariable-pattern\`\!  
 564 | :::  
 565 |   
 566 | :::info  
 567 | The metavariable should be bound to an expression, a statement, or a list of statements, for this test to be meaningful. A metavariable bound to a list of function arguments, a type, or a pattern, always evaluate to false.  
 568 | :::  
 569 |   
 570 | \#\#\#\# \`metavariable-pattern\` with nested language  
 571 |   
 572 | If the metavariable's content is a string, then it is possible to use \`metavariable-pattern\` to match this string as code by specifying the target language via the \`language\` key. See the following examples of \`metavariable-pattern\`:  
 573 |   
 574 | :::note Examples of \`metavariable-pattern\`  
 575 | \- Match JavaScript code inside HTML in the following \[Semgrep Playground\](https://semgrep.dev/s/z95k) example.  
 576 | \- Filter regex matches in the following \[Semgrep Playground\](https://semgrep.dev/s/pkNk) example.  
 577 | :::  
 578 |   
 579 | \#\#\#\# Example: Match JavaScript code inside HTML  
 580 |   
 581 | \`\`\`yaml  
 582 | rules:  
 583 |   \- id: test  
 584 |     languages:  
 585 |       \- generic  
 586 |     message: javascript inside html working\!  
 587 |     patterns:  
 588 |       \- pattern: |  
 589 |           \<script ...\>$...JS\</script\>  
 590 |       \- metavariable-pattern:  
 591 |           language: javascript  
 592 |           metavariable: $...JS  
 593 |           patterns:  
 594 |             \- pattern: |  
 595 |                 console.log(...)  
 596 |     severity: WARNING  
 597 |   
 598 | \`\`\`  
 599 |   
 600 | The pattern immediately above matches the following:  
 601 |   
 602 | \`\`\`python  
 603 | \<\!-- ruleid:test \--\>  
 604 | \# highlight-next-line  
 605 | \<script\>  
 606 | \# highlight-next-line  
 607 | console.log("hello")  
 608 | \# highlight-next-line  
 609 | \</script\>  
 610 | \`\`\`  
 611 |   
 612 | \#\#\#\# Example: Filter regex matches  
 613 |   
 614 | \`\`\`yaml  
 615 | rules:  
 616 |   \- id: test  
 617 |     languages:  
 618 |       \- generic  
 619 |     message: "Google dependency: $1 $2"  
 620 |     patterns:  
 621 |       \- pattern-regex: gem "(.\*)", "(.\*)"  
 622 |       \- metavariable-pattern:  
 623 |           metavariable: $1  
 624 |           language: generic  
 625 |           patterns:  
 626 |             \- pattern: google  
 627 |     severity: INFO  
 628 | \`\`\`  
 629 |   
 630 | The pattern immediately above matches the following:  
 631 |   
 632 | \`\`\`python  
 633 | \# highlight-next-line  
 634 | source "https://rubygems.org"  
 635 |   
 636 | \#OK:test  
 637 | gem "functions\_framework", "\~\> 0.7"  
 638 | \#ruleid:test  
 639 | \# highlight-next-line  
 640 | gem "google-cloud-storage", "\~\> 1.29"  
 641 | \`\`\`  
 642 |   
 643 | \#\#\# \`metavariable-comparison\`  
 644 |   
 645 | The \`metavariable-comparison\` operator compares metavariables against a basic \[Python comparison\](https://docs.python.org/3/reference/expressions.html\#comparisons) expression. This is useful for filtering results based on a \[metavariable's\](/writing-rules/pattern-syntax/\#metavariables) numeric value.  
 646 |   
 647 | The \`metavariable-comparison\` operator is a mapping which requires the \`metavariable\` and \`comparison\` keys. It can be combined with other pattern operators in the following \[Semgrep Playground\](https://semgrep.dev/s/GWv6) example.  
 648 |   
 649 | This matches code such as \`set\_port(80)\` or \`set\_port(443)\`, but not \`set\_port(8080)\`.  
 650 |   
 651 | Comparison expressions support simple arithmetic as well as composition with \[Boolean operators\](https://docs.python.org/3/reference/expressions.html\#boolean-operations) to allow for more complex matching. This is particularly useful for checking that metavariables are divisible by particular values, such as enforcing that a particular value is even or odd.  
 652 |   
 653 | \`\`\`yaml  
 654 | rules:  
 655 |   \- id: superuser-port  
 656 |     languages:  
 657 |       \- python  
 658 |     message: module setting superuser port  
 659 |     patterns:  
 660 |       \- pattern: set\_port($ARG)  
 661 |       \- metavariable-comparison:  
 662 |           comparison: $ARG \< 1024 and $ARG % 2 \== 0  
 663 |           metavariable: $ARG  
 664 |     severity: ERROR  
 665 | \`\`\`  
 666 |   
 667 | The pattern immediately above matches the following:  
 668 |   
 669 | \`\`\`python  
 670 | \# ok: superuser-port  
 671 | set\_port(443)  
 672 | \# ruleid: superuser-port  
 673 | \# highlight-next-line  
 674 | set\_port(80)  
 675 | \# ok: superuser-port  
 676 | set\_port(8080)  
 677 | \`\`\`  
 678 |   
 679 | Building on the previous example, this still matches code such as \`set\_port(80)\` but it no longer matches \`set\_port(443)\` or \`set\_port(8080)\`.  
 680 |   
 681 | The \`comparison\` key accepts Python expression using:  
 682 |   
 683 | \- Boolean, string, integer, and float literals.  
 684 | \- Boolean operators \`not\`, \`or\`, and \`and\`.  
 685 | \- Arithmetic operators \`+\`, \`-\`, \`\*\`, \`/\`, and \`%\`.  
 686 | \- Comparison operators \`==\`, \`\!=\`, \`\<\`, \`\<=\`, \`\>\`, and \`\>=\`.  
 687 | \- Function \`int()\` to convert strings into integers.  
 688 | \- Function \`str()\` to convert numbers into strings.  
 689 | \- Function \`today()\` that gets today's date as a float representing epoch time.  
 690 | \- Function \`strptime()\` that converts strings in the format \`"yyyy-mm-dd"\` to a float representing the date in epoch time.  
 691 | \- Lists, together with the \`in\`, and \`not in\` infix operators.  
 692 | \- Strings, together with the \`in\` and \`not in\` infix operators, for substring containment.  
 693 | \- Function \`re.match()\` to match a regular expression (without the optional \`flags\` argument).  
 694 |   
 695 | You can use Semgrep metavariables such as \`$MVAR\`, which Semgrep evaluates as follows:  
 696 |   
 697 | \- If \`$MVAR\` binds to a literal, then that literal is the value assigned to \`$MVAR\`.  
 698 | \- If \`$MVAR\` binds to a code variable that is a constant, and constant propagation is enabled (as it is by default), then that constant is the value assigned to \`$MVAR\`.  
 699 | \- Otherwise the code bound to the \`$MVAR\` is kept unevaluated, and its string representation can be obtained using the \`str()\` function, as in \`str($MVAR)\`. For example, if \`$MVAR\` binds to the code variable \`x\`, \`str($MVAR)\` evaluates to the string literal \`"x"\`.  
 700 |   
 701 | \#\#\#\# Legacy \`metavariable-comparison\` keys  
 702 |   
 703 | :::info  
 704 | You can avoid the use of the legacy keys described below (\`base: int\` and \`strip: bool\`) by using the \`int()\` function, as in \`int($ARG) \> 0o600\` or \`int($ARG) \> 2147483647\`.  
 705 | :::  
 706 |   
 707 | The \`metavariable-comparison\` operator also takes optional \`base: int\` and \`strip: bool\` keys. These keys set the integer base the metavariable value should be interpreted as and remove quotes from the metavariable value, respectively.  
 708 |   
 709 | \`\`\`yaml  
 710 | rules:  
 711 |   \- id: excessive-permissions  
 712 |     languages:  
 713 |       \- python  
 714 |     message: module setting excessive permissions  
 715 |     patterns:  
 716 |       \- pattern: set\_permissions($ARG)  
 717 |       \- metavariable-comparison:  
 718 |           comparison: $ARG \> 0o600  
 719 |           metavariable: $ARG  
 720 |           base: 8  
 721 |     severity: ERROR  
 722 | \`\`\`  
 723 |   
 724 | The pattern immediately above matches the following:  
 725 |   
 726 | \`\`\`python  
 727 | \# ruleid: excessive-permissions  
 728 | \# highlight-next-line  
 729 | set\_permissions(0o700)  
 730 | \# ok: excessive-permissions  
 731 | set\_permissions(0o400)  
 732 | \`\`\`  
 733 |   
 734 | This interprets metavariable values found in code as octal. As a result, Semgrep detects \`0700\`, but it does \*\*not\*\* detect \`0400\`.  
 735 |   
 736 | \`\`\`yaml  
 737 | rules:  
 738 |   \- id: int-overflow  
 739 |     languages:  
 740 |       \- python  
 741 |     message: Potential integer overflow  
 742 |     patterns:  
 743 |       \- pattern: int($ARG)  
 744 |       \- metavariable-comparison:  
 745 |           strip: true  
 746 |           comparison: $ARG \> 2147483647  
 747 |           metavariable: $ARG  
 748 |     severity: ERROR  
 749 | \`\`\`  
 750 |   
 751 | The pattern immediately above matches the following:  
 752 |   
 753 | \`\`\`python  
 754 | \# ruleid: int-overflow  
 755 | \# highlight-next-line  
 756 | int("2147483648")  
 757 | \# ok: int-overflow  
 758 | int("2147483646")  
 759 | \`\`\`  
 760 |   
 761 | This removes quotes (\`'\`, \`"\`, and \`\` \` \`\`) from both ends of the metavariable content. As a result, Semgrep detects \`"2147483648"\`, but it does \*\*not\*\* detect \`"2147483646"\`. This is useful when you expect strings to contain integer or float data.  
 762 |   
 763 | \#\#\# \`metavariable-name\`  
 764 |   
 765 | :::tip  
 766 | \- \`metavariable-name\` requires a Semgrep account and the use of Semgrep's proprietary engine since it requires name resolution information. This means that it does \*\*not\*\* work with the \`--oss-only\` flag.  
 767 | \- While optional, you can improve the accuracy of \`metavariable-name\` by enabling \*\*\[cross-file analysis\](/docs/getting-started/cli\#enable-cross-file-analysis)\*\*.   
 768 | :::  
 769 |   
 770 | The \`metavariable-name\` operator adds a constraint to the types of identifiers a metavariable is able to match. Currently the only constraint supported is on the module or namespace an identifier originates from. This is useful for filtering results in languages which don't have a native syntax for fully qualified names, or languages where module names may contain characters which are not legal in identifiers, such as JavaScript or TypeScript.   
 771 |   
 772 |   
 773 | \`\`\`yaml  
 774 | rules:  
 775 |   \- id: insecure-method  
 776 |     patterns:  
 777 |       \- pattern: $MODULE.insecure(...)  
 778 |       \- metavariable-name:  
 779 |           metavariable: $MODULE  
 780 |           module: "@foo-bar"  
 781 |     message: Uses insecure method from @foo-bar.  
 782 |     languages:  
 783 |       \- javascript  
 784 |     severity: ERROR  
 785 | \`\`\`  
 786 |   
 787 | The pattern immediately above matches the following:  
 788 |   
 789 | \`\`\`javascript  
 790 | // ECMAScript modules  
 791 | import \* as lib from '@foo-bar';  
 792 | import \* as lib2 from 'myotherlib';  
 793 |   
 794 | // CommonJS modules  
 795 | const { insecure } \= require('@foo-bar');  
 796 | const lib3 \= require('myotherlib');  
 797 |   
 798 | // ruleid: insecure-method  
 799 | // highlight-next-line  
 800 | lib.insecure("test");  
 801 | // ruleid: insecure-method  
 802 | // highlight-next-line  
 803 | insecure("test");  
 804 |   
 805 | // ok: insecure-method  
 806 | lib.secure("test");  
 807 | // ok: insecure-method  
 808 | lib2.insecure("test");  
 809 | // ok: insecure-method  
 810 | lib3.insecure("test");  
 811 | \`\`\`  
 812 |   
 813 | In the event that a match should occur if the metavariable matches one of a variety of matches, there is also a shorthand \`modules\` key, which takes a list of module names.  
 814 |   
 815 | \`\`\`yaml  
 816 | rules:  
 817 |   \- id: insecure-method  
 818 |     patterns:  
 819 |       \- pattern: $MODULE.method(...)  
 820 |       \- metavariable-regex:  
 821 |           metavariable: $MODULE  
 822 |           modules:  
 823 |            \- foo  
 824 |            \- bar  
 825 |     message: Uses insecure method from @foo-bar.  
 826 |     languages:  
 827 |       \- javascript  
 828 |     severity: ERROR  
 829 | \`\`\`  
 830 |   
 831 | This can be useful in instances where there may be multiple API-compatible packages which share an issue.  
 832 |   
 833 | \#\#\# \`pattern-not\`  
 834 |   
 835 | The \`pattern-not\` operator is the opposite of the \`pattern\` operator. It finds code that does not match its expression. This is useful for eliminating common false positives.  
 836 |   
 837 | \`\`\`yaml  
 838 | rules:  
 839 |   \- id: unverified-db-query  
 840 |     patterns:  
 841 |       \- pattern: db\_query(...)  
 842 |       \- pattern-not: db\_query(..., verify=True, ...)  
 843 |     message: Found unverified db query  
 844 |     severity: ERROR  
 845 |     languages:  
 846 |       \- python  
 847 | \`\`\`  
 848 |   
 849 | The pattern immediately above matches the following:  
 850 |   
 851 | \`\`\`python  
 852 | \# ruleid: unverified-db-query  
 853 | \# highlight-next-line  
 854 | db\_query("SELECT \* FROM ...")  
 855 | \# ok: unverified-db-query  
 856 | db\_query("SELECT \* FROM ...", verify=True, env="prod")  
 857 | \`\`\`  
 858 |   
 859 | Alternatively, \`pattern-not\` accepts a \`patterns\` or \`pattern-either\` property and negates everything inside the property.  
 860 |   
 861 | \`\`\`yaml  
 862 | rules:  
 863 |   \- id: unverified-db-query  
 864 |     patterns:  
 865 |       \- pattern: db\_query(...)  
 866 |       \- pattern-not:  
 867 |           pattern-either:  
 868 |             \- pattern: db\_query(..., verify=True, ...)  
 869 |             \- pattern-inside: |  
 870 |                 with ensure\_verified(db\_query):  
 871 |                   db\_query(...)  
 872 |     message: Found unverified db query  
 873 |     severity: ERROR  
 874 |     languages:  
 875 |       \- python  
 876 | \`\`\`  
 877 |   
 878 | \#\#\# \`pattern-inside\`  
 879 |   
 880 | The \`pattern-inside\` operator keeps matched findings that reside within its expression. This is useful for finding code inside other pieces of code like functions or if blocks.  
 881 |   
 882 | \`\`\`yaml  
 883 | rules:  
 884 |   \- id: return-in-init  
 885 |     patterns:  
 886 |       \- pattern: return ...  
 887 |       \- pattern-inside: |  
 888 |           class $CLASS:  
 889 |             ...  
 890 |       \- pattern-inside: |  
 891 |           def \_\_init\_\_(...):  
 892 |               ...  
 893 |     message: return should never appear inside a class \_\_init\_\_ function  
 894 |     languages:  
 895 |       \- python  
 896 |     severity: ERROR  
 897 | \`\`\`  
 898 |   
 899 | The pattern immediately above matches the following:  
 900 |   
 901 | \`\`\`python  
 902 | class A:  
 903 |     def \_\_init\_\_(self):  
 904 |         \# ruleid: return-in-init  
 905 |         \# highlight-next-line  
 906 |         return None  
 907 |   
 908 | class B:  
 909 |     def \_\_init\_\_(self):  
 910 |         \# ok: return-in-init  
 911 |         self.inited \= True  
 912 |   
 913 | def foo():  
 914 |     \# ok: return-in-init  
 915 |     return 5  
 916 | \`\`\`  
 917 |   
 918 | \#\#\# \`pattern-not-inside\`  
 919 |   
 920 | The \`pattern-not-inside\` operator keeps matched findings that do not reside within its expression. It is the opposite of \`pattern-inside\`. This is useful for finding code that’s missing a corresponding cleanup action like disconnect, close, or shutdown. It’s also useful for finding problematic code that isn't inside code that mitigates the issue.  
 921 |   
 922 | \`\`\`yaml  
 923 | rules:  
 924 |   \- id: open-never-closed  
 925 |     patterns:  
 926 |       \- pattern: $F \= open(...)  
 927 |       \- pattern-not-inside: |  
 928 |           $F \= open(...)  
 929 |           ...  
 930 |           $F.close()  
 931 |     message: file object opened without corresponding close  
 932 |     languages:  
 933 |       \- python  
 934 |     severity: ERROR  
 935 | \`\`\`  
 936 |   
 937 | The pattern immediately above matches the following:  
 938 |   
 939 | \`\`\`python  
 940 | def func1():  
 941 |     \# ruleid: open-never-closed  
 942 |     \# highlight-next-line  
 943 |     fd \= open('test.txt')  
 944 |     results \= fd.read()  
 945 |     return results  
 946 |   
 947 | def func2():  
 948 |     \# ok: open-never-closed  
 949 |     fd \= open('test.txt')  
 950 |     results \= fd.read()  
 951 |     fd.close()  
 952 |     return results  
 953 | \`\`\`  
 954 |   
 955 | The above rule looks for files that are opened but never closed, possibly leading to resource exhaustion. It looks for the \`open(...)\` pattern \_and not\_ a following \`close()\` pattern.  
 956 |   
 957 | The \`$F\` metavariable ensures that the same variable name is used in the \`open\` and \`close\` calls. The ellipsis operator allows for any arguments to be passed to \`open\` and any sequence of code statements in-between the \`open\` and \`close\` calls. The rule ignores how \`open\` is called or what happens up to a \`close\` call\&mdash;it only needs to make sure \`close\` is called.  
 958 |   
 959 | \#\# Metavariable matching  
 960 |   
 961 | Metavariable matching operates differently for logical AND (\`patterns\`) and logical OR (\`pattern-either\`) parent operators. Behavior is consistent across all child operators: \`pattern\`, \`pattern-not\`, \`pattern-regex\`, \`pattern-inside\`, \`pattern-not-inside\`.  
 962 |   
 963 | \#\#\# Metavariables in logical ANDs  
 964 |   
 965 | Metavariable values must be identical across sub-patterns when performing logical AND operations with the \`patterns\` operator.  
 966 |   
 967 | Example:  
 968 |   
 969 | \`\`\`yaml  
 970 | rules:  
 971 |   \- id: function-args-to-open  
 972 |     patterns:  
 973 |       \- pattern-inside: |  
 974 |           def $F($X):  
 975 |               ...  
 976 |       \- pattern: open($X)  
 977 |     message: "Function argument passed to open() builtin"  
 978 |     languages: \[python\]  
 979 |     severity: ERROR  
 980 | \`\`\`  
 981 |   
 982 | This rule matches the following code:  
 983 |   
 984 | \`\`\`python  
 985 | def foo(path):  
 986 |     open(path)  
 987 | \`\`\`  
 988 |   
 989 | The example rule doesn’t match this code:  
 990 |   
 991 | \`\`\`python  
 992 | def foo(path):  
 993 |     open(something\_else)  
 994 | \`\`\`  
 995 |   
 996 | \#\#\# Metavariables in logical ORs  
 997 |   
 998 | Metavariable matching does not affect the matching of logical OR operations with the \`pattern-either\` operator.  
 999 |   
1000 | Example:  
1001 |   
1002 | \`\`\`yaml  
1003 | rules:  
1004 |   \- id: insecure-function-call  
1005 |     pattern-either:  
1006 |       \- pattern: insecure\_func1($X)  
1007 |       \- pattern: insecure\_func2($X)  
1008 |     message: "Insecure function use"  
1009 |     languages: \[python\]  
1010 |     severity: ERROR  
1011 | \`\`\`  
1012 |   
1013 | The above rule matches both examples below:  
1014 |   
1015 | \`\`\`python  
1016 | insecure\_func1(something)  
1017 | insecure\_func2(something)  
1018 | \`\`\`  
1019 |   
1020 | \`\`\`python  
1021 | insecure\_func1(something)  
1022 | insecure\_func2(something\_else)  
1023 | \`\`\`  
1024 |   
1025 | \#\#\# Metavariables in complex logic  
1026 |   
1027 | Metavariable matching still affects subsequent logical ORs if the parent is a logical AND.  
1028 |   
1029 | Example:  
1030 |   
1031 | \`\`\`yaml  
1032 | patterns:  
1033 |   \- pattern-inside: |  
1034 |       def $F($X):  
1035 |         ...  
1036 |   \- pattern-either:  
1037 |       \- pattern: bar($X)  
1038 |       \- pattern: baz($X)  
1039 | \`\`\`  
1040 |   
1041 | The above rule matches both examples below:  
1042 |   
1043 | \`\`\`python  
1044 | def foo(something):  
1045 |     bar(something)  
1046 | \`\`\`  
1047 |   
1048 | \`\`\`python  
1049 | def foo(something):  
1050 |     baz(something)  
1051 | \`\`\`  
1052 |   
1053 | The example rule doesn’t match this code:  
1054 |   
1055 | \`\`\`python  
1056 | def foo(something):  
1057 |     bar(something\_else)  
1058 | \`\`\`  
1059 |   
1060 | \#\# \`options\`  
1061 |   
1062 | Enable, disable, or modify the following matching features:  
1063 |   
1064 | \<\!-- Options are sorted alphabetically \--\>  
1065 |   
1066 | | Option                 | Default | Description                                                            |  
1067 | | :--------------------- | :------ | :--------------------------------------------------------------------- |  
1068 | | \`ac\_matching\`          | \`true\`  | \[Matching modulo associativity and commutativity\](/writing-rules/pattern-syntax.mdx\#associative-and-commutative-operators), treat Boolean AND/OR as associative, and bitwise AND/OR/XOR as both associative and commutative. |  
1069 | | \`attr\_expr\`            | \`true\`  | Expression patterns (for example: \`f($X)\`) matches attributes (for example: \`@f(a)\`). |  
1070 | | \`commutative\_boolop\`   | \`false\` | Treat Boolean AND/OR as commutative even if not semantically accurate. |  
1071 | | \`constant\_propagation\` | \`true\`  | \[Constant propagation\](/writing-rules/pattern-syntax/\#constants), including \[intra-procedural flow-sensitive constant propagation\](/writing-rules/data-flow/constant-propagation). |  
1072 | | \`decorators\_order\_matters\` | \`false\` | Match non-keyword attributes (for example: decorators in Python) in order, instead of the order-agnostic default. Keyword attributes (for example: \`static\`, \`inline\`, etc) are not affected. |  
1073 | | \`generic\_comment\_style\` | none   | In generic mode, assume that comments follow the specified syntax. They are then ignored for matching purposes. Allowed values for comment styles are: \<ul\>\<li\>\`c\` for traditional C-style comments (\`/\* ... \*/\`). \</li\>\<li\> \`cpp\` for modern C or C++ comments (\`// ...\` or \`/\* ... \*/\`). \</li\>\<li\> \`shell\` for shell-style comments (\`\# ...\`). \</li\>\</ul\> By default, the generic mode does not recognize any comments. Available since Semgrep version 0.96. For more information about generic mode, see \[Generic pattern matching\](/writing-rules/generic-pattern-matching) documentation. |  
1074 | | \`generic\_ellipsis\_max\_span\` | \`10\` | In generic mode, this is the maximum number of newlines that an ellipsis operator \`...\` can match or equivalently, the maximum number of lines covered by the match minus one. The default value is \`10\` (newlines) for performance reasons. Increase it with caution. Note that the same effect as \`20\` can be achieved without changing this setting and by writing \`... ...\` in the pattern instead of \`...\`. Setting it to \`0\` is useful with line-oriented languages (for example \[INI\](https://en.wikipedia.org/wiki/INI\_file) or key-value pairs in general) to force a match to not extend to the next line of code. Available since Semgrep 0.96. For more information about generic mode, see \[Generic pattern matching\](/writing-rules/generic-pattern-matching) documentation. |  
1075 | | \`implicit\_return\`   | \`true\` | Return statement patterns (for example \`return $E\`) match expressions that may be evaluated last in a function as if there was a return keyword in front of those expressions. Only applies to certain expression-based languages, such as Ruby and Julia. |  
1076 | | \`interfile\`   | \`false\` | Set this value to \`true\` for Semgrep to run this rule with cross-function and cross-file analysis. It is \*\*required\*\* for rules that use cross-function, cross-file analysis. |  
1077 | | \`symmetric\_eq\`      | \`false\` | Treat equal operations as symmetric (for example: \`a \== b\` is equal to \`b \== a\`). |  
1078 | | \`taint\_assume\_safe\_functions\` | \`false\` | Experimental option which will be subject to future changes. Used in taint analysis. Assume that function calls do \*\*not\*\* propagate taint from their arguments to their output. Otherwise, Semgrep always assumes that functions may propagate taint. Can replace \*\*not-conflicting\*\* sanitizers added in v0.69.0 in the future. |  
1079 | | \`taint\_assume\_safe\_indexes\` | \`false\` | Used in taint analysis. Assume that an array-access expression is safe even if the index expression is tainted. Otherwise Semgrep assumes that for example: \`a\[i\]\` is tainted if \`i\` is tainted, even if \`a\` is not. Enabling this option is recommended for high-signal rules, whereas disabling is preferred for audit rules. Currently, it is disabled by default to attain backwards compatibility, but this can change in the near future after some evaluation. |  
1080 | | \`vardef\_assign\`        | \`true\`  | Assignment patterns (for example \`$X \= $E\`) match variable declarations (for example \`var x \= 1;\`). |  
1081 | | \`xml\_attrs\_implicit\_ellipsis\` | \`true\` | Any XML/JSX/HTML element patterns have implicit ellipsis for attributes (for example: \`\<div /\>\` matches \`\<div foo="1"\>\`. |  
1082 |   
1083 | The full list of available options can be consulted in the \[Semgrep matching engine configuration\](https://github.com/semgrep/semgrep/blob/develop/interfaces/Rule\_options.atd) module. Note that options not included in the table above are considered experimental, and they may change or be removed without notice.  
1084 |   
1085 | \#\# \`fix\`  
1086 |   
1087 | The \`fix\` top-level key allows for simple autofixing of a pattern by suggesting an autofix for each match. Run \`semgrep\` with \`--autofix\` to apply the changes to the files.  
1088 |   
1089 | Example:  
1090 |   
1091 | \`\`\`yaml  
1092 | rules:  
1093 |   \- id: use-dict-get  
1094 |     patterns:  
1095 |       \- pattern: $DICT\[$KEY\]  
1096 |     fix: $DICT.get($KEY)  
1097 |     message: "Use \`.get()\` method to avoid a KeyNotFound error"  
1098 |     languages: \[python\]  
1099 |     severity: ERROR  
1100 | \`\`\`  
1101 |   
1102 | For more information about \`fix\` and \`--autofix\` see \[Autofix\](/writing-rules/autofix) documentation.  
1103 |   
1104 | \#\# \`metadata\`  
1105 |   
1106 | Provide additional information for a rule with the \`metadata:\` key, such as a related CWE, likelihood, OWASP.  
1107 |   
1108 | Example:  
1109 |   
1110 | \`\`\`yaml  
1111 | rules:  
1112 |   \- id: eqeq-is-bad  
1113 |     patterns:  
1114 |       \- \[...\]  
1115 |     message: "useless comparison operation \`$X \== $X\` or \`$X \!= $X\`"  
1116 |     metadata:  
1117 |       cve: CVE-2077-1234  
1118 |       discovered-by: Ikwa L'equale  
1119 | \`\`\`  
1120 |   
1121 | The metadata are also displayed in the output of Semgrep if you’re running it with \`--json\`.  
1122 | Rules with \`category: security\` have additional metadata requirements. See \[Including fields required by security category\](/contributing/contributing-to-semgrep-rules-repository/\#fields-required-by-the-security-category) for more information.  
1123 |   
1124 | \#\# \`min-version\` and \`max-version\`  
1125 |   
1126 | Each rule supports optional fields \`min-version\` and \`max-version\` specifying  
1127 | minimum and maximum Semgrep versions. If the Semgrep  
1128 | version being used doesn't satisfy these constraints,  
1129 | the rule is skipped without causing a fatal error.  
1130 |   
1131 | Example rule:  
1132 |   
1133 | \`\`\`yaml  
1134 | rules:  
1135 |   \- id: bad-goflags  
1136 |     \# earlier semgrep versions can't parse the pattern  
1137 |     min-version: 1.31.0  
1138 |     pattern: |  
1139 |       ENV ... GOFLAGS='-tags=dynamic \-buildvcs=false' ...  
1140 |     languages: \[dockerfile\]  
1141 |     message: "We should not use these flags"  
1142 |     severity: WARNING  
1143 | \`\`\`  
1144 |   
1145 | Another use case is when a newer version of a rule works better than  
1146 | before but relies on a new feature. In this case, we could use  
1147 | \`min-version\` and \`max-version\` to ensure that either the older or the  
1148 | newer rule is used but not both. The rules would look like this:  
1149 |   
1150 | \`\`\`yaml  
1151 | rules:  
1152 |   \- id: something-wrong-v1  
1153 |     max-version: 1.72.999  
1154 |     ...  
1155 |   \- id: something-wrong-v2  
1156 |     min-version: 1.73.0  
1157 |     \# 10x faster than v1\!  
1158 |     ...  
1159 | \`\`\`  
1160 |   
1161 | The \`min-version\`/\`max-version\` feature is available since Semgrep  
1162 | 1.38.0. It is intended primarily for publishing rules that rely on  
1163 | newly released features without causing errors in older Semgrep  
1164 | installations.  
1165 |   
1166 |   
1167 | \#\# \`category\`  
1168 |   
1169 | Provide a category for users of the rule. For example: \`best-practice\`, \`correctness\`, \`maintainability\`. For more information, see \[Semgrep registry rule requirements\](/contributing/contributing-to-semgrep-rules-repository/\#semgrep-registry-rule-requirements).  
1170 |   
1171 | \#\# \`paths\`  
1172 |   
1173 | \#\#\# Excluding a rule in paths  
1174 |   
1175 | To ignore a specific rule on specific files, set the \`paths:\` key with  
1176 | one or more filters. The patterns apply to the full file paths  
1177 | relative to the project root.  
1178 |   
1179 | \<\!--  
1180 |   The current behavior is inconsistent with the Gitignore specification  
1181 |   which is used for Semgrepignore patterns in .semgrepignore files  
1182 |   and \--exclude/--include command-line filters.  
1183 |   The pattern \`/foo\` should match the path \`foo\` and not \`bar/foo\` but  
1184 |   it matches neither.  
1185 |   The pattern \`a/b\` should match the path \`a/b\` but not \`c/a/b\`  
1186 |   but it matches both.  
1187 |   If we decide we'll never fix this, we should clarify these discrepancies.  
1188 | \--\>  
1189 |   
1190 | Example:  
1191 |   
1192 | \`\`\`yaml  
1193 | rules:  
1194 |   \- id: eqeq-is-bad  
1195 |     pattern: $X \== $X  
1196 |     paths:  
1197 |       exclude:  
1198 |         \- "src/\*\*/\*.jinja2"  
1199 |         \- "\*\_test.go"  
1200 |         \- "project/tests"  
1201 |         \- project/static/\*.js  
1202 | \`\`\`  
1203 |   
1204 | When invoked with \`semgrep \-f rule.yaml project/\`, the above rule runs on files inside \`project/\`, but no results are returned for:  
1205 |   
1206 | \- any file with a \`.jinja2\` file extension  
1207 | \- any file whose name ends in \`\_test.go\`, such as \`project/backend/server\_test.go\`  
1208 | \- any file inside \`project/tests\` or its subdirectories  
1209 | \- any file matching the \`project/static/\*.js\` glob pattern  
1210 |   
1211 | :::note  
1212 | The glob syntax is from \[Python's \`wcmatch\`\](https://pypi.org/project/wcmatch/) and is used to match against the given file and all its parent directories.  
1213 | :::  
1214 |   
1215 | \#\#\# Limiting a rule to paths  
1216 |   
1217 | Conversely, to run a rule \_only\_ on specific files, set a \`paths:\` key with one or more of these filters:  
1218 |   
1219 | \`\`\`yaml  
1220 | rules:  
1221 |   \- id: eqeq-is-bad  
1222 |     pattern: $X \== $X  
1223 |     paths:  
1224 |       include:  
1225 |         \- "\*\_test.go"  
1226 |         \- "project/server"  
1227 |         \- "project/schemata"  
1228 |         \- "project/static/\*.js"  
1229 |         \- "tests/\*\*/\*.js"  
1230 | \`\`\`  
1231 |   
1232 | When invoked with \`semgrep \-f rule.yaml project/\`, this rule runs on files inside \`project/\`, but results are returned only for:  
1233 |   
1234 | \- files whose name ends in \`\_test.go\`, such as \`project/backend/server\_test.go\`  
1235 | \- files inside \`project/server\`, \`project/schemata\`, or their subdirectories  
1236 | \- files matching the \`project/static/\*.js\` glob pattern  
1237 | \- all files with the \`.js\` extension, arbitrary depth inside the tests folder  
1238 |   
1239 | If you are writing tests for your rules, add any test file or directory to the included paths as well.  
1240 |   
1241 | :::note  
1242 | When mixing inclusion and exclusion filters, the exclusion ones take precedence.  
1243 | :::  
1244 |   
1245 | Example:  
1246 |   
1247 | \`\`\`yaml  
1248 | paths:  
1249 |   include: "project/schemata"  
1250 |   exclude: "\*\_internal.py"  
1251 | \`\`\`  
1252 |   
1253 | The above rule returns results from \`project/schemata/scan.py\` but not from \`project/schemata/scan\_internal.py\`.  
1254 |   
1255 | \#\# Other examples  
1256 |   
1257 | This section contains more complex rules that perform advanced code searching.  
1258 |   
1259 | \#\#\# Complete useless comparison  
1260 |   
1261 | \`\`\`yaml  
1262 | rules:  
1263 |   \- id: eqeq-is-bad  
1264 |     patterns:  
1265 |       \- pattern-not-inside: |  
1266 |           def \_\_eq\_\_(...):  
1267 |               ...  
1268 |       \- pattern-not-inside: assert(...)  
1269 |       \- pattern-not-inside: assertTrue(...)  
1270 |       \- pattern-not-inside: assertFalse(...)  
1271 |       \- pattern-either:  
1272 |           \- pattern: $X \== $X  
1273 |           \- pattern: $X \!= $X  
1274 |           \- patterns:  
1275 |               \- pattern-inside: |  
1276 |                   def \_\_init\_\_(...):  
1277 |                        ...  
1278 |               \- pattern: self.$X \== self.$X  
1279 |       \- pattern-not: 1 \== 1  
1280 |     message: "useless comparison operation \`$X \== $X\` or \`$X \!= $X\`"  
1281 | \`\`\`  
1282 |   
1283 | The above rule makes use of many operators. It uses \`pattern-either\`, \`patterns\`, \`pattern\`, and \`pattern-inside\` to carefully consider different cases, and uses \`pattern-not-inside\` and \`pattern-not\` to whitelist certain useless comparisons.  
1284 |   
1285 | \#\# Full specification  
1286 |   
1287 | The \[full configuration-file format\](https://github.com/semgrep/semgrep-interfaces/blob/main/rule\_schema\_v1.yaml) is defined as  
1288 | a \[jsonschema\](http://json-schema.org/specification.html) object.  
1289 | 

\--------------------------------------------------------------------------------  
/docs/writing-rules/testing-rules.md:  
\--------------------------------------------------------------------------------  
  1 | \---  
  2 | append\_help\_link: true  
  3 | slug: testing-rules  
  4 | description: "Semgrep provides a convenient testing mechanism for your rules. You can simply write code and provide a few annotations to let Semgrep know where you are or aren't expecting findings."  
  5 | tags:  
  6 |   \- Rule writing  
  7 | \---  
  8 |   
  9 |   
 10 | import EnableAutofix from "/src/components/procedure/\_enable-autofix.mdx"  
 11 |   
 12 | \# Testing rules  
 13 |   
 14 | Semgrep provides a convenient testing mechanism for your rules. You can simply write code and provide a few annotations to let Semgrep know where you are or aren't expecting findings. Semgrep provides the following annotations:  
 15 |   
 16 | \- \`ruleid: \<rule-id\>\`, for protecting against false negatives  
 17 | \- \`ok: \<rule-id\>\` for protecting against false positives  
 18 | \- \`todoruleid: \<rule-id\>\` for future "positive" rule improvements  
 19 | \- \`todook: \<rule-id\>\` for future "negative" rule improvements  
 20 |   
 21 | Other than annotations there are three things to remember when creating tests:  
 22 |   
 23 | 1\. The \`--test\` flag tells Semgrep to run tests in the specified directory.  
 24 | 2\. Annotations are specified as a comment above the offending line.  
 25 | 3\. Semgrep looks for tests based on the rule filename and the languages  
 26 |    specified in the rule. In other words, \`path/to/rule.yaml\` searches for  
 27 |    \`path/to/rule.py\`, \`path/to/rule.js\` and similar, based on the languages specified in the rule.  
 28 |   
 29 | :::info  
 30 | The \`.test.yaml\` file extension can also be used for test files. This is necessary when testing YAML language rules.  
 31 | :::  
 32 |   
 33 | \#\# Testing autofix  
 34 |   
 35 | Semgrep's testing mechanism also provides a way to test the behavior of any \`fix\` values defined in the rules.  
 36 |   
 37 | To define a test for autofix behavior:  
 38 |   
 39 | 1\. Create a new \*\*autofix test file\*\* with the \`.fixed\` suffix before the file type extension.  
 40 |    For example, name the autofix test file of a rule with test code in \`path/to/rule.py\` as \`path/to/rule.fixed.py\`.  
 41 | 2\. Within the autofix test file, enter the expected result of applied autofix rule to the test code.  
 42 | 3\. Run \`semgrep \--test\` to verify that your autofix test file is correctly detected.  
 43 |   
 44 | When you use \`semgrep \--test\`, Semgrep applies the autofix rule to the original test code (\`path/to/rule.py\`), and then verifies whether this matches the expected outcome defined in the autofix test file (\`path/to/rule.fixed.py)\`. If there is a mismatch, the line diffs are printed.  
 45 |   
 46 | :::info  
 47 | \*\*Hint\*\*: Creating an autofix test for a rule with autofix can take less than a minute with the following flow of commands:  
 48 | \`\`\`sh  
 49 | cp rule.py rule.fixed.py  
 50 | semgrep \--config rule.yaml rule.fixed.py \--autofix  
 51 | \`\`\`  
 52 |   
 53 | These commands apply the autofix of the rule to the test code. After Semgrep delivers a fix, inspect whether the outcome of this fix looks as expected (for example using \`vimdiff rule.py rule.fixed.py\`).  
 54 | :::  
 55 |   
 56 | \#\# Example  
 57 |   
 58 | Consider the following rule:  
 59 |   
 60 | \`\`\`yaml  
 61 | rules:  
 62 | \- id: insecure-eval-use  
 63 |   patterns:  
 64 |   \- pattern: eval($VAR)  
 65 |   \- pattern-not: eval("...")  
 66 |   fix: secure\_eval($VAR)  
 67 |   message: Calling 'eval' with user input  
 68 |   languages: \[python\]  
 69 |   severity: WARNING  
 70 | \`\`\`  
 71 |   
 72 | Given the above is named \`rules/detect-eval.yaml\`, you can create \`rules/detect-eval.py\`:  
 73 |   
 74 | \`\`\`python  
 75 | from lib import get\_user\_input, safe\_get\_user\_input, secure\_eval  
 76 |   
 77 | user\_input \= get\_user\_input()  
 78 | \# ruleid: insecure-eval-use  
 79 | eval(user\_input)  
 80 |   
 81 | \# ok: insecure-eval-use  
 82 | eval('print("Hardcoded eval")')  
 83 |   
 84 | totally\_safe\_eval \= eval  
 85 | \# todoruleid: insecure-eval-use  
 86 | totally\_safe\_eval(user\_input)  
 87 |   
 88 | \# todook: insecure-eval-use  
 89 | eval(safe\_get\_user\_input())  
 90 | \`\`\`  
 91 |   
 92 | Run the tests with the following:  
 93 |   
 94 | \`\`\`sh  
 95 | semgrep \--test rules/  
 96 | \`\`\`  
 97 |   
 98 | Which will produce the following output:  
 99 | \`\`\`sh  
100 | 1/1: ✓ All tests passed  
101 | No tests for fixes found.  
102 | \`\`\`  
103 |   
104 | Semgrep tests automatically avoid failing on lines marked with \`\# todoruleid\` or \`\# todook\`.  
105 |   
106 | \#\# Storing rules and test targets in different directories  
107 |   
108 | Creating different directories for rules and tests helps users manage a growing library of custom rules. To store rules and test targets in different directories use the \`--config\` option.  
109 |   
110 | For example, in the directory with the following structure:  
111 |   
112 | \`\`\`sh  
113 | $ tree tests  
114 |   
115 | tests  
116 | ├── rules  
117 | │   └── python  
118 | │       └── insecure-eval-use.yaml  
119 | └── targets  
120 |     └── python  
121 |         └── insecure-eval-use.py  
122 |   
123 | 4 directories, 2 files  
124 | \`\`\`  
125 |   
126 | Use of the following command:  
127 |   
128 | \`\`\`sh  
129 | semgrep \--test \--config tests/rules/ tests/targets/  
130 | \`\`\`  
131 |   
132 | Produces the same output as in the previous example.  
133 |   
134 | The subdirectory structure of these two directories must be the same for Semgrep to correctly find the associated files.  
135 |   
136 | To test the autofix behavior, add the autofix test file \`rules/detect-eval.fixed.py\` to represent the expected outcome of applying the fix to the test code:  
137 |   
138 | \`\`\`python  
139 | from lib import get\_user\_input, safe\_get\_user\_input, secure\_eval  
140 |   
141 | user\_input \= get\_user\_input()  
142 | \# ruleid: insecure-eval-use  
143 | secure\_eval(user\_input)  
144 |   
145 | \# ok: insecure-eval-use  
146 | eval('print("Hardcoded eval")')  
147 |   
148 | totally\_safe\_eval \= eval  
149 | \# todoruleid: insecure-eval-use  
150 | totally\_safe\_eval(user\_input)  
151 |   
152 | \# todook: insecure-eval-use  
153 | secure\_eval(safe\_get\_user\_input())  
154 | \`\`\`  
155 |   
156 | So that the directory structure is printed as the following:  
157 |   
158 | \`\`\`sh  
159 | $ tree tests  
160 |   
161 | tests  
162 | ├── rules  
163 | │   └── python  
164 | │       └── insecure-eval-use.yaml  
165 | └── targets  
166 |     └── python  
167 |         └── insecure-eval-use.py  
168 |         └── insecure-eval-use.fixed.py  
169 |   
170 | 4 directories, 2 files  
171 | \`\`\`  
172 |   
173 | Use of the following command:  
174 |   
175 | \`\`\`sh  
176 | semgrep \--test \--config tests/rules/ tests/targets/  
177 | \`\`\`  
178 |   
179 | Results in the following outcome:  
180 |   
181 | \`\`\`sh  
182 | 1/1: ✓ All tests passed  
183 | 1/1: ✓ All fix tests passed  
184 | \`\`\`  
185 |   
186 | If the fix does not behave as expected, the output prints a line diff.  
187 | For example, if we replace \`secure\_eval\` with \`safe\_eval\`, we can see that lines 5 and 15 are not rendered as expected.  
188 |   
189 | \`\`\`sh  
190 | 1/1: ✓ All tests passed  
191 | 0/1: 1 fix tests did not pass:  
192 | \--------------------------------------------------------------------------------  
193 | 	✖ targets/python/detect-eval.fixed.py \<\> autofix applied to targets/python/detect-eval.py  
194 |   
195 | 	\---  
196 | 	\+++  
197 | 	@@ \-5 \+5 @@  
198 | 	\-safe\_eval(user\_input)  
199 | 	\+secure\_eval(user\_input)  
200 | 	@@ \-15 \+15 @@  
201 | 	\-safe\_eval(safe\_get\_user\_input())  
202 | 	\+secure\_eval(safe\_get\_user\_input())  
203 |   
204 | \`\`\`  
205 |   
206 | \#\# Validating rules  
207 |   
208 | At Semgrep, Inc., we believe in checking the code we write, and that includes rules.  
209 |   
210 | You can run \`semgrep \--validate \--config \[filename\]\` to check the configuration. This command runs a combination of Semgrep rules and OCaml checks against your rules to search for issues such as duplicate patterns and missing fields. All rules submitted to the Semgrep Registry are validated.  
211 |   
212 | The semgrep rules are pulled from \`p/semgrep-rule-lints\`.  
213 |   
214 | This feature is still experimental and under active development. Your feedback is welcomed\!  
215 |   
216 | \#\# Enabling autofix in Semgrep Code  
217 |   
218 | \<EnableAutofix /\>  
219 | 

\--------------------------------------------------------------------------------