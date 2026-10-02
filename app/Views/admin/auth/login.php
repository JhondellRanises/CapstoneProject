<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#143b69">
    <title><?= esc($title) ?> | GPTransco MPC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/admin/css/login.css') ?>">
</head>
<body class="auth-page">
<main class="auth-shell">
    <section class="brand-panel" aria-label="GPTransco MPC introduction">
        <div class="brand-panel__accents" aria-hidden="true"><span></span><span></span><span></span></div>
        <div class="brand-panel__copy">
            <p class="brand-panel__eyebrow">GPTransco MPC</p>
            <h1>Safe Ride.<br>Better Tomorrow.</h1>
            <p class="brand-panel__subtitle">GPTransco MPC &mdash; Serving the Community, One Trip at a Time.</p>
        </div>
        <div class="brand-panel__bottom" aria-hidden="true"><span class="brand-panel__route-line"></span><span>General Santos City, Philippines</span></div>
    </section>

    <section class="login-panel" aria-labelledby="login-title">
        <div class="login-content">
            <header class="login-brand">
                <img src="<?= base_url('assets/admin/images/gptrascologo.png') ?>" alt="Official GPTransco MPC logo" class="login-brand__logo">
                <div><div class="login-brand__name">GPTransco MPC</div><div class="login-brand__system">AI-BASED BOOKING AND TRACKING SYSTEM</div></div>
            </header>

            <div class="login-heading">
                <span class="login-heading__overline">ADMIN PORTAL</span>
                <h2 id="login-title">Admin Login</h2>
                <p>Please enter your credentials to continue.</p>
            </div>

            <?php if ($error = session()->getFlashdata('error')): ?><div class="alert alert-danger auth-alert" role="alert"><?= esc($error) ?></div><?php endif ?>
            <?php if ($message = session()->getFlashdata('message')): ?><div class="alert alert-success auth-alert" role="status"><?= esc($message) ?></div><?php endif ?>
            <?php if (isset($validation)): ?><div class="alert alert-danger auth-alert" role="alert"><?= $validation->listErrors() ?></div><?php endif ?>

            <form method="post" action="<?= site_url('admin/login') ?>" class="login-form">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="email" class="form-label">Username / Email</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/></svg>
                        <input id="email" name="email" type="email" class="form-control auth-input" value="<?= esc(old('email')) ?>" placeholder="Enter your admin email" autocomplete="username" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="auth-input-wrap">
                        <svg class="auth-input-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input id="password" name="password" type="password" class="form-control auth-input auth-input--password" placeholder="Enter your password" autocomplete="current-password" required>
                        <button class="password-toggle" type="button" data-password-toggle="password" aria-pressed="false">Show</button>
                    </div>
                    <div class="text-end mt-2"><a class="auth-link" href="<?= site_url('forgot-password') ?>">Forgot password?</a></div>
                </div>
                <button class="btn auth-submit w-100" type="submit"><span>Login</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>
            </form>

            <footer class="login-footer">&copy; 2026 GPTransco MPC. All rights reserved.</footer>
        </div>
    </section>
</main>
<script src="<?= base_url('assets/admin/js/admin.js') ?>" defer></script>
</body>
</html>
