<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isLoggedIn();
$current_role = $is_logged_in ? ($_SESSION['role'] ?? '') : '';
$display_name = $is_logged_in ? e($_SESSION['user_name'] ?? 'User') : '';
$header_search_value = e($_GET['q'] ?? '');
$cart_count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += (int)$qty;
    }
}

$current_script = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$is_home = $current_script === 'index.php';
$is_products = $current_script === 'products.php' || $current_script === 'product.php';
$is_cart = $current_script === 'cart.php';
$is_wishlist = $current_script === 'wishlist.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FERRO831 — Jamshedpur Streetwear</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600;700&family=Barlow+Condensed:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    <script>window.FERRO_BASE_URL = '<?php echo rtrim(BASE_URL, '/'); ?>';</script>
</head>
<body>

<!-- ANNOUNCEMENT BANNER -->
<div class="announcement ferro-announcement" id="announcement">
    <span class="ferro-announcement-text">Free shipping on orders above Rs 999 &nbsp;|&nbsp; Use code <strong>FERRO10</strong> for 10% off your first order</span>
    <button type="button" class="close-btn ferro-announcement-close" onclick="closeAnnouncement()" aria-label="Close announcement">×</button>
</div>

<!-- MOBILE MENU -->
<div class="mobile-menu ferro-mobile-menu" id="mobileMenu" aria-hidden="true">
    <div class="mobile-menu-header ferro-mobile-menu-header">
        <a href="<?php echo url('index.php'); ?>" class="logo ferro-logo">FERRO<span>831</span></a>
        <button type="button" class="mobile-close ferro-mobile-close" onclick="closeMobileMenu()" aria-label="Close menu">×</button>
    </div>

    <nav class="mobile-nav-links ferro-mobile-nav-links">
        <a href="<?php echo url('index.php'); ?>" onclick="closeMobileMenu()">Home</a>
        <a href="<?php echo url('products.php'); ?>" onclick="closeMobileMenu()">Shop</a>
        <a href="<?php echo url('index.php#brand'); ?>" onclick="closeMobileMenu()">Brand</a>
        <a href="<?php echo url('cart.php'); ?>" onclick="closeMobileMenu()">Cart</a>
        <button type="button" class="ferro-mobile-search-link" onclick="closeMobileMenu(); openSearch();">Search</button>

        <?php if ($is_logged_in): ?>
            <a class="mobile-small-link" href="<?php echo url('wishlist.php'); ?>" onclick="closeMobileMenu()">Wishlist</a>
            <a class="mobile-small-link" href="<?php echo url('user/orders.php'); ?>" onclick="closeMobileMenu()">My Orders</a>
            <a class="mobile-small-link" href="<?php echo url('profile.php'); ?>" onclick="closeMobileMenu()">Profile</a>
            <?php if ($current_role === 'admin'): ?>
                <a class="mobile-small-link" href="<?php echo url('admin/dashboard.php'); ?>" onclick="closeMobileMenu()">Admin</a>
            <?php endif; ?>
            <a class="mobile-small-link" href="<?php echo url('logout.php'); ?>" onclick="closeMobileMenu()">Logout</a>
        <?php else: ?>
            <a class="mobile-small-link" href="<?php echo url('wishlist.php'); ?>" onclick="closeMobileMenu()">Wishlist</a>
            <a class="mobile-small-link" href="<?php echo url('login.php'); ?>" onclick="closeMobileMenu()">Login</a>
            <a class="mobile-small-link" href="<?php echo url('register.php'); ?>" onclick="closeMobileMenu()">Register</a>
        <?php endif; ?>
    </nav>

    <div class="mobile-footer ferro-mobile-footer">
        <div class="social-btn">𝕏</div>
        <div class="social-btn">IG</div>
        <div class="social-btn">IN</div>
    </div>
</div>
<div class="ferro-mobile-menu-overlay" id="mobileMenuOverlay" onclick="closeMobileMenu()" aria-hidden="true"></div>

<!-- SEARCH OVERLAY -->
<div class="search-overlay ferro-search-overlay" id="searchOverlay" aria-hidden="true">
    <form class="search-bar ferro-search-box" action="<?php echo url('products.php'); ?>" method="GET" onsubmit="searchProducts(); return false;">
        <div class="ferro-search-head">
            <span class="ferro-search-title">Search FERRO831</span>
            <button type="button" class="search-close ferro-search-close" onclick="closeSearch()" aria-label="Close search">×</button>
        </div>
        <div class="ferro-search-input-wrap">
            <input
                type="text"
                name="q"
                placeholder="Search products..."
                id="searchInput"
                value="<?php echo $header_search_value; ?>"
                onkeydown="if(event.key === 'Enter') searchProducts()"
            >
            <button type="submit" class="ferro-search-submit">Search</button>
        </div>
        <p class="ferro-search-tip">Tip: Press <kbd>Ctrl</kbd> + <kbd>K</kbd> to open search.</p>
    </form>
</div>

