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

**Transport tracking (optional per shipment):** besides the customs stages, a shipment can also follow a six-stage transport track (pickup to delivery), with carrier and vehicle details, an expected delivery date and a proof of delivery. Staff switch each track on or off; clients see a progress line per track.

**Staff workspace:** a sidebar workspace (Shipments, New request, Clients, and for super admins Team and Telegram). Find a shipment from anywhere (press `/`, type a tracking number or just its 6-character code), search, sort and filter by stage, change a shipment's stage straight from the list, export CSV, assign shipments to team members, and switch to compact rows. A light or dark theme follows the device and can be changed in the account menu. On phones the filters fold away and shipment pages have a jump bar.

**Website admin (`/admin`, super admins only):** a Filament panel for editing the public site without touching code: company and contact details, menus and footer links, the home page (banner, section headings, why-us, team, testimonials, partners, badges), pricing packs and calculator rates, the wording of every page (About, Services, Pricing, Contact, Quote, Blog, Clearing agent, service pages and the footer, in *Page text*), services, blog articles, FAQs, the "how it works" steps, and every photo (upload your own). Sign in with the same username and password as `/staff`; other staff get a 403. Edits are saved in the `content_overrides` table on top of the defaults in `resources/content`, each page has "Back to the original text", and the tracking system is not part of it.

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
| Company details, phone, email, services, plans, team, FAQs, articles, calculator rates, photos | **`/admin`** (super admins). The shipped defaults are in `resources/content/*.php`; `[IN BRACKETS]` values are placeholders and hide themselves. Admin pages: `app/Filament/Pages`; how edits are layered: `app/Support/Content.php` |
| Colours, spacing, motion timings | `resources/css/app.css` (`@theme`) |
| Page layouts | `resources/views` (`components/home/*` are the home sections) |
| Header menus, carousels, scroll reveal, calculator, forms | `resources/js` |
| Tracking logic | `app/Http/Controllers/TrackController.php`, `app/Services/Notify.php`, `app/Support/Messages.php` |
| Staff screens | `app/Http/Controllers/Staff`, `resources/views/staff` |
| Telegram bot | `app/Http/Controllers/TelegramWebhookController.php`, `app/Services/Telegram.php` |
| The 7 clearance stages (labels, the wording clients see, their order) | `resources/content/statuses.php` |

**Photos:** in the admin, *Photos* → upload your own (stored in `storage/app/public/uploads`, served at `/media/uploads/...`, kept across deploys). To add a library photo instead: put a new JPG in `public/photos`, add it to `resources/content/images.php`, run `npm run photos` (makes the resized WebP copies), commit the result.

**Checking the clone against the original:** `python3 tools/compare.py /pricing` compares a page's markup with the live Next.js site (`--a` and `--b` set the two addresses).

## Environment settings

All in `.env` (see `.env.example`): database, mail (`MAIL_*`), `SITE_URL`, `FORM_WEBHOOK_URL` (optional), `TELEGRAM_*`, search-engine verification codes, and `TD_HSTS` (set to `true` once the real domain serves HTTPS). Every page is sent with clickjacking, content-type and referrer protections (`app/Http/Middleware/SecurityHeaders.php`).

## What is not in this version

See [docs/DIFFERENCES.md](docs/DIFFERENCES.md).
