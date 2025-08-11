└── php  
    └── wordpress-plugins  
        └── security  
            └── audit  
                ├── wp-ajax-no-auth-and-auth-hooks-audit.php  
                ├── wp-ajax-no-auth-and-auth-hooks-audit.yaml  
                ├── wp-authorisation-checks-audit.php  
                ├── wp-authorisation-checks-audit.yaml  
                ├── wp-code-execution-audit.php  
                ├── wp-code-execution-audit.yaml  
                ├── wp-command-execution-audit.php  
                ├── wp-command-execution-audit.yaml  
                ├── wp-csrf-audit.php  
                ├── wp-csrf-audit.yaml  
                ├── wp-file-download-audit.php  
                ├── wp-file-download-audit.yaml  
                ├── wp-file-inclusion-audit.php  
                ├── wp-file-inclusion-audit.yaml  
                ├── wp-file-manipulation-audit.php  
                ├── wp-file-manipulation-audit.yaml  
                ├── wp-open-redirect-audit.php  
                ├── wp-open-redirect-audit.yaml  
                ├── wp-php-object-injection-audit.php  
                ├── wp-php-object-injection-audit.yaml  
                ├── wp-sql-injection-audit.php  
                ├── wp-sql-injection-audit.yaml  
                ├── wp-ssrf-audit.php  
                └── wp-ssrf-audit.yaml

/php/wordpress-plugins/security/audit/wp-ajax-no-auth-and-auth-hooks-audit.php:  
\--------------------------------------------------------------------------------  
 1 | \<?php  
 2 |   
 3 | // ruleid: wp-ajax-no-auth-and-auth-hooks-audit  
 4 | add\_action( 'wp\_ajax\_priv\_upload', 'auth\_upload' );  
 5 |   
 6 | // ruleid: wp-ajax-no-auth-and-auth-hooks-audit  
 7 | add\_action( 'wp\_ajax\_nopriv\_upload', 'no\_auth\_upload');  
 8 |   
 9 | // ok: wp-ajax-no-auth-and-auth-hooks-audit  
10 | add\_action('plugins\_loaded','upload\_plugins\_loaded');  
11 |   
12 |   
13 |   
14 | ?\>

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-ajax-no-auth-and-auth-hooks-audit.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 |   \- id: wp-ajax-no-auth-and-auth-hooks-audit  
 3 |     patterns:  
 4 |       \- pattern: add\_action($HOOK,...)  
 5 |       \- metavariable-regex:  
 6 |           metavariable: $HOOK  
 7 |           regex: "'wp\_ajax\_.\*'"  
 8 |     message: \>-  
 9 |       These hooks allow the developer to handle the custom AJAX  
10 |       endpoints."wp\_ajax\_$action" hook get fires for any authenticated user and  
11 |       "wp\_ajax\_nopriv\_$action" hook get fires for non-authenticated users.  
12 |     paths:  
13 |       include:  
14 |         \- '\*\*/wp-content/plugins/\*\*/\*.php'  
15 |     languages:  
16 |       \- php  
17 |     severity: WARNING  
18 |     metadata:  
19 |       category: security  
20 |       confidence: LOW  
21 |       likelihood: LOW  
22 |       impact: MEDIUM  
23 |       subcategory:  
24 |         \- audit  
25 |       technology:  
26 |         \- Wordpress Plugins  
27 |       references:  
28 |         \- https://github.com/wpscanteam/wpscan/wiki/WordPress-Plugin-Security-Testing-Cheat-Sheet\#authorisation  
29 |         \- https://developer.wordpress.org/reference/hooks/wp\_ajax\_action/  
30 |       owasp:   
31 |         \- A01:2021 \- Broken Access Control  
32 |       cwe:   
33 |         \- "CWE-285: Improper Authorization"  
34 | 

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-authorisation-checks-audit.php:  
\--------------------------------------------------------------------------------  
 1 | \<?php  
 2 |   
 3 | // ruleid: wp-authorisation-checks-audit  
 4 | if ( is\_admin() ) {  
 5 | }  
 6 |   
 7 | // ruleid: wp-authorisation-checks-audit  
 8 | return is\_user\_logged\_in() ? get\_current\_user\_id() : '';  
 9 |   
