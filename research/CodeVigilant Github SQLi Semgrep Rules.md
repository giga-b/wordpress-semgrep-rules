└── php  
    └── wordpress  
        └── SQLi  
            ├── basic\_cookie\_sqli.yaml  
            ├── basic\_sqli.yaml  
            ├── basic\_sqli\_2.yaml  
            ├── basic\_sqli\_get\_row.yaml  
            ├── basic\_sqli\_get\_var.yaml  
            ├── basic\_sqli\_var\_subs\_get\_row.yaml  
            └── basic\_user\_agent\_sqli.yaml

/php/wordpress/SQLi/basic\_cookie\_sqli.yaml:  
\--------------------------------------------------------------------------------  
  1 | rules:  
  2 | \- id: http\_header\_sqli  
  3 |   patterns:  
  4 |       \- pattern-either:  
  5 |         \- pattern: $\_COOKIE\[$KEY\]  
  6 |       \- pattern-inside: $wpdb-\>get\_results($X);  
  7 |       \- pattern-not-inside: $wpdb-\>prepare(...)  
  8 |       \- pattern-not-inside: $wpdb-\>escape(...)  
  9 |       \- pattern-not-inside: intval(...)  
 10 |       \- pattern-not-inside: sprintf(...)  
 11 |       \- pattern-not-inside: esc\_sql(...)  
 12 |       \- pattern-not-inside: rawurlencode(...)  
 13 |   languages: \[php\]  
 14 |   mode: search  
 15 |   message: Look for basic SQL Injection  
 16 |   severity: WARNING  
 17 |   
 18 | \- id: basic\_sqli\_get\_row  
 19 |   patterns:  
 20 |       \- pattern-either:  
 21 |         \- pattern: $\_COOKIE\[$KEY\]  
 22 |       \- pattern-inside: $wpdb-\>get\_row($X);  
 23 |       \- pattern-not-inside: $wpdb-\>prepare(...)  
 24 |       \- pattern-not-inside: intval(...)  
 25 |       \- pattern-not-inside: $wpdb-\>escape(...)  
 26 |       \- pattern-not-inside: sprintf(...)  
 27 |       \- pattern-not-inside: esc\_sql(...)  
 28 |       \- pattern-not-inside: rawurlencode(...)  
 29 |   languages: \[php\]  
 30 |   mode: search  
 31 |   message: Look for basic SQL Injection  
 32 |   severity: WARNING  
 33 |   
 34 | \- id: basic\_sqli\_query  
 35 |   patterns:  
 36 |       \- pattern-either:  
 37 |         \- pattern: $\_COOKIE\[$KEY\]  
 38 |       \- pattern-inside: $wpdb-\>query($X);  
 39 |       \- pattern-not-inside: $wpdb-\>prepare(...)  
 40 |       \- pattern-not-inside: intval(...)  
 41 |       \- pattern-not-inside: $wpdb-\>escape(...)  
 42 |       \- pattern-not-inside: sprintf(...)  
 43 |       \- pattern-not-inside: esc\_sql(...)  
 44 |       \- pattern-not-inside: rawurlencode(...)  
 45 |   languages: \[php\]  
 46 |   mode: search  
 47 |   message: Look for basic SQL Injection  
 48 |   severity: WARNING  
 49 |   
 50 |   
 51 | rules:  
 52 | \- id: get\_row\_deep\_exp  
 53 |   patterns:  
 54 |     \- pattern-either:   
 55 |       \- pattern: |  
 56 |           $V \= $\_COOKIE\[$KEY\];  
 57 |           ...  
 58 |           $wpdb-\>get\_row(\<... $V ...\>);  
 59 |     \- pattern-not-inside: |  
 60 |         $V \= $\_COOKIE\[$KEY\];  
 61 |         ...  
 62 |         $wpdb-\>get\_row(\<... (int) $V ...\>);  
 63 |     \- pattern-not: |  
 64 |         $V \= $\_COOKIE\[$KEY\];  
 65 |         ...  
 66 |         $wpdb-\>get\_row(\<... intval($V) ...\>);  
 67 |     \- pattern-not-inside: |  
 68 |         $V \= $\_COOKIE\[$KEY\];  
 69 |         ...  
 70 |         $wpdb-\>get\_row(\<... (int)$V ...\>);  
 71 |     \- pattern-not-inside: |  
 72 |         $V \= $\_COOKIE\[$KEY\];  
 73 |         ...  
 74 |         $wpdb-\>get\_row(\<... intval($V) ...\>);  
 75 |     \- pattern-not-inside: |  
 76 |         $V \= $\_COOKIE\[$KEY\];  
 77 |         ...  
 78 |         $wpdb-\>get\_row(\<... sprintf($V) ...\>);  
 79 |     \- pattern-not: |  
 80 |         $V \= $\_COOKIE\[$KEY\];  
 81 |         ...  
 82 |         $wpdb-\>get\_row(\<... esc\_sql($V) ...\>);  
 83 |     \- pattern-not-inside: |  
 84 |         $V \= $\_COOKIE\[$KEY\];  
 85 |         ...  
 86 |         $wpdb-\>get\_row(\<... sprintf($V) ...\>);  
 87 |     \- pattern-not-inside: |  
 88 |         $V \= $\_COOKIE\[$KEY\];  
 89 |         ...  
 90 |         $wpdb-\>get\_row(\<... esc\_sql($V) ...\>);  
 91 |   languages: \[php\]  
 92 |   mode: search  
 93 |   message: Semgrep found a match  
 94 |   severity: WARNING  
 95 |   
 96 | \- id: query\_deep\_exp  
 97 |   patterns:  
 98 |     \- pattern-either:  
 99 |       \- pattern: |  
