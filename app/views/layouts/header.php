<?php
$title = $title ?? 'Dashboard';
$appName = defined('APP_NAME') ? APP_NAME : 'Kopsis POS';
$activeMenu = $activeMenu ?? '';
$currentUser = $user ?? (class_exists('Session') ? Session::user() : null);

$pageCss = $pageCss ?? [];
$pageScript = $pageScript ?? null;
$useChart = $useChart ?? false;

if (is_string($pageCss)) {
    $pageCss = [$pageCss];
}

if (!function_exists('app_e')) {
    function app_e(mixed $value): string
    {
        if (class_exists('Security')) {
            return Security::e($value);
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('app_base_url')) {
    function app_base_url(): string
    {
        if (defined('BASE_URL') && BASE_URL !== '') {
            return rtrim(BASE_URL, '/');
        }

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

        if (str_contains($scriptName, '/public/index.php')) {
            return rtrim(str_replace('/public/index.php', '', $scriptName), '/');
        }

        if (str_contains($scriptName, '/index.php')) {
            return rtrim(str_replace('/index.php', '', $scriptName), '/');
        }

        return '';
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        return app_base_url() . '/' . ltrim($path, '/');
    }
}

if (!function_exists('app_asset')) {
    function app_asset(string $path): string
    {
        return app_url($path);
    }
}

if (!function_exists('app_user_name')) {
    function app_user_name(?array $user): string
    {
        if (!$user) {
            return 'Pengguna';
        }

        return $user['nama_lengkap']
            ?? $user['nama']
            ?? $user['username']
            ?? 'Pengguna';
    }
}

if (!function_exists('app_user_role')) {
    function app_user_role(?array $user): string
    {
        return strtolower((string) ($user['role'] ?? 'admin'));
    }
}

if (!function_exists('app_user_initial')) {
    function app_user_initial(?array $user): string
    {
        $name = trim(app_user_name($user));

        if ($name === '') {
            return 'U';
        }

        return function_exists('mb_substr')
            ? strtoupper(mb_substr($name, 0, 1))
            : strtoupper(substr($name, 0, 1));
    }
}

if (!function_exists('app_is_active')) {
    function app_is_active(string $key, string $activeMenu): string
    {
        return $key === $activeMenu ? 'is-active' : '';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= app_e($title) ?> - <?= app_e($appName) ?></title>

    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/dist/tabler-icons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="<?= app_e(app_asset('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= app_e(app_asset('assets/css/components.css')) ?>">

    <?php foreach ($pageCss as $cssFile): ?>
        <link rel="stylesheet" href="<?= app_e(app_asset((string) $cssFile)) ?>">
    <?php endforeach; ?>
</head>
<body class="app-body">
<div class="app-shell">