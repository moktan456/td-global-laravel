# TD Global Clearing & Logistics — Laravel version

A complete rebuild of the Next.js site ([`Edulink-Trading-Website`](https://github.com/moktan456/Edulink-Trading-Website)) in **Laravel 13**, made to run on cheap **cPanel shared hosting** (PHP + MySQL) with **no monthly fees beyond the hosting and the domain**. It has the same pages, the same design, the same animations, and the same tracking application.

- **Stack:** Laravel 13, Blade, Tailwind CSS 4 (the very same stylesheet), Vite, a little vanilla JavaScript. SQLite locally, MySQL on the server.
- **No separate services:** no Vercel, no Supabase. Database, sessions, rate limiting and email all run on the hosting account.
- **Tests:** `php artisan test` (40 tests).

## What it does

**Public site:** home, services (and a page per service), pricing, cost calculator, quote and contact forms, about (with certificates viewer), blog, the Gelephu landing page, search, photo credits, a 404 page, `robots.txt` and `sitemap.xml`.

**Tracking:**
- `/track` — the **tracking number alone** shows the stages, dates and the notes staff chose to show. Nothing about the client. Rate limited per visitor.
- "Lost your tracking number?" — enter a phone or email; the tracking numbers are sent to the email (and Telegram) already on file, and the page never says whether anyone matched.
- Tracking numbers look like `TD-2610-K7QX4M` (older 4-character numbers still work). Email and Telegram links open `/track?n=<number>`.

**Staff (`/staff`):**
- Sign in with **username + password** (temporary password → must choose your own at first sign-in).
- Dashboard with filters, new request, status updates with an optional note and a "notify the client" tick, a timeline, and a **notification log** (sent / failed / skipped, never hidden).
- Clients: register, and create a one-time **Telegram connect link**.
- **Super admin only:** *Team* (add staff, reset passwords, switch people off; at least one super admin always remains) and *Telegram* (checklist and one-click webhook set-up).

**Notifications:** email (plain text) and Telegram to clients, per client preference; Telegram bot commands `/start`, `/status`, `/stop`, `/chatid`.

## Run it on your computer

Needs PHP 8.3+, Composer and Node 20+.

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate
npm install && npm run build        # or: npm run dev
php artisan staff:create nima "Nima Moktan" --admin   # first super admin (asks for a temporary password)
php artisan serve                   # http://localhost:8000
php artisan staff:reset-password nima   # forgotten password (asks for a temporary one)
php artisan test
```

## Put it on the internet

See **[docs/DEPLOY.md](docs/DEPLOY.md)** (cPanel shared hosting, step by step, including the GitHub Actions auto-deploy).

## Where things are

| To change… | Edit |
|---|---|
| Company details, phone, email, services, plans, team, FAQs, articles, calculator rates, photos' alt text | `resources/content/*.php` (plain PHP arrays; `[IN BRACKETS]` values are placeholders and hide themselves) |
| Colours, spacing, motion timings | `resources/css/app.css` (`@theme`) |
| Page layouts | `resources/views` (`components/home/*` are the home sections) |
| Header menus, carousels, scroll reveal, calculator, forms | `resources/js` |
| Tracking logic | `app/Http/Controllers/TrackController.php`, `app/Services/Notify.php`, `app/Support/Messages.php` |
| Staff screens | `app/Http/Controllers/Staff`, `resources/views/staff` |
| Telegram bot | `app/Http/Controllers/TelegramWebhookController.php`, `app/Services/Telegram.php` |
| The 8 stages | `resources/content/statuses.php` |

**Photos:** put a new JPG in `public/photos`, add it to `resources/content/images.php`, run `npm run photos` (makes the resized WebP copies), commit the result.

**Checking the clone against the original:** `python3 tools/compare.py /pricing` compares a page's markup with the live Next.js site (`--a` and `--b` set the two addresses).

## Environment settings

All in `.env` (see `.env.example`): database, mail (`MAIL_*`), `SITE_URL`, `FORM_WEBHOOK_URL` (optional), `TELEGRAM_*`, search-engine verification codes.

## What is not in this version

See [docs/DIFFERENCES.md](docs/DIFFERENCES.md).
