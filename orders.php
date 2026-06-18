<?php
session_start();
include 'koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Logika Update Status Tracking
if (isset($_POST['update_status'])) {
    $id_order = mysqli_real_escape_string($koneksi, $_POST['id_order']);
    $status_baru = mysqli_real_escape_string($koneksi, $_POST['status_order']);

    // Validasi apakah sales sudah mengirim bukti transaksi
    $query_cek_bukti = $koneksi->query("SELECT bukti_transfer FROM orders WHERE id_order = '$id_order'");
    if ($query_cek_bukti && $query_cek_bukti->num_rows > 0) {
        $order_data = $query_cek_bukti->fetch_assoc();
        if (empty($order_data['bukti_transfer'])) {
            $_SESSION['gagal'] = "Gagal memperbarui status: Bukti transaksi belum dikirim atau telah ditolak oleh Admin!";
            header("Location: orders.php");
            exit;
        }
    }

    // Pastikan nama kolom di database Anda adalah status_order
    $update = $koneksi->query("UPDATE orders SET status_order = '$status_baru' WHERE id_order = '$id_order'");
    if ($update) {
        $_SESSION['sukses'] = "Status pesanan #$id_order berhasil diperbarui menjadi " . ucfirst($status_baru) . "!";
        header("Location: orders.php");
        exit;
    } else {
        $_SESSION['gagal'] = "Gagal memperbarui status: " . $koneksi->error;
        header("Location: orders.php");
        exit;
    }
}

// Logika Tolak Bukti Transfer (Minta Upload Ulang)
if (isset($_POST['tolak_bukti'])) {
    $id_order = mysqli_real_escape_string($koneksi, $_POST['id_order']);
    $alasan_tolak = mysqli_real_escape_string($koneksi, $_POST['alasan_tolak']);

    // Ambil info bukti transfer lama untuk dihapus filenya
    $query_cek = $koneksi->query("SELECT bukti_transfer FROM orders WHERE id_order = '$id_order'");
    if ($query_cek && $query_cek->num_rows > 0) {
        $order_data = $query_cek->fetch_assoc();
        $bukti_lama = $order_data['bukti_transfer'];
        $target_dir = 'assets/img/bukti_transfer/';
        if (!empty($bukti_lama) && file_exists($target_dir . $bukti_lama)) {
            unlink($target_dir . $bukti_lama);
        }
    }

    $update = $koneksi->query("UPDATE orders SET bukti_transfer = NULL, catatan_bukti = '$alasan_tolak' WHERE id_order = '$id_order'");
    if ($update) {
        $_SESSION['sukses'] = "Bukti transfer pesanan #$id_order berhasil ditolak. Sales diminta mengunggah ulang.";
        header("Location: orders.php");
        exit;
    } else {
        $_SESSION['gagal'] = "Gagal menolak bukti transfer: " . $koneksi->error;
        header("Location: orders.php");
        exit;
    }
}

