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

if ($res_chart) {
    while ($row_chart = $res_chart->fetch_assoc()) {
        $platform_name = $row_chart['platform_clean'];
        if (array_key_exists($platform_name, $chart_data)) {
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
            --accent-olive-hover: #65752b;
        }

        body {
            background-color: var(--bg-cream);
            color: var(--space-cadet);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Navbar & Sidebar Custom */
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
            /* Slightly darker Space Cadet */
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

        /* Kartu Ringkasan (Summary Cards) */
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

        /* Border Kiri Kartu Berwarna */
        .border-l-kategori {
            border-left: 5px solid var(--accent-olive);
        }

        .text-kategori {
            color: var(--accent-olive);
        }

        .border-l-produk {
            border-left: 5px solid var(--old-heliotrope);
        }

        .text-produk {
            color: var(--old-heliotrope);
        }

        .border-l-sales {
            border-left: 5px solid var(--royal-fuchsia);
        }

        .text-sales {
            color: var(--royal-fuchsia);
        }

        .border-l-promo {
            border-left: 5px solid var(--tyrian-purple);
        }

        .text-promo {
            color: var(--tyrian-purple);
        }

        .border-l-pesanan {
            border-left: 5px solid var(--space-cadet);
        }

        .text-pesanan {
            color: var(--space-cadet);
        }

        /* Panel Card Biasa (Grafik & Tabel) */
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

        /* Kustomisasi Tabel & Badge */
        .table>thead {
            background-color: var(--space-cadet);
            color: white;
        }

        .badge-status-pending {
            background-color: #FFF3CD;
            color: #856404;
            border: 1px solid #FFEBAA;
        }

        .badge-status-selesai {
            background-color: #D1E7DD;
            color: #0F5132;
            border: 1px solid #BADBCC;
        }

        /* Footer */
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
                        <a class="nav-link active" href="dashboard_admin.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
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
                            <div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div>
                            Kelola Akun
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small text-white-50">Logged in as:</div>
                    <span class="fw-bold text-white"><?php echo ucfirst($_SESSION['role'] ?? 'Admin'); ?></span>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4 py-4">
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <h2 class="fw-bolder mb-1" style="color: var(--space-cadet);">Dashboard Overview</h2>
                            <p class="text-muted mb-0">Ringkasan Sistem Informasi E-Catalogue Rahayu</p>
                        </div>
                        <div class="text-muted small fw-medium">
                            <i class="far fa-calendar-alt me-1"></i> <?php echo date('l, d F Y'); ?>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-xl-2 col-md-4 col-sm-6">
                            <div class="summary-card border-l-kategori h-100 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase fw-bold text-kategori small mb-1">Data Kategori</div>
                                        <h3 class="fw-bold text-dark mb-0"><?php echo $countKategori; ?></h3>
                                    </div>
                                </div>
                                <i class="fas fa-tags summary-icon text-kategori"></i>
                                <a href="kategori.php" class="stretched-link"></a>
                            </div>
                        </div>

                        <div class="col-xl-2 col-md-4 col-sm-6">
                            <div class="summary-card border-l-produk h-100 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase fw-bold text-produk small mb-1">Total Produk</div>
                                        <h3 class="fw-bold text-dark mb-0"><?php echo $countProduk; ?></h3>
                                    </div>
                                </div>
                                <i class="fas fa-box-open summary-icon text-produk"></i>
                                <a href="produk.php" class="stretched-link"></a>
                            </div>
                        </div>

                        <div class="col-xl-2 col-md-4 col-sm-6">
                            <div class="summary-card border-l-sales h-100 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase fw-bold text-sales small mb-1">Data Sales</div>
                                        <h3 class="fw-bold text-dark mb-0"><?php echo $countSales; ?></h3>
                                    </div>
                                </div>
                                <i class="fas fa-user-tie summary-icon text-sales"></i>
                                <a href="sales.php" class="stretched-link"></a>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 col-sm-6">
                            <div class="summary-card border-l-promo h-100 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase fw-bold text-promo small mb-1">Promo Aktif</div>
                                        <h3 class="fw-bold text-dark mb-0"><?php echo $countPromo; ?></h3>
                                    </div>
                                </div>
                                <i class="fas fa-percent summary-icon text-promo"></i>
                                <a href="promo.php" class="stretched-link"></a>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 col-sm-6">
                            <div class="summary-card border-l-pesanan h-100 p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-uppercase fw-bold text-pesanan small mb-1">Pesanan Baru (Pending)</div>
                                        <h3 class="fw-bold text-dark mb-0"><?php echo $countOrder; ?></h3>
                                    </div>
                                </div>
                                <i class="fas fa-shopping-cart summary-icon text-pesanan"></i>
                                <a href="orders.php" class="stretched-link"></a>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-xl-6">
                            <div class="card panel-card h-100">
                                <div class="panel-header">
                                    <i class="fas fa-chart-pie me-2" style="color: var(--old-heliotrope);"></i>
                                    Perbandingan Klik Link Eksternal
                                </div>
                                <div class="card-body bg-white d-flex align-items-center">
                                    <canvas id="marketplaceChart" width="100%" height="50"></canvas>
                                </div>
                                <div class="card-footer small text-muted bg-white border-0 pt-0 pb-3 text-center">
                                    <i class="fas fa-clock me-1"></i> Data real-time log klik marketplace
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card panel-card h-100">
                                <div class="panel-header">
                                    <i class="fas fa-clipboard-list me-2" style="color: var(--old-heliotrope);"></i>
                                    Pesanan Masuk Terakhir
                                </div>
                                <div class="card-body bg-white p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead style="background-color: var(--space-cadet); color: white;">
                                                <tr>
                                                    <th class="ps-3 border-0 py-3 font-weight-normal">Tanggal</th>
                                                    <th class="border-0 py-3 font-weight-normal">Pelanggan</th>
                                                    <th class="border-0 py-3 font-weight-normal">Sales</th>
                                                    <th class="border-0 py-3 text-end font-weight-normal">Total</th>
                                                    <th class="border-0 py-3 text-center pe-3 font-weight-normal">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="border-top-0">
                                                <?php
                                                $orders = $koneksi->query("SELECT o.*, u.nama_lengkap as nama_sales 
                                                                           FROM orders o JOIN users u ON o.id_user = u.id_user 
                                                                           ORDER BY tgl_pesan DESC LIMIT 5");
                                                if ($orders->num_rows > 0):
                                                    while ($row = $orders->fetch_assoc()):
                                                ?>
                                                        <tr>
                                                            <td class="ps-3 text-muted small"><?php echo date('d/m/y H:i', strtotime($row['tgl_pesan'])); ?></td>
                                                            <td class="fw-bold" style="color: var(--space-cadet);"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                                                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['nama_sales']); ?></span></td>
                                                            <td class="text-end fw-semibold text-danger">Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                                            <td class="text-center pe-3">
                                                                <span class="badge rounded-pill px-3 py-1 <?php echo ($row['status_order'] == 'pending') ? 'badge-status-pending' : 'badge-status-selesai'; ?>">
                                                                    <?php echo ucfirst($row['status_order']); ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    endwhile;
                                                else:
                                                    ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data pesanan.</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0 text-center py-3">
                                    <a href="orders.php" class="btn btn-sm btn-outline-secondary rounded-pill px-4">Lihat Semua Pesanan <i class="fas fa-arrow-right ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', event => {
            // ==================== INISIALISASI CHART.JS ====================
            // (Logika dan Data Chart Tidak Berubah, Hanya Penyesuaian Warna Tema)
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
                            '#231F48', // Space Cadet (TikTok)
                            '#BB3F95', // Royal Fuchsia (Shopee)
                            '#6B4773' // Old Heliotrope (Lazada)
                        ],
                        borderColor: [
                            '#121026',
                            '#963276',
                            '#4a3150'
                        ],
                        borderWidth: 0,
                        borderRadius: 5,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [2, 4],
                                color: '#E0E1F6'
                            },
                            ticks: {
                                stepSize: 1,
                                color: '#6c757d'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#231F48',
                                font: {
                                    weight: 'bold'
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(35, 31, 72, 0.9)',
                            padding: 10,
                            cornerRadius: 8
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>