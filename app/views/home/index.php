<!-- TRUST BAR -->
<div class="trust-bar reveal">
    <div class="ferro-home-container ferro-trust-inner">
        <div class="trust-item">
            <span class="trust-icon">A</span>
            <span>Authentic Jamshedpur Brand</span>
        </div>

        <div class="trust-item">
            <span class="trust-icon">S</span>
            <span>Ships in 2-3 Days</span>
        </div>

        <div class="trust-item">
            <span class="trust-icon">R</span>
            <span>7-Day Easy Returns</span>
        </div>

        <div class="trust-item">
            <span class="trust-icon">SEC</span>
            <span>Secure Checkout</span>
        </div>
    </div>
</div>

<!-- HERO -->
<section class="hero ferro-home-hero reveal" data-countdown-hours="24">
    <div class="ferro-hero-bg" aria-hidden="true"></div>
    <div class="ferro-hero-grid" aria-hidden="true"></div>
    <div class="ferro-hero-noise" aria-hidden="true"></div>
    <div class="ferro-hero-watermark" aria-hidden="true">831</div>

    <div class="ferro-hero-content ferro-home-container">
        <div class="ferro-hero-layout">
            <div class="ferro-hero-left">
                <div class="hero-tag ferro-hero-eyebrow reveal">JAMSHEDPUR STREETWEAR</div>
                <div class="hero-title ferro-hero-title reveal">FERRO<br><span class="green">831</span></div>
                <p class="hero-sub ferro-hero-sub reveal">Forged in Jamshedpur. Built for the streets. Everyday steel-city energy, now wearable.</p>

                <div class="hero-buttons ferro-hero-buttons reveal">
                    <button class="btn-primary" onclick="window.location.href='<?php echo url('products.php'); ?>'">SHOP NOW</button>
                    <button class="btn-secondary" onclick="document.getElementById('brand').scrollIntoView({behavior:'smooth'})">OUR STORY</button>
                </div>

                <div class="hero-countdown ferro-hero-countdown reveal">
                    <span class="countdown-label">NEXT DROP IN</span>
                    <span class="countdown-time" id="countdownDisplay">24:00:00</span>
                </div>

                <div class="ferro-hero-stats reveal">
                    <div><strong>500+</strong><span>Orders Delivered</span></div>
                    <div><strong>4.8</strong><span>Average Rating</span></div>
                    <div><strong>100%</strong><span>Made in Jharkhand</span></div>
                </div>
            </div>

            <div class="ferro-hero-right reveal" aria-hidden="true">
                <div class="ferro-hero-feature-card">
                    <span class="ferro-feature-eyebrow">LATEST DROP</span>
                    <div class="ferro-feature-title">Steel City Essentials</div>
                    <p>Industrial silhouettes with premium street comfort.</p>
                    <a href="<?php echo url('products.php'); ?>">Browse Collection</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MARQUEE TICKER -->
<div class="ferro-marquee reveal" aria-label="Brand ticker">
    <div class="ferro-marquee-track">
        <span>FERRO831</span><span class="dot">•</span>
        <span>JAMSHEDPUR STREETWEAR</span><span class="dot">•</span>
        <span>STEEL CITY SOUL</span><span class="dot">•</span>
        <span>STREET LINE</span><span class="dot">•</span>
        <span>DAILY LINE</span><span class="dot">•</span>
        <span>DESK LINE</span><span class="dot">•</span>
        <span>FREE SHIPPING ABOVE RS 999</span><span class="dot">•</span>
        <span>FERRO831</span><span class="dot">•</span>
        <span>JAMSHEDPUR STREETWEAR</span><span class="dot">•</span>
        <span>STEEL CITY SOUL</span><span class="dot">•</span>
        <span>STREET LINE</span><span class="dot">•</span>
        <span>DAILY LINE</span><span class="dot">•</span>
        <span>DESK LINE</span><span class="dot">•</span>
        <span>FREE SHIPPING ABOVE RS 999</span><span class="dot">•</span>
    </div>
</div>

<!-- FEATURED DROPS -->
<section class="products-section reveal" id="products">
    <div class="ferro-home-container">
        <div class="section-header">
            <div>
                <div class="section-tag">NEW ARRIVALS</div>
                <div class="section-title">FEATURED DROPS</div>
            </div>
            <a href="<?php echo url('products.php'); ?>" class="view-all">VIEW ALL -></a>
        </div>

        <div class="filter-chips">
            <button class="chip active" onclick="filterProducts('all', this)">All</button>
            <button class="chip" onclick="filterProducts('street-line', this)">Street Line</button>
            <button class="chip" onclick="filterProducts('daily-line', this)">Daily Line</button>
            <button class="chip" onclick="filterProducts('desk-line', this)">Desk Line</button>
            <button class="chip" onclick="filterProducts('budget', this)">Under Rs 999</button>
        </div>

        <?php if (empty($products)): ?>
            <div class="cart-empty" style="margin:40px auto 0;">
                <h2>No Products Found</h2>
                <p>No products found for selected filters</p>
            </div>
        <?php else: ?>
            <div class="product-grid" id="productGrid">
                <?php foreach ($products as $row): ?>
                    <?php
                        $product_id = (int)$row['id'];
                        $product_name = (string)$row['name'];
                        $product_category = (string)$row['category'];
                        $product_price = (float)$row['price'];
                        $product_stock = isset($row['stock']) ? (int)$row['stock'] : 0;
                        $product_sold_count = isset($row['sold_count']) ? (int)$row['sold_count'] : 0;
                        $has_variants = !empty($row['has_variants']);
                        $category_slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $product_category), '-'));
                        $is_wished = in_array($product_id, $wished_product_ids, true);
                        $is_low_stock = $product_stock > 0 && $product_stock <= 5;
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
                    <div class="product-card modern-product-card" data-category="<?php echo e($category_slug); ?>" data-price="<?php echo e($product_price); ?>" onclick="window.location.href='<?php echo url('product.php?id=' . $product_id); ?>'">
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
                            ><?php echo $is_wished ? '?' : '?'; ?></button>

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
                                        <?php elseif ($is_low_stock): ?>
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
    </div>
