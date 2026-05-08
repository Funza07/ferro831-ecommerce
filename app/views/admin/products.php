<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products - Ferro831 Admin</title>
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
      <a href="dashboard.php" class="nav-item"><span class="nav-icon">+</span> Dashboard</a>
      <a href="products.php" class="nav-item active"><span class="nav-icon">#</span> Products</a>
      <a href="add-product.php" class="nav-item"><span class="nav-icon">+</span> Add Product</a>
      <a href="orders.php" class="nav-item"><span class="nav-icon">*</span> Orders</a>
      <a href="../index.php" class="nav-item"><span class="nav-icon">></span> View Website</a>
      <a href="../logout.php" class="nav-item"><span class="nav-icon">x</span> Logout</a>
    </nav>
    <div class="sidebar-footer">
      <div class="admin-tag"><?php echo $admin_name; ?> &nbsp;|&nbsp; ADMIN</div>
    </div>
  </aside>

  <main class="admin-main">
    <header class="admin-header">
      <span class="page-title">Products</span>
      <span class="header-divider"></span>
      <span class="header-breadcrumb">ferro831 / admin / products</span>
      <span class="header-spacer"></span>
      <span class="header-badge">Live</span>
    </header>

    <div class="admin-content">
      <div class="section-title">Inventory Visibility</div>
      <div class="table-wrapper">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Image</th>
              <th>Product</th>
              <th>Category</th>
              <th>Base Price</th>
              <th>Compare</th>
              <th>Parent Stock</th>
              <th>Variant Count</th>
              <th>Variant Stock</th>
              <th>Low-Stock Variants</th>
              <th>Sold</th>
              <th>Created</th>
              <th>Inventory Source</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($products)): ?>
              <tr><td colspan="13"><div class="empty-state">NO PRODUCTS FOUND</div></td></tr>
            <?php else: ?>
              <?php foreach ($products as $p): ?>
                <?php
                $image = trim((string)($p['image'] ?? ''));
                $variantCount = (int)$p['variant_count'];
                $variantStock = (int)$p['variant_stock_sum'];
                $stockDisplay = $variantCount > 0 ? $variantStock : (int)$p['stock'];
                ?>
                <tr>
                  <td>
                    <?php if ($image !== ''): ?>
                      <img src="../assets/images/<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" alt="thumb" style="width:42px;height:42px;object-fit:cover;border-radius:6px;border:1px solid rgba(74,127,165,.25);">
                    <?php else: ?>
                      <span class="admin-product-thumb-placeholder">No image</span>
                    <?php endif; ?>
                  </td>
                  <td class="customer-name"><?php echo htmlspecialchars((string)$p['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars((string)$p['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td>Rs <?php echo htmlspecialchars(number_format((float)$p['price'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo $p['compare_price'] !== null ? ('Rs ' . htmlspecialchars(number_format((float)$p['compare_price'], 2), ENT_QUOTES, 'UTF-8')) : '-'; ?></td>
                  <td><?php echo (int)$p['stock']; ?></td>
                  <td><?php echo $variantCount; ?></td>
                  <td><?php echo $variantStock; ?></td>
                  <td><?php echo (int)$p['low_stock_variant_count']; ?></td>
                  <td><?php echo (int)$p['sold_count']; ?></td>
                  <td><span class="date-cell"><?php echo htmlspecialchars((string)$p['created_at'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                  <td><?php echo $variantCount > 0 ? 'Variant stock (' . $stockDisplay . ')' : 'Product stock (' . $stockDisplay . ')'; ?></td>
                  <td>
                    <div class="action-btns">
                      <a href="../product.php?id=<?php echo (int)$p['id']; ?>" class="act-btn" target="_blank" rel="noopener noreferrer">View</a>
                      <a href="manage-variants.php?product_id=<?php echo (int)$p['id']; ?>" class="act-btn">Manage Variants</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
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
