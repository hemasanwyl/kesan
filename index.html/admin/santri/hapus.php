<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

if (isset($_GET["id"])) {
    $id_santri = (int) $_GET["id"];

    $sql = "DELETE FROM santri WHERE id_santri = ?";
    /** @var mysqli $conn */
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_santri);
    mysqli_stmt_execute($stmt);
}

header("Location: index.php");
exit;