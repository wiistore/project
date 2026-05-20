<?php

declare(strict_types=1);

class RiwayatController extends Controller
{
    private $transaksiModel;
    private $detailModel;
    private $barangModel;
    private $restockModel;

    public function __construct()
    {
        $this->transaksiModel = $this->model('Transaksi');
        $this->detailModel = $this->model('DetailTransaksi');
        $this->barangModel = $this->model('Barang');
        $this->restockModel = $this->model('Restock');
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

    /**
     * Batalkan / Refund transaksi
     */
    public function cancel($id): void
    {
        // Cek akses
        $this->requireRole('admin');

        $id = (int) $id;

        // Validasi alasan
        $alasan = trim($_POST['alasan_batal'] ?? '');

        if ($alasan === '') {
            Session::setFlash('error', 'Alasan pembatalan wajib diisi.');
            $this->redirect('/admin/riwayat-transaksi');
        }

        // Ambil transaksi
        $transaksi = $this->transaksiModel->findById($id);

        if (!$transaksi) {
            Session::setFlash('error', 'Transaksi tidak ditemukan.');
            $this->redirect('/admin/riwayat-transaksi');
        }

        // Cek status - yang sudah dibatalkan tidak boleh dibatalkan lagi
        $status = $transaksi['status'] ?? 'selesai';
        if ($status === 'dibatalkan') {
            Session::setFlash('error', 'Transaksi ini sudah dibatalkan sebelumnya.');
            $this->redirect('/admin/riwayat-transaksi');
        }

        // Ambil detail item
        $items = $this->detailModel->getByTransaksiId($id);

        if (empty($items)) {
            Session::setFlash('error', 'Detail transaksi kosong.');
            $this->redirect('/admin/riwayat-transaksi');
        }

        $db = $this->transaksiModel->db();

        try {
            $db->beginTransaction();

            // Kembalikan stok semua barang
            foreach ($items as $item) {
                $stockReturned = $this->barangModel->increaseStock(
                    (int) $item['id_barang'],
                    (int) $item['qty']
                );

                if (!$stockReturned) {
                    throw new RuntimeException('Gagal mengembalikan stok barang ID: ' . $item['id_barang']);
                }
            }

            // Update status transaksi menjadi dibatalkan
            $updated = $this->transaksiModel->updateStatus($id, 'dibatalkan', $alasan);

            if (!$updated) {
                throw new RuntimeException('Gagal mengubah status transaksi.');
            }

            $db->commit();

            Session::setFlash('success', 'Transaksi berhasil dibatalkan. Stok barang sudah dikembalikan.');
            $this->redirect('/admin/riwayat-transaksi');
        } catch (Throwable $error) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            Session::setFlash('error', APP_DEBUG ? $error->getMessage() : 'Gagal membatalkan transaksi.');
            $this->redirect('/admin/riwayat-transaksi');
        }
    }

    /**
     * Form edit transaksi
     */
    public function edit($id): void
    {
        // Cek akses
        $this->requireRole('admin');

        $id = (int) $id;
        $transaksi = $this->transaksiModel->findById($id);

        if (!$transaksi) {
            Session::setFlash('error', 'Transaksi tidak ditemukan.');
            $this->redirect('/admin/riwayat-transaksi');
        }

        // Cek status - yang dibatalkan tidak boleh diedit
        $status = $transaksi['status'] ?? 'selesai';
        if ($status === 'dibatalkan') {
            Session::setFlash('error', 'Transaksi yang sudah dibatalkan tidak bisa diedit.');
            $this->redirect('/admin/riwayat-transaksi');
        }

        // Ambil detail item
        $items = $this->detailModel->getItemsWithBarang($id);

        // Ambil semua barang aktif untuk pilihan tambah barang baru
        $barangs = $this->barangModel->getActive();

        // Tampilkan form edit
        $this->view('admin/riwayat/edit', [
            'title' => 'Edit Transaksi',
            'activeMenu' => 'riwayat',
            'user' => Session::user(),
            'transaksi' => $transaksi,
            'items' => $items,
            'barangs' => $barangs,
            'flash' => [
                'success' => Session::getFlash('success'),
                'error' => Session::getFlash('error'),
            ],
        ]);
    }

