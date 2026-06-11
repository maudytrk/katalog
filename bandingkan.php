<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

$is_login  = isset($_SESSION['login']) && $_SESSION['login'] === true;
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
$is_sales  = ($is_login && $user_role === 'sales');

// Ambil ID produk dari parameter URL
$product_ids = [];

// 1. Tangkap dari link di katalog.php (format string: ?ids=1,2,3)
if (isset($_GET['ids']) && !empty($_GET['ids'])) {
    $raw_ids = explode(',', $_GET['ids']);
    foreach ($raw_ids as $id) {
        if ((int)$id > 0) $product_ids[] = (int)$id;
    }
}
// 2. Tangkap dari form submit di halaman ini sendiri (format array: ?produk[]=1&produk[]=2)
elseif (isset($_GET['produk']) && is_array($_GET['produk'])) {
    foreach ($_GET['produk'] as $id) {
        if ((int)$id > 0) $product_ids[] = (int)$id;
    }
}

// Hapus duplikat produk jika ada
$product_ids = array_unique($product_ids);

// Batasi kembali di sisi server maksimal 4 produk demi keamanan layout
$product_ids = array_slice($product_ids, 0, 4);
$total_produk = count($product_ids);

// Persiapan data untuk ditaruh di kolom tabel
$list_produk = [];

if ($total_produk >= 2) {
    // Mengubah array ID menjadi string terpisah koma untuk query SQL (e.g., "1,2,3")
    $ids_string = implode(',', $product_ids);

    $today = date('Y-m-d');

    // Ambil produk beserta foto utama dan promo
    $query = "SELECT p.*, k.nama_kategori, pr.diskon_persen,
              (SELECT nama_file FROM produk_foto WHERE id_produk = p.id_produk LIMIT 1) as foto_utama
              FROM produk p 
              LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
              LEFT JOIN promo pr ON p.id_produk = pr.id_produk AND '$today' BETWEEN pr.tgl_mulai AND pr.tgl_selesai
              WHERE p.id_produk IN ($ids_string)
              ORDER BY FIELD(p.id_produk, $ids_string)";

    $result = $koneksi->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Gunakan foto utama dari produk_foto jika ada, jika tidak gunakan kolom foto bawaan
            $row['foto'] = !empty($row['foto_utama']) ? $row['foto_utama'] : $row['foto'];
            $list_produk[] = $row;
        }
    }
}

