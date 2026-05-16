<?php
$title = $title ?? 'Data Barang';
$activeMenu = $activeMenu ?? 'barang';

$pageCss = ['assets/css/barang.css?v=1' . time()];

$barangs = $barangs ?? ($barang ?? []);
$summary = $summary ?? [];
$flash = $flash ?? [];

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

    <section class="barang-hero">
        <div class="barang-hero-content">
            <span class="barang-eyebrow">
                <i class="ti ti-package"></i>
                Master Barang
            </span>

            <h2>Data Barang</h2>

            <p>
                Kelola barang koperasi dari satu tempat. Stok tidak diedit manual dari sini, karena stok masuk lewat restock dan keluar lewat transaksi.
            </p>
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

    <section class="barang-summary" data-animate="fade-up" data-delay="140">
        <article class="barang-summary-card summary-green">
            <span class="barang-summary-icon">
               <i class="ti ti-package"></i>
            </span>

            <div>
                <small>Total Barang</small>
                <strong><?= app_e((string) ($summary['total_barang'] ?? count($barangs))) ?></strong>
                <p>Semua data barang</p>
            </div>
        </article>

        <article class="barang-summary-card summary-blue">
            <span class="barang-summary-icon">
                <i class="ti ti-circle-check"></i>
            </span>

            <div>
                <small>Barang Aktif</small>
                <strong><?= app_e((string) ($summary['barang_aktif'] ?? 0)) ?></strong>
                <p>Siap transaksi</p>
            </div>
        </article>

        <article class="barang-summary-card summary-gray">
            <span class="barang-summary-icon">
                <i class="ti ti-circle-off"></i>
            </span>

            <div>
                <small>Nonaktif</small>
                <strong><?= app_e((string) ($summary['barang_nonaktif'] ?? 0)) ?></strong>
                <p>Tidak dipakai</p>
            </div>
        </article>

        <article class="barang-summary-card summary-red">
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

    <section class="barang-panel">
        <div class="barang-panel-header">
            <div>
                <span>Inventori</span>
                <h3>Daftar Barang</h3>
            </div>

            <div class="barang-tools">
                <label class="barang-search">
                    <i class="ti ti-search"></i>
                    <input
                        type="search"
                        placeholder="Cari kode, barcode, nama, kategori..."
                        data-barang-search
                    >
                </label>

                <select class="barang-filter" data-barang-status-filter aria-label="Filter status barang">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>

                <select class="barang-filter" data-barang-stock-filter aria-label="Filter stok barang">
                    <option value="">Semua Stok</option>
                    <option value="aman">Aman</option>
                    <option value="menipis">Menipis</option>
                    <option value="habis">Habis</option>
                </select>

                <button type="button" class="barang-btn barang-btn-ghost" data-barang-reset>
                    <i class="ti ti-refresh"></i>
                    Reset
                </button>
            </div>
        </div>

        <?php if (empty($barangs)): ?>
            <div class="barang-empty">
                <span>
                    <i class="ti ti-package-off"></i>
                </span>
                <h4>Belum ada data barang</h4>
                <p>Tambahkan barang pertama supaya koperasi ini tidak cuma jadi halaman kosong yang terlihat mahal.</p>

                <a href="<?= app_e(app_url('/admin/barang/create')) ?>" class="barang-btn barang-btn-primary">
                    <i class="ti ti-plus"></i>
                    Tambah Barang
                </a>
            </div>
        <?php else: ?>
            <div class="barang-table-wrap">
                <table class="barang-table">
                    <thead>
                        <tr>
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
                            $status = strtolower((string) ($item['status'] ?? 'nonaktif'));
                            $stok = (int) ($item['stok'] ?? 0);
                            $stokMinimum = (int) ($item['stok_minimum'] ?? 0);
                            $stockMeta = barang_stock_meta($stok, $stokMinimum);

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
                                <td>
                                    <span class="barang-number"><?= app_e((string) ($index + 1)) ?></span>
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
                                        <?= app_e(($item['barcode'] ?? '') !== '' ? $item['barcode'] : '-') ?>
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
                                        <a
                                            href="<?= app_e(app_url('/admin/barang/edit/' . ($item['id'] ?? ''))) ?>"
                                            class="barang-action-btn action-edit"
                                            title="Edit barang"
                                            aria-label="Edit barang"
                                        >
                                            <i class="ti ti-edit"></i>
                                        </a>

                                        <form
                                            action="<?= app_e(app_url('/admin/barang/delete/' . ($item['id'] ?? ''))) ?>"
                                            method="POST"
                                            data-barang-delete-form
                                            data-confirm-title="Hapus / Nonaktifkan Barang"
                                            data-confirm-message="Barang <?= app_e($item['nama'] ?? '-') ?> akan dihapus kalau belum punya histori, atau dinonaktifkan kalau sudah pernah dipakai. Lanjut?"
                                        >
                                            <button
                                                type="submit"
                                                class="barang-action-btn action-delete"
                                                title="Hapus barang"
                                                aria-label="Hapus barang"
                                            >
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
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
        <?php endif; ?>
    </section>
</div>

<script src="<?= app_e(app_asset('assets/js/barang.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>