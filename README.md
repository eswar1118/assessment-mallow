# Store Order & Inventory Mini-System

A small Laravel application for a retail counter to record customer orders against a product catalog and keep stock in sync. Orders are created through a JSON API, stock is deducted safely under concurrent requests, and a queued job simulates an order-confirmation email.

## Tech Stack

- PHP <version>, Laravel <version>
- SQLite (local dev and tests; MySQL also works by changing `.env`)
- Queue driver: `database`
- Tests: Pest / PHPUnit
- UI: Blade + vanilla JS

## Setup

```bash
git clone <repo-url>
cd store-order-system
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

In a second terminal, start the queue worker:

```bash
php artisan queue:work
```

Run the tests:

```bash
php artisan test
```

The UI is available at `http://127.0.0.1:8000`. Queued confirmation "emails" are written to `storage/logs/laravel.log`.

## Environment Variables

| Variable | Default | Purpose |
|---|---|---|
| `STORE_LOW_STOCK_THRESHOLD` | `10` | Products with stock below this value are "low stock" |
| `QUEUE_CONNECTION` | `database` | Queue driver |
| `DB_CONNECTION` | `sqlite` | Database driver |

## API Documentation

### 1. Create order

`POST /api/orders`

Request:
```json
{
  "customer": { "email": "thomas@example.com", "name": "Thomas" },
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 2, "quantity": 5 }
  ]
}
```

Response `201 Created`:
```json
{
  "data": {
    "id": 1,
    "customer": { "name": "Thomas", "email": "thomas@example.com" },
    "items": [ { "product": "Colgate Toothpaste", "quantity": 2, "unit_price": "50.00", "line_total": "108.00" } ],
    "subtotal": "210.00",
    "tax_total": "17.20",
    "grand_total": "227.20"
  }
}
```

Errors:
- `422` insufficient stock (response lists the product and the available quantity)
- `422` validation errors (empty items, quantity < 1, unknown product, invalid email)

### 2. Customer order history

`GET /api/customers/{email}/orders`

Returns the customer's orders (newest first) with items. Returns `404` if the customer does not exist.

### 3. Low-stock products

`GET /api/products/low-stock`

Returns products with `stock < threshold`. The threshold comes from `config('store.low_stock_threshold')` and can be overridden per request with `?threshold=20`.

## Database Schema

```
customers 1 ──< orders 1 ──< order_items >── 1 products
```

| Table | Key columns |
|---|---|
| `products` | name, code (unique), price, tax_percent, stock |
| `customers` | name, email (unique) |
| `orders` | customer_id, subtotal, tax_total, grand_total |
| `order_items` | order_id, product_id, quantity, unit_price, tax_percent, line_subtotal, line_tax, line_total |

`order_items` is the supporting table that makes the order-to-products relationship many-to-many with extra data.

## Assumptions & Design Decisions

1. **Customer handling:** The order endpoint accepts email and name. If the email exists, the existing customer is reused (name is not overwritten); otherwise a new customer is created.
2. **Price and tax snapshot:** `order_items` stores the unit price and tax percent at order time, so later product price changes never alter historical orders.
3. **Money:** Stored as `decimal(10,2)`. Tax is calculated per line (`price × qty × tax% / 100`) and rounded to 2 decimals, and order totals are the sum of the rounded line values.
4. **Duplicate lines:** If the same `product_id` appears more than once in a request, quantities are merged before the stock check.
5. **Low stock definition:** `stock < threshold` (strictly below). The threshold is configurable via env and overridable per request.
6. **Queue:** The confirmation job is dispatched only after the DB transaction commits, so a rolled-back order never triggers an email. "Sending" is simulated with a log entry.
7. **No authentication:** The brief did not mention it, so the API is open.
8. **Out of scope from the wireframe:** PDF bill emailing and the cash denomination breakdown for "balance to return". The UI shows the balance amount only.
9. **Customer name conflicts:** If an existing email is sent with a different name, the stored name is kept.

## Concurrency Handling

Order creation runs inside `DB::transaction()`. Inside it, the requested products are read with `lockForUpdate()` (pessimistic row locks), sorted by id to keep the lock order consistent and avoid deadlocks.

If two requests compete for the last unit, the second waits for the first transaction to finish. When it gets the lock it reads the fresh stock (0), fails the check, and returns a clean `422`. Stock can never go negative and the order is rolled back entirely.

**Testing note:** True parallel requests are hard to reproduce in a single-process PHPUnit run (and SQLite serializes writes anyway). The tests verify the check-and-deduct logic with sequential requests for the last unit and rollback behavior. The locking itself relies on `lockForUpdate()`, which is effective on MySQL/PostgreSQL. <Add anything you manually verified, e.g. two parallel curl calls.>

## Testing

```bash
php artisan test
```

Covered cases:
- Happy path: totals, stock deduction, price snapshot
- Insufficient stock returns 422 and changes nothing
- Last unit: first order succeeds, second fails
- Multi-line order where one line fails rolls back everything
- Validation errors (empty items, qty 0, bad product, bad email)
- Duplicate line merging
- Existing vs new customer
- Job dispatched on success and not dispatched on failure
- Order history and 404 for unknown email
- Low-stock endpoint with config threshold and override
- Unit test for tax/rounding calculation

## AI Usage

I used Antigravity (<model>) to scaffold most of the code. Prompts are saved as screenshots in `/prompts`. I reviewed the generated code, <mention what you changed or fixed, e.g. "fixed the lock ordering", "added the rollback test">.

## Known Limitations / Future Improvements

- Authentication and authorization
- PDF invoice generation and emailing
- Pagination on order history
- A real multi-process concurrency test against MySQL
- Idempotency keys to prevent duplicate order submissions