# "ThOR" BESS — Backend & Admin Panel

Laravel 12 backend for the "ThOR" BESS website (`../frontend`, Next.js).
It stores every website form submission, emails the sales team, serves blog posts to the site,
and provides a branded admin panel (TailAdmin theme recoloured to the website's navy/green palette).

## What's inside

| Area | Details |
|---|---|
| **Auth (web)** | Sign in (rate-limited, "keep me signed in"), sign out, forgot / reset password by email, optional self-registration, profile + change password (signs out other devices), deactivated users are blocked immediately |
| **Auth (API)** | Sanctum tokens: `POST /api/auth/login`, `GET /api/auth/me`, `POST /api/auth/logout` |
| **Roles** | `admin` (everything, incl. user management) and `editor` (leads + blog) |
| **Leads** | Contact enquiries, datasheet requests and e-Generator quotes in one inbox — filter, search, status, assignee, internal notes, attachment download, CSV export |
| **Blog** | Create / edit / schedule / feature posts with cover image upload; seeded with the 8 articles from `frontend/app/data/blog.ts` |
| **Dashboard** | Lead totals, 14-day trend, split by form and status, recent leads and posts |

## Local setup (XAMPP)

```bash
cd backend
composer install            # vendor/ is already present; run if you pull fresh
cp .env.example .env        # already done locally — .env currently uses SQLite
php artisan key:generate
php artisan migrate --seed  # creates the first admin + imports blog posts
php artisan storage:link
php artisan serve           # http://127.0.0.1:8000/admin
```

**Switching to MySQL:** start MySQL in the XAMPP control panel, create a `thor_bess` database
(utf8mb4_unicode_ci) in phpMyAdmin, then in `.env` set `DB_CONNECTION=mysql` and uncomment the
`DB_HOST` … `DB_PASSWORD` lines, and run `php artisan migrate --seed`.

**First admin:** `db:seed` creates `ADMIN_SEED_EMAIL`. If `ADMIN_SEED_PASSWORD` is empty a random
password is generated and printed once. Change it from *My profile* after the first sign-in.

### Important `.env` keys

| Key | Purpose |
|---|---|
| `FRONTEND_URL` | Website origin(s) allowed by CORS; also used for "View on website" links and seeded blog image URLs |
| `LEADS_NOTIFY_EMAIL` | Who receives new-lead emails (comma-separated). `QUOTE_NOTIFY_EMAIL` / `CONTACT_NOTIFY_EMAIL` override per form |
| `MAIL_MAILER` | `log` (default) writes emails to `storage/logs/laravel.log`; set SMTP/Resend details for production |
| `ADMIN_ALLOW_REGISTRATION` | `true` enables `/admin/register` (new accounts are editors). Keep `false` in production |

## Website API

All responses use the website's shape: `{ "ok": true, ... }` or `{ "ok": false, "error": "message" }`.
Lead endpoints are rate-limited to 5 requests / 10 minutes / IP and honour the hidden `website` honeypot field.

| Method | Endpoint | Body |
|---|---|---|
| POST | `/api/leads/contact` | multipart: `organization*`, `name*`, `role`, `email*`, `phone*`, `dial`, `segment`, `message`, `attachment` (≤ 4 MB: pdf, doc(x), xls(x), png, jpg, dwg, zip) |
| POST | `/api/leads/datasheet` | JSON: `name*`, `company*`, `email*`, `phone*`, `product*` (slug, see `config/thor.php`), `designation`, `location`, `message` |
| POST | `/api/leads/quote` | JSON: `segment*` (light/medium/heavy/unsure), `application`, `capacity`, `power`, `backup`, `sources[]`, `quantity`, `name*`, `company`, `email*`, `phone*`, `location`, `message` |
| GET | `/api/posts?category=&per_page=` | Published posts (paginated, newest first) |
| GET | `/api/posts/{slug}` | One published post including `body` |
| GET | `/api/posts/categories` | Blog categories |

Option lists (products, industries, segments, applications, charging sources, blog categories) live in
`config/thor.php` — keep them in sync with `frontend/app/data/*.ts`.

## How the website uses this backend

```
Visitor ──► Next.js /api/{contact,datasheet,quote}  (validates, rate-limits)
               └─► POST backend /api/leads/*  ──► saved in admin + email to sales
               └─► backend down / not configured? → falls back to the old direct Resend email

Next.js blog pages, home blog section, sitemap ──► GET backend /api/posts   (cached 5 min)
               └─► backend down / not configured? → falls back to frontend/app/data/blog.ts
Admin saves/deletes a post ──► POST website /api/revalidate  (refreshes the cache immediately)
```

Settings that must match on both sides:

| Website (`frontend/.env.local`) | Backend (`backend/.env`) |
|---|---|
| `BACKEND_API_URL=http://127.0.0.1:8000` | `FRONTEND_URL=http://localhost:3000` |
| `REVALIDATE_SECRET=<random>` | `WEBSITE_REVALIDATE_SECRET=<same random>` |
| — | `TRUSTED_PROXIES=<IP of the Next.js server>` (so leads/rate limits use the visitor's IP) |

Frontend files involved: `app/lib/backend.ts` (lead forwarding), `app/lib/blog.ts` (blog data),
`app/api/revalidate/route.ts`, and the pages that call them. Posts created in the admin get their own
`/blog/{slug}` page on first visit — no website rebuild needed.

Run both locally: `php artisan serve` here and `npm run dev` in `../frontend`.

## Theme

- `public/assets/css/style.css` — the original compiled TailAdmin theme (untouched).
- `public/assets/css/thor-admin.css` — brand layer: remaps the theme's colour tokens to the website palette
  (navy `#1e3e55`, green `#5db556`, bolt `#f5c518`, Inter font) and adds the admin components (`t-*` classes).
  Because `style.css` is precompiled, Tailwind utilities that it doesn't already contain must be added to `thor-admin.css`.

## Tests

```bash
php artisan test   # 27 feature tests: auth, password reset, API tokens, lead API, admin pages, roles, website cache refresh
```
