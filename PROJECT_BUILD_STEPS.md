# Mann Travel Ticketing Manager — Build Steps

This document records **what was actually built** in this project, in the order the work was done. It is a walkthrough of the live codebase, not a future plan.

**Product:** Mann Travel Ticketing Desk — a Laravel web app for fare and commission management.  
**Stack:** Laravel 10, PHP 8.1+, MySQL, Blade, Bootstrap 5, custom CSS/JS.  
**Hosting:** Laragon (`d:\laragon\www\ticketing-manager`), with a project-root `index.php` and `.htaccess` so the site can run without pointing the vhost at `/public`.

---

## Step 1 — Laravel 10 project scaffold

A standard Laravel 10 application was created (`composer.json` requires `laravel/framework ^10.0`).

What this included:

- Default app structure (`app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`, `public/`, `storage/`)
- Default Laravel tables via migrations:
  - `users`
  - `password_reset_tokens`
  - `failed_jobs`
  - `personal_access_tokens` (Sanctum)
- Environment template (`.env.example`) pointing at MySQL database `ticketing_manager`
- Default `README.md` (Laravel framework readme, not product docs)

---

## Step 2 — Laragon / document-root wiring

Laravel normally serves from `public/`. For Laragon, the front controller was also placed at the **project root**:

- `index.php` — loads Composer autoload, boots the app, handles the HTTP request
- `.htaccess` — rewrite rules send non-file requests to `index.php`

Assets still live under `public/` (`css/style.css`, `js/ticketing.js`) and are loaded with Laravel `asset()`.

A helper `clear-cache.php` was added to run `optimize:clear` from the browser; it is currently commented out.

---

## Step 3 — Static ticketing desk UI (browser-only data)

The first product UI was a **Ticketing Desk** designed around two jobs:

1. **Ticketing Manager** — enter/edit published fares, commission/discount, markup
2. **Ticketing Team** — read-only view of net / sell fare / margin for issuance

Work done:

- Layout: navy sidebar + main column (`resources/views/layouts/app.blade.php`)
- Styling: `public/css/style.css` (app shell, tables, badges, history panel)
- Client logic: `public/js/ticketing.js` (and a spare copy `public/js/ticketing2.js`)

The JS file still documents the original intent: data started as **in-browser mock arrays** sourced from airline trade circulars (e.g. Air India tour code AUSNT007, BSP commission circulars, AU IATA PCC 8T03). A later step was to persist that data in Laravel + MySQL.

**Fare math (still used in the form preview):**

```
net   = published × (1 − disc_comm / 100)
gross = net + markup
margin = gross − net   (same as markup)
```

---

## Step 4 — MySQL domain tables mapped with Eloquent

Business tables were connected as Eloquent models. Most of these tables were **not** created by Laravel migrations in this repo; they already exist in MySQL and are mapped by `$table` names.

| Model | Table | Purpose |
|-------|--------|---------|
| `Fare` | `fares` | Earlier fare CRUD (largely superseded) |
| `FareCommissionEntries` | `fare_and_commission_entries` | Main fare + commission records |
| `Route` | `routes` | Origin / destination + IATA codes |
| `Cabin` | `cabins` | Cabin class lookup |
| `FareSource` | `fare_sources` | Private / IATA-BSP lookup |
| `Currency` | `currencies` | Currency codes |
| `AirlineCommission` | `sys_commission_list` | IATA/BSP commission master (AU vs ex-AU %) |

Relationships on `FareCommissionEntries`:

- `belongsTo` Route (`route_id`)
- `belongsTo` Cabin (`cabin_id`)
- `belongsTo` FareSource (`source_id`)
- `belongsTo` Currency (`currency_id`)

Fillable fare fields include airline, airline code, published, disc_comm, net, markup, gross, travel dates, valid_until, tour_code, PCC/IATA ref, notes, and status.

---

## Step 5 — Authentication (login / register / logout)

A custom session-based auth layer was added (not Laravel Breeze/Jetstream).

**Files created:**

- `app/Http/Controllers/AuthController.php`
- `resources/views/auth/login.blade.php` — branded two-column Mann Travel sign-in
- `resources/views/auth/register.blade.php`
- `resources/views/dashboard.blade.php`
- `database/seeders/UserSeeder.php`
- `AUTHENTICATION_SETUP.md`, `AUTH_QUICK_REFERENCE.md`, `verify_auth_setup.sh`

**Routes (`routes/web.php`):**

| Method | Path | Access | Action |
|--------|------|--------|--------|
| GET | `/` | Public | Logged in → dashboard; else → login |
| GET/POST | `/login` | Guest | Sign in |
| GET/POST | `/register` | Guest | Create user |
| GET | `/dashboard` | Auth | Welcome cards |
| POST | `/logout` | Auth | Destroy session |

**Behaviour:**

- Email + password login, optional “remember me”
- Session regenerated on login; invalidated on logout
- CSRF on forms
- After login/register, redirect goes to the ticketing desk (`ticketing.index`)
- Test users from seeder: `test@manntravel.com` / `password123` and `demo@manntravel.com` / `demo1234`