10 | // ruleid: wp-authorisation-checks-audit  
11 | if ( \! current\_user\_can( 'install\_languages' ) ) {  
12 |   
13 | }  
14 |   
15 | // ok: wp-authorisation-checks-audit  
16 | get\_current\_user\_id();  
17 |   
18 |   
19 |   
20 | ?\>

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-authorisation-checks-audit.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 |   \- id: wp-authorisation-checks-audit  
 3 |     patterns:  
 4 |       \- pattern: $FUNCTION(...)  
 5 |       \- metavariable-regex:  
 6 |           metavariable: $FUNCTION  
 7 |           regex: current\_user\_can|is\_admin|is\_user\_logged\_in|is\_user\_admin  
 8 |     message: \>-  
 9 |       These are some of the patterns used for authorisation. Look properly if  
10 |       the authorisation is proper or not.  
11 |     paths:  
12 |       include:  
13 |         \- '\*\*/wp-content/plugins/\*\*/\*.php'  
14 |     languages:  
15 |       \- php  
16 |     severity: WARNING  
17 |     metadata:  
18 |       category: security  
19 |       confidence: LOW  
20 |       likelihood: LOW  
21 |       impact: MEDIUM  
22 |       subcategory:  
23 |         \- audit  
24 |       technology:  
25 |         \- Wordpress Plugins  
26 |       references:  
27 |         \- https://github.com/wpscanteam/wpscan/wiki/WordPress-Plugin-Security-Testing-Cheat-Sheet\#authorisation  
28 |       owasp:   
29 |         \- A01:2021 \- Broken Access Control  
30 |       cwe:   
31 |         \- "CWE-285: Improper Authorization"  
32 | 

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-code-execution-audit.php:  
\--------------------------------------------------------------------------------  
 1 | \<?php  
 2 |   
 3 | // ruleid: wp-code-execution-audit  
 4 | $snippetValue \= eval('return ' .$sanitizedSnippet . ';');  
 5 |   
 6 | // ruleid: wp-code-execution-audit  
 7 | $val \= call\_user\_func($filter, $val);  
 8 |   
 9 | // ok: wp-code-execution-audit  
10 | some\_other\_safe\_function($args);  
11 |   
12 |   
13 | ?\>

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-code-execution-audit.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 |   \- id: wp-code-execution-audit  
 3 |     patterns:  
 4 |       \- pattern-either:  
 5 |           \- pattern: eval(...)  
 6 |           \- pattern: assert(...)  
 7 |           \- pattern: call\_user\_func(...)  
 8 |     message: \>-  
 9 |       These functions can lead to code injection if the data inside them is  
10 |       user-controlled. Don't use the input directly or validate the data  
11 |       properly before passing it to these functions.  
12 |     paths:  
13 |       include:  
14 |         \- '\*\*/wp-content/plugins/\*\*/\*.php'  
15 |     languages:  
16 |       \- php  
17 |     severity: WARNING  
18 |     metadata:  
19 |       category: security  
20 |       confidence: LOW  
21 |       likelihood: LOW  
22 |       impact: HIGH  
23 |       subcategory:  
24 |         \- audit  
25 |       technology:  
26 |         \- Wordpress Plugins  
27 |       references:  
28 |         \- https://github.com/wpscanteam/wpscan/wiki/WordPress-Plugin-Security-Testing-Cheat-Sheet\#php-code-execution  
29 |       owasp:   
30 |         \- "A03:2021 \- Injection"  
31 |       cwe:   
32 |         \- "CWE-94: Improper Control of Generation of Code ('Code Injection')"  
33 | 

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-command-execution-audit.php:  
\--------------------------------------------------------------------------------  
 1 | \<?php  
 2 |   
 3 | // ruleid: wp-command-execution-audit  
 4 | exec('rm \-rf ' . $dir, $o, $r);  
 5 |   
 6 | // ruleid: wp-command-execution-audit  
 7 | $stderr \= shell\_exec($command);  
 8 |   
 9 |   
