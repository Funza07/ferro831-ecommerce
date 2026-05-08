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

<?php if (!isset($order) || !$order): ?>
    <section class="section">
        <div class="order-empty-state">
            <h2>Order Not Found</h2>
            <p><?php echo htmlspecialchars((string)($not_found_message ?? 'Order not found.'), ENT_QUOTES, 'UTF-8'); ?></p>
            <a href="/ferro831/user/orders.php" class="btn-primary" style="display:inline-flex;">Back to Orders</a>
        </div>
    </section>
<?php else: ?>
    <div class="page-header">
        <p class="page-header-eyebrow">Order Details</p>
        <h1>Order #<?php echo htmlspecialchars((string)$order['id'], ENT_QUOTES, 'UTF-8'); ?></h1>
    </div>

    <section class="section">
        <div class="order-detail-grid">
            <div class="order-detail-card">
                <p class="order-detail-title">Customer Details</p>
                <div class="order-detail-row"><span>Name</span><strong><?php echo htmlspecialchars((string)$order['customer_name'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <div class="order-detail-row"><span>Email</span><strong><?php echo htmlspecialchars((string)$order['customer_email'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <div class="order-detail-row"><span>Phone</span><strong><?php echo htmlspecialchars((string)$order['customer_phone'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <div class="order-detail-row"><span>Address</span><strong><?php echo nl2br(htmlspecialchars((string)$order['customer_address'], ENT_QUOTES, 'UTF-8')); ?></strong></div>
            </div>

            <div class="order-detail-card">
                <p class="order-detail-title">Tracking & Status</p>
                <div class="order-detail-row"><span>Date</span><strong><?php echo htmlspecialchars((string)$order['created_at'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <div class="order-detail-row">
                    <span>Status</span>
                    <strong>
                        <span class="order-status-pill <?php echo htmlspecialchars(orderStatusClass((string)$order['order_status']), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars((string)$order['order_status'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </strong>
                </div>
                <div class="order-detail-row"><span>Tracking</span><strong><?php echo !empty($order['tracking_number']) ? htmlspecialchars((string)$order['tracking_number'], ENT_QUOTES, 'UTF-8') : 'Pending'; ?></strong></div>
                <div class="order-detail-row"><span>Total</span><strong>Rs <?php echo htmlspecialchars((string)$order['total_amount'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
            </div>
        </div>

        <div class="order-items-card">
            <p class="order-detail-title">Order Timeline</p>

            <?php if (empty($status_history)): ?>
                <p style="color:var(--mid-grey);">No status updates available yet.</p>
            <?php else: ?>
                <div class="order-timeline">
                    <?php foreach ($status_history as $history): ?>
                        <div class="order-timeline-item">
                            <div class="order-timeline-dot <?php echo htmlspecialchars(orderStatusClass((string)$history['status']), ENT_QUOTES, 'UTF-8'); ?>"></div>
                            <div class="order-timeline-content">
                                <div class="order-timeline-head">
                                    <span class="order-status-pill <?php echo htmlspecialchars(orderStatusClass((string)$history['status']), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars((string)$history['status'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <span class="order-timeline-date"><?php echo htmlspecialchars((string)$history['created_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <p class="order-timeline-note"><?php echo htmlspecialchars((string)($history['note'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="order-items-card">
            <p class="order-detail-title">Order Items</p>

            <?php if (empty($order_items)): ?>
                <p style="color:var(--mid-grey);">No items found for this order.</p>
            <?php else: ?>
                <table class="order-history-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars((string)$item['product_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if (!empty($item['size']) || !empty($item['color'])): ?>
                                        <div style="font-size:12px;color:var(--mid-grey);">
                                            <?php echo htmlspecialchars((string)($item['size'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if (!empty($item['size']) && !empty($item['color'])): ?> / <?php endif; ?>
                                            <?php echo htmlspecialchars((string)($item['color'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if (!empty($item['sku'])): ?> | SKU: <?php echo htmlspecialchars((string)$item['sku'], ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>Rs <?php echo htmlspecialchars((string)$item['product_price'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string)$item['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>Rs <?php echo htmlspecialchars((string)$item['total_price'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div style="margin-top:24px;">
            <a href="/ferro831/user/orders.php" class="btn-primary" style="display:inline-flex;">Back to My Orders</a>
        </div>
    </section>
<?php endif; ?>
