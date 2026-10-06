<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

/** @var mysqli $conn */

// Filter & Parameter Pencarian
$q = trim($_GET["q"] ?? "");
$bulan = $_GET["bulan"] ?? "";
$tahun = $_GET["tahun"] ?? "";
$status = $_GET["status"] ?? "";

$conditions = [];
$params = [];
$types = "";

if ($q !== "") {
    $conditions[] = "(santri.nama_santri LIKE ? OR jenjang.nama_jenjang LIKE ?)";
    $term = "%" . $q . "%";
    $params[] = $term;
    $params[] = $term;
    $types .= "ss";
}

if ($bulan !== "") {
    $conditions[] = "pembayaran_spp.bulan = ?";
    $params[] = $bulan;
    $types .= "s";
}

if ($tahun !== "") {
    $conditions[] = "pembayaran_spp.tahun = ?";
    $params[] = (int) $tahun;
    $types .= "i";
}

if ($status !== "") {
    $conditions[] = "pembayaran_spp.status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql = "SELECT 
            pembayaran_spp.*,
            santri.nama_santri,
            jenjang.nama_jenjang,
            users.nama AS nama_admin
        FROM pembayaran_spp
        INNER JOIN santri ON pembayaran_spp.id_santri = santri.id_santri
        INNER JOIN jenjang ON santri.id_jenjang = jenjang.id_jenjang
        INNER JOIN users ON pembayaran_spp.id_user = users.id_user";

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY pembayaran_spp.tahun DESC, pembayaran_spp.id_pembayaran DESC";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}

$total_rows = $result ? mysqli_num_rows($result) : 0;
$is_filtered = ($q !== "" || $bulan !== "" || $tahun !== "" || $status !== "");

$bulan_list = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
];

include "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold m-0">Pembayaran SPP</h2>
        <p class="text-muted m-0">Riwayat dan pencatatan iuran SPP santri</p>
    </div>
    <a href="tambah.php" class="btn btn-success fw-bold">
        <i class="fa-solid fa-plus me-1"></i> Catat Pembayaran SPP
    </a>
</div>

<!-- FORM FILTER & PENCARIAN -->
<div class="card card-custom p-3 mb-4 bg-white border-0 shadow-sm">
    <form method="GET" action="index.php" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-bold text-muted mb-1">Cari Santri / Jenjang</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-search"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Nama santri..." value="<?= htmlspecialchars($q) ?>">
            </div>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted mb-1">Bulan</label>
            <select name="bulan" class="form-select">
                <option value="">-- Semua --</option>
                <?php foreach ($bulan_list as $b) { ?>
                    <option value="<?= $b ?>" <?= $bulan === $b ? 'selected' : '' ?>><?= $b ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted mb-1">Tahun</label>
            <input type="number" name="tahun" class="form-control" placeholder="Contoh: <?= date('Y') ?>" value="<?= htmlspecialchars($tahun) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted mb-1">Status</label>
            <select name="status" class="form-select">
                <option value="">-- Semua --</option>
                <option value="Lunas" <?= $status === 'Lunas' ? 'selected' : '' ?>>Lunas</option>
                <option value="Belum Lunas" <?= $status === 'Belum Lunas' ? 'selected' : '' ?>>Belum Lunas</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-success w-100 fw-semibold">
                <i class="fa-solid fa-filter me-1"></i> Filter
            </button>
            <?php if ($is_filtered) { ?>
                <a href="index.php" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php } ?>
        </div>
    </form>
    <?php if ($is_filtered) { ?>
        <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">
                <i class="fa-solid fa-info-circle text-success me-1"></i>
                Ditemukan <strong><?= $total_rows ?></strong> transaksi pembayaran sesuai filter pencarian
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
                    <th>Santri</th>
                    <th>Jenjang</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>Nominal</th>
                    <th>Tanggal Bayar</th>
                    <th>Status</th>
                    <th>Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($total_rows > 0) {
                    $no = 1;
                    while ($data = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="fw-bold text-dark">
                            <i class="fa-solid fa-user-graduate text-success me-1"></i>
                            <?= htmlspecialchars($data["nama_santri"]) ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis border">
                                <?= htmlspecialchars($data["nama_jenjang"]) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($data["bulan"]) ?></td>
                        <td><?= htmlspecialchars($data["tahun"]) ?></td>
                        <td class="fw-bold text-success">
                            Rp <?= number_format($data["nominal"], 0, ",", ".") ?>
                        </td>
                        <td><?= htmlspecialchars($data["tanggal_bayar"]) ?></td>
                        <td>
                            <?php if ($data["status"] == "Lunas") { ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Lunas</span>
                            <?php } else { ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Belum Lunas</span>
                            <?php } ?>
                        </td>
                        <td>
                            <small class="text-muted"><?= htmlspecialchars($data["nama_admin"]) ?></small>
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