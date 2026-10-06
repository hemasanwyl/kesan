<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

/** @var mysqli $conn */

// Parameter Pencarian
$q = trim($_GET["q"] ?? "");
$is_filtered = ($q !== "");

/* RIWAYAT TRANSAKSI SANGU */
$sql = "SELECT transaksi_sangu.*, santri.nama_santri, users.nama AS nama_admin
        FROM transaksi_sangu
        INNER JOIN santri ON transaksi_sangu.id_santri = santri.id_santri
        INNER JOIN users ON transaksi_sangu.id_user = users.id_user";

if ($is_filtered) {
    $sql .= " WHERE (santri.nama_santri LIKE ? OR transaksi_sangu.keterangan LIKE ?)";
}

$sql .= " ORDER BY transaksi_sangu.tanggal DESC, transaksi_sangu.id_transaksi_sangu DESC";

if ($is_filtered) {
    $stmt = mysqli_prepare($conn, $sql);
    $term = "%" . $q . "%";
    mysqli_stmt_bind_param($stmt, "ss", $term, $term);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}

/* TOTAL KESELURUHAN */
$sql_total = "SELECT
                COALESCE(SUM(CASE WHEN jenis = 'Masuk' THEN nominal ELSE 0 END), 0) AS total_masuk,
                COALESCE(SUM(CASE WHEN jenis = 'Keluar' THEN nominal ELSE 0 END), 0) AS total_keluar
              FROM transaksi_sangu";
$result_total = mysqli_query($conn, $sql_total);
$total = mysqli_fetch_assoc($result_total);
$saldo = ($total["total_masuk"] ?? 0) - ($total["total_keluar"] ?? 0);

/* SALDO PER SANTRI */
$sql_saldo = "SELECT santri.id_santri, santri.nama_santri,
                COALESCE(SUM(CASE WHEN transaksi_sangu.jenis = 'Masuk' THEN transaksi_sangu.nominal ELSE 0 END), 0) AS total_masuk,
                COALESCE(SUM(CASE WHEN transaksi_sangu.jenis = 'Keluar' THEN transaksi_sangu.nominal ELSE 0 END), 0) AS total_keluar
              FROM santri
              LEFT JOIN transaksi_sangu ON santri.id_santri = transaksi_sangu.id_santri
              WHERE santri.status = 'Aktif'";

if ($is_filtered) {
    $sql_saldo .= " AND santri.nama_santri LIKE ?";
}

$sql_saldo .= " GROUP BY santri.id_santri, santri.nama_santri ORDER BY santri.nama_santri ASC";

if ($is_filtered) {
    $stmt_saldo = mysqli_prepare($conn, $sql_saldo);
    $term = "%" . $q . "%";
    mysqli_stmt_bind_param($stmt_saldo, "s", $term);
    mysqli_stmt_execute($stmt_saldo);
    $result_saldo = mysqli_stmt_get_result($stmt_saldo);
} else {
    $result_saldo = mysqli_query($conn, $sql_saldo);
}

include "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold m-0">Uang Sangu Santri</h2>
        <p class="text-muted m-0">Pengelolaan dan pencarian saldo serta transaksi uang sangu santri</p>
    </div>
    <a href="tambah.php" class="btn btn-secondary text-white fw-bold">
        <i class="fa-solid fa-plus me-1"></i> Transaksi Sangu Baru
    </a>
</div>

<!-- FORM PENCARIAN -->
<div class="card card-custom p-3 mb-4 bg-white border-0 shadow-sm">
    <form method="GET" action="index.php" class="row g-2 align-items-end">
        <div class="col-md-9 col-lg-10">
            <label class="form-label small fw-bold text-muted mb-1">Cari Data Uang Sangu</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-search"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Cari berdasarkan nama santri atau keterangan..." value="<?= htmlspecialchars($q) ?>">
            </div>
        </div>
        <div class="col-md-3 col-lg-2 d-flex gap-1">
            <button type="submit" class="btn btn-secondary w-100 fw-semibold">
                <i class="fa-solid fa-magnifying-glass me-1"></i> Cari
            </button>
            <?php if ($is_filtered) { ?>
                <a href="index.php" class="btn btn-outline-secondary" title="Reset Pencarian">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php } ?>
        </div>
    </form>
    <?php if ($is_filtered) { ?>
        <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">
                <i class="fa-solid fa-info-circle text-secondary me-1"></i>
                Menyaring data uang sangu untuk kata kunci: <span class="badge bg-light text-dark border">"<?= htmlspecialchars($q) ?>"</span>
            </small>
            <a href="index.php" class="small text-danger text-decoration-none fw-semibold">
                <i class="fa-solid fa-xmark me-1"></i>Hapus Filter
            </a>
        </div>
    <?php } ?>
