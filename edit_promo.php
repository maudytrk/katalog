<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman: Pastikan hanya admin yang bisa masuk
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Proteksi SQL Injection dari method GET URL
$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$ambildata = $koneksi->query("SELECT * FROM promo WHERE id_promo = '$id'");
$data = $ambildata->fetch_assoc();

// Jika ID tidak valid atau data tidak ditemukan di database
if (!$data) {
    echo "<script>alert('Data promo tidak ditemukan!'); window.location='promo.php';</script>";
    exit;
}

if (isset($_POST['update_promo'])) {
    $nama_promo  = mysqli_real_escape_string($koneksi, $_POST['nama_promo']);
    $id_produk   = mysqli_real_escape_string($koneksi, $_POST['id_produk']);
    $diskon      = (int)$_POST['diskon'];
    $tgl_mulai   = mysqli_real_escape_string($koneksi, $_POST['tgl_mulai']);
    $tgl_selesai = mysqli_real_escape_string($koneksi, $_POST['tgl_selesai']);

    // Validasi Logika Tanggal
    if (strtotime($tgl_selesai) < strtotime($tgl_mulai)) {
        echo "<script>alert('Gagal! Tanggal selesai tidak boleh lebih awal dari tanggal mulai.'); window.history.back();</script>";
        exit;
    }

    $query = "UPDATE promo SET 
              nama_promo = '$nama_promo', 
              id_produk = '$id_produk', 
              diskon_persen = '$diskon', 
              tgl_mulai = '$tgl_mulai', 
              tgl_selesai = '$tgl_selesai' 
              WHERE id_promo = '$id'";

    if ($koneksi->query($query)) {
        echo "<script>alert('Promo berhasil diperbarui!'); window.location='promo.php';</script>";
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
    <title>Edit Promo - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-sm mx-auto" style="max-width: 600px;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Edit Promo</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Promo</label>
                        <input type="text" name="nama_promo" class="form-control" value="<?= htmlspecialchars($data['nama_promo']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Produk</label>
                        <select name="id_produk" class="form-select" required>
                            <?php 
                            $produk = $koneksi->query("SELECT * FROM produk");
                            while($p = $produk->fetch_assoc()):
                            ?>
                            <option value="<?= $p['id_produk']; ?>" <?= ($p['id_produk'] == $data['id_produk']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($p['nama_produk']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Diskon (%)</label>
                        <input type="number" name="diskon" class="form-control" value="<?= htmlspecialchars($data['diskon_persen']); ?>" min="1" max="100" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($data['tgl_mulai']); ?>" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control" value="<?= htmlspecialchars($data['tgl_selesai']); ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="update_promo" class="btn btn-primary w-100 shadow-sm">Simpan Perubahan</button>
                    <a href="promo.php" class="btn btn-light w-100 border text-muted mt-2">Batal</a>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>