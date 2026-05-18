<?php

$reportType = $reportType ?? 'penjualan';

$tanggalMulai = $tanggalMulai ?? '';
$tanggalSelesai = $tanggalSelesai ?? '';

$summary = $summary ?? [];
$penjualanHarian = $penjualanHarian ?? [];
$penjualanKasir = $penjualanKasir ?? [];
$metodePembayaran = $metodePembayaran ?? [];
$barangTerlaris = $barangTerlaris ?? [];
$restockByBarang = $restockByBarang ?? [];
$restockBySupplier = $restockBySupplier ?? [];

$config = [
    'penjualan' => [
        'title' => 'Laporan Penjualan',
        'desc' => 'Ringkasan penjualan harian, kasir, dan metode pembayaran.',
        'action' => '/admin/laporan/penjualan',
    ],
    'laba' => [
        'title' => 'Laporan Laba',
        'desc' => 'Pantau modal, penjualan, dan laba dari transaksi.',
        'action' => '/admin/laporan/laba',
    ],
    'barang-terlaris' => [
        'title' => 'Laporan Barang Terlaris',
        'desc' => 'Daftar barang paling banyak terjual.',
        'action' => '/admin/laporan/barang-terlaris',
    ],
    'restock' => [
        'title' => 'Laporan Restock',
        'desc' => 'Ringkasan stok masuk dari supplier.',
        'action' => '/admin/laporan/restock',
    ],
];

