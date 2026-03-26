# Short_slug

Short_slug is a Laravel 10 URL shortener API with:

- Authentication using Laravel Sanctum
- Link management (CRUD)
- Redirect tracking with rate limiting and idempotency
- Analytics endpoints
- Threshold-based webhook delivery with queue jobs

## Requirements

- PHP 8.1+
- Composer
- Node.js and npm (for Vite assets)
- A database supported by Laravel (MySQL/PostgreSQL/SQLite)

## Quick Start

1. Install backend dependencies:

   composer install

2. Create environment file:

   cp .env.example .env

3. Generate app key:

   php artisan key:generate

4. Configure database in `.env`.

5. Run migrations (and optional seeders):

   php artisan migrate
   php artisan db:seed

6. Install frontend dependencies (optional for API-only usage):

   npm install
   npm run build

7. Start the app:

   php artisan serve

8. Start queue worker (required for webhook jobs):

   php artisan queue:work

## Important Environment Variables

Add or verify these values in `.env`:

- `QUEUE_CONNECTION` (for queued webhook jobs)
- `WEBHOOK_TARGET_URL` (fallback destination for outgoing webhooks)
- `WEBHOOK_SECRET` (optional HMAC SHA-256 signature secret)
- `IS_ADMIN` (optional global admin mode fallback)

## Authentication

API auth uses Sanctum bearer tokens.

- POST `/api/register`
- POST `/api/login`
- POST `/api/logout` (auth required)

Use returned token as:

Authorization: Bearer YOUR_TOKEN

## Main API Endpoints

### Links (auth required)

- GET `/api/links`
- POST `/api/links`
- GET `/api/links/{id}`
- PUT/PATCH `/api/links/{id}`
- DELETE `/api/links/{id}`

Supported query params on listing include:

- `q` (search in slug/target URL)
- `is_active`
- `is_expired`
- `sort` (`created_at`, `clicks_count`, `expires_at`, and `-` prefix for desc)
- `per_page` (1 to 100)

### Redirect Tracking

- POST `/api/r/{slug}`

Behavior:

- Validates active/non-expired links
- Rate limits by IP + slug (3 requests/minute)
- Supports idempotency via `idempotency_key`
- Records click and updates `clicks_count`
- Dispatches webhook jobs when thresholds are crossed (10, 100, 1000)

### Analytics (auth required)

- GET `/api/links/{id}/stats`
- GET `/api/analytics/overview`

### Webhooks (auth required)

- GET `/api/webhooks`
- POST `/api/webhooks/{id}/retry`

## Web UI Routes

This project also has blade-based auth pages and a simple index flow:

- GET `/register`, POST `/register`
- GET `/login`, POST `/login`
- Authenticated routes: `/index`, `/reset_password`, `/logout`

## Postman

Use included files for quick API testing:

- `postman_collection.json`
- `postman_environment.json`

## Notes

- Admin access checks can come from `X-Is-Admin: 1` header or `IS_ADMIN=true`.
- Webhook job stores attempt status in `webhooks` table.
- If webhook target is missing, failed webhook records are still persisted for observability.
