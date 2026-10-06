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

$conditions = [];
$params = [$tgl_mulai, $tgl_selesai];
$types = "ss";

if ($jenis_laporan == 'spp') {
    $judul = "Laporan Pembayaran SPP";
    $sql = "SELECT pembayaran_spp.*, santri.nama_santri, users.nama AS nama_admin 
            FROM pembayaran_spp 
            INNER JOIN santri ON pembayaran_spp.id_santri = santri.id_santri 
            INNER JOIN users ON pembayaran_spp.id_user = users.id_user 
            WHERE pembayaran_spp.tanggal_bayar BETWEEN ? AND ?";
    if ($q !== '') {
        $sql .= " AND santri.nama_santri LIKE ?";
        $params[] = "%" . $q . "%";
        $types .= "s";
    }
    $sql .= " ORDER BY pembayaran_spp.tanggal_bayar ASC";

} elseif ($jenis_laporan == 'madin') {
    $judul = "Laporan Pembayaran Madin";
    $sql = "SELECT pembayaran_madin.*, santri.nama_santri, users.nama AS nama_admin 
            FROM pembayaran_madin 
            INNER JOIN santri ON pembayaran_madin.id_santri = santri.id_santri 
            INNER JOIN users ON pembayaran_madin.id_user = users.id_user 
            WHERE pembayaran_madin.tanggal_bayar BETWEEN ? AND ?";
    if ($q !== '') {
        $sql .= " AND santri.nama_santri LIKE ?";
        $params[] = "%" . $q . "%";
        $types .= "s";
    }
    $sql .= " ORDER BY pembayaran_madin.tanggal_bayar ASC";

} elseif ($jenis_laporan == 'sangu') {
    $judul = "Laporan Transaksi Uang Sangu";
    $sql = "SELECT transaksi_sangu.*, santri.nama_santri, users.nama AS nama_admin 
            FROM transaksi_sangu 
            INNER JOIN santri ON transaksi_sangu.id_santri = santri.id_santri 
            INNER JOIN users ON transaksi_sangu.id_user = users.id_user 
            WHERE transaksi_sangu.tanggal BETWEEN ? AND ?";
    if ($q !== '') {
        $sql .= " AND santri.nama_santri LIKE ?";
        $params[] = "%" . $q . "%";
        $types .= "s";
    }
    $sql .= " ORDER BY transaksi_sangu.tanggal ASC";

} else {
    $judul = "Laporan Transaksi Tabungan";
    $sql = "SELECT transaksi_tabungan.*, santri.nama_santri, users.nama AS nama_admin 
            FROM transaksi_tabungan 
            INNER JOIN santri ON transaksi_tabungan.id_santri = santri.id_santri 
            INNER JOIN users ON transaksi_tabungan.id_user = users.id_user 
            WHERE transaksi_tabungan.tanggal BETWEEN ? AND ?";
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $judul ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 12px; background: #fff; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="container my-4">
    <div class="text-center mb-4">
        <h3 class="fw-bold m-0">PONDOK PESANTREN</h3>
        <h5 class="m-0"><?= strtoupper($judul) ?></h5>
        <p class="text-muted m-0">Periode: <?= date('d/m/Y', strtotime($tgl_mulai)) ?> s/d <?= date('d/m/Y', strtotime($tgl_selesai)) ?></p>
        <?php if ($q !== '') { ?>
            <p class="text-muted m-0 small">Filter Santri: <strong>"<?= htmlspecialchars($q) ?>"</strong></p>
        <?php } ?>
    </div>

    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th width="5%">No</th>
                <th>Nama Santri</th>
                <th>Tanggal</th>
                <?php if ($jenis_laporan == 'spp' || $jenis_laporan == 'madin') { ?>
                    <th>Periode</th>
                <?php } else { ?>
                    <th>Jenis</th>
                <?php } ?>
                <th>Nominal</th>
                <th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $total_nominal = 0;
            while ($row = mysqli_fetch_assoc($result)) { 
                $nominal = $row['nominal'];
                $total_nominal += $nominal;
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama_santri']) ?></td>
                    <td><?= htmlspecialchars($row['tanggal_bayar'] ?? $row['tanggal']) ?></td>
                    <?php if ($jenis_laporan == 'spp' || $jenis_laporan == 'madin') { ?>
                        <td><?= htmlspecialchars($row['bulan']) ?> <?= htmlspecialchars($row['tahun']) ?></td>
                    <?php } else { ?>
                        <td><?= htmlspecialchars($row['jenis']) ?></td>
                    <?php } ?>
                    <td>Rp <?= number_format($nominal, 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($row['nama_admin']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="4" class="text-end">TOTAL</td>
                <td colspan="2">Rp <?= number_format($total_nominal, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="row mt-5">
        <div class="col-8"></div>
        <div class="col-4 text-center">
            <p class="mb-5">Pengelola Keuangan,</p>
            <p class="fw-bold mb-0"><u>( .................................... )</u></p>
        </div>
    </div>
</div>

</body>
</html>