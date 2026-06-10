<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman: Hanya Admin yang boleh mengakses halaman ini
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Mengamankan ID dari parameter URL untuk mencegah SQL Injection
$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$ambildata = $koneksi->query("SELECT * FROM users WHERE id_user = '$id' AND role = 'sales'");
$data = $ambildata->fetch_assoc();

// Jika sales tidak ditemukan
if (!$data) {
    echo "<script>alert('Data sales tidak ditemukan!'); window.location='sales.php';</script>";
    exit;
}

if (isset($_POST['update_sales'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);

    // Logika jika password juga diganti
    if (!empty($_POST['password'])) {
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "UPDATE users SET nama_lengkap='$nama', username='$username', password='$pass' WHERE id_user='$id'";
    } else {
        $query = "UPDATE users SET nama_lengkap='$nama', username='$username' WHERE id_user='$id'";
    }

    if ($koneksi->query($query)) {
        echo "<script>alert('Data sales diperbarui!'); window.location='sales.php';</script>";
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
    <title>Edit Sales - Rahayu Admin</title>
    <?php include 'pwa_meta.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-sm mx-auto" style="max-width: 500px;">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Edit Akun Sales</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama_lengkap']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($data['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru (Opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ganti password">
                    </div>
                    <hr>
                    <button type="submit" name="update_sales" class="btn btn-primary w-100 shadow-sm">Simpan Perubahan</button>
                    <a href="sales.php" class="btn btn-light w-100 border text-muted mt-2">Batal</a>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>