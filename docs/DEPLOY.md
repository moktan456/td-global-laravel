# Deploying to cPanel shared hosting

Works on any cPanel host with **PHP 8.3 or newer**, **MySQL** and **SSH** (GoDaddy, HostGator, Bluehost, and similar). It is the same layout the owner's other Laravel site uses on Bluehost: the app lives in a folder **outside** `public_html`, and only the public files are in `public_html`.

```
~/td-global/      the Laravel app (code, vendor/, storage/, .env)
~/public_html/    the web root: index.php (special), .htaccess, build/, photos/, brand/, images/, icons
```

## One-time set-up on the server

1. **PHP version.** cPanel → *MultiPHP Manager* → choose PHP 8.3 or 8.4 for the domain. Note the path to the PHP binary (cPanel's is often `/opt/cpanel/ea-php84/root/usr/bin/php`).
2. **Database.** cPanel → *MySQL Databases*: create a database, a user, and add the user to the database with all privileges. Write down the three names and the password.
3. **Composer.** Make sure `~/bin/composer` exists (cPanel's Terminal: `curl -sS https://getcomposer.org/installer | php -- --install-dir=$HOME/bin --filename=composer`), or ask the host.
4. **Create the folders and the first upload.** Either let the GitHub Action do it (step 6), or by hand: upload the project (without `node_modules`, `vendor`, `.env`) to `~/td-global/`, and the contents of `public/` to `~/public_html/`.
5. **`~/public_html/index.php`.** Copy `deploy/public_html-index.php` over it (once). It points at `~/td-global`. The deploy never overwrites it.
6. **`~/td-global/.env`.** Copy `.env.example` and set at least:

   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=            # php artisan key:generate --show
   APP_URL=https://your-domain
   SITE_URL=https://your-domain
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=...  DB_USERNAME=...  DB_PASSWORD=...
   MAIL_MAILER=smtp  MAIL_HOST=...  MAIL_PORT=...  MAIL_USERNAME=...  MAIL_PASSWORD=...
   MAIL_FROM_ADDRESS="updates@your-domain"
   ```

   Mail options: the host's own mail server, **Brevo** (free, 300 emails a day), or **Resend** (`smtp.resend.com`, port `465`, username `resend`, password = API key). The sender address must be on a domain verified with the provider; without that, real clients' emails are refused or land in spam.
7. **Tables and the first super admin** (cPanel Terminal or SSH):

   ```bash
   cd ~/td-global
   PHP=/opt/cpanel/ea-php84/root/usr/bin/php
   $PHP ~/bin/composer install --no-dev --optimize-autoloader
   $PHP artisan migrate --force
   $PHP artisan config:cache && $PHP artisan view:cache
   $PHP artisan staff:create nima "Nima Moktan" --admin      # asks for a temporary password
   ```

   Sign in at `/staff/login`; you will be asked to choose your own password. From then on add staff under **Team**.

There is **no cron job to set up**: messages are sent right after the page is returned, and nothing is scheduled.

## Automatic deploys (GitHub Actions)

Every push to `main` runs `.github/workflows/deploy.yml`: build the front end, run the 40 tests, upload over SSH, run `composer install`, `migrate` and rebuild the config/view caches. Add these in GitHub → *Settings → Secrets and variables → Actions*:

| Secret | Value |
|---|---|
| `HOST_SSH_KEY` | the private key whose public half is authorised in cPanel → *SSH Access* |
| `HOST_NAME` | the server's SSH host name |
| `HOST_USER` | the cPanel user name |
| `HOST_PHP` *(optional)* | path to PHP 8.3+, if not the cPanel default above |
| `HOST_APP_DIR` *(optional)* | the app folder, if not `td-global` |

If a deploy fails with "Connection closed" or "reset by peer", wait 15–30 minutes and re-run it (some hosts rate-limit SSH after many connections).

## After the first deploy: Telegram (optional)

1. In Telegram, @BotFather → `/newbot`; keep the token private.
2. In `.env`: `TELEGRAM_BOT_TOKEN`, `TELEGRAM_BOT_USERNAME`, `TELEGRAM_WEBHOOK_SECRET` (`openssl rand -hex 24`), then `php artisan config:cache`.
3. Sign in as super admin → **Telegram** → *Connect the bot to this website*. The checklist shows what Telegram reports.
4. Staff alert group (optional): add the bot to a group, send `/chatid`, put the number in `TELEGRAM_STAFF_CHAT_ID`, `php artisan config:cache`.
5. A client connects from **Staff → Clients → Create Telegram link** (send it, e.g. by WhatsApp; it works once and expires in 48 hours).

## Good to know

- **Backups.** Most hosts back up daily; also export the database from cPanel (*phpMyAdmin → Export*) now and then.
- **HTTPS.** cPanel's free AutoSSL; then set `APP_URL` and `SITE_URL` to `https://...`.
- **Updating text.** Edit `resources/content/*.php` and push. Nothing is stored in the database except shipments, clients and staff.
- **Photos too heavy?** Run `npm run photos` after adding photos.
- **Never run the app with `APP_DEBUG=true` on the server.**
