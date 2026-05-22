<?php
session_start();
include 'koneksi.php';

// Proteksi halaman admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['simpan'])) {
    // 1. Ambil data produk
    $kode      = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $id_kat    = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga     = (int)$_POST['harga'];
    $stok      = (int)$_POST['stok'];
    $tiktok    = mysqli_real_escape_string($koneksi, $_POST['link_tiktok']);
    $shopee    = mysqli_real_escape_string($koneksi, $_POST['link_shopee']);
    $lazada    = mysqli_real_escape_string($koneksi, $_POST['link_lazada']);

    // 2. Insert data produk ke tabel 'produk'
    // Catatan: Kolom 'foto' pada tabel produk sekarang bisa menjadi foto utama (opsional)
    // atau jika Anda ingin meninggalkan kolom foto tersebut, kita ambil foto pertama dari array.
    $query = "INSERT INTO produk (kode_produk, nama_produk, deskripsi, id_kategori, harga, stok, link_tiktok, link_shopee, link_lazada) 
              VALUES ('$kode', '$nama', '$deskripsi', '$id_kat', '$harga', '$stok', '$tiktok', '$shopee', '$lazada')";

    if ($koneksi->query($query)) {
        $id_produk_baru = $koneksi->insert_id; // Mengambil ID produk yang baru saja di-insert

        // 3. Logika Multi-Upload Foto
        if (!empty($_FILES['foto']['name'][0])) {
            $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg');

            foreach ($_FILES['foto']['tmp_name'] as $key => $tmp_name) {
                $nama_file = $_FILES['foto']['name'][$key];
                $x = explode('.', $nama_file);
                $ekstensi = strtolower(end($x));

                if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
                    $nama_foto_baru = date('dmyhis') . '-' . uniqid() . '.' . $ekstensi;
                    if (move_uploaded_file($tmp_name, 'assets/img/' . $nama_foto_baru)) {

                        // Masukkan nama file ke tabel produk_foto
                        $koneksi->query("INSERT INTO produk_foto (id_produk, nama_file) VALUES ('$id_produk_baru', '$nama_foto_baru')");

                        // (Opsional) Jika Anda masih butuh kolom 'foto' di tabel 'produk' sebagai cover utama:
                        if ($key === 0) {
                            $koneksi->query("UPDATE produk SET foto = '$nama_foto_baru' WHERE id_produk = '$id_produk_baru'");
                        }
                    }
                }
            }
        }
        echo "<script>alert('Produk dan foto berhasil ditambahkan!'); window.location='produk.php';</script>";
    } else {
        echo "Error: " . $koneksi->error;
    }
}
