# Padel Platform — Backlog for AI & Human Implementers

**Purpose:** Actionable backlog of bugs, missing flows, enhancements, and UI/UX work. Any AI agent or developer can pick an item by **ID**, implement it, and mark it done.

**Companion doc:** [PROJECT_ANALYSIS.md](./PROJECT_ANALYSIS.md) (architecture overview)

**Last audited:** May 23, 2026

---

## How to use this document (AI handoff)

1. **Pick one item** by ID (e.g. `BUG-001`). Do not batch unrelated P0 fixes without explicit request.
2. **Read listed files** before coding. Match existing Laravel/Filament patterns.
3. **Implement + test:** Add/update PHPUnit feature test when touching API/payment/auth.
4. **Definition of done:** All acceptance criteria checked; no new linter errors; migration reversible if DB changed.
5. **Mark complete** in the checklist at the bottom or open a PR referencing the ID.

### Classification legend

| Priority | Meaning | Target |
|----------|---------|--------|
| **P0** | Production-breaking / money / data loss | Fix immediately |
| **P1** | Major broken flow or security hole | Next sprint |
| **P2** | Important UX or correctness | Planned |
| **P3** | Polish, nice-to-have | Backlog |

| Difficulty | Typical effort | Examples |
|------------|----------------|----------|
| **Easy** | 1–4 hours | Typo, missing relation, validation rule, filter |
| **Medium** | 4–16 hours | New Filament resource, transaction wrapper, notification |
| **Hard** | 16+ hours | Payment refactor, multi-tenant UI, full booking flow in panel |

---

## Quick reference — P0 items (start here)

| ID | Title | Difficulty |
|----|-------|------------|
| BUG-001 | Session payments stored as `booking_id` (FK break) | Hard |
| BUG-002 | SaaS `past_due` subscriptions never expire | Easy |
| BUG-003 | `clubUsers` relationship missing — owner emails fail | Easy |
| BUG-004 | Double-booking race on `BookingController::store` | Medium |
| BUG-005 | Owner can set `status: confirmed` without payment | Easy |
| BUG-006 | `BookingConfirmedNotification` uses `booking_date` (N/A) | Easy |
| BUG-007 | `ClubController::store` bypasses registration approval | Medium |
| BUG-008 | SaaS `renew` activates without payment verification | Medium |
| FLOW-001 | Password reset API (missing entirely) | Medium |
| FLOW-002 | Refund / cancellation payment reversal | Hard |
| UX-001 | Player cannot pay or book in `/player` panel | Hard |
| UX-002 | Public site: no links to player/coach login | Easy |

---

# Part A — Bugs (correctness & security)

## P0 — Critical bugs

### BUG-001 — Academy session Paymob webhook writes invalid `booking_id`
- **Priority:** P0 | **Difficulty:** Hard | **Area:** API / Payments
- **Files:** `app/Http/Controllers/Api/WebhookController.php`, `app/Services/PaymobService.php`, `app/Models/PaymentTransaction.php`, `database/migrations/2026_03_21_011456_create_payment_transactions_table.php`
- **Problem:** Enrollment merchant IDs are `session_{sessionId}_user_{userId}` but webhook stores `sessionId` in `payment_transactions.booking_id` (FK → `bookings`). Insert fails or corrupts data; paid users may never enroll.
- **Fix:** Migration: add nullable `academy_session_id` FK (or polymorphic `payable_type`/`payable_id`). Update webhook, model, and `PaymobService` to use correct reference.
- **Acceptance criteria:**
  - [x] Session payment creates `PaymentTransaction` without FK violation
  - [x] Successful webhook enrolls user in `academy_session_user`
  - [x] Feature test: webhook payload for `session_*` merchant order

---

### BUG-002 — SaaS `past_due` subscriptions never become `expired`
- **Priority:** P0 | **Difficulty:** Easy | **Area:** Console / SaaS
- **Files:** `app/Console/Commands/ExpireSaasSubscriptions.php`, `app/Models/ClubSaasSubscription.php`
- **Problem:** Command only expires `status = active`. After grace, subs move to `past_due` and stay forever; `syncClubStatus()` keeps club `active`.
- **Fix:** Second pass: expire `past_due` where `ends_at < now() - graceDays`. Call `syncClubStatus()`.
- **Acceptance criteria:**
  - [x] `past_due` sub past grace → `expired`, club `inactive`
  - [x] Unit/feature test for command

