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

// --- LOGIKA HAPUS FOTO (DIPINDAHKAN KE ATAS, LUAR BLOK POST) ---
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

// Ambil data produk untuk form
$ambildata = $koneksi->query("SELECT * FROM produk WHERE id_produk = '$id'");
$data = $ambildata->fetch_assoc();

if (!$data) {
    echo "<script>alert('Data produk tidak ditemukan!'); window.location='produk.php';</script>";
    exit;
}

// --- LOGIKA UPDATE DATA ---
if (isset($_POST['update'])) {
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $id_kat    = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga     = (int)$_POST['harga'];
    $stok      = (int)$_POST['stok'];
    $tiktok    = mysqli_real_escape_string($koneksi, $_POST['link_tiktok']);
    $shopee    = mysqli_real_escape_string($koneksi, $_POST['link_shopee']);
    $lazada    = mysqli_real_escape_string($koneksi, $_POST['link_lazada']);

    // Update data teks produk
    $query_update = "UPDATE produk SET nama_produk='$nama', deskripsi='$deskripsi', id_kategori='$id_kat', 
                     harga='$harga', stok='$stok', link_tiktok='$tiktok', link_shopee='$shopee', 
                     link_lazada='$lazada' WHERE id_produk='$id'";

    $koneksi->query($query_update);

    // Proses Tambah Foto Baru
    if (!empty($_FILES['foto_baru']['name'][0])) {
        foreach ($_FILES['foto_baru']['tmp_name'] as $key => $tmp_name) {
            $nama_file_asli = $_FILES['foto_baru']['name'][$key];
            $ext = pathinfo($nama_file_asli, PATHINFO_EXTENSION);
            $nama_foto_baru = date('dmyhis') . '-' . uniqid() . '.' . $ext;

            if (move_uploaded_file($tmp_name, 'assets/img/' . $nama_foto_baru)) {
                $koneksi->query("INSERT INTO produk_foto (id_produk, nama_file) VALUES ('$id', '$nama_foto_baru')");
            }
        }
    }

    echo "<script>alert('Data berhasil diperbarui!'); window.location='produk.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Rahayu Admin</title>
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
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-edit shadow-sm mb-5">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Edit Data Produk: <?php echo htmlspecialchars($data['kode_produk']); ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Nama Produk</label>
                                <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($data['nama_produk']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="4"><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Kategori</label>
                                    <select name="kategori" class="form-select">
                                        <?php
                                        $kat = $koneksi->query("SELECT * FROM kategori");
                                        while ($k = $kat->fetch_assoc()):
                                        ?>
                                            <option value="<?php echo $k['id_kategori']; ?>" <?php echo ($k['id_kategori'] == $data['id_kategori']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($k['nama_kategori']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Harga (Rp)</label>
                                    <input type="number" name="harga" class="form-control" value="<?php echo htmlspecialchars($data['harga']); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Stok</label>
                                    <input type="number" name="stok" class="form-control" value="<?php echo htmlspecialchars($data['stok']); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Foto Produk Saat Ini:</label>
                                <div class="row g-2 mb-3">
                                    <?php
                                    // Query ambil foto dari tabel baru (produk_foto)
                                    $foto_query = $koneksi->query("SELECT * FROM produk_foto WHERE id_produk = '$id'");
                                    while ($f = $foto_query->fetch_assoc()): ?>
                                        <div class="col-md-2 text-center position-relative">
                                            <img src="assets/img/<?= htmlspecialchars($f['nama_file']) ?>"
                                                class="img-thumbnail" style="height: 100px; width: 100px; object-fit: cover;">
                                            <a href="edit_produk.php?id=<?= $id ?>&hapus_foto=<?= $f['id_foto'] ?>"
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0"
                                                style="padding: 0px 6px;"
                                                onclick="return confirm('Hapus foto ini?')">×</a>
                                        </div>
                                    <?php endwhile; ?>
                                </div>

                                <label class="form-label">Tambah Foto Baru (Bisa pilih lebih dari satu):</label>
                                <input type="file" name="foto_baru[]" class="form-control" id="fotoInput" accept="image/*" multiple>

                                <div id="previewContainer" class="row g-2 mt-3"></div>
                                <hr>
                                <h6>Link Marketplace</h6>
                                <div class="mb-2"><input type="url" name="link_tiktok" class="form-control" placeholder="Link TikTok" value="<?php echo htmlspecialchars($data['link_tiktok']); ?>"></div>
                                <div class="mb-2"><input type="url" name="link_shopee" class="form-control" placeholder="Link Shopee" value="<?php echo htmlspecialchars($data['link_shopee']); ?>"></div>
                                <div class="mb-3"><input type="url" name="link_lazada" class="form-control" placeholder="Link Lazada" value="<?php echo htmlspecialchars($data['link_lazada']); ?>"></div>

                                <div class="mt-4">
                                    <button type="submit" name="update" class="btn btn-update px-4">Update Data</button>
                                    <a href="produk.php" class="btn btn-light px-4">Batal</a>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inisialisasi DataTransfer object
        let dt = new DataTransfer();

        document.getElementById('fotoInput').addEventListener('change', function(e) {
            const previewContainer = document.getElementById('previewContainer');

            for (let file of this.files) {
                dt.items.add(file); // Tambahkan file ke object transfer

                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-2 text-center position-relative';
                    col.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" style="height: 100px; width: 100px; object-fit: cover;">
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" style="padding: 0px 6px;">×</button>
                `;

                    // Logika Hapus Preview & File
                    col.querySelector('button').onclick = function() {
                        const fileName = file.name;
                        // Hapus file dari DataTransfer object
                        for (let i = 0; i < dt.items.length; i++) {
                            if (dt.items[i].getAsFile().name === fileName) {
                                dt.items.remove(i);
                                break;
                            }
                        }
                        // Update file pada input
                        document.getElementById('fotoInput').files = dt.files;
                        col.remove();
                    };
                    previewContainer.appendChild(col);
                }
                reader.readAsDataURL(file);
            }
            // Sync input dengan DataTransfer
            this.files = dt.files;
        });
    </script>
</body>

</html>