100 |           $V \= $\_COOKIE\[$KEY\];  
101 |           ...  
102 |           $wpdb-\>query(\<... $V ...\>);  
103 |     \- pattern-not: |  
104 |         $V \= $\_COOKIE\[$KEY\];  
105 |         ...  
106 |         $wpdb-\>query(\<... (int) $V ...\>);  
107 |     \- pattern-not: |  
108 |         $V \= $\_COOKIE\[$KEY\];  
109 |         ...  
110 |         $wpdb-\>query(\<... intval($V) ...\>);  
111 |     \- pattern-not: |  
112 |         $V \= $\_COOKIE\[$KEY\];  
113 |         ...  
114 |         $wpdb-\>query(\<... (int)$V ...\>);  
115 |     \- pattern-not: |  
116 |         $V \= $\_COOKIE\[$KEY\];  
117 |         ...  
118 |         $wpdb-\>query(\<... intval($V) ...\>);  
119 |     \- pattern-not: |  
120 |         $V \= $\_COOKIE\[$KEY\];  
121 |         ...  
122 |         $wpdb-\>query(\<... sprintf($V) ...\>);  
123 |     \- pattern-not: |  
124 |         $V \= $\_COOKIE\[$KEY\];  
125 |         ...  
126 |         $wpdb-\>query(\<... esc\_sql($V) ...\>);  
127 |     \- pattern-not: |  
128 |         $V \= $\_COOKIE\[$KEY\];  
129 |         ...  
130 |         $wpdb-\>query(\<... sprintf($V) ...\>);  
131 |     \- pattern-not: |  
132 |         $V \= $\_COOKIE\[$KEY\];  
133 |         ...  
134 |         $wpdb-\>query(\<... esc\_sql($V) ...\>);  
135 |   languages: \[php\]  
136 |   mode: search  
137 |   message: Semgrep found a match  
138 |   severity: WARNING  
139 |   
140 | 

\--------------------------------------------------------------------------------  
/php/wordpress/SQLi/basic\_sqli.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 | \- id: basic\_sqli\_get\_results  
 3 |   patterns:  
 4 |       \- pattern-either:  
 5 |         \- pattern: $\_GET\[$KEY\]  
 6 |         \- pattern: $\_POST\[$KEY\]  
 7 |       \- pattern-inside: $wpdb-\>get\_results($X);  
 8 |       \- pattern-not-inside: $wpdb-\>prepare(...)  
 9 |       \- pattern-not-inside: $wpdb-\>escape(...)  
