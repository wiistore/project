<?php

declare(strict_types=1);

class Security
{
    public static function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function clean($value): string
    {
        return trim(strip_tags((string) $value));
    }

    public static function cleanArray(array $data): array
    {
        $clean = [];

        foreach ($data as $key => $value) {
            $clean[$key] = is_array($value) ? self::cleanArray($value) : self::clean($value);
        }

        return $clean;
    }

    public static function passwordHash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function passwordVerify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function token(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    public static function rupiah($value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    public static function safeFileName(string $fileName): string
    {
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);

        return trim($fileName, '_');
    }
}