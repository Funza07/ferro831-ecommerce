# FERRO831 Database Documentation

## 1. Database Info
- Name: `ferro831_db`
- Source reviewed: `local development database export`
- Engine style: InnoDB + foreign keys

## 2. Core Tables

| Table | Purpose |
|---|---|
| `products` | Parent product/design records |
| `product_images` | Product gallery images |
| `product_variants` | Sellable SKU variants (size/color/stock/sku) |
| `users` | User/admin accounts |
| `user_addresses` | Saved delivery addresses |
| `wishlist` | User-product wish mapping |
| `orders` | Order header/customer summary |
| `order_items` | Per-item order snapshot |
| `order_status_history` | Audit trail of status updates |

## 3. Key Table Design Notes

### `products`
- Parent-level data: name, category, base price, description, primary image, aggregated stock.
- Contains search indexes and fulltext index over `name, category, description`.

### `product_variants`
- Real sellable SKUs for size/color combinations.
- Fields include: `size`, `color`, `color_hex`, `sku`, `price_override`, `stock`, `is_active`.
- Constraint: unique (`product_id`, `size`, `color`).

### `order_items`
- Stores transactional snapshot at purchase time:
  - `product_name`
  - `size`
  - `color`
  - `sku`
  - `product_price`
  - `quantity`
  - `total_price`
- Prevents old orders from changing when product catalog changes later.

## 4. Relationships

| From | To | Type | On Delete |
|---|---|---|---|
| `orders.user_id` | `users.id` | FK | `SET NULL` |
| `order_items.order_id` | `orders.id` | FK | default (restrict/no action) |
| `order_status_history.order_id` | `orders.id` | FK | `CASCADE` |
| `product_images.product_id` | `products.id` | FK | `CASCADE` |
| `product_variants.product_id` | `products.id` | FK | `CASCADE` |
| `user_addresses.user_id` | `users.id` | FK | `CASCADE` |
| `wishlist.user_id` | `users.id` | FK | `CASCADE` |
| `wishlist.product_id` | `products.id` | FK | `CASCADE` |

## 5. Business Rules to Preserve
- `products` is parent design; `product_variants` is sellable unit.
- Parent `products.stock` should always represent sum of active variant stock when variants exist.
- Order placement must write snapshot fields to `order_items`.
- `wishlist` has unique (`user_id`, `product_id`) to prevent duplicates.

## 6. Index/Constraint Highlights
- PK on all tables (`id`).
- `users.email` unique.
- `wishlist(user_id, product_id)` unique.
- `product_variants(product_id, size, color)` unique.
- Product indexes on category, price, created_at + fulltext search index.

## 7. Local Credentials Warning
Current local config from `config/db.php`:
- host: `localhost`
- user: `root`
- password: empty
- database: `ferro831_db`

Must be replaced before Hostinger deployment.
