<?php
$title = $title ?? 'Dashboard Kasir';
$activeMenu = $activeMenu ?? 'dashboard';

$pageCss = ['assets/css/kasir-dashboard.css'];

$__viewData = get_defined_vars();

$user = isset($__viewData['user']) && is_array($__viewData['user'])
    ? $__viewData['user']
    : (class_exists('Session') ? (Session::user() ?? []) : []);

$transaksiTerbaru = isset($__viewData['transaksiTerbaru']) && is_array($__viewData['transaksiTerbaru'])
    ? $__viewData['transaksiTerbaru']
    : [];

$totalTransaksiHariIni = (int) ($__viewData['totalTransaksiHariIni'] ?? 0);
$totalPenjualanHariIni = (float) ($__viewData['totalPenjualanHariIni'] ?? 0);
$totalItemHariIni = (int) ($__viewData['totalItemHariIni'] ?? 0);

$username = (string) ($user['username'] ?? 'Kasir');
$email = (string) ($user['email'] ?? '-');

if (!function_exists('kasir_dash_money')) {
    function kasir_dash_money(mixed $value): string
    {
        if (class_exists('Security') && method_exists('Security', 'rupiah')) {
            return Security::rupiah($value);
        }

        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('kasir_dash_date')) {
    function kasir_dash_date(mixed $value): string
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

if (!function_exists('kasir_dash_method_label')) {
    function kasir_dash_method_label(mixed $method): string
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

if (!function_exists('kasir_dash_method_icon')) {
    function kasir_dash_method_icon(mixed $method): string
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

$summaryCards = [
    [
        'class' => 'summary-green',
        'icon' => 'ti ti-receipt',
        'label' => 'Transaksi Hari Ini',
        'value' => (string) $totalTransaksiHariIni,
        'desc' => 'Transaksi yang kamu proses',
        'count' => $totalTransaksiHariIni,
        'format' => 'thousand',
        'prefix' => '',
    ],
    [
        'class' => 'summary-blue',
        'icon' => 'ti ti-cash',
        'label' => 'Penjualan Hari Ini',
        'value' => kasir_dash_money($totalPenjualanHariIni),
        'desc' => 'Total omzet transaksi',
        'count' => (int) $totalPenjualanHariIni,
        'format' => 'rupiah',
        'prefix' => 'Rp ',
    ],
    [
        'class' => 'summary-orange',
        'icon' => 'ti ti-packages',
        'label' => 'Item Terjual',
        'value' => (string) $totalItemHariIni,
        'desc' => 'Total item keluar',
        'count' => $totalItemHariIni,
        'format' => 'thousand',
        'prefix' => '',
    ],
];
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="kasir-dashboard-page">
    <section class="kasir-hero" data-aos="fade-down" data-aos-duration="700">
        <div class="kasir-hero-content">
            <span class="kasir-eyebrow">
                <i class="ti ti-cash-register"></i>
                Dashboard Kasir
            </span>

            <h2>Halo, <?= app_e($username) ?></h2>

        </div>

        <div class="kasir-hero-actions">
            <a href="<?= app_e(app_url('/kasir/transaksi')) ?>" class="kasir-btn kasir-btn-primary">
                <i class="ti ti-shopping-cart-plus"></i>
                Mulai Transaksi
            </a>

            <a href="<?= app_e(app_url('/kasir/profil')) ?>" class="kasir-btn kasir-btn-soft">
                <i class="ti ti-user-circle"></i>
                Profil Saya
            </a>
        </div>
    </section>

    <section class="kasir-summary summary-count-3">
        <?php foreach ($summaryCards as $idx => $card): ?>
            <article class="kasir-summary-card <?= app_e($card['class']) ?>" data-aos="zoom-in" data-aos-delay="<?= app_e((string) (80 + $idx * 100)) ?>">
                <span class="kasir-summary-icon">
                    <i class="<?= app_e($card['icon']) ?>"></i>
                </span>

                <div>
                    <small><?= app_e($card['label']) ?></small>

                    <strong
                        data-counter="<?= app_e((string) ($card['count'] ?? 0)) ?>"
                        data-counter-format="<?= app_e($card['format']) ?>"
                        <?php if ($card['prefix'] !== ''): ?>
                            data-counter-prefix="<?= app_e($card['prefix']) ?>"
                        <?php endif; ?>
                    ><?= app_e($card['prefix']) ?>0</strong>

                    <p><?= app_e($card['desc']) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="kasir-layout">
        <article class="kasir-panel kasir-transaction-panel" data-aos="fade-right" data-aos-delay="200">
            <div class="kasir-panel-head">
                <div>
                    <span>Riwayat</span>
                    <h3>Transaksi Terbaru Saya</h3>
                </div>

                <a href="<?= app_e(app_url('/kasir/transaksi')) ?>" class="kasir-mini-link">
                    <i class="ti ti-plus"></i>
                    Transaksi Baru
                </a>
            </div>

            <?php if (empty($transaksiTerbaru)): ?>
                <div class="kasir-empty">
                    <span>
                        <i class="ti ti-receipt-off"></i>
                    </span>

                    <h4>Belum ada transaksi</h4>

                    <p>
                        Kamu belum memproses transaksi. Mulai dari tombol transaksi, bukan dari menatap layar sambil berharap omzet muncul.
                    </p>

                    <a href="<?= app_e(app_url('/kasir/transaksi')) ?>" class="kasir-btn kasir-btn-form">
                        <i class="ti ti-shopping-cart-plus"></i>
                        Mulai Transaksi
                    </a>
                </div>
            <?php else: ?>
                <div class="kasir-table-wrap">
                    <table class="kasir-table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Tanggal</th>
                                <th>Metode</th>
                                <th>Total</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($transaksiTerbaru as $transaksi): ?>
                                <?php
                                $id = (int) ($transaksi['id'] ?? 0);
                                $kode = (string) ($transaksi['kode_transaksi'] ?? '-');
                                $tanggal = (string) ($transaksi['tanggal'] ?? '');
                                $method = strtolower((string) ($transaksi['metode_bayar'] ?? '-'));
                                $total = (float) ($transaksi['total_jual'] ?? 0);
                                ?>

                                <tr>
                                    <td>
                                        <div class="kasir-code">
                                            <span>
                                                <i class="ti ti-receipt"></i>
                                            </span>

                                            <div>
                                                <strong><?= app_e($kode) ?></strong>
                                                <small>ID: <?= app_e((string) $id) ?></small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="kasir-date">
                                            <i class="ti ti-calendar"></i>
                                            <?= app_e(kasir_dash_date($tanggal)) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="kasir-method method-<?= app_e($method) ?>">
                                            <i class="<?= app_e(kasir_dash_method_icon($method)) ?>"></i>
                                            <?= app_e(kasir_dash_method_label($method)) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <strong class="kasir-money">
                                            <?= app_e(kasir_dash_money($total)) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <div class="kasir-actions">
                                            <a
                                                href="<?= app_e(app_url('/kasir/transaksi/struk/' . $id)) ?>"
                                                class="kasir-action-btn action-struk"
                                                title="Lihat struk"
                                                aria-label="Lihat struk"
                                            >
                                                <i class="ti ti-receipt"></i>
                                            </a>

                                            <a
                                                href="<?= app_e(app_url('/kasir/transaksi/pdf/' . $id)) ?>"
                                                class="kasir-action-btn action-pdf"
                                                title="Download PDF"
                                                aria-label="Download PDF"
                                            >
                                                <i class="ti ti-file-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </article>

        <aside class="kasir-side" data-aos="fade-left" data-aos-delay="200">
            <div class="kasir-profile-card">
                <span class="kasir-profile-icon">
                    <i class="ti ti-user-circle"></i>
                </span>

                <h4><?= app_e($username) ?></h4>
                <p><?= app_e($email) ?></p>

                <div class="kasir-profile-list">
                    <a href="<?= app_e(app_url('/kasir/transaksi')) ?>">
                        <i class="ti ti-shopping-cart-plus"></i>
                        <span>
                            <strong>POS Transaksi</strong>
                            <small>Buka halaman kasir</small>
                        </span>
                    </a>

                    <a href="<?= app_e(app_url('/kasir/profil')) ?>">
                        <i class="ti ti-user-cog"></i>
                        <span>
                            <strong>Profil Saya</strong>
                            <small>Kelola akun kasir</small>
                        </span>
                    </a>
                </div>
            </div>

            <div class="kasir-tips-card">
                <span>
                    <i class="ti ti-bulb"></i>
                </span>

                <h4>Checklist Kasir</h4>

                <ul>
                    <li>
                        <i class="ti ti-check"></i>
                        Pastikan barang dan qty sudah benar sebelum simpan.
                    </li>
                    <li>
                        <i class="ti ti-check"></i>
                        Untuk cash, cek nominal bayar dan kembalian.
                    </li>
                    <li>
                        <i class="ti ti-check"></i>
                        Cetak atau salin struk setelah transaksi selesai.
                    </li>
                </ul>
            </div>
        </aside>
    </section>
</div>

<script src="<?= app_e(app_asset_versioned('assets/js/kasir-dashboard.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>