---

### BUG-003 — `clubUsers` relation used but not defined on `User`
- **Priority:** P0 | **Difficulty:** Easy | **Area:** Models / Notifications
- **Files:** `app/Console/Commands/NotifyExpiringSubscriptions.php`, `app/Filament/Saas/Resources/Academies/AcademyResource.php` (lines ~245, ~278)
- **Problem:** `whereHas('clubUsers', ...)` — relation does not exist. Owner emails on approve/reject/expiry never send.
- **Fix:** Add `clubUsers()` hasMany to `ClubUser` model **or** replace with `$club->users()->wherePivot('role', 'owner')`.
- **Acceptance criteria:**
  - [x] `saas:notify-expiring` finds owners
  - [x] Academy approve/reject notifies owners

---

### BUG-004 — Concurrent bookings can overlap (no row lock)
- **Priority:** P0 | **Difficulty:** Medium | **Area:** API
- **Files:** `app/Http/Controllers/Api/BookingController.php` (~87–160)
- **Problem:** Overlap check runs outside transaction without `lockForUpdate()`.
- **Fix:** Wrap create + overlap check in `DB::transaction()` with court/date lock or pessimistic lock on overlapping rows.
- **Acceptance criteria:**
  - [x] Two simultaneous POSTs for same court/slot → one succeeds, one 422
  - [x] Feature test with concurrent requests (or simulated)

---

### BUG-005 — API allows confirming booking without all payments
- **Priority:** P0 | **Difficulty:** Easy | **Area:** API
- **Files:** `app/Http/Controllers/Api/BookingController.php` (`update`)
- **Problem:** Client can PATCH `status: confirmed` while participants still `pending`.
- **Fix:** Reject `confirmed` unless all participants `paid` (or owner-only private booking paid).
- **Acceptance criteria:**
  - [x] PATCH with `confirmed` + unpaid participant → 422
  - [x] Webhook path still works

---

### BUG-006 — Booking confirmation email shows date as N/A
- **Priority:** P0 | **Difficulty:** Easy | **Area:** Notifications
- **Files:** `app/Notifications/BookingConfirmedNotification.php` (line 27)
- **Problem:** Uses `$booking->booking_date` — column does not exist.
- **Fix:** Use `$booking->start_time?->format('Y-m-d')` (or localized).
- **Acceptance criteria:**
  - [x] Mail subject/body show correct date
  - [x] Action URL points to meaningful route (not `/`)

---

### BUG-007 — `POST /api/clubs` bypasses pending registration workflow
- **Priority:** P0 | **Difficulty:** Medium | **Area:** API / SaaS
- **Files:** `app/Http/Controllers/Api/ClubController.php` (`store`), `routes/api.php`
- **Problem:** Any user can create club with `subscription_status` default/active, skip super-admin approval.
- **Fix:** Remove public `store` from apiResource **or** force `registration_status=pending`, `subscription_status=inactive`, delegate to `ClubRegistrationController` only.
- **Acceptance criteria:**
  - [x] New clubs via API require approval before operational booking
  - [x] Only `register-club` or SaaS approval activates club

---

### BUG-008 — SaaS subscription `renew` marks active without payment proof
- **Priority:** P0 | **Difficulty:** Medium | **Area:** API / Payments
- **Files:** `app/Http/Controllers/Api/ClubRegistrationController.php` (`renew`)
- **Problem:** Creates `status: active` with optional `payment_reference` string — no Paymob verification.
- **Fix:** Create `pending` sub + Paymob session; activate via webhook (mirror booking flow).
- **Acceptance criteria:**
  - [x] Renew returns payment URL or pending status until webhook
  - [x] Cannot activate without successful transaction

---

## P1 — High severity bugs

