<?php
$title = $title ?? 'Riwayat Transaksi';
$activeMenu = $activeMenu ?? 'riwayat';

$pageCss = ['assets/css/riwayat.css?v=1' . time()];

$transaksis = $transaksis ?? [];
$summary = $summary ?? [];
$flash = $flash ?? [];

$tanggalMulai = $tanggalMulai ?? ($_GET['tanggal_mulai'] ?? '');
$tanggalSelesai = $tanggalSelesai ?? ($_GET['tanggal_selesai'] ?? '');

$success = $flash['success'] ?? null;
$error = $flash['error'] ?? null;

if (!function_exists('riwayat_money')) {
    function riwayat_money(mixed $value): string
    {
        if (class_exists('Security') && method_exists('Security', 'rupiah')) {
            return Security::rupiah($value);
        }

        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('riwayat_date')) {
    function riwayat_date(mixed $value, bool $withTime = true): string
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

if (!function_exists('riwayat_method_label')) {
    function riwayat_method_label(mixed $method): string
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

if (!function_exists('riwayat_method_icon')) {
    function riwayat_method_icon(mixed $method): string
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

$totalTransaksi = (int) ($summary['total_transaksi'] ?? count($transaksis));
$totalPenjualan = (float) ($summary['total_penjualan'] ?? 0);
$totalModal = (float) ($summary['total_modal'] ?? 0);
$totalLaba = (float) ($summary['total_laba'] ?? 0);

$methodCounts = [
    'cash' => 0,
    'qris' => 0,
    'transfer' => 0,
    'ewallet' => 0,
];

foreach ($transaksis as $transaksi) {
    $method = strtolower((string) ($transaksi['metode_bayar'] ?? ''));

    if (!isset($methodCounts[$method])) {
        $methodCounts[$method] = 0;
    }

    $methodCounts[$method]++;
}

arsort($methodCounts);
$topMethod = array_key_first($methodCounts);
$topMethodCount = $topMethod !== null ? (int) $methodCounts[$topMethod] : 0;

$summaryCards = [
    [
        'class' => 'summary-green',
        'icon' => 'ti ti-receipt',
        'label' => 'Total Transaksi',
        'value' => (string) $totalTransaksi,
        'desc' => 'Data yang tampil',
    ],
    [
        'class' => 'summary-blue',
        'icon' => 'ti ti-cash',
        'label' => 'Total Penjualan',
        'value' => riwayat_money($totalPenjualan),
        'desc' => 'Omzet transaksi',
    ],
    [
        'class' => 'summary-orange',
        'icon' => 'ti ti-chart-line',
        'label' => 'Total Laba',
        'value' => riwayat_money($totalLaba),
        'desc' => 'Laba dari transaksi',
    ],
    [
        'class' => 'summary-purple',
        'icon' => riwayat_method_icon($topMethod),
        'label' => 'Metode Terbanyak',
        'value' => $topMethodCount > 0 ? riwayat_method_label($topMethod) : '-',
        'desc' => $topMethodCount > 0 ? $topMethodCount . ' transaksi' : 'Belum ada data',
    ],
];

$summaryCount = count($summaryCards);
$summaryClass = $summaryCount <= 4 ? 'summary-count-' . $summaryCount : 'summary-count-many';
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="riwayat-page">
    <?php if ($success): ?>
        <div class="riwayat-alert riwayat-alert-success">
            <i class="ti ti-circle-check"></i>
            <span><?= app_e($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="riwayat-alert riwayat-alert-error">
            <i class="ti ti-alert-triangle"></i>
            <span><?= app_e($error) ?></span>
        </div>
    <?php endif; ?>

    <section class="riwayat-hero">
        <div class="riwayat-hero-content">
            <span class="riwayat-eyebrow">
                <i class="ti ti-history"></i>
                Riwayat Penjualan
            </span>

            <h2>Riwayat Transaksi</h2>

            <p>
                Lihat transaksi yang sudah tersimpan, filter berdasarkan tanggal, cek struk, dan download PDF. Bukan buat menghapus dosa transaksi, cuma menampilkannya.
            </p>
        </div>

        <div class="riwayat-hero-actions">
            <a href="<?= app_e(app_url('/admin/transaksi')) ?>" class="riwayat-btn riwayat-btn-primary">
                <i class="ti ti-shopping-cart-plus"></i>
                Transaksi Baru
            </a>

            <a href="<?= app_e(app_url('/admin/laporan')) ?>" class="riwayat-btn riwayat-btn-soft">
                <i class="ti ti-chart-bar"></i>
                Laporan
            </a>
        </div>
    </section>

    <section class="riwayat-summary <?= app_e($summaryClass) ?>">
        <?php foreach ($summaryCards as $card): ?>
            <article class="riwayat-summary-card <?= app_e($card['class']) ?>">
                <span class="riwayat-summary-icon">
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

    <section class="riwayat-panel">
        <div class="riwayat-panel-header">
            <div>
                <span>Transaksi</span>
                <h3>Daftar Riwayat</h3>
            </div>

            <div class="riwayat-tools">
                <form action="<?= app_e(app_url('/admin/riwayat-transaksi')) ?>" method="GET" class="riwayat-date-filter">
                    <label>
                        <span>Tanggal Mulai</span>
                        <input type="date" name="tanggal_mulai" value="<?= app_e($tanggalMulai) ?>">
                    </label>

                    <label>
                        <span>Tanggal Selesai</span>
                        <input type="date" name="tanggal_selesai" value="<?= app_e($tanggalSelesai) ?>">
                    </label>

                    <button type="submit" class="riwayat-btn riwayat-btn-ghost">
                        <i class="ti ti-filter"></i>
                        Filter
                    </button>

                    <a href="<?= app_e(app_url('/admin/riwayat-transaksi')) ?>" class="riwayat-btn riwayat-btn-muted">
                        <i class="ti ti-refresh"></i>
                        Reset
                    </a>
                </form>

                <div class="riwayat-table-tools">
                    <label class="riwayat-search">
                        <i class="ti ti-search"></i>
                        <input
                            type="search"
                            placeholder="Cari kode, kasir, metode..."
                            data-riwayat-search
                        >
                    </label>

                    <select class="riwayat-filter" data-riwayat-method-filter aria-label="Filter metode pembayaran">
                        <option value="">Semua Metode</option>
                        <option value="cash">Cash</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer">Transfer</option>
                        <option value="ewallet">E-Wallet</option>
                    </select>
                </div>
            </div>
        </div>

        <?php if (empty($transaksis)): ?>
            <div class="riwayat-empty">
                <span>
                    <i class="ti ti-receipt-off"></i>
                </span>

                <h4>Belum ada transaksi</h4>

                <p>
                    Belum ada riwayat sesuai filter ini. Entah memang sepi, atau filternya terlalu ambisius.
                </p>

                <a href="<?= app_e(app_url('/admin/transaksi')) ?>" class="riwayat-btn riwayat-btn-form">
                    <i class="ti ti-shopping-cart-plus"></i>
                    Buat Transaksi
                </a>
            </div>
        <?php else: ?>
            <div class="riwayat-table-wrap">
                <table class="riwayat-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Transaksi</th>
                            <th>Tanggal</th>
                            <th>Kasir</th>
                            <th>Metode</th>
                            <th>Total</th>
                            <th>Modal</th>
                            <th>Laba</th>
                            <th>Bayar</th>
                            <th>Kembalian</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>

                    <tbody data-riwayat-table-body>
                        <?php foreach ($transaksis as $index => $transaksi): ?>
                            <?php
                            $id = (int) ($transaksi['id'] ?? 0);
                            $kode = (string) ($transaksi['kode_transaksi'] ?? '-');
                            $tanggal = (string) ($transaksi['tanggal'] ?? '');
                            $kasir = (string) ($transaksi['nama_kasir'] ?? '-');
                            $method = strtolower((string) ($transaksi['metode_bayar'] ?? '-'));

                            $totalJualRow = (float) ($transaksi['total_jual'] ?? 0);
                            $totalBeliRow = (float) ($transaksi['total_beli'] ?? 0);
                            $totalLabaRow = (float) ($transaksi['total_laba'] ?? 0);
                            $nominalBayarRow = (float) ($transaksi['nominal_bayar'] ?? 0);
                            $kembalianRow = (float) ($transaksi['kembalian'] ?? 0);

                            $searchText = strtolower(implode(' ', [
                                $kode,
                                $tanggal,
                                $kasir,
                                $method,
                                riwayat_method_label($method),
                                $totalJualRow,
                                $totalLabaRow,
                            ]));
                            ?>

                            <tr
                                data-riwayat-row
                                data-search="<?= app_e($searchText) ?>"
                                data-method="<?= app_e($method) ?>"
                            >
                                <td>
                                    <span class="riwayat-number"><?= app_e((string) ($index + 1)) ?></span>
                                </td>

                                <td>
                                    <div class="riwayat-code">
                                        <span class="riwayat-code-icon">
                                            <i class="ti ti-receipt"></i>
                                        </span>

                                        <div>
                                            <strong><?= app_e($kode) ?></strong>
                                            <small>ID: <?= app_e((string) $id) ?></small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="riwayat-date">
                                        <i class="ti ti-calendar"></i>
                                        <?= app_e(riwayat_date($tanggal)) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="riwayat-user">
                                        <i class="ti ti-user"></i>
                                        <?= app_e($kasir) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="riwayat-method method-<?= app_e($method) ?>">
                                        <i class="<?= app_e(riwayat_method_icon($method)) ?>"></i>
                                        <?= app_e(riwayat_method_label($method)) ?>
                                    </span>
                                </td>

                                <td>
                                    <strong class="riwayat-money">
                                        <?= app_e(riwayat_money($totalJualRow)) ?>
                                    </strong>
                                </td>

                                <td>
                                    <strong class="riwayat-money is-muted">
                                        <?= app_e(riwayat_money($totalBeliRow)) ?>
                                    </strong>
                                </td>

                                <td>
                                    <strong class="riwayat-money <?= $totalLabaRow >= 0 ? 'is-profit' : 'is-loss' ?>">
                                        <?= app_e(riwayat_money($totalLabaRow)) ?>
                                    </strong>
                                </td>

                                <td>
                                    <strong class="riwayat-money">
                                        <?= app_e(riwayat_money($nominalBayarRow)) ?>
                                    </strong>
                                </td>

                                <td>
                                    <strong class="riwayat-money is-change">
                                        <?= app_e(riwayat_money($kembalianRow)) ?>
                                    </strong>
                                </td>

                                <td>
                                    <div class="riwayat-actions">
                                        <a
                                            href="<?= app_e(app_url('/admin/riwayat-transaksi/detail/' . $id)) ?>"
                                            class="riwayat-action-btn action-detail"
                                            title="Lihat detail"
                                            aria-label="Lihat detail transaksi"
                                        >
                                            <i class="ti ti-eye"></i>
                                        </a>

                                        <a
                                            href="<?= app_e(app_url('/admin/transaksi/struk/' . $id)) ?>"
                                            class="riwayat-action-btn action-struk"
                                            title="Lihat struk"
                                            aria-label="Lihat struk transaksi"
                                        >
                                            <i class="ti ti-receipt"></i>
                                        </a>

                                        <a
                                            href="<?= app_e(app_url('/admin/transaksi/pdf/' . $id)) ?>"
                                            class="riwayat-action-btn action-pdf"
                                            title="Download PDF"
                                            aria-label="Download PDF transaksi"
                                        >
                                            <i class="ti ti-file-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="riwayat-filter-empty" data-riwayat-filter-empty hidden>
                    <span>
                        <i class="ti ti-search-off"></i>
                    </span>

                    <h4>Data tidak ketemu</h4>

                    <p>
                        Keyword atau filter terlalu spesifik. Database bukan cenayang, dia cuma cocokkan teks.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<script src="<?= app_e(app_asset('assets/js/riwayat.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>