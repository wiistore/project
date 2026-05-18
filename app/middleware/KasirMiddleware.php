<?php

declare(strict_types=1);

class KasirMiddleware
{
    public static function handle(): void
    {
        self::check();
    }

    public static function check(): void
    {
        // Cek akses kasir
        AuthMiddleware::check();

        if (Session::role() !== 'kasir') {
            Response::redirect('/403');
        }
    }
}