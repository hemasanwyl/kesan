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

/* ==============================
   CEK ID SANTRI
   ============================== */
if (!isset($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id_santri = (int) $_GET["id"];

/* ==============================
   DATA SANTRI
   ============================== */
$sql_santri = "SELECT id_santri, nama_santri FROM santri WHERE id_santri = ?";
/** @var mysqli $conn */
$stmt_santri = mysqli_prepare($conn, $sql_santri);
mysqli_stmt_bind_param($stmt_santri, "i", $id_santri);
mysqli_stmt_execute($stmt_santri);
$result_santri = mysqli_stmt_get_result($stmt_santri);
$santri = mysqli_fetch_assoc($result_santri);

if (!$santri) {
    die("Data santri tidak ditemukan.");
}

/* ==============================
   TOTAL TABUNGAN
   ============================== */
$sql_total = "SELECT
                COALESCE(SUM(CASE WHEN jenis = 'Masuk' THEN nominal ELSE 0 END), 0) AS total_masuk,
                COALESCE(SUM(CASE WHEN jenis = 'Keluar' THEN nominal ELSE 0 END), 0) AS total_keluar
              FROM transaksi_tabungan
              WHERE id_santri = ?";

$stmt_total = mysqli_prepare($conn, $sql_total);
mysqli_stmt_bind_param($stmt_total, "i", $id_santri);
mysqli_stmt_execute($stmt_total);
$result_total = mysqli_stmt_get_result($stmt_total);
$total = mysqli_fetch_assoc($result_total);

$total_masuk = $total["total_masuk"];
$total_keluar = $total["total_keluar"];
$saldo = $total_masuk - $total_keluar;

/* ==============================
   RIWAYAT TRANSAKSI
   ============================== */
$sql_transaksi = "SELECT
                    transaksi_tabungan.*,
                    users.nama AS nama_admin
                  FROM transaksi_tabungan
                  INNER JOIN users ON transaksi_tabungan.id_user = users.id_user
                  WHERE transaksi_tabungan.id_santri = ?
                  ORDER BY transaksi_tabungan.tanggal DESC, transaksi_tabungan.id_transaksi_tabungan DESC";

$stmt_transaksi = mysqli_prepare($conn, $sql_transaksi);
mysqli_stmt_bind_param($stmt_transaksi, "i", $id_santri);
mysqli_stmt_execute($stmt_transaksi);
$result_transaksi = mysqli_stmt_get_result($stmt_transaksi);

include "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Detail Tabungan</h4>
        <p class="text-muted small mb-0">Riwayat & Saldo: <strong class="text-dark"><?= htmlspecialchars($santri["nama_santri"]) ?></strong></p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary fw-semibold">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<!-- CARDS RINGKASAN -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-custom border-start border-success border-4 h-100">
            <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Masuk</div>
                <h3 class="fw-bold text-success mb-0">Rp <?= number_format($total_masuk, 0, ",", ".") ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom border-start border-danger border-4 h-100">
            <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Keluar</div>
                <h3 class="fw-bold text-danger mb-0">Rp <?= number_format($total_keluar, 0, ",", ".") ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom border-start border-primary border-4 h-100">
            <div class="card-body">
                <div class="text-muted small fw-bold text-uppercase mb-1">Saldo Saat Ini</div>
                <h3 class="fw-bold text-primary mb-0">Rp <?= number_format($saldo, 0, ",", ".") ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- TABEL RIWAYAT TRANSAKSI -->
<div class="card card-custom">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Riwayat Transaksi</h5>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" width="5%">#</th>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Nominal</th>
                        <th>Keterangan</th>
                        <th>Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if (mysqli_num_rows($result_transaksi) > 0) {
                        while ($data = mysqli_fetch_assoc($result_transaksi)) {
                            $is_masuk = ($data["jenis"] == "Masuk");
                    ?>
                        <tr>
                            <td class="ps-4"><?= $no++ ?></td>
                            <td><?= date('d-m-Y', strtotime($data["tanggal"])) ?></td>
                            <td>
                                <span class="badge bg-<?= $is_masuk ? 'success' : 'danger' ?>-subtle text-<?= $is_masuk ? 'success' : 'danger' ?> border border-<?= $is_masuk ? 'success' : 'danger' ?>-subtle">
                                    <?= htmlspecialchars($data["jenis"]) ?>
                                </span>
                            </td>
                            <td class="fw-bold text-<?= $is_masuk ? 'success' : 'danger' ?>">
                                <?= $is_masuk ? '+' : '-' ?> Rp <?= number_format($data["nominal"], 0, ",", ".") ?>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($data["keterangan"] ?? '-') ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($data["nama_admin"]) ?></span></td>
                <?php 
                        }
                    } 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>