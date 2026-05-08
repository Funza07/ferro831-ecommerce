<div class="page-header">
    <p class="page-header-eyebrow">Your Account</p>
    <h1>Wishlist</h1>
</div>

<section class="section">
    <?php if (empty($wishlist_products)): ?>
        <div class="order-empty-state">
            <h2>Your Wishlist is Empty</h2>
            <p>Save products you love and come back to them anytime.</p>
            <a href="/ferro831/index.php#products" class="btn-primary" style="display:inline-flex;">Explore Products</a>
        </div>
    <?php else: ?>
        <div class="wishlist-grid">
            <?php foreach ($wishlist_products as $item): ?>
                <div class="wishlist-card">
                    <div class="wishlist-card-img-wrap">
                        <img
                            src="/ferro831/assets/images/<?php echo htmlspecialchars((string)$item['image'], ENT_QUOTES, 'UTF-8'); ?>"
                            alt="<?php echo htmlspecialchars((string)$item['name'], ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>

                    <div class="wishlist-card-body">
                        <p class="product-card-cat"><?php echo htmlspecialchars((string)$item['category'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <h3 class="product-card-name"><?php echo htmlspecialchars((string)$item['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="product-card-price">Rs <?php echo htmlspecialchars((string)$item['price'], ENT_QUOTES, 'UTF-8'); ?></p>

                        <div class="wishlist-card-actions">
                            <a href="/ferro831/product.php?id=<?php echo (int)$item['product_id']; ?>" class="btn-card">View Product</a>

                            <form method="POST" action="/ferro831/move-wishlist-to-cart.php">
                                <input type="hidden" name="wishlist_id" value="<?php echo (int)$item['wishlist_id']; ?>">
                                <input type="hidden" name="product_id" value="<?php echo (int)$item['product_id']; ?>">
                                <button type="submit" class="btn-card">Move to Cart</button>
                            </form>

                            <form method="POST" action="/ferro831/remove-wishlist.php">
                                <input type="hidden" name="wishlist_id" value="<?php echo (int)$item['wishlist_id']; ?>">
                                <input type="hidden" name="product_id" value="<?php echo (int)$item['product_id']; ?>">
                                <button type="submit" class="btn-card wishlist-remove-btn">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
