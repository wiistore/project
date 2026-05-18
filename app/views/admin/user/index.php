<?php
$title = $title ?? 'Data User Kasir';
$activeMenu = $activeMenu ?? 'user';

$pageCss = ['assets/css/user.css?v=1' . time()];

$__viewData = get_defined_vars();

$user = isset($__viewData['user']) && is_array($__viewData['user'])
    ? $__viewData['user']
    : [];

$users = isset($__viewData['users']) && is_array($__viewData['users'])
    ? $__viewData['users']
    : [];

$flash = isset($__viewData['flash']) && is_array($__viewData['flash'])
    ? $__viewData['flash']
    : [];

$success = $flash['success'] ?? null;
$error = $flash['error'] ?? null;

if (!function_exists('user_date')) {
    function user_date(mixed $value): string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return '-';
        }

        $time = strtotime($raw);

        if ($time === false) {
            return $raw;
        }

        return date('d M Y, H:i', $time);
    }
}

$totalUser = count($users);
$totalAdmin = 0;
$totalKasir = 0;
$totalAktif = 0;
$totalNonaktif = 0;
$totalProtected = 0;

foreach ($users as $item) {
    $role = strtolower((string) ($item['role'] ?? ''));
    $status = strtolower((string) ($item['status'] ?? ''));

    if ($role === 'admin') {
        $totalAdmin++;
    }

    if ($role === 'kasir') {
        $totalKasir++;
    }

    if ($status === 'aktif') {
        $totalAktif++;
    }

    if ($status === 'nonaktif') {
        $totalNonaktif++;
    }

    if ((int) ($item['is_protected'] ?? 0) === 1) {
        $totalProtected++;
    }
}

