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

// Query untuk mengambil riwayat order. Menggabungkan tabel orders, order_detail, dan produk
$query_riwayat = "SELECT o.id_order, o.nama_pelanggan, o.tgl_pesan, o.total_bayar, o.status_order, o.bukti_transfer,
                         IFNULL(SUM(od.jumlah), 0) as jumlah_item,
                         GROUP_CONCAT(p.nama_produk SEPARATOR ', ') as nama_produk_list
                  FROM orders o
                  LEFT JOIN order_detail od ON o.id_order = od.id_order
                  LEFT JOIN produk p ON od.id_produk = p.id_produk
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
    <?php include 'pwa_meta.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">


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

        footer {
            background-color: var(--accent-indigo) !important;
            color: var(--bg-lavender) !important;
        }

        footer hr {
            background-color: var(--accent-gray);
            opacity: 0.3;
        }

        /* Style untuk footer contact links */
        .contact-link {
            color: var(--bg-lavender);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .contact-link:hover {
            transform: translateX(5px);
        }

        .contact-link-wa:hover {
            color: #25D366 !important;
        }

        .contact-link-ig:hover {
            color: #E4405F !important;
        }

        .contact-link-email:hover {
            color: #D44638 !important;
        }

        /* Custom SweetAlert Button Styling */
        .swal2-confirm.swal2-styled {
            border-radius: 20px !important;
            font-weight: bold !important;
            padding: 10px 24px !important;
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
                            <th width="12%">ID Order</th>
                            <th width="15%">Tanggal Transaksi</th>
                            <th width="15%">Nama Pelanggan</th>
                            <th width="15%">Nama Produk</th>
                            <th class="text-center" width="10%">Jumlah Item</th>
                            <th class="text-end" width="12%">Total Bayar</th>
                            <th class="text-center" width="8%">Status</th>
                            <th class="text-center" width="8%">Bukti Transfer</th>
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
                                    <td class="text-muted small">
                                        <?= htmlspecialchars($row['nama_produk_list'] ?? '-'); ?>
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
                                    <td class="text-center">
                                        <?php if ($status == 'dibatalkan'): ?>
                                            <span class="text-muted small">-</span>
                                        <?php else: ?>
                                            <?php if (empty($row['bukti_transfer'])): ?>
                                                <button class="btn btn-sm btn-theme rounded-pill px-3 shadow-sm fw-bold" style="background-color: var(--accent-plum); font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#modalBukti<?= $row['id_order']; ?>">
                                                    <i class="fas fa-upload me-1"></i> Upload
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-bold" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#modalBukti<?= $row['id_order']; ?>">
                                                    <i class="fas fa-image me-1"></i> Lihat Bukti
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
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

    <?php if ($result_riwayat && $result_riwayat->num_rows > 0): ?>
        <?php
        $result_riwayat->data_seek(0);
        while ($row = $result_riwayat->fetch_assoc()):
            $status = strtolower($row['status_order']);
            if ($status != 'dibatalkan'):
        ?>
                <div class="modal fade" id="modalBukti<?= $row['id_order']; ?>" tabindex="-1" aria-labelledby="modalBuktiLabel<?= $row['id_order']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg border-0" style="border-radius: 15px; overflow: hidden;">
                            <div class="modal-header border-0 text-white" style="background: linear-gradient(135deg, var(--accent-indigo) 0%, var(--accent-plum) 100%);">
                                <h5 class="modal-title fw-bold" id="modalBuktiLabel<?= $row['id_order']; ?>">
                                    <i class="fas fa-receipt text-warning me-2"></i>Bukti Transfer #<?= htmlspecialchars($row['id_order']); ?>
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="proses_upload_bukti.php" method="POST" enctype="multipart/form-data">
                                <div class="modal-body p-4 bg-white text-dark">
                                    <input type="hidden" name="id_order" value="<?= htmlspecialchars($row['id_order']); ?>">

                                    <?php if (!empty($row['bukti_transfer'])): ?>
                                        <div class="text-center mb-3">
                                            <span class="d-block small text-muted mb-2">Bukti Transfer Saat Ini:</span>
                                            <div class="p-2 border rounded-3 bg-light d-inline-block">
                                                <img src="assets/img/bukti_transfer/<?= htmlspecialchars($row['bukti_transfer']); ?>" alt="Bukti Transfer" class="img-fluid rounded shadow-sm" style="max-height: 250px; object-fit: contain;">
                                            </div>
                                        </div>
                                        <hr class="my-3 opacity-25">
                                    <?php endif; ?>

                                    <div class="mb-3 text-start">
                                        <label class="form-label fw-bold text-dark mb-1">
                                            <?= !empty($row['bukti_transfer']) ? 'Unggah / Ganti Bukti Transfer Baru' : 'Unggah Bukti Transfer' ?>
                                        </label>
                                        <input type="file" name="bukti_transfer" class="form-control border-secondary-subtle" accept="image/*" required>
                                        <div class="form-text small text-muted mt-2">
                                            <i class="fas fa-info-circle me-1"></i> Format yang diperbolehkan: <strong>PNG, JPG, JPEG, WEBP</strong>. Maksimal <strong>5MB</strong>.
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light p-3 border-0 d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-theme rounded-pill px-4">
                                        <i class="fas fa-paper-plane me-1"></i> Unggah Bukti
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
        <?php
            endif;
        endwhile;
        ?>
    <?php endif; ?>

    <footer class="py-5">
        <div class="container">
            <div class="row">
                <div id="about" class="col-md-6 mb-4 mb-md-0">
                    <h5 class="fw-bold mb-3 text-white"><i class="bi bi-shop me-2" style="color: var(--accent-gray);"></i>PT Rahayu Karunia Utama</h5>
                    <p class="small" style="line-height: 1.8; color: var(--bg-lavender);">Produsen perlengkapan inner wanita berkualitas yang mengedepankan kenyamanan dan estetika bagi setiap pelanggan kami. Karya terbaik dari penjahit lokal untuk menemani hari-hari Anda.</p>
                </div>
                <div id="contact" class="col-md-6 text-md-end">
                    <h5 class="fw-bold mb-3 text-white">Hubungi Kami</h5>
                    <p class="small" style="line-height: 1.8; color: var(--bg-lavender);">
                        <i class="bi bi-geo-alt-fill me-2" style="color: var(--accent-gray);"></i>Kota Depok<br>
                        <a href="https://wa.me/6289696611750" target="_blank" class="contact-link contact-link-wa">
                            <i class="bi bi-whatsapp me-2" style="color: var(--accent-gray);"></i>+62 896-9661-1750
                        </a><br>
                        <a href="https://www.instagram.com/rahayuofficialstore.id" target="_blank" class="contact-link contact-link-ig">
                            <i class="bi bi-instagram me-2" style="color: var(--accent-gray);"></i>rahayuofficialstore.id
                        </a><br>
                        <a href="mailto:Rahayuofficialstore.id@gmail.com" class="contact-link contact-link-email">
                            <i class="bi bi-envelope-fill me-2" style="color: var(--accent-gray);"></i>Rahayuofficialstore.id@gmail.com
                        </a><br>
                    </p>
                </div>
            </div>
            <hr class="mt-4 mb-3">
            <p class="m-0 text-center small opacity-75 text-white">&copy; <?php echo date('Y'); ?> E-Catalogue Toko Rahayu. All Rights Reserved.</p>
        </div>
    </footer>

    <div style="height: 50px;"></div>

    <?php include 'modal_notifikasi.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>