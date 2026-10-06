<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

$error = "";
$id = $_GET["id"] ?? 0;

if (isset($_POST["update"])) {
    $id_santri = $_POST["id_santri"];
    $tanggal = $_POST["tanggal"];
    $jenis = $_POST["jenis"];
    $nominal = $_POST["nominal"];
    $keterangan = trim($_POST["keterangan"]);

    if (empty($id_santri) || empty($tanggal) || empty($jenis) || empty($nominal)) {
        $error = "Semua bidang wajib diisi.";
    } else {
        $sql = "UPDATE transaksi_sangu SET id_santri = ?, tanggal = ?, jenis = ?, nominal = ?, keterangan = ? WHERE id_transaksi_sangu = ?";
        /** @var mysqli $conn */
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issisi", $id_santri, $tanggal, $jenis, $nominal, $keterangan, $id);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal memperbarui transaksi.";
        }
    }
}

/** @var mysqli $conn */
$sql_data = "SELECT * FROM transaksi_sangu WHERE id_transaksi_sangu = ?";
$stmt_data = mysqli_prepare($conn, $sql_data);
mysqli_stmt_bind_param($stmt_data, "i", $id);
mysqli_stmt_execute($stmt_data);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_data));

if (!$data) {
    header("Location: index.php");
    exit;
}

$santri = mysqli_query($conn, "SELECT id_santri, nama_santri FROM santri WHERE status = 'Aktif' ORDER BY nama_santri ASC");

include "../includes/header.php";
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold m-0">Edit Transaksi Sangu</h4>
                <a href="index.php" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
            </div>
            <hr>

            <?php if ($error != "") { ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
            <?php } ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Santri</label>
                    <select name="id_santri" class="form-select" required>
                        <?php while ($s = mysqli_fetch_assoc($santri)) { ?>
                            <option value="<?= $s["id_santri"] ?>" <?= $s["id_santri"] == $data["id_santri"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($s["nama_santri"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($data["tanggal"]) ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Jenis Transaksi</label>
                        <select name="jenis" class="form-select" required>
                            <option value="Masuk" <?= $data["jenis"] == "Masuk" ? "selected" : "" ?>>Masuk (Setor)</option>
                            <option value="Keluar" <?= $data["jenis"] == "Keluar" ? "selected" : "" ?>>Keluar (Tarik)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nominal (Rp)</label>
                    <input type="number" name="nominal" class="form-control" value="<?= htmlspecialchars($data["nominal"]) ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" value="<?= htmlspecialchars($data["keterangan"]) ?>">
                </div>

                <button type="submit" name="update" class="btn btn-primary w-100 fw-bold">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>