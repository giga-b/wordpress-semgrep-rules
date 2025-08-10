#!/usr/bin/env python3
"""
Tiny Semgrep ruleset generator for missed-variant suggestions

Generates Semgrep rules from predefined categories to catch additional
vulnerability patterns and evasion techniques.
"""

import argparse
import sys
import textwrap

CATEGORIES = {
    "token-mismanagement": [
        {
            "id": "vx.suggest.token.leak.header",
            "message": "Token exposure in response headers (variant)",
            "pattern_either": [
                'header("X-Token: " . $TOK)',
                'header("X-*Token*: " . $TOK)',
                'header($HNAME . ": " . $TOK)',
            ],
            "metavariable_regex": {"$HNAME": r"(?i).*(token|auth|session).*"},
            "notes": "Catches explicit and dynamic header names; extend $TOK naming filters if needed."
        },
        {
            "id": "vx.suggest.token.compare.loose",
            "message": "Loose comparison on secrets (== or !=) (variant)",
            "patterns": [
                {"pattern": "if ($A == $B) { ... }"},
                {"pattern": "if ($A != $B) { ... }"},
            ],
            "metavariable_regex": {
                "$A": r"(?i).*(token|auth|secret|key|provided|nonce).*",
                "$B": r"(?i).*(token|auth|secret|key|stored|nonce).*",
            },
            "notes": "Covers loose equality variants; complement with strict compare allowlist rule."
        },
        {
            "id": "vx.suggest.token.logged",
            "message": "Sensitive token logged (variant)",
            "pattern_either": [
                "error_log($X)",
                "trigger_error($X)",
                "print_r($X)",
            ],
            "metavariable_regex": {"$X": r"(?i).*(token|auth|secret|key).*"},
            "severity": "WARNING",
            "notes": "Captures common logging sinks; widen as needed."
        },
        {
            "id": "vx.suggest.token.weak.randomness",
            "message": "Weak randomness for token generation (variant)",
            "pattern_either": [
                "md5(uniqid(mt_rand(), true))",
                "md5(uniqid())",
                "sha1(uniqid())",
                "base64_encode(uniqid())",
            ],
            "notes": "Various weak randomness patterns for token generation."
        },
    ],
    "nonce-confusion": [
        {
            "id": "vx.suggest.nonce.check.noaction",
            "message": "Nonce verification without action context (wp_verify_nonce/check_admin_referer)",
            "pattern_either": [
                "wp_verify_nonce($NONCE)",
                "check_admin_referer($ACTION_OR_NONCE)",
            ],
            "notes": "Flag when action context is missing or reused across distinct actions.",
        },
        {
            "id": "vx.suggest.nonce.persisted",
            "message": "Nonce generated and stored persistently (stale/long-lived risk)",
            "patterns": [
                {"pattern": "$N = wp_create_nonce($CTX)"},
                {"pattern": "update_option($K, $N)"},
            ],
            "notes": "Nonce should not be stored in options/user meta; prefer per-request usage.",
        },
        {
            "id": "vx.suggest.nonce.reused",
            "message": "Nonce reused across multiple actions (confusion risk)",
            "patterns": [
                {"pattern": "wp_verify_nonce($NONCE, $ACTION1)"},
                {"pattern": "wp_verify_nonce($NONCE, $ACTION2)"},
            ],
            "notes": "Same nonce used for different actions - potential confusion attack."
        },
    ],
    "ssrf": [
        {
            "id": "vx.suggest.ssrf.escurl.unvalidated",
            "message": "esc_url_raw used without allowlist / internal IP restriction",
            "patterns": [{"pattern": "esc_url_raw($URL)"}],
            "notes": "Add taint-mode linking $URL to sinks wp_remote_get/post/request.",
        },
        {
            "id": "vx.suggest.ssrf.headers.usercontrolled",
            "message": "User-controlled request headers in outbound HTTP",
            "patterns": [
                {"pattern": "wp_remote_get($URL, ['headers' => $H])"},
                {"pattern": "wp_remote_request($URL, ['headers' => $H])"},
            ],
            "notes": "Follow with taint rules marking $H from REST params or $_GET/$_POST.",
        },
        {
            "id": "vx.suggest.ssrf.curl",
            "message": "cURL with user-controlled URL (SSRF risk)",
            "patterns": [
                {"pattern": "curl_setopt($CH, CURLOPT_URL, $URL)"},
                {"pattern": "curl_exec($CH)"},
            ],
            "notes": "cURL operations with potentially user-controlled URLs."
        },
    ],
    "header-injection": [
        {
            "id": "vx.suggest.header.injection.userinput",
            "message": "User input reflected into header()",
            "pattern_either": [
                "header($NAME . ': ' . $_GET[$X])",
                "header($NAME . ': ' . $_POST[$X])",
                "header($NAME . ': ' . $V)",
            ],
            "metavariable_regex": {"$NAME": r".*"},
            "notes": "Pair with taint mode to ensure $V comes from user-controlled sources.",
        },
        {
            "id": "vx.suggest.header.injection.crlf",
            "message": "Potential CRLF injection in header construction",
            "patterns": [
                {"pattern": "header($HDR . '\\r\\n' . $CONTENT)"},
                {"pattern": "header($HDR . '\\n' . $CONTENT)"},
            ],
            "notes": "Direct CRLF injection patterns in header construction."
        },
    ],
    "toctou": [
        {
            "id": "vx.suggest.toctou.unlink",
            "message": "TOCTOU: file_exists() check followed by unlink() on same path",
            "patterns": [{"pattern": "if (file_exists($P)) { unlink($P); }"}],
            "notes": "Extend with path traversal checks and lack of canonicalization.",
        },
        {
            "id": "vx.suggest.toctou.file_ops",
            "message": "TOCTOU: file operations without atomic handling",
            "patterns": [
                {"pattern": "if (file_exists($F)) { copy($F, $DEST); }"},
                {"pattern": "if (file_exists($F)) { rename($F, $DEST); }"},
            ],
            "notes": "Various file operations with TOCTOU race conditions."
        },
    ],
    "type-juggling": [
        {
            "id": "vx.suggest.typejuggle.loose.ops",
            "message": "Loose compare on sensitive values enables type-juggling",
            "pattern_either": [
                "if ($A == $B)",
                "if ($A != $B)",
                "$A == $B",
                "$A != $B",
            ],
            "metavariable_regex": {
                "$A": r"(?i).*(token|auth|secret|hash|nonce|provided).*",
                "$B": r"(?i).*(token|auth|secret|hash|nonce|stored).*",
            },
        },
        {
            "id": "vx.suggest.typejuggle.array_access",
            "message": "Type juggling via array access on sensitive values",
            "patterns": [
                {"pattern": "if ($A[0] == $B)"},
                {"pattern": "if ($A == $B[0])"},
            ],
            "metavariable_regex": {
                "$A": r"(?i).*(token|auth|secret|hash|nonce).*",
                "$B": r"(?i).*(token|auth|secret|hash|nonce).*",
            },
        },
    ],
    "permissive-kses": [
        {
            "id": "vx.suggest.kses.permissive.events",
            "message": "Permissive wp_kses allows event handlers (onclick, onmouseover, etc.)",
            "patterns": [{"pattern": "wp_kses($HTML, $ALLOWED)"}],
            "notes": "Use pattern-where-python to assert event attributes exist in $ALLOWED.",
        },
        {
            "id": "vx.suggest.kses.permissive.scripts",
            "message": "Permissive wp_kses allows script tags or javascript: URLs",
            "pattern_either": [
                "wp_kses($HTML, ['script' => []])",
                "wp_kses($HTML, ['a' => ['href' => true]])",
            ],
            "notes": "Allows script tags or javascript: URLs in href attributes."
        },
    ],
    "dom-xss": [
        {
            "id": "vx.suggest.dom.innerhtml",
            "message": "DOM XSS: assignment to innerHTML",
            "languages": ["javascript"],
            "patterns": [{"pattern": "$E.innerHTML = $S"}],
            "notes": "Pair with taint sources (location.hash, dataset, JSON-injected globals).",
        },
        {
            "id": "vx.suggest.dom.outerhtml",
            "message": "DOM XSS: assignment to outerHTML",
            "languages": ["javascript"],
            "patterns": [{"pattern": "$E.outerHTML = $S"}],
            "notes": "Similar to innerHTML but replaces entire element."
        },
        {
            "id": "vx.suggest.dom.document.write",
            "message": "DOM XSS: document.write with user input",
            "languages": ["javascript"],
            "patterns": [{"pattern": "document.write($S)"}],
            "notes": "Direct document.write with potentially user-controlled content."
        },
    ],
    "deserialization": [
        {
            "id": "vx.suggest.deserialize.untrusted",
            "message": "Dangerous deserialization of user-controlled data",
            "pattern_either": [
                "unserialize($DATA)",
                "maybe_unserialize($DATA)",
                "json_decode($DATA, true)",
            ],
            "notes": "Various deserialization functions with user-controlled input."
        },
        {
            "id": "vx.suggest.deserialize.echo",
            "message": "Echo of deserialized content without validation",
            "patterns": [
                {"pattern": "echo $DESERIALIZED['html']"},
                {"pattern": "print $DESERIALIZED['content']"},
            ],
            "notes": "Direct output of deserialized content - XSS sink."
        },
    ],
    "path-traversal": [
        {
            "id": "vx.suggest.path.unvalidated.concat",
            "message": "Path concatenation without validation",
            "patterns": [
                {"pattern": "$PATH = $BASE . $USER_INPUT"},
                {"pattern": "$PATH = $BASE . '/' . $USER_INPUT"},
            ],
            "notes": "User-controlled path concatenation without validation."
        },
        {
            "id": "vx.suggest.path.directory_traversal",
            "message": "Directory traversal patterns",
            "pattern_either": [
                "..",
                "../",
                "..\\",
                "%2e%2e",
            ],
            "notes": "Common directory traversal patterns in user input."
        },
    ],
    "sql-injection": [
        {
            "id": "vx.suggest.sql.direct_query",
            "message": "Direct SQL query with user input",
            "patterns": [
                {"pattern": "$wpdb->query('SELECT * FROM ' . $TABLE . ' WHERE id = ' . $ID)"},
                {"pattern": "$wpdb->query(\"SELECT * FROM $TABLE WHERE id = $ID\")"},
            ],
            "notes": "Direct SQL query construction with user-controlled input."
        },
        {
            "id": "vx.suggest.sql.prepare_misuse",
            "message": "Misuse of prepared statements",
            "patterns": [
                {"pattern": "$wpdb->prepare('SELECT * FROM ' . $TABLE . ' WHERE id = %s', $ID)"},
            ],
            "notes": "Prepared statement with table name concatenation."
        },
    ],
    "xss": [
        {
            "id": "vx.suggest.xss.echo_untrusted",
            "message": "Echo of untrusted data without escaping",
            "patterns": [
                {"pattern": "echo $UNTRUSTED"},
                {"pattern": "print $UNTRUSTED"},
            ],
            "notes": "Direct output of untrusted data without proper escaping."
        },
        {
            "id": "vx.suggest.xss.attribute_untrusted",
            "message": "Untrusted data in HTML attributes",
            "patterns": [
                {"pattern": "<img src=\"$UNTRUSTED\">"},
                {"pattern": "<a href=\"$UNTRUSTED\">"},
            ],
            "notes": "User-controlled data in HTML attributes without validation."
        },
    ],
}


