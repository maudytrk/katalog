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

        $query_foto = $koneksi->query("SELECT nama_file FROM produk_foto WHERE id_produk = '$id_produk'");

        // Cek apakah produk memiliki promo aktif
        $has_promo = isset($row['diskon_persen']) && $row['diskon_persen'] > 0;

        // Naikkan jumlah statistik klik karena produk sedang dilihat detailnya
        $koneksi->query("UPDATE produk SET jumlah_klik = jumlah_klik + 1 WHERE id_produk = '$id_produk'");

        $foto_utama = !empty($row['foto']) ? htmlspecialchars($row['foto']) : 'no-image.jpg';
?>

        <div class="row">
            <div class="col-md-5 mb-3 mb-md-0 text-center position-relative">
                <?php if ($has_promo): ?>
                    <div class="position-absolute top-0 end-0 m-3" style="z-index: 10;">
                        <span class="badge bg-danger text-white fw-bold px-3 py-2 rounded shadow-sm fs-6">
                            -<?php echo $row['diskon_persen']; ?>%
                        </span>
                    </div>
                <?php endif; ?>

                <div id="productSlider" class="carousel slide shadow-sm rounded border" data-bs-ride="carousel">
                    <div class="carousel-inner rounded" style="background-color: #E0E1F6;">
                        <?php
                        // Pastikan query_foto sudah dieksekusi sebelumnya
                        $jumlah_foto = $query_foto->num_rows;

                        if ($jumlah_foto > 0) {
                            $i = 0;
                            while ($foto = $query_foto->fetch_assoc()) {
                                $active = ($i == 0) ? 'active' : '';
                        ?>
                                <div class="carousel-item <?php echo $active; ?>">
                                    <img src="assets/img/<?php echo htmlspecialchars($foto['nama_file']); ?>"
                                        class="d-block w-100"
                                        alt="<?php echo htmlspecialchars($row['nama_produk']); ?>"
                                        style="height: 380px; width: 100%; object-fit: contain; background-color: #f8f9fa;">
                                </div>
                        <?php
                                $i++;
                            }
                        } else {
                            // Jika tidak ada data di tabel produk_foto, tampilkan gambar default
                            echo '<div class="carousel-item active"><img src="assets/img/no-image.jpg" class="d-block w-100" style="height: 380px; object-fit: cover;"></div>';
                        }
                        ?>
                    </div>

                    <?php if ($jumlah_foto > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#productSlider" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon p-3 rounded-circle" aria-hidden="true" style="background-color: #241F48;"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#productSlider" data-bs-slide="next">
                            <span class="carousel-control-next-icon p-3 rounded-circle" aria-hidden="true" style="background-color: #241F48;"></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-7 d-flex flex-column justify-content-between p-3 p-md-4">
                <div>
                    <span class="badge mb-2" style="background-color: #6C4773;"><?php echo htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?></span>
                    <h3 class="fw-bold mb-1" style="color: #241F48;"><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
                    <p class="text-muted small mb-3">Kode Produk: <span class="fw-semibold"><?php echo htmlspecialchars($row['kode_produk'] ?? '-'); ?></span></p>

                    <div class="mb-3 p-3 rounded" style="background-color: #F8F9FA; border-left: 4px solid #6C4773;">
                        <?php if ($has_promo):
                            $harga_akhir = $row['harga'] - ($row['harga'] * ($row['diskon_persen'] / 100));
                        ?>
                            <span class="text-muted text-decoration-line-through small d-block mb-1">Harga Normal: Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></span>
                            <h3 class="fw-bold mb-0" style="color: #6C4773;">Rp <?php echo number_format($harga_akhir, 0, ',', '.'); ?></h3>
                        <?php else: ?>
                            <h3 class="fw-bold mb-0" style="color: #241F48;">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></h3>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold mb-2" style="color: #241F48;"><i class="bi bi-info-circle me-1"></i> Deskripsi Produk:</h6>
                        <div class="text-muted small border rounded p-2" style="max-height: 200px; overflow-y: auto; text-align: justify; line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($row['deskripsi'])); ?>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center border-top border-bottom py-3 mb-4">
                            <span class="fw-semibold" style="color: #B0B7CA;">Ketersediaan Stok:</span>
                            <?php if ($row['stok'] > 0): ?>
                                <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle me-1"></i> Tersedia (<?php echo $row['stok']; ?> pcs)</span>
                            <?php else: ?>
                                <span class="badge bg-danger px-3 py-2"><i class="bi bi-x-circle me-1"></i> Stok Habis</span>
                            <?php endif; ?>
                        </div>

                        <h6 class="fw-bold mb-3" style="color: #241F48;">Aksi & Pembelian:</h6>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-start">

                            <?php if (!empty($row['link_shopee'])): ?>
                                <a href="redirect.php?id_produk=<?php echo $row['id_produk']; ?>&type=marketplace&platform=shopee" target="_blank" class="btn btn-sm text-white px-3" style="background-color: #ee4d2d;">
                                    <i class="bi bi-bag-fill me-1"></i> Shopee
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($row['link_tiktok'])): ?>
                                <a href="redirect.php?id_produk=<?php echo $row['id_produk']; ?>&type=marketplace&platform=tiktok" target="_blank" class="btn btn-sm btn-dark px-3">
                                    <i class="bi bi-tiktok me-1"></i> TikTok Shop
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($row['link_lazada'])): ?>
                                <a href="redirect.php?id_produk=<?php echo $row['id_produk']; ?>&type=marketplace&platform=lazada" target="_blank" class="btn btn-sm text-white px-3" style="background-color: #0F136D;">
                                    <i class="bi bi-heart-fill me-1"></i> Lazada
                                </a>
                            <?php endif; ?>

                            <?php if (empty($row['link_shopee']) && empty($row['link_tiktok']) && empty($row['link_lazada'])): ?>
                                <span class="text-muted small italic">Link e-commerce belum tersedia.</span>
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