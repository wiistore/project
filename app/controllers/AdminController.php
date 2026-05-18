<?php

declare(strict_types=1);

class AdminController extends Controller
{
    private $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = $this->model('Dashboard');
    }

    public function dashboard(): void
    {
        $this->requireRole('admin');

        $summary = $this->dashboardModel->adminSummary();

        $this->view('admin/dashboard', [
            'title' => 'Dashboard Admin',
            'activeMenu' => 'dashboard',
            'user' => Session::user(),

            'dashboard' => $summary,
            'totalBarang' => $summary['total_barang'] ?? 0,
            'totalTransaksiHariIni' => $summary['total_transaksi_hari_ini'] ?? 0,
            'totalPenjualanHariIni' => $summary['penjualan_hari_ini'] ?? 0,
            'stokMenipis' => $summary['stok_menipis'] ?? 0,
            'transaksiTerbaru' => $summary['transaksi_terbaru'] ?? [],

            'chartPenjualan7Hari' => $summary['chart_penjualan_7_hari'] ?? [
                'labels' => [],
                'values' => [],
            ],
            'chartTopBarang' => $summary['chart_top_barang'] ?? [
                'labels' => [],
                'values' => [],
            ],
            'chartStatusStok' => $summary['chart_status_stok'] ?? [
                'labels' => [],
                'values' => [],
            ],
        ]);
    }
}