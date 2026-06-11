<?php
session_start();
include 'koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Logika Hapus Promo (Aman dari SQL Injection)
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    // Validasi apakah data memilikinya sebelum dihapus
    $cek_promo = $koneksi->query("SELECT id_promo FROM promo WHERE id_promo = '$id'");
    if ($cek_promo->num_rows > 0) {
        $koneksi->query("DELETE FROM promo WHERE id_promo = '$id'");
        $_SESSION['sukses'] = "Promo berhasil dihapus!";
        header("Location: promo.php");
        exit;
    } else {
        $_SESSION['gagal'] = "Data promo tidak ditemukan!";
        header("Location: promo.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Manajemen Promo - Admin</title>
    <?php include 'pwa_meta.php'; ?>
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
        .badge-success-custom {
            background-color: #D1E7DD !important;
            color: #0F5132 !important;
            border: 1px solid #BADBCC;
            font-weight: 600;
        }

        .badge-secondary-custom {
            background-color: #E2E3F8 !important;
            color: var(--space-cadet) !important;
            border: 1px solid #C4C6F0;
            font-weight: 600;
        }

        footer.bg-light {
            background-color: #ffffff !important;
            border-top: 1px solid var(--lavender-mist);
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
                            <div class="sb-nav-link-icon"><i class="fas fa-box-open"></i></div>Kelola Produk
                        </a>
                        <a class="nav-link" href="sales.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-user-tie"></i></div>Pengelolaan Sales
                        </a>
                        <a class="nav-link active" href="promo.php">
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
                            <h2 class="fw-bolder mb-1" style="color: var(--space-cadet);">Manajemen Promo</h2>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="dashboard_admin.php" class="text-decoration-none text-muted">Dashboard</a></li>
                                <li class="breadcrumb-item active" style="color: var(--space-cadet);">Promo</li>
                            </ol>
                        </div>
                    </div>

                    <div class="card panel-card mb-4">
                        <div class="panel-header d-flex justify-content-between align-items-center">
                            <div><i class="fas fa-percent me-1"></i> Daftar Promo Aktif</div>
                            <button class="btn btn-custom-primary btn-sm fw-bold px-3 rounded" data-bs-toggle="modal" data-bs-target="#modalTambahPromo">
                                <i class="fas fa-plus me-1"></i> Tambah Promo
                            </button>
                        </div>
                        <div class="card-body bg-white p-3">
                            <table id="datatablesSimple" class="table table-striped table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Nama Promo</th>
                                        <th>Produk</th>
                                        <th class="text-center">Diskon</th>
                                        <th>Periode</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" width="12%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $today = date('Y-m-d');
                                    $query = "SELECT pr.*, p.nama_produk FROM promo pr 
                                                  JOIN produk p ON pr.id_produk = p.id_produk";
                                    $res = $koneksi->query($query);
                                    while ($row = $res->fetch_assoc()):
                                        $is_expired = ($today > $row['tgl_selesai']);
                                    ?>
                                        <tr>
                                            <td class="fw-bold" style="color: var(--space-cadet);"><?php echo htmlspecialchars($row['nama_promo']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                            <td class="text-center"><span class="badge bg-danger px-2 py-1"><?php echo $row['diskon_persen']; ?>%</span></td>
                                            <td>
                                                <small class="text-muted fw-medium">
                                                    <i class="far fa-calendar-alt me-1"></i><?php echo date('d M Y', strtotime($row['tgl_mulai'])); ?> - <br>
                                                    <i class="far fa-calendar-check me-1"></i><?php echo date('d M Y', strtotime($row['tgl_selesai'])); ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($is_expired): ?>
                                                    <span class="badge badge-secondary-custom px-3 py-1.5 rounded-pill">Berakhir</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success-custom px-3 py-1.5 rounded-pill">Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <a href="edit_promo.php?id=<?php echo $row['id_promo']; ?>" class="btn btn-warning btn-sm text-dark" title="Edit Promo">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalKonfirmasiHapus"
                                                        data-href="promo.php?hapus=<?php echo $row['id_promo']; ?>"
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

            <div class="modal fade" id="modalTambahPromo" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <form action="proses_promo.php" method="POST">
                            <div class="modal-header modal-header-custom">
                                <h5 class="modal-title fw-bold"><i class="fas fa-tags me-2 text-warning"></i>Tambah Promo Baru</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-dark bg-white">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" style="color: var(--space-cadet);">Nama Promo</label>
                                    <input type="text" name="nama_promo" class="form-control" placeholder="Contoh: Promo Ramadhan" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" style="color: var(--space-cadet);">Pilih Produk</label>
                                    <select name="id_produk" class="form-select" required>
                                        <option value="">-- Pilih Produk --</option>
                                        <?php
                                        $produk = $koneksi->query("SELECT id_produk, nama_produk FROM produk WHERE stok > 0");
                                        while ($p = $produk->fetch_assoc()) {
                                            echo "<option value='" . $p['id_produk'] . "'>" . htmlspecialchars($p['nama_produk']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold" style="color: var(--space-cadet);">Diskon (%)</label>
                                    <input type="number" name="diskon" class="form-control" min="1" max="100" placeholder="1 - 100" required>
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label fw-semibold" style="color: var(--space-cadet);">Tanggal Mulai</label>
                                        <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label fw-semibold" style="color: var(--space-cadet);">Tanggal Selesai</label>
                                        <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light">
                                <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="simpan_promo" class="btn btn-custom-primary px-4 shadow-sm fw-bold">Simpan Promo</button>
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
    <?php include 'modal_notifikasi.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', event => {
            const datatablesSimple = document.getElementById('datatablesSimple');
            if (datatablesSimple) {
                new simpleDatatables.DataTable(datatablesSimple);
            }

            // Script interaktif agar Tanggal Selesai tidak bisa dipilih sebelum Tanggal Mulai
            const tglMulai = document.getElementById('tgl_mulai');
            const tglSelesai = document.getElementById('tgl_selesai');

            if (tglMulai && tglSelesai) {
                tglMulai.addEventListener('change', function() {
                    tglSelesai.min = this.value;
                    if (tglSelesai.value && tglSelesai.value < this.value) {
                        tglSelesai.value = this.value;
                    }
                });
            }
        });
    </script>
</body>

</html>