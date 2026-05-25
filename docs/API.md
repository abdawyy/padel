# Padel API Reference

Base URL: `{APP_URL}/api`

Authentication: Laravel Sanctum (`Authorization: Bearer {token}`) or cookie session for SPA domains listed in `SANCTUM_STATEFUL_DOMAINS`.

Email verification: when `REQUIRE_EMAIL_VERIFICATION=true`, protected routes require a verified email (`verified.api` middleware).

Rate limits: login/register `10/min`; forgot/reset password `5/min`; sport rules `30/min`.

---

## Auth

| Method | Path | Description |
|--------|------|-------------|
| POST | `/register` | Register player/coach (throttled) |
| POST | `/login` | Login (throttled) |
| POST | `/logout` | Logout (auth) |
| POST | `/forgot-password` | Send reset link |
| POST | `/reset-password` | Reset password with token |
| GET | `/email/verify/{id}/{hash}` | Verify email (signed) |
| POST | `/email/verification-notification` | Resend verification |
| POST | `/user/password` | Change password (auth) |

---

## Player — courts & bookings

| Method | Path | Description |
|--------|------|-------------|
| GET | `/clubs/{club}/player-availability?date=YYYY-MM-DD` | Public court availability (approved clubs) |
| GET | `/user/bookings?type=upcoming\|past` | My bookings |
| POST | `/bookings` | Create booking |
| POST | `/bookings/{id}/cancel` | Cancel booking |
| POST | `/bookings/{id}/leave` | Leave open match |
| POST | `/bookings/{id}/pay` | Paymob session for participant share |

---

## Academy sessions

| Method | Path | Description |
|--------|------|-------------|
| GET | `/academy-sessions/public` | Upcoming public sessions |
| GET | `/user/academy-sessions` | My enrolled sessions |
| POST | `/academy-sessions/{id}/enroll` | Enroll (402 + Paymob when fee > 0) |
| PATCH | `/academy-sessions/{id}` | Update (club admin) |
| POST | `/academy-sessions/{id}/cancel` | Cancel session |

---

## Coach applications

| Method | Path | Description |
|--------|------|-------------|
| POST | `/academy-sessions/{id}/coach-apply` | Coach applies |
| DELETE | `/coach-applications/{id}` | Withdraw application |
| GET | `/academy-sessions/{id}/coach-applications` | List (club admin) |
| PATCH | `/coach-applications/{id}` | Accept/decline (club admin) |

---

## Clubs (admin)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/clubs/{club}/availability` | Admin availability calendar |
| GET/POST | `/clubs/{club}/court-slots` | Recurring slot templates |
| POST | `/clubs/{club}/court-slots/{slot}/schedule` | Generate academy session from slot |

---

## Packages & SaaS

| Method | Path | Description |
|--------|------|-------------|
| GET/POST | `/clubs/{club}/packages` | List/create packages |
| POST | `/clubs/{club}/packages/{id}/subscribe` | Subscribe (Paymob when price > 0) |
| POST | `/register-club` | SaaS club registration (402 when plan has price) |

---

## Webhooks

| Method | Path | Description |
|--------|------|-------------|
| POST | `/webhooks/paymob/transaction-processed` | Paymob HMAC webhook (optional `PAYMOB_WEBHOOK_ALLOWED_IPS`) |

---

## OpenAPI / Scribe

To generate interactive docs, install [Scribe](https://scribe.knuckles.wtf):

```bash
composer require --dev knuckleswtf/scribe
php artisan vendor:publish --tag=scribe-config
php artisan scribe:generate
```

Generated docs are served at `/docs` after configuration.
