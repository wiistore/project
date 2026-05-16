<?php

declare(strict_types=1);

// Config aplikasi
date_default_timezone_set('Asia/Jakarta');

define('APP_NAME', 'Kopsis POS');
define('APP_TIMEZONE', 'Asia/Jakarta');
define('APP_DEBUG', true);

// Path utama
define('ROOT_PATH', dirname(__DIR__, 2));
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'app');
define('PUBLIC_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'public');
define('DATABASE_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'database');
define('STORAGE_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'storage');

// Base URL dinamis
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');

$basePath = dirname($scriptName);
$basePath = preg_replace('#/public$#', '', $basePath);
$basePath = ($basePath === '/' || $basePath === '.' || $basePath === '\\') ? '' : rtrim($basePath, '/');

define('BASE_URL', $protocol . '://' . $host . $basePath);
define('ASSET_URL', BASE_URL . '/public/assets');

// Mode error
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

// Helper URL
function base_url(string $path = ''): string
{
    $path = ltrim($path, '/');

    return $path === '' ? BASE_URL : BASE_URL . '/' . $path;
}

function asset_url(string $path = ''): string
{
    $path = ltrim($path, '/');

    return $path === '' ? ASSET_URL : ASSET_URL . '/' . $path;
}