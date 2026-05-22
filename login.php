<?php
session_start();
require 'koneksi.php';
$error = false;

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);

        // Cek dengan Bcrypt (password_verify) ATAU SHA256 (hash)
        if (password_verify($password, $row['password']) || hash('sha256', $password) === $row['password']) {
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $row['id_user'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['nama'] = $row['nama_lengkap'];

            if ($row['role'] === 'admin') {
                header("Location: dashboard_admin.php");
            } else {
                header("Location: katalog.php");
            }
            exit;
        }
    }
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Rahayu Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --rahayu-purple: #E0D7FF; 
            --rahayu-dark-purple: #5A4E8C;
        }
        body, html {
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
            text-transform: uppercase;
            font-size: 1.8rem;
        }
        .info-panel h2 {
            font-weight: 700;
            color: #333;
            line-height: 1.2;
        }
        .info-panel h2 span { color: var(--rahayu-dark-purple); }
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
        .login-form-wrapper h3 { font-weight: 700; margin-bottom: 5px; }
        .login-form-wrapper p.subtitle { color: #888; font-size: 0.9rem; margin-bottom: 30px; }
        .form-control { background-color: #F9F9F9; border: 1px solid #eee; padding: 12px 15px; border-radius: 8px; }
        .input-group-text { background-color: #F9F9F9; border: 1px solid #eee; }
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
        .btn-login:hover { background-color: #d1c5ff; color: #40366b; }
        .forgot-link { text-decoration: none; font-size: 0.85rem; color: #bbb; }
        @media (max-width: 992px) { .info-panel { display: none; } }
    </style>
</head>
<body>

<div class="login-container">
    <div class="info-panel">
        <h1>RAHAYU MANAGEMENT SYSTEM</h1>
        <h2>Optimize, Scale, and Grow<br>with <span>RAHAYU Management System.</span></h2>
        <p>A sophisticated administrative platform designed to centralize control, streamline complex workflows, and provide actionable insights for enterprise scalability.</p>
        <div class="copyright">PT Rahayu Karunia Utama © Rahayu</div>
    </div>

    <div class="login-panel">
        <div class="login-form-wrapper">
            <h3>Admin Portal Login</h3>
            <p class="subtitle">Enter your administrator credentials to access the system.</p>

            <?php if ($error) : ?>
                <div class="alert alert-danger py-2" style="font-size: 0.85rem;">Username atau Password salah!</div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label class="small mb-1 text-muted">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                        <input class="form-control" name="username" type="text" placeholder="Masukkan username" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="small mb-1 text-muted">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input class="form-control" name="password" type="password" placeholder="********" required />
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label small text-muted" for="remember">Ingat saya</label>
                    </div>
                    <a class="forgot-link" href="#">Lupa Password?</a>
                </div>

                <button type="submit" name="login" class="btn btn-login">MASUK KE DASHBOARD →</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>