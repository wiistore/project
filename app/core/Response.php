<?php

declare(strict_types=1);

class Response
{
    public static function redirect(string $url): void
    {
        if (!preg_match('#^https?://#', $url)) {
            $url = base_url($url);
        }

        header('Location: ' . $url);
        exit;
    }

    public static function back(): void
    {
        $fallback = base_url('/');
        $target = $_SERVER['HTTP_REFERER'] ?? $fallback;

        header('Location: ' . $target);
        exit;
    }

    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function abort(int $statusCode = 404, string $message = ''): void
    {
        http_response_code($statusCode);

        $viewFile = APP_PATH . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . 'errors'
            . DIRECTORY_SEPARATOR . $statusCode . '.php';

        if (file_exists($viewFile)) {
            require $viewFile;
            exit;
        }

        self::plainError($statusCode, $message);
    }

    private static function plainError(int $statusCode, string $message): void
    {
        $title = self::statusText($statusCode);
        $safeTitle = Security::e($title);
        $safeMessage = Security::e($message ?: $title);

        echo '<!DOCTYPE html>';
        echo '<html lang="id">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>' . $safeTitle . '</title>';
        echo '<style>';
        echo 'body{font-family:Arial,sans-serif;background:#f3f4f6;color:#111827;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}';
        echo '.box{background:white;padding:28px;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.08);max-width:460px;text-align:center}';
        echo 'h1{margin:0 0 8px;font-size:42px;color:#166534}';
        echo 'p{margin:0 0 18px;color:#4b5563}';
        echo 'a{display:inline-block;background:#166534;color:white;text-decoration:none;padding:10px 16px;border-radius:8px}';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<div class="box">';
        echo '<h1>' . Security::e((string) $statusCode) . '</h1>';
        echo '<p>' . $safeMessage . '</p>';
        echo '<a href="' . Security::e(base_url('/')) . '">Kembali</a>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
        exit;
    }

    private static function statusText(int $statusCode): string
    {
        $texts = [
            400 => 'Bad Request',
            403 => 'Akses Ditolak',
            404 => 'Halaman Tidak Ditemukan',
            405 => 'Method Tidak Didukung',
            500 => 'Server Error',
        ];

        return $texts[$statusCode] ?? 'Error';
    }
}