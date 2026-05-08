<div class="page-header">
    <p class="page-header-eyebrow">Your Account</p>
    <h1>Profile</h1>
</div>

<section class="section">
    <div class="profile-card">
        <p class="profile-card-title">Personal Information</p>

        <?php if ($success_message !== ''): ?>
            <div class="profile-alert profile-alert-success"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($error_message !== ''): ?>
            <div class="profile-alert profile-alert-error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="POST" action="/ferro831/profile.php">
            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input class="form-input" type="text" id="name" name="name" required value="<?php echo htmlspecialchars((string)$user['name'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input class="form-input" type="email" id="email" value="<?php echo htmlspecialchars((string)$user['email'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone Number</label>
                <input class="form-input" type="text" id="phone" name="phone" value="<?php echo htmlspecialchars((string)($user['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Address</label>
                <textarea class="form-textarea" id="address" name="address"><?php echo htmlspecialchars((string)($user['address'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <button type="submit" name="save_profile" class="btn-place-order">Save Profile</button>
        </form>
    </div>
</section>
