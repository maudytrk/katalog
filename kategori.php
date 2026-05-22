<?php
session_start();
include 'koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$sukses = "";
$gagal  = "";

// ==================== PROSES TAMBAH KATEGORI ====================
if (isset($_POST['tambah_kategori'])) {
    $nama_kategori = mysqli_real_escape_with_html_tags($koneksi, $_POST['nama_kategori']);
    $keterangan    = mysqli_real_escape_with_html_tags($koneksi, $_POST['keterangan']);

    if (!empty($nama_kategori)) {
        $query = "INSERT INTO kategori (nama_kategori, keterangan) VALUES ('$nama_kategori', '$keterangan')";
        if ($koneksi->query($query)) {
            $sukses = "Kategori baru berhasil ditambahkan!";
        } else {
            $gagal = "Gagal menambahkan kategori: " . $koneksi->error;
        }
    }
}

// ==================== PROSES EDIT KATEGORI ====================
if (isset($_POST['edit_kategori'])) {
    $id_kategori   = (int)$_POST['id_kategori'];
    $nama_kategori = mysqli_real_escape_with_html_tags($koneksi, $_POST['nama_kategori']);
    $keterangan    = mysqli_real_escape_with_html_tags($koneksi, $_POST['keterangan']);

    if ($id_kategori > 0 && !empty($nama_kategori)) {
        $query = "UPDATE kategori SET nama_kategori = '$nama_kategori', keterangan = '$keterangan' WHERE id_kategori = $id_kategori";
        if ($koneksi->query($query)) {
            $sukses = "Data kategori berhasil diperbarui!";
        } else {
            $gagal = "Gagal memperbarui kategori: " . $koneksi->error;
        }
    }
}

// ==================== PROSES HAPUS KATEGORI ====================
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    if ($id_hapus > 0) {
        // Cek apakah ada produk yang terikat ke kategori ini sebelum menghapus
        $cek_produk = $koneksi->query("SELECT COUNT(*) as total FROM produk WHERE id_kategori = $id_hapus")->fetch_assoc();
        if ($cek_produk['total'] > 0) {
            $gagal = "Kategori tidak dapat dihapus karena masih digunakan oleh " . $cek_produk['total'] . " produk!";
        } else {
            $query = "DELETE FROM kategori WHERE id_kategori = $id_hapus";
            if ($koneksi->query($query)) {
                // Dialihkan kembali ke kategori.php untuk menghindari resubmit / bug datatable client-side
                header("Location: kategori.php?msg=sukses_hapus");
                exit;
            } else {
                $gagal = "Gagal menghapus kategori: " . $koneksi->error;
            }
        }
    }
}

// Menangkap feedback redirect hapus
if (isset($_GET['msg']) && $_GET['msg'] == 'sukses_hapus') {
    $sukses = "Kategori berhasil dihapus dari database!";
}

