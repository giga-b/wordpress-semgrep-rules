# XSS Test Scenarios Matrix

## Purpose

Design a comprehensive, context-aware suite of XSS scenarios to validate WordPress Semgrep rules across vulnerable and safe implementations. These scenarios align with Task 1.6.2 and WordPress Coding Standards for escaping and sanitization.

## Files

- Vulnerable: `tests/vulnerable-examples/xss-context-matrix.php`
- Safe: `tests/safe-examples/xss-context-matrix-safe.php`

Existing complementary files:

- Vulnerable: `tests/vulnerable-examples/xss-vulnerable.php`, `tests/vulnerable-examples/xss-taint-vulnerable.php`, `tests/vulnerable-examples/xss-vuln-min.php`
- Safe: `tests/safe-examples/xss-safe.php`, `tests/safe-examples/xss-taint-safe.php`, `tests/safe-examples/xss-safe-min.php`

## Scenario Matrix

- HTML Content
  - Vulnerable: direct echo/print from `$_GET`, `$_POST`, database
  - Safe: `esc_html()` or `wp_kses_post()`

- HTML Attributes (quoted/unquoted)
  - Vulnerable: direct concatenation into attributes
  - Safe: `esc_attr()`

- JavaScript Context (inline script, event handler)
  - Vulnerable: embedding raw input inside `<script>` or `onclick`, etc.
  - Safe: `esc_js()` or `wp_json_encode()` for data values

- URL/HREF/SRC Context
  - Vulnerable: raw user-controlled URLs
  - Safe: `esc_url()` or `esc_url_raw()`

- CSS Inline Style Context
  - Vulnerable: raw values in `style` attribute
  - Safe: `sanitize_hex_color()` where applicable or `esc_attr()` for constrained values

- Data Attributes (e.g., `data-*`)
  - Vulnerable: raw input
  - Safe: `esc_attr()`

- JSON Output / AJAX / REST
  - Vulnerable: `wp_send_json_*` or JSON-encoding with raw input
  - Safe: sanitize first and/or `wp_json_encode()` the sanitized values; escape before HTML embedding

- HTTP Headers
  - Vulnerable: raw header values
  - Safe: validated/sanitized and context-escaped

- Mixed and Flow Patterns (arrays, objects, functions, class properties, loops)
  - Vulnerable: raw flow into sinks
  - Safe: sanitize/escape at sink per context

## WordPress Best Practices Reference

- HTML text: `esc_html()`
- HTML attribute: `esc_attr()`
- JavaScript: `esc_js()` or `wp_json_encode()`
- URLs: `esc_url()` / `esc_url_raw()`
- Content allowlist: `wp_kses_post()` / `wp_kses()`

## How to Run

Using the advanced testing framework:

```bash
python tests/advanced-testing-framework.py --workers 4 --timeout 300
```

Or target XSS taint rules specifically:

```bash
semgrep --config packs/experimental/xss-taint-rules.yaml tests/vulnerable-examples/xss-context-matrix.php
semgrep --config packs/experimental/xss-taint-rules.yaml tests/safe-examples/xss-context-matrix-safe.php
```

## Expected Outcomes

- Vulnerable file should trigger context-appropriate XSS rules.
- Safe file should not trigger XSS rules (false-positive guard).


