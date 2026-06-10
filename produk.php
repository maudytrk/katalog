<?php
session_start();
include 'koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Logika Hapus Data
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    $ambil_foto = $koneksi->query("SELECT nama_file FROM produk_foto WHERE id_produk = '$id'");
    while ($f = $ambil_foto->fetch_assoc()) {
        $file_path = "assets/img/" . $f['nama_file'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    $koneksi->query("DELETE FROM produk_foto WHERE id_produk = '$id'");
    $koneksi->query("DELETE FROM produk_kategori WHERE id_produk = '$id'"); // Menghapus relasi kategori
    $koneksi->query("DELETE FROM produk WHERE id_produk = '$id'");

    echo "<script>alert('Produk dan semua data terkait berhasil dihapus!'); window.location='produk.php';</script>";
}

// Logika Hapus SATU Foto via AJAX (Saat Proses Edit)
if (isset($_GET['hapus_foto_ajax'])) {
    $id_foto = mysqli_real_escape_string($koneksi, $_GET['hapus_foto_ajax']);
    $f = $koneksi->query("SELECT nama_file FROM produk_foto WHERE id_foto = '$id_foto'")->fetch_assoc();

    if ($f) {
        if (file_exists("assets/img/" . $f['nama_file'])) {
            unlink("assets/img/" . $f['nama_file']);
        }
        $koneksi->query("DELETE FROM produk_foto WHERE id_foto = '$id_foto'");
        echo "Sukses";
    } else {
        echo "Gagal";
    }
    exit;
}

// --- LOGIKA UPDATE DATA (EDIT PRODUK) ---
if (isset($_POST['update'])) {
    $id_edit   = mysqli_real_escape_string($koneksi, $_POST['id_produk_edit']);
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $harga     = (int)$_POST['harga'];
    $stok      = (int)$_POST['stok'];
    $tiktok    = mysqli_real_escape_string($koneksi, $_POST['link_tiktok']);
    $shopee    = mysqli_real_escape_string($koneksi, $_POST['link_shopee']);
    $lazada    = mysqli_real_escape_string($koneksi, $_POST['link_lazada']);

    // 1. Update data teks
    $koneksi->query("UPDATE produk SET nama_produk='$nama', deskripsi='$deskripsi', 
                     harga='$harga', stok='$stok', link_tiktok='$tiktok', link_shopee='$shopee', 
                     link_lazada='$lazada' WHERE id_produk='$id_edit'");

    // 2. Update Kategori
    $kategori_baru_arr = isset($_POST['kategori']) ? $_POST['kategori'] : [];
    if (!empty($kategori_baru_arr)) {
        $koneksi->query("DELETE FROM produk_kategori WHERE id_produk = '$id_edit'");
        foreach ($kategori_baru_arr as $id_kat_baru) {
            $id_kat_clean = mysqli_real_escape_string($koneksi, $id_kat_baru);
            $koneksi->query("INSERT INTO produk_kategori (id_produk, id_kategori) VALUES ('$id_edit', '$id_kat_clean')");
        }
    }

    // 3. Tambah Foto Baru
    $foto_utama_set = false;
    $cek_foto_utama = $koneksi->query("SELECT foto FROM produk WHERE id_produk = '$id_edit'")->fetch_assoc();
    if (!empty($cek_foto_utama['foto']) && $cek_foto_utama['foto'] !== 'no-image.jpg') {
        $foto_utama_set = true;
    }

    if (isset($_FILES['foto_baru']['name']) && is_array($_FILES['foto_baru']['name'])) {
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg', 'webp');
        $total_files = count($_FILES['foto_baru']['name']);

        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['foto_baru']['error'][$i] === UPLOAD_ERR_OK) {
                $nama_file = $_FILES['foto_baru']['name'][$i];
                $tmp_name = $_FILES['foto_baru']['tmp_name'][$i];

                $x = explode('.', $nama_file);
                $ekstensi = strtolower(end($x));

                if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
                    $nama_foto_baru = date('YmdHis') . '-' . rand(1000, 9999) . '.' . $ekstensi;

                    if (move_uploaded_file($tmp_name, 'assets/img/' . $nama_foto_baru)) {
                        $koneksi->query("INSERT INTO produk_foto (id_produk, nama_file) VALUES ('$id_edit', '$nama_foto_baru')");

                        if (!$foto_utama_set) {
                            $koneksi->query("UPDATE produk SET foto = '$nama_foto_baru' WHERE id_produk = '$id_edit'");
                            $foto_utama_set = true;
                        }
                    }
                }
            }
        }
    }
    echo "<script>alert('Data produk berhasil diperbarui!'); window.location='produk.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Kelola Produk - Admin</title>
    <?php include 'pwa_meta.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <style>
        :root {
            --old-heliotrope: #6B4773;
            --royal-fuchsia: #BB3F95;
            --lavender-mist: #E0E1F6;
            --space-cadet: #231F48;
            --tyrian-purple: #560A39;
            --bg-cream: #FCF8F1;
            --accent-olive: #7D8F37;
        }

        body {
            background-color: var(--bg-cream);
            color: var(--space-cadet);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-admin-custom {
            background-color: var(--space-cadet) !important;
            border-bottom: 3px solid var(--old-heliotrope);
        }

        .navbar-admin-custom .navbar-brand {
            color: #fff !important;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .sb-sidenav-dark {
            background-color: #1a1736 !important;
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .sb-sidenav-dark .sb-sidenav-menu-heading {
            color: var(--lavender-mist) !important;
            font-weight: 600;
            opacity: 0.6;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sb-sidenav-dark .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: all 0.3s;
        }

        .sb-sidenav-dark .nav-link .sb-nav-link-icon {
            color: var(--lavender-mist) !important;
        }

        .sb-sidenav-dark .nav-link.active {
            color: #fff !important;
            background-color: var(--old-heliotrope) !important;
            border-radius: 0 25px 25px 0;
            margin-right: 15px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
        }

        .sb-sidenav-dark .nav-link:hover:not(.active) {
            color: #fff !important;
            background-color: rgba(107, 71, 115, 0.3) !important;
            border-radius: 0 25px 25px 0;
            margin-right: 15px;
        }

        .sb-sidenav-footer {
            background-color: #121026 !important;
            color: var(--lavender-mist) !important;
        }

        .panel-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(35, 31, 72, 0.04);
            overflow: hidden;
        }

        .panel-header {
            background-color: var(--lavender-mist);
            color: var(--space-cadet);
            font-weight: 700;
            border-bottom: 2px solid #DCD6EA;
            padding: 15px 20px;
        }

        .table>thead {
            background-color: var(--space-cadet);
            color: white;
        }

        .btn-custom-primary {
            background-color: var(--old-heliotrope);
            color: white;
            border: none;
        }

        .btn-custom-primary:hover {
            background-color: var(--tyrian-purple);
            color: white;
        }

        .btn-update {
            background-color: var(--space-cadet);
            color: white;
            border: none;
        }

        .btn-update:hover {
            background-color: #1a1736;
            color: white;
        }

        .modal-header-custom {
            background-color: var(--space-cadet);
            color: white;
            border-bottom: 3px solid var(--old-heliotrope);
        }

        .modal-header-edit {
            background-color: var(--old-heliotrope);
            color: white;
            border-bottom: 3px solid var(--royal-fuchsia);
        }

        footer.bg-light {
            background-color: #ffffff !important;
            border-top: 1px solid var(--lavender-mist);
        }

        /* Custom untuk memperbaiki tampilan border library Choices agar match dengan Bootstrap */
        .choices__inner {
            border-radius: 0.375rem !important;
            border: 1px solid #ced4da !important;
            background-color: #fff !important;
            padding: 4.5px 7.5px !important;
            min-height: 38px !important;
        }

        .choices[data-type*="select-multiple"] .choices__button {
            border-left: 1px solid rgba(255, 255, 255, 0.5);
        }

        .choices__list--multiple .choices__item {
            background-color: var(--old-heliotrope) !important;
            border: none !important;
            border-radius: 4px;
        }

        /* PREVIEW FOTO CSS */
        .preview-wrapper {
            position: relative;
            width: 80px;
            height: 80px;
            margin-right: 8px;
            margin-bottom: 8px;
        }

        .preview-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #E0E1F6;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .preview-remove-btn {
            position: absolute;
            top: -7px;
            right: -7px;
            background: #FF4444;
            color: white;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            font-size: 14px;
            line-height: 20px;
            text-align: center;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            border: 2px solid white;
            transition: all 0.2s;
            z-index: 10;
        }

        .preview-remove-btn:hover {
            background: #CC0000;
            transform: scale(1.1);
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark navbar-admin-custom">
        <a class="navbar-brand ps-3" href="dashboard_admin.php"><i class="fas fa-crown text-warning me-2"></i>RAHAYU ADMIN</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
        <ul class="navbar-nav ms-auto me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle fw-bold" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle fa-fw me-1"></i> <?php echo isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Administrator'; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item fw-medium text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav mt-3">
                        <div class="sb-sidenav-menu-heading">Utama</div>
                        <a class="nav-link" href="dashboard_admin.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div> Dashboard
                        </a>

                        <div class="sb-sidenav-menu-heading mt-2">Manajemen Data</div>
                        <a class="nav-link" href="kategori.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>Kelola Kategori
                        </a>
                        <a class="nav-link active" href="produk.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-box-open"></i></div>Kelola Produk
                        </a>
                        <a class="nav-link" href="sales.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-user-tie"></i></div>Pengelolaan Sales
                        </a>
                        <a class="nav-link" href="promo.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-percent"></i></div>Kelola Promo
                        </a>
                        <a class="nav-link" href="orders.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>Pesanan Masuk
                        </a>
                        <a class="nav-link" href="katalog.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-store"></i></div>Katalog Produk
                        </a>
                        <a class="nav-link" href="bandingkan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-balance-scale"></i></div>Bandingkan Produk
                        </a>

                        <div class="sb-sidenav-menu-heading mt-2">Pengaturan Sistem</div>
                        <a class="nav-link" href="kelola_admin.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div> Kelola Akun
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small text-white-50">Logged in as:</div>
                    <span class="fw-bold text-white"><?php echo isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Admin'; ?></span>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4 py-4">
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <h2 class="fw-bolder mb-1" style="color: var(--space-cadet);">Manajemen Katalog Produk</h2>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="dashboard_admin.php" class="text-decoration-none text-muted">Dashboard</a></li>
                                <li class="breadcrumb-item active" style="color: var(--space-cadet);">Produk</li>
                            </ol>
                        </div>
                    </div>

                    <div class="card panel-card mb-4">
                        <div class="panel-header d-flex justify-content-between align-items-center">
                            <div><i class="fas fa-table me-1"></i> Daftar Stok & Link Marketplace</div>
                            <button class="btn btn-sm btn-custom-primary fw-bold px-3 rounded" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="fas fa-plus me-1"></i> Tambah Produk
                            </button>
                        </div>
                        <div class="card-body bg-white p-3">
                            <table id="datatablesSimple" class="table table-striped table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th width="8%">Foto</th>
                                        <th width="22%">Nama & Kategori</th>
                                        <th width="25%">Deskripsi</th>
                                        <th width="15%">Harga</th>
                                        <th width="8%">Stok</th>
                                        <th width="12%">Link</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT p.*, GROUP_CONCAT(k.nama_kategori SEPARATOR ', ') as daftar_kategori 
                                              FROM produk p 
                                              LEFT JOIN produk_kategori pk ON p.id_produk = pk.id_produk
                                              LEFT JOIN kategori k ON pk.id_kategori = k.id_kategori
                                              GROUP BY p.id_produk";
                                    $res = $koneksi->query($query);
                                    while ($row = $res->fetch_assoc()):
                                    ?>
                                        <tr>
                                            <td class="text-center">
                                                <img src="assets/img/<?php echo (!empty($row['foto'])) ? htmlspecialchars($row['foto']) : 'no-image.jpg'; ?>" width="50" class="img-thumbnail" alt="Produk">
                                            </td>
                                            <td>
                                                <strong style="color: var(--space-cadet);"><?php echo htmlspecialchars($row['nama_produk']); ?></strong><br>
                                                <small class="text-muted"><i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($row['daftar_kategori'] ?? 'Tanpa Kategori'); ?></small>
                                            </td>
                                            <td><small class="text-muted"><?php echo htmlspecialchars(substr($row['deskripsi'], 0, 60)); ?>...</small></td>
                                            <td class="fw-semibold text-danger">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                            <td class="text-center fw-bold"><?php echo $row['stok']; ?></td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <?php if (!empty($row['link_tiktok'])) echo '<a href="' . htmlspecialchars($row['link_tiktok']) . '" target="_blank" class="btn btn-dark" title="TikTok"><i class="fab fa-tiktok"></i></a>'; ?>
                                                    <?php if (!empty($row['link_shopee'])) echo '<a href="' . htmlspecialchars($row['link_shopee']) . '" target="_blank" class="btn" style="background-color:#ee4d2d; color:white;" title="Shopee"><i class="fas fa-shopping-bag"></i></a>'; ?>
                                                    <?php if (!empty($row['link_lazada'])) echo '<a href="' . htmlspecialchars($row['link_lazada']) . '" target="_blank" class="btn btn-primary" title="Lazada"><i class="fas fa-heart"></i></a>'; ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <button type="button" class="btn btn-warning btn-sm text-dark btn-edit-produk" data-id="<?php echo $row['id_produk']; ?>" title="Edit Data">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalKonfirmasiHapus" data-href="produk.php?hapus=<?php echo $row['id_produk']; ?>" title="Hapus Data">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

            <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <form id="formTambahProduk" action="proses_tambah.php" method="POST" enctype="multipart/form-data">
                            <div class="modal-header modal-header-custom">
                                <h5 class="modal-title fw-bold"><i class="fas fa-box-open me-2 text-warning"></i>Tambah Produk Baru</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-dark bg-white">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold" style="color: var(--space-cadet);">Kode Produk</label>
                                        <input type="text" name="kode" class="form-control" placeholder="Contoh: BRG-001" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold" style="color: var(--space-cadet);">Nama Produk</label>
                                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama produk" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" style="color: var(--space-cadet);">Deskripsi Produk</label>
                                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Tulis deskripsi spesifikasi produk secara detail..." required></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold" style="color: var(--space-cadet);">Kategori <span class="text-danger small">(Bisa > 1)</span></label>
                                        <select name="kategori[]" id="kategoriSelectTambah" class="form-control" multiple required>
                                            <option value="" placeholder>Pilih Kategori Produk...</option>
                                            <?php
                                            $kat = $koneksi->query("SELECT * FROM kategori");
                                            while ($k = $kat->fetch_assoc()) {
                                                echo "<option value='" . $k['id_kategori'] . "'>" . htmlspecialchars($k['nama_kategori']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold" style="color: var(--space-cadet);">Harga (Rp)</label>
                                        <input type="number" name="harga" min="0" class="form-control" placeholder="0" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold" style="color: var(--space-cadet);">Stok Tersedia</label>
                                        <input type="number" name="stok" min="0" class="form-control" placeholder="0" required>
                                    </div>
                                </div>
                                <hr class="my-4" style="border-top: 2px dashed var(--lavender-mist);">
                                <h6 class="fw-bold mb-3" style="color: var(--old-heliotrope);"><i class="fas fa-link me-1"></i> Integrasi Link Marketplace (Opsional)</h6>
                                <div class="mb-2"><input type="url" name="link_tiktok" class="form-control form-control-sm" placeholder="https://tiktok.com/..."></div>
                                <div class="mb-2"><input type="url" name="link_shopee" class="form-control form-control-sm" placeholder="https://shopee.co.id/..."></div>
                                <div class="mb-3"><input type="url" name="link_lazada" class="form-control form-control-sm" placeholder="https://lazada.co.id/..."></div>

                                <div class="mb-3 p-3 rounded" style="background-color: #F8F9FA; border: 1px dashed #B0B7CA;">
                                    <label class="form-label fw-semibold" style="color: var(--space-cadet);"><i class="fas fa-images me-1"></i> Foto Produk</label>
                                    <input type="file" name="foto[]" id="fotoInputTambah" class="form-control" accept="image/*" multiple required>
                                    <div class="form-text text-muted mb-3">Tekan CTRL (Laptop) atau Tahan layar (HP) untuk memilih banyak gambar sekaligus.</div>
                                    <div id="previewContainerTambah" class="d-flex flex-wrap gap-2 mt-2"></div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="simpan" class="btn btn-custom-primary px-4 shadow-sm fw-bold">Simpan Data</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <form id="formEditProduk" action="" method="POST" enctype="multipart/form-data">
                            <div class="modal-header modal-header-edit">
                                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Data Produk</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-dark bg-white" id="kontenModalEdit">
                                <div class="text-center py-5">
                                    <div class="spinner-border" style="color: var(--old-heliotrope);" role="status"></div>
                                    <p class="mt-2 fw-semibold text-muted">Memuat data produk...</p>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="update" class="btn btn-update px-4 shadow-sm fw-bold">Update Data</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted fw-medium">PT Rahayu Karunia Utama &copy; <?php echo date('Y'); ?> E-Catalogue</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <?php include 'modal_konfirmasi_hapus.php'; ?>
    <?php include 'modal_logout.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>

    <script>
        let choicesTambahInstance;

        window.addEventListener('DOMContentLoaded', event => {
            const datatablesSimple = document.getElementById('datatablesSimple');
            if (datatablesSimple) {
                new simpleDatatables.DataTable(datatablesSimple);
            }

            // Inisialisasi Combo Box untuk Form Tambah (KOSONGKAN itemSelectText)
            const elKategoriTambah = document.getElementById('kategoriSelectTambah');
            if (elKategoriTambah) {
                choicesTambahInstance = new Choices(elKategoriTambah, {
                    removeItemButton: true,
                    searchPlaceholderValue: 'Cari kategori...',
                    noResultsText: 'Kategori tidak ditemukan',
                    itemSelectText: '', // KOSONGKAN INI
                    shouldSort: false
                });
            }
        });

        // ===================== LOGIKA PREVIEW GAMBAR (TAMBAH PRODUK) =====================
        let selectedFilesTambah = [];
        const fotoInputTambah = document.getElementById('fotoInputTambah');
        const previewContainerTambah = document.getElementById('previewContainerTambah');

        fotoInputTambah.addEventListener('change', function(e) {
            selectedFilesTambah = Array.from(e.target.files);
            renderPreviewTambah();
        });

        function renderPreviewTambah() {
            previewContainerTambah.innerHTML = '';
            selectedFilesTambah.forEach((file, index) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'preview-wrapper';
                const imgUrl = URL.createObjectURL(file);
                const img = document.createElement('img');
                img.src = imgUrl;
                img.onload = () => URL.revokeObjectURL(imgUrl);
                const removeBtn = document.createElement('span');
                removeBtn.innerHTML = '&times;';
                removeBtn.className = 'preview-remove-btn';
                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    selectedFilesTambah.splice(index, 1);
                    const dataTransfer = new DataTransfer();
                    selectedFilesTambah.forEach(f => dataTransfer.items.add(f));
                    fotoInputTambah.files = dataTransfer.files;
                    renderPreviewTambah();
                });
                wrapper.appendChild(img);
                wrapper.appendChild(removeBtn);
                previewContainerTambah.appendChild(wrapper);
            });
        }

        var modalTambah = document.getElementById('modalTambah');
        modalTambah.addEventListener('hidden.bs.modal', function() {
            var form = modalTambah.querySelector('form');
            form.reset();

            // Bersihkan Combo Box saat modal ditutup
            if (choicesTambahInstance) {
                choicesTambahInstance.removeActiveItems();
            }

            selectedFilesTambah = [];
            renderPreviewTambah();
        });

        // ===================== LOGIKA AJAX EDIT PRODUK (MODAL) =====================
        const modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));
        const kontenEdit = document.getElementById('kontenModalEdit');

        // Menggunakan Event Delegation untuk tombol edit
        document.body.addEventListener('click', function(e) {
            const btnEdit = e.target.closest('.btn-edit-produk');
            if (btnEdit) {
                const idProduk = btnEdit.getAttribute('data-id');

                kontenEdit.innerHTML = `<div class="text-center py-5"><div class="spinner-border" style="color: var(--old-heliotrope);" role="status"></div><p class="mt-2 fw-semibold text-muted">Memuat data produk...</p></div>`;
                modalEdit.show();

                fetch('get_produk.php?id=' + idProduk)
                    .then(response => response.text())
                    .then(htmlResponse => {
                        kontenEdit.innerHTML = htmlResponse;
                        attachEditImagePreviewEvent();

                        // Inisialisasi Combo Box untuk form Edit (setelah AJAX ter-load, KOSONGKAN itemSelectText)
                        const elKategoriEdit = document.getElementById('kategoriSelectEdit');
                        if (elKategoriEdit) {
                            new Choices(elKategoriEdit, {
                                removeItemButton: true,
                                searchPlaceholderValue: 'Cari kategori...',
                                noResultsText: 'Kategori tidak ditemukan',
                                itemSelectText: '', // KOSONGKAN INI
                                shouldSort: false
                            });
                        }
                    })
                    .catch(error => {
                        kontenEdit.innerHTML = `<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><h5>Gagal memuat data.</h5><p>Periksa koneksi internet Anda.</p></div>`;
                    });
            }
        });

        // Fungsi Hapus Foto Lama via AJAX
        function hapusFotoLama(idFoto, element) {
            if (confirm('Anda yakin ingin menghapus foto ini secara permanen dari sistem?')) {
                fetch('produk.php?hapus_foto_ajax=' + idFoto)
                    .then(response => response.text())
                    .then(res => {
                        if (res === 'Sukses') {
                            element.closest('.preview-wrapper').remove();
                        } else {
                            alert('Gagal menghapus foto.');
                        }
                    });
            }
        }

        // ===================== LOGIKA PREVIEW GAMBAR (EDIT PRODUK) =====================
        let selectedFilesEdit = [];

        function attachEditImagePreviewEvent() {
            selectedFilesEdit = [];
            const fotoInputEdit = document.getElementById('fotoInputEdit');
            const previewContainerEdit = document.getElementById('previewContainerEdit');

            if (fotoInputEdit) {
                fotoInputEdit.addEventListener('change', function(e) {
                    selectedFilesEdit = Array.from(e.target.files);
                    renderPreviewEdit();
                });
            }

            function renderPreviewEdit() {
                previewContainerEdit.innerHTML = '';
                selectedFilesEdit.forEach((file, index) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'preview-wrapper';
                    const imgUrl = URL.createObjectURL(file);
                    const img = document.createElement('img');
                    img.src = imgUrl;
                    img.onload = () => URL.revokeObjectURL(imgUrl);
                    const removeBtn = document.createElement('span');
                    removeBtn.innerHTML = '&times;';
                    removeBtn.className = 'preview-remove-btn';

                    removeBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        selectedFilesEdit.splice(index, 1);
                        const dataTransfer = new DataTransfer();
                        selectedFilesEdit.forEach(f => dataTransfer.items.add(f));
                        fotoInputEdit.files = dataTransfer.files;
                        renderPreviewEdit();
                    });
                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    previewContainerEdit.appendChild(wrapper);
                });
            }
        }
    </script>
</body>

</html>