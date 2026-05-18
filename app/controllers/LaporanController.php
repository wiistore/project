<?php

declare(strict_types=1);

class LaporanController extends Controller
{
    private $laporanModel;

    public function __construct()
    {
        $this->laporanModel = $this->model('Laporan');
    }

    public function index(): void
    {
        // Cek akses
        $this->requireRole('admin');

        // Ambil filter
        $filter = $this->dateFilter();

        // Ambil data
        $summary = $this->laporanModel->summary($filter['tanggal_mulai'], $filter['tanggal_selesai']);
        $penjualanHarian = $this->laporanModel->penjualanHarian($filter['tanggal_mulai'], $filter['tanggal_selesai']);
        $barangTerlaris = $this->laporanModel->barangTerlaris($filter['tanggal_mulai'], $filter['tanggal_selesai'], 10);
        $metodePembayaran = $this->laporanModel->metodePembayaran($filter['tanggal_mulai'], $filter['tanggal_selesai']);
        $stokMenipis = $this->laporanModel->stokMenipis();

        // Tampilkan halaman
        $this->view('admin/laporan/index', [
            'title' => 'Laporan',
            'activeMenu' => 'laporan',
            'user' => Session::user(),
            'tanggalMulai' => $filter['tanggal_mulai'],
            'tanggalSelesai' => $filter['tanggal_selesai'],
            'summary' => $summary,
            'penjualanHarian' => $penjualanHarian,
            'barangTerlaris' => $barangTerlaris,
            'metodePembayaran' => $metodePembayaran,
            'stokMenipis' => $stokMenipis,
            'flash' => [
                'success' => Session::getFlash('success'),
                'error' => Session::getFlash('error'),
            ],
        ]);
    }

    public function penjualan(): void
    {
        // Cek akses
        $this->requireRole('admin');

        // Ambil filter
        $filter = $this->dateFilter();

        // Ambil data
        $summary = $this->laporanModel->summary($filter['tanggal_mulai'], $filter['tanggal_selesai']);
        $penjualanHarian = $this->laporanModel->penjualanHarian($filter['tanggal_mulai'], $filter['tanggal_selesai']);
        $penjualanKasir = $this->laporanModel->penjualanByKasir($filter['tanggal_mulai'], $filter['tanggal_selesai']);
        $metodePembayaran = $this->laporanModel->metodePembayaran($filter['tanggal_mulai'], $filter['tanggal_selesai']);

        // Tampilkan halaman
        $this->view('admin/laporan/penjualan', [
            'title' => 'Laporan Penjualan',
            'activeMenu' => 'laporan',
            'user' => Session::user(),
            'tanggalMulai' => $filter['tanggal_mulai'],
            'tanggalSelesai' => $filter['tanggal_selesai'],
            'summary' => $summary,
            'penjualanHarian' => $penjualanHarian,
            'penjualanKasir' => $penjualanKasir,
            'metodePembayaran' => $metodePembayaran,
        ]);
    }

    public function laba(): void
    {
        // Cek akses
        $this->requireRole('admin');

        // Ambil filter
        $filter = $this->dateFilter();

        // Ambil data
        $summary = $this->laporanModel->summary($filter['tanggal_mulai'], $filter['tanggal_selesai']);
        $penjualanHarian = $this->laporanModel->penjualanHarian($filter['tanggal_mulai'], $filter['tanggal_selesai']);
        $penjualanKasir = $this->laporanModel->penjualanByKasir($filter['tanggal_mulai'], $filter['tanggal_selesai']);
        $barangTerlaris = $this->laporanModel->barangTerlaris($filter['tanggal_mulai'], $filter['tanggal_selesai'], 20);

        // Tampilkan halaman
        $this->view('admin/laporan/laba', [
            'title' => 'Laporan Laba',
            'activeMenu' => 'laporan',
            'user' => Session::user(),
            'tanggalMulai' => $filter['tanggal_mulai'],
            'tanggalSelesai' => $filter['tanggal_selesai'],
            'summary' => $summary,
            'penjualanHarian' => $penjualanHarian,
            'penjualanKasir' => $penjualanKasir,
            'barangTerlaris' => $barangTerlaris,
        ]);
    }

