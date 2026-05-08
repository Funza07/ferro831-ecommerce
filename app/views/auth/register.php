<section class="section" style="max-width:560px;margin:0 auto;">
    <p class="section-label">Join Ferro831</p>
    <h1 class="section-title">Register</h1>

    <?php if ($success): ?>
        <div style="border:1px solid #2e6f2e;background:#142914;color:#c9f0c9;padding:14px 16px;border-radius:10px;margin:18px 0;">
            Account created successfully. You can now <a href="/ferro831/login.php">login</a>.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="border:1px solid #8a1e1e;background:#2a1515;color:#f2caca;padding:14px 16px;border-radius:10px;margin:18px 0;">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/ferro831/register.php" style="display:grid;gap:14px;">
        <div class="form-group">
            <label class="form-label" for="name">Full Name</label>
            <input class="form-input" type="text" id="name" name="name" required value="<?php echo e((string)($old_input['name'] ?? '')); ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input class="form-input" type="email" id="email" name="email" required value="<?php echo e((string)($old_input['email'] ?? '')); ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input class="form-input" type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="confirm_password">Confirm Password</label>
            <input class="form-input" type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn-primary" style="display:inline-flex;">Create Account</button>
    </form>

    <p style="margin-top:16px;color:var(--mid-grey);">
        Already registered? <a href="/ferro831/login.php">Login</a>
    </p>
</section>

