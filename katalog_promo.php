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

// 2. Ambil Tanggal Hari Ini untuk Filter Masa Promo
$today = date('Y-m-d');

// 3. Query Ambil Data Produk yang Sedang Aktif Masa Promonya (Krusial)
$sql_promo = "SELECT p.*, k.nama_kategori, pr.nama_promo, pr.diskon_persen, pr.tgl_selesai 
              FROM produk p 
              INNER JOIN promo pr ON p.id_produk = pr.id_produk 
              LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
              WHERE ? BETWEEN pr.tgl_mulai AND pr.tgl_selesai 
                AND pr.diskon_persen > 0 
                AND p.status_tampil = 'aktif'
              ORDER BY pr.diskon_persen DESC";

$stmt = $koneksi->prepare($sql_promo);
$stmt->bind_param("s", $today);
$stmt->execute();
$result_promo = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promo Spesial - Rahayu</title>
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
        .btn-olive { background-color: var(--accent-olive); color: white; border: none; }
        .btn-olive:hover { background-color: var(--accent-olive-hover); color: white; }
        .btn-outline-olive { border: 2px solid var(--accent-olive); color: var(--accent-olive); background: transparent; }
        .btn-outline-olive:hover { background-color: var(--accent-olive); color: white; }
        
        /* Promo Banner Custom Header */
        .promo-banner { 
            background: linear-gradient(135deg, #ffefe5 0%, #fffbf5 100%); 
            border-bottom: 3px solid #ffc107; 
        }
        .product-card { border: none; transition: transform 0.2s ease, box-shadow 0.2s ease; background-color: #ffffff; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
        .card-img-container { height: 250px; overflow: hidden; background-color: #f8f9fa; }
        .card-img-container img { object-fit: cover; width: 100%; height: 100%; }
    </style>
</head>
<body>

    <?php 
    if ($is_login && $user_role === 'admin') {
        echo '<nav class="navbar navbar-dark bg-dark sticky-top py-3"><div class="container"><a class="navbar-brand fw-bold" href="#!"><i class="fas fa-user-shield text-danger me-2"></i>Katalog Promo: Admin</a><a href="dashboard_admin.php" class="btn btn-primary btn-sm rounded-pill">Dashboard Admin</a></div></nav>';
    } else {
        include 'navbar.php'; 
    }
    ?>

    <div class="promo-banner py-5 mb-5 text-center shadow-sm">
        <div class="container py-2">
            <span class="badge bg-danger mb-3 px-3 py-2 rounded-pill text-uppercase tracking-wider">
                <i class="fas fa-bolt me-1"></i> Flash Sale & Promo Aktif
            </span>
            <h1 class="display-5 fw-bold text-dark"><i class="fas fa-percentage text-warning me-2"></i>Penawaran Terbatas</h1>
            <p class="lead text-muted mx-auto" style="max-width: 650px;">Diskon Spesial Rahayu: Dapatkan produk kualitas premium terbaik dengan potongan harga terbesar khusus minggu ini!</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            
            <?php if ($result_promo->num_rows > 0) : ?>
                <?php while ($row = $result_promo->fetch_assoc()) : 
                    // Perhitungan Logika Harga Akhir setelah Diskon
                    $persen_diskon = (int)$row['diskon_persen'];
                    $harga_asli    = (float)$row['harga'];
                    $harga_diskon  = $harga_asli - ($harga_asli * ($persen_diskon / 100));
                ?>
                    <div class="col">
                        <div class="card h-100 product-card shadow-sm rounded-3 overflow-hidden">
                            
                            <div class="card-img-container position-relative">
                                <img src="assets/img/<?= $row['foto'] ? htmlspecialchars($row['foto']) : 'no-image.jpg'; ?>" class="card-img-top" alt="<?= htmlspecialchars($row['nama_produk']); ?>">
                                <span class="position-absolute top-0 start-0 bg-danger text-white px-3 py-1.5 m-2 rounded-pill small fw-bold shadow">
                                    <i class="fas fa-fire me-1"></i> Hemat <?= $persen_diskon; ?>%
                                </span>
                            </div>

                            <div class="card-body d-flex flex-column p-3">
                                <span class="text-uppercase font-monospace text-muted small mb-1"><?= htmlspecialchars($row['nama_kategori'] ?? 'Katalog'); ?></span>
                                <h5 class="card-title text-dark fw-bold mb-2"><?= htmlspecialchars($row['nama_produk']); ?></h5>
                                
                                <p class="card-text text-muted small flex-grow-1 mb-3">
                                    <?= htmlspecialchars(substr($row['deskripsi'], 0, 70)); ?>...
                                </p>
                                
                                <div class="mb-2">
                                    <del class="text-muted small d-block">Rp <?= number_format($harga_asli, 0, ',', '.'); ?></del>
                                    <span class="fw-bold fs-5" style="color: red;">
                                        Rp <?= number_format($harga_diskon, 0, ',', '.'); ?>
                                    </span>
                                </div>

                                <div class="mb-3 py-1 px-2 bg-light border rounded text-start">
                                    <small class="text-muted d-block" style="font-size: 0.78rem;">
                                        <i class="far fa-clock text-warning me-1"></i> Promo berakhir pada:
                                    </small>
                                    <span class="fw-bold text-dark small" style="font-size: 0.85rem;">
                                        <?= date('d-m-Y', strtotime($row['tgl_selesai'])); ?>
                                    </span>
                                </div>

                                <div class="form-check mb-3 mt-1">
                                    <input class="form-check-input compare-checkbox" type="checkbox" value="<?= $row['id_produk']; ?>" id="comp-<?= $row['id_produk']; ?>">
                                    <label class="form-check-label small text-muted" for="comp-<?= $row['id_produk']; ?>">Pilih untuk Bandingkan</label>
                                </div>

                                <div class="d-grid mt-auto">
                                    <?php if ($is_sales) : ?>
                                        <button type="button" class="btn btn-olive fw-bold mb-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalOrderCepat<?= $row['id_produk']; ?>">
                                            <i class="fas fa-bolt me-2"></i>Input Pesanan Cepat
                                        </button>
                                        
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 btn-lihat-detail-promo" data-id="<?= $row['id_produk']; ?>">Detail Produk</button>
                                    <?php else : ?>
                                        
                                        <button type="button" class="btn btn-outline-olive fw-bold rounded-2 py-2 shadow-sm btn-lihat-detail-promo" data-id="<?= $row['id_produk']; ?>">
                                            <i class="fas fa-info-circle me-1"></i> Detail Produk
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($is_sales) : ?>
                    <div class="modal fade" id="modalOrderCepat<?= $row['id_produk']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="proses_order_cepat.php" method="POST">
                                    <div class="modal-header bg-dark text-white">
                                        <h5 class="modal-title"><i class="fas fa-cart-plus text-warning me-2"></i>Form Transaksi Instan (Promo)</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-start">
                                        <div class="alert alert-warning py-2 shadow-sm mb-3">
                                            <small class="d-block text-muted">Produk Promo Dipilih:</small>
                                            <strong><?= htmlspecialchars($row['nama_produk']); ?></strong> 
                                            <span class="badge bg-danger ms-1">Diskon <?= $persen_diskon; ?>%</span>
                                        </div>
                                        
                                        <input type="hidden" name="id_produk" value="<?= $row['id_produk']; ?>">
                                        <input type="hidden" name="harga_satuan" value="<?= $harga_diskon; ?>">
                                        <input type="hidden" id="db-stok-<?= $row['id_produk']; ?>" value="<?= $row['stok']; ?>">

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Nama Pelanggan</label>
                                            <input type="text" name="nama_pelanggan" class="form-control" placeholder="Masukkan nama pembeli" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">No. HP / WhatsApp</label>
                                            <input type="tel" name="no_hp" class="form-control" placeholder="Contoh: 08XXXXXXXXXX" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Jumlah Beli</label>
                                                <input type="number" name="jumlah_beli" data-id="<?= $row['id_produk']; ?>" class="form-control qty-input-field" min="1" value="1" required>
                                            </div>
                                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                                <div class="w-100 p-2 bg-light border rounded text-center">
                                                    <small class="text-muted d-block">Stok Gudang:</small>
                                                    <span class="fw-bold text-dark"><?= $row['stok']; ?> Pcs</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="error-msg-<?= $row['id_produk']; ?>" class="text-danger small fw-bold mt-2 text-center" style="display: none;">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Maaf, Stok Kurang / Habis!
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="submit_order_cepat" id="btn-submit-<?= $row['id_produk']; ?>" class="btn btn-olive px-4 fw-bold">Simpan Transaksi Promo</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php endwhile; ?>
            <?php else : ?>
                <div class="col-12 text-center py-5">
                    <div class="card p-5 border-0 shadow-sm rounded-3 bg-white">
                        <i class="fas fa-percentage fa-3x text-muted mb-3"></i>
                        <h5 class="fw-bold text-secondary">Saat Ini Belum Ada Promo Berjalan</h5>
                        <p class="text-muted small mb-0">Silakan kembali beberapa saat lagi untuk memantau pembaruan diskon dari admin pusat.</p>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="modal fade" id="modalDetailPromo" tabindex="-1" aria-labelledby="modalDetailPromoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="modalDetailPromoLabel"><i class="fas fa-info-circle me-2 text-secondary"></i>Detail Produk Promo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="isiKontenModalPromo">
                    <div class="text-center py-4">
                        <div class="spinner-border text-danger" role="status"></div>
                        <p class="text-muted small mt-2">Sedang memuat spesifikasi produk...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-4 bg-white mt-auto border-top text-center">
        <div class="container">
            <small class="text-muted">PT Rahayu Karunia Utama &copy; Rahayu</small>
        </div>
    </footer>

    <div id="compare-floating-btn" class="position-fixed bottom-0 end-0 p-4" style="z-index: 1050; display: none;">
        <button type="button" id="btn-submit-compare" class="btn shadow-lg fw-bold rounded-pill px-4 text-white" style="background-color: var(--accent-olive);">
            <i class="fas fa-columns me-2"></i>Bandingkan Promo (<span id="compare-count">0</span>)
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- LOGIKA TAMBAHAN: MODAL DETAIL PRODUK DINAMIS VIA AJAX ---
        const modalDetailPromo = new bootstrap.Modal(document.getElementById('modalDetailPromo'));
        const kontainerIsiPromo = document.getElementById('isiKontenModalPromo');

        document.querySelectorAll('.btn-lihat-detail-promo').forEach(button => {
            button.addEventListener('click', function() {
                const idProduk = this.getAttribute('data-id');
                
                // Animasi Loading Spinner setiap kali pop-up dibuka
                kontainerIsiPromo.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-danger" role="status"></div>
                        <p class="text-muted small mt-2">Sedang memuat spesifikasi produk...</p>
                    </div>`;
                
                modalDetailPromo.show();

                // Request data ke detail.php
                fetch('detail.php?id=' + idProduk)
                    .then(response => response.text())
                    .then(htmlResponse => {
                        kontainerIsiPromo.innerHTML = htmlResponse;
                    })
                    .catch(error => {
                        kontainerIsiPromo.innerHTML = `
                            <div class="text-center py-4 text-danger">
                                <i class="fas fa-exclamation-triangle display-4"></i>
                                <p class="mt-2 fw-bold">Gagal mengambil informasi produk. Silakan coba kembali.</p>
                            </div>`;
                    });
            });
        });

        // --- LOGIKA 1: FITUR BANDINGKAN PRODUK PROMO ---
        const checkboxes = document.querySelectorAll('.compare-checkbox');
        const floatingBtn = document.getElementById('compare-floating-btn');
        const compareCount = document.getElementById('compare-count');
        const submitBtn = document.getElementById('btn-submit-compare');
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedCount = document.querySelectorAll('.compare-checkbox:checked').length;
                if (checkedCount > 3) {
                    alert('Maksimal membandingkan 3 produk promo sekaligus.');
                    this.checked = false; 
                    return;
                }
                floatingBtn.style.display = checkedCount > 0 ? 'block' : 'none';
                compareCount.textContent = checkedCount;
            });
        });

        submitBtn.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.compare-checkbox:checked');
            const ids = Array.from(checkedBoxes).map(cb => cb.value);
            if(ids.length < 2) { 
                alert('Pilih minimal 2 produk promo untuk dibandingkan.'); 
                return; 
            }
            window.location.href = 'bandingkan.php?ids=' + ids.join(',');
        });

        // --- LOGIKA 2: JAVASCRIPT VALIDASI STOK FORM TRANSAKSI INSTAN (TERPUSAT) ---
        const qtyInputs = document.querySelectorAll('.qty-input-field');
        qtyInputs.forEach(input => {
            input.addEventListener('input', function() {
                const prodId = this.getAttribute('data-id');
                const qtyInput = parseInt(this.value) || 0;
                const dbStok = parseInt(document.getElementById('db-stok-' + prodId).value);
                const btnSubmit = document.getElementById('btn-submit-' + prodId);
                const errorMsg = document.getElementById('error-msg-' + prodId);

                if (qtyInput > dbStok || qtyInput <= 0) {
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