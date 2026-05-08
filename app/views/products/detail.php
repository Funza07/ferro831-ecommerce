<?php
$product_id = (int)$product['id'];
$product_name = (string)$product['name'];
$product_category = (string)$product['category'];
$product_price = (float)$product['price'];
$product_description = (string)$product['description'];
$product_stock = (int)$stock_available;
$variants = is_array($variants ?? null) ? $variants : [];
$has_variants = (bool)($has_variants ?? false);
$available_sizes = is_array($available_sizes ?? null) ? $available_sizes : [];
$available_colors = is_array($available_colors ?? null) ? $available_colors : [];

$thumb_images = [];
if (!empty($main_image)) {
    $thumb_images[] = (string)$main_image;
}
if (!empty($gallery_images) && is_array($gallery_images)) {
    foreach ($gallery_images as $img) {
        $img = (string)$img;
        if ($img !== '' && !in_array($img, $thumb_images, true)) {
            $thumb_images[] = $img;
        }
    }
}
if (empty($thumb_images) && !empty($main_image)) {
    $thumb_images[] = (string)$main_image;
}

$category_link = url('products.php?category=' . rawurlencode($product_category));
?>

<section class="modern-product-page">
    <div class="modern-breadcrumb">
        <a href="<?php echo url('index.php'); ?>">Home</a>
        <span>&gt;</span>
        <a href="<?php echo url('products.php'); ?>">Collections</a>
        <span>&gt;</span>
        <a href="<?php echo e($category_link); ?>"><?php echo e($product_category); ?></a>
        <span>&gt;</span>
        <strong><?php echo e($product_name); ?></strong>
    </div>

    <div class="modern-product-wrap">
        <div class="modern-product-media">
            <div class="modern-main-image-frame">
                <img
                    id="modernMainProductImage"
                    src="<?php echo url('assets/images/' . $main_image); ?>"
                    alt="<?php echo e($product_name); ?>"
                >
                <span class="modern-zoom-note">Hover to zoom</span>
            </div>

            <div class="modern-thumb-strip">
                <?php foreach ($thumb_images as $index => $image_path): ?>
                    <?php $thumb_url = url('assets/images/' . $image_path); ?>
                    <button
                        type="button"
                        class="modern-thumb<?php echo $index === 0 ? ' active' : ''; ?>"
                        data-image="<?php echo e($thumb_url); ?>"
                        onclick="modernSelectThumb(this)"
                        aria-label="View image <?php echo (int)$index + 1; ?>"
                    >
                        <img src="<?php echo e($thumb_url); ?>" alt="<?php echo e($product_name); ?> thumbnail <?php echo (int)$index + 1; ?>">
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="modern-product-info">
            <span class="modern-info-cat"><?php echo e(strtoupper($product_category)); ?></span>
            <h1 class="modern-info-name"><?php echo e($product_name); ?></h1>

            <div class="modern-rating-row">
                <span>★★★★★</span>
                <span>4.8</span>
                <span>128 reviews</span>
                <button type="button" class="modern-link-btn">Write a review</button>
            </div>

            <div class="modern-price-block">Rs <?php echo e(number_format($product_price, 2)); ?></div>

            <div id="modernStockText" class="modern-stock-urgency <?php echo $is_out_of_stock ? 'is-out' : ($product_stock <= 5 ? 'is-low' : 'is-in'); ?>">
                <?php if ($is_out_of_stock): ?>
                    Out of stock
                <?php elseif ($product_stock <= 5): ?>
                    Only <?php echo $product_stock; ?> left — order soon
                <?php else: ?>
                    In stock
                <?php endif; ?>
            </div>

            <div class="modern-size-grid">
                <p>Size</p>
                <div>
                    <?php if ($has_variants && !empty($available_sizes)): ?>
                        <?php foreach ($available_sizes as $size_row): ?>
                            <?php
                            $size_value = (string)$size_row['size'];
                            $size_has_stock = (bool)$size_row['has_stock'];
                            ?>
                            <button
                                type="button"
                                class="modern-size-option<?php echo !$size_has_stock ? ' is-unavailable' : ''; ?>"
                                data-size="<?php echo e($size_value); ?>"
                                onclick="modernSelectSize(this)"
                            ><?php echo e($size_value); ?></button>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <button type="button" class="modern-size-option active" onclick="modernSelectSize(this)">One Size</button>
                    <?php endif; ?>
                </div>
                <button type="button" class="modern-link-btn" onclick="modernShowSizeGuide()">Size guide</button>
            </div>

            <div class="modern-color-row">
                <p>Colour</p>
                <div>
                    <?php if ($has_variants && !empty($available_colors)): ?>
                        <?php foreach ($available_colors as $color_row): ?>
                            <?php
                            $color_value = (string)$color_row['color'];
                            $color_hex = (string)$color_row['color_hex'];
                            $color_has_stock = (bool)$color_row['has_stock'];
                            ?>
                            <button
                                type="button"
                                class="modern-color-dot<?php echo !$color_has_stock ? ' is-unavailable' : ''; ?>"
                                style="--dot:<?php echo e($color_hex ?: '#111111'); ?>;"
                                data-color="<?php echo e($color_value); ?>"
                                onclick="modernSelectColor(this)"
                                aria-label="<?php echo e($color_value); ?>"
                            ></button>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <button type="button" class="modern-color-dot active" style="--dot:#111111;" onclick="modernSelectColor(this)" aria-label="Default"></button>
                    <?php endif; ?>
                </div>
            </div>

            <p id="modernVariantSelectionText" style="margin:0 0 10px;color:var(--mid-grey);font-size:13px;">
                <?php echo $has_variants ? 'Please select size and colour.' : 'Default product selection.'; ?>
            </p>

            <form id="modernAddToCartForm" action="/ferro831/cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" id="modernVariantIdInput" name="variant_id" value="0">

                <div class="modern-qty-wrap">
                    <span>Quantity</span>
                    <div class="modern-qty-stepper">
                        <button type="button" onclick="modernChangeQty(-1, <?php echo max(1, $product_stock); ?>)">−</button>
                        <input
                            id="modernQtyInput"
                            type="number"
                            name="quantity"
                            value="1"
                            min="1"
                            max="<?php echo $is_out_of_stock ? 1 : max(1, $product_stock); ?>"
                            <?php echo $is_out_of_stock ? 'disabled' : ''; ?>
                        >
                        <button type="button" onclick="modernChangeQty(1, <?php echo max(1, $product_stock); ?>)">+</button>
                    </div>
                </div>

                <button
                    type="submit"
                    id="modernAtcButton"
                    name="add_to_cart"
                    value="1"
                    class="modern-atc-btn"
                    <?php echo ($is_out_of_stock || $has_variants) ? 'disabled' : ''; ?>
                >
                    <?php echo $is_out_of_stock ? 'SOLD OUT' : ($has_variants ? 'Select size & colour' : 'Add to Cart'); ?>
                </button>
            </form>

            <button
                type="button"
                class="wishlist-btn modern-wish-btn<?php echo $is_in_wishlist ? ' active' : ''; ?>"
                data-product-id="<?php echo $product_id; ?>"
                data-variant="detail"
                data-logged-in="<?php echo $is_user_logged_in ? '1' : '0'; ?>"
                data-login-url="/ferro831/login.php"
                onclick="toggleWishlist(<?php echo $product_id; ?>, this)"
                aria-label="<?php echo $is_in_wishlist ? 'Remove from wishlist' : 'Add to wishlist'; ?>"
                aria-pressed="<?php echo $is_in_wishlist ? 'true' : 'false'; ?>"
            >
                <?php if ($is_user_logged_in): ?>
                    <?php echo $is_in_wishlist ? '&#10084; Saved to Wishlist' : '&#9825; Save to Wishlist'; ?>
                <?php else: ?>
                    &#9825; Login for Wishlist
                <?php endif; ?>
            </button>

            <div class="modern-trust-strip">
                <span>Free shipping above Rs 999</span>
                <span>Easy 7-day returns</span>
                <span>COD available</span>
                <span>Secure checkout</span>
            </div>

            <div class="modern-share-row">
                <button type="button" onclick="window.open('https://wa.me/?text=' + encodeURIComponent(window.location.href), '_blank')">WhatsApp</button>
                <button type="button">Instagram</button>
                <button type="button" onclick="modernCopyProductLink()">Copy link</button>
            </div>
        </div>
    </div>

    <div class="modern-tabs-section">
        <div class="modern-tab-head">
            <button type="button" class="modern-tab active" data-tab="description" onclick="modernSwitchTab('description')">Description</button>
            <button type="button" class="modern-tab" data-tab="size-chart" onclick="modernSwitchTab('size-chart')">Size chart</button>
            <button type="button" class="modern-tab" data-tab="reviews" onclick="modernSwitchTab('reviews')">Reviews</button>
            <button type="button" class="modern-tab" data-tab="shipping" onclick="modernSwitchTab('shipping')">Shipping & returns</button>
        </div>

        <div class="modern-tab-body active" data-tab-body="description">
            <p><?php echo e($product_description); ?></p>
            <div class="modern-spec-grid">
                <div><strong>Category</strong><span><?php echo e($product_category); ?></span></div>
                <div><strong>Origin</strong><span>Jamshedpur, Jharkhand</span></div>
                <div><strong>Brand</strong><span>FERRO831</span></div>
                <div><strong>Stock</strong><span><?php echo e((string)$product_stock); ?></span></div>
                <div><strong>Material</strong><span>Premium cotton blend</span></div>
                <div><strong>Fit</strong><span>Relaxed street fit</span></div>
            </div>
        </div>

        <div class="modern-tab-body" data-tab-body="size-chart">
            <table class="modern-size-table">
                <thead>
                    <tr><th>Size</th><th>Chest (in)</th><th>Length (in)</th><th>Shoulder (in)</th></tr>
                </thead>
                <tbody>
                    <tr><td>XS</td><td>36</td><td>25</td><td>16</td></tr>
                    <tr><td>S</td><td>38</td><td>26</td><td>17</td></tr>
                    <tr><td>M</td><td>40</td><td>27</td><td>18</td></tr>
                    <tr><td>L</td><td>42</td><td>28</td><td>19</td></tr>
                    <tr><td>XL</td><td>44</td><td>29</td><td>20</td></tr>
                    <tr><td>XXL</td><td>46</td><td>30</td><td>21</td></tr>
                </tbody>
            </table>
        </div>

        <div class="modern-tab-body" data-tab-body="reviews">
            <div class="modern-review-card">
                <p><strong>Arjun K.</strong> · ★★★★★</p>
                <p>Great fit and fabric quality. Placeholder review UI until live review backend arrives.</p>
            </div>
            <div class="modern-review-card">
                <p><strong>Shruti M.</strong> · ★★★★☆</p>
                <p>Loved the style. Delivery was smooth and packaging felt premium.</p>
            </div>
        </div>

        <div class="modern-tab-body" data-tab-body="shipping">
            <ul class="modern-policy-list">
                <li>Standard delivery in 3–5 working days.</li>
                <li>Free shipping above Rs 999.</li>
                <li>Cash on Delivery available.</li>
                <li>7-day return window on eligible items.</li>
            </ul>
        </div>
    </div>

    <div class="modern-upsell-section">
        <h2>You may also like</h2>
        <p>Explore more Ferro831 drops built for everyday street movement.</p>
        <a href="<?php echo url('products.php'); ?>" class="btn-primary">Browse Products</a>
    </div>
</section>

<div class="modern-size-guide-modal" id="modernSizeGuideModal" aria-hidden="true">
    <div class="modern-size-guide-card">
        <button type="button" class="modern-size-guide-close" onclick="modernHideSizeGuide()">×</button>
        <h3>Size Guide</h3>
        <p>Use this as a quick fit reference. If you prefer a relaxed fit, choose one size up.</p>
        <table class="modern-size-table">
            <thead>
                <tr><th>Size</th><th>Chest (in)</th><th>Length (in)</th></tr>
            </thead>
            <tbody>
                <tr><td>S</td><td>38</td><td>26</td></tr>
                <tr><td>M</td><td>40</td><td>27</td></tr>
                <tr><td>L</td><td>42</td><td>28</td></tr>
                <tr><td>XL</td><td>44</td><td>29</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php if ($has_variants): ?>
<script>
window.FERRO_VARIANTS = <?php echo json_encode($variants, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
<?php endif; ?>
