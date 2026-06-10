<?php
session_start();
include 'koneksi.php';

// Proteksi halaman: Jika belum login, kembalikan ke login.php
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Fitur Tambah & Edit Data
if (isset($_POST['simpan_akun'])) {
    $id_user      = $_POST['id_user'];
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $role         = $_POST['role'];
    $password     = $_POST['password'];

    if (empty($id_user)) {
        // --- PROSES TAMBAH (CREATE) ---
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (username, password, nama_lengkap, role, created_at) 
                  VALUES ('$username', '$password_hashed', '$nama_lengkap', '$role', NOW())";
        if ($koneksi->query($query)) {
            $_SESSION['sukses'] = "Akun baru berhasil ditambahkan ke sistem!";
            header("Location: kelola_admin.php");
            exit;
        } else {
            $_SESSION['gagal'] = "Gagal menambah akun: " . $koneksi->error;
            header("Location: kelola_admin.php");
            exit;
        }
    } else {
        // --- PROSES EDIT (UPDATE) ---
        $id_user = mysqli_real_escape_string($koneksi, $id_user);
        if (!empty($password)) {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE users SET username='$username', password='$password_hashed', nama_lengkap='$nama_lengkap', role='$role' WHERE id_user='$id_user'";
        } else {
            $query = "UPDATE users SET username='$username', nama_lengkap='$nama_lengkap', role='$role' WHERE id_user='$id_user'";
        }

        if ($koneksi->query($query)) {
            $_SESSION['sukses'] = "Data akun berhasil diperbarui!";
            header("Location: kelola_admin.php");
            exit;
        } else {
            $_SESSION['gagal'] = "Gagal memperbarui akun: " . $koneksi->error;
            header("Location: kelola_admin.php");
            exit;
        }
    }
}

