<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

if (!isset($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id_santri = (int) $_GET["id"];

// Ambil data santri
/** @var mysqli $conn */
$stmt_santri = mysqli_prepare($conn, "SELECT nama_santri FROM santri WHERE id_santri = ?");
mysqli_stmt_bind_param($stmt_santri, "i", $id_santri);
mysqli_stmt_execute($stmt_santri);
$santri = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_santri));

if (!$santri) {
    header("Location: index.php");
    exit;
}

// Ambil riwayat pembayaran Madin santri tersebut
$sql = "SELECT 
            pembayaran_madin.*,
            users.nama AS nama_admin
        FROM pembayaran_madin
        INNER JOIN users ON pembayaran_madin.id_user = users.id_user
        WHERE pembayaran_madin.id_santri = ?
        ORDER BY pembayaran_madin.tahun DESC, pembayaran_madin.id_pembayaran_madin DESC";

$stmt_bayar = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt_bayar, "i", $id_santri);
mysqli_stmt_execute($stmt_bayar);
$result_bayar = mysqli_stmt_get_result($stmt_bayar);

include "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold m-0">Detail Pembayaran Madin</h2>
        <p class="text-muted m-0">Santri: <span class="text-dark fw-bold"><?= htmlspecialchars($santri["nama_santri"]) ?></span></p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary fw-semibold">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead class="table-light">
                <tr>
                    <th width="5%">No</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>Nominal</th>
                    <th>Tanggal Bayar</th>
                    <th>Status</th>
                    <th>Admin</th>
                    <th class="text-center" width="10%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                if (mysqli_num_rows($result_bayar) > 0) {
                    while ($data = mysqli_fetch_assoc($result_bayar)) {
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($data["bulan"]) ?></td>
                        <td><?= htmlspecialchars($data["tahun"]) ?></td>
                        <td class="fw-bold text-success">
                            Rp <?= number_format($data["nominal"], 0, ",", ".") ?>
                        </td>
                        <td><?= htmlspecialchars($data["tanggal_bayar"]) ?></td>
                        <td>
                            <?php if ($data["status"] == "Lunas") { ?>
                                <span class="badge bg-success">Lunas</span>
                            <?php } else { ?>
                                <span class="badge bg-danger">Belum Lunas</span>
                            <?php } ?>
                        </td>
                        <td><small class="text-muted"><?= htmlspecialchars($data["nama_admin"]) ?></small></td>
                        <td class="text-center">
                            <a href="hapus.php?id=<?= $data["id_pembayaran_madin"] ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Yakin ingin menghapus pembayaran bulan <?= $data["bulan"] ?> ini?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                <?php 
                    }
                } 
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>