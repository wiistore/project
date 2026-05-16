<?php
$title = $title ?? 'Form Kategori';
$activeMenu = $activeMenu ?? 'kategori';

$pageCss = ['assets/css/kategori.css'];

$formAction = $formAction ?? '/admin/kategori/store';
$formMode = $formMode ?? 'create';
$kategori = $kategori ?? null;
$errors = $errors ?? [];
$old = $old ?? [];

$nama = $old['nama'] ?? ($kategori['nama'] ?? '');
$deskripsi = $old['deskripsi'] ?? ($kategori['deskripsi'] ?? '');

$isEdit = $formMode === 'edit';

if (!function_exists('kategori_field_error')) {
    function kategori_field_error(array $errors, string $field): string
    {
        return isset($errors[$field]) ? ' is-invalid' : '';
    }
}
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="kategori-page">
    <section class="kategori-hero kategori-form-hero">
        <div class="kategori-hero-content">
            <span class="kategori-eyebrow">
                <i class="<?= $isEdit ? 'ti ti-edit' : 'ti ti-folder-plus' ?>"></i>
                <?= $isEdit ? 'Edit Master Kategori' : 'Tambah Master Kategori' ?>
            </span>

            <h2><?= app_e($title) ?></h2>

            <p>
                <?= $isEdit
                    ? 'Perbarui nama dan deskripsi kategori. Jangan ganti nama asal-asalan kalau sudah dipakai banyak barang.'
                    : 'Tambahkan kategori baru supaya barang bisa dikelompokkan dengan benar, bukan dilempar ke kategori asal hidup.'
                ?>
            </p>
        </div>

        <div class="kategori-hero-actions">
            <a href="<?= app_e(app_url('/admin/kategori')) ?>" class="kategori-btn kategori-btn-soft">
                <i class="ti ti-arrow-left"></i>
                Kembali
            </a>
        </div>
    </section>

    <section class="kategori-form-layout">
        <article class="kategori-form-card">
            <div class="kategori-form-head">
                <div>
                    <span>Form Kategori</span>
                    <h3><?= $isEdit ? 'Edit Data Kategori' : 'Tambah Kategori Baru' ?></h3>
                </div>

                <span class="kategori-form-badge">
                    <i class="<?= $isEdit ? 'ti ti-pencil' : 'ti ti-plus' ?>"></i>
                    <?= $isEdit ? 'Mode Edit' : 'Mode Tambah' ?>
                </span>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="kategori-alert kategori-alert-error">
                    <i class="ti ti-alert-triangle"></i>
                    <span>Masih ada input yang salah. Komputer sudah cukup menderita, jangan ditambah data invalid.</span>
                </div>
            <?php endif; ?>

            <form action="<?= app_e(app_url($formAction)) ?>" method="POST" class="kategori-form" data-kategori-form>
                <div class="kategori-form-grid">
                    <div class="kategori-field">
                        <label for="nama">
                            Nama Kategori
                            <span>*</span>
                        </label>

                        <div class="kategori-input-wrap">
                            <i class="ti ti-folder"></i>
                            <input
                                type="text"
                                id="nama"
                                name="nama"
                                value="<?= app_e($nama) ?>"
                                placeholder="Contoh: Alat Tulis"
                                class="<?= app_e(kategori_field_error($errors, 'nama')) ?>"
                                maxlength="100"
                                autocomplete="off"
                                autofocus
                            >
                        </div>

                        <div class="kategori-field-footer">
                            <small class="kategori-field-hint">
                                Maksimal 100 karakter.
                            </small>

                            <small class="kategori-counter" data-kategori-counter>
                                0/100
                            </small>
                        </div>

                        <?php if (isset($errors['nama'])): ?>
                            <small class="kategori-field-error"><?= app_e($errors['nama']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="kategori-field field-full">
                        <label for="deskripsi">
                            Deskripsi
                        </label>

                        <div class="kategori-textarea-wrap">
                            <i class="ti ti-align-left"></i>
                            <textarea
                                id="deskripsi"
                                name="deskripsi"
                                rows="6"
                                placeholder="Contoh: Kategori untuk pensil, pulpen, buku, penghapus, dan perlengkapan tulis lain."
                                class="<?= app_e(kategori_field_error($errors, 'deskripsi')) ?>"
                            ><?= app_e($deskripsi) ?></textarea>
                        </div>

                        <small class="kategori-field-hint">
                            Opsional, tapi lebih bagus diisi. Nama doang kadang terlalu percaya diri.
                        </small>

                        <?php if (isset($errors['deskripsi'])): ?>
                            <small class="kategori-field-error"><?= app_e($errors['deskripsi']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="kategori-form-actions">
                    <button type="submit" class="kategori-btn kategori-btn-primary kategori-submit-btn">
                        <i class="ti ti-device-floppy"></i>
                        Simpan
                    </button>

                    <a href="<?= app_e(app_url('/admin/kategori')) ?>" class="kategori-btn kategori-btn-ghost">
                        <i class="ti ti-x"></i>
                        Batal
                    </a>
                </div>
            </form>
        </article>

        <aside class="kategori-form-aside">
            <div class="kategori-info-card">
                <span class="kategori-info-icon">
                    <i class="ti ti-info-circle"></i>
                </span>

                <h4>Catatan Kategori</h4>

                <p>
                    Kategori dipakai oleh master barang. Kalau kategori masih dipakai barang, backend akan menolak penghapusan. Ini bagus, karena data relasi yang hancur itu bukan fitur.
                </p>

                <ul>
                    <li>
                        <i class="ti ti-folder"></i>
                        Nama kategori wajib diisi.
                    </li>
                    <li>
                        <i class="ti ti-package"></i>
                        Kategori dipakai saat tambah atau edit barang.
                    </li>
                    <li>
                        <i class="ti ti-trash-off"></i>
                        Kategori yang masih dipakai barang tidak bisa dihapus.
                    </li>
                </ul>
            </div>
        </aside>
    </section>
</div>

<script src="<?= app_e(app_asset('assets/js/kategori.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>