<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id_produk = mysqli_real_escape_string($koneksi, $_GET['id']);
    $today = date('Y-m-d');

    $query = "SELECT p.*, k.nama_kategori, pr.diskon_persen FROM produk p 
              LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
              LEFT JOIN promo pr ON p.id_produk = pr.id_produk AND '$today' BETWEEN pr.tgl_mulai AND pr.tgl_selesai
              WHERE p.id_produk = '$id_produk' LIMIT 1";

    $result = $koneksi->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $query_foto = $koneksi->query("SELECT nama_file FROM produk_foto WHERE id_produk = '$id_produk'");
        $has_promo = isset($row['diskon_persen']) && $row['diskon_persen'] > 0;
        $koneksi->query("UPDATE produk SET jumlah_klik = jumlah_klik + 1 WHERE id_produk = '$id_produk'");

        $all_fotos = [];
        if ($query_foto->num_rows > 0) {
            while ($f = $query_foto->fetch_assoc()) {
                $all_fotos[] = $f['nama_file'];
            }
            $query_foto->data_seek(0);
        } else {
            $all_fotos[] = !empty($row['foto']) ? $row['foto'] : 'no-image.jpg';
        }
        $json_fotos = htmlspecialchars(json_encode($all_fotos), ENT_QUOTES, 'UTF-8');
?>

        <style>
            .zoomable-img {
                cursor: zoom-in;
                transition: transform 0.3s ease;
            }

            .zoomable-img:hover {
                transform: scale(1.02);
            }
        </style>

        <div class="row align-items-center">
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
                        $jumlah_foto = count($all_fotos);

                        if ($query_foto->num_rows > 0) {
                            $i = 0;
                            while ($foto = $query_foto->fetch_assoc()) {
                                $active = ($i == 0) ? 'active' : '';
                        ?>
                                <div class="carousel-item <?php echo $active; ?>">
                                    <img src="assets/img/<?php echo htmlspecialchars($foto['nama_file']); ?>"
                                        class="d-block w-100 zoomable-img"
                                        alt="<?php echo htmlspecialchars($row['nama_produk']); ?>"
                                        style="height: 380px; width: 100%; object-fit: contain; background-color: #f8f9fa;"
                                        onclick="(function(idx, arr){ var ov=document.createElement('div');ov.style.cssText='position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.9);z-index:99999999;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(5px);';var cl=document.createElement('span');cl.innerHTML='&times;';cl.style.cssText='position:absolute;top:20px;right:40px;font-size:40px;color:#fff;cursor:pointer;z-index:1000;';cl.onclick=function(){ov.remove();};ov.appendChild(cl);var gal=document.createElement('div');gal.style.cssText='position:relative;max-width:90vw;max-height:90vh;display:flex;align-items:center;justify-content:center;border-radius:10px;box-shadow:0 10px 25px rgba(0,0,0,0.5);overflow:hidden;';var im=document.createElement('img');im.src='assets/img/'+arr[idx];im.style.cssText='max-height:90vh;max-width:85vw;object-fit:contain;border-radius:10px;';gal.appendChild(im);if(arr.length>1){var btnCss='position:absolute;top:50%;transform:translateY(-50%);font-size:28px;color:#fff;cursor:pointer;user-select:none;background:rgba(0,0,0,0.6);width:50px;height:50px;display:flex;align-items:center;justify-content:center;border-radius:50%;box-shadow:0 0 10px rgba(0,0,0,0.3);z-index:999;transition:background 0.3s ease;';var pr=document.createElement('div');pr.innerHTML='&#10094;';pr.style.cssText=btnCss+'left:-15px;';pr.onmouseover=function(){this.style.background='rgba(0,0,0,0.8)';};pr.onmouseout=function(){this.style.background='rgba(0,0,0,0.6)';};pr.onclick=function(e){e.stopPropagation();idx=(idx-1+arr.length)%arr.length;im.src='assets/img/'+arr[idx];};var nx=document.createElement('div');nx.innerHTML='&#10095;';nx.style.cssText=btnCss+'right:-15px;';nx.onmouseover=function(){this.style.background='rgba(0,0,0,0.8)';};nx.onmouseout=function(){this.style.background='rgba(0,0,0,0.6)';};nx.onclick=function(e){e.stopPropagation();idx=(idx+1)%arr.length;im.src='assets/img/'+arr[idx];};gal.appendChild(pr);gal.appendChild(nx);}ov.appendChild(gal);ov.onclick=function(e){if(e.target===ov)ov.remove();};document.body.appendChild(ov); })(<?php echo $i; ?>, <?php echo $json_fotos; ?>)">
                                </div>
                        <?php
                                $i++;
                            }
                        } else {
                            echo '<div class="carousel-item active">
                                    <img src="assets/img/' . $all_fotos[0] . '" 
                                         class="d-block w-100 zoomable-img" 
                                         style="height: 380px; object-fit: cover;"
                                         onclick="(function(idx, arr){ var ov=document.createElement(\'div\');ov.style.cssText=\'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.9);z-index:99999999;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(5px);\';var cl=document.createElement(\'span\');cl.innerHTML=\'&times;\';cl.style.cssText=\'position:absolute;top:20px;right:40px;font-size:40px;color:#fff;cursor:pointer;z-index:1000;\';cl.onclick=function(){ov.remove();};ov.appendChild(cl);var gal=document.createElement(\'div\');gal.style.cssText=\'position:relative;max-width:90vw;max-height:90vh;display:flex;align-items:center;justify-content:center;border-radius:10px;box-shadow:0 10px 25px rgba(0,0,0,0.5);overflow:hidden;\';var im=document.createElement(\'img\');im.src=\'assets/img/\'+arr[idx];im.style.cssText=\'max-height:90vh;max-width:85vw;object-fit:contain;border-radius:10px;\';gal.appendChild(im);ov.appendChild(gal);ov.onclick=function(e){if(e.target===ov)ov.remove();};document.body.appendChild(ov); })(0, ' . $json_fotos . ')">
                                  </div>';
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
        </div>

<?php
    } else {
        echo "<div class='text-center py-4 text-danger'><i class='bi bi-exclamation-triangle display-4'></i><p class='mt-2'>Data detail produk gagal dimuat.</p></div>";
    }
}
?>