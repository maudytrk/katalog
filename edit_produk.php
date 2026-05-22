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
$ambildata = $koneksi->query("SELECT * FROM produk WHERE id_produk = '$id'");
$data = $ambildata->fetch_assoc();

// Jika data tidak ditemukan, kembalikan ke manajemen produk
if (!$data) {
    echo "<script>alert('Data produk tidak ditemukan!'); window.location='produk.php';</script>";
    exit;
}

if (isset($_POST['update'])) {
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $id_kat    = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga     = (int)$_POST['harga'];
    $stok      = (int)$_POST['stok'];
    $tiktok    = mysqli_real_escape_string($koneksi, $_POST['link_tiktok']);
    $shopee    = mysqli_real_escape_string($koneksi, $_POST['link_shopee']);
    $lazada    = mysqli_real_escape_string($koneksi, $_POST['link_lazada']);

    $nama_foto = $_FILES['foto']['name'];
    if ($nama_foto != "") {
        // Validasi Ekstensi Gambar pada Fitur Edit
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg');
        $x = explode('.', $nama_foto);
        $ekstensi = strtolower(end($x));
        
        if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
            // Hapus foto lama jika bukan gambar bawaan sistem
            if ($data['foto'] != "no-image.jpg" && file_exists("assets/img/" . $data['foto'])) {
                unlink("assets/img/" . $data['foto']);
            }
            
            $file_tmp = $_FILES['foto']['tmp_name'];
            $nama_foto_baru = date('dmyhis') . '-' . uniqid() . '.' . $ekstensi;
            move_uploaded_file($file_tmp, 'assets/img/' . $nama_foto_baru);
            
            $query = "UPDATE produk SET nama_produk='$nama', deskripsi='$deskripsi', id_kategori='$id_kat', harga='$harga', stok='$stok', link_tiktok='$tiktok', link_shopee='$shopee', link_lazada='$lazada', foto='$nama_foto_baru' WHERE id_produk='$id'";
        } else {
            echo "<script>alert('Ekstensi gambar hanya boleh png, jpg, jpeg'); window.location='edit_produk.php?id=$id';</script>";
            exit;
        }
    } else {
        // Jika tidak mengganti foto
        $query = "UPDATE produk SET nama_produk='$nama', deskripsi='$deskripsi', id_kategori='$id_kat', harga='$harga', stok='$stok', link_tiktok='$tiktok', link_shopee='$shopee', link_lazada='$lazada' WHERE id_produk='$id'";
    }

    if ($koneksi->query($query)) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location='produk.php';</script>";
    } else {
        echo "Error: " . $koneksi->error;
    }
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
        body { background-color: #F8F7FF; }
        .card-edit { border-radius: 15px; border: none; }
        .btn-update { background-color: #5A4E8C; color: white; }
        .btn-update:hover { background-color: #463c6e; color: white; }
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
                                        while($k = $kat->fetch_assoc()):
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
                                <label class="form-label">Foto Produk (Biarkan kosong jika tidak diganti)</label>
                                <div class="mb-2"><img src="assets/img/<?php echo $data['foto']; ?>" width="100" class="img-thumbnail" alt="Foto Produk"></div>
                                <input type="file" name="foto" class="form-control">
                            </div>
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
</body>
</html>