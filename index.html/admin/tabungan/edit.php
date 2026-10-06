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

if (!isset($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET["id"];
$error = "";

/* ==============================
   AMBIL DATA TRANSAKSI
   ============================== */
$sql = "SELECT * FROM transaksi_tabungan WHERE id_transaksi_tabungan = ?";
/** @var mysqli $conn */
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Transaksi tidak ditemukan.");
}

/* ==============================
   PROSES UPDATE
   ============================== */
if (isset($_POST["simpan"])) {

    $id_santri = (int) $_POST["id_santri"];
    $tanggal = $_POST["tanggal"];
    $jenis = $_POST["jenis"];
    $nominal = (float) $_POST["nominal"];
    $keterangan = trim($_POST["keterangan"]);

    if ($nominal <= 0) {
        $error = "Nominal harus lebih dari 0.";
    } else {
        $sql_update = "UPDATE transaksi_tabungan
                       SET id_santri = ?,
                           tanggal = ?,
                           jenis = ?,
                           nominal = ?,
                           keterangan = ?
                       WHERE id_transaksi_tabungan = ?";

        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param(
            $stmt_update,
            "issdsi",
            $id_santri,
            $tanggal,
            $jenis,
            $nominal,
            $keterangan,
            $id
        );

        if (mysqli_stmt_execute($stmt_update)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Transaksi gagal diperbarui.";
        }
    }
}

/* ==============================
   AMBIL DATA SANTRI
   ============================== */
$sql_santri = "SELECT id_santri, nama_santri FROM santri WHERE status = 'Aktif' ORDER BY nama_santri ASC";
$result_santri = mysqli_query($conn, $sql_santri);

include "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Edit Transaksi Tabungan</h4>
        <p class="text-muted small mb-0">Ubah detail rincian transaksi tabungan</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary fw-semibold">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-8 col-lg-6">
        <div class="card card-custom">
            <div class="card-body p-4">
                <?php if ($error != "") { ?>
                    <div class="alert alert-danger mb-4" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php } ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Santri</label>
                        <select name="id_santri" class="form-select" required>
                            <?php while ($s = mysqli_fetch_assoc($result_santri)) { ?>
                                <option value="<?= $s["id_santri"] ?>" <?= $s["id_santri"] == $data["id_santri"] ? "selected" : "" ?>>
                                    <?= htmlspecialchars($s["nama_santri"]) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jenis Transaksi</label>
                        <select name="jenis" class="form-select" required>
                            <option value="Masuk" <?= $data["jenis"] == "Masuk" ? "selected" : "" ?>>Masuk</option>
                            <option value="Keluar" <?= $data["jenis"] == "Keluar" ? "selected" : "" ?>>Keluar</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nominal (Rp)</label>
                        <input type="number" name="nominal" class="form-control" min="1" value="<?= htmlspecialchars($data["nominal"]) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($data["tanggal"]) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3"><?= htmlspecialchars($data["keterangan"]) ?></textarea>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-primary fw-semibold w-100">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>