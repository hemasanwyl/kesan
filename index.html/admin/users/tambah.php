<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

/** @var mysqli $conn */

$error = "";

if (isset($_POST["simpan"])) {
    $nama = trim($_POST["nama"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($nama) || empty($username) || empty($password)) {
        $error = "Semua bidang wajib diisi!";
    } else {
        // Cek apakah username sudah dipakai
        $sql_cek = "SELECT id_user FROM users WHERE username = ?";
        $stmt_cek = mysqli_prepare($conn, $sql_cek);
        mysqli_stmt_bind_param($stmt_cek, "s", $username);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);

        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $error = "Username sudah digunakan, cari username lain!";
        } else {
            // Hash password secara aman
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (nama, username, password) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $nama, $username, $password_hash);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['msg_success'] = "Admin baru berhasil ditambahkan!";
                header("Location: index.php");
                exit;
            } else {
                $error = "Gagal menyimpan data admin.";
            }
        }
    }
}

include "../includes/header.php";
?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold m-0">Tambah Admin Baru</h4>
                <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Kembali</a>
            </div>
            <hr>

            <?php if ($error != "") { ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php } ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Ahmad Subagja" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Contoh: ahmad123" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>

                <button type="submit" name="simpan" class="btn btn-primary w-100 fw-bold py-2">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Admin
                </button>
            </form>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>