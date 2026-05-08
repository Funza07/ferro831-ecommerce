<section class="section" style="max-width:520px;margin:0 auto;">
    <p class="section-label">Welcome Back</p>
    <h1 class="section-title">Login</h1>

    <?php if (!empty($errors)): ?>
        <div style="border:1px solid #8a1e1e;background:#2a1515;color:#f2caca;padding:14px 16px;border-radius:10px;margin:18px 0;">
            <?php foreach ($errors as $error): ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/ferro831/login.php" style="display:grid;gap:14px;">
        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input class="form-input" type="email" id="email" name="email" required value="<?php echo e((string)($old_input['email'] ?? '')); ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input class="form-input" type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn-primary" style="display:inline-flex;">Login</button>
    </form>

    <p style="margin-top:16px;color:var(--mid-grey);">
        New here? <a href="/ferro831/register.php">Create an account</a>
    </p>
</section>

