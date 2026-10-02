<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(($id ? 'Edit ' : 'Add ') . $title) ?> | GPTransco Admin</title>
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
            <h1 class="h3 mb-3"><?= esc(($id ? 'Edit ' : 'Add ') . $title) ?></h1>
            <?php if ($error = session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif ?>
            <?php if ($validation): ?><div class="alert alert-danger"><?= $validation->listErrors() ?></div><?php endif ?>
            <form class="card border-0 shadow-sm" method="post" action="<?= site_url('admin/' . $slug . ($id ? '/' . $id : '')) ?>">
                <?= csrf_field() ?>
                <div class="card-body"><div class="row g-3">
                    <?php foreach ($fields as $field): $value = old($field['name'], $field['value']); $type = $field['type']; ?>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="<?= esc($field['name']) ?>"><?= esc($field['label']) ?><?= $field['required'] ? ' *' : '' ?></label>
                        <?php if (isset($options[$type])): ?>
                            <select class="form-select" id="<?= esc($field['name']) ?>" name="<?= esc($field['name']) ?>" <?= $field['required'] ? 'required' : '' ?>>
                                <?php if (! $field['required']): ?><option value="">Not assigned</option><?php endif ?>
                                <?php foreach ($options[$type] as $optionValue => $optionLabel): ?><option value="<?= esc((string) $optionValue) ?>" <?= (string) $value === (string) $optionValue ? 'selected' : '' ?>><?= esc($optionLabel) ?></option><?php endforeach ?>
                            </select>
                        <?php else: ?>
                            <input class="form-control" id="<?= esc($field['name']) ?>" name="<?= esc($field['name']) ?>" type="<?= esc($type) ?>" value="<?= $type === 'password' ? '' : esc((string) $value) ?>" <?= $field['required'] ? 'required' : '' ?> <?= $type === 'number' ? 'min="1"' : '' ?> <?= $type === 'password' ? 'minlength="12" autocomplete="new-password"' : '' ?>>
                            <?php if ($type === 'password' && $id): ?><div class="form-text">Leave blank to keep the current password.</div><?php elseif ($type === 'password'): ?><div class="form-text">Use at least 12 characters.</div><?php endif ?>
                        <?php endif ?>
                    </div>
                    <?php endforeach ?>
                </div></div>
                <div class="card-footer bg-white d-flex gap-2"><button class="btn btn-primary" type="submit">Save</button><a class="btn btn-outline-secondary" href="<?= site_url('admin/' . $slug) ?>">Cancel</a></div>
            </form>
        </section>
    </main>
</div>
<script src="<?= base_url('assets/admin/js/admin.js') ?>" defer></script>
</body>
</html>
