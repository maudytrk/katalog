<?php
// 1. Inisialisasi Session dan Koneksi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

// 2. Proteksi Halaman (Hanya untuk Sales yang Login)
$is_login  = isset($_SESSION['login']) && $_SESSION['login'] === true;
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

if (!$is_login || $user_role !== 'sales') {
    // Jika bukan sales, kembalikan ke index
    header("Location: index.php");
    exit;
}

// 3. Mengambil Data Pesanan Khusus Sales yang sedang Login
// Sesuaikan 'id_user' dengan nama kolom ID di tabel sales/user Anda
$id_sales_aktif = $_SESSION['user_id'] ?? 0;

// Query untuk mengambil riwayat order. Menggabungkan tabel orders dan order_detail
// Asumsi: tabel orders memiliki kolom (id_order, id_user, nama_pelanggan, tgl_pesan, total_bayar, status_order)
$query_riwayat = "SELECT o.id_order, o.nama_pelanggan, o.tgl_pesan, o.total_bayar, o.status_order,
                         IFNULL(SUM(od.jumlah), 0) as jumlah_item
                  FROM orders o
                  LEFT JOIN order_detail od ON o.id_order = od.id_order
                  WHERE o.id_user = '$id_sales_aktif'
                  GROUP BY o.id_order
                  ORDER BY o.tgl_pesan DESC";

$result_riwayat = $koneksi->query($query_riwayat);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan Sales - Rahayu Catalogue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-lavender: #E0E1F6;
            --accent-indigo: #241F48;
            --accent-plum: #6C4773;
            --accent-gray: #B0B7CA;
            --soft-cream: #F8F9FA;
        }

        body {
            background-color: var(--soft-cream);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--accent-indigo);
        }

        /* Tema Dashboard Header */
        .dashboard-header {
            background: linear-gradient(135deg, var(--accent-indigo) 0%, var(--accent-plum) 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: -40px;
            /* Overlap effect */
            padding-bottom: 80px;
        }

        /* Glassmorphism Card Effect */
        .card-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(36, 31, 72, 0.08);
        }

        /* Styling Tabel */
        .table-custom th {
            background-color: var(--bg-lavender);
            color: var(--accent-indigo);
            font-weight: 700;
            border-bottom: 2px solid var(--accent-gray);
            padding: 15px;
        }

        .table-custom td {
            vertical-align: middle;
            padding: 15px;
            border-color: #f0f0f0;
        }

        .table-custom tbody tr:hover {
            background-color: var(--soft-cream);
        }

        /* Status Badges */
        .badge-pending {
            background-color: #f39c12;
            color: #fff;
        }

        .badge-proses {
            background-color: var(--accent-indigo);
            color: #fff;
        }

        .badge-selesai {
            background-color: #27ae60;
            color: #fff;
        }

        .badge-batal {
            background-color: #e74c3c;
            color: #fff;
        }

        /* Tombol */
        .btn-theme {
            background-color: var(--accent-plum);
            color: white;
            border: none;
            transition: 0.3s;
        }

        .btn-theme:hover {
            background-color: var(--accent-indigo);
            color: white;
        }
    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="dashboard-header text-center">
        <div class="container">
            <h2 class="fw-bold"><i class="fas fa-clipboard-list me-2 text-warning"></i>Riwayat Pesanan Sales</h2>
            <p class="opacity-75">Pantau seluruh transaksi grosir dan pesanan lapangan yang telah Anda input.</p>
        </div>
    </div>

    <div class="container mb-5 position-relative" style="z-index: 10;">
        <div class="card card-glass p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0" style="color: var(--accent-indigo);">Daftar Transaksi Terakhir</h5>
                <a href="katalog.php" class="btn btn-theme btn-sm rounded-pill px-4 fw-bold">
                    <i class="fas fa-plus me-1"></i> Buat Pesanan Baru
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="15%">ID Order</th>
                            <th width="20%">Tanggal Transaksi</th>
                            <th width="25%">Nama Pelanggan</th>
                            <th class="text-center" width="10%">Jumlah Item</th>
                            <th class="text-end" width="15%">Total Bayar</th>
                            <th class="text-center" width="10%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_riwayat && $result_riwayat->num_rows > 0): ?>
                            <?php $no = 1;
                            while ($row = $result_riwayat->fetch_assoc()):

                                // Tentukan warna badge berdasarkan status
                                $status = strtolower($row['status_order']);
                                $badge_class = 'bg-secondary';
                                if ($status == 'pending') $badge_class = 'badge-pending';
                                elseif ($status == 'proses') $badge_class = 'badge-proses';
                                elseif ($status == 'selesai') $badge_class = 'badge-selesai';
                                elseif ($status == 'dibatalkan') $badge_class = 'badge-batal';
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $no++; ?></td>
                                    <td>
                                        <a href="lacak_pesanan.php?keyword=<?= $row['id_order']; ?>" class="fw-bold text-decoration-none" style="color: var(--accent-plum);">
                                            #<?= htmlspecialchars($row['id_order']); ?>
                                        </a>
                                    </td>
                                    <td class="text-muted small">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?= date('d M Y, H:i', strtotime($row['tgl_pesan'])); ?>
                                    </td>
                                    <td class="fw-semibold" style="color: var(--accent-indigo);">
                                        <?= htmlspecialchars($row['nama_pelanggan']); ?>
                                    </td>
                                    <td class="text-center fw-bold text-secondary">
                                        <?= $row['jumlah_item']; ?> Pcs
                                    </td>
                                    <td class="text-end fw-bold" style="color: var(--accent-indigo);">
                                        Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $badge_class; ?> rounded-pill px-3 py-2 text-uppercase" style="font-size: 0.75rem;">
                                            <?= htmlspecialchars($status); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3 opacity-50"></i>
                                    <h6 class="text-muted fw-bold">Belum ada transaksi yang Anda buat.</h6>
                                    <p class="small text-muted mb-0">Pesanan yang Anda input melalui fitur "Pesanan Cepat" di Katalog akan muncul di sini.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($result_riwayat && $result_riwayat->num_rows > 0): ?>
                <div class="mt-4 pt-3 border-top text-end">
                    <p class="small text-muted mb-0"><i class="fas fa-info-circle me-1"></i> Klik ID Order untuk melihat detail pelacakan pengiriman.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div style="height: 50px;"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>