10 |       \- pattern-not-inside: intval(...)  
11 |       \- pattern-not-inside: sprintf(...)  
12 |       \- pattern-not-inside: esc\_sql(...)  
13 |       \- pattern-not-inside: rawurlencode(...)  
14 |   languages: \[php\]  
15 |   mode: search  
16 |   message: Look for basic SQL Injection  
17 |   severity: WARNING  
18 |   
19 | \- id: basic\_sqli\_get\_row  
20 |   patterns:  
21 |       \- pattern-either:  
22 |         \- pattern: $\_GET\[$KEY\]  
23 |         \- pattern: $\_POST\[$KEY\]  
24 |       \- pattern-inside: $wpdb-\>get\_row($X);  
25 |       \- pattern-not-inside: $wpdb-\>prepare(...)  
26 |       \- pattern-not-inside: intval(...)  
27 |       \- pattern-not-inside: $wpdb-\>escape(...)  
28 |       \- pattern-not-inside: sprintf(...)  
29 |       \- pattern-not-inside: esc\_sql(...)  
30 |       \- pattern-not-inside: rawurlencode(...)  
31 |   languages: \[php\]  
32 |   mode: search  
33 |   message: Look for basic SQL Injection  
34 |   severity: WARNING  
35 |   
36 | \- id: basic\_sqli\_query  
37 |   patterns:  
38 |       \- pattern-either:  
39 |         \- pattern: $\_GET\[$KEY\]  
40 |         \- pattern: $\_POST\[$KEY\]  
41 |       \- pattern-inside: $wpdb-\>query($X);  
42 |       \- pattern-not-inside: $wpdb-\>prepare(...)  
43 |       \- pattern-not-inside: intval(...)  
44 |       \- pattern-not-inside: $wpdb-\>escape(...)  
45 |       \- pattern-not-inside: sprintf(...)  
46 |       \- pattern-not-inside: esc\_sql(...)  
47 |       \- pattern-not-inside: rawurlencode(...)  
48 |   languages: \[php\]  
49 |   mode: search  
50 |   message: Look for basic SQL Injection  
51 |   severity: WARNING  
52 |   
53 |   
54 | 

\--------------------------------------------------------------------------------  
/php/wordpress/SQLi/basic\_sqli\_2.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 | \- id: basic\_sqli\_get\_results  
 3 |   patterns:  
 4 |       \- pattern-either:  
 5 |         \- pattern: $\_GET\[$KEY\]  
 6 |         \- pattern: $\_POST\[$KEY\]  
 7 |       \- pattern-inside: $wpdb-\>get\_results($X);  
 8 |       \- pattern-not-inside: $wpdb-\>prepare(...)  
 9 |       \- pattern-not-inside: $wpdb-\>escape(...)  
10 |       \- pattern-not-inside: intval(...)  
11 |       \- pattern-not-inside: sprintf(...)  
12 |       \- pattern-not-inside: esc\_sql(...)  
13 |   languages: \[php\]  
14 |   mode: search  
15 |   message: Look for basic SQL Injection  
16 |   severity: WARNING  
17 |   
18 | \- id: basic\_sqli\_get\_row  
19 |   patterns:  
20 |       \- pattern-either:  
21 |         \- pattern: $\_GET\[$KEY\]  
22 |         \- pattern: $\_POST\[$KEY\]  
23 |       \- pattern-not: (int)$\_POST\[$KEY\]  
24 |       \- pattern-inside: $wpdb-\>get\_row($X);  
25 |       \- pattern-not-inside: $wpdb-\>prepare(...)  
26 |       \- pattern-not-inside: intval(...)  
27 |       \- pattern-not-inside: $wpdb-\>escape(...)  
28 |       \- pattern-not-inside: sprintf(...)  
29 |       \- pattern-not-inside: esc\_sql(...)  
30 |   languages: \[php\]  
31 |   mode: search  
32 |   message: Look for basic SQL Injection  
33 |   severity: WARNING  
34 |   
35 | \- id: basic\_sqli\_query  
36 |   patterns:  
37 |       \- pattern-either:  
38 |         \- pattern: $\_GET\[$KEY\]  
39 |         \- pattern: $\_POST\[$KEY\]  
40 |       \- pattern-inside: $wpdb-\>query($X);  
41 |       \- pattern-not-inside: $wpdb-\>prepare(...)  
42 |       \- pattern-not-inside: intval(...)  
43 |       \- pattern-not-inside: $wpdb-\>escape(...)  
44 |       \- pattern-not-inside: sprintf(...)  
45 |       \- pattern-not-inside: esc\_sql(...)  
46 |   languages: \[php\]  
47 |   mode: search  
48 |   message: Look for basic SQL Injection  
49 |   severity: WARNING  
50 |   
51 |   
52 | 

