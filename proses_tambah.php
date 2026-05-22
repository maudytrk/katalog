<?php
session_start();
include 'koneksi.php';

// Proteksi halaman admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['simpan'])) {
    $kode      = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $id_kat    = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga     = (int)$_POST['harga']; // Cast langsung ke integer demi keamanan
    $stok      = (int)$_POST['stok'];  // Cast langsung ke integer demi keamanan
    $tiktok    = mysqli_real_escape_string($koneksi, $_POST['link_tiktok']);
    $shopee    = mysqli_real_escape_string($koneksi, $_POST['link_shopee']);
    $lazada    = mysqli_real_escape_string($koneksi, $_POST['link_lazada']);

    // Logika Upload Foto
    $nama_foto = $_FILES['foto']['name'];
    if ($nama_foto != "") {
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg');
        $x = explode('.', $nama_foto);
        $ekstensi = strtolower(end($x));
        $file_tmp = $_FILES['foto']['tmp_name'];
        $nama_foto_baru = date('dmyhis') . '-' . uniqid() . '.' . $ekstensi; // Ditambah uniqid agar nama file lebih unik

        if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
            move_uploaded_file($file_tmp, 'assets/img/' . $nama_foto_baru);
        } else {
            echo "<script>alert('Ekstensi gambar hanya boleh png, jpg, jpeg'); window.location='produk.php';</script>";
            exit;
        }
    } else {
        $nama_foto_baru = "no-image.jpg";
    }

    $query = "INSERT INTO produk (kode_produk, nama_produk, deskripsi, id_kategori, harga, stok, link_tiktok, link_shopee, link_lazada, foto) 
              VALUES ('$kode', '$nama', '$deskripsi', '$id_kat', '$harga', '$stok', '$tiktok', '$shopee', '$lazada', '$nama_foto_baru')";

    if ($koneksi->query($query)) {
        echo "<script>alert('Produk berhasil ditambahkan!'); window.location='produk.php';</script>";
    } else {
        echo "Error: " . $koneksi->error;
    }
}
?>