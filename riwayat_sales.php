<?php
// 1. Inisialisasi Session dan Koneksi Database
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

// 2. Proteksi Hak Akses (Hanya boleh diakses oleh sales yang sudah login)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'sales') {
    die("Akses ditolak! Halaman ini hanya dapat diakses oleh Tim Sales resmi.");
}

// Mengambil ID Sales dari session yang aktif saat login
$id_sales_aktif = $_SESSION['user_id'];

// 3. Query Ambil Data Riwayat Orders khusus untuk Sales yang sedang login
// Menggunakan INNER JOIN ke tabel order_detail dan produk untuk opsional jika ingin menampilkan ringkasan item (opsional)
$sql_riwayat = "SELECT * FROM orders 
                WHERE id_user = '$id_sales_aktif' 
                ORDER BY tgl_pesan DESC";
$result_riwayat = $koneksi->query($sql_riwayat);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Input Sales - Rahayu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-cream: #FCF8F1;
            --navbar-purple: #E0D2F0;
            --accent-olive: #7D8F37;
            --accent-olive-hover: #65752b;
        }
        body {
            background-color: var(--bg-cream);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-custom { 
            background-color: var(--navbar-purple) !important; 
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            background-color: #ffffff;
        }
        .table-custom thead {
            background-color: #f8f9fa;
            color: #495057;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-1">
                    <i class="fas fa-history text-muted me-2"></i>Riwayat Input Pesanan
                </h3>
                <p class="text-muted mb-0">Memantau status real-time dari seluruh transaksi pelanggan yang telah Anda input ke sistem.</p>
            </div>
            <div>
                <span class="badge bg-white text-dark border p-2 px-3 rounded-pill shadow-sm">
                    <i class="fas fa-user-tag text-success me-2"></i>Sales: <strong><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Akun Sales'); ?></strong>
                </span>
            </div>
        </div>

        <div class="alert alert-warning d-flex align-items-center rounded-3 shadow-sm mb-4" role="alert">
            <i class="fas fa-info-circle fs-4 me-3 flex-shrink-0"></i>
            <div>
                <small class="d-block fw-bold">Mode Pengawasan (Read-Only)</small>
                Halaman ini bersifat monitor pasif. Untuk melakukan pembatalan kesalahan input atau modifikasi status perubahan berkala, silakan hubungi berkas administrasi pusat (Admin Utama).
            </div>
        </div>

        <div class="card card-custom shadow-sm overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase font-monospace small border-bottom">
                            <tr>
                                <th class="text-center py-3" style="width: 7%;">No</th>
                                <th class="py-3">Tanggal Transaksi</th>
                                <th class="py-3">ID Order Unik</th>
                                <th class="py-3">Nama Pelanggan</th>
                                <th class="text-end py-3">Total Bayar</th>
                                <th class="text-center py-3" style="width: 15%;">Status Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($result_riwayat && $result_riwayat->num_rows > 0) : 
                                $no = 1;
                                while ($row = $result_riwayat->fetch_assoc()) : 
                                    $status_raw = strtolower($row['status_order'] ?? 'pending');
                            ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted"><?= $no++; ?></td>
                                    
                                    <td>
                                        <span class="d-block text-dark fw-semibold">
                                            <?= date('d M Y', strtotime($row['tgl_pesan'])); ?>
                                        </span>
                                        <small class="text-muted text-xs">
                                            Jam <?= date('H:i', strtotime($row['tgl_pesan'])); ?> WIB
                                        </small>
                                    </td>
                                    
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1.5">
                                            #ORD-<?= str_pad($row['id_order'], 5, '0', STR_PAD_LEFT); ?>
                                        </span>
                                    </td>
                                    
                                    <td class="fw-bold text-secondary">
                                        <?= htmlspecialchars($row['nama_pelanggan']); ?>
                                        <?php if(!empty($row['no_hp'])): ?>
                                            <span class="d-block font-monospace text-muted small fw-normal">
                                                <i class="fab fa-whatsapp me-1 text-success"></i><?= htmlspecialchars($row['no_hp']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-end fw-bold text-dark">
                                        Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <?php 
                                        switch ($status_raw) {
                                            case 'proses':
                                                echo '<span class="badge bg-primary px-3 py-2 rounded-pill"><i class="fas fa-spinner fa-spin me-1"></i> Diproses</span>';
                                                break;
                                            case 'selesai':
                                                echo '<span class="badge bg-success px-3 py-2 rounded-pill"><i class="fas fa-check-double me-1"></i> Selesai</span>';
                                                break;
                                            case 'dibatalkan':
                                                echo '<span class="badge bg-danger px-3 py-2 rounded-pill"><i class="fas fa-times-circle me-1"></i> Dibatalkan</span>';
                                                break;
                                            case 'pending':
                                            default:
                                                echo '<span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="fas fa-hourglass-half me-1"></i> Pending</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 bg-white text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 text-light"></i>
                                        <h5 class="fw-bold text-secondary">Belum Ada Riwayat Transaksi</h5>
                                        <p class="small text-muted mb-0">Pesanan yang Anda buat melalui fitur input instan akan muncul di sini.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-4 bg-white mt-5 border-top text-center">
        <div class="container">
            <small class="text-muted">PT Rahayu Karunia Utama &copy; Rahayu</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>