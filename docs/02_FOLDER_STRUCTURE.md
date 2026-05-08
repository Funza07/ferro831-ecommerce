# FERRO831 Folder Structure

## 1. Top-Level Structure

| Path | Purpose |
|---|---|
| `admin/` | Legacy admin wrapper routes (`admin/*.php`) |
| `app/` | MVC app code: controllers, models, views, helpers |
| `assets/` | Static assets (CSS, JS, images, product images) |
| `config/` | Environment config (`db.php`) |
| `core/` | Router/core base classes (`App`, `Controller`, `Model`) |
| `includes/` | Legacy include partials |
| `public/` | Front controller (`public/index.php`) |
| `user/` | Legacy user route wrappers (`user/orders.php`, etc.) |
| `*.php` (root) | Legacy customer route wrappers (`products.php`, `cart.php`, etc.) |
| `docs/` | Project documentation (this folder) |

## 2. `app/` Breakdown

| Path | Purpose |
|---|---|
| `app/bootstrap.php` | Session bootstrapping, autoload registration, DB/helper include |
| `app/controllers/` | Web request handlers |
| `app/models/` | DB access and core business logic |
| `app/views/` | UI templates |
| `app/helpers/functions.php` | URL, redirect, auth helpers, flash helpers |

### Controllers currently present
- `AdminController.php`
- `AuthController.php`
- `CartController.php`
- `CheckoutController.php`
- `HomeController.php`
- `ProductController.php`
- `UserController.php`
- `WishlistController.php`

### Models currently present
- `Address.php`
- `Cart.php`
- `Order.php`
- `Product.php`
- `User.php`
- `Wishlist.php`

### Views currently present
- `views/admin/*`
- `views/auth/*`
- `views/cart/*`
- `views/checkout/*`
- `views/home/*`
- `views/layouts/*`
- `views/orders/*`
- `views/products/*`
- `views/user/*`

## 3. Legacy Wrapper Strategy
The project keeps old `.php` URLs as wrappers that call the matching controller action directly.

Examples:
- `products.php` -> `ProductController::index()`
- `product.php` -> `ProductController::show()`
- `cart.php` -> `CartController` action switch
- `admin/products.php` -> `AdminController::products()`

This keeps backward compatibility for existing links/bookmarks while the MVC app structure is used internally.

## 4. Assets Layout

| Path | Purpose |
|---|---|
| `assets/css/style.css` | Customer-facing styles |
| `assets/css/admin.css` | Admin styles |
| `assets/js/main.js` | Frontend behavior |
| `assets/images/` | General images |
| `assets/images/products/` | Uploaded product gallery images |
