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
  <title>Orders - Ferro831 Admin</title>
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
      <span class="page-title">Orders</span>
      <span class="header-divider"></span>
      <span class="header-breadcrumb">ferro831 / admin / orders</span>
      <span class="header-spacer"></span>
      <span class="header-badge">Live</span>
    </header>

    <div class="admin-content">

      <div class="section-title">All Orders</div>

      <?php if (empty($orders)): ?>
        <div class="admin-card">
          <div class="empty-state">NO ORDERS YET | CHECK BACK SOON</div>
        </div>
      <?php else: ?>

      <div class="table-wrapper">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Phone</th>
              <th>Total</th>
              <th>Status</th>
              <th>Tracking</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><span class="order-id">#<?php echo htmlspecialchars((string)$order['id'], ENT_QUOTES, 'UTF-8'); ?></span></td>
              <td><span class="customer-name"><?php echo htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8'); ?></span></td>
              <td><?php echo htmlspecialchars($order['customer_phone'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><span class="amount">Rs <?php echo htmlspecialchars((string)$order['total_amount'], ENT_QUOTES, 'UTF-8'); ?></span></td>
              <td>
                <span class="status-badge <?php echo statusClass($order['order_status']); ?>">
                  <?php echo htmlspecialchars($order['order_status'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
              </td>
              <td>
                <?php if (!empty($order['tracking_number'])): ?>
                  <span class="admin-tracking-code"><?php echo htmlspecialchars((string)$order['tracking_number'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php else: ?>
                  <span class="admin-tracking-pending">Pending</span>
                <?php endif; ?>
              </td>
              <td><span class="date-cell"><?php echo htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8'); ?></span></td>
              <td>
                <a href="view-order.php?id=<?php echo (int)$order['id']; ?>" class="btn-view">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php endif; ?>

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

