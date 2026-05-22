<?php
$title = $title ?? 'Data Kategori';
$activeMenu = $activeMenu ?? 'kategori';

$pageCss = ['assets/css/kategori.css'];

$kategoris = $kategoris ?? [];
$flash = $flash ?? [];

$success = $flash['success'] ?? null;
$error = $flash['error'] ?? null;

$totalKategori = count($kategoris);
$totalDenganDeskripsi = 0;
$totalTanpaDeskripsi = 0;

foreach ($kategoris as $kategoriItem) {
    $deskripsi = trim((string) ($kategoriItem['deskripsi'] ?? ''));

    if ($deskripsi !== '') {
        $totalDenganDeskripsi++;
    } else {
        $totalTanpaDeskripsi++;
    }
}

if (!function_exists('kategori_date')) {
    function kategori_date(mixed $value): string
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return '-';
        }

        $time = strtotime($raw);

        if ($time === false) {
            return $raw;
        }

        return date('d M Y, H:i', $time);
    }
}

if (!function_exists('kategori_short')) {
    function kategori_short(mixed $value, int $limit = 90): string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return '-';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            return mb_strlen($text) > $limit
                ? mb_substr($text, 0, $limit) . '...'
                : $text;
        }

        return strlen($text) > $limit
            ? substr($text, 0, $limit) . '...'
            : $text;
    }
}
?>

<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
<?php require APP_PATH . '/views/layouts/navbar.php'; ?>

