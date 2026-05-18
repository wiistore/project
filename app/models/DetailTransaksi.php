<?php

declare(strict_types=1);

class DetailTransaksi extends Model
{
    private $table = 'detail_transaksi';

    public function create(array $data): int
    {
        // Simpan detail transaksi
        $sql = "
            INSERT INTO {$this->table}
                (
                    id_transaksi,
                    id_barang,
                    qty,
                    harga_jual,
                    harga_beli,
                    subtotal_jual,
                    subtotal_beli,
                    laba_item
                )
            VALUES
                (
                    :id_transaksi,
                    :id_barang,
                    :qty,
                    :harga_jual,
                    :harga_beli,
                    :subtotal_jual,
                    :subtotal_beli,
                    :laba_item
                )
        ";

        $this->execute($sql, [
            'id_transaksi' => (int) $data['id_transaksi'],
            'id_barang' => (int) $data['id_barang'],
            'qty' => (int) $data['qty'],
            'harga_jual' => (float) $data['harga_jual'],
            'harga_beli' => (float) $data['harga_beli'],
            'subtotal_jual' => (float) $data['subtotal_jual'],
            'subtotal_beli' => (float) $data['subtotal_beli'],
            'laba_item' => (float) $data['laba_item'],
        ]);

        return $this->lastInsertId();
    }

    public function insert(array $data): int
    {
        return $this->create($data);
    }

    public function createMany(int $transaksiId, array $items): bool
    {
        // Simpan banyak item
        foreach ($items as $item) {
            $item['id_transaksi'] = $transaksiId;

            $detailId = $this->create($item);

            if ($detailId <= 0) {
                return false;
            }
        }

        return true;
    }

    public function getByTransaksiId(int $transaksiId): array
    {
        // Detail item transaksi
        $sql = "
            SELECT
                id,
                id_transaksi,
                id_barang,
                qty,
                harga_jual,
                harga_beli,
                subtotal_jual,
                subtotal_beli,
                laba_item
            FROM {$this->table}
            WHERE id_transaksi = :id_transaksi
            ORDER BY id ASC
        ";

        return $this->fetchAll($sql, [
            'id_transaksi' => $transaksiId,
        ]);
    }

    public function getItemsWithBarang(int $transaksiId): array
    {
        // Detail transaksi + barang
        $sql = "
            SELECT
                dt.id,
                dt.id_transaksi,
                dt.id_barang,
                b.kode_barang,
                b.barcode,
                b.nama AS nama_barang,
                b.satuan,
                dt.qty,
                dt.harga_jual,
                dt.harga_beli,
                dt.subtotal_jual,
                dt.subtotal_beli,
                dt.laba_item
            FROM {$this->table} dt
            INNER JOIN barang b ON b.id = dt.id_barang
            WHERE dt.id_transaksi = :id_transaksi
            ORDER BY dt.id ASC
        ";

        return $this->fetchAll($sql, [
            'id_transaksi' => $transaksiId,
        ]);
    }

    public function getByBarangId(int $barangId, int $limit = 100): array
    {
        // Histori transaksi per barang
        $limit = max(1, min($limit, 300));

        $sql = "
            SELECT
                dt.id,
                dt.id_transaksi,
                t.kode_transaksi,
                t.tanggal,
                u.username AS nama_kasir,
                dt.id_barang,
                dt.qty,
                dt.harga_jual,
                dt.harga_beli,
                dt.subtotal_jual,
                dt.subtotal_beli,
                dt.laba_item
            FROM {$this->table} dt
            INNER JOIN transaksi t ON t.id = dt.id_transaksi
            INNER JOIN users u ON u.id = t.id_user
            WHERE dt.id_barang = :id_barang
            ORDER BY t.tanggal DESC, dt.id DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql, [
            'id_barang' => $barangId,
        ]);
    }

    public function sumQtyByTransaksiId(int $transaksiId): int
    {
        // Total qty dalam transaksi
        $sql = "
            SELECT COALESCE(SUM(qty), 0)
            FROM {$this->table}
            WHERE id_transaksi = :id_transaksi
        ";

        return (int) $this->query($sql, [
            'id_transaksi' => $transaksiId,
        ])->fetchColumn();
    }

    public function summaryByTransaksiId(int $transaksiId): array
    {
        // Ringkasan detail transaksi
        $sql = "
            SELECT
                COUNT(id) AS total_item,
                COALESCE(SUM(qty), 0) AS total_qty,
                COALESCE(SUM(subtotal_jual), 0) AS total_jual,
                COALESCE(SUM(subtotal_beli), 0) AS total_beli,
                COALESCE(SUM(laba_item), 0) AS total_laba
            FROM {$this->table}
            WHERE id_transaksi = :id_transaksi
        ";

        $row = $this->fetch($sql, [
            'id_transaksi' => $transaksiId,
        ]);

        return [
            'total_item' => (int) ($row['total_item'] ?? 0),
            'total_qty' => (int) ($row['total_qty'] ?? 0),
            'total_jual' => (float) ($row['total_jual'] ?? 0),
            'total_beli' => (float) ($row['total_beli'] ?? 0),
            'total_laba' => (float) ($row['total_laba'] ?? 0),
        ];
    }

    public function countAll(): int
    {
        $sql = "
            SELECT COUNT(id)
            FROM {$this->table}
        ";

        return $this->countRows($sql);
    }
}