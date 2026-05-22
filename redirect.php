<?php
include 'koneksi.php';

// Menangkap parameter dengan aman
$id_produk = isset($_GET['id_produk']) ? intval($_GET['id_produk']) : 0;
$type      = isset($_GET['type']) ? $_GET['type'] : '';
$platform  = isset($_GET['platform']) ? strtolower(trim($_GET['platform'])) : ''; // Ditambah trim() untuk hapus spasi

if ($id_produk <= 0) {
    header("Location: index.php");
    exit;
}

if ($type === 'detail') {
    // 1. Logika Klik Detail: Update total klik produk
    $sql_update = "UPDATE produk SET jumlah_klik = jumlah_klik + 1 WHERE id_produk = ?";
    $stmt = $koneksi->prepare($sql_update);
    $stmt->bind_param("i", $id_produk);
    $stmt->execute();
    $stmt->close();

    header("Location: detail.php?id=" . $id_produk);
    exit;

} elseif ($type === 'marketplace' && in_array($platform, ['tiktok', 'shopee', 'lazada'])) {
    
    // ==================== PERBAIKAN DI SINI ====================
    
    // A. Masukkan data ke log_klik_marketplace beserta waktu_klik (NOW())
    $sql_log = "INSERT INTO log_klik_marketplace (id_produk, platform, waktu_klik) VALUES (?, ?, NOW())";
    $stmt_log = $koneksi->prepare($sql_log);
    $stmt_log->bind_param("is", $id_produk, $platform);
    $stmt_log->execute();
    $stmt_log->close();

    // B. Tambahan Opsional: Naikkan juga jumlah_klik di tabel produk saat marketplace diklik
    $sql_update_produk = "UPDATE produk SET jumlah_klik = jumlah_klik + 1 WHERE id_produk = ?";
    $stmt_up = $koneksi->prepare($sql_update_produk);
    $stmt_up->bind_param("i", $id_produk);
    $stmt_up->execute();
    $stmt_up->close();
    
    // ===========================================================

    // Ambil URL link marketplace asli dari database produk
    $kolom_link = "link_" . $platform; 
    $sql_link = "SELECT $kolom_link FROM produk WHERE id_produk = ?";
    $stmt_link = $koneksi->prepare($sql_link);
    $stmt_link->bind_param("i", $id_produk);
    $stmt_link->execute();
    $res_link = $stmt_link->get_result()->fetch_assoc();
    $stmt_link->close();

    $url_tujuan = (!empty($res_link[$kolom_link])) ? $res_link[$kolom_link] : "index.php";
    
    header("Location: " . $url_tujuan);
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>