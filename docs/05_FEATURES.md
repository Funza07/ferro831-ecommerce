# FERRO831 Features Documentation

## 1. Customer Features

| Feature | Status | Implementation Notes |
|---|---|---|
| Homepage | Active | Product feed with filter/sort/search |
| Product listing | Active | Category + price range + sorting |
| Product detail | Active | Gallery images, stock state, variant options |
| Variant/SKU support | Active | Product variants + optional price override |
| Cart | Active | Session-based cart with product:variant cart key |
| Checkout | Active | Supports guest + logged-in flow with saved addresses |
| Wishlist | Active | Login-gated add/remove/move-to-cart |
| Login/Register | Active | Role-aware redirects |
| Profile | Active | Update name/phone/address |
| Address book | Active | Add/edit/delete/default address |
| User orders | Active | Order list and detailed status history |

## 2. Admin Features

| Feature | Status | Implementation Notes |
|---|---|---|
| Dashboard | Active | Product + order metrics and summaries |
| Products/inventory page | Active | Variant-aware stock summaries |
| Add product | Active | Multi-image upload + optional variant rows |
| Manage variants | Active | Add/update/toggle/delete-if-safe |
| Orders list | Active | All orders with latest state |
| Order status updates | Active | Status + tracking + note -> history table |

## 3. Inventory/Variant Logic
- If product has active variants, cart/checkout uses variant stock.
- Variant selection is required when active variants exist.
- Admin actions call stock sync methods to keep parent product stock aligned.
- Variant delete is safety-aware; if order history exists, variant can be deactivated instead of hard delete.

## 4. Cart and Checkout Behavior
- Cart lives in `$_SESSION['cart']`.
- Cart key format supports variant granularity (`product_id:variant_id`).
- Quantity is auto-clamped to available stock.
- Checkout order placement writes:
  - `orders` row
  - `order_items` rows with snapshot fields
  - `order_status_history` initial event

## 5. Auth and Access Control
- `requireLogin()` protects user-only actions.
- `requireAdmin()` protects admin actions.
- `users.role` supports `user` and `admin`.

## 6. Compatibility Pattern
- Legacy URLs (e.g., `/cart.php`, `/admin/orders.php`) remain functional.
- Router supports clean paths and normalizes `.php` paths.
