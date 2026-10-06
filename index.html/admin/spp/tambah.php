<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

$error = "";

/* =========================
   PROSES SIMPAN
   ========================= */

if (isset($_POST["simpan"])) {

    $id_santri = $_POST["id_santri"];
    $bulan = $_POST["bulan"];
    $tahun = $_POST["tahun"];
    $tanggal_bayar = $_POST["tanggal_bayar"];
    $status = $_POST["status"];
    $id_user = $_SESSION["id_user"];

    /* Ambil jenjang santri */
    $sql = "SELECT jenjang.nama_jenjang
            FROM santri
            INNER JOIN jenjang
                ON santri.id_jenjang = jenjang.id_jenjang
            WHERE santri.id_santri = ?";

    /** @var mysqli $conn */
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_santri);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data_santri = mysqli_fetch_assoc($result);

    if (!$data_santri) {
        $error = "Santri tidak ditemukan.";
    } else {
        $nama_jenjang = $data_santri["nama_jenjang"];

        /* Tentukan tarif */
        if ($nama_jenjang == "SD") {
            $nominal = 650000;
        } else {
            $nominal = 700000;
        }

        /* Cek apakah bulan tersebut sudah dibayar */
        $sql = "SELECT id_pembayaran
                FROM pembayaran_spp
                WHERE id_santri = ?
                AND bulan = ?
                AND tahun = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isi", $id_santri, $bulan, $tahun);
        mysqli_stmt_execute($stmt);
        $cek = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($cek) > 0) {
            $error = "SPP untuk bulan tersebut sudah tercatat.";
        } else {
            /* Simpan pembayaran */
            $sql = "INSERT INTO pembayaran_spp
                    (id_santri, bulan, tahun, nominal, tanggal_bayar, status, id_user)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param(
                $stmt,
                "isiissi",
                $id_santri,
                $bulan,
                $tahun,
                $nominal,
                $tanggal_bayar,
                $status,
                $id_user
            );

            if (mysqli_stmt_execute($stmt)) {
                header("Location: index.php");
                exit;
            } else {
                $error = "Pembayaran SPP gagal disimpan.";
            }
        }
    }
}

/* =========================
   AMBIL DATA SANTRI
   ========================= */
/** @var mysqli $conn */
$santri = mysqli_query(
    $conn,
    "SELECT
        santri.id_santri,
        santri.nama_santri,
        jenjang.nama_jenjang
     FROM santri
     INNER JOIN jenjang
        ON santri.id_jenjang = jenjang.id_jenjang
     WHERE santri.status = 'Aktif'
     ORDER BY santri.nama_santri ASC"
);

include "../includes/header.php";
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-custom p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold m-0">Catat Pembayaran SPP</h4>
                <a href="index.php" class="btn btn-outline-secondary btn-sm">
                    &larr; Kembali
                </a>
            </div>
            <hr>

            <?php if ($error != "") { ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php } ?>

            <form method="POST">
                <!-- SANTRI -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Santri</label>
                    <select name="id_santri" id="id_santri" class="form-select" required>
                        <option value="">-- Pilih Santri --</option>
                        <?php while ($s = mysqli_fetch_assoc($santri)) { ?>
                            <option
                                value="<?= $s["id_santri"] ?>"
                                data-jenjang="<?= htmlspecialchars($s["nama_jenjang"]) ?>"
                            >
                                <?= htmlspecialchars($s["nama_santri"]) ?> - <?= htmlspecialchars($s["nama_jenjang"]) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="row">
                    <!-- JENJANG -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Jenjang</label>
                        <input type="text" id="jenjang" class="form-control bg-light" readonly>
                    </div>

                    <!-- NOMINAL -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nominal SPP</label>
                        <input type="text" id="nominal_tampil" class="form-control bg-light text-success fw-bold" readonly>
                        <input type="hidden" name="nominal" id="nominal">
                    </div>
                </div>

                <div class="row">
                    <!-- BULAN -->
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

                    <!-- TAHUN -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tahun</label>
                        <input type="number" name="tahun" class="form-control" value="<?= date("Y") ?>" required>
                    </div>
                </div>

                <!-- TANGGAL -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal Bayar</label>
                    <input type="date" name="tanggal_bayar" class="form-control" value="<?= date("Y-m-d") ?>" required>
                </div>

                <!-- STATUS -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="Lunas">Lunas</option>
                    </select>
                </div>

                <button type="submit" name="simpan" class="btn btn-primary w-100 fw-bold">
                    Simpan Pembayaran
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const santriSelect = document.getElementById("id_santri");
    const jenjangInput = document.getElementById("jenjang");
    const nominalTampil = document.getElementById("nominal_tampil");
    const nominalInput = document.getElementById("nominal");

    santriSelect.addEventListener("change", function () {
        const option = this.options[this.selectedIndex];
        const jenjang = option.dataset.jenjang;

        jenjangInput.value = jenjang || "";

        let nominal = 700000;
        if (jenjang === "SD") {
            nominal = 650000;
        }

        nominalTampil.value = "Rp " + nominal.toLocaleString("id-ID");
        nominalInput.value = nominal;
    });
</script>

<?php include "../includes/footer.php"; ?>