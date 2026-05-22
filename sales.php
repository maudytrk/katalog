<?php
session_start();
include 'koneksi.php';

// Proteksi halaman: Hanya admin yang bisa mengelola sales
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Logika Hapus Akun Sales (Aman dari SQL Injection)
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Pastikan user yang dihapus memang memiliki role sales
    $cek_sales = $koneksi->query("SELECT id_user FROM users WHERE id_user = '$id' AND role = 'sales'");
    if ($cek_sales->num_rows > 0) {
        $koneksi->query("DELETE FROM users WHERE id_user = '$id' AND role = 'sales'");
        echo "<script>alert('Akun sales berhasil dihapus'); window.location='sales.php';</script>";
    } else {
        echo "<script>alert('Akun sales tidak ditemukan atau Anda tidak memiliki akses!'); window.location='sales.php';</script>";
    }
}

// Mengambil Statistik untuk Card
$totalSales = $koneksi->query("SELECT COUNT(*) as total FROM users WHERE role = 'sales'")->fetch_assoc()['total'];
$totalOrder = $koneksi->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Manajemen Sales - Admin</title>
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
            .sb-topnav .nav-link, .sb-topnav .btn-link {
                color: var(--text-dark) !important;
            }
            .sb-topnav .nav-link:hover, .sb-topnav .btn-link:hover {
                color: var(--dark-olive) !important;
            }
            
            /* Kustomisasi Sidebar */
            .sb-sidenav-dark {
                background-color: #373029 !important; /* Cokelat gelap berbasis earth-tone */
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

            /* Card Statistik Custom (Earth Tone) */
            .card-stat-sales {
                background-color: var(--pastel-purple) !important;
                color: var(--text-dark) !important;
                border: 1px solid rgba(125, 143, 55, 0.2);
            }
            .card-stat-order {
                background-color: #E8EDD5 !important; /* Hijau muda pastel */
                color: var(--text-dark) !important;
                border: 1px solid var(--olive-green);
            }

            /* Kustomisasi Card & Table */
            .card {
                border: 1px solid rgba(125, 143, 55, 0.15);
                box-shadow: 0 4px 6px rgba(0,0,0,0.02);
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
            
            h1, .breadcrumb-item.active, .breadcrumb-item a {
                color: var(--text-dark) !important;
                text-decoration: none;
            }
            .breadcrumb-item a:hover {
                color: var(--dark-olive) !important;
                text-decoration: underline;
            }
            
            /* Badge Kustom */
            .badge-custom-info {
                background-color: var(--pastel-purple) !important;
                color: var(--text-dark) !important;
                border: 1px solid rgba(74, 64, 54, 0.2);
                font-weight: 600;
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
                            <a class="nav-link" href="dashboard_admin.php"><div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div> Dashboard</a>
                            
                            <div class="sb-sidenav-menu-heading">Manajemen Data</div>
                            <a class="nav-link" href="kategori.php"><div class="sb-nav-link-icon"><i class="fas fa-folder"></i></div>Kelola Kategori</a>
                            
                            <a class="nav-link" href="produk.php"><div class="sb-nav-link-icon"><i class="fas fa-box"></i></div> Kelola Produk</a>
                            <a class="nav-link active" href="sales.php"><div class="sb-nav-link-icon"><i class="fas fa-users"></i></div> Pengelolaan Sales</a>
                            <a class="nav-link" href="promo.php"><div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div> Kelola Promo</a>
                            <a class="nav-link" href="orders.php"><div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div> Pesanan Masuk</a>
                            <a class="nav-link" href="katalog.php"><div class="sb-nav-link-icon"><i class="fas fa-list-ul"></i></div>Katalog Produk</a>
                            <a class="nav-link" href="bandingkan.php"><div class="sb-nav-link-icon"><i class="fas fa-balance-scale"></i></div>Bandingkan Produk</a>
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
                        <h1 class="mt-4">Manajemen Tim Sales</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard_admin.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Sales</li>
                        </ol>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-stat-sales mb-4 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="small fw-semibold text-muted text-uppercase">Sales Aktif</div>
                                                <div class="display-6 fw-bold mt-1"><?php echo $totalSales; ?></div>
                                            </div>
                                            <i class="fas fa-user-check fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-stat-order mb-4 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="small fw-semibold text-muted text-uppercase">Total Pesanan Masuk</div>
                                                <div class="display-6 fw-bold mt-1"><?php echo $totalOrder; ?></div>
                                            </div>
                                            <i class="fas fa-shopping-basket fa-2x opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div><i class="fas fa-table me-1"></i> Daftar Akun Sales</div>
                                <button class="btn btn-custom-olive btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahSales">
                                    <i class="fas fa-plus me-1"></i> Tambah Sales Baru
                                </button>
                            </div>
                            <div class="card-body bg-white">
                                <table id="datatablesSimple" class="table table-striped table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th>Nama Lengkap</th>
                                            <th>Username/Email</th>
                                            <th class="text-center" width="20%">Total Input Pesanan</th>
                                            <th class="text-center" width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.id_user = u.id_user) as jml_order 
                                                  FROM users u WHERE u.role = 'sales'";
                                        $res = $koneksi->query($query);
                                        while($row = $res->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-custom-info px-3 py-2 rounded-pill"><?php echo $row['jml_order']; ?> Pesanan</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <a href="edit_sales.php?id=<?php echo $row['id_user']; ?>" class="btn btn-warning btn-sm text-dark" title="Edit Akun">
                                                        <i class="fas fa-user-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalKonfirmasiHapus" 
                                                            data-href="sales.php?hapus=<?php echo $row['id_user']; ?>" 
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

                <div class="modal fade" id="modalTambahSales" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="proses_sales.php" method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Registrasi Akun Sales Baru</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-dark bg-white">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Nama Lengkap</label>
                                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap sales" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Username / Email</label>
                                        <input type="text" name="username" class="form-control" placeholder="Username untuk keperluan login" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Password</label>
                                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" minlength="6" required>
                                        <div class="form-text text-muted">Password kredensial awal untuk akses login pertama kali tim sales.</div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="tambah_sales" class="btn btn-custom-olive px-4 shadow-sm fw-bold">Daftarkan Sales</button>
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

        <?php include 'modal_logout.php'; ?>
        <?php include 'modal_konfirmasi_hapus.php'; ?>

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
    </body>
</html>