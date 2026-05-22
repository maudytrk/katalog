<?php
session_start();
include 'koneksi.php';

// Proteksi halaman: Jika belum login, kembalikan ke login.php
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Mengambil data ringkasan dari database
$countProduk   = $koneksi->query("SELECT COUNT(*) as total FROM produk")->fetch_assoc()['total'];
$countSales    = $koneksi->query("SELECT COUNT(*) as total FROM users WHERE role = 'sales'")->fetch_assoc()['total'];
$countPromo    = $koneksi->query("SELECT COUNT(*) as total FROM promo")->fetch_assoc()['total'];
$countOrder    = $koneksi->query("SELECT COUNT(*) as total FROM orders WHERE status_order = 'pending'")->fetch_assoc()['total'];
$countKategori = $koneksi->query("SELECT COUNT(*) as total FROM kategori")->fetch_assoc()['total'];

// ==================== QUERY ANALISIS KLIK MARKETPLACE ====================
$chart_data = ['tiktok' => 0, 'shopee' => 0, 'lazada' => 0];
// Menggunakan LOWER() agar pencarian data tidak case-sensitive (sensitif huruf besar/kecil)
$sql_chart  = "SELECT LOWER(platform) as platform_clean, COUNT(*) as total 
               FROM log_klik_marketplace 
               WHERE LOWER(platform) IN ('tiktok', 'shopee', 'lazada') 
               GROUP BY LOWER(platform)";
$res_chart  = $koneksi->query($sql_chart);