10 | // ok: wp-command-execution-audit  
11 | some\_other\_safe\_function($args);  
12 |   
13 |   
14 | ?\>  
15 | 

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-command-execution-audit.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 |   \- id: wp-command-execution-audit  
 3 |     patterns:  
 4 |       \- pattern-either:  
 5 |           \- pattern: system(...)  
 6 |           \- pattern: exec(...)  
 7 |           \- pattern: passthru(...)  
 8 |           \- pattern: shell\_exec(...)  
 9 |     message: \>-  
10 |       These functions can lead to command execution if the data inside them  
11 |       is user-controlled. Don't use the input directly or validate the data  
12 |       properly before passing it to these functions.  
13 |     paths:  
14 |       include:  
15 |         \- '\*\*/wp-content/plugins/\*\*/\*.php'  
16 |     languages:  
17 |       \- php  
18 |     severity: WARNING  
19 |     metadata:  
20 |       category: security  
21 |       confidence: LOW  
22 |       likelihood: LOW  
23 |       impact: HIGH  
24 |       subcategory:  
25 |         \- audit  
26 |       technology:  
27 |         \- Wordpress Plugins  
28 |       references:  
29 |         \- https://github.com/wpscanteam/wpscan/wiki/WordPress-Plugin-Security-Testing-Cheat-Sheet\#command-execution  
30 |       owasp:   
31 |         \- "A03:2021 \- Injection"  
32 |       cwe:  
33 |         \- "CWE-78: Improper Neutralization of Special Elements used in an OS Command ('OS Command Injection')"  
34 | 

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-csrf-audit.php:  
\--------------------------------------------------------------------------------  
 1 | \<?php  
 2 |   
 3 | // ruleid: wp-csrf-audit  
 4 | check\_ajax\_referer( 'wpforms-admin', 'nonce', false );  
 5 |   
 6 | // ok: wp-csrf-audit  
 7 | check\_ajax\_referer( 'wpforms-admin', 'nonce', true );  
 8 |   
 9 |   
10 | // ok: wp-csrf-audit  
11 | check\_ajax\_referer( 'wpforms-admin', 'nonce' );  
12 |   
13 | ?\>

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-csrf-audit.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 |   \- id: wp-csrf-audit  
 3 |     pattern: check\_ajax\_referer(...,...,false)  
 4 |     message: \>-  
 5 |       Passing false or 0 as the third argument to this function will not  
 6 |       cause the script to die, making the check useless.  
 7 |     paths:  
 8 |       include:  
 9 |         \- '\*\*/wp-content/plugins/\*\*/\*.php'  
10 |     languages:  
11 |       \- php  
12 |     severity: WARNING  
13 |     metadata:  
14 |       category: security  
15 |       confidence: LOW  
16 |       likelihood: LOW  
17 |       impact: MEDIUM  
18 |       subcategory:  
19 |         \- audit  
20 |       technology:  
21 |         \- Wordpress Plugins  
22 |       references:  
23 |         \- https://github.com/wpscanteam/wpscan/wiki/WordPress-Plugin-Security-Testing-Cheat-Sheet\#cross-site-request-forgery-csrf  
24 |         \- https://developer.wordpress.org/reference/functions/check\_ajax\_referer/  
25 |       owasp:  
26 |         \- A05:2021 \- Security Misconfiguration  
27 |       cwe:  
28 |         \- "CWE-352: Cross-Site Request Forgery (CSRF)"  
29 | 

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-file-download-audit.php:  
\--------------------------------------------------------------------------------  
 1 | \<?php  
 2 |   
 3 | // ruleid: wp-file-download-audit  
 4 | $json \= file\_get\_contents( 'php://input' );  
 5 |   
 6 | // ruleid: wp-file-download-audit  
 7 | readfile($zip\_name);  
 8 |   
 9 | // ruleid: wp-file-download-audit  
