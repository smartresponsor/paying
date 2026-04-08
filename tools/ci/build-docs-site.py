#!/usr/bin/env python3
from __future__ import annotations

from html import escape
from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[2]
DOCS = ROOT / "docs"
SITE = DOCS / "site"
PAGES = [
    DOCS / "index.md",
    DOCS / "INSTALL.md",
    DOCS / "ARCHITECTURE.md",
    DOCS / "OPERATIONS.md",
    DOCS / "API.md",
    DOCS / "LIMITS.md",
    DOCS / "PROOF_PACK.md",
    DOCS / "release" / "runtime-baseline.md",
]


def render_markdown(text: str) -> str:
    lines = text.splitlines()
    html: list[str] = []
    in_list = False
    in_code = False
    code_lines: list[str] = []

    def close_list() -> None:
        nonlocal in_list
        if in_list:
            html.append("</ul>")
            in_list = False

    def close_code() -> None:
        nonlocal in_code, code_lines
        if in_code:
            html.append("<pre><code>{}</code></pre>".format(escape("\n".join(code_lines))))
            in_code = False
            code_lines = []

    for raw in lines:
        line = raw.rstrip("\n")
        if line.strip().startswith("```"):
            close_list()
            if in_code:
                close_code()
            else:
                in_code = True
            continue
        if in_code:
            code_lines.append(line)
            continue
        if not line.strip():
            close_list()
            html.append("")
            continue
        if line.startswith("### "):
            close_list()
            html.append(f"<h3>{escape(line[4:])}</h3>")
            continue
        if line.startswith("## "):
            close_list()
            html.append(f"<h2>{escape(line[3:])}</h2>")
            continue
        if line.startswith("# "):
            close_list()
            html.append(f"<h1>{escape(line[2:])}</h1>")
            continue
        if re.match(r"^\s*[-*] ", line):
            if not in_list:
                html.append("<ul>")
                in_list = True
            item = re.sub(r"^\s*[-*] ", "", line)
            html.append(f"<li>{escape(item)}</li>")
            continue
        close_list()
        html.append(f"<p>{escape(line)}</p>")

    close_list()
    close_code()
    return "\n".join(part for part in html if part != "")


def build_page(source: Path) -> None:
    rel = source.relative_to(DOCS)
    target = SITE / rel.with_suffix(".html")
    target.parent.mkdir(parents=True, exist_ok=True)
    body = render_markdown(source.read_text(encoding="utf-8"))
    title = source.stem.replace("-", " ").replace("_", " ").title()
    nav = "".join(
        f'<li><a href="{page.relative_to(DOCS).with_suffix(".html")}">{page.stem}</a></li>'
        for page in PAGES
    )
    target.write_text(
        """<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{title}</title>
  <style>
    body {{ font-family: Arial, sans-serif; margin: 0; background: #f6f8fa; color: #1f2328; }}
    .layout {{ max-width: 1100px; margin: 0 auto; padding: 24px; }}
    nav {{ background: #fff; border: 1px solid #d0d7de; border-radius: 12px; padding: 16px; margin-bottom: 24px; }}
    nav ul {{ margin: 0; padding-left: 20px; }}
    article {{ background: #fff; border: 1px solid #d0d7de; border-radius: 12px; padding: 24px; }}
    pre {{ overflow-x: auto; background: #f6f8fa; padding: 12px; border-radius: 8px; }}
    code {{ font-family: Consolas, monospace; }}
    a {{ color: #0969da; }}
  </style>
</head>
<body>
  <div class="layout">
    <nav>
      <strong>Payment docs</strong>
      <ul>{nav}</ul>
    </nav>
    <article>{body}</article>
  </div>
</body>
</html>
""".format(title=escape(title), nav=nav, body=body),
        encoding="utf-8",
    )


def main() -> None:
    SITE.mkdir(parents=True, exist_ok=True)
    for page in PAGES:
        if page.exists():
            build_page(page)


if __name__ == "__main__":
    main()
