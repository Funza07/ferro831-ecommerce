<div class="page-header">
    <p class="page-header-eyebrow">Almost There</p>
    <h1>Checkout</h1>
</div>

<div class="checkout-layout">

    <div class="checkout-panel">
        <p class="checkout-panel-title">Customer Details</p>

        <form id="checkoutForm" method="POST" action="<?php echo function_exists('url') ? url('order-success.php') : '/ferro831/order-success.php'; ?>">
            <?php if (!empty($saved_addresses)): ?>
                <div class="saved-addresses-wrap">
                    <p class="saved-addresses-title">Saved Addresses</p>
                    <?php foreach ($saved_addresses as $saved_address): ?>
                        <?php
                        $saved_id = (int)$saved_address['id'];
                        $is_checked = ($saved_id === $default_address_id);
                        ?>
                        <label class="saved-address-option">
                            <input type="radio" name="selected_address_id" value="<?php echo $saved_id; ?>" <?php echo $is_checked ? 'checked' : ''; ?>>
                            <span class="saved-address-content">
                                <strong><?php echo e((string)$saved_address['full_name']); ?></strong>
                                <?php if ((int)$saved_address['is_default'] === 1): ?>
                                    <span class="address-default-badge">Default</span>
                                <?php endif; ?>
                                <small>
                                    <?php echo e((string)$saved_address['phone']); ?><br>
                                    <?php echo e((string)$saved_address['address_line']); ?>,
                                    <?php echo e((string)$saved_address['city']); ?>,
                                    <?php echo e((string)$saved_address['state']); ?>
                                    - <?php echo e((string)$saved_address['pincode']); ?>
                                </small>
                            </span>
                        </label>
                    <?php endforeach; ?>
                    <p class="saved-address-hint">Selected saved address will be used at order placement. You can still fill the form below as fallback.</p>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label" for="customer_name">Full Name</label>
                <input class="form-input" type="text" id="customer_name" name="customer_name" placeholder="Your full name" required value="<?php echo e($prefill_name); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="customer_email">Email Address</label>
                <input class="form-input" type="email" id="customer_email" name="customer_email" placeholder="you@example.com" required value="<?php echo e($prefill_email); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="customer_phone">Phone Number</label>
                <input class="form-input" type="text" id="customer_phone" name="customer_phone" placeholder="+91 XXXXX XXXXX" required value="<?php echo e($prefill_phone); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="customer_address">Delivery Address</label>
                <textarea class="form-textarea" id="customer_address" name="customer_address" placeholder="Full address including city, state, PIN code" required><?php echo e($prefill_address); ?></textarea>
            </div>

            <button type="submit" form="checkoutForm" name="place_order" value="1" class="btn-place-order">
                Place Order ->
            </button>
        </form>
    </div>

    <div class="checkout-summary-card">
        <p class="checkout-panel-title">Order Summary</p>

        <?php foreach ($cart_items as $item): ?>
        <div class="order-summary-item">
            <span class="order-item-name">
                <?php echo e((string)$item['product']['name']); ?>
                <?php if (!empty($item['variant'])): ?>
                    <small style="display:block;color:var(--mid-grey);">
                        <?php echo e((string)$item['variant']['size']); ?> / <?php echo e((string)$item['variant']['color']); ?>
                    </small>
                <?php endif; ?>
            </span>
            <span class="order-item-qty">x<?php echo (int)$item['quantity']; ?></span>
            <span class="order-item-price">Rs <?php echo e(number_format((float)$item['total'], 2)); ?></span>
        </div>
        <?php endforeach; ?>

        <div class="order-total-row">
            <span class="order-total-label">Total</span>
            <span class="order-total-amount">Rs <?php echo e(number_format((float)$grand_total, 2)); ?></span>
        </div>
    </div>

</div>
