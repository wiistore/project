<?php
$title = $title ?? 'Dashboard Admin';
$activeMenu = $activeMenu ?? 'dashboard';

$pageCss = ['assets/css/dashboard.css?v=1' . time()];
$pageScript = 'dashboard';
$useChart = true;

$user = $user ?? [];

$totalBarang = (int) ($totalBarang ?? ($dashboard['total_barang'] ?? 0));
$totalTransaksiHariIni = (int) ($totalTransaksiHariIni ?? ($dashboard['total_transaksi_hari_ini'] ?? 0));
$totalPenjualanHariIni = (float) ($totalPenjualanHariIni ?? ($dashboard['penjualan_hari_ini'] ?? 0));
$stokMenipis = (int) ($stokMenipis ?? ($dashboard['stok_menipis'] ?? 0));

$transaksiTerbaru = $transaksiTerbaru ?? ($dashboard['transaksi_terbaru'] ?? []);

$chartPenjualan7Hari = $chartPenjualan7Hari ?? ($dashboard['chart_penjualan_7_hari'] ?? [
    'labels' => [],
    'values' => [],
]);

$chartTopBarang = $chartTopBarang ?? ($dashboard['chart_top_barang'] ?? [
    'labels' => [],
    'values' => [],
]);

$chartStatusStok = $chartStatusStok ?? ($dashboard['chart_status_stok'] ?? [
    'labels' => ['Aman', 'Menipis', 'Habis'],
    'values' => [0, 0, 0],
]);

