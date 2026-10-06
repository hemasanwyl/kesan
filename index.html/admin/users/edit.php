<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

/** @var mysqli $conn */

$id_user_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = "";

// Ambil Data Admin saat ini
$sql_user = "SELECT * FROM users WHERE id_user = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $id_user_edit);
mysqli_stmt_execute($stmt_user);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_user));

if (!$user) {
    header("Location: index.php");
    exit;
}

if (isset($_POST["update"])) {
    $nama = trim($_POST["nama"]);
    $username = trim($_POST["username"]);
    $password_baru = $_POST["password_baru"];

    if (empty($nama) || empty($username)) {
        $error = "Nama dan Username tidak boleh kosong!";
    } else {
        // Cek apakah username sudah dipakai oleh user lain
        $sql_cek = "SELECT id_user FROM users WHERE username = ? AND id_user != ?";
        $stmt_cek = mysqli_prepare($conn, $sql_cek);
        mysqli_stmt_bind_param($stmt_cek, "si", $username, $id_user_edit);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);

        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $error = "Username sudah digunakan oleh akun lain!";
        } else {
            if (!empty($password_baru)) {
                $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET nama = ?, username = ?, password = ? WHERE id_user = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssi", $nama, $username, $password_hash, $id_user_edit);
            } else {
                $sql = "UPDATE users SET nama = ?, username = ? WHERE id_user = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssi", $nama, $username, $id_user_edit);
            }

            if (mysqli_stmt_execute($stmt)) {
                // Jika mengedit akun sendiri, update session
                if ($id_user_edit == $_SESSION['id_user']) {
                    $_SESSION['nama'] = $nama;
                }

                $_SESSION['msg_success'] = "Data admin berhasil diperbarui!";
                header("Location: index.php");
                exit;
            } else {
                $error = "Gagal memperbarui data admin.";
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
                <h4 class="fw-bold m-0">Edit Data Admin</h4>
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
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Password Baru</label>
                    <input type="password" name="password_baru" class="form-control" placeholder="Kosongkan jika tidak ingin diubah">
                </div>

                <button type="submit" name="update" class="btn btn-primary w-100 fw-bold py-2">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>