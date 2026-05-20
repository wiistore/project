<?php
$__view = get_defined_vars();

$title = isset($__view['title']) ? (string) $__view['title'] : 'Transaksi POS';
$activeMenu = 'transaksi';
$pageCss = ['assets/css/pos.css'];

$user = isset($__view['user']) && is_array($__view['user'])
    ? $__view['user']
    : ($_SESSION['user'] ?? []);

$role = strtolower((string) ($user['role'] ?? ($_SESSION['role'] ?? (class_exists('Session') ? Session::role() : 'kasir'))));
$isAdmin = $role === 'admin';

$barangs = isset($__view['barangs']) && is_array($__view['barangs'])
    ? $__view['barangs']
    : [];

$metodePembayaran = isset($__view['metodePembayaran']) && is_array($__view['metodePembayaran'])
    ? $__view['metodePembayaran']
    : [
        'cash' => 'Cash',
        'qris' => 'QRIS',
        'transfer' => 'Transfer',
        'ewallet' => 'E-Wallet',
    ];

$flash = isset($__view['flash']) && is_array($__view['flash'])
    ? $__view['flash']
    : [];

$success = $flash['success'] ?? null;
$error = $flash['error'] ?? null;

$storeUrl = '/transaksi/store';
$historyUrl = $isAdmin ? '/admin/riwayat-transaksi' : '/kasir/dashboard';
$dashboardUrl = $isAdmin ? '/admin/dashboard' : '/kasir/dashboard';
$barangUrl = $isAdmin ? '/admin/barang' : null;

