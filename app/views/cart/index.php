<div class="page-header">
    <p class="page-header-eyebrow">Your Selections</p>
    <h1>Cart</h1>
</div>

<?php if ($cart_notice !== ''): ?>
    <div class="section" style="padding-top:20px;padding-bottom:0;">
        <div class="cart-notice">
            <?php echo e($cart_notice); ?>
        </div>
    </div>
<?php endif; ?>

<?php if (empty($cart_items)): ?>

<div class="cart-empty order-result-card">
    <div class="cart-empty-icon">Cart</div>
    <h2>Cart is Empty</h2>
    <p>You have not added anything yet. Browse our drops and find something worth wearing.</p>
    <a href="/ferro831/index.php" class="btn-primary cart-empty-cta">Shop Now</a>
</div>

<?php else: ?>

<form method="POST" action="cart.php">
<div class="cart-layout">

    <div class="cart-items-panel">
        <?php foreach ($cart_items as $item):
            $product = $item['product'];
            $variant = $item['variant'] ?? null;
            $quantity = (int)$item['quantity'];
            $cart_key = (string)$item['cart_key'];
            $stock = (int)$item['stock'];
            $total = $item['total'];
            $unit_price = (float)$item['unit_price'];
        ?>
        <div class="cart-item">
            <img
                class="cart-item-img"
                src="/ferro831/assets/images/<?php echo e((string)$product['image']); ?>"
                alt="<?php echo e((string)$product['name']); ?>"
            >

            <div>
                <h3 class="cart-item-name"><?php echo e((string)$product['name']); ?></h3>
                <?php if (!empty($variant)): ?>
                    <p class="cart-item-stock">Size: <?php echo e((string)$variant['size']); ?> | Colour: <?php echo e((string)$variant['color']); ?></p>
                    <?php if (!empty($variant['sku'])): ?><p class="cart-item-stock">SKU: <?php echo e((string)$variant['sku']); ?></p><?php endif; ?>
                <?php endif; ?>
                <p class="cart-item-price">Rs <?php echo e(number_format($unit_price, 2)); ?> each</p>
                <p class="cart-item-stock">Available: <?php echo $stock; ?></p>
                <input
                    class="cart-qty-input"
                    type="number"
                    name="quantities[<?php echo e($cart_key); ?>]"
                    value="<?php echo $quantity; ?>"
                    min="1"
                    max="<?php echo $stock; ?>"
                >
            </div>

            <div class="cart-item-total-col">
                <div class="cart-item-total">Rs <?php echo e(number_format((float)$total, 2)); ?></div>
                <a href="cart.php?remove=<?php echo urlencode($cart_key); ?>" class="btn-remove">x Remove</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="cart-sidebar">
        <div class="cart-summary-card">
            <p class="cart-summary-title">Order Summary</p>

            <div class="cart-summary-row">
                <span>Subtotal</span>
                <span>Rs <?php echo e((string)$grand_total); ?></span>
            </div>
            <div class="cart-summary-row">
                <span>Shipping</span>
                <span class="cart-summary-shipping">Calculated at checkout</span>
            </div>

            <div class="cart-summary-total">
                <span class="cart-summary-total-label">Total</span>
                <span class="cart-summary-total-amount">Rs <?php echo e((string)$grand_total); ?></span>
            </div>

            <a href="/ferro831/checkout.php" class="btn-checkout">Proceed to Checkout -></a>

            <button type="submit" name="update_cart" class="btn-update">Update Cart</button>
        </div>
    </div>

</div>
</form>

<?php endif; ?>
