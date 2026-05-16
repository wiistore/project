<?php

declare(strict_types=1);

class Restock extends Model
{
    private $table = 'restock';

    public function getAll(int $limit = 100): array
    {
        // Ambil data restock
        $limit = max(1, min($limit, 200));

        $sql = "
            SELECT
                r.id,
                r.tanggal,
                r.id_barang,
                b.kode_barang,
                b.nama AS nama_barang,
                b.satuan,
                r.id_supplier,
                s.nama AS nama_supplier,
                r.id_user,
                u.username AS dibuat_oleh,
                r.qty,
                r.harga_beli,
                r.harga_jual_baru,
                r.total_nilai,
                r.catatan,
                r.created_at
            FROM {$this->table} r
            INNER JOIN barang b ON b.id = r.id_barang
            INNER JOIN supplier s ON s.id = r.id_supplier
            INNER JOIN users u ON u.id = r.id_user
            ORDER BY r.tanggal DESC, r.id DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql);
    }

    public function findById(int $id)
    {
        // Detail restock
        $sql = "
            SELECT
                r.id,
                r.tanggal,
                r.id_barang,
                b.kode_barang,
                b.nama AS nama_barang,
                b.satuan,
                r.id_supplier,
                s.nama AS nama_supplier,
                r.id_user,
                u.username AS dibuat_oleh,
                r.qty,
                r.harga_beli,
                r.harga_jual_baru,
                r.total_nilai,
                r.catatan,
                r.created_at
            FROM {$this->table} r
            INNER JOIN barang b ON b.id = r.id_barang
            INNER JOIN supplier s ON s.id = r.id_supplier
            INNER JOIN users u ON u.id = r.id_user
            WHERE r.id = :id
            LIMIT 1
        ";

        return $this->fetch($sql, [
            'id' => $id,
        ]);
    }

    public function getById(int $id)
    {
        return $this->findById($id);
    }

    public function create(array $data): int
    {
        // Simpan restock
        $sql = "
            INSERT INTO {$this->table}
                (
                    tanggal,
                    id_barang,
                    id_supplier,
                    id_user,
                    qty,
                    harga_beli,
                    harga_jual_baru,
                    total_nilai,
                    catatan
                )
            VALUES
                (
                    :tanggal,
                    :id_barang,
                    :id_supplier,
                    :id_user,
                    :qty,
                    :harga_beli,
                    :harga_jual_baru,
                    :total_nilai,
                    :catatan
                )
        ";

        $qty = (int) $data['qty'];
        $hargaBeli = (float) $data['harga_beli'];
        $totalNilai = $qty * $hargaBeli;

        $this->execute($sql, [
            'tanggal' => $data['tanggal'] ?? date('Y-m-d'),
            'id_barang' => (int) $data['id_barang'],
            'id_supplier' => (int) $data['id_supplier'],
            'id_user' => (int) $data['id_user'],
            'qty' => $qty,
            'harga_beli' => $hargaBeli,
            'harga_jual_baru' => $this->nullableNumber($data['harga_jual_baru'] ?? null),
            'total_nilai' => $totalNilai,
            'catatan' => $this->nullableText($data['catatan'] ?? ''),
        ]);

        return $this->lastInsertId();
    }

    public function insert(array $data): int
    {
        return $this->create($data);
    }

    public function getByDateRange(?string $start = null, ?string $end = null, int $limit = 200): array
    {
        // Filter tanggal
        $limit = max(1, min($limit, 500));

        $sql = "
            SELECT
                r.id,
                r.tanggal,
                r.id_barang,
                b.kode_barang,
                b.nama AS nama_barang,
                b.satuan,
                r.id_supplier,
                s.nama AS nama_supplier,
                r.id_user,
                u.username AS dibuat_oleh,
                r.qty,
                r.harga_beli,
                r.harga_jual_baru,
                r.total_nilai,
                r.catatan,
                r.created_at
            FROM {$this->table} r
            INNER JOIN barang b ON b.id = r.id_barang
            INNER JOIN supplier s ON s.id = r.id_supplier
            INNER JOIN users u ON u.id = r.id_user
            WHERE 1 = 1
        ";

        $params = [];

        if ($start !== null && $start !== '') {
            $sql .= " AND r.tanggal >= :tanggal_mulai";
            $params['tanggal_mulai'] = $start;
        }

        if ($end !== null && $end !== '') {
            $sql .= " AND r.tanggal <= :tanggal_selesai";
            $params['tanggal_selesai'] = $end;
        }

        $sql .= "
            ORDER BY r.tanggal DESC, r.id DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql, $params);
    }

    public function getBySupplierId(int $supplierId, int $limit = 100): array
    {
        // Restock per supplier
        $limit = max(1, min($limit, 200));

        $sql = "
            SELECT
                r.id,
                r.tanggal,
                b.nama AS nama_barang,
                r.qty,
                r.harga_beli,
                r.total_nilai,
                r.created_at
            FROM {$this->table} r
            INNER JOIN barang b ON b.id = r.id_barang
            WHERE r.id_supplier = :id_supplier
            ORDER BY r.tanggal DESC, r.id DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql, [
            'id_supplier' => $supplierId,
        ]);
    }

    public function getByBarangId(int $barangId, int $limit = 100): array
    {
        // Restock per barang
        $limit = max(1, min($limit, 200));

        $sql = "
            SELECT
                r.id,
                r.tanggal,
                s.nama AS nama_supplier,
                r.qty,
                r.harga_beli,
                r.harga_jual_baru,
                r.total_nilai,
                r.created_at
            FROM {$this->table} r
            INNER JOIN supplier s ON s.id = r.id_supplier
            WHERE r.id_barang = :id_barang
            ORDER BY r.tanggal DESC, r.id DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql, [
            'id_barang' => $barangId,
        ]);
    }

    public function getLastHargaBeli(int $barangId): float
    {
        // Harga beli terakhir buat transaksi nanti
        $sql = "
            SELECT harga_beli
            FROM {$this->table}
            WHERE id_barang = :id_barang
            ORDER BY tanggal DESC, id DESC
            LIMIT 1
        ";

        $harga = $this->query($sql, [
            'id_barang' => $barangId,
        ])->fetchColumn();

        return $harga === false ? 0.0 : (float) $harga;
    }

    public function summary(?string $start = null, ?string $end = null): array
    {
        // Ringkasan restock
        $sql = "
            SELECT
                COUNT(r.id) AS total_restock,
                COALESCE(SUM(r.qty), 0) AS total_qty,
                COALESCE(SUM(r.total_nilai), 0) AS total_nilai
            FROM {$this->table} r
            WHERE 1 = 1
        ";

        $params = [];

        if ($start !== null && $start !== '') {
            $sql .= " AND r.tanggal >= :tanggal_mulai";
            $params['tanggal_mulai'] = $start;
        }

        if ($end !== null && $end !== '') {
            $sql .= " AND r.tanggal <= :tanggal_selesai";
            $params['tanggal_selesai'] = $end;
        }

        $row = $this->fetch($sql, $params);

        return [
            'total_restock' => (int) ($row['total_restock'] ?? 0),
            'total_qty' => (int) ($row['total_qty'] ?? 0),
            'total_nilai' => (float) ($row['total_nilai'] ?? 0),
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

    private function nullableText($value)
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function nullableNumber($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}