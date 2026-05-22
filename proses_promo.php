<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman: Pastikan hanya admin yang bisa mengeksekusi script ini
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['simpan_promo'])) {
    $nama_promo  = mysqli_real_escape_string($koneksi, $_POST['nama_promo']);
    $id_produk   = mysqli_real_escape_string($koneksi, $_POST['id_produk']);
    $diskon      = (int)$_POST['diskon']; // Cast ke integer agar aman
    $tgl_mulai   = mysqli_real_escape_string($koneksi, $_POST['tgl_mulai']);
    $tgl_selesai = mysqli_real_escape_string($koneksi, $_POST['tgl_selesai']);

    // Validasi Logika Tanggal
    if (strtotime($tgl_selesai) < strtotime($tgl_mulai)) {
        echo "<script>alert('Gagal! Tanggal selesai tidak boleh lebih awal dari tanggal mulai.'); window.history.back();</script>";
        exit;
    }

    $query = "INSERT INTO promo (nama_promo, id_produk, diskon_persen, tgl_mulai, tgl_selesai) 
              VALUES ('$nama_promo', '$id_produk', '$diskon', '$tgl_mulai', '$tgl_selesai')";

    if ($koneksi->query($query)) {
        echo "<script>alert('Promo berhasil ditambahkan!'); window.location='promo.php';</script>";
    } else {
        echo "Error: " . $koneksi->error;
    }
}
?>