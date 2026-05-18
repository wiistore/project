<?php
$title = $title ?? 'Laporan Barang Terlaris';
$activeMenu = $activeMenu ?? 'laporan';

$pageCss = ['assets/css/laporan.css?v=1' . time()];

$__viewData = get_defined_vars();

$barangTerlaris = isset($__viewData['barangTerlaris']) && is_array($__viewData['barangTerlaris'])
    ? $__viewData['barangTerlaris']
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

$totalJenisBarang = count($barangTerlaris);
$totalQty = 0;
$totalPenjualan = 0;
$totalModal = 0;
$totalLaba = 0;

foreach ($barangTerlaris as $row) {
    $totalQty += (int) ($row['total_qty'] ?? 0);
    $totalPenjualan += (float) ($row['total_penjualan'] ?? 0);
    $totalModal += (float) ($row['total_modal'] ?? 0);
    $totalLaba += (float) ($row['total_laba'] ?? 0);
}

$topBarang = $barangTerlaris[0] ?? null;
$topBarangNama = $topBarang['nama_barang'] ?? '-';
$topBarangQty = (int) ($topBarang['total_qty'] ?? 0);

$marginLaba = $totalPenjualan > 0 ? ($totalLaba / $totalPenjualan) * 100 : 0;

$summaryCards = [
    [
        'class' => 'summary-green',
        'icon' => 'ti ti-award',
        'label' => 'Jenis Terjual',
        'value' => (string) $totalJenisBarang,
        'desc' => 'Barang masuk laporan',
    ],
    [
        'class' => 'summary-blue',
        'icon' => 'ti ti-packages',
        'label' => 'Total Qty',
        'value' => laporan_number($totalQty),
        'desc' => 'Akumulasi barang terjual',
    ],
    [
        'class' => 'summary-orange',
        'icon' => 'ti ti-cash',
        'label' => 'Total Penjualan',
        'value' => laporan_money($totalPenjualan),
        'desc' => 'Omzet dari barang',
    ],
    [
        'class' => 'summary-purple',
        'icon' => 'ti ti-chart-line',
        'label' => 'Total Laba',
        'value' => laporan_money($totalLaba),
        'desc' => number_format($marginLaba, 1, ',', '.') . '% margin',
    ],
];

$chartBarangQty = [
    'labels' => [],
    'values' => [],
    'datasetLabel' => 'Qty Terjual',
    'tooltipMode' => 'number',
];

