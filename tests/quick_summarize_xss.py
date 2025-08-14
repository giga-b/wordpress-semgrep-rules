#!/usr/bin/env python3
import json
from pathlib import Path
from collections import Counter, defaultdict


def load_results(path: Path):
    raw = path.read_bytes()
    for enc in ('utf-8', 'utf-8-sig', 'utf-16', 'utf-16le', 'utf-16be'):
        try:
            text = raw.decode(enc)
            data = json.loads(text)
            return data.get('results', [])
        except Exception:
            continue
    # Last resort: replace errors and try to parse
    text = raw.decode('utf-8', errors='replace')
    data = json.loads(text)
    return data.get('results', [])


def get_check_id(item: dict) -> str:
    return item.get('check_id') or item.get('extra', {}).get('check_id') or '<unknown>'


def get_location(item: dict) -> str:
    path = item.get('path') or item.get('extra', {}).get('path') or ''
    start = item.get('start', {}) or {}
    line = start.get('line') or item.get('extra', {}).get('start', {}).get('line')
    if line is None:
        return f"{path}"
    return f"{path}:{line}"


def summarize(results):
    ids = [get_check_id(r) for r in results]
    counts = Counter(ids)
    samples = defaultdict(list)
    for r in results:
        cid = get_check_id(r)
        if len(samples[cid]) < 3:
            samples[cid].append(get_location(r))
    return counts, samples


def main():
    base = Path('results/quick-debug')
    vuln_file = base / 'xss-context-matrix-vuln.json'
    safe_file = base / 'xss-context-matrix-safe.json'

    vuln = load_results(vuln_file) if vuln_file.exists() else []
    safe = load_results(safe_file) if safe_file.exists() else []

    v_counts, v_samples = summarize(vuln)
    s_counts, s_samples = summarize(safe)

    print(f"vulnerable findings: {sum(v_counts.values())}")
    print(f"safe findings: {sum(s_counts.values())}")

    print("\nTop vulnerable check_ids:")
    for cid, cnt in v_counts.most_common(15):
        print(f"- {cid}: {cnt}")

    print("\nFalse-positive check_ids (present in safe examples):")
    for cid, cnt in s_counts.most_common():
        print(f"- {cid}: {cnt}")

    # Write Markdown report
    report = [
        "# XSS Context Matrix Semgrep Summary",
        "",
        f"- vulnerable findings: {sum(v_counts.values())}",
        f"- safe findings: {sum(s_counts.values())}",
        "",
        "## Top vulnerable check_ids",
    ]
    for cid, cnt in v_counts.most_common(20):
        report.append(f"- {cid}: {cnt}")
        if v_samples.get(cid):
            for loc in v_samples[cid]:
                report.append(f"  - {loc}")
    report.append("")
    report.append("## False-positive check_ids (safe examples)")
    for cid, cnt in s_counts.most_common():
        report.append(f"- {cid}: {cnt}")
        if s_samples.get(cid):
            for loc in s_samples[cid]:
                report.append(f"  - {loc}")

    out = base / 'xss-context-matrix-report.md'
    out.write_text("\n".join(report), encoding='utf-8')
    print(f"\nReport saved to {out}")


if __name__ == '__main__':
    main()


