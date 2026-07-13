# Island Central Mactan CMS

The system contains two separate applications:

- `backend/` - Laravel 12 REST API, MySQL database, authentication, OTP, and CMS operations.
- `frontend/` - React + Vite public website and admin panel.

## Run locally

Backend:

```powershell
cd backend
composer install
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Frontend, in another terminal:

```powershell
cd frontend
npm install
npm run dev
```

Open `http://localhost:5173`. The admin portal is at `http://localhost:5173/admin`.

## Administrator authentication

Administrator credentials are read from `ADMIN_NAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` in `backend/.env`; they are not stored in source control. Authentication requires the password followed by a six-digit, single-use email code. Codes expire after 10 minutes, allow at most five attempts, and may only be resent after a cooldown. Successful logins create an expiring, independently revocable administrator session.

### Gmail OTP setup

Gmail does not accept a normal account password for SMTP. Enable two-step verification on the Google account, create a Google App Password, and configure `backend/.env`:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=sample@gmail.com
MAIL_PASSWORD=your-16-character-google-app-password
MAIL_FROM_ADDRESS=sample@gmail.com
MAIL_FROM_NAME="Island Central Mactan"
```

Then run:

```powershell
php artisan config:clear
```

Run the queue worker so OTP emails are delivered promptly:

```powershell
php artisan queue:work --tries=3
```

Never commit the Google App Password or `backend/.env`.

## API overview

- `GET /api/content/{resource}` - published public content.
- `GET /api/content/settings` - public site settings.
- `POST /api/inquiries` - validated public inquiries.
- `POST /api/admin/login` - password validation and OTP delivery.
- `POST /api/admin/verify-otp` - OTP verification and token issuance.
- `POST /api/admin/resend-otp` - rate-limited OTP replacement.
- `/api/admin/media` - protected image library and uploads.
- `/api/admin/{resource}` - protected CMS operations.

Tenant seed data is maintained in `backend/database/seeders/data/tenants.json`.

## Content and media management

Page hero titles, descriptions, background images, buttons, About Us content, footer text, social links, map URLs, and SEO defaults are stored in `site_settings`. They can be edited under **Admin → Settings**. Upload images under **Admin → Media library**, add useful alternative text, copy the resulting URL, and use it in the relevant setting or content record.

The public site uses WebP versions of large photographs. Keep uploaded images below 8 MB and prefer WebP or AVIF for new photography.

## Scheduled maintenance and backups

Laravel removes expired OTP and administrator-session records every day. Database backups can also run daily. Keep the scheduler running in production:

```powershell
php artisan schedule:work
```

Configure backups in `backend/.env`:

```env
DB_BACKUP_ENABLED=true
DB_BACKUP_RETENTION_DAYS=14
MYSQLDUMP_PATH=C:\path\to\mysqldump.exe
```

Backups are private files under `backend/storage/app/private/backups`. Copy them to encrypted off-site storage as part of the server backup policy and regularly test restoring one.

## Production checklist

- Set `APP_ENV=production`, `APP_DEBUG=false`, and the public HTTPS `APP_URL`.
- Set frontend `VITE_SITE_URL` to the final HTTPS website URL before building; the build generates `sitemap.xml` and `robots.txt` automatically.
- Set a long, unique administrator password and rotate the current development password.
- Restrict `FRONTEND_URLS` to the real HTTPS frontend origin.
- Use a dedicated MySQL user with access only to this database.
- Run `php artisan optimize`, `npm run build`, a queue worker, and the scheduler.
- Configure HTTPS, response compression, security headers, and long-lived caching for versioned assets at the web server or CDN.
- Set up database/off-site backups, uptime checks for `/up`, and application error monitoring.
- Update canonical URLs and submit the deployed sitemap through the search-engine webmaster tools after the final domain is known.
