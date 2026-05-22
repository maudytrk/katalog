<?php
// Memastikan session dimulai, jika di file utama belum di-start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menentukan status login dan role pengguna
$is_login = isset($_SESSION['login']) && $_SESSION['login'] === true;
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'customer';
$nama_user = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Sales';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --pastel-purple: #E0D2F0;
        --soft-cream: #FCF8F1;
        --olive-green: #7D8F37;
        --dark-olive: #5A6926;
        --text-dark: #4A4036;
    }
    
    /* Override Bootstrap Navbar agar mengikuti skema warna customer.php */
    .custom-navbar {
        background-color: var(--pastel-purple) !important;
        border-bottom: none;
    }
    .custom-navbar .navbar-brand strong {
        color: var(--text-dark);
    }
    .custom-navbar .navbar-brand .brand-accent {
        color: var(--olive-green) !important;
    }
    .custom-navbar .nav-link {
        color: var(--text-dark) !important;
        font-weight: 500;
    }
    .custom-navbar .nav-link:hover, 
    .custom-navbar .nav-link.active {
        color: var(--olive-green) !important;
    }
    
    /* Komponen Tombol Bertema Olive Green */
    .btn-theme-olive {
        color: var(--olive-green) !important;
        border-color: var(--olive-green) !important;
        background-color: transparent !important;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-theme-olive:hover {
        background-color: var(--olive-green) !important;
        color: white !important;
    }
    .btn-theme-olive-filled {
        background-color: var(--olive-green) !important;
        border-color: var(--olive-green) !important;
        color: white !important;
        font-weight: 600;
    }
    .btn-theme-olive-filled:hover {
        background-color: var(--dark-olive) !important;
        border-color: var(--dark-olive) !important;
    }
    
    /* Badge Informasi Akun */
    .badge-user-sales {
        background-color: white !important;
        color: var(--text-dark) !important;
        border: 1px solid var(--olive-green);
    }
</style>

<nav class="navbar navbar-expand-lg navbar-light custom-navbar shadow-sm sticky-top py-3">
    <div class="container px-4 px-lg-5">
        <a class="navbar-brand" href="index.php#home">
            <strong>RAHAYU</strong> <span class="brand-accent">CATALOGUE</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            
            <?php if (!$is_login || $role !== 'sales') : ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="index.php#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#populer">🔥 Terpopuler</a></li>
                    <li class="nav-item"><a class="nav-link" href="katalog.php">Katalog Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="katalog_promo.php">Promo & Diskon</a></li>
                    <li class="nav-item"><a class="nav-link" href="bandingkan.php">Bandingkan</a></li>
                    <li class="nav-item"><a class="nav-link" href="lacak_pesanan.php">Lacak Pesanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#about">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="login.php" class="btn btn-theme-olive px-4 rounded-pill shadow-sm">
                        <i class="fas fa-sign-in-alt me-2"></i>Admin Login
                    </a>
                </div>

            <?php else : ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item">
                        <a class="nav-link" href="katalog.php"><i class="fas fa-box me-1"></i>Katalog Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="katalog_promo.php"><i class="fas fa-tags me-1"></i>Promo & Diskon</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="riwayat_sales.php"><i class="fas fa-history me-1"></i>Riwayat Order Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bandingkan.php"><i class="fas fa-balance-scale me-1"></i>Bandingkan Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="lacak_pesanan.php"><i class="fas fa-box-open me-1"></i>Lacak Pesanan</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <span class="badge-user-sales px-3 py-2 rounded-3 small">
                        <i class="fas fa-user-circle me-2 text-warning"></i>Halo, <strong><?php echo htmlspecialchars($nama_user); ?></strong>
                    </span>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal" class="btn btn-danger fw-bold px-3 rounded-pill shadow-sm">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</nav>

<?php include 'modal_logout.php'; ?>