### BUG-009 — Open-match skill filter broken for API-created bookings
- **Priority:** P1 | **Difficulty:** Easy | **Area:** API
- **Files:** `app/Http/Controllers/Api/BookingController.php`, `app/Http/Requests/StoreBookingRequest.php`
- **Problem:** Stores `skill_level` string; matchmaking filters `skill_min`/`skill_max` (never set).
- **Fix:** Map skill level to min/max on create, or validate and persist `skill_min`/`skill_max`.
- **Acceptance criteria:**
  - [x] Open match with skill 3–5 only visible to eligible players

---

### BUG-010 — Enrollment capacity race (academy sessions)
- **Priority:** P1 | **Difficulty:** Medium | **Area:** API
- **Files:** `app/Http/Controllers/Api/AcademySessionController.php`, `WebhookController.php`
- **Problem:** `loadCount` + `attach` without lock; webhook enrolls without capacity check.
- **Fix:** Transaction + `lockForUpdate()` on session; re-check count before attach.
- **Acceptance criteria:**
  - [x] Cannot exceed `max_players` under concurrent enroll/webhook

---

### BUG-011 — Webhook does not verify payment amount
- **Priority:** P1 | **Difficulty:** Medium | **Area:** API / Payments
- **Files:** `app/Http/Controllers/Api/WebhookController.php`
- **Problem:** Marks paid without comparing `amount_cents` to `amount_due`.
- **Fix:** Load participant pivot / session price; reject mismatch (log + 422).
- **Acceptance criteria:**
  - [x] Underpayment does not confirm booking or enroll

---

### BUG-012 — Paymob called inside DB transaction (match join)
- **Priority:** P1 | **Difficulty:** Medium | **Area:** API
- **Files:** `app/Http/Controllers/Api/MatchmakingController.php` (~88–122)
- **Problem:** External HTTP inside transaction holds locks.
- **Fix:** Insert participant in transaction; commit; then call Paymob; rollback participant on Paymob failure.
- **Acceptance criteria:**
  - [x] Paymob failure does not leave orphan paid status
  - [x] No long-held DB locks during HTTP

---

### BUG-013 — Unpaid open-match join blocks capacity
- **Priority:** P1 | **Difficulty:** Medium | **Area:** API / Matchmaking
- **Files:** `app/Http/Controllers/Api/MatchmakingController.php`, `WebhookController.php`
- **Problem:** Join creates `payment_status=pending` participant; counts toward `max_players` indefinitely.
- **Fix:** Expire pending participants after TTL (scheduled job) or don't count unpaid toward capacity.
- **Acceptance criteria:**
  - [x] Full match not blocked by abandoned pending joins

---

### BUG-014 — Payment split rounding ≠ total_price
- **Priority:** P1 | **Difficulty:** Easy | **Area:** API
- **Files:** `BookingController.php`, `MatchmakingController.php`
- **Problem:** `round(total/max, 2)` per player may not sum to total.
- **Fix:** Assign remainder to owner or last participant; document in API.
- **Acceptance criteria:**
  - [x] Sum of `amount_due` equals `total_price` for all participants

---

### BUG-015 — No unique index on `booking_participants` / `club_users`
- **Priority:** P1 | **Difficulty:** Easy | **Area:** Database
- **Files:** `database/migrations/2026_03_21_005724_*`, `2026_03_21_005714_*`
- **Problem:** Duplicate pivot rows possible under race.
- **Fix:** New migration: `unique(['booking_id','user_id'])`, `unique(['club_id','user_id'])`.
- **Acceptance criteria:**
  - [x] Duplicate attach fails at DB level

---

### BUG-016 — Bookings allowed on inactive / unapproved clubs
- **Priority:** P1 | **Difficulty:** Easy | **Area:** API
- **Files:** `app/Http/Controllers/Api/BookingController.php`, `CourtController.php`
- **Problem:** No check for `registration_status`, `subscription_status`, court `is_active`.
- **Fix:** Shared policy/helper; use in store/update.
- **Acceptance criteria:**
  - [x] Booking on pending club → 422

---

