<div class="page-header">
    <p class="page-header-eyebrow">Your Account</p>
    <h1>Addresses</h1>
</div>

<section class="section">
    <div class="address-page-actions">
        <a href="/ferro831/add-address.php" class="btn-primary" style="display:inline-flex;">Add Address</a>
    </div>

    <?php if (empty($addresses)): ?>
        <div class="order-empty-state">
            <h2>No Saved Address</h2>
            <p>Add your delivery address to speed up checkout.</p>
            <a href="/ferro831/add-address.php" class="btn-primary" style="display:inline-flex;">Add First Address</a>
        </div>
    <?php else: ?>
        <div class="address-grid">
            <?php foreach ($addresses as $address): ?>
                <div class="address-card">
                    <div class="address-card-top">
                        <p class="address-name"><?php echo htmlspecialchars((string)$address['full_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php if ((int)$address['is_default'] === 1): ?>
                            <span class="address-default-badge">Default</span>
                        <?php endif; ?>
                    </div>

                    <p class="address-phone"><?php echo htmlspecialchars((string)$address['phone'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="address-lines">
                        <?php echo htmlspecialchars((string)$address['address_line'], ENT_QUOTES, 'UTF-8'); ?><br>
                        <?php echo htmlspecialchars((string)$address['city'], ENT_QUOTES, 'UTF-8'); ?>,
                        <?php echo htmlspecialchars((string)$address['state'], ENT_QUOTES, 'UTF-8'); ?>
                        - <?php echo htmlspecialchars((string)$address['pincode'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>

                    <div class="address-actions">
                        <a href="/ferro831/edit-address.php?id=<?php echo (int)$address['id']; ?>" class="btn-card address-action-btn">Edit</a>

                        <form method="POST" action="/ferro831/delete-address.php" onsubmit="return confirm('Delete this address?');">
                            <input type="hidden" name="address_id" value="<?php echo (int)$address['id']; ?>">
                            <button type="submit" class="btn-card address-action-btn address-action-danger">Delete</button>
                        </form>

                        <?php if ((int)$address['is_default'] !== 1): ?>
                            <form method="POST" action="/ferro831/set-default-address.php">
                                <input type="hidden" name="address_id" value="<?php echo (int)$address['id']; ?>">
                                <button type="submit" class="btn-card address-action-btn">Set Default</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
