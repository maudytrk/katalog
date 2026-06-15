<?php
session_start();
include 'koneksi.php';

// Atur header agar aplikasi mengenali output sebagai JSON
header('Content-Type: application/json');

// 1. Proteksi Hak Akses
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'sales') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak! Sesi Anda mungkin telah kedaluwarsa. Silakan login kembali.']);
    exit;
}

// 1.5 Proteksi Tambahan
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Galat: ID User tidak ditemukan dalam sesi saat ini. Silakan logout dan login kembali.']);
    exit;
}

// ==========================================
// PENANGANAN MULTI-FORMAT REQUEST
// ==========================================
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$data = [];

// Mengecek apakah request berformat JSON atau Form Standar
if (strpos($contentType, 'application/json') !== false) {
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, TRUE);
} else {
    $data = $_POST;
}

// Lanjutkan pemrosesan jika ada data yang masuk
if (!empty($data)) {

    // AKTIFKAN REPORT EXCEPTION MYSQLI
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // 2. Ambil data input 
    $id_user        = mysqli_real_escape_string($koneksi, $_SESSION['user_id']);
    $id_produk      = isset($data['id_produk']) ? (int)$data['id_produk'] : 0;
    $harga_satuan   = isset($data['harga_satuan']) ? (float)$data['harga_satuan'] : 0;
    $nama_pelanggan = isset($data['nama_pelanggan']) ? mysqli_real_escape_string($koneksi, trim($data['nama_pelanggan'])) : '';
    $no_hp          = isset($data['no_hp']) ? mysqli_real_escape_string($koneksi, trim($data['no_hp'])) : '';
    $jumlah_beli    = isset($data['jumlah_beli']) && $data['jumlah_beli'] !== '' ? (int)$data['jumlah_beli'] : 0;

    // Buat variabel tanggal secara otomatis dari server
    $tanggal        = date('Y-m-d H:i:s');

    // Validasi input dasar
    if ($jumlah_beli <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal! Jumlah beli tidak valid atau kosong.']);
        exit;
    }

    // 3. Mulai Database Transaction 
    $koneksi->begin_transaction();

    try {
        // 4. Validasi Ulang Stok 
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

        // A. Generate ID Alfanumerik 
        $id_order_baru = 'ORD-' . strtoupper(bin2hex(random_bytes(6)));

        // B. Insert ke tabel 'orders'
        $query_order = "INSERT INTO orders (id_order, id_user, nama_pelanggan, no_hp, total_bayar, status_order, tgl_pesan) 
                VALUES ('$id_order_baru', '$id_user', '$nama_pelanggan', '$no_hp', '$total_bayar', 'pending', '$tanggal')";
        $koneksi->query($query_order);

        // C. Insert ke tabel 'order_detail' 
        $query_detail = "INSERT INTO order_detail (id_order, id_produk, jumlah, harga_satuan, subtotal) 
                         VALUES ('$id_order_baru', '$id_produk', '$jumlah_beli', '$harga_satuan', '$total_bayar')";
        $koneksi->query($query_detail);

        // D. Update & Potong Stok Produk
        $stok_baru = $stok_sekarang - $jumlah_beli;
        $query_update_stok = "UPDATE produk SET stok = '$stok_baru' WHERE id_produk = '$id_produk'";
        $koneksi->query($query_update_stok);

        // Jika semua sukses, commit data
        $koneksi->commit();

        // Kembalikan respons JSON
        echo json_encode([
            'status' => 'success',
            'message' => "Transaksi Berhasil! ID Order Pelanggan: $id_order_baru",
            'id_order' => $id_order_baru
        ]);
        exit;
    } catch (\Exception $e) { 
        $koneksi->rollback();
        $pesan_error = $e->getMessage();

        if ($pesan_error == "Produk tidak ditemukan." || $pesan_error == "Jumlah beli melebihi ketersediaan stok terbaru.") {
            echo json_encode(['status' => 'error', 'message' => "Gagal Simpan! $pesan_error"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => "MySQL Error: " . $pesan_error]);
        }
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak valid. Data form kosong atau terblokir.']);
    exit;
}