<div class="kategori-page">
    <?php if ($success): ?>
        <div class="kategori-alert kategori-alert-success">
            <i class="ti ti-circle-check"></i>
            <span><?= app_e($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="kategori-alert kategori-alert-error">
            <i class="ti ti-alert-triangle"></i>
            <span><?= app_e($error) ?></span>
        </div>
    <?php endif; ?>

    <section class="kategori-hero" data-aos="fade-down" data-aos-duration="700">
        <div class="kategori-hero-content">
            <span class="kategori-eyebrow">
                <i class="ti ti-folder"></i>
                Master Kategori
            </span>

            <h2>Data Kategori</h2>
        </div>

        <div class="kategori-hero-actions">
            <a href="<?= app_e(app_url('/admin/kategori/create')) ?>" class="kategori-btn kategori-btn-primary">
                <i class="ti ti-plus"></i>
                Tambah Kategori
            </a>

            <a href="<?= app_e(app_url('/admin/barang')) ?>" class="kategori-btn kategori-btn-soft">
                <i class="ti ti-package"></i>
                Lihat Barang
            </a>
        </div>
    </section>

    <section class="kategori-summary" data-aos="fade-up" data-aos-delay="140">
        <article class="kategori-summary-card summary-green" data-aos="zoom-in" data-aos-delay="80">
            <span class="kategori-summary-icon">
                <i class="ti ti-folders"></i>
            </span>

            <div>
                <small>Total Kategori</small>
                <strong><?= app_e((string) $totalKategori) ?></strong>
                <p>Semua kategori</p>
            </div>
        </article>

        <article class="kategori-summary-card summary-blue" data-aos="zoom-in" data-aos-delay="180">
            <span class="kategori-summary-icon">
                <i class="ti ti-notes"></i>
            </span>

            <div>
                <small>Punya Deskripsi</small>
                <strong><?= app_e((string) $totalDenganDeskripsi) ?></strong>
                <p>Data lebih jelas</p>
            </div>
        </article>

        <article class="kategori-summary-card summary-orange" data-aos="zoom-in" data-aos-delay="280">
            <span class="kategori-summary-icon">
                <i class="ti ti-note-off"></i>
            </span>

            <div>
                <small>Tanpa Deskripsi</small>
                <strong><?= app_e((string) $totalTanpaDeskripsi) ?></strong>
                <p>Masih bisa dirapikan</p>
            </div>
        </article>
    </section>

    <section class="kategori-panel" data-aos="fade-up" data-aos-delay="200">
        <div class="kategori-panel-header">
            <div>
                <span>Inventori</span>
                <h3>Daftar Kategori</h3>
            </div>

            <div class="kategori-tools">
                <label class="kategori-search">
                    <i class="ti ti-search"></i>
                    <input
                        type="search"
                        placeholder="Cari nama atau deskripsi..."
                        data-kategori-search
                    >
                </label>

                <button type="button" class="kategori-btn kategori-btn-ghost" data-kategori-reset>
                    <i class="ti ti-refresh"></i>
                    Reset
                </button>
            </div>
        </div>

        <?php if (empty($kategoris)): ?>
            <div class="kategori-empty">
                <span>
                    <i class="ti ti-folder-off"></i>
                </span>

                <h4>Belum ada kategori</h4>

                <p>
                    Tambahkan kategori dulu supaya barang tidak numpuk di satu tempat seperti laci kabel bekas.
                </p>

                <a href="<?= app_e(app_url('/admin/kategori/create')) ?>" class="kategori-btn kategori-btn-primary">
                    <i class="ti ti-plus"></i>
                    Tambah Kategori
                </a>
            </div>
        <?php else: ?>
            <div class="kategori-table-wrap">
                <table class="kategori-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Dibuat</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>

                    <tbody data-kategori-table-body>
                        <?php foreach ($kategoris as $index => $kategoriItem): ?>
                            <?php
                            $nama = (string) ($kategoriItem['nama'] ?? '-');
                            $deskripsi = (string) ($kategoriItem['deskripsi'] ?? '');
                            $createdAt = $kategoriItem['created_at'] ?? '';
                            $searchText = strtolower(trim($nama . ' ' . $deskripsi . ' ' . $createdAt));
                            ?>

                            <tr data-kategori-row data-search="<?= app_e($searchText) ?>">
                                <td>
                                    <span class="kategori-number"><?= app_e((string) ($index + 1)) ?></span>
                                </td>

                                <td>
                                    <div class="kategori-name">
                                        <span class="kategori-name-icon">
                                            <i class="ti ti-folder"></i>
                                        </span>

                                        <div>
                                            <strong><?= app_e($nama) ?></strong>
                                            <small>ID: <?= app_e((string) ($kategoriItem['id'] ?? '-')) ?></small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="<?= trim($deskripsi) === '' ? 'kategori-desc is-empty' : 'kategori-desc' ?>">
                                        <?= app_e(kategori_short($deskripsi)) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="kategori-date">
                                        <i class="ti ti-calendar"></i>
                                        <?= app_e(kategori_date($createdAt)) ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="kategori-actions">
                                        <a
                                            href="<?= app_e(app_url('/admin/kategori/edit/' . ($kategoriItem['id'] ?? ''))) ?>"
                                            class="kategori-action-btn action-edit"
                                            title="Edit kategori"
                                            aria-label="Edit kategori"
                                        >
                                            <i class="ti ti-edit"></i>
                                        </a>

                                        <form
                                            action="<?= app_e(app_url('/admin/kategori/delete/' . ($kategoriItem['id'] ?? ''))) ?>"
                                            method="POST"
                                            data-kategori-delete-form
                                            data-confirm-title="Hapus Kategori"
                                            data-confirm-message="Kategori <?= app_e($nama) ?> akan dihapus kalau belum dipakai barang. Kalau masih dipakai, sistem akan menolak. Lanjut?"
                                        >
                                            <button
                                                type="submit"
                                                class="kategori-action-btn action-delete"
                                                title="Hapus kategori"
                                                aria-label="Hapus kategori"
                                            >
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="kategori-filter-empty" data-kategori-filter-empty hidden>
                    <span>
                        <i class="ti ti-search-off"></i>
                    </span>

                    <h4>Data tidak ketemu</h4>

                    <p>
                        Keyword-nya terlalu niat. Coba cari yang lebih masuk akal.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<script src="<?= app_e(app_asset_versioned('assets/js/kategori.js')) ?>"></script>

<?php
$pagination = $pagination ?? null;
if ($pagination) {
    require APP_PATH . '/views/components/pagination.php';
}
?>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>
<?php require APP_PATH . '/views/layouts/scripts.php'; ?>