**Note:** Login compares the stored password as **plain text** (`$user->password === $credentials['password']`). Registration also stores the password without hashing. Auth docs originally described bcrypt; the live controller does not hash.

---

## Step 6 — Protect the desk behind `auth` middleware

Ticketing URLs were moved inside `Route::middleware('auth')`. Unauthenticated users are sent to `/login` (`Authenticate` middleware).

Guest middleware keeps logged-in users off the login page.

---

## Step 7 — First backend CRUD: `FareController`

`FareController` was built to load lookups (cabins, fare sources, currencies) and CRUD `fares`:

- `index` — desk view with fares + commission list
- `store` / `update` / `destroy` — validated fare fields (`published_fare`, `discount_commission_percent`, `agency_markup`, etc.)

Those fare routes in `web.php` are now **commented out**. The live desk uses fare-and-commission entries instead (Step 8). The controller file is still in the project.

---

## Step 8 — Main data path: fare & commission entries

Work moved to table `fare_and_commission_entries` via `FareCommissionEntriesController`.

### Manager page (`GET /ticketing`)

- Loads all entries with route, cabin, source, currency
- Loads lookup dropdowns
- Loads `sys_commission_list` (search by airline or code)
- JSON response if the request expects JSON (used by AJAX search)

### Create (`POST /fare-commission-entries`)

1. Validate airline, codes, origin/destination, cabin, source, published, currency, disc_comm, markup, dates, notes
2. `Route::firstOrCreate` on origin + destination (IATA codes start null)
3. Store `route_id` on the entry (origin/destination not stored as the primary route fields)

### Update (`PUT /fare-commission-entries/{id}`)

- Resolves cabin / source / currency by **name/code** from the inline table editor
- Finds or creates the route
- Updates airline, amounts, dates, status

### Delete (`DELETE /fare-commission-entries/{id}`)

- Deletes the entry, then deletes the related `routes` row

### Manager UI (Blade + JS)

- Add-fare form with live net/gross/margin preview
- Table: airline, route, cabin, source, published, disc/comm, net, markup, gross, valid until, status
- Inline **Edit / Save / Cancel** and **Delete**
- Toasts for success/error
- Page reload after create

---

## Step 9 — IATA / BSP commission master list

`CommissionMasterController` was added for table `sys_commission_list`.

- `PUT /airline-commissions/{id}` — update airline, code, numeric, AU commission %, ex-AU commission %
- `DELETE /airline-commissions/{id}` — delete a carrier row (wired in controller; manager table currently focuses on inline edit)

Manager table is editable in-place via `ticketing.js` (`edit-commission` / `save-commission` / `cancel-commission`). Ticketing Team sees the same list as **reference only** (no edit buttons).

Search on the manager page filters this list by airline name or code.

---

## Step 10 — Route IATA codes

Routes were split from free-text origin/destination:

- `routes.origin`, `routes.destination`
- `routes.origin_code`, `routes.destination_code`

`RouteController::updateCodes` (`PUT /routes/{id}/codes`) saves uppercase trimmed codes.

Manager UI:

- Route shown as `Origin (XXX) → Destination (YYY)`
- If a code is missing, an **Add Code** button opens a Bootstrap modal
- JS submits the modal with AJAX, updates the row, removes the button, shows a toast

---

## Step 11 — Roles: Ticketing Manager vs Ticketing Team

### Database

Migration `2026_08_20_035742_add_role_to_users_table.php` adds:

- `users.role` string, default `ticketing_team`

### Middleware

`RoleMiddleware` (`role` alias in `app/Http/Kernel.php`):

- Must be logged in
- User `role` must be in the allowed list, or **403**

### Navigation (`layouts/navbar.blade.php`)

- `ticketing_manager` — link to Ticketing Manager (add/edit fares)
- `ticketing_manager` **and** `ticketing_team` — link to Ticketing Team (read-only desk)

### Sidebar stats (shared)

`AppServiceProvider` view composer on the navbar:

- Active fares (`status = Active`)
- Expiring soon (`status = Expiring Soon`)
- Distinct carrier count

Logout + current user name sit in the sidebar footer.

---

## Step 12 — Ticketing Team page (read-only + filters)

`FareCommissionEntriesController::ticketingTeam` + view `resources/views/ticketing-team/index.blade.php`.

Route: `GET /ticketing-team` (`ticketing.team`).

What was built:

- Stat tiles: active fares, expiring ≤ 7 days, average disc/comm %, total margin (`SUM(gross − net)`)
- Table of fares (no edit/delete — those buttons are commented out)
- Filters: search (airline / origin / destination), fare source, status
- AJAX search that returns JSON when `Accept: application/json`
- Read-only commission master list

Manager and team share the same CSS and `ticketing.js`.

---

## Step 13 — Shared app layout (replace standalone HTML)

