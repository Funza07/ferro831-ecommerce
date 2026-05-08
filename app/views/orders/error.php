<div class="order-result-wrap">
    <div class="order-result-card order-result-card-error">
        <div class="order-result-icon order-result-icon-error">!</div>
        <p class="order-result-eyebrow">Checkout Issue</p>
        <h1><?php echo e($message); ?></h1>
        <?php if (!empty($detail)): ?>
            <p><?php echo e($detail); ?></p>
        <?php endif; ?>
        <div class="order-result-actions">
            <a href="<?php echo e($back_url ?? '/ferro831/cart.php'); ?>" class="btn-outline order-result-btn">
                <?php echo e($back_label ?? 'Back to Cart'); ?>
            </a>
            <a href="/ferro831/products.php" class="btn-primary order-result-btn">Continue Shopping</a>
        </div>
    </div>
</div>