<!-- NAVBAR -->
<nav class="navbar ferro-nav" id="ferroNav">
    <div class="ferro-nav-inner">
        <div class="navbar-left ferro-nav-left">
            <button type="button" class="hamburger ferro-hamburger" onclick="openMobileMenu()" aria-label="Open menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <a href="<?php echo url('index.php'); ?>" class="logo ferro-logo">FERRO<span>831</span></a>
        </div>

        <div class="nav-links ferro-nav-links">
            <a href="<?php echo url('index.php'); ?>" class="ferro-nav-link<?php echo $is_home ? ' active' : ''; ?>">Home</a>
            <a href="<?php echo url('index.php#collections'); ?>" class="ferro-nav-link">Collections</a>
            <a href="<?php echo url('index.php#brand'); ?>" class="ferro-nav-link">Brand</a>
            <a href="<?php echo url('products.php'); ?>" class="ferro-nav-link<?php echo $is_products ? ' active' : ''; ?>">Products</a>
        </div>

        <div class="navbar-right ferro-nav-right">
            <button type="button" class="nav-icon ferro-nav-icon" onclick="openSearch()" title="Search" aria-label="Search">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>

            <?php if ($is_logged_in): ?>
                <a href="<?php echo url('wishlist.php'); ?>" class="nav-icon ferro-nav-icon" title="Wishlist" aria-label="Wishlist">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </a>
            <?php endif; ?>

            <a href="<?php echo url('cart.php'); ?>" class="cart-btn ferro-cart-btn ferro-cart-trigger" onclick="return openCartDrawer(event);">
                CART
                <span class="cart-badge" id="cartBadge"><?php echo (int)$cart_count; ?></span>
            </a>

            <?php if (!$is_logged_in): ?>
                <a href="<?php echo url('login.php'); ?>" class="nav-user-link ferro-user-link mobile-keep">LOGIN</a>
            <?php else: ?>
                <span class="nav-user-name ferro-user-name"><?php echo $display_name; ?></span>
                <a href="<?php echo url('profile.php'); ?>" class="nav-user-link ferro-user-link">PROFILE</a>
                <?php if ($current_role === 'admin'): ?>
                    <a href="<?php echo url('admin/dashboard.php'); ?>" class="nav-user-link ferro-user-link">ADMIN</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- MOBILE BOTTOM NAV -->
<nav class="ferro-bottom-nav" aria-label="Mobile bottom navigation">
    <a href="<?php echo url('index.php'); ?>" class="ferro-bottom-nav-item<?php echo $is_home ? ' active' : ''; ?>">
        <span class="ferro-bottom-nav-icon">HOME</span>
    </a>
    <a href="<?php echo url('products.php'); ?>" class="ferro-bottom-nav-item<?php echo $is_products ? ' active' : ''; ?>">
        <span class="ferro-bottom-nav-icon">SHOP</span>
    </a>
    <a href="<?php echo url('cart.php'); ?>" class="ferro-bottom-nav-item ferro-bottom-nav-cart<?php echo $is_cart ? ' active' : ''; ?>" onclick="return openCartDrawer(event);">
        <span class="ferro-bottom-nav-icon">CART</span>
        <span class="ferro-bottom-nav-badge" id="mobileCartBadge"><?php echo (int)$cart_count; ?></span>
    </a>
    <button type="button" class="ferro-bottom-nav-item" onclick="openSearch()">
        <span class="ferro-bottom-nav-icon">SEARCH</span>
    </button>
    <a href="<?php echo $is_logged_in ? url('wishlist.php') : url('login.php'); ?>" class="ferro-bottom-nav-item<?php echo $is_wishlist ? ' active' : ''; ?>">
        <span class="ferro-bottom-nav-icon">WISHLIST</span>
    </a>
</nav>

<!-- SAFE CART DRAWER -->
<div class="ferro-cart-drawer-overlay" id="cartDrawerOverlay" onclick="closeCartDrawer()" aria-hidden="true"></div>
<aside class="ferro-cart-drawer" id="cartDrawer" aria-hidden="true" aria-label="Cart drawer">
    <div class="ferro-cart-drawer-head">
        <h3>Your Cart</h3>
        <button type="button" class="ferro-cart-drawer-close" onclick="closeCartDrawer()" aria-label="Close cart drawer">×</button>
    </div>
    <div class="ferro-cart-drawer-body">
        <p class="ferro-cart-drawer-count">Items in cart: <strong id="cartDrawerCount"><?php echo (int)$cart_count; ?></strong></p>
        <p class="ferro-cart-drawer-note">Review your selections before checkout.</p>
        <p class="ferro-cart-drawer-empty<?php echo $cart_count > 0 ? ' is-hidden' : ''; ?>" id="cartDrawerEmptyState">Your cart is empty.</p>
    </div>
    <div class="ferro-cart-drawer-actions">
        <a href="<?php echo url('cart.php'); ?>" class="ferro-cart-drawer-btn ferro-btn-view">View Cart</a>
        <a href="<?php echo url('checkout.php'); ?>" class="ferro-cart-drawer-btn ferro-btn-checkout">Checkout</a>
        <a href="<?php echo url('products.php'); ?>" class="ferro-cart-drawer-btn ferro-btn-shop">Continue Shopping</a>
    </div>
</aside>
