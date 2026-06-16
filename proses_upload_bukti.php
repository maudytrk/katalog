<?php
session_start();
include 'koneksi.php';

// 1. Proteksi Halaman (Hanya untuk Sales yang Login)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'sales') {
    $_SESSION['gagal'] = "Akses ditolak! Sesi Anda mungkin telah kedaluwarsa. Silakan login kembali.";
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Ambil parameter input
    $id_order = mysqli_real_escape_string($koneksi, $_POST['id_order'] ?? '');
    
    if (empty($id_order)) {
        $_SESSION['gagal'] = "Gagal: ID Order tidak valid atau tidak disertakan.";
        header("Location: riwayat_sales.php");
        exit;
    }

    // 3. Verifikasi Kepemilikan Order (Sales hanya bisa mengunggah untuk pesanan milik mereka sendiri)
    $id_sales_aktif = $_SESSION['user_id'] ?? 0;
    $query_cek = "SELECT id_order, bukti_transfer, status_order FROM orders WHERE id_order = '$id_order' AND id_user = '$id_sales_aktif' LIMIT 1";
    $result_cek = $koneksi->query($query_cek);

    if (!$result_cek || $result_cek->num_rows === 0) {
        $_SESSION['gagal'] = "Gagal: Pesanan tidak ditemukan atau Anda tidak memiliki hak akses untuk pesanan ini.";
        header("Location: riwayat_sales.php");
        exit;
    }

    $order_data = $result_cek->fetch_assoc();
    $bukti_lama = $order_data['bukti_transfer'];
    $status_order = strtolower($order_data['status_order']);

    // Cegah upload jika pesanan dibatalkan
    if ($status_order === 'dibatalkan') {
        $_SESSION['gagal'] = "Gagal: Tidak dapat mengunggah bukti transfer untuk pesanan yang telah dibatalkan.";
        header("Location: riwayat_sales.php");
        exit;
    }

    // 4. Proses File Upload
    if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === UPLOAD_ERR_OK) {
        $nama_file = $_FILES['bukti_transfer']['name'];
        $tmp_name = $_FILES['bukti_transfer']['tmp_name'];
        $file_size = $_FILES['bukti_transfer']['size'];

        // Ekstensi yang diperbolehkan
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg', 'webp');
        $x = explode('.', $nama_file);
        $ekstensi = strtolower(end($x));

        // Validasi Ekstensi
        if (!in_array($ekstensi, $ekstensi_diperbolehkan)) {
            $_SESSION['gagal'] = "Gagal: Format file tidak didukung! Silakan unggah gambar dengan format PNG, JPG, JPEG, atau WEBP.";
            header("Location: riwayat_sales.php");
            exit;
        }

        // Validasi Ukuran (Maksimum 5MB)
        $max_size = 5 * 1024 * 1024;
        if ($file_size > $max_size) {
            $_SESSION['gagal'] = "Gagal: Ukuran file terlalu besar! Maksimal ukuran file adalah 5MB.";
            header("Location: riwayat_sales.php");
            exit;
        }

        // 5. Simpan File Baru & Update Database
        $nama_foto_baru = 'BUKTI-' . $id_order . '-' . date('YmdHis') . '-' . rand(1000, 9999) . '.' . $ekstensi;
        $target_dir = 'assets/img/bukti_transfer/';

        // Pastikan folder tujuan ada
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($tmp_name, $target_dir . $nama_foto_baru)) {
            // Update nama file bukti transfer di database
            $update = $koneksi->query("UPDATE orders SET bukti_transfer = '$nama_foto_baru' WHERE id_order = '$id_order'");

            if ($update) {
                // Hapus file bukti transfer lama dari server jika ada untuk efisiensi ruang
                if (!empty($bukti_lama) && file_exists($target_dir . $bukti_lama)) {
                    unlink($target_dir . $bukti_lama);
                }
                $_SESSION['sukses'] = "Berhasil! Bukti transfer untuk pesanan #$id_order telah berhasil diperbarui.";
            } else {
                // Jika DB update gagal, hapus file yang baru saja dipindah
                if (file_exists($target_dir . $nama_foto_baru)) {
                    unlink($target_dir . $nama_foto_baru);
                }
                $_SESSION['gagal'] = "Gagal: Terjadi kesalahan saat menyimpan data ke database.";
            }
        } else {
            $_SESSION['gagal'] = "Gagal: Tidak dapat memindahkan file ke folder penyimpanan server.";
        }
    } else {
        $error_code = $_FILES['bukti_transfer']['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error_code === UPLOAD_ERR_INI_SIZE || $error_code === UPLOAD_ERR_FORM_SIZE) {
            $_SESSION['gagal'] = "Gagal: Ukuran file melebihi batas maksimal yang diperbolehkan oleh konfigurasi server.";
        } elseif ($error_code === UPLOAD_ERR_NO_FILE) {
            $_SESSION['gagal'] = "Gagal: Anda belum memilih file foto bukti transfer untuk diunggah.";
        } else {
            $_SESSION['gagal'] = "Gagal mengunggah file. Kode Kesalahan: " . $error_code;
        }
    }
} else {
    $_SESSION['gagal'] = "Gagal: Metode permintaan tidak valid.";
}

header("Location: riwayat_sales.php");
exit;
?>
