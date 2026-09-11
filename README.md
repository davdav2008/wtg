# Property Booking REST API

A Laravel REST API for importing accommodation offers, finding the cheapest
available offer per property, and booking offers safely.

**Repository:** https://github.com/davdav2008/wtg

## Tech Stack

- PHP 8.2+
- Laravel 11/12
- MySQL 8+
- Queue driver: `database` or Redis

## Installation and Setup

1. Clone the repository.
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy `.env.example` to `.env` and configure the database connection:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Run migrations and seeders (creates the `supplier-a` and `supplier-b` suppliers):
   ```bash
   php artisan migrate --seed
   ```
5. Start the queue worker to process imports:
   ```bash
   php artisan queue:work
   ```
6. (Optional) Start the built-in server:
   ```bash
   php artisan serve
   ```

## API Endpoints

- `POST   /api/imports` — accept an import, returns `202 Accepted`
- `GET    /api/imports/{import}` — current state of an import
- `GET    /api/properties` — search the cheapest current offer per property
- `POST   /api/offers/{offer}/reservations` — create a reservation

## Testing

```bash
php artisan test
```

Feature tests cover imports, property search, and reservations.

## Implementation Details

### Import Idempotency

Idempotency is enforced at several levels:

1. **Database.** The `imports` table has a unique index on
   `(supplier_id, external_import_id)`, and the `offers` table has a unique
   index on `(supplier_id, external_id)`.
2. **Action (`CreateImportAction`).** Before creating a new import record,
   `firstOrCreate` is called on `(supplier_id, external_import_id)`. If the
   import already exists, the job is not dispatched again, and the client
   receives the same `Import` record with its current status (e.g.
   `completed`).
3. **Job (`ProcessImportJob`).** Each offer is upserted using
   `firstOrNew` + `fill` + `save` keyed on `(supplier_id, external_id)`.
   Re-submitting an import therefore updates existing offers instead of
   creating duplicates.

### Double Booking Protection

To prevent two concurrent requests from booking the last available unit,
`CreateReservationAction::execute()` performs an atomic statement
(via `decrement`):

```sql
UPDATE offers
SET available_units = available_units - 1
WHERE id = ? AND available_units > 0
```

This guarantees `available_units` never becomes negative and only one
process can successfully decrement the counter when a single unit remains.
The entire reservation creation flow runs inside a database transaction.

### Cheapest Offer Search

The search runs as a single SQL query using a window function:

```sql
ROW_NUMBER() OVER (PARTITION BY p.id ORDER BY o.price ASC, o.id ASC)
```

This returns only one (the cheapest) current offer per property directly
in MySQL, without grouping collections in PHP. Pagination is performed at
the database level.
