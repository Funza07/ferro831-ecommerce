# FERRO831 Testing Checklist

## 1. Pre-Test Setup
- [ ] Import latest DB dump into local `ferro831_db`.
- [ ] Ensure XAMPP Apache + MySQL are running.
- [ ] Verify app loads at `http://localhost/ferro831/`.
- [ ] Ensure at least one admin user exists.

## 2. Customer Flow Checklist

### Browsing
- [ ] Homepage loads without PHP warnings.
- [ ] Product search works.
- [ ] Category filter works.
- [ ] Price filters work.
- [ ] Sorting options work.

### Product Details / Variants
- [ ] Product detail page loads from listing click.
- [ ] Variant options render for variant-enabled products.
- [ ] Add-to-cart without variant is blocked when required.
- [ ] Out-of-stock variant cannot be added.

### Cart
- [ ] Add product to cart (no variant product).
- [ ] Add product variant to cart.
- [ ] Quantity update works.
- [ ] Quantity auto-adjusts when above stock.
- [ ] Remove item works.
- [ ] Cart totals calculate correctly.

### Auth
- [ ] Register new user.
- [ ] Login with user account.
- [ ] Logout works.
- [ ] Invalid login shows error.

### Wishlist
- [ ] Add to wishlist (logged-in user).
- [ ] Remove from wishlist.
- [ ] Move wishlist item to cart.
- [ ] Wishlist action while logged out redirects/returns login-required.

### Profile & Addresses
- [ ] Update profile fields.
- [ ] Add address.
- [ ] Edit address.
- [ ] Delete address.
- [ ] Set default address.

### Checkout & Orders
- [ ] Checkout page opens with cart items.
- [ ] Logged-in checkout can select saved address.
- [ ] Place order succeeds.
- [ ] Success page shows order id.
- [ ] User order history lists new order.
- [ ] User order detail shows items + status history.

## 3. Admin Flow Checklist
- [ ] Admin login works.
- [ ] Dashboard metrics load.
- [ ] Products page lists products.
- [ ] Add product with one image works.
- [ ] Add product with multiple images works.
- [ ] Add product with variants works.
- [ ] Manage variants page loads for product.
- [ ] Add variant works.
- [ ] Update variant works.
- [ ] Toggle variant active/inactive works.
- [ ] Delete/deactivate variant safe behavior works.
- [ ] Orders list loads.
- [ ] View order details works.
- [ ] Update order status works.
- [ ] Tracking number update works.

## 4. Data Integrity Checks
- [ ] Parent `products.stock` matches sum of active variant stock for variant-enabled products.
- [ ] `wishlist` does not allow duplicate user-product pairs.
- [ ] `order_items` snapshots include variant `size`, `color`, `sku` when variant order is placed.
- [ ] `order_status_history` inserts a row when status changes.

## 5. Regression / URL Compatibility
- [ ] Legacy URLs load: `index.php`, `products.php`, `product.php`, `cart.php`.
- [ ] Admin legacy URLs load: `admin/dashboard.php`, `admin/products.php`, `admin/orders.php`.
- [ ] Clean route equivalents also load under router.
