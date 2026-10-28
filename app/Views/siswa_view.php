<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa RPL</title>

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 (Vector Icons, No Emoji) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- CDN CSS Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Modern UI/UX Stylesheet -->
    <link href="<?= base_url('css/style.css'); ?>" rel="stylesheet">
</head>
<body>
    <?php
        // Helper function: Sorting Links with direction toggle
        $sortLink = static function (string $column) use ($sort, $direction): string {
            $nextDirection = ($sort === $column && $direction === 'ASC') ? 'DESC' : 'ASC';
            $page = service('request')->getGet('page');

            $params = [
                'sort' => $column,
                'direction' => $nextDirection,
            ];
            if ($page !== null) {
                $params['page'] = $page;
            }

            return current_url() . '?' . http_build_query($params);
        };

        // Helper function: Sort Column Icon
        $getSortIcon = static function (string $column) use ($sort, $direction): string {
            if ($sort !== $column) {
                return '<i class="fa-solid fa-sort sort-indicator inactive" aria-hidden="true"></i>';
            }
            if ($direction === 'ASC') {
                return '<i class="fa-solid fa-arrow-up-wide-short sort-indicator active" aria-hidden="true"></i>';
            }
            return '<i class="fa-solid fa-arrow-down-wide-short sort-indicator active" aria-hidden="true"></i>';
        };

        // Helper function: Page Link with preserved sort parameters
        $pageLink = static function (?string $uri) use ($sort, $direction): ?string {
            if ($uri === null) {
                return null;
            }
            $parsed = parse_url($uri);
            $queryParams = [];
            if (!empty($parsed['query'])) {
                parse_str($parsed['query'], $queryParams);
            }
            if (!empty($sort) && $sort !== 'id') {
                $queryParams['sort'] = $sort;
            }
            if (!empty($direction) && $direction !== 'ASC') {
                $queryParams['direction'] = $direction;
            }
            $base = $parsed['path'] ?? current_url();
            return $base . (!empty($queryParams) ? '?' . http_build_query($queryParams) : '');
        };
    ?>

    <!-- Ikon Komputer Transparan Berwarna Putih di Latar Belakang -->
    <div class="bg-decorations" aria-hidden="true">
        <div class="bg-decor-item bg-decor-top-right">
            <i class="fa-solid fa-desktop"></i>
        </div>
        <div class="bg-decor-item bg-decor-bottom-left">
            <i class="fa-solid fa-laptop-code"></i>
        </div>
        <div class="bg-decor-item bg-decor-center-right">
            <i class="fa-solid fa-display"></i>
        </div>
        <div class="bg-decor-item bg-decor-top-left">
            <i class="fa-solid fa-computer"></i>
        </div>
    </div>

    <!-- 1. Top Navbar -->
    <header style="position: relative; z-index: 10;">
        <nav class="navbar-app" aria-label="Navigasi Utama">
            <div class="container d-flex align-items-center justify-content-between">
                <a class="text-decoration-none" href="<?= base_url(); ?>">
                    <span class="brand-title">Data Siswa RPL</span>
                </a>
            </div>
        </nav>
    </header>

    <!-- 2. Container Utama -->
    <main class="container my-4" style="position: relative; z-index: 1;">

        <!-- Flash Message Notifications -->
        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert-modern alert-modern-success alert-dismissible fade show" role="alert" id="flashAlert">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-check alert-icon-wrapper" aria-hidden="true"></i>
                    <div><?= esc(session()->getFlashdata('success')); ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert-modern alert-modern-danger alert-dismissible fade show" role="alert" id="flashAlert">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation alert-icon-wrapper" aria-hidden="true"></i>
                    <div><?= esc(session()->getFlashdata('error')); ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <!-- Main Card Section -->
        <div class="card-main">
            <!-- Toolbar: Pencarian Cepat & Tombol Tambah Data -->
            <div class="card-toolbar d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="toolbar-left">
                    <div class="search-input-group">
                        <i class="fa-solid fa-magnifying-glass search-icon" aria-hidden="true"></i>
                        <input type="text"
                               id="tableSearchInput"
                               class="form-control search-input"
                               placeholder="Cari NIS, nama, atau kelas..."
                               aria-label="Cari data siswa"
                               autocomplete="off">
                        <button type="button" id="clearSearchBtn" class="search-clear-btn" aria-label="Hapus pencarian">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>
                    <span id="searchCountBadge" class="badge bg-light text-secondary border px-3 py-2 d-none text-nowrap">
                        0 ditemukan
                    </span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button"
                            class="btn-modern-primary text-nowrap"
                            data-bs-toggle="modal"
                            data-bs-target="#modalTambah"
                            id="btnBukaModalTambah">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        <span>Tambah Data Siswa</span>
                    </button>
                </div>
            </div>

            <!-- Tabel Data Siswa -->
            <div class="table-container">
                <table class="table-modern" id="tableSiswa">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center" style="width: 60px;">No</th>
                            <th scope="col" style="width: 170px;">
                                <a class="sort-link-modern" href="<?= esc($sortLink('nis')); ?>" aria-label="Urutkan berdasarkan NIS">
                                    <span>NIS</span>
                                    <?= $getSortIcon('nis'); ?>
                                </a>
                            </th>
                            <th scope="col">
                                <a class="sort-link-modern" href="<?= esc($sortLink('nama')); ?>" aria-label="Urutkan berdasarkan Nama">
                                    <span>Nama</span>
                                    <?= $getSortIcon('nama'); ?>
                                </a>
                            </th>
                            <th scope="col" style="width: 180px;">
                                <a class="sort-link-modern" href="<?= esc($sortLink('kelas')); ?>" aria-label="Urutkan berdasarkan Kelas">
                                    <span>Kelas</span>
                                    <?= $getSortIcon('kelas'); ?>
                                </a>
                            </th>
                            <th scope="col" class="text-center" style="width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="siswaTableBody">
                        <?php if (!empty($siswa)) : ?>
                            <?php $no = (($pager->getCurrentPage() - 1) * 7) + 1; ?>
                            <?php foreach ($siswa as $row) : ?>
                                <tr class="siswa-row"
                                    data-nis="<?= esc(strtolower($row['nis'])); ?>"
                                    data-nama="<?= esc(strtolower($row['nama'])); ?>"
                                    data-kelas="<?= esc(strtolower($row['kelas'])); ?>">
                                    <td class="text-center">
                                        <span class="badge-number"><?= $no++; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-nis"><?= esc($row['nis']); ?></span>
                                    </td>
                                    <td>
                                        <span class="student-name-text"><?= esc($row['nama']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-kelas">
                                            <i class="fa-solid fa-graduation-cap me-1" aria-hidden="true"></i>
                                            <?= esc($row['kelas']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1" role="group" aria-label="Tindakan">
                                            <!-- Tombol Edit -->
                                            <button type="button"
                                                    class="btn-action btn-action-edit"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEdit<?= $row['id']; ?>"
                                                    title="Edit Data Siswa"
                                                    aria-label="Edit <?= esc($row['nama']); ?>">
                                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <button type="button"
                                                    class="btn-action btn-action-delete"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalHapus<?= $row['id']; ?>"
                                                    title="Hapus Data Siswa"
                                                    aria-label="Hapus <?= esc($row['nama']); ?>">
                                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit Data Siswa -->
                                <div class="modal fade" id="modalEdit<?= $row['id']; ?>" tabindex="-1"
                                     aria-labelledby="modalEditLabel<?= $row['id']; ?>" aria-hidden="true" data-bs-backdrop="static">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-content-modern">
                                            <div class="modal-header-modern">
                                                <h5 class="modal-title-modern" id="modalEditLabel<?= $row['id']; ?>">
                                                    Edit Data Siswa
                                                </h5>
                                                <button type="button" class="btn-close btn-close-modal-edit" aria-label="Tutup"></button>
                                            </div>

                                            <form action="<?= base_url('home/edit/' . $row['id']); ?>" method="POST" class="modal-edit-form needs-validation" novalidate>
                                                <?= csrf_field(); ?>
                                                <div class="modal-body-modern">
                                                    <!-- Input NIS -->
                                                    <div class="mb-3">
                                                        <label for="edit_nis_<?= $row['id']; ?>" class="form-label-modern">
                                                            <span>NIS</span>
                                                            <span class="required-mark" title="Wajib diisi">*</span>
                                                        </label>
                                                        <div class="input-group-modern">
                                                            <span class="input-icon-box">
                                                                <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                                                            </span>
                                                            <input type="text"
                                                                   name="nis"
                                                                   id="edit_nis_<?= $row['id']; ?>"
                                                                   class="form-control input-modern"
                                                                   value="<?= esc($row['nis']); ?>"
                                                                   placeholder="NIS"
                                                                   required
                                                                   autocomplete="off">
                                                            <div class="form-invalid-feedback">
                                                                NIS wajib diisi.
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Input Nama Lengkap -->
                                                    <div class="mb-3">
                                                        <label for="edit_nama_<?= $row['id']; ?>" class="form-label-modern">
                                                            <span>Nama</span>
                                                            <span class="required-mark" title="Wajib diisi">*</span>
                                                        </label>
                                                        <div class="input-group-modern">
                                                            <span class="input-icon-box">
                                                                <i class="fa-solid fa-user" aria-hidden="true"></i>
                                                            </span>
                                                            <input type="text"
                                                                   name="nama"
                                                                   id="edit_nama_<?= $row['id']; ?>"
                                                                   class="form-control input-modern"
                                                                   value="<?= esc($row['nama']); ?>"
                                                                   placeholder="Nama Lengkap"
                                                                   required
                                                                   autocomplete="off">
                                                            <div class="form-invalid-feedback">
                                                                Nama wajib diisi.
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Input Kelas -->
                                                    <div class="mb-1">
                                                        <label for="edit_kelas_<?= $row['id']; ?>" class="form-label-modern">
                                                            <span>Kelas</span>
                                                            <span class="required-mark" title="Wajib diisi">*</span>
                                                        </label>
                                                        <div class="input-group-modern">
                                                            <span class="input-icon-box">
                                                                <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                                                            </span>
                                                            <input type="text"
                                                                   name="kelas"
                                                                   id="edit_kelas_<?= $row['id']; ?>"
                                                                   list="kelasListEdit<?= $row['id']; ?>"
                                                                   class="form-control input-modern"
                                                                   value="<?= esc($row['kelas']); ?>"
                                                                   placeholder="Kelas"
                                                                   required
                                                                   autocomplete="off">
                                                            <datalist id="kelasListEdit<?= $row['id']; ?>">
                                                                <option value="X RPL 1">
                                                                <option value="X RPL 2">
                                                                <option value="XI RPL 1">
                                                                <option value="XI RPL 2">
                                                                <option value="XII RPL 1">
                                                                <option value="XII RPL 2">
                                                            </datalist>
                                                            <div class="form-invalid-feedback">
                                                                Kelas wajib diisi.
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal-footer-modern">
                                                    <button type="button" class="btn-modern-secondary btn-batal-modal-edit">
                                                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                                        <span>Batal</span>
                                                    </button>
                                                    <button type="submit" class="btn-modern-primary">
                                                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                                                        <span>Simpan Perubahan</span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Konfirmasi Hapus Siswa -->
                                <div class="modal fade" id="modalHapus<?= $row['id']; ?>" tabindex="-1"
                                     aria-labelledby="modalHapusLabel<?= $row['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content modal-content-modern">
                                            <div class="modal-header-modern">
                                                <h5 class="modal-title-modern text-danger" id="modalHapusLabel<?= $row['id']; ?>">
                                                    Hapus Data Siswa
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                            </div>

                                            <div class="modal-body-modern">
                                                <p class="text-secondary mb-3">
                                                    Yakin ingin menghapus data siswa ini?
                                                </p>
                                                <div class="p-3 bg-light rounded-3 border">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="text-muted small">NIS:</span>
                                                        <span class="fw-bold font-monospace"><?= esc($row['nis']); ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="text-muted small">Nama:</span>
                                                        <span class="fw-bold text-dark"><?= esc($row['nama']); ?></span>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span class="text-muted small">Kelas:</span>
                                                        <span class="badge bg-primary-subtle text-primary border"><?= esc($row['kelas']); ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer-modern">
                                                <button type="button" class="btn-modern-secondary" data-bs-dismiss="modal">
                                                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                                    <span>Batal</span>
                                                </button>
                                                <a href="<?= base_url('home/hapus/' . $row['id']); ?>" class="btn btn-danger btn-modern-primary bg-danger border-danger">
                                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                    <span>Hapus</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state-card">
                                        <div class="empty-state-icon">
                                            <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                                        </div>
                                        <div class="empty-state-title">Belum Ada Data Siswa</div>
                                        <button type="button" class="btn-modern-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                            <i class="fa-solid fa-plus" aria-hidden="true"></i>
                                            <span>Tambah Data Siswa</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <!-- Baris Pesan Pencarian Nihil -->
                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="5">
                                <div class="empty-state-card py-4">
                                    <div class="empty-state-title">Data Tidak Ditemukan</div>
                                    <button type="button" id="btnResetSearch" class="btn btn-sm btn-outline-secondary mt-2">
                                        <i class="fa-solid fa-arrow-rotate-left me-1" aria-hidden="true"></i>
                                        <span>Reset Pencarian</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Tabel: Paginasi -->
            <?php
                $previousPage = $pager->getPreviousPageURI();
                $nextPage = $pager->getNextPageURI();
                $currentPage = $pager->getCurrentPage();
                $pageCount = max(1, $pager->getPageCount());
            ?>
            <div class="card-footer-modern justify-content-center">
                <nav aria-label="Navigasi halaman data siswa">
                    <ul class="pagination-modern justify-content-center">
                        <!-- Tombol Sebelumnya -->
                        <li class="page-item <?= $previousPage === null ? 'disabled' : ''; ?>">
                            <?php if ($previousPage !== null) : ?>
                                <a class="page-link" href="<?= esc($pageLink($previousPage)); ?>" aria-label="Halaman Sebelumnya">
                                    <i class="fa-solid fa-chevron-left me-1 small" aria-hidden="true"></i>
                                    <span>Sebelumnya</span>
                                </a>
                            <?php else : ?>
                                <span class="page-link" aria-disabled="true">
                                    <i class="fa-solid fa-chevron-left me-1 small" aria-hidden="true"></i>
                                    <span>Sebelumnya</span>
                                </span>
                            <?php endif; ?>
                        </li>

                        <!-- Nomor Halaman -->
                        <?php for ($page = 1; $page <= $pageCount; $page++) : ?>
                            <?php if ($page === $currentPage) : ?>
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link"><?= $page; ?></span>
                                </li>
                            <?php else : ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= esc($pageLink($pager->getPageURI($page))); ?>">
                                        <?= $page; ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <!-- Tombol Selanjutnya -->
                        <li class="page-item <?= $nextPage === null ? 'disabled' : ''; ?>">
                            <?php if ($nextPage !== null) : ?>
                                <a class="page-link" href="<?= esc($pageLink($nextPage)); ?>" aria-label="Halaman Selanjutnya">
                                    <span>Lanjut</span>
                                    <i class="fa-solid fa-chevron-right ms-1 small" aria-hidden="true"></i>
                                </a>
                            <?php else : ?>
                                <span class="page-link" aria-disabled="true">
                                    <span>Lanjut</span>
                                    <i class="fa-solid fa-chevron-right ms-1 small" aria-hidden="true"></i>
                                </span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </main>

    <!-- Modal Tambah Data Siswa -->
    <div class="modal fade" id="modalTambah" tabindex="-1"
         aria-labelledby="modalTambahLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-modern">
                <div class="modal-header-modern">
                    <h5 class="modal-title-modern" id="modalTambahLabel">
                        Tambah Data Siswa
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <!-- Form mengarah ke method 'tambah' di Controller -->
                <form action="<?= base_url('siswa/tambah'); ?>" method="POST" class="needs-validation" id="formTambahSiswa" novalidate>
                    <?= csrf_field(); ?>
                    <div class="modal-body-modern">
                        <!-- Input NIS -->
                        <div class="mb-3">
                            <label for="tambah_nis" class="form-label-modern">
                                <span>NIS</span>
                                <span class="required-mark" title="Wajib diisi">*</span>
                            </label>
                            <div class="input-group-modern">
                                <span class="input-icon-box">
                                    <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                                </span>
                                <input type="text"
                                       name="nis"
                                       id="tambah_nis"
                                       class="form-control input-modern"
                                       placeholder="NIS"
                                       required
                                       autocomplete="off">
                                <div class="form-invalid-feedback">
                                    NIS wajib diisi.
                                </div>
                            </div>
                        </div>

                        <!-- Input Nama Lengkap -->
                        <div class="mb-3">
                            <label for="tambah_nama" class="form-label-modern">
                                <span>Nama</span>
                                <span class="required-mark" title="Wajib diisi">*</span>
                            </label>
                            <div class="input-group-modern">
                                <span class="input-icon-box">
                                    <i class="fa-solid fa-user" aria-hidden="true"></i>
                                </span>
                                <input type="text"
                                       name="nama"
                                       id="tambah_nama"
                                       class="form-control input-modern"
                                       placeholder="Nama Lengkap"
                                       required
                                       autocomplete="off">
                                <div class="form-invalid-feedback">
                                    Nama wajib diisi.
                                </div>
                            </div>
                        </div>

                        <!-- Input Kelas -->
                        <div class="mb-1">
                            <label for="tambah_kelas" class="form-label-modern">
                                <span>Kelas</span>
                                <span class="required-mark" title="Wajib diisi">*</span>
                            </label>
                            <div class="input-group-modern">
                                <span class="input-icon-box">
                                    <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
                                </span>
                                <input type="text"
                                       name="kelas"
                                       id="tambah_kelas"
                                       list="kelasListTambah"
                                       class="form-control input-modern"
                                       placeholder="Kelas"
                                       required
                                       autocomplete="off">
                                <datalist id="kelasListTambah">
                                    <option value="X RPL 1">
                                    <option value="X RPL 2">
                                    <option value="XI RPL 1">
                                    <option value="XI RPL 2">
                                    <option value="XII RPL 1">
                                    <option value="XII RPL 2">
                                </datalist>
                                <div class="form-invalid-feedback">
                                    Kelas wajib diisi.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer-modern">
                        <button type="button" class="btn-modern-secondary" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            <span>Batal</span>
                        </button>
                        <button type="submit" class="btn-modern-primary" id="btnSubmitTambah">
                            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- CDN JS Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script Fungsionalitas & Interaktivitas UI/UX -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Auto-dismiss Flash Alert
            const flashAlert = document.getElementById('flashAlert');
            if (flashAlert) {
                setTimeout(function () {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(flashAlert);
                    if (bsAlert) {
                        bsAlert.close();
                    }
                }, 5000);
            }

            // 2. Client-side Live Search
            const searchInput = document.getElementById('tableSearchInput');
            const clearSearchBtn = document.getElementById('clearSearchBtn');
            const searchCountBadge = document.getElementById('searchCountBadge');
            const tableRows = document.querySelectorAll('#siswaTableBody tr.siswa-row');
            const noResultsRow = document.getElementById('noResultsRow');
            const btnResetSearch = document.getElementById('btnResetSearch');

            if (searchInput) {
                const performFilter = function () {
                    const query = searchInput.value.trim().toLowerCase();
                    let matchCount = 0;

                    if (query.length > 0) {
                        clearSearchBtn.style.display = 'block';
                    } else {
                        clearSearchBtn.style.display = 'none';
                    }

                    tableRows.forEach(function (row) {
                        const nis = row.getAttribute('data-nis') || '';
                        const nama = row.getAttribute('data-nama') || '';
                        const kelas = row.getAttribute('data-kelas') || '';

                        if (nis.includes(query) || nama.includes(query) || kelas.includes(query)) {
                            row.style.display = '';
                            matchCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    if (noResultsRow) {
                        if (matchCount === 0 && tableRows.length > 0) {
                            noResultsRow.style.display = '';
                        } else {
                            noResultsRow.style.display = 'none';
                        }
                    }

                    if (searchCountBadge) {
                        if (query.length > 0) {
                            searchCountBadge.textContent = matchCount + ' ditemukan';
                            searchCountBadge.classList.remove('d-none');
                        } else {
                            searchCountBadge.classList.add('d-none');
                        }
                    }
                };

                searchInput.addEventListener('input', performFilter);

                if (clearSearchBtn) {
                    clearSearchBtn.addEventListener('click', function () {
                        searchInput.value = '';
                        performFilter();
                        searchInput.focus();
                    });
                }

                if (btnResetSearch) {
                    btnResetSearch.addEventListener('click', function () {
                        searchInput.value = '';
                        performFilter();
                        searchInput.focus();
                    });
                }

                // Keyboard shortcut: Pressing '/' to focus search bar
                window.addEventListener('keydown', function (e) {
                    if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        searchInput.focus();
                    }
                });
            }

            // 3. Form Validation & Double Submission Prevention (Form Tambah)
            const formTambah = document.getElementById('formTambahSiswa');
            if (formTambah) {
                formTambah.addEventListener('submit', function (e) {
                    if (!this.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    } else {
                        const btnSubmit = document.getElementById('btnSubmitTambah');
                        if (btnSubmit) {
                            btnSubmit.disabled = true;
                            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...';
                        }
                    }
                    this.classList.add('was-validated');
                });
            }

            // 4. Form Edit: Dirty Check & Confirmation Before Cancel
            document.querySelectorAll('.modal-edit-form').forEach(function (form) {
                const modalElement = form.closest('.modal');
                const bsModal = bootstrap.Modal.getOrCreateInstance(modalElement);
                const inputs = form.querySelectorAll('input[name="nis"], input[name="nama"], input[name="kelas"]');
                const btnClose = modalElement.querySelector('.btn-close-modal-edit');
                const btnBatal = modalElement.querySelector('.btn-batal-modal-edit');

                // Save initial values
                inputs.forEach(function (input) {
                    input.dataset.original = input.value;
                });

                const isFormDirty = function () {
                    let dirty = false;
                    inputs.forEach(function (input) {
                        if (input.value !== input.dataset.original) {
                            dirty = true;
                        }
                    });
                    return dirty;
                };

                const handleCancelAttempt = function () {
                    if (isFormDirty()) {
                        const confirmCancel = window.confirm('Batalkan perubahan data?');
                        if (confirmCancel) {
                            // Reset inputs to original state
                            inputs.forEach(function (input) {
                                input.value = input.dataset.original;
                            });
                            form.classList.remove('was-validated');
                            bsModal.hide();
                        }
                    } else {
                        bsModal.hide();
                    }
                };

                if (btnClose) {
                    btnClose.addEventListener('click', handleCancelAttempt);
                }
                if (btnBatal) {
                    btnBatal.addEventListener('click', handleCancelAttempt);
                }

                form.addEventListener('submit', function (e) {
                    if (!this.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    } else {
                        const btnSubmit = form.querySelector('button[type="submit"]');
                        if (btnSubmit) {
                            btnSubmit.disabled = true;
                            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...';
                        }
                    }
                    this.classList.add('was-validated');
                });
            });
        });
    </script>
</body>
</html>
