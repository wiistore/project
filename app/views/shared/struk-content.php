<?php
$transaksi = $transaksi ?? [];
$items = $items ?? ($detailTransaksi ?? []);
$user = $user ?? [];

$kodeTransaksi = (string) ($transaksi['kode_transaksi'] ?? $transaksi['kode'] ?? '-');
$tanggalRaw = $transaksi['tanggal'] ?? $transaksi['created_at'] ?? date('Y-m-d H:i:s');
$tanggal = struk_date($tanggalRaw);

$kasir = $transaksi['nama_kasir']
    ?? $transaksi['kasir']
    ?? $transaksi['nama_user']
    ?? struk_user_name($user);

$metodeBayar = strtoupper((string) ($transaksi['metode_bayar'] ?? $transaksi['metode_pembayaran'] ?? '-'));
$totalJual = (float) ($transaksi['total_jual'] ?? $transaksi['total'] ?? $transaksi['grand_total'] ?? 0);
$nominalBayar = (float) ($transaksi['nominal_bayar'] ?? $totalJual);
$kembalian = (float) ($transaksi['kembalian'] ?? max(0, $nominalBayar - $totalJual));

$totalQty = 0;

foreach ($items as $row) {
    $totalQty += (int) ($row['qty'] ?? 0);
}
?>

<div class="struk-receipt-paper struk-print-area">
    <div class="struk-receipt-brand">
    <div class="struk-receipt-logo">
        <img
            src="<?= app_e(app_asset('assets/images/mts.png')) ?>"
            alt="Logo MTSN 8 Banyuwangi"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';"
        >

        <span class="struk-logo-fallback" aria-hidden="true" style="display:none;">
            <i class="ti ti-school"></i>
        </span>
    </div>

        <h3>LAB KEWIRAUSAHAAN</h3>
        <strong>MTSN 8 BANYUWANGI</strong>
        <p>Jl. Samiran Dusun Krajan II.7, RT.003/RW.003, Jalen Parungan, Setail, Kec. Genteng, Kabupaten Banyuwangi, Jawa Timur 68465</p>
    </div>

    <div class="struk-divider"></div>

    <div class="struk-meta">
        <div>
            <span>No</span>
            <strong><?= struk_e($kodeTransaksi) ?></strong>
        </div>

        <div>
            <span>Tanggal</span>
            <strong><?= struk_e($tanggal) ?></strong>
        </div>

        <div>
            <span>Kasir</span>
            <strong><?= struk_e(ucwords((string) $kasir)) ?></strong>
        </div>

        <div>
            <span>Metode</span>
            <strong><?= struk_e($metodeBayar) ?></strong>
        </div>
    </div>

    <div class="struk-divider"></div>

    <table class="struk-table">
        <thead>
            <tr>
                <th>Barang</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="3" class="text-center">Tidak ada item.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <?php
                    $namaBarang = (string) ($item['nama_barang'] ?? $item['nama'] ?? $item['barang'] ?? '-');
                    $kodeBarang = (string) ($item['kode_barang'] ?? $item['kode'] ?? '');
                    $qty = (int) ($item['qty'] ?? 0);
                    $hargaJual = (float) ($item['harga_jual'] ?? $item['harga'] ?? 0);
                    $subtotal = (float) ($item['subtotal_jual'] ?? $item['subtotal'] ?? ($qty * $hargaJual));
                    ?>

                    <tr>
                        <td>
                            <strong><?= struk_e($namaBarang) ?></strong>

                            <?php if ($kodeBarang !== ''): ?>
                                <small><?= struk_e($kodeBarang) ?></small>
                            <?php endif; ?>

                            <small><?= struk_e((string) $qty) ?> x <?= struk_e(struk_money($hargaJual)) ?></small>
                        </td>

                        <td class="text-center"><?= struk_e((string) $qty) ?></td>
                        <td class="text-end"><?= struk_e(struk_money($subtotal)) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="struk-divider"></div>

    <div class="struk-total">
        <div>
            <span>Total Item</span>
            <strong><?= struk_e((string) $totalQty) ?></strong>
        </div>

        <div>
            <span>Total</span>
            <strong><?= struk_e(struk_money($totalJual)) ?></strong>
        </div>

        <div>
            <span>Bayar</span>
            <strong><?= struk_e(struk_money($nominalBayar)) ?></strong>
        </div>

        <div>
            <span>Kembalian</span>
            <strong><?= struk_e(struk_money($kembalian)) ?></strong>
        </div>
    </div>

    <div class="struk-divider"></div>

    <div class="struk-receipt-footer">
        <strong>Terima kasih</strong>
        <p>Telah Berbelanja Disini, Ditunggu Kedatangannya untuk belanja Lagi.</p>
    </div>
</div>