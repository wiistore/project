<?php
$title = $title ?? 'Laporan Restock';
$activeMenu = $activeMenu ?? 'laporan';

$pageCss = ['assets/css/laporan.css'];

$__viewData = get_defined_vars();

$summary = isset($__viewData['summary']) && is_array($__viewData['summary'])
    ? $__viewData['summary']
    : [];

$restockByBarang = isset($__viewData['restockByBarang']) && is_array($__viewData['restockByBarang'])
    ? $__viewData['restockByBarang']
    : [];

$restockBySupplier = isset($__viewData['restockBySupplier']) && is_array($__viewData['restockBySupplier'])
    ? $__viewData['restockBySupplier']
    : [];

$tanggalMulai = isset($__viewData['tanggalMulai'])
    ? (string) $__viewData['tanggalMulai']
    : ($_GET['tanggal_mulai'] ?? '');

$tanggalSelesai = isset($__viewData['tanggalSelesai'])
    ? (string) $__viewData['tanggalSelesai']
    : ($_GET['tanggal_selesai'] ?? '');

if (!function_exists('laporan_money')) {
    function laporan_money(mixed $value): string
    {
        if (class_exists('Security') && method_exists('Security', 'rupiah')) {
            return Security::rupiah($value);
        }

        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('laporan_number')) {
    function laporan_number(mixed $value): string
    {
        return number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('laporan_export_url')) {
    function laporan_export_url(string $path, string $tanggalMulai, string $tanggalSelesai): string
    {
        $query = [];

        if ($tanggalMulai !== '') {
            $query['tanggal_mulai'] = $tanggalMulai;
        }

        if ($tanggalSelesai !== '') {
            $query['tanggal_selesai'] = $tanggalSelesai;
        }

        $url = app_url($path);

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }
}

$totalRestock = (int) ($summary['total_restock'] ?? 0);
$totalQty = (int) ($summary['total_qty'] ?? 0);
$totalNilai = (float) ($summary['total_nilai'] ?? 0);

$totalBarangRestock = count($restockByBarang);
$totalSupplierRestock = count($restockBySupplier);

$topBarang = $restockByBarang[0] ?? null;
$topSupplier = $restockBySupplier[0] ?? null;

$topBarangNama = (string) ($topBarang['nama_barang'] ?? '-');
$topBarangQty = (int) ($topBarang['total_qty'] ?? 0);

$topSupplierNama = (string) ($topSupplier['nama_supplier'] ?? '-');
$topSupplierQty = (int) ($topSupplier['total_qty'] ?? 0);

$rataNilaiRestock = $totalRestock > 0 ? $totalNilai / $totalRestock : 0;

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
        'icon' => 'ti ti-packages',
        'label' => 'Qty Masuk',
        'value' => laporan_number($totalQty),
        'desc' => 'Total barang masuk',
    ],
    [
        'class' => 'summary-orange',
        'icon' => 'ti ti-cash',
        'label' => 'Nilai Restock',
        'value' => laporan_money($totalNilai),
        'desc' => 'Total modal restock',
    ],
    [
        'class' => 'summary-purple',
        'icon' => 'ti ti-calculator',
        'label' => 'Rata Restock',
        'value' => laporan_money($rataNilaiRestock),
        'desc' => 'Nilai rata-rata transaksi',
    ],
];

$chartRestockBarang = [
    'labels' => [],
    'values' => [],
    'datasetLabel' => 'Qty Masuk',
    'tooltipMode' => 'number',
];

foreach (array_slice($restockByBarang, 0, 10) as $row) {
    $chartRestockBarang['labels'][] = (string) ($row['nama_barang'] ?? '-');
    $chartRestockBarang['values'][] = (int) ($row['total_qty'] ?? 0);
}

$chartRestockSupplier = [
    'labels' => [],
    'values' => [],
    'datasetLabel' => 'Nilai Restock',
    'tooltipMode' => 'money',
];

