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
        $_SESSION['gagal'] = "Username sudah terdaftar!";
        header("Location: sales.php");
        exit;
    } else {
        $query = "INSERT INTO users (nama_lengkap, username, password, role) 
                  VALUES ('$nama', '$username', '$password', '$role')";

        if ($koneksi->query($query)) {
            $_SESSION['sukses'] = "Sales baru berhasil didaftarkan!";
            header("Location: sales.php");
            exit;
        } else {
            $_SESSION['gagal'] = "Error: " . $koneksi->error;
            header("Location: sales.php");
            exit;
        }
    }
}
?>