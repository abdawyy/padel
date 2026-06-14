# Padel Project — Full Technical Analysis

**Generated:** May 23, 2026  
**Repository:** `c:\padel`  
**Framework:** Laravel 13 + Filament 5 (PHP 8.3)

---

## 1. Executive Summary

This is a **multi-tenant sports academy / club management platform** oriented toward racket sports (padel, tennis, squash, pickleball). It targets clubs (“academies”) that operate courts, training sessions, memberships, and open-match matchmaking, with a **B2B SaaS layer** where platform operators sell subscription plans to clubs.

The system serves four audiences through distinct surfaces:

| Audience | Surface | Path |
|----------|---------|------|
| Platform operator (super admin) | Filament SaaS panel | `/saas` |
| Club staff / academy admins | Filament Admin panel | `/admin` |
| Coaches | Filament Coach panel | `/coach` |
| Players | Filament Player panel + REST API | `/player`, `/api/*` |
| Prospective club owners | Public web registration | `/`, `/register-academy` |

Payments are integrated via **Paymob** (Egypt-focused gateway, default currency EGP). Mobile or third-party clients are expected to consume the **Sanctum-authenticated JSON API**.

---

## 2. Technology Stack

### Backend

| Component | Version / Package |
|-----------|-------------------|
| PHP | ^8.3 |
| Laravel | ^13.0 |
| Filament | ^5.4 (admin UI) |
| Laravel Sanctum | ^4.3 (API tokens) |
| Laravel Tinker | ^3.0 |

### Frontend (minimal)

| Component | Purpose |
|-----------|---------|
| Vite 8 | Asset bundling |
| Tailwind CSS 4 | Styling for Blade views |
| Axios | HTTP client (bootstrap) |

### Dev tooling

- **PHPUnit 12** — tests
- **Laravel Pint** — code style
- **Laravel Pail** — log tailing in `composer dev`
- **Faker** — factories/seeders
- **Concurrently** — runs server, queue, logs, and Vite together via `composer dev`

### External services

- **Paymob** — card payments, webhooks, iframe checkout
- **Mail** — Postmark, Resend, SES configured in `config/services.php` (notifications)

---

## 3. High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         Clients / Users                                  │
├──────────────┬──────────────┬──────────────┬──────────────┬──────────────┤
│ Mobile App   │ Web Browser  │ Academy Admin│ Coach        │ Super Admin  │
│ (API token)  │ (Blade)      │ (Filament)   │ (Filament)   │ (Filament)   │
└──────┬───────┴──────┬───────┴──────┬───────┴──────┬───────┴──────┬───────┘
       │              │              │              │              │
       ▼              ▼              ▼              ▼              ▼
┌──────────────────────────────────────────────────────────────────────────┐
│                    Laravel Application (Monolith)                           │
│  ┌─────────────┐  ┌──────────────┐  ┌─────────────┐  ┌─────────────────┐ │
│  │ routes/api  │  │ routes/web   │  │ 4× Filament │  │ Console Schedule │ │
│  │ Sanctum     │  │ Session auth │  │ Panels      │  │ saas:expire-*    │ │
│  └──────┬──────┘  └──────┬───────┘  └──────┬──────┘  └────────┬────────┘ │
│         │                │                 │                   │          │
│         └────────────────┴────────┬────────┴───────────────────┘          │
│                                   ▼                                         │
│              Controllers → Models (Eloquent) → MySQL/SQLite                │
│              PaymobService, Notifications, Form Requests                    │
└──────────────────────────────────────────────────────────────────────────┘
       │
       ▼