if($res_chart) {
    while($row_chart = $res_chart->fetch_assoc()) {
        $platform_name = $row_chart['platform_clean'];
        if(array_key_exists($platform_name, $chart_data)) {
            $chart_data[$platform_name] = (int)$row_chart['total'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Dashboard Admin - E-Catalogue</title>
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
                background-color: #373029 !important; /* Cokelat gelap berbasis kontras tekstur kayu/earthy */
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
                color: white !important;
                background-color: var(--dark-olive) !important;
            }
            .sb-sidenav-dark .nav-link:hover {
                color: white !important;
                background-color: rgba(125, 143, 55, 0.3) !important;
            }
            .sb-sidenav-footer {
                background-color: #2b2520 !important;
                color: var(--pastel-purple) !important;
            }

            /* Overriding Warna Utama Info Card Kontras Tinggi */
            .bg-custom-produk { background-color: var(--olive-green) !important; color: #fff !important; }
            .bg-custom-sales { background-color: #5C6BC0 !important; color: #fff !important; }
            .bg-custom-promo { background-color: #D9A05B !important; color: #fff !important; }
            .bg-custom-pesanan { background-color: #C0392B !important; color: #fff !important; }
            .bg-custom-kategori { background-color: #26A69A !important; color: #fff !important; }
            
            /* Fleksibilitas Kartu Bertema */
            .bg-custom-produk, .bg-custom-sales, .bg-custom-promo, .bg-custom-pesanan, .bg-custom-kategori {
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            }
            /* Penyesuaian khusus Warna Khusus Elemen Informasi */
            .card {
                border: 1px solid rgba(125, 143, 55, 0.15);
            }
            .card-header {
                background-color: rgba(224, 210, 240, 0.4) !important;
                color: var(--text-dark) !important;
                font-weight: 600;
                border-bottom: 1px solid rgba(125, 143, 55, 0.15);
            }
            
            /* Penyesuaian Footer Utama */
            footer.bg-light {
                background-color: var(--pastel-purple) !important;
                border-top: 1px solid rgba(125, 143, 55, 0.2);
            }
            footer .text-muted {
                color: var(--text-dark) !important;
                font-weight: 500;
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
                            <a class="nav-link active" href="dashboard_admin.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>
                            <div class="sb-sidenav-menu-heading">Manajemen Data</div>
                            <a class="nav-link" href="kategori.php"><div class="sb-nav-link-icon"><i class="fas fa-folder"></i></div>Kelola Kategori</a>
                            <a class="nav-link" href="produk.php"><div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>Kelola Produk</a>
                            <a class="nav-link" href="sales.php"><div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>Pengelolaan Sales</a>
                            <a class="nav-link" href="promo.php"><div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>Kelola Promo</a>
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
                        <div class="small">Logged in as:</div> <?php echo ucfirst($_SESSION['role'] ?? 'Admin'); ?>
                    </div>
                </nav>
            </div>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Dashboard Admin</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Ringkasan Sistem Informasi E-Catalogue</li>
                        </ol>
                        
                        <div class="row">
                            <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                                <div class="card bg-custom-kategori text-white h-100">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <span>Data Kategori</span>
                                        <h3 class="fw-bold mb-0"><?php echo $countKategori; ?></h3>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between" style="background: rgba(0,0,0,0.15);">
                                        <a class="small text-white stretched-link text-decoration-none" href="kategori.php">Lihat Detail</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                                <div class="card bg-custom-produk text-white h-100">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <span>Katalog Produk</span>
                                        <h3 class="fw-bold mb-0"><?php echo $countProduk; ?></h3>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between" style="background: rgba(0,0,0,0.15);">
                                        <a class="small text-white stretched-link text-decoration-none" href="produk.php">Lihat Detail</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                                <div class="card bg-custom-sales text-white h-100">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <span>Data Sales</span>
                                        <h3 class="fw-bold mb-0"><?php echo $countSales; ?></h3>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between" style="background: rgba(0,0,0,0.15);">
                                        <a class="small text-white stretched-link text-decoration-none" href="sales.php">Lihat Detail</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 col-sm-6 mb-4">
                                <div class="card bg-custom-promo text-white h-100">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <span>Promo Aktif</span>
                                        <h3 class="fw-bold mb-0"><?php echo $countPromo; ?></h3>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between" style="background: rgba(0,0,0,0.15);">
                                        <a class="small text-white stretched-link text-decoration-none" href="promo.php">Lihat Detail</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 col-sm-6 mb-4">
                                <div class="card bg-custom-pesanan text-white h-100">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <span>Pesanan Baru</span>
                                        <h3 class="fw-bold mb-0"><?php echo $countOrder; ?></h3>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between" style="background: rgba(0,0,0,0.15);">
                                        <a class="small text-white stretched-link text-decoration-none" href="orders.php">Lihat Detail</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Perbandingan Performa Klik Link Marketplace External
                                    </div>
                                    <div class="card-body bg-white">
                                        <canvas id="marketplaceChart" width="100%" height="50"></canvas>
                                    </div>
                                    <div class="card-footer small text-muted bg-white">
                                        Diperbarui secara real-time dari log_klik_marketplace.
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-table me-1"></i>
                                        Informasi Pesanan Terbaru
                                    </div>
                                    <div class="card-body bg-white">
                                        <table id="datatablesSimple" class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Tgl Pesan</th>
                                                    <th>Pelanggan</th>
                                                    <th>Sales</th>
                                                    <th>Total Bayar</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $orders = $koneksi->query("SELECT o.*, u.nama_lengkap as nama_sales 
                                                                           FROM orders o JOIN users u ON o.id_user = u.id_user 
                                                                           ORDER BY tgl_pesan DESC LIMIT 5");
                                                while($row = $orders->fetch_assoc()):
                                                ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($row['tgl_pesan'])); ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['nama_sales']); ?></td>
                                                    <td>Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo ($row['status_order'] == 'pending') ? 'bg-warning text-dark' : 'bg-success'; ?>">
                                                            <?php echo ucfirst($row['status_order']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
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
        
        <?php include 'modal_logout.php'; ?>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script>
            window.addEventListener('DOMContentLoaded', event => {
                const datatablesSimple = document.getElementById('datatablesSimple');
                if (datatablesSimple) {
                    new simpleDatatables.DataTable(datatablesSimple);
                }
                
                // ==================== INISIALISASI CHART.JS ====================
                const ctx = document.getElementById('marketplaceChart').getContext('2d');
                const marketplaceChart = new Chart(ctx, {
                    type: 'bar', 
                    data: {
                        labels: ['TikTok Shop', 'Shopee', 'Lazada'],
                        datasets: [{
                            label: 'Jumlah Akumulasi Klik',
                            data: [
                                <?php echo $chart_data['tiktok']; ?>, 
                                <?php echo $chart_data['shopee']; ?>, 
                                <?php echo $chart_data['lazada']; ?>
                            ],
                            backgroundColor: [
                                'rgba(37, 37, 37, 0.85)',   // TikTok
                                'rgba(238, 77, 45, 0.85)',  // Shopee
                                'rgba(15, 26, 162, 0.85)'   // Lazada
                            ],
                            borderColor: [
                                'rgba(37, 37, 37, 1)',
                                'rgba(238, 77, 45, 1)',
                                'rgba(15, 26, 162, 1)'
                            ],
                            borderWidth: 1.5
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false 
                            }
                        }
                    }
                });
            });
        </script>
    </body>
</html>