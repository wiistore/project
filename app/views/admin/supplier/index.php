<?php
$title = $title ?? 'Data Supplier';
$activeMenu = $activeMenu ?? 'supplier';

$pageCss = ['assets/css/supplier.css'];

$suppliers = $suppliers ?? [];
$flash = $flash ?? [];

$success = $flash['success'] ?? null;
$error = $flash['error'] ?? null;

$totalSupplier = count($suppliers);
$totalAktif = 0;
$totalNonaktif = 0;
$totalRestock = 0;

foreach ($suppliers as $item) {
    $status = strtolower((string) ($item['status'] ?? ''));

    if ($status === 'aktif') {
        $totalAktif++;
    }

    if ($status === 'nonaktif') {
        $totalNonaktif++;
    }

    $totalRestock += (int) ($item['total_restock'] ?? 0);
}

$summaryCards = [
    [
        'class' => 'summary-green',
        'icon' => 'ti ti-truck-delivery',
        'label' => 'Total Supplier',
        'value' => (string) $totalSupplier,
        'desc' => 'Semua supplier',
    ],
    [
        'class' => 'summary-blue',
        'icon' => 'ti ti-circle-check',
        'label' => 'Supplier Aktif',
        'value' => (string) $totalAktif,
        'desc' => 'Bisa dipakai restock',
    ],
    [
        'class' => 'summary-red',
        'icon' => 'ti ti-circle-off',
        'label' => 'Nonaktif',
        'value' => (string) $totalNonaktif,
        'desc' => 'Tidak dipakai',
    ],
    [
        'class' => 'summary-orange',
        'icon' => 'ti ti-stack-push',
        'label' => 'Total Restock',
        'value' => (string) $totalRestock,
        'desc' => 'Riwayat dari supplier',
    ],
];

$summaryCount = count($summaryCards);
$summaryClass = $summaryCount <= 4 ? 'summary-count-' . $summaryCount : 'summary-count-many';