foreach (array_slice($restockBySupplier, 0, 8) as $row) {
    $chartRestockSupplier['labels'][] = (string) ($row['nama_supplier'] ?? '-');
    $chartRestockSupplier['values'][] = (float) ($row['total_nilai'] ?? 0);
}
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="laporan-page">
    <section class="laporan-hero" data-aos="fade-down" data-aos-duration="700">
        <div class="laporan-hero-content">
            <span class="laporan-eyebrow">
                <i class="ti ti-stack-push"></i>
                Laporan Restock
            </span>

            <h2>Restock</h2>

            <p>
                Pantau barang masuk, total modal restock, supplier paling aktif, dan produk yang paling sering diisi ulang. Ini bagian gudang yang biasanya berantakan kalau cuma mengandalkan ingatan manusia.
            </p>
        </div>

        <div class="laporan-hero-actions">
            <a href="<?= app_e(laporan_export_url('/admin/laporan/export/restock', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-btn laporan-btn-primary">
                <i class="ti ti-file-spreadsheet"></i>
                Export Excel
            </a>

            <a href="<?= app_e(app_url('/admin/restock')) ?>" class="laporan-btn laporan-btn-soft">
                <i class="ti ti-stack-push"></i>
                Data Restock
            </a>
        </div>
    </section>

    <section class="laporan-nav" data-aos="fade-up" data-aos-delay="100">
        <a href="<?= app_e(app_url('/admin/laporan')) ?>">
            <i class="ti ti-layout-dashboard"></i>
            Ringkasan
        </a>

        <a href="<?= app_e(app_url('/admin/laporan/penjualan')) ?>">
            <i class="ti ti-cash"></i>
            Penjualan
        </a>

        <a href="<?= app_e(app_url('/admin/laporan/laba')) ?>">
            <i class="ti ti-chart-line"></i>
            Laba
        </a>

        <a href="<?= app_e(app_url('/admin/laporan/barang-terlaris')) ?>">
            <i class="ti ti-award"></i>
            Barang Terlaris
        </a>

        <a href="<?= app_e(app_url('/admin/laporan/restock')) ?>" class="is-active">
            <i class="ti ti-stack-push"></i>
            Restock
        </a>
    </section>

    <section class="laporan-filter-panel" data-aos="fade-up" data-aos-delay="150">
        <div>
            <span>Filter Periode</span>
            <h3>Atur Rentang Tanggal</h3>
        </div>

        <form action="<?= app_e(app_url('/admin/laporan/restock')) ?>" method="GET" class="laporan-filter-form">
            <label>
                <span>Tanggal Mulai</span>
                <input type="date" name="tanggal_mulai" value="<?= app_e($tanggalMulai) ?>">
            </label>

            <label>
                <span>Tanggal Selesai</span>
                <input type="date" name="tanggal_selesai" value="<?= app_e($tanggalSelesai) ?>">
            </label>

            <button type="submit" class="laporan-btn laporan-btn-ghost">
                <i class="ti ti-filter"></i>
                Filter
            </button>

            <a href="<?= app_e(app_url('/admin/laporan/restock')) ?>" class="laporan-btn laporan-btn-muted">
                <i class="ti ti-refresh"></i>
                Reset
            </a>
        </form>
    </section>

    <section class="laporan-summary summary-count-4" data-aos="fade-up" data-aos-delay="200">
        <?php foreach ($summaryCards as $idx => $card): ?>
            <article class="laporan-summary-card <?= app_e($card['class']) ?>" data-aos="zoom-in" data-aos-delay="<?= app_e((string) (80 + ((int) ($idx ?? 0)) * 100)) ?>">
                <span class="laporan-summary-icon">
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

    <section class="laporan-chart-grid" data-aos="zoom-in" data-aos-delay="250">
        <article class="laporan-chart-card laporan-chart-wide">
            <div class="laporan-card-head">
                <div>
                    <span>Grafik</span>
                    <h3>Top Restock Barang</h3>
                </div>

                <a href="<?= app_e(laporan_export_url('/admin/laporan/export/restock', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-mini-link">
                    <i class="ti ti-file-spreadsheet"></i>
                    Export
                </a>
            </div>

            <?php if (empty($restockByBarang)): ?>
                <div class="laporan-empty is-chart">
                    <span>
                        <i class="ti ti-package-off"></i>
                    </span>

                    <h4>Belum ada restock barang</h4>
                    <p>Belum ada barang masuk di periode ini. Gudang sedang tenang, atau datanya memang kosong.</p>
                </div>
            <?php else: ?>
                <div class="laporan-chart-wrap">
                    <canvas id="laporanTopProductChart"></canvas>
                </div>
            <?php endif; ?>
        </article>

        <article class="laporan-panel">
            <div class="laporan-card-head">
                <div>
                    <span>Produk</span>
                    <h3>Barang Paling Sering Direstock</h3>
                </div>
            </div>

            <?php if ($topBarang === null): ?>
                <div class="laporan-empty">
                    <span>
                        <i class="ti ti-package-off"></i>
                    </span>

                    <h4>Belum ada barang</h4>
                    <p>Belum ada data restock barang.</p>
                </div>
            <?php else: ?>
                <div class="laporan-method-list">
                    <div class="laporan-method-item method-cash">
                        <span>
                            <i class="ti ti-package-import"></i>
                        </span>

                        <div>
                            <strong><?= app_e($topBarangNama) ?></strong>
                            <small><?= app_e($topBarang['kode_barang'] ?? '-') ?></small>
                        </div>

                        <b><?= app_e(laporan_number($topBarangQty)) ?> <?= app_e($topBarang['satuan'] ?? '') ?></b>
                    </div>

                    <div class="laporan-method-item method-qris">
                        <span>
                            <i class="ti ti-cash"></i>
                        </span>

                        <div>
                            <strong>Total Nilai</strong>
                            <small>Modal restock barang</small>
                        </div>

                        <b><?= app_e(laporan_money($topBarang['total_nilai'] ?? 0)) ?></b>
                    </div>

                    <div class="laporan-method-item method-ewallet">
                        <span>
                            <i class="ti ti-calculator"></i>
                        </span>

                        <div>
                            <strong>Rata Harga Beli</strong>
                            <small>Harga beli rata-rata</small>
                        </div>

                        <b><?= app_e(laporan_money($topBarang['rata_harga_beli'] ?? 0)) ?></b>
                    </div>
                </div>
            <?php endif; ?>
        </article>

        <article class="laporan-panel">
            <div class="laporan-card-head">
                <div>
                    <span>Supplier</span>
                    <h3>Supplier Teraktif</h3>
                </div>
            </div>

            <?php if ($topSupplier === null): ?>
                <div class="laporan-empty">
                    <span>
                        <i class="ti ti-truck-off"></i>
                    </span>

                    <h4>Belum ada supplier</h4>
                    <p>Belum ada supplier yang punya riwayat restock.</p>
                </div>
            <?php else: ?>
                <div class="laporan-method-list">
                    <div class="laporan-method-item method-transfer">
                        <span>
                            <i class="ti ti-truck-delivery"></i>
                        </span>

                        <div>
                            <strong><?= app_e($topSupplierNama) ?></strong>
                            <small><?= app_e((string) ($topSupplier['total_restock'] ?? 0)) ?> transaksi restock</small>
                        </div>

                        <b><?= app_e(laporan_number($topSupplierQty)) ?> item</b>
                    </div>

                    <div class="laporan-method-item method-qris">
                        <span>
                            <i class="ti ti-cash"></i>
                        </span>

                        <div>
                            <strong>Total Nilai</strong>
                            <small>Nilai pembelian supplier</small>
                        </div>

                        <b><?= app_e(laporan_money($topSupplier['total_nilai'] ?? 0)) ?></b>
                    </div>
                </div>
            <?php endif; ?>
        </article>
    </section>

    <section class="laporan-grid" data-aos="fade-up" data-aos-delay="300">
        <article class="laporan-panel">
            <div class="laporan-card-head">
                <div>
                    <span>Barang</span>
                    <h3>Restock Per Barang</h3>
                </div>

                <a href="<?= app_e(laporan_export_url('/admin/laporan/export/restock', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-mini-link">
                    <i class="ti ti-file-spreadsheet"></i>
                    Excel
                </a>
            </div>

            <?php if (empty($restockByBarang)): ?>
                <div class="laporan-empty">
                    <span>
                        <i class="ti ti-package-off"></i>
                    </span>

                    <h4>Belum ada restock barang</h4>
                    <p>Belum ada data restock barang pada periode ini.</p>
                </div>
            <?php else: ?>
                <div class="laporan-table-wrap">
                    <table class="laporan-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Barang</th>
                                <th>Qty Masuk</th>
                                <th>Total Nilai</th>
                                <th>Rata Harga Beli</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($restockByBarang as $index => $row): ?>
                                <tr>
                                    <td>
                                        <strong class="laporan-number">
                                            #<?= app_e((string) ($index + 1)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <div class="laporan-product">
                                            <span>
                                                <?= app_e((string) ($index + 1)) ?>
                                            </span>

                                            <div>
                                                <strong><?= app_e($row['nama_barang'] ?? '-') ?></strong>
                                                <small><?= app_e($row['kode_barang'] ?? '-') ?></small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <strong class="laporan-number">
                                            <?= app_e(laporan_number($row['total_qty'] ?? 0)) ?>
                                            <?= app_e($row['satuan'] ?? '') ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-money">
                                            <?= app_e(laporan_money($row['total_nilai'] ?? 0)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-money is-muted">
                                            <?= app_e(laporan_money($row['rata_harga_beli'] ?? 0)) ?>
                                        </strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </article>

        <article class="laporan-panel">
            <div class="laporan-card-head">
                <div>
                    <span>Supplier</span>
                    <h3>Restock Per Supplier</h3>
                </div>

                <a href="<?= app_e(laporan_export_url('/admin/laporan/export/restock', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-mini-link">
                    <i class="ti ti-file-spreadsheet"></i>
                    Excel
                </a>
            </div>

            <?php if (empty($restockBySupplier)): ?>
                <div class="laporan-empty">
                    <span>
                        <i class="ti ti-truck-off"></i>
                    </span>

                    <h4>Belum ada restock supplier</h4>
                    <p>Belum ada data supplier pada periode ini.</p>
                </div>
            <?php else: ?>
                <div class="laporan-table-wrap">
                    <table class="laporan-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Supplier</th>
                                <th>Total Restock</th>
                                <th>Total Qty</th>
                                <th>Total Nilai</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($restockBySupplier as $index => $row): ?>
                                <tr>
                                    <td>
                                        <strong class="laporan-number">
                                            #<?= app_e((string) ($index + 1)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <div class="laporan-product">
                                            <span>
                                                <i class="ti ti-truck-delivery"></i>
                                            </span>

                                            <div>
                                                <strong><?= app_e($row['nama_supplier'] ?? '-') ?></strong>
                                                <small>Supplier restock</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <strong class="laporan-number">
                                            <?= app_e((string) ($row['total_restock'] ?? 0)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-number">
                                            <?= app_e(laporan_number($row['total_qty'] ?? 0)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-money">
                                            <?= app_e(laporan_money($row['total_nilai'] ?? 0)) ?>
                                        </strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </article>
    </section>
</div>

<script type="application/json" id="laporanChartData">
    <?= json_encode([
        'sales' => [
            'labels' => [],
            'penjualan' => [],
            'laba' => [],
        ],
        'payments' => [
            'labels' => [],
            'values' => [],
        ],
        'products' => $chartRestockBarang,
        'suppliers' => $chartRestockSupplier,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= app_e(app_asset_versioned('assets/js/laporan.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>