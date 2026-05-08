<div class="page-header">
    <p class="page-header-eyebrow">Your Account</p>
    <h1>Edit Address</h1>
</div>

<section class="section">
    <div class="profile-card">
        <p class="profile-card-title">Update Address</p>

        <?php if ($error_message !== ''): ?>
            <div class="profile-alert profile-alert-error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="POST" action="/ferro831/edit-address.php?id=<?php echo (int)$address_id; ?>">
            <input type="hidden" name="address_id" value="<?php echo (int)$address_id; ?>">

            <div class="form-group">
                <label class="form-label" for="full_name">Full Name</label>
                <input class="form-input" type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars((string)($_POST['full_name'] ?? $address['full_name']), ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number</label>
                <input class="form-input" type="text" id="phone" name="phone" required value="<?php echo htmlspecialchars((string)($_POST['phone'] ?? $address['phone']), ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="address_line">Address Line</label>
                <textarea class="form-textarea" id="address_line" name="address_line" required><?php echo htmlspecialchars((string)($_POST['address_line'] ?? $address['address_line']), ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="city">City</label>
                <input class="form-input" type="text" id="city" name="city" required value="<?php echo htmlspecialchars((string)($_POST['city'] ?? $address['city']), ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="state">State</label>
                <input class="form-input" type="text" id="state" name="state" required value="<?php echo htmlspecialchars((string)($_POST['state'] ?? $address['state']), ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="pincode">Pincode</label>
                <input class="form-input" type="text" id="pincode" name="pincode" required value="<?php echo htmlspecialchars((string)($_POST['pincode'] ?? $address['pincode']), ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <button type="submit" name="update_address" class="btn-place-order">Update Address</button>
        </form>
    </div>
</section>
