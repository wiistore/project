<?php
if (!function_exists('pdf_e')) {
    function pdf_e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('pdf_money')) {
    function pdf_money(mixed $value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}

if (!function_exists('pdf_date')) {
    function pdf_date(mixed $value): string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return '-';
        }

        $time = strtotime($raw);

        if ($time === false) {
            return $raw;
        }

        return date('d/m/Y H:i', $time);
    }
}

$transaksi = $transaksi ?? [];
$detailTransaksi = $detailTransaksi ?? ($items ?? []);
$user = $user ?? [];

$kodeTransaksi = (string) ($transaksi['kode_transaksi'] ?? $transaksi['kode'] ?? '-');
$tanggalRaw = $transaksi['tanggal'] ?? $transaksi['created_at'] ?? date('Y-m-d H:i:s');
$tanggal = pdf_date($tanggalRaw);

$kasir = $transaksi['nama_kasir']
    ?? $transaksi['kasir']
    ?? $transaksi['nama_user']
    ?? $user['nama_lengkap']
    ?? $user['nama']
    ?? $user['username']
    ?? 'Kasir';

$metodeBayar = strtoupper((string) ($transaksi['metode_bayar'] ?? $transaksi['metode_pembayaran'] ?? '-'));
$totalJual = (float) ($transaksi['total_jual'] ?? $transaksi['total'] ?? $transaksi['grand_total'] ?? 0);
$nominalBayar = (float) ($transaksi['nominal_bayar'] ?? $totalJual);
$kembalian = (float) ($transaksi['kembalian'] ?? max(0, $nominalBayar - $totalJual));

$totalQty = 0;

foreach ($detailTransaksi as $row) {
    $totalQty += (int) ($row['qty'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk <?= pdf_e($kodeTransaksi) ?></title>

    <style>
        @page {
            margin: 8px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        .receipt {
            width: 100%;
        }

        .brand {
            text-align: center;
        }

        .brand h1 {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .3px;
        }

        .brand strong {
            display: block;
            margin-top: 2px;
            font-size: 10px;
        }

        .brand p {
            margin: 3px 0 0;
            color: #555;
            font-size: 9px;
        }

        .divider {
            margin: 8px 0;
            border-top: 1px dashed #999;
        }

        .meta div,
        .summary div {
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }

        .meta span,
        .summary span {
            display: table-cell;
            color: #555;
        }

        .meta strong,
        .summary strong {
            display: table-cell;
            text-align: right;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding-bottom: 4px;
            border-bottom: 1px dashed #999;
            text-align: left;
            font-size: 9px;
        }

        td {
            padding: 5px 0;
            border-bottom: 1px dotted #ddd;
            vertical-align: top;
        }

        td strong {
            display: block;
            font-size: 10px;
        }

        td small {
            display: block;
            color: #555;
            font-size: 8px;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .footer {
            text-align: center;
        }

        .footer strong {
            display: block;
            margin-bottom: 3px;
            font-size: 10px;
        }

        .footer p {
            margin: 0;
            color: #555;
            font-size: 8px;
        }
    </style>
</head>

<body>
    <div class="receipt">
        <div class="brand">
            <h1>LAB KEWIRAUSAHAAN</h1>
            <strong>MTSN 8 BANYUWANGI</strong>
            <p>Jl. Pendidikan • Banyuwangi</p>
        </div>

        <div class="divider"></div>

        <div class="meta">
            <div>
                <span>No</span>
                <strong><?= pdf_e($kodeTransaksi) ?></strong>
            </div>

            <div>
                <span>Tanggal</span>
                <strong><?= pdf_e($tanggal) ?></strong>
            </div>

            <div>
                <span>Kasir</span>
                <strong><?= pdf_e(ucwords((string) $kasir)) ?></strong>
            </div>

            <div>
                <span>Metode</span>
                <strong><?= pdf_e($metodeBayar) ?></strong>
            </div>
        </div>

        <div class="divider"></div>

        <table>
            <thead>
                <tr>
                    <th>Barang</th>
                    <th class="center">Qty</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($detailTransaksi)): ?>
                    <tr>
                        <td colspan="3" class="center">Tidak ada item.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($detailTransaksi as $item): ?>
                        <?php
                        $namaBarang = (string) ($item['nama_barang'] ?? $item['nama'] ?? $item['barang'] ?? '-');
                        $kodeBarang = (string) ($item['kode_barang'] ?? $item['kode'] ?? '');
                        $qty = (int) ($item['qty'] ?? 0);
                        $hargaJual = (float) ($item['harga_jual'] ?? $item['harga'] ?? 0);
                        $subtotal = (float) ($item['subtotal_jual'] ?? $item['subtotal'] ?? ($qty * $hargaJual));
                        ?>

                        <tr>
                            <td>
                                <strong><?= pdf_e($namaBarang) ?></strong>

                                <?php if ($kodeBarang !== ''): ?>
                                    <small><?= pdf_e($kodeBarang) ?></small>
                                <?php endif; ?>

                                <small><?= pdf_e((string) $qty) ?> x <?= pdf_e(pdf_money($hargaJual)) ?></small>
                            </td>

                            <td class="center"><?= pdf_e((string) $qty) ?></td>
                            <td class="right"><?= pdf_e(pdf_money($subtotal)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="divider"></div>

        <div class="summary">
            <div>
                <span>Total Item</span>
                <strong><?= pdf_e((string) $totalQty) ?></strong>
            </div>

            <div>
                <span>Total</span>
                <strong><?= pdf_e(pdf_money($totalJual)) ?></strong>
            </div>

            <div>
                <span>Bayar</span>
                <strong><?= pdf_e(pdf_money($nominalBayar)) ?></strong>
            </div>

            <div>
                <span>Kembalian</span>
                <strong><?= pdf_e(pdf_money($kembalian)) ?></strong>
            </div>
        </div>

        <div class="divider"></div>

        <div class="footer">
            <strong>Terima kasih</strong>
            <p>Barang yang sudah dibeli tidak dapat dikembalikan tanpa persetujuan admin.</p>
        </div>
    </div>
</body>
</html>