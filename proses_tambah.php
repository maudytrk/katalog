<?php
session_start();
include 'koneksi.php';

// Proteksi halaman admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['simpan'])) {
    // 1. Ambil data teks produk
    $kode      = mysqli_real_escape_string($koneksi, $_POST['kode']);

    // --- CEK KODE PRODUK GANDA ---
    $cek_kode = $koneksi->query("SELECT id_produk FROM produk WHERE kode_produk = '$kode'");
    if ($cek_kode->num_rows > 0) {
        $_SESSION['gagal'] = "Kode Produk \"$kode\" sudah digunakan. Silakan gunakan kode yang berbeda.";
        header("Location: produk.php");
        exit;
    }
    // -----------------------------

    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $harga     = (int)$_POST['harga'];
    $stok      = (int)$_POST['stok'];
    $tiktok    = mysqli_real_escape_string($koneksi, $_POST['link_tiktok']);
    $shopee    = mysqli_real_escape_string($koneksi, $_POST['link_shopee']);
    $lazada    = mysqli_real_escape_string($koneksi, $_POST['link_lazada']);

    // Ambil array kategori (Bisa lebih dari 1)
    $kategori_arr = isset($_POST['kategori']) ? $_POST['kategori'] : [];
    if (empty($kategori_arr)) {
        $_SESSION['gagal'] = "Anda harus memilih minimal 1 kategori.";
        header("Location: produk.php");
        exit;
    }

    // Validasi minimal 1 foto diupload
    if (!isset($_FILES['foto']['name']) || empty($_FILES['foto']['name'][0]) || $_FILES['foto']['error'][0] === UPLOAD_ERR_NO_FILE) {
        $_SESSION['gagal'] = "Minimal harus upload 1 foto produk.";
        header("Location: produk.php");
        exit;
    }

    // 2. Insert data produk utama
    $query = "INSERT INTO produk (kode_produk, nama_produk, deskripsi, harga, stok, link_tiktok, link_shopee, link_lazada) 
              VALUES ('$kode', '$nama', '$deskripsi', '$harga', '$stok', '$tiktok', '$shopee', '$lazada')";

    if ($koneksi->query($query)) {
        $id_produk_baru = $koneksi->insert_id;

        // 3. Simpan relasi multi-kategori ke tabel perantara (produk_kategori)
        foreach ($kategori_arr as $id_kat) {
            $id_kat_clean = mysqli_real_escape_string($koneksi, $id_kat);
            $koneksi->query("INSERT INTO produk_kategori (id_produk, id_kategori) VALUES ('$id_produk_baru', '$id_kat_clean')");
        }

        // 4. Logika Multi-Upload Foto
        $foto_utama_set = false;
        $jumlah_berhasil = 0;
        $gagal_upload = false;

        if (isset($_FILES['foto']['name']) && is_array($_FILES['foto']['name'])) {
            $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg', 'webp');
            $total_files = count($_FILES['foto']['name']);
            $max_size = 5 * 1024 * 1024; // 5MB

            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['foto']['error'][$i] === UPLOAD_ERR_OK) {
                    $nama_file = $_FILES['foto']['name'][$i];
                    $tmp_name = $_FILES['foto']['tmp_name'][$i];
                    $file_size = $_FILES['foto']['size'][$i];

                    $x = explode('.', $nama_file);
                    $ekstensi = strtolower(end($x));

                    // Validasi ukuran file
                    if ($file_size > $max_size) {
                        $gagal_upload = true;
                        continue;
                    }

                    if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
                        $nama_foto_baru = date('YmdHis') . '-' . rand(1000, 9999) . '.' . $ekstensi;

                        if (move_uploaded_file($tmp_name, 'assets/img/' . $nama_foto_baru)) {
                            $koneksi->query("INSERT INTO produk_foto (id_produk, nama_file) VALUES ('$id_produk_baru', '$nama_foto_baru')");

                            if (!$foto_utama_set) {
                                $koneksi->query("UPDATE produk SET foto = '$nama_foto_baru' WHERE id_produk = '$id_produk_baru'");
                                $foto_utama_set = true;
                            }
                            $jumlah_berhasil++;
                        } else {
                            $gagal_upload = true;
                        }
                    } else {
                        $gagal_upload = true;
                    }
                }
            }
        }

        // Cek apakah setidaknya 1 foto berhasil diupload
        if ($jumlah_berhasil > 0) {
            $pesan_sukses = "Produk baru berhasil ditambahkan!";
            if ($jumlah_berhasil > 1) {
                $pesan_sukses .= " ($jumlah_berhasil foto berhasil diupload)";
            }
            if ($gagal_upload) {
                $pesan_sukses .= " Beberapa foto gagal diupload karena format/ukuran tidak sesuai.";
            }
            $_SESSION['sukses'] = $pesan_sukses;
        } else {
            // Jika tidak ada foto yang berhasil diupload, hapus produk yang baru saja dibuat
            $koneksi->query("DELETE FROM produk_kategori WHERE id_produk = '$id_produk_baru'");
            $koneksi->query("DELETE FROM produk WHERE id_produk = '$id_produk_baru'");
            $_SESSION['gagal'] = "Gagal upload foto. Pastikan format file: PNG, JPG, JPEG, WEBP dan ukuran max 5MB per file.";
        }
        
        header("Location: produk.php");
        exit;
    } else {
        $_SESSION['gagal'] = "Gagal menyimpan data ke database: " . $koneksi->error;
        header("Location: produk.php");
        exit;
    }
}