def rule_to_yaml(rule):
    """Convert a rule dictionary to YAML format."""
    # Default values
    lang = rule.get("languages", ["php"])
    severity = rule.get("severity", "ERROR")
    lines = []
    
    lines.append("  - id: {}".format(rule["id"]))
    lines.append("    languages: [{}]".format(", ".join(lang)))
    
    if "patterns" in rule:
        lines.append("    patterns:")
        for p in rule["patterns"]:
            lines.append("      - pattern: |")
            lines.append("          {}".format(p["pattern"]))
    elif "pattern_either" in rule:
        lines.append("    pattern-either:")
        for p in rule["pattern_either"]:
            lines.append("      - pattern: |")
            lines.append("          {}".format(p))
    else:
        lines.append("    pattern: |")
        lines.append("      {}".format(rule["pattern"]))
    
    lines.append("    message: {}".format(rule["message"]))
    lines.append("    severity: {}".format(severity))
    lines.append("    metadata:")
    lines.append("      category: suggest")
    
    if "notes" in rule:
        for line in textwrap.dedent(rule["notes"]).splitlines():
            if line.strip():
                lines.append("      note: '{}'".format(line.replace("'", "''")))
    
    if "metavariable_regex" in rule:
        lines.append("    metavariable-regex:")
        for k, v in rule["metavariable_regex"].items():
            lines.append("      {}: {}".format(k, v))
    
    return "\n".join(lines)