foreach (array_slice($barangTerlaris, 0, 10) as $row) {
    $chartBarangQty['labels'][] = (string) ($row['nama_barang'] ?? '-');
    $chartBarangQty['values'][] = (int) ($row['total_qty'] ?? 0);
}
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="laporan-page">
    <section class="laporan-hero">
        <div class="laporan-hero-content">
            <span class="laporan-eyebrow">
                <i class="ti ti-award"></i>
                Laporan Barang Terlaris
            </span>

            <h2>Barang Terlaris</h2>

            <p>
                Lihat barang paling sering terjual, total qty, omzet, modal, dan laba per barang. Ini halaman buat tahu produk mana yang kerja keras, bukan cuma numpang nama di etalase.
            </p>
        </div>

        <div class="laporan-hero-actions">
            <a href="<?= app_e(laporan_export_url('/admin/laporan/export/barang-terlaris', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-btn laporan-btn-primary">
                <i class="ti ti-file-spreadsheet"></i>
                Export Excel
            </a>

            <a href="<?= app_e(app_url('/admin/laporan')) ?>" class="laporan-btn laporan-btn-soft">
                <i class="ti ti-layout-dashboard"></i>
                Ringkasan
            </a>
        </div>
    </section>

    <section class="laporan-nav">
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

        <a href="<?= app_e(app_url('/admin/laporan/barang-terlaris')) ?>" class="is-active">
            <i class="ti ti-award"></i>
            Barang Terlaris
        </a>

        <a href="<?= app_e(app_url('/admin/laporan/restock')) ?>">
            <i class="ti ti-stack-push"></i>
            Restock
        </a>
    </section>

    <section class="laporan-filter-panel">
        <div>
            <span>Filter Periode</span>
            <h3>Atur Rentang Tanggal</h3>
        </div>

        <form action="<?= app_e(app_url('/admin/laporan/barang-terlaris')) ?>" method="GET" class="laporan-filter-form">
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

            <a href="<?= app_e(app_url('/admin/laporan/barang-terlaris')) ?>" class="laporan-btn laporan-btn-muted">
                <i class="ti ti-refresh"></i>
                Reset
            </a>
        </form>
    </section>

    <section class="laporan-summary summary-count-4">
        <?php foreach ($summaryCards as $card): ?>
            <article class="laporan-summary-card <?= app_e($card['class']) ?>">
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

    <section class="laporan-chart-grid">
        <article class="laporan-chart-card laporan-chart-wide">
            <div class="laporan-card-head">
                <div>
                    <span>Grafik</span>
                    <h3>Top Barang Berdasarkan Qty</h3>
                </div>

                <a href="<?= app_e(laporan_export_url('/admin/laporan/export/barang-terlaris', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-mini-link">
                    <i class="ti ti-file-spreadsheet"></i>
                    Export
                </a>
            </div>

            <?php if (empty($barangTerlaris)): ?>
                <div class="laporan-empty is-chart">
                    <span>
                        <i class="ti ti-award-off"></i>
                    </span>

                    <h4>Belum ada barang terjual</h4>
                    <p>Belum ada transaksi di periode ini. Barang tidak bisa jadi terlaris kalau belum pernah laku, kejam tapi logis.</p>
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
                    <span>Juara</span>
                    <h3>Barang Paling Laris</h3>
                </div>
            </div>

            <?php if ($topBarang === null): ?>
                <div class="laporan-empty">
                    <span>
                        <i class="ti ti-package-off"></i>
                    </span>

                    <h4>Belum ada juara</h4>
                    <p>Belum ada barang yang terjual di periode ini.</p>
                </div>
            <?php else: ?>
                <div class="laporan-method-list">
                    <div class="laporan-method-item method-cash">
                        <span>
                            <i class="ti ti-award"></i>
                        </span>

                        <div>
                            <strong><?= app_e($topBarangNama) ?></strong>
                            <small><?= app_e($topBarang['kode_barang'] ?? '-') ?> • <?= app_e($topBarang['nama_kategori'] ?? '-') ?></small>
                        </div>

                        <b><?= app_e(laporan_number($topBarangQty)) ?> <?= app_e($topBarang['satuan'] ?? '') ?></b>
                    </div>

                    <div class="laporan-method-item method-qris">
                        <span>
                            <i class="ti ti-cash"></i>
                        </span>

                        <div>
                            <strong>Total Penjualan</strong>
                            <small>Omzet barang teratas</small>
                        </div>

                        <b><?= app_e(laporan_money($topBarang['total_penjualan'] ?? 0)) ?></b>
                    </div>

                    <div class="laporan-method-item method-ewallet">
                        <span>
                            <i class="ti ti-chart-line"></i>
                        </span>

                        <div>
                            <strong>Total Laba</strong>
                            <small>Laba barang teratas</small>
                        </div>

                        <b><?= app_e(laporan_money($topBarang['total_laba'] ?? 0)) ?></b>
                    </div>
                </div>
            <?php endif; ?>
        </article>
    </section>

    <section class="laporan-panel">
        <div class="laporan-card-head">
            <div>
                <span>Ranking</span>
                <h3>Daftar Barang Terlaris</h3>
            </div>

            <a href="<?= app_e(laporan_export_url('/admin/laporan/export/barang-terlaris', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-mini-link">
                <i class="ti ti-file-spreadsheet"></i>
                Excel
            </a>
        </div>

        <?php if (empty($barangTerlaris)): ?>
            <div class="laporan-empty">
                <span>
                    <i class="ti ti-package-off"></i>
                </span>

                <h4>Belum ada data barang terlaris</h4>
                <p>Belum ada barang yang terjual dalam periode ini.</p>
            </div>
        <?php else: ?>
            <div class="laporan-table-wrap">
                <table class="laporan-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Barang</th>
                            <th>Barcode</th>
                            <th>Kategori</th>
                            <th>Qty Terjual</th>
                            <th>Penjualan</th>
                            <th>Modal</th>
                            <th>Laba</th>
                            <th>Margin</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($barangTerlaris as $index => $row): ?>
                            <?php
                            $rowPenjualan = (float) ($row['total_penjualan'] ?? 0);
                            $rowModal = (float) ($row['total_modal'] ?? 0);
                            $rowLaba = (float) ($row['total_laba'] ?? 0);
                            $rowMargin = $rowPenjualan > 0 ? ($rowLaba / $rowPenjualan) * 100 : 0;
                            ?>

                            <tr>
                                <td>
                                    <span class="laporan-number">
                                        #<?= app_e((string) ($index + 1)) ?>
                                    </span>
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

                                <td><?= app_e(($row['barcode'] ?? '') !== '' ? $row['barcode'] : '-') ?></td>
                                <td><?= app_e($row['nama_kategori'] ?? '-') ?></td>

                                <td>
                                    <strong class="laporan-number">
                                        <?= app_e(laporan_number($row['total_qty'] ?? 0)) ?>
                                        <?= app_e($row['satuan'] ?? '') ?>
                                    </strong>
                                </td>

                                <td>
                                    <strong class="laporan-money">
                                        <?= app_e(laporan_money($rowPenjualan)) ?>
                                    </strong>
                                </td>

                                <td>
                                    <strong class="laporan-money is-muted">
                                        <?= app_e(laporan_money($rowModal)) ?>
                                    </strong>
                                </td>

                                <td>
                                    <strong class="laporan-money <?= $rowLaba >= 0 ? 'is-profit' : 'is-loss' ?>">
                                        <?= app_e(laporan_money($rowLaba)) ?>
                                    </strong>
                                </td>

                                <td>
                                    <strong class="laporan-number">
                                        <?= app_e(number_format($rowMargin, 1, ',', '.')) ?>%
                                    </strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
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
        'products' => $chartBarangQty,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= app_e(app_asset('assets/js/laporan.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>