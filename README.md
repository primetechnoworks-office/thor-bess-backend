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
| **Blog** | Create / edit / schedule / feature posts with cover image upload and a CKEditor article body (headings, highlight box, links, lists, quotes, images, tables); web address generated from the title; seeded with the 8 articles from `frontend/app/data/blog.ts` |
| **Products** | LFP battery catalogue: name, series, sizes & prices, specs, "best for", photos (upload, reorder, cover), show/hide, order. Feeds the datasheet form too |
| **Testimonials** | Client quotes for the website slider, with photo, show/hide and order (flags leftover placeholder text) |
| **Team** | Leadership team on the About page: name, role, portrait, LinkedIn, show/hide, order |
| **Milestones** | Grants & work orders on the About page: tag, title, text, badge, note, status label, photo |
| **FAQs** | Questions & answers per website page (today: the e-Generator page) |
| **Partners** | Home page logo slider (logo, link) + the About page incubators list |
| **Hero & banners** | Home page film (YouTube link or uploaded MP4) and poster; banner photo for each inner page |
| **Certificates & awards** (admin only) | DPIIT / Udyam / GeM / patent numbers, verify link, awards, certificate image |
| **Company settings** (admin only) | Phones, WhatsApp, emails, hours, address, offices + Google Maps, social links, PayU "Book now" and IndiaMART links |
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

Next.js pages ──► GET backend (cached 5 min):
     /api/posts               blog, home blog section, sitemap
     /api/products[/{slug}]   LFP Battery catalogue + product pages, sitemap
     /api/products/datasheet-options   "Request datasheet" dropdown + its validation
     /api/testimonials        home + About testimonial slider
     /api/team                About page leadership team
     /api/settings            phones, emails, offices, socials, Book now / IndiaMART links (header, footer, contact…)
     /api/milestones          About page milestones
     /api/faqs?page=…         FAQ lists (e-Generator page)
     /api/partners            home logo slider + About incubators
     /api/credentials         "Our Recognition" section + Technology page patent no.
     /api/media               home hero film + inner-page banner images
               └─► backend down / not configured? → falls back to frontend/app/data/*.ts
Admin saves/deletes any of these ──► POST website /api/revalidate  (refreshes that content immediately)
```

Settings that must match on both sides:

| Website (`frontend/.env.local`) | Backend (`backend/.env`) |
|---|---|
| `BACKEND_API_URL=http://127.0.0.1:8000` | `FRONTEND_URL=http://localhost:3000` |
| `REVALIDATE_SECRET=<random>` | `WEBSITE_REVALIDATE_SECRET=<same random>` |
| — | `TRUSTED_PROXIES=<IP of the Next.js server>` (so leads/rate limits use the visitor's IP) |

Frontend files involved: `app/lib/backend.ts` (cached fetch + lead forwarding), `app/lib/blog.ts`, `app/lib/products.ts`,
`app/lib/content.ts` (testimonials, team), `app/lib/site.ts` + `app/components/site-provider.tsx` (company details for
server and client components), `app/api/revalidate/route.ts`, and the pages that call them. Posts and products created in
the admin get their own page on first visit — no website rebuild needed. Company names and logos stay in `data/site.ts`.

Run both locally: `php artisan serve` here and `npm run dev` in `../frontend`.

## Article editor (CKEditor 5)

- Self-hosted CKEditor 5 **v49.1.0** in `public/assets/vendor/ckeditor5/` (no CKEditor cloud/CDN), configured in
  `resources/views/admin/posts/form.blade.php`. It runs under CKEditor's open-source **GPL** licence key (`licenseKey: 'GPL'`);
  if GPL terms don't suit the business, buy a commercial key from ckeditor.com and replace `'GPL'` with it.
- Bodies are stored as HTML. Every save goes through `App\Support\PostBody::normalize()`, which converts old text-format
  bodies and **sanitizes** the HTML (only editor markup survives — no scripts, event handlers, inline styles or iframes).
  Allowed CSS classes: `callout` (Style → Highlight box), `lead` (Style → Intro text), `image`, `table`.
- Images dropped into the editor are uploaded to `POST /admin/posts/images` and stored in `storage/app/public/posts/content`.
- To update CKEditor: `npm install --save-dev ckeditor5@<version>`, then copy
  `node_modules/ckeditor5/dist/browser/ckeditor5.umd.js` and `ckeditor5.css` into `public/assets/vendor/ckeditor5/`.
- A backup of the posts table from before the switch to HTML is in `storage/app/private/backups/`.

## Theme

- `public/assets/css/style.css` — the original compiled TailAdmin theme (untouched).
- `public/assets/css/thor-admin.css` — brand layer: remaps the theme's colour tokens to the website palette
  (navy `#1e3e55`, green `#5db556`, bolt `#f5c518`, Inter font) and adds the admin components (`t-*` classes).
  Because `style.css` is precompiled, Tailwind utilities that it doesn't already contain must be added to `thor-admin.css`.

## Tests

```bash
php artisan test   # 52 feature tests: auth, password reset, API tokens, lead API, admin pages, roles, website cache refresh, slugs, article HTML, site content
```
