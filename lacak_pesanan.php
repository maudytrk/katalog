<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

$order_found = false;
$search_keyword = "";
$order_data = [];
$items_data = [];

// Memproses input pencarian jika form disubmit
if (isset($_GET['keyword']) && !empty(trim($_GET['keyword']))) {
    // Proteksi SQL Injection menggunakan mysqli_real_escape_string
    $search_keyword = mysqli_real_escape_string($koneksi, trim($_GET['keyword']));

    // PERBAIKAN: Hanya cari berdasarkan ID Order (strict match), hapus pencarian nama_pelanggan
    $query_order = "SELECT * FROM orders WHERE id_order = '$search_keyword' LIMIT 1";
    $result_order = $koneksi->query($query_order);

    if ($result_order && $result_order->num_rows > 0) {
        $order_found = true;
        $order_data = $result_order->fetch_assoc();

        $id_order_aktif = $order_data['id_order'];

        // Rincian semua produk melalui order_detail
        $query_items = "SELECT od.*, p.nama_produk, p.kode_produk, k.nama_kategori 
                        FROM order_detail od
                        JOIN produk p ON od.id_produk = p.id_produk
                        LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
                        WHERE od.id_order = '$id_order_aktif'";
        $result_items = $koneksi->query($query_items);

        while ($item = $result_items->fetch_assoc()) {
            $items_data[] = $item;
        }
    } else {
        $order_found = false;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan - Rahayu</title>
    <?php include 'pwa_meta.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* PERUBAHAN: TEMA WARNA BARU DARI REFERENSI GAMBAR */
        :root {
            --bg-lavender: #E0E1F6;
            --accent-indigo: #241F48;
            --accent-plum: #6C4773;
            --accent-gray: #B0B7CA;
        }

        body {
            background-color: var(--bg-lavender);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

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

        /* --- STYLING TIMELINE PROGRESS TRACKER --- */
        .track-line {
            height: 4px;
            background-color: var(--accent-gray);
            position: absolute;
            top: 28px;
            left: 0;
            right: 0;
            z-index: 1;
        }

        .track-step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }

        .track-icon {
            width: 60px;
            height: 60px;
            line-height: 56px;
            border-radius: 50%;
            background-color: #fff;
            border: 3px solid var(--accent-gray);
            color: var(--accent-gray);
            font-size: 20px;
            margin: 0 auto 10px auto;
            transition: all 0.3s ease;
        }

        .track-step.active .track-icon {
            background-color: var(--accent-indigo);
            border-color: var(--accent-indigo);
            color: #fff;
            box-shadow: 0 0 15px rgba(36, 31, 72, 0.3);
        }

        .track-step.active .track-text {
            font-weight: 700;
            color: var(--accent-indigo);
        }

        .track-text {
            font-size: 0.9rem;
            color: #6c757d;
        }

        /* Glassmorphism Card Effect */
        .card-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
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
    <script>
        document.querySelector('.navbar').style.backgroundColor = '#FFFFFF';
    </script>

    <div class="container py-5">
        <div class="text-center mb-5 mt-4">
            <span class="badge mb-2 px-3 py-2 rounded-pill shadow-sm" style="background-color: var(--accent-plum); color: white;">Fitur Pelacakan Publik</span>
            <h2 class="fw-bold" style="color: var(--accent-indigo);"><i class="fas fa-route text-muted me-2"></i>Lacak Status Pengiriman</h2>
            <p class="text-muted mx-auto" style="max-width: 550px;">Inputkan Nomor ID Transaksi untuk memantau status pesanan.</p>
        </div>

        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="card shadow border-0 p-2 rounded-pill bg-white">
                    <form method="GET" action="lacak_pesanan.php" class="d-flex align-items-center">
                        <span class="px-4 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="keyword" class="form-control border-0 shadow-none py-3" placeholder="Masukkan ID Order Anda..." value="<?php echo htmlspecialchars($search_keyword); ?>" required style="background: transparent;">
                        <button type="submit" class="btn btn-theme px-5 py-3 fw-bold rounded-pill"><i class="fas fa-paper-plane me-2"></i>Lacak</button>
                    </form>
                </div>
            </div>
        </div>

        <?php if (!empty($search_keyword)): ?>
            <?php if ($order_found):
                $current_status = strtolower($order_data['status_order'] ?? 'pending');
                $step = 1;
                if ($current_status == 'proses') {
                    $step = 2;
                } elseif ($current_status == 'dikirim') {
                    $step = 3;
                } elseif ($current_status == 'selesai') {
                    $step = 4;
                }
            ?>

                <div class="card card-glass shadow-sm mb-4 p-4">
                    <h5 class="fw-bold mb-4 border-bottom pb-3" style="color: var(--accent-indigo);"><i class="fas fa-tasks me-2" style="color: var(--accent-plum);"></i>Progress Pesanan</h5>

                    <?php if ($current_status == 'dibatalkan'): ?>
                        <div class="alert alert-danger d-flex align-items-center my-2 py-3 rounded-3" role="alert">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Pesanan Ini Telah Dibatalkan</h6>
                                <span class="small text-secondary">Mohon hubungi pihak sales untuk informasi lebih lanjut.</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="position-relative d-flex justify-content-between align-items-center my-4 px-md-5">
                            <div class="track-line"></div>

                            <div class="track-step <?php echo ($step >= 1) ? 'active' : ''; ?>">
                                <div class="track-icon"><i class="fas fa-clock"></i></div>
                                <div class="track-text">Pending</div>
                            </div>

                            <div class="track-step <?php echo ($step >= 2) ? 'active' : ''; ?>">
                                <div class="track-icon"><i class="fas fa-box-open"></i></div>
                                <div class="track-text">Diproses</div>
                            </div>

                            <div class="track-step <?php echo ($step >= 3) ? 'active' : ''; ?>">
                                <div class="track-icon"><i class="fas fa-truck"></i></div>
                                <div class="track-text">Sedang Dikirim</div>
                            </div>

                            <div class="track-step <?php echo ($step >= 4) ? 'active' : ''; ?>">
                                <div class="track-icon"><i class="fas fa-check-double"></i></div>
                                <div class="track-text">Selesai</div>
                            </div>
                        </div>
                        <?php if ($step == 1): ?>
                            <div class="text-center mt-3">
                                <a href="https://wa.me/628123456789?text=Halo admin, saya ingin menanyakan pesanan atas nama <?php echo urlencode($order_data['nama_pelanggan']); ?> dengan ID <?php echo $order_data['id_order']; ?>" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-4">
                                    <i class="fab fa-whatsapp me-1"></i> Tanya Admin via WA
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="row g-4">
                    <div class="col-md-5">
                        <div class="card card-glass shadow-sm p-4 h-100">
                            <h5 class="fw-bold mb-3" style="color: var(--accent-indigo);"><i class="fas fa-file-invoice me-2" style="color: var(--accent-plum);"></i>Informasi Nota</h5>
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted ps-0 py-2" style="width: 45%;">ID Transaksi</td>
                                    <td class="fw-bold py-2" style="color: var(--accent-indigo);">: #<?php echo htmlspecialchars($order_data['id_order'] ?? ''); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-2">Nama Konsumen</td>
                                    <td class="fw-bold py-2" style="color: var(--accent-indigo);">: <?php echo htmlspecialchars($order_data['nama_pelanggan'] ?? ''); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-2">Tanggal Transaksi</td>
                                    <td class="text-dark py-2">: <?php echo isset($order_data['tgl_pesan']) ? date('d M Y - H:i', strtotime($order_data['tgl_pesan'])) : '-'; ?></td>
                                </tr>

                                <tr>
                                    <td class="text-muted ps-0 py-2">Status</td>
                                    <td class="py-2">:
                                        <?php if ($current_status == 'selesai'): ?>
                                            <span class="badge bg-success px-3 py-1 rounded-pill">Selesai</span>
                                        <?php elseif ($current_status == 'dikirim'): ?>
                                            <span class="badge bg-info px-3 py-1 rounded-pill text-dark">Sedang Dikirim</span>
                                        <?php elseif ($current_status == 'proses'): ?>
                                            <span class="badge px-3 py-1 rounded-pill" style="background-color: var(--accent-indigo);">Diproses</span>
                                        <?php elseif ($current_status == 'dibatalkan'): ?>
                                            <span class="badge bg-danger px-3 py-1 rounded-pill">Dibatalkan</span>
                                        <?php else: ?>
                                            <span class="badge text-dark border px-3 py-1 rounded-pill" style="background-color: var(--accent-gray);">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card card-glass shadow-sm p-4 h-100">
                            <h5 class="fw-bold mb-3" style="color: var(--accent-indigo);"><i class="fas fa-shopping-bag me-2" style="color: var(--accent-plum);"></i>Item yang Dibeli</h5>
                            <div class="table-responsive">
                                <table class="table align-middle table-hover">
                                    <thead style="background-color: #F8F9FA;">
                                        <tr>
                                            <th class="text-secondary font-weight-normal border-0 rounded-start">Produk</th>
                                            <th class="text-center text-secondary font-weight-normal border-0">Qty</th>
                                            <th class="text-end text-secondary font-weight-normal border-0">Harga</th>
                                            <th class="text-end text-secondary font-weight-normal border-0 rounded-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">

                                        <?php
                                        $grand_total = 0;
                                        foreach ($items_data as $item):
                                            $qty = $item['jumlah'] ?? 0;
                                            $harga = $item['harga_satuan'] ?? 0;
                                            $subtotal = $item['subtotal'] ?? ($qty * $harga);
                                            $grand_total += $subtotal;
                                        ?>
                                            <tr>
                                                <td class="border-bottom-0 py-3">
                                                    <span class="fw-bold d-block" style="color: var(--accent-indigo);"><?php echo htmlspecialchars($item['nama_produk']); ?></span>
                                                    <small class="text-muted">Kode: <?php echo htmlspecialchars($item['kode_produk']); ?></small>
                                                </td>
                                                <td class="text-center border-bottom-0 py-3 fw-semibold"><?php echo $qty; ?></td>
                                                <td class="text-end border-bottom-0 py-3 text-muted">Rp <?php echo number_format($harga, 0, ',', '.'); ?></td>
                                                <td class="text-end border-bottom-0 py-3 fw-bold" style="color: var(--accent-plum);">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr style="background-color: #F8F9FA;">
                                            <td colspan="3" class="text-end fw-bold py-3 border-0 rounded-start">Total Pembayaran:</td>
                                            <td class="text-end fw-bold py-3 border-0 rounded-end fs-5" style="color: var(--accent-indigo);">Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="card card-glass shadow-sm p-5 text-center rounded-3">
                    <div class="py-4">
                        <i class="fas fa-search-minus fa-3x mb-3" style="color: var(--accent-gray);"></i>
                        <h4 class="fw-bold" style="color: var(--accent-indigo);">Data Tidak Ditemukan</h4>
                        <p class="text-muted mx-auto mt-2" style="max-width: 480px;">
                            Pesanan dengan ID "<strong style="color: var(--accent-plum);"><?php echo htmlspecialchars($search_keyword); ?></strong>" tidak terdaftar. Mohon periksa kembali kesesuaian ID Transaksi Anda.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

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
                        <i class="bi bi-envelope-fill me-2" style="color: var(--accent-gray);"></i>rahayuofficialstore.id@gmail.com<br>
                        <i class="bi bi-whatsapp me-2" style="color: var(--accent-gray);"></i>+62 812-3456-7890
                    </p>
                </div>
            </div>
            <hr class="mt-4 mb-3">
            <p class="m-0 text-center small opacity-75 text-white">&copy; <?php echo date('Y'); ?> E-Catalogue Toko Rahayu. All Rights Reserved.</p>
        </div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>