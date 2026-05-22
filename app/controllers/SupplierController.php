<?php

declare(strict_types=1);

class SupplierController extends Controller
{
    private $supplierModel;

    public function __construct()
    {
        $this->supplierModel = $this->model('Supplier');
    }

    public function index(): void
    {
        // Cek akses
        $this->requireRole('admin');

        // Pagination
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $total = $this->supplierModel->countAll();
        $totalPages = max(1, (int) ceil($total / $perPage));

        // Ambil data
        $suppliers = $this->supplierModel->getPaginated($page, $perPage);

        // Tampilkan halaman
        $this->view('admin/supplier/index', [
            'title' => 'Data Supplier',
            'activeMenu' => 'supplier',
            'user' => Session::user(),
            'suppliers' => $suppliers,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ],
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

        // Tampilkan form
        $this->view('admin/supplier/form', [
            'title' => 'Tambah Supplier',
            'activeMenu' => 'supplier',
            'user' => Session::user(),
            'formAction' => '/admin/supplier/store',
            'formMode' => 'create',
            'supplier' => null,
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
            'nama' => trim($_POST['nama'] ?? ''),
            'kontak_person' => trim($_POST['kontak_person'] ?? ''),
            'no_hp' => trim($_POST['no_hp'] ?? ''),
            'alamat' => trim($_POST['alamat'] ?? ''),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'status' => trim($_POST['status'] ?? 'aktif'),
        ];

        Session::set('_old', $data);

        $errors = Validator::validate($data, [
            'nama' => ['required', 'max:150'],
            'kontak_person' => ['max:100'],
            'no_hp' => ['max:20'],
        ]);

        if (!Validator::in($data['status'], ['aktif', 'nonaktif'])) {
            $errors['status'] = 'Status tidak valid.';
        }

        if ($this->supplierModel->namaExists($data['nama'])) {
            $errors['nama'] = 'Nama supplier sudah dipakai.';
        }

        if (!empty($errors)) {
            Session::set('_errors', $errors);
            $this->redirect('/admin/supplier/create');
        }

        // Simpan data
        $created = $this->supplierModel->create($data);

        if (!$created) {
            Session::setFlash('error', 'Supplier gagal ditambahkan.');
            $this->redirect('/admin/supplier/create');
        }

        Session::remove('_old');
        Session::setFlash('success', 'Supplier berhasil ditambahkan.');
        $this->redirect('/admin/supplier');
    }

    public function edit($id): void
    {
        // Cek akses
        $this->requireRole('admin');

        $id = (int) $id;
        $supplier = $this->supplierModel->findById($id);

        if (!$supplier) {
            Session::setFlash('error', 'Supplier tidak ditemukan.');
            $this->redirect('/admin/supplier');
        }

        // Tampilkan form
        $this->view('admin/supplier/form', [
            'title' => 'Edit Supplier',
            'activeMenu' => 'supplier',
            'user' => Session::user(),
            'formAction' => '/admin/supplier/update/' . $id,
            'formMode' => 'edit',
            'supplier' => $supplier,
            'errors' => Session::get('_errors', []),
            'old' => Session::get('_old', []),
        ]);

        Session::remove('_errors');
        Session::remove('_old');
    }

    public function update($id): void
    {
        // Cek akses
        $this->requireRole('admin');

        $id = (int) $id;
        $supplier = $this->supplierModel->findById($id);

        if (!$supplier) {
            Session::setFlash('error', 'Supplier tidak ditemukan.');
            $this->redirect('/admin/supplier');
        }

        // Validasi input
        $data = [
            'nama' => trim($_POST['nama'] ?? ''),
            'kontak_person' => trim($_POST['kontak_person'] ?? ''),
            'no_hp' => trim($_POST['no_hp'] ?? ''),
            'alamat' => trim($_POST['alamat'] ?? ''),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'status' => trim($_POST['status'] ?? 'aktif'),
        ];

        Session::set('_old', $data);

        $errors = Validator::validate($data, [
            'nama' => ['required', 'max:150'],
            'kontak_person' => ['max:100'],
            'no_hp' => ['max:20'],
        ]);

        if (!Validator::in($data['status'], ['aktif', 'nonaktif'])) {
            $errors['status'] = 'Status tidak valid.';
        }

        if ($this->supplierModel->namaExists($data['nama'], $id)) {
            $errors['nama'] = 'Nama supplier sudah dipakai.';
        }

        if (!empty($errors)) {
            Session::set('_errors', $errors);
            $this->redirect('/admin/supplier/edit/' . $id);
        }

        // Simpan perubahan
        $updated = $this->supplierModel->update($id, $data);

        if (!$updated) {
            Session::setFlash('error', 'Supplier gagal diperbarui.');
            $this->redirect('/admin/supplier/edit/' . $id);
        }

        Session::remove('_old');
        Session::setFlash('success', 'Supplier berhasil diperbarui.');
        $this->redirect('/admin/supplier');
    }

    public function delete($id): void
    {
        // Cek akses
        $this->requireRole('admin');

        $id = (int) $id;
        $supplier = $this->supplierModel->findById($id);

        if (!$supplier) {
            Session::setFlash('error', 'Supplier tidak ditemukan.');
            $this->redirect('/admin/supplier');
        }

        // Cek relasi
        if ($this->supplierModel->isUsedByRestock($id)) {
            Session::setFlash('error', 'Supplier memiliki histori restock. Gunakan toggle aktif/nonaktif.');
            $this->redirect('/admin/supplier');
        }

        // Hapus permanen
        $deleted = $this->supplierModel->deleteOrDeactivate($id);

        if (!$deleted) {
            Session::setFlash('error', 'Supplier gagal dihapus.');
            $this->redirect('/admin/supplier');
        }

        Session::setFlash('success', 'Supplier berhasil dihapus permanen.');
        $this->redirect('/admin/supplier');
    }

    public function toggleStatus($id): void
    {
        $this->requireRole('admin');

        $id = (int) $id;
        $supplier = $this->supplierModel->findById($id);

        if (!$supplier) {
            Session::setFlash('error', 'Supplier tidak ditemukan.');
            $this->redirect('/admin/supplier');
        }

        $currentStatus = strtolower((string) ($supplier['status'] ?? 'aktif'));
        $newStatus = $currentStatus === 'aktif' ? 'nonaktif' : 'aktif';

        $updated = $this->supplierModel->updateStatus($id, $newStatus);

        if (!$updated) {
            Session::setFlash('error', 'Gagal mengubah status supplier.');
            $this->redirect('/admin/supplier');
        }

        $label = $newStatus === 'aktif' ? 'diaktifkan' : 'dinonaktifkan';
        Session::setFlash('success', 'Supplier berhasil ' . $label . '.');
        $this->redirect('/admin/supplier');
    }
}