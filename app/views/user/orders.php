<?php
if (!function_exists('orderStatusClass')) {
    function orderStatusClass($status) {
        $map = [
            'Pending' => 'order-status-pending',
            'Confirmed' => 'order-status-confirmed',
            'Packed' => 'order-status-packed',
            'Shipped' => 'order-status-shipped',
            'Delivered' => 'order-status-delivered',
            'Cancelled' => 'order-status-cancelled',
        ];
        return $map[$status] ?? 'order-status-pending';
    }
}
?>
<div class="page-header">
    <p class="page-header-eyebrow">Your Account</p>
    <h1>My Orders</h1>
</div>

<section class="section">
    <?php if (empty($orders)): ?>
        <div class="order-empty-state">
            <h2>No Orders Yet</h2>
            <p>You have not placed any orders yet. Explore our latest drops and place your first order.</p>
            <a href="/ferro831/products.php" class="btn-primary" style="display:inline-flex;">Shop Products</a>
        </div>
    <?php else: ?>
        <div class="order-history-wrap">
            <table class="order-history-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tracking</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars((string)$order['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string)$order['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>Rs <?php echo htmlspecialchars((string)$order['total_amount'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="order-status-pill <?php echo htmlspecialchars(orderStatusClass((string)$order['order_status']), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars((string)$order['order_status'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($order['tracking_number'])): ?>
                                    <span class="order-tracking-code"><?php echo htmlspecialchars((string)$order['tracking_number'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php else: ?>
                                    <span class="order-tracking-pending">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/ferro831/user/view-order.php?id=<?php echo (int)$order['id']; ?>" class="btn-card">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
