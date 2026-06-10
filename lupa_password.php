<?php
session_start();
require 'koneksi.php';

$step = 1;
$error = false;
$error_msg = '';

// Langkah 1: Verifikasi Data Pengguna (Username & Nama)
if (isset($_POST['cek_user'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);

    $query = mysqli_query($koneksi, "SELECT id_user FROM users WHERE username = '$username' AND nama_lengkap = '$nama_lengkap'");

    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);
        $_SESSION['reset_user_id'] = $row['id_user'];
        $step = 2; // Lolos, pindah ke form pembuatan password baru
    } else {
        $error = true;
        $error_msg = 'Username atau Nama Lengkap tidak cocok dengan data terdaftar!';
    }
}

// Langkah 2: Proses Ganti Password Baru
if (isset($_POST['reset_password'])) {
    if (isset($_SESSION['reset_user_id'])) {
        $id_user = $_SESSION['reset_user_id'];
        $password_baru = $_POST['password_baru'];
        $konfirmasi = $_POST['konfirmasi_password'];

        if (strlen($password_baru) < 6) {
            $error = true;
            $error_msg = 'Password minimal harus 6 karakter!';
            $step = 2;
        } elseif ($password_baru !== $konfirmasi) {
            $error = true;
            $error_msg = 'Konfirmasi password tidak cocok!';
            $step = 2;
        } else {
            // Enkripsi dan Simpan
            $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
            $update = mysqli_query($koneksi, "UPDATE users SET password = '$password_hash' WHERE id_user = '$id_user'");

            if ($update) {
                unset($_SESSION['reset_user_id']); // Bersihkan session reset
                echo "<script>alert('Password berhasil direset! Silakan login dengan password baru Anda.'); window.location.href='login.php';</script>";
                exit;
            } else {
                $error = true;
                $error_msg = 'Terjadi kesalahan sistem. Gagal mereset password.';
                $step = 2;
            }
        }
    } else {
        $step = 1;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lupa Password - Rahayu</title>
    <?php include 'pwa_meta.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --rahayu-purple: #E0D7FF;
            --rahayu-dark-purple: #5A4E8C;
        }

        body,
        html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .info-panel {
            background-color: var(--rahayu-purple);
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px;
            position: relative;
        }

        .info-panel h1 {
            font-weight: 800;
            color: #2D2D2D;
            margin-bottom: 2rem;
            font-size: 1.8rem;
        }

        .info-panel h2 {
            font-weight: 700;
            color: #333;
            line-height: 1.2;
        }

        .info-panel h2 span {
            color: var(--rahayu-dark-purple);
        }

        .info-panel p {
            color: #555;
            font-size: 1.1rem;
            margin-top: 20px;
            max-width: 450px;
        }

        .copyright {
            position: absolute;
            bottom: 40px;
            left: 80px;
            font-size: 0.8rem;
            color: #666;
        }

        .login-panel {
            background-color: #ffffff;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-form-wrapper {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .login-form-wrapper h3 {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .login-form-wrapper p.subtitle {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .form-control {
            background-color: #F9F9F9;
            border: 1px solid #eee;
            padding: 12px 15px;
            border-radius: 8px;
        }

        .input-group-text {
            background-color: #F9F9F9;
            border: 1px solid #eee;
        }

        .btn-login {
            background-color: var(--rahayu-purple);
            border: none;
            color: #5A4E8C;
            font-weight: 600;
            padding: 12px;
            width: 100%;
            border-radius: 25px;
            margin-top: 20px;
            transition: 0.3s;
        }

        .btn-login:hover {
            background-color: #d1c5ff;
            color: #40366b;
        }

        .forgot-link {
            text-decoration: none;
            font-size: 0.85rem;
            color: #bbb;
        }

        @media (max-width: 992px) {
            .info-panel {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="info-panel">
            <h1>RAHAYU MANAGEMENT SYSTEM</h1>
            <h2>Security Verification<br><span>Reset Password.</span></h2>
            <p>Fitur keamanan mandiri untuk memulihkan akses Anda ke sistem. Pastikan data yang dimasukkan sesuai dengan yang terdaftar di database.</p>
            <div class="copyright">PT Rahayu Karunia Utama © Rahayu</div>
        </div>

        <div class="login-panel">
            <div class="login-form-wrapper">
                <h3>Lupa Password?</h3>
                <p class="subtitle">
                    <?php echo ($step == 1) ? 'Verifikasi identitas Anda untuk mereset password.' : 'Silakan masukkan password baru Anda.'; ?>
                </p>

                <?php if ($error) : ?>
                    <div class="alert alert-danger py-2" style="font-size: 0.85rem;"><i class="fas fa-exclamation-triangle me-1"></i> <?php echo $error_msg; ?></div>
                <?php endif; ?>

                <?php if ($step == 1) : ?>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="small mb-1 text-muted">Username Terdaftar</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                                <input class="form-control" name="username" type="text" placeholder="Masukkan username" required />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small mb-1 text-muted">Nama Lengkap (Sesuai Akun)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-regular fa-id-badge"></i></span>
                                <input class="form-control" name="nama_lengkap" type="text" placeholder="Contoh: Budi Santoso" required />
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <a class="forgot-link text-primary fw-bold" href="login.php">← Kembali ke Login</a>
                        </div>

                        <button type="submit" name="cek_user" class="btn btn-login">VERIFIKASI DATA →</button>
                    </form>

                <?php else : ?>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="small mb-1 text-muted">Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input class="form-control" name="password_baru" type="password" placeholder="Minimal 6 karakter" required minlength="6" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small mb-1 text-muted">Ulangi Password Baru</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-check"></i></span>
                                <input class="form-control" name="konfirmasi_password" type="password" placeholder="Ketik ulang password baru" required minlength="6" />
                            </div>
                        </div>

                        <button type="submit" name="reset_password" class="btn btn-login" style="background-color: var(--rahayu-dark-purple); color: white;">SIMPAN PASSWORD BARU <i class="fas fa-save ms-1"></i></button>
                    </form>
                <?php endif; ?>

            </div>
        </div>
    </div>
</body>

</html>