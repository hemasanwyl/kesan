<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

$id_santri = (int) ($_GET["id"] ?? 0);
/** @var mysqli $conn */

/* DETAIL SANTRI */
$sql_santri = "SELECT santri.*, jenjang.nama_jenjang 
               FROM santri 
               INNER JOIN jenjang ON santri.id_jenjang = jenjang.id_jenjang 
               WHERE santri.id_santri = ?";
$stmt = mysqli_prepare($conn, $sql_santri);
mysqli_stmt_bind_param($stmt, "i", $id_santri);
mysqli_stmt_execute($stmt);
$santri = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$santri) {
    header("Location: index.php");
    exit;
}

$wali_val = $santri["nama_wali"] ?? $santri["nama_ortu"] ?? "-";

/* RIWAYAT TRANSAKSI SANGU SANTRI TERSEBUT */
$sql_transaksi = "SELECT transaksi_sangu.*, users.nama AS nama_admin
                  FROM transaksi_sangu
                  INNER JOIN users ON transaksi_sangu.id_user = users.id_user
                  WHERE transaksi_sangu.id_santri = ?
                  ORDER BY transaksi_sangu.tanggal DESC, transaksi_sangu.id_transaksi_sangu DESC";
$stmt_trans = mysqli_prepare($conn, $sql_transaksi);
mysqli_stmt_bind_param($stmt_trans, "i", $id_santri);
mysqli_stmt_execute($stmt_trans);
$result_trans = mysqli_stmt_get_result($stmt_trans);

/* TOTAL SALDO SANTRI */
$sql_total = "SELECT 
                COALESCE(SUM(CASE WHEN jenis = 'Masuk' THEN nominal ELSE 0 END), 0) AS masuk,
                COALESCE(SUM(CASE WHEN jenis = 'Keluar' THEN nominal ELSE 0 END), 0) AS keluar
              FROM transaksi_sangu WHERE id_santri = ?";
$stmt_tot = mysqli_prepare($conn, $sql_total);
mysqli_stmt_bind_param($stmt_tot, "i", $id_santri);
mysqli_stmt_execute($stmt_tot);
$tot = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_tot));
$saldo_santri = ($tot["masuk"] ?? 0) - ($tot["keluar"] ?? 0);

include "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold m-0">Detail Uang Sangu Santri</h2>
        <p class="text-muted m-0">Riwayat transaksi uang sangu untuk <?= htmlspecialchars($santri["nama_santri"]) ?></p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary fw-bold">&larr; Kembali</a>
</div>

<!-- PROFILE SANTRI CARD -->
<div class="card card-custom p-4 mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h4 class="fw-bold text-primary mb-1"><?= htmlspecialchars($santri["nama_santri"]) ?></h4>
            <p class="text-muted mb-0">Jenjang: <strong><?= htmlspecialchars($santri["nama_jenjang"]) ?></strong> | Wali: <strong><?= htmlspecialchars($wali_val) ?></strong></p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <small class="text-muted d-block fw-bold">TOTAL SALDO SANGU</small>
            <h3 class="text-success fw-bold m-0">Rp <?= number_format($saldo_santri, 0, ",", ".") ?></h3>
        </div>
    </div>
</div>

<!-- RIWAYAT DETAIL -->
<div class="card card-custom p-4">
    <h5 class="fw-bold mb-3">Catatan Setor & Tarik Uang Sangu</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead class="table-light">
                <tr>
                    <th width="5%">No</th>
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
                if ($result_trans && mysqli_num_rows($result_trans) > 0) {
                    while ($row = mysqli_fetch_assoc($result_trans)) { 
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row["tanggal"]) ?></td>
                        <td>
                            <?php if ($row["jenis"] == "Masuk") { ?>
                                <span class="badge bg-success">Masuk</span>
                            <?php } else { ?>
                                <span class="badge bg-danger">Keluar</span>
                            <?php } ?>
                        </td>
                        <td class="fw-bold <?= $row["jenis"] == "Masuk" ? "text-success" : "text-danger" ?>">
                            Rp <?= number_format($row["nominal"], 0, ",", ".") ?>
                        </td>
                        <td><?= htmlspecialchars($row["keterangan"] ?? '-') ?></td>
                        <td><small class="text-muted"><?= htmlspecialchars($row["nama_admin"]) ?></small></td>
                    </tr>
                <?php 
                    }
                } 
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>