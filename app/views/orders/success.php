<div class="order-result-wrap">
    <div class="order-result-card order-result-card-success">
        <div class="order-result-icon order-result-icon-success">OK</div>
        <p class="order-result-eyebrow">Order Confirmed</p>
        <h1>Order Placed!</h1>
        <p>
            Thank you, <strong><?php echo e($customer_name); ?></strong>.<br>
            Your order has been received and is being processed.
        </p>

        <div class="order-result-order-id">
            <div class="order-result-order-id-label">Order ID</div>
            <div class="order-result-order-id-num">#<?php echo str_pad(e((string)$order_id), 5, '0', STR_PAD_LEFT); ?></div>
        </div>

        <ul class="order-result-steps">
            <li>We have sent your order details for processing.</li>
            <li>You can track updates from your orders page.</li>
            <li>Typical dispatch starts within 24-48 hours.</li>
        </ul>

        <div class="order-result-actions">
            <a href="/ferro831/user/orders.php" class="btn-outline order-result-btn">View Orders</a>
            <a href="/ferro831/index.php" class="btn-primary order-result-btn">Continue Shopping</a>
        </div>
    </div>
</div>
