<?php

declare(strict_types=1);

class Dashboard extends Model
{
    public function adminSummary(): array
    {
        return [
            'total_barang' => $this->countTotalBarang(),
            'total_transaksi_hari_ini' => $this->countTransaksiHariIni(),
            'penjualan_hari_ini' => $this->sumPenjualanHariIni(),
            'stok_menipis' => $this->countStokMenipis(),
            'transaksi_terbaru' => $this->getTransaksiTerbaru(),
            'chart_penjualan_7_hari' => $this->getPenjualanTujuhHari(),
            'chart_top_barang' => $this->getTopBarangTerlaris(),
            'chart_status_stok' => $this->getStatusStok(),
        ];
    }

    public function kasirSummary(int $userId): array
    {
        return [
            'total_transaksi_hari_ini' => $this->countTransaksiHariIniByUser($userId),
            'penjualan_hari_ini' => $this->sumPenjualanHariIniByUser($userId),
            'total_item_hari_ini' => $this->sumItemHariIniByUser($userId),
            'transaksi_terbaru' => $this->getTransaksiTerbaruByUser($userId),
        ];
    }

    private function countTotalBarang(): int
    {
        $sql = "SELECT COUNT(id) FROM barang";
        return $this->countRows($sql);
    }

    private function countTransaksiHariIni(): int
    {
        $sql = "SELECT COUNT(id) FROM transaksi WHERE DATE(tanggal) = CURDATE()";
        return $this->countRows($sql);
    }

    private function sumPenjualanHariIni(): float
    {
        $sql = "SELECT COALESCE(SUM(total_jual), 0) FROM transaksi WHERE DATE(tanggal) = CURDATE()";
        return (float) $this->query($sql)->fetchColumn();
    }

    private function countStokMenipis(): int
    {
        $sql = "
            SELECT COUNT(id)
            FROM barang
            WHERE status = 'aktif'
              AND stok <= stok_minimum
        ";

        return $this->countRows($sql);
    }

    private function getTransaksiTerbaru(int $limit = 10): array
    {
        $limit = max(1, min($limit, 20));

        $sql = "
            SELECT
                t.id,
                t.kode_transaksi,
                t.tanggal,
                t.total_jual,
                t.metode_bayar,
                u.username AS kasir
            FROM transaksi t
            INNER JOIN users u ON u.id = t.id_user
            ORDER BY t.tanggal DESC, t.id DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql);
    }

    private function getPenjualanTujuhHari(): array
    {
        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $labels[$date] = date('d M', strtotime($date));
            $values[$date] = 0.0;
        }

        $sql = "
            SELECT
                DATE(tanggal) AS tanggal,
                COALESCE(SUM(total_jual), 0) AS total
            FROM transaksi
            WHERE DATE(tanggal) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(tanggal)
            ORDER BY DATE(tanggal) ASC
        ";

        $rows = $this->fetchAll($sql);

        foreach ($rows as $row) {
            $date = (string) ($row['tanggal'] ?? '');

            if (array_key_exists($date, $values)) {
                $values[$date] = (float) ($row['total'] ?? 0);
            }
        }

        return [
            'labels' => array_values($labels),
            'values' => array_values($values),
        ];
    }

    private function getTopBarangTerlaris(int $limit = 5): array
    {
        $limit = max(1, min($limit, 10));

        $sql = "
            SELECT
                b.nama,
                COALESCE(SUM(dt.qty), 0) AS total_qty
            FROM detail_transaksi dt
            INNER JOIN barang b ON b.id = dt.id_barang
            INNER JOIN transaksi t ON t.id = dt.id_transaksi
            WHERE DATE(t.tanggal) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY b.id, b.nama
            ORDER BY total_qty DESC, b.nama ASC
            LIMIT {$limit}
        ";

        $rows = $this->fetchAll($sql);

        return [
            'labels' => array_map(static fn ($row) => (string) ($row['nama'] ?? '-'), $rows),
            'values' => array_map(static fn ($row) => (int) ($row['total_qty'] ?? 0), $rows),
        ];
    }

    private function getStatusStok(): array
    {
        $sql = "
            SELECT
                SUM(CASE WHEN status = 'aktif' AND stok > stok_minimum THEN 1 ELSE 0 END) AS aman,
                SUM(CASE WHEN status = 'aktif' AND stok > 0 AND stok <= stok_minimum THEN 1 ELSE 0 END) AS menipis,
                SUM(CASE WHEN status = 'aktif' AND stok <= 0 THEN 1 ELSE 0 END) AS habis
            FROM barang
        ";

        $row = $this->fetch($sql) ?: [];

        return [
            'labels' => ['Aman', 'Menipis', 'Habis'],
            'values' => [
                (int) ($row['aman'] ?? 0),
                (int) ($row['menipis'] ?? 0),
                (int) ($row['habis'] ?? 0),
            ],
        ];
    }

    private function countTransaksiHariIniByUser(int $userId): int
    {
        $sql = "
            SELECT COUNT(id)
            FROM transaksi
            WHERE id_user = :user_id
              AND DATE(tanggal) = CURDATE()
        ";

        return $this->countRows($sql, [
            'user_id' => $userId,
        ]);
    }

    private function sumPenjualanHariIniByUser(int $userId): float
    {
        $sql = "
            SELECT COALESCE(SUM(total_jual), 0)
            FROM transaksi
            WHERE id_user = :user_id
              AND DATE(tanggal) = CURDATE()
        ";

        return (float) $this->query($sql, [
            'user_id' => $userId,
        ])->fetchColumn();
    }

    private function sumItemHariIniByUser(int $userId): int
    {
        $sql = "
            SELECT COALESCE(SUM(dt.qty), 0)
            FROM detail_transaksi dt
            INNER JOIN transaksi t ON t.id = dt.id_transaksi
            WHERE t.id_user = :user_id
              AND DATE(t.tanggal) = CURDATE()
        ";

        return (int) $this->query($sql, [
            'user_id' => $userId,
        ])->fetchColumn();
    }

    private function getTransaksiTerbaruByUser(int $userId, int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));

        $sql = "
            SELECT
                id,
                kode_transaksi,
                tanggal,
                total_jual,
                metode_bayar
            FROM transaksi
            WHERE id_user = :user_id
            ORDER BY tanggal DESC, id DESC
            LIMIT {$limit}
        ";

        return $this->fetchAll($sql, [
            'user_id' => $userId,
        ]);
    }
}