10 | $localeFunctions \= file($functionNamesFile, FILE\_IGNORE\_NEW\_LINES | FILE\_SKIP\_EMPTY\_LINES);  
11 |   
12 | // ok: wp-file-download-audit  
13 | some\_other\_function($args);  
14 |   
15 |   
16 | ?\>  
17 | 

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-file-download-audit.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 |   \- id: wp-file-download-audit  
 3 |     patterns:  
 4 |       \- pattern-either:  
 5 |           \- pattern: file(...)  
 6 |           \- pattern: readfile(...)  
 7 |           \- pattern: file\_get\_contents(...)  
 8 |     message: \>-  
 9 |       These functions can be used to read to content of the files if the data  
10 |       inside is user-controlled. Don't use the input directly or validate the  
11 |       data properly before passing it to these functions.  
12 |     paths:  
13 |       include:  
14 |         \- '\*\*/wp-content/plugins/\*\*/\*.php'  
15 |     languages:  
16 |       \- php  
17 |     severity: WARNING  
18 |     metadata:  
19 |       category: security  
20 |       confidence: LOW  
21 |       likelihood: LOW  
22 |       impact: MEDIUM  
23 |       subcategory:  
24 |         \- audit  
25 |       technology:  
26 |         \- Wordpress Plugins  
27 |       references:  
28 |         \- https://github.com/wpscanteam/wpscan/wiki/WordPress-Plugin-Security-Testing-Cheat-Sheet\#file-download  
29 |       cwe:  
30 |         \- "CWE-73: External Control of File Name or Path"  
31 |       owasp:  
32 |         \- A01:2021 \- Broken Access Control  
33 | 

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-file-inclusion-audit.php:  
\--------------------------------------------------------------------------------  
 1 | \<?php  
 2 |   
 3 | // ruleid: wp-file-inclusion-audit  
 4 | require $located;  
 5 |   
 6 | // ruleid: wp-file-inclusion-audit  
 7 | require\_once ABSPATH . 'wp-admin/includes/plugin.php';  
 8 |   
 9 | // ruleid: wp-file-inclusion-audit  
10 | include \_\_DIR\_\_ . '/wp-hooks.php';  
11 |   
12 | // ruleid: wp-file-inclusion-audit  
13 | include\_once($extension\_upload\_value);  
14 |   
15 | // ruleid: wp-file-inclusion-audit  
16 | $contents \= json\_decode( fread( $handle, filesize( $eventInfoFile ) ) );  
17 |   
18 | // ruleid: wp-file-inclusion-audit  
19 | $read\_text\_ser \= fread($open\_txt , filesize($import\_txt\_path));  
20 |   
21 | //ok: wp-file-inclusion-audit  
22 | some\_other\_function($args);  
23 |   
24 |   
25 | ?\>

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-file-inclusion-audit.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 |   \- id: wp-file-inclusion-audit  
 3 |     patterns:  
 4 |       \- pattern-either:  
 5 |           \- pattern: include(...)  
 6 |           \- pattern: require(...)  
 7 |           \- pattern: include\_once(...)  
 8 |           \- pattern: require\_once(...)  
 9 |           \- pattern: fread(...)  