### BUG-017 — `publicIndex` academy sessions expose unapproved clubs
- **Priority:** P1 | **Difficulty:** Easy | **Area:** API
- **Files:** `app/Http/Controllers/Api/AcademySessionController.php` (`publicIndex`)
- **Fix:** `whereHas('club', fn ($q) => $q->where('registration_status','approved')->where(...))`.
- **Acceptance criteria:**
  - [x] Pending club sessions not in public API

---

### BUG-018 — Coach applications: accept race + no club validation
- **Priority:** P1 | **Difficulty:** Medium | **Area:** API
- **Files:** `app/Http/Controllers/Api/CoachApplicationController.php`
- **Problem:** Two accepts possible; coach not verified for club.
- **Fix:** Transaction + lock application row; validate coach club membership.
- **Acceptance criteria:**
  - [x] Only one accepted coach per session
  - [x] Coach must belong to session's club

---

### BUG-019 — UI status enums mismatch database
- **Priority:** P1 | **Difficulty:** Easy | **Area:** Filament / Player UI
- **Files:** `app/Filament/Player/Pages/MyMatches.php`, `MyTraining.php`, booking migration
- **Problem:** UI queries `completed`/`ongoing`; DB has `confirmed`/`scheduled`/`active`.
- **Fix:** Align constants or migrate enum values.
- **Acceptance criteria:**
  - [x] Player dashboards show correct upcoming/past/cancelled sessions

---

### BUG-020 — Package assign lists all players globally
- **Priority:** P1 | **Difficulty:** Easy | **Area:** Filament Admin
- **Files:** `app/Filament/Resources/Packages/RelationManagers/SubscribersRelationManager.php`
- **Fix:** Scope `User` query to package's `club_id` members.
- **Acceptance criteria:**
  - [x] Only club-linked players selectable

---

## P2 — Medium bugs

| ID | Title | Difficulty | Files (primary) |
|----|-------|------------|-----------------|
| BUG-021 | `schedule` court slot race (duplicate sessions) | Medium | `CourtSlotController.php` | [x] |
| BUG-022 | Overnight court slots rejected by validation | Medium | `CourtSlotController.php`, forms | [x] |
| BUG-023 | `AvailabilityController` — any auth user reads any club | Easy | `AvailabilityController.php` | [x] |
| BUG-024 | Mid-night-spanning bookings missed by `whereDate` | Medium | `AvailabilityController.php` | [x] |
| BUG-025 | `renew()` creates duplicate active SaaS rows | Medium | `ClubRegistrationController.php` | [x] |
| BUG-026 | Client-controlled `coach_fee` / pricing | Medium | `BookingController.php` | [x] |
| BUG-027 | Admin enroll skips payment when `price_per_player > 0` | Medium | `AcademySessionController.php` | [x] |
| BUG-028 | `PaymentController` allows pay on cancelled bookings | Easy | `PaymentController.php` | [x] |
| BUG-029 | Staff default password `password123` in API | Easy | `ClubStaffController.php` | [x] |
| BUG-030 | Notifications implement `ShouldQueue` but queue may be unset | Easy | `app/Notifications/*`, `.env` | [x] |
| BUG-031 | Webhook ignores `is_refunded` / `is_voided` | Medium | `WebhookController.php` | [x] |
| BUG-032 | `CourtSlot` `slot_type` vs `session_type` mismatch | Easy | `CourtSlotController.php` | [x] |
| BUG-033 | `participant_ids` not checked for schedule conflicts | Medium | `BookingController.php` | [x] |
| BUG-034 | `update` coach without club validation | Easy | `BookingController.php` | [x] |
| BUG-035 | HMAC secret empty → all webhooks rejected silently | Easy | `WebhookController.php`, docs | [x] |

---

## P3 — Low severity bugs

| ID | Title | Difficulty |
|----|-------|------------|
| BUG-036 | `ClubController::destroy` returns JSON `[]` with 204 | Easy | [x] |
| BUG-037 | `userBookings` / `mySessions` `type` query unvalidated | Easy | [x] |
| BUG-038 | `publicIndex` shows sessions from yesterday unnecessarily | Easy | [x] |
| BUG-039 | `sportRules` endpoint public with no rate limit | Easy | [x] |
| BUG-040 | `ClubRegistration` subscription dates start before approval | Easy | [x] |
| BUG-041 | No duplicate club registration guard per user | Easy | [x] |
| BUG-042 | Paymob billing `phone_number` hardcoded `NA` | Easy | [x] |

