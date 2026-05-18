<?php
$title = $title ?? 'Form Kasir';
$activeMenu = $activeMenu ?? 'user';

$pageCss = ['assets/css/user.css?v=1' . time()];

$__viewData = get_defined_vars();

$formAction = isset($__viewData['formAction'])
    ? (string) $__viewData['formAction']
    : '/admin/user/store';

$formMode = isset($__viewData['formMode'])
    ? (string) $__viewData['formMode']
    : 'create';

$userData = isset($__viewData['userData']) && is_array($__viewData['userData'])
    ? $__viewData['userData']
    : null;

$errors = isset($__viewData['errors']) && is_array($__viewData['errors'])
    ? $__viewData['errors']
    : [];

$old = isset($__viewData['old']) && is_array($__viewData['old'])
    ? $__viewData['old']
    : [];

$isEdit = $formMode === 'edit';

$username = $old['username'] ?? ($userData['username'] ?? '');
$email = $old['email'] ?? ($userData['email'] ?? '');
$status = $old['status'] ?? ($userData['status'] ?? 'aktif');

if (!function_exists('user_field_error')) {
    function user_field_error(array $errors, string $field): string
    {
        return isset($errors[$field]) ? ' is-invalid' : '';
    }
}
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="user-page">
    <section class="user-hero user-form-hero">
        <div class="user-hero-content">
            <span class="user-eyebrow">
                <i class="<?= $isEdit ? 'ti ti-edit' : 'ti ti-user-plus' ?>"></i>
                <?= $isEdit ? 'Edit Akun Kasir' : 'Tambah Akun Kasir' ?>
            </span>

            <h2><?= app_e($title) ?></h2>

            <p>
                <?= $isEdit
                    ? 'Perbarui username, email, dan status kasir. Password direset dari halaman terpisah supaya tidak tercampur seperti kabel charger di laci.'
                    : 'Buat akun kasir baru untuk login POS. Password minimal 8 karakter, karena “12345678” itu bukan keamanan, itu undangan bencana.'
                ?>
            </p>
        </div>

        <div class="user-hero-actions">
            <a href="<?= app_e(app_url('/admin/user')) ?>" class="user-btn user-btn-soft">
                <i class="ti ti-arrow-left"></i>
                Kembali
            </a>
        </div>
    </section>

    <section class="user-form-layout">
        <article class="user-form-card">
            <div class="user-form-head">
                <div>
                    <span>Form Kasir</span>
                    <h3><?= $isEdit ? 'Edit Data Kasir' : 'Tambah Kasir Baru' ?></h3>
                </div>

                <span class="user-form-badge">
                    <i class="<?= $isEdit ? 'ti ti-pencil' : 'ti ti-plus' ?>"></i>
                    <?= $isEdit ? 'Mode Edit' : 'Mode Tambah' ?>
                </span>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="user-alert user-alert-error">
                    <i class="ti ti-alert-triangle"></i>
                    <span>Masih ada input yang perlu dibenerin. User login bukan tempat trial-error barbar.</span>
                </div>
            <?php endif; ?>

            <form action="<?= app_e(app_url($formAction)) ?>" method="POST" class="user-form" data-user-form>
                <div class="user-form-grid">
                    <div class="user-field">
                        <label for="username">
                            Username
                            <span>*</span>
                        </label>

                        <div class="user-input-wrap">
                            <i class="ti ti-user"></i>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?= app_e($username) ?>"
                                placeholder="Contoh: kasir01"
                                maxlength="50"
                                autocomplete="off"
                                class="<?= app_e(user_field_error($errors, 'username')) ?>"
                                autofocus
                            >
                        </div>

                        <div class="user-field-footer">
                            <small class="user-field-hint">Wajib diisi. Maksimal 50 karakter.</small>
                            <small class="user-counter" data-user-counter>0/50</small>
                        </div>

                        <?php if (isset($errors['username'])): ?>
                            <small class="user-field-error"><?= app_e($errors['username']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="user-field">
                        <label for="email">
                            Email
                            <span>*</span>
                        </label>

                        <div class="user-input-wrap">
                            <i class="ti ti-mail"></i>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= app_e($email) ?>"
                                placeholder="kasir@email.com"
                                maxlength="100"
                                autocomplete="off"
                                class="<?= app_e(user_field_error($errors, 'email')) ?>"
                            >
                        </div>

                        <?php if (isset($errors['email'])): ?>
                            <small class="user-field-error"><?= app_e($errors['email']) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="user-field">
                        <label for="status">
                            Status
                            <span>*</span>
                        </label>

                        <div class="user-input-wrap">
                            <i class="ti ti-toggle-right"></i>
                            <select
                                id="status"
                                name="status"
                                class="<?= app_e(user_field_error($errors, 'status')) ?>"
                            >
                                <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>

                        <?php if (isset($errors['status'])): ?>
                            <small class="user-field-error"><?= app_e($errors['status']) ?></small>
                        <?php endif; ?>
                    </div>

                    <?php if (!$isEdit): ?>
                        <div class="user-field">
                            <label for="password">
                                Password
                                <span>*</span>
                            </label>

                            <div class="user-input-wrap">
                                <i class="ti ti-lock"></i>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Minimal 8 karakter"
                                    class="<?= app_e(user_field_error($errors, 'password')) ?>"
                                    data-password-input
                                >

                                <button type="button" class="user-toggle-password" data-toggle-password aria-label="Tampilkan password">
                                    <i class="ti ti-eye"></i>
                                </button>
                            </div>

                            <div class="user-password-meter">
                                <span data-password-meter></span>
                            </div>

                            <small class="user-field-hint" data-password-hint>
                                Minimal 8 karakter.
                            </small>

                            <?php if (isset($errors['password'])): ?>
                                <small class="user-field-error"><?= app_e($errors['password']) ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="user-field">
                            <label for="password_confirmation">
                                Konfirmasi Password
                                <span>*</span>
                            </label>

                            <div class="user-input-wrap">
                                <i class="ti ti-lock-check"></i>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    placeholder="Ulangi password"
                                    class="<?= app_e(user_field_error($errors, 'password_confirmation')) ?>"
                                >

                                <button type="button" class="user-toggle-password" data-toggle-password aria-label="Tampilkan password">
                                    <i class="ti ti-eye"></i>
                                </button>
                            </div>

                            <?php if (isset($errors['password_confirmation'])): ?>
                                <small class="user-field-error"><?= app_e($errors['password_confirmation']) ?></small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="user-form-actions">
                    <button type="submit" class="user-btn user-btn-submit user-submit-btn">
                        <i class="ti ti-device-floppy"></i>
                        Simpan
                    </button>

                    <a href="<?= app_e(app_url('/admin/user')) ?>" class="user-btn user-btn-ghost">
                        <i class="ti ti-x"></i>
                        Batal
                    </a>

                    <?php if ($isEdit && !empty($userData['id'])): ?>
                        <a href="<?= app_e(app_url('/admin/user/reset-password/' . $userData['id'])) ?>" class="user-btn user-btn-warning">
                            <i class="ti ti-key"></i>
                            Reset Password
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </article>

        <aside class="user-form-aside">
            <div class="user-info-card">
                <span class="user-info-icon">
                    <i class="ti ti-info-circle"></i>
                </span>

                <h4>Catatan Akun</h4>

                <p>
                    Akun dari menu ini selalu dibuat sebagai kasir. Admin utama tidak dikelola dari sini supaya tidak ada orang iseng menghapus pusat komando.
                </p>

                <ul>
                    <li>
                        <i class="ti ti-cash-register"></i>
                        Kasir aktif bisa login dan memakai POS.
                    </li>
                    <li>
                        <i class="ti ti-user-off"></i>
                        Kasir nonaktif tidak dipakai untuk operasional.
                    </li>
                    <li>
                        <i class="ti ti-key"></i>
                        Password edit lewat halaman reset.
                    </li>
                </ul>
            </div>
        </aside>
    </section>
</div>

<script src="<?= app_e(app_asset('assets/js/user.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>