<?php
include 'koneksi.php';

// Pastikan parameter ID produk dikirimkan dan valid
if (isset($_GET['id'])) {
    $id_produk = mysqli_real_escape_string($koneksi, $_GET['id']);
    $today = date('Y-m-d');
    
    // PERBAIKAN QUERY: Menambahkan LEFT JOIN ke tabel promo berdasarkan tanggal aktif saat ini
    $query = "SELECT p.*, k.nama_kategori, pr.diskon_persen FROM produk p 
              LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
              LEFT JOIN promo pr ON p.id_produk = pr.id_produk AND '$today' BETWEEN pr.tgl_mulai AND pr.tgl_selesai
              WHERE p.id_produk = '$id_produk' LIMIT 1";
              
    $result = $koneksi->query($query);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Cek apakah produk memiliki promo aktif
        $has_promo = isset($row['diskon_persen']) && $row['diskon_persen'] > 0;
        
        // Naikkan jumlah statistik klik karena produk sedang dilihat detailnya
        $koneksi->query("UPDATE produk SET jumlah_klik = jumlah_klik + 1 WHERE id_produk = '$id_produk'");
        ?>
        
        <div class="row">
            <div class="col-md-5 mb-3 mb-md-0 text-center position-relative">
                <?php if ($has_promo): ?>
                <div class="position-absolute top-0 end-0 m-2" style="z-index: 2;">
                    <span class="badge bg-danger text-white fw-bold px-3 py-2 rounded shadow-sm fs-6">
                        -<?php echo $row['diskon_persen']; ?>%
                    </span>
                </div>
                <?php endif; ?>

                <img src="assets/img/<?php echo !empty($row['foto']) ? htmlspecialchars($row['foto']) : 'no-image.jpg'; ?>" 
                     class="img-fluid rounded shadow-sm border" 
                     alt="<?php echo htmlspecialchars($row['nama_produk']); ?>"
                     style="max-height: 350px; object-fit: cover; width: 100%;">
            </div>
            
            <div class="col-md-7 d-flex flex-column justify-content-between">
                <div>
                    <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?></span>
                    <h3 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
                    <p class="text-muted small mb-3">Kode Produk: <span class="fw-semibold"><?php echo htmlspecialchars($row['kode_produk'] ?? '-'); ?></span></p>
                    
                    <div class="mb-3">
                        <?php if ($has_promo): 
                            $harga_akhir = $row['harga'] - ($row['harga'] * ($row['diskon_persen'] / 100));
                        ?>
                            <span class="text-muted text-decoration-line-through small d-block mb-1">Harga Normal: Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></span>
                            <h4 class="text-danger fw-bold mb-3">Rp <?php echo number_format($harga_akhir, 0, ',', '.'); ?></h4>
                        <?php else: ?>
                            <h4 class="text-primary fw-bold mb-3">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></h4>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> Deskripsi Produk:</h6>
                        <p class="text-muted small" style="white-space: pre-line; text-align: justify;">
                            <?php echo htmlspecialchars($row['deskripsi']); ?>
                        </p>
                    </div>
                </div>

                <div>
                    <div class="d-flex justify-content-between align-items-center border-top border-bottom py-2 mb-3">
                        <span class="text-muted small">Ketersediaan Stok:</span>
                        <?php if ($row['stok'] > 0): ?>
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Tersedia (<?php echo $row['stok']; ?> pcs)</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Stok Habis</span>
                        <?php endif; ?>
                    </div>
                    
                    <h6>Beli/Pesan via Marketplace:</h6>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        
                        <?php if(!empty($row['link_shopee'])): ?>
                            <a href="redirect.php?id_produk=<?php echo $row['id_produk']; ?>&type=marketplace&platform=shopee" target="_blank" class="btn btn-sm text-white px-3" style="background-color: #ee4d2d;">
                                <i class="bi bi-bag-fill me-1"></i> Shopee
                            </a>
                        <?php endif; ?>
                        
                        <?php if(!empty($row['link_tiktok'])): ?>
                            <a href="redirect.php?id_produk=<?php echo $row['id_produk']; ?>&type=marketplace&platform=tiktok" target="_blank" class="btn btn-sm btn-dark px-3">
                                <i class="bi bi-tiktok me-1"></i> TikTok Shop
                            </a>
                        <?php endif; ?>
                        
                        <?php if(!empty($row['link_lazada'])): ?>
                            <a href="redirect.php?id_produk=<?php echo $row['id_produk']; ?>&type=marketplace&platform=lazada" target="_blank" class="btn btn-sm btn-primary px-3">
                                <i class="bi bi-heart-fill me-1"></i> Lazada
                            </a>
                        <?php endif; ?>

                        <?php if(empty($row['link_shopee']) && empty($row['link_tiktok']) && empty($row['link_lazada'])): ?>
                            <span class="text-muted small italic">Link pembelian belum tersedia.</span>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>

        <?php
    } else {
        echo "<div class='text-center py-4 text-danger'><i class='bi bi-exclamation-triangle display-4'></i><p class='mt-2'>Data detail produk gagal dimuat.</p></div>";
    }
}
?>