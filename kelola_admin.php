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
            echo "<script>alert('Akun baru berhasil ditambahkan!'); window.location='kelola_admin.php';</script>";
        } else {
            echo "<script>alert('Gagal menambah akun: " . $koneksi->error . "');</script>";
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
            echo "<script>alert('Data akun berhasil diperbarui!'); window.location='kelola_admin.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui akun: " . $koneksi->error . "');</script>";
        }
    }
}

// Fitur Hapus Data (DELETE)
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Mencegah admin menghapus dirinya sendiri yang sedang login (Diselaraskan ke $_SESSION['user_id'])
    if (isset($_SESSION['user_id']) && $id_hapus == $_SESSION['user_id']) {
        echo "<script>alert('Anda tidak bisa menghapus akun Anda sendiri yang sedang aktif!'); window.location='kelola_admin.php';</script>";
        exit;
    }

    $query = "DELETE FROM users WHERE id_user = '$id_hapus'";
    if ($koneksi->query($query)) {
        echo "<script>alert('Akun berhasil dihapus!'); window.location='kelola_admin.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus akun: " . $koneksi->error . "');</script>";
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
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        
        <style>
            :root {
                --pastel-purple: #E0D2F0;
                --soft-cream: #FCF8F1;
                --olive-green: #7D8F37;
                --dark-olive: #5A6926;
                --text-dark: #4A4036;
            }
            body { background-color: var(--soft-cream); color: var(--text-dark); }
            .sb-topnav.navbar { background-color: var(--pastel-purple) !important; border-bottom: 2px solid var(--olive-green); }
            .sb-topnav .navbar-brand { color: var(--text-dark) !important; font-weight: 700; letter-spacing: 0.5px; }
            .sb-topnav .nav-link, .sb-topnav .btn-link { color: var(--text-dark) !important; }
            .sb-topnav .nav-link:hover, .sb-topnav .btn-link:hover { color: var(--dark-olive) !important; }
            .sb-sidenav-dark { background-color: #373029 !important; color: rgba(255, 255, 255, 0.75) !important; }
            .sb-sidenav-dark .sb-sidenav-menu-heading { color: var(--pastel-purple) !important; font-weight: 600; opacity: 0.8; }
            .sb-sidenav-dark .nav-link { color: rgba(255, 255, 255, 0.8) !important; }
            .sb-sidenav-dark .nav-link .sb-nav-link-icon { color: var(--pastel-purple) !important; }
            .sb-sidenav-dark .nav-link.active { color: white !important; background-color: var(--dark-olive) !important; }
            .sb-sidenav-dark .nav-link:hover { color: white !important; background-color: rgba(125, 143, 55, 0.3) !important; }
            .sb-sidenav-dark .sidenav-menu-nested .nav-link.active { background-color: transparent !important; color: var(--pastel-purple) !important; font-weight: bold; }
            .sb-sidenav-footer { background-color: #2b2520 !important; color: var(--pastel-purple) !important; }
            .card { border: 1px solid rgba(125, 143, 55, 0.15); }
            .card-header { background-color: rgba(224, 210, 240, 0.4) !important; color: var(--text-dark) !important; font-weight: 600; border-bottom: 1px solid rgba(125, 143, 55, 0.15); }
            .card-body bg-white { background-color: #ffffff !important; }
            footer.bg-light { background-color: var(--pastel-purple) !important; border-top: 1px solid rgba(125, 143, 55, 0.2); }
            footer .text-muted { color: var(--text-dark) !important; font-weight: 500; }
            h1, .breadcrumb-item.active { color: var(--text-dark) !important; }
        </style>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark">
            <a class="navbar-brand ps-3" href="dashboard_admin.php">RAHAYU ADMIN</a>
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <ul class="navbar-nav ms-auto me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i> <?php echo isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Administrator'; ?></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">Utama</div>
                            <a class="nav-link" href="dashboard_admin.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>Dashboard
                            </a>
                            <div class="sb-sidenav-menu-heading">Manajemen Data</div>
                            <a class="nav-link" href="kategori.php"><div class="sb-nav-link-icon"><i class="fas fa-folder"></i></div>Kelola Kategori</a>
                            
                            <a class="nav-link" href="produk.php"><div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>Kelola Produk</a>
                            <a class="nav-link" href="sales.php"><div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>Pengelolaan Sales</a>
                            <a class="nav-link" href="promo.php"><div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>Kelola Promo</a>
                            <a class="nav-link" href="orders.php"><div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>Pesanan Masuk</a>
                            <a class="nav-link" href="katalog.php"><div class="sb-nav-link-icon"><i class="fas fa-list-ul"></i></div>Katalog Produk</a>
                            <a class="nav-link" href="bandingkan.php"><div class="sb-nav-link-icon"><i class="fas fa-balance-scale"></i></div>Bandingkan Produk</a>
                            
                            <div class="sb-sidenav-menu-heading">Pengaturan Sistem</div>
                            <a class="nav-link active" href="kelola_admin.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div>Kelola Akun
                            </a>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div> <?php echo ucfirst($_SESSION['role'] ?? 'Admin'); ?>
                    </div>
                </nav>
            </div>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Pengelolaan Akun Pengguna</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard_admin.php" class="text-decoration-none text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item active">Kelola Akun Admin / Sales</li>
                        </ol>

                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-users-cog me-1"></i>
                                    Daftar Pengguna Sistem Informasi
                                </div>
                                <button type="button" class="btn btn-sm btn-success fw-bold px-3" onclick="tambahAkunModal()">
                                    <i class="fas fa-plus-circle me-1"></i> Tambah Akun Baru
                                </button>
                            </div>
                            <div class="card-body bg-white">
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
                                            <td><strong><?php echo htmlspecialchars($user['nama_lengkap']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td>
                                                <span class="badge <?php echo ($user['role'] == 'admin') ? 'bg-primary' : 'bg-info text-dark'; ?> text-capitalize px-3 py-2">
                                                    <i class="fas <?php echo ($user['role'] == 'admin') ? 'fa-user-shield' : 'fa-user-tag'; ?> me-1"></i>
                                                    <?php echo $user['role']; ?>
                                                </span>
                                            </td>
                                            <td class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-warning btn-sm mx-1 fw-500" 
                                                    onclick="editAkunModal(
                                                        '<?php echo $user['id_user']; ?>', 
                                                        '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>', 
                                                        '<?php echo htmlspecialchars($user['nama_lengkap'], ENT_QUOTES); ?>', 
                                                        '<?php echo $user['role']; ?>'
                                                    )">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm mx-1" 
                                                    onclick="konfirmasiHapusModal('<?php echo $user['id_user']; ?>', '<?php echo htmlspecialchars($user['nama_lengkap'], ENT_QUOTES); ?>')">
                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                </button>
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
                            <div class="text-muted">PT Rahayu Karunia Utama &copy; Rahayu</div>
                        </div>
                    </div>
                </footer >
            </div>
        </div>

        <!-- Modal Tambah / Edit -->
        <div class="modal fade" id="akunModal" tabindex="-1" aria-labelledby="akunModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-dark">
                    <form action="kelola_admin.php" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="akunModalLabel"><i class="fas fa-user-plus me-2"></i>Tambah Akun Pengguna</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_user" id="id_user">

                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label fw-bold">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" id="nama_lengkap" required placeholder="Contoh: Maudy">
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label fw-bold">Username</label>
                                <input type="text" class="form-control" name="username" id="username" required placeholder="Contoh: admin_maudy">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold" id="label_password">Password</label>
                                <input type="password" class="form-control" name="password" id="password" placeholder="Masukkan password baru">
                                <div class="form-text text-muted" id="hint_password">Password wajib diisi untuk pengguna baru.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Hak Akses (Role)</label>
                                <select class="form-select" name="role" id="role" required>
                                    <option value="" disabled selected>-- Pilih Role --</option>
                                    <option value="admin">Admin</option>
                                    <option value="sales">Sales</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="simpan_akun" class="btn btn-success px-4">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Hapus -->
        <div class="modal fade" id="hapusAkunModal" tabindex="-1" aria-labelledby="hapusAkunModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-dark">
                    <div class="modal-header">
                        <h5 class="modal-title" id="hapusAkunModalLabel"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Konfirmasi Hapus Akun</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Apakah Anda yakin ingin menghapus akun pengguna bernama <strong id="nama_akun_hapus" class="text-danger"></strong>? Tindakan ini tidak dapat dibatalkan dan data pengguna akan dihapus permanen.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <a href="#" id="link_konfirmasi_hapus" class="btn btn-danger px-4">Ya, Hapus Data</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Logout -->
        <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-dark">
                    <div class="modal-header">
                        <h5 class="modal-title" id="logoutModalLabel"><i class="fas fa-sign-out-alt text-danger me-2"></i>Konfirmasi Keluar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Apakah Anda yakin ingin keluar dari sistem logistik RAHAYU ADMIN? Sesi aktif Anda akan segera diakhiri.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <a href="logout.php" class="btn btn-danger">Ya, Keluar</a>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        
        <script>
            // Inisialisasi DataTables
            window.addEventListener('DOMContentLoaded', event => {
                const datatablesSimple = document.getElementById('datatablesSimple');
                if (datatablesSimple) {
                    new simpleDatatables.DataTable(datatablesSimple);
                }
            });

            // Instance modal Bootstrap 5
            const modalForm = new bootstrap.Modal(document.getElementById('akunModal'));
            const modalHapus = new bootstrap.Modal(document.getElementById('hapusAkunModal'));

            // Fungsi tambah akun
            function tambahAkunModal() {
                document.getElementById('id_user').value = '';
                document.getElementById('nama_lengkap').value = '';
                document.getElementById('username').value = '';
                document.getElementById('password').value = '';
                document.getElementById('password').setAttribute('required', 'required');
                document.getElementById('role').value = '';
                
                document.getElementById('akunModalLabel').innerHTML = '<i class="fas fa-user-plus text-success me-2"></i>Tambah Akun Pengguna';
                document.getElementById('label_password').innerText = 'Password';
                document.getElementById('hint_password').innerText = 'Password wajib diisi untuk pengguna baru.';
                
                modalForm.show();
            }

            // Fungsi edit akun
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

            // Fungsi konfirmasi hapus akun
            function konfirmasiHapusModal(id, namaLengkap) {
                document.getElementById('nama_akun_hapus').innerText = namaLengkap;
                document.getElementById('link_konfirmasi_hapus').setAttribute('href', 'kelola_admin.php?hapus=' + id);
                modalHapus.show();
            }
        </script>
    </body>
</html>