if (!function_exists('pos_money')) {
    function pos_money(mixed $value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('pos_stock_meta')) {
    function pos_stock_meta(int $stok, int $minimum): array
    {
        if ($stok <= 0) {
            return [
                'label' => 'Habis',
                'class' => 'stock-empty',
                'icon' => 'ti ti-alert-circle',
            ];
        }

        if ($stok <= $minimum) {
            return [
                'label' => 'Menipis',
                'class' => 'stock-low',
                'icon' => 'ti ti-alert-triangle',
            ];
        }

        return [
            'label' => 'Aman',
            'class' => 'stock-safe',
            'icon' => 'ti ti-circle-check',
        ];
    }
}

$kategoriMap = [];

foreach ($barangs as $barang) {
    $kategoriId = (string) ($barang['id_kategori'] ?? '');
    $kategoriNama = (string) ($barang['nama_kategori'] ?? 'Lainnya');

    if ($kategoriId !== '') {
        $kategoriMap[$kategoriId] = $kategoriNama;
    }
}

$totalBarangAktif = count($barangs);

$totalStok = array_sum(array_map(static function ($item): int {
    return (int) ($item['stok'] ?? 0);
}, $barangs));

$totalBarangTersedia = count(array_filter($barangs, static function ($item): bool {
    return (int) ($item['stok'] ?? 0) > 0;
}));

$productsForJs = array_map(static function ($barang): array {
    return [
        'id' => (int) ($barang['id'] ?? 0),
        'kode_barang' => (string) ($barang['kode_barang'] ?? ''),
        'barcode' => (string) ($barang['barcode'] ?? ''),
        'nama' => (string) ($barang['nama'] ?? ''),
        'kategori_id' => (string) ($barang['id_kategori'] ?? ''),
        'kategori' => (string) ($barang['nama_kategori'] ?? ''),
        'satuan' => (string) ($barang['satuan'] ?? ''),
        'harga_jual' => (float) ($barang['harga_jual'] ?? 0),
        'stok' => (int) ($barang['stok'] ?? 0),
        'stok_minimum' => (int) ($barang['stok_minimum'] ?? 0),
    ];
}, $barangs);

require APP_PATH . '/views/layouts/header.php';
require APP_PATH . '/views/layouts/sidebar.php';
require APP_PATH . '/views/layouts/navbar.php';
?>

<div class="pos-page" data-pos-page>
    <?php if ($success): ?>
        <div class="pos-alert pos-alert-success">
            <i class="ti ti-circle-check"></i>
            <span><?= app_e($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="pos-alert pos-alert-error">
            <i class="ti ti-alert-triangle"></i>
            <span><?= app_e($error) ?></span>
        </div>
    <?php endif; ?>

    <section class="pos-hero" data-aos="fade-down" data-aos-duration="700">
        <div class="pos-hero-content">
            <span class="pos-eyebrow">
                <i class="ti ti-shopping-cart"></i>
                Point of Sale
            </span>

            <h2>Transaksi Penjualan</h2>

        </div>

        <div class="pos-hero-actions">
            <a href="<?= app_e(app_url($dashboardUrl)) ?>" class="pos-btn pos-btn-soft">
                <i class="ti ti-arrow-left"></i>
                Dashboard
            </a>

            <?php if ($isAdmin): ?>
                <a href="<?= app_e(app_url($historyUrl)) ?>" class="pos-btn pos-btn-soft">
                    <i class="ti ti-history"></i>
                    Riwayat
                </a>

                <a href="<?= app_e(app_url($barangUrl)) ?>" class="pos-btn pos-btn-primary">
                    <i class="ti ti-package"></i>
                    Barang
                </a>
            <?php endif; ?>
        </div>
    </section>

    <section class="pos-summary summary-count-3">
        <article class="pos-summary-card summary-green" data-aos="zoom-in" data-aos-delay="80">
            <span class="pos-summary-icon">
                <i class="ti ti-package"></i>
            </span>

            <div>
                <small>Barang Aktif</small>
                <strong data-counter="<?= app_e((string) $totalBarangAktif) ?>" data-counter-format="thousand">0</strong>
                <p>Semua barang aktif</p>
            </div>
        </article>

        <article class="pos-summary-card summary-blue" data-aos="zoom-in" data-aos-delay="180">
            <span class="pos-summary-icon">
                <i class="ti ti-packages"></i>
            </span>

            <div>
                <small>Total Stok</small>
                <strong data-counter="<?= app_e((string) $totalStok) ?>" data-counter-format="thousand">0</strong>
                <p>Akumulasi stok barang</p>
            </div>
        </article>

        <article class="pos-summary-card summary-orange" data-aos="zoom-in" data-aos-delay="280">
            <span class="pos-summary-icon">
                <i class="ti ti-shopping-bag-check"></i>
            </span>

            <div>
                <small>Siap Dijual</small>
                <strong data-counter="<?= app_e((string) $totalBarangTersedia) ?>" data-counter-format="thousand">0</strong>
                <p>Stok lebih dari nol</p>
            </div>
        </article>
    </section>

    <section class="pos-layout">
        <article class="pos-products-panel" data-aos="fade-right" data-aos-delay="200">
            <div class="pos-panel-head">
                <div>
                    <span>Produk</span>
                    <h3>Pilih Barang</h3>
                </div>

                <button type="button" class="pos-scan-btn" data-pos-focus-search>
                    <i class="ti ti-barcode"></i>
                    Scan / F2
                </button>
            </div>

            <div class="pos-toolbar">
                <label class="pos-search">
                    <i class="ti ti-search"></i>
                    <input
                        type="search"
                        placeholder="Cari nama barang, kode, atau scan barcode..."
                        autocomplete="off"
                        data-pos-search
                    >
                    <kbd>F2</kbd>
                </label>

                <div class="pos-category-tabs" data-pos-categories>
                    <button type="button" class="is-active" data-category="all">
                        Semua
                    </button>

                    <?php foreach ($kategoriMap as $kategoriId => $kategoriNama): ?>
                        <button type="button" data-category="<?= app_e($kategoriId) ?>">
                            <?= app_e($kategoriNama) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (empty($barangs)): ?>
                <div class="pos-empty">
                    <span>
                        <i class="ti ti-package-off"></i>
                    </span>

                    <h4>Belum ada barang aktif</h4>
                    <p>Tambahkan barang dan restock dulu. POS tanpa barang itu cuma kalkulator mahal.</p>

                    <?php if ($isAdmin): ?>
                        <a href="<?= app_e(app_url('/admin/barang/create')) ?>" class="pos-btn pos-btn-form">
                            <i class="ti ti-plus"></i>
                            Tambah Barang
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="pos-product-grid" data-product-list>
                    <?php foreach ($barangs as $barang): ?>
                        <?php
                        $id = (int) ($barang['id'] ?? 0);
                        $kode = (string) ($barang['kode_barang'] ?? '-');
                        $barcode = (string) ($barang['barcode'] ?? '');
                        $nama = (string) ($barang['nama'] ?? '-');
                        $kategoriId = (string) ($barang['id_kategori'] ?? '');
                        $kategoriNama = (string) ($barang['nama_kategori'] ?? 'Lainnya');
                        $satuan = (string) ($barang['satuan'] ?? 'pcs');
                        $harga = (float) ($barang['harga_jual'] ?? 0);
                        $stok = (int) ($barang['stok'] ?? 0);
                        $stokMinimum = (int) ($barang['stok_minimum'] ?? 0);
                        $stockMeta = pos_stock_meta($stok, $stokMinimum);
                        $isOut = $stok <= 0;

                        $searchText = strtolower(implode(' ', [
                            $kode,
                            $barcode,
                            $nama,
                            $kategoriNama,
                            $satuan,
                        ]));
                        ?>

                        <button
                            type="button"
                            class="pos-product-card <?= $isOut ? 'is-disabled' : '' ?>"
                            data-product-card
                            data-product-id="<?= app_e((string) $id) ?>"
                            data-category="<?= app_e($kategoriId) ?>"
                            data-search="<?= app_e($searchText) ?>"
                            <?= $isOut ? 'disabled' : '' ?>
                        >
                            <span class="pos-product-icon">
                                <i class="ti ti-package"></i>
                            </span>

                            <span class="pos-product-info">
                                <strong><?= app_e($nama) ?></strong>
                                <small>
                                    <?= app_e($kode) ?>
                                    <?= $barcode !== '' ? ' • ' . app_e($barcode) : '' ?>
                                </small>
                            </span>

                            <span class="pos-product-bottom">
                                <span class="pos-product-price"><?= app_e(pos_money($harga)) ?></span>

                                <span class="pos-product-stock <?= app_e($stockMeta['class']) ?>">
                                    <i class="<?= app_e($stockMeta['icon']) ?>"></i>
                                    <?= app_e($stockMeta['label']) ?> · <?= app_e((string) $stok) ?> <?= app_e($satuan) ?>
                                </span>
                            </span>

                            <?php if ($isOut): ?>
                                <span class="pos-product-disabled-label">Habis</span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="pos-filter-empty" data-product-empty hidden>
                    <span>
                        <i class="ti ti-search-off"></i>
                    </span>

                    <h4>Barang tidak ketemu</h4>
                    <p>Coba cari nama, kode, atau barcode yang bener. Sistem ini bukan paranormal.</p>
                </div>
            <?php endif; ?>
        </article>

        <aside class="pos-cart-panel" data-aos="fade-left" data-aos-delay="250">
            <form action="<?= app_e(app_url($storeUrl)) ?>" method="POST" class="pos-form" data-pos-form>
                <input type="hidden" name="cart_json" value="" data-cart-json>

                <div class="pos-cart-head">
                    <div>
                        <span>Keranjang</span>
                        <h3>Transaksi Saat Ini</h3>
                        <p><strong data-cart-count>0</strong> item dipilih</p>
                    </div>

                    <button type="button" class="pos-clear-btn" data-cart-clear>
                        <i class="ti ti-trash"></i>
                        Kosongkan
                    </button>
                </div>

                <div class="pos-cart-items" data-cart-items>
                    <div class="pos-cart-empty" data-cart-empty>
                        <span>
                            <i class="ti ti-shopping-cart-off"></i>
                        </span>

                        <h4>Keranjang kosong</h4>
                        <p>Pilih barang dari daftar produk.</p>
                    </div>
                </div>

                <div class="pos-payment-box">
                    <div class="pos-total-box">
                        <span>Total Bayar</span>
                        <strong data-total-pay>Rp 0</strong>
                    </div>

                    <div class="pos-mini-summary">
                        <div>
                            <span>Total Item</span>
                            <strong data-total-item>0</strong>
                        </div>

                        <div>
                            <span>Kembalian</span>
                            <strong data-change>Rp 0</strong>
                        </div>
                    </div>

                    <div class="pos-payment-section">
                        <label>Metode Pembayaran</label>

                        <div class="pos-payment-methods">
                            <?php foreach ($metodePembayaran as $key => $label): ?>
                                <?php
                                $methodKey = (string) $key;
                                $methodLabel = (string) $label;
                                $icon = match ($methodKey) {
                                    'cash' => 'ti ti-cash',
                                    'qris' => 'ti ti-qrcode',
                                    'transfer' => 'ti ti-building-bank',
                                    'ewallet' => 'ti ti-wallet',
                                    default => 'ti ti-credit-card',
                                };
                                ?>

                                <label class="pos-payment-method <?= $methodKey === 'cash' ? 'is-active' : '' ?>">
                                    <input
                                        type="radio"
                                        name="metode_pembayaran"
                                        value="<?= app_e($methodKey) ?>"
                                        <?= $methodKey === 'cash' ? 'checked' : '' ?>
                                        data-payment-method
                                    >

                                    <span>
                                        <i class="<?= app_e($icon) ?>"></i>
                                        <?= app_e($methodLabel) ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="pos-payment-section" data-cash-box>
                        <label for="nominalBayar">Nominal Bayar</label>

                        <div class="pos-money-input">
                            <span>Rp</span>
                            <input
                                type="number"
                                id="nominalBayar"
                                name="nominal_bayar"
                                value=""
                                placeholder="0"
                                min="0"
                                step="1"
                                data-cash-input
                            >
                        </div>

                        <small>Wajib diisi untuk pembayaran cash.</small>
                    </div>

                    <div class="pos-warning" data-payment-warning hidden>
                        <i class="ti ti-alert-triangle"></i>
                        <span>Nominal bayar kurang dari total transaksi.</span>
                    </div>

                    <button type="submit" class="pos-pay-btn" data-pay-button disabled>
                        <i class="ti ti-device-floppy"></i>
                        Simpan Transaksi
                        <span>F9</span>
                    </button>

                    <p class="pos-payment-note">
                        Harga dan stok final tetap dihitung ulang oleh backend.
                    </p>
                </div>
            </form>
        </aside>
    </section>
</div>

<script type="application/json" id="posProductData">
    <?= json_encode($productsForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>

<script src="<?= app_e(app_asset_versioned('assets/js/pos.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/scripts.php'; ?>