---

# Part B — Missing flows (features not built)

## P0 / P1 — Critical missing flows

### FLOW-001 — Password reset (API + optional web)
- **Priority:** P1 | **Difficulty:** Medium | **Area:** API / Auth
- **Problem:** `password_reset_tokens` table exists; no routes.
- **Deliverables:**
  - [ ] `POST /api/forgot-password` (email)
  - [ ] `POST /api/reset-password` (token, password)
  - [ ] `POST /api/user/password` (authenticated change)
  - [ ] Feature tests
- **Files to add:** routes in `api.php`, controller methods, notifications

---

### FLOW-002 — Cancellation + refund policy
- **Priority:** P1 | **Difficulty:** Hard | **Area:** Payments / API / UI
- **Problem:** Cancel/leave/detach do not reverse Paymob or pivot payment state.
- **Deliverables:**
  - [ ] `POST /api/bookings/{id}/cancel` with rules (time window, fees)
  - [ ] Paymob refund API in `PaymobService` (or manual admin mark)
  - [x] Webhook handling for `is_refunded`
  - [ ] Player `MyMatches` / admin booking actions wired
- **Acceptance criteria:**
  - [ ] Cancelled booking releases court; participants notified
  - [ ] Refund recorded in `payment_transactions`

---

### FLOW-003 — Email verification (optional but recommended)
- **Priority:** P2 | **Difficulty:** Medium | **Area:** Auth
- **Problem:** `MustVerifyEmail` commented out; register issues token immediately.
- **Deliverables:**
  - [ ] Enable `MustVerifyEmail` on `User` (configurable)
  - [ ] `GET /api/email/verify/{id}/{hash}` + resend
  - [ ] Block sensitive actions until verified (optional)

---

### FLOW-004 — Academy session cancel / update API
- **Priority:** P1 | **Difficulty:** Medium | **Area:** API
- **Problem:** Only create, list, show, enroll — no update/cancel.
- **Deliverables:**
  - [ ] `PUT/PATCH /api/academy-sessions/{id}` (staff)
  - [ ] `POST /api/academy-sessions/{id}/cancel` with enrolled player notify
  - [ ] Filament actions mirror API

---

### FLOW-005 — Package purchase API + consumption
- **Priority:** P1 | **Difficulty:** Hard | **Area:** API / Domain
- **Problem:** Packages only in admin Filament; `sessions_remaining` never decrements.
- **Deliverables:**
  - [ ] `GET /api/clubs/{club}/packages`, `POST subscribe`, Paymob pay
  - [ ] Decrement sessions on booking/session attendance
  - [ ] Scheduled `packages:expire` command
  - [ ] `PackageExpiringNotification`

---

### FLOW-006 — SaaS club registration payment (web + API)
- **Priority:** P1 | **Difficulty:** Hard | **Area:** Payments / Web
- **Problem:** Web registration creates pending sub without Paymob; renew trusts reference string.
- **Deliverables:**
  - [ ] Paymob checkout on register-academy (or invoice later)
  - [ ] Webhook activates subscription + club on payment

---

### FLOW-007 — Booking reminder notifications
- **Priority:** P2 | **Difficulty:** Medium | **Area:** Notifications / Schedule
- **Deliverables:**
  - [ ] `booking:remind` command (24h / 2h before `start_time`)
  - [ ] Mail + optional database notification channel

---

### FLOW-008 — Coach application notifications
- **Priority:** P2 | **Difficulty:** Easy | **Area:** Notifications
- **Deliverables:**
  - [ ] Notify club managers on apply
  - [ ] Notify coach on accept/reject
- **Files:** `CoachApplicationController.php`, new notification classes

---

### FLOW-009 — Club registration status emails
- **Priority:** P2 | **Difficulty:** Easy | **Area:** Notifications
- **Deliverables:**
  - [ ] Email on pending submission (owner)
  - [ ] Fix `AcademyStatusNotification` + owner lookup (see BUG-003)