\--------------------------------------------------------------------------------  
/php/wordpress/SQLi/basic\_sqli\_get\_row.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 | \- id: get\_row\_deep\_exp  
 3 |   patterns:  
 4 |     \- pattern-either:   
 5 |       \- pattern: |  
 6 |           $wpdb-\>get\_row(\<... $\_GET\[$KEY\] ...\>)  
 7 |       \- pattern: |  
 8 |           $wpdb-\>get\_row(\<... $\_POST\[$KEY\] ...\>)  
 9 |     \- pattern-not: |  
10 |         $wpdb-\>get\_row(\<... (int) $\_POST\[$KEY\] ...\>)  
11 |     \- pattern-not: |  
12 |         $wpdb-\>get\_row(\<... intval($\_POST\[$KEY\]) ...\>)  
13 |     \- pattern-not: |  
14 |         $wpdb-\>get\_row(\<... sprintf($\_POST\[$KEY\]) ...\>)  
15 |     \- pattern-not: |  
16 |         $wpdb-\>get\_row(\<... esc\_sql($\_POST\[$KEY\]) ...\>)  
17 |     \- pattern-not: |  
18 |         $wpdb-\>get\_row(\<... (int) $GET\[$KEY\] ...\>)  
19 |     \- pattern-not: |  
20 |         $wpdb-\>get\_row(\<... intval($GET\[$KEY\]) ...\>)  
21 |     \- pattern-not: |  
22 |         $wpdb-\>get\_row(\<... sprintf($\_GET\[$KEY\]) ...\>)  
23 |     \- pattern-not: |  
24 |         $wpdb-\>get\_row(\<... esc\_sql($\_GET\[$KEY\]) ...\>)  
25 |   languages: \[php\]  
26 |   mode: search  
27 |   message: Semgrep found a match  
28 |   severity: WARNING  
29 |   
30 | \- id: query\_deep\_expr  
31 |   patterns:  
32 |     \- pattern-either:   
33 |       \- pattern: |  
34 |           $wpdb-\>query(\<... $\_GET\[$KEY\] ...\>)  
35 |       \- pattern: |  
36 |           $wpdb-\>query(\<... $\_POST\[$KEY\] ...\>)   
37 |     \- pattern-not: |  
38 |         $wpdb-\>query(\<... (int) $\_POST\[$KEY\] ...\>)  
39 |     \- pattern-not: |  
40 |         $wpdb-\>query(\<... intval($\_POST\[$KEY\]) ...\>)  
41 |     \- pattern-not: |  
42 |         $wpdb-\>query(\<... sprintf($\_POST\[$KEY\]) ...\>)  
43 |     \- pattern-not: |  
44 |         $wpdb-\>query(\<... esc\_sql($\_POST\[$KEY\]) ...\>)  
45 |     \- pattern-not: |  
46 |         $wpdb-\>query(\<... (int) $GET\[$KEY\] ...\>)  
47 |     \- pattern-not: |  
48 |         $wpdb-\>query(\<... intval($GET\[$KEY\]) ...\>)  
49 |     \- pattern-not: |  
50 |         $wpdb-\>query(\<... sprintf($\_GET\[$KEY\]) ...\>)  
51 |     \- pattern-not: |  
52 |         $wpdb-\>query(\<... esc\_sql($\_GET\[$KEY\]) ...\>)  
53 |   languages: \[php\]  
54 |   mode: search  
55 |   message: Semgrep found a match  
56 |   severity: WARNING  
57 |   
58 |   
59 |   
60 | 

\--------------------------------------------------------------------------------  
/php/wordpress/SQLi/basic\_sqli\_get\_var.yaml:  
\--------------------------------------------------------------------------------  
 1 | rules:  
 2 | \- id: http\_header\_sqli  
 3 |   patterns:  
 4 |       \- pattern-either:  
 5 |         \- pattern: $\_GET\[$KEY\]  
 6 |         \- pattern: $\_POST\[$KEY\]  
 7 |         \- pattern: $\_COOKIE\[$KEY\]  
 8 |         \- pattern: $\_SERVER\[$KEY\]  
 9 |       \- pattern-inside: $wpdb-\>get\_var($X);  