Earlier screens were full HTML pages. The desk was then wrapped in:

- `layouts/app.blade.php` — Bootstrap 5 + Icons, CSRF meta, `@yield('content')`, `@stack` for CSS/JS
- `layouts/navbar.blade.php` — role-aware sidebar

Login, register, and dashboard remain standalone pages (their own CSS, no app shell).

---

## Step 14 — Audit history on fare entries

### Database

Migration `2026_08_21_101120_create_audit_logs_table.php`:

- `user_id`, `action`, `model_type`, `model_id`
- JSON `old_values` / `new_values`
- Index on `(model_type, model_id)`

### Observer

`FareCommissionEntriesObserver` registered in `AppServiceProvider`:

- **created** — stores new values; replaces `route_id` with `"Origin → Destination"`
- **updated** — stores old + new; same route label
- **deleted** — stores old values

### UI

- History clock button on each manager row
- Slide-over panel + overlay (`history-panel` / `history-overlay` in CSS)
- `GET /fare-commission-entries/{id}/history` returns JSON logs
- JS renders created / updated / deleted events with field diffs

---

## Step 15 — Front-end polish on the desk

In CSS/JS/Blade, the desk was refined for daily use:

- Sticky/app-shell layout, table scroll, status badges
- Live date pill in the top bar (en-AU format)
- Toast notifications
- Inline editors that swap text for inputs, then PUT JSON
- History panel styling
- Duplicate asset copies at project root (`css/style.css`, `js/ticketing.js`) in addition to `public/`

---

## Current route map (protected unless noted)

| Method | URI | Name | Who |
|--------|-----|------|-----|
| GET | `/` | — | Public redirect |
| GET/POST | `/login` | `login` | Guest |
| GET/POST | `/register` | `register` | Guest |
| GET | `/dashboard` | `dashboard` | Any logged-in user |
| POST | `/logout` | `logout` | Any logged-in user |
| GET | `/ticketing` | `ticketing.index` | Roles `ticketing_manager`, `ticketing_team` |
| POST | `/fare-commission-entries` | `fare-commission-entries.store` | Same |
| PUT | `/fare-commission-entries/{id}` | `fare-commission-entries.update` | Same |
| DELETE | `/fare-commission-entries/{id}` | `fare-commission-entries.destroy` | Same |
| GET | `/fare-commission-entries/{id}/history` | `fare-commission-entries.history` | Same |
| PUT | `/airline-commissions/{id}` | `airline-commissions.update` | Same |
| DELETE | `/airline-commissions/{id}` | `airline-commissions.destroy` | Same |
| PUT | `/routes/{id}/codes` | `routes.codes.update` | Same |
| GET | `/ticketing-team` | `ticketing.team` | Same |

Navbar still hides the **Manager** link from `ticketing_team` users even though the route group currently allows both roles.

---

## File map of work done (application code only)

```
app/
  Http/Controllers/
    AuthController.php
    FareController.php                    (older fares CRUD; routes commented)
    FareCommissionEntriesController.php   (live desk)
    CommissionMasterController.php
    RouteController.php
  Http/Middleware/
    RoleMiddleware.php
    Authenticate.php / RedirectIfAuthenticated.php
  Models/
    User.php, Fare.php, FareCommissionEntries.php, Route.php,
    Cabin.php, FareSource.php, Currency.php, AirlineCommission.php, AuditLog.php
  Observers/
    FareCommissionEntriesObserver.php
  Providers/
    AppServiceProvider.php                (observer + navbar stats)

database/migrations/
  2014_10_12_* users, password resets
  2019_* failed_jobs, personal_access_tokens
  2026_08_20_035742_add_role_to_users_table.php
  2026_08_21_101120_create_audit_logs_table.php

database/seeders/UserSeeder.php

resources/views/
  layouts/app.blade.php, layouts/navbar.blade.php
  auth/login.blade.php, auth/register.blade.php
  dashboard.blade.php
  ticketing/index.blade.php
  ticketing-team/index.blade.php

public/css/style.css
public/js/ticketing.js
routes/web.php
```

---

## How to run (as the project is set up today)

1. MySQL database `ticketing_manager` with domain tables + Laravel tables
2. `.env` with `DB_*` and `SESSION_DRIVER=file`
3. `php artisan migrate` (users, role column, audit_logs)
4. `php artisan db:seed --class=UserSeeder` if test users are needed
5. Open via Laragon vhost **or** `php artisan serve`
6. Sign in, then use **Ticketing Manager** and/or **Ticketing Team** from the sidebar

---

## What this project is for (end state)

A travel-agency **ticketing desk**:

- Managers capture published fares, private/tour-code or IATA-BSP source, commission %, markup, validity, and notes
- The system derives net and sell (gross) fares for the team
- The team filters and reads issuance data plus the standing AU IATA PCC 8T03 commission schedule
- Changes to fare entries are kept in `audit_logs` and shown in a history panel
- Access is session-based, with a `role` column separating manager vs team in the UI
