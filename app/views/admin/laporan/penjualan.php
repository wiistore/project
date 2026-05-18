<?php
$title = $title ?? 'Laporan Penjualan';
$activeMenu = $activeMenu ?? 'laporan';

$pageCss = ['assets/css/laporan.css'];

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

$metodePembayaran = isset($__viewData['metodePembayaran']) && is_array($__viewData['metodePembayaran'])
    ? $__viewData['metodePembayaran']
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
        'desc' => 'Transaksi penjualan',
    ],
    [
        'class' => 'summary-blue',
        'icon' => 'ti ti-cash',
        'label' => 'Total Penjualan',
        'value' => laporan_money($totalPenjualan),
        'desc' => 'Omzet dalam periode',
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
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="laporan-page">
    <section class="laporan-hero" data-aos="fade-down" data-aos-duration="700">
        <div class="laporan-hero-content">
            <span class="laporan-eyebrow">
                <i class="ti ti-cash"></i>
                Laporan Penjualan
            </span>

            <h2>Penjualan</h2>

            <p>
                Pantau omzet, modal, laba, performa harian, kontribusi kasir, dan metode pembayaran. Angka-angka ini lebih jujur daripada feeling “kayaknya rame”.
            </p>
        </div>

        <div class="laporan-hero-actions">
            <a href="<?= app_e(laporan_export_url('/admin/laporan/export/penjualan', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-btn laporan-btn-primary">
                <i class="ti ti-file-spreadsheet"></i>
                Export Excel
            </a>

            <a href="<?= app_e(app_url('/admin/laporan')) ?>" class="laporan-btn laporan-btn-soft">
                <i class="ti ti-layout-dashboard"></i>
                Ringkasan
            </a>
        </div>
    </section>

    <section class="laporan-nav" data-aos="fade-up" data-aos-delay="100">
        <a href="<?= app_e(app_url('/admin/laporan')) ?>">
            <i class="ti ti-layout-dashboard"></i>
            Ringkasan
        </a>

        <a href="<?= app_e(app_url('/admin/laporan/penjualan')) ?>" class="is-active">
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

        <form action="<?= app_e(app_url('/admin/laporan/penjualan')) ?>" method="GET" class="laporan-filter-form">
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

            <a href="<?= app_e(app_url('/admin/laporan/penjualan')) ?>" class="laporan-btn laporan-btn-muted">
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
                    <p>Belum ada transaksi dalam periode ini. Data tidak bisa muncul dari doa, sayangnya.</p>
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
                    <span>Pembayaran</span>
                    <h3>Metode Pembayaran</h3>
                </div>
            </div>

            <?php if (empty($metodePembayaran)): ?>
                <div class="laporan-empty is-chart">
                    <span>
                        <i class="ti ti-credit-card-off"></i>
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

        <article class="laporan-panel">
            <div class="laporan-card-head">
                <div>
                    <span>Ringkasan</span>
                    <h3>Metode Bayar</h3>
                </div>
            </div>

            <?php if (empty($metodePembayaran)): ?>
                <div class="laporan-empty">
                    <span>
                        <i class="ti ti-wallet-off"></i>
                    </span>

                    <h4>Belum ada data</h4>
                    <p>Belum ada transaksi pembayaran.</p>
                </div>
            <?php else: ?>
                <div class="laporan-method-list">
                    <?php foreach ($metodePembayaran as $row): ?>
                        <?php
                        $method = strtolower((string) ($row['metode_bayar'] ?? '-'));
                        $totalMethodTransaksi = (int) ($row['total_transaksi'] ?? 0);
                        $totalMethodPenjualan = (float) ($row['total_penjualan'] ?? 0);
                        ?>

                        <div class="laporan-method-item method-<?= app_e($method) ?>">
                            <span>
                                <i class="<?= app_e(laporan_method_icon($method)) ?>"></i>
                            </span>

                            <div>
                                <strong><?= app_e(laporan_method_label($method)) ?></strong>
                                <small><?= app_e((string) $totalMethodTransaksi) ?> transaksi</small>
                            </div>

                            <b><?= app_e(laporan_money($totalMethodPenjualan)) ?></b>
                        </div>
                    <?php endforeach; ?>
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
                    <span>Kasir</span>
                    <h3>Penjualan Per Kasir</h3>
                </div>

                <a href="<?= app_e(laporan_export_url('/admin/laporan/export/penjualan', $tanggalMulai, $tanggalSelesai)) ?>" class="laporan-mini-link">
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
                    <p>Belum ada kasir yang punya transaksi dalam periode ini.</p>
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
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($penjualanKasir as $index => $row): ?>
                                <tr>
                                    <td>
                                        <div class="laporan-product">
                                            <span>
                                                <?= app_e((string) ($index + 1)) ?>
                                            </span>

                                            <div>
                                                <strong><?= app_e($row['nama_kasir'] ?? '-') ?></strong>
                                                <small>Kasir transaksi</small>
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
    </section>
</div>

<script type="application/json" id="laporanChartData">
    <?= json_encode([
        'sales' => $chartPenjualan,
        'payments' => $chartMetode,
        'products' => [
            'labels' => [],
            'values' => [],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= app_e(app_asset_versioned('assets/js/laporan.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>