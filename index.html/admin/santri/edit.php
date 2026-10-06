<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

$error = "";
$id_santri = $_GET["id"] ?? 0;

if (isset($_POST["update"])) {
    $nama_santri = trim($_POST["nama_santri"]);
    $id_jenjang = $_POST["id_jenjang"];
    $nama_wali = trim($_POST["nama_wali"]);
    $status = $_POST["status"];

    if (empty($nama_santri) || empty($id_jenjang) || empty($nama_wali) || empty($status)) {
        $error = "Semua bidang wajib diisi.";
    } else {
        // Deteksi kolom secara dinamis
        $col_res = @mysqli_query($conn, "SHOW COLUMNS FROM santri");
        $has_ortu = false;
        $has_wali = false;
        if ($col_res) {
            while ($c = mysqli_fetch_assoc($col_res)) {
                if ($c["Field"] === "nama_ortu") $has_ortu = true;
                if ($c["Field"] === "nama_wali") $has_wali = true;
            }
        }

        if ($has_wali && $has_ortu) {
            $sql = "UPDATE santri SET nama_santri = ?, id_jenjang = ?, nama_wali = ?, nama_ortu = ?, status = ? WHERE id_santri = ?";
            /** @var mysqli $conn */
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sisssi", $nama_santri, $id_jenjang, $nama_wali, $nama_wali, $status, $id_santri);
        } elseif ($has_ortu && !$has_wali) {
            $sql = "UPDATE santri SET nama_santri = ?, id_jenjang = ?, nama_ortu = ?, status = ? WHERE id_santri = ?";
            /** @var mysqli $conn */
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sissi", $nama_santri, $id_jenjang, $nama_wali, $status, $id_santri);
        } else {
            $sql = "UPDATE santri SET nama_santri = ?, id_jenjang = ?, nama_wali = ?, status = ? WHERE id_santri = ?";
            /** @var mysqli $conn */
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sissi", $nama_santri, $id_jenjang, $nama_wali, $status, $id_santri);
        }

        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal memperbarui data santri.";
        }
    }
}

/** @var mysqli $conn */
$sql_santri = "SELECT * FROM santri WHERE id_santri = ?";
$stmt_santri = mysqli_prepare($conn, $sql_santri);
mysqli_stmt_bind_param($stmt_santri, "i", $id_santri);
mysqli_stmt_execute($stmt_santri);
$data_santri = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_santri));

if (!$data_santri) {
    header("Location: index.php");
    exit;
}

$jenjang = mysqli_query($conn, "SELECT * FROM jenjang ORDER BY id_jenjang ASC");

include "../includes/header.php";
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold m-0">Edit Data Santri</h4>
                <a href="index.php" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
            </div>
            <hr>

            <?php if ($error != "") { ?>
                <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
            <?php } ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Santri</label>
                    <input type="text" name="nama_santri" class="form-control" value="<?= htmlspecialchars($data_santri["nama_santri"]) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Jenjang Pendidikan</label>
                    <select name="id_jenjang" class="form-select" required>
                        <?php while ($j = mysqli_fetch_assoc($jenjang)) { ?>
                            <option value="<?= $j["id_jenjang"] ?>" <?= $j["id_jenjang"] == $data_santri["id_jenjang"] ? "selected" : "" ?>>
                                <?= htmlspecialchars($j["nama_jenjang"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Wali / Orang Tua</label>
                    <input type="text" name="nama_wali" class="form-control" value="<?= htmlspecialchars($data_santri["nama_wali"] ?? $data_santri["nama_ortu"] ?? '') ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Status Santri</label>
                    <select name="status" class="form-select" required>
                        <option value="Aktif" <?= $data_santri["status"] == "Aktif" ? "selected" : "" ?>>Aktif</option>
                        <option value="Non-Aktif" <?= $data_santri["status"] == "Non-Aktif" ? "selected" : "" ?>>Non-Aktif</option>
                    </select>
                </div>

                <button type="submit" name="update" class="btn btn-primary w-100 fw-bold">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>