def main():
    """Main function to generate rules."""
    ap = argparse.ArgumentParser(description="Generate Semgrep rules for missed variants")
    ap.add_argument("--categories", help="Comma-separated categories")
    ap.add_argument("--outfile", default="missed-variants.yaml", help="Output file name")
    ap.add_argument("--list", action="store_true", help="List available categories")
    args = ap.parse_args()

    if args.list:
        print("Available categories:")
        for cat in sorted(CATEGORIES.keys()):
            print(f"  - {cat}")
        return

    if not args.categories:
        print("[err] --categories is required unless using --list", file=sys.stderr)
        sys.exit(1)

    cats = [c.strip().lower() for c in args.categories.split(",") if c.strip()]
    selected = []
    
    for c in cats:
        if c not in CATEGORIES:
            print(f"[warn] Unknown category: {c}", file=sys.stderr)
            continue
        selected.extend(CATEGORIES[c])

    if not selected:
        print("[err] No valid categories selected.", file=sys.stderr)
        sys.exit(1)

    out = ["rules:"]
    for rule in selected:
        out.append(rule_to_yaml(rule))

    with open(args.outfile, "w", encoding="utf-8") as f:
        f.write("\n".join(out) + "\n")

    print(f"[ok] Wrote {len(selected)} rules to {args.outfile}")
    print(f"[info] Categories included: {', '.join(cats)}")


if __name__ == "__main__":
    main()