    public function barangTerlaris(): void
    {
        // Cek akses
        $this->requireRole('admin');

        // Ambil filter
        $filter = $this->dateFilter();

        // Ambil data
        $barangTerlaris = $this->laporanModel->barangTerlaris($filter['tanggal_mulai'], $filter['tanggal_selesai'], 100);

        // Tampilkan halaman
        $this->view('admin/laporan/barang-terlaris', [
            'title' => 'Laporan Barang Terlaris',
            'activeMenu' => 'laporan',
            'user' => Session::user(),
            'tanggalMulai' => $filter['tanggal_mulai'],
            'tanggalSelesai' => $filter['tanggal_selesai'],
            'barangTerlaris' => $barangTerlaris,
        ]);
    }

    public function restock(): void
    {
        // Cek akses
        $this->requireRole('admin');

        // Ambil filter
        $filter = $this->dateFilter();

        // Ambil data
        $summary = $this->laporanModel->restockSummary($filter['tanggal_mulai'], $filter['tanggal_selesai']);
        $restockByBarang = $this->laporanModel->restockByBarang($filter['tanggal_mulai'], $filter['tanggal_selesai']);
        $restockBySupplier = $this->laporanModel->restockBySupplier($filter['tanggal_mulai'], $filter['tanggal_selesai']);

        // Tampilkan halaman
        $this->view('admin/laporan/restock', [
            'title' => 'Laporan Restock',
            'activeMenu' => 'laporan',
            'user' => Session::user(),
            'tanggalMulai' => $filter['tanggal_mulai'],
            'tanggalSelesai' => $filter['tanggal_selesai'],
            'summary' => $summary,
            'restockByBarang' => $restockByBarang,
            'restockBySupplier' => $restockBySupplier,
        ]);
    }
public function exportRingkasan(): void
{
    // Cek akses
    $this->requireRole('admin');

    $filter = $this->dateFilter();

    $summary = $this->laporanModel->summary($filter['tanggal_mulai'], $filter['tanggal_selesai']);
    $penjualanHarian = $this->laporanModel->penjualanHarian($filter['tanggal_mulai'], $filter['tanggal_selesai']);
    $barangTerlaris = $this->laporanModel->barangTerlaris($filter['tanggal_mulai'], $filter['tanggal_selesai'], 100);
    $metodePembayaran = $this->laporanModel->metodePembayaran($filter['tanggal_mulai'], $filter['tanggal_selesai']);
    $stokMenipis = $this->laporanModel->stokMenipis();

    $this->downloadExcel('laporan-ringkasan', function () use ($summary, $penjualanHarian, $barangTerlaris, $metodePembayaran, $stokMenipis) {
        echo '<h2>Ringkasan Laporan</h2>';
        echo '<table border="1">';
        echo '<tr><th>Total Transaksi</th><th>Total Penjualan</th><th>Total Modal</th><th>Total Laba</th></tr>';
        echo '<tr>';
        echo '<td>' . Security::e($summary['total_transaksi'] ?? 0) . '</td>';
        echo '<td>' . Security::e($summary['total_penjualan'] ?? 0) . '</td>';
        echo '<td>' . Security::e($summary['total_modal'] ?? 0) . '</td>';
        echo '<td>' . Security::e($summary['total_laba'] ?? 0) . '</td>';
        echo '</tr>';
        echo '</table><br>';

        $this->tablePenjualanHarian($penjualanHarian);
        echo '<br>';
        $this->tableMetodePembayaran($metodePembayaran);
        echo '<br>';
        $this->tableBarangTerlaris($barangTerlaris);
        echo '<br>';
        $this->tableStokMenipis($stokMenipis);
    });
}

public function exportPenjualan(): void
{
    // Cek akses
    $this->requireRole('admin');

    $filter = $this->dateFilter();

    $summary = $this->laporanModel->summary($filter['tanggal_mulai'], $filter['tanggal_selesai']);
    $penjualanHarian = $this->laporanModel->penjualanHarian($filter['tanggal_mulai'], $filter['tanggal_selesai']);
    $penjualanKasir = $this->laporanModel->penjualanByKasir($filter['tanggal_mulai'], $filter['tanggal_selesai']);
    $metodePembayaran = $this->laporanModel->metodePembayaran($filter['tanggal_mulai'], $filter['tanggal_selesai']);

    $this->downloadExcel('laporan-penjualan', function () use ($summary, $penjualanHarian, $penjualanKasir, $metodePembayaran) {
        echo '<h2>Ringkasan Penjualan</h2>';
        echo '<table border="1">';
        echo '<tr><th>Total Transaksi</th><th>Total Penjualan</th><th>Total Modal</th><th>Total Laba</th></tr>';
        echo '<tr>';
        echo '<td>' . Security::e($summary['total_transaksi'] ?? 0) . '</td>';
        echo '<td>' . Security::e($summary['total_penjualan'] ?? 0) . '</td>';
        echo '<td>' . Security::e($summary['total_modal'] ?? 0) . '</td>';
        echo '<td>' . Security::e($summary['total_laba'] ?? 0) . '</td>';
        echo '</tr>';
        echo '</table><br>';

        $this->tablePenjualanHarian($penjualanHarian);
        echo '<br>';
        $this->tablePenjualanKasir($penjualanKasir);
        echo '<br>';
        $this->tableMetodePembayaran($metodePembayaran);
    });
}

public function exportLaba(): void
{
    // Cek akses
    $this->requireRole('admin');

    $filter = $this->dateFilter();

    $summary = $this->laporanModel->summary($filter['tanggal_mulai'], $filter['tanggal_selesai']);
    $penjualanHarian = $this->laporanModel->penjualanHarian($filter['tanggal_mulai'], $filter['tanggal_selesai']);
    $barangTerlaris = $this->laporanModel->barangTerlaris($filter['tanggal_mulai'], $filter['tanggal_selesai'], 100);

    $this->downloadExcel('laporan-laba', function () use ($summary, $penjualanHarian, $barangTerlaris) {
        echo '<h2>Ringkasan Laba</h2>';
        echo '<table border="1">';
        echo '<tr><th>Total Transaksi</th><th>Total Penjualan</th><th>Total Modal</th><th>Total Laba</th></tr>';
        echo '<tr>';
        echo '<td>' . Security::e($summary['total_transaksi'] ?? 0) . '</td>';
        echo '<td>' . Security::e($summary['total_penjualan'] ?? 0) . '</td>';
        echo '<td>' . Security::e($summary['total_modal'] ?? 0) . '</td>';
        echo '<td>' . Security::e($summary['total_laba'] ?? 0) . '</td>';
        echo '</tr>';
        echo '</table><br>';

        $this->tablePenjualanHarian($penjualanHarian);
        echo '<br>';
        $this->tableBarangTerlaris($barangTerlaris);
    });
}

public function exportBarangTerlaris(): void
{
    // Cek akses
    $this->requireRole('admin');

    $filter = $this->dateFilter();

    $barangTerlaris = $this->laporanModel->barangTerlaris($filter['tanggal_mulai'], $filter['tanggal_selesai'], 100);

    $this->downloadExcel('laporan-barang-terlaris', function () use ($barangTerlaris) {
        $this->tableBarangTerlaris($barangTerlaris);
    });
}

public function exportRestock(): void
{
    // Cek akses
    $this->requireRole('admin');

    $filter = $this->dateFilter();

    $summary = $this->laporanModel->restockSummary($filter['tanggal_mulai'], $filter['tanggal_selesai']);
    $restockByBarang = $this->laporanModel->restockByBarang($filter['tanggal_mulai'], $filter['tanggal_selesai']);
    $restockBySupplier = $this->laporanModel->restockBySupplier($filter['tanggal_mulai'], $filter['tanggal_selesai']);

    $this->downloadExcel('laporan-restock', function () use ($summary, $restockByBarang, $restockBySupplier) {
        echo '<h2>Ringkasan Restock</h2>';
        echo '<table border="1">';
        echo '<tr><th>Total Restock</th><th>Total Qty Masuk</th><th>Total Nilai Restock</th></tr>';
        echo '<tr>';
        echo '<td>' . Security::e($summary['total_restock'] ?? 0) . '</td>';
        echo '<td>' . Security::e($summary['total_qty'] ?? 0) . '</td>';
        echo '<td>' . Security::e($summary['total_nilai'] ?? 0) . '</td>';
        echo '</tr>';
        echo '</table><br>';

        $this->tableRestockByBarang($restockByBarang);
        echo '<br>';
        $this->tableRestockBySupplier($restockBySupplier);
    });
}

private function downloadExcel(string $filename, callable $content): void
{
    // Export Excel sederhana
    $safeFilename = preg_replace('/[^a-zA-Z0-9-_]/', '-', $filename);
    $downloadName = $safeFilename . '-' . date('Ymd-His') . '.xls';

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo '<html>';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; margin-bottom: 16px; }
        th { background: #116530; color: #ffffff; font-weight: bold; }
        th, td { padding: 8px; border: 1px solid #999999; }
        h2 { color: #116530; }
    </style>';
    echo '</head>';
    echo '<body>';

    $content();

    echo '</body>';
    echo '</html>';

    exit;
}

private function tablePenjualanHarian(array $rows): void
{
    echo '<h2>Penjualan Harian</h2>';
    echo '<table border="1">';
    echo '<tr><th>Tanggal</th><th>Total Transaksi</th><th>Total Penjualan</th><th>Total Modal</th><th>Total Laba</th></tr>';

    foreach ($rows as $row) {
        echo '<tr>';
        echo '<td>' . Security::e($row['tanggal'] ?? '-') . '</td>';
        echo '<td>' . Security::e($row['total_transaksi'] ?? 0) . '</td>';
        echo '<td>' . Security::e($row['total_penjualan'] ?? 0) . '</td>';
        echo '<td>' . Security::e($row['total_modal'] ?? 0) . '</td>';
        echo '<td>' . Security::e($row['total_laba'] ?? 0) . '</td>';
        echo '</tr>';
    }

    echo '</table>';
}

private function tablePenjualanKasir(array $rows): void
{
    echo '<h2>Penjualan Per Kasir</h2>';
    echo '<table border="1">';
    echo '<tr><th>Kasir</th><th>Total Transaksi</th><th>Total Penjualan</th><th>Total Modal</th><th>Total Laba</th></tr>';

    foreach ($rows as $row) {
        echo '<tr>';
        echo '<td>' . Security::e($row['nama_kasir'] ?? '-') . '</td>';
        echo '<td>' . Security::e($row['total_transaksi'] ?? 0) . '</td>';
        echo '<td>' . Security::e($row['total_penjualan'] ?? 0) . '</td>';
        echo '<td>' . Security::e($row['total_modal'] ?? 0) . '</td>';
        echo '<td>' . Security::e($row['total_laba'] ?? 0) . '</td>';
        echo '</tr>';
    }

    echo '</table>';
}

private function tableMetodePembayaran(array $rows): void
{
    echo '<h2>Metode Pembayaran</h2>';
    echo '<table border="1">';
    echo '<tr><th>Metode</th><th>Total Transaksi</th><th>Total Penjualan</th></tr>';

    foreach ($rows as $row) {
        echo '<tr>';
        echo '<td>' . Security::e(strtoupper($row['metode_bayar'] ?? '-')) . '</td>';
        echo '<td>' . Security::e($row['total_transaksi'] ?? 0) . '</td>';
        echo '<td>' . Security::e($row['total_penjualan'] ?? 0) . '</td>';
        echo '</tr>';
    }

    echo '</table>';
}

private function tableBarangTerlaris(array $rows): void
{
    echo '<h2>Barang Terlaris</h2>';
    echo '<table border="1">';
    echo '<tr><th>No</th><th>Kode Barang</th><th>Barcode</th><th>Nama Barang</th><th>Kategori</th><th>Qty Terjual</th><th>Total Penjualan</th><th>Total Modal</th><th>Total Laba</th></tr>';

    foreach ($rows as $index => $row) {
        echo '<tr>';
        echo '<td>' . Security::e($index + 1) . '</td>';
        echo '<td>' . Security::e($row['kode_barang'] ?? '-') . '</td>';
        echo '<td>' . Security::e($row['barcode'] ?? '-') . '</td>';
        echo '<td>' . Security::e($row['nama_barang'] ?? '-') . '</td>';
        echo '<td>' . Security::e($row['nama_kategori'] ?? '-') . '</td>';
        echo '<td>' . Security::e($row['total_qty'] ?? 0) . ' ' . Security::e($row['satuan'] ?? '') . '</td>';
        echo '<td>' . Security::e($row['total_penjualan'] ?? 0) . '</td>';
        echo '<td>' . Security::e($row['total_modal'] ?? 0) . '</td>';
        echo '<td>' . Security::e($row['total_laba'] ?? 0) . '</td>';
        echo '</tr>';
    }

    echo '</table>';
}

private function tableStokMenipis(array $rows): void
{
    echo '<h2>Stok Menipis</h2>';
    echo '<table border="1">';
    echo '<tr><th>Kode Barang</th><th>Nama Barang</th><th>Kategori</th><th>Stok</th><th>Stok Minimum</th></tr>';

    foreach ($rows as $row) {
        echo '<tr>';
        echo '<td>' . Security::e($row['kode_barang'] ?? '-') . '</td>';
        echo '<td>' . Security::e($row['nama_barang'] ?? '-') . '</td>';
        echo '<td>' . Security::e($row['nama_kategori'] ?? '-') . '</td>';
        echo '<td>' . Security::e($row['stok'] ?? 0) . ' ' . Security::e($row['satuan'] ?? '') . '</td>';
        echo '<td>' . Security::e($row['stok_minimum'] ?? 0) . '</td>';
        echo '</tr>';
    }

    echo '</table>';
}

private function tableRestockByBarang(array $rows): void
{
    echo '<h2>Restock Per Barang</h2>';
    echo '<table border="1">';
    echo '<tr><th>Kode Barang</th><th>Nama Barang</th><th>Qty Masuk</th><th>Total Nilai</th><th>Rata Harga Beli</th></tr>';

    foreach ($rows as $row) {
        echo '<tr>';
        echo '<td>' . Security::e($row['kode_barang'] ?? '-') . '</td>';
        echo '<td>' . Security::e($row['nama_barang'] ?? '-') . '</td>';
        echo '<td>' . Security::e($row['total_qty'] ?? 0) . ' ' . Security::e($row['satuan'] ?? '') . '</td>';
        echo '<td>' . Security::e($row['total_nilai'] ?? 0) . '</td>';
        echo '<td>' . Security::e($row['rata_harga_beli'] ?? 0) . '</td>';
        echo '</tr>';
    }

    echo '</table>';
}

private function tableRestockBySupplier(array $rows): void
{
    echo '<h2>Restock Per Supplier</h2>';
    echo '<table border="1">';
    echo '<tr><th>Supplier</th><th>Total Restock</th><th>Total Qty</th><th>Total Nilai</th></tr>';

    foreach ($rows as $row) {
        echo '<tr>';
        echo '<td>' . Security::e($row['nama_supplier'] ?? '-') . '</td>';
        echo '<td>' . Security::e($row['total_restock'] ?? 0) . '</td>';
        echo '<td>' . Security::e($row['total_qty'] ?? 0) . '</td>';
        echo '<td>' . Security::e($row['total_nilai'] ?? 0) . '</td>';
        echo '</tr>';
    }

    echo '</table>';
}
    private function dateFilter(): array
    {
        // Filter tanggal
        $tanggalMulai = trim($_GET['tanggal_mulai'] ?? '');
        $tanggalSelesai = trim($_GET['tanggal_selesai'] ?? '');

        if ($tanggalMulai !== '' && !Validator::date($tanggalMulai)) {
            $tanggalMulai = '';
        }

        if ($tanggalSelesai !== '' && !Validator::date($tanggalSelesai)) {
            $tanggalSelesai = '';
        }

        if ($tanggalMulai !== '' && $tanggalSelesai !== '' && $tanggalMulai > $tanggalSelesai) {
            $temp = $tanggalMulai;
            $tanggalMulai = $tanggalSelesai;
            $tanggalSelesai = $temp;
        }

        return [
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
        ];
    }
}