<?php

declare(strict_types=1);

class Laporan extends Model
{
    public function summary(?string $start = null, ?string $end = null): array
    {
        // Ringkasan laporan (exclude transaksi dibatalkan)
        $sql = "
            SELECT
                COUNT(t.id) AS total_transaksi,
                COALESCE(SUM(t.total_jual), 0) AS total_penjualan,
                COALESCE(SUM(t.total_beli), 0) AS total_modal,
                COALESCE(SUM(t.total_laba), 0) AS total_laba
            FROM transaksi t
            WHERE t.status != 'dibatalkan'
        ";

        $params = $this->dateParams($start, $end, $sql);

        $row = $this->fetch($sql, $params);

        return [
            'total_transaksi' => (int) ($row['total_transaksi'] ?? 0),
            'total_penjualan' => (float) ($row['total_penjualan'] ?? 0),
            'total_modal' => (float) ($row['total_modal'] ?? 0),
            'total_laba' => (float) ($row['total_laba'] ?? 0),
        ];
    }

    public function penjualanHarian(?string $start = null, ?string $end = null): array
    {
        // Penjualan per hari (exclude transaksi dibatalkan)
        $sql = "
            SELECT
                DATE(t.tanggal) AS tanggal,
                COUNT(t.id) AS total_transaksi,
                COALESCE(SUM(t.total_jual), 0) AS total_penjualan,
                COALESCE(SUM(t.total_beli), 0) AS total_modal,
                COALESCE(SUM(t.total_laba), 0) AS total_laba
            FROM transaksi t
            WHERE t.status != 'dibatalkan'
        ";

        $params = $this->dateParams($start, $end, $sql);

        $sql .= "
            GROUP BY DATE(t.tanggal)
            ORDER BY tanggal ASC
            LIMIT 100
        ";

        return $this->fetchAll($sql, $params);
    }

    public function penjualanByKasir(?string $start = null, ?string $end = null): array
    {
        // Penjualan per kasir (exclude transaksi dibatalkan)
        $sql = "
            SELECT
                u.id AS id_user,
                u.username AS nama_kasir,
                COUNT(t.id) AS total_transaksi,
                COALESCE(SUM(t.total_jual), 0) AS total_penjualan,
                COALESCE(SUM(t.total_beli), 0) AS total_modal,
                COALESCE(SUM(t.total_laba), 0) AS total_laba
            FROM transaksi t
            INNER JOIN users u ON u.id = t.id_user
            WHERE t.status != 'dibatalkan'
        ";

        $params = $this->dateParams($start, $end, $sql);

        $sql .= "
            GROUP BY u.id, u.username
            ORDER BY total_penjualan DESC
            LIMIT 100
        ";

        return $this->fetchAll($sql, $params);
    }