// Logika Filter
$filter = isset($_GET['filter_status']) ? mysqli_real_escape_string($koneksi, $_GET['filter_status']) : 'semua';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Pesanan Masuk - E-Catalogue</title>
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
            color: white;
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

        /* Badges Status Tracking yang Lembut & Kontras */
        .badge-status {
            font-weight: 600;
            padding: 0.5rem 0.75rem;
            border-radius: 50px;
            letter-spacing: 0.3px;
        }

        .badge-pending {
            background-color: #FFF3CD !important;
            color: #856404 !important;
            border: 1px solid #FFEBAA;
        }

        .badge-proses {
            background-color: #D1ECF1 !important;
            color: #0C5460 !important;
            border: 1px solid #BEE5EB;
        }

        .badge-dikirim {
            background-color: var(--lavender-mist) !important;
            color: var(--space-cadet) !important;
            border: 1px solid #C4C6F0;
        }

        .badge-selesai {
            background-color: #D1E7DD !important;
            color: #0F5132 !important;
            border: 1px solid #BADBCC;
        }

        .badge-dibatalkan {
            background-color: #F8D7DA !important;
            color: #721C24 !important;
            border: 1px solid #F5C6CB;
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
                        <a class="nav-link" href="promo.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-percent"></i></div>Kelola Promo
                        </a>
                        <a class="nav-link active" href="orders.php">
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
                            <h2 class="fw-bolder mb-1" style="color: var(--space-cadet);">Monitoring Pesanan Masuk</h2>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="dashboard_admin.php" class="text-decoration-none text-muted">Dashboard</a></li>
                                <li class="breadcrumb-item active" style="color: var(--space-cadet);">Pesanan</li>
                            </ol>
                        </div>
                    </div>

                    <div class="card mb-4 border-0 shadow-sm" style="background-color: transparent;">
                        <div class="card-body bg-white rounded-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <form method="GET" class="d-flex gap-2">
                                        <select name="filter_status" class="form-select w-50 border-secondary-subtle">
                                            <option value="semua" <?php if ($filter == 'semua') echo 'selected'; ?>>Semua Status</option>
                                            <option value="pending" <?php if ($filter == 'pending') echo 'selected'; ?>>Pending</option>
                                            <option value="proses" <?php if ($filter == 'proses') echo 'selected'; ?>>Proses</option>
                                            <option value="dikirim" <?php if ($filter == 'dikirim') echo 'selected'; ?>>Dikirim</option>
                                            <option value="selesai" <?php if ($filter == 'selesai') echo 'selected'; ?>>Selesai</option>
                                            <option value="dibatalkan" <?php if ($filter == 'dibatalkan') echo 'selected'; ?>>Dibatalkan</option>
                                        </select>
                                        <button type="submit" class="btn btn-custom-primary px-4 fw-medium"><i class="fas fa-filter me-1"></i> Filter</button>
                                    </form>
                                </div>
                                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                    <a href="export_excel.php?filter_status=<?php echo $filter; ?>" class="btn btn-success fw-medium shadow-sm">
                                        <i class="fas fa-file-excel me-1"></i> Export Laporan (.xls)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card panel-card mb-4">
                        <div class="panel-header">
                            <div><i class="fas fa-shipping-fast me-1"></i> Tracking & Riwayat Transaksi Sales</div>
                        </div>
                        <div class="card-body bg-white p-3">
                            <table id="datatablesSimple" class="table table-striped table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th class="text-center">ID Order</th>
                                        <th>Tanggal</th>
                                        <th>Pelanggan</th>
                                        <th>Sales</th>
                                        <th>Total</th>
                                        <th class="text-center">Status Tracking</th>
                                        <th class="text-center">Bukti Transaksi</th>
                                        <th class="text-center" width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT o.*, u.nama_lengkap as nama_sales 
                                            FROM orders o LEFT JOIN users u ON o.id_user = u.id_user";

                                    if ($filter != 'semua') {
                                        $sql .= " WHERE o.status_order = '$filter'";
                                    }

                                    $sql .= " ORDER BY o.tgl_pesan DESC";
                                    $orders = $koneksi->query($sql);

                                    while ($row = $orders->fetch_assoc()):
                                    ?>
                                        <tr>
                                            <td class="text-center fw-bold" style="color: var(--space-cadet);">#<?php echo htmlspecialchars($row['id_order']); ?></td>
                                            <td><small class="fw-medium text-muted"><i class="far fa-calendar-alt me-1"></i><?php echo date('d/m/Y', strtotime($row['tgl_pesan'])); ?></small></td>
                                            <td class="fw-bold" style="color: var(--space-cadet);"><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                                            <td><span class="badge bg-light text-dark border px-2 py-1.5"><i class="fas fa-user-tag me-1 text-muted"></i><?php echo htmlspecialchars($row['nama_sales'] ?? 'Umum/Tanpa Sales'); ?></span></td>
                                            <td class="fw-bold text-danger">Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                            <td class="text-center">
                                                <?php
                                                $status = $row['status_order'] ?? 'pending';
                                                $badgeClass = 'badge-pending';
                                                if ($status == 'proses') $badgeClass = 'badge-proses';
                                                if ($status == 'selesai') $badgeClass = 'badge-selesai';
                                                if ($status == 'dikirim') $badgeClass = 'badge-dikirim';
                                                if ($status == 'dibatalkan') $badgeClass = 'badge-dibatalkan';
                                                ?>
                                                <span class="badge badge-status <?php echo $badgeClass; ?> text-uppercase">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($row['bukti_transfer'])): ?>
                                                    <button class="btn btn-sm text-white fw-bold shadow-sm" style="background-color: var(--accent-olive); border: none; font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#modalViewBukti<?php echo htmlspecialchars($row['id_order']); ?>">
                                                        <i class="fas fa-image me-1"></i> Lihat Bukti
                                                    </button>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1.5 text-uppercase" style="font-size: 0.7rem; font-weight: 600;">
                                                        <i class="fas fa-hourglass-start me-1"></i> Belum Ada
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (empty($row['bukti_transfer'])): ?>
                                                    <button class="btn btn-warning text-dark btn-sm fw-medium shadow-sm" disabled style="opacity: 0.65; cursor: not-allowed;" title="Sales belum mengirim bukti transaksi atau bukti ditolak">
                                                        <i class="fas fa-edit me-1"></i> Update
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-warning text-dark btn-sm fw-medium shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUpdate<?php echo htmlspecialchars($row['id_order']); ?>">
                                                        <i class="fas fa-edit me-1"></i> Update
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <!-- Modal Lihat Bukti Transfer -->
                                        <?php if (!empty($row['bukti_transfer'])): ?>
                                            <div class="modal fade" id="modalViewBukti<?php echo htmlspecialchars($row['id_order']); ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content shadow-lg border-0" style="border-radius: 12px; overflow: hidden;">
                                                        <div class="modal-header modal-header-custom">
                                                            <h5 class="modal-title fw-bold"><i class="fas fa-receipt me-2 text-warning"></i> Bukti Transfer #<?php echo htmlspecialchars($row['id_order']); ?></h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body bg-white text-center p-4">
                                                            <span class="d-block small text-muted mb-3">Pesanan oleh: <strong><?php echo htmlspecialchars($row['nama_pelanggan']); ?></strong></span>
                                                            <div class="p-2 border rounded bg-light d-inline-block w-100">
                                                                <img src="assets/img/bukti_transfer/<?php echo htmlspecialchars($row['bukti_transfer']); ?>" alt="Bukti Transfer" class="img-fluid rounded shadow-sm" style="max-height: 400px; object-fit: contain;">
                                                            </div>
                                                            <div class="mt-3">
                                                                <a href="assets/img/bukti_transfer/<?php echo htmlspecialchars($row['bukti_transfer']); ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                                                    <i class="fas fa-external-link-alt me-1"></i> Buka Gambar Penuh
                                                                </a>
                                                            </div>
                                                            <hr class="my-3 opacity-25">
                                                            <form method="POST" action="orders.php" class="text-start mt-3 p-3 bg-light rounded border">
                                                                <input type="hidden" name="id_order" value="<?php echo htmlspecialchars($row['id_order']); ?>">
                                                                <div class="mb-3">
                                                                    <label for="alasan_tolak_<?php echo htmlspecialchars($row['id_order']); ?>" class="form-label fw-bold text-dark small mb-1">
                                                                        <i class="fas fa-comment-dots text-danger me-1"></i> Alasan Bukti Tidak Sesuai:
                                                                    </label>
                                                                    <textarea name="alasan_tolak" id="alasan_tolak_<?php echo htmlspecialchars($row['id_order']); ?>" class="form-control form-control-sm border-secondary-subtle" rows="2" placeholder="Tulis alasan penolakan... (contoh: Gambar buram / nominal salah)" required></textarea>
                                                                </div>
                                                                <button type="submit" name="tolak_bukti" class="btn btn-danger btn-sm fw-bold w-100 py-2 shadow-sm">
                                                                    <i class="fas fa-times-circle me-1"></i> Tolak Bukti & Minta Upload Ulang
                                                                </button>
                                                            </form>
                                                        </div>
                                                        <div class="modal-footer bg-light p-2 border-0">
                                                            <button type="button" class="btn btn-secondary btn-sm w-100 py-2 fw-bold" data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>


                                        <div class="modal fade" id="modalUpdate<?php echo htmlspecialchars($row['id_order']); ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-sm modal-dialog-centered">
                                                <div class="modal-content shadow-lg border-0">
                                                    <form method="POST" action="orders.php">
                                                        <div class="modal-header modal-header-custom">
                                                            <h5 class="modal-title fw-bold"><i class="fas fa-route me-1 text-warning"></i> Update Status</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body bg-white text-dark">
                                                            <input type="hidden" name="id_order" value="<?php echo htmlspecialchars($row['id_order']); ?>">
                                                            <div class="mb-2 text-center">
                                                                <span class="small text-muted">ID Pesanan:</span>
                                                                <strong class="d-block" style="color: var(--space-cadet);">#<?php echo htmlspecialchars($row['id_order']); ?></strong>
                                                            </div>
                                                            <hr class="my-2 opacity-25">
                                                            <label class="form-label fw-semibold small mb-2" style="color: var(--space-cadet);">Pilih Status Baru:</label>
                                                            <select name="status_order" class="form-select border-secondary-subtle">
                                                                <option value="pending" <?php if (($row['status_order'] ?? 'pending') == 'pending') echo 'selected'; ?>>Pending</option>
                                                                <option value="proses" <?php if (($row['status_order'] ?? '') == 'proses') echo 'selected'; ?>>Proses</option>
                                                                <option value="dikirim" <?php if (($row['status_order'] ?? '') == 'dikirim') echo 'selected'; ?>>Dikirim</option>
                                                                <option value="selesai" <?php if (($row['status_order'] ?? '') == 'selesai') echo 'selected'; ?>>Selesai</option>
                                                                <option value="dibatalkan" <?php if (($row['status_order'] ?? '') == 'dibatalkan') echo 'selected'; ?>>Dibatalkan</option>
                                                            </select>
                                                        </div>
                                                        <div class="modal-footer bg-light p-2">
                                                            <button type="submit" name="update_status" class="btn btn-custom-primary btn-sm w-100 py-2 fw-bold shadow-sm">Simpan Perubahan</button>
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
                        <div class="text-muted fw-medium">PT Rahayu Karunia Utama &copy; <?php echo date('Y'); ?> E-Catalogue</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <?php include 'modal_logout.php'; ?>
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
        });
    </script>
</body>

</html>