10 |     message: \>-  
11 |       These functions can lead to Local File Inclusion (LFI) or Remote File  
12 |       Inclusion (RFI) if the data inside is user-controlled. Validate the data  
13 |       properly before passing it to these functions.  
14 |     paths:  
15 |       include:  
16 |         \- "'\*\*/wp-content/plugins/\*\*/\*.php'"  
17 |     languages:  
18 |       \- php  
19 |     severity: WARNING  
20 |     metadata:  
21 |       category: security  
22 |       confidence: LOW  
23 |       likelihood: LOW  
24 |       impact: HIGH  
25 |       subcategory:  
26 |         \- audit  
27 |       technology:  
28 |         \- Wordpress Plugins  
29 |       references:  
30 |         \- https://github.com/wpscanteam/wpscan/wiki/WordPress-Plugin-Security-Testing-Cheat-Sheet\#file-inclusion  
31 |       owasp:  
32 |         \- A01:2021 \- Broken Access Control  
33 |         \- A08:2021 \- Software and Data Integrity Failures  
34 |       cwe:  
35 |         \- "CWE-22: Improper Limitation of a Pathname to a Restricted Directory ('Path Traversal')"  
36 |         \- "CWE-73: The software allows user input to control or influence paths of file names that are used in filesystem operations."  
37 |         \- "CWE-98: Improper Control of Filename for Include/Require Statement in PHP Program ('PHP Remote File Inclusion')"  
38 | 

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-file-manipulation-audit.php:  
\--------------------------------------------------------------------------------  
 1 | \<?php  
 2 |   
 3 | // ruleid: wp-file-manipulation-audit  
 4 | wp\_delete\_file( $file\_path );  
 5 |   
 6 | // ruleid: wp-file-manipulation-audit  
 7 | unlink($file\_path);  
 8 |   
 9 | // ok: wp-file-manipulation-audit  
10 | some\_other\_function($args);  
11 |   
12 |   
13 | ?\>  
14 | 

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-file-manipulation-audit.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 |   \- id: wp-file-manipulation-audit  
 3 |     patterns:  
 4 |       \- pattern-either:  
 5 |           \- pattern: unlink(...)  
 6 |           \- pattern: wp\_delete\_file(...)  
 7 |     message: \>-  
 8 |       These functions can be used to delete the files if the data inside the  
 9 |       functions are user controlled. Use these functions carefully.  
10 |     paths:  
11 |       include:  
12 |         \- '\*\*/wp-content/plugins/\*\*/\*.php'  
13 |     languages:  
14 |       \- php  
15 |     severity: WARNING  
16 |     metadata:  
17 |       category: security  
18 |       confidence: LOW  
19 |       likelihood: LOW  
20 |       impact: HIGH  
21 |       subcategory:  
22 |         \- audit  
23 |       technology:  
24 |         \- Wordpress Plugins  
25 |       references:  
26 |         \- https://github.com/wpscanteam/wpscan/wiki/WordPress-Plugin-Security-Testing-Cheat-Sheet\#file-manipulation  
27 |       owasp:  
28 |         \- A01:2021 \- Broken Access Control  
29 |         \- A08:2021 \- Software and Data Integrity Failures  
30 |       cwe:  
31 |         \- "CWE-22: Improper Limitation of a Pathname to a Restricted Directory ('Path Traversal')"  
32 |         \- "CWE-73: The software allows user input to control or influence paths of file names that are used in filesystem operations."  
33 |         \- "CWE-98: Improper Control of Filename for Include/Require Statement in PHP Program ('PHP Remote File Inclusion')"  
34 | 

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-open-redirect-audit.php:  
\--------------------------------------------------------------------------------  
 1 | \<?php  
 2 |   
 3 | // redirect should be followed by exit  
 4 |   
 5 | // ruleid: wp-open-redirect-audit  
 6 | wp\_redirect( $url);  
 7 | exit;  
 8 |   
 9 |   
10 | // ok: wp-open-redirect-audit  
11 | // safe redirect  
12 | wp\_safe\_redirect($url);   
13 | exit;  
14 | ?\>

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-open-redirect-audit.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 |   \- id: wp-open-redirect-audit  
 3 |     pattern: wp\_redirect(...)  
 4 |     message: \>-  
 5 |       This function can be used to redirect to user supplied URLs. If user  
 6 |       input is not sanitised or validated, this could lead to Open Redirect  
 7 |       vulnerabilities. Use "wp\_safe\_redirect()" to prevent this kind of attack.  
 8 |     paths:  
 9 |       include:  