┌──────────────┐     ┌─────────────────┐
│   Database   │     │ Paymob Webhooks │
└──────────────┘     └─────────────────┘
```

**Pattern:** Classic Laravel monolith. Business logic lives primarily in controllers, Eloquent models, and one service class (`PaymobService`). Authorization is **inline** in controllers via `User` helper methods (`canManageClub`, `hasAdminAccess`, etc.) rather than Laravel Policies.

**API design:** REST-style JSON under `/api`, with public routes for discovery (clubs, courts, open matches, academy sessions, SaaS plans) and protected routes behind `auth:sanctum`.

---

## 4. Domain Model

### Core entities (14 Eloquent models)

| Model | Responsibility |
|-------|----------------|
| `User` | Authentication, global role, skill level, player profile |
| `Club` | Academy/venue; sport type, registration approval, SaaS status, sport rules JSON |
| `ClubUser` | Pivot: user ↔ club with pivot role (`owner`, `manager`, `staff`) |
| `Court` | Bookable court belonging to a club |
| `CourtSlot` | Recurring weekly template slots (training schedules) |
| `Booking` | Court reservation; private or open match; skill gates |
| `BookingParticipant` | Pivot: users joining open matches with split payment |
| `AcademySession` | Group training / academy events |
| `CoachApplication` | Coaches applying to lead a session |
| `Package` | Membership bundles (monthly, sessions, etc.) |
| `PackageSubscription` | Player subscription to a package |
| `PaymentTransaction` | Paymob payment audit trail |
| `SaasPlan` | Platform subscription tiers for clubs |
| `ClubSaasSubscription` | Club’s active/pending/expired SaaS billing record |

### Entity relationships (simplified)

```
SaasPlan ──< ClubSaasSubscription >── Club ──< Court ──< Booking
                                              │              │
                                              │              └──< BookingParticipant >── User
                                              ├──< AcademySession
                                              ├──< Package ──< PackageSubscription >── User
                                              └──< ClubUser >── User

