<?php
// Konfigurasi Database
$host     = "localhost";    // Nama host (default XAMPP adalah localhost)
$username = "root";         // Username database (default XAMPP adalah root)
$password = "";             // Password database (default XAMPP adalah kosong)
$database = "katalog";      // Nama database sesuai permintaan Anda

// Membuat koneksi ke database
$koneksi = new mysqli($host, $username, $password, $database);

// Memeriksa apakah koneksi berhasil
if ($koneksi->connect_error) {
    // Jika koneksi gagal, hentikan program dan tampilkan pesan error
    die("Koneksi ke database gagal: " . $koneksi->connect_error);
}

// Opsional: Set karakter set ke UTF-8 agar mendukung simbol/karakter khusus
$koneksi->set_charset("utf8");

// Jika berhasil tersambung (opsional, bisa dihapus jika sudah masuk tahap produksi)
// echo "Koneksi berhasil terhubung ke database katalog";
?>