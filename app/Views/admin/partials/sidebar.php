<?php
$menuItems = [
    ['slug' => 'dashboard', 'label' => 'Dashboard', 'url' => 'admin'],
    ['slug' => 'trips', 'label' => 'Trips', 'url' => 'admin/trips'],
    ['slug' => 'bookings', 'label' => 'Bookings', 'url' => 'admin/bookings'],
    ['slug' => 'schedules', 'label' => 'Schedules', 'url' => 'admin/schedules'],
    ['slug' => 'drivers', 'label' => 'Drivers', 'url' => 'admin/drivers'],
    ['slug' => 'vans', 'label' => 'Vans', 'url' => 'admin/vans'],
    ['slug' => 'passengers', 'label' => 'Passengers', 'url' => 'admin/passengers'],
    ['slug' => 'live-tracking', 'label' => 'Live Tracking', 'url' => 'admin/live-tracking'],
    ['slug' => 'reports', 'label' => 'Reports', 'url' => 'admin/reports'],
    ['slug' => 'users-roles', 'label' => 'Users & Roles', 'url' => 'admin/users-roles'],
];
?>
<aside class="sidebar p-3" id="adminSidebar">
    <a class="sidebar-brand" href="<?= site_url('admin') ?>" aria-label="GPTransco MPC dashboard">
        <img src="<?= base_url('walaybackground.png') ?>" alt="GPTransco MPC logo" width="82" height="64">
    </a>
    <div class="sidebar-section-title small text-uppercase text-white-50 mt-2 mb-2 px-2">Management</div>
    <nav aria-label="Admin navigation">
        <?php foreach ($menuItems as $item): ?>
            <a class="<?= $activePage === $item['slug'] ? 'active' : '' ?>"
               href="<?= site_url($item['url']) ?>"
               <?= $activePage === $item['slug'] ? 'aria-current="page"' : '' ?>><?= esc($item['label']) ?></a>
        <?php endforeach ?>
    </nav>
</aside>
