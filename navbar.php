<?php
// Memastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Deteksi cerdas status dan role pengguna
$is_login = isset($_SESSION['login']) && $_SESSION['login'] === true;
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
$nama_user = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Pengguna';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --bg-lavender: #E0E1F6;
        --accent-indigo: #241F48;
        --accent-plum: #6C4773;
        --accent-gray: #B0B7CA;
        --soft-cream: #F8F9FA;
    }

    /* Efek Glassmorphism (Kaca Buram) untuk Navbar */
    .glass-navbar {
        background: rgba(224, 225, 246, 0.85) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.5);
        transition: all 0.3s ease;
    }

    .glass-navbar .navbar-brand strong {
        color: var(--accent-indigo);
        letter-spacing: 1px;
    }

    .glass-navbar .navbar-brand .brand-accent {
        color: var(--accent-plum) !important;
    }

    /* Animasi Hover Interaktif pada Menu */
    .glass-navbar .nav-link {
        color: var(--accent-indigo) !important;
        font-weight: 600;
        margin: 0 5px;
        position: relative;
        transition: 0.3s ease;
    }

    .glass-navbar .nav-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 3px;
        bottom: 0;
        left: 50%;
        background-color: var(--accent-plum);
        transition: all 0.3s ease;
        border-radius: 5px;
    }

    .glass-navbar .nav-link:hover::after,
    .glass-navbar .nav-link.active::after {
        width: 100%;
        left: 0;
    }

    .glass-navbar .nav-link:hover {
        color: var(--accent-plum) !important;
        transform: translateY(-2px);
    }

    /* Styling Tombol Dinamis */
    .btn-login-admin {
        color: var(--accent-indigo) !important;
        border: 2px solid var(--accent-indigo) !important;
        background-color: transparent !important;
        font-weight: 700;
        border-radius: 50rem;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .btn-login-admin:hover {
        background-color: var(--accent-indigo) !important;
        color: white !important;
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(36, 31, 72, 0.2);
    }

    .badge-user {
        background-color: white !important;
        color: var(--accent-indigo) !important;
        border: 1px solid var(--accent-gray);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
</style>

<nav class="navbar navbar-expand-lg glass-navbar shadow-sm sticky-top py-3">
    <div class="container px-4 px-lg-5">
        <a class="navbar-brand" href="index.php">
            <strong>RAHAYU</strong> <span class="brand-accent">CATALOGUE</span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars fs-3 text-dark"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <?php if (!$is_login) : ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item"><a class="nav-link" href="index.php#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#populer"><i class="fas fa-fire text-danger me-1"></i> Terpopuler</a></li>
                    <li class="nav-item"><a class="nav-link" href="katalog.php">Katalog Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="katalog_promo.php">Promo & Diskon</a></li>
                    <li class="nav-item"><a class="nav-link" href="bandingkan.php">Bandingkan</a></li>
                    <li class="nav-item"><a class="nav-link" href="lacak_pesanan.php">Lacak Pesanan</a></li>
                </ul>
                <div class="d-flex align-items-center mt-3 mt-lg-0">
                    <a href="login.php" class="btn btn-login-admin px-4 py-2">
                        <i class="fas fa-lock me-2"></i>Login Karyawan
                    </a>
                </div>

            <?php elseif ($role === 'sales') : ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item"><a class="nav-link" href="katalog.php"><i class="fas fa-box me-1"></i>Katalog Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="katalog_promo.php"><i class="fas fa-tags me-1"></i>Promo</a></li>
                    <li class="nav-item"><a class="nav-link" href="riwayat_sales.php"><i class="fas fa-history me-1"></i>Riwayat Order Saya</a></li>
                    <li class="nav-item"><a class="nav-link" href="bandingkan.php"><i class="fas fa-balance-scale me-1"></i>Bandingkan</a></li>
                    <li class="nav-item"><a class="nav-link" href="lacak_pesanan.php"><i class="fas fa-search-location me-1"></i>Lacak</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                    <span class="badge-user px-3 py-2 rounded-pill small fw-bold">
                        <i class="fas fa-user-tie me-2" style="color: var(--accent-plum);"></i>Sales: <?php echo htmlspecialchars($nama_user); ?>
                    </span>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="btn btn-danger fw-bold px-3 rounded-pill shadow-sm"><i class="fas fa-sign-out-alt"></i></a>
                </div>

            <?php elseif ($role === 'admin') : ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item"><a class="nav-link" href="dashboard_admin.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard Admin</a></li>
                    <li class="nav-item"><a class="nav-link" href="katalog.php">Lihat Katalog</a></li>
                    <li class="nav-item"><a class="nav-link" href="orders.php">Kelola Pesanan</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                    <span class="badge-user px-3 py-2 rounded-pill small fw-bold" style="border-color: #D32F2F;">
                        <i class="fas fa-user-shield me-2 text-danger"></i>Admin: <?php echo htmlspecialchars($nama_user); ?>
                    </span>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="btn btn-danger fw-bold px-3 rounded-pill shadow-sm"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</nav>

<?php if ($is_login) include 'modal_logout.php'; ?>

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            // Gunakan path relatif 'sw.js' agar aman dijalankan di dalam folder Laragon/XAMPP
            navigator.serviceWorker.register('sw.js')
                .then(reg => console.log('✅ Service Worker berhasil terdaftar. Mode Offline Siap!', reg))
                .catch(err => console.error('❌ Pendaftaran Service Worker gagal:', err));
        });
    }
</script>