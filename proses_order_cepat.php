<?php
session_start();
include 'koneksi.php';

// 1. Proteksi Hak Akses (Hanya boleh diakses oleh sales)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'sales') {
    die("Akses ditolak! Halaman ini hanya untuk Tim Sales.");
}

if (isset($_POST['submit_order_cepat'])) {
    
    // AKTIFKAN REPORT EXCEPTION MYSQLI (Penting agar try-catch berfungsi pada MySQLi)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // 2. Ambil data input (Menggunakan 'user_id' disesuaikan dengan file riwayat_sales.php)
    $id_user        = mysqli_real_escape_string($koneksi, $_SESSION['user_id']); 
    $id_produk      = (int)$_POST['id_produk'];
    $harga_satuan   = (float)$_POST['harga_satuan'];
    $nama_pelanggan = mysqli_real_escape_string($koneksi, trim($_POST['nama_pelanggan']));
    $no_hp          = mysqli_real_escape_string($koneksi, trim($_POST['no_hp']));
    $jumlah_beli    = (int)$_POST['jumlah_beli'];

    // Validasi input dasar untuk mencegah angka minus atau nol
    if ($jumlah_beli <= 0) {
        echo "<script>alert('Gagal! Jumlah beli tidak valid.'); window.location='katalog.php';</script>";
        exit;
    }

    // 3. Validasi Ulang Stok di Sisi Server
    $query_cek_stok = "SELECT stok FROM produk WHERE id_produk = '$id_produk' LIMIT 1";
    $res_stok = $koneksi->query($query_cek_stok);
    $data_produk = $res_stok->fetch_assoc();

    if (!$data_produk) {
        echo "<script>alert('Gagal! Produk tidak ditemukan.'); window.location='katalog.php';</script>";
        exit;
    }

    $stok_sekarang = (int)$data_produk['stok'];

    if ($jumlah_beli > $stok_sekarang) {
        echo "<script>alert('Gagal Simpan! Jumlah beli melebihi ketersediaan stok terbaru.'); window.location='katalog.php';</script>";
        exit;
    }

    // 4. Hitung Total Bayar
    $total_bayar = $jumlah_beli * $harga_satuan;

    // 5. Mulai Database Transaction
    $koneksi->begin_transaction();

    try {
        // A. Insert ke tabel 'orders'
        $query_order = "INSERT INTO orders (id_user, nama_pelanggan, no_hp, total_bayar, status_order) 
                        VALUES ('$id_user', '$nama_pelanggan', '$no_hp', '$total_bayar', 'pending')";
        $koneksi->query($query_order);
        
        // Mengambil ID order baru
        $id_order_baru = $koneksi->insert_id;

        // B. Insert ke tabel 'order_detail'
        $query_detail = "INSERT INTO order_detail (id_order, id_produk, jumlah, harga_satuan, subtotal) 
                         VALUES ('$id_order_baru', '$id_produk', '$jumlah_beli', '$harga_satuan', '$total_bayar')";
        $koneksi->query($query_detail);

        // C. Update & Potong Stok Produk
        $stok_baru = $stok_sekarang - $jumlah_beli;
        $query_update_stok = "UPDATE produk SET stok = '$stok_baru' WHERE id_produk = '$id_produk'";
        $koneksi->query($query_update_stok);

        // Jika semua sukses, commit data
        $koneksi->commit();
        echo "<script>alert('Transaksi Berhasil Disimpan! Status: Pending.'); window.location='katalog.php';</script>";

    } catch (Exception $e) {
        // Jika ada query yang gagal, batalkan semua manipulasi data secara total
        $koneksi->rollback();
        echo "<script>alert('Terjadi kesalahan sistem, transaksi gagal diproses.'); window.location='katalog.php';</script>";
    }
} else {
    header("Location: katalog.php");
    exit;
}
?>