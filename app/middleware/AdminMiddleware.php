<?php

declare(strict_types=1);

class AdminMiddleware
{
    public static function handle(): void
    {
        self::check();
    }

    public static function check(): void
    {
        // Cek akses admin
        AuthMiddleware::check();

        if (Session::role() !== 'admin') {
            Response::redirect('/403');
        }
    }
}