</div>

<!-- RINGKASAN KAS UANG SANGU -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-custom p-3 border-start border-success border-4">
            <small class="text-muted fw-bold">TOTAL SANGU MASUK</small>
            <h4 class="text-success fw-bold m-0">Rp <?= number_format($total["total_masuk"] ?? 0, 0, ",", ".") ?></h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom p-3 border-start border-danger border-4">
            <small class="text-muted fw-bold">TOTAL SANGU KELUAR</small>
            <h4 class="text-danger fw-bold m-0">Rp <?= number_format($total["total_keluar"] ?? 0, 0, ",", ".") ?></h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom p-3 border-start border-secondary border-4">
            <small class="text-muted fw-bold">SALDO SANGU TOTAL</small>
            <h4 class="text-secondary fw-bold m-0">Rp <?= number_format($saldo, 0, ",", ".") ?></h4>
        </div>
    </div>
</div>

<!-- SALDO PER SANTRI -->
<div class="card card-custom p-4 mb-4">
    <h5 class="fw-bold mb-3"><i class="fa-solid fa-wallet text-secondary me-2"></i>Saldo Uang Sangu Per Santri</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead class="table-light">
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Santri</th>
                    <th>Total Masuk</th>
                    <th>Total Keluar</th>
                    <th>Saldo</th>
                    <th class="text-center" width="12%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no_s = 1; 
                if ($result_saldo && mysqli_num_rows($result_saldo) > 0) {
                    while ($s = mysqli_fetch_assoc($result_saldo)) { 
                        $saldo_s = $s["total_masuk"] - $s["total_keluar"];
                ?>
                    <tr>
                        <td><?= $no_s++ ?></td>
                        <td class="fw-bold text-dark">
                            <i class="fa-solid fa-user-graduate text-secondary me-1"></i>
                            <?= htmlspecialchars($s["nama_santri"]) ?>
                        </td>
                        <td class="text-success">Rp <?= number_format($s["total_masuk"], 0, ",", ".") ?></td>
                        <td class="text-danger">Rp <?= number_format($s["total_keluar"], 0, ",", ".") ?></td>
                        <td class="fw-bold text-secondary">Rp <?= number_format($saldo_s, 0, ",", ".") ?></td>
                        <td class="text-center">
                            <a href="detail.php?id=<?= $s["id_santri"] ?>" class="btn btn-secondary btn-sm text-white">
                                <i class="fa-solid fa-eye me-1"></i> Detail
                            </a>
                        </td>
                    </tr>
                <?php 
                    }
                } 
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- RIWAYAT TRANSAKSI -->
<div class="card card-custom p-4">
    <h5 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>Riwayat Transaksi Uang Sangu</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead class="table-light">
                <tr>
                    <th width="5%">No</th>
                    <th>Santri</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Nominal</th>
                    <th>Keterangan</th>
                    <th>Admin</th>
                    <th class="text-center" width="12%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; 
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($data = mysqli_fetch_assoc($result)) { 
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($data["nama_santri"]) ?></td>
                        <td><?= htmlspecialchars($data["tanggal"]) ?></td>
                        <td>
                            <?php if ($data["jenis"] == "Masuk") { ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Masuk</span>
                            <?php } else { ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Keluar</span>
                            <?php } ?>
                        </td>
                        <td class="fw-bold <?= $data["jenis"] == "Masuk" ? "text-success" : "text-danger" ?>">
                            Rp <?= number_format($data["nominal"], 0, ",", ".") ?>
                        </td>
                        <td><?= htmlspecialchars($data["keterangan"] ?? '-') ?></td>
                        <td><small class="text-muted"><?= htmlspecialchars($data["nama_admin"]) ?></small></td>
                        <td class="text-center">
                            <a href="edit.php?id=<?= $data["id_transaksi_sangu"] ?>" class="btn btn-warning btn-sm me-1" title="Edit Transaksi">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="hapus.php?id=<?= $data["id_transaksi_sangu"] ?>" class="btn btn-danger btn-sm" title="Hapus Transaksi" onclick="return confirm('Hapus transaksi uang sangu ini?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
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