    /**
     * Proses update transaksi
     */
    public function update($id): void
    {
        // Cek akses
        $this->requireRole('admin');

        $id = (int) $id;
        $transaksi = $this->transaksiModel->findById($id);

        if (!$transaksi) {
            Session::setFlash('error', 'Transaksi tidak ditemukan.');
            $this->redirect('/admin/riwayat-transaksi');
        }

        // Cek status
        $status = $transaksi['status'] ?? 'selesai';
        if ($status === 'dibatalkan') {
            Session::setFlash('error', 'Transaksi yang sudah dibatalkan tidak bisa diedit.');
            $this->redirect('/admin/riwayat-transaksi');
        }

        // Parse cart baru dari form
        $cartJson = trim($_POST['cart_json'] ?? '');
        $nominalBayar = trim($_POST['nominal_bayar'] ?? '0');

        $newItems = $this->parseEditCart($cartJson);

        if (empty($newItems)) {
            Session::setFlash('error', 'Keranjang tidak boleh kosong. Minimal 1 barang.');
            $this->redirect('/admin/riwayat-transaksi/edit/' . $id);
        }

        // Ambil detail transaksi lama
        $oldItems = $this->detailModel->getByTransaksiId($id);

        $db = $this->transaksiModel->db();

        try {
            $db->beginTransaction();

            // STEP 1: Kembalikan stok dari transaksi lama
            foreach ($oldItems as $oldItem) {
                $stockReturned = $this->barangModel->increaseStock(
                    (int) $oldItem['id_barang'],
                    (int) $oldItem['qty']
                );

                if (!$stockReturned) {
                    throw new RuntimeException('Gagal mengembalikan stok barang lama.');
                }
            }

            // STEP 2: Hitung ulang dari database dan validasi stok baru
            $prepared = $this->prepareEditItems($newItems);

            if (empty($prepared['items'])) {
                throw new RuntimeException('Tidak ada barang valid di keranjang baru.');
            }

            // STEP 3: Kurangi stok berdasarkan transaksi baru
            foreach ($prepared['items'] as $item) {
                $stockDecreased = $this->barangModel->decreaseStock(
                    (int) $item['id_barang'],
                    (int) $item['qty']
                );

                if (!$stockDecreased) {
                    throw new RuntimeException('Stok tidak cukup untuk barang: ' . ($item['nama'] ?? 'ID ' . $item['id_barang']));
                }
            }

            // STEP 4: Hapus detail transaksi lama
            $this->detailModel->deleteByTransaksiId($id);

            // STEP 5: Simpan detail transaksi baru
            foreach ($prepared['items'] as $item) {
                $detailId = $this->detailModel->create([
                    'id_transaksi' => $id,
                    'id_barang' => $item['id_barang'],
                    'qty' => $item['qty'],
                    'harga_jual' => $item['harga_jual'],
                    'harga_beli' => $item['harga_beli'],
                    'subtotal_jual' => $item['subtotal_jual'],
                    'subtotal_beli' => $item['subtotal_beli'],
                    'laba_item' => $item['laba_item'],
                ]);

                if ($detailId <= 0) {
                    throw new RuntimeException('Gagal menyimpan detail transaksi baru.');
                }
            }

            // STEP 6: Update total transaksi
            $totalJual = $prepared['total_jual'];
            $totalBeli = $prepared['total_beli'];
            $totalLaba = $prepared['total_laba'];

            $metodeBayar = $transaksi['metode_bayar'] ?? 'cash';
            $nominalBayarValue = $metodeBayar === 'cash' ? (float) $nominalBayar : $totalJual;
            $kembalian = $metodeBayar === 'cash' ? max(0, $nominalBayarValue - $totalJual) : 0;

            if ($metodeBayar === 'cash' && $nominalBayarValue < $totalJual) {
                throw new RuntimeException('Nominal bayar kurang dari total transaksi baru.');
            }

            $updated = $this->transaksiModel->updateTotals($id, [
                'total_jual' => $totalJual,
                'total_beli' => $totalBeli,
                'total_laba' => $totalLaba,
                'nominal_bayar' => $nominalBayarValue,
                'kembalian' => $kembalian,
            ]);

            if (!$updated) {
                throw new RuntimeException('Gagal memperbarui total transaksi.');
            }

            $db->commit();

            Session::setFlash('success', 'Transaksi berhasil diubah. Stok dan total sudah diperbarui.');
            $this->redirect('/admin/riwayat-transaksi');
        } catch (Throwable $error) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            Session::setFlash('error', APP_DEBUG ? $error->getMessage() : 'Gagal mengubah transaksi.');
            $this->redirect('/admin/riwayat-transaksi/edit/' . $id);
        }
    }

    private function parseEditCart(string $cartJson): array
    {
        // Parse cart JSON dari form edit
        if ($cartJson === '') {
            return [];
        }

        $decoded = json_decode($cartJson, true);

        if (!is_array($decoded)) {
            return [];
        }

        $rawItems = $decoded['items'] ?? $decoded;
        $items = [];

        if (!is_array($rawItems)) {
            return [];
        }

        foreach ($rawItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            $idBarang = (int) ($item['id_barang'] ?? ($item['id'] ?? 0));
            $qty = (int) ($item['qty'] ?? 0);

            // Skip item dengan qty 0 atau kurang (dianggap dihapus)
            if ($idBarang <= 0 || $qty <= 0) {
                continue;
            }

            if (!isset($items[$idBarang])) {
                $items[$idBarang] = [
                    'id_barang' => $idBarang,
                    'qty' => 0,
                ];
            }

            $items[$idBarang]['qty'] += $qty;
        }

        return array_values($items);
    }

    private function prepareEditItems(array $items): array
    {
        // Hitung ulang dari database
        $preparedItems = [];
        $totalJual = 0.0;
        $totalBeli = 0.0;
        $totalLaba = 0.0;

        foreach ($items as $item) {
            $idBarang = (int) $item['id_barang'];
            $qty = (int) $item['qty'];

            $barang = $this->barangModel->findActiveById($idBarang);

            if (!$barang) {
                throw new RuntimeException('Barang tidak ditemukan atau nonaktif (ID: ' . $idBarang . ')');
            }

            if ((int) $barang['stok'] < $qty) {
                throw new RuntimeException('Stok tidak cukup untuk barang: ' . $barang['nama'] . ' (tersedia: ' . $barang['stok'] . ', diminta: ' . $qty . ')');
            }

            $hargaJual = (float) $barang['harga_jual'];
            $hargaBeli = (float) $this->restockModel->getLastHargaBeli($idBarang);

            $subtotalJual = $hargaJual * $qty;
            $subtotalBeli = $hargaBeli * $qty;
            $labaItem = $subtotalJual - $subtotalBeli;

            $preparedItems[] = [
                'id_barang' => $idBarang,
                'nama' => $barang['nama'] ?? '',
                'qty' => $qty,
                'harga_jual' => $hargaJual,
                'harga_beli' => $hargaBeli,
                'subtotal_jual' => $subtotalJual,
                'subtotal_beli' => $subtotalBeli,
                'laba_item' => $labaItem,
            ];

            $totalJual += $subtotalJual;
            $totalBeli += $subtotalBeli;
            $totalLaba += $labaItem;
        }

        return [
            'items' => $preparedItems,
            'total_jual' => $totalJual,
            'total_beli' => $totalBeli,
            'total_laba' => $totalLaba,
        ];
    }

    private function makeSummary(array $transaksis): array
    {
        // Ringkasan dari data yang sedang tampil (exclude dibatalkan)
        $totalTransaksi = 0;
        $totalPenjualan = 0.0;
        $totalModal = 0.0;
        $totalLaba = 0.0;

        foreach ($transaksis as $transaksi) {
            $status = $transaksi['status'] ?? 'selesai';
            if ($status === 'dibatalkan') {
                continue;
            }

            $totalTransaksi++;
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
