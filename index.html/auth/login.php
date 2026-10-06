<?php
// Aktifkan pemantauan error sementara
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (isset($_SESSION["id_user"])) {
    header("Location: ../admin/dashboard.php");
    exit;
}

// Pastikan file koneksi ada
if (!file_exists("../config/koneksi.php")) {
    die("Error: File config/koneksi.php tidak ditemukan!");
}
require_once "../config/koneksi.php";

$error = "";

if (isset($_POST["login"])) {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        $error = "Username dan Password tidak boleh kosong.";
    } else {
        $sql = "SELECT * FROM users WHERE username = ?";
        /** @var mysqli $conn */
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($user = mysqli_fetch_assoc($result)) {
                if (password_verify($password, $user["password"])) {
                    $_SESSION["id_user"] = $user["id_user"];
                    $_SESSION["nama"] = $user["nama"];

                    header("Location: ../admin/dashboard.php");
                    exit;
                } else {
                    $error = "Password salah.";
                }
            } else {
                $error = "Username tidak ditemukan.";
            }
        } else {
            $error = "Gagal memproses query database.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Keuangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 380px;
            background: #ffffff;
        }
        .brand-avatar {
            width: 60px;
            height: 60px;
            background-color: #e7f1ff;
            color: #0d6efd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px auto;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>

<div class="card login-card p-4">
    <div class="text-center mb-4">
        <div class="brand-avatar">
            <i class="fa-solid fa-wallet"></i>
        </div>
        <h5 class="fw-bold m-0 text-dark">Sistem Keuangan</h5>
        <p class="text-muted small m-0">Akses Masuk Administrator</p>
    </div>

    <?php if ($error != "") { ?>
        <div class="alert alert-danger py-2 small" role="alert">
            <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php } ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label fw-bold small text-secondary">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                <input type="text" name="username" class="form-control bg-light border-start-0" placeholder="Masukkan username" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold small text-secondary">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                <input type="password" name="password" class="form-control bg-light border-start-0" placeholder="Masukkan password" required>
            </div>
        </div>

        <button type="submit" name="login" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
            Masuk <i class="fa-solid fa-arrow-right-to-bracket ms-1"></i>
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>