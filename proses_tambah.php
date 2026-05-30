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

    // 2. Insert data awal (foto dikosongkan terlebih dahulu)
    $query = "INSERT INTO produk (kode_produk, nama_produk, deskripsi, id_kategori, harga, stok, link_tiktok, link_shopee, link_lazada) 
              VALUES ('$kode', '$nama', '$deskripsi', '$id_kat', '$harga', '$stok', '$tiktok', '$shopee', '$lazada')";

    if ($koneksi->query($query)) {
        $id_produk_baru = $koneksi->insert_id; 

        // 3. Logika Multi-Upload Foto yang Disempurnakan (Anti-Gagal)
        $foto_utama_set = false;
        $jumlah_berhasil = 0;

        if (isset($_FILES['foto']['name']) && is_array($_FILES['foto']['name'])) {
            $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg', 'webp');
            $total_files = count($_FILES['foto']['name']);
            
            for ($i = 0; $i < $total_files; $i++) {
                // Pastikan file ini tidak ada error saat diupload ke memori sementara
                if ($_FILES['foto']['error'][$i] === UPLOAD_ERR_OK) {
                    $nama_file = $_FILES['foto']['name'][$i];
                    $tmp_name = $_FILES['foto']['tmp_name'][$i];
                    
                    $x = explode('.', $nama_file);
                    $ekstensi = strtolower(end($x));

                    if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
                        // Bikin nama unik (TahunBulanTanggalJamMenitDetik + Angka Acak) agar tidak bentrok
                        $nama_foto_baru = date('YmdHis') . '-' . rand(1000, 9999) . '.' . $ekstensi;
                        
                        if (move_uploaded_file($tmp_name, 'assets/img/' . $nama_foto_baru)) {
                            // Masukkan ke galeri foto produk (tabel produk_foto)
                            $koneksi->query("INSERT INTO produk_foto (id_produk, nama_file) VALUES ('$id_produk_baru', '$nama_foto_baru')");

                            // Jadikan foto pertama yang berhasil diupload sebagai Foto Utama (cover depan katalog)
                            if (!$foto_utama_set) {
                                $koneksi->query("UPDATE produk SET foto = '$nama_foto_baru' WHERE id_produk = '$id_produk_baru'");
                                $foto_utama_set = true;
                            }
                            $jumlah_berhasil++;
                        }
                    }
                }
            }
        }
        
        echo "<script>alert('Sukses! Produk baru beserta $jumlah_berhasil foto berhasil ditambahkan.'); window.location='produk.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan data ke database: " . $koneksi->error . "'); window.history.back();</script>";
    }
}
?>