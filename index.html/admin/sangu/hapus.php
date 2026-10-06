<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

if (isset($_GET["id"])) {
    $id = (int) $_GET["id"];

    $sql = "DELETE FROM transaksi_sangu WHERE id_transaksi_sangu = ?";
    /** @var mysqli $conn */
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
}

header("Location: index.php");
exit;