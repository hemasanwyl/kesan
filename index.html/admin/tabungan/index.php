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

/** @var mysqli $conn */

// Parameter Pencarian
$q = trim($_GET["q"] ?? "");
$is_filtered = ($q !== "");

/* ==============================
   RIWAYAT TRANSAKSI TABUNGAN
   ============================== */
$sql = "SELECT
            transaksi_tabungan.*,
            santri.nama_santri,
            users.nama AS nama_admin
        FROM transaksi_tabungan
        INNER JOIN santri ON transaksi_tabungan.id_santri = santri.id_santri
        INNER JOIN users ON transaksi_tabungan.id_user = users.id_user";

if ($is_filtered) {
    $sql .= " WHERE (santri.nama_santri LIKE ? OR transaksi_tabungan.keterangan LIKE ?)";
}

$sql .= " ORDER BY transaksi_tabungan.tanggal DESC, transaksi_tabungan.id_transaksi_tabungan DESC";

if ($is_filtered) {
    $stmt = mysqli_prepare($conn, $sql);
    $term = "%" . $q . "%";
    mysqli_stmt_bind_param($stmt, "ss", $term, $term);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}


/* ==============================
   TOTAL KESELURUHAN
   ============================== */
$sql_total = "SELECT
                COALESCE(SUM(CASE WHEN jenis = 'Masuk' THEN nominal ELSE 0 END), 0) AS total_masuk,
                COALESCE(SUM(CASE WHEN jenis = 'Keluar' THEN nominal ELSE 0 END), 0) AS total_keluar
              FROM transaksi_tabungan";

$result_total = mysqli_query($conn, $sql_total);
$total = mysqli_fetch_assoc($result_total);

$total_masuk = $total["total_masuk"];
$total_keluar = $total["total_keluar"];
$saldo = $total_masuk - $total_keluar;


/* ==============================
   SALDO PER SANTRI
   ============================== */
$sql_saldo = "SELECT
                santri.id_santri,
                santri.nama_santri,
                COALESCE(SUM(CASE WHEN transaksi_tabungan.jenis = 'Masuk' THEN transaksi_tabungan.nominal ELSE 0 END), 0) AS total_masuk,
                COALESCE(SUM(CASE WHEN transaksi_tabungan.jenis = 'Keluar' THEN transaksi_tabungan.nominal ELSE 0 END), 0) AS total_keluar
              FROM santri
              LEFT JOIN transaksi_tabungan ON santri.id_santri = transaksi_tabungan.id_santri
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

<!-- HEADER HALAMAN -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Tabungan Santri</h4>
        <p class="text-muted small mb-0">Pengelolaan saldo dan riwayat transaksi tabungan santri</p>
    </div>
    <a href="tambah.php" class="btn btn-primary fw-semibold">
        <i class="fa-solid fa-plus me-1"></i> Tambah Transaksi
    </a>
</div>

<!-- FORM PENCARIAN -->
<div class="card card-custom p-3 mb-4 bg-white border-0 shadow-sm">
    <form method="GET" action="index.php" class="row g-2 align-items-end">
        <div class="col-md-9 col-lg-10">
            <label class="form-label small fw-bold text-muted mb-1">Cari Data Tabungan</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-search"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Cari berdasarkan nama santri atau keterangan..." value="<?= htmlspecialchars($q) ?>">
            </div>
        </div>
        <div class="col-md-3 col-lg-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100 fw-semibold">
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
                <i class="fa-solid fa-info-circle text-primary me-1"></i>
                Menyaring data tabungan untuk kata kunci: <span class="badge bg-light text-dark border">"<?= htmlspecialchars($q) ?>"</span>
            </small>
            <a href="index.php" class="small text-danger text-decoration-none fw-semibold">
                <i class="fa-solid fa-xmark me-1"></i>Hapus Filter
            </a>
        </div>
    <?php } ?>
</div>

<!-- RINGKASAN CARDS -->
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
                <div class="text-muted small fw-bold text-uppercase mb-1">Saldo Keseluruhan</div>
                <h3 class="fw-bold text-primary mb-0">Rp <?= number_format($saldo, 0, ",", ".") ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- SALDO PER SANTRI -->
<div class="card card-custom mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-users me-2 text-primary"></i>Saldo Tabungan Per Santri</h5>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" width="5%">#</th>
                        <th>Nama Santri</th>
                        <th>Total Masuk</th>
                        <th>Total Keluar</th>
                        <th>Saldo Akhir</th>
                        <th class="text-center" width="12%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no_santri = 1;
                    if ($result_saldo && mysqli_num_rows($result_saldo) > 0) {
                        while ($s = mysqli_fetch_assoc($result_saldo)) {
                            $masuk_santri = $s["total_masuk"];
                            $keluar_santri = $s["total_keluar"];
                            $saldo_santri = $masuk_santri - $keluar_santri;
                    ?>
                        <tr>
                            <td class="ps-3"><?= $no_santri++ ?></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($s["nama_santri"]) ?></td>
                            <td class="text-success">Rp <?= number_format($masuk_santri, 0, ",", ".") ?></td>
                            <td class="text-danger">Rp <?= number_format($keluar_santri, 0, ",", ".") ?></td>
                            <td class="fw-bold text-primary">Rp <?= number_format($saldo_santri, 0, ",", ".") ?></td>
                            <td class="text-center">
                                <a href="detail.php?id=<?= $s['id_santri'] ?>" class="btn btn-sm btn-outline-primary fw-semibold" title="Lihat Detail Tabungan">
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
</div>

<!-- RIWAYAT TRANSAKSI -->
<div class="card card-custom">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Riwayat Transaksi Tabungan</h5>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" width="5%">#</th>
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
                            $is_masuk = ($data["jenis"] == "Masuk");
                    ?>
                        <tr>
                            <td class="ps-3"><?= $no++ ?></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($data["nama_santri"]) ?></td>
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
                            <td class="text-center">
                                <a href="edit.php?id=<?= $data['id_transaksi_tabungan'] ?>" class="btn btn-sm btn-outline-warning me-1" title="Edit Transaksi">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <a href="hapus.php?id=<?= $data['id_transaksi_tabungan'] ?>" class="btn btn-sm btn-outline-danger" title="Hapus Transaksi" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');">
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
</div>

<?php include "../includes/footer.php"; ?>