CourtSlot ── Court
CoachApplication ── AcademySession
```

### Sports support

Clubs and courts carry a `sport_type` (default `padel`). `Club::getRulesForSport()` merges defaults for padel, tennis, pickleball, and squash (max players, match duration, court dimensions). Clubs can override via `sport_rules` JSON.

### Skill levels

Users have `skill_level` (integer 1–7 with human labels on `User::skillLevelLabel()`). Bookings and academy sessions support `skill_min` / `skill_max` for matchmaking eligibility.

---

## 5. User Roles & Access Control

### Global user roles (`users.role`)

| Role | Filament panel access |
|------|------------------------|
| `super_admin` | `/saas` only (not academy admin panel) |
| `admin`, `manager`, `coach`, `staff` | `/admin` if linked to a club |
| `player` | `/player` |
| `coach` | `/coach` (+ admin if `hasAdminAccess`) |

Access is enforced in `User::canAccessPanel()` implementing Filament’s `FilamentUser` contract.

### Club-scoped roles (`club_users.role`)

| Pivot role | Typical permissions |
|------------|---------------------|
| `owner` | Full club management, staff CRUD, SaaS renewal |
| `manager` | Same as owner for `canManageClub()` |
| `staff` | Operational access via `hasAdminAccess()` |

### Authorization helpers (on `User`)

- `isSuperAdmin()` — platform-wide access
- `accessibleClubIds()` — clubs the user can see
- `belongsToClub($club)` — membership check
- `canManageClub($club)` — owner or manager
- `hasAdminAccess($club)` — staff portal eligibility

**Note:** No dedicated `Policy` classes; API controllers call these methods directly or use private `abortIfUnauthorized` / `authorizeManagement` helpers.

---

## 6. Application Surfaces

### 6.1 REST API (`routes/api.php`)

#### Public endpoints

| Method | Route | Purpose |
|--------|-------|---------|
| POST | `/api/register`, `/api/login` | User auth (Sanctum token) |
| POST | `/api/webhooks/paymob/transaction-processed` | Payment webhook (HMAC verified) |
| GET | `/api/academy-sessions`, `/{id}` | Browse training sessions |
| GET | `/api/matches/open` | Open match discovery |
| GET | `/api/saas-plans` | List SaaS tiers |
| GET | `/api/clubs`, `/api/courts` | Public directory |

#### Authenticated endpoints (sample)

| Area | Key routes |
|------|------------|
| Club lifecycle | `POST register-club`, SaaS subscription show/renew |
| Staff | CRUD `/clubs/{club}/staff` |
| Courts & slots | CRUD courts, slots, schedule generation |
| Bookings | Full `apiResource` + `POST bookings/{id}/pay` |
| Matchmaking | `POST bookings/{id}/join` (split Paymob payment) |
| Academy | Club sessions CRUD, enroll, coach applications |
| Availability | `GET clubs/{club}/availability` |

**Token behavior:** Login deletes all previous tokens (`$user->tokens()->delete()`), issuing a single `mobile-app` token.

### 6.2 Public web (`routes/web.php`)

- `/` — Landing page with SaaS plans (`web.home`)
- `/register-academy` — Multi-step academy owner signup (creates user + pending club + pending subscription)
- `/register-academy/pending` — Post-registration confirmation

Uses session-based `Auth` for the registering owner (distinct from API token flow).

### 6.3 Filament panels (4)

| Panel ID | Path | Brand | Resources / pages |
|----------|------|-------|-------------------|
| `admin` (default) | `/admin` | Academy Portal | Clubs, Courts, CourtSlots, Bookings, AcademySessions, Packages, Users |
| `saas` | `/saas` | SaaS | Academies (clubs), Plans, Subscriptions, Users; `SaasStatsOverview` widget |
| `player` | `/player` | Player Portal | Dashboard, MyPackages, MyTraining, MyMatches |
| `coach` | `/coach` | Coach Portal | Dashboard, CoachSessions, CoachMatches |

Admin resources follow Filament v5 structure: `Resources/*/Schemas`, `Tables`, `Pages`.

---

## 7. Key Business Flows

### 7.1 Club onboarding (SaaS)

1. Owner registers via web or API with a `SaasPlan` and billing cycle.
2. Club created with `registration_status = pending`, `subscription_status = inactive`.
3. `ClubSaasSubscription` created with `status = pending`.
4. Super admin approves in SaaS panel → club becomes operational.
5. Scheduled command `saas:expire-subscriptions` manages `active` → `past_due` (3-day grace) → `expired`.
6. `saas:notify-expiring` sends `SubscriptionExpiringNotification` daily at 09:00.

### 7.2 Court booking

1. Authenticated user creates booking on a court (private or `open_match`).
2. Booking includes sport, session type, skill range, coach optional, pricing.
3. For open matches, other players discover via `GET /api/matches/open` with filters (club, sport, skill, coached-only).
4. Join flow: `POST bookings/{id}/join` → participant row + Paymob session (price split by `max_players`).

### 7.3 Payment & confirmation (Paymob)

1. `PaymobService` authenticates, registers order, generates payment key + iframe URL.
2. Merchant order IDs: `booking_{id}_user_{id}` or `session_{id}_user_{id}`.
3. Webhook validates HMAC (SHA-512), records `PaymentTransaction`.
4. On success for bookings: marks participant `paid`; when all paid → booking `confirmed` + `BookingConfirmedNotification`.
5. On success for sessions: enrolls player in `academy_session_user`.

### 7.4 Academy / training

- **Court slots:** Recurring templates (`day_of_week`, time range) schedulable into `AcademySession` instances.
- **Sessions:** Group training with coach, skill gates, video URLs, session plans.
- **Enrollment:** API enroll + payment path mirrors booking split logic.
- **Coach applications:** Coaches apply to sessions; club managers accept/reject via API.

### 7.5 Packages (memberships)

- Clubs define `Package` records (type, duration, session count, price per player).
- Players subscribe via `package_subscriptions` pivot (status, expiry, sessions remaining).
- Player panel page `MyPackages` surfaces subscriptions.

---

## 8. Database Schema Overview

**23 migrations** (March–May 2026), including Laravel defaults (users, cache, jobs, personal_access_tokens).

### Notable tables

| Table | Highlights |
|-------|------------|
| `users` | Soft deletes; role, phone, skill_level, date_of_birth, preferred_sport |
| `clubs` | sport_type, registration_status, sport_rules JSON, approval metadata |
| `club_users` | owner / manager / staff pivot |
| `courts` | price_per_hour, capacity, slot_duration_minutes, is_active |
| `court_slots` | Weekly recurring slot definitions |
| `bookings` | match_type, session_type, skill_min/max, coach_fee, max_players |
| `booking_participants` | amount_due, payment_status |
| `academy_sessions` | session_plan, video_url(s), skill range |
| `academy_session_user` | enrollment pivot |
| `coach_applications` | apply / withdraw / respond workflow |
| `packages` / `package_subscriptions` | Membership products |
| `payment_transactions` | Paymob audit |
| `saas_plans` / `club_saas_subscriptions` | B2B billing |

---

## 9. Services & Integrations

### PaymobService (`app/Services/PaymobService.php`)

Centralizes:

- `createPaymentSessionForParticipant(Booking, User, float)`
- `createPaymentSessionForEnrollment(AcademySession, User, float)`

Configuration via `config/services.php` → env vars: `PAYMOB_API_KEY`, `PAYMOB_INTEGRATION_ID`, `PAYMOB_IFRAME_ID`, `PAYMOB_HMAC_SECRET`, `PAYMOB_CURRENCY`.

### Notifications

| Class | Trigger |
|-------|---------|
| `BookingConfirmedNotification` | All participants paid on a booking |
| `SubscriptionExpiringNotification` | Scheduled SaaS expiry warning |

### Scheduled commands (`routes/console.php`)

| Command | Schedule |
|---------|----------|
| `saas:expire-subscriptions` | Daily 00:05 |
| `NotifyExpiringSubscriptions` | Daily 09:00 |

---

## 10. HTTP Layer Details

### API controllers (16 files)

Located under `app/Http/Controllers/Api/`:

- `AuthController` — register, login, logout
- `ClubController`, `ClubRegistrationController`, `ClubStaffController`
- `CourtController`, `CourtSlotController`, `AvailabilityController`
- `BookingController`, `PaymentController`, `MatchmakingController`
- `AcademySessionController`, `CoachApplicationController`
- `SaasPlanController`, `WebhookController`

### API resources (JSON transformers)

`app/Http/Resources/`: `BookingResource`, `ClubResource`, `CourtAvailabilityResource`, `OpenMatchResource`, and others for consistent API shapes.

### Form requests

At least `StoreBookingRequest` with `authorize()` — limited use of dedicated request classes; most validation is inline in controllers.

---

## 11. Filament Admin Structure

```
app/Filament/
├── Resources/          # Academy admin CRUD (default panel)
│   ├── Clubs, Courts, CourtSlots, Bookings
│   ├── AcademySessions, Packages, Users
├── Saas/               # Platform operator
│   ├── Resources/Academies, Plans, Subscriptions, Users
│   └── Widgets/SaasStatsOverview.php
├── Player/Pages/       # Player self-service
├── Coach/Pages/        # Coach self-service
```

**~80 Filament PHP files** — substantial UI investment for back-office operations.

---

## 12. Data Seeding & Local Development

### Seeders

| Seeder | Purpose |
|--------|---------|
| `SaasPlanSeeder` | Platform plans |
| `AdminSeeder` | Fixed demo admin accounts |
| `DatabaseSeeder` | 5 clubs, courts, slots, bookings, sessions, packages; 50 players, 10 coaches |
| `NewFlowsSeeder` | Additional flows dummy data |

**Demo super admin:** `admin@padel.test` / `password`

### Composer scripts

```bash
composer setup   # install, .env, key, migrate, npm build
composer dev     # serve + queue + pail + vite (concurrent)
composer test    # php artisan test
```

### Health check

`GET /up` — Laravel 13 health route.

---

## 13. Testing

| File | Coverage |
|------|----------|
| `tests/Feature/AuthApiTest.php` | Register, login, logout with Sanctum |
| `tests/Feature/ExampleTest.php` | Scaffold |
| `tests/Unit/ExampleTest.php` | Scaffold |

**Assessment:** Test coverage is **minimal** relative to domain complexity. Critical paths (payments, matchmaking, webhooks, SaaS expiry) lack automated tests.

---

## 14. Project Directory Map

```
padel/
├── app/
│   ├── Console/Commands/       # SaaS subscription maintenance
│   ├── Filament/               # 4 panels, ~80 files
│   ├── Http/
│   │   ├── Controllers/Api/    # REST API
│   │   ├── Controllers/Web/    # Public registration
│   │   ├── Resources/          # API transformers
│   │   └── Requests/
│   ├── Models/                 # 14 domain models
│   ├── Notifications/
│   ├── Providers/Filament/     # Panel providers
│   └── Services/PaymobService.php
├── bootstrap/app.php           # Routes, stateful API middleware
├── config/                     # Standard Laravel + paymob in services.php
├── database/
│   ├── factories/              # Model factories
│   ├── migrations/             # 23 migrations
│   └── seeders/
├── public/                     # Entry point + Filament assets
├── resources/
│   ├── views/web/              # Landing + registration
│   ├── views/filament/         # Custom panel views
│   ├── css/, js/
├── routes/
│   ├── api.php
│   ├── web.php
│   └── console.php
├── tests/
├── composer.json
├── package.json
└── vite.config.js
```

**Approximate scale:** ~233 tracked files in workspace glob; PHP-heavy with limited custom JavaScript.

---

## 15. Security Considerations

| Area | Status |
|------|--------|
| API auth | Sanctum bearer tokens; inactive users blocked at login |
| Webhook | HMAC-SHA512 validation on Paymob payloads |
| CSRF | Filament/web routes protected; API stateful middleware enabled |
| Authorization | Ad-hoc controller checks — consistent but not centralized |
| Mass assignment | Models use `$fillable` |
| Soft deletes | Users, clubs, courts, bookings |

**Gaps to review:**

- Webhook endpoint is public — relies entirely on HMAC (correct pattern if secret is set).
- No rate limiting visible on auth endpoints in `routes/api.php`.
- `PaymentTransaction` stores full `provider_payload` — ensure PII handling in production.
- Role `academy_admin` accepted at API register but panel access mapping should be verified.

---

## 16. Strengths

1. **Clear multi-panel UX** — Separates platform ops, club admin, coach, and player concerns.
2. **Rich domain** — Open matches, skill-based matchmaking, academy sessions, packages, and SaaS in one codebase.
3. **Sport-agnostic design** — Rules and types extensible beyond padel.
4. **Payment flow** — Transaction locking, split payments, webhook idempotency (`already_processed`).
5. **Operational automation** — Subscription expiry and notification scheduling.
6. **Seed data** — Realistic local/demo environment.

---

## 17. Gaps & Recommendations

| Priority | Item | Suggestion |
|----------|------|------------|
| High | Test coverage | Add feature tests for webhook HMAC, join match, club registration approval |
| High | README | Replace default Laravel README with project-specific setup, env vars, panel URLs |
| Medium | Authorization | Extract Laravel Policies from inline `abort_unless` for maintainability |
| Medium | `.env.example` | Missing in repo — add documented Paymob and DB variables |
| Medium | API documentation | OpenAPI/Swagger or Scribe for mobile team |
| Low | Service layer | Move complex booking/payment logic out of controllers |
| Low | Queue usage | Notifications and webhooks could be queued for resilience |

---

## 18. Environment Variables (Expected)

Based on `config/services.php` and Laravel defaults:

```env
APP_NAME=Padel
APP_URL=http://localhost

DB_CONNECTION=sqlite  # or mysql

PAYMOB_BASE_URL=https://accept.paymob.com/api
PAYMOB_API_KEY=
PAYMOB_INTEGRATION_ID=
PAYMOB_IFRAME_ID=
PAYMOB_HMAC_SECRET=
PAYMOB_CURRENCY=EGP

QUEUE_CONNECTION=database
```

`PAYMOB_HMAC_SECRET` must be set in production; if it is missing, Paymob webhooks return **503** with a clear configuration message (not a silent signature failure).

Application notifications are sent **synchronously** (no queue worker required for mail).

Mail and queue drivers follow standard Laravel configuration.

---

## 19. Summary

**Padel** is a production-oriented Laravel monolith for **sports club operations and SaaS monetization**, with a strong Filament back-office footprint and a Sanctum API aimed at mobile players. Core value flows—court booking, open-match matchmaking with split Paymob payments, academy training, coach workflows, and club subscriptions—are implemented end-to-end with scheduled SaaS lifecycle management.

The codebase is **feature-rich but documentation- and test-light**; the default README does not describe the actual product. For new contributors, start with `routes/api.php`, `app/Models/User.php`, the four `*PanelProvider.php` files, and `PaymobService` / `WebhookController` to understand the critical paths.

---

*This document was generated by automated codebase analysis. Re-run or extend sections as the project evolves.*
