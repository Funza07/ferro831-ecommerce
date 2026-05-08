
// FERRO831 UX interactions + backend integration
(function () {
  let totalSeconds = 2 * 3600 + 14 * 60 + 37;
  const countdownEl = document.getElementById('countdownDisplay');
  if (countdownEl) {
    setInterval(function () {
      if (totalSeconds <= 0) {
        countdownEl.textContent = 'ENDED';
        return;
      }
      totalSeconds--;
      const h = Math.floor(totalSeconds / 3600);
      const m = Math.floor((totalSeconds % 3600) / 60);
      const s = totalSeconds % 60;
      countdownEl.textContent = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }, 1000);
  }

  const backTop = document.getElementById('backTop');
  if (backTop) {
    window.addEventListener('scroll', function () {
      backTop.classList.toggle('visible', window.scrollY > 300);
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeSearch();
      closeMobileMenu();
      closeCartDrawer();
    }
  });
})();

function closeAnnouncement() {
  const el = document.getElementById('announcement');
  if (el) el.style.display = 'none';
}

function openMobileMenu() {
  const el = document.getElementById('mobileMenu');
  const overlay = document.getElementById('mobileMenuOverlay');
  if (el) {
    el.classList.add('open');
    el.setAttribute('aria-hidden', 'false');
  }
  if (overlay) {
    overlay.classList.add('open');
    overlay.setAttribute('aria-hidden', 'false');
  }
  document.body.classList.add('ferro-mobile-menu-open');
}

function closeMobileMenu() {
  const el = document.getElementById('mobileMenu');
  const overlay = document.getElementById('mobileMenuOverlay');
  if (el) {
    el.classList.remove('open');
    el.setAttribute('aria-hidden', 'true');
  }
  if (overlay) {
    overlay.classList.remove('open');
    overlay.setAttribute('aria-hidden', 'true');
  }
  document.body.classList.remove('ferro-mobile-menu-open');
}

function openSearch() {
  const overlay = document.getElementById('searchOverlay');
  const input = document.getElementById('searchInput');
  if (overlay) {
    overlay.classList.add('open');
    overlay.setAttribute('aria-hidden', 'false');
  }
  if (input) setTimeout(() => input.focus(), 250);
}

function closeSearch() {
  const overlay = document.getElementById('searchOverlay');
  if (overlay) {
    overlay.classList.remove('open');
    overlay.setAttribute('aria-hidden', 'true');
  }
}

function searchProducts() {
  const input = document.getElementById('searchInput');
  const query = input ? input.value.trim() : '';
  if (query) {
    window.location.href = `${window.FERRO_BASE_URL || '/ferro831'}/products.php?q=${encodeURIComponent(query)}`;
  }
}

function openCartDrawer(event) {
  const drawer = document.getElementById('cartDrawer');
  const overlay = document.getElementById('cartDrawerOverlay');
  if (!drawer || !overlay) return true;
  if (event && typeof event.preventDefault === 'function') {
    event.preventDefault();
  }
  drawer.classList.add('open');
  overlay.classList.add('open');
  drawer.setAttribute('aria-hidden', 'false');
  overlay.setAttribute('aria-hidden', 'false');
  document.body.classList.add('ferro-cart-drawer-open');
  return false;
}

function closeCartDrawer() {
  const drawer = document.getElementById('cartDrawer');
  const overlay = document.getElementById('cartDrawerOverlay');
  if (drawer) {
    drawer.classList.remove('open');
    drawer.setAttribute('aria-hidden', 'true');
  }
  if (overlay) {
    overlay.classList.remove('open');
    overlay.setAttribute('aria-hidden', 'true');
  }
  document.body.classList.remove('ferro-cart-drawer-open');
}

function updateCartDrawerCount(count) {
  const next = Math.max(0, parseInt(count || '0', 10));
  const drawerCount = document.getElementById('cartDrawerCount');
  const emptyState = document.getElementById('cartDrawerEmptyState');
  if (drawerCount) drawerCount.textContent = String(next);
  if (emptyState) emptyState.classList.toggle('is-hidden', next > 0);
}

function filterProducts(category, chip) {
  document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
  if (chip) chip.classList.add('active');

  document.querySelectorAll('.product-card').forEach(card => {
    const cat = (card.dataset.category || '').toLowerCase();
    const price = parseFloat(card.dataset.price || '0');
    let show = false;

    if (category === 'all') show = true;
    else if (category === 'budget') show = price < 999;
    else show = cat === category;

    card.style.display = show ? 'block' : 'none';
  });
}

