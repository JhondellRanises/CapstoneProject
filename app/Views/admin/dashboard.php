<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?> | GPTransco Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/admin/css/admin.css?v=5') ?>">
</head>
<body>
<div class="app-shell d-flex">
    <?= view('admin/partials/sidebar', ['activePage' => $activePage]) ?>

    <main class="workspace">
        <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary d-md-none sidebar-toggle" type="button" aria-controls="adminSidebar" aria-expanded="false">Menu</button>
                <div><span class="fw-semibold">Dashboard</span><span class="text-secondary ms-2">Administration</span></div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary small"><?= esc(session()->get('user_name')) ?> · Admin</span>
                <form method="post" action="<?= site_url('admin/logout') ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Logout</button>
                </form>
            </div>
        </header>

        <section class="p-4">
            <h1 class="h3 mb-1">Dashboard</h1>
            <p class="text-secondary mb-4">A quick view of bookings and trips.</p>

            <div class="row g-3 mb-4">
                <?php foreach ($counts as $label => $count): ?>
                    <div class="col-12 col-sm-6 col-xl">
                        <div class="card metric-card shadow-sm"><div class="card-body">
                            <div class="text-secondary small"><?= esc($label) ?></div>
                            <div class="fs-2 fw-semibold mt-2"><?= esc((string) $count) ?></div>
                        </div></div>
                    </div>
                <?php endforeach ?>
            </div>

            <div class="row g-3">
                <div class="col-12 col-xl-6">
                    <section class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-semibold py-3">Recent Bookings</div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead><tr><th>Booking</th><th>Passenger</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php if ($recentBookings === []): ?>
                                    <tr><td colspan="3" class="text-secondary text-center py-4">No bookings to show yet.</td></tr>
                                <?php else: foreach ($recentBookings as $booking): ?>
                                    <tr><td><?= esc((string) ($booking['id'] ?? '—')) ?></td><td><?= esc((string) ($booking['passenger_name'] ?? $booking['passenger_id'] ?? '—')) ?></td><td><?= esc((string) ($booking['status'] ?? '—')) ?></td></tr>
                                <?php endforeach; endif ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <div class="col-12 col-xl-6">
                    <section class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-semibold py-3">Active / Ongoing Trips</div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead><tr><th>Trip</th><th>Route</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php if ($activeTrips === []): ?>
                                    <tr><td colspan="3" class="text-secondary text-center py-4">No active trips to show yet.</td></tr>
                                <?php else: foreach ($activeTrips as $trip): ?>
                                    <tr><td><?= esc((string) ($trip['id'] ?? '—')) ?></td><td><?= esc((string) ($trip['origin'] ?? '—')) ?> → <?= esc((string) ($trip['destination'] ?? '—')) ?></td><td><?= esc((string) ($trip['status'] ?? '—')) ?></td></tr>
                                <?php endforeach; endif ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </main>
</div>
<script src="<?= base_url('assets/admin/js/admin.js') ?>" defer></script>
</body>
</html>
