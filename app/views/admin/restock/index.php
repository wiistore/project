<?php
$title = $title ?? 'Restock Barang';
$activeMenu = $activeMenu ?? 'restock';

$pageCss = ['assets/css/restock.css?v=1' . time()];

$restocks = $restocks ?? [];
$summary = $summary ?? [];
$flash = $flash ?? [];

$tanggalMulai = $tanggalMulai ?? ($_GET['tanggal_mulai'] ?? '');
$tanggalSelesai = $tanggalSelesai ?? ($_GET['tanggal_selesai'] ?? '');

$success = $flash['success'] ?? null;
$error = $flash['error'] ?? null;

if (!function_exists('restock_rupiah')) {
    function restock_rupiah(mixed $value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('restock_date')) {
    function restock_date(mixed $value, bool $withTime = false): string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return '-';
        }

        $time = strtotime($raw);

        if ($time === false) {
            return $raw;
        }

        return $withTime ? date('d M Y, H:i', $time) : date('d M Y', $time);
    }
}

if (!function_exists('restock_short')) {
    function restock_short(mixed $value, int $limit = 70): string
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

$totalRestock = (int) ($summary['total_restock'] ?? count($restocks));
$totalQty = (int) ($summary['total_qty'] ?? 0);
$totalNilai = (float) ($summary['total_nilai'] ?? 0);

$summaryCards = [
    [
        'class' => 'summary-green',
        'icon' => 'ti ti-stack-push',
        'label' => 'Total Restock',
        'value' => (string) $totalRestock,
        'desc' => 'Transaksi stok masuk',
    ],
    [
        'class' => 'summary-blue',
        'icon' => 'ti ti-package-import',
        'label' => 'Qty Masuk',
        'value' => (string) $totalQty,
        'desc' => 'Total barang masuk',
    ],
    [
        'class' => 'summary-orange',
        'icon' => 'ti ti-cash',
        'label' => 'Nilai Restock',
        'value' => restock_rupiah($totalNilai),
        'desc' => 'Modal pembelian barang',
    ],
];

$summaryCount = count($summaryCards);
$summaryClass = $summaryCount <= 4 ? 'summary-count-' . $summaryCount : 'summary-count-many';
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="restock-page">
    <?php if ($success): ?>
        <div class="restock-alert restock-alert-success">
            <i class="ti ti-circle-check"></i>
            <span><?= app_e($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="restock-alert restock-alert-error">
            <i class="ti ti-alert-triangle"></i>
            <span><?= app_e($error) ?></span>
        </div>
    <?php endif; ?>

    <section class="restock-hero">
        <div class="restock-hero-content">
            <span class="restock-eyebrow">
                <i class="ti ti-stack-push"></i>
                Stok Masuk
            </span>

            <h2>Restock Barang</h2>

            <p>
                Catat stok masuk dari supplier. Setiap restock akan menambah stok barang dan menyimpan harga beli untuk kebutuhan laporan laba.
            </p>
        </div>

        <div class="restock-hero-actions">
            <a href="<?= app_e(app_url('/admin/restock/create')) ?>" class="restock-btn restock-btn-primary">
                <i class="ti ti-plus"></i>
                Tambah Restock
            </a>

            <a href="<?= app_e(app_url('/admin/barang')) ?>" class="restock-btn restock-btn-soft">
                <i class="ti ti-package"></i>
                Lihat Barang
            </a>
        </div>
    </section>

    <section class="restock-summary <?= app_e($summaryClass) ?>"  data-animate="fade-up" data-delay="140">
        <?php foreach ($summaryCards as $card): ?>
            <article class="restock-summary-card <?= app_e($card['class']) ?>">
                <span class="restock-summary-icon">
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

    <section class="restock-panel">
        <div class="restock-panel-header">
            <div>
                <span>Inventori</span>
                <h3>Riwayat Restock</h3>
            </div>

            <div class="restock-tools">
                <form action="<?= app_e(app_url('/admin/restock')) ?>" method="GET" class="restock-date-filter">
                    <label>
                        <span>Tanggal Mulai</span>
                        <input type="date" name="tanggal_mulai" value="<?= app_e($tanggalMulai) ?>">
                    </label>

                    <label>
                        <span>Tanggal Selesai</span>
                        <input type="date" name="tanggal_selesai" value="<?= app_e($tanggalSelesai) ?>">
                    </label>

                    <button type="submit" class="restock-btn restock-btn-ghost">
                        <i class="ti ti-filter"></i>
                        Filter
                    </button>

                    <a href="<?= app_e(app_url('/admin/restock')) ?>" class="restock-btn restock-btn-muted">
                        <i class="ti ti-refresh"></i>
                        Reset
                    </a>
                </form>

                <label class="restock-search">
                    <i class="ti ti-search"></i>
                    <input
                        type="search"
                        placeholder="Cari barang, supplier, pembuat, catatan..."
                        data-restock-search
                    >
                </label>
            </div>
        </div>

        <?php if (empty($restocks)): ?>
            <div class="restock-empty">
                <span>
                    <i class="ti ti-stack-pop"></i>
                </span>

                <h4>Belum ada data restock</h4>

                <p>
                    Tambahkan restock pertama. Stok barang tidak akan bertambah dari doa, walau manusia sering mencoba.
                </p>

                <a href="<?= app_e(app_url('/admin/restock/create')) ?>" class="restock-btn restock-btn-primary">
                    <i class="ti ti-plus"></i>
                    Tambah Restock
                </a>
            </div>
        <?php else: ?>
            <div class="restock-table-wrap">
                <table class="restock-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Barang</th>
                            <th>Supplier</th>
                            <th>Qty</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual Baru</th>
                            <th>Total Nilai</th>
                            <th>Dibuat Oleh</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>

                    <tbody data-restock-table-body>
                        <?php foreach ($restocks as $index => $restock): ?>
                            <?php
                            $hargaJualBaru = $restock['harga_jual_baru'] ?? null;
                            $hasHargaJualBaru = $hargaJualBaru !== null && $hargaJualBaru !== '';
                            $searchText = implode(' ', [
                                $restock['tanggal'] ?? '',
                                $restock['kode_barang'] ?? '',
                                $restock['nama_barang'] ?? '',
                                $restock['nama_supplier'] ?? '',
                                $restock['dibuat_oleh'] ?? '',
                                $restock['catatan'] ?? '',
                            ]);
                            ?>

                            <tr data-restock-row data-search="<?= app_e(strtolower($searchText)) ?>">
                                <td>
                                    <span class="restock-number"><?= app_e((string) ($index + 1)) ?></span>
                                </td>

                                <td>
                                    <span class="restock-date">
                                        <i class="ti ti-calendar"></i>
                                        <?= app_e(restock_date($restock['tanggal'] ?? '')) ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="restock-product">
                                        <span class="restock-product-icon">
                                            <i class="ti ti-package"></i>
                                        </span>

                                        <div>
                                            <strong><?= app_e($restock['nama_barang'] ?? '-') ?></strong>
                                            <small>
                                                <?= app_e($restock['kode_barang'] ?? '-') ?>
                                                <?php if (!empty($restock['satuan'])): ?>
                                                    • <?= app_e($restock['satuan']) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="restock-pill">
                                        <i class="ti ti-truck-delivery"></i>
                                        <?= app_e($restock['nama_supplier'] ?? '-') ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="restock-qty">
                                        <i class="ti ti-plus"></i>
                                        <?= app_e((string) ((int) ($restock['qty'] ?? 0))) ?>
                                    </span>
                                </td>

                                <td>
                                    <strong class="restock-money">
                                        <?= app_e(restock_rupiah($restock['harga_beli'] ?? 0)) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php if ($hasHargaJualBaru): ?>
                                        <strong class="restock-money is-new-price">
                                            <?= app_e(restock_rupiah($hargaJualBaru)) ?>
                                        </strong>
                                    <?php else: ?>
                                        <span class="restock-muted">Tidak berubah</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <strong class="restock-money">
                                        <?= app_e(restock_rupiah($restock['total_nilai'] ?? 0)) ?>
                                    </strong>
                                </td>

                                <td>
                                    <span class="restock-user">
                                        <i class="ti ti-user"></i>
                                        <?= app_e($restock['dibuat_oleh'] ?? '-') ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="restock-note">
                                        <?= app_e(restock_short($restock['catatan'] ?? '', 60)) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="restock-filter-empty" data-restock-filter-empty hidden>
                    <span>
                        <i class="ti ti-search-off"></i>
                    </span>

                    <h4>Data tidak ketemu</h4>

                    <p>Keyword-nya terlalu spesifik. Database bukan dukun, dia cuma cocokkan teks.</p>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<script src="<?= app_e(app_asset('assets/js/restock.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>