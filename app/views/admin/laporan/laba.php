<?php
$title = $title ?? 'Laporan Laba';
$activeMenu = $activeMenu ?? 'laporan';

$pageCss = ['assets/css/laporan.css?v1=' . time()];

$__viewData = get_defined_vars();

$summary = isset($__viewData['summary']) && is_array($__viewData['summary'])
    ? $__viewData['summary']
    : [];

$penjualanHarian = isset($__viewData['penjualanHarian']) && is_array($__viewData['penjualanHarian'])
    ? $__viewData['penjualanHarian']
    : [];

$penjualanKasir = isset($__viewData['penjualanKasir']) && is_array($__viewData['penjualanKasir'])
    ? $__viewData['penjualanKasir']
    : [];

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

if (!function_exists('laporan_date')) {
    function laporan_date(mixed $value): string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return '-';
        }

        $time = strtotime($raw);

        if ($time === false) {
            return $raw;
        }

        return date('d M Y', $time);
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

$totalTransaksi = (int) ($summary['total_transaksi'] ?? 0);
$totalPenjualan = (float) ($summary['total_penjualan'] ?? 0);
$totalModal = (float) ($summary['total_modal'] ?? 0);
$totalLaba = (float) ($summary['total_laba'] ?? 0);

$marginLaba = $totalPenjualan > 0 ? ($totalLaba / $totalPenjualan) * 100 : 0;
$rasioModal = $totalPenjualan > 0 ? ($totalModal / $totalPenjualan) * 100 : 0;

$summaryCards = [
    [
        'class' => 'summary-green',
        'icon' => 'ti ti-chart-line',
        'label' => 'Total Laba',
        'value' => laporan_money($totalLaba),
        'desc' => number_format($marginLaba, 1, ',', '.') . '% margin',
    ],
    [
        'class' => 'summary-blue',
        'icon' => 'ti ti-cash',
        'label' => 'Total Penjualan',
        'value' => laporan_money($totalPenjualan),
        'desc' => 'Omzet kotor',
    ],
    [
        'class' => 'summary-orange',
        'icon' => 'ti ti-wallet',
        'label' => 'Total Modal',
        'value' => laporan_money($totalModal),
        'desc' => number_format($rasioModal, 1, ',', '.') . '% dari omzet',
    ],
    [
        'class' => 'summary-purple',
        'icon' => 'ti ti-receipt',
        'label' => 'Total Transaksi',
        'value' => (string) $totalTransaksi,
        'desc' => 'Transaksi dalam periode',
    ],
];

$chartPenjualan = [
    'labels' => [],
    'penjualan' => [],
    'laba' => [],
];

foreach ($penjualanHarian as $row) {
    $chartPenjualan['labels'][] = laporan_date($row['tanggal'] ?? '');
    $chartPenjualan['penjualan'][] = (float) ($row['total_penjualan'] ?? 0);
    $chartPenjualan['laba'][] = (float) ($row['total_laba'] ?? 0);
}

$chartBarang = [
    'labels' => [],
    'values' => [],
    'datasetLabel' => 'Laba Barang',
    'tooltipMode' => 'money',
];

