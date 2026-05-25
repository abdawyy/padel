# Padel Platform

Multi-tenant padel / racket sports club management: Laravel API, Paymob payments (EGP), Filament admin (`/admin`), SaaS (`/saas`), player (`/player`), and coach (`/coach`) portals.

## Requirements

- PHP 8.3+
- Composer
- MySQL (or SQLite for local tests)
- Node.js (front-end assets)

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
npm install && npm run build
```

Configure Paymob, database, and mail in `.env` (see `.env.example`).

## Development

`composer dev` runs four processes in parallel:

| Process | Role |
|---------|------|
| `php artisan serve` | HTTP server |
| `php artisan queue:listen` | **Processes queued notifications and jobs** |
| `php artisan pail` | Log tail |
| `npm run dev` | Vite assets |

All application notifications implement `ShouldQueue` — use the queue worker above in development and a persistent worker (Supervisor, Horizon, etc.) in production.

## API

See [docs/API.md](docs/API.md). Pay endpoints accept optional `X-Idempotency-Key` to safely retry checkout session creation.

## Admin panel

Academy admins can switch the active club from the top bar (session `admin_club_id`). Resources scoped to `club_id` filter to that club; leave unset to see all accessible clubs.

## Tests

```bash
php artisan test
```
