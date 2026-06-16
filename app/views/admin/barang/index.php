<?php
$title = $title ?? 'Data Barang';
$activeMenu = $activeMenu ?? 'barang';

$pageCss = ['assets/css/barang.css'];
$useBarcode = true;

$barangs = $barangs ?? ($barang ?? []);
$summary = $summary ?? [];
$filters = $filters ?? [];

$filterSearch = (string) ($filters['search'] ?? '');
$filterStatus = (string) ($filters['status'] ?? '');
$filterStock = (string) ($filters['stock'] ?? '');

$hasActiveFilter = $filterSearch !== '' || $filterStatus !== '' || $filterStock !== '';

$success = $flash['success'] ?? null;
$error = $flash['error'] ?? null;

if (!function_exists('barang_rupiah')) {
    function barang_rupiah(mixed $value): string
    {
        if (class_exists('Security') && method_exists('Security', 'rupiah')) {
            return Security::rupiah($value);
        }

        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('barang_stock_meta')) {
    function barang_stock_meta(int $stok, int $stokMinimum): array
    {
        if ($stok <= 0) {
            return [
                'label' => 'Habis',
                'class' => 'stock-empty',
                'filter' => 'habis',
                'icon' => 'ti ti-alert-circle',
            ];
        }

        if ($stok <= $stokMinimum) {
            return [
                'label' => 'Menipis',
                'class' => 'stock-low',
                'filter' => 'menipis',
                'icon' => 'ti ti-alert-triangle',
            ];
        }

        return [
            'label' => 'Aman',
            'class' => 'stock-safe',
            'filter' => 'aman',
            'icon' => 'ti ti-circle-check',
        ];
    }
}
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="barang-page">
    <?php if ($success): ?>
        <div class="barang-alert barang-alert-success">
            <i class="ti ti-circle-check"></i>
            <span><?= app_e($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="barang-alert barang-alert-error">
            <i class="ti ti-alert-triangle"></i>
            <span><?= app_e($error) ?></span>
        </div>
    <?php endif; ?>

    <section class="barang-hero" data-aos="fade-down" data-aos-duration="700">
        <div class="barang-hero-content">
            <span class="barang-eyebrow">
                <i class="ti ti-package"></i>
                Master Barang
            </span>

            <h2>Data Barang</h2>

        </div>

        <div class="barang-hero-actions">
            <a href="<?= app_e(app_url('/admin/barang/create')) ?>" class="barang-btn barang-btn-primary">
                <i class="ti ti-plus"></i>
                Tambah Barang
            </a>

            <a href="<?= app_e(app_url('/admin/restock')) ?>" class="barang-btn barang-btn-soft">
                <i class="ti ti-stack-push"></i>
                Restock
            </a>
        </div>
    </section>

    <section class="barang-summary" data-aos="fade-up" data-aos-delay="140">
        <article class="barang-summary-card summary-green" data-aos="zoom-in" data-aos-delay="80">
            <span class="barang-summary-icon">
               <i class="ti ti-package"></i>
            </span>

            <div>
                <small>Total Barang</small>
                <strong><?= app_e((string) ($summary['total_barang'] ?? count($barangs))) ?></strong>
                <p>Semua data barang</p>
            </div>
        </article>

        <article class="barang-summary-card summary-blue" data-aos="zoom-in" data-aos-delay="180">
            <span class="barang-summary-icon">
                <i class="ti ti-circle-check"></i>
            </span>

            <div>
                <small>Barang Aktif</small>
                <strong><?= app_e((string) ($summary['barang_aktif'] ?? 0)) ?></strong>
                <p>Siap transaksi</p>
            </div>
        </article>

        <article class="barang-summary-card summary-gray" data-aos="zoom-in" data-aos-delay="280">
            <span class="barang-summary-icon">
                <i class="ti ti-circle-off"></i>
            </span>

            <div>
                <small>Nonaktif</small>
                <strong><?= app_e((string) ($summary['barang_nonaktif'] ?? 0)) ?></strong>
                <p>Tidak dipakai</p>
            </div>
        </article>

        <article class="barang-summary-card summary-red" data-aos="zoom-in" data-aos-delay="380">
            <span class="barang-summary-icon">
                <i class="ti ti-alert-triangle"></i>
            </span>

            <div>
                <small>Stok Menipis</small>
                <strong><?= app_e((string) ($summary['stok_menipis'] ?? 0)) ?></strong>
                <p>Perlu dicek</p>
            </div>
        </article>
    </section>

    <section class="barang-panel" data-aos="fade-up" data-aos-delay="200">
        <div class="barang-panel-header">
            <div>
                <span>Inventori</span>
                <h3>Daftar Barang</h3>
            </div>

            <div class="barang-tools">
                <form
    class="barang-tools"
    method="GET"
    action="<?= app_e(app_url('/admin/barang')) ?>"
    data-barang-filter-form
>
    <label class="barang-search">
        <i class="ti ti-search"></i>
<input
    type="search"
    name="search"
    value="<?= app_e($filters['search'] ?? '') ?>"
    placeholder="Cari kode / nama / barcode barang"
    autocomplete="off"
    data-barang-search
    data-keep-focus
>
    </label>

    <select
        class="barang-filter"
        name="status"
        data-barang-status-filter
        aria-label="Filter status barang"
    >
        <option value="" <?= $filterStatus === '' ? 'selected' : '' ?>>Semua Status</option>
        <option value="aktif" <?= $filterStatus === 'aktif' ? 'selected' : '' ?>>Aktif</option>
        <option value="nonaktif" <?= $filterStatus === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
    </select>

    <select
        class="barang-filter"
        name="stock"
        data-barang-stock-filter
        aria-label="Filter stok barang"
    >
        <option value="" <?= $filterStock === '' ? 'selected' : '' ?>>Semua Stok</option>
        <option value="aman" <?= $filterStock === 'aman' ? 'selected' : '' ?>>Aman</option>
        <option value="menipis" <?= $filterStock === 'menipis' ? 'selected' : '' ?>>Menipis</option>
        <option value="habis" <?= $filterStock === 'habis' ? 'selected' : '' ?>>Habis</option>
    </select>

    <a href="<?= app_e(app_url('/admin/barang')) ?>" class="barang-btn barang-btn-ghost" data-barang-reset>
        <i class="ti ti-refresh"></i>
        Reset
    </a>
</form>
            </div>
        </div>

        <?php if (empty($barangs)): ?>
            <div class="barang-empty">
    <span>
        <i class="<?= $hasActiveFilter ? 'ti ti-search-off' : 'ti ti-package-off' ?>"></i>
    </span>

    <h4><?= $hasActiveFilter ? 'Data barang tidak ketemu' : 'Belum ada data barang' ?></h4>

    <p>
        <?php if ($hasActiveFilter): ?>
            Filter atau keyword terlalu sempit. Reset filter kalau mau lihat semua data lagi.
        <?php else: ?>
            Tambahkan barang pertama supaya koperasi ini tidak cuma jadi halaman kosong yang terlihat mahal.
        <?php endif; ?>
    </p>

    <?php if ($hasActiveFilter): ?>
        <a href="<?= app_e(app_url('/admin/barang')) ?>" class="barang-btn barang-btn-primary">
            <i class="ti ti-refresh"></i>
            Reset Filter
        </a>
    <?php else: ?>
        <a href="<?= app_e(app_url('/admin/barang/create')) ?>" class="barang-btn barang-btn-primary">
            <i class="ti ti-plus"></i>
            Tambah Barang
        </a>
    <?php endif; ?>
</div>
        <?php else: ?>
            <form
                action="<?= app_e(app_url('/admin/barang/label-bulk')) ?>"
                method="POST"
                data-barang-bulk-label-form
                target="_blank"
            >
                <div class="barang-bulk-bar" data-barang-bulk-bar>
                    <div class="barang-bulk-info">
                        <i class="ti ti-checks"></i>
                        <span>
                            <strong data-barang-bulk-count>0</strong> barang dipilih
                        </span>
                    </div>

                    <button
                        type="submit"
                        class="barang-btn barang-btn-primary"
                        data-barang-bulk-label-btn
                        disabled
                    >
                        <i class="ti ti-printer"></i>
                        Cetak Label Terpilih
                    </button>
                </div>

                <div class="barang-table-wrap">
                    <table class="barang-table">
                        <thead>
                            <tr>
                                <th class="barang-col-check">
                                    <input
                                        type="checkbox"
                                        class="barang-checkbox"
                                        data-barang-select-all
                                        aria-label="Pilih semua barang"
                                    >
                                </th>
                                <th>No</th>
                                <th>Barang</th>
                                <th>Barcode</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th>Harga Jual</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>

                        <tbody data-barang-table-body>
                            <?php foreach ($barangs as $index => $item): ?>
    <?php
    $rowNumber = (((int) ($pagination['current_page'] ?? 1) - 1) * (int) ($pagination['per_page'] ?? 10)) + $index + 1;

    $status = strtolower((string) ($item['status'] ?? 'nonaktif'));
    $stok = (int) ($item['stok'] ?? 0);
    $stokMinimum = (int) ($item['stok_minimum'] ?? 0);
    $stockMeta = barang_stock_meta($stok, $stokMinimum);
    $itemId = (string) ($item['id'] ?? '');
    $itemBarcode = (string) ($item['barcode'] ?? '');
    $hasBarcode = trim($itemBarcode) !== '';

    $searchText = implode(' ', [
        $item['kode_barang'] ?? '',
        $item['barcode'] ?? '',
        $item['nama'] ?? '',
        $item['nama_kategori'] ?? '',
        $item['satuan'] ?? '',
        $status,
        $stockMeta['label'],
    ]);
    ?>

                                <tr
                                    data-barang-row
                                    data-search="<?= app_e(strtolower($searchText)) ?>"
                                    data-status="<?= app_e($status) ?>"
                                    data-stock="<?= app_e($stockMeta['filter']) ?>"
                                >
                                    <td class="barang-col-check">
                                        <input
                                            type="checkbox"
                                            class="barang-checkbox"
                                            name="ids[]"
                                            value="<?= app_e($itemId) ?>"
                                            data-barang-checkbox
                                            <?= !$hasBarcode ? 'disabled' : '' ?>
                                            aria-label="Pilih barang <?= app_e($item['nama'] ?? '') ?>"
                                            title="<?= !$hasBarcode ? 'Barang ini belum punya barcode, edit dulu.' : 'Pilih untuk cetak label' ?>"
                                        >
                                    </td>

                                    <td>
                                        <span class="barang-number"><?= app_e((string) $rowNumber) ?></span>
                                    </td>

                                    <td>
                                        <div class="barang-product">
                                            <span class="barang-product-icon">
                                                <i class="ti ti-package"></i>
                                            </span>

                                            <div>
                                                <strong><?= app_e($item['nama'] ?? '-') ?></strong>
                                                <small><?= app_e($item['kode_barang'] ?? '-') ?></small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="barang-code">
                                            <i class="ti ti-barcode"></i>
                                            <?= app_e($hasBarcode ? $itemBarcode : '-') ?>
                                        </span>
                                    </td>

                                    <td><?= app_e($item['nama_kategori'] ?? '-') ?></td>

                                    <td>
                                        <span class="barang-unit"><?= app_e($item['satuan'] ?? '-') ?></span>
                                    </td>

                                    <td>
                                        <strong class="barang-price">
                                            <?= app_e(barang_rupiah($item['harga_jual'] ?? 0)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <div class="barang-stock <?= app_e($stockMeta['class']) ?>">
                                            <span>
                                                <i class="<?= app_e($stockMeta['icon']) ?>"></i>
                                                <?= app_e((string) $stok) ?>
                                            </span>
                                            <small>Min. <?= app_e((string) $stokMinimum) ?></small>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="barang-status <?= $status === 'aktif' ? 'status-active' : 'status-inactive' ?>">
                                            <i class="<?= $status === 'aktif' ? 'ti ti-circle-check' : 'ti ti-circle-off' ?>"></i>
                                            <?= app_e(ucfirst($status)) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="barang-actions">
                                            <?php if ($hasBarcode): ?>
                                                <a
                                                    href="<?= app_e(app_url('/admin/barang/label/' . $itemId)) ?>"
                                                    class="barang-action-btn action-print"
                                                    title="Cetak label barcode"
                                                    aria-label="Cetak label barcode"
                                                    target="_blank"
                                                >
                                                    <i class="ti ti-printer"></i>
                                                </a>
                                            <?php endif; ?>

                                            <a
                                                href="<?= app_e(app_url('/admin/barang/edit/' . $itemId)) ?>"
                                                class="barang-action-btn action-edit"
                                                title="Edit barang"
                                                aria-label="Edit barang"
                                            >
                                                <i class="ti ti-edit"></i>
                                            </a>

                                            <button
                                                type="button"
                                                class="barang-action-btn action-delete"
                                                title="Hapus barang"
                                                aria-label="Hapus barang"
                                                data-barang-delete-trigger
                                                data-delete-url="<?= app_e(app_url('/admin/barang/delete/' . $itemId)) ?>"
                                                data-confirm-title="Hapus / Nonaktifkan Barang"
                                                data-confirm-message="Barang <?= app_e($item['nama'] ?? '-') ?> akan dihapus kalau belum punya histori, atau dinonaktifkan kalau sudah pernah dipakai. Lanjut?"
                                            >
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="barang-filter-empty" data-barang-filter-empty hidden>
                        <span>
                            <i class="ti ti-search-off"></i>
                        </span>
                        <h4>Data tidak ketemu</h4>
                        <p>Filter atau keyword terlalu semangat. Coba longgarkan sedikit.</p>
                    </div>
                </div>
            </form>

            <!-- Hidden delete forms (dipanggil oleh tombol [data-barang-delete-trigger]) -->
            <div data-barang-delete-forms hidden>
                <?php foreach ($barangs as $item): ?>
                    <form
                        action="<?= app_e(app_url('/admin/barang/delete/' . ($item['id'] ?? ''))) ?>"
                        method="POST"
                        data-barang-delete-form
                        data-delete-id="<?= app_e((string) ($item['id'] ?? '')) ?>"
                    ></form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<script src="<?= app_e(app_asset_versioned('assets/js/barang.js')) ?>"></script>

<?php
$pagination = $pagination ?? null;
if ($pagination) {
    require APP_PATH . '/views/components/pagination.php';
}
?>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>