// Fungsi bantu sanitasi input database
function mysqli_real_escape_with_html_tags($koneksi, $data) {
    return mysqli_real_escape_string($koneksi, trim($data));
}
?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Kelola Kategori - E-Catalogue</title>
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
            .sb-topnav.navbar {
                background-color: var(--pastel-purple) !important;
                border-bottom: 2px solid var(--olive-green);
            }
            .sb-topnav .navbar-brand {
                color: var(--text-dark) !important;
                font-weight: 700;
            }
            .sb-topnav .nav-link, .sb-topnav .btn-link {
                color: var(--text-dark) !important;
            }
            .sb-sidenav-dark {
                background-color: #373029 !important;
            }
            .sb-sidenav-dark .sb-sidenav-menu-heading {
                color: var(--pastel-purple) !important;
                font-weight: 600;
            }
            .sb-sidenav-dark .nav-link {
                color: rgba(255, 255, 255, 0.8) !important;
            }
            .sb-sidenav-dark .nav-link .sb-nav-link-icon {
                color: var(--pastel-purple) !important;
            }
            .sb-sidenav-dark .nav-link.active {
                color: white !important;
                background-color: var(--dark-olive) !important;
            }
            .card-header {
                background-color: rgba(224, 210, 240, 0.4) !important;
                color: var(--text-dark) !important;
                font-weight: 600;
            }
            .btn-olive {
                background-color: var(--olive-green);
                color: white;
            }
            .btn-olive:hover {
                background-color: var(--dark-olive);
                color: white;
            }
            footer.bg-light {
                background-color: var(--pastel-purple) !important;
            }
            h1, .breadcrumb-item.active {
                color: var(--text-dark) !important;
            }
        </style>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark">
            <a class="navbar-brand ps-3" href="dashboard_admin.php">RAHAYU ADMIN</a>
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <ul class="navbar-nav ms-auto me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i> <?php echo isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Administrator'; ?></a>
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
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>
                            <div class="sb-sidenav-menu-heading">Manajemen Data</div>
                            <a class="nav-link active" href="kategori.php"><div class="sb-nav-link-icon"><i class="fas fa-folder"></i></div>Kelola Kategori</a>
                            <a class="nav-link" href="produk.php"><div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>Kelola Produk</a>
                            <a class="nav-link" href="sales.php"><div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>Pengelolaan Sales</a>
                            <a class="nav-link" href="promo.php"><div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>Kelola Promo</a>
                            <a class="nav-link" href="orders.php"><div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>Pesanan Masuk</a>
                            <a class="nav-link" href="katalog.php"><div class="sb-nav-link-icon"><i class="fas fa-list-ul"></i></div>Katalog Produk</a>
                            <a class="nav-link" href="bandingkan.php"><div class="sb-nav-link-icon"><i class="fas fa-balance-scale"></i></div>Bandingkan Produk</a>
                            
                            <div class="sb-sidenav-menu-heading">Pengaturan Sistem</div>
                            <a class="nav-link" href="kelola_admin.php"><div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div>Kelola Akun</a>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div> <?php echo ucfirst($_SESSION['role'] ?? 'Admin'); ?>
                    </div>
                </nav>
            </div>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Manajemen Kategori</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard_admin.php" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active">Kelola Kategori</li>
                        </ol>

                        <?php if(!empty($sukses)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $sukses; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($gagal)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $gagal; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div><i class="fas fa-table me-1"></i>List Master Kategori Produk</div>
                                <button class="btn btn-sm btn-olive rounded px-3" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                                    <i class="fas fa-plus-circle me-1"></i>Tambah Kategori
                                </button>
                            </div>
                            <div class="card-body bg-white">
                                <table id="datatablesKategori" class="table table-striped table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th style="width: 8%">No</th>
                                            <th style="width: 30%">Nama Kategori</th>
                                            <th>Keterangan</th>
                                            <th style="width: 15%" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        $res = $koneksi->query("SELECT * FROM kategori ORDER BY id_kategori DESC");
                                        while ($row = $res->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                            <td><?php echo !empty($row['keterangan']) ? nl2br(htmlspecialchars($row['keterangan'])) : '<span class="text-muted italic small">Tidak ada keterangan</span>'; ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-xs btn-warning px-2 py-1 text-dark me-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEditKategori<?php echo $row['id_kategori']; ?>" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalKonfirmasiHapus" 
                                                        data-href="kategori.php?hapus=<?php echo $row['id_kategori']; ?>" 
                                                        title="Hapus Data">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <div class="modal fade" id="modalEditKategori<?php echo $row['id_kategori']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow">
                                                    <form action="kategori.php" method="POST">
                                                        <div class="modal-header bg-warning text-dark fw-bold">
                                                            <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Ubah Data Kategori</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <input type="hidden" name="id_kategori" value="<?php echo $row['id_kategori']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Nama Kategori</label>
                                                                <input type="text" name="nama_kategori" class="form-control" value="<?php echo htmlspecialchars($row['nama_kategori']); ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Keterangan</label>
                                                                <textarea name="keterangan" class="form-control" rows="4"><?php echo htmlspecialchars($row['keterangan']); ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-sm btn-secondary rounded px-3" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="edit_kategori" class="btn btn-sm btn-warning rounded px-4 fw-bold">Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">PT Rahayu Karunia Utama &copy; Rahayu</div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="kategori.php" method="POST">
                        <div class="modal-header text-white fw-bold" style="background-color: var(--dark-olive)">
                            <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Tambah Kategori Baru</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-start">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Kategori</label>
                                <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Inner Hijab, Manset" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="4" placeholder="Deskripsi opsional mengenai ruang lingkup kategori produk..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-sm btn-secondary rounded px-3" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="tambah_kategori" class="btn btn-sm btn-olive rounded px-4 fw-bold">Tambah Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include 'modal_logout.php'; ?>
        <?php include 'modal_konfirmasi_hapus.php'; ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script>
            window.addEventListener('DOMContentLoaded', event => {
                const datatablesKategori = document.getElementById('datatablesKategori');
                if (datatablesKategori) {
                    new simpleDatatables.DataTable(datatablesKategori);
                }
            });
        </script>
    </body>
</html>