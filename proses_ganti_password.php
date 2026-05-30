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
        echo "<script>alert('Gagal! Konfirmasi password tidak cocok dengan password baru.'); window.history.back();</script>";
        exit;
    }

    // Cek minimal panjang karakter (6 Karakter)
    if (strlen($password_baru) < 6) {
        echo "<script>alert('Gagal! Password baru minimal harus 6 karakter!'); window.history.back();</script>";
        exit;
    }

    // Hash (Enkripsi) password baru dengan BCRYPT agar sangat aman
    $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

    // Proses Simpan ke Database
    $update = mysqli_query($koneksi, "UPDATE users SET password = '$password_hash' WHERE id_user = '$id_user'");

    if ($update) {
        // Jika berhasil, paksa user untuk logout agar login kembali menggunakan password baru
        echo "<script>
                alert('Berhasil! Password Anda telah diubah. Silakan login kembali dengan password baru.'); 
                window.location.href = 'logout.php';
              </script>";
    } else {
        echo "<script>alert('Terjadi kesalahan sistem, gagal mengubah password!'); window.history.back();</script>";
    }
} else {
    header("Location: index.php");
}
?>