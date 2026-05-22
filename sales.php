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
        /* TEMA WARNA BERDASARKAN PALETTE E-CATALOGUE */
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

        /* Navbar Custom */
        .navbar-admin-custom {
            background-color: var(--space-cadet) !important;
            border-bottom: 3px solid var(--old-heliotrope);
        }

        .navbar-admin-custom .navbar-brand {
            color: #fff !important;
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* Sidebar Custom */
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

        /* Card Statistik Custom Menyamai Dashboard Admin */
        .summary-card {
            background-color: #fff;
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(35, 31, 72, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(35, 31, 72, 0.1);
        }

        .summary-icon {
            position: absolute;
            right: -15px;
            bottom: -15px;
            font-size: 6rem;
            opacity: 0.08;
            z-index: -1;
            transform: rotate(-10deg);
        }

        .border-l-sales {
            border-left: 5px solid var(--royal-fuchsia);
        }

        .text-sales {
            color: var(--royal-fuchsia);
        }

        .border-l-pesanan {
            border-left: 5px solid var(--space-cadet);
        }

        .text-pesanan {
            color: var(--space-cadet);
        }

        /* Panel Card Biasa (Tabel) */
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

        /* Tombol & Modal Custom */
        .btn-custom-primary {
            background-color: var(--old-heliotrope);
            color: white;
            border: none;
        }

        .btn-custom-primary:hover {
            background-color: var(--tyrian-purple);
        }

        .modal-header-custom {
            background-color: var(--space-cadet);
            color: white;
            border-bottom: 3px solid var(--old-heliotrope);
        }

        footer.bg-light {
            background-color: #ffffff !important;
            border-top: 1px solid var(--lavender-mist);
        }

        h1,
        h2,
        .breadcrumb-item.active,
        .breadcrumb-item a {
            color: var(--space-cadet) !important;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: var(--old-heliotrope) !important;
            text-decoration: underline;
        }

        /* Badge Kustom */
        .badge-custom-info {
            background-color: var(--lavender-mist) !important;
            color: var(--space-cadet) !important;
            border: 1px solid #DCD6EA;
            font-weight: 600;
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
                        <a class="nav-link" href="produk.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-box-open"></i></div> Kelola Produk
                        </a>
                        <a class="nav-link active" href="sales.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-user-tie"></i></div> Pengelolaan Sales
                        </a>
                        <a class="nav-link" href="promo.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-percent"></i></div> Kelola Promo
                        </a>
                        <a class="nav-link" href="orders.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div> Pesanan Masuk
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
                            <h2 class="fw-bolder mb-1" style="color: var(--space-cadet);">Manajemen Tim Sales</h2>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="dashboard_admin.php" class="text-decoration-none text-muted">Dashboard</a></li>
                                <li class="breadcrumb-item active" style="color: var(--space-cadet);">Sales</li>
                            </ol>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="summary-card border-l-sales h-100 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase fw-bold text-sales small mb-1">Sales Aktif</div>
                                        <h3 class="fw-bold text-dark mb-0"><?php echo $totalSales; ?></h3>
                                    </div>
                                </div>
                                <i class="fas fa-user-check summary-icon text-sales"></i>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="summary-card border-l-pesanan h-100 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase fw-bold text-pesanan small mb-1">Total Pesanan Masuk</div>
                                        <h3 class="fw-bold text-dark mb-0"><?php echo $totalOrder; ?></h3>
                                    </div>
                                </div>
                                <i class="fas fa-shopping-basket summary-icon text-pesanan"></i>
                            </div>
                        </div>
                    </div>

                    <div class="card panel-card mb-4">
                        <div class="panel-header d-flex justify-content-between align-items-center">
                            <div><i class="fas fa-table me-1"></i> Daftar Akun Sales</div>
                            <button class="btn btn-custom-primary btn-sm fw-bold px-3 rounded" data-bs-toggle="modal" data-bs-target="#modalTambahSales">
                                <i class="fas fa-plus me-1"></i> Tambah Sales Baru
                            </button>
                        </div>
                        <div class="card-body bg-white p-3">
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
                                    while ($row = $res->fetch_assoc()):
                                    ?>
                                        <tr>
                                            <td class="fw-bold" style="color: var(--space-cadet);"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
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
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <form action="proses_sales.php" method="POST">
                            <div class="modal-header modal-header-custom">
                                <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2 text-warning"></i>Registrasi Akun Sales Baru</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-dark bg-white">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" style="color: var(--space-cadet);">Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap sales" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" style="color: var(--space-cadet);">Username / Email</label>
                                    <input type="text" name="username" class="form-control" placeholder="Username untuk keperluan login" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" style="color: var(--space-cadet);">Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" minlength="6" required>
                                    <div class="form-text text-muted">Password kredensial awal untuk akses login pertama kali tim sales.</div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="tambah_sales" class="btn btn-custom-primary px-4 shadow-sm fw-bold">Daftarkan Sales</button>
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