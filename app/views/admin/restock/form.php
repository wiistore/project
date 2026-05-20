<?php
$title = $title ?? 'Tambah Restock';
$activeMenu = $activeMenu ?? 'restock';

$pageCss = ['assets/css/restock.css'];

$formAction = $formAction ?? '/admin/restock/store';

$barangs = $barangs ?? [];
$suppliers = $suppliers ?? [];
$errors = $errors ?? [];
$old = $old ?? [];

$tanggal = $old['tanggal'] ?? date('Y-m-d');
$idBarang = $old['id_barang'] ?? '';
$idSupplier = $old['id_supplier'] ?? '';
$qty = $old['qty'] ?? '';
$hargaBeli = $old['harga_beli'] ?? '';
$hargaJualBaru = $old['harga_jual_baru'] ?? '';
$catatan = $old['catatan'] ?? '';

if (!function_exists('restock_field_error')) {
    function restock_field_error(array $errors, string $field): string
    {
        return isset($errors[$field]) ? ' is-invalid' : '';
    }
}

if (!function_exists('restock_option_price')) {
    function restock_option_price(mixed $value): string
    {
        return number_format((float) $value, 0, '.', '');
    }
}
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="restock-page">
    <section class="restock-hero restock-form-hero" data-aos="fade-down" data-aos-duration="700">
        <div class="restock-hero-content">
            <span class="restock-eyebrow">
                <i class="ti ti-stack-push"></i>
                Tambah Stok Masuk
            </span>

            <h2><?= app_e($title) ?></h2>
        </div>

        <div class="restock-hero-actions">
            <a href="<?= app_e(app_url('/admin/restock')) ?>" class="restock-btn restock-btn-soft">
                <i class="ti ti-arrow-left"></i>
                Kembali
            </a>
        </div>
    </section>

    <section class="restock-form-layout" data-aos="fade-up" data-aos-delay="150">
        <article class="restock-form-card">
            <div class="restock-form-head">
                <div>
                    <span>Form Restock</span>
                    <h3>Tambah Restock Barang</h3>
                </div>

                <span class="restock-form-badge">
                    <i class="ti ti-plus"></i>
                    Stok Masuk
                </span>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="restock-alert restock-alert-error">
                    <i class="ti ti-alert-triangle"></i>
                    <!-- <span>Masih ada input yang perlu dibenerin. Restock itu angka, bukan firasat.</span> -->
                </div>
            <?php endif; ?>

            <form action="<?= app_e(app_url($formAction)) ?>" method="POST" class="restock-form" data-restock-form>
                <div class="restock-form-grid">
                    <div class="restock-field">
                        <label for="tanggal">
                            Tanggal Restock
                            <span>*</span>
                        </label>

                        <div class="restock-input-wrap">
                            <i class="ti ti-calendar"></i>
                            <input
                                type="date"
                                id="tanggal"
                                name="tanggal"
                                value="<?= app_e($tanggal) ?>"
                                class="<?= app_e(restock_field_error($errors, 'tanggal')) ?>"
                            >
                        </div>

                        <?php if (isset($errors['tanggal'])): ?>
                            <small class="restock-field-error"><?= app_e($errors['tanggal']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="restock-field">
                        <label for="id_supplier">
                            Supplier
                            <span>*</span>
                        </label>

                        <div class="restock-input-wrap">
                            <i class="ti ti-truck-delivery"></i>
                            <select
                                id="id_supplier"
                                name="id_supplier"
                                class="<?= app_e(restock_field_error($errors, 'id_supplier')) ?>"
                                data-supplier-select
                            >
                                <option value="">Pilih supplier</option>

                                <?php foreach ($suppliers as $supplier): ?>
                                    <option
                                        value="<?= app_e($supplier['id'] ?? '') ?>"
                                        data-name="<?= app_e($supplier['nama'] ?? '-') ?>"
                                        data-contact="<?= app_e($supplier['kontak_person'] ?? '-') ?>"
                                        data-phone="<?= app_e($supplier['no_hp'] ?? '-') ?>"
                                        <?= (string) $idSupplier === (string) ($supplier['id'] ?? '') ? 'selected' : '' ?>
                                    >
                                        <?= app_e($supplier['nama'] ?? '-') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if (isset($errors['id_supplier'])): ?>
                            <small class="restock-field-error"><?= app_e($errors['id_supplier']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="restock-field field-full">
                        <label for="id_barang">
                            Barang
                            <span>*</span>
                        </label>

                        <div class="restock-input-wrap">
                            <i class="ti ti-package"></i>
                            <select
                                id="id_barang"
                                name="id_barang"
                                class="<?= app_e(restock_field_error($errors, 'id_barang')) ?>"
                                data-barang-select
                            >
                                <option value="">Pilih barang</option>

                                <?php foreach ($barangs as $barang): ?>
                                    <option
                                        value="<?= app_e($barang['id'] ?? '') ?>"
                                        data-name="<?= app_e($barang['nama'] ?? '-') ?>"
                                        data-code="<?= app_e($barang['kode_barang'] ?? '-') ?>"
                                        data-stock="<?= app_e((string) ($barang['stok'] ?? 0)) ?>"
                                        data-unit="<?= app_e($barang['satuan'] ?? '-') ?>"
                                        data-price="<?= app_e(restock_option_price($barang['harga_jual'] ?? 0)) ?>"
                                        <?= (string) $idBarang === (string) ($barang['id'] ?? '') ? 'selected' : '' ?>
                                    >
                                        <?= app_e($barang['kode_barang'] ?? '-') ?> - <?= app_e($barang['nama'] ?? '-') ?>
                                        | Stok: <?= app_e((string) ($barang['stok'] ?? 0)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if (isset($errors['id_barang'])): ?>
                            <small class="restock-field-error"><?= app_e($errors['id_barang']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="restock-field">
                        <label for="qty">
                            Qty Masuk
                            <span>*</span>
                        </label>

                        <div class="restock-input-wrap">
                            <i class="ti ti-plus"></i>
                            <input
                                type="number"
                                id="qty"
                                name="qty"
                                value="<?= app_e($qty) ?>"
                                min="1"
                                step="1"
                                placeholder="Contoh: 20"
                                class="<?= app_e(restock_field_error($errors, 'qty')) ?>"
                                data-qty-input
                            >
                        </div>

                        <?php if (isset($errors['qty'])): ?>
                            <small class="restock-field-error"><?= app_e($errors['qty']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="restock-field">
                        <label for="harga_beli">
                            Harga Beli per Item
                            <span>*</span>
                        </label>

                        <div class="restock-input-wrap">
                            <i class="ti ti-cash"></i>
                            <input
                                type="number"
                                id="harga_beli"
                                name="harga_beli"
                                value="<?= app_e($hargaBeli) ?>"
                                min="1"
                                step="1"
                                placeholder="Contoh: 1500"
                                class="<?= app_e(restock_field_error($errors, 'harga_beli')) ?>"
                                data-buy-price-input
                            >
                        </div>

                        <small class="restock-field-hint" data-buy-price-preview>
                            Preview harga beli akan muncul di sini.
                        </small>

                        <?php if (isset($errors['harga_beli'])): ?>
                            <small class="restock-field-error"><?= app_e($errors['harga_beli']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="restock-field">
                        <label for="harga_jual_baru">
                            Harga Jual Baru
                        </label>

                        <div class="restock-input-wrap">
                            <i class="ti ti-tag"></i>
                            <input
                                type="number"
                                id="harga_jual_baru"
                                name="harga_jual_baru"
                                value="<?= app_e($hargaJualBaru) ?>"
                                min="1"
                                step="1"
                                placeholder="Kosongkan kalau tidak berubah"
                                class="<?= app_e(restock_field_error($errors, 'harga_jual_baru')) ?>"
                                data-new-price-input
                            >
                        </div>

                        <!-- <small class="restock-field-hint">
                            Opsional. Kalau diisi, harga jual barang akan ikut diperbarui.
                        </small> -->

                        <?php if (isset($errors['harga_jual_baru'])): ?>
                            <small class="restock-field-error"><?= app_e($errors['harga_jual_baru']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="restock-field field-full">
                        <label for="catatan">Catatan</label>

                        <div class="restock-textarea-wrap">
                            <i class="ti ti-notes"></i>
                            <textarea
                                id="catatan"
                                name="catatan"
                                rows="5"
                                placeholder="Catatan tambahan, nomor nota, atau info pembelian"
                                class="<?= app_e(restock_field_error($errors, 'catatan')) ?>"
                            ><?= app_e($catatan) ?></textarea>
                        </div>

                        <?php if (isset($errors['catatan'])): ?>
                            <small class="restock-field-error"><?= app_e($errors['catatan']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="restock-total-box">
                    <div>
                        <span>Total Nilai Restock</span>
                        <strong data-restock-total>Rp 0</strong>
                    </div>

                    <small>
                        Total = qty masuk × harga beli per item.
                    </small>
                </div>

                <div class="restock-form-actions">
                    <button type="submit" class="restock-btn restock-btn-primary restock-submit-btn">
                        <i class="ti ti-device-floppy"></i>
                        Simpan Restock
                    </button>

                    <a href="<?= app_e(app_url('/admin/restock')) ?>" class="restock-btn restock-btn-ghost">
                        <i class="ti ti-x"></i>
                        Batal
                    </a>
                </div>
            </form>
        </article>

        <aside class="restock-form-aside">
            <div class="restock-info-card" data-barang-preview>
                <span class="restock-info-icon">
                    <i class="ti ti-package"></i>
                </span>

                <h4>Info Barang</h4>

                <!-- <p>Pilih barang dulu. Iya, komputer belum bisa nebak barang dari niat baik.</p> -->

                <ul>
                    <li>
                        <i class="ti ti-barcode"></i>
                        <span data-preview-code>Kode: -</span>
                    </li>
                    <li>
                        <i class="ti ti-stack"></i>
                        <span data-preview-stock>Stok saat ini: -</span>
                    </li>
                    <li>
                        <i class="ti ti-cash"></i>
                        <span data-preview-price>Harga jual sekarang: -</span>
                    </li>
                </ul>
            </div>

            <div class="restock-info-card" data-supplier-preview>
                <span class="restock-info-icon icon-blue">
                    <i class="ti ti-truck-delivery"></i>
                </span>

                <h4>Info Supplier</h4>

                <!-- <p>Pilih supplier aktif yang menjadi sumber restock barang.</p> -->

                <ul>
                    <li>
                        <i class="ti ti-user"></i>
                        <span data-preview-contact>Kontak: -</span>
                    </li>
                    <li>
                        <i class="ti ti-phone"></i>
                        <span data-preview-phone>No HP: -</span>
                    </li>
                </ul>
            </div>
        </aside>
    </section>
</div>

<script src="<?= app_e(app_asset_versioned('assets/js/restock.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>