// Mengambil semua daftar produk untuk opsi dropdown
$result_all = $koneksi->query("SELECT id_produk, nama_produk FROM produk ORDER BY nama_produk ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandingkan Produk Inner Wanita - Rahayu</title>
    <?php include 'pwa_meta.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* TEMA WARNA BERDASARKAN PALETTE (warna.jpeg) */
        :root {
            --old-heliotrope: #6B4773;
            --royal-fuchsia: #BB3F95;
            --lavender-mist: #E0E1F6;
            --space-cadet: #231F48;
            --tyrian-purple: #560A39;
            --bg-cream: #FCF8F1;

            /* Warna untuk tombol/stok */
            --accent-olive: #7D8F37;
            --accent-olive-hover: #65752b;
        }

        body {
            background-color: var(--bg-cream);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--space-cadet);
        }

        footer {
            background-color: var(--accent-indigo) !important;
            color: var(--bg-lavender) !important;
        }

        footer hr {
            background-color: var(--accent-gray);
            opacity: 0.3;
        }

        /* Navbar Custom */
        .navbar-admin-custom {
            background-color: var(--space-cadet) !important;
        }

        /* TABEL KOMPARASI (Responsive & Sticky Header) */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 12px;
            border: 1px solid var(--lavender-mist);
            box-shadow: 0 4px 15px rgba(35, 31, 72, 0.05);
            background-color: #ffffff;
        }

        .compare-table {
            margin-bottom: 0;
            white-space: nowrap;
            /* Mencegah tabel terlipat di HP */
        }

        .compare-table td,
        .compare-table th {
            vertical-align: middle;
            text-align: center;
            padding: 20px;
            border-color: #dcd1e8;
            min-width: 280px;
            /* Lebar minimum tiap kolom produk */
            background-color: #ffffff;
        }

        /* Kolom Pertama (Header Samping) yg menempel saat di-scroll */
        .compare-table .row-header {
            background-color: var(--lavender-mist) !important;
            color: var(--space-cadet);
            font-weight: 700;
            text-align: left;
            min-width: 180px;
            width: 15%;
            position: sticky;
            left: 0;
            z-index: 10;
            border-right: 2px solid #c8c9e3;
        }

        /* Styling Foto Produk (Dengan Border Royal Fuchsia) */
        .product-img-compare {
            height: 220px;
            width: 220px;
            object-fit: cover;
            border-radius: 8px;
            border: 3px solid var(--royal-fuchsia);
            padding: 3px;
            background-color: #fff;
            margin-bottom: 10px;
        }

        /* Format Teks Deskripsi agar bisa wrap ke bawah */
        .desc-text {
            white-space: pre-wrap;
            text-align: left;
            font-size: 0.9rem;
            color: #555;
            line-height: 1.6;
            min-width: 250px;
        }

        /* Komponen Tombol Warna Tema */
        .btn-olive {
            background-color: var(--accent-olive);
            color: white;
            border: none;
        }

        .btn-olive:hover,
        .btn-olive:focus {
            background-color: var(--accent-olive-hover);
            color: white;
        }

        .btn-outline-purple {
            color: var(--space-cadet);
            border-color: var(--space-cadet);
            background: transparent;
        }

        .btn-outline-purple:hover {
            background-color: var(--space-cadet);
            color: white;
        }

        .text-purple {
            color: var(--space-cadet) !important;
        }

        /* Badge Custom */
        .badge-kategori {
            background-color: var(--old-heliotrope);
            color: white;
            font-weight: 500;
        }

        .badge-diskon {
            background-color: var(--royal-fuchsia);
            color: white;
        }

        .text-harga-diskon {
            color: var(--tyrian-purple);
        }

        .badge-ready {
            background-color: #198754;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .badge-empty {
            background-color: #dc3545;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        /* Modal Customization */
        .modal-theme-header {
            background-color: var(--lavender-mist) !important;
            color: var(--space-cadet) !important;
            border-bottom: 2px solid #dcd1e8;
        }

        .modal-admin-header {
            background-color: var(--space-cadet) !important;
            color: white !important;
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
    </style>
</head>

<body>

    <?php if ($is_login && $user_role === 'admin') : ?>
        <nav class="navbar navbar-expand-lg navbar-dark navbar-admin-custom sticky-top shadow-sm py-3">
            <div class="container">
                <a class="navbar-brand fw-bold" href="#!"><i class="fas fa-user-shield text-warning me-2"></i>Bandingkan Mode: Admin</a>
                <div class="d-flex gap-2 ms-auto">
                    <a href="dashboard_admin.php" class="btn btn-olive fw-bold px-4 rounded-pill"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="btn btn-outline-light px-3 rounded-pill"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>
        </nav>
    <?php else : ?>
        <?php include 'navbar.php'; ?>
    <?php endif; ?>

    <script>
        // Memastikan navbar bawaan (jika ter-include) dipaksa menggunakan warna soft purple
        if (document.querySelector('.navbar:not(.navbar-admin-custom)')) {
            document.querySelector('.navbar').style.backgroundColor = '#E0D2F0';
        }
    </script>

    <div class="container py-5">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between mb-4">
            <div class="text-center text-md-start mb-3 mb-md-0">
                <h2 class="fw-bold text-purple mb-1"><i class="fas fa-balance-scale me-2"></i>Komparasi Spek Produk</h2>
                <p class="text-muted mb-0">Membandingkan aspek material, harga, dan ketersediaan stok produk secara bersisian.</p>
            </div>
            <a href="katalog.php" class="btn btn-outline-purple rounded-pill fw-medium px-4">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Katalog
            </a>
        </div>

        <?php if ($total_produk < 2): ?>
            <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-3">
                <div class="py-4">
                    <i class="fas fa-compress-alt fa-3x text-muted mb-3" style="color: var(--navbar-purple) !important;"></i>
                    <h4 class="text-purple fw-semibold">Produk Pembanding Kurang</h4>
                    <p class="text-muted mx-auto" style="max-width: 450px;">Silakan pilih minimal 2 atau maksimal 4 produk di halaman katalog terlebih dahulu untuk melihat visualisasi tabel perbandingan.</p>
                    <a href="katalog.php" class="btn btn-olive fw-bold rounded-pill px-4 mt-2">Pilih Produk Sekarang</a>
                </div>
            </div>
        <?php else: ?>

            <div class="table-responsive shadow-sm rounded-3">
                <table class="table table-bordered compare-table mb-0 text-center align-middle">
                    <tbody>
                        <tr>
                            <th class="row-header ps-4">Foto Produk</th>
                            <?php foreach ($list_produk as $prod): ?>
                                <td>
                                    <img src="assets/img/<?php echo $prod['foto'] ? $prod['foto'] : 'no-image.jpg'; ?>" class="product-img-compare shadow-sm img-fluid" alt="Foto">
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <th class="row-header ps-4">Nama Produk</th>
                            <?php foreach ($list_produk as $prod): ?>
                                <td>
                                    <span class="fw-bold text-purple d-block fs-5"><?php echo htmlspecialchars($prod['nama_produk']); ?></span>
                                    <small class="text-muted bg-light px-2 py-1 rounded d-inline-block mt-1">Kode: <?php echo htmlspecialchars($prod['kode_produk']); ?></small>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <th class="row-header ps-4">Kategori</th>
                            <?php foreach ($list_produk as $prod): ?>
                                <td>
                                    <span class="badge badge-kategori px-4 py-2 rounded-pill fs-7"><?php echo htmlspecialchars($prod['nama_kategori']); ?></span>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <th class="row-header ps-4">Deskripsi Produk</th>
                            <?php foreach ($list_produk as $prod): ?>
                                <td class="text-start px-3">
                                    <div class="desc-text">
                                        <?php echo htmlspecialchars($prod['deskripsi']); ?>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <th class="row-header ps-4">Harga Jual</th>
                            <?php foreach ($list_produk as $prod): ?>
                                <td>
                                    <?php if ($prod['diskon_persen'] > 0):
                                        $harga_akhir = $prod['harga'] - ($prod['harga'] * ($prod['diskon_persen'] / 100));
                                    ?>
                                        <del class="text-muted small d-block">Rp <?php echo number_format($prod['harga'], 0, ',', '.'); ?></del>
                                        <div class="text-harga-diskon fw-bold fs-4 d-inline-block mt-1">
                                            Rp <?php echo number_format($harga_akhir, 0, ',', '.'); ?>
                                        </div>
                                        <span class="badge badge-diskon fs-7 rounded-2 ms-1 align-top">-<?php echo $prod['diskon_persen']; ?>%</span>
                                    <?php else: ?>
                                        <div class="text-purple fw-bold fs-4">Rp <?php echo number_format($prod['harga'], 0, ',', '.'); ?></div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <th class="row-header ps-4">Stok Tersedia</th>
                            <?php foreach ($list_produk as $prod): ?>
                                <td>
                                    <?php if ($prod['stok'] <= 5 && $prod['stok'] > 0): ?>
                                        <span class="badge px-3 py-2" style="background-color: var(--tyrian-purple); color: white;"><i class="fas fa-exclamation-triangle me-1"></i> Tersisa <?php echo $prod['stok']; ?> Pcs</span>
                                    <?php elseif ($prod['stok'] > 5): ?>
                                        <span class="badge badge-ready"><i class="fas fa-check-circle me-1"></i> <?php echo $prod['stok']; ?> Pcs (Ready)</span>
                                    <?php else: ?>
                                        <span class="badge badge-empty"><i class="fas fa-times-circle me-1"></i> Stok Habis</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <th class="row-header ps-4 border-bottom-0">Aksi Lanjutan</th>
                            <?php foreach ($list_produk as $prod): ?>
                                <td class="border-bottom-0">
                                    <div class="d-grid gap-2 px-3">
                                        <button type="button" class="btn btn-sm btn-outline-purple fw-bold rounded-pill btn-lihat-detail-compare" data-id="<?php echo $prod['id_produk']; ?>">
                                            <i class="fas fa-info-circle me-1"></i> Detail Lengkap
                                        </button>

                                        <?php if (isset($_SESSION['login']) && $_SESSION['role'] === 'sales') : ?>
                                            <button class="btn btn-sm btn-olive fw-bold shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalOrderCepat<?php echo $prod['id_produk']; ?>">
                                                <i class="fas fa-bolt me-1"></i> Pesanan Cepat
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </div>

    <div class="modal fade" id="modalDetailCompare" tabindex="-1" aria-labelledby="modalDetailCompareLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 modal-theme-header">
                    <h5 class="modal-title fw-bold" id="modalDetailCompareLabel"><i class="fas fa-info-circle me-2"></i>Spesifikasi Lengkap Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="isiKontenModalCompare">
                    <div class="text-center py-4">
                        <div class="spinner-border text-purple" role="status"></div>
                        <p class="text-muted small mt-2">Memanggil detail produk...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['login']) && $_SESSION['role'] === 'sales' && $total_produk >= 2) : ?>
        <?php foreach ($list_produk as $prod) :
            $persen_diskon = (int)$prod['diskon_persen'];
            $harga_asli    = (float)$prod['harga'];
            $harga_final   = ($persen_diskon > 0) ? ($harga_asli - ($harga_asli * ($persen_diskon / 100))) : $harga_asli;
        ?>
            <div class="modal fade" id="modalOrderCepat<?php echo $prod['id_produk']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <form action="proses_order_cepat.php" method="POST">
                            <div class="modal-header modal-admin-header">
                                <h5 class="modal-title fw-bold"><i class="fas fa-cart-plus text-warning me-2"></i>Form Transaksi Cepat</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-start p-4">
                                <div class="alert py-2 mb-3" style="background-color: var(--lavender-mist); border-left: 4px solid var(--space-cadet);">
                                    <small class="d-block text-muted">Produk yang Dipilih:</small>
                                    <strong class="text-purple"><?php echo htmlspecialchars($prod['nama_produk']); ?></strong>
                                    <span class="badge badge-kategori ms-1"><?php echo htmlspecialchars($prod['kode_produk']); ?></span>
                                </div>

                                <input type="hidden" name="id_produk" value="<?php echo $prod['id_produk']; ?>">
                                <input type="hidden" name="harga_satuan" value="<?php echo $harga_final; ?>">
                                <input type="hidden" id="db-stok-<?php echo $prod['id_produk']; ?>" value="<?php echo $prod['stok']; ?>">

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-purple">Nama Pelanggan</label>
                                    <input type="text" name="nama_pelanggan" class="form-control" placeholder="Masukkan nama pembeli" required style="border-color: #eedffc;">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-purple">No. HP / WhatsApp</label>
                                    <input type="tel" name="no_hp" class="form-control" placeholder="08..." required style="border-color: #eedffc;">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold text-purple">Jumlah Beli</label>
                                        <input type="number" name="jumlah_beli" data-id="<?php echo $prod['id_produk']; ?>" class="form-control qty-input-field" min="1" value="1" required style="border-color: #eedffc;">
                                    </div>
                                    <div class="col-md-6 mb-3 d-flex align-items-end">
                                        <div class="w-100 p-2 border rounded text-center" style="background-color: var(--bg-cream); border-color: #eedffc !important;">
                                            <small class="text-muted d-block">Stok Gudang:</small>
                                            <span class="fw-bold text-purple"><?php echo $prod['stok']; ?> Pcs</span>
                                        </div>
                                    </div>
                                </div>
                                <div id="error-msg-<?php echo $prod['id_produk']; ?>" class="small fw-bold mt-2 text-center" style="display: none; color: var(--tyrian-purple);">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Stok Kurang / Habis!
                                </div>
                            </div>
                            <div class="modal-footer bg-light border-0">
                                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="submit_order_cepat" id="btn-submit-<?php echo $prod['id_produk']; ?>" class="btn btn-sm btn-olive px-4 fw-bold rounded-pill shadow-sm">Simpan Transaksi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($is_login && $user_role === 'admin') : ?>
        <footer class="py-5 mt-auto" style="background-color: var(--space-cadet) !important; color: #ffffff !important;">
            <div class="container">
                <div class="row">
                    <div id="about" class="col-md-6 mb-4 mb-md-0">
                        <h5 class="fw-bold mb-3 text-white"><i class="bi bi-shop me-2" style="color: var(--accent-gray);"></i>PT Rahayu Karunia Utama</h5>
                        <p class="small" style="line-height: 1.8; color: #ccc;">Produsen perlengkapan inner wanita berkualitas yang mengedepankan kenyamanan dan estetika bagi setiap pelanggan kami sejak tahun 2011.</p>
                    </div>
                    <div id="contact" class="col-md-6 text-md-end">
                        <h5 class="fw-bold mb-3 text-white">Hubungi Kami</h5>
                    <p class="small" style="line-height: 1.8; color: var(--bg-lavender);">
                        <i class="bi bi-geo-alt-fill me-2" style="color: var(--accent-gray);"></i>Kota Depok<br>
                        <a href="https://wa.me/6289696611750" target="_blank" class="contact-link contact-link-wa">
                            <i class="bi bi-whatsapp me-2" style="color: var(--accent-gray);"></i>+62 896-9661-1750
                        </a><br>
                        <a href="https://www.instagram.com/rahayuofficialstore" target="_blank" class="contact-link contact-link-ig">
                            <i class="bi bi-instagram me-2" style="color: var(--accent-gray);"></i>rahayuofficialstore
                        </a><br>
                        <a href="mailto:Rahayuofficialstore.id@gmail.com" class="contact-link contact-link-email">
                            <i class="bi bi-envelope-fill me-2" style="color: var(--accent-gray);"></i>Rahayuofficialstore.id@gmail.com
                        </a><br>
                    </p>
                    </div>
                </div>
                <hr class="mt-4 mb-3 border-secondary">
                <p class="m-0 text-center small opacity-75">&copy; <?php echo date('Y'); ?> E-Catalogue Toko Rahayu. All Rights Reserved.</p>
            </div>
        </footer>
    <?php else : ?>
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
                        <a href="https://www.instagram.com/rahayuofficialstore" target="_blank" class="contact-link contact-link-ig">
                            <i class="bi bi-instagram me-2" style="color: var(--accent-gray);"></i>rahayuofficialstore
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
    <?php endif; ?>
    <?php include 'modal_logout.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- MODAL DETAIL VIA FETCH AJAX ---
            const modalDetailCompare = new bootstrap.Modal(document.getElementById('modalDetailCompare'));
            const kontainerIsiCompare = document.getElementById('isiKontenModalCompare');

            document.querySelectorAll('.btn-lihat-detail-compare').forEach(button => {
                button.addEventListener('click', function() {
                    const idProduk = this.getAttribute('data-id');

                    // Set loading state menggunakan warna tema baru
                    kontainerIsiCompare.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-purple" role="status"></div>
                        <p class="text-muted small mt-2">Memanggil detail produk...</p>
                    </div>`;

                    modalDetailCompare.show();

                    // Get UI-HTML dari detail.php
                    fetch('detail.php?id=' + idProduk)
                        .then(response => response.text())
                        .then(htmlResponse => {
                            kontainerIsiCompare.innerHTML = htmlResponse;
                        })
                        .catch(error => {
                            kontainerIsiCompare.innerHTML = `
                            <div class="text-center py-4" style="color: var(--accent-terracotta);">
                                <i class="fas fa-exclamation-triangle display-4"></i>
                                <p class="mt-2 fw-bold">Gagal mengambil informasi produk. Silakan coba kembali.</p>
                            </div>`;
                        });
                });
            });

            // --- VALIDASI REAL-TIME STOK ---
            const qtyInputs = document.querySelectorAll('.qty-input-field');
            qtyInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const prodId = this.getAttribute('data-id');
                    const qtyValue = parseInt(this.value) || 0;
                    const dbStok = parseInt(document.getElementById('db-stok-' + prodId).value);
                    const btnSubmit = document.getElementById('btn-submit-' + prodId);
                    const errorMsg = document.getElementById('error-msg-' + prodId);

                    if (qtyValue > dbStok || qtyValue <= 0) {
                        btnSubmit.disabled = true;
                        errorMsg.style.display = 'block';
                    } else {
                        btnSubmit.disabled = false;
                        errorMsg.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>

</html>