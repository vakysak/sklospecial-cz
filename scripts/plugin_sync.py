#!/usr/bin/env python3
"""Push local wp-plugins/poptavkovy-kosik to WordPress via temporary Code Snippet."""

from __future__ import annotations

import base64
import json
import mimetypes
import os
import ssl
import sys
import time
import urllib.error
import urllib.request
from pathlib import Path

WP = os.environ.get("SKLO_WP_URL", "https://sklospecial.eu").rstrip("/")
USER = os.environ.get("SKLO_WP_USER", "vakysak")
PASS = os.environ.get("SKLO_WP_APP_PASSWORD", "")
if not PASS:
    raise SystemExit("Set SKLO_WP_APP_PASSWORD (WP application password)")
AUTH = base64.b64encode(f"{USER}:{PASS}".encode()).decode()
CTX = ssl.create_default_context()

PLUGIN_SLUG = "poptavkovy-kosik"
PLUGIN_DIR = Path(__file__).resolve().parents[1] / "wp-plugins" / PLUGIN_SLUG
PLUGIN_FILE = f"{PLUGIN_SLUG}/{PLUGIN_SLUG}.php"
SNIPPET_NAME = "Sklospecial plugin sync"

BINARY_SUFFIXES = {
    ".png",
    ".jpg",
    ".jpeg",
    ".gif",
    ".webp",
    ".ico",
    ".woff",
    ".woff2",
    ".ttf",
    ".otf",
    ".eot",
    ".pdf",
    ".zip",
}

SNIPPET_CODE = r"""
add_action('rest_api_init', function () {
    register_rest_route('sklo/v1', '/plugin-sync', [
        'methods' => 'POST',
        'permission_callback' => function () {
            return current_user_can('activate_plugins');
        },
        'callback' => function ($req) {
            $slug = sanitize_key((string) $req->get_param('slug'));
            $files = $req->get_param('files');
            $activate = (bool) $req->get_param('activate');
            if ($slug === '' || !is_array($files) || !$files) {
                return new WP_Error('bad_request', 'slug and files required', ['status' => 400]);
            }
            if (str_contains($slug, '..') || str_contains($slug, '/') || str_contains($slug, '\\')) {
                return new WP_Error('bad_slug', 'invalid slug', ['status' => 400]);
            }
            $dest = WP_PLUGIN_DIR . '/' . $slug;
            if (!is_dir($dest) && !wp_mkdir_p($dest)) {
                return new WP_Error('mkdir', 'cannot create plugin dir', ['status' => 500]);
            }
            $written = [];
            foreach ($files as $rel => $content) {
                $rel = ltrim(str_replace(['\\'], ['/'], (string) $rel), '/');
                if ($rel === '' || str_contains($rel, '..')) {
                    continue;
                }
                $path = $dest . '/' . $rel;
                $dir = dirname($path);
                if (!is_dir($dir) && !wp_mkdir_p($dir)) {
                    return new WP_Error('mkdir_file', 'cannot create ' . $dir, ['status' => 500]);
                }
                if (is_array($content) && ($content['encoding'] ?? '') === 'base64') {
                    $bytes = base64_decode((string) ($content['data'] ?? ''), true);
                    if ($bytes === false) {
                        return new WP_Error('b64', 'bad base64 for ' . $rel, ['status' => 400]);
                    }
                    $ok = file_put_contents($path, $bytes);
                } else {
                    $ok = file_put_contents($path, (string) $content);
                }
                if ($ok === false) {
                    return new WP_Error('write', 'cannot write ' . $rel, ['status' => 500]);
                }
                $written[] = $rel;
            }
            $plugin_file = $slug . '/' . $slug . '.php';
            $activated = false;
            if ($activate) {
                if (!function_exists('activate_plugin')) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }
                $result = activate_plugin($plugin_file, '', false, true);
                if (is_wp_error($result)) {
                    return new WP_Error(
                        'activate_fail',
                        $result->get_error_message(),
                        ['status' => 500, 'written' => $written]
                    );
                }
                $activated = true;
            }
            $active = (array) get_option('active_plugins', []);
            return [
                'ok' => true,
                'written' => $written,
                'activated' => $activated,
                'plugin_active' => in_array($plugin_file, $active, true),
                'plugin_file' => $plugin_file,
            ];
        },
    ]);
});
""".strip()


