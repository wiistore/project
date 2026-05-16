<?php
$title = $title ?? 'Detail Transaksi';
$activeMenu = $activeMenu ?? 'riwayat';

$pageCss = ['assets/css/riwayat.css?v=1' . time()];

$transaksi = $transaksi ?? [];
$items = $items ?? ($detailTransaksi ?? []);
$detailSummary = $detailSummary ?? [];

if (!function_exists('riwayat_detail_money')) {
    function riwayat_detail_money(mixed $value): string
    {
        if (class_exists('Security') && method_exists('Security', 'rupiah')) {
            return Security::rupiah($value);
        }

        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('riwayat_detail_date')) {
    function riwayat_detail_date(mixed $value, bool $withTime = true): string
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

if (!function_exists('riwayat_detail_method_label')) {
    function riwayat_detail_method_label(mixed $method): string
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

if (!function_exists('riwayat_detail_method_icon')) {
    function riwayat_detail_method_icon(mixed $method): string
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

$id = (int) ($transaksi['id'] ?? 0);
$kode = (string) ($transaksi['kode_transaksi'] ?? '-');
$tanggal = (string) ($transaksi['tanggal'] ?? '');
$kasir = (string) ($transaksi['nama_kasir'] ?? '-');
$method = strtolower((string) ($transaksi['metode_bayar'] ?? '-'));

$totalJual = (float) ($transaksi['total_jual'] ?? ($detailSummary['total_jual'] ?? 0));
$totalBeli = (float) ($transaksi['total_beli'] ?? ($detailSummary['total_beli'] ?? 0));
$totalLaba = (float) ($transaksi['total_laba'] ?? ($detailSummary['total_laba'] ?? 0));
$nominalBayar = (float) ($transaksi['nominal_bayar'] ?? 0);
$kembalian = (float) ($transaksi['kembalian'] ?? 0);

$totalItem = (int) ($detailSummary['total_item'] ?? count($items));
$totalQty = (int) ($detailSummary['total_qty'] ?? 0);

if ($totalQty <= 0) {
    foreach ($items as $item) {
        $totalQty += (int) ($item['qty'] ?? 0);
    }
}

$labaPercent = $totalJual > 0 ? ($totalLaba / $totalJual) * 100 : 0;

$summaryCards = [
    [
        'class' => 'summary-green',
        'icon' => 'ti ti-cash',
        'label' => 'Total Penjualan',
        'value' => riwayat_detail_money($totalJual),
        'desc' => 'Nilai transaksi',
    ],
    [
        'class' => 'summary-blue',
        'icon' => 'ti ti-packages',
        'label' => 'Total Qty',
        'value' => (string) $totalQty,
        'desc' => $totalItem . ' jenis item',
    ],
    [
        'class' => 'summary-orange',
        'icon' => 'ti ti-chart-line',
        'label' => 'Total Laba',
        'value' => riwayat_detail_money($totalLaba),
        'desc' => number_format($labaPercent, 1, ',', '.') . '% margin',
    ],
];
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="riwayat-page">
    <section class="riwayat-hero">
        <div class="riwayat-hero-content">
            <span class="riwayat-eyebrow">
                <i class="ti ti-receipt"></i>
                Detail Transaksi
            </span>

            <h2><?= app_e($kode) ?></h2>

            <p>
                Detail item, pembayaran, modal, dan laba transaksi. Ini tempat angka dibedah, bukan tempat pura-pura laporan selalu benar.
            </p>
        </div>

        <div class="riwayat-hero-actions">
            <a href="<?= app_e(app_url('/admin/riwayat-transaksi')) ?>" class="riwayat-btn riwayat-btn-soft">
                <i class="ti ti-arrow-left"></i>
                Kembali
            </a>

            <a href="<?= app_e(app_url('/admin/transaksi/struk/' . $id)) ?>" class="riwayat-btn riwayat-btn-primary">
                <i class="ti ti-receipt"></i>
                Lihat Struk
            </a>
        </div>
    </section>

    <section class="riwayat-summary summary-count-3">
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

    <section class="riwayat-detail-layout">
        <article class="riwayat-detail-panel">
            <div class="riwayat-detail-head">
                <div>
                    <span>Informasi</span>
                    <h3>Informasi Transaksi</h3>
                </div>

                <span class="riwayat-detail-status">
                    <i class="ti ti-circle-check"></i>
                    Selesai
                </span>
            </div>

            <div class="riwayat-info-grid">
                <div class="riwayat-info-item">
                    <span>Kode Transaksi</span>
                    <strong><?= app_e($kode) ?></strong>
                </div>

                <div class="riwayat-info-item">
                    <span>Tanggal</span>
                    <strong><?= app_e(riwayat_detail_date($tanggal)) ?></strong>
                </div>

                <div class="riwayat-info-item">
                    <span>Kasir</span>
                    <strong><?= app_e($kasir) ?></strong>
                </div>

                <div class="riwayat-info-item">
                    <span>Metode Bayar</span>
                    <strong class="riwayat-method-text">
                        <i class="<?= app_e(riwayat_detail_method_icon($method)) ?>"></i>
                        <?= app_e(riwayat_detail_method_label($method)) ?>
                    </strong>
                </div>

                <div class="riwayat-info-item">
                    <span>Nominal Bayar</span>
                    <strong><?= app_e(riwayat_detail_money($nominalBayar)) ?></strong>
                </div>

                <div class="riwayat-info-item">
                    <span>Kembalian</span>
                    <strong class="is-change"><?= app_e(riwayat_detail_money($kembalian)) ?></strong>
                </div>

                <div class="riwayat-info-item">
                    <span>Total Modal</span>
                    <strong><?= app_e(riwayat_detail_money($totalBeli)) ?></strong>
                </div>

                <div class="riwayat-info-item">
                    <span>Total Laba</span>
                    <strong class="<?= $totalLaba >= 0 ? 'is-profit' : 'is-loss' ?>">
                        <?= app_e(riwayat_detail_money($totalLaba)) ?>
                    </strong>
                </div>
            </div>
        </article>

        <aside class="riwayat-detail-side">
            <div class="riwayat-detail-action-card">
                <span class="riwayat-detail-action-icon">
                    <i class="ti ti-bolt"></i>
                </span>

                <h4>Aksi Cepat</h4>

                <p>
                    Cek struk, download PDF, atau balik ke riwayat. Hidup terlalu pendek buat nyari tombol di pojokan.
                </p>

                <div class="riwayat-detail-actions">
                    <a href="<?= app_e(app_url('/admin/transaksi/struk/' . $id)) ?>" class="riwayat-detail-action action-struk">
                        <i class="ti ti-receipt"></i>
                        <span>
                            <strong>Lihat Struk</strong>
                            <small>Preview dan cetak struk</small>
                        </span>
                    </a>

                    <a href="<?= app_e(app_url('/admin/transaksi/pdf/' . $id)) ?>" class="riwayat-detail-action action-pdf">
                        <i class="ti ti-file-download"></i>
                        <span>
                            <strong>Download PDF</strong>
                            <small>Struk thermal PDF</small>
                        </span>
                    </a>

                    <a href="<?= app_e(app_url('/admin/riwayat-transaksi')) ?>" class="riwayat-detail-action action-back">
                        <i class="ti ti-arrow-left"></i>
                        <span>
                            <strong>Kembali</strong>
                            <small>Ke daftar riwayat</small>
                        </span>
                    </a>
                </div>
            </div>
        </aside>
    </section>

    <section class="riwayat-panel">
        <div class="riwayat-panel-header">
            <div>
                <span>Item</span>
                <h3>Detail Item Transaksi</h3>
            </div>

            <div class="riwayat-detail-total">
                <span>Total Laba</span>
                <strong><?= app_e(riwayat_detail_money($totalLaba)) ?></strong>
            </div>
        </div>

        <?php if (empty($items)): ?>
            <div class="riwayat-empty">
                <span>
                    <i class="ti ti-package-off"></i>
                </span>

                <h4>Detail item kosong</h4>

                <p>
                    Transaksi ada, tapi item-nya kosong. Ini aneh. Kalau sering kejadian, backend perlu diaudit, bukan cuma dipandangi.
                </p>
            </div>
        <?php else: ?>
            <div class="riwayat-table-wrap">
                <table class="riwayat-table riwayat-detail-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Barang</th>
                            <th>Barcode</th>
                            <th>Satuan</th>
                            <th>Qty</th>
                            <th>Harga Jual</th>
                            <th>Harga Beli</th>
                            <th>Subtotal Jual</th>
                            <th>Subtotal Beli</th>
                            <th>Laba Item</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                            <?php
                            $qty = (int) ($item['qty'] ?? 0);
                            $hargaJual = (float) ($item['harga_jual'] ?? 0);
                            $hargaBeli = (float) ($item['harga_beli'] ?? 0);
                            $subtotalJual = (float) ($item['subtotal_jual'] ?? ($qty * $hargaJual));
                            $subtotalBeli = (float) ($item['subtotal_beli'] ?? ($qty * $hargaBeli));
                            $labaItem = (float) ($item['laba_item'] ?? ($subtotalJual - $subtotalBeli));
                            ?>

                            <tr>
                                <td>
                                    <span class="riwayat-number"><?= app_e((string) ($index + 1)) ?></span>
                                </td>

                                <td>
                                    <div class="riwayat-code">
                                        <span class="riwayat-code-icon">
                                            <i class="ti ti-package"></i>
                                        </span>

                                        <div>
                                            <strong><?= app_e($item['nama_barang'] ?? '-') ?></strong>
                                            <small><?= app_e($item['kode_barang'] ?? '-') ?></small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="riwayat-date">
                                        <i class="ti ti-barcode"></i>
                                        <?= app_e(($item['barcode'] ?? '') !== '' ? $item['barcode'] : '-') ?>
                                    </span>
                                </td>

                                <td><?= app_e($item['satuan'] ?? '-') ?></td>

                                <td>
                                    <span class="riwayat-qty">
                                        <i class="ti ti-x"></i>
                                        <?= app_e((string) $qty) ?>
                                    </span>
                                </td>

                                <td>
                                    <strong class="riwayat-money"><?= app_e(riwayat_detail_money($hargaJual)) ?></strong>
                                </td>

                                <td>
                                    <strong class="riwayat-money is-muted"><?= app_e(riwayat_detail_money($hargaBeli)) ?></strong>
                                </td>

                                <td>
                                    <strong class="riwayat-money"><?= app_e(riwayat_detail_money($subtotalJual)) ?></strong>
                                </td>

                                <td>
                                    <strong class="riwayat-money is-muted"><?= app_e(riwayat_detail_money($subtotalBeli)) ?></strong>
                                </td>

                                <td>
                                    <strong class="riwayat-money <?= $labaItem >= 0 ? 'is-profit' : 'is-loss' ?>">
                                        <?= app_e(riwayat_detail_money($labaItem)) ?>
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

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>