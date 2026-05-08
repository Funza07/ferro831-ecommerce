<?php
if (!function_exists('statusClass')) {
    function statusClass($status) {
        $map = [
            'Pending' => 'status-pending',
            'Confirmed' => 'status-confirmed',
            'Packed' => 'status-packed',
            'Shipped' => 'status-shipped',
            'Delivered' => 'status-delivered',
            'Cancelled' => 'status-cancelled',
        ];
        return $map[$status] ?? 'status-pending';
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order #<?php echo (int)$order['id']; ?> - Ferro831 Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700;800&family=Barlow:wght@300;400;500;600&family=Share+Tech+Mono&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<button class="mobile-menu-toggle" id="menuToggle" aria-label="Toggle menu">
  <span></span><span></span><span></span>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-layout">

  <aside class="admin-sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-name">FERRO<span>831</span></div>
      <div class="brand-sub">Admin Panel v1.0</div>
    </div>

    <div class="sidebar-label">Navigation</div>

    <nav class="sidebar-nav">
      <a href="dashboard.php" class="nav-item">
        <span class="nav-icon">+</span> Dashboard
      </a>
      <a href="add-product.php" class="nav-item">
        <span class="nav-icon">+</span> Add Product
      </a>
      <a href="orders.php" class="nav-item active">
        <span class="nav-icon">*</span> Orders
      </a>
      <a href="../index.php" class="nav-item">
        <span class="nav-icon">></span> View Website
      </a>
      <a href="../logout.php" class="nav-item">
        <span class="nav-icon">x</span> Logout
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="admin-tag"><?php echo $admin_name; ?> &nbsp;|&nbsp; ADMIN</div>
    </div>
  </aside>

  <main class="admin-main">

    <header class="admin-header">
      <span class="page-title">Order Details</span>
      <span class="header-divider"></span>
      <span class="header-breadcrumb">ferro831 / orders / #<?php echo (int)$order['id']; ?></span>
      <span class="header-spacer"></span>
      <a href="orders.php" class="btn-secondary" style="font-size:13px;padding:6px 16px;">Back</a>
    </header>

    <div class="admin-content">

      <?php if ($flash_message !== ''): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($flash_message, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <?php if ($flash_error !== ''): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($flash_error, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;flex-wrap:wrap;">
        <div class="section-title" style="margin-bottom:0;">
          Order <span style="color:var(--steel-light);">#<?php echo htmlspecialchars((string)$order['id'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <span class="status-badge <?php echo statusClass((string)$order['order_status']); ?>">
          <?php echo htmlspecialchars((string)$order['order_status'], ENT_QUOTES, 'UTF-8'); ?>
        </span>
      </div>

      <div class="order-detail-grid">
        <div class="detail-block">
          <div class="detail-block-title">Customer Info</div>
          <div class="detail-row">
            <span class="detail-key">Name</span>
            <span class="detail-val"><?php echo htmlspecialchars((string)$order['customer_name'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div class="detail-row">
            <span class="detail-key">Email</span>
            <span class="detail-val"><?php echo htmlspecialchars((string)$order['customer_email'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <div class="detail-row">
            <span class="detail-key">Phone</span>
            <span class="detail-val"><?php echo htmlspecialchars((string)$order['customer_phone'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        </div>

        <div class="detail-block">
          <div class="detail-block-title">Tracking & Status</div>
          <div class="detail-row">
            <span class="detail-key">Status</span>
            <span class="detail-val">
              <span class="status-badge <?php echo statusClass((string)$order['order_status']); ?>">
                <?php echo htmlspecialchars((string)$order['order_status'], ENT_QUOTES, 'UTF-8'); ?>
              </span>
            </span>
          </div>
          <div class="detail-row">
            <span class="detail-key">Tracking</span>
            <span class="detail-val">
              <?php echo !empty($order['tracking_number']) ? htmlspecialchars((string)$order['tracking_number'], ENT_QUOTES, 'UTF-8') : 'Pending'; ?>
            </span>
          </div>
          <div class="detail-row">
            <span class="detail-key">Date</span>
            <span class="detail-val"><?php echo htmlspecialchars((string)$order['created_at'], ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
        </div>
      </div>

      <div class="detail-block mb-4">
        <div class="detail-block-title">Delivery Address</div>
        <div class="detail-row">
          <span class="detail-key">Address</span>
          <span class="detail-val"><?php echo nl2br(htmlspecialchars((string)$order['customer_address'], ENT_QUOTES, 'UTF-8')); ?></span>
        </div>
      </div>

      <div class="section-title">Order Items</div>

      <div class="table-wrapper mb-4">
        <table class="admin-table">
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
              <td class="customer-name">
                <?php echo htmlspecialchars((string)$item['product_name'], ENT_QUOTES, 'UTF-8'); ?>
                <?php if (!empty($item['size']) || !empty($item['color'])): ?>
                  <div style="font-size:12px;color:#98a2ad;">
                    <?php echo htmlspecialchars((string)($item['size'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    <?php if (!empty($item['size']) && !empty($item['color'])): ?> / <?php endif; ?>
                    <?php echo htmlspecialchars((string)($item['color'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    <?php if (!empty($item['sku'])): ?> | SKU: <?php echo htmlspecialchars((string)$item['sku'], ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                  </div>
                <?php endif; ?>
              </td>
              <td>Rs <?php echo htmlspecialchars((string)$item['product_price'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars((string)$item['quantity'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><span class="amount">Rs <?php echo htmlspecialchars((string)$item['total_price'], ENT_QUOTES, 'UTF-8'); ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="total-summary">
        <span class="total-label">Order Total</span>
        <span class="total-amount">Rs <?php echo htmlspecialchars((string)$order['total_amount'], ENT_QUOTES, 'UTF-8'); ?></span>
      </div>

      <div class="admin-card mt-6">
        <div class="card-title">Update Order</div>
        <form method="POST" action="view-order.php?id=<?php echo (int)$order_id; ?>">
          <div class="status-update-form">
            <div class="status-form-field">
              <label for="order_status">Order Status</label>
              <select id="order_status" name="order_status" required>
                <?php foreach ($allowed_statuses as $status): ?>
                  <option value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ((string)$order['order_status'] === $status) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="status-form-field">
              <label for="tracking_number">Tracking Number</label>
              <input
                type="text"
                id="tracking_number"
                name="tracking_number"
                placeholder="Enter tracking number"
                value="<?php echo htmlspecialchars((string)($order['tracking_number'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
              >
            </div>

            <div class="status-form-field status-note-field">
              <label for="status_note">Status Note (Optional)</label>
              <textarea id="status_note" name="status_note" placeholder="Add note for status history..."></textarea>
            </div>

            <button type="submit" name="update_order" class="btn-primary">Save Update</button>
          </div>
        </form>
      </div>

      <div class="admin-card mt-6">
        <div class="card-title">Status History</div>
        <?php if (empty($status_history)): ?>
          <div class="empty-state" style="padding:24px 0;">NO STATUS HISTORY FOUND</div>
        <?php else: ?>
          <div class="admin-timeline">
            <?php foreach ($status_history as $history): ?>
              <div class="admin-timeline-item">
                <div class="admin-timeline-dot <?php echo statusClass((string)$history['status']); ?>"></div>
                <div class="admin-timeline-content">
                  <div class="admin-timeline-head">
                    <span class="status-badge <?php echo statusClass((string)$history['status']); ?>">
                      <?php echo htmlspecialchars((string)$history['status'], ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="admin-timeline-date"><?php echo htmlspecialchars((string)$history['created_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                  </div>
                  <p class="admin-timeline-note"><?php echo htmlspecialchars((string)($history['note'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>

</div>

<script>
  const toggle = document.getElementById('menuToggle');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');

  toggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
  });

  overlay.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('active');
  });
</script>

</body>
</html>