if (!function_exists('supplier_short')) {
    function supplier_short(mixed $value, int $limit = 75): string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return '-';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($text) > $limit
                ? mb_substr($text, 0, $limit) . '...'
                : $text;
        }

        return strlen($text) > $limit
            ? substr($text, 0, $limit) . '...'
            : $text;
    }
}
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="supplier-page">
    <?php if ($success): ?>
        <div class="supplier-alert supplier-alert-success">
            <i class="ti ti-circle-check"></i>
            <span><?= app_e($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="supplier-alert supplier-alert-error">
            <i class="ti ti-alert-triangle"></i>
            <span><?= app_e($error) ?></span>
        </div>
    <?php endif; ?>

    <section class="supplier-hero" data-aos="fade-down" data-aos-duration="700">
        <div class="supplier-hero-content">
            <span class="supplier-eyebrow">
                <i class="ti ti-truck-delivery"></i>
                Master Supplier
            </span>

            <h2>Data Supplier</h2>

        </div>

        <div class="supplier-hero-actions">
            <a href="<?= app_e(app_url('/admin/supplier/create')) ?>" class="supplier-btn supplier-btn-primary">
                <i class="ti ti-plus"></i>
                Tambah Supplier
            </a>

            <a href="<?= app_e(app_url('/admin/restock')) ?>" class="supplier-btn supplier-btn-soft">
                <i class="ti ti-stack-push"></i>
                Lihat Restock
            </a>
        </div>
    </section>

    <section class="supplier-summary <?= app_e($summaryClass) ?>" data-aos="fade-up" data-aos-delay="140">
        <?php foreach ($summaryCards as $idx => $card): ?>
            <article class="supplier-summary-card <?= app_e($card['class']) ?>" data-aos="zoom-in" data-aos-delay="<?= app_e((string) (80 + ((int) ($idx ?? 0)) * 100)) ?>">
                <span class="supplier-summary-icon">
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

    <section class="supplier-panel" data-aos="fade-up" data-aos-delay="200">
        <div class="supplier-panel-header">
            <div>
                <span>Inventori</span>
                <h3>Daftar Supplier</h3>
            </div>

            <div class="supplier-tools">
                <label class="supplier-search">
                    <i class="ti ti-search"></i>
                    <input
                        type="search"
                        placeholder="Cari supplier, kontak, no HP, alamat..."
                        data-supplier-search
                    >
                </label>

                <select class="supplier-filter" data-supplier-status-filter aria-label="Filter status supplier">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>

                <button type="button" class="supplier-btn supplier-btn-ghost" data-supplier-reset>
                    <i class="ti ti-refresh"></i>
                    Reset
                </button>
            </div>
        </div>

        <?php if (empty($suppliers)): ?>
            <div class="supplier-empty">
                <span>
                    <i class="ti ti-truck-off"></i>
                </span>

                <h4>Belum ada supplier</h4>

                <p>
                    Tambahkan supplier dulu supaya restock tidak bergantung pada “toko yang itu lho”, kalimat yang jelas-jelas bukan data.
                </p>

                <a href="<?= app_e(app_url('/admin/supplier/create')) ?>" class="supplier-btn supplier-btn-primary">
                    <i class="ti ti-plus"></i>
                    Tambah Supplier
                </a>
            </div>
        <?php else: ?>
            <div class="supplier-table-wrap">
                <table class="supplier-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Supplier</th>
                            <th>Kontak Person</th>
                            <th>No HP</th>
                            <th>Alamat</th>
                            <th>Restock</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>

                    <tbody data-supplier-table-body>
                        <?php foreach ($suppliers as $index => $supplier): ?>
                            <?php
                            $status = strtolower((string) ($supplier['status'] ?? 'nonaktif'));
                            $totalSupplierRestock = (int) ($supplier['total_restock'] ?? 0);

                            $searchText = implode(' ', [
                                $supplier['nama'] ?? '',
                                $supplier['kontak_person'] ?? '',
                                $supplier['no_hp'] ?? '',
                                $supplier['alamat'] ?? '',
                                $supplier['keterangan'] ?? '',
                                $status,
                            ]);
                            ?>

                            <tr
                                data-supplier-row
                                data-search="<?= app_e(strtolower($searchText)) ?>"
                                data-status="<?= app_e($status) ?>"
                            >
                                <td>
                                    <span class="supplier-number"><?= app_e((string) ($index + 1)) ?></span>
                                </td>

                                <td>
                                    <div class="supplier-name">
                                        <span class="supplier-name-icon">
                                            <i class="ti ti-truck-delivery"></i>
                                        </span>

                                        <div>
                                            <strong><?= app_e($supplier['nama'] ?? '-') ?></strong>

                                            <?php if (!empty($supplier['keterangan'])): ?>
                                                <small><?= app_e(supplier_short($supplier['keterangan'], 55)) ?></small>
                                            <?php else: ?>
                                                <small>Tidak ada catatan</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="supplier-pill">
                                        <i class="ti ti-user"></i>
                                        <?= app_e(($supplier['kontak_person'] ?? '') !== '' ? $supplier['kontak_person'] : '-') ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if (!empty($supplier['no_hp'])): ?>
                                        <a href="tel:<?= app_e($supplier['no_hp']) ?>" class="supplier-phone">
                                            <i class="ti ti-phone"></i>
                                            <?= app_e($supplier['no_hp']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="supplier-muted">-</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="supplier-address">
                                        <?= app_e(supplier_short($supplier['alamat'] ?? '', 70)) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="supplier-restock <?= $totalSupplierRestock > 0 ? 'has-restock' : 'no-restock' ?>">
                                        <i class="ti ti-stack-push"></i>
                                        <?= app_e((string) $totalSupplierRestock) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="supplier-status <?= $status === 'aktif' ? 'status-active' : 'status-inactive' ?>">
                                        <i class="<?= $status === 'aktif' ? 'ti ti-circle-check' : 'ti ti-circle-off' ?>"></i>
                                        <?= app_e(ucfirst($status)) ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="supplier-actions">
                                        <a
                                            href="<?= app_e(app_url('/admin/supplier/edit/' . ($supplier['id'] ?? ''))) ?>"
                                            class="supplier-action-btn action-edit"
                                            title="Edit supplier"
                                            aria-label="Edit supplier"
                                        >
                                            <i class="ti ti-edit"></i>
                                        </a>

                                        <form
                                            action="<?= app_e(app_url('/admin/supplier/delete/' . ($supplier['id'] ?? ''))) ?>"
                                            method="POST"
                                            data-supplier-delete-form
                                            data-confirm-title="<?= $totalSupplierRestock > 0 ? 'Nonaktifkan Supplier' : 'Hapus Supplier' ?>"
                                            data-confirm-message="Supplier <?= app_e($supplier['nama'] ?? '-') ?> <?= $totalSupplierRestock > 0 ? 'sudah pernah dipakai restock, jadi akan dinonaktifkan.' : 'akan dihapus karena belum punya histori restock.' ?> Lanjut?"
                                        >
                                            <button
                                                type="submit"
                                                class="supplier-action-btn action-delete"
                                                title="<?= $totalSupplierRestock > 0 ? 'Nonaktifkan supplier' : 'Hapus supplier' ?>"
                                                aria-label="<?= $totalSupplierRestock > 0 ? 'Nonaktifkan supplier' : 'Hapus supplier' ?>"
                                            >
                                                <i class="<?= $totalSupplierRestock > 0 ? 'ti ti-user-off' : 'ti ti-trash' ?>"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="supplier-filter-empty" data-supplier-filter-empty hidden>
                    <span>
                        <i class="ti ti-search-off"></i>
                    </span>

                    <h4>Data tidak ketemu</h4>

                    <p>
                        Keyword atau filter-nya terlalu sakti. Longgarkan dikit, hidup sudah cukup penuh batasan.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<script src="<?= app_e(app_asset_versioned('assets/js/supplier.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>