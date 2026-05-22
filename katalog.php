<?php
// 1. Inisialisasi Session dan Koneksi Database
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

// Menentukan status login dan role user
$is_login  = isset($_SESSION['login']) && $_SESSION['login'] === true;
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
$is_sales  = ($is_login && $user_role === 'sales');

// 2. Logika Pencarian & Filter
$where_clause = "WHERE 1=1"; 

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_keyword = mysqli_real_escape_string($koneksi, trim($_GET['search']));
    $where_clause .= " AND (p.nama_produk LIKE '%$search_keyword%' OR p.deskripsi LIKE '%$search_keyword%' OR p.kode_produk LIKE '%$search_keyword%')";
}

if (isset($_GET['kategori']) && !empty($_GET['kategori'])) {
    $id_kategori = mysqli_real_escape_string($koneksi, $_GET['kategori']);
    $where_clause .= " AND p.id_kategori = '$id_kategori'";
}

// 3. Query Ambil Data Produk + Info Promo
$today = date('Y-m-d');
$sql_produk = "SELECT p.*, k.nama_kategori, pr.diskon_persen 
                FROM produk p 
                LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
                LEFT JOIN promo pr ON p.id_produk = pr.id_produk AND '$today' BETWEEN pr.tgl_mulai AND pr.tgl_selesai
                $where_clause 
                ORDER BY p.id_produk DESC";
