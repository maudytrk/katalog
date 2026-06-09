<?php
include 'koneksi.php';
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>E-Catalogue - PT Rahayu Karunia Utama</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        html {
            scroll-behavior: smooth;
        }

        :root {
            --bg-lavender: #E0E1F6;
            --accent-indigo: #241F48;
            --accent-plum: #6C4773;
            --accent-gray: #B0B7CA;
            --soft-cream: #F8F9FA;
        }

        body {
            background-color: var(--soft-cream);
            color: var(--accent-indigo);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Hero Section dengan Gradient Animasi Halus */
        .hero-section {
            background: linear-gradient(rgba(36, 31, 72, 0.75), rgba(108, 71, 115, 0.65)), url('https://images.unsplash.com/photo-1520006403909-838d6b92c22e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            /* Parallax Effect */
            padding: 140px 0 100px 0;
            color: white;
            margin-top: -85px;
            /* Menarik hero ke atas agar navbar glassmorphism terlihat menumpuk */
        }

        .btn-primary {
            background-color: var(--accent-indigo) !important;
            border-color: var(--accent-indigo) !important;
            color: white !important;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--accent-plum) !important;
            border-color: var(--accent-plum) !important;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(108, 71, 115, 0.4);
        }

        .search-bar .card {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8) !important;
            border-radius: 50rem;
            box-shadow: 0 15px 35px rgba(36, 31, 72, 0.1) !important;
        }

        /* Card Produk Interaktif */
        .product-card {
            background-color: white;
            border-radius: 15px;
            border: 1px solid #eee;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(36, 31, 72, 0.15) !important;
            border-color: var(--bg-lavender);
        }

        .product-card img {
            transition: transform 0.5s ease;
        }

        .product-card:hover img {
            transform: scale(1.05);
        }

        .btn-outline-primary {
            color: var(--accent-indigo);
            border: 2px solid var(--accent-indigo);
            font-weight: 600;
        }

        .btn-outline-primary:hover {
            background-color: var(--accent-indigo);
            border-color: var(--accent-indigo);
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
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <header id="home" class="hero-section text-center">
        <div class="container position-relative" style="z-index: 2;">
            <span class="badge bg-light text-dark px-3 py-2 rounded-pill mb-3 shadow-sm fw-bold" style="letter-spacing: 2px;">EST. 2011</span>
            <h1 class="display-4 fw-bolder mb-3 shadow-sm" style="text-shadow: 2px 2px 8px rgba(0,0,0,0.5);">Toko Rahayu</h1>
            <p class="lead fw-normal mb-5" style="color: var(--bg-lavender); text-shadow: 1px 1px 4px rgba(0,0,0,0.5);">Temukan sentuhan kenyamanan sempurna untuk aktivitas harian Anda</p>
            <a href="katalog.php" class="btn btn-primary btn-lg px-5 py-3 rounded-pill fw-bold"><i class="bi bi-cart3 me-2"></i>Jelajahi Katalog</a>
        </div>
    </header>

    <div class="container search-bar mb-5" style="margin-top: -45px; position: relative; z-index: 10;">
        <div class="card border-0">
            <div class="card-body p-2">
                <form action="index.php#populer" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-10 ps-4">
                        <input type="text" name="search" class="form-control form-control-lg border-0 shadow-none bg-transparent" placeholder="Cari ciput, manset, atau legging..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill"><i class="bi bi-search me-2"></i>Cari</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (empty($_GET['search'])): ?>

        <section class="py-4 bg-transparent mb-4">
            <div class="container px-4 px-lg-5">
                <div class="row align-items-center shadow-sm rounded-4 p-4 p-md-5" style="background-color: white; border: 1px solid var(--bg-lavender);">
                    <div class="col-lg-7 mb-4 mb-lg-0">
                        <span class="badge px-3 py-2 rounded-pill mb-3" style="background-color: var(--accent-plum); color: white;">Tentang Kami</span>
                        <h2 class="fw-bold mb-3" style="color: var(--accent-indigo);">PT Rahayu Karunia Utama</h2>
                        <p class="text-muted mb-4" style="line-height: 1.8;">Kami adalah produsen perlengkapan inner wanita berkualitas yang telah berdiri sejak tahun 2011. Dengan dedikasi tinggi, kami memproduksi ciput, manset, dan legging yang mengedepankan kenyamanan, kerapihan, dan estetika. Produk kami dijahit langsung oleh pengrajin lokal berpengalaman untuk menemani aktivitas harian Anda.</p>
                        <a href="katalog.php" class="btn btn-outline-primary rounded-pill px-4 fw-bold">Jelajahi Koleksi</a>
                    </div>
                    <div class="col-lg-5 text-center">
                        <img src="assets/img/logorahayu.png" alt="PT Rahayu" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
                    </div>
                </div>
            </div>
        </section>

        <?php
        $promo_query = "SELECT p.*, GROUP_CONCAT(k.nama_kategori SEPARATOR ', ') as daftar_kategori, pr.diskon_persen 
                        FROM produk p 
                        LEFT JOIN produk_kategori pk ON p.id_produk = pk.id_produk
                        LEFT JOIN kategori k ON pk.id_kategori = k.id_kategori 
                        JOIN promo pr ON p.id_produk = pr.id_produk AND '$today' BETWEEN pr.tgl_mulai AND pr.tgl_selesai
                        WHERE pr.diskon_persen > 0
                        GROUP BY p.id_produk
                        ORDER BY p.id_produk DESC LIMIT 4";
        $promo_result = $koneksi->query($promo_query);

        if ($promo_result && $promo_result->num_rows > 0):
        ?>
            <section class="py-5" style="background-color: rgba(108, 71, 115, 0.04);">
                <div class="container px-4 px-lg-5">
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <h3 class="fw-bold m-0" style="color: var(--accent-plum);"><i class="bi bi-tags-fill text-danger me-2"></i>Promo Spesial</h3>
                            <div class="mt-2" style="width: 60px; height: 4px; background-color: var(--accent-indigo); border-radius: 5px;"></div>
                        </div>
                        <a href="katalog.php" class="text-decoration-none fw-bold" style="color: var(--accent-indigo);">Lihat Semua Promo <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                        <?php while ($p_row = $promo_result->fetch_assoc()):
                            $has_promo = true;
                            $harga_akhir = $p_row['harga'] - ($p_row['harga'] * ($p_row['diskon_persen'] / 100));
                        ?>
                            <div class="col mb-5">
                                <div class="card h-100 product-card position-relative">
                                    <div class="position-absolute top-0 end-0 m-2" style="z-index: 2;">
                                        <span class="badge bg-danger text-white fw-bold px-2 py-1 rounded shadow-sm">
                                            -<?php echo $p_row['diskon_persen']; ?>%
                                        </span>
                                    </div>
                                    <div style="overflow: hidden; height: 220px; background-color: var(--bg-lavender);">
                                        <img class="card-img-top w-100 h-100" style="object-fit: cover;" src="assets/img/<?php echo !empty($p_row['foto']) ? htmlspecialchars($p_row['foto']) : 'no-image.jpg'; ?>" alt="Foto Produk" />
                                    </div>
                                    <div class="card-body p-3 text-center d-flex flex-column">
                                        <small class="d-block mb-2 fw-bold text-uppercase" style="color: var(--accent-plum); letter-spacing: 1px; font-size: 0.7rem;"><?php echo htmlspecialchars($p_row['daftar_kategori'] ?? 'Tanpa Kategori'); ?></small>
                                        <h6 class="fw-bold text-truncate mb-auto" style="color: var(--accent-indigo);"><?php echo htmlspecialchars($p_row['nama_produk']); ?></h6>
                                        <div class="product-price mt-3 p-2 rounded" style="background-color: var(--soft-cream); border: 1px dashed var(--accent-gray);">
                                            <span class="text-muted text-decoration-line-through small d-block">Rp <?php echo number_format($p_row['harga'], 0, ',', '.'); ?></span>
                                            <span class="fw-bold fs-6" style="color: #D32F2F;">Rp <?php echo number_format($harga_akhir, 0, ',', '.'); ?></span>
                                        </div>
                                    </div>
                                    <div class="card-footer p-3 pt-0 border-top-0 bg-transparent">
                                        <button class="btn btn-outline-primary btn-sm w-100 rounded-pill btn-lihat-detail py-2" data-id="<?php echo $p_row['id_produk']; ?>">Lihat Spesifikasi</button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php
        $terbaru_query = "SELECT p.*, GROUP_CONCAT(k.nama_kategori SEPARATOR ', ') as daftar_kategori, pr.diskon_persen 
                          FROM produk p 
                          LEFT JOIN produk_kategori pk ON p.id_produk = pk.id_produk
                          LEFT JOIN kategori k ON pk.id_kategori = k.id_kategori 
                          LEFT JOIN promo pr ON p.id_produk = pr.id_produk AND '$today' BETWEEN pr.tgl_mulai AND pr.tgl_selesai
                          GROUP BY p.id_produk
                          ORDER BY p.id_produk DESC LIMIT 4";
        $terbaru_result = $koneksi->query($terbaru_query);

        if ($terbaru_result && $terbaru_result->num_rows > 0):
        ?>
            <section class="py-5 bg-white">
                <div class="container px-4 px-lg-5">
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <h3 class="fw-bold m-0" style="color: var(--accent-indigo);"><i class="bi bi-box-seam text-primary me-2"></i>Produk Terbaru</h3>
                            <div class="mt-2" style="width: 60px; height: 4px; background-color: var(--accent-plum); border-radius: 5px;"></div>
                        </div>
                        <a href="katalog.php" class="text-decoration-none fw-bold" style="color: var(--accent-plum);">Lihat Katalog <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                        <?php while ($p_row = $terbaru_result->fetch_assoc()):
                            $has_promo = isset($p_row['diskon_persen']) && $p_row['diskon_persen'] > 0;
                        ?>
                            <div class="col mb-5">
                                <div class="card h-100 product-card position-relative">
                                    <div class="position-absolute top-0 start-0 m-2" style="z-index: 2;">
                                        <span class="badge shadow-sm small" style="background-color: rgba(36, 31, 72, 0.85); backdrop-filter: blur(5px);">
                                            Baru
                                        </span>
                                    </div>
                                    <?php if ($has_promo): ?>
                                        <div class="position-absolute top-0 end-0 m-2" style="z-index: 2;">
                                            <span class="badge bg-danger text-white fw-bold px-2 py-1 rounded shadow-sm">
                                                -<?php echo $p_row['diskon_persen']; ?>%
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <div style="overflow: hidden; height: 220px; background-color: var(--bg-lavender);">
                                        <img class="card-img-top w-100 h-100" style="object-fit: cover;" src="assets/img/<?php echo !empty($p_row['foto']) ? htmlspecialchars($p_row['foto']) : 'no-image.jpg'; ?>" alt="Foto Produk" />
                                    </div>
                                    <div class="card-body p-3 text-center d-flex flex-column">
                                        <small class="d-block mb-2 fw-bold text-uppercase" style="color: var(--accent-plum); letter-spacing: 1px; font-size: 0.7rem;"><?php echo htmlspecialchars($p_row['daftar_kategori'] ?? 'Tanpa Kategori'); ?></small>
                                        <h6 class="fw-bold text-truncate mb-auto" style="color: var(--accent-indigo);"><?php echo htmlspecialchars($p_row['nama_produk']); ?></h6>
                                        <div class="product-price mt-3 p-2 rounded" style="background-color: var(--soft-cream); border: 1px dashed var(--accent-gray);">
                                            <?php if ($has_promo):
                                                $harga_akhir = $p_row['harga'] - ($p_row['harga'] * ($p_row['diskon_persen'] / 100));
                                            ?>
                                                <span class="text-muted text-decoration-line-through small d-block">Rp <?php echo number_format($p_row['harga'], 0, ',', '.'); ?></span>
                                                <span class="fw-bold fs-6" style="color: #D32F2F;">Rp <?php echo number_format($harga_akhir, 0, ',', '.'); ?></span>
                                            <?php else: ?>
                                                <span class="fw-bold fs-6" style="color: var(--accent-indigo);">Rp <?php echo number_format($p_row['harga'], 0, ',', '.'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer p-3 pt-0 border-top-0 bg-transparent">
                                        <button class="btn btn-outline-primary btn-sm w-100 rounded-pill btn-lihat-detail py-2" data-id="<?php echo $p_row['id_produk']; ?>">Lihat Spesifikasi</button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

    <?php endif; ?>

    <section class="py-4 mb-5" id="populer">
        <div class="container px-4 px-lg-5">
            <div class="row mb-5 justify-content-center text-center">
                <div class="col-lg-8">
                    <h3 class="fw-bold" style="color: var(--accent-indigo);"><i class="bi bi-stars text-warning me-2"></i>
                        <?php echo isset($_GET['search']) && $_GET['search'] != '' ? 'Hasil Pencarian Produk' : 'Produk Terpopuler Minggu Ini'; ?>
                    </h3>
                    <div class="mx-auto mt-3" style="width: 60px; height: 4px; background-color: var(--accent-plum); border-radius: 5px;"></div>
                    <p class="text-muted mt-3">Koleksi pilihan dengan minat pencarian tertinggi dari pelanggan kami.</p>
                </div>
            </div>

            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-5 justify-content-center">
                <?php
                $today = date('Y-m-d');

                if (isset($_GET['search']) && $_GET['search'] != '') {
                    $search = mysqli_real_escape_string($koneksi, $_GET['search']);
                    $populer_query = "SELECT p.*, GROUP_CONCAT(k.nama_kategori SEPARATOR ', ') as daftar_kategori, pr.diskon_persen 
                                      FROM produk p 
                                      LEFT JOIN produk_kategori pk ON p.id_produk = pk.id_produk
                                      LEFT JOIN kategori k ON pk.id_kategori = k.id_kategori 
                                      LEFT JOIN promo pr ON p.id_produk = pr.id_produk AND '$today' BETWEEN pr.tgl_mulai AND pr.tgl_selesai
                                      WHERE p.nama_produk LIKE '%$search%' OR p.deskripsi LIKE '%$search%'
                                      GROUP BY p.id_produk
                                      ORDER BY p.jumlah_klik DESC LIMIT 10";
                } else {
                    $populer_query = "SELECT p.*, GROUP_CONCAT(k.nama_kategori SEPARATOR ', ') as daftar_kategori, pr.diskon_persen 
                                      FROM produk p 
                                      LEFT JOIN produk_kategori pk ON p.id_produk = pk.id_produk
                                      LEFT JOIN kategori k ON pk.id_kategori = k.id_kategori 
                                      LEFT JOIN promo pr ON p.id_produk = pr.id_produk AND '$today' BETWEEN pr.tgl_mulai AND pr.tgl_selesai
                                      GROUP BY p.id_produk
                                      ORDER BY p.jumlah_klik DESC LIMIT 10";
                }

                $populer_result = $koneksi->query($populer_query);

                if ($populer_result->num_rows > 0) {
                    while ($p_row = $populer_result->fetch_assoc()) {
                        $has_promo = isset($p_row['diskon_persen']) && $p_row['diskon_persen'] > 0;
                ?>
                        <div class="col mb-5">
                            <div class="card h-100 product-card position-relative">
                                <div class="position-absolute top-0 start-0 m-2" style="z-index: 2;">
                                    <span class="badge shadow-sm small" style="background-color: rgba(36, 31, 72, 0.85); backdrop-filter: blur(5px);">
                                        <i class="bi bi-eye-fill me-1"></i> <?php echo $p_row['jumlah_klik']; ?>
                                    </span>
                                </div>

                                <?php if ($has_promo): ?>
                                    <div class="position-absolute top-0 end-0 m-2" style="z-index: 2;">
                                        <span class="badge bg-danger text-white fw-bold px-2 py-1 rounded shadow-sm">
                                            -<?php echo $p_row['diskon_persen']; ?>%
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <div style="overflow: hidden; height: 220px; background-color: var(--bg-lavender);">
                                    <img class="card-img-top w-100 h-100" style="object-fit: cover;" src="assets/img/<?php echo !empty($p_row['foto']) ? htmlspecialchars($p_row['foto']) : 'no-image.jpg'; ?>" alt="Foto Produk" />
                                </div>

                                <div class="card-body p-3 text-center d-flex flex-column">
                                    <small class="d-block mb-2 fw-bold text-uppercase" style="color: var(--accent-plum); letter-spacing: 1px; font-size: 0.7rem;"><?php echo htmlspecialchars($p_row['daftar_kategori'] ?? 'Tanpa Kategori'); ?></small>
                                    <h6 class="fw-bold text-truncate mb-auto" style="color: var(--accent-indigo);"><?php echo htmlspecialchars($p_row['nama_produk']); ?></h6>

                                    <div class="product-price mt-3 p-2 rounded" style="background-color: var(--soft-cream); border: 1px dashed var(--accent-gray);">
                                        <?php if ($has_promo):
                                            $harga_akhir = $p_row['harga'] - ($p_row['harga'] * ($p_row['diskon_persen'] / 100));
                                        ?>
                                            <span class="text-muted text-decoration-line-through small d-block">Rp <?php echo number_format($p_row['harga'], 0, ',', '.'); ?></span>
                                            <span class="fw-bold fs-6" style="color: #D32F2F;">Rp <?php echo number_format($harga_akhir, 0, ',', '.'); ?></span>
                                        <?php else: ?>
                                            <span class="fw-bold fs-6" style="color: var(--accent-indigo);">Rp <?php echo number_format($p_row['harga'], 0, ',', '.'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-footer p-3 pt-0 border-top-0 bg-transparent">
                                    <button class="btn btn-outline-primary btn-sm w-100 rounded-pill btn-lihat-detail py-2" data-id="<?php echo $p_row['id_produk']; ?>">Lihat Spesifikasi</button>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo "<div class='col-12 text-center py-5'><div class='p-5 rounded-4 shadow-sm' style='background-color: var(--bg-lavender);'><i class='bi bi-search display-4 text-muted mb-3'></i><p class='text-muted fw-bold'>Produk yang Anda cari tidak ditemukan.</p></div></div>";
                }
                ?>
            </div>
        </div>
    </section>

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
                        <i class="bi bi-envelope-fill me-2" style="color: var(--accent-gray);"></i>rahayuofficialstore.id@gmail.com<br>
                        <i class="bi bi-whatsapp me-2" style="color: var(--accent-gray);"></i>+62 812-3456-7890
                    </p>
                </div>
            </div>
            <hr class="mt-4 mb-3">
            <p class="m-0 text-center small opacity-75 text-white">&copy; <?php echo date('Y'); ?> E-Catalogue Toko Rahayu. All Rights Reserved.</p>
        </div>
    </footer>

    <div class="modal fade" id="modalDetailProduk" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0" style="background-color: var(--bg-lavender); border-radius: 1rem 1rem 0 0;">
                    <h5 class="modal-title fw-bold" style="color: var(--accent-indigo);" id="modalDetailLabel"><i class="bi bi-info-circle me-2"></i>Detail Spesifikasi Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="isiKontenModal">
                    <div class="text-center py-5">
                        <div class="spinner-border" style="color: var(--accent-plum);" role="status"></div>
                        <p class="small mt-3 fw-semibold" style="color: var(--accent-indigo);">Menyiapkan data katalog...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Logika Animasi Navbar saat Scroll
            const navbar = document.querySelector('.glass-navbar');
            if (navbar) {
                window.addEventListener('scroll', function() {
                    if (window.scrollY > 50) {
                        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                        navbar.style.boxShadow = '0 5px 20px rgba(0,0,0,0.1)';
                    } else {
                        navbar.style.background = 'rgba(224, 225, 246, 0.85)';
                        navbar.style.boxShadow = 'none';
                    }
                });
            }

            // Logika Load Detail Modal
            const modalDetail = new bootstrap.Modal(document.getElementById('modalDetailProduk'));
            const kontainerIsi = document.getElementById('isiKontenModal');

            document.querySelectorAll('.btn-lihat-detail').forEach(button => {
                button.addEventListener('click', function() {
                    const idProduk = this.getAttribute('data-id');

                    kontainerIsi.innerHTML = `
                        <div class="text-center py-5">
                            <div class="spinner-border" style="color: var(--accent-plum);" role="status"></div>
                            <p class="small mt-3 fw-semibold" style="color: var(--accent-indigo);">Menyiapkan data katalog...</p>
                        </div>`;

                    modalDetail.show();

                    fetch('detail.php?id=' + idProduk)
                        .then(response => response.text())
                        .then(htmlResponse => {
                            kontainerIsi.innerHTML = htmlResponse;
                        })
                        .catch(error => {
                            kontainerIsi.innerHTML = `
                                <div class="text-center py-4 text-danger">
                                    <i class="bi bi-wifi-off display-4 mb-3"></i>
                                    <h6 class="fw-bold">Koneksi Terputus</h6>
                                    <p class="small text-muted">Aplikasi dalam mode offline. Pastikan fitur ini tersimpan di cache.</p>
                                </div>`;
                        });
                });
            });
        });
    </script>
</body>

</html>