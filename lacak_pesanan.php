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
    
    // Cari ID Order utama terlebih dahulu agar valid jika dicari berdasarkan nama pelanggan
    $query_find_id = "SELECT id_order FROM orders WHERE id_order = '$search_keyword' OR nama_pelanggan LIKE '%$search_keyword%' ORDER BY tgl_pesan DESC LIMIT 1";
    $result_find_id = $koneksi->query($query_find_id);

    if ($result_find_id && $result_find_id->num_rows > 0) {
        $row_id = $result_find_id->fetch_assoc();
        $id_order_aktif = $row_id['id_order'];

        // Query 1: Ambil informasi nota induk
        $query_order = "SELECT * FROM orders WHERE id_order = '$id_order_aktif' LIMIT 1";
        $result_order = $koneksi->query($query_order);
        
        if ($result_order && $result_order->num_rows > 0) {
            $order_found = true;
            $order_data = $result_order->fetch_assoc();

            // Rincian semua produk melalui order_detail, join ke produk dan kategori
            $query_items = "SELECT od.*, p.nama_produk, p.kode_produk, k.nama_kategori 
                            FROM order_detail od
                            JOIN produk p ON od.id_produk = p.id_produk
                            LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
                            WHERE od.id_order = '$id_order_aktif'";
            $result_items = $koneksi->query($query_items);
            
            while ($item = $result_items->fetch_assoc()) {
                $items_data[] = $item;
            }
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
        .btn-olive {
            background-color: var(--accent-olive);
            color: white;
            border: none;
        }
        .btn-olive:hover {
            background-color: var(--accent-olive-hover);
            color: white;
        }
        
        /* --- STYLING TIMELINE PROGRESS TRACKER --- */
        .track-line {
            height: 4px;
            background-color: #e0e0e0;
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
            border: 3px solid #e0e0e0;
            color: #9e9e9e;
            font-size: 20px;
            margin: 0 auto 10px auto;
            transition: all 0.3s ease;
        }
        .track-step.active .track-icon {
            background-color: var(--accent-olive);
            border-color: var(--accent-olive);
            color: #fff;
            box-shadow: 0 0 12px rgba(125, 143, 55, 0.4);
        }
        .track-step.active .track-text {
            font-weight: 600;
            color: #212529;
        }
        .track-text {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>
    <script>document.querySelector('.navbar').style.backgroundColor = '#E0D2F0';</script>

    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="badge mb-2 text-dark px-3 py-2 rounded-pill" style="background-color: var(--navbar-purple)">Fitur Pelacakan Publik</span>
            <h2 class="fw-bold text-dark"><i class="fas fa-route text-muted me-2"></i>Lacak Status Pengiriman</h2>
            <p class="text-muted mx-auto" style="max-width: 550px;">Inputkan Nomor ID Transaksi unik atau Nama Pelanggan untuk memantau proses pesanan Anda secara berkala.</p>
        </div>

        <div class="row justify-content-center mb-5">
            <div class="col-md-7">
                <div class="card shadow-sm border-0 p-4 rounded-3 bg-white">
                    <form method="GET" action="lacak_pesanan.php">
                        <label class="form-label fw-bold text-secondary mb-2">Cari Berdasarkan ID Order / Nama Konsumen</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" name="keyword" class="form-control border-start-0 py-2" placeholder="Contoh: ORD002 atau Maudy..." value="<?php echo htmlspecialchars($search_keyword); ?>" required>
                            <button type="submit" class="btn btn-olive px-4 fw-bold"><i class="fas fa-paper-plane me-2"></i>Lacak</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if (!empty($search_keyword)): ?>
            <?php if ($order_found): 
                $current_status = strtolower($order_data['status_order'] ?? 'pending');
                $step = 1;
                if ($current_status == 'proses') { $step = 2; }
                elseif ($current_status == 'selesai') { $step = 3; }
            ?>
                
                <div class="card shadow-sm border-0 p-4 mb-4 bg-white rounded-3">
                    <h5 class="fw-bold text-dark mb-4 border-bottom pb-2"><i class="fas fa-tasks me-2 text-muted"></i>Progress Status Pesanan</h5>
                    
                    <?php if ($current_status == 'dibatalkan'): ?>
                        <!-- Tampilan Khusus Jika Pesanan Dibatalkan -->
                        <div class="alert alert-danger d-flex align-items-center my-2 py-3" role="alert">
                            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Pesanan Ini Telah Dibatalkan</h6>
                                <span class="small text-secondary">Mohon hubungi pihak sales atau admin Rahayu Karunia Utama untuk informasi lebih lanjut mengenai pembatalan ini.</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Tampilan Alur Tracker Normal Berdasarkan Status Admin -->
                        <div class="position-relative d-flex justify-content-between align-items-center my-3 px-md-5">
                            <div class="track-line"></div>
                            
                            <div class="track-step <?php echo ($step >= 1) ? 'active' : ''; ?>">
                                <div class="track-icon"><i class="fas fa-clock"></i></div>
                                <div class="track-text">Pending / Antri</div>
                            </div>
                            
                            <div class="track-step <?php echo ($step >= 2) ? 'active' : ''; ?>">
                                <div class="track-icon"><i class="fas fa-box-open"></i></div>
                                <div class="track-text">Sedang Diproses</div>
                            </div>
                            
                            <div class="track-step <?php echo ($step >= 3) ? 'active' : ''; ?>">
                                <div class="track-icon"><i class="fas fa-check-double"></i></div>
                                <div class="track-text">Selesai Diterima</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row g-4">
                    <div class="col-md-5">
                        <div class="card shadow-sm border-0 p-4 bg-white rounded-3 h-100">
                            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-file-invoice me-2 text-muted"></i>Informasi Nota</h5>
                            <table class="table table-borderless sm-text mb-0">
                                <tr>
                                    <td class="text-muted ps-0 py-1" style="width: 40%;">ID Transaksi</td>
                                    <td class="fw-bold text-dark py-1">: #<?php echo htmlspecialchars($order_data['id_order'] ?? ''); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-1">Nama Konsumen</td>
                                    <td class="fw-bold text-dark py-1">: <?php echo htmlspecialchars($order_data['nama_pelanggan'] ?? ''); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-1">Tanggal Transaksi</td>
                                    <td class="text-dark py-1">: <?php echo isset($order_data['tgl_pesan']) ? date('d M Y - H:i', strtotime($order_data['tgl_pesan'])) : '-'; ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-1">Kurir Ekspedisi</td>
                                    <td class="text-dark py-1">: <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($order_data['ekspedisi'] ?? '-'); ?></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0 py-1">Status Utama</td>
                                    <td class="py-1">: 
                                        <?php if($current_status == 'selesai'): ?>
                                            <span class="badge bg-success">Selesai</span>
                                        <?php elseif($current_status == 'proses'): ?>
                                            <span class="badge bg-primary">Diproses</span>
                                        <?php elseif($current_status == 'dibatalkan'): ?>
                                            <span class="badge bg-danger">Dibatalkan</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card shadow-sm border-0 p-4 bg-white rounded-3 h-100">
                            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-shopping-bag me-2 text-muted"></i>Item yang Dibeli</h5>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Detail Produk</th>
                                            <th class="text-center">Jumlah (Qty)</th>
                                            <th class="text-end">Harga Satuan</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $grand_total = 0;
                                        foreach ($items_data as $item): 
                                            $qty = $item['jumlah'] ?? 0;
                                            $harga = $item['harga_satuan'] ?? 0;
                                            $subtotal = $item['subtotal'] ?? ($qty * $harga);
                                            $grand_total += $subtotal;
                                            
                                            // Memastikan jika nama_kategori kosong/null tetap aman ditampilkan
                                            $kategori = !empty($item['nama_kategori']) ? $item['nama_kategori'] : 'Umum';
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($item['nama_produk']); ?></span>
                                                <small class="text-muted">Kode: <?php echo htmlspecialchars($item['kode_produk']); ?> | Kategori: <?php echo htmlspecialchars($kategori); ?></small>
                                            </td>
                                            <td class="text-center"><?php echo $qty; ?> Pcs</td>
                                            <td class="text-end">Rp <?php echo number_format($harga, 0, ',', '.'); ?></td>
                                            <td class="text-end fw-bold text-dark">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-light">
                                            <td colspan="3" class="text-end fw-bold">Total Pembayaran:</td>
                                            <td class="text-end fw-bold text-danger fs-5">Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-3">
                    <div class="py-3">
                        <i class="fas fa-search-minus fa-3x text-danger mb-3"></i>
                        <h4 class="text-secondary fw-bold">Data Tidak Ditemukan</h4>
                        <p class="text-muted mx-auto" style="max-width: 480px;">
                            Pesanan dengan keyword "<strong class="text-dark"><?php echo htmlspecialchars($search_keyword); ?></strong>" tidak terdaftar di database kami. Mohon periksa kembali kesesuaian ID Order atau ejaan nama yang Anda ketikkan.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <footer class="py-4 bg-white mt-5 border-top">
        <div class="container text-center">
            <small class="text-muted">PT Rahayu Karunia Utama &copy; Rahayu</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>