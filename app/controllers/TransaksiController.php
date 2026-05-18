<?php

declare(strict_types=1);

class TransaksiController extends Controller
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

        // Ambil data
        $barangs = $this->barangModel->getActive();

        // Tampilkan halaman
        $this->view('admin/transaksi/index', [
            'title' => 'Transaksi Admin',
            'activeMenu' => 'transaksi',
            'user' => Session::user(),
            'barangs' => $barangs,
            'metodePembayaran' => $this->paymentMethods(),
            'flash' => [
                'success' => Session::getFlash('success'),
                'error' => Session::getFlash('error'),
            ],
        ]);
    }

    public function kasirIndex(): void
    {
        // Cek akses
        $this->requireRole('kasir');

        // Ambil data
        $barangs = $this->barangModel->getActive();

        // Tampilkan halaman
        $this->view('kasir/transaksi/transaksi', [
            'title' => 'Transaksi Kasir',
            'activeMenu' => 'transaksi',
            'user' => Session::user(),
            'barangs' => $barangs,
            'metodePembayaran' => $this->paymentMethods(),
            'flash' => [
                'success' => Session::getFlash('success'),
                'error' => Session::getFlash('error'),
            ],
        ]);
    }

    public function store(): void
    {
        // Cek akses
        $this->requireRole(['admin', 'kasir']);

        $role = Session::role();
        $backUrl = $role === 'admin' ? '/admin/transaksi' : '/kasir/transaksi';

        // Ambil input
        $cartJson = trim($_POST['cart_json'] ?? '');
        $metodeBayar = trim($_POST['metode_pembayaran'] ?? ($_POST['metode_bayar'] ?? ''));
        $nominalBayar = trim($_POST['nominal_bayar'] ?? '0');

        $items = $this->normalizeCart($cartJson);
        $errors = $this->validateTransactionInput($items, $metodeBayar, $nominalBayar);

        if (!empty($errors)) {
            Session::setFlash('error', implode(' ', $errors));
            $this->redirect($backUrl);
        }

        $db = $this->transaksiModel->db();

        try {
            $db->beginTransaction();

            // Hitung ulang dari database
            $prepared = $this->prepareItems($items);

            if (empty($prepared['items'])) {
                throw new RuntimeException('Keranjang tidak valid.');
            }

            $totalJual = $prepared['total_jual'];
            $totalBeli = $prepared['total_beli'];
            $totalLaba = $prepared['total_laba'];

            $nominalBayarValue = $metodeBayar === 'cash' ? (float) $nominalBayar : $totalJual;
            $kembalian = $metodeBayar === 'cash' ? $nominalBayarValue - $totalJual : 0;

            if ($metodeBayar === 'cash' && $nominalBayarValue < $totalJual) {
                throw new RuntimeException('Nominal bayar kurang dari total transaksi.');
            }

            // Simpan transaksi utama
            $transaksiId = $this->transaksiModel->create([
                'kode_transaksi' => $this->transaksiModel->generateCode(),
                'id_user' => (int) Session::userId(),
                'tanggal' => date('Y-m-d H:i:s'),
                'total_jual' => $totalJual,
                'total_beli' => $totalBeli,
                'total_laba' => $totalLaba,
                'metode_bayar' => $metodeBayar,
                'nominal_bayar' => $nominalBayarValue,
                'kembalian' => $kembalian,
            ]);

            if ($transaksiId <= 0) {
                throw new RuntimeException('Gagal menyimpan transaksi.');
            }

            // Simpan detail dan kurangi stok
            foreach ($prepared['items'] as $item) {
                $detailId = $this->detailModel->create([
                    'id_transaksi' => $transaksiId,
                    'id_barang' => $item['id_barang'],
                    'qty' => $item['qty'],
                    'harga_jual' => $item['harga_jual'],
                    'harga_beli' => $item['harga_beli'],
                    'subtotal_jual' => $item['subtotal_jual'],
                    'subtotal_beli' => $item['subtotal_beli'],
                    'laba_item' => $item['laba_item'],
                ]);

                if ($detailId <= 0) {
                    throw new RuntimeException('Gagal menyimpan detail transaksi.');
                }

                $stockUpdated = $this->barangModel->decreaseStock(
                    (int) $item['id_barang'],
                    (int) $item['qty']
                );

                if (!$stockUpdated) {
                    throw new RuntimeException('Stok barang tidak cukup atau gagal dikurangi.');
                }
            }

            $db->commit();

            Session::setFlash('success', 'Transaksi berhasil disimpan.');

            if ($role === 'admin') {
                $this->redirect('/admin/transaksi/struk/' . $transaksiId);
            }

            $this->redirect('/kasir/transaksi/struk/' . $transaksiId);
        } catch (Throwable $error) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            Session::setFlash('error', APP_DEBUG ? $error->getMessage() : 'Transaksi gagal disimpan.');
            $this->redirect($backUrl);
        }
    }

    public function adminStruk($id): void
    {
        // Cek akses
        $this->requireRole('admin');

        $id = (int) $id;
        $transaksi = $this->transaksiModel->findById($id);

        if (!$transaksi) {
            Session::setFlash('error', 'Transaksi tidak ditemukan.');
            $this->redirect('/admin/transaksi');
        }

        $items = $this->detailModel->getItemsWithBarang($id);

        // Tampilkan struk
        $this->view('admin/transaksi/struk', [
            'title' => 'Struk Transaksi',
            'activeMenu' => 'transaksi',
            'user' => Session::user(),
            'transaksi' => $transaksi,
            'items' => $items,
            'detailTransaksi' => $items,
        ]);
    }

    public function kasirStruk($id): void
    {
        // Cek akses
        $this->requireRole('kasir');

        $id = (int) $id;
        $transaksi = $this->transaksiModel->findById($id);

        if (!$transaksi) {
            Session::setFlash('error', 'Transaksi tidak ditemukan.');
            $this->redirect('/kasir/transaksi');
        }

        if ((int) $transaksi['id_user'] !== (int) Session::userId()) {
            Session::setFlash('error', 'Kamu tidak punya akses ke struk ini.');
            $this->redirect('/kasir/transaksi');
        }

        $items = $this->detailModel->getItemsWithBarang($id);

        // Tampilkan struk
        $this->view('kasir/transaksi/struk', [
            'title' => 'Struk Transaksi',
            'activeMenu' => 'transaksi',
            'user' => Session::user(),
            'transaksi' => $transaksi,
            'items' => $items,
            'detailTransaksi' => $items,
        ]);
    }

    private function normalizeCart(string $cartJson): array
    {
        // Rapikan keranjang
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

    private function validateTransactionInput(array $items, string $metodeBayar, string $nominalBayar): array
    {
        // Validasi input transaksi
        $errors = [];

        if (empty($items)) {
            $errors[] = 'Keranjang masih kosong.';
        }

        if (!$this->transaksiModel->isValidPaymentMethod($metodeBayar)) {
            $errors[] = 'Metode pembayaran tidak valid.';
        }

        if ($metodeBayar === 'cash') {
            if ($nominalBayar === '' || !is_numeric($nominalBayar)) {
                $errors[] = 'Nominal bayar wajib diisi untuk pembayaran cash.';
            } elseif ((float) $nominalBayar <= 0) {
                $errors[] = 'Nominal bayar harus lebih dari 0.';
            }
        }

        return $errors;
    }

    private function prepareItems(array $items): array
    {
        // Hitung ulang transaksi dari database
        $preparedItems = [];
        $totalJual = 0.0;
        $totalBeli = 0.0;
        $totalLaba = 0.0;

        foreach ($items as $item) {
            $idBarang = (int) $item['id_barang'];
            $qty = (int) $item['qty'];

            $barang = $this->barangModel->findActiveById($idBarang);

            if (!$barang) {
                throw new RuntimeException('Ada barang yang tidak ditemukan atau nonaktif.');
            }

            if ((int) $barang['stok'] < $qty) {
                throw new RuntimeException('Stok tidak cukup untuk barang: ' . $barang['nama']);
            }

            $hargaJual = (float) $barang['harga_jual'];
            $hargaBeli = (float) $this->restockModel->getLastHargaBeli($idBarang);

            $subtotalJual = $hargaJual * $qty;
            $subtotalBeli = $hargaBeli * $qty;
            $labaItem = $subtotalJual - $subtotalBeli;

            $preparedItems[] = [
                'id_barang' => $idBarang,
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

    public function adminPdf($id): void
{
    // Cek akses
    $this->requireRole('admin');

    $this->downloadPdf((int) $id, 'admin');
}

public function kasirPdf($id): void
{
    // Cek akses
    $this->requireRole('kasir');

    $id = (int) $id;
    $transaksi = $this->transaksiModel->findById($id);

    if (!$transaksi) {
        Session::setFlash('error', 'Transaksi tidak ditemukan.');
        $this->redirect('/kasir/transaksi');
    }

    if ((int) $transaksi['id_user'] !== (int) Session::userId()) {
        Session::setFlash('error', 'Kamu tidak punya akses ke struk ini.');
        $this->redirect('/kasir/transaksi');
    }

    $this->downloadPdf($id, 'kasir');
}

private function downloadPdf(int $id, string $role): void
{
    // Ambil data struk
    $transaksi = $this->transaksiModel->findById($id);

    if (!$transaksi) {
        Session::setFlash('error', 'Transaksi tidak ditemukan.');
        $this->redirect($role === 'admin' ? '/admin/transaksi' : '/kasir/transaksi');
    }

    $items = $this->detailModel->getItemsWithBarang($id);

    if (!class_exists('\Dompdf\Dompdf')) {
        throw new RuntimeException('Dompdf belum terinstall. Jalankan: composer require dompdf/dompdf');
    }

    // Render HTML khusus PDF
    ob_start();

    $title = 'Struk PDF';
    $user = Session::user();
    $detailTransaksi = $items;

    require APP_PATH . '/views/shared/struk-pdf.php';

    $html = ob_get_clean();

    // Generate PDF
    $dompdf = new \Dompdf\Dompdf([
        'isRemoteEnabled' => true,
        'defaultFont' => 'Arial',
    ]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper([0, 0, 226.77, 600], 'portrait'); // kurang lebih 80mm wide
    $dompdf->render();

    $filename = 'struk-' . ($transaksi['kode_transaksi'] ?? $id) . '.pdf';

    $dompdf->stream($filename, [
        'Attachment' => true,
    ]);

    exit;
}

    private function paymentMethods(): array
    {
        // Metode pembayaran
        return [
            'cash' => 'Cash',
            'qris' => 'QRIS',
            'transfer' => 'Transfer',
            'ewallet' => 'E-Wallet',
        ];
    }
}