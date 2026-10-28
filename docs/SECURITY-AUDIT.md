# Security audit, 28 October 2026

Scope: the Laravel application (public site, `/staff`, `/admin`, tracking, forms, uploads), its dependencies, and the live temporary site at `website-d3b60c6c.cpi.dmr.mybluehost.me`. Method: code review of every route, controller and view that handles input or shows stored text; exploit attempts in a throw-away test; probes of the live site (files, headers, cookies, rate limits); `composer audit` and `npm audit`. The regression tests are in `tests/Feature/SecurityAuditTest.php`.

## Found and fixed

| # | Severity | Finding | Fix |
|---|---|---|---|
| 1 | High | **Forged `X-Forwarded-For` dodged every rate limit** (staff sign-in 5/min, admin sign-in, tracking lookups, "lost number"). The host connects directly, so the header is written by the visitor; each new value counted as a new visitor, allowing unlimited password guesses. | `bootstrap/app.php`: forwarded-for is never trusted; the visitor is the connection's own address. Confirmed live. |
| 2 | High | **Stored script injection through admin text.** The structured-data (JSON-LD) escape for `<` did nothing, so text such as `</script><script>…` in an FAQ answer, service or article ended the script and ran on every visitor's browser. | `components/json-ld.blade.php` now uses `JSON_HEX_TAG`/`JSON_HEX_AMP`. |
| 3 | Medium | **`javascript:` and `data:` links accepted** in menu, footer, banner button, social, map and credit links, so an admin (or whoever took over an admin session) could plant code that runs when a visitor clicks. | Fields reject unsafe links; the save step has a second check; `safe_url()` neutralises anything already stored when the page is shown; structured-data `sameAs` takes only http(s). |
| 4 | Medium | **Contact and quote forms had no limit**: 40 posts in a second sent 40 emails. Bots could flood the company inbox. | 5 messages per 10 minutes per connection (`td.limits.forms`), a hidden honeypot field, and caps on field length (a 200 KB message used to be emailed whole). |
| 5 | Low | **Photo upload accepted SVG** (which can carry script). It was never served (the media route only serves jpg/png/webp/gif), but it sat in storage. | Upload accepts jpeg, png, webp and gif only. |
| 6 | Low | Changing your own password left your other signed-in devices working. | Other sessions of that account are ended. (An admin's password reset already did this.) |
| 7 | Hardening | No `Content-Security-Policy`. | Added `base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'`. |

## Checked and found sound

- Staff and admin areas: every `/staff` and `/admin` page needs a signed-in, active account; Team and Telegram need a super admin; `/admin` is super admins only; a deactivated account is signed out on its next request; temporary-password accounts cannot reach anything but "choose your password".
- Sign-in: one message for every failure (no username guessing), per-username-and-address throttling, session id renewed on sign-in, cookies `Secure; HttpOnly; SameSite=Lax`, 2-hour lifetime. Passwords are hashed (bcrypt), at least 10 characters.
- CSRF protection on every form; only the Telegram webhook is exempt and it checks its secret with a constant-time comparison.
- Telegram connect links: 256-bit random, stored hashed, one use.
- Tracking: numbers are validated, lookups are rate limited, the lost-number page never says whether a contact matched and is limited per visitor and per contact, and the public page shows only what staff marked visible.
- CSV export neutralises spreadsheet formulas.
- `/media` serves only pictures from `uploads/`, blocks traversal, and sends `nosniff`.
- Output: all text is escaped by Blade; the few raw outputs are fixed markup or escaped first.
- Live site: `/.env`, `.git`, `vendor`, `storage`, `composer.json`, logs and directory listings are all refused or absent; no debug output; no version headers; `/livewire/update` refuses GET.
- Dependencies: `composer audit` and `npm audit` report nothing.

## Not fixed, for your decision

1. **Two-step sign-in (2FA) for super admins.** A stolen password is the biggest remaining risk, because an admin can change the whole site. Filament has built-in authenticator-app support; it needs one small change and a one-time set-up per admin.
2. **A full script policy (CSP).** The pages use inline scripts, so a strict policy needs nonces on each. Worth doing before the real domain goes live.
3. **HTTPS-only header (HSTS).** Still off (`TD_HSTS=false`) until the real domain serves HTTPS.
4. **Email authentication (SPF, DKIM, DMARC)** once the sending domain is verified, so client emails cannot be forged and do not land in spam.
5. **Backups.** No scheduled database backup yet. Edits made in the admin live only in the database.
6. **Account lock-out.** Sign-in is limited per username and address. A deliberately slow attack from many addresses is not stopped. Strong, unique passwords and 2FA are the answer; a hard lock-out would let anyone lock a real user out.
7. **Staff accounts** have no 2FA either. Lower risk, same reasoning as 1.
