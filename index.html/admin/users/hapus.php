<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

$error = "";

if (isset($_POST["simpan"])) {
    $nama = trim($_POST["nama"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($nama) || empty($username) || empty($password)) {
        $error = "Semua bidang wajib diisi.";
    } else {
        // Cek username duplikat
        $sql_cek = "SELECT id_user FROM users WHERE username = ?";
        /** @var mysqli $conn */
        $stmt_cek = mysqli_prepare($conn, $sql_cek);
        mysqli_stmt_bind_param($stmt_cek, "s", $username);
        mysqli_stmt_execute($stmt_cek);
        $res = mysqli_stmt_get_result($stmt_cek);

        if (mysqli_num_rows($res) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql_insert = "INSERT INTO users (nama, username, password) VALUES (?, ?, ?)";
            $stmt_ins = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_ins, "sss", $nama, $username, $pass_hash);

            if (mysqli_stmt_execute($stmt_ins)) {
                header("Location: index.php");
                exit;
            } else {
                $error = "Gagal menambah admin.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Admin - Kesan</title>
</head>
<body>

<h1>Tambah Admin Baru</h1>

<a href="index.php">← Kembali</a>
<br><br>

<?php if ($error != "") { ?>
    <p><?= htmlspecialchars($error) ?></p>
<?php } ?>

<form method="POST">
    <label>Nama Lengkap</label><br>
    <input type="text" name="nama" required><br><br>

    <label>Username</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit" name="simpan">Simpan Admin</button>
</form>

</body>
</html>