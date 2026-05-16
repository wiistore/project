<?php

declare(strict_types=1);

class BarangController extends Controller
{
    private $barangModel;
    private $kategoriModel;

    public function __construct()
    {
        $this->barangModel = $this->model('Barang');
        $this->kategoriModel = $this->model('Kategori');
    }

    public function index(): void
    {
        // Cek akses
        $this->requireRole('admin');

        // Ambil data
        $barangs = $this->barangModel->getAll();
        $summary = $this->barangModel->summary();

        // Tampilkan halaman
        $this->view('admin/barang/index', [
            'title' => 'Data Barang',
            'activeMenu' => 'barang',
            'user' => Session::user(),
            'barangs' => $barangs,
            'barang' => $barangs,
            'summary' => $summary,
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

        // Ambil kategori
        $kategoris = $this->kategoriModel->getAll();

        if (empty($kategoris)) {
            Session::setFlash('error', 'Buat kategori dulu sebelum tambah barang.');
            $this->redirect('/admin/kategori/create');
        }

        // Tampilkan form
        $this->view('admin/barang/form', [
            'title' => 'Tambah Barang',
            'activeMenu' => 'barang',
            'user' => Session::user(),
            'formAction' => '/admin/barang/store',
            'formMode' => 'create',
            'barang' => null,
            'barangs' => [],
            'kategoris' => $kategoris,
            'kategori' => $kategoris,
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
        $data = $this->payload();

        if ($data['barcode'] === '') {
            $data['barcode'] = $this->generateBarcode();
        }

        Session::set('_old', $data);

        $errors = $this->validatePayload($data);

        if ($this->barangModel->kodeExists($data['kode_barang'])) {
            $errors['kode_barang'] = 'Kode barang sudah dipakai.';
        }

        if ($this->barangModel->barcodeExists($data['barcode'])) {
            $errors['barcode'] = 'Barcode sudah dipakai.';
        }

        if (!empty($errors)) {
            Session::set('_errors', $errors);
            $this->redirect('/admin/barang/create');
        }

        // Simpan data
        $created = $this->barangModel->create($data);

        if (!$created) {
            Session::setFlash('error', 'Barang gagal ditambahkan.');
            $this->redirect('/admin/barang/create');
        }

        Session::remove('_old');
        Session::setFlash('success', 'Barang berhasil ditambahkan.');
        $this->redirect('/admin/barang');
    }

    public function detail($id): void
    {
        // Cek akses
        $this->requireRole('admin');

        $id = (int) $id;
        $barang = $this->barangModel->findById($id);

        if (!$barang) {
            Session::setFlash('error', 'Barang tidak ditemukan.');
            $this->redirect('/admin/barang');
        }

        // Tampilkan detail
        $this->view('admin/barang/detail', [
            'title' => 'Detail Barang',
            'activeMenu' => 'barang',
            'user' => Session::user(),
            'barang' => $barang,
        ]);
    }

    public function edit($id): void
    {
        // Cek akses
        $this->requireRole('admin');

        $id = (int) $id;
        $barang = $this->barangModel->findById($id);

        if (!$barang) {
            Session::setFlash('error', 'Barang tidak ditemukan.');
            $this->redirect('/admin/barang');
        }

        // Ambil kategori
        $kategoris = $this->kategoriModel->getAll();

        if (empty($kategoris)) {
            Session::setFlash('error', 'Kategori belum tersedia.');
            $this->redirect('/admin/barang');
        }

        // Tampilkan form
        $this->view('admin/barang/form', [
            'title' => 'Edit Barang',
            'activeMenu' => 'barang',
            'user' => Session::user(),
            'formAction' => '/admin/barang/update/' . $id,
            'formMode' => 'edit',
            'barang' => $barang,
            'barangs' => [],
            'kategoris' => $kategoris,
            'kategori' => $kategoris,
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
        $barang = $this->barangModel->findById($id);

        if (!$barang) {
            Session::setFlash('error', 'Barang tidak ditemukan.');
            $this->redirect('/admin/barang');
        }

        // Validasi input
        $data = $this->payload();

        if ($data['barcode'] === '') {
            $data['barcode'] = $barang['barcode'] ?: $this->generateBarcode();
        }

        Session::set('_old', $data);

        $errors = $this->validatePayload($data);

        if ($this->barangModel->kodeExists($data['kode_barang'], $id)) {
            $errors['kode_barang'] = 'Kode barang sudah dipakai.';
        }

        if ($this->barangModel->barcodeExists($data['barcode'], $id)) {
            $errors['barcode'] = 'Barcode sudah dipakai.';
        }

        if (!empty($errors)) {
            Session::set('_errors', $errors);
            $this->redirect('/admin/barang/edit/' . $id);
        }

        // Simpan perubahan
        $updated = $this->barangModel->update($id, $data);

        if (!$updated) {
            Session::setFlash('error', 'Barang gagal diperbarui.');
            $this->redirect('/admin/barang/edit/' . $id);
        }

        Session::remove('_old');
        Session::setFlash('success', 'Barang berhasil diperbarui.');
        $this->redirect('/admin/barang');
    }

    public function delete($id): void
    {
        // Cek akses
        $this->requireRole('admin');

        $id = (int) $id;
        $barang = $this->barangModel->findById($id);

        if (!$barang) {
            Session::setFlash('error', 'Barang tidak ditemukan.');
            $this->redirect('/admin/barang');
        }

        // Hapus atau nonaktifkan
        $hasHistory = $this->barangModel->hasHistory($id);
        $deleted = $this->barangModel->deleteOrDeactivate($id);

        if (!$deleted) {
            Session::setFlash('error', 'Barang gagal diproses.');
            $this->redirect('/admin/barang');
        }

        if ($hasHistory) {
            Session::setFlash('success', 'Barang sudah punya histori, jadi dinonaktifkan.');
        } else {
            Session::setFlash('success', 'Barang berhasil dihapus.');
        }

        $this->redirect('/admin/barang');
    }

    private function payload(): array
    {
        return [
            'kode_barang' => trim($_POST['kode_barang'] ?? ''),
            'barcode' => trim($_POST['barcode'] ?? ''),
            'nama' => trim($_POST['nama'] ?? ''),
            'id_kategori' => trim($_POST['id_kategori'] ?? ''),
            'satuan' => trim($_POST['satuan'] ?? 'pcs'),
            'harga_jual' => trim($_POST['harga_jual'] ?? ''),
            'stok_minimum' => trim($_POST['stok_minimum'] ?? '5'),
            'status' => trim($_POST['status'] ?? 'aktif'),
        ];
    }

    private function validatePayload(array $data): array
    {
        // Validasi dasar
        $errors = Validator::validate($data, [
            'kode_barang' => ['required', 'max:50'],
            'barcode' => ['required', 'max:100'],
            'nama' => ['required', 'max:150'],
            'id_kategori' => ['required', 'integer'],
            'satuan' => ['required', 'max:30'],
            'harga_jual' => ['required', 'numeric'],
            'stok_minimum' => ['required', 'integer'],
        ]);

        // Validasi tambahan
        if ($data['harga_jual'] !== '' && (float) $data['harga_jual'] <= 0) {
            $errors['harga_jual'] = 'Harga jual harus lebih dari 0.';
        }

        if ($data['stok_minimum'] !== '' && (int) $data['stok_minimum'] < 0) {
            $errors['stok_minimum'] = 'Stok minimum tidak boleh minus.';
        }

        if (!Validator::in($data['status'], ['aktif', 'nonaktif'])) {
            $errors['status'] = 'Status tidak valid.';
        }

        if ($data['id_kategori'] !== '' && !$this->kategoriModel->findById((int) $data['id_kategori'])) {
            $errors['id_kategori'] = 'Kategori tidak ditemukan.';
        }

        return $errors;
    }

    private function generateBarcode(): string
    {
        // Generate barcode internal
        do {
            $barcode = 'KPS' . date('Ymd') . random_int(1000, 9999);
        } while ($this->barangModel->barcodeExists($barcode));

        return $barcode;
    }
}