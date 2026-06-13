<?php
include 'koneksi.php';

// Pastikan sistem membalas dengan format JSON murni
header('Content-Type: application/json');

$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, TRUE);

if (isset($data['ids']) && is_array($data['ids']) && count($data['ids']) > 0) {
    $ids = $data['ids'];
    $id_aman = [];

    foreach ($ids as $id) {
        $trimmed = trim($id);
        // Mengabaikan jika ada ID yang kosong atau tidak sengaja bernilai null
        if ($trimmed !== '') {
            // Dibungkus kutipan agar aman untuk tipe data angka (int) maupun teks (varchar)
            $id_aman[] = "'" . mysqli_real_escape_string($koneksi, $trimmed) . "'";
        }
    }

    if (count($id_aman) > 0) {
        $list_id = implode(',', $id_aman);

        $query = "SELECT id_produk, nama_produk, harga FROM produk WHERE id_produk IN ($list_id)";
        $result = $koneksi->query($query);

        $data_produk = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['harga_format'] = "Rp " . number_format($row['harga'], 0, ',', '.');
                $data_produk[] = $row;
            }
        }

        // Mengirimkan status sukses bersama query untuk bahan analisis di console
        echo json_encode([
            'status' => 'success',
            'query_eksekusi' => $query,
            'products' => $data_produk
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Daftar ID produk kosong setelah dibersihkan.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Tidak ada ID produk yang diterima oleh server.'
    ]);
}
