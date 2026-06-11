<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

$is_login  = isset($_SESSION['login']) && $_SESSION['login'] === true;
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
$is_sales  = ($is_login && $user_role === 'sales');
$is_admin  = ($is_login && $user_role === 'admin');

// Filter Kategori
$kategori_aktif = isset($_GET['kat']) ? mysqli_real_escape_string($koneksi, $_GET['kat']) : '';
$search_query   = isset($_GET['q']) ? mysqli_real_escape_string($koneksi, $_GET['q']) : '';

// --- PERBAIKAN QUERY ---
// Menggunakan GROUP_CONCAT untuk menggabungkan banyak kategori
$sql = "SELECT p.*, GROUP_CONCAT(k.nama_kategori SEPARATOR ', ') as daftar_kategori 
        FROM produk p 
        LEFT JOIN produk_kategori pk ON p.id_produk = pk.id_produk
        LEFT JOIN kategori k ON pk.id_kategori = k.id_kategori";

$where_clauses = [];
if (!empty($kategori_aktif)) {
    // Karena bisa lebih dari 1 kategori, kita cek menggunakan FIND_IN_SET atau LIKE
    $where_clauses[] = "pk.id_kategori = '$kategori_aktif'";
}
if (!empty($search_query)) {
    $where_clauses[] = "p.nama_produk LIKE '%$search_query%'";
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}

// Jangan lupa GROUP BY agar produk tidak ganda
$sql .= " GROUP BY p.id_produk ORDER BY p.id_produk DESC";

