<?php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>E-Catalogue - PT Rahayu Karunia Utama</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        html { scroll-behavior: smooth; }
        :root {
            --pastel-purple: #E0D2F0;
            --soft-cream: #FCF8F1;
            --olive-green: #7D8F37;
            --dark-olive: #5A6926;
            --text-dark: #4A4036;
        }
        body { background-color: var(--soft-cream); color: var(--text-dark); }
        .navbar.bg-light { background-color: var(--pastel-purple) !important; border-bottom: none; }
        .navbar-brand strong { color: var(--text-dark); }
        .navbar-brand .text-primary { color: var(--olive-green) !important; }
        .hero-section {
            background: linear-gradient(rgba(252, 248, 241, 0.6), rgba(224, 210, 240, 0.4)), url('https://images.unsplash.com/photo-1520006403909-838d6b92c22e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover; background-position: center; padding: 100px 0; color: var(--text-dark); 
        }
        .btn-primary { background-color: var(--olive-green) !important; border-color: var(--olive-green) !important; color: white !important; }
        .btn-primary:hover { background-color: var(--dark-olive) !important; border-color: var(--dark-olive) !important; }
        .search-bar .card { background-color: white; border: 1px solid var(--pastel-purple) !important; }
        .card { background-color: white; }
        .text-primary { color: var(--olive-green) !important; }
        .btn-outline-primary { color: var(--olive-green); border-color: var(--olive-green); }
        .btn-outline-primary:hover { background-color: var(--olive-green); border-color: var(--olive-green); color: white; }
        .badge.bg-danger { background-color: var(--olive-green) !important; }
        footer.bg-dark { background-color: var(--pastel-purple) !important; }
        footer h5, footer p, footer .text-white, footer .text-muted { color: var(--text-dark) !important; }
        footer hr { background-color: var(--olive-green); opacity: 0.2; }
        .navbar-brand img { height: 40px; }
        .btn-marketplace { font-size: 0.85rem; padding: 5px 10px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm sticky-top">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="#home">
                <strong>RAHAYU</strong> <span class="text-primary">CATALOGUE</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#populer">🔥 Terpopuler</a></li>
                    <li class="nav-item"><a class="nav-link" href="katalog.php">Katalog Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="katalog_promo.php">Promo & Diskon</a></li>
                    <li class="nav-item"><a class="nav-link" href="bandingkan.php">Bandingkan</a></li>
                    <li class="nav-item"><a class="nav-link" href="lacak_pesanan.php">Lacak Pesanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
                <div class="d-flex">
                    <a href="login.php" class="btn btn-outline-dark">
                        <i class="bi-person-fill me-1"></i> Admin Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <header id="home" class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bolder">Inner Wanita Berkualitas</h1>
            <p class="lead fw-normal text-white-50 mb-4">Temukan kenyamanan terbaik untuk aktivitas harian Anda</p>
            <a href="katalog.php" class="btn btn-primary btn-lg px-5 py-3 rounded-pill fw-bold">Lihat Katalog</a>
        </div>
    </header>

    <div class="container search-bar mb-5">
        <div class="card shadow border-0">
            <div class="card-body p-3">
                <form action="index.php#populer" method="GET" class="row g-2">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control form-control-lg border-0" placeholder="Cari produk inner (misal: Manset, Ciput)..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-lg w-100">Cari</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <section class="py-3 mb-5" id="populer">
        <div class="container px-4 px-lg-5">
            <div class="row mb-4">
                <div class="col">
                    <h3 class="fw-bold"><i class="bi bi-fire text-danger me-2"></i>
                        <?php echo isset($_GET['search']) && $_GET['search'] != '' ? 'Hasil Pencarian Produk' : 'Produk Terpopuler Minggu Ini'; ?>
                    </h3>
                    <p class="text-muted small">Daftar produk dengan minat pencarian dan klik tertinggi dari pelanggan.</p>
                </div>
            </div>
            <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-5 justify-content-center">
                <?php
                $today = date('Y-m-d');

                if (isset($_GET['search']) && $_GET['search'] != '') {
                    $search = mysqli_real_escape_string($koneksi, $_GET['search']);
                    $populer_query = "SELECT p.*, k.nama_kategori, pr.diskon_persen 
                                      FROM produk p 
                                      LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                                      LEFT JOIN promo pr ON p.id_produk = pr.id_produk AND '$today' BETWEEN pr.tgl_mulai AND pr.tgl_selesai
                                      WHERE p.nama_produk LIKE '%$search%' OR p.deskripsi LIKE '%$search%'
                                      ORDER BY p.jumlah_klik DESC LIMIT 10";
                } else {
                    $populer_query = "SELECT p.*, k.nama_kategori, pr.diskon_persen 
                                      FROM produk p 
                                      LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
                                      LEFT JOIN promo pr ON p.id_produk = pr.id_produk AND '$today' BETWEEN pr.tgl_mulai AND pr.tgl_selesai
                                      ORDER BY p.jumlah_klik DESC LIMIT 10";
                }
                
                $populer_result = $koneksi->query($populer_query);

                if ($populer_result->num_rows > 0) {
                    while($p_row = $populer_result->fetch_assoc()) {
                        $has_promo = isset($p_row['diskon_persen']) && $p_row['diskon_persen'] > 0;
                ?>
                <div class="col mb-4">
                    <div class="card h-100 shadow-sm border-0 position-relative">
                        <div class="position-absolute top-0 start-0 m-2 z-index-2">
                            <span class="badge bg-dark opacity-75 small">
                                <i class="bi bi-eye-fill me-1"></i> <?php echo $p_row['jumlah_klik']; ?> Klik
                            </span>
                        </div>
                        
                        <?php if ($has_promo): ?>
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-danger text-white fw-bold px-2 py-1 rounded">
                                -<?php echo $p_row['diskon_persen']; ?>%
                            </span>
                        </div>
                        <?php endif; ?>

                        <img class="card-img-top" src="assets/img/<?php echo $p_row['foto'] ? $p_row['foto'] : 'no-image.jpg'; ?>" alt="Foto Produk" />
                        <div class="card-body p-3 text-center">
                            <small class="text-muted d-block"><?php echo htmlspecialchars($p_row['nama_kategori'] ?? 'Tanpa Kategori'); ?></small>
                            <h6 class="fw-bold text-truncate mb-1"><?php echo htmlspecialchars($p_row['nama_produk']); ?></h6>
                            
                            <div class="product-price">
                                <?php if ($has_promo): 
                                    $harga_akhir = $p_row['harga'] - ($p_row['harga'] * ($p_row['diskon_persen'] / 100));
                                ?>
                                    <span class="text-muted text-decoration-line-through small d-block">Rp <?php echo number_format($p_row['harga'], 0, ',', '.'); ?></span>
                                    <span class="text-danger fw-semibold fs-6">Rp <?php echo number_format($harga_akhir, 0, ',', '.'); ?></span>
                                <?php else: ?>
                                    <span class="text-primary fw-semibold fs-6">Rp <?php echo number_format($p_row['harga'], 0, ',', '.'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer p-3 pt-0 border-top-0 bg-transparent">
                            <button class="btn btn-outline-primary btn-sm w-100 btn-lihat-detail" data-id="<?php echo $p_row['id_produk']; ?>">Detail Produk</button>
                        </div>
                    </div>
                </div>
                <?php 
                    }
                } else {
                    echo "<div class='col-12 text-center py-5'><p class='text-muted'>Produk yang Anda cari tidak ditemukan.</p></div>";
                }
                ?>
            </div>
        </div>
    </section>

    <footer class="py-5 bg-dark">
        <div class="container">
            <div class="row text-white">
                <div id="about" class="col-md-6 mb-4 mb-md-0">
                    <h5>PT Rahayu Karunia Utama</h5>
                    <p class="small text-muted">Produsen perlengkapan inner wanita berkualitas yang mengedepankan kenyamanan dan estetika bagi setiap pelanggan kami.</p>
                </div>
                <div id="contact" class="col-md-6 text-md-end">
                    <h5>Hubungi Kami</h5>
                    <p class="small text-muted">Jl. Raya Industri No. 123, Jakarta<br>Email: info@rahayu.com<br>WhatsApp: +62 812-3456-7890</p>
                </div>
            </div>
            <hr class="bg-secondary">
            <p class="m-0 text-center text-white">PT Rahayu Karunia Utama &copy; Rahayu</p>
        </div>
    </footer>

    <div class="modal fade" id="modalDetailProduk" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="modalDetailLabel">Detail Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="isiKontenModal">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted small mt-2">Sedang memuat data produk...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modalDetail = new bootstrap.Modal(document.getElementById('modalDetailProduk'));
            const kontainerIsi = document.getElementById('isiKontenModal');

            document.querySelectorAll('.btn-lihat-detail').forEach(button => {
                button.addEventListener('click', function() {
                    const idProduk = this.getAttribute('data-id');
                    
                    kontainerIsi.innerHTML = `
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="text-muted small mt-2">Sedang memuat data produk...</p>
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
                                    <i class="bi bi-exclamation-triangle display-4"></i>
                                    <p class="mt-2">Gagal memuat data produk. Coba lagi nanti.</p>
                                </div>`;
                        });
                });
            });
        });
    </script>
</body>
</html>