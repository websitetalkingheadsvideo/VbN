# Windows Setup Checklist (No WSL, GUI‑First)

Use this to get a clean, repeatable setup. Check off as you go.

Progress (last updated by Codex)
- Completed: Node.js + npm verified, PHP 8.3 CLI in PATH, Composer installed, Git installed, sanitized MCP config.
- Next: Verify database, optionally set PowerShell 7 as default.

## 0) Quick Status
- [ ] PowerShell 7 (pwsh) opens by default
- [x] Node.js + npm installed (already OK)
- [x] PHP 8.3 CLI in PATH (C:\php\8.3.0\php.exe)
- [x] Composer installed
- [x] Git installed (already OK)
- [ ] Database choice verified (MySQL/MariaDB or SQLite)
- [x] MCP/IDE secrets safe (no keys stored in repo)

---

## 1) PowerShell 7 (Verify + Make Default)

Open PowerShell 7
- Start menu → type “PowerShell 7 (x64)” → Enter
- Or Win+R → `pwsh` → Enter
- Verify: `$PSVersionTable.PSVersion` (Major ≥ 7)

Make PowerShell 7 default (Windows Terminal)
- Open Windows Terminal → Ctrl+, → Startup → Default profile = “PowerShell”
- Profiles → “PowerShell” → Command line = `C:\Program Files\PowerShell\7\pwsh.exe`
- Save, close, reopen Windows Terminal → `$PSVersionTable.PSVersion` shows 7.x

Create a direct shortcut (optional)
- Right‑click desktop → New → Shortcut → Target: `C:\Program Files\PowerShell\7\pwsh.exe`
- Name it “PowerShell 7” → Pin to taskbar if you want

---

## 2) Node.js + npm (Verify)
- In PowerShell 7:
  - `node -v` (you reported v24.9.0)
  - `npm -v` (you reported 11.6.0)
- If missing: download LTS Windows x64 Installer from https://nodejs.org/en

---

## 3) PHP 8.3 CLI (Install + PATH)

Install PHP 8.3 (CLI only, no server)
- Create folder: `C:\php\8.3.0`
- Download zip: go to https://windows.php.net/download/ → pick 64‑bit “Non Thread Safe (NTS)” for 8.3.x (VS16)
  - If you prefer a direct example: https://windows.php.net/downloads/releases/archives/php-8.3.0-nts-Win32-vs16-x64.zip (archives don’t change, but versions age)
- Extract the zip contents into `C:\php\8.3.0` so that `C:\php\8.3.0\php.exe` exists

Add to PATH (permanent)
- Win+R → `SystemPropertiesAdvanced` → Enter → “Environment Variables…”
- Bottom list “System variables” → select `Path` → “Edit…”
- Click “New” → add exactly: `C:\php\8.3.0`
- If `C:\xampp\php` is present, use “Move Up” so `C:\php\8.3.0` is above it
- OK all dialogs

Turn off Store alias that can hijack “php”
- Settings → Apps → Advanced app settings → App execution aliases
- Find “php” → toggle Off

Verify (open a NEW PowerShell 7 window)
- `php -v` → should show “PHP 8.3.x (cli)”
- `Get-Command php | Select-Object -Expand Source` → `C:\php\8.3.0\php.exe`

Troubleshooting “php not recognized”
- In any pwsh window: `& 'C:\php\8.3.0\php.exe' -v` (proves PHP works)
- If that works but `php -v` fails:
  - Recheck PATH entry is exactly `C:\php\8.3.0` (not “php C:\php\8.3.0”)
  - Make sure `C:\php\8.3.0` is above `C:\xampp\php`
  - Close all terminals; reopen PowerShell 7 only
  - Recheck “App execution aliases” for php is Off
  - Confirm actual exe: `Get-Command php`

---

## 4) Composer (PHP dependency manager)

Install (GUI)
- Download Windows installer: https://getcomposer.org/Composer-Setup.exe
- Run it; when it asks for PHP, browse to `C:\php\8.3.0\php.exe`
- Accept defaults

Verify
- Open a NEW PowerShell 7 → `composer --version`
- If not found, add Composer’s bin to PATH (installer usually does this):
  - User PATH typically adds: `C:\Users\<you>\AppData\Local\ComposerSetup\bin` (or similar)

---

## 5) Git
- You said it’s installed and used daily — we’re good.

GUI installers (if needed)
- Git for Windows: https://git-scm.com/download/win

---

## 6) Database (pick one)

MySQL/MariaDB via XAMPP or Laragon (GUI‑friendly)
- XAMPP: https://www.apachefriends.org/download.html
- Laragon: https://laragon.org/download/
- Start the DB service from their Control Panel
- Verify service in PowerShell 7:
  - `Get-Service | Where-Object { $_.Name -match 'mysql|mariadb' } | Select Name, Status`
  - `mysql --version` (if client installed)

SQLite (no service)
- Install SQLite tools: https://www.sqlite.org/download.html (sqlite-tools-win-x64)
- Verify: `sqlite3 -version`

---

## 7) MCP/IDE Secrets Hygiene

Do NOT store API keys in your repo
- File to sanitize: `.cursor/mcp.json`
- Replace any hardcoded keys with environment variables
  - Set env vars (permanent): Win+R → `SystemPropertiesAdvanced` → Environment Variables…
  - Add “User variables” like `OPENAI_API_KEY`, `ANTHROPIC_API_KEY`, etc.
- In `mcp.json`, refer to them without values (your IDE will read from environment)

---

## 8) Run the PHP App (if applicable)

Quick local server (CLI)
- From your project’s web root:
  - `php -S localhost:8000`
  - If your app needs a document root, use: `php -S localhost:8000 -t public`

Open in browser
- http://localhost:8000

---

## 9) Common Fixes (Quick Reference)

PowerShell 5 opens instead of 7
- Start menu → “PowerShell 7 (x64)”
- Or Win+R → `pwsh`
- Windows Terminal → Settings → Startup → Default profile = PowerShell → Profiles → “PowerShell” → Command line = `C:\Program Files\PowerShell\7\pwsh.exe`

“php” not recognized, but full path works
- PATH must contain `C:\php\8.3.0` (exact)
- `C:\php\8.3.0` must appear before `C:\xampp\php` in PATH
- App execution aliases → “php” Off
- New terminal window after changes

winget not available
- Use the GUI installers linked above (Node, PHP, Composer)
- Or install winget via Microsoft Store (“App Installer”)

32‑bit vs 64‑bit
- Use x64 packages only (Node, PHP, Composer, Git)
- PHP zip name includes “x64” and “nts” (non‑thread‑safe) for CLI

---

## 10) After Reboot Checklist
- [ ] Open PowerShell 7 (`pwsh`), not Windows PowerShell
- [ ] `php -v` shows 8.3.x (source = `C:\php\8.3.0\php.exe`)
- [ ] `composer --version` works
- [ ] `node -v` and `npm -v` work
- [ ] DB is installed and reachable (if needed)
- [ ] No API keys in `.cursor/mcp.json`; env vars set
