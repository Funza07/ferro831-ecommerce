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

$status_order = ['Pending', 'Confirmed', 'Packed', 'Shipped', 'Delivered', 'Cancelled'];
$status_colors = [
    'Pending' => '#EF9F27',
    'Confirmed' => '#378ADD',
    'Packed' => '#5CA5DD',
    'Shipped' => '#7F77DD',
    'Delivered' => '#1D9E75',
    'Cancelled' => '#D85A30',
];

$labels = [];
$revenue_data = [];
$orders_data = [];
if (!empty($revenue_last_days) && is_array($revenue_last_days)) {
    foreach ($revenue_last_days as $row) {
        $day = (string)($row['date'] ?? '');
        $labels[] = $day !== '' ? date('M j', strtotime($day)) : '';
        $revenue_data[] = (float)($row['revenue'] ?? 0);
        $orders_data[] = (int)($row['orders'] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Ferro831 Admin</title>
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
      <a href="dashboard.php" class="nav-item active"><span class="nav-icon">+</span> Dashboard</a>
      <a href="products.php" class="nav-item"><span class="nav-icon">#</span> Products</a>\n      <a href="add-product.php" class="nav-item"><span class="nav-icon">+</span> Add Product</a>
      <a href="orders.php" class="nav-item"><span class="nav-icon">*</span> Orders</a>
      <a href="../index.php" class="nav-item"><span class="nav-icon">></span> View Website</a>
      <a href="../logout.php" class="nav-item"><span class="nav-icon">x</span> Logout</a>
    </nav>

    <div class="sidebar-footer">
      <div class="admin-tag"><?php echo htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8'); ?> &nbsp;|&nbsp; ADMIN</div>
    </div>
  </aside>

  <main class="admin-main">
    <header class="admin-header">
      <span class="page-title">Dashboard</span>
      <span class="header-divider"></span>
      <span class="header-breadcrumb">ferro831 / admin</span>
      <span class="header-spacer"></span>
      <span class="header-badge">Live</span>
    </header>

    <div class="admin-content admin-dashboard-wrap">
      <div class="admin-dashboard-topbar">
        <span class="admin-dashboard-title">Business Dashboard</span>
        <span class="admin-dashboard-date">Today: <?php echo htmlspecialchars(date('M j, Y'), ENT_QUOTES, 'UTF-8'); ?></span>
      </div>

      <div class="admin-stats-grid">
        <div class="admin-stat-card">
          <div class="admin-stat-label">Parent Products</div>
          <div class="admin-stat-value"><?php echo (int)$total_products; ?></div>
          <div class="admin-stat-helper">Parent designs</div>
        </div>
        <div class="admin-stat-card is-pending">
          <div class="admin-stat-label">Total SKUs</div>
          <div class="admin-stat-value"><?php echo (int)$total_variant_count; ?></div>
          <div class="admin-stat-helper">Active variants</div>
        </div>
        <div class="admin-stat-card is-revenue">
          <div class="admin-stat-label">Variant Stock</div>
          <div class="admin-stat-value"><?php echo (int)$total_stock_available; ?></div>
          <div class="admin-stat-helper">Variant stock (fallback to product stock)</div>
        </div>
        <div class="admin-stat-card is-low">
          <div class="admin-stat-label">Low Stock SKUs</div>
          <div class="admin-stat-value"><?php echo (int)$low_stock_count; ?></div>
          <div class="admin-stat-helper">Restock soon</div>
        </div>
      </div>

      <div class="admin-stats-grid">
        <div class="admin-stat-card">
          <div class="admin-stat-label">Total Orders</div>
          <div class="admin-stat-value"><?php echo (int)$total_orders; ?></div>
          <div class="admin-stat-helper"><?php echo (int)$orders_this_week; ?> in last 7 days</div>
        </div>
        <div class="admin-stat-card is-pending">
          <div class="admin-stat-label">Pending Orders</div>
          <div class="admin-stat-value"><?php echo (int)$pending_orders; ?></div>
          <div class="admin-stat-helper">Needs attention</div>
        </div>
        <div class="admin-stat-card">
          <div class="admin-stat-label">Total Units Sold</div>
          <div class="admin-stat-value"><?php echo (int)$total_units_sold; ?></div>
          <div class="admin-stat-helper">All-time sold count</div>
        </div>
        <div class="admin-stat-card is-revenue">
          <div class="admin-stat-label">Delivered Revenue</div>
          <div class="admin-stat-value">Rs <?php echo htmlspecialchars(number_format((float)$delivered_revenue, 2), ENT_QUOTES, 'UTF-8'); ?></div>
          <div class="admin-stat-helper">Delivered orders only</div>
        </div>
      </div>

      <section class="admin-panel">
        <div class="admin-panel-head">
          <h2>Revenue - Last 7 Days</h2>
        </div>
        <div class="admin-chart-wrap">
          <canvas id="adminRevenueChart" aria-label="Revenue and order chart"></canvas>
        </div>
      </section>

      <div class="admin-two-col-panels">
        <section class="admin-panel">
          <div class="admin-panel-head"><h2>Orders by Status</h2></div>
          <?php
          $max_status = 1;
          foreach ($status_order as $status_name) {
              $max_status = max($max_status, (int)($status_counts[$status_name] ?? 0));
          }
          ?>
          <?php foreach ($status_order as $status_name): ?>
            <?php
            $count = (int)($status_counts[$status_name] ?? 0);
            $width = (int)round(($count / $max_status) * 100);
            $color = $status_colors[$status_name] ?? '#999';
            ?>
            <div class="admin-status-row">
              <span class="admin-status-dot" style="background:<?php echo htmlspecialchars($color, ENT_QUOTES, 'UTF-8'); ?>"></span>
              <span class="admin-status-name"><?php echo htmlspecialchars($status_name, ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="admin-status-bar"><span style="width:<?php echo $width; ?>%;background:<?php echo htmlspecialchars($color, ENT_QUOTES, 'UTF-8'); ?>"></span></span>
              <span class="admin-status-count"><?php echo $count; ?></span>
            </div>
          <?php endforeach; ?>
        </section>

        <section class="admin-panel">
          <div class="admin-panel-head"><h2>Top Selling Products</h2></div>
          <?php if (empty($top_selling_products)): ?>
            <div class="empty-state">NO SALES DATA YET</div>
          <?php else: ?>
            <?php foreach ($top_selling_products as $idx => $item): ?>
              <?php
              $units = (int)($item['units_sold'] ?? 0);
              $price = (float)($item['price'] ?? 0);
              $est = $units * $price;
              $img = trim((string)($item['image'] ?? ''));
              ?>
              <div class="admin-top-product">
                <div class="admin-top-rank"><?php echo (int)$idx + 1; ?></div>
                <div class="admin-top-product-img">
                  <?php if ($img !== ''): ?>
                    <img class="admin-product-thumb" src="../assets/images/<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string)$item['product_name'], ENT_QUOTES, 'UTF-8'); ?>">
                  <?php else: ?>
                    <span class="admin-product-thumb-placeholder" aria-hidden="true">□</span>
                  <?php endif; ?>
                </div>
                <div class="admin-top-info">
                  <div class="admin-top-name"><?php echo htmlspecialchars((string)$item['product_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                  <div class="admin-top-units"><?php echo $units; ?> units sold</div>
                </div>
                <div class="admin-top-rev">Rs <?php echo htmlspecialchars(number_format($est, 2), ENT_QUOTES, 'UTF-8'); ?></div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </section>
      </div>

      <section class="admin-panel">
        <div class="admin-panel-head"><h2>Low Stock Variant SKUs</h2></div>
        <?php if (empty($low_stock_variants)): ?>
          <div class="empty-state">NO LOW STOCK SKUS</div>
        <?php else: ?>
          <div class="table-wrapper">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Variant</th>
                  <th>SKU</th>
                  <th>Stock</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($low_stock_variants as $v): ?>
                  <tr>
                    <td class="customer-name"><?php echo htmlspecialchars((string)$v['product_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$v['size'], ENT_QUOTES, 'UTF-8'); ?> / <?php echo htmlspecialchars((string)$v['color'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td class="admin-sku-text"><?php echo htmlspecialchars((string)($v['sku'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="admin-variant-stock-badge-text"><?php echo (int)$v['stock']; ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>

      <section class="admin-panel">
        <div class="admin-panel-head">
          <h2>Recent Orders</h2>
          <button type="button" class="export-btn" id="exportCsvBtn">Export CSV</button>
        </div>

        <div class="admin-search-wrap">
          <input type="text" id="orderSearchInput" placeholder="Search by name, phone, order ID...">
        </div>

        <div class="admin-filter-pills" id="statusPills">
          <button type="button" class="fpill active" data-status="All">All</button>
          <?php foreach ($status_order as $status_name): ?>
            <button type="button" class="fpill" data-status="<?php echo htmlspecialchars($status_name, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($status_name, ENT_QUOTES, 'UTF-8'); ?></button>
          <?php endforeach; ?>
        </div>

        <div class="table-wrapper">
          <table class="admin-table admin-orders-table" id="recentOrdersTable">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recent_orders)): ?>
                <tr><td colspan="6"><div class="empty-state">NO ORDERS YET</div></td></tr>
              <?php else: ?>
                <?php foreach ($recent_orders as $order): ?>
                  <?php
                  $id = (int)$order['id'];
                  $name = (string)($order['customer_name'] ?? '');
                  $phone = (string)($order['customer_phone'] ?? '');
                  $status = (string)($order['order_status'] ?? 'Pending');
                  $phone_digits = preg_replace('/\D+/', '', $phone);
                  $wa_href = $phone_digits !== '' ? 'https://wa.me/91' . $phone_digits : '';
                  ?>
                  <tr
                    data-status="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>"
                    data-search="<?php echo htmlspecialchars(strtolower('#' . $id . ' ' . $name . ' ' . $phone), ENT_QUOTES, 'UTF-8'); ?>"
                  >
                    <td><span class="order-id">#<?php echo $id; ?></span></td>
                    <td><span class="customer-name"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td><span class="amount">Rs <?php echo htmlspecialchars(number_format((float)$order['total_amount'], 2), ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td><span class="status-badge <?php echo htmlspecialchars(statusClass($status), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td><span class="date-cell"><?php echo htmlspecialchars((string)$order['created_at'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td>
                      <div class="action-btns">
                        <a href="view-order.php?id=<?php echo $id; ?>" class="act-btn">View</a>
                        <?php if ($wa_href !== ''): ?>
                          <a href="<?php echo htmlspecialchars($wa_href, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="act-btn wa">WA</a>
                        <?php else: ?>
                          <button type="button" class="act-btn wa" disabled>WA</button>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
  (function () {
    const toggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggle && sidebar && overlay) {
      toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
      });
      overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
      });
    }

    const labels = <?php echo json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;
    const revenueData = <?php echo json_encode($revenue_data, JSON_UNESCAPED_UNICODE); ?>;
    const ordersData = <?php echo json_encode($orders_data, JSON_UNESCAPED_UNICODE); ?>;
    const chartEl = document.getElementById('adminRevenueChart');

    if (chartEl && window.Chart) {
      new Chart(chartEl, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Revenue',
              data: revenueData,
              borderColor: '#1D9E75',
              backgroundColor: 'rgba(29,158,117,0.12)',
              fill: true,
              tension: 0.35,
              pointRadius: 3,
              pointBackgroundColor: '#1D9E75',
              borderWidth: 2
            },
            {
              label: 'Orders',
              data: ordersData,
              borderColor: '#378ADD',
              fill: false,
              tension: 0.35,
              pointRadius: 3,
              pointBackgroundColor: '#378ADD',
              borderWidth: 2,
              borderDash: [4, 3],
              yAxisID: 'y2'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }
          },
          scales: {
            x: { ticks: { color: '#7f8a95' }, grid: { color: 'rgba(255,255,255,0.06)' } },
            y: {
              ticks: {
                color: '#7f8a95',
                callback: function (v) { return 'Rs ' + Number(v).toLocaleString(); }
              },
              grid: { color: 'rgba(255,255,255,0.06)' }
            },
            y2: {
              position: 'right',
              ticks: { color: '#7f8a95' },
              grid: { display: false }
            }
          }
        }
      });
    }

    const table = document.getElementById('recentOrdersTable');
    const searchInput = document.getElementById('orderSearchInput');
    const pillsWrap = document.getElementById('statusPills');
    const exportBtn = document.getElementById('exportCsvBtn');
    let currentStatus = 'All';

    function applyFilters() {
      if (!table) return;
      const rows = table.querySelectorAll('tbody tr');
      const q = (searchInput ? searchInput.value.trim().toLowerCase() : '');

      rows.forEach((row) => {
        const status = (row.getAttribute('data-status') || '');
        const searchText = (row.getAttribute('data-search') || '');
        const matchStatus = currentStatus === 'All' || status === currentStatus;
        const matchSearch = q === '' || searchText.indexOf(q) !== -1;
        row.style.display = (matchStatus && matchSearch) ? '' : 'none';
      });
    }

    if (searchInput) {
      searchInput.addEventListener('input', applyFilters);
    }

    if (pillsWrap) {
      pillsWrap.addEventListener('click', function (event) {
        const btn = event.target.closest('.fpill');
        if (!btn) return;
        currentStatus = btn.getAttribute('data-status') || 'All';
        pillsWrap.querySelectorAll('.fpill').forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        applyFilters();
      });
    }

    if (exportBtn && table) {
      exportBtn.addEventListener('click', function () {
        const rows = Array.from(table.querySelectorAll('tbody tr')).filter((row) => row.style.display !== 'none');
        const csv = [];
        csv.push(['Order ID', 'Customer', 'Total', 'Status', 'Date'].join(','));
        rows.forEach((row) => {
          const cells = row.querySelectorAll('td');
          if (cells.length < 5) return;
          const orderId = cells[0].innerText.trim();
          const customer = cells[1].innerText.trim();
          const total = cells[2].innerText.trim();
          const status = cells[3].innerText.trim();
          const date = cells[4].innerText.trim();
          const line = [orderId, customer, total, status, date].map((v) => '"' + v.replace(/"/g, '""') + '"').join(',');
          csv.push(line);
        });
        const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'ferro831_recent_orders.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
      });
    }
  })();
</script>

</body>
</html>