if (!function_exists('dash_e')) {
    function dash_e(mixed $value): string
    {
        if (function_exists('app_e')) {
            return app_e($value);
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('dash_rupiah')) {
    function dash_rupiah(mixed $value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('dash_date')) {
    function dash_date(mixed $value): string
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

if (!function_exists('dash_user_name')) {
    function dash_user_name(?array $user): string
    {
        if (!$user) {
            return 'Admin';
        }

        return $user['nama_lengkap']
            ?? $user['nama']
            ?? $user['username']
            ?? 'Admin';
    }
}

$dashboardCharts = [
    'sales' => [
        'labels' => array_values($chartPenjualan7Hari['labels'] ?? []),
        'values' => array_map('floatval', array_values($chartPenjualan7Hari['values'] ?? [])),
    ],
    'topProducts' => [
        'labels' => array_values($chartTopBarang['labels'] ?? []),
        'values' => array_map('intval', array_values($chartTopBarang['values'] ?? [])),
    ],
    'stockStatus' => [
        'labels' => array_values($chartStatusStok['labels'] ?? ['Aman', 'Menipis', 'Habis']),
        'values' => array_map('intval', array_values($chartStatusStok['values'] ?? [0, 0, 0])),
    ],
];

$userName = dash_user_name($user);
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="dashboard-page">
    <section class="dashboard-hero" data-animate="fade-up">
        <div class="dashboard-hero-content">
            <span class="dashboard-eyebrow">
                <i class="ti ti-sparkles"></i>
                Ringkasan Hari Ini
            </span>

            <h2>Halo, <?= dash_e($userName) ?></h2>

            <p>
                Pantau barang, transaksi, stok menipis, dan performa koperasi dari satu halaman dashboard.
            </p>
        </div>

        <div class="dashboard-hero-actions">
            <a href="<?= dash_e(app_url('/admin/transaksi')) ?>" class="dashboard-action dashboard-action-primary">
                <i class="ti ti-shopping-cart-plus"></i>
                Mulai Transaksi
            </a>

            <a href="<?= dash_e(app_url('/admin/laporan')) ?>" class="dashboard-action dashboard-action-soft">
                <i class="ti ti-report-analytics"></i>
                Lihat Laporan
            </a>
        </div>
    </section>

    <section class="dashboard-stats">
        <a href="<?= dash_e(app_url('/admin/barang')) ?>" class="dashboard-stat-card stat-green" data-animate="fade-right" data-delay="40">
            <span class="dashboard-stat-icon">
                <i class="ti ti-package"></i>
            </span>
            <div>
                <small>Total Barang</small>
                <strong data-count-up="<?= dash_e((string) $totalBarang) ?>">0</strong>
                <p>Semua barang terdata</p>
            </div>
        </a>

        <a href="<?= dash_e(app_url('/admin/riwayat-transaksi')) ?>" class="dashboard-stat-card stat-blue" data-animate="zoom-in" data-delay="100">
            <span class="dashboard-stat-icon">
                <i class="ti ti-receipt"></i>
            </span>
            <div>
                <small>Transaksi Hari Ini</small>
                <strong data-count-up="<?= dash_e((string) $totalTransaksiHariIni) ?>">0</strong>
                <p>Jumlah transaksi masuk</p>
            </div>
        </a>

        <a href="<?= dash_e(app_url('/admin/laporan')) ?>" class="dashboard-stat-card stat-orange" data-animate="zoom-in" data-delay="160">
            <span class="dashboard-stat-icon">
                <i class="ti ti-cash"></i>
            </span>
            <div>
                <small>Penjualan Hari Ini</small>
                <strong data-count-up="<?= dash_e((string) $totalPenjualanHariIni) ?>" data-prefix="Rp ">Rp 0</strong>
                <p>Total omzet hari ini</p>
            </div>
        </a>

        <a href="<?= dash_e(app_url('/admin/barang')) ?>" class="dashboard-stat-card stat-red" data-animate="fade-left" data-delay="220">
            <span class="dashboard-stat-icon">
                <i class="ti ti-alert-triangle"></i>
            </span>
            <div>
                <small>Stok Menipis</small>
                <strong data-count-up="<?= dash_e((string) $stokMenipis) ?>">0</strong>
                <p>Perlu dicek/restock</p>
            </div>
        </a>
    </section>

    <section class="dashboard-grid">
        <article class="dashboard-card dashboard-card-large" data-animate="fade-right" data-delay="150">
            <div class="dashboard-card-header">
                <div>
                    <span>Grafik Penjualan</span>
                    <h3>Penjualan 7 Hari Terakhir</h3>
                </div>

                <span class="dashboard-badge">
                    <i class="ti ti-trending-up"></i>
                    Mingguan
                </span>
            </div>

            <div class="dashboard-chart chart-large">
                <canvas id="salesChart"></canvas>
            </div>
        </article>

        <article class="dashboard-card" data-animate="fade-left" data-delay="150">
            <div class="dashboard-card-header">
                <div>
                    <span>Status Stok</span>
                    <h3>Kondisi Barang</h3>
                </div>

                <span class="dashboard-badge badge-blue">
                    <i class="ti ti-package-import"></i>
                    Aktif
                </span>
            </div>

            <div class="dashboard-chart chart-doughnut">
                <canvas id="stockChart"></canvas>
            </div>

            <div class="dashboard-legend">
                <span><i class="legend-green"></i>Aman</span>
                <span><i class="legend-orange"></i>Menipis</span>
                <span><i class="legend-red"></i>Habis</span>
            </div>
        </article>
    </section>

    <section class="dashboard-grid dashboard-grid-bottom">
        <article class="dashboard-card" data-animate="fade-right" data-delay="150">
            <div class="dashboard-card-header">
                <div>
                    <span>Produk Populer</span>
                    <h3>Top Barang Terlaris</h3>
                </div>

                <a href="<?= dash_e(app_url('/admin/laporan')) ?>" class="dashboard-card-link">
                    Detail
                    <i class="ti ti-arrow-right"></i>
                </a>
            </div>

            <div class="dashboard-chart chart-bar">
                <canvas id="topProductChart"></canvas>
            </div>
        </article>

        <article class="dashboard-card" data-animate="fade-left" data-delay="150">
            <div class="dashboard-card-header">
                <div>
                    <span>Aktivitas</span>
                    <h3>Transaksi Terbaru</h3>
                </div>

                <a href="<?= dash_e(app_url('/admin/riwayat-transaksi')) ?>" class="dashboard-card-link">
                    Semua
                    <i class="ti ti-arrow-right"></i>
                </a>
            </div>

            <?php if (empty($transaksiTerbaru)): ?>
                <div class="dashboard-empty">
                    <i class="ti ti-receipt-off"></i>
                    <h4>Belum ada transaksi</h4>
                    <p>Transaksi terbaru akan muncul di sini setelah ada penjualan.</p>
                </div>
            <?php else: ?>
                <div class="dashboard-transactions">
                    <?php foreach ($transaksiTerbaru as $index => $transaksi): ?>
                        <a href="<?= dash_e(app_url('/admin/riwayat-transaksi')) ?>" class="dashboard-transaction-item">
                            <span class="dashboard-transaction-icon">
                                <i class="ti ti-receipt-2"></i>
                            </span>

                            <span class="dashboard-transaction-main">
                                <strong><?= dash_e($transaksi['kode_transaksi'] ?? '-') ?></strong>
                                <small>
                                    <?= dash_e(dash_date($transaksi['tanggal'] ?? '')) ?>
                                    •
                                    <?= dash_e($transaksi['kasir'] ?? '-') ?>
                                </small>
                            </span>

                            <span class="dashboard-transaction-side">
                                <strong><?= dash_e(dash_rupiah($transaksi['total_jual'] ?? 0)) ?></strong>
                                <small><?= dash_e(strtoupper((string) ($transaksi['metode_bayar'] ?? '-'))) ?></small>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>
    </section>

    <section class="dashboard-shortcuts" data-animate="fade-up" data-delay="150">
        <a href="<?= dash_e(app_url('/admin/barang/create')) ?>" class="dashboard-shortcut">
            <i class="ti ti-package"></i>
            <span>Tambah Barang</span>
        </a>

        <a href="<?= dash_e(app_url('/admin/restock/create')) ?>" class="dashboard-shortcut">
            <i class="ti ti-stack-push"></i>
            <span>Restock Barang</span>
        </a>

        <a href="<?= dash_e(app_url('/admin/transaksi')) ?>" class="dashboard-shortcut">
            <i class="ti ti-shopping-cart"></i>
            <span>Transaksi POS</span>
        </a>

        <a href="<?= dash_e(app_url('/admin/laporan')) ?>" class="dashboard-shortcut">
            <i class="ti ti-file-analytics"></i>
            <span>Cetak Laporan</span>
        </a>
    </section>
</div>

<script type="application/json" id="dashboardChartData">
    <?= json_encode($dashboardCharts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>