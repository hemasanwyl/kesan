<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

$error = "";

if (isset($_POST["simpan"])) {
    $id_santri = $_POST["id_santri"];
    $bulan = $_POST["bulan"];
    $tahun = $_POST["tahun"];
    $nominal = $_POST["nominal"];
    $tanggal_bayar = $_POST["tanggal_bayar"];
    $status = $_POST["status"];
    $id_user = $_SESSION["id_user"];

    if (empty($id_santri) || empty($bulan) || empty($tahun) || empty($nominal) || empty($tanggal_bayar)) {
        $error = "Semua bidang wajib diisi.";
    } else {
        /* Cek duplikasi pembayaran */
        $sql_cek = "SELECT id_pembayaran_madin FROM pembayaran_madin WHERE id_santri = ? AND bulan = ? AND tahun = ?";
        /** @var mysqli $conn */
        $stmt_cek = mysqli_prepare($conn, $sql_cek);
        mysqli_stmt_bind_param($stmt_cek, "isi", $id_santri, $bulan, $tahun);
        mysqli_stmt_execute($stmt_cek);
        $res_cek = mysqli_stmt_get_result($stmt_cek);

        if (mysqli_num_rows($res_cek) > 0) {
            $error = "Pembayaran Madin bulan ini sudah ada.";
        } else {
            $sql = "INSERT INTO pembayaran_madin (id_santri, bulan, tahun, nominal, tanggal_bayar, status, id_user) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "isiissi", $id_santri, $bulan, $tahun, $nominal, $tanggal_bayar, $status, $id_user);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: index.php");
                exit;
            } else {
                $error = "Gagal menyimpan pembayaran Madin.";
            }
        }
    }
}

/** @var mysqli $conn */
$santri = mysqli_query($conn, "SELECT id_santri, nama_santri FROM santri WHERE status = 'Aktif' ORDER BY nama_santri ASC");

include "../includes/header.php";
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold m-0">Catat Pembayaran Madin</h4>
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
                        <option value="">-- Pilih Santri --</option>
                        <?php while ($s = mysqli_fetch_assoc($santri)) { ?>
                            <option value="<?= $s["id_santri"] ?>"><?= htmlspecialchars($s["nama_santri"]) ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Bulan</label>
                        <select name="bulan" class="form-select" required>
                            <option value="">-- Pilih Bulan --</option>
                            <option value="Januari">Januari</option>
                            <option value="Februari">Februari</option>
                            <option value="Maret">Maret</option>
                            <option value="April">April</option>
                            <option value="Mei">Mei</option>
                            <option value="Juni">Juni</option>
                            <option value="Juli">Juli</option>
                            <option value="Agustus">Agustus</option>
                            <option value="September">September</option>
                            <option value="Oktober">Oktober</option>
                            <option value="November">November</option>
                            <option value="Desember">Desember</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tahun</label>
                        <input type="number" name="tahun" class="form-control" value="<?= date("Y") ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nominal (Rp)</label>
                    <input type="number" name="nominal" class="form-control" placeholder="Contoh: 50000" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal Bayar</label>
                    <input type="date" name="tanggal_bayar" class="form-control" value="<?= date("Y-m-d") ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="Lunas">Lunas</option>
                    </select>
                </div>

                <button type="submit" name="simpan" class="btn btn-info text-white w-100 fw-bold">Simpan Pembayaran</button>
            </form>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>