<?php
session_start();
include 'koneksi.php';

// Atur header agar aplikasi mengenali output sebagai JSON
header('Content-Type: application/json');

// 1. Proteksi Hak Akses (Hanya boleh diakses oleh sales)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'sales') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak! Halaman ini hanya untuk Tim Sales.']);
    exit;
}

// Menerima aliran data JSON dari Fetch API PWA
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, TRUE);

if ($data) {

    // AKTIFKAN REPORT EXCEPTION MYSQLI (Penting agar try-catch berfungsi pada MySQLi)
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // 2. Ambil data input dari objek JSON (Menggunakan 'user_id' disesuaikan dengan file riwayat_sales.php)
    $id_user        = mysqli_real_escape_string($koneksi, $_SESSION['user_id']);
    $id_produk      = (int)$data['id_produk'];
    $harga_satuan   = (float)$data['harga_satuan'];
    $nama_pelanggan = mysqli_real_escape_string($koneksi, trim($data['nama_pelanggan']));
    $no_hp          = mysqli_real_escape_string($koneksi, trim($data['no_hp']));
    $jumlah_beli    = (int)$data['jumlah_beli'];

    // Validasi input dasar untuk mencegah angka minus atau nol
    if ($jumlah_beli <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal! Jumlah beli tidak valid.']);
        exit;
    }

    // 3. Mulai Database Transaction (Dipindah ke atas agar pengecekan stok terkunci aman)
    $koneksi->begin_transaction();

    try {
        // 4. Validasi Ulang Stok di Sisi Server (Menggunakan FOR UPDATE untuk mencegah Race Condition)
        $query_cek_stok = "SELECT stok FROM produk WHERE id_produk = '$id_produk' LIMIT 1 FOR UPDATE";
        $res_stok = $koneksi->query($query_cek_stok);
        $data_produk = $res_stok->fetch_assoc();

        if (!$data_produk) {
            throw new Exception("Produk tidak ditemukan.");
        }

        $stok_sekarang = (int)$data_produk['stok'];

        if ($jumlah_beli > $stok_sekarang) {
            throw new Exception("Jumlah beli melebihi ketersediaan stok terbaru.");
        }

        // 5. Hitung Total Bayar
        $total_bayar = $jumlah_beli * $harga_satuan;

        // A. Generate ID Alfanumerik Acak & Panjang (Contoh: ORD-A8F93B4CDE12)
        $id_order_baru = 'ORD-' . strtoupper(bin2hex(random_bytes(6)));

        // B. Insert ke tabel 'orders' (masukkan id_order_baru secara manual)
        $query_order = "INSERT INTO orders (id_order, id_user, nama_pelanggan, no_hp, total_bayar, status_order) 
                        VALUES ('$id_order_baru', '$id_user', '$nama_pelanggan', '$no_hp', '$total_bayar', 'pending')";
        $koneksi->query($query_order);

        // C. Insert ke tabel 'order_detail' menggunakan ID yang di-generate tadi
        $query_detail = "INSERT INTO order_detail (id_order, id_produk, jumlah, harga_satuan, subtotal) 
                         VALUES ('$id_order_baru', '$id_produk', '$jumlah_beli', '$harga_satuan', '$total_bayar')";
        $koneksi->query($query_detail);

        // D. Update & Potong Stok Produk
        $stok_baru = $stok_sekarang - $jumlah_beli;
        $query_update_stok = "UPDATE produk SET stok = '$stok_baru' WHERE id_produk = '$id_produk'";
        $koneksi->query($query_update_stok);

        // Jika semua sukses, commit data
        $koneksi->commit();

        // Kembalikan respons berhasil dalam format JSON
        echo json_encode([
            'status' => 'success',
            'message' => "Transaksi Berhasil! ID Order Pelanggan: $id_order_baru",
            'id_order' => $id_order_baru
        ]);
        exit;
    } catch (Exception $e) {
        // Jika ada query yang gagal atau stok tidak cukup, batalkan semua manipulasi data
        $koneksi->rollback();
        $pesan_error = $e->getMessage();

        // Memisahkan pesan error karena stok/produk dengan pesan error query database
        if ($pesan_error == "Produk tidak ditemukan." || $pesan_error == "Jumlah beli melebihi ketersediaan stok terbaru.") {
            echo json_encode(['status' => 'error', 'message' => "Gagal Simpan! $pesan_error"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => "Terjadi kesalahan sistem, transaksi gagal diproses."]);
        }
        exit;
    }
} else {
    // Menangani akses langsung ke URL tanpa data JSON
    echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak valid.']);
    exit;
}
