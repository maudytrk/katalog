<?php
session_start();
include 'koneksi.php';

// Proteksi halaman - Hanya admin/user login yang bisa unduh
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Mengambil parameter filter jika ada
$filter = isset($_GET['filter_status']) ? mysqli_real_escape_string($koneksi, $_GET['filter_status']) : 'semua';

// Menentukan nama file excel yang akan diunduh
$filename = "Laporan_Pesanan_Masuk_" . date('Y-m-d_H-i-s') . ".xls";

// Mengatur Header HTTP agar browser mengenali file sebagai Excel (.xls)
header("Content-Type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Menyusun Query SQL berdasarkan filter yang aktif
$sql = "SELECT o.*, u.nama_lengkap as nama_sales 
        FROM orders o 
        LEFT JOIN users u ON o.id_user = u.id_user";

if ($filter != 'semua') {
    $sql .= " WHERE o.status_order = '$filter'";
}

$sql .= " ORDER BY o.tgl_pesan DESC";
$result = $koneksi->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* Desain layout dasar tabel saat dibuka di Microsoft Excel */
        .title {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }
        .subtitle {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            text-align: center;
            color: #555555;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 11px;
        }
        th {
            background-color: #343a40;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            height: 30px;
        }
        td {
            height: 25px;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-uppercase {
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <div class="title">PT RAHAYU KARUNIA UTAMA</div>
    <div class="title">LAPORAN DATA PESANAN MASUK (E-CATALOGUE)</div>
    <div class="subtitle">Filter Status: <?php echo strtoupper($filter); ?> | Tanggal Unduh: <?php echo date('d F Y H:i:s'); ?></div>
    <br>

    <table border="1">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">ID Order</th>
                <th style="width: 15%;">Tanggal Pesan</th>
                <th style="width: 20%;">Nama Pelanggan</th>
                <th style="width: 20%;">Nama Sales penanggung Jawab</th>
                <th style="width: 15%;">Total Bayar</th>
                <th style="width: 10%;">Status Pesanan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $grand_total = 0;
            if ($result && $result->num_rows > 0):
                while ($row = $result->fetch_assoc()): 
                    $grand_total += $row['total_bayar'];
            ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td style="vnd.ms-excel.numberformat:@">#ORD-<?php echo $row['id_order']; ?></td>
                    <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($row['tgl_pesan'])); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_pelanggan']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_sales'] ?? 'Umum / Tanpa Sales'); ?></td>
                    <td class="text-right">Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                    <td class="text-center text-uppercase"><?php echo htmlspecialchars($row['status_order'] ?? 'pending'); ?></td>
                </tr>
            <?php 
                endwhile; 
            else:
            ?>
                <tr>
                    <td colspan="7" class="text-center" style="height: 40px; font-style: italic; color: #999;">Tidak ada data pesanan yang sesuai dengan filter.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="5" class="text-right">GRAND TOTAL KESELURUHAN:</td>
                <td class="text-right">Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>