// Fitur Hapus Data (DELETE)
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    // Mencegah admin menghapus dirinya sendiri yang sedang login
    if (isset($_SESSION['user_id']) && $id_hapus == $_SESSION['user_id']) {
        $_SESSION['gagal'] = "Anda tidak bisa menghapus akun Anda sendiri yang sedang aktif!";
        header("Location: kelola_admin.php");
        exit;
    }

    $query = "DELETE FROM users WHERE id_user = '$id_hapus'";
    if ($koneksi->query($query)) {
        $_SESSION['sukses'] = "Akun berhasil dihapus dari sistem secara permanen!";
        header("Location: kelola_admin.php");
        exit;
    } else {
        $_SESSION['gagal'] = "Gagal menghapus akun: " . $koneksi->error;
        header("Location: kelola_admin.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Kelola Akun Admin - E-Catalogue</title>
    <?php include 'pwa_meta.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <style>
        /* TEMA WARNA BERDASARKAN PALETTE E-CATALOGUE */
        :root {
            --old-heliotrope: #6B4773;
            --royal-fuchsia: #BB3F95;
            --lavender-mist: #E0E1F6;
            --space-cadet: #231F48;
            --tyrian-purple: #560A39;
            --bg-cream: #FCF8F1;
            --accent-olive: #7D8F37;
        }

        body {
            background-color: var(--bg-cream);
            color: var(--space-cadet);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Navbar Custom */
        .navbar-admin-custom {
            background-color: var(--space-cadet) !important;
            border-bottom: 3px solid var(--old-heliotrope);
        }

        .navbar-admin-custom .navbar-brand {
            color: #fff !important;
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* Sidebar Custom */
        .sb-sidenav-dark {
            background-color: #1a1736 !important;
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .sb-sidenav-dark .sb-sidenav-menu-heading {
            color: var(--lavender-mist) !important;
            font-weight: 600;
            opacity: 0.6;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sb-sidenav-dark .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: all 0.3s;
        }

        .sb-sidenav-dark .nav-link .sb-nav-link-icon {
            color: var(--lavender-mist) !important;
        }

        .sb-sidenav-dark .nav-link.active {
            color: #fff !important;
            background-color: var(--old-heliotrope) !important;
            border-radius: 0 25px 25px 0;
            margin-right: 15px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
        }

        .sb-sidenav-dark .nav-link:hover:not(.active) {
            color: #fff !important;
            background-color: rgba(107, 71, 115, 0.3) !important;
            border-radius: 0 25px 25px 0;
            margin-right: 15px;
        }

        .sb-sidenav-footer {
            background-color: #121026 !important;
            color: var(--lavender-mist) !important;
        }

        /* Panel Card Biasa (Tabel) */
        .panel-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(35, 31, 72, 0.04);
            overflow: hidden;
        }

        .panel-header {
            background-color: var(--lavender-mist);
            color: var(--space-cadet);
            font-weight: 700;
            border-bottom: 2px solid #DCD6EA;
            padding: 15px 20px;
        }

        .table>thead {
            background-color: var(--space-cadet);
            color: white;
        }

        /* Tombol & Modal Custom */
        .btn-custom-primary {
            background-color: var(--old-heliotrope);
            color: white;
            border: none;
        }

        .btn-custom-primary:hover {
            background-color: var(--tyrian-purple);
            color: white;
        }

        .modal-header-custom {
            background-color: var(--space-cadet);
            color: white;
            border-bottom: 3px solid var(--old-heliotrope);
        }

        h1,
        h2,
        .breadcrumb-item.active,
        .breadcrumb-item a {
            color: var(--space-cadet) !important;
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: var(--old-heliotrope) !important;
            text-decoration: underline;
        }

        footer.bg-light {
            background-color: #ffffff !important;
            border-top: 1px solid var(--lavender-mist);
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark navbar-admin-custom">
        <a class="navbar-brand ps-3" href="dashboard_admin.php"><i class="fas fa-crown text-warning me-2"></i>RAHAYU ADMIN</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
        <ul class="navbar-nav ms-auto me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle fw-bold" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user-circle fa-fw me-1"></i> <?php echo isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Administrator'; ?></a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item fw-medium text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav mt-3">
                        <div class="sb-sidenav-menu-heading">Utama</div>
                        <a class="nav-link" href="dashboard_admin.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>Dashboard
                        </a>

                        <div class="sb-sidenav-menu-heading mt-2">Manajemen Data</div>
                        <a class="nav-link" href="kategori.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>Kelola Kategori
                        </a>
                        <a class="nav-link" href="produk.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-box-open"></i></div>Kelola Produk
                        </a>
                        <a class="nav-link" href="sales.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-user-tie"></i></div>Pengelolaan Sales
                        </a>
                        <a class="nav-link" href="promo.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-percent"></i></div>Kelola Promo
                        </a>
                        <a class="nav-link" href="orders.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>Pesanan Masuk
                        </a>
                        <a class="nav-link" href="katalog.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-store"></i></div>Katalog Produk
                        </a>
                        <a class="nav-link" href="bandingkan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-balance-scale"></i></div>Bandingkan Produk
                        </a>

                        <div class="sb-sidenav-menu-heading mt-2">Pengaturan Sistem</div>
                        <a class="nav-link active" href="kelola_admin.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div>Kelola Akun
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small text-white-50">Logged in as:</div>
                    <span class="fw-bold text-white"><?php echo ucfirst($_SESSION['role'] ?? 'Admin'); ?></span>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4 py-4">
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <h2 class="fw-bolder mb-1" style="color: var(--space-cadet);">Pengelolaan Akun Pengguna</h2>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="dashboard_admin.php" class="text-decoration-none text-muted">Dashboard</a></li>
                                <li class="breadcrumb-item active" style="color: var(--space-cadet);">Kelola Akun Admin / Sales</li>
                            </ol>
                        </div>
                    </div>

                    <div class="card panel-card mb-4">
                        <div class="panel-header d-flex justify-content-between align-items-center">
                            <div><i class="fas fa-users-cog me-1"></i> Daftar Pengguna Sistem Informasi</div>
                            <button type="button" class="btn btn-sm btn-custom-primary fw-bold px-3 rounded" onclick="tambahAkunModal()">
                                <i class="fas fa-plus-circle me-1"></i> Tambah Akun Baru
                            </button>
                        </div>
                        <div class="card-body bg-white p-3">
                            <table id="datatablesSimple" class="table table-striped table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Nama Lengkap</th>
                                        <th>Username</th>
                                        <th>Hak Akses / Role</th>
                                        <th>Waktu Dibuat</th>
                                        <th width="15%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $users_query = $koneksi->query("SELECT * FROM users ORDER BY id_user DESC");
                                    while ($user = $users_query->fetch_assoc()) :
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td class="fw-bold" style="color: var(--space-cadet);"><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td>
                                                <span class="badge <?php echo ($user['role'] == 'admin') ? 'bg-primary' : 'bg-info text-dark'; ?> text-capitalize px-3 py-2">
                                                    <i class="fas <?php echo ($user['role'] == 'admin') ? 'fa-user-shield' : 'fa-user-tag'; ?> me-1"></i>
                                                    <?php echo $user['role']; ?>
                                                </span>
                                            </td>
                                            <td class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <button type="button" class="btn btn-warning btn-sm fw-500 text-dark"
                                                        onclick="editAkunModal(
                                                            '<?php echo $user['id_user']; ?>', 
                                                            '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>', 
                                                            '<?php echo htmlspecialchars($user['nama_lengkap'], ENT_QUOTES); ?>', 
                                                            '<?php echo $user['role']; ?>'
                                                        )" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="konfirmasiHapusModal('<?php echo $user['id_user']; ?>', '<?php echo htmlspecialchars($user['nama_lengkap'], ENT_QUOTES); ?>')" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted fw-medium">PT Rahayu Karunia Utama &copy; <?php echo date('Y'); ?> E-Catalogue</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <div class="modal fade" id="akunModal" tabindex="-1" aria-labelledby="akunModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="kelola_admin.php" method="POST">
                    <div class="modal-header modal-header-custom">
                        <h5 class="modal-title fw-bold" id="akunModalLabel"><i class="fas fa-user-plus me-2 text-warning"></i>Tambah Akun Pengguna</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-dark bg-white">
                        <input type="hidden" name="id_user" id="id_user">

                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label fw-semibold" style="color: var(--space-cadet);">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap" id="nama_lengkap" required placeholder="Contoh: Maudy">
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold" style="color: var(--space-cadet);">Username</label>
                            <input type="text" class="form-control" name="username" id="username" required placeholder="Contoh: admin_maudy">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold" id="label_password" style="color: var(--space-cadet);">Password</label>
                            <input type="password" class="form-control" name="password" id="password" placeholder="Masukkan password baru">
                            <div class="form-text text-muted" id="hint_password">Password wajib diisi untuk pengguna baru.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color: var(--space-cadet);">Hak Akses (Role)</label>
                            <select class="form-select" name="role" id="role" required>
                                <option value="" disabled selected>-- Pilih Role --</option>
                                <option value="admin">Admin</option>
                                <option value="sales">Sales</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="simpan_akun" class="btn btn-custom-primary px-4 fw-bold shadow-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="hapusAkunModal" tabindex="-1" aria-labelledby="hapusAkunModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow text-dark">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="hapusAkunModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus Akun</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    Apakah Anda yakin ingin menghapus akun pengguna bernama <br><strong id="nama_akun_hapus" class="fs-5" style="color: var(--space-cadet);"></strong>?<br><br>
                    <span class="text-muted small">Tindakan ini tidak dapat dibatalkan dan data pengguna akan dihapus permanen.</span>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="link_konfirmasi_hapus" class="btn btn-danger px-4 fw-bold">Ya, Hapus Data</a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'modal_logout.php'; ?>

    <?php include 'modal_notifikasi.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>

    <script>
        window.addEventListener('DOMContentLoaded', event => {
            const datatablesSimple = document.getElementById('datatablesSimple');
            if (datatablesSimple) {
                new simpleDatatables.DataTable(datatablesSimple);
            }
        });

        const modalForm = new bootstrap.Modal(document.getElementById('akunModal'));
        const modalHapus = new bootstrap.Modal(document.getElementById('hapusAkunModal'));

        function tambahAkunModal() {
            document.getElementById('id_user').value = '';
            document.getElementById('nama_lengkap').value = '';
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
            document.getElementById('password').setAttribute('required', 'required');
            document.getElementById('role').value = '';

            document.getElementById('akunModalLabel').innerHTML = '<i class="fas fa-user-plus text-warning me-2"></i>Tambah Akun Pengguna';
            document.getElementById('label_password').innerText = 'Password';
            document.getElementById('hint_password').innerText = 'Password wajib diisi untuk pengguna baru.';

            modalForm.show();
        }

        function editAkunModal(id, username, nama_lengkap, role) {
            document.getElementById('id_user').value = id;
            document.getElementById('nama_lengkap').value = nama_lengkap;
            document.getElementById('username').value = username;
            document.getElementById('password').value = '';
            document.getElementById('password').removeAttribute('required');
            document.getElementById('role').value = role;

            document.getElementById('akunModalLabel').innerHTML = '<i class="fas fa-user-edit text-warning me-2"></i>Edit Akun Pengguna';
            document.getElementById('label_password').innerText = 'Password Baru (Opsional)';
            document.getElementById('hint_password').innerText = 'Biarkan kosong jika Anda tidak ingin mengubah password akun saat ini.';

            modalForm.show();
        }

        function konfirmasiHapusModal(id, namaLengkap) {
            document.getElementById('nama_akun_hapus').innerText = namaLengkap;
            document.getElementById('link_konfirmasi_hapus').setAttribute('href', 'kelola_admin.php?hapus=' + id);
            modalHapus.show();
        }
    </script>
</body>

</html>