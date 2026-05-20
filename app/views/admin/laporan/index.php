<?php
$title = $title ?? 'Laporan';
$activeMenu = $activeMenu ?? 'laporan';

$pageCss = ['assets/css/laporan.css'];

$__viewData = get_defined_vars();

$summary = isset($__viewData['summary']) && is_array($__viewData['summary'])
    ? $__viewData['summary']
    : [];

$penjualanHarian = isset($__viewData['penjualanHarian']) && is_array($__viewData['penjualanHarian'])
    ? $__viewData['penjualanHarian']
    : [];

$barangTerlaris = isset($__viewData['barangTerlaris']) && is_array($__viewData['barangTerlaris'])
    ? $__viewData['barangTerlaris']
    : [];

$metodePembayaran = isset($__viewData['metodePembayaran']) && is_array($__viewData['metodePembayaran'])
    ? $__viewData['metodePembayaran']
    : [];

$stokMenipisData = isset($__viewData['stokMenipis']) && is_array($__viewData['stokMenipis'])
    ? $__viewData['stokMenipis']
    : [];

$flash = isset($__viewData['flash']) && is_array($__viewData['flash'])
    ? $__viewData['flash']
    : [];

$tanggalMulai = $tanggalMulai ?? ($_GET['tanggal_mulai'] ?? '');
$tanggalSelesai = $tanggalSelesai ?? ($_GET['tanggal_selesai'] ?? '');

$success = $flash['success'] ?? null;
$error = $flash['error'] ?? null;

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

if (!function_exists('laporan_method_label')) {
    function laporan_method_label(mixed $method): string
    {
        return match (strtolower((string) $method)) {
            'cash' => 'Cash',
            'qris' => 'QRIS',
            'transfer' => 'Transfer',
            'ewallet' => 'E-Wallet',
            default => ucfirst((string) $method),
        };
    }
}

