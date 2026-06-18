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
        $filterTipe = trim($_GET['tipe'] ?? '');
        $filterBarang = trim($_GET['id_barang'] ?? '');
        $filterSupplier = trim($_GET['id_supplier'] ?? '');

        if (!in_array($filterTipe, ['', 'masuk', 'keluar'], true)) {
            $filterTipe = '';
        }

        $filterBarangId = ctype_digit($filterBarang) && (int) $filterBarang > 0
            ? (int) $filterBarang
            : null;

        $filterSupplierId = ctype_digit($filterSupplier) && (int) $filterSupplier > 0
            ? (int) $filterSupplier
            : null;

        if ($filterBarangId === null) {
            $filterBarang = '';
        }

        if ($filterSupplierId === null) {
            $filterSupplier = '';
        }

        $hasFilter = $tanggalMulai !== ''
            || $tanggalSelesai !== ''
            || $filterTipe !== ''
            || $filterBarangId !== null
            || $filterSupplierId !== null;

        // Ambil data opsi filter
        $barangs = $this->barangModel->getActive();
        $suppliers = $this->supplierModel->getActive();

        // Ambil data
        if ($hasFilter) {
            $restocks = $this->restockModel->getFiltered(
                $tanggalMulai,
                $tanggalSelesai,
                $filterTipe,
                500,
                $filterBarangId,
                $filterSupplierId
            );

            $summary = $this->restockModel->summary(
                $tanggalMulai,
                $tanggalSelesai,
                $filterTipe,
                $filterBarangId,
                $filterSupplierId
            );

            $pagination = null;
        } else {
            // Pagination
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $perPage = 10;
            $total = $this->restockModel->countAll();
            $totalPages = max(1, (int) ceil($total / $perPage));

            $restocks = $this->restockModel->getPaginated($page, $perPage);
            $summary = $this->restockModel->summary();
            $pagination = [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ];
        }

        // Tampilkan halaman
        $this->view('admin/restock/index', [
            'title' => 'Restock & Penyesuaian Stok',
            'activeMenu' => 'restock',
            'user' => Session::user(),
            'restocks' => $restocks,
            'summary' => $summary,
            'pagination' => $pagination,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'filterTipe' => $filterTipe,
            'filterBarang' => $filterBarang,
            'filterSupplier' => $filterSupplier,
            'barangs' => $barangs,
            'suppliers' => $suppliers,
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

        // Ambil tipe dari query string (default: masuk)
        $tipe = trim($_GET['tipe'] ?? 'masuk');
        if (!in_array($tipe, ['masuk', 'keluar'], true)) {
            $tipe = 'masuk';
        }

        // Ambil data form
        $barangs = $this->barangModel->getActive();
        $suppliers = $this->supplierModel->getActive();

        if (empty($barangs)) {
            Session::setFlash('error', 'Belum ada barang aktif. Tambahkan barang dulu.');
            $this->redirect('/admin/barang/create');
        }

        if ($tipe === 'masuk' && empty($suppliers)) {
            Session::setFlash('error', 'Belum ada supplier aktif. Tambahkan supplier dulu.');
            $this->redirect('/admin/supplier/create');
        }

        // Tampilkan form
        $this->view('admin/restock/form', [
            'title' => $tipe === 'masuk' ? 'Tambah Stok Masuk' : 'Kurangi Stok',
            'activeMenu' => 'restock',
            'user' => Session::user(),
            'tipe' => $tipe,
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

        // Ambil tipe
        $tipe = trim($_POST['tipe'] ?? 'masuk');
        if (!in_array($tipe, ['masuk', 'keluar'], true)) {
            $tipe = 'masuk';
        }

        // Validasi input
        $data = [
            'tipe' => $tipe,
            'tanggal' => trim($_POST['tanggal'] ?? date('Y-m-d')),
            'id_barang' => trim($_POST['id_barang'] ?? ''),
            'id_supplier' => trim($_POST['id_supplier'] ?? ''),
            'qty' => trim($_POST['qty'] ?? ''),
            'harga_beli' => trim($_POST['harga_beli'] ?? ''),
            'harga_jual_baru' => trim($_POST['harga_jual_baru'] ?? ''),
            'catatan' => trim($_POST['catatan'] ?? ''),
            'alasan' => trim($_POST['alasan'] ?? ''),
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

        // Supplier wajib untuk tipe masuk
        if ($tipe === 'masuk' && empty($errors['id_supplier'])) {
            $supplier = $this->supplierModel->findById((int) $data['id_supplier']);

            if (!$supplier) {
                $errors['id_supplier'] = 'Supplier tidak ditemukan.';
            } elseif (($supplier['status'] ?? 'nonaktif') !== 'aktif') {
                $errors['id_supplier'] = 'Supplier sedang nonaktif.';
            }
        }

        // Validasi stok cukup untuk tipe keluar
        if ($tipe === 'keluar' && $barang && empty($errors['qty'])) {
            $stokSekarang = (int) ($barang['stok'] ?? 0);
            $qtyKeluar = (int) $data['qty'];

            if ($qtyKeluar > $stokSekarang) {
                $errors['qty'] = 'Qty keluar (' . $qtyKeluar . ') melebihi stok tersedia (' . $stokSekarang . ').';
            }
        }

        if (!empty($errors)) {
            Session::set('_errors', $errors);
            $this->redirect('/admin/restock/create?tipe=' . $tipe);
        }

        // Simpan dengan transaction
        $db = $this->restockModel->db();

        try {
            $db->beginTransaction();

            // Untuk tipe keluar, supplier bisa null
            $restockData = [
                'tipe' => $tipe,
                'tanggal' => $data['tanggal'],
                'id_barang' => (int) $data['id_barang'],
                'id_supplier' => $tipe === 'masuk' ? (int) $data['id_supplier'] : ($data['id_supplier'] !== '' ? (int) $data['id_supplier'] : null),
                'id_user' => $data['id_user'],
                'qty' => (int) $data['qty'],
                'harga_beli' => (float) $data['harga_beli'],
                'harga_jual_baru' => $data['harga_jual_baru'],
                'catatan' => $data['catatan'],
                'alasan' => $tipe === 'keluar' ? $data['alasan'] : null,
            ];

            $restockId = $this->restockModel->create($restockData);

            if ($restockId <= 0) {
                throw new RuntimeException('Gagal menyimpan data restock.');
            }

            if ($tipe === 'masuk') {
                // Tambah stok
                $stockUpdated = $this->barangModel->increaseStock(
                    (int) $data['id_barang'],
                    (int) $data['qty']
                );

                if (!$stockUpdated) {
                    throw new RuntimeException('Gagal menambah stok barang.');
                }

                // Update harga jual jika diisi
                if ($data['harga_jual_baru'] !== '') {
                    $hargaUpdated = $this->barangModel->updateHargaJual(
                        (int) $data['id_barang'],
                        (float) $data['harga_jual_baru']
                    );

                    if (!$hargaUpdated) {
                        throw new RuntimeException('Gagal memperbarui harga jual barang.');
                    }
                }
            } else {
                // Kurangi stok
                $stockUpdated = $this->barangModel->decreaseStock(
                    (int) $data['id_barang'],
                    (int) $data['qty']
                );

                if (!$stockUpdated) {
                    throw new RuntimeException('Gagal mengurangi stok barang. Stok tidak cukup.');
                }
            }

            $db->commit();

            Session::remove('_old');

            $message = $tipe === 'masuk'
                ? 'Restock berhasil disimpan dan stok barang sudah bertambah.'
                : 'Stok barang berhasil dikurangi.';

            Session::setFlash('success', $message);
            $this->redirect('/admin/restock');
        } catch (Throwable $error) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            Session::setFlash('error', APP_DEBUG ? $error->getMessage() : 'Restock gagal disimpan.');
            $this->redirect('/admin/restock/create?tipe=' . $tipe);
        }
    }

    private function validatePayload(array $data): array
    {
        $tipe = $data['tipe'] ?? 'masuk';

        // Validasi dasar
        $rules = [
            'tanggal' => ['required', 'date'],
            'id_barang' => ['required', 'integer'],
            'qty' => ['required', 'integer'],
            'harga_beli' => ['required', 'numeric'],
        ];

        // Supplier wajib hanya untuk tipe masuk
        if ($tipe === 'masuk') {
            $rules['id_supplier'] = ['required', 'integer'];
        }

        $errors = Validator::validate($data, $rules);

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

        // Alasan wajib untuk tipe keluar
        if ($tipe === 'keluar' && trim($data['alasan'] ?? '') === '') {
            $errors['alasan'] = 'Alasan pengurangan stok wajib diisi.';
        }

        if ((int) ($data['id_user'] ?? 0) <= 0) {
            $errors['id_user'] = 'Session user tidak valid. Silakan login ulang.';
        }

        return $errors;
    }
}