function addToCart(productId, btn) {
  if (!productId || !btn || btn.dataset.loading === '1') return;

  const base = window.FERRO_BASE_URL || '/ferro831';
  const originalText = btn.textContent;
  const payload = new URLSearchParams();
  payload.append('add_to_cart', '1');
  payload.append('product_id', productId);
  payload.append('quantity', '1');

  btn.dataset.loading = '1';

  fetch(`${base}/cart.php`, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
    body: payload.toString()
  })
    .then(response => response.json().catch(() => ({})))
    .then(data => {
      if (!data || data.success !== true) {
        return;
      }
      const badge = document.getElementById('cartBadge');
      if (badge) {
        const count = parseInt(badge.textContent || '0', 10);
        const nextCount = count + 1;
        badge.textContent = String(nextCount);
        const mobileBadge = document.getElementById('mobileCartBadge');
        if (mobileBadge) {
          mobileBadge.textContent = String(nextCount);
        }
        updateCartDrawerCount(nextCount);
      }

      btn.textContent = 'ADDED ✓';
      btn.classList.add('filled');

      setTimeout(() => {
        btn.textContent = originalText || 'ADD TO CART';
        btn.classList.remove('filled');
      }, 1600);
    })
    .finally(() => {
      btn.dataset.loading = '0';
    });
}

function toggleWishlist(productId, btn) {
  if (!btn) return;

  const loggedIn = btn.getAttribute('data-logged-in') === '1';
  const base = window.FERRO_BASE_URL || '/ferro831';
  const loginUrl = btn.getAttribute('data-login-url') || `${base}/login.php`;

  if (!loggedIn) {
    window.location.href = loginUrl;
    return;
  }

  if (!productId || btn.dataset.loading === '1') return;

  const isActive = btn.classList.contains('active');
  const endpoint = isActive ? `${base}/remove-wishlist.php` : `${base}/add-to-wishlist.php`;
  const payload = new URLSearchParams();
  payload.append('product_id', productId);
  payload.append('ajax', '1');

  btn.dataset.loading = '1';

  fetch(endpoint, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    body: payload.toString()
  })
    .then(response => response.json().catch(() => ({})))
    .then(data => {
      if (data && data.login_required) {
        window.location.href = data.redirect || loginUrl;
        return;
      }

      if (data && data.success) {
        const shouldBeActive = data.action === 'added';
        if (typeof window.showFerroToast === 'function') {
          window.showFerroToast(shouldBeActive ? 'Added to wishlist.' : 'Removed from wishlist.', 'success');
        }
        document.querySelectorAll(`.wishlist-btn[data-product-id="${productId}"]`).forEach(button => {
          const variant = button.getAttribute('data-variant') || 'icon';
          button.classList.toggle('active', shouldBeActive);
          if (variant === 'detail') {
            button.innerHTML = shouldBeActive ? '&#10084; Saved to Wishlist' : '&#9825; Save to Wishlist';
          } else {
            button.textContent = shouldBeActive ? '♥' : '♡';
          }
          button.setAttribute('aria-pressed', shouldBeActive ? 'true' : 'false');
        });
      }
    })
    .finally(() => {
      btn.dataset.loading = '0';
    });
}

function subscribeNewsletter() {
  const input = document.getElementById('emailInput');
  const message = document.getElementById('newsletterMessage');
  if (!input) return;

  if (!input.value || !input.value.includes('@')) {
    input.style.borderColor = 'var(--red)';
    if (message) message.textContent = 'Please enter a valid email.';
    return;
  }

  input.style.borderColor = 'var(--green)';
  input.value = '';
  input.placeholder = "You're in! 🎉";
  if (message) message.textContent = 'Newsletter backend coming soon.';

  setTimeout(() => {
    input.placeholder = 'your@email.com';
    if (message) message.textContent = '';
  }, 3000);
}

function modernSelectSize(button) {
  if (!button || button.classList.contains('is-unavailable')) return;
  const row = button.closest('.modern-size-grid');
  if (!row) return;
  row.querySelectorAll('.modern-size-option').forEach((item) => item.classList.remove('active'));
  button.classList.add('active');
  modernSyncVariantSelection();
}

function modernSelectColor(button) {
  if (!button || button.classList.contains('is-unavailable')) return;
  const row = button.closest('.modern-color-row');
  if (!row) return;
  row.querySelectorAll('.modern-color-dot').forEach((item) => item.classList.remove('active'));
  button.classList.add('active');
  modernSyncVariantSelection();
}

function modernChangeQty(delta, maxStock) {
  const input = document.getElementById('modernQtyInput');
  if (!input || input.disabled) return;
  const max = Math.max(1, parseInt(maxStock || input.max || '1', 10));
  const current = Math.max(1, parseInt(input.value || '1', 10));
  let next = current + delta;
  if (next < 1) next = 1;
  if (next > max) next = max;
  input.value = String(next);
}

