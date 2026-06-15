<?php
session_start();
include 'koneksi.php';

// Pastikan yang mengakses halaman ini sudah login
if (!isset($_SESSION['login']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['submit_ganti_password'])) {
    $id_user = $_SESSION['user_id'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Cek apakah password baru sama dengan kolom konfirmasinya
    if ($password_baru !== $konfirmasi_password) {
        $_SESSION['password_status'] = 'error';
        $_SESSION['password_message'] = 'Konfirmasi password tidak cocok dengan password baru!';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Cek minimal panjang karakter (6 Karakter)
    if (strlen($password_baru) < 6) {
        $_SESSION['password_status'] = 'error';
        $_SESSION['password_message'] = 'Password baru minimal harus 6 karakter!';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Hash (Enkripsi) password baru dengan BCRYPT agar sangat aman
    $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

    // Proses Simpan ke Database
    $update = mysqli_query($koneksi, "UPDATE users SET password = '$password_hash' WHERE id_user = '$id_user'");

    if ($update) {
        // Jika berhasil, simpan ke session dan redirect ke halaman sebelumnya
        $_SESSION['password_status'] = 'success';
        $_SESSION['password_message'] = 'Password Anda telah berhasil diubah. Silakan login kembali dengan password baru.';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        $_SESSION['password_status'] = 'error';
        $_SESSION['password_message'] = 'Terjadi kesalahan sistem, gagal mengubah password!';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    header("Location: index.php");
}
?>