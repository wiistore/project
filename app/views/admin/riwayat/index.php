<?php
$title = $title ?? 'Riwayat Transaksi';
$activeMenu = $activeMenu ?? 'riwayat';

$pageCss = ['assets/css/riwayat.css'];

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

if (!function_exists('riwayat_status_badge')) {
    function riwayat_status_badge(string $status): string
    {
        return match ($status) {
            'selesai' => '<span class="riwayat-status-badge status-selesai"><i class="ti ti-circle-check"></i> Selesai</span>',
            'diubah' => '<span class="riwayat-status-badge status-diubah"><i class="ti ti-pencil"></i> Diubah</span>',
            'dibatalkan' => '<span class="riwayat-status-badge status-dibatalkan"><i class="ti ti-x"></i> Dibatalkan</span>',
            default => '<span class="riwayat-status-badge status-selesai"><i class="ti ti-circle-check"></i> Selesai</span>',
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
    $statusCount = $transaksi['status'] ?? 'selesai';
    if ($statusCount === 'dibatalkan') {
        continue;
    }

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
        'desc' => 'Transaksi valid',
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

    <section class="riwayat-hero" data-aos="fade-down" data-aos-duration="700">
        <div class="riwayat-hero-content">
            <span class="riwayat-eyebrow">
                <i class="ti ti-history"></i>
                Riwayat Penjualan
            </span>

            <h2>Riwayat Transaksi</h2>
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

    <section class="riwayat-summary <?= app_e($summaryClass) ?>" data-aos="fade-up" data-aos-delay="140">
        <?php foreach ($summaryCards as $idx => $card): ?>
            <article class="riwayat-summary-card <?= app_e($card['class']) ?>" data-aos="zoom-in" data-aos-delay="<?= app_e((string) (80 + ((int) ($idx ?? 0)) * 100)) ?>">
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

    <section class="riwayat-panel" data-aos="fade-up" data-aos-delay="200">
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
                    Belum ada riwayat sesuai filter ini.
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
                            <th>Laba</th>
                            <th>Status</th>
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
                            $statusRow = (string) ($transaksi['status'] ?? 'selesai');

                            $totalJualRow = (float) ($transaksi['total_jual'] ?? 0);
                            $totalLabaRow = (float) ($transaksi['total_laba'] ?? 0);

                            $isDibatalkan = $statusRow === 'dibatalkan';

                            $searchText = strtolower(implode(' ', [
                                $kode,
                                $tanggal,
                                $kasir,
                                $method,
                                riwayat_method_label($method),
                                $statusRow,
                                $totalJualRow,
                                $totalLabaRow,
                            ]));
                            ?>

                            <tr
                                data-riwayat-row
                                data-search="<?= app_e($searchText) ?>"
                                data-method="<?= app_e($method) ?>"
                                class="<?= $isDibatalkan ? 'row-dibatalkan' : '' ?>"
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
                                    <strong class="riwayat-money <?= $isDibatalkan ? 'is-muted' : '' ?>">
                                        <?= app_e(riwayat_money($totalJualRow)) ?>
                                    </strong>
                                </td>

                                <td>
                                    <strong class="riwayat-money <?= $isDibatalkan ? 'is-muted' : ($totalLabaRow >= 0 ? 'is-profit' : 'is-loss') ?>">
                                        <?= app_e(riwayat_money($totalLabaRow)) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= riwayat_status_badge($statusRow) ?>
                                </td>

                                <td>
                                    <div class="riwayat-actions">
                                        <a
                                            href="<?= app_e(app_url('/admin/riwayat-transaksi/detail/' . $id)) ?>"
                                            class="riwayat-action-btn action-detail"
                                            title="Lihat detail"
                                        >
                                            <i class="ti ti-eye"></i>
                                        </a>

                                        <?php if (!$isDibatalkan): ?>
                                            <a
                                                href="<?= app_e(app_url('/admin/riwayat-transaksi/edit/' . $id)) ?>"
                                                class="riwayat-action-btn action-edit"
                                                title="Edit transaksi"
                                            >
                                                <i class="ti ti-pencil"></i>
                                            </a>

                                            <button
                                                type="button"
                                                class="riwayat-action-btn action-cancel"
                                                title="Batalkan transaksi"
                                                data-cancel-btn
                                                data-cancel-id="<?= app_e((string) $id) ?>"
                                                data-cancel-kode="<?= app_e($kode) ?>"
                                            >
                                                <i class="ti ti-x"></i>
                                            </button>
                                        <?php endif; ?>

                                        <a
                                            href="<?= app_e(app_url('/admin/transaksi/struk/' . $id)) ?>"
                                            class="riwayat-action-btn action-struk"
                                            title="Lihat struk"
                                        >
                                            <i class="ti ti-receipt"></i>
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
                        Keyword atau filter terlalu spesifik.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- Modal Batalkan Transaksi -->
<style>
.riwayat-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    backdrop-filter: blur(2px);
}
.riwayat-modal-overlay[hidden] {
    display: none;
}
.riwayat-modal {
    background: #ffffff;
    border-radius: 16px;
    width: 100%;
    max-width: 480px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.25s ease;
}
@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(-20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.riwayat-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #f1f5f9;
}
.riwayat-modal-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}
.riwayat-modal-header h3 i {
    color: #f59e0b;
    font-size: 1.3rem;
}
.riwayat-modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: #f1f5f9;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    transition: background 0.15s, color 0.15s;
}
.riwayat-modal-close:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.riwayat-modal-body {
    padding: 20px 24px;
}
.riwayat-modal-body p {
    margin: 0 0 12px;
    color: #334155;
    font-size: 0.95rem;
    line-height: 1.5;
}
.riwayat-modal-warning {
    background: #fef3c7;
    border: 1px solid #fcd34d;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 0.85rem;
    color: #92400e;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 16px !important;
}
.riwayat-modal-warning i {
    color: #d97706;
    font-size: 1.1rem;
    margin-top: 1px;
}
.riwayat-modal-field {
    margin-top: 4px;
}
.riwayat-modal-field label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 6px;
}
.riwayat-modal-field label span {
    color: #ef4444;
}
.riwayat-modal-field textarea {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 0.9rem;
    resize: vertical;
    transition: border-color 0.15s;
    font-family: inherit;
}
.riwayat-modal-field textarea:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}
.riwayat-modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px 20px;
    border-top: 1px solid #f1f5f9;
}
.riwayat-modal-footer .riwayat-btn-danger {
    background: #ef4444;
    color: #ffffff;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.15s;
}
.riwayat-modal-footer .riwayat-btn-danger:hover {
    background: #dc2626;
}
.riwayat-modal-footer .riwayat-btn-ghost {
    background: none;
    border: 1px solid #e2e8f0;
    color: #64748b;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}
.riwayat-modal-footer .riwayat-btn-ghost:hover {
    background: #f8fafc;
    color: #334155;
}
</style>

<div class="riwayat-modal-overlay" id="cancelModal" hidden>
    <div class="riwayat-modal">
        <div class="riwayat-modal-header">
            <h3><i class="ti ti-alert-triangle"></i> Batalkan Transaksi</h3>
            <button type="button" class="riwayat-modal-close" data-cancel-modal-close>
                <i class="ti ti-x"></i>
            </button>
        </div>

        <form id="cancelForm" method="POST" action="">
            <div class="riwayat-modal-body">
                <p>Anda yakin ingin membatalkan transaksi <strong id="cancelKode"></strong>?</p>
                <p class="riwayat-modal-warning">
                    <i class="ti ti-info-circle"></i>
                    Stok barang akan dikembalikan. Transaksi tidak akan masuk laporan.
                </p>

                <div class="riwayat-modal-field">
                    <label for="alasan_batal">
                        Alasan Pembatalan <span>*</span>
                    </label>
                    <textarea
                        id="alasan_batal"
                        name="alasan_batal"
                        rows="3"
                        placeholder="Tulis alasan pembatalan (wajib)"
                        required
                    ></textarea>
                </div>
            </div>

            <div class="riwayat-modal-footer">
                <button type="submit" class="riwayat-btn riwayat-btn-danger">
                    <i class="ti ti-x"></i>
                    Batalkan Transaksi
                </button>

                <button type="button" class="riwayat-btn riwayat-btn-ghost" data-cancel-modal-close>
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cancel modal logic
    const modal = document.getElementById('cancelModal');
    const form = document.getElementById('cancelForm');
    const kodeEl = document.getElementById('cancelKode');
    const closeBtns = document.querySelectorAll('[data-cancel-modal-close]');
    const cancelBtns = document.querySelectorAll('[data-cancel-btn]');

    cancelBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-cancel-id');
            const kode = this.getAttribute('data-cancel-kode');

            form.action = '<?= app_e(app_url('/admin/riwayat-transaksi/cancel/')) ?>' + id;
            kodeEl.textContent = kode;
            modal.hidden = false;
        });
    });

    closeBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            modal.hidden = true;
            form.action = '';
            kodeEl.textContent = '';
            document.getElementById('alasan_batal').value = '';
        });
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.hidden = true;
        }
    });
});
</script>

<script src="<?= app_e(app_asset_versioned('assets/js/riwayat.js')) ?>"></script>

<?php
$pagination = $pagination ?? null;
if ($pagination) {
    require APP_PATH . '/views/components/pagination.php';
}
?>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>