$page = $config[$reportType] ?? $config['penjualan'];
$title = $title ?? $page['title'];

    $exportMap = [
        'penjualan' => '/admin/laporan/export/penjualan',
        'laba' => '/admin/laporan/export/laba',
        'barang-terlaris' => '/admin/laporan/export/barang-terlaris',
        'restock' => '/admin/laporan/export/restock',
    ];

    $exportUrl = $exportMap[$reportType] ?? '/admin/laporan/export/ringkasan';
    $exportUrl .= '?tanggal_mulai=' . urlencode($tanggalMulai) . '&tanggal_selesai=' . urlencode($tanggalSelesai)
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::e($title) ?> - <?= Security::e(APP_NAME) ?></title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f6faf7; color: #172033; }
        .page { max-width: 1280px; margin: 0 auto; padding: 28px; }
        .top { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 18px; }
        h1 { margin: 0 0 6px; color: #116530; }
        h2 { margin: 0 0 14px; color: #116530; font-size: 20px; }
        p { margin: 0; color: #667085; }
        .btn { display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 8px; padding: 9px 12px; font-weight: 700; text-decoration: none; cursor: pointer; font-size: 14px; }
        .btn-green { background: #116530; color: #fff; }
        .btn-soft { background: #eaf7ee; color: #116530; }
        .btn-gray { background: #f2f4f7; color: #344054; }
        .nav { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
        .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin: 20px 0; }
        .card, .box, .filter-box { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; box-shadow: 0 10px 26px rgba(15, 23, 42, .06); }
        .card { padding: 16px; }
        .card span { display: block; font-size: 13px; color: #667085; margin-bottom: 8px; }
        .card strong { display: block; font-size: 24px; color: #116530; }
        .filter-box { padding: 18px; margin-bottom: 16px; }
        .filter-form { display: grid; grid-template-columns: 1fr 1fr auto auto; gap: 12px; align-items: end; }
        label { display: block; margin-bottom: 7px; font-size: 14px; font-weight: 700; }
        input { width: 100%; height: 40px; padding: 0 12px; border: 1px solid #d0d5dd; border-radius: 8px; box-sizing: border-box; font: inherit; }
        .grid-two { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .box { padding: 18px; overflow-x: auto; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; min-width: 780px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eef2f7; font-size: 14px; vertical-align: middle; }
        th { background: #eef8f1; color: #116530; }
        .profit { color: #116530; font-weight: 800; }
        .empty { text-align: center; padding: 28px 12px; color: #667085; }
        .badge { display: inline-block; padding: 5px 9px; border-radius: 999px; font-size: 12px; font-weight: 800; }
        .method-cash { color: #166534; background: #dcfce7; }
        .method-qris { color: #075985; background: #e0f2fe; }
        .method-transfer { color: #7c2d12; background: #ffedd5; }
        .method-ewallet { color: #6b21a8; background: #f3e8ff; }

        @media print {
            .top, .nav, .filter-box, .no-print { display: none; }
            body { background: #fff; }
            .page { max-width: none; padding: 0; }
            .card, .box { box-shadow: none; border-color: #ddd; }
        }

        @media (max-width: 960px) {
            .summary, .grid-two { grid-template-columns: 1fr 1fr; }
            .filter-form { grid-template-columns: 1fr; }
            .top { align-items: flex-start; flex-direction: column; }
        }

        @media (max-width: 560px) {
            .summary, .grid-two { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="top">
            <div>
                <h1><?= Security::e($page['title']) ?></h1>
                <p><?= Security::e($page['desc']) ?></p>
            </div>

        <a href="<?= Security::e(base_url($exportUrl)) ?>" class="btn btn-green no-print">Export Excel      </a>
        </div>

        <div class="nav no-print">
            <a class="btn btn-soft" href="<?= Security::e(base_url('/admin/laporan')) ?>">Ringkasan</a>
            <a class="btn btn-soft" href="<?= Security::e(base_url('/admin/laporan/penjualan')) ?>">Penjualan</a>
            <a class="btn btn-soft" href="<?= Security::e(base_url('/admin/laporan/laba')) ?>">Laba</a>
            <a class="btn btn-soft" href="<?= Security::e(base_url('/admin/laporan/barang-terlaris')) ?>">Barang Terlaris</a>
            <a class="btn btn-soft" href="<?= Security::e(base_url('/admin/laporan/restock')) ?>">Restock</a>
            <a class="btn btn-gray" href="<?= Security::e(base_url('/admin/dashboard')) ?>">Dashboard</a>
        </div>

        <section class="filter-box no-print">
            <form action="<?= Security::e(base_url($page['action'])) ?>" method="GET" class="filter-form">
                <div>
                    <label for="tanggal_mulai">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?= Security::e($tanggalMulai) ?>">
                </div>

                <div>
                    <label for="tanggal_selesai">Tanggal Selesai</label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="<?= Security::e($tanggalSelesai) ?>">
                </div>

                <button type="submit" class="btn btn-green">Filter</button>
                <a href="<?= Security::e(base_url($page['action'])) ?>" class="btn btn-gray">Reset</a>
            </form>
        </section>

        <?php if ($reportType !== 'barang-terlaris' && $reportType !== 'restock'): ?>
            <section class="summary">
                <div class="card">
                    <span>Total Transaksi</span>
                    <strong><?= Security::e($summary['total_transaksi'] ?? 0) ?></strong>
                </div>

                <div class="card">
                    <span>Total Penjualan</span>
                    <strong><?= Security::e(Security::rupiah($summary['total_penjualan'] ?? 0)) ?></strong>
                </div>

                <div class="card">
                    <span>Total Modal</span>
                    <strong><?= Security::e(Security::rupiah($summary['total_modal'] ?? 0)) ?></strong>
                </div>

                <div class="card">
                    <span>Total Laba</span>
                    <strong><?= Security::e(Security::rupiah($summary['total_laba'] ?? 0)) ?></strong>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($reportType === 'restock'): ?>
            <section class="summary">
                <div class="card">
                    <span>Total Restock</span>
                    <strong><?= Security::e($summary['total_restock'] ?? 0) ?></strong>
                </div>

                <div class="card">
                    <span>Total Qty Masuk</span>
                    <strong><?= Security::e($summary['total_qty'] ?? 0) ?></strong>
                </div>

                <div class="card">
                    <span>Total Nilai Restock</span>
                    <strong><?= Security::e(Security::rupiah($summary['total_nilai'] ?? 0)) ?></strong>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($reportType === 'penjualan'): ?>
            <section class="grid-two">
                <div class="box">
                    <h2>Penjualan Harian</h2>

                    <?php if (empty($penjualanHarian)): ?>
                        <div class="empty">Belum ada data penjualan.</div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Transaksi</th>
                                    <th>Penjualan</th>
                                    <th>Laba</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($penjualanHarian as $row): ?>
                                    <tr>
                                        <td><?= Security::e($row['tanggal'] ?? '-') ?></td>
                                        <td><?= Security::e($row['total_transaksi'] ?? 0) ?></td>
                                        <td><?= Security::e(Security::rupiah($row['total_penjualan'] ?? 0)) ?></td>
                                        <td class="profit"><?= Security::e(Security::rupiah($row['total_laba'] ?? 0)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="box">
                    <h2>Penjualan Per Kasir</h2>

                    <?php if (empty($penjualanKasir)): ?>
                        <div class="empty">Belum ada data kasir.</div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Kasir</th>
                                    <th>Transaksi</th>
                                    <th>Penjualan</th>
                                    <th>Laba</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($penjualanKasir as $row): ?>
                                    <tr>
                                        <td><?= Security::e($row['nama_kasir'] ?? '-') ?></td>
                                        <td><?= Security::e($row['total_transaksi'] ?? 0) ?></td>
                                        <td><?= Security::e(Security::rupiah($row['total_penjualan'] ?? 0)) ?></td>
                                        <td class="profit"><?= Security::e(Security::rupiah($row['total_laba'] ?? 0)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </section>

            <section class="box">
                <h2>Metode Pembayaran</h2>

                <?php if (empty($metodePembayaran)): ?>
                    <div class="empty">Belum ada data pembayaran.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Metode</th>
                                <th>Transaksi</th>
                                <th>Penjualan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($metodePembayaran as $row): ?>
                                <?php $method = $row['metode_bayar'] ?? 'cash'; ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= Security::e('method-' . $method) ?>">
                                            <?= Security::e(strtoupper($method)) ?>
                                        </span>
                                    </td>
                                    <td><?= Security::e($row['total_transaksi'] ?? 0) ?></td>
                                    <td><?= Security::e(Security::rupiah($row['total_penjualan'] ?? 0)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if ($reportType === 'laba'): ?>
            <section class="box">
                <h2>Laba Harian</h2>

                <?php if (empty($penjualanHarian)): ?>
                    <div class="empty">Belum ada data laba.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Penjualan</th>
                                <th>Modal</th>
                                <th>Laba</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($penjualanHarian as $row): ?>
                                <tr>
                                    <td><?= Security::e($row['tanggal'] ?? '-') ?></td>
                                    <td><?= Security::e(Security::rupiah($row['total_penjualan'] ?? 0)) ?></td>
                                    <td><?= Security::e(Security::rupiah($row['total_modal'] ?? 0)) ?></td>
                                    <td class="profit"><?= Security::e(Security::rupiah($row['total_laba'] ?? 0)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <section class="box">
                <h2>Laba Per Barang</h2>

                <?php if (empty($barangTerlaris)): ?>
                    <div class="empty">Belum ada data barang.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Qty</th>
                                <th>Penjualan</th>
                                <th>Modal</th>
                                <th>Laba</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($barangTerlaris as $row): ?>
                                <tr>
                                    <td><?= Security::e($row['nama_barang'] ?? '-') ?></td>
                                    <td><?= Security::e($row['total_qty'] ?? 0) ?> <?= Security::e($row['satuan'] ?? '') ?></td>
                                    <td><?= Security::e(Security::rupiah($row['total_penjualan'] ?? 0)) ?></td>
                                    <td><?= Security::e(Security::rupiah($row['total_modal'] ?? 0)) ?></td>
                                    <td class="profit"><?= Security::e(Security::rupiah($row['total_laba'] ?? 0)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if ($reportType === 'barang-terlaris'): ?>
            <section class="box">
                <h2>Barang Terlaris</h2>

                <?php if (empty($barangTerlaris)): ?>
                    <div class="empty">Belum ada data barang terjual.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Barang</th>
                                <th>Kategori</th>
                                <th>Qty</th>
                                <th>Penjualan</th>
                                <th>Laba</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($barangTerlaris as $index => $row): ?>
                                <tr>
                                    <td><?= Security::e($index + 1) ?></td>
                                    <td><?= Security::e($row['kode_barang'] ?? '-') ?></td>
                                    <td><?= Security::e($row['nama_barang'] ?? '-') ?></td>
                                    <td><?= Security::e($row['nama_kategori'] ?? '-') ?></td>
                                    <td><?= Security::e($row['total_qty'] ?? 0) ?> <?= Security::e($row['satuan'] ?? '') ?></td>
                                    <td><?= Security::e(Security::rupiah($row['total_penjualan'] ?? 0)) ?></td>
                                    <td class="profit"><?= Security::e(Security::rupiah($row['total_laba'] ?? 0)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if ($reportType === 'restock'): ?>
            <section class="grid-two">
                <div class="box">
                    <h2>Restock Per Barang</h2>

                    <?php if (empty($restockByBarang)): ?>
                        <div class="empty">Belum ada data restock barang.</div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th>Qty</th>
                                    <th>Total Nilai</th>
                                    <th>Rata Harga Beli</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($restockByBarang as $row): ?>
                                    <tr>
                                        <td>
                                            <strong><?= Security::e($row['nama_barang'] ?? '-') ?></strong>
                                            <br>
                                            <small><?= Security::e($row['kode_barang'] ?? '-') ?></small>
                                        </td>
                                        <td><?= Security::e($row['total_qty'] ?? 0) ?> <?= Security::e($row['satuan'] ?? '') ?></td>
                                        <td><?= Security::e(Security::rupiah($row['total_nilai'] ?? 0)) ?></td>
                                        <td><?= Security::e(Security::rupiah($row['rata_harga_beli'] ?? 0)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="box">
                    <h2>Restock Per Supplier</h2>

                    <?php if (empty($restockBySupplier)): ?>
                        <div class="empty">Belum ada data restock supplier.</div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Supplier</th>
                                    <th>Restock</th>
                                    <th>Qty</th>
                                    <th>Total Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($restockBySupplier as $row): ?>
                                    <tr>
                                        <td><?= Security::e($row['nama_supplier'] ?? '-') ?></td>
                                        <td><?= Security::e($row['total_restock'] ?? 0) ?></td>
                                        <td><?= Security::e($row['total_qty'] ?? 0) ?></td>
                                        <td><?= Security::e(Security::rupiah($row['total_nilai'] ?? 0)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>