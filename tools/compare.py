#!/usr/bin/env python3
"""
Compares a page of the original Next.js site with the same page of this Laravel clone, at the markup level.

  python3 tools/compare.py /services/import-clearance
  python3 tools/compare.py / --a https://td-trading-global.vercel.app --b http://127.0.0.1:8000

Both HTML documents are reduced to the same list of lines (tag, id, classes, the attributes that matter for
behaviour and accessibility, and the visible text). Framework noise is ignored: scripts, preload links, Next's
image URLs and srcset, React comment markers. Anything left that differs is a real difference.
"""
import argparse, difflib, re, sys, urllib.error, urllib.request
from html.parser import HTMLParser

SKIP_TAGS = {"script", "style", "link", "meta", "noscript", "template", "title", "head", "svg_path"}
VOID = {"area", "base", "br", "col", "embed", "hr", "img", "input", "link", "meta", "source", "track", "wbr"}
KEEP_ATTRS = {"id", "href", "role", "type", "name", "for", "alt", "inert", "tabindex", "target", "rel", "action", "method",
              "autocomplete", "inputmode", "placeholder", "required", "disabled", "hidden", "value", "rows", "title",
              "loading", "decoding", "fetchpriority", "sizes", "autocapitalize", "spellcheck", "novalidate", "lang", "viewbox"}
IGNORE_ATTR_PREFIX = ("data-nimg", "data-next", "data-reveal-", "data-slide", "data-dot", "data-bar", "data-live", "data-label", "data-icon", "data-submit",
                      "data-enhance", "data-track", "data-scroll", "data-prev", "data-next", "data-counter", "data-hero", "data-service", "data-article",
                      "data-company", "data-collapsible", "data-search", "data-mobile", "data-site", "data-password", "data-chevron", "data-form")


def fetch(url):
    req = urllib.request.Request(url, headers={"User-Agent": "td-compare/1.0"})
    try:
        with urllib.request.urlopen(req, timeout=30) as r:
            return r.read().decode("utf-8", "replace")
    except urllib.error.HTTPError as e:  # 404 pages are pages too
        return e.read().decode("utf-8", "replace")


class Flatten(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.lines, self.depth, self.skip, self.stack, self.text = [], 0, 0, [], []

    def flush_text(self):
        t = re.sub(r"\s+", " ", "".join(self.text)).strip()
        self.text = []
        if t and not self.skip:
            self.lines.append(f"“{t}”")

    def handle_starttag(self, tag, attrs):
        self.flush_text()
        if self.skip or tag in SKIP_TAGS:
            if tag not in VOID:
                self.skip += 1
                self.stack.append((tag, True))
            return
        a = dict(attrs)
        cls = " ".join(sorted(c for c in (a.pop("class", "") or "").split() if not c.startswith("lucide-")))
        bits = []
        for k in sorted(a):
            kl = k.lower()
            if kl.startswith(IGNORE_ATTR_PREFIX):
                continue
            if kl.startswith("aria-") or kl in KEEP_ATTRS:
                v = a[k]
                if kl in ("sizes",):
                    continue
                if kl in ("id", "for", "aria-controls", "aria-describedby", "aria-labelledby"):
                    v = "ID"  # generated ids differ (React useId vs fixed ids); relationships are checked by hand
                if kl in ("loading", "decoding", "fetchpriority"):
                    continue
                if kl == "href":
                    v = re.sub(r"^https?://[^/]+", "", v or "")
                bits.append(f"{kl}={v}" if v not in (None, "") else kl)
        line = f"<{tag}" + (f" .{cls.replace(' ', '.')}" if cls else "") + (" [" + " ".join(bits) + "]" if bits else "") + ">"
        self.lines.append(line)
        if tag not in VOID:
            self.stack.append((tag, False))
            self.depth += 1

    def handle_endtag(self, tag):
        self.flush_text()
        while self.stack:
            t, skipped = self.stack.pop()
            if skipped:
                self.skip -= 1
            else:
                self.depth -= 1
            if t == tag:
                break

    def handle_startendtag(self, tag, attrs):
        # <img ... /> and <path ... />: open, and close unless the tag is void (void tags never push a level).
        self.handle_starttag(tag, attrs)
        if tag not in VOID:
            self.handle_endtag(tag)

    def handle_data(self, data):
        if not self.skip:
            self.text.append(data)


def normalise(html, body_only=True):
    m = re.search(r"<body[^>]*>(.*)</body>", html, re.S | re.I)
    inner = m.group(1) if m else html
    inner = re.sub(r"<!--.*?-->", "", inner, flags=re.S)
    inner = re.sub(r"<next-route-announcer.*?</next-route-announcer>", "", inner, flags=re.S)
    p = Flatten()
    p.feed(inner)
    p.flush_text()
    # Next.js streaming placeholders: an empty hidden <div> that carries no content.
    drop = {"<div [hidden]>", "<span>", "<span [hidden]>"}  # Next.js placeholders and the icon-swap wrappers used by the scripts
    return [l for l in p.lines if l.strip() not in drop]


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("path")
    ap.add_argument("--a", default="https://td-trading-global.vercel.app", help="original Next.js site")
    ap.add_argument("--b", default="http://127.0.0.1:8099", help="Laravel clone")
    ap.add_argument("--context", type=int, default=2)
    ap.add_argument("--max", type=int, default=120)
    args = ap.parse_args()
    a = normalise(fetch(args.a.rstrip("/") + args.path))
    b = normalise(fetch(args.b.rstrip("/") + args.path))
    diff = list(difflib.unified_diff(a, b, "next.js", "laravel", n=args.context, lineterm=""))
    same = sum(1 for _ in difflib.SequenceMatcher(None, a, b).get_matching_blocks())
    ratio = difflib.SequenceMatcher(None, a, b, autojunk=False).ratio()
    print(f"{args.path}: {len(a)} vs {len(b)} lines, similarity {ratio:.1%}")
    if not diff:
        print("IDENTICAL")
        return
    for line in diff[: args.max]:
        print(line)
    if len(diff) > args.max:
        print(f"... {len(diff) - args.max} more diff lines")


if __name__ == "__main__":
    main()
