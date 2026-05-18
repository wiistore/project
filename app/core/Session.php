<?php

declare(strict_types=1);

class Session
{
    private static $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        // Config session
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
        self::$started = true;
    }

    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        self::start();

        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();

        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        self::start();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'] ?? '/',
                $params['domain'] ?? '',
                $params['secure'] ?? false,
                $params['httponly'] ?? true
            );
        }

        session_destroy();
        self::$started = false;
    }

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function flash(string $key, ?string $message = null): ?string
    {
        self::start();

        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }

        if (!isset($_SESSION['_flash'][$key])) {
            return null;
        }

        $flash = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);

        return $flash;
    }

    public static function setFlash(string $key, string $message): void
    {
        self::flash($key, $message);
    }

    public static function getFlash(string $key): ?string
    {
        return self::flash($key);
    }

    public static function allFlash(): array
    {
        self::start();

        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);

        return $flash;
    }

    public static function login(array $user): void
    {
        self::regenerate();
        self::set('user', $user);
    }

    public static function logout(): void
    {
        self::destroy();
    }

    public static function isLoggedIn(): bool
    {
        self::start();

        return isset($_SESSION['user']) && is_array($_SESSION['user']);
    }

    public static function user()
    {
        self::start();

        return $_SESSION['user'] ?? null;
    }

    public static function userId(): ?int
    {
        $user = self::user();

        return isset($user['id']) ? (int) $user['id'] : null;
    }

    public static function role(): ?string
    {
        $user = self::user();

        return $user['role'] ?? null;
    }
}