10 |       \- pattern-not-inside: $wpdb-\>prepare(...)  
11 |       \- pattern-not-inside: $wpdb-\>escape(...)  
12 |       \- pattern-not-inside: intval(...)  
13 |       \- pattern-not-inside: sprintf(...)  
14 |       \- pattern-not-inside: esc\_sql(...)  
15 |       \- pattern-not-inside: rawurlencode(...)  
16 |   languages: \[php\]  
17 |   mode: search  
18 |   message: Look for basic SQL Injection  
19 |   severity: WARNING  
20 | 

\--------------------------------------------------------------------------------  
/php/wordpress/SQLi/basic\_sqli\_var\_subs\_get\_row.yaml:  
\--------------------------------------------------------------------------------  
  1 | rules:  
  2 | \- id: get\_row\_deep\_exp  
  3 |   patterns:  
  4 |     \- pattern-either:   
  5 |       \- pattern: |  
  6 |           $V \= $\_GET\[$KEY\];  
  7 |           ...  
  8 |           $wpdb-\>get\_row(\<... $V ...\>);  
  9 |       \- pattern: |  
 10 |           $V \= $\_POST\[$KEY\];  
 11 |           ...  
 12 |           $wpdb-\>get\_row(\<... $V ...\>);  
 13 |     \- pattern-not-inside: |  
 14 |         $V \= $\_POST\[$KEY\];  
 15 |         ...  
 16 |         $wpdb-\>get\_row($wpdb-\>prepare(...));  
 17 |     \- pattern-not-inside: |  
 18 |         $V \= $\_GET\[$KEY\];  
 19 |         ...  
 20 |         $wpdb-\>get\_row($wpdb-\>prepare(...));  
 21 |     \- pattern-not-inside: |  
 22 |         $V \= $\_POST\[$KEY\];  
 23 |         ...  
 24 |         $wpdb-\>get\_row(\<... (int) $V ...\>);  
 25 |     \- pattern-not: |  
 26 |         $V \= $\_POST\[$KEY\];  
 27 |         ...  
 28 |         $wpdb-\>get\_row(\<... intval($V) ...\>);  
 29 |     \- pattern-not-inside: |  
 30 |         $V \= $\_GET\[$KEY\];  
 31 |         ...  
 32 |         $wpdb-\>get\_row(\<... (int)$V ...\>);  
 33 |     \- pattern-not-inside: |  
 34 |         $V \= $\_GET\[$KEY\];  
 35 |         ...  
 36 |         $wpdb-\>get\_row(\<... intval($V) ...\>);  
 37 |     \- pattern-not-inside: |  
 38 |         $V \= $\_POST\[$KEY\];  
 39 |         ...  
 40 |         $wpdb-\>get\_row(\<... sprintf($V) ...\>);  
 41 |     \- pattern-not: |  
 42 |         $V \= $\_POST\[$KEY\];  
 43 |         ...  
 44 |         $wpdb-\>get\_row(\<... esc\_sql($V) ...\>);  
 45 |     \- pattern-not-inside: |  
 46 |         $V \= $\_GET\[$KEY\];  
 47 |         ...  
 48 |         $wpdb-\>get\_row(\<... sprintf($V) ...\>);  
 49 |     \- pattern-not-inside: |  
 50 |         $V \= $\_GET\[$KEY\];  
 51 |         ...  
 52 |         $wpdb-\>get\_row(\<... esc\_sql($V) ...\>);  
 53 |   languages: \[php\]  
 54 |   mode: search  
 55 |   message: Semgrep found a match  
 56 |   severity: WARNING  
 57 |   
 58 | \- id: query\_deep\_exp  
 59 |   patterns:  
 60 |     \- pattern-either:  
 61 |       \- pattern: |  
 62 |           $V \= $\_GET\[$KEY\];  
 63 |           ...  
 64 |           $wpdb-\>query(\<... $V ...\>);  
 65 |       \- pattern: |  
 66 |           $V \= $\_POST\[$KEY\];  
 67 |           ...  
 68 |           $wpdb-\>query(\<... $V ...\>);  
 69 |     \- pattern-not-inside: |  
 70 |         $V \= $\_POST\[$KEY\];  
 71 |         ...  
 72 |         $wpdb-\>query($wpdb-\>prepare(...));  
 73 |     \- pattern-not-inside: |  
 74 |         $V \= $\_GET\[$KEY\];  
 75 |         ...  
 76 |         $wpdb-\>query($wpdb-\>prepare(...));  
 77 |     \- pattern-not-inside: |  
 78 |         $V \= $\_POST\[$KEY\];  
 79 |         ...  
 80 |         $wpdb-\>query(\<... (int) $V ...\>);  
 81 |     \- pattern-not: |  
 82 |         $V \= $\_POST\[$KEY\];  
 83 |         ...  
 84 |         $wpdb-\>query(\<... intval($V) ...\>);  
 85 |     \- pattern-not-inside: |  
 86 |         $V \= $\_GET\[$KEY\];  
 87 |         ...  
 88 |         $wpdb-\>query(\<... (int)$V ...\>);  
 89 |     \- pattern-not-inside: |  
 90 |         $V \= $\_GET\[$KEY\];  
 91 |         ...  
 92 |         $wpdb-\>query(\<... intval($V) ...\>);  
 93 |     \- pattern-not-inside: |  
 94 |         $V \= $\_POST\[$KEY\];  
 95 |         ...  
 96 |         $wpdb-\>query(\<... sprintf($V) ...\>);  
 97 |     \- pattern-not: |  
 98 |         $V \= $\_POST\[$KEY\];  
 99 |         ...  
