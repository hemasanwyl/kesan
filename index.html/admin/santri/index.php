<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

/** @var mysqli $conn */

// Ambil parameter filter & pencarian
$q = trim($_GET["q"] ?? "");
$id_jenjang = $_GET["id_jenjang"] ?? "";
$status = $_GET["status"] ?? "";

// Deteksi struktur kolom nama orang tua/wali secara dinamis
$has_nama_ortu = false;
$has_nama_wali = false;
$col_res = @mysqli_query($conn, "SHOW COLUMNS FROM santri");
if ($col_res) {
    while ($c = mysqli_fetch_assoc($col_res)) {
        if ($c["Field"] === "nama_ortu") $has_nama_ortu = true;
        if ($c["Field"] === "nama_wali") $has_nama_wali = true;
    }
}
$wali_col = $has_nama_wali ? "santri.nama_wali" : ($has_nama_ortu ? "santri.nama_ortu" : "''");

// Ambil daftar jenjang untuk dropdown filter
$res_jenjang = @mysqli_query($conn, "SELECT id_jenjang, nama_jenjang FROM jenjang ORDER BY nama_jenjang ASC");

// Susun Query Dinamis dengan Prepared Statement
$conditions = [];
$params = [];
$types = "";

if ($q !== "") {
    $search_term = "%" . $q . "%";
    if ($wali_col !== "''") {
        $conditions[] = "(santri.nama_santri LIKE ? OR jenjang.nama_jenjang LIKE ? OR $wali_col LIKE ?)";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "sss";
    } else {
        $conditions[] = "(santri.nama_santri LIKE ? OR jenjang.nama_jenjang LIKE ?)";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "ss";
    }
}

if ($id_jenjang !== "") {
    $conditions[] = "santri.id_jenjang = ?";
    $params[] = (int) $id_jenjang;
    $types .= "i";
}

if ($status !== "") {
    $conditions[] = "santri.status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql = "SELECT santri.*, jenjang.nama_jenjang 
        FROM santri 
        INNER JOIN jenjang ON santri.id_jenjang = jenjang.id_jenjang";

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY santri.nama_santri ASC";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}

$total_santri = $result ? mysqli_num_rows($result) : 0;
$is_filtered = ($q !== "" || $id_jenjang !== "" || $status !== "");

include "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold m-0">Data Santri</h2>
        <p class="text-muted m-0">Kelola informasi dan pencarian seluruh santri terdaftar</p>
    </div>
    <a href="tambah.php" class="btn btn-primary fw-bold">
        <i class="fa-solid fa-plus me-1"></i> Tambah Santri
    </a>
</div>

<!-- FORM PENCARIAN & FILTER -->
<div class="card card-custom p-3 mb-4 bg-white border-0 shadow-sm">
    <form method="GET" action="index.php" class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label small fw-bold text-muted mb-1">Cari Santri</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-search"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Cari nama santri, orang tua/wali..." value="<?= htmlspecialchars($q) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold text-muted mb-1">Jenjang</label>
            <select name="id_jenjang" class="form-select">
                <option value="">-- Semua Jenjang --</option>
                <?php if ($res_jenjang) { 
                    mysqli_data_seek($res_jenjang, 0);
                    while ($j = mysqli_fetch_assoc($res_jenjang)) { ?>
                        <option value="<?= $j['id_jenjang'] ?>" <?= $id_jenjang == $j['id_jenjang'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($j['nama_jenjang']) ?>
                        </option>
                <?php } } ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted mb-1">Status</label>
            <select name="status" class="form-select">
                <option value="">-- Semua Status --</option>
                <option value="Aktif" <?= $status === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="Non-Aktif" <?= ($status === 'Non-Aktif' || $status === 'Tidak Aktif') ? 'selected' : '' ?>>Non-Aktif</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100 fw-semibold">
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
                <i class="fa-solid fa-info-circle text-primary me-1"></i>
                Menampilkan <strong><?= $total_santri ?></strong> data santri sesuai filter pencarian
                <?php if ($q !== '') { ?> untuk kata kunci: <span class="badge bg-light text-dark border">"<?= htmlspecialchars($q) ?>"</span><?php } ?>
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
                    <th>Jenjang</th>
                    <th>Wali / Ortu</th>
                    <th>Status</th>
                    <th class="text-center" width="18%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_santri > 0) { 
                    $no = 1; 
                    while ($data = mysqli_fetch_assoc($result)) { 
                        $wali_val = $data["nama_wali"] ?? $data["nama_ortu"] ?? "-";
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="fw-bold text-dark">
                            <i class="fa-solid fa-circle-user text-primary me-1"></i>
                            <?= htmlspecialchars($data["nama_santri"]) ?>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                <?= htmlspecialchars($data["nama_jenjang"]) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($wali_val) ?></td>
                        <td>
                            <?php if ($data["status"] == "Aktif") { ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                            <?php } else { ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Non-Aktif</span>
                            <?php } ?>
                        </td>
                        <td class="text-center">
                            <a href="detail.php?id=<?= $data["id_santri"] ?>" class="btn btn-info btn-sm text-white me-1" title="Lihat Profil Santri">
                                <i class="fa-solid fa-eye"></i> Detail
                            </a>
                            <a href="edit.php?id=<?= $data["id_santri"] ?>" class="btn btn-warning btn-sm me-1" title="Edit Data">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="hapus.php?id=<?= $data["id_santri"] ?>" class="btn btn-danger btn-sm" title="Hapus Data" onclick="return confirm('Apakah Anda yakin ingin menghapus santri ini?')">
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