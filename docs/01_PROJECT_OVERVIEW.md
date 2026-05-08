# FERRO831 Project Overview

## 1. Project Summary
FERRO831 is a custom PHP + MySQL MVC e-commerce application.

- Stack: PHP (custom MVC), MySQL/MariaDB, HTML/CSS/JS
- Local dev: XAMPP (`C:\xampp\htdocs\ferro831`)
- Deployment target: Hostinger shared hosting
- Entry points:
  - Front controller style: `public/index.php` -> `App::run()`
  - Legacy compatibility wrappers: root `*.php` files (for old URLs)

## 2. Current Functional Scope

### Customer side
- Homepage with product grid and filters
- Product listing and product detail
- Variant/SKU-aware add-to-cart
- Session-based cart
- Checkout and order placement
- Wishlist (logged-in users)
- Authentication (login/register/logout)
- Profile update
- Address book (add/edit/delete/default)
- User order history + order detail

### Admin side
- Admin login
- Dashboard metrics
- Product list / inventory summary
- Add product with multi-image upload
- Variant management (add/update/activate/deactivate/delete-if-safe)
- Order list and order detail
- Order status + tracking updates

## 3. MVC Architecture (Current)
- Controllers: `app/controllers`
- Models: `app/models`
- Views: `app/views`
- Core classes: `core/App.php`, `core/Controller.php`, `core/Model.php`
- Bootstrap: `app/bootstrap.php`

### Request flow
1. Request hits either `public/index.php` (router) or a legacy wrapper (`products.php`, `cart.php`, etc.).
2. Bootstrap starts session, loads DB config and helpers, registers autoloader.
3. Controller action runs business logic via model calls.
4. View renders through `layouts/main.php` (or no layout for admin pages where `layout = null`).

## 4. Important Domain Rules
- `products` table represents parent product/design.
- `product_variants` table represents sellable SKUs.
- Parent `products.stock` must stay synced with sum of active variant stock.
- `order_items` stores purchase-time snapshot (`size`, `color`, `sku`, `product_price`).
- Cart is stored in PHP session.
- Legacy `.php` URLs are intentionally preserved.

## 5. Environment Notes
- Current local DB config (`config/db.php`) uses:
  - host: `localhost`
  - user: `root`
  - password: empty
  - database: `ferro831_db`
- `BASE_URL` helper default is hardcoded as `/ferro831`.

## 6. Known Deployment Risks (High Priority)
- DB credentials are local defaults and must be changed for Hostinger.
- Several model methods use `mysqli_stmt_get_result` (possible compatibility issue depending on Hostinger PHP build).
- Hardcoded `/ferro831/` paths are used in redirects/routes and must be reviewed for production base path.