function modernSelectThumb(button, imageUrl) {
  const mainImage = document.getElementById('modernMainProductImage');
  const strip = button ? button.closest('.modern-thumb-strip') : null;
  const nextImage = imageUrl || (button ? button.dataset.image : '');
  if (!mainImage || !button || !nextImage) return;
  mainImage.src = nextImage;
  if (strip) {
    strip.querySelectorAll('.modern-thumb').forEach((item) => item.classList.remove('active'));
  }
  button.classList.add('active');
}

function modernSwitchTab(tabName) {
  document.querySelectorAll('.modern-tab').forEach((tab) => {
    tab.classList.toggle('active', tab.getAttribute('data-tab') === tabName);
  });
  document.querySelectorAll('.modern-tab-body').forEach((body) => {
    body.classList.toggle('active', body.getAttribute('data-tab-body') === tabName);
  });
}

function modernShowSizeGuide() {
  const modal = document.getElementById('modernSizeGuideModal');
  if (!modal) return;
  modal.classList.add('open');
  modal.setAttribute('aria-hidden', 'false');
}

function modernHideSizeGuide() {
  const modal = document.getElementById('modernSizeGuideModal');
  if (!modal) return;
  modal.classList.remove('open');
  modal.setAttribute('aria-hidden', 'true');
}

function modernCopyProductLink() {
  const url = window.location.href;
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url).then(() => {
      alert('Product link copied.');
    }).catch(() => {
      window.prompt('Copy this product link:', url);
    });
    return;
  }
  window.prompt('Copy this product link:', url);
}

function modernSyncVariantSelection() {
  const variants = Array.isArray(window.FERRO_VARIANTS) ? window.FERRO_VARIANTS : null;
  if (!variants) return;

  const selectedSizeEl = document.querySelector('.modern-size-grid .modern-size-option.active');
  const selectedColorEl = document.querySelector('.modern-color-row .modern-color-dot.active');
  const selectedSize = selectedSizeEl ? (selectedSizeEl.dataset.size || selectedSizeEl.textContent || '').trim() : '';
  const selectedColor = selectedColorEl ? (selectedColorEl.dataset.color || selectedColorEl.getAttribute('aria-label') || '').trim() : '';

  const variantInput = document.getElementById('modernVariantIdInput');
  const qtyInput = document.getElementById('modernQtyInput');
  const atcBtn = document.getElementById('modernAtcButton');
  const stockText = document.getElementById('modernStockText');
  const selectionText = document.getElementById('modernVariantSelectionText');

  let matched = null;
  if (selectedSize && selectedColor) {
    matched = variants.find(v => String(v.size || '') === selectedSize && String(v.color || '') === selectedColor) || null;
  }

  if (!matched || parseInt(matched.stock || 0, 10) <= 0) {
    if (variantInput) variantInput.value = '0';
    if (atcBtn) {
      atcBtn.disabled = true;
      atcBtn.textContent = selectedSize && selectedColor ? 'Out of stock' : 'Select size & colour';
    }
    if (qtyInput) {
      qtyInput.max = '1';
      qtyInput.value = '1';
      qtyInput.disabled = true;
    }
    if (selectionText) {
      selectionText.textContent = selectedSize && selectedColor
        ? `Selected: ${selectedSize} / ${selectedColor} (Out of stock)`
        : 'Please select size and colour.';
    }
    if (stockText && selectedSize && selectedColor) {
      stockText.textContent = 'Out of stock';
      stockText.classList.remove('is-in', 'is-low');
      stockText.classList.add('is-out');
    }
    return;
  }

  const stock = Math.max(0, parseInt(matched.stock || 0, 10));
  if (variantInput) variantInput.value = String(matched.id || 0);
  if (atcBtn) {
    atcBtn.disabled = stock <= 0;
    atcBtn.textContent = stock <= 0 ? 'Out of stock' : 'Add to Cart';
  }
  if (qtyInput) {
    qtyInput.disabled = stock <= 0;
    qtyInput.max = String(Math.max(1, stock));
    if (parseInt(qtyInput.value || '1', 10) > stock) {
      qtyInput.value = String(Math.max(1, stock));
    }
  }
  if (selectionText) {
    selectionText.textContent = `Selected: ${selectedSize} / ${selectedColor}`;
  }
  if (stockText) {
    if (stock <= 0) {
      stockText.textContent = 'Out of stock';
      stockText.classList.remove('is-in', 'is-low');
      stockText.classList.add('is-out');
    } else if (stock <= 5) {
      stockText.textContent = `Only ${stock} left - order soon`;
      stockText.classList.remove('is-in', 'is-out');
      stockText.classList.add('is-low');
    } else {
      stockText.textContent = 'In stock';
      stockText.classList.remove('is-low', 'is-out');
      stockText.classList.add('is-in');
    }
  }
}