def req(method: str, path: str, data=None, timeout: int = 180):
    body = None if data is None else json.dumps(data).encode()
    r = urllib.request.Request(
        WP + path,
        data=body,
        method=method,
        headers={
            "Authorization": f"Basic {AUTH}",
            "Content-Type": "application/json",
            "Accept": "application/json",
        },
    )
    try:
        with urllib.request.urlopen(r, context=CTX, timeout=timeout) as resp:
            raw = resp.read().decode()
            return json.loads(raw) if raw else {}
    except urllib.error.HTTPError as e:
        err = e.read().decode(errors="ignore")
        raise RuntimeError(f"{method} {path} -> {e.code}: {err[:800]}") from e


def upsert_snippet(active: bool) -> int:
    snips = req("GET", "/wp-json/code-snippets/v1/snippets")
    sync = next((s for s in snips if s.get("name") == SNIPPET_NAME), None)
    payload = {
        "name": SNIPPET_NAME,
        "desc": "Temporary REST writer for plugin deploy (keep inactive).",
        "code": SNIPPET_CODE,
        "scope": "global",
        "priority": 5,
        "active": active,
    }
    if sync:
        req("PUT", f"/wp-json/code-snippets/v1/snippets/{sync['id']}", payload)
        return int(sync["id"])
    created = req("POST", "/wp-json/code-snippets/v1/snippets", payload)
    return int(created["id"])


def set_snippet_active(snippet_id: int, active: bool):
    snips = req("GET", "/wp-json/code-snippets/v1/snippets")
    sync = next(s for s in snips if int(s["id"]) == int(snippet_id))
    req(
        "PUT",
        f"/wp-json/code-snippets/v1/snippets/{snippet_id}",
        {
            "name": sync["name"],
            "desc": sync.get("desc") or "",
            "code": SNIPPET_CODE,
            "scope": sync.get("scope") or "global",
            "priority": sync.get("priority") or 5,
            "active": active,
        },
    )


def collect_files() -> dict:
    files: dict = {}
    for p in PLUGIN_DIR.rglob("*"):
        if not p.is_file() or p.name == ".DS_Store":
            continue
        rel = str(p.relative_to(PLUGIN_DIR)).replace("\\", "/")
        suffix = p.suffix.lower()
        raw = p.read_bytes()
        if suffix in BINARY_SUFFIXES:
            files[rel] = {
                "encoding": "base64",
                "data": base64.b64encode(raw).decode("ascii"),
                "mime": mimetypes.guess_type(p.name)[0] or "application/octet-stream",
            }
            print("binary", rel, len(raw))
        else:
            try:
                files[rel] = raw.decode("utf-8")
            except UnicodeDecodeError:
                files[rel] = {
                    "encoding": "base64",
                    "data": base64.b64encode(raw).decode("ascii"),
                    "mime": "application/octet-stream",
                }
                print("binary(fallback)", rel, len(raw))
    return files


def main():
    if not PLUGIN_DIR.is_dir():
        raise SystemExit(f"Missing plugin dir: {PLUGIN_DIR}")

    files = collect_files()
    print(f"files={len(files)} target={WP} slug={PLUGIN_SLUG}")

    snippet_id = upsert_snippet(active=True)
    print(f"snippet_id={snippet_id} active")
    time.sleep(0.8)
    try:
        out = req(
            "POST",
            "/wp-json/sklo/v1/plugin-sync",
            {"slug": PLUGIN_SLUG, "files": files, "activate": True},
            timeout=300,
        )
        written = out.get("written") or []
        print(
            f"written={len(written)} activated={out.get('activated')} "
            f"plugin_active={out.get('plugin_active')} file={out.get('plugin_file')}"
        )
        if not out.get("ok"):
            print(json.dumps(out, ensure_ascii=False)[:2000])
            sys.exit(1)
        if not out.get("plugin_active"):
            print("WARN: files written but plugin not listed as active")
            sys.exit(2)
    finally:
        set_snippet_active(snippet_id, False)
        print("plugin-sync snippet off")


if __name__ == "__main__":
    main()
