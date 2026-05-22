<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman: Hanya Admin yang boleh mengakses script ini
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['tambah_sales'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Enkripsi password aman
    $role     = 'sales';

    // Cek apakah username sudah dipakai
    $cek = $koneksi->query("SELECT * FROM users WHERE username = '$username'");
    if ($cek->num_rows > 0) {
        echo "<script>alert('Username sudah terdaftar!'); window.location='sales.php';</script>";
    } else {
        $query = "INSERT INTO users (nama_lengkap, username, password, role) 
                  VALUES ('$nama', '$username', '$password', '$role')";

        if ($koneksi->query($query)) {
            echo "<script>alert('Sales baru berhasil didaftarkan!'); window.location='sales.php';</script>";
        } else {
            echo "Error: " . $koneksi->error;
        }
    }
}
?>