---

### FLOW-010 — Open-match discovery in Player panel
- **Priority:** P1 | **Difficulty:** Hard | **Area:** Player UI / API
- **Problem:** API has `GET /matches/open` and `POST join`; player panel only lists own bookings.
- **Deliverables:**
  - [ ] Browse/filter open matches page
  - [ ] Join → Paymob iframe or redirect
  - [ ] Skill/sport/club filters

---

### FLOW-011 — Court booking flow in Player panel
- **Priority:** P1 | **Difficulty:** Hard | **Area:** Player UI
- **Deliverables:**
  - [ ] Club/court picker, availability calendar
  - [ ] Create private/open booking
  - [ ] Payment for owner share

---

### FLOW-012 — Academy browse + enroll in Player panel
- **Priority:** P1 | **Difficulty:** Medium | **Area:** Player UI
- **Deliverables:**
  - [ ] Public sessions list (approved clubs)
  - [ ] Enroll + Paymob when fee > 0

---

### FLOW-013 — Coach apply to sessions in Coach panel
- **Priority:** P1 | **Difficulty:** Medium | **Area:** Coach UI
- **Deliverables:**
  - [ ] List open sessions needing coach
  - [ ] Apply / withdraw
  - [ ] Application status column

---

### FLOW-014 — Admin: coach applications Filament resource
- **Priority:** P1 | **Difficulty:** Medium | **Area:** Admin UI
- **Deliverables:**
  - [ ] `CoachApplicationResource` with approve/reject actions
  - [ ] Link from `AcademySession` view

---

### FLOW-015 — Admin: payment transactions resource
- **Priority:** P1 | **Difficulty:** Medium | **Area:** Admin UI
- **Deliverables:**
  - [ ] Read-only `PaymentTransactionResource` with filters (status, booking, user)
  - [ ] Link from booking infolist

---

### FLOW-016 — Admin: club staff management UI
- **Priority:** P1 | **Difficulty:** Medium | **Area:** Admin UI
- **Deliverables:**
  - [ ] Relation manager on `Club` for `club_users` (role: owner/manager/staff)
  - [ ] Invite user by email or select existing

---

### FLOW-017 — Court slot → generate academy sessions (admin)
- **Priority:** P2 | **Difficulty:** Medium | **Area:** Admin UI
- **Deliverables:**
  - [ ] Filament bulk action on `CourtSlot` calling `CourtSlotController::schedule` logic

---

### FLOW-018 — API documentation (OpenAPI / Scribe)
- **Priority:** P2 | **Difficulty:** Medium | **Area:** DX
- **Deliverables:**
  - [ ] Install Scribe or L5-Swagger; document auth + main resources

---

### FLOW-019 — Rate limiting on auth + webhook
- **Priority:** P2 | **Difficulty:** Easy | **Area:** Security
- **Deliverables:**
  - [ ] `throttle` on login/register/forgot-password
  - [ ] Optional IP allowlist middleware for Paymob webhook

---

### FLOW-020 — `.env.example` with all required keys
- **Priority:** P2 | **Difficulty:** Easy | **Area:** DX
- **Deliverables:**
  - [ ] Document Paymob, DB, queue, mail vars

---

# Part C — Enhancements (non-bug improvements)

## P1 — High value enhancements

| ID | Title | Difficulty | Area |
|----|-------|------------|------|
| ENH-001 | Laravel Policies instead of inline `abort_unless` | Hard | Architecture |
| ENH-002 | Extract `BookingPaymentService` / `EnrollmentPaymentService` | Hard | Services |
| ENH-003 | Server-side price calculation (court hourly × duration + coach fee) | Medium | API |
| ENH-004 | Composite DB indexes on `bookings`, `club_saas_subscriptions` | Easy | DB |
| ENH-005 | `withoutOverlapping()` on scheduled commands | Easy | Console |
| ENH-006 | Idempotency key header for Paymob pay endpoints | Medium | API |
| ENH-007 | Expand test suite: webhook, join, registration, expire command | Medium | Tests |
| ENH-008 | Multi-club context switcher in admin panel | Hard | Filament |
| ENH-009 | Club-scoped global query scope for admin resources | Hard | Filament |
| ENH-010 | Queue all notifications; document `composer dev` runs queue | Easy | Ops |