    public function barangTerlaris(?string $start = null, ?string $end = null, int $limit = 20): array
    {
        // Barang paling banyak terjual (exclude transaksi dibatalkan)
        $limit = max(1, min($limit, 100));

        $sql = "
            SELECT
                b.id,
                b.kode_barang,
                b.barcode,
                b.nama AS nama_barang,
                b.satuan,
                k.nama AS nama_kategori,
                COALESCE(SUM(dt.qty), 0) AS total_qty,
                COALESCE(SUM(dt.subtotal_jual), 0) AS total_penjualan,
                COALESCE(SUM(dt.subtotal_beli), 0) AS total_modal,
                COALESCE(SUM(dt.laba_item), 0) AS total_laba
            FROM detail_transaksi dt
            INNER JOIN transaksi t ON t.id = dt.id_transaksi
            INNER JOIN barang b ON b.id = dt.id_barang
            INNER JOIN kategori k ON k.id = b.id_kategori
            WHERE t.status != 'dibatalkan'
        ";

        $params = $this->dateParams($start, $end, $sql);

        $sql .= "
            GROUP BY 
                b.id,
                b.kode_barang,
                b.barcode,
                b.nama,
                b.satuan,
                k.nama
            ORDER BY total_qty DESC, total_penjualan DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql, $params);
    }

    public function metodePembayaran(?string $start = null, ?string $end = null): array
    {
        // Ringkasan metode bayar (exclude transaksi dibatalkan)
        $sql = "
            SELECT
                t.metode_bayar,
                COUNT(t.id) AS total_transaksi,
                COALESCE(SUM(t.total_jual), 0) AS total_penjualan
            FROM transaksi t
            WHERE t.status != 'dibatalkan'
        ";

        $params = $this->dateParams($start, $end, $sql);

        $sql .= "
            GROUP BY t.metode_bayar
            ORDER BY total_penjualan DESC
        ";

        return $this->fetchAll($sql, $params);
    }

    public function restockSummary(?string $start = null, ?string $end = null): array
    {
        // Ringkasan restock
        $sql = "
            SELECT
                COUNT(r.id) AS total_restock,
                COALESCE(SUM(r.qty), 0) AS total_qty,
                COALESCE(SUM(r.total_nilai), 0) AS total_nilai
            FROM restock r
            WHERE 1 = 1
        ";

        $params = $this->dateParams($start, $end, $sql, 'r.tanggal');

        $row = $this->fetch($sql, $params);

        return [
            'total_restock' => (int) ($row['total_restock'] ?? 0),
            'total_qty' => (int) ($row['total_qty'] ?? 0),
            'total_nilai' => (float) ($row['total_nilai'] ?? 0),
        ];
    }

    public function restockByBarang(?string $start = null, ?string $end = null): array
    {
        // Restock per barang
        $sql = "
            SELECT
                b.id,
                b.kode_barang,
                b.nama AS nama_barang,
                b.satuan,
                COALESCE(SUM(r.qty), 0) AS total_qty,
                COALESCE(SUM(r.total_nilai), 0) AS total_nilai,
                AVG(r.harga_beli) AS rata_harga_beli
            FROM restock r
            INNER JOIN barang b ON b.id = r.id_barang
            WHERE 1 = 1
        ";

        $params = $this->dateParams($start, $end, $sql, 'r.tanggal');

        $sql .= "
            GROUP BY b.id, b.kode_barang, b.nama, b.satuan
            ORDER BY total_nilai DESC
            LIMIT 100
        ";

        return $this->fetchAll($sql, $params);
    }

    public function restockBySupplier(?string $start = null, ?string $end = null): array
    {
        // Restock per supplier
        $sql = "
            SELECT
                s.id,
                s.nama AS nama_supplier,
                COUNT(r.id) AS total_restock,
                COALESCE(SUM(r.qty), 0) AS total_qty,
                COALESCE(SUM(r.total_nilai), 0) AS total_nilai
            FROM restock r
            INNER JOIN supplier s ON s.id = r.id_supplier
            WHERE 1 = 1
        ";

        $params = $this->dateParams($start, $end, $sql, 'r.tanggal');

        $sql .= "
            GROUP BY s.id, s.nama
            ORDER BY total_nilai DESC
            LIMIT 100
        ";

        return $this->fetchAll($sql, $params);
    }

    public function stokMenipis(): array
    {
        // Barang stok menipis
        $sql = "
            SELECT
                b.id,
                b.kode_barang,
                b.nama AS nama_barang,
                b.stok,
                b.stok_minimum,
                b.satuan,
                k.nama AS nama_kategori
            FROM barang b
            INNER JOIN kategori k ON k.id = b.id_kategori
            WHERE b.status = 'aktif'
              AND b.stok <= b.stok_minimum
            ORDER BY b.stok ASC, b.nama ASC
            LIMIT 100
        ";

        return $this->fetchAll($sql);
    }

    private function dateParams(?string $start, ?string $end, string &$sql, string $column = 't.tanggal'): array
    {
        // Filter tanggal
        $params = [];

        if ($start !== null && $start !== '') {
            $sql .= " AND DATE({$column}) >= :tanggal_mulai";
            $params['tanggal_mulai'] = $start;
        }

        if ($end !== null && $end !== '') {
            $sql .= " AND DATE({$column}) <= :tanggal_selesai";
            $params['tanggal_selesai'] = $end;
        }

        return $params;
    }
}
