<?php
$title = $title ?? 'Struk Transaksi';
$activeMenu = $activeMenu ?? 'transaksi';
$pageCss = ['assets/css/struk.css?v=1' . time()];

$transaksi = $transaksi ?? [];
$items = $items ?? ($detailTransaksi ?? []);
$user = $user ?? (class_exists('Session') ? Session::user() : []);

$role = strtolower((string) ($user['role'] ?? (class_exists('Session') ? Session::role() : 'kasir')));
$isAdmin = $role === 'admin';

$transaksiId = (int) ($transaksi['id'] ?? 0);
$kodeTransaksi = (string) ($transaksi['kode_transaksi'] ?? '-');

$backUrl = $isAdmin ? '/admin/transaksi' : '/kasir/transaksi';
$dashboardUrl = $isAdmin ? '/admin/dashboard' : '/kasir/dashboard';
$pdfUrl = $isAdmin
    ? '/admin/transaksi/pdf/' . $transaksiId
    : '/kasir/transaksi/pdf/' . $transaksiId;

if (!function_exists('struk_e')) {
    function struk_e(mixed $value): string
    {
        if (function_exists('app_e')) {
            return app_e($value);
        }

        if (class_exists('Security')) {
            return Security::e($value);
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('struk_money')) {
    function struk_money(mixed $value): string
    {
        if (class_exists('Security') && method_exists('Security', 'rupiah')) {
            return Security::rupiah($value);
        }

        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('struk_date')) {
    function struk_date(mixed $value): string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return '-';
        }

        $time = strtotime($raw);

        if ($time === false) {
            return $raw;
        }

        return date('d/m/Y H:i', $time);
    }
}

if (!function_exists('struk_user_name')) {
    function struk_user_name(array $user): string
    {
        return (string) (
            $user['nama_lengkap']
            ?? $user['nama']
            ?? $user['username']
            ?? 'Kasir'
        );
    }
}

$totalQty = 0;

foreach ($items as $item) {
    $totalQty += (int) ($item['qty'] ?? 0);
}

$receiptLines = [];
$receiptLines[] = 'LAB KEWIRAUSAHAAN';
$receiptLines[] = 'MTSN 8 BANYUWANGI';
$receiptLines[] = '------------------------------';
$receiptLines[] = 'No      : ' . $kodeTransaksi;
$receiptLines[] = 'Tanggal : ' . struk_date($transaksi['tanggal'] ?? $transaksi['created_at'] ?? '');
$receiptLines[] = 'Kasir   : ' . ($transaksi['nama_kasir'] ?? struk_user_name($user));
$receiptLines[] = 'Metode  : ' . strtoupper((string) ($transaksi['metode_bayar'] ?? '-'));
$receiptLines[] = '------------------------------';

foreach ($items as $item) {
    $namaBarang = (string) ($item['nama_barang'] ?? $item['nama'] ?? '-');
    $qty = (int) ($item['qty'] ?? 0);
    $harga = struk_money($item['harga_jual'] ?? 0);
    $subtotal = struk_money($item['subtotal_jual'] ?? 0);

    $receiptLines[] = $namaBarang;
    $receiptLines[] = $qty . ' x ' . $harga . ' = ' . $subtotal;
}

$receiptLines[] = '------------------------------';
$receiptLines[] = 'Total Item : ' . $totalQty;
$receiptLines[] = 'Total      : ' . struk_money($transaksi['total_jual'] ?? 0);
$receiptLines[] = 'Bayar      : ' . struk_money($transaksi['nominal_bayar'] ?? 0);
$receiptLines[] = 'Kembalian  : ' . struk_money($transaksi['kembalian'] ?? 0);
$receiptLines[] = '------------------------------';
$receiptLines[] = 'Terima kasih sudah berbelanja.';
$receiptLines[] = 'Barang yang sudah dibeli harap dicek kembali.';

$receiptText = implode("\n", $receiptLines);
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="struk-page">
    <section class="struk-hero">
        <div class="struk-hero-content">
            <span class="struk-eyebrow">
                <i class="ti ti-receipt"></i>
                Transaksi Berhasil
            </span>

            <h2>Struk Transaksi</h2>

            <p>
                Transaksi sudah tersimpan. Dari sini bisa cetak fisik, download PDF, atau salin teks struk buat non-fisik.
            </p>
        </div>

        <div class="struk-hero-actions">
            <a href="<?= app_e(app_url($backUrl)) ?>" class="struk-btn struk-btn-primary">
                <i class="ti ti-plus"></i>
                Transaksi Baru
            </a>

            <a href="<?= app_e(app_url($dashboardUrl)) ?>" class="struk-btn struk-btn-soft">
                <i class="ti ti-layout-dashboard"></i>
                Dashboard
            </a>
        </div>
    </section>

    <section class="struk-layout">
        <article class="struk-preview-card">
            <div class="struk-card-head">
                <div>
                    <span>Preview Struk</span>
                    <h3><?= struk_e($kodeTransaksi) ?></h3>
                </div>

                <span class="struk-status-badge">
                    <i class="ti ti-circle-check"></i>
                    Sukses
                </span>
            </div>

            <div class="struk-copy-status" data-copy-status hidden>
                <i class="ti ti-check"></i>
                <span>Struk berhasil disalin. Tempel ke WhatsApp/chat, lalu pura-pura semuanya mudah.</span>
            </div>

            <div class="struk-receipt-wrap">
                <?php require APP_PATH . '/views/shared/struk-content.php'; ?>
            </div>
        </article>

        <aside class="struk-side">
            <div class="struk-action-card">
                <span class="struk-action-icon">
                    <i class="ti ti-printer"></i>
                </span>

                <h4>Aksi Struk</h4>

                <p>
                    Pilih metode keluaran struk. Cetak fisik untuk printer, PDF untuk arsip, salin teks untuk non-fisik.
                </p>

                <div class="struk-action-list">
                    <button type="button" class="struk-action-btn action-print" data-print-receipt>
                        <i class="ti ti-printer"></i>
                        <span>
                            <strong>Cetak Fisik</strong>
                            <small>Print area struk 80mm</small>
                        </span>
                    </button>

                    <a href="<?= app_e(app_url($pdfUrl)) ?>" class="struk-action-btn action-pdf">
                        <i class="ti ti-file-download"></i>
                        <span>
                            <strong>Download PDF</strong>
                            <small>Struk thermal PDF</small>
                        </span>
                    </a>

                    <button type="button" class="struk-action-btn action-copy" data-copy-receipt>
                        <i class="ti ti-copy"></i>
                        <span>
                            <strong>Salin Struk</strong>
                            <small>Untuk WhatsApp/chat</small>
                        </span>
                    </button>
                </div>
            </div>

            <div class="struk-summary-card">
                <h4>Ringkasan</h4>

                <div class="struk-summary-row">
                    <span>Total Item</span>
                    <strong><?= struk_e((string) $totalQty) ?></strong>
                </div>

                <div class="struk-summary-row">
                    <span>Total Bayar</span>
                    <strong><?= struk_e(struk_money($transaksi['total_jual'] ?? 0)) ?></strong>
                </div>

                <div class="struk-summary-row">
                    <span>Nominal Bayar</span>
                    <strong><?= struk_e(struk_money($transaksi['nominal_bayar'] ?? 0)) ?></strong>
                </div>

                <div class="struk-summary-row is-green">
                    <span>Kembalian</span>
                    <strong><?= struk_e(struk_money($transaksi['kembalian'] ?? 0)) ?></strong>
                </div>
            </div>
        </aside>
    </section>
</div>

<script type="application/json" id="strukReceiptText">
    <?= json_encode($receiptText, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>

<script src="<?= app_e(app_asset('assets/js/struk.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/scripts.php'; ?>