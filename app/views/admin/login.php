<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Ferro831</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;500;600;700;800&family=Barlow:wght@300;400;500;600&family=Share+Tech+Mono&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="login-page">
  <div class="login-card">

    <div class="login-brand">
      <div class="brand-logo">FERRO<span>831</span></div>
      <div class="brand-tagline">Jamshedpur Streetwear</div>
      <span class="admin-label">Admin Panel</span>
    </div>

    <?php if ($error !== ''): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form class="login-form" method="POST" action="login.php">
      <div class="form-group">
        <label for="email">Email</label>
        <input
          type="email"
          id="email"
          name="email"
          placeholder="Enter admin email"
          autocomplete="username"
          required
          value="<?php echo htmlspecialchars((string)($old_input['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
        >
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          placeholder="Enter password"
          autocomplete="current-password"
          required
        >
      </div>

      <button type="submit" class="btn-primary">
        Sign In
      </button>
    </form>

    <div class="login-footer">
      <a href="../login.php">Main Login</a> &nbsp;|&nbsp; <a href="../index.php">Back to Store</a>
    </div>

  </div>
</div>

</body>
</html>