$summaryCards = [
    [
        'class' => 'summary-green',
        'icon' => 'ti ti-users',
        'label' => 'Total User',
        'value' => (string) $totalUser,
        'desc' => 'Admin dan kasir',
    ],
    [
        'class' => 'summary-blue',
        'icon' => 'ti ti-user-check',
        'label' => 'Kasir',
        'value' => (string) $totalKasir,
        'desc' => 'Akun operasional',
    ],
    [
        'class' => 'summary-orange',
        'icon' => 'ti ti-circle-check',
        'label' => 'User Aktif',
        'value' => (string) $totalAktif,
        'desc' => 'Bisa login',
    ],
    [
        'class' => 'summary-purple',
        'icon' => 'ti ti-shield-lock',
        'label' => 'Protected',
        'value' => (string) $totalProtected,
        'desc' => 'Tidak bisa diedit',
    ],
];
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="user-page">
    <?php if ($success): ?>
        <div class="user-alert user-alert-success">
            <i class="ti ti-circle-check"></i>
            <span><?= app_e($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="user-alert user-alert-error">
            <i class="ti ti-alert-triangle"></i>
            <span><?= app_e($error) ?></span>
        </div>
    <?php endif; ?>

    <section class="user-hero">
        <div class="user-hero-content">
            <span class="user-eyebrow">
                <i class="ti ti-users"></i>
                Manajemen User
            </span>

            <h2>User Kasir</h2>

            <p>
                Kelola akun kasir untuk akses POS. Admin utama tetap dilindungi, karena membiarkan tombol hapus admin utama itu ide yang lahir dari kecerobohan.
            </p>
        </div>

        <div class="user-hero-actions">
            <a href="<?= app_e(app_url('/admin/user/create')) ?>" class="user-btn user-btn-primary">
                <i class="ti ti-user-plus"></i>
                Tambah Kasir
            </a>

            <a href="<?= app_e(app_url('/admin/dashboard')) ?>" class="user-btn user-btn-soft">
                <i class="ti ti-layout-dashboard"></i>
                Dashboard
            </a>
        </div>
    </section>

    <section class="user-summary summary-count-4">
        <?php foreach ($summaryCards as $card): ?>
            <article class="user-summary-card <?= app_e($card['class']) ?>">
                <span class="user-summary-icon">
                    <i class="<?= app_e($card['icon']) ?>"></i>
                </span>

                <div>
                    <small><?= app_e($card['label']) ?></small>
                    <strong><?= app_e($card['value']) ?></strong>
                    <p><?= app_e($card['desc']) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="user-panel">
        <div class="user-panel-header">
            <div>
                <span>Akun</span>
                <h3>Daftar User</h3>
            </div>

            <div class="user-tools">
                <label class="user-search">
                    <i class="ti ti-search"></i>
                    <input
                        type="search"
                        placeholder="Cari username, email, role..."
                        data-user-search>
                </label>

                <select class="user-filter" data-user-role-filter aria-label="Filter role">
                    <option value="">Semua Role</option>
                    <option value="admin">Admin</option>
                    <option value="kasir">Kasir</option>
                </select>

                <select class="user-filter" data-user-status-filter aria-label="Filter status">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>

                <button type="button" class="user-btn user-btn-ghost" data-user-reset>
                    <i class="ti ti-refresh"></i>
                    Reset
                </button>
            </div>
        </div>

        <?php if (empty($users)): ?>
            <div class="user-empty">
                <span>
                    <i class="ti ti-user-off"></i>
                </span>

                <h4>Belum ada user</h4>

                <p>
                    Tambahkan kasir dulu supaya POS tidak dipakai semua orang seperti komputer warnet.
                </p>

                <a href="<?= app_e(app_url('/admin/user/create')) ?>" class="user-btn user-btn-form">
                    <i class="ti ti-user-plus"></i>
                    Tambah Kasir
                </a>
            </div>
        <?php else: ?>
            <div class="user-table-wrap">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Proteksi</th>
                            <th>Dibuat</th>
                            <th>Update</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>

                    <tbody data-user-table-body>
                        <?php foreach ($users as $index => $item): ?>
                            <?php
                            $id = (int) ($item['id'] ?? 0);
                            $username = (string) ($item['username'] ?? '-');
                            $email = (string) ($item['email'] ?? '-');
                            $role = strtolower((string) ($item['role'] ?? '-'));
                            $status = strtolower((string) ($item['status'] ?? 'nonaktif'));
                            $isProtected = $role === 'admin' || (int) ($item['is_protected'] ?? 0) === 1;

                            $searchText = strtolower(implode(' ', [
                                $username,
                                $email,
                                $role,
                                $status,
                            ]));
                            ?>

                            <tr
                                data-user-row
                                data-search="<?= app_e($searchText) ?>"
                                data-role="<?= app_e($role) ?>"
                                data-status="<?= app_e($status) ?>">
                                <td>
                                    <span class="user-number"><?= app_e((string) ($index + 1)) ?></span>
                                </td>

                                <td>
                                    <div class="user-name">
                                        <span class="user-avatar <?= $role === 'admin' ? 'is-admin' : '' ?>">
                                            <i class="<?= $role === 'admin' ? 'ti ti-shield-lock' : 'ti ti-user' ?>"></i>
                                        </span>

                                        <div>
                                            <strong><?= app_e($username) ?></strong>
                                            <small>ID: <?= app_e((string) $id) ?></small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <a href="mailto:<?= app_e($email) ?>" class="user-email">
                                        <i class="ti ti-mail"></i>
                                        <?= app_e($email) ?>
                                    </a>
                                </td>

                                <td>
                                    <span class="user-role role-<?= app_e($role) ?>">
                                        <i class="<?= $role === 'admin' ? 'ti ti-crown' : 'ti ti-cash-register' ?>"></i>
                                        <?= app_e(ucfirst($role)) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="user-status <?= $status === 'aktif' ? 'status-active' : 'status-inactive' ?>">
                                        <i class="<?= $status === 'aktif' ? 'ti ti-circle-check' : 'ti ti-circle-off' ?>"></i>
                                        <?= app_e(ucfirst($status)) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="user-protected <?= $isProtected ? 'is-locked' : 'is-open' ?>">
                                        <i class="<?= $isProtected ? 'ti ti-lock' : 'ti ti-lock-open' ?>"></i>
                                        <?= $isProtected ? 'Dilindungi' : 'Bisa dikelola' ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="user-date">
                                        <i class="ti ti-calendar-plus"></i>
                                        <?= app_e(user_date($item['created_at'] ?? '')) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="user-date">
                                        <i class="ti ti-calendar-cog"></i>
                                        <?= app_e(user_date($item['updated_at'] ?? '')) ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="user-actions">
                                        <?php if (!$isProtected): ?>
                                            <?php
                                            $nextStatus = $status === 'aktif' ? 'nonaktif' : 'aktif';
                                            $toggleTitle = $status === 'aktif' ? 'Nonaktifkan Kasir' : 'Aktifkan Kasir';
                                            $toggleMessage = $status === 'aktif'
                                                ? 'Kasir ' . $username . ' akan dinonaktifkan. Histori transaksi tetap aman. Lanjut?'
                                                : 'Kasir ' . $username . ' akan diaktifkan lagi dan bisa login POS. Lanjut?';
                                            $toggleButtonClass = $status === 'aktif' ? 'action-delete' : 'action-activate';
                                            $toggleIcon = $status === 'aktif' ? 'ti ti-user-off' : 'ti ti-user-check';
                                            $toggleLabel = $status === 'aktif' ? 'Nonaktifkan kasir' : 'Aktifkan kasir';

                                            $nama = (string) ($item['nama'] ?? $item['nama_lengkap'] ?? $username);
                                            ?>

                                            <a
                                                href="<?= app_e(app_url('/admin/user/edit/' . $id)) ?>"
                                                class="user-action-btn action-edit"
                                                title="Edit kasir"
                                                aria-label="Edit kasir">
                                                <i class="ti ti-edit"></i>
                                            </a>

                                            <a
                                                href="<?= app_e(app_url('/admin/user/reset-password/' . $id)) ?>"
                                                class="user-action-btn action-password"
                                                title="Reset password"
                                                aria-label="Reset password">
                                                <i class="ti ti-key"></i>
                                            </a>

                                            <form
                                                action="<?= app_e(app_url('/admin/user/update/' . $id)) ?>"
                                                method="POST"
                                                data-user-delete-form
                                                data-confirm-title="<?= app_e($toggleTitle) ?>"
                                                data-confirm-message="<?= app_e($toggleMessage) ?>"
                                                data-confirm-submit="<?= $status === 'aktif' ? 'Ya, nonaktifkan' : 'Ya, aktifkan' ?>">
                                                <input type="hidden" name="nama" value="<?= app_e($nama) ?>">
                                                <input type="hidden" name="username" value="<?= app_e($username) ?>">
                                                <input type="hidden" name="email" value="<?= app_e($email) ?>">
                                                <input type="hidden" name="status" value="<?= app_e($nextStatus) ?>">

                                                <button
                                                    type="submit"
                                                    class="user-action-btn <?= app_e($toggleButtonClass) ?>"
                                                    title="<?= app_e($toggleLabel) ?>"
                                                    aria-label="<?= app_e($toggleLabel) ?>">
                                                    <i class="<?= app_e($toggleIcon) ?>"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="user-action-disabled" title="Admin/protected tidak bisa diedit">
                                                <i class="ti ti-lock"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="user-filter-empty" data-user-filter-empty hidden>
                    <span>
                        <i class="ti ti-search-off"></i>
                    </span>

                    <h4>User tidak ketemu</h4>

                    <p>
                        Keyword atau filter terlalu sempit. Database bukan cenayang, walau sering diperlakukan begitu.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<script src="<?= app_e(app_asset('assets/js/user.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>