10 |         \- '\*\*/wp-content/plugins/\*\*/\*.php'  
11 |     languages:  
12 |       \- php  
13 |     severity: WARNING  
14 |     metadata:  
15 |       category: security  
16 |       confidence: LOW  
17 |       likelihood: LOW  
18 |       impact: MEDIUM  
19 |       subcategory:  
20 |         \- audit  
21 |       technology:  
22 |         \- Wordpress Plugins  
23 |       references:  
24 |         \- https://github.com/wpscanteam/wpscan/wiki/WordPress-Plugin-Security-Testing-Cheat-Sheet\#open-redirect  
25 |         \- https://developer.wordpress.org/reference/functions/wp\_safe\_redirect/  
26 |       cwe:  
27 |         \- "CWE-601: URL Redirection to Untrusted Site ('Open Redirect')"  
28 |       owasp:  
29 |         \- A05:2021 \- Security Misconfiguration  
30 | 

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-php-object-injection-audit.php:  
\--------------------------------------------------------------------------------  
 1 | \<?php  
 2 |   
 3 | // ruleid: wp-php-object-injection-audit  
 4 | $content \= unserialize($POST\['post\_content'\]);  
 5 |   
 6 | // ruleid: wp-php-object-injection-audit  
 7 | $rank\_math=unserialize($rank\_value);  
 8 |   
 9 | // ruleid: wp-php-object-injection-audit  
10 | $import\_options \= maybe\_unserialize($import-\>options);  
11 |   
12 | // ruleid: wp-php-object-injection-audit  
13 | $data \= unserialize(base64\_decode($var));  
14 |   
15 | // ok: wp-php-object-injection-audit  
16 | $data \= serialize(base64\_encode($var))  
17 |   
18 | ?\>

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-php-object-injection-audit.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 |   \- id: wp-php-object-injection-audit  
 3 |     patterns:  
 4 |       \- pattern-either:  
 5 |           \- pattern: unserialize(...)  
 6 |           \- pattern: maybe\_unserialize(...)  
 7 |     message: \>-  
 8 |       If the data used inside the patterns are directly used without proper  
 9 |       sanitization, then this could lead to PHP Object Injection. Do not use  
10 |       these function with user-supplied input, use JSON functions instead.  
11 |     paths:  
12 |       include:  
13 |         \- '\*\*/wp-content/plugins/\*\*/\*.php'  
14 |     languages:  
15 |       \- php  
16 |     severity: WARNING  
17 |     metadata:  
18 |       category: security  
19 |       confidence: LOW  
20 |       likelihood: LOW  
21 |       impact: HIGH  
22 |       subcategory:  
23 |         \- audit  
24 |       technology:  
25 |         \- Wordpress Plugins  
26 |       references:  
27 |         \- https://github.com/wpscanteam/wpscan/wiki/WordPress-Plugin-Security-Testing-Cheat-Sheet\#php-object-injection  
28 |         \- https://owasp.org/www-community/vulnerabilities/PHP\_Object\_Injection  
29 |       cwe:  
30 |         \- "CWE-502: Deserialization of Untrusted Data"  
31 |       owasp:  
32 |         \- A03:2021 \- Injection  
33 | 

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-sql-injection-audit.php:  
\--------------------------------------------------------------------------------  
 1 | \<?php  
 2 |   
 3 | // ruleid: wp-sql-injection-audit  
 4 | $result \= $wpdb-\>get\_var("SELECT meta\_value FROM {$wpdb-\>prefix}table WHERE order\_item\_id \= $order\_item\_id AND meta\_key \= $meta\_key");  
 5 |   
 6 | // ruleid: wp-sql-injection-audit  
 7 | $get\_question\_options \= $wpdb-\>get\_results("SELECT \* FROM {$wpdb-\>prefix}table WHERE question\_id \= $id ", ARRAY\_A);  
 8 |   
 9 | // ok: wp-sql-injection-audit  
