<?php
$title = $title ?? 'Profil Saya';
$activeMenu = $activeMenu ?? 'profil';

$pageCss = ['assets/css/profil.css'];

$__viewData = get_defined_vars();

$user = isset($__viewData['user']) && is_array($__viewData['user'])
    ? $__viewData['user']
    : (class_exists('Session') ? (Session::user() ?? []) : []);

$userData = isset($__viewData['userData']) && is_array($__viewData['userData'])
    ? $__viewData['userData']
    : $user;

$flash = isset($__viewData['flash']) && is_array($__viewData['flash'])
    ? $__viewData['flash']
    : [];

$errors = isset($__viewData['errors']) && is_array($__viewData['errors'])
    ? $__viewData['errors']
    : [];

$success = $flash['success'] ?? null;
$error = $flash['error'] ?? null;

$username = (string) ($userData['username'] ?? 'Kasir');
$email = (string) ($userData['email'] ?? '-');
$role = strtolower((string) ($userData['role'] ?? 'kasir'));
$status = strtolower((string) ($userData['status'] ?? 'aktif'));
$createdAt = (string) ($userData['created_at'] ?? '');
$updatedAt = (string) ($userData['updated_at'] ?? '');

if (!function_exists('profil_date')) {
    function profil_date(mixed $value): string
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

if (!function_exists('profil_field_error')) {
    function profil_field_error(array $errors, string $field): string
    {
        return isset($errors[$field]) ? ' is-invalid' : '';
    }
}
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="profil-page">
    <?php if ($success): ?>
        <div class="profil-alert profil-alert-success">
            <i class="ti ti-circle-check"></i>
            <span><?= app_e($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="profil-alert profil-alert-error">
            <i class="ti ti-alert-triangle"></i>
            <span><?= app_e($error) ?></span>
        </div>
    <?php endif; ?>

    <section class="profil-hero">
        <div class="profil-hero-content">
            <span class="profil-eyebrow">
                <i class="ti ti-user-circle"></i>
                Profil Kasir
            </span>

            <h2>Profil Saya</h2>

            <p>
                Halaman ini hanya untuk melihat info akun dan mengganti password. Username dan email dikelola admin, biar akun kasir tidak berubah nama seenaknya seperti file final_revisi_fix_beneran.zip.
            </p>
        </div>

        <div class="profil-hero-actions">
            <a href="<?= app_e(app_url('/kasir/dashboard')) ?>" class="profil-btn profil-btn-primary">
                <i class="ti ti-layout-dashboard"></i>
                Dashboard
            </a>

            <a href="<?= app_e(app_url('/kasir/transaksi')) ?>" class="profil-btn profil-btn-soft">
                <i class="ti ti-shopping-cart-plus"></i>
                POS
            </a>
        </div>
    </section>

    <section class="profil-overview">
        <article class="profil-card profil-identity-card">
            <div class="profil-avatar">
                <i class="ti ti-user"></i>
            </div>

            <div class="profil-identity-content">
                <span>Akun Login</span>
                <h3><?= app_e($username) ?></h3>
                <p><?= app_e($email) ?></p>
            </div>

            <div class="profil-badges">
                <span class="profil-role">
                    <i class="ti ti-cash-register"></i>
                    <?= app_e(ucfirst($role)) ?>
                </span>

                <span class="profil-status <?= $status === 'aktif' ? 'is-active' : 'is-inactive' ?>">
                    <i class="<?= $status === 'aktif' ? 'ti ti-circle-check' : 'ti ti-circle-off' ?>"></i>
                    <?= app_e(ucfirst($status)) ?>
                </span>
            </div>
        </article>

        <article class="profil-mini-card summary-green">
            <span>
                <i class="ti ti-calendar-plus"></i>
            </span>

            <div>
                <small>Dibuat</small>
                <strong><?= app_e(profil_date($createdAt)) ?></strong>
            </div>
        </article>

        <article class="profil-mini-card summary-blue">
            <span>
                <i class="ti ti-calendar-cog"></i>
            </span>

            <div>
                <small>Update Terakhir</small>
                <strong><?= app_e(profil_date($updatedAt)) ?></strong>
            </div>
        </article>
    </section>

    <section class="profil-layout profil-password-only">
        <article class="profil-card profil-form-card">
            <div class="profil-card-head">
                <div>
                    <span>Keamanan</span>
                    <h3>Reset Password</h3>
                </div>

                <span class="profil-head-badge">
                    <i class="ti ti-shield-lock"></i>
                    Password
                </span>
            </div>

            <?php if (isset($errors['current_password']) || isset($errors['password']) || isset($errors['password_confirmation'])): ?>
                <div class="profil-alert profil-alert-error">
                    <i class="ti ti-alert-triangle"></i>
                    <span>Password belum valid. Bukan berarti sistem jahat, passwordnya saja yang belum beres.</span>
                </div>
            <?php endif; ?>

            <form action="<?= app_e(app_url('/kasir/profil/password')) ?>" method="POST" class="profil-form" data-profil-form>
                <div class="profil-field">
                    <label for="current_password">
                        Password Saat Ini
                        <span>*</span>
                    </label>

                    <div class="profil-input-wrap">
                        <i class="ti ti-lock"></i>
                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            placeholder="Masukkan password saat ini"
                            class="<?= app_e(profil_field_error($errors, 'current_password')) ?>"
                            autocomplete="current-password"
                            autofocus
                        >

                        <button type="button" class="profil-toggle-password" data-toggle-password aria-label="Tampilkan password">
                            <i class="ti ti-eye"></i>
                        </button>
                    </div>

                    <?php if (isset($errors['current_password'])): ?>
                        <small class="profil-field-error"><?= app_e($errors['current_password']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="profil-field">
                    <label for="password">
                        Password Baru
                        <span>*</span>
                    </label>

                    <div class="profil-input-wrap">
                        <i class="ti ti-key"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Minimal 8 karakter"
                            class="<?= app_e(profil_field_error($errors, 'password')) ?>"
                            autocomplete="new-password"
                            data-password-input
                        >

                        <button type="button" class="profil-toggle-password" data-toggle-password aria-label="Tampilkan password">
                            <i class="ti ti-eye"></i>
                        </button>
                    </div>

                    <div class="profil-password-meter">
                        <span data-password-meter></span>
                    </div>

                    <small class="profil-field-hint" data-password-hint>Minimal 8 karakter.</small>

                    <?php if (isset($errors['password'])): ?>
                        <small class="profil-field-error"><?= app_e($errors['password']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="profil-field">
                    <label for="password_confirmation">
                        Konfirmasi Password Baru
                        <span>*</span>
                    </label>

                    <div class="profil-input-wrap">
                        <i class="ti ti-lock-check"></i>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Ulangi password baru"
                            class="<?= app_e(profil_field_error($errors, 'password_confirmation')) ?>"
                            autocomplete="new-password"
                        >

                        <button type="button" class="profil-toggle-password" data-toggle-password aria-label="Tampilkan password">
                            <i class="ti ti-eye"></i>
                        </button>
                    </div>

                    <?php if (isset($errors['password_confirmation'])): ?>
                        <small class="profil-field-error"><?= app_e($errors['password_confirmation']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="profil-form-actions">
                    <button type="submit" class="profil-btn profil-btn-submit" data-submit-label="Simpan Password">
                        <i class="ti ti-device-floppy"></i>
                        Simpan Password
                    </button>
                </div>
            </form>
        </article>

        <aside class="profil-side">
            <div class="profil-card profil-tips-card">
                <span class="profil-tips-icon">
                    <i class="ti ti-shield-lock"></i>
                </span>

                <h4>Keamanan Akun</h4>

                <p>
                    Username dan email hanya bisa diubah admin. Kasir cukup mengganti password sendiri supaya akses POS tetap aman.
                </p>

                <ul>
                    <li>
                        <i class="ti ti-check"></i>
                        Gunakan password minimal 8 karakter.
                    </li>
                    <li>
                        <i class="ti ti-check"></i>
                        Jangan pakai username sebagai password.
                    </li>
                    <li>
                        <i class="ti ti-check"></i>
                        Logout kalau komputer dipakai bersama.
                    </li>
                </ul>
            </div>

            <div class="profil-card profil-action-card">
                <h4>Aksi Cepat</h4>

                <a href="<?= app_e(app_url('/kasir/dashboard')) ?>">
                    <i class="ti ti-layout-dashboard"></i>
                    <span>
                        <strong>Dashboard</strong>
                        <small>Kembali ke ringkasan kasir</small>
                    </span>
                </a>

                <a href="<?= app_e(app_url('/kasir/transaksi')) ?>">
                    <i class="ti ti-shopping-cart-plus"></i>
                    <span>
                        <strong>POS Transaksi</strong>
                        <small>Mulai transaksi baru</small>
                    </span>
                </a>
            </div>
        </aside>
    </section>
</div>

<script src="<?= app_e(app_asset('assets/js/profil.js')) ?>"></script>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>