</section>

<!-- SOCIAL PROOF -->
<div class="social-proof reveal">
    <div class="ferro-home-container ferro-social-inner">
        <div class="proof-item">
            <div class="proof-number">500+</div>
            <div class="proof-label">ORDERS DELIVERED</div>
        </div>
        <div class="proof-divider"></div>
        <div class="proof-item">
            <div class="proof-number green">4.8*</div>
            <div class="proof-label">AVG RATING</div>
        </div>
        <div class="proof-divider"></div>
        <div class="proof-item">
            <div class="proof-number">100%</div>
            <div class="proof-label">MADE IN JHARKHAND</div>
        </div>
        <div class="proof-divider"></div>
        <div class="proof-item">
            <div class="proof-number">7-Day</div>
            <div class="proof-label">EASY RETURNS</div>
        </div>
    </div>
</div>

<!-- BRAND STORY -->
<section class="brand-story reveal" id="brand">
    <div class="ferro-home-container ferro-brand-layout">
        <div class="ferro-brand-visual">
            <div class="brand-logo-block">
                <div class="big-logo">FERRO<span>831</span></div>
                <div class="divider-line"></div>
                <div class="est">JAMSHEDPUR - EST. 2024</div>
            </div>
        </div>

        <div>
            <div class="section-tag">WHO WE ARE</div>
            <div class="section-title" style="margin-bottom:20px;">STEEL CITY SOUL</div>
            <p>Ferro831 was born from the smoke, steel, and spirit of Jamshedpur - India's first planned industrial city. We blend the grit of the factory floor with the ease of the streets.</p>
            <p>Every piece we make carries the weight of Dalma Hills and the pulse of a city that built a nation. This isn't fast fashion. This is identity.</p>
            <div class="category-pills">
                <span class="pill">City Soul</span>
                <span class="pill">Homegrown</span>
                <span class="pill">Jharkhand</span>
            </div>
            <button class="btn-secondary" style="margin-top:24px;" onclick="window.location.href='<?php echo url('products.php'); ?>'">SHOP THE STORY -></button>
        </div>
    </div>
</section>

<!-- NEWSLETTER PLACEHOLDER -->
<section class="newsletter reveal ferro-home-newsletter-lite" id="collections">
    <div class="ferro-home-container">
        <div class="section-tag">COLLECTIONS</div>
        <div class="section-title">Explore More Drops</div>
        <p>Browse all lines and find your next steel-city essential.</p>
        <a class="btn-secondary" href="<?php echo url('products.php'); ?>">VIEW ALL PRODUCTS -></a>
    </div>
</section>

<!-- OUR LINES -->
<section class="lines-section reveal">
    <div class="ferro-home-container">
        <div class="section-header">
            <div>
                <div class="section-tag">BROWSE</div>
                <div class="section-title">OUR LINES</div>
            </div>
        </div>

        <div class="lines-grid">
            <a class="line-card" href="<?php echo url('products.php?category=Street+Line'); ?>">
                <div class="line-icon">T</div>
                <div class="line-name">STREET LINE</div>
                <div class="line-desc">Oversized tees, hoodies, and caps forged for the urban grind. Heavy washed, raw edges, unapologetic.</div>
                <div class="line-explore">EXPLORE -></div>
            </a>
            <a class="line-card" href="<?php echo url('products.php?category=Daily+Line'); ?>">
                <div class="line-icon">D</div>
                <div class="line-name">DAILY LINE</div>
                <div class="line-desc">Tote bags, caps, and everyday carry - built for the commuter, the chai break, and everything between.</div>
                <div class="line-explore">EXPLORE -></div>
            </a>
            <a class="line-card" href="<?php echo url('products.php?category=Desk+Line'); ?>">
                <div class="line-icon">DSK</div>
                <div class="line-name">DESK LINE</div>
                <div class="line-desc">Mugs, mousepads, and desk drops with industrial-grade aesthetic. From your city, for your setup.</div>
                <div class="line-explore">EXPLORE -></div>
            </a>
        </div>
    </div>
</section>




