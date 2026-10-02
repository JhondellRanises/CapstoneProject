<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?> | GPTransco Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/admin/css/admin.css?v=5') ?>">
</head>
<body>
<div class="app-shell d-flex">
    <?= view('admin/partials/sidebar', ['activePage' => $slug]) ?>
    <main class="workspace">
        <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2"><button class="btn btn-sm btn-outline-secondary d-md-none sidebar-toggle" type="button" aria-controls="adminSidebar" aria-expanded="false">Menu</button><div><span class="fw-semibold"><?= esc($title) ?></span><span class="text-secondary ms-2">Administration</span></div></div>
            <div class="d-flex align-items-center gap-3"><span class="text-secondary small"><?= esc(session()->get('user_name')) ?> · Admin</span><form method="post" action="<?= site_url('admin/logout') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-secondary">Logout</button></form></div>
        </header>
        <section class="p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div><h1 class="h3 mb-1"><?= esc($title) ?></h1><p class="text-secondary mb-0">View and manage <?= strtolower(esc($title)) ?> records.</p></div>
                <a class="btn btn-primary" href="<?= site_url('admin/' . $slug . '/new') ?>">Add <?= rtrim(esc($title), 's') ?></a>
            </div>
            <?php if ($message = session()->getFlashdata('message')): ?><div class="alert alert-success"><?= esc($message) ?></div><?php endif ?>
            <?php if ($error = session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif ?>
            <div class="card border-0 shadow-sm"><div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><?php foreach ($columns as $label): ?><th><?= esc($label) ?></th><?php endforeach ?><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if ($rows === []): ?><tr><td colspan="<?= count($columns) + 1 ?>" class="text-center text-secondary py-4">No records found.</td></tr>
                    <?php else: foreach ($rows as $row): ?><tr>
                        <?php foreach ($columns as $field => $label): ?><td><?php
                            $value = $row[$field] ?? '';
                            if ($field === 'is_active') $value = (int) $value === 1 ? 'Active' : 'Inactive';
                            echo esc((string) $value);
                        ?></td><?php endforeach ?>
                        <td class="text-nowrap"><?php if ($slug === 'passengers'): ?><a class="btn btn-sm btn-outline-secondary" href="<?= site_url('admin/passengers/' . $row['id'] . '/bookings') ?>">Booking history</a> <?php endif ?><a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/' . $slug . '/' . $row['id'] . '/edit') ?>">Edit</a>
                            <?php if (! (isset($row['status']) && in_array($row['status'], ['Cancelled', 'Completed', 'Inactive'], true)) && (! isset($row['is_active']) || (int) $row['is_active'] === 1)): ?>
                            <form class="d-inline" method="post" action="<?= site_url('admin/' . $slug . '/' . $row['id'] . '/deactivate') ?>" data-confirm="Deactivate or cancel this record?">
                                <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger" type="submit">Deactivate</button>
                            </form><?php endif ?>
                        </td>
                    </tr><?php endforeach; endif ?>
                    </tbody>
                </table>
            </div></div>
        </section>
    </main>
</div>
<script src="<?= base_url('assets/admin/js/admin.js') ?>" defer></script>
</body>
</html>