foreach (array_slice($barangTerlaris, 0, 8) as $row) {
    $chartBarang['labels'][] = (string) ($row['nama_barang'] ?? '-');
    $chartBarang['values'][] = (float) ($row['total_laba'] ?? 0);
}
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="laporan-page">
    <section class="laporan-hero">
        <div class="laporan-hero-content">
            <span class="laporan-eyebrow">
                <i class="ti ti-chart-line"></i>
                Laporan Laba
            </span>

            <h2>Laba</h2>

            <p>
                Pantau laba bersih dari penjualan, modal barang, margin, performa harian, kasir, dan barang penyumbang laba. Ini bagian yang biasanya bikin pemilik usaha tiba-tiba religius.
            </p>
        </div>

        <div class="laporan-hero-actions">
            <a href="<?= app_e(laporan_export_url('/admin/laporan/export/laba', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-btn laporan-btn-primary">
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

        <a href="<?= app_e(app_url('/admin/laporan/laba')) ?>" class="is-active">
            <i class="ti ti-chart-line"></i>
            Laba
        </a>

        <a href="<?= app_e(app_url('/admin/laporan/barang-terlaris')) ?>">
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

        <form action="<?= app_e(app_url('/admin/laporan/laba')) ?>" method="GET" class="laporan-filter-form">
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

            <a href="<?= app_e(app_url('/admin/laporan/laba')) ?>" class="laporan-btn laporan-btn-muted">
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
                    <h3>Penjualan vs Laba Harian</h3>
                </div>

                <a href="<?= app_e(laporan_export_url('/admin/laporan/export/laba', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-mini-link">
                    <i class="ti ti-file-spreadsheet"></i>
                    Export
                </a>
            </div>

            <?php if (empty($penjualanHarian)): ?>
                <div class="laporan-empty is-chart">
                    <span>
                        <i class="ti ti-chart-line"></i>
                    </span>

                    <h4>Belum ada data laba</h4>
                    <p>Belum ada transaksi dalam periode ini. Laba tidak tumbuh dari niat baik, sayangnya.</p>
                </div>
            <?php else: ?>
                <div class="laporan-chart-wrap">
                    <canvas id="laporanSalesChart"></canvas>
                </div>
            <?php endif; ?>
        </article>

        <article class="laporan-chart-card">
            <div class="laporan-card-head">
                <div>
                    <span>Barang</span>
                    <h3>Laba per Barang</h3>
                </div>
            </div>

            <?php if (empty($barangTerlaris)): ?>
                <div class="laporan-empty is-chart">
                    <span>
                        <i class="ti ti-package-off"></i>
                    </span>

                    <h4>Belum ada laba barang</h4>
                    <p>Barang belum punya transaksi di periode ini.</p>
                </div>
            <?php else: ?>
                <div class="laporan-chart-wrap chart-small">
                    <canvas id="laporanTopProductChart"></canvas>
                </div>
            <?php endif; ?>
        </article>

        <article class="laporan-panel">
            <div class="laporan-card-head">
                <div>
                    <span>Margin</span>
                    <h3>Komposisi Keuangan</h3>
                </div>
            </div>

            <div class="laporan-method-list">
                <div class="laporan-method-item method-cash">
                    <span>
                        <i class="ti ti-chart-line"></i>
                    </span>

                    <div>
                        <strong>Margin Laba</strong>
                        <small>Dari total penjualan</small>
                    </div>

                    <b><?= app_e(number_format($marginLaba, 1, ',', '.')) ?>%</b>
                </div>

                <div class="laporan-method-item method-qris">
                    <span>
                        <i class="ti ti-cash"></i>
                    </span>

                    <div>
                        <strong>Penjualan</strong>
                        <small>Omzet kotor</small>
                    </div>

                    <b><?= app_e(laporan_money($totalPenjualan)) ?></b>
                </div>

                <div class="laporan-method-item method-ewallet">
                    <span>
                        <i class="ti ti-wallet"></i>
                    </span>

                    <div>
                        <strong>Modal</strong>
                        <small>Harga beli barang</small>
                    </div>

                    <b><?= app_e(laporan_money($totalModal)) ?></b>
                </div>
            </div>
        </article>
    </section>

    <section class="laporan-grid">
        <article class="laporan-panel">
            <div class="laporan-card-head">
                <div>
                    <span>Harian</span>
                    <h3>Laba Harian</h3>
                </div>

                <a href="<?= app_e(laporan_export_url('/admin/laporan/export/laba', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-mini-link">
                    <i class="ti ti-file-spreadsheet"></i>
                    Excel
                </a>
            </div>

            <?php if (empty($penjualanHarian)): ?>
                <div class="laporan-empty">
                    <span>
                        <i class="ti ti-calendar-off"></i>
                    </span>

                    <h4>Belum ada data laba</h4>
                    <p>Belum ada transaksi di periode ini.</p>
                </div>
            <?php else: ?>
                <div class="laporan-table-wrap">
                    <table class="laporan-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Transaksi</th>
                                <th>Penjualan</th>
                                <th>Modal</th>
                                <th>Laba</th>
                                <th>Margin</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($penjualanHarian as $row): ?>
                                <?php
                                $rowPenjualan = (float) ($row['total_penjualan'] ?? 0);
                                $rowModal = (float) ($row['total_modal'] ?? 0);
                                $rowLaba = (float) ($row['total_laba'] ?? 0);
                                $rowMargin = $rowPenjualan > 0 ? ($rowLaba / $rowPenjualan) * 100 : 0;
                                ?>

                                <tr>
                                    <td>
                                        <span class="laporan-date">
                                            <i class="ti ti-calendar"></i>
                                            <?= app_e(laporan_date($row['tanggal'] ?? '')) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <strong class="laporan-number">
                                            <?= app_e((string) ($row['total_transaksi'] ?? 0)) ?>
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
        </article>

        <article class="laporan-panel">
            <div class="laporan-card-head">
                <div>
                    <span>Kasir</span>
                    <h3>Laba Per Kasir</h3>
                </div>

                <a href="<?= app_e(laporan_export_url('/admin/laporan/export/laba', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-mini-link">
                    <i class="ti ti-file-spreadsheet"></i>
                    Excel
                </a>
            </div>

            <?php if (empty($penjualanKasir)): ?>
                <div class="laporan-empty">
                    <span>
                        <i class="ti ti-user-off"></i>
                    </span>

                    <h4>Belum ada data kasir</h4>
                    <p>Belum ada kasir yang menghasilkan transaksi pada periode ini.</p>
                </div>
            <?php else: ?>
                <div class="laporan-table-wrap">
                    <table class="laporan-table">
                        <thead>
                            <tr>
                                <th>Kasir</th>
                                <th>Transaksi</th>
                                <th>Penjualan</th>
                                <th>Modal</th>
                                <th>Laba</th>
                                <th>Margin</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($penjualanKasir as $index => $row): ?>
                                <?php
                                $kasirPenjualan = (float) ($row['total_penjualan'] ?? 0);
                                $kasirModal = (float) ($row['total_modal'] ?? 0);
                                $kasirLaba = (float) ($row['total_laba'] ?? 0);
                                $kasirMargin = $kasirPenjualan > 0 ? ($kasirLaba / $kasirPenjualan) * 100 : 0;
                                ?>

                                <tr>
                                    <td>
                                        <div class="laporan-product">
                                            <span>
                                                <?= app_e((string) ($index + 1)) ?>
                                            </span>

                                            <div>
                                                <strong><?= app_e($row['nama_kasir'] ?? '-') ?></strong>
                                                <small>Kontribusi laba</small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <strong class="laporan-number">
                                            <?= app_e((string) ($row['total_transaksi'] ?? 0)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-money">
                                            <?= app_e(laporan_money($kasirPenjualan)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-money is-muted">
                                            <?= app_e(laporan_money($kasirModal)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-money <?= $kasirLaba >= 0 ? 'is-profit' : 'is-loss' ?>">
                                            <?= app_e(laporan_money($kasirLaba)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-number">
                                            <?= app_e(number_format($kasirMargin, 1, ',', '.')) ?>%
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
                    <span>Produk</span>
                    <h3>Laba Per Barang</h3>
                </div>

                <a href="<?= app_e(laporan_export_url('/admin/laporan/export/laba', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-mini-link">
                    <i class="ti ti-file-spreadsheet"></i>
                    Excel
                </a>
            </div>

            <?php if (empty($barangTerlaris)): ?>
                <div class="laporan-empty">
                    <span>
                        <i class="ti ti-package-off"></i>
                    </span>

                    <h4>Belum ada data barang</h4>
                    <p>Barang belum punya transaksi pada periode ini.</p>
                </div>
            <?php else: ?>
                <div class="laporan-table-wrap">
                    <table class="laporan-table">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Qty</th>
                                <th>Penjualan</th>
                                <th>Modal</th>
                                <th>Laba</th>
                                <th>Margin</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($barangTerlaris as $index => $row): ?>
                                <?php
                                $barangPenjualan = (float) ($row['total_penjualan'] ?? 0);
                                $barangModal = (float) ($row['total_modal'] ?? 0);
                                $barangLaba = (float) ($row['total_laba'] ?? 0);
                                $barangMargin = $barangPenjualan > 0 ? ($barangLaba / $barangPenjualan) * 100 : 0;
                                ?>

                                <tr>
                                    <td>
                                        <div class="laporan-product">
                                            <span>
                                                <?= app_e((string) ($index + 1)) ?>
                                            </span>

                                            <div>
                                                <strong><?= app_e($row['nama_barang'] ?? '-') ?></strong>
                                                <small>
                                                    <?= app_e($row['kode_barang'] ?? '-') ?>
                                                    <?php if (!empty($row['nama_kategori'])): ?>
                                                        • <?= app_e($row['nama_kategori']) ?>
                                                    <?php endif; ?>
                                                </small>
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
                                            <?= app_e(laporan_money($barangPenjualan)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-money is-muted">
                                            <?= app_e(laporan_money($barangModal)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-money <?= $barangLaba >= 0 ? 'is-profit' : 'is-loss' ?>">
                                            <?= app_e(laporan_money($barangLaba)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-number">
                                            <?= app_e(number_format($barangMargin, 1, ',', '.')) ?>%
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
        'sales' => $chartPenjualan,
        'payments' => [
            'labels' => [],
            'values' => [],
        ],
        'products' => $chartBarang,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= app_e(app_asset('assets/js/laporan.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>