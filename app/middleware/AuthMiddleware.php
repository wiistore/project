<?php

declare(strict_types=1);

class AuthMiddleware
{
    public static function handle(): void
    {
        self::check();
    }

    public static function check(): void
    {
        // Cek login
        if (!Session::isLoggedIn()) {
            Response::redirect('/login');
        }
    }

    public static function guest(): void
    {
        if (!Session::isLoggedIn()) {
            return;
        }

        $role = Session::role();

        if ($role === 'admin') {
            Response::redirect('/admin/dashboard');
        }

        if ($role === 'kasir') {
            Response::redirect('/kasir/dashboard');
        }

        Session::logout();
        Response::redirect('/login');
    }
}