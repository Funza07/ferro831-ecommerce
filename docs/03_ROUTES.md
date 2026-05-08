# FERRO831 Routes Documentation

## 1. Router-Based Routes (`core/App.php`)

## GET Routes

| Method | Path | Controller::Action | Notes |
|---|---|---|---|
| GET | `/` | `HomeController::index` | Homepage |
| GET | `/home` | `HomeController::index` | Alias |
| GET | `/products` | `ProductController::index` | Product listing |
| GET | `/product` | `ProductController::show` | Expects query `id` |
| GET | `/cart` | `CartController::index` | Cart page |
| GET | `/checkout` | `CheckoutController::index` | Checkout page |
| GET | `/login` | `AuthController::login` | User login page |
| GET | `/register` | `AuthController::register` | User register page |
| GET | `/logout` | `AuthController::logout` | Logout |
| GET | `/wishlist` | `WishlistController::index` | Requires login |
| GET | `/profile` | `UserController::profile` | Requires login |
| GET | `/addresses` | `UserController::addresses` | Requires login |
| GET | `/add-address` | `UserController::addAddress` | Requires login |
| GET | `/edit-address` | `UserController::editAddress` | Requires login |
| GET | `/delete-address` | `UserController::deleteAddress` | Requires login |
| GET | `/set-default-address` | `UserController::setDefaultAddress` | Requires login |
| GET | `/user/orders` | `UserController::orders` | Requires login |
| GET | `/user/view-order` | `UserController::viewOrder` | Requires login |
| GET | `/admin/login` | `AuthController::adminLogin` | Admin login page |
| GET | `/admin/dashboard` | `AdminController::dashboard` | Requires admin |
| GET | `/admin/add-product` | `AdminController::addProduct` | Requires admin |
| GET | `/admin/orders` | `AdminController::orders` | Requires admin |
| GET | `/admin/view-order` | `AdminController::viewOrder` | Requires admin |

## POST Routes

| Method | Path | Controller::Action | Notes |
|---|---|---|---|
| POST | `/login` | `AuthController::authenticate` | User login submit |
| POST | `/register` | `AuthController::storeUser` | User register submit |
| POST | `/cart` | `CartController::update` | Update cart quantities |
| POST | `/order-success` | `CheckoutController::placeOrder` | Places order |
| POST | `/add-to-wishlist` | `WishlistController::add` | Requires login |
| POST | `/remove-wishlist` | `WishlistController::remove` | Requires login |
| POST | `/move-wishlist-to-cart` | `WishlistController::moveToCart` | Requires login |
| POST | `/profile` | `UserController::updateProfile` | Requires login |
| POST | `/add-address` | `UserController::storeAddress` | Requires login |
| POST | `/edit-address` | `UserController::updateAddress` | Requires login |
| POST | `/delete-address` | `UserController::deleteAddress` | Requires login |
| POST | `/set-default-address` | `UserController::setDefaultAddress` | Requires login |
| POST | `/admin/login` | `AuthController::adminAuthenticate` | Admin login submit |
| POST | `/admin/view-order` | `AdminController::updateOrderStatus` | Admin status update |
| POST | `/admin/add-product` | `AdminController::storeProduct` | Add product submit |

## Special Route Logic
- `POST /cart`:
  - if `add_to_cart` is set -> `CartController::add`
  - otherwise -> `CartController::update`
- `GET /cart` with `?remove=...` -> `CartController::remove`

## 2. Legacy Wrapper Routes (Direct `.php` access)

These wrappers are still active and important for backward compatibility.

| URL | Action |
|---|---|
| `/index.php` | `HomeController::index()` |
| `/products.php` | `ProductController::index()` |
| `/product.php` | `ProductController::show()` |
| `/cart.php` | add/update/remove/index switch in wrapper |
| `/checkout.php` | `CheckoutController::index()` |
| `/order-success.php` | `CheckoutController::placeOrder()` |
| `/login.php`, `/register.php`, `/logout.php` | auth actions |
| `/wishlist.php` | wishlist page |
| `/add-to-wishlist.php`, `/remove-wishlist.php`, `/move-wishlist-to-cart.php` | wishlist actions |
| `/profile.php` | profile view/update switch |
| `/addresses.php`, `/add-address.php`, `/edit-address.php`, `/delete-address.php`, `/set-default-address.php` | address management |
| `/user/orders.php`, `/user/view-order.php` | user orders |
| `/admin/*.php` | admin wrapper actions (dashboard, products, variants, orders, view-order, add-product, login) |

## 3. Base Path Handling
`App::resolvePath()` strips one of these prefixes before route matching:
- `/ferro831/public`
- `/ferro831`
- `/public`

It also normalizes `.php` URLs to route paths.
