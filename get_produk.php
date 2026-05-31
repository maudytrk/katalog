<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin' || !isset($_GET['id'])) {
    echo "Akses ditolak.";
    exit;
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);

$ambildata = $koneksi->query("SELECT * FROM produk WHERE id_produk = '$id'");
$data = $ambildata->fetch_assoc();

if (!$data) {
    echo "<div class='alert alert-danger'>Data produk tidak ditemukan.</div>";
    exit;
}

// Ambil array id kategori yang saat ini dimiliki produk
$kategori_saat_ini = [];
$query_kat_lama = $koneksi->query("SELECT id_kategori FROM produk_kategori WHERE id_produk = '$id'");
if ($query_kat_lama) {
    while ($kl = $query_kat_lama->fetch_assoc()) {
        $kategori_saat_ini[] = $kl['id_kategori'];
    }
}
?>

<input type="hidden" name="id_produk_edit" value="<?php echo $id; ?>">

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold" style="color: #231F48;">Kode Produk (Otomatis/Tetap)</label>
        <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($data['kode_produk']); ?>" disabled readonly>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label fw-semibold" style="color: #231F48;">Nama Produk</label>
        <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($data['nama_produk']); ?>" required>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold" style="color: #231F48;">Deskripsi Produk</label>
    <textarea name="deskripsi" class="form-control" rows="3" required><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold" style="color: #231F48;">Kategori <span class="text-danger small">(Bisa > 1)</span></label>
        <select name="kategori[]" id="kategoriSelectEdit" class="form-control" multiple required>
            <option value="" placeholder>Pilih Kategori Produk...</option>
            <?php
            $kat = $koneksi->query("SELECT * FROM kategori");
            while ($k = $kat->fetch_assoc()) {
                $selected = in_array($k['id_kategori'], $kategori_saat_ini) ? 'selected' : '';
                echo "<option value='" . $k['id_kategori'] . "' $selected>" . htmlspecialchars($k['nama_kategori']) . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold" style="color: #231F48;">Harga (Rp)</label>
        <input type="number" name="harga" min="0" class="form-control" value="<?php echo htmlspecialchars($data['harga']); ?>" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-semibold" style="color: #231F48;">Stok Tersedia</label>
        <input type="number" name="stok" min="0" class="form-control" value="<?php echo htmlspecialchars($data['stok']); ?>" required>
    </div>
</div>

<hr class="my-3 text-muted">

<div class="mb-3">
    <label class="form-label fw-bold" style="color: #231F48;"><i class="fas fa-images me-1"></i>Foto Produk Saat Ini:</label>
    <div class="d-flex flex-wrap gap-2 mb-3 p-3 rounded" style="background-color: #f1f2f6; border: 1px solid #dcdde1;">
        <?php
        $foto_query = $koneksi->query("SELECT * FROM produk_foto WHERE id_produk = '$id'");
        if ($foto_query && $foto_query->num_rows > 0) {
            while ($f = $foto_query->fetch_assoc()): ?>
                <div class="preview-wrapper">
                    <img src="assets/img/<?= htmlspecialchars($f['nama_file']) ?>">
                    <button type="button" class="preview-remove-btn border-0" onclick="hapusFotoLama(<?= $f['id_foto'] ?>, this)">&times;</button>
                </div>
            <?php endwhile;
        } else {
            echo "<span class='text-muted small italic'>Belum ada foto.</span>";
        }
        ?>
    </div>

    <label class="form-label fw-semibold" style="color: #231F48;">Tambah Foto Baru <span class="text-muted small">(Opsional)</span></label>
    <input type="file" name="foto_baru[]" class="form-control" id="fotoInputEdit" accept="image/*" multiple>
    <div class="form-text text-muted mb-2">Pilih banyak foto baru. Foto yang dipilih tidak akan menghapus foto lama kecuali Anda menyilangnya (X) di atas.</div>
    <div id="previewContainerEdit" class="d-flex flex-wrap gap-2 mt-2"></div>
</div>

<hr class="my-3 text-muted">

<h6 class="fw-bold mb-3" style="color: #6B4773;"><i class="fas fa-link me-1"></i> Link Marketplace</h6>
<div class="mb-2"><input type="url" name="link_tiktok" class="form-control form-control-sm" value="<?php echo htmlspecialchars($data['link_tiktok']); ?>"></div>
<div class="mb-2"><input type="url" name="link_shopee" class="form-control form-control-sm" value="<?php echo htmlspecialchars($data['link_shopee']); ?>"></div>
<div class="mb-3"><input type="url" name="link_lazada" class="form-control form-control-sm" value="<?php echo htmlspecialchars($data['link_lazada']); ?>"></div>