100 |         $wpdb-\>query(\<... esc\_sql($V) ...\>);  
101 |     \- pattern-not-inside: |  
102 |         $V \= $\_GET\[$KEY\];  
103 |         ...  
104 |         $wpdb-\>query(\<... sprintf($V) ...\>);  
105 |     \- pattern-not-inside: |  
106 |         $V \= $\_GET\[$KEY\];  
107 |         ...  
108 |         $wpdb-\>query(\<... esc\_sql($V) ...\>);  
109 |   languages: \[php\]  
110 |   mode: search  
111 |   message: Semgrep found a match  
112 |   severity: WARNING  
113 |   
114 | 

\--------------------------------------------------------------------------------  
/php/wordpress/SQLi/basic\_user\_agent\_sqli.yaml:  
\--------------------------------------------------------------------------------  
  1 | rules:  
  2 | \- id: http\_header\_sqli  
  3 |   patterns:  
  4 |       \- pattern-either:  
  5 |         \- pattern: $\_SERVER\['HTTP\_USER\_AGENT'\]  
  6 |       \- pattern-inside: $wpdb-\>get\_results($X);  
  7 |       \- pattern-not-inside: $wpdb-\>prepare(...)  
  8 |       \- pattern-not-inside: $wpdb-\>escape(...)  
  9 |       \- pattern-not-inside: intval(...)  
 10 |       \- pattern-not-inside: sprintf(...)  
 11 |       \- pattern-not-inside: esc\_sql(...)  
 12 |       \- pattern-not-inside: rawurlencode(...)  
 13 |   languages: \[php\]  
 14 |   mode: search  
 15 |   message: Look for basic SQL Injection  
 16 |   severity: WARNING  
 17 |   
 18 | \- id: basic\_sqli\_get\_row  
 19 |   patterns:  
 20 |       \- pattern-either:  
 21 |         \- pattern: $\_SERVER\['HTTP\_USER\_AGENT'\]  
 22 |       \- pattern-inside: $wpdb-\>get\_row($X);  
 23 |       \- pattern-not-inside: $wpdb-\>prepare(...)  
 24 |       \- pattern-not-inside: intval(...)  
 25 |       \- pattern-not-inside: $wpdb-\>escape(...)  
 26 |       \- pattern-not-inside: sprintf(...)  
 27 |       \- pattern-not-inside: esc\_sql(...)  
 28 |       \- pattern-not-inside: rawurlencode(...)  
 29 |   languages: \[php\]  
 30 |   mode: search  
 31 |   message: Look for basic SQL Injection  
 32 |   severity: WARNING  
 33 |   
 34 | \- id: basic\_sqli\_query  
 35 |   patterns:  
 36 |       \- pattern-either:  
 37 |         \- pattern: $\_SERVER\['HTTP\_USER\_AGENT'\]  
 38 |       \- pattern-inside: $wpdb-\>query($X);  
 39 |       \- pattern-not-inside: $wpdb-\>prepare(...)  
 40 |       \- pattern-not-inside: intval(...)  
 41 |       \- pattern-not-inside: $wpdb-\>escape(...)  
 42 |       \- pattern-not-inside: sprintf(...)  
 43 |       \- pattern-not-inside: esc\_sql(...)  
 44 |       \- pattern-not-inside: rawurlencode(...)  
 45 |   languages: \[php\]  
 46 |   mode: search  
 47 |   message: Look for basic SQL Injection  
 48 |   severity: WARNING  
 49 |   
 50 |   
 51 | rules:  
 52 | \- id: get\_row\_deep\_exp  
 53 |   patterns:  
 54 |     \- pattern-either:   
 55 |       \- pattern: |  
 56 |           $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
 57 |           ...  
 58 |           $wpdb-\>get\_row(\<... $V ...\>);  
 59 |       \- pattern: |  
 60 |           $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
 61 |           ...  
 62 |           $wpdb-\>get\_row(\<... $V ...\>);  
 63 |     \- pattern-not-inside: |  
 64 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
 65 |         ...  
 66 |         $wpdb-\>get\_row(\<... (int) $V ...\>);  
 67 |     \- pattern-not: |  
 68 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
 69 |         ...  
 70 |         $wpdb-\>get\_row(\<... intval($V) ...\>);  
 71 |     \- pattern-not-inside: |  
 72 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
 73 |         ...  
 74 |         $wpdb-\>get\_row(\<... (int)$V ...\>);  
 75 |     \- pattern-not-inside: |  
 76 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
 77 |         ...  
 78 |         $wpdb-\>get\_row(\<... intval($V) ...\>);  
 79 |     \- pattern-not-inside: |  
 80 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
 81 |         ...  
 82 |         $wpdb-\>get\_row(\<... sprintf($V) ...\>);  
 83 |     \- pattern-not: |  
 84 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
 85 |         ...  
 86 |         $wpdb-\>get\_row(\<... esc\_sql($V) ...\>);  
 87 |     \- pattern-not-inside: |  
 88 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
 89 |         ...  
 90 |         $wpdb-\>get\_row(\<... sprintf($V) ...\>);  
 91 |     \- pattern-not-inside: |  
 92 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
 93 |         ...  
 94 |         $wpdb-\>get\_row(\<... esc\_sql($V) ...\>);  
 95 |   languages: \[php\]  
 96 |   mode: search  
 97 |   message: Semgrep found a match  
 98 |   severity: WARNING  
 99 |   
