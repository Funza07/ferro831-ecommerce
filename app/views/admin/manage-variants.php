<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Variants - Ferro831 Admin</title>
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
      <span class="page-title">Manage Variants</span>
      <span class="header-divider"></span>
      <span class="header-breadcrumb">ferro831 / admin / manage-variants</span>
      <span class="header-spacer"></span>
      <span class="header-badge">Live</span>
    </header>

    <div class="admin-content">
      <?php if ($flash_message !== ''): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($flash_message, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      <?php if ($flash_error !== ''): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($flash_error, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <div class="admin-card">
        <div class="card-title">Product: <?php echo htmlspecialchars((string)$product['name'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="admin-stat-helper">Category: <?php echo htmlspecialchars((string)$product['category'], ENT_QUOTES, 'UTF-8'); ?> | Base Price: Rs <?php echo htmlspecialchars(number_format((float)$product['price'], 2), ENT_QUOTES, 'UTF-8'); ?> | Parent Stock: <?php echo (int)$product['stock']; ?></div>
        <div class="admin-stat-helper">Active Variants: <?php echo (int)$summary['active_variant_count']; ?> | Inactive: <?php echo (int)$summary['inactive_variant_count']; ?> | Variant Stock: <?php echo (int)$summary['total_variant_stock']; ?> | Low Stock: <?php echo (int)$summary['low_stock_variant_count']; ?></div>
      </div>

      <div class="admin-card mt-4">
        <div class="card-title">Add Variant</div>
        <form method="POST" action="manage-variants.php?product_id=<?php echo (int)$product['id']; ?>">
          <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
          <div class="admin-variant-row" style="padding:0;grid-template-columns:.8fr .9fr .9fr 1fr .8fr .9fr .8fr auto;">
            <div class="admin-variant-field"><input type="text" name="size" placeholder="Size" required></div>
            <div class="admin-variant-field"><input type="text" name="color" placeholder="Color" required></div>
            <div class="admin-variant-field"><input type="text" name="color_hex" placeholder="#111111" value="#111111"></div>
            <div class="admin-variant-field"><input type="text" name="sku" placeholder="SKU optional"></div>
            <div class="admin-variant-field"><input type="number" name="stock" min="0" value="0" required></div>
            <div class="admin-variant-field"><input type="number" name="price_override" min="0" step="0.01" placeholder="Final price"></div>
            <div class="admin-variant-field">
              <select name="is_active">
                <option value="1" selected>Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
            <div class="admin-variant-actions">
              <button type="submit" name="add_variant" class="btn-primary" style="padding:10px 14px;font-size:12px;">Add</button>
            </div>
          </div>
        </form>
      </div>

      <div class="admin-card mt-4">
        <div class="card-title">Existing Variants</div>
        <?php if (empty($variants)): ?>
          <div class="empty-state">NO VARIANTS FOUND</div>
        <?php else: ?>
          <div class="table-wrapper">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Size</th>
                  <th>Color</th>
                  <th>Hex</th>
                  <th>SKU</th>
                  <th>Stock</th>
                  <th>Final Price</th>
                  <th>Status</th>
                  <th>Update</th>
                  <th>Toggle</th>
                  <th>Delete</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($variants as $v): ?>
                <tr>
                  <form method="POST" action="manage-variants.php?product_id=<?php echo (int)$product['id']; ?>">
                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                    <input type="hidden" name="variant_id" value="<?php echo (int)$v['id']; ?>">
                    <td>#<?php echo (int)$v['id']; ?></td>
                    <td><input type="text" name="size" value="<?php echo htmlspecialchars((string)$v['size'], ENT_QUOTES, 'UTF-8'); ?>" required></td>
                    <td><input type="text" name="color" value="<?php echo htmlspecialchars((string)$v['color'], ENT_QUOTES, 'UTF-8'); ?>" required></td>
                    <td><input type="text" name="color_hex" value="<?php echo htmlspecialchars((string)$v['color_hex'], ENT_QUOTES, 'UTF-8'); ?>"></td>
                    <td><input type="text" name="sku" value="<?php echo htmlspecialchars((string)$v['sku'], ENT_QUOTES, 'UTF-8'); ?>"></td>
                    <td><input type="number" name="stock" min="0" value="<?php echo (int)$v['stock']; ?>"></td>
                    <td><input type="number" name="price_override" min="0" step="0.01" value="<?php echo $v['price_override'] !== null ? htmlspecialchars((string)$v['price_override'], ENT_QUOTES, 'UTF-8') : ''; ?>"></td>
                    <td>
                      <select name="is_active">
                        <option value="1" <?php echo ((int)$v['is_active'] === 1) ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo ((int)$v['is_active'] === 0) ? 'selected' : ''; ?>>Inactive</option>
                      </select>
                    </td>
                    <td><button type="submit" name="update_variant" class="btn-view">Save</button></td>
                  </form>
                  <td>
                    <form method="POST" action="manage-variants.php?product_id=<?php echo (int)$product['id']; ?>">
                      <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                      <input type="hidden" name="variant_id" value="<?php echo (int)$v['id']; ?>">
                      <input type="hidden" name="is_active" value="<?php echo ((int)$v['is_active'] === 1) ? 0 : 1; ?>">
                      <button type="submit" name="toggle_variant_status" class="btn-secondary" style="padding:6px 10px;"><?php echo ((int)$v['is_active'] === 1) ? 'Deactivate' : 'Activate'; ?></button>
                    </form>
                  </td>
                  <td>
                    <form method="POST" action="manage-variants.php?product_id=<?php echo (int)$product['id']; ?>" onsubmit="return confirm('Delete/deactivate this variant?');">
                      <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                      <input type="hidden" name="variant_id" value="<?php echo (int)$v['id']; ?>">
                      <button type="submit" name="delete_variant" class="btn-secondary" style="padding:6px 10px;">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
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
