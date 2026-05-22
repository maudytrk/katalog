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
if (isset($_GET['ids']) && !empty($_GET['ids'])) {
    // Memisahkan string ID menjadi array
    $raw_ids = explode(',', $_GET['ids']);
    
    // Validasi & Proteksi SQL Injection dengan memastikan data berupa angka (integer)
    foreach ($raw_ids as $id) {
        $clean_id = (int)$id;
        if ($clean_id > 0) {
            $product_ids[] = $clean_id;
        }
    }
}

// Batasi kembali di sisi server maksimal 3 produk demi keamanan layout
$product_ids = array_slice($product_ids, 0, 3);
$total_produk = count($product_ids);

// Persiapan data untuk ditaruh di kolom tabel
$list_produk = [];

if ($total_produk >= 2) {
    // Mengubah array ID menjadi string terpisah koma untuk query SQL (e.g., "1,2,3")
    $ids_string = implode(',', $product_ids);
    
    $today = date('Y-m-d');
    $query = "SELECT p.*, k.nama_kategori, pr.diskon_persen 
              FROM produk p 
              LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
              LEFT JOIN promo pr ON p.id_produk = pr.id_produk AND '$today' BETWEEN pr.tgl_mulai AND pr.tgl_selesai
              WHERE p.id_produk IN ($ids_string)";
              
    $result = $koneksi->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $list_produk[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandingkan Produk Inner Wanita - Rahayu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-cream: #FCF8F1;
            --navbar-purple: #E0D2F0;
            --text-dark-purple: #4a3b5c;
            --accent-olive: #7D8F37;
            --accent-olive-hover: #65752b;
            --soft-purple-bg: #f2ebf9;
            --accent-terracotta: #C87A53; /* Alternatif pengganti merah terang untuk tema hangat */
        }
        body {
            background-color: var(--bg-cream);
            font-family: 'Segoe UI', sans-serif;
            color: #333;
        }
        
        /* Navbar Admin Penyesuaian */
        .navbar-admin-custom {
            background-color: var(--text-dark-purple) !important;
        }
        
        /* Tabel Komparasi */
        .compare-table th {
            background-color: var(--soft-purple-bg) !important;
            color: var(--text-dark-purple);
            width: 20%;
            vertical-align: middle;
            font-weight: 600;
            border-color: #dcd1e8;
        }
        .compare-table td {
            background-color: #ffffff;
            vertical-align: middle;
            width: calc(80% / <?php echo max($total_produk, 1); ?>);
            border-color: #eedffc;
        }
        .product-img-compare {
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid var(--soft-purple-bg);
        }
        
        /* Komponen Tombol Warna Tema */
        .btn-olive {
            background-color: var(--accent-olive);
            color: white;
            border: none;
        }
        .btn-olive:hover, .btn-olive:focus {
            background-color: var(--accent-olive-hover);
            color: white;
        }
        .btn-outline-purple {
            color: var(--text-dark-purple);
            border-color: var(--text-dark-purple);
        }
        .btn-outline-purple:hover {
            background-color: var(--text-dark-purple);
            color: white;
        }
        .text-purple {
            color: var(--text-dark-purple);
        }
        
        /* Badge Custom */
        .badge-kategori {
            background-color: var(--navbar-purple);
            color: var(--text-dark-purple);
            font-weight: 500;
        }
        .badge-diskon {
            background-color: var(--accent-terracotta);
            color: white;
        }
        .text-harga-diskon {
            color: var(--accent-terracotta);
        }
        
        /* Modal Customization */
        .modal-theme-header {
            background-color: var(--navbar-purple) !important;
            color: var(--text-dark-purple) !important;
            border-bottom: 2px solid var(--soft-purple-bg);
        }
        .modal-admin-header {
            background-color: var(--text-dark-purple) !important;
            color: white !important;
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
        if(document.querySelector('.navbar:not(.navbar-admin-custom)')) {
            document.querySelector('.navbar').style.backgroundColor = '#E0D2F0';
        }
    </script>

    <div class="container py-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold text-purple"><i class="fas fa-balance-scale me-2"></i>Komparasi Spek Produk</h2>
                <p class="text-muted mb-0">Membandingkan aspek material, harga, dan ketersediaan stok produk secara bersisian.</p>
            </div>
            <a href="katalog.php" class="btn btn-outline-purple rounded-pill fw-medium">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Katalog
            </a>
        </div>

        <?php if ($total_produk < 2): ?>
            <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-3">
                <div class="py-4">
                    <i class="fas fa-compress-alt fa-3x text-muted mb-3" style="color: var(--navbar-purple) !important;"></i>
                    <h4 class="text-purple fw-semibold">Produk Pembanding Kurang</h4>
                    <p class="text-muted mx-auto" style="max-width: 450px;">Silakan pilih minimal 2 atau maksimal 3 produk di halaman katalog terlebih dahulu untuk melihat visualisasi tabel perbandingan.</p>
                    <a href="katalog.php" class="btn btn-olive fw-bold rounded-pill px-4 mt-2">Pilih Produk Sekarang</a>
                </div>
            </div>
        <?php else: ?>
            
            <div class="table-responsive shadow-sm rounded-3 overflow-hidden">
                <table class="table table-bordered compare-table mb-0 text-center align-middle">
                    <tbody>
                        <tr>
                            <th class="text-start ps-4">Foto Produk</th>
                            <?php foreach ($list_produk as $prod): ?>
                                <td>
                                    <img src="assets/img/<?php echo $prod['foto'] ? $prod['foto'] : 'no-image.jpg'; ?>" class="product-img-compare shadow-sm img-fluid" alt="Foto">
                                </td>
                            <?php endforeach; ?> 
                        </tr>

                        <tr>
                            <th class="text-start ps-4">Nama Produk</th>
                            <?php foreach ($list_produk as $prod): ?>
                                <td>
                                    <span class="fw-bold text-purple d-block fs-5"><?php echo htmlspecialchars($prod['nama_produk']); ?></span>
                                    <small class="text-muted bg-light px-2 py-1 rounded d-inline-block mt-1">Kode: <?php echo htmlspecialchars($prod['kode_produk']); ?></small>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <th class="text-start ps-4">Kategori</th>
                            <?php foreach ($list_produk as $prod): ?>
                                <td>
                                    <span class="badge badge-kategori px-3 py-2 rounded-pill fs-7"><?php echo htmlspecialchars($prod['nama_kategori']); ?></span>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <th class="text-start ps-4">Deskripsi Produk</th>
                            <?php foreach ($list_produk as $prod): ?>
                                <td class="text-start px-3">
                                    <small class="text-dark opacity-75" style="line-height: 1.6; display: block;">
                                        <?php echo nl2br(htmlspecialchars($prod['deskripsi'])); ?>
                                    </small>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <th class="text-start ps-4">Harga Jual</th>
                            <?php foreach ($list_produk as $prod): ?>
                                <td>
                                    <?php if ($prod['diskon_persen'] > 0): 
                                        $harga_akhir = $prod['harga'] - ($prod['harga'] * ($prod['diskon_persen'] / 100));
                                    ?>
                                        <del class="text-muted small">Rp <?php echo number_format($prod['harga'], 0, ',', '.'); ?></del>
                                        <div class="text-harga-diskon fw-bold fs-5">
                                            Rp <?php echo number_format($harga_akhir, 0, ',', '.'); ?> 
                                            <span class="badge badge-diskon fs-7 rounded-2 ms-1">-<?php echo $prod['diskon_persen']; ?>%</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-purple fw-bold fs-5">Rp <?php echo number_format($prod['harga'], 0, ',', '.'); ?></div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <th class="text-start ps-4">Stok Tersedia</th>
                            <?php foreach ($list_produk as $prod): ?>
                                <td>
                                    <?php if ($prod['stok'] <= 5): ?>
                                        <span class="badge px-3 py-2" style="background-color: var(--accent-terracotta);"><i class="fas fa-exclamation-triangle me-1"></i> Tersisa <?php echo $prod['stok']; ?> Pcs</span>
                                    <?php else: ?>
                                        <span class="badge bg-success px-3 py-2"><i class="fas fa-check-circle me-1"></i> <?php echo $prod['stok']; ?> Pcs (Ready)</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <th class="text-start ps-4">Aksi Lanjutan</th>
                            <?php foreach ($list_produk as $prod): ?>
                                <td>
                                    <div class="d-grid gap-2 px-3">
                                        <button type="button" class="btn btn-sm btn-outline-purple fw-bold btn-lihat-detail-compare" data-id="<?php echo $prod['id_produk']; ?>">
                                            <i class="fas fa-info-circle me-1"></i> Detail Lengkap
                                        </button>
                                        
                                        <?php if (isset($_SESSION['login']) && $_SESSION['role'] === 'sales' ) : ?>
                                            <button class="btn btn-sm btn-olive fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalOrderCepat<?php echo $prod['id_produk']; ?>">
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
                            <div class="alert py-2 mb-3" style="background-color: var(--soft-purple-bg); border-left: 4px solid var(--text-dark-purple);">
                                <small class="d-block text-muted">Produk yang Dipilih:</small>
                                <strong class="text-purple"><?php echo htmlspecialchars($prod['nama_produk']); ?></strong> <span class="badge badge-kategori ms-1"><?php echo htmlspecialchars($prod['kode_produk']); ?></span>
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
                            <div id="error-msg-<?php echo $prod['id_produk']; ?>" class="small fw-bold mt-2 text-center" style="display: none; color: var(--accent-terracotta);">
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

    <footer class="py-4 mt-5 border-top text-center bg-white">
        <small class="text-muted">PT Rahayu Karunia Utama &copy; Rahayu</small>
    </footer>

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