100 | \- id: query\_deep\_exp  
101 |   patterns:  
102 |     \- pattern-either:  
103 |       \- pattern: |  
104 |           $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
105 |           ...  
106 |           $wpdb-\>query(\<... $V ...\>);  
107 |       \- pattern: |  
108 |           $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
109 |           ...  
110 |           $wpdb-\>query(\<... $V ...\>);  
111 |     \- pattern-not-inside: |  
112 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
113 |         ...  
114 |         $wpdb-\>query(\<... (int) $V ...\>);  
115 |     \- pattern-not: |  
116 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
117 |         ...  
118 |         $wpdb-\>query(\<... intval($V) ...\>);  
119 |     \- pattern-not-inside: |  
120 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
121 |         ...  
122 |         $wpdb-\>query(\<... (int)$V ...\>);  
123 |     \- pattern-not-inside: |  
124 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
125 |         ...  
126 |         $wpdb-\>query(\<... intval($V) ...\>);  
127 |     \- pattern-not-inside: |  
128 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
129 |         ...  
130 |         $wpdb-\>query(\<... sprintf($V) ...\>);  
131 |     \- pattern-not: |  
132 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
133 |         ...  
134 |         $wpdb-\>query(\<... esc\_sql($V) ...\>);  
135 |     \- pattern-not-inside: |  
136 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
137 |         ...  
138 |         $wpdb-\>query(\<... sprintf($V) ...\>);  
139 |     \- pattern-not-inside: |  
140 |         $V \= $\_SERVER\['HTTP\_USER\_AGENT'\];  
141 |         ...  
142 |         $wpdb-\>query(\<... esc\_sql($V) ...\>);  
143 |   languages: \[php\]  
144 |   mode: search  
145 |   message: Semgrep found a match  
146 |   severity: WARNING  
147 |   
148 | 

\--------------------------------------------------------------------------------  
