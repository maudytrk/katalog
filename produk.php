<?php
session_start();
include 'koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Logika Hapus Data (Aman & Menghapus Semua File Terkait)
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    // 1. Ambil SEMUA nama file foto yang terkait dengan produk ini
    $ambil_foto = $koneksi->query("SELECT nama_file FROM produk_foto WHERE id_produk = '$id'");

    // Hapus file fisik dari server
    while ($f = $ambil_foto->fetch_assoc()) {
        $file_path = "assets/img/" . $f['nama_file'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    // 2. Hapus data dari database (Tabel produk dan produk_foto akan terhapus otomatis 
    // jika Anda sudah mengatur FOREIGN KEY dengan ON DELETE CASCADE)
    // Jika belum menggunakan CASCADE, jalankan query berikut:
    $koneksi->query("DELETE FROM produk_foto WHERE id_produk = '$id'");
    $koneksi->query("DELETE FROM produk WHERE id_produk = '$id'");

    echo "<script>alert('Produk dan semua foto terkait berhasil dihapus!'); window.location='produk.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Kelola Produk - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <style>
        :root {
            --pastel-purple: #E0D2F0;
            --soft-cream: #FCF8F1;
            --olive-green: #7D8F37;
            --dark-olive: #5A6926;
            --text-dark: #4A4036;
        }

        body {
            background-color: var(--soft-cream);
            color: var(--text-dark);
        }

        /* Kustomisasi Top Navigation Bar */
        .sb-topnav.navbar {
            background-color: var(--pastel-purple) !important;
            border-bottom: 2px solid var(--olive-green);
        }

        .sb-topnav .navbar-brand {
            color: var(--text-dark) !important;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .sb-topnav .nav-link,
        .sb-topnav .btn-link {
            color: var(--text-dark) !important;
        }

        .sb-topnav .nav-link:hover,
        .sb-topnav .btn-link:hover {
            color: var(--dark-olive) !important;
        }

        /* Kustomisasi Sidebar */
        .sb-sidenav-dark {
            background-color: #373029 !important;
            /* Cokelat gelap berbasis earth-tone */
            color: rgba(255, 255, 255, 0.75) !important;
        }

        .sb-sidenav-dark .sb-sidenav-menu-heading {
            color: var(--pastel-purple) !important;
            font-weight: 600;
            opacity: 0.8;
        }

        .sb-sidenav-dark .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .sb-sidenav-dark .nav-link .sb-nav-link-icon {
            color: var(--pastel-purple) !important;
        }

        .sb-sidenav-dark .nav-link.active {
            color: #fff !important;
            background-color: var(--dark-olive) !important;
        }

        .sb-sidenav-dark .nav-link:hover {
            color: #fff !important;
            background-color: rgba(125, 143, 55, 0.3) !important;
        }

        .sb-sidenav-footer {
            background-color: #2b2520 !important;
            color: var(--pastel-purple) !important;
        }

        /* Kustomisasi Card & Table */
        .card {
            border: 1px solid rgba(125, 143, 55, 0.15);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }

        .card-header {
            background-color: rgba(224, 210, 240, 0.4) !important;
            color: var(--text-dark) !important;
            font-weight: 600;
            border-bottom: 1px solid rgba(125, 143, 55, 0.15);
        }

        /* Tombol Custom Khas Rahayu */
        .btn-custom-olive {
            background-color: var(--olive-green) !important;
            color: white !important;
            border: none;
        }

        .btn-custom-olive:hover {
            background-color: var(--dark-olive) !important;
        }

        /* Kustomisasi Modal Dialog */
        .modal-header {
            background-color: var(--pastel-purple) !important;
            color: var(--text-dark) !important;
            border-bottom: 2px solid var(--olive-green);
        }

        .modal-title {
            font-weight: 700;
        }

        .modal-content {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        /* Penyesuaian Footer Utama */
        footer.bg-light {
            background-color: var(--pastel-purple) !important;
            border-top: 1px solid rgba(125, 143, 55, 0.2);
        }

        h1,
        .breadcrumb-item.active,
        .breadcrumb-item a {
            color: var(--text-dark) !important;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: var(--dark-olive) !important;
            text-decoration: underline;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark">
        <a class="navbar-brand ps-3" href="dashboard_admin.php">RAHAYU ADMIN</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
        <ul class="navbar-nav ms-auto me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i> <?php echo isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Administrator'; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Utama</div>
                        <a class="nav-link" href="dashboard_admin.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div> Dashboard
                        </a>
                        <div class="sb-sidenav-menu-heading">Manajemen Data</div>
                        <a class="nav-link" href="kategori.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-folder"></i></div>Kelola Kategori
                        </a>

                        <a class="nav-link active" href="produk.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>Kelola Produk
                        </a>
                        <a class="nav-link" href="sales.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>Pengelolaan Sales
                        </a>
                        <a class="nav-link" href="promo.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>Kelola Promo
                        </a>
                        <a class="nav-link" href="orders.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>Pesanan Masuk
                        </a>
                        <a class="nav-link" href="katalog.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-list-ul"></i></div>Katalog Produk
                        </a>
                        <a class="nav-link" href="bandingkan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-balance-scale"></i></div>Bandingkan Produk
                        </a>
                        <div class="sb-sidenav-menu-heading">Pengaturan Sistem</div>
                        <a class="nav-link" href="kelola_admin.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div>
                            Kelola Akun
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div> <?php echo isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Admin'; ?>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Manajemen Katalog Produk</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="dashboard_admin.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Produk</li>
                    </ol>

                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div><i class="fas fa-table me-1"></i> Daftar Stok & Link Marketplace</div>
                            <button class="btn btn-custom-olive btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="fas fa-plus me-1"></i> Tambah Produk
                            </button>
                        </div>
                        <div class="card-body bg-white">
                            <table id="datatablesSimple" class="table table-striped table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th width="8%">Foto</th>
                                        <th width="22%">Nama Produk</th>
                                        <th width="25%">Deskripsi</th>
                                        <th width="15%">Harga</th>
                                        <th width="8%">Stok</th>
                                        <th width="12%">Link Marketplace</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT p.*, k.nama_kategori FROM produk p 
                                                  LEFT JOIN kategori k ON p.id_kategori = k.id_kategori";
                                    $res = $koneksi->query($query);
                                    while ($row = $res->fetch_assoc()):
                                    ?>
                                        <tr>
                                            <td class="text-center">
                                                <img src="assets/img/<?php echo (!empty($row['foto'])) ? htmlspecialchars($row['foto']) : 'no-image.jpg'; ?>" width="50" class="img-thumbnail" alt="Produk">
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['nama_produk']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?></small>
                                            </td>
                                            <td><small class="text-muted"><?php echo htmlspecialchars(substr($row['deskripsi'], 0, 60)); ?>...</small></td>
                                            <td class="fw-semibold text-dark">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                            <td class="text-center"><?php echo $row['stok']; ?></td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <?php if (!empty($row['link_tiktok'])) echo '<a href="' . htmlspecialchars($row['link_tiktok']) . '" target="_blank" class="btn btn-dark" title="TikTok"><i class="fab fa-tiktok"></i></a>'; ?>
                                                    <?php if (!empty($row['link_shopee'])) echo '<a href="' . htmlspecialchars($row['link_shopee']) . '" target="_blank" class="btn" style="background-color:#ee4d2d; color:white;" title="Shopee"><i class="fas fa-shopping-bag"></i></a>'; ?>
                                                    <?php if (!empty($row['link_lazada'])) echo '<a href="' . htmlspecialchars($row['link_lazada']) . '" target="_blank" class="btn btn-primary" title="Lazada"><i class="fas fa-heart"></i></a>'; ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <a href="edit_produk.php?id=<?php echo $row['id_produk']; ?>" class="btn btn-warning btn-sm text-dark" title="Edit Data">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalKonfirmasiHapus"
                                                        data-href="produk.php?hapus=<?php echo $row['id_produk']; ?>"
                                                        title="Hapus Data">
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
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="proses_tambah.php" method="POST" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-box-open me-2"></i>Tambah Produk Baru</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-dark bg-white">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Kode Produk</label>
                                        <input type="text" name="kode" class="form-control" placeholder="Contoh: BRG-001" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Nama Produk</label>
                                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama produk" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Deskripsi Produk</label>
                                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Tulis deskripsi spesifikasi produk secara detail..." required></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Kategori</label>
                                        <select name="kategori" class="form-select" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            <?php
                                            $kat = $koneksi->query("SELECT * FROM kategori");
                                            while ($k = $kat->fetch_assoc()) {
                                                echo "<option value='" . $k['id_kategori'] . "'>" . htmlspecialchars($k['nama_kategori']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Harga (Rp)</label>
                                        <input type="number" name="harga" min="0" class="form-control" placeholder="0" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Stok Ter sedia</label>
                                        <input type="number" name="stok" min="0" class="form-control" placeholder="0" required>
                                    </div>
                                </div>
                                <hr class="my-4" style="border-top: 2px dashed var(--pastel-purple);">
                                <h6 class="fw-bold mb-3" style="color: var(--dark-olive);"><i class="fas fa-link me-1"></i> Integrasi Link Marketplace (Opsional)</h6>
                                <div class="mb-2">
                                    <label class="small fw-semibold text-muted">Link TikTok Shop</label>
                                    <input type="url" name="link_tiktok" class="form-control form-control-sm" placeholder="https://tiktok.com/...">
                                </div>
                                <div class="mb-2">
                                    <label class="small fw-semibold text-muted">Link Shopee</label>
                                    <input type="url" name="link_shopee" class="form-control form-control-sm" placeholder="https://shopee.co.id/...">
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-semibold text-muted">Link Lazada</label>
                                    <input type="url" name="link_lazada" class="form-control form-control-sm" placeholder="https://lazada.co.id/...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Foto Produk (Bisa pilih lebih dari satu)</label>
                                    <input type="file" name="foto[]" class="form-control" accept="image/*" multiple required>
                                    <div class="form-text text-muted">Format yang didukung: JPG, JPEG, PNG. Maksimal 2MB.</div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="simpan" class="btn btn-custom-olive px-4 shadow-sm fw-bold">Simpan Data</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-dark fw-medium">PT Rahayu Karunia Utama &copy; Rahayu</div>
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
        window.addEventListener('DOMContentLoaded', event => {
            const datatablesSimple = document.getElementById('datatablesSimple');
            if (datatablesSimple) {
                new simpleDatatables.DataTable(datatablesSimple);
            }
        });
    </script>
    <script>
        // Menangkap event saat modal ditutup (hidden.bs.modal)
        var modalTambah = document.getElementById('modalTambah');
        modalTambah.addEventListener('hidden.bs.modal', function() {
            // Reset/bersihkan form di dalam modal
            var form = modalTambah.querySelector('form');
            form.reset();

            // Opsional: Jika Anda memiliki elemen preview foto atau teks error, 
            // Anda bisa membersihkannya juga di sini.
            console.log("Form telah dikosongkan.");
        });
    </script>
</body>

</html>