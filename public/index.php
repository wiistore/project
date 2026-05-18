<?php

declare(strict_types=1);

// entry point app, semua request masuk lewat sini terus di-route ke controller masing2
// jangan taro logic berat di file ini, cukup wiring doang

// load composer autoload kalo ada folder vendor
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// config dulu (path, db creds, dll), wajib paling awal
// Load config
require_once dirname(__DIR__) . '/app/config/config.php';

// core class manual require, biar gak butuh autoloader
// Load core
require_once APP_PATH . '/core/Session.php';
require_once APP_PATH . '/core/Security.php';
require_once APP_PATH . '/core/Response.php';
require_once APP_PATH . '/core/Validator.php';
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/Router.php';

// scan folder middleware, semua file .php di-include
// Load middleware kalau ada
foreach (glob(APP_PATH . '/middleware/*.php') ?: [] as $middlewareFile) {
    require_once $middlewareFile;
}

// session harus dijalanin sebelum routing biar Session::isLoggedIn() bisa kebaca
// Mulai session
Session::start();

// router siap nerima definisi route di bawah
// Init router
$router = new Router();

// === root === redirect ke dashboard sesuai role, kalo blm login lempar ke /login
/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/

$router->get('/', function (): void {
    if (!Session::isLoggedIn()) {
        Response::redirect('/login');
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
});

// === routes auth === login/logout aja, simple
/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->post('/logout', 'AuthController@logout');

// === routes dashboard === landing page tiap role
/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/kasir/dashboard', 'KasirController@dashboard');

// === routes profil kasir === edit profil + ganti password
/*
|--------------------------------------------------------------------------
| Profil kasir
|--------------------------------------------------------------------------
*/

$router->get('/kasir/profil', 'KasirController@profil');
$router->post('/kasir/profil/update', 'KasirController@updateProfil');
$router->post('/kasir/profil/password', 'KasirController@updatePassword');

// === routes kategori === CRUD admin only
/*
|--------------------------------------------------------------------------
| Kategori
|--------------------------------------------------------------------------
*/

$router->get('/admin/kategori', 'KategoriController@index');
$router->get('/admin/kategori/create', 'KategoriController@create');
$router->post('/admin/kategori/store', 'KategoriController@store');
$router->get('/admin/kategori/edit/{id}', 'KategoriController@edit');
$router->post('/admin/kategori/update/{id}', 'KategoriController@update');
$router->post('/admin/kategori/delete/{id}', 'KategoriController@delete');

// === routes barang === CRUD + halaman detail
/*
|--------------------------------------------------------------------------
| Barang
|--------------------------------------------------------------------------
*/

$router->get('/admin/barang', 'BarangController@index');
$router->get('/admin/barang/create', 'BarangController@create');
$router->post('/admin/barang/store', 'BarangController@store');
$router->get('/admin/barang/detail/{id}', 'BarangController@detail');
$router->get('/admin/barang/edit/{id}', 'BarangController@edit');
$router->post('/admin/barang/update/{id}', 'BarangController@update');
$router->post('/admin/barang/delete/{id}', 'BarangController@delete');

// === routes supplier === CRUD admin only
/*
|--------------------------------------------------------------------------
| Supplier
|--------------------------------------------------------------------------
*/

$router->get('/admin/supplier', 'SupplierController@index');
$router->get('/admin/supplier/create', 'SupplierController@create');
$router->post('/admin/supplier/store', 'SupplierController@store');
$router->get('/admin/supplier/edit/{id}', 'SupplierController@edit');
$router->post('/admin/supplier/update/{id}', 'SupplierController@update');
$router->post('/admin/supplier/delete/{id}', 'SupplierController@delete');

// === routes restock === nambah stok barang dari supplier
/*
|--------------------------------------------------------------------------
| Restock
|--------------------------------------------------------------------------
*/

$router->get('/admin/restock', 'RestockController@index');
$router->get('/admin/restock/create', 'RestockController@create');
$router->post('/admin/restock/store', 'RestockController@store');

// === routes user === manage akun kasir, admin yg pegang
/*
|--------------------------------------------------------------------------
| User kasir
|--------------------------------------------------------------------------
*/

$router->get('/admin/user', 'UserController@index');
$router->get('/admin/user/create', 'UserController@create');
$router->post('/admin/user/store', 'UserController@store');
$router->get('/admin/user/edit/{id}', 'UserController@edit');
$router->post('/admin/user/update/{id}', 'UserController@update');
$router->get('/admin/user/reset-password/{id}', 'UserController@resetPassword');
$router->post('/admin/user/reset-password/{id}', 'UserController@updatePassword');
$router->post('/admin/user/delete/{id}', 'UserController@delete');

// === routes transaksi === inti app, ada versi admin & kasir + struk + pdf
/* |--------------------------------------------------------------------------
| Transaksi Routes
|-------------------------------------------------------------------------- */
$router->get('/admin/transaksi', 'TransaksiController@adminIndex');
$router->get('/kasir/transaksi', 'TransaksiController@kasirIndex');
$router->post('/transaksi/store', 'TransaksiController@store');

$router->get('/admin/transaksi/struk/{id}', 'TransaksiController@adminStruk');
$router->get('/kasir/transaksi/struk/{id}', 'TransaksiController@kasirStruk');

$router->get('/admin/transaksi/pdf/{id}', 'TransaksiController@adminPdf');
$router->get('/kasir/transaksi/pdf/{id}', 'TransaksiController@kasirPdf');
/*
|--------------------------------------------------------------------------
| Riwayat transaksi admin
|--------------------------------------------------------------------------
*/

$router->get('/admin/riwayat-transaksi', 'RiwayatController@adminIndex');
$router->get('/admin/riwayat-transaksi/detail/{id}', 'RiwayatController@adminDetail');

/* |--------------------------------------------------------------------------
| Laporan Routes
|-------------------------------------------------------------------------- */
$router->get('/admin/laporan', 'LaporanController@index');
$router->get('/admin/laporan/penjualan', 'LaporanController@penjualan');
$router->get('/admin/laporan/laba', 'LaporanController@laba');
$router->get('/admin/laporan/barang-terlaris', 'LaporanController@barangTerlaris');
$router->get('/admin/laporan/restock', 'LaporanController@restock');

// Export laporan
$router->get('/admin/laporan/export/ringkasan', 'LaporanController@exportRingkasan');
$router->get('/admin/laporan/export/penjualan', 'LaporanController@exportPenjualan');
$router->get('/admin/laporan/export/laba', 'LaporanController@exportLaba');
$router->get('/admin/laporan/export/barang-terlaris', 'LaporanController@exportBarangTerlaris');
$router->get('/admin/laporan/export/restock', 'LaporanController@exportRestock');
/*
|--------------------------------------------------------------------------
| Error
|--------------------------------------------------------------------------
*/

$router->get('/403', function (): void {
    Response::abort(403, 'Akses ditolak.');
});

$router->get('/404', function (): void {
    Response::abort(404, 'Halaman tidak ditemukan.');
});

// Jalankan aplikasi
try {
    $router->run();
} catch (Throwable $error) {
    error_log($error->getMessage());

    $message = APP_DEBUG
        ? $error->getMessage() . ' di ' . $error->getFile() . ':' . $error->getLine()
        : 'Terjadi kesalahan pada server.';

    Response::abort(500, $message);
}