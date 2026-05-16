<?php
$title = $title ?? 'Login';
$flash = $flash ?? [];
$old = $old ?? [];

$appName = defined('APP_NAME') ? APP_NAME : 'Kopsis POS';

function auth_e(mixed $value): string
{
    if (class_exists('Security')) {
        return Security::e($value);
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function auth_asset(string $path): string
{
    $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    return $baseUrl . '/' . ltrim($path, '/');
}

$errorMessage = $flash['error'] ?? null;
$successMessage = $flash['success'] ?? null;
$oldUsername = is_array($old) ? ($old['username'] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= auth_e($title) ?> - <?= auth_e($appName) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= auth_e(auth_asset('assets/css/auth.css')) ?>">
</head>
<body>
    <main class="auth-page">
        <canvas id="authParticles" class="auth-particles" aria-hidden="true"></canvas>

        <section class="auth-left">
            <div class="auth-shape auth-shape-1"></div>
            <div class="auth-shape auth-shape-2"></div>
            <div class="auth-dots auth-dots-1"></div>
            <div class="auth-dots auth-dots-2"></div>
            <div class="auth-cross"></div>

            <div class="auth-bg-hero" aria-hidden="true">
                <img
                src="<?= auth_e(auth_asset('assets/images/icon.png')) ?>"
                alt=""
                onerror="this.parentElement.style.display='none';">
            </div>

            <div class="auth-left-center">
                <div class="auth-logo-wrap" data-auth-animate="zoom-in" data-delay="60">
                    <img
                        src="<?= auth_e(auth_asset('assets/images/mts.png')) ?>"
                        alt="Logo Laboratorium Kewirausahaan"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';"
                    >
                    <span class="auth-logo-fallback">
                        <i class="ti ti-school"></i>
                    </span>
                </div>

                <div class="auth-title-block" data-auth-animate="fade-down" data-delay="120">
                    <span>Sistem Kasir</span>
                    <h1>Koperasi Sekolah</h1>
                    <p>MTSN 8 Banyuwangi</p>
                    <div class="auth-title-line"></div>
                </div>

                <p class="auth-description" data-auth-animate="fade-up" data-delay="180">
                    Kelola Koperasi Sekolah dengan lebih mudah, cepat, dan terstruktur.
                </p>
            </div>

            <div class="auth-benefits">
                <div class="auth-benefit" data-auth-animate="fade-right" data-delay="220">
                    <i class="ti ti-shield-check"></i>
                    <div>
                        <strong>Aman</strong>
                        <span>Data terjamin keamanannya</span>
                    </div>
                </div>

                <div class="auth-benefit" data-auth-animate="fade-up" data-delay="280">
                    <i class="ti ti-list-details"></i>
                    <div>
                        <strong>Terstruktur</strong>
                        <span>Data rapi dan mudah dikelola</span>
                    </div>
                </div>

                <div class="auth-benefit" data-auth-animate="fade-left" data-delay="340">
                    <i class="ti ti-bolt"></i>
                    <div>
                        <strong>Cepat</strong>
                        <span>Proses transaksi lebih efisien</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="auth-right">
            <div class="auth-form-card" data-auth-animate="fade-left" data-delay="140">
                <div class="auth-lock" data-auth-animate="zoom-in" data-delay="190">
                    <i class="ti ti-lock"></i>
                </div>

                <div class="auth-heading">
                    <h2>LOGIN</h2>
                    <p>Masuk untuk mengakses sistem kasir</p>
                </div>

                <?php if (!empty($errorMessage)): ?>
                    <div class="auth-alert auth-alert-danger" role="alert">
                        <i class="ti ti-alert-circle"></i>
                        <span><?= auth_e($errorMessage) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($successMessage)): ?>
                    <div class="auth-alert auth-alert-success" role="alert">
                        <i class="ti ti-circle-check"></i>
                        <span><?= auth_e($successMessage) ?></span>
                    </div>
                <?php endif; ?>

                <form action="<?= auth_e(auth_asset('login')) ?>" method="POST" class="auth-form" autocomplete="on">
                    <div class="auth-field">
                        <label for="username">Username</label>
                        <div class="auth-input">
                            <i class="ti ti-user"></i>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?= auth_e($oldUsername) ?>"
                                placeholder="Username"
                                autocomplete="username"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="password">Password</label>
                        <div class="auth-input">
                            <i class="ti ti-lock"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Password"
                                autocomplete="current-password"
                                required
                            >
                            <button
                                type="button"
                                class="auth-eye"
                                data-password-toggle="password"
                                aria-label="Tampilkan password"
                            >
                                <i class="ti ti-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="auth-options">
                        <label class="auth-check">
                            <input type="checkbox" name="remember" value="1">
                            <span>Remember me</span>
                        </label>

                        <span class="auth-link">Forgot Password?</span>
                    </div>

                    <button type="submit" class="auth-submit">
                        Masuk ke Sistem
                    </button>
                </form>
            </div>
        </section>
    </main>

    <script src="<?= auth_e(auth_asset('assets/js/auth-particles.js')) ?>"></script>
</body>
</html>