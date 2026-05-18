<?php

declare(strict_types=1);

class RiwayatController extends Controller
{
    private $transaksiModel;
    private $detailModel;

    public function __construct()
    {
        $this->transaksiModel = $this->model('Transaksi');
        $this->detailModel = $this->model('DetailTransaksi');
    }

    public function adminIndex(): void
    {
        // Cek akses
        $this->requireRole('admin');

        // Ambil filter
        $tanggalMulai = trim($_GET['tanggal_mulai'] ?? '');
        $tanggalSelesai = trim($_GET['tanggal_selesai'] ?? '');

        // Ambil data
        if ($tanggalMulai !== '' || $tanggalSelesai !== '') {
            $transaksis = $this->transaksiModel->getByDateRange($tanggalMulai, $tanggalSelesai);
        } else {
            $transaksis = $this->transaksiModel->getWithUser(300);
        }

        // Hitung ringkasan ringan
        $summary = $this->makeSummary($transaksis);

        // Tampilkan halaman
        $this->view('admin/riwayat/index', [
            'title' => 'Riwayat Transaksi',
            'activeMenu' => 'riwayat',
            'user' => Session::user(),
            'transaksis' => $transaksis,
            'summary' => $summary,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'flash' => [
                'success' => Session::getFlash('success'),
                'error' => Session::getFlash('error'),
            ],
        ]);
    }

    public function adminDetail($id): void
    {
        // Cek akses
        $this->requireRole('admin');

        $id = (int) $id;
        $transaksi = $this->transaksiModel->findById($id);

        if (!$transaksi) {
            Session::setFlash('error', 'Transaksi tidak ditemukan.');
            $this->redirect('/admin/riwayat-transaksi');
        }

        // Ambil detail
        $items = $this->detailModel->getItemsWithBarang($id);
        $detailSummary = $this->detailModel->summaryByTransaksiId($id);

        // Tampilkan halaman
        $this->view('admin/riwayat/detail', [
            'title' => 'Detail Transaksi',
            'activeMenu' => 'riwayat',
            'user' => Session::user(),
            'transaksi' => $transaksi,
            'items' => $items,
            'detailTransaksi' => $items,
            'detailSummary' => $detailSummary,
        ]);
    }

    private function makeSummary(array $transaksis): array
    {
        // Ringkasan dari data yang sedang tampil
        $totalTransaksi = count($transaksis);
        $totalPenjualan = 0.0;
        $totalModal = 0.0;
        $totalLaba = 0.0;

        foreach ($transaksis as $transaksi) {
            $totalPenjualan += (float) ($transaksi['total_jual'] ?? 0);
            $totalModal += (float) ($transaksi['total_beli'] ?? 0);
            $totalLaba += (float) ($transaksi['total_laba'] ?? 0);
        }

        return [
            'total_transaksi' => $totalTransaksi,
            'total_penjualan' => $totalPenjualan,
            'total_modal' => $totalModal,
            'total_laba' => $totalLaba,
        ];
    }
}