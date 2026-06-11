<?php
session_start();
include 'koneksi.php';

// Proteksi halaman admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Mengamankan parameter ID dari URL
$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// --- LOGIKA HAPUS FOTO (DIPINDAHKAN KE ATAS) ---
if (isset($_GET['hapus_foto'])) {
    $id_foto = mysqli_real_escape_string($koneksi, $_GET['hapus_foto']);
    $f = $koneksi->query("SELECT nama_file FROM produk_foto WHERE id_foto = '$id_foto'")->fetch_assoc();

    if ($f) {
        if (file_exists("assets/img/" . $f['nama_file'])) {
            unlink("assets/img/" . $f['nama_file']);
        }
        $koneksi->query("DELETE FROM produk_foto WHERE id_foto = '$id_foto'");
    }
    // Redirect kembali ke halaman edit agar query string hapus bersih
    echo "<script>window.location='edit_produk.php?id=$id';</script>";
    exit;
}

// Ambil data produk utama
$ambildata = $koneksi->query("SELECT * FROM produk WHERE id_produk = '$id'");
$data = $ambildata->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data produk tidak ditemukan!'); window.location='produk.php';</script>";
    exit;
}

// AMBIL KATEGORI YANG SAAT INI DIMILIKI PRODUK INI (UNTUK CHECKBOX)
$kategori_saat_ini = [];
$query_kat_lama = $koneksi->query("SELECT id_kategori FROM produk_kategori WHERE id_produk = '$id'");
while ($kl = $query_kat_lama->fetch_assoc()) {
    $kategori_saat_ini[] = $kl['id_kategori'];
}


// --- LOGIKA UPDATE DATA ---
if (isset($_POST['update'])) {
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $harga     = (int)$_POST['harga'];
    $stok      = (int)$_POST['stok'];
    $tiktok    = mysqli_real_escape_string($koneksi, $_POST['link_tiktok']);
    $shopee    = mysqli_real_escape_string($koneksi, $_POST['link_shopee']);
    $lazada    = mysqli_real_escape_string($koneksi, $_POST['link_lazada']);

    // 1. Update data teks produk
    $query_update = "UPDATE produk SET nama_produk='$nama', deskripsi='$deskripsi', 
                     harga='$harga', stok='$stok', link_tiktok='$tiktok', link_shopee='$shopee', 
                     link_lazada='$lazada' WHERE id_produk='$id'";

    if ($koneksi->query($query_update)) {
        // 2. UPDATE MULTI-KATEGORI
        $kategori_baru_arr = isset($_POST['kategori']) ? $_POST['kategori'] : [];

        if (!empty($kategori_baru_arr)) {
            $koneksi->query("DELETE FROM produk_kategori WHERE id_produk = '$id'");
            foreach ($kategori_baru_arr as $id_kat_baru) {
                $id_kat_clean = mysqli_real_escape_string($koneksi, $id_kat_baru);
                $koneksi->query("INSERT INTO produk_kategori (id_produk, id_kategori) VALUES ('$id', '$id_kat_clean')");
            }
        }

        // 3. Proses Tambah Foto Baru
        $foto_utama_set = false;
        $cek_foto_utama = $koneksi->query("SELECT foto FROM produk WHERE id_produk = '$id'")->fetch_assoc();
        if (!empty($cek_foto_utama['foto']) && $cek_foto_utama['foto'] !== 'no-image.jpg') {
            $foto_utama_set = true;
        }

        if (isset($_FILES['foto_baru']['name']) && is_array($_FILES['foto_baru']['name'])) {
            $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg', 'webp');
            $total_files = count($_FILES['foto_baru']['name']);

            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['foto_baru']['error'][$i] === UPLOAD_ERR_OK) {
                    $nama_file = $_FILES['foto_baru']['name'][$i];
                    $tmp_name = $_FILES['foto_baru']['tmp_name'][$i];

                    $x = explode('.', $nama_file);
                    $ekstensi = strtolower(end($x));

                    if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
                        $nama_foto_baru = date('YmdHis') . '-' . rand(1000, 9999) . '.' . $ekstensi;

                        if (move_uploaded_file($tmp_name, 'assets/img/' . $nama_foto_baru)) {
                            $koneksi->query("INSERT INTO produk_foto (id_produk, nama_file) VALUES ('$id', '$nama_foto_baru')");

                            if (!$foto_utama_set) {
                                $koneksi->query("UPDATE produk SET foto = '$nama_foto_baru' WHERE id_produk = '$id'");
                                $foto_utama_set = true;
                            }
                        }
                    }
                }
            }
        }
        
        $_SESSION['sukses'] = "Data produk berhasil diperbarui!";
    } else {
        $_SESSION['gagal'] = "Gagal memperbarui data: " . mysqli_error($koneksi);
    }
    
    header("Location: produk.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Rahayu Admin</title>
    <?php include 'pwa_meta.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <style>
        body {
            background-color: #F8F7FF;
        }

        .card-edit {
            border-radius: 15px;
            border: none;
        }

        .btn-update {
            background-color: #5A4E8C;
            color: white;
        }

        .btn-update:hover {
            background-color: #463c6e;
            color: white;
        }

        /* CSS UNTUK PREVIEW GAMBAR & TOMBOL HAPUS (X) SEPERTI DI FORM TAMBAH */
        .preview-wrapper {
            position: relative;
            width: 80px;
            height: 80px;
            margin-right: 8px;
            margin-bottom: 8px;
        }

        .preview-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #E0E1F6;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .preview-remove-btn {
            position: absolute;
            top: -7px;
            right: -7px;
            background: #FF4444;
            color: white;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            font-size: 14px;
            line-height: 20px;
            text-align: center;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            border: 2px solid white;
            transition: all 0.2s;
            z-index: 10;
        }

        .preview-remove-btn:hover {
            background: #CC0000;
            transform: scale(1.1);
        }
    </style>
