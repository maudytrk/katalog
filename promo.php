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
        echo "<script>alert('Promo berhasil dihapus!'); window.location='promo.php';</script>";
    } else {
        echo "<script>alert('Data promo tidak ditemukan!'); window.location='promo.php';</script>";
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

            /* Kustomisasi Card & Tabel */
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

            /* Penyesuaian Elemen Teks */
            h1, .breadcrumb-item.active, .breadcrumb-item a {
                color: var(--text-dark) !important;
                text-decoration: none;
            }
            .breadcrumb-item a:hover {
                color: var(--dark-olive) !important;
                text-decoration: underline;
            }
            
            /* Badge Kustom */
            .badge-success-custom {
                background-color: #E8EDD5 !important;
                color: var(--dark-olive) !important;
                border: 1px solid var(--olive-green);
                font-weight: 600;
            }
            .badge-secondary-custom {
                background-color: #EAEAEA !important;
                color: #6C757D !important;
                border: 1px solid #CED4DA;
                font-weight: 600;
            }

            /* Penyesuaian Footer Utama */
            footer.bg-light {
                background-color: var(--pastel-purple) !important;
                border-top: 1px solid rgba(125, 143, 55, 0.2);
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
                            
                            <a class="nav-link" href="produk.php"><div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>Kelola Produk</a>
                            <a class="nav-link" href="sales.php"><div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>Pengelolaan Sales</a>
                            <a class="nav-link active" href="promo.php"><div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>Kelola Promo</a>
                            <a class="nav-link" href="orders.php"><div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>Pesanan Masuk</a>
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
                        <h1 class="mt-4">Manajemen Promo</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard_admin.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Promo</li>
                        </ol>

                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div><i class="fas fa-percent me-1"></i> Daftar Promo Aktif</div>
                                <button class="btn btn-custom-olive btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahPromo">
                                    <i class="fas fa-plus me-1"></i> Tambah Promo
                                </button>
                            </div>
                            <div class="card-body bg-white">
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
                                        while($row = $res->fetch_assoc()):
                                            $is_expired = ($today > $row['tgl_selesai']);
                                        ?>
                                        <tr>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($row['nama_promo']); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                            <td class="text-center"><span class="badge bg-danger px-2 py-1"><?php echo $row['diskon_persen']; ?>%</span></td>
                                            <td>
                                                <small class="text-muted fw-medium">
                                                    <i class="far fa-calendar-alt me-1"></i><?php echo date('d M Y', strtotime($row['tgl_mulai'])); ?> - <br>
                                                    <i class="far fa-calendar-check me-1"></i><?php echo date('d M Y', strtotime($row['tgl_selesai'])); ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <?php if($is_expired): ?>
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
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="proses_promo.php" method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fas fa-tags me-2"></i>Tambah Promo Baru</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-dark bg-white">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Nama Promo</label>
                                        <input type="text" name="nama_promo" class="form-control" placeholder="Contoh: Promo Ramadhan" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Pilih Produk</label>
                                        <select name="id_produk" class="form-select" required>
                                            <option value="">-- Pilih Produk --</option>
                                            <?php
                                            $produk = $koneksi->query("SELECT id_produk, nama_produk FROM produk WHERE stok > 0");
                                            while($p = $produk->fetch_assoc()) {
                                                echo "<option value='".$p['id_produk']."'>".htmlspecialchars($p['nama_produk'])."</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Diskon (%)</label>
                                        <input type="number" name="diskon" class="form-control" min="1" max="100" placeholder="1 - 100" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label class="form-label fw-semibold">Tanggal Mulai</label>
                                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label fw-semibold">Tanggal Selesai</label>
                                            <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="simpan_promo" class="btn btn-custom-olive px-4 shadow-sm fw-bold">Simpan Promo</button>
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

                // Script interaktif agar Tanggal Selesai tidak bisa dipilih sebelum Tanggal Mulai
                const tglMulai = document.getElementById('tgl_mulai');
                const tglSelesai = document.getElementById('tgl_selesai');
                
                if(tglMulai && tglSelesai){
                    tglMulai.addEventListener('change', function() {
                        tglSelesai.min = this.value;
                        if(tglSelesai.value && tglSelesai.value < this.value){
                            tglSelesai.value = this.value;
                        }
                    });
                }
            });
        </script>
    </body>
</html>