## P2 — Medium enhancements

| ID | Title | Difficulty |
|----|-------|------------|
| ENH-011 | Booking participants relation manager (payment_status, amounts) | Medium |
| ENH-012 | Academy session enrolled players relation manager | Medium |
| ENH-013 | Admin table filters (bookings, courts, users, packages, sessions, slots) | Medium |
| ENH-014 | Custom admin dashboard widgets (today's bookings, revenue) | Medium |
| ENH-015 | SaaS dashboard: click-through to expiring subscriptions list | Easy |
| ENH-016 | SaaS user resource: enable view/edit | Medium |
| ENH-017 | Align `subscription_status` enums (admin vs SaaS: paused/inactive) | Easy |
| ENH-018 | API versioning prefix `/api/v1` | Medium |
| ENH-019 | Audit log for SaaS approve/reject and payment events | Hard |
| ENH-020 | Localisation (AR/EN) for player-facing strings | Hard |

## P3 — Low enhancements

| ID | Title | Difficulty |
|----|-------|------------|
| ENH-021 | Remove `FilamentInfoWidget` from production admin | Easy |
| ENH-022 | Shared logo + favicon across 4 panels + web | Easy |
| ENH-023 | Unified Filament theme (single primary + CSS variables) | Medium |
| ENH-024 | Replace `welcome.blade.php` or remove dead route | Easy |
| ENH-025 | README: replace Laravel default with project setup | Easy |

---

# Part D — UI/UX improvements

## P1 — High priority UX

### UX-001 — Player panel: payments (Paymob)
- **Priority:** P1 | **Difficulty:** Hard
- **Problem:** API supports pay; player UI has no checkout.
- **Pages:** `MyMatches`, `MyTraining`, new `MyPayments` optional
- **Acceptance criteria:**
  - [ ] Pay outstanding booking share in browser
  - [ ] Complete enrollment payment from training browse

---

### UX-002 — Public site navigation to all portals
- **Priority:** P1 | **Difficulty:** Easy
- **File:** `resources/views/web/layout.blade.php`
- **Fix:** Footer or nav links: Player login, Coach login, Academy admin, SaaS (or combined "Login" dropdown).
- **Acceptance criteria:**
  - [ ] New user can find `/player/login` without docs

---

### UX-003 — Coach dashboard: remove/fix broken admin link
- **Priority:** P1 | **Difficulty:** Easy
- **File:** `resources/views/filament/coach/pages/coach-dashboard.blade.php`
- **Problem:** "Manage Sessions (Admin)" → 403 for most coaches.
- **Fix:** Show only if `auth()->user()->hasAdminAccess()`; else hide.

---

### UX-004 — Currency consistency (EGP everywhere)
- **Priority:** P1 | **Difficulty:** Easy
- **Files:** `MyMatches` blade, `CoachMatches.php`, `SaasStatsOverview.php`, config `app.currency`
- **Problem:** `$` vs `EGP` mixed.
- **Fix:** Central `format_money()` helper; use Paymob currency from config.

---

### UX-005 — Player/coach: browse & join (see FLOW-010–012)
- **Priority:** P1 | **Difficulty:** Hard
- (Cross-reference FLOW items)

---

### UX-006 — Modal accessibility + XSS fix (player training)
- **Priority:** P1 | **Difficulty:** Medium
- **File:** `resources/views/filament/player/pages/my-training.blade.php`
- **Problems:** No `role="dialog"`, focus trap; `innerHTML` for `session_plan`/`notes`.
- **Fix:** Filament modal or Livewire; escape user content (`{{ }}` / `@js`).

---

### UX-007 — Replace `confirm()` with Filament action modals
- **Priority:** P2 | **Difficulty:** Easy
- **Files:** `my-matches.blade.php`, `my-training.blade.php`
- **Fix:** Cancel/leave/withdraw use Filament Actions with confirmation.

---

## P2 — Medium priority UX

| ID | Title | Difficulty | Area |
|----|-------|------------|------|
| UX-008 | Player profile edit page (skill, sport, phone, DOB) | Medium | Player |
| UX-009 | Player: filters on packages (active/expired), training (upcoming/past) | Easy | Player |
| UX-010 | Player dashboard: remove hardcoded "Padel" if `preferred_sport` set | Easy | Player |
| UX-011 | Coach dashboard: upcoming sessions/matches widgets | Medium | Coach |
| UX-012 | Coach sessions: row actions (view players, session plan, videos) | Medium | Coach |
| UX-013 | Coach tables: date range + club filters | Easy | Coach |
| UX-014 | Rename coach nav "My Training" → "My Sessions" (avoid player confusion) | Easy | Coach |
| UX-015 | Admin booking infolist: show payment_status per participant | Easy | Admin |
| UX-016 | Landing page: align marketing copy with actual features | Easy | Web |
| UX-017 | Registration pending page: expected timeline + contact | Easy | Web |
| UX-018 | Booking confirmation email: deep link to `/player` booking | Easy | Notifications |

## P3 — Low priority UX

| ID | Title | Difficulty |
|----|-------|------------|
| UX-019 | Shared brand logo on all Filament panels | Easy |
| UX-020 | Player cards: use Filament components vs inline CSS | Medium |
| UX-021 | Empty states: consistent illustration/copy | Easy |
| UX-022 | Video modal: `title` on iframe for a11y | Easy |
| UX-023 | SaaS plans table: filter by active | Easy |
| UX-024 | Admin clubs: show `registration_status` + approve (or link to SaaS) | Medium |
| UX-025 | Dark mode parity on custom player blades | Medium |

---

# Part E — Suggested implementation waves (for AI planners)

### Wave 1 — Stop the bleeding (1–3 days)
`BUG-001` → `BUG-003` → `BUG-006` → `BUG-002` → `BUG-005` → `BUG-004` → `BUG-015`

### Wave 2 — Money & trust (3–7 days)
`BUG-007` → `BUG-008` → `BUG-011` → `BUG-012` → `BUG-013` → `FLOW-002` (partial) → `UX-004`

### Wave 3 — Player product (1–2 weeks)
`FLOW-010` → `FLOW-011` → `FLOW-012` → `UX-001` → `UX-002` → `UX-006` → `BUG-019`

### Wave 4 — Admin completeness (1 week)
`FLOW-014` → `FLOW-015` → `FLOW-016` → `ENH-011` → `ENH-012` → `ENH-013`

### Wave 5 — Auth, packages, polish (ongoing)
`FLOW-001` → `FLOW-003` → `FLOW-005` → `FLOW-018` → `ENH-007` → `ENH-025`

---

# Part F — Test coverage gaps (add with each fix)

| Area | Suggested test class | Related IDs |
|------|---------------------|-------------|
| Paymob webhook HMAC + booking pay | `WebhookTest` | BUG-001, 011, 031 |
| Match join + capacity | `MatchmakingTest` | BUG-013, 012 |
| Booking overlap | `BookingConcurrencyTest` | BUG-004 |
| SaaS expire command | `ExpireSaasSubscriptionsTest` | BUG-002 |
| Club registration | `ClubRegistrationTest` | BUG-007, 008 |
| Auth password reset | `PasswordResetTest` | FLOW-001 |
| Academy enroll capacity | `AcademyEnrollmentTest` | BUG-010 |

---

# Completion checklist (update when done)

```
P0 Bugs:     [x] BUG-001–008 (all complete) [ ] BUG-006 [ ] BUG-007 [ ] BUG-008
P1 Flows:    [ ] FLOW-001 [ ] FLOW-002 [ ] FLOW-010 [ ] FLOW-011 [ ] FLOW-012 [ ] FLOW-014 [ ] FLOW-015 [ ] FLOW-016
P1 UX:       [ ] UX-001 [ ] UX-002 [ ] UX-003 [ ] UX-004 [ ] UX-006
```

---

*Generated from full codebase audit. When implementing, prefer fixing P0 bugs before new features. Link PRs to IDs (e.g. `fix(api): BUG-001 session payment FK`).*
