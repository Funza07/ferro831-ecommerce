<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Product - Ferro831 Admin</title>
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
      <a href="add-product.php" class="nav-item active">
        <span class="nav-icon">+</span> Add Product
      </a>
      <a href="orders.php" class="nav-item">
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
      <span class="page-title">Add Product</span>
      <span class="header-divider"></span>
      <span class="header-breadcrumb">ferro831 / admin / add-product</span>
      <span class="header-spacer"></span>
      <span class="header-badge">Live</span>
    </header>

    <div class="admin-content">
      <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message !== '' ? $success_message : 'Product added successfully to your catalog.', ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          Please fix the following:
          <ul style="margin:10px 0 0 18px;">
            <?php foreach ($errors as $e): ?>
              <li><?php echo htmlspecialchars((string)$e, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($db_error !== ''): ?>
        <div class="alert alert-error">Database error: <?php echo htmlspecialchars($db_error, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="admin-product-form">
        <section class="admin-form-section">
          <div class="admin-section-header">
            <h2>Product Basics</h2>
          </div>
          <div class="admin-form-grid">
            <div class="admin-form-group">
              <label for="name">Product Name</label>
              <input type="text" id="name" name="name" placeholder="e.g. Ferro Cargo Tee" required value="<?php echo htmlspecialchars((string)($old_input['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group">
              <label for="category">Category</label>
              <input type="text" id="category" name="category" placeholder="e.g. T-Shirts, Hoodies" required value="<?php echo htmlspecialchars((string)($old_input['category'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group">
              <label for="price">Price (Rs)</label>
              <input type="number" id="price" name="price" placeholder="e.g. 999" required min="0" step="0.01" value="<?php echo htmlspecialchars((string)($old_input['price'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group">
              <label for="stock">Stock Quantity</label>
              <input type="number" id="stock" name="stock" placeholder="e.g. 50" required min="0" value="<?php echo htmlspecialchars((string)($old_input['stock'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
          </div>
        </section>

        <section class="admin-form-section">
          <div class="admin-section-header">
            <h2>Product Images</h2>
          </div>
          <div class="admin-upload-box">
            <div class="admin-form-group">
              <label for="images">Upload Images</label>
              <input type="file" id="images" name="images[]" accept=".jpg,.jpeg,.png,.webp" multiple>
            </div>
            <p class="admin-stat-helper">Upload one or more product images. First image is used as primary.</p>
          </div>
        </section>

        <section class="admin-form-section">
          <div class="admin-section-header">
            <h2>Description</h2>
          </div>
          <div class="admin-form-group">
            <label for="description">Product Description</label>
            <textarea id="description" name="description" placeholder="Describe material, fit, print, origin, and product story..."><?php echo htmlspecialchars((string)($old_input['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
          </div>
        </section>

        <section class="admin-form-section admin-variant-section">
          <div class="admin-section-header">
            <h2>Product Variants / SKUs</h2>
          </div>
          <p class="admin-stat-helper">Leave all variant rows empty to use product-level stock only.</p>
          <p class="admin-stat-helper">Optional. Leave blank to use base product price. Enter full final selling price, not extra amount.</p>

          <div class="admin-variant-table">
            <div class="admin-variant-row admin-variant-head">
              <div class="admin-variant-field">Size</div>
              <div class="admin-variant-field">Color</div>
              <div class="admin-variant-field">Hex</div>
              <div class="admin-variant-field">SKU</div>
              <div class="admin-variant-field">Stock</div>
              <div class="admin-variant-field">Final Price Override</div>
              <div class="admin-variant-field">Status</div>
              <div class="admin-variant-field">Action</div>
            </div>
            <div id="adminVariantRows">
              <?php $oldVariants = is_array($old_input['variants'] ?? null) ? $old_input['variants'] : []; ?>
              <?php if (empty($oldVariants)): ?>
                <div class="admin-variant-row">
                  <div class="admin-variant-field"><input type="text" name="variants[0][size]" placeholder="Size" value="S"></div>
                  <div class="admin-variant-field"><input type="text" name="variants[0][color]" placeholder="Color" value="Black"></div>
                  <div class="admin-variant-field">
                    <div class="admin-variant-hex-wrap">
                      <input type="text" name="variants[0][color_hex]" placeholder="#111111" value="#111111" class="admin-color-hex-input">
                      <span class="admin-variant-stock-badge" style="background:#111111;"></span>
                    </div>
                  </div>
                  <div class="admin-variant-field"><input type="text" name="variants[0][sku]" placeholder="SKU optional"></div>
                  <div class="admin-variant-field"><input type="number" name="variants[0][stock]" placeholder="0" min="0" value="0"></div>
                  <div class="admin-variant-field"><input type="number" name="variants[0][price_override]" placeholder="e.g. 1099" min="0" step="0.01"></div>
                  <div class="admin-variant-field">
                    <select name="variants[0][is_active]">
                      <option value="1" selected>Active</option>
                      <option value="0">Inactive</option>
                    </select>
                  </div>
                  <div class="admin-variant-actions">
                    <button type="button" class="admin-remove-variant-btn">Remove</button>
                  </div>
                </div>
              <?php else: ?>
                <?php foreach ($oldVariants as $idx => $v): ?>
                  <?php $hex = (string)($v['color_hex'] ?? '#111111'); ?>
                  <div class="admin-variant-row">
                    <div class="admin-variant-field"><input type="text" name="variants[<?php echo (int)$idx; ?>][size]" placeholder="Size" value="<?php echo htmlspecialchars((string)($v['size'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div>
                    <div class="admin-variant-field"><input type="text" name="variants[<?php echo (int)$idx; ?>][color]" placeholder="Color" value="<?php echo htmlspecialchars((string)($v['color'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div>
                    <div class="admin-variant-field">
                      <div class="admin-variant-hex-wrap">
                        <input type="text" name="variants[<?php echo (int)$idx; ?>][color_hex]" placeholder="#111111" value="<?php echo htmlspecialchars($hex, ENT_QUOTES, 'UTF-8'); ?>" class="admin-color-hex-input">
                        <span class="admin-variant-stock-badge" style="background:<?php echo htmlspecialchars($hex, ENT_QUOTES, 'UTF-8'); ?>;"></span>
                      </div>
                    </div>
                    <div class="admin-variant-field"><input type="text" name="variants[<?php echo (int)$idx; ?>][sku]" placeholder="SKU optional" value="<?php echo htmlspecialchars((string)($v['sku'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div>
                    <div class="admin-variant-field"><input type="number" name="variants[<?php echo (int)$idx; ?>][stock]" placeholder="0" min="0" value="<?php echo htmlspecialchars((string)($v['stock'] ?? '0'), ENT_QUOTES, 'UTF-8'); ?>"></div>
                    <div class="admin-variant-field"><input type="number" name="variants[<?php echo (int)$idx; ?>][price_override]" placeholder="e.g. 1099" min="0" step="0.01" value="<?php echo htmlspecialchars((string)($v['price_override'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div>
                    <div class="admin-variant-field">
                      <select name="variants[<?php echo (int)$idx; ?>][is_active]">
                        <option value="1" <?php echo ((string)($v['is_active'] ?? '1') !== '0') ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo ((string)($v['is_active'] ?? '1') === '0') ? 'selected' : ''; ?>>Inactive</option>
                      </select>
                    </div>
                    <div class="admin-variant-actions">
                      <button type="button" class="admin-remove-variant-btn">Remove</button>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <div style="margin-top:12px;">
            <button type="button" class="admin-add-variant-btn" id="adminAddVariantBtn">Add Variant Row</button>
          </div>
        </section>

        <div class="admin-form-actions">
          <button type="submit" name="add_product" class="btn-primary">Add Product</button>
          <a href="dashboard.php" class="btn-secondary">Cancel</a>
        </div>
      </form>
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

  (function () {
    const rowsWrap = document.getElementById('adminVariantRows');
    const addBtn = document.getElementById('adminAddVariantBtn');
    if (!rowsWrap || !addBtn) return;

    function rowCount() {
      return rowsWrap.querySelectorAll('.admin-variant-row').length;
    }

    function bindRow(row) {
      const removeBtn = row.querySelector('.admin-remove-variant-btn');
      if (removeBtn) {
        removeBtn.addEventListener('click', function () {
          if (rowCount() <= 1) {
            row.querySelectorAll('input').forEach(function (input) {
              if (input.type === 'number') {
                input.value = input.name.indexOf('[stock]') !== -1 ? '0' : '';
              } else {
                input.value = '';
              }
            });
            const activeSelect = row.querySelector('select');
            if (activeSelect) activeSelect.value = '1';
            const dot = row.querySelector('.admin-variant-stock-badge');
            if (dot) dot.style.background = '#111111';
            return;
          }
          row.remove();
          reindexRows();
        });
      }

      const hexInput = row.querySelector('.admin-color-hex-input');
      const dot = row.querySelector('.admin-variant-stock-badge');
      if (hexInput && dot) {
        const applyHex = function () {
          const v = (hexInput.value || '').trim();
          dot.style.background = /^#[0-9a-fA-F]{6}$/.test(v) ? v : '#111111';
        };
        hexInput.addEventListener('input', applyHex);
        applyHex();
      }
    }

    function reindexRows() {
      rowsWrap.querySelectorAll('.admin-variant-row').forEach(function (row, idx) {
        row.querySelectorAll('input,select').forEach(function (field) {
          const name = field.getAttribute('name') || '';
          field.setAttribute('name', name.replace(/variants\[\d+\]/, 'variants[' + idx + ']'));
        });
      });
    }

    function addRow() {
      const idx = rowCount();
      const row = document.createElement('div');
      row.className = 'admin-variant-row';
      row.innerHTML = ''
        + '<div class="admin-variant-field"><input type="text" name="variants[' + idx + '][size]" placeholder="Size"></div>'
        + '<div class="admin-variant-field"><input type="text" name="variants[' + idx + '][color]" placeholder="Color"></div>'
        + '<div class="admin-variant-field"><div class="admin-variant-hex-wrap"><input type="text" class="admin-color-hex-input" name="variants[' + idx + '][color_hex]" placeholder="#111111" value="#111111"><span class="admin-variant-stock-badge" style="background:#111111;"></span></div></div>'
        + '<div class="admin-variant-field"><input type="text" name="variants[' + idx + '][sku]" placeholder="SKU optional"></div>'
        + '<div class="admin-variant-field"><input type="number" name="variants[' + idx + '][stock]" placeholder="0" min="0" value="0"></div>'
        + '<div class="admin-variant-field"><input type="number" name="variants[' + idx + '][price_override]" placeholder="e.g. 1099" min="0" step="0.01"></div>'
        + '<div class="admin-variant-field"><select name="variants[' + idx + '][is_active]"><option value="1" selected>Active</option><option value="0">Inactive</option></select></div>'
        + '<div class="admin-variant-actions"><button type="button" class="admin-remove-variant-btn">Remove</button></div>';
      rowsWrap.appendChild(row);
      bindRow(row);
      reindexRows();
    }

    addBtn.addEventListener('click', addRow);
    rowsWrap.querySelectorAll('.admin-variant-row').forEach(bindRow);
  })();
</script>

</body>
</html>