document.addEventListener('DOMContentLoaded', function () {
  if (Array.isArray(window.FERRO_VARIANTS)) {
    modernSyncVariantSelection();
  }
});

// FERRO831 NEW UI/UX FOUNDATION
(function () {
  const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

  function ensureToastContainer() {
    let container = document.getElementById('ferro-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'ferro-toast-container';
      container.setAttribute('aria-live', 'polite');
      container.setAttribute('aria-atomic', 'true');
      document.body.appendChild(container);
    }
    return container;
  }

  window.showFerroToast = function (message, type) {
    if (!message) return;
    const toastType = typeof type === 'string' ? type.toLowerCase() : 'info';
    const cls = toastType === 'success' || toastType === 'error' ? toastType : 'info';
    const container = ensureToastContainer();
    const toast = document.createElement('div');
    toast.className = `ferro-toast is-${cls}`;
    toast.setAttribute('role', 'status');
    toast.textContent = String(message);
    container.appendChild(toast);

    const removeAfter = setTimeout(function () {
      toast.remove();
    }, 2600);

    toast.addEventListener('click', function () {
      clearTimeout(removeAfter);
      toast.remove();
    });
  };

  function initRevealObserver() {
    const revealEls = document.querySelectorAll('.reveal');
    if (!revealEls.length) return;

    if (reduceMotionQuery.matches || !('IntersectionObserver' in window)) {
      revealEls.forEach(function (el) {
        el.classList.add('reveal-visible');
      });
      return;
    }

    const observer = new IntersectionObserver(function (entries, obs) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('reveal-visible');
          obs.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.12,
      rootMargin: '0px 0px -30px 0px'
    });

    revealEls.forEach(function (el) {
      observer.observe(el);
    });
  }

  function initDesktopCursor() {
    if (reduceMotionQuery.matches) return;
    if (!window.matchMedia('(pointer: fine)').matches) return;

    const body = document.body;
    if (!body) return;

    body.classList.add('ferro-cursor-enabled');

    let dot = document.getElementById('ferroCursorDot');
    let ring = document.getElementById('ferroCursorRing');

    if (!dot) {
      dot = document.createElement('div');
      dot.id = 'ferroCursorDot';
      body.appendChild(dot);
    }

    if (!ring) {
      ring = document.createElement('div');
      ring.id = 'ferroCursorRing';
      body.appendChild(ring);
    }

    let mouseX = window.innerWidth / 2;
    let mouseY = window.innerHeight / 2;
    let ringX = mouseX;
    let ringY = mouseY;

    function onMove(e) {
      mouseX = e.clientX;
      mouseY = e.clientY;
      dot.style.left = `${mouseX}px`;
      dot.style.top = `${mouseY}px`;
    }

    document.addEventListener('mousemove', onMove, { passive: true });

    function animateRing() {
      ringX += (mouseX - ringX) * 0.18;
      ringY += (mouseY - ringY) * 0.18;
      ring.style.left = `${ringX}px`;
      ring.style.top = `${ringY}px`;
      requestAnimationFrame(animateRing);
    }
    requestAnimationFrame(animateRing);

    document.addEventListener('mouseover', function (e) {
      const target = e.target;
      if (!(target instanceof Element)) return;
      const interactive = target.closest('a, button, [role="button"], input, select, textarea, .product-card');
      body.classList.toggle('ferro-cursor-hover', Boolean(interactive));
    }, { passive: true });
  }

  document.addEventListener('DOMContentLoaded', function () {
    ensureToastContainer();
    initRevealObserver();
    initDesktopCursor();
  });
})();

document.addEventListener('keydown', function (e) {
  if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
    const input = document.getElementById('searchInput');
    if (!input) return;
    e.preventDefault();
    openSearch();
  }
});

function toggleFooterAccordion(button) {
  const col = button ? button.closest('.ferro-footer-col') : null;
  if (!col) return;
  col.classList.toggle('open');
}

function handleNewsletterSubmit(event) {
  if (event) event.preventDefault();
  const input = document.getElementById('ferroNewsletterEmail');
  const value = input ? String(input.value || '').trim() : '';
  const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

  if (!isValid) {
    if (typeof window.showFerroToast === 'function') {
      window.showFerroToast('Enter a valid email', 'error');
    }
    return false;
  }

  if (typeof window.showFerroToast === 'function') {
    window.showFerroToast('Thanks! Newsletter backend will be connected soon.', 'success');
  }
  if (input) input.value = '';
  return false;
}


