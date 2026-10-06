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
        if ($jenis === "Keluar") {
            $sql_saldo = "SELECT
                            COALESCE(
                                SUM(
                                    CASE
                                        WHEN jenis = 'Masuk' THEN nominal
                                        WHEN jenis = 'Keluar' THEN -nominal
                                        ELSE 0
                                    END
                                ), 0
                            ) AS saldo
                          FROM transaksi_tabungan
                          WHERE id_santri = ?";

            /** @var mysqli $conn */
            $stmt_saldo = mysqli_prepare($conn, $sql_saldo);
            mysqli_stmt_bind_param($stmt_saldo, "i", $id_santri);
            mysqli_stmt_execute($stmt_saldo);
            $res_saldo = mysqli_stmt_get_result($stmt_saldo);
            $data_saldo = mysqli_fetch_assoc($res_saldo);

            $saldo_saat_ini = $data_saldo["saldo"];

            if ($nominal > $saldo_saat_ini) {
                $error = "Saldo Tabungan tidak mencukupi. Saldo saat ini: Rp " . number_format($saldo_saat_ini, 0, ",", ".");
            }
        }

        if ($error === "") {
            $sql = "INSERT INTO transaksi_tabungan
                    (id_santri, tanggal, jenis, nominal, keterangan, id_user, waktu)
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
}

/* ==============================
   AMBIL DATA SANTRI AKTIF
   ============================== */
$sql_santri = "SELECT id_santri, nama_santri FROM santri WHERE status = 'Aktif' ORDER BY nama_santri ASC";
/** @var mysqli $conn */
$result_santri = mysqli_query($conn, $sql_santri);

include "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Tambah Transaksi Tabungan</h4>
        <p class="text-muted small mb-0">Catat transaksi setoran atau penarikan tabungan santri</p>
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
                        <input type="number" name="nominal" class="form-control" placeholder="Contoh: 50000" min="1" step="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Setoran awal tabungan"></textarea>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-primary fw-semibold w-100">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Transaksi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>