if (!function_exists('laporan_method_icon')) {
    function laporan_method_icon(mixed $method): string
    {
        return match (strtolower((string) $method)) {
            'cash' => 'ti ti-cash',
            'qris' => 'ti ti-qrcode',
            'transfer' => 'ti ti-building-bank',
            'ewallet' => 'ti ti-wallet',
            default => 'ti ti-credit-card',
        };
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

$summaryCards = [
    [
        'class' => 'summary-green',
        'icon' => 'ti ti-receipt',
        'label' => 'Total Transaksi',
        'value' => (string) $totalTransaksi,
        'desc' => 'Transaksi dalam periode',
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
        'desc' => 'Harga beli barang',
    ],
    [
        'class' => 'summary-purple',
        'icon' => 'ti ti-chart-line',
        'label' => 'Total Laba',
        'value' => laporan_money($totalLaba),
        'desc' => number_format($marginLaba, 1, ',', '.') . '% margin',
    ],
];

$summaryCount = count($summaryCards);
$summaryClass = $summaryCount <= 4 ? 'summary-count-' . $summaryCount : 'summary-count-many';

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

$chartMetode = [
    'labels' => [],
    'values' => [],
];

foreach ($metodePembayaran as $row) {
    $chartMetode['labels'][] = laporan_method_label($row['metode_bayar'] ?? '-');
    $chartMetode['values'][] = (float) ($row['total_penjualan'] ?? 0);
}

$chartBarang = [
    'labels' => [],
    'values' => [],
];

foreach (array_slice($barangTerlaris, 0, 6) as $row) {
    $chartBarang['labels'][] = (string) ($row['nama_barang'] ?? '-');
    $chartBarang['values'][] = (int) ($row['total_qty'] ?? 0);
}
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="laporan-page">
    <?php if ($success): ?>
        <div class="laporan-alert laporan-alert-success">
            <i class="ti ti-circle-check"></i>
            <span><?= app_e($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="laporan-alert laporan-alert-error">
            <i class="ti ti-alert-triangle"></i>
            <span><?= app_e($error) ?></span>
        </div>
    <?php endif; ?>

    <section class="laporan-hero" data-aos="fade-down" data-aos-duration="700">
        <div class="laporan-hero-content">
            <span class="laporan-eyebrow">
                <i class="ti ti-chart-bar"></i>
                Ringkasan Laporan
            </span>

            <h2>Laporan</h2>


        </div>

        <div class="laporan-hero-actions">
            <a href="<?= app_e(laporan_export_url('/admin/laporan/export/ringkasan', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-btn laporan-btn-primary">
                <i class="ti ti-file-spreadsheet"></i>
                Export Excel
            </a>

            <a href="<?= app_e(app_url('/admin/riwayat-transaksi')) ?>" class="laporan-btn laporan-btn-soft">
                <i class="ti ti-history"></i>
                Riwayat
            </a>
        </div>
    </section>

    <section class="laporan-nav" data-aos="fade-up" data-aos-delay="100">
        <a href="<?= app_e(app_url('/admin/laporan')) ?>" class="is-active">
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

        <a href="<?= app_e(app_url('/admin/laporan/restock')) ?>">
            <i class="ti ti-stack-push"></i>
            Restock
        </a>
    </section>

    <section class="laporan-filter-panel" data-aos="fade-up" data-aos-delay="150">
        <div>
            <span>Filter Periode</span>
            <h3>Atur Rentang Tanggal</h3>
        </div>

        <form action="<?= app_e(app_url('/admin/laporan')) ?>" method="GET" class="laporan-filter-form">
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

            <a href="<?= app_e(app_url('/admin/laporan')) ?>" class="laporan-btn laporan-btn-muted">
                <i class="ti ti-refresh"></i>
                Reset
            </a>
        </form>
    </section>

    <section class="laporan-summary <?= app_e($summaryClass) ?>" data-aos="fade-up" data-aos-delay="200">
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
                    <h3>Penjualan & Laba Harian</h3>
                </div>

                <a href="<?= app_e(laporan_export_url('/admin/laporan/export/penjualan', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-mini-link">
                    <i class="ti ti-file-spreadsheet"></i>
                    Export
                </a>
            </div>

            <?php if (empty($penjualanHarian)): ?>
                <div class="laporan-empty is-chart">
                    <span>
                        <i class="ti ti-chart-line"></i>
                    </span>
                    <h4>Belum ada data penjualan</h4>
                    <p>Transaksi belum ada di periode ini. Laporan tidak bisa mengarang, sayangnya.</p>
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
                    <span>Metode</span>
                    <h3>Pembayaran</h3>
                </div>
            </div>

            <?php if (empty($metodePembayaran)): ?>
                <div class="laporan-empty is-chart">
                    <span>
                        <i class="ti ti-wallet-off"></i>
                    </span>
                    <h4>Belum ada pembayaran</h4>
                    <p>Metode pembayaran belum tercatat.</p>
                </div>
            <?php else: ?>
                <div class="laporan-chart-wrap chart-small">
                    <canvas id="laporanPaymentChart"></canvas>
                </div>
            <?php endif; ?>
        </article>

        <article class="laporan-chart-card">
            <div class="laporan-card-head">
                <div>
                    <span>Produk</span>
                    <h3>Top Barang</h3>
                </div>
            </div>

            <?php if (empty($barangTerlaris)): ?>
                <div class="laporan-empty is-chart">
                    <span>
                        <i class="ti ti-award-off"></i>
                    </span>
                    <h4>Belum ada barang terjual</h4>
                    <p>Barang belum punya riwayat penjualan.</p>
                </div>
            <?php else: ?>
                <div class="laporan-chart-wrap chart-small">
                    <canvas id="laporanTopProductChart"></canvas>
                </div>
            <?php endif; ?>
        </article>
    </section>

    <section class="laporan-grid" data-aos="fade-up" data-aos-delay="300">
        <article class="laporan-panel">
            <div class="laporan-card-head">
                <div>
                    <span>Harian</span>
                    <h3>Penjualan Harian</h3>
                </div>

                <a href="<?= app_e(laporan_export_url('/admin/laporan/export/penjualan', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-mini-link">
                    <i class="ti ti-file-spreadsheet"></i>
                    Excel
                </a>
            </div>

            <?php if (empty($penjualanHarian)): ?>
                <div class="laporan-empty">
                    <span>
                        <i class="ti ti-calendar-off"></i>
                    </span>
                    <h4>Belum ada data penjualan</h4>
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
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($penjualanHarian as $row): ?>
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
                                            <?= app_e(laporan_money($row['total_penjualan'] ?? 0)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-money is-muted">
                                            <?= app_e(laporan_money($row['total_modal'] ?? 0)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-money is-profit">
                                            <?= app_e(laporan_money($row['total_laba'] ?? 0)) ?>
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
                    <h3>Barang Terlaris</h3>
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
                    <h4>Belum ada barang terjual</h4>
                    <p>Barang belum punya data penjualan.</p>
                </div>
            <?php else: ?>
                <div class="laporan-table-wrap">
                    <table class="laporan-table">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Qty</th>
                                <th>Penjualan</th>
                                <th>Laba</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach (array_slice($barangTerlaris, 0, 10) as $index => $row): ?>
                                <tr>
                                    <td>
                                        <div class="laporan-product">
                                            <span><?= app_e((string) ($index + 1)) ?></span>

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
                                            <?= app_e(laporan_money($row['total_penjualan'] ?? 0)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-money is-profit">
                                            <?= app_e(laporan_money($row['total_laba'] ?? 0)) ?>
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
                    <span>Pembayaran</span>
                    <h3>Metode Pembayaran</h3>
                </div>
            </div>

            <?php if (empty($metodePembayaran)): ?>
                <div class="laporan-empty">
                    <span>
                        <i class="ti ti-credit-card-off"></i>
                    </span>
                    <h4>Belum ada pembayaran</h4>
                    <p>Data metode pembayaran kosong.</p>
                </div>
            <?php else: ?>
                <div class="laporan-method-list">
                    <?php foreach ($metodePembayaran as $row): ?>
                        <?php $method = strtolower((string) ($row['metode_bayar'] ?? '-')); ?>

                        <div class="laporan-method-item method-<?= app_e($method) ?>">
                            <span>
                                <i class="<?= app_e(laporan_method_icon($method)) ?>"></i>
                            </span>

                            <div>
                                <strong><?= app_e(laporan_method_label($method)) ?></strong>
                                <small><?= app_e((string) ($row['total_transaksi'] ?? 0)) ?> transaksi</small>
                            </div>

                            <b><?= app_e(laporan_money($row['total_penjualan'] ?? 0)) ?></b>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

        <article class="laporan-panel">
            <div class="laporan-card-head">
                <div>
                    <span>Inventori</span>
                    <h3>Stok Menipis</h3>
                </div>

                <a href="<?= app_e(app_url('/admin/barang')) ?>" class="laporan-mini-link">
                    <i class="ti ti-package"></i>
                    Barang
                </a>
            </div>

            <?php if (empty($stokMenipisData)): ?>
                <div class="laporan-empty">
                    <span>
                        <i class="ti ti-circle-check"></i>
                    </span>
                    <h4>Stok aman</h4>
                    <p>Tidak ada barang yang masuk stok menipis.</p>
                </div>
            <?php else: ?>
                <div class="laporan-table-wrap">
                    <table class="laporan-table">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Min.</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($stokMenipisData as $row): ?>
                                <tr>
                                    <td>
                                        <div class="laporan-product">
                                            <span class="is-danger">
                                                <i class="ti ti-alert-triangle"></i>
                                            </span>

                                            <div>
                                                <strong><?= app_e($row['nama_barang'] ?? '-') ?></strong>
                                                <small><?= app_e($row['kode_barang'] ?? '-') ?></small>
                                            </div>
                                        </div>
                                    </td>

                                    <td><?= app_e($row['nama_kategori'] ?? '-') ?></td>

                                    <td>
                                        <strong class="laporan-stock is-low">
                                            <?= app_e((string) ($row['stok'] ?? 0)) ?>
                                            <?= app_e($row['satuan'] ?? '') ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <strong class="laporan-stock">
                                            <?= app_e((string) ($row['stok_minimum'] ?? 0)) ?>
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
        'payments' => $chartMetode,
        'products' => $chartBarang,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= app_e(app_asset_versioned('assets/js/laporan.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>