<div class="page-header">
    <p class="page-header-eyebrow">Browse Catalog</p>
    <h1>Products</h1>
</div>

<section class="section" id="products-listing">
    <?php if ($search_query !== ''): ?>
        <p style="color:var(--mid-grey);margin-bottom:20px;">
            Search results for: <strong><?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?></strong>
        </p>
    <?php else: ?>
        <p style="color:var(--mid-grey);margin-bottom:20px;">Showing all products</p>
    <?php endif; ?>

    <?php if (empty($products)): ?>
        <div class="cart-empty" style="margin:40px auto 0;">
            <h2>No Products Found</h2>
            <p>
                <?php if ($search_query !== ''): ?>
                    No products matched "<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>".
                <?php else: ?>
                    No products available right now.
                <?php endif; ?>
            </p>
            <a href="/ferro831/products.php" class="btn-primary" style="display:inline-flex;">View All Products</a>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $row): ?>
            <?php
                $product_id = (int)$row['id'];
                $product_name = (string)$row['name'];
                $product_category = (string)$row['category'];
                $product_price = (float)$row['price'];
                $product_stock = isset($row['stock']) ? (int)$row['stock'] : 0;
                $product_sold_count = isset($row['sold_count']) ? (int)$row['sold_count'] : 0;
                $has_variants = !empty($row['has_variants']);
                $is_user_logged_in = isset($is_user_logged_in) ? (bool)$is_user_logged_in : (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0);
                $wished_ids = isset($wished_product_ids) && is_array($wished_product_ids) ? $wished_product_ids : [];
                $is_wished = in_array($product_id, $wished_ids, true);
                if ($product_stock <= 0) {
                    $badge_text = 'SOLD OUT';
                } elseif ($product_stock <= 5) {
                    $badge_text = 'LOW STOCK';
                } elseif ($product_sold_count >= 5) {
                    $badge_text = 'HOT';
                } else {
                    $badge_text = 'NEW';
                }
            ?>
            <div class="product-card modern-product-card" onclick="window.location.href='<?php echo url('product.php?id=' . $product_id); ?>'">
                <div class="modern-product-image-wrap">
                    <span class="modern-product-badge"><?php echo e($badge_text); ?></span>

                    <button
                        type="button"
                        class="wishlist-btn modern-wishlist-btn<?php echo $is_wished ? ' active' : ''; ?>"
                        data-product-id="<?php echo $product_id; ?>"
                        data-logged-in="<?php echo $is_user_logged_in ? '1' : '0'; ?>"
                        data-login-url="<?php echo url('login.php'); ?>"
                        onclick="event.stopPropagation(); toggleWishlist(<?php echo $product_id; ?>, this)"
                        aria-label="<?php echo $is_wished ? 'Remove from wishlist' : 'Add to wishlist'; ?>"
                        aria-pressed="<?php echo $is_wished ? 'true' : 'false'; ?>"
                    ><?php echo $is_wished ? '♥' : '♡'; ?></button>

                    <a href="<?php echo url('product.php?id=' . $product_id); ?>" class="modern-product-overlay" onclick="event.stopPropagation();">
                        <?php if (!empty($row['image'])): ?>
                            <img class="modern-product-image" src="<?php echo url('assets/images/' . $row['image']); ?>" alt="<?php echo e($product_name); ?>">
                        <?php else: ?>
                            <span class="modern-product-image modern-product-image-placeholder">Product image</span>
                        <?php endif; ?>
                        <span class="modern-quick-view">Quick view</span>
                    </a>
                </div>

                <div class="modern-product-body">
                    <div class="modern-product-category"><?php echo e(strtoupper($product_category)); ?></div>
                    <div class="modern-product-name"><?php echo e($product_name); ?></div>
                    <div class="modern-rating-row">
                        <span>&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                        <span>4.8</span>
                    </div>
                    <div class="modern-size-row">
                        <button type="button" class="modern-size-chip" onclick="event.stopPropagation();">S</button>
                        <button type="button" class="modern-size-chip" onclick="event.stopPropagation();">M</button>
                        <button type="button" class="modern-size-chip" onclick="event.stopPropagation();">L</button>
                        <button type="button" class="modern-size-chip" onclick="event.stopPropagation();">XL</button>
                    </div>
                    <div class="modern-product-bottom">
                        <div>
                            <div class="modern-price-main">Rs <?php echo e(number_format($product_price, 0)); ?></div>
                            <div class="modern-stock-note">
                                <?php if ($product_stock <= 0): ?>
                                    Out of stock
                                <?php elseif ($product_stock <= 5): ?>
                                    Only <?php echo $product_stock; ?> left!
                                <?php else: ?>
                                    In stock
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modern-card-action-strip">
                    <?php if ($product_stock <= 0): ?>
                        <button type="button" class="modern-add-cart-btn modern-action-btn" disabled onclick="event.stopPropagation();">SOLD OUT</button>
                    <?php elseif ($has_variants): ?>
                        <button type="button" class="modern-add-cart-btn modern-action-btn" onclick="event.stopPropagation(); window.location.href='<?php echo url('product.php?id=' . $product_id); ?>';">SELECT OPTIONS</button>
                    <?php else: ?>
                        <button type="button" class="add-to-cart modern-add-cart-btn modern-action-btn" onclick="event.stopPropagation(); addToCart(<?php echo $product_id; ?>, this)">+ CART</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>



