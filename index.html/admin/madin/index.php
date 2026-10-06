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

// Query Santri Aktif & Rekap Pembayaran Madin
$sql = "SELECT 
            santri.id_santri,
            santri.nama_santri,
            COUNT(pembayaran_madin.id_pembayaran_madin) AS total_bulan_bayar,
            COALESCE(SUM(pembayaran_madin.nominal), 0) AS total_nominal
        FROM santri
        LEFT JOIN pembayaran_madin ON santri.id_santri = pembayaran_madin.id_santri
        WHERE santri.status = 'Aktif'";

if ($q !== "") {
    $sql .= " AND santri.nama_santri LIKE ?";
}

$sql .= " GROUP BY santri.id_santri, santri.nama_santri ORDER BY santri.nama_santri ASC";

if ($q !== "") {
    $stmt = mysqli_prepare($conn, $sql);
    $search_term = "%" . $q . "%";
    mysqli_stmt_bind_param($stmt, "s", $search_term);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}

$total_santri = $result ? mysqli_num_rows($result) : 0;
$is_filtered = ($q !== "");

include "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold m-0">Pembayaran Madin</h2>
        <p class="text-muted m-0">Rekapitulasi dan pencarian iuran Madrasah Diniyah per santri</p>
    </div>
    <a href="tambah.php" class="btn btn-info text-white fw-bold">
        <i class="fa-solid fa-plus me-1"></i> Catat Pembayaran Madin
    </a>
</div>

<!-- FORM PENCARIAN -->
<div class="card card-custom p-3 mb-4 bg-white border-0 shadow-sm">
    <form method="GET" action="index.php" class="row g-2 align-items-end">
        <div class="col-md-9 col-lg-10">
            <label class="form-label small fw-bold text-muted mb-1">Cari Santri</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-search"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Ketik nama santri untuk mencari..." value="<?= htmlspecialchars($q) ?>">
            </div>
        </div>
        <div class="col-md-3 col-lg-2 d-flex gap-1">
            <button type="submit" class="btn btn-info text-white w-100 fw-semibold">
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
                <i class="fa-solid fa-info-circle text-info me-1"></i>
                Ditemukan <strong><?= $total_santri ?></strong> santri untuk kata kunci: <span class="badge bg-light text-dark border">"<?= htmlspecialchars($q) ?>"</span>
            </small>
            <a href="index.php" class="small text-danger text-decoration-none fw-semibold">
                <i class="fa-solid fa-xmark me-1"></i>Hapus Filter
            </a>
        </div>
    <?php } ?>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead class="table-light">
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Santri</th>
                    <th>Bulan Dibayar</th>
                    <th>Total Nominal</th>
                    <th class="text-center" width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($total_santri > 0) {
                    $no = 1;
                    while ($data = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="fw-bold text-dark">
                            <i class="fa-solid fa-user-graduate text-info me-1"></i>
                            <?= htmlspecialchars($data["nama_santri"]) ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis border">
                                <?= $data["total_bulan_bayar"] ?> Bulan
                            </span>
                        </td>
                        <td class="fw-bold text-success">
                            Rp <?= number_format($data["total_nominal"], 0, ",", ".") ?>
                        </td>
                        <td class="text-center">
                            <a href="detail.php?id=<?= $data["id_santri"] ?>" class="btn btn-primary btn-sm fw-semibold">
                                <i class="fa-solid fa-eye me-1"></i> Detail
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