10 | $wpdb-\>prepare("SELECT $column FROM $this-\>table\_name WHERE $this-\>primary\_key \= %d LIMIT 1;",(int) $row\_id);  
11 |   
12 |   
13 |   
14 | ?\>

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-sql-injection-audit.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 |   \- id: wp-sql-injection-audit  
 3 |     patterns:  
 4 |       \- pattern-either:  
 5 |           \- pattern: $wpdb-\>query(...)  
 6 |           \- pattern: $wpdb-\>get\_var(...)  
 7 |           \- pattern: $wpdb-\>get\_row(...)  
 8 |           \- pattern: $wpdb-\>get\_col(...)  
 9 |           \- pattern: $wpdb-\>get\_results(...)  
10 |           \- pattern: $wpdb-\>replace(...)  
11 |       \- pattern-not: $wpdb-\>prepare(...)  
12 |       \- pattern-not: $wpdb-\>delete(...)  
13 |       \- pattern-not: $wpdb-\>update(...)  
14 |       \- pattern-not: $wpdb-\>insert(...)  
15 |     message: \>-  
16 |       Detected unsafe API methods. This could lead to SQL Injection if the  
17 |       used variable in the functions are user controlled and not properly  
18 |       escaped or sanitized. In order to prevent SQL Injection, use safe api  
19 |       methods like "$wpdb-\>prepare" properly or escape/sanitize the data  
20 |       properly.  
21 |     paths:  
22 |       include:  
23 |         \- '\*\*/wp-content/plugins/\*\*/\*.php'  
24 |     languages:  
25 |       \- php  
26 |     severity: WARNING  
27 |     metadata:  
28 |       confidence: LOW  
29 |       likelihood: LOW  
30 |       impact: HIGH  
31 |       category: security  
32 |       subcategory:  
33 |         \- audit  
34 |       technology:  
35 |         \- Wordpress Plugins  
36 |       references:  
37 |         \- https://github.com/wpscanteam/wpscan/wiki/WordPress-Plugin-Security-Testing-Cheat-Sheet\#sql-injection  
38 |         \- https://owasp.org/www-community/attacks/SQL\_Injection  
39 |       owasp:  
40 |         \- A03:2021 \- Injection  
41 |       cwe:  
42 |         \- "CWE-89: Improper Neutralization of Special Elements used in an SQL Command ('SQL Injection')"  
43 | 

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-ssrf-audit.php:  
\--------------------------------------------------------------------------------  
 1 | \<?php  
 2 |   
 3 | $url \= $\_GET\['url'\];  
 4 | // ruleid: wp-ssrf-audit  
 5 | $response \= wp\_remote\_get($url);  
 6 |   
 7 | $url \= $\_GET\['url'\];  
 8 | // ruleid: wp-ssrf-audit  
 9 | $response \= wp\_safe\_remote\_get($url);  