$result = $koneksi->query($sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk - PT Rahayu Karunia Utama</title>
    <?php include 'pwa_meta.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
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
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }

        .btn-theme {
            background-color: var(--accent-plum);
            color: white;
            border: none;
        }

        .btn-theme:hover {
            background-color: var(--accent-indigo);
            color: white;
        }

        .btn-outline-theme {
            border: 2px solid var(--accent-indigo);
            color: var(--accent-indigo);
            background: transparent;
        }

        .btn-outline-theme:hover {
            background-color: var(--accent-indigo);
            color: white;
        }

        .filter-btn {
            border-radius: 50rem;
            padding: 8px 20px;
            font-weight: 600;
            border: 1px solid var(--accent-gray);
            color: var(--accent-indigo);
            background: white;
            transition: all 0.3s;
        }

        .filter-btn.active,
        .filter-btn:hover {
            background-color: var(--accent-indigo);
            color: white;
            border-color: var(--accent-indigo);
        }

        .product-card {
            border: none;
            transition: transform 0.3s ease;
            background-color: #ffffff;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(36, 31, 72, 0.1) !important;
        }

        .card-img-container {
            height: 250px;
            overflow: hidden;
            background-color: #f8f9fa;
        }

        .card-img-container img {
            object-fit: cover;
            width: 100%;
            height: 100%;
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
    </style>
</head>

<body>

    <?php if ($is_admin) : ?>
        <nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow-sm py-3" style="background-color: var(--accent-indigo);">
            <div class="container">
                <a class="navbar-brand fw-bold" href="#!"><i class="fas fa-user-shield text-warning me-2"></i>Katalog Mode: Admin</a>
                <div class="d-flex gap-2 ms-auto">
                    <a href="dashboard_admin.php" class="btn fw-bold px-4 rounded-pill" style="background-color: var(--bg-lavender); color: var(--accent-indigo);">Kembali ke Dashboard</a>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="btn btn-danger px-3 rounded-pill"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>
        </nav>
    <?php else : ?>
        <?php include 'navbar.php'; ?>
    <?php endif; ?>


    <div class="container py-4 mt-2">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold" style="color: var(--accent-indigo);">Koleksi Produk Kami</h2>
                <p class="text-muted mb-0">Temukan kenyamanan terbaik dari koleksi pilihan Rahayu.</p>
            </div>
            <div class="col-md-6 mt-3 mt-md-0">
                <form action="" method="GET" class="d-flex shadow-sm rounded-pill overflow-hidden">
                    <input type="text" name="q" class="form-control border-0 px-4 py-2 bg-white" placeholder="Cari nama produk..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="btn px-4 text-white m-1 rounded-pill" style="background-color: var(--accent-plum);"><i class="fas fa-search"></i> Cari</button>
                </form>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-4 pb-3 border-bottom overflow-auto" style="white-space: nowrap;">
            <a href="katalog.php" class="filter-btn text-decoration-none <?php echo empty($kategori_aktif) ? 'active' : ''; ?>">Semua Produk</a>
            <?php
            $kat_query = $koneksi->query("SELECT * FROM kategori");
            while ($k = $kat_query->fetch_assoc()) :
                $active = ($kategori_aktif == $k['id_kategori']) ? 'active' : '';
            ?>
                <a href="katalog.php?kat=<?php echo $k['id_kategori']; ?>" class="filter-btn text-decoration-none <?php echo $active; ?>">
                    <?php echo htmlspecialchars($k['nama_kategori']); ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>


    <div class="container mb-5">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">

            <?php if ($result && $result->num_rows > 0) : ?>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <div class="col">
                        <div class="card h-100 product-card shadow-sm rounded-3 overflow-hidden">

                            <div class="card-img-container">
                                <img src="assets/img/<?php echo !empty($row['foto']) ? htmlspecialchars($row['foto']) : 'no-image.jpg'; ?>" class="card-img-top">
                            </div>

                            <div class="card-body d-flex flex-column p-3">
                                <span class="text-uppercase small mb-1 fw-semibold" style="color: var(--accent-plum);">
                                    <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($row['daftar_kategori'] ?? 'Umum'); ?>
                                </span>

                                <h5 class="card-title fw-bold mb-2" style="color: var(--accent-indigo);"><?php echo htmlspecialchars($row['nama_produk']); ?></h5>

                                <div class="mb-3 p-2 rounded" style="background-color: var(--soft-cream); border-left: 3px solid var(--accent-plum);">
                                    <span class="fw-bold fs-5" style="color: var(--accent-indigo);">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></span>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input compare-checkbox" type="checkbox" value="<?php echo $row['id_produk']; ?>" id="comp-<?php echo $row['id_produk']; ?>">
                                    <label class="form-check-label small text-muted" for="comp-<?php echo $row['id_produk']; ?>">Bandingkan</label>
                                </div>

                                <div class="d-grid mt-auto">
                                    <?php if ($is_sales) : ?>
                                        <button type="button" class="btn fw-bold mb-2 rounded-pill text-white btn-order-cepat shadow-sm"
                                            style="background-color: #27ae60;"
                                            data-bs-toggle="modal" data-bs-target="#modalOrderCepat"
                                            data-id="<?= $row['id_produk']; ?>" data-nama="<?= htmlspecialchars($row['nama_produk']); ?>"
                                            data-harga="<?= $row['harga']; ?>" data-stok="<?= $row['stok']; ?>">
                                            <i class="fas fa-bolt me-1"></i> Pesanan Cepat
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-outline-theme btn-sm fw-bold rounded-pill btn-lihat-detail" data-id="<?php echo $row['id_produk']; ?>">Lihat Detail</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-box-open fa-3x mb-3 text-muted opacity-50"></i>
                    <h5 class="text-muted fw-bold">Belum ada produk untuk kategori ini.</h5>
                </div>
            <?php endif; ?>

        </div>
    </div>


    <?php if ($is_sales) : ?>
        <div class="modal fade" id="modalOrderCepat" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form action="proses_order_cepat.php" method="POST">
                        <div class="modal-header border-0" style="background-color: var(--bg-lavender); border-radius: 1rem 1rem 0 0;">
                            <h5 class="modal-title fw-bold" style="color: var(--accent-indigo);"><i class="fas fa-bolt text-warning me-2"></i>Form Pesanan Sales</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4 text-start">

                            <div class="mb-4 p-3 rounded" style="background-color: var(--soft-cream); border-left: 4px solid var(--accent-plum);">
                                <small class="d-block text-muted">Produk yang Dipilih:</small>
                                <h6 class="fw-bold mb-1" style="color: var(--accent-indigo);" id="display-nama-produk">Nama Produk</h6>
                                <div class="d-flex justify-content-between mt-2">
                                    <span class="fw-bold text-danger" id="display-harga-satuan">Rp 0</span>
                                    <span class="badge bg-secondary" id="display-stok">Sisa Stok: 0</span>
                                </div>
                            </div>

                            <input type="hidden" name="id_produk" id="input-id-produk">
                            <input type="hidden" name="harga_satuan" id="input-harga-satuan">
                            <input type="hidden" id="input-db-stok">
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Nama Pelanggan / Reseller <span class="text-danger">*</span></label>
                                <input type="text" name="nama_pelanggan" class="form-control py-2 shadow-sm" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-muted">No. WhatsApp <span class="text-danger">*</span></label>
                                    <input type="number" name="no_hp" class="form-control py-2 shadow-sm" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold small text-muted">Jumlah Beli <span class="text-danger">*</span></label>
                                    <input type="number" name="jumlah_beli" id="input-qty" class="form-control py-2 shadow-sm border-primary" min="1" value="1" required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3 p-3 rounded border">
                                <span class="fw-semibold text-muted">Total Bayar:</span>
                                <h4 class="fw-bold mb-0" style="color: var(--accent-indigo);" id="display-total-bayar">Rp 0</h4>
                            </div>

                            <div id="error-msg-order" class="text-danger small fw-bold mt-2 text-center" style="display: none;">
                                <i class="fas fa-exclamation-triangle me-1"></i> Jumlah melebihi stok!
                            </div>

                        </div>
                        <div class="modal-footer border-0 pb-4 px-4 justify-content-between">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="submit_order_cepat" id="btn-submit-order" class="btn rounded-pill px-4 text-white fw-bold shadow-sm" style="background-color: var(--accent-indigo);">Simpan Transaksi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <div class="modal fade" id="modalDetailProduk" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0" style="background-color: var(--bg-lavender); border-radius: 1rem 1rem 0 0;">
                    <h5 class="modal-title fw-bold" style="color: var(--accent-indigo);" id="modalDetailLabel"><i class="bi bi-info-circle me-2"></i>Detail Spesifikasi Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="isiKontenModal">
                    <div class="text-center py-5">
                        <div class="spinner-border" style="color: var(--accent-plum);" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="compare-floating-btn" class="position-fixed bottom-0 end-0 p-4" style="z-index: 1050; display: none;">
        <button type="button" id="btn-submit-compare" class="btn shadow-lg fw-bold rounded-pill px-4 py-2 text-white" style="background-color: var(--accent-indigo);">
            Bandingkan (<span id="compare-count">0</span>) <i class="fas fa-arrow-right ms-2"></i>
        </button>
    </div>


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

    <!-- Modal Peringatan Global -->
<div class="modal fade" id="warningModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0" style="background-color: var(--accent-plum); border-radius: 1rem 1rem 0 0;">
                <h5 class="modal-title fw-bold text-white" id="warningModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Peringatan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="fas fa-info-circle fa-3x mb-3" style="color: var(--accent-plum);"></i>
                <p class="mb-0 fs-5" id="warningMessage">Pesan peringatan akan ditampilkan di sini.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn px-4 rounded-pill text-white fw-bold shadow-sm" style="background-color: var(--accent-indigo);" data-bs-dismiss="modal">
                    <i class="fas fa-check me-2"></i>Mengerti
                </button>
            </div>
        </div>
    </div>
</div>

    <?php if ($is_login) include 'modal_logout.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const formatRupiah = (number) => {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(number);
            };

            // --- AJAX UNTUK MODAL DETAIL ---
            const modalDetail = new bootstrap.Modal(document.getElementById('modalDetailProduk'));
            const kontainerIsi = document.getElementById('isiKontenModal');

            document.querySelectorAll('.btn-lihat-detail').forEach(button => {
                button.addEventListener('click', function() {
                    const idProduk = this.getAttribute('data-id');

                    // Tampilkan animasi pemuatan
                    kontainerIsi.innerHTML = `<div class="text-center py-5"><div class="spinner-border" style="color: var(--accent-plum);"></div><p class="text-muted small mt-2 fw-semibold">Sedang memuat data produk...</p></div>`;
                    modalDetail.show();

                    // Gunakan jalur absolut agar peramban tidak salah arah
                    fetch('/katalog/detail.php?id=' + idProduk)
                        .then(response => {
                            // Periksa apakah respons dari server atau cache berhasil
                            if (!response.ok) throw new Error("Data tidak tersedia");
                            return response.text();
                        })
                        .then(htmlResponse => {
                            kontainerIsi.innerHTML = htmlResponse;
                        })
                        .catch(error => {
                            // Peringatan ini HANYA muncul jika produk belum pernah dibuka saat internet menyala
                            kontainerIsi.innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="fas fa-wifi display-4 mb-2"></i>
                    <p class="mt-2 fw-bold">Anda sedang offline.</p>
                    <p class="text-muted small">Spesifikasi produk ini belum tersimpan di memori. Silakan buka produk ini minimal satu kali saat internet terhubung.</p>
                </div>`;
                        });
                });
            });

            // --- LOGIKA CHECKBOX BANDINGKAN PRODUK ---
            const checkboxes = document.querySelectorAll('.compare-checkbox');
            const floatingBtn = document.getElementById('compare-floating-btn');
            const compareCount = document.getElementById('compare-count');
            const submitBtn = document.getElementById('btn-submit-compare');

            if (checkboxes.length > 0) {
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const checkedCount = document.querySelectorAll('.compare-checkbox:checked').length;

                        if (checkedCount > 4) {
                            // Tampilkan modal peringatan
                            const warningModal = new bootstrap.Modal(document.getElementById('warningModal'));
                            document.getElementById('warningModalLabel').innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Maksimal 4 Produk';
                            document.getElementById('warningMessage').textContent = 'Anda hanya dapat membandingkan maksimal 4 produk dalam satu waktu untuk menjaga tampilan tabel perbandingan tetap rapi dan informatif.';
                            warningModal.show();
                            this.checked = false;
                            return;
                        }
                        if (checkedCount > 0) {
                            floatingBtn.style.display = 'block';
                            compareCount.textContent = checkedCount;
                        } else {
                            floatingBtn.style.display = 'none';
                        }
                    });
                });

                submitBtn.addEventListener('click', function() {
                    const checkedBoxes = document.querySelectorAll('.compare-checkbox:checked');
                    const ids = Array.from(checkedBoxes).map(cb => cb.value);

                    if (ids.length < 2) {
                        const warningModal = new bootstrap.Modal(document.getElementById('warningModal'));
                        document.getElementById('warningModalLabel').innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Produk Kurang';
                        document.getElementById('warningMessage').textContent = 'Silakan pilih minimal 2 produk untuk dibandingkan.';
                        warningModal.show();
                        return;
                    }

                    if (ids.length > 0) {
                        window.location.href = 'bandingkan.php?ids=' + ids.join(',');
                    }
                });
            }

            // --- LOGIKA MODAL ORDER CEPAT (KHUSUS SALES) ---
            const orderModal = document.getElementById('modalOrderCepat');
            if (orderModal) {
                orderModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;

                    const stok = parseInt(button.getAttribute('data-stok'));
                    const harga = parseFloat(button.getAttribute('data-harga'));

                    document.getElementById('input-id-produk').value = button.getAttribute('data-id');
                    document.getElementById('input-harga-satuan').value = harga;
                    document.getElementById('input-db-stok').value = stok;

                    document.getElementById('display-nama-produk').textContent = button.getAttribute('data-nama');
                    document.getElementById('display-harga-satuan').textContent = formatRupiah(harga);
                    document.getElementById('display-stok').textContent = 'Sisa Stok: ' + stok + ' Pcs';

                    document.getElementById('display-total-bayar').textContent = formatRupiah(harga);
                    document.getElementById('input-qty').max = stok;
                    document.getElementById('input-qty').value = 1;

                    document.getElementById('btn-submit-order').disabled = (stok <= 0);
                    document.getElementById('error-msg-order').style.display = 'none';
                });

                document.getElementById('input-qty').addEventListener('input', function() {
                    let qty = parseInt(this.value) || 0;
                    const maxStok = parseInt(document.getElementById('input-db-stok').value);
                    const hargaSatuan = parseFloat(document.getElementById('input-harga-satuan').value);

                    document.getElementById('display-total-bayar').textContent = formatRupiah(qty * hargaSatuan);

                    const isError = (qty > maxStok || qty <= 0);
                    document.getElementById('btn-submit-order').disabled = isError;
                    document.getElementById('error-msg-order').style.display = isError ? 'block' : 'none';
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/idb@7/build/umd.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const orderForm = document.querySelector('form[action="proses_order_cepat.php"]');

            if (orderForm) {
                orderForm.addEventListener("submit", async function(e) {
                    e.preventDefault();

                    const btnSubmit = orderForm.querySelector('button[type="submit"]');
                    const teksAsli = btnSubmit ? btnSubmit.innerHTML : "Simpan Transaksi";

                    if (btnSubmit) {
                        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
                        btnSubmit.disabled = true;
                    }

                    const formData = new FormData(orderForm);
                    const dataOrder = Object.fromEntries(formData.entries());
                    dataOrder.tanggal = new Date().toISOString();
                    dataOrder.id_lokal = Date.now(); // Kunci identitas unik untuk memori lokal

                    if (navigator.onLine) {
                        try {
                            const response = await fetch("/katalog/proses_order_cepat.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json"
                                },
                                body: JSON.stringify(dataOrder)
                            });

                            const textResponse = await response.text();

                            try {
                                const result = JSON.parse(textResponse);
                                if (result.status === "success") {
                                    alert(result.message);
                                    window.location.reload();
                                } else {
                                    alert("Gagal: " + result.message);
                                    resetTombol(btnSubmit, teksAsli);
                                }
                            } catch (parseError) {
                                alert("Terjadi kesalahan pada sistem server PHP.");
                                resetTombol(btnSubmit, teksAsli);
                            }
                        } catch (error) {
                            simpanPesananLokal(dataOrder, btnSubmit, teksAsli);
                        }
                    } else {
                        simpanPesananLokal(dataOrder, btnSubmit, teksAsli);
                    }
                });
            }

            function resetTombol(btnSubmit, teksAsli) {
                if (btnSubmit) {
                    btnSubmit.innerHTML = teksAsli;
                    btnSubmit.disabled = false;
                }
            }

            // FUNGSI BARU: Menyimpan pesanan tanpa IndexedDB (Sangat Ringan & Aman)
            function simpanPesananLokal(data, btnSubmit, teksAsli) {
                try {
                    // Ambil data lama atau buat ruang kosong baru
                    let pesananOffline = JSON.parse(localStorage.getItem("offlineOrders")) || [];

                    // Masukkan pesanan baru
                    pesananOffline.push(data);

                    // Simpan kembali ke dalam brankas peramban
                    localStorage.setItem("offlineOrders", JSON.stringify(pesananOffline));

                    alert("MODE OFFLINE AKTIF. Transaksi tersimpan aman di memori perangkat. Sistem akan mengirim data saat internet menyala.");
                    window.location.reload();
                } catch (err) {
                    alert("Penyimpanan luring gagal. Memori peramban mungkin penuh.");
                    resetTombol(btnSubmit, teksAsli);
                }
            }

            // FUNGSI BARU: Sinkronisasi super cepat
            async function prosesSinkronisasi() {
                let pesananOffline = JSON.parse(localStorage.getItem("offlineOrders")) || [];
                if (pesananOffline.length === 0) return;

                let berhasilSync = 0;
                let sisaPesanan = []; // Untuk menampung pesanan yang gagal terkirim (misal karena sinyal putus nyambung)

                for (const order of pesananOffline) {
                    try {
                        const response = await fetch("/katalog/proses_order_cepat.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify(order)
                        });
                        const textResponse = await response.text();

                        try {
                            const result = JSON.parse(textResponse);
                            if (result.status === "success") {
                                berhasilSync++;
                            } else {
                                // Jika server PHP menolak (misal stok habis), data tetap dihapus agar tidak nyangkut
                                console.error("Ditolak server:", result.message);
                            }
                        } catch (e) {
                            sisaPesanan.push(order); // Pertahankan data jika PHP error
                        }
                    } catch (e) {
                        sisaPesanan.push(order); // Pertahankan data jika internet kembali mati
                    }
                }

                // Perbarui isi brankas peramban (Hapus yang sukses, simpan yang masih tertunda)
                localStorage.setItem("offlineOrders", JSON.stringify(sisaPesanan));

                if (berhasilSync > 0) {
                    alert(`SINKRONISASI SUKSES: ${berhasilSync} data pesanan luring telah berhasil disalurkan ke sistem pusat.`);
                    window.location.reload();
                }
            }

            // Deteksi otomatis saat internet kembali menyala
            window.addEventListener("online", prosesSinkronisasi);
            if (navigator.onLine) {
                prosesSinkronisasi();
            }
        });
    </script>

</body>

</html>