$result_produk = $koneksi->query($sql_produk);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk - Rahayu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-cream: #FCF8F1;
            --navbar-purple: #E0D2F0;
            --dark-purple: #5A4375;
            --accent-olive: #7D8F37;
            --accent-olive-hover: #65752b;
            --soft-pink: #F7EFE5;
        }
        body {
            background-color: var(--bg-cream);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        /* Button Utilities */
        .btn-olive { background-color: var(--accent-olive); color: white; border: none; }
        .btn-olive:hover { background-color: var(--accent-olive-hover); color: white; }
        .btn-outline-olive { border: 2px solid var(--accent-olive); color: var(--accent-olive); background: transparent; }
        .btn-outline-olive:hover { background-color: var(--accent-olive); color: white; }
        
        /* Layout & Card Components */
        .hero-banner { background: linear-gradient(135deg, #f5eefd 0%, #fffbf5 100%); border-bottom: 3px solid var(--navbar-purple); }
        .product-card { border: none; transition: transform 0.2s ease, box-shadow 0.2s ease; background-color: #ffffff; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(90,67,117,0.1) !important; }
        .card-img-container { height: 250px; overflow: hidden; background-color: #f8f9fa; }
        .card-img-container img { object-fit: cover; width: 100%; height: 100%; }
        
        /* Custom Theme Adjustments */
        .bg-admin-navbar { background-color: var(--dark-purple) !important; }
        .badge-promo { background-color: #D9534F; }
        .border-olive { border-color: var(--accent-olive) !important; }
        .text-purple { color: var(--dark-purple); }
    </style>
</head>
<body>

    <?php if ($is_login && $user_role === 'admin') : ?>
        <nav class="navbar navbar-expand-lg navbar-dark bg-admin-navbar sticky-top shadow-sm py-3">
            <div class="container">
                <a class="navbar-brand fw-bold" href="#!"><i class="fas fa-user-shield text-warning me-2"></i>Katalog Mode: Admin</a>
                <div class="d-flex gap-2 ms-auto">
                    <a href="dashboard_admin.php" class="btn btn-light fw-bold px-4 rounded-pill text-purple"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="btn btn-outline-light px-3 rounded-pill"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>
        </nav>
    <?php else : ?>
        <?php include 'navbar.php'; ?>
    <?php endif; ?>

    <div class="hero-banner py-5 mb-4 text-center">
        <div class="container py-3">
            <span class="badge mb-2 text-dark px-3 py-2 rounded-pill" style="background-color: var(--navbar-purple)">Koleksi Kenyamanan Wanita</span>
            <h1 class="display-5 fw-bold text-dark">Premium Innerwear & Loungewear</h1>
        </div>
    </div>

    <div class="container mb-5">
        <div class="card shadow-sm p-4 border-0 rounded-3" style="background-color: #ffffff;">
            <form method="GET" action="katalog.php" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari produk..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>
                        <?php
                        $kat_res = $koneksi->query("SELECT * FROM kategori");
                        while ($k = $kat_res->fetch_assoc()) {
                            $selected = (isset($_GET['kategori']) && $_GET['kategori'] == $k['id_kategori']) ? 'selected' : '';
                            echo "<option value='".$k['id_kategori']."' $selected>".$k['nama_kategori']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-olive fw-bold"><i class="fas fa-filter me-2"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            
            <?php if ($result_produk->num_rows > 0) : ?>
                <?php while ($row = $result_produk->fetch_assoc()) : 
                    $harga_jual = $row['harga'];
                    if ($row['diskon_persen'] > 0) {
                        $harga_jual = $row['harga'] - ($row['harga'] * ($row['diskon_persen'] / 100));
                    }
                ?>
                    <div class="col">
                        <div class="card h-100 product-card shadow-sm rounded-3 overflow-hidden">
                            <div class="card-img-container position-relative">
                                <img src="assets/img/<?php echo $row['foto'] ? $row['foto'] : 'no-image.jpg'; ?>" class="card-img-top">
                                <?php if ($row['diskon_persen'] > 0) : ?>
                                    <span class="position-absolute top-0 start-0 badge-promo text-white px-3 py-1 m-2 rounded-pill small fw-bold">
                                        Promo <?php echo $row['diskon_persen']; ?>%
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="card-body d-flex flex-column p-3">
                                <span class="text-uppercase text-muted small mb-1"><?php echo $row['nama_kategori']; ?></span>
                                <h5 class="card-title text-dark fw-bold mb-2"><?php echo $row['nama_produk']; ?></h5>
                                
                                <div class="mb-3">
                                    <?php if ($row['diskon_persen'] > 0) : ?>
                                        <del class="text-muted small d-block">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></del>
                                        <span class="text-danger fw-bold fs-5">Rp <?php echo number_format($harga_jual, 0, ',', '.'); ?></span>
                                    <?php else : ?>
                                        <span class="text-dark fw-bold fs-5">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input compare-checkbox" type="checkbox" value="<?php echo $row['id_produk']; ?>" id="comp-<?php echo $row['id_produk']; ?>">
                                    <label class="form-check-label small text-muted" for="comp-<?php echo $row['id_produk']; ?>">Bandingkan</label>
                                </div>

                                <div class="d-grid mt-auto">
                                    <?php if ($is_sales) : ?>
                                        <button type="button" 
                                                class="btn btn-olive fw-bold mb-2 btn-trigger-order" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalOrderCepat"
                                                data-id="<?= $row['id_produk']; ?>"
                                                data-nama="<?= htmlspecialchars($row['nama_produk']); ?>"
                                                data-kode="<?= htmlspecialchars($row['kode_produk']); ?>"
                                                data-harga="<?= $harga_jual; ?>"
                                                data-stok="<?= $row['stok']; ?>">
                                            <i class="fas fa-bolt me-2"></i>Input Pesanan Cepat
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button type="button" class="btn btn-outline-olive btn-sm fw-bold btn-lihat-detail" data-id="<?php echo $row['id_produk']; ?>">Detail Produk</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="col-12 text-center py-5">
                    <h5 class="text-muted">Produk tidak ditemukan.</h5>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="modal fade" id="modalOrderCepat" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <form action="proses_order_cepat.php" method="POST">
                    <div class="modal-header text-white" style="background-color: var(--dark-purple);">
                        <h5 class="modal-title"><i class="fas fa-cart-plus text-warning me-2"></i>Form Transaksi Sales</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-start">
                        <div class="alert py-2 border-0" style="background-color: var(--soft-pink); color: var(--dark-purple);">
                            <small class="d-block text-muted">Produk yang Dipilih:</small>
                            <strong id="display-nama-produk"></strong> (<span id="display-kode-produk"></span>)
                        </div>
                        
                        <input type="hidden" name="id_produk" id="input-id-produk">
                        <input type="hidden" name="harga_satuan" id="input-harga-satuan">
                        <input type="hidden" id="input-db-stok">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Nama Pelanggan</label>
                            <input type="text" name="nama_pelanggan" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">No. HP / WhatsApp</label>
                            <input type="tel" name="no_hp" class="form-control" placeholder="0812..." required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-dark">Jumlah Beli</label>
                                <input type="number" name="jumlah_beli" id="input-qty" class="form-control" min="1" value="1" required>
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="w-100 p-2 border rounded text-center" style="background-color: var(--bg-cream);">
                                    <small class="text-muted d-block">Stok Gudang:</small>
                                    <span class="fw-bold text-dark" id="display-stok">0 Pcs</span>
                                </div>
                            </div>
                        </div>
                        <div id="error-msg-order" class="text-danger small fw-bold mt-2 text-center" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-1"></i> Stok Kurang / Habis!
                        </div>
                    </div>
                    <div class="modal-footer bg-lightborder-0">
                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="submit_order_cepat" id="btn-submit-order" class="btn btn-olive px-4 fw-bold rounded-pill">Simpan Transaksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetailProduk" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="modalDetailLabel"><i class="fas fa-info-circle d-inline-block me-2 text-muted"></i>Detail Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="isiKontenModal">
                    <div class="text-center py-4">
                        <div class="spinner-border" style="color: var(--accent-olive);" role="status"></div>
                        <p class="text-muted small mt-2">Sedang memuat data produk...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-4 bg-white mt-auto border-top text-center">
        <small class="text-muted">PT Rahayu Karunia Utama &copy; Rahayu</small>
    </footer>

    <div id="compare-floating-btn" class="position-fixed bottom-0 end-0 p-4" style="z-index: 1050; display: none;">
        <button type="button" id="btn-submit-compare" class="btn shadow-lg fw-bold rounded-pill px-4 text-white btn-olive">
            Bandingkan Sekarang (<span id="compare-count">0</span>)
        </button>
    </div>

    <?php include 'modal_logout.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- LOGIKA MODAL DETAIL PRODUK DINAMIS (AJAX) ---
        const modalDetail = new bootstrap.Modal(document.getElementById('modalDetailProduk'));
        const kontainerIsi = document.getElementById('isiKontenModal');

        document.querySelectorAll('.btn-lihat-detail').forEach(button => {
            button.addEventListener('click', function() {
                const idProduk = this.getAttribute('data-id');
                
                kontainerIsi.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border" style="color: var(--accent-olive);" role="status"></div>
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
                                <i class="fas fa-exclamation-triangle display-4"></i>
                                <p class="mt-2 fw-bold">Gagal memuat data produk. Coba lagi nanti.</p>
                            </div>`;
                    });
            });
        });

        // --- LOGIKA BANDINGKAN ---
        const checkboxes = document.querySelectorAll('.compare-checkbox');
        const floatingBtn = document.getElementById('compare-floating-btn');
        const compareCount = document.getElementById('compare-count');
        const submitBtn = document.getElementById('btn-submit-compare');
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedCount = document.querySelectorAll('.compare-checkbox:checked').length;
                if (checkedCount > 3) {
                    alert('Maksimal 3 produk.');
                    this.checked = false; return;
                }
                floatingBtn.style.display = checkedCount > 0 ? 'block' : 'none';
                compareCount.textContent = checkedCount;
            });
        });

        submitBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.compare-checkbox:checked');
            const ids = Array.from(checkedBoxes).map(cb => cb.value);
            window.location.href = 'bandingkan.php?ids=' + ids.join(',');
        });

        // --- LOGIKA MODAL ORDER CEPAT ---
        const orderModal = document.getElementById('modalOrderCepat');
        if(orderModal) {
            orderModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                
                const id = button.getAttribute('data-id');
                const nama = button.getAttribute('data-nama');
                const kode = button.getAttribute('data-kode');
                const harga = button.getAttribute('data-harga');
                const stok = button.getAttribute('data-stok');

                document.getElementById('input-id-produk').value = id;
                document.getElementById('input-harga-satuan').value = harga;
                document.getElementById('input-db-stok').value = stok;
                document.getElementById('display-nama-produk').textContent = nama;
                document.getElementById('display-kode-produk').textContent = kode;
                document.getElementById('display-stok').textContent = stok + ' Pcs';
                
                const qtyInput = document.getElementById('input-qty');
                qtyInput.value = 1;
                document.getElementById('error-msg-order').style.display = 'none';
                document.getElementById('btn-submit-order').disabled = (parseInt(stok) <= 0);
            });

            document.getElementById('input-qty').addEventListener('input', function() {
                const qty = parseInt(this.value) || 0;
                const maxStok = parseInt(document.getElementById('input-db-stok').value);
                const btnSubmit = document.getElementById('btn-submit-order');
                const errorMsg = document.getElementById('error-msg-order');

                if (qty > maxStok || qty <= 0) {
                    btnSubmit.disabled = true;
                    errorMsg.style.display = 'block';
                } else {
                    btnSubmit.disabled = false;
                    errorMsg.style.display = 'none';
                }
            });
        }
    });
    </script>
</body>
</html>