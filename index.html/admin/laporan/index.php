<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');
$jenis_laporan = $_GET['jenis_laporan'] ?? 'spp';
$q = trim($_GET['q'] ?? '');

/** @var mysqli $conn */

// Query Data Berdasarkan Jenis Laporan & Pencarian Santri
$conditions = [];
$params = [];
$types = "";

if ($jenis_laporan == 'spp') {
    $sql = "SELECT pembayaran_spp.*, santri.nama_santri, users.nama AS nama_admin 
            FROM pembayaran_spp 
            INNER JOIN santri ON pembayaran_spp.id_santri = santri.id_santri 
            INNER JOIN users ON pembayaran_spp.id_user = users.id_user 
            WHERE pembayaran_spp.tanggal_bayar BETWEEN ? AND ?";
    $params[] = $tgl_mulai;
    $params[] = $tgl_selesai;
    $types .= "ss";

    if ($q !== '') {
        $sql .= " AND santri.nama_santri LIKE ?";
        $params[] = "%" . $q . "%";
        $types .= "s";
    }
    $sql .= " ORDER BY pembayaran_spp.tanggal_bayar ASC";

} elseif ($jenis_laporan == 'madin') {
    $sql = "SELECT pembayaran_madin.*, santri.nama_santri, users.nama AS nama_admin 
            FROM pembayaran_madin 
            INNER JOIN santri ON pembayaran_madin.id_santri = santri.id_santri 
            INNER JOIN users ON pembayaran_madin.id_user = users.id_user 
            WHERE pembayaran_madin.tanggal_bayar BETWEEN ? AND ?";
    $params[] = $tgl_mulai;
    $params[] = $tgl_selesai;
    $types .= "ss";

    if ($q !== '') {
        $sql .= " AND santri.nama_santri LIKE ?";
        $params[] = "%" . $q . "%";
        $types .= "s";
    }
    $sql .= " ORDER BY pembayaran_madin.tanggal_bayar ASC";

} elseif ($jenis_laporan == 'sangu') {
    $sql = "SELECT transaksi_sangu.*, santri.nama_santri, users.nama AS nama_admin 
            FROM transaksi_sangu 
            INNER JOIN santri ON transaksi_sangu.id_santri = santri.id_santri 
            INNER JOIN users ON transaksi_sangu.id_user = users.id_user 
            WHERE transaksi_sangu.tanggal BETWEEN ? AND ?";
    $params[] = $tgl_mulai;
    $params[] = $tgl_selesai;
    $types .= "ss";

    if ($q !== '') {
        $sql .= " AND santri.nama_santri LIKE ?";
        $params[] = "%" . $q . "%";
        $types .= "s";
    }
    $sql .= " ORDER BY transaksi_sangu.tanggal ASC";

} else {
    $sql = "SELECT transaksi_tabungan.*, santri.nama_santri, users.nama AS nama_admin 
            FROM transaksi_tabungan 
            INNER JOIN santri ON transaksi_tabungan.id_santri = santri.id_santri 
            INNER JOIN users ON transaksi_tabungan.id_user = users.id_user 
            WHERE transaksi_tabungan.tanggal BETWEEN ? AND ?";
    $params[] = $tgl_mulai;
    $params[] = $tgl_selesai;
    $types .= "ss";

    if ($q !== '') {
        $sql .= " AND santri.nama_santri LIKE ?";
        $params[] = "%" . $q . "%";
        $types .= "s";
    }
    $sql .= " ORDER BY transaksi_tabungan.tanggal ASC";
}

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

include "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold m-0">Laporan Keuangan</h2>
        <p class="text-muted m-0">Filter, pencarian santri, dan cetak laporan transaksi keuangan</p>
    </div>
    <a href="cetak.php?jenis_laporan=<?= $jenis_laporan ?>&tgl_mulai=<?= $tgl_mulai ?>&tgl_selesai=<?= $tgl_selesai ?>&q=<?= urlencode($q) ?>" target="_blank" class="btn btn-dark fw-bold">
        <i class="fa-solid fa-print me-1"></i> Cetak Laporan
    </a>
</div>

<!-- FILTER & PENCARIAN CARD -->
<div class="card card-custom p-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-bold small">Jenis Laporan</label>
            <select name="jenis_laporan" class="form-select">
                <option value="spp" <?= $jenis_laporan == 'spp' ? 'selected' : '' ?>>Pembayaran SPP</option>
                <option value="madin" <?= $jenis_laporan == 'madin' ? 'selected' : '' ?>>Pembayaran Madin</option>
                <option value="sangu" <?= $jenis_laporan == 'sangu' ? 'selected' : '' ?>>Transaksi Sangu</option>
                <option value="tabungan" <?= $jenis_laporan == 'tabungan' ? 'selected' : '' ?>>Transaksi Tabungan</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-bold small">Cari Nama Santri</label>
            <input type="text" name="q" class="form-control" placeholder="Semua santri..." value="<?= htmlspecialchars($q) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Dari Tanggal</label>
            <input type="date" name="tgl_mulai" class="form-control" value="<?= $tgl_mulai ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Sampai Tanggal</label>
            <input type="date" name="tgl_selesai" class="form-control" value="<?= $tgl_selesai ?>">
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100 fw-bold">
                <i class="fa-solid fa-filter me-1"></i> Filter
            </button>
            <?php if ($q !== '') { ?>
                <a href="index.php?jenis_laporan=<?= $jenis_laporan ?>&tgl_mulai=<?= $tgl_mulai ?>&tgl_selesai=<?= $tgl_selesai ?>" class="btn btn-outline-secondary" title="Reset Cari Santri">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php } ?>
        </div>
    </form>
</div>

<!-- TABEL HASIL LAPORAN -->
<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle datatable">
            <thead class="table-light">
                <tr>
                    <th width="5%">No</th>
                    <th>Santri</th>
                    <th>Tanggal</th>
                    <?php if ($jenis_laporan == 'spp' || $jenis_laporan == 'madin') { ?>
                        <th>Bulan / Tahun</th>
                    <?php } else { ?>
                        <th>Jenis</th>
                    <?php } ?>
                    <th>Nominal</th>
                    <th>Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                $total_nominal = 0;
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) { 
                        $nominal = $row['nominal'];
                        $total_nominal += $nominal;
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="fw-bold text-dark">
                            <i class="fa-solid fa-user me-1 text-primary"></i>
                            <?= htmlspecialchars($row['nama_santri']) ?>
                        </td>
                        <td><?= htmlspecialchars($row['tanggal_bayar'] ?? $row['tanggal']) ?></td>
                        <?php if ($jenis_laporan == 'spp' || $jenis_laporan == 'madin') { ?>
                            <td><?= htmlspecialchars($row['bulan'] ?? '') ?> <?= htmlspecialchars($row['tahun'] ?? '') ?></td>
                        <?php } else { ?>
                            <td>
                                <span class="badge bg-<?= $row['jenis'] == 'Masuk' ? 'success' : 'danger' ?>-subtle text-<?= $row['jenis'] == 'Masuk' ? 'success' : 'danger' ?> border">
                                    <?= htmlspecialchars($row['jenis']) ?>
                                </span>
                            </td>
                        <?php } ?>
                        <td class="fw-bold text-success">Rp <?= number_format($nominal, 0, ',', '.') ?></td>
                        <td><small class="text-muted"><?= htmlspecialchars($row['nama_admin']) ?></small></td>
                <?php 
                    }
                } 
                ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th class="text-end">TOTAL:</th>
                    <th class="text-success fw-bold">Rp <?= number_format($total_nominal, 0, ',', '.') ?></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>