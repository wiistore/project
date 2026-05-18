<?php

declare(strict_types=1);

class RestockController extends Controller
{
    private $restockModel;
    private $barangModel;
    private $supplierModel;

    public function __construct()
    {
        $this->restockModel = $this->model('Restock');
        $this->barangModel = $this->model('Barang');
        $this->supplierModel = $this->model('Supplier');
    }

    public function index(): void
    {
        // Cek akses
        $this->requireRole('admin');

        // Ambil filter
        $tanggalMulai = trim($_GET['tanggal_mulai'] ?? '');
        $tanggalSelesai = trim($_GET['tanggal_selesai'] ?? '');

        // Ambil data
        if ($tanggalMulai !== '' || $tanggalSelesai !== '') {
            $restocks = $this->restockModel->getByDateRange($tanggalMulai, $tanggalSelesai);
            $summary = $this->restockModel->summary($tanggalMulai, $tanggalSelesai);
        } else {
            $restocks = $this->restockModel->getAll();
            $summary = $this->restockModel->summary();
        }

        // Tampilkan halaman
        $this->view('admin/restock/index', [
            'title' => 'Restock Barang',
            'activeMenu' => 'restock',
            'user' => Session::user(),
            'restocks' => $restocks,
            'summary' => $summary,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'flash' => [
                'success' => Session::getFlash('success'),
                'error' => Session::getFlash('error'),
            ],
        ]);
    }

    public function create(): void
    {
        // Cek akses
        $this->requireRole('admin');

        // Ambil data form
        $barangs = $this->barangModel->getActive();
        $suppliers = $this->supplierModel->getActive();

        if (empty($barangs)) {
            Session::setFlash('error', 'Belum ada barang aktif. Tambahkan barang dulu.');
            $this->redirect('/admin/barang/create');
        }

        if (empty($suppliers)) {
            Session::setFlash('error', 'Belum ada supplier aktif. Tambahkan supplier dulu.');
            $this->redirect('/admin/supplier/create');
        }

        // Tampilkan form
        $this->view('admin/restock/form', [
            'title' => 'Tambah Restock',
            'activeMenu' => 'restock',
            'user' => Session::user(),
            'barangs' => $barangs,
            'suppliers' => $suppliers,
            'formAction' => '/admin/restock/store',
            'errors' => Session::get('_errors', []),
            'old' => Session::get('_old', []),
        ]);

        Session::remove('_errors');
        Session::remove('_old');
    }

    public function store(): void
    {
        // Cek akses
        $this->requireRole('admin');

        // Validasi input
        $data = [
            'tanggal' => trim($_POST['tanggal'] ?? date('Y-m-d')),
            'id_barang' => trim($_POST['id_barang'] ?? ''),
            'id_supplier' => trim($_POST['id_supplier'] ?? ''),
            'qty' => trim($_POST['qty'] ?? ''),
            'harga_beli' => trim($_POST['harga_beli'] ?? ''),
            'harga_jual_baru' => trim($_POST['harga_jual_baru'] ?? ''),
            'catatan' => trim($_POST['catatan'] ?? ''),
            'id_user' => (int) Session::userId(),
        ];

        Session::set('_old', $data);

        $errors = $this->validatePayload($data);

        $barang = null;
        $supplier = null;

        if (empty($errors['id_barang'])) {
            $barang = $this->barangModel->findActiveById((int) $data['id_barang']);

            if (!$barang) {
                $errors['id_barang'] = 'Barang tidak ditemukan atau sedang nonaktif.';
            }
        }

        if (empty($errors['id_supplier'])) {
            $supplier = $this->supplierModel->findById((int) $data['id_supplier']);

            if (!$supplier) {
                $errors['id_supplier'] = 'Supplier tidak ditemukan.';
            } elseif (($supplier['status'] ?? 'nonaktif') !== 'aktif') {
                $errors['id_supplier'] = 'Supplier sedang nonaktif.';
            }
        }

        if (!empty($errors)) {
            Session::set('_errors', $errors);
            $this->redirect('/admin/restock/create');
        }

        // Simpan dengan transaction
        $db = $this->restockModel->db();

        try {
            $db->beginTransaction();

            $restockId = $this->restockModel->create($data);

            if ($restockId <= 0) {
                throw new RuntimeException('Gagal menyimpan data restock.');
            }

            $stockUpdated = $this->barangModel->increaseStock(
                (int) $data['id_barang'],
                (int) $data['qty']
            );

            if (!$stockUpdated) {
                throw new RuntimeException('Gagal menambah stok barang.');
            }

            if ($data['harga_jual_baru'] !== '') {
                $hargaUpdated = $this->barangModel->updateHargaJual(
                    (int) $data['id_barang'],
                    (float) $data['harga_jual_baru']
                );

                if (!$hargaUpdated) {
                    throw new RuntimeException('Gagal memperbarui harga jual barang.');
                }
            }

            $db->commit();

            Session::remove('_old');
            Session::setFlash('success', 'Restock berhasil disimpan dan stok barang sudah bertambah.');
            $this->redirect('/admin/restock');
        } catch (Throwable $error) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            Session::setFlash('error', APP_DEBUG ? $error->getMessage() : 'Restock gagal disimpan.');
            $this->redirect('/admin/restock/create');
        }
    }

    private function validatePayload(array $data): array
    {
        // Validasi dasar
        $errors = Validator::validate($data, [
            'tanggal' => ['required', 'date'],
            'id_barang' => ['required', 'integer'],
            'id_supplier' => ['required', 'integer'],
            'qty' => ['required', 'integer'],
            'harga_beli' => ['required', 'numeric'],
        ]);

        // Validasi angka
        if ($data['qty'] !== '' && (int) $data['qty'] <= 0) {
            $errors['qty'] = 'Qty harus lebih dari 0.';
        }

        if ($data['harga_beli'] !== '' && (float) $data['harga_beli'] <= 0) {
            $errors['harga_beli'] = 'Harga beli harus lebih dari 0.';
        }

        if ($data['harga_jual_baru'] !== '') {
            if (!is_numeric($data['harga_jual_baru'])) {
                $errors['harga_jual_baru'] = 'Harga jual baru harus berupa angka.';
            } elseif ((float) $data['harga_jual_baru'] <= 0) {
                $errors['harga_jual_baru'] = 'Harga jual baru harus lebih dari 0.';
            }
        }

        if ((int) ($data['id_user'] ?? 0) <= 0) {
            $errors['id_user'] = 'Session user tidak valid. Silakan login ulang.';
        }

        return $errors;
    }
}