</head>

<body>
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-edit shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="mb-0 fw-bold" style="color: #231F48;"><i class="fas fa-edit text-warning me-2"></i>Edit Data Produk: <?php echo htmlspecialchars($data['kode_produk']); ?></h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="color: #231F48;">Nama Produk</label>
                                <input type="text" name="nama" class="form-control bg-light" value="<?php echo htmlspecialchars($data['nama_produk']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="color: #231F48;">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control bg-light" rows="4" required><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold" style="color: #231F48;">Kategori <span class="text-danger small">(Bisa > 1)</span></label>
                                    <div class="border rounded p-2 form-control bg-light" style="max-height: 120px; overflow-y: auto;">
                                        <?php
                                        $kat = $koneksi->query("SELECT * FROM kategori");
                                        while ($k = $kat->fetch_assoc()) {
                                            // Cek apakah ID Kategori ini ada di dalam array $kategori_saat_ini
                                            $checked = in_array($k['id_kategori'], $kategori_saat_ini) ? 'checked' : '';
                                            echo "<div class='form-check mb-1'>
                                                    <input class='form-check-input' type='checkbox' name='kategori[]' value='" . $k['id_kategori'] . "' id='kat_" . $k['id_kategori'] . "' $checked>
                                                    <label class='form-check-label small text-dark fw-medium' style='cursor:pointer;' for='kat_" . $k['id_kategori'] . "'>" . htmlspecialchars($k['nama_kategori']) . "</label>
                                                  </div>";
                                        }
                                        ?>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.75rem;">*Minimal pilih 1 kategori.</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold" style="color: #231F48;">Harga (Rp)</label>
                                    <input type="number" name="harga" class="form-control bg-light" value="<?php echo htmlspecialchars($data['harga']); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold" style="color: #231F48;">Stok</label>
                                    <input type="number" name="stok" class="form-control bg-light" value="<?php echo htmlspecialchars($data['stok']); ?>" required>
                                </div>
                            </div>

                            <hr class="my-4 text-muted">

                            <div class="mb-3">
                                <label class="form-label fw-bold" style="color: #231F48;"><i class="fas fa-images me-1"></i>Foto Produk Saat Ini:</label>
                                <div class="d-flex flex-wrap gap-2 mb-3 p-3 rounded" style="background-color: #f1f2f6; border: 1px solid #dcdde1;">
                                    <?php
                                    // Query ambil foto dari tabel baru (produk_foto)
                                    $foto_query = $koneksi->query("SELECT * FROM produk_foto WHERE id_produk = '$id'");
                                    if ($foto_query->num_rows > 0) {
                                        while ($f = $foto_query->fetch_assoc()): ?>
                                            <div class="preview-wrapper">
                                                <img src="assets/img/<?= htmlspecialchars($f['nama_file']) ?>">
                                                <a href="edit_produk.php?id=<?= $id ?>&hapus_foto=<?= $f['id_foto'] ?>" class="preview-remove-btn text-decoration-none" onclick="return confirm('Hapus foto ini dari sistem?')">×</a>
                                            </div>
                                    <?php endwhile;
                                    } else {
                                        echo "<span class='text-muted small italic'>Belum ada foto. Silakan tambahkan di bawah.</span>";
                                    }
                                    ?>
                                </div>

                                <label class="form-label fw-semibold" style="color: #231F48;">Tambah Foto Baru <span class="text-muted small">(Opsional)</span></label>
                                <input type="file" name="foto_baru[]" class="form-control bg-light" id="fotoInput" accept="image/*" multiple>
                                <div class="form-text text-muted mb-2">Tekan CTRL (Laptop) atau Tahan layar (HP) untuk memilih banyak gambar.</div>

                                <div id="previewContainerMandiri" class="d-flex flex-wrap gap-2 mt-2"></div>
                            </div>

                            <hr class="my-4 text-muted">

                            <h6 class="fw-bold mb-3" style="color: #6B4773;"><i class="fas fa-link me-1"></i> Link Marketplace</h6>
                            <div class="mb-2"><input type="url" name="link_tiktok" class="form-control bg-light" placeholder="Link TikTok" value="<?php echo htmlspecialchars($data['link_tiktok']); ?>"></div>
                            <div class="mb-2"><input type="url" name="link_shopee" class="form-control bg-light" placeholder="Link Shopee" value="<?php echo htmlspecialchars($data['link_shopee']); ?>"></div>
                            <div class="mb-4"><input type="url" name="link_lazada" class="form-control bg-light" placeholder="Link Lazada" value="<?php echo htmlspecialchars($data['link_lazada']); ?>"></div>

                            <div class="mt-4 border-top pt-4 text-end">
                                <a href="produk.php" class="btn btn-light px-4 border shadow-sm fw-semibold me-2">Batal</a>
                                <button type="submit" name="update" class="btn btn-update px-5 shadow-sm fw-bold"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script>
        // SCRIPT LIVE PREVIEW GAMBAR (Sama persis dengan di tambah produk)
        let selectedFiles = [];
        const fotoInput = document.getElementById('fotoInput');
        const previewContainerMandiri = document.getElementById('previewContainerMandiri');

        fotoInput.addEventListener('change', function(e) {
            selectedFiles = Array.from(e.target.files);
            renderPreviewMandiri();
        });

        function renderPreviewMandiri() {
            previewContainerMandiri.innerHTML = '';

            selectedFiles.forEach((file, index) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'preview-wrapper';

                const imgUrl = URL.createObjectURL(file);
                const img = document.createElement('img');
                img.src = imgUrl;
                img.onload = () => URL.revokeObjectURL(imgUrl);

                const removeBtn = document.createElement('span');
                removeBtn.innerHTML = '&times;';
                removeBtn.className = 'preview-remove-btn';
                removeBtn.title = 'Batalkan gambar ini';

                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    selectedFiles.splice(index, 1);

                    const dataTransfer = new DataTransfer();
                    selectedFiles.forEach(f => dataTransfer.items.add(f));
                    fotoInput.files = dataTransfer.files;

                    renderPreviewMandiri();
                });

                wrapper.appendChild(img);
                wrapper.appendChild(removeBtn);
                previewContainerMandiri.appendChild(wrapper);
            });
        }
    </script>
</body>

</html>