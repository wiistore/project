<?php
$title = $title ?? 'Form Barang';
$activeMenu = $activeMenu ?? 'barang';

$pageCss = ['assets/css/barang.css'];
$useBarcode = true;

$formAction = $formAction ?? '/admin/barang/store';
$formMode = $formMode ?? 'create';
$barang = $barang ?? null;
$kategoris = $kategoris ?? ($kategori ?? []);
$errors = $errors ?? [];
$old = $old ?? [];

$kodeBarang = $old['kode_barang'] ?? ($barang['kode_barang'] ?? '');
$barcode = $old['barcode'] ?? ($barang['barcode'] ?? '');
$nama = $old['nama'] ?? ($barang['nama'] ?? '');
$idKategori = $old['id_kategori'] ?? ($barang['id_kategori'] ?? '');
$satuan = $old['satuan'] ?? ($barang['satuan'] ?? 'pcs');
$hargaJual = $old['harga_jual'] ?? ($barang['harga_jual'] ?? '');
$stokMinimum = $old['stok_minimum'] ?? ($barang['stok_minimum'] ?? 5);
$status = $old['status'] ?? ($barang['status'] ?? 'aktif');

$isEdit = $formMode === 'edit';

if (!function_exists('barang_field_error')) {
    function barang_field_error(array $errors, string $field): string
    {
        return isset($errors[$field]) ? ' is-invalid' : '';
    }
}
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="barang-page">
    <section class="barang-hero barang-form-hero" data-aos="fade-down" data-aos-duration="700">
        <div class="barang-hero-content">
            <span class="barang-eyebrow">
                <i class="<?= $isEdit ? 'ti ti-edit' : 'ti ti-package-plus' ?>"></i>
                <?= $isEdit ? 'Edit Master Barang' : 'Tambah Master Barang' ?>
            </span>

            <h2><?= app_e($title) ?></h2>

            <p>
                <?= $isEdit
                    ? 'Perbarui data master barang. Stok tetap dikunci supaya alur restock dan transaksi tidak kacau.'
                    : 'Tambahkan barang baru ke master data. Barcode boleh dikosongkan kalau mau digenerate otomatis oleh sistem.'
                ?>
            </p>
        </div>

        <div class="barang-hero-actions">
            <a href="<?= app_e(app_url('/admin/barang')) ?>" class="barang-btn barang-btn-soft">
                <i class="ti ti-arrow-left"></i>
                Kembali
            </a>
        </div>
    </section>

    <section class="barang-form-layout" data-aos="fade-up" data-aos-delay="150">
        <article class="barang-form-card">
            <div class="barang-form-head">
                <div>
                    <span>Form Barang</span>
                    <h3><?= $isEdit ? 'Edit Data Barang' : 'Tambah Barang Baru' ?></h3>
                </div>

                <span class="barang-form-badge">
                    <i class="<?= $isEdit ? 'ti ti-pencil' : 'ti ti-plus' ?>"></i>
                    <?= $isEdit ? 'Mode Edit' : 'Mode Tambah' ?>
                </span>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="barang-alert barang-alert-error">
                    <i class="ti ti-alert-triangle"></i>
                    <!-- <span>Masih ada input yang perlu diberesin. Form bukan tempat buat tebak-tebakan.</span> -->
                </div>
            <?php endif; ?>

            <form action="<?= app_e(app_url($formAction)) ?>" method="POST" class="barang-form" data-barang-form>
                <div class="barang-form-grid">
                    <div class="barang-field">
                        <label for="kode_barang">
                            Kode Barang
                            <span>*</span>
                        </label>

                        <div class="barang-input-wrap">
                            <i class="ti ti-hash"></i>
                            <input
                                type="text"
                                id="kode_barang"
                                name="kode_barang"
                                value="<?= app_e($kodeBarang) ?>"
                                placeholder="Contoh: BRG001"
                                class="<?= app_e(barang_field_error($errors, 'kode_barang')) ?>"
                                autocomplete="off"
                                autofocus
                            >
                        </div>

                        <?php if (isset($errors['kode_barang'])): ?>
                            <small class="barang-field-error"><?= app_e($errors['kode_barang']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="barang-field">
                        <label for="barcode">
                            Barcode
                            <span>*</span>
                        </label>

                        <div class="barang-input-wrap barang-input-wrap-with-action">
                            <i class="ti ti-barcode"></i>
                            <input
                                type="text"
                                id="barcode"
                                name="barcode"
                                value="<?= app_e($barcode) ?>"
                                placeholder="Scan / ketik / klik Generate"
                                class="<?= app_e(barang_field_error($errors, 'barcode')) ?>"
                                autocomplete="off"
                                data-barang-barcode-input
                            >
                            <button
                                type="button"
                                class="barang-btn barang-btn-soft barang-btn-generate"
                                data-barang-generate-barcode
                                data-generate-url="<?= app_e(app_url('/admin/barang/generate-barcode')) ?>"
                                title="Generate barcode otomatis"
                            >
                                <i class="ti ti-wand"></i>
                                <span>Generate</span>
                            </button>
                        </div>

                        <small class="barang-field-hint">
                            Bisa diisi manual (untuk barang pabrikan dengan barcode asli), discan langsung dari kemasan, atau klik <strong>Generate</strong> untuk barcode internal otomatis.
                        </small>

                        <?php if (!empty($barcode)): ?>
                            <div class="barang-barcode-preview" data-barang-barcode-preview>
                                <svg id="barangBarcodePreview" class="barang-barcode-svg" data-barcode-value="<?= app_e($barcode) ?>"></svg>
                            </div>
                        <?php else: ?>
                            <div class="barang-barcode-preview is-empty" data-barang-barcode-preview>
                                <svg id="barangBarcodePreview" class="barang-barcode-svg" data-barcode-value=""></svg>
                                <span class="barang-barcode-empty-text">Preview barcode akan muncul setelah barcode diisi.</span>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($errors['barcode'])): ?>
                            <small class="barang-field-error"><?= app_e($errors['barcode']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="barang-field field-full">
                        <label for="nama">
                            Nama Barang
                            <span>*</span>
                        </label>

                        <div class="barang-input-wrap">
                            <i class="ti ti-package"></i>
                            <input
                                type="text"
                                id="nama"
                                name="nama"
                                value="<?= app_e($nama) ?>"
                                placeholder="Contoh: Pensil 2B"
                                class="<?= app_e(barang_field_error($errors, 'nama')) ?>"
                                autocomplete="off"
                            >
                        </div>

                        <?php if (isset($errors['nama'])): ?>
                            <small class="barang-field-error"><?= app_e($errors['nama']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="barang-field">
                        <label for="id_kategori">
                            Kategori
                            <span>*</span>
                        </label>

                        <div class="barang-input-wrap">
                            <i class="ti ti-folder"></i>
                            <select
                                id="id_kategori"
                                name="id_kategori"
                                class="<?= app_e(barang_field_error($errors, 'id_kategori')) ?>"
                            >
                                <option value="">Pilih kategori</option>

                                <?php foreach ($kategoris as $kategoriItem): ?>
                                    <option
                                        value="<?= app_e($kategoriItem['id'] ?? '') ?>"
                                        <?= (string) $idKategori === (string) ($kategoriItem['id'] ?? '') ? 'selected' : '' ?>
                                    >
                                        <?= app_e($kategoriItem['nama'] ?? '-') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if (isset($errors['id_kategori'])): ?>
                            <small class="barang-field-error"><?= app_e($errors['id_kategori']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="barang-field">
                        <label for="satuan">
                            Satuan
                            <span>*</span>
                        </label>

                        <div class="barang-input-wrap">
                            <i class="ti ti-ruler"></i>
                            <input
                                type="text"
                                id="satuan"
                                name="satuan"
                                value="<?= app_e($satuan) ?>"
                                placeholder="pcs, box, botol"
                                class="<?= app_e(barang_field_error($errors, 'satuan')) ?>"
                                autocomplete="off"
                            >
                        </div>

                        <?php if (isset($errors['satuan'])): ?>
                            <small class="barang-field-error"><?= app_e($errors['satuan']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="barang-field">
                        <label for="harga_jual">
                            Harga Jual
                            <span>*</span>
                        </label>

                        <div class="barang-input-wrap">
                            <i class="ti ti-cash"></i>
                            <input
                                type="number"
                                id="harga_jual"
                                name="harga_jual"
                                value="<?= app_e($hargaJual) ?>"
                                min="1"
                                step="1"
                                placeholder="Contoh: 2000"
                                class="<?= app_e(barang_field_error($errors, 'harga_jual')) ?>"
                                data-price-preview-input
                            >
                        </div>

                        <small class="barang-field-hint" data-price-preview>
                            Preview harga akan muncul di sini.
                        </small>

                        <?php if (isset($errors['harga_jual'])): ?>
                            <small class="barang-field-error"><?= app_e($errors['harga_jual']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="barang-field">
                        <label for="stok_minimum">
                            Stok Minimum
                            <span>*</span>
                        </label>

                        <div class="barang-input-wrap">
                            <i class="ti ti-alert-triangle"></i>
                            <input
                                type="number"
                                id="stok_minimum"
                                name="stok_minimum"
                                value="<?= app_e($stokMinimum) ?>"
                                min="0"
                                step="1"
                                placeholder="Contoh: 5"
                                class="<?= app_e(barang_field_error($errors, 'stok_minimum')) ?>"
                            >
                        </div>

                        <?php if (isset($errors['stok_minimum'])): ?>
                            <small class="barang-field-error"><?= app_e($errors['stok_minimum']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="barang-field">
                        <label for="status">
                            Status
                            <span>*</span>
                        </label>

                        <div class="barang-input-wrap">
                            <i class="ti ti-toggle-right"></i>
                            <select
                                id="status"
                                name="status"
                                class="<?= app_e(barang_field_error($errors, 'status')) ?>"
                            >
                                <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>

                        <?php if (isset($errors['status'])): ?>
                            <small class="barang-field-error"><?= app_e($errors['status']) ?></small>
                        <?php endif; ?>
                    </div>

                    <?php if ($isEdit): ?>
                        <div class="barang-field">
                            <label>Stok Saat Ini</label>

                            <div class="barang-input-wrap is-readonly">
                                <i class="ti ti-stack"></i>
                                <input
                                    type="text"
                                    value="<?= app_e((string) ($barang['stok'] ?? 0)) ?>"
                                    disabled
                                >
                            </div>

                            <small class="barang-field-hint">
                                Readonly. Ubah stok dari menu restock atau transaksi.
                            </small>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="barang-form-actions">
                    <button type="submit" class="barang-btn barang-btn-primary">
                        <i class="ti ti-device-floppy"></i>
                        Simpan
                    </button>

                    <a href="<?= app_e(app_url('/admin/barang')) ?>" class="barang-btn barang-btn-ghost">
                        <i class="ti ti-x"></i>
                        Batal
                    </a>
                </div>
            </form>
        </article>

        <aside class="barang-form-aside">
            <div class="barang-info-card">
                <span class="barang-info-icon">
                    <i class="ti ti-info-circle"></i>
                </span>

                <h4>Catatan Stok</h4>

                <!-- <p>
                    Stok barang sengaja tidak diedit di sini. Kalau stok diedit manual dari banyak tempat, nanti data restock dan transaksi jadi saling tuduh.
                </p> -->

                <ul>
                    <li>
                        <i class="ti ti-stack-push"></i>
                        Stok masuk lewat menu Restock.
                    </li>
                    <li>
                        <i class="ti ti-shopping-cart"></i>
                        Stok keluar lewat Transaksi.
                    </li>
                    <li>
                        <i class="ti ti-alert-triangle"></i>
                        Stok minimum dipakai untuk penanda stok menipis.
                    </li>
                </ul>
            </div>
        </aside>
    </section>
</div>

<script src="<?= app_e(app_asset_versioned('assets/js/barang.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>