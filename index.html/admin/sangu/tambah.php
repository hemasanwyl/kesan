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

$error = "";


/* ==============================
   PROSES SIMPAN TRANSAKSI
   ============================== */

if (isset($_POST["simpan"])) {

    $id_santri = (int) $_POST["id_santri"];
    $tanggal = $_POST["tanggal"];
    $jenis = $_POST["jenis"];
    $nominal = (float) $_POST["nominal"];
    $keterangan = trim($_POST["keterangan"]);
    $id_user = (int) $_SESSION["id_user"];


    if ($nominal <= 0) {

        $error = "Nominal harus lebih dari 0.";

    } else {

        $sql = "INSERT INTO transaksi_sangu
                (
                    id_santri,
                    tanggal,
                    jenis,
                    nominal,
                    keterangan,
                    id_user,
                    waktu
                )
                VALUES (?, ?, ?, ?, ?, ?, NOW())";

        /** @var mysqli $conn */
        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "issdsi",
            $id_santri,
            $tanggal,
            $jenis,
            $nominal,
            $keterangan,
            $id_user
        );

        if (mysqli_stmt_execute($stmt)) {

            header("Location: index.php");
            exit;

        } else {

            $error = "Transaksi gagal disimpan.";

        }
    }
}


/* ==============================
   AMBIL DATA SANTRI AKTIF
   ============================== */

$sql_santri = "SELECT
                    id_santri,
                    nama_santri

               FROM santri

               WHERE status = 'Aktif'

               ORDER BY nama_santri ASC";

/** @var mysqli $conn */
$result_santri = mysqli_query(
    $conn,
    $sql_santri
);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Transaksi Uang Sangu - Kesan</title>
    <!-- Bootstrap 5 CSS & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">

    <div class="container py-4">
        <!-- HEADER HALAMAN -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Tambah Transaksi Uang Sangu</h4>
                <p class="text-muted small mb-0">Catat pemasukan atau penarikan uang sangu santri</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary fw-semibold">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Uang Sangu
            </a>
        </div>

        <!-- FORM CARD -->
        <div class="row">
            <div class="col-md-8 col-lg-6 mx-auto">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">

                        <?php if ($error != "") { ?>
                            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                                <i class="fa-solid fa-circle-exclamation me-2"></i>
                                <div><?= htmlspecialchars($error) ?></div>
                            </div>
                        <?php } ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Santri</label>
                                <select name="id_santri" class="form-select" required>
                                    <option value="">-- Pilih Santri --</option>
                                    <?php while ($s = mysqli_fetch_assoc($result_santri)) { ?>
                                        <option value="<?= $s["id_santri"] ?>">
                                            <?= htmlspecialchars($s["nama_santri"]) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jenis Transaksi</label>
                                <select name="jenis" class="form-select" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="Masuk">Masuk (Setoran)</option>
                                    <option value="Keluar">Keluar (Penarikan)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nominal (Rp)</label>
                                <input type="number" name="nominal" class="form-control" placeholder="Masukkan nominal" min="1" step="1" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Setoran tabungan"></textarea>
                            </div>

                            <button type="submit" name="simpan" class="btn btn-primary fw-semibold w-100 py-2">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Transaksi
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>