10 |   
11 | $url \= $\_GET\['url'\];  
12 | // ruleid: wp-ssrf-audit  
13 | $response \= wp\_safe\_remote\_request($url);  
14 |   
15 | $url \= $\_GET\['url'\];  
16 | // ruleid: wp-ssrf-audit  
17 | $response \= wp\_safe\_remote\_head($url);  
18 |   
19 | $url \= $\_GET\['url'\];  
20 | // ruleid: wp-ssrf-audit  
21 | $response \= wp\_oembed\_get($url);  
22 |   
23 | $url \= $\_GET\['url'\];  
24 | // ruleid: wp-ssrf-audit  
25 | $response \= vip\_safe\_wp\_remote\_get($url);  
26 |   
27 | $url \= $\_GET\['url'\];  
28 | // ruleid: wp-ssrf-audit  
29 | $response \= wp\_safe\_remote\_post($url);  
30 |   
31 | // ruleid: wp-ssrf-audit  
32 | $response \= wp\_remote\_get($\_POST\['link'\]);  
33 |   
34 | // ruleid: wp-ssrf-audit  
35 | $response \= wp\_safe\_remote\_post($\_POST\['link'\]);  
36 |   
37 | // ruleid: wp-ssrf-audit  
38 | $response \= wp\_remote\_get($\_REQUEST\['target'\]);  
39 |   
40 | // ruleid: wp-ssrf-audit  
41 | $response \= wp\_safe\_remote\_request($\_REQUEST\['target'\]);  
42 |   
43 | $url \= get\_option('external\_api\_url');  
44 | // ruleid: wp-ssrf-audit  
45 | $response \= wp\_remote\_get($url);  
46 |   
47 | $url \= get\_user\_meta(get\_current\_user\_id(), 'custom\_api', true);  
48 | // ruleid: wp-ssrf-audit  
49 | $response \= wp\_remote\_get($url);  
50 |   
51 | $url \= get\_query\_var('redirect\_url');  
52 | // ruleid: wp-ssrf-audit  
53 | $response \= wp\_remote\_get($url);  
54 |   
55 | // ok: wp-ssrf-audit  
56 | $response \= wp\_remote\_get('https://example.com/api/data');  
57 |   
58 | ?\>

\--------------------------------------------------------------------------------  
/php/wordpress-plugins/security/audit/wp-ssrf-audit.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 | \- id: wp-ssrf-audit  
 3 |   languages:  
 4 |   \- php  
 5 |   severity: WARNING  
 6 |   message: Detected usage of vulnerable functions with user input, which could lead  
 7 |     to SSRF vulnerabilities.  
 8 |   mode: taint  
 9 |   pattern-sources:  
10 |     \- patterns:  
11 |       \- pattern-either:  
12 |         \- pattern: $\_GET\[...\]  
13 |         \- pattern: $\_POST\[...\]  
14 |         \- pattern: $\_REQUEST\[...\]  
15 |         \- pattern: get\_option(...)  
16 |         \- pattern: get\_user\_meta(...)  
17 |         \- pattern: get\_query\_var(...)  
18 |   pattern-sinks:  
19 |     \- patterns:  
20 |       \- focus-metavariable: $URL  
21 |       \- pattern-either:  
22 |         \- pattern: wp\_remote\_get($URL, ...)  
23 |         \- pattern: wp\_safe\_remote\_get($URL, ...)  
24 |         \- pattern: wp\_safe\_remote\_request($URL, ...)  
25 |         \- pattern: wp\_safe\_remote\_head($URL, ...)  
26 |         \- pattern: wp\_oembed\_get($URL, ...)  
27 |         \- pattern: vip\_safe\_wp\_remote\_get($URL, ...)  
28 |         \- pattern: wp\_safe\_remote\_post($URL, ...)  
29 |   paths:  
30 |     include:  
31 |     \- '\*\*/wp-content/plugins/\*\*/\*.php'  
32 |   metadata:  
33 |     cwe: 'CWE-918: Server-Side Request Forgery (SSRF)'  
34 |     owasp: A10:2021 \- Server-Side Request Forgery (SSRF)  
35 |     category: security  
36 |     confidence: MEDIUM  
37 |     likelihood: MEDIUM  
38 |     impact: HIGH  
39 |     subcategory:  
40 |     \- audit  
41 |     technology:  
42 |     \- Wordpress Plugins  
43 |     references:  
44 |     \- https://developer.wordpress.org/reference/functions/wp\_safe\_remote\_get/  
45 |     \- https://developer.wordpress.org/reference/functions/wp\_remote\_get/  
46 |     \- https://patchstack.com/articles/exploring-the-unpatched-wordpress-ssrf/  
47 |     vulnerability\_class:  
48 |     \- Server-Side Request Forgery (SSRF)  
49 | 

\--------------------------------------------------------------------------------  
