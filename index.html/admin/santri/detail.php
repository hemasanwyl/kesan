<?php
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

$id_santri = (int) $_GET["id"];

/* Ambil data santri + jenjang */
$sql = "SELECT santri.*, jenjang.nama_jenjang
        FROM santri
        INNER JOIN jenjang ON santri.id_jenjang = jenjang.id_jenjang
        WHERE santri.id_santri = ?";

/** @var mysqli $conn */
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_santri);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$santri = mysqli_fetch_assoc($result);

if (!$santri) {
    header("Location: index.php");
    exit;
}

$wali = $santri["nama_wali"] ?? $santri["nama_ortu"] ?? "-";

include "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold m-0">Detail Profil Santri</h2>
        <p class="text-muted m-0">Informasi lengkap data diri dan administrasi santri</p>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-outline-secondary fw-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Data Santri
        </a>
        <a href="edit.php?id=<?= $santri['id_santri'] ?>" class="btn btn-warning fw-semibold">
            <i class="fa-solid fa-pen-to-square me-1"></i> Edit Data
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- KARTU INFORMASI UTAMA -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 text-center bg-white mb-4">
            <div class="my-3">
                <i class="fa-solid fa-circle-user text-primary" style="font-size: 80px;"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($santri["nama_santri"]) ?></h4>
            <p class="text-muted mb-2">
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                    <?= htmlspecialchars($santri["nama_jenjang"]) ?> - Kelas <?= htmlspecialchars($santri["kelas"] ?? '-') ?>
                </span>
            </p>
            <div>
                <?php if ($santri["status"] == "Aktif") { ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">Status: Aktif</span>
                <?php } else { ?>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">Status: Non-Aktif</span>
                <?php } ?>
            </div>
        </div>

        <!-- AKSES CEPAT KEUANGAN -->
        <div class="card card-custom p-3 bg-white">
            <h6 class="fw-bold text-muted text-uppercase small mb-3">Akses Modul Keuangan</h6>
            <div class="d-grid gap-2">
                <a href="../spp/index.php?q=<?= urlencode($santri['nama_santri']) ?>" class="btn btn-outline-success text-start btn-sm fw-semibold">
                    <i class="fa-solid fa-money-bill-wave me-2"></i> Riwayat SPP Santri &rarr;
                </a>
                <a href="../madin/detail.php?id=<?= $santri['id_santri'] ?>" class="btn btn-outline-info text-start btn-sm fw-semibold">
                    <i class="fa-solid fa-school me-2"></i> Riwayat Madin Santri &rarr;
                </a>
                <a href="../tabungan/detail.php?id=<?= $santri['id_santri'] ?>" class="btn btn-outline-warning text-dark text-start btn-sm fw-semibold">
                    <i class="fa-solid fa-piggy-bank me-2"></i> Tabungan Santri &rarr;
                </a>
                <a href="../sangu/detail.php?id=<?= $santri['id_santri'] ?>" class="btn btn-outline-secondary text-start btn-sm fw-semibold">
                    <i class="fa-solid fa-wallet me-2"></i> Uang Sangu Santri &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- DETAIL BIODATA -->
    <div class="col-lg-8">
        <div class="card card-custom p-4 bg-white">
            <h5 class="fw-bold mb-4 text-dark border-bottom pb-2">
                <i class="fa-solid fa-id-card text-primary me-2"></i>Biodata Lengkap
            </h5>

            <table class="table table-borderless align-middle mb-0">
                <tbody>
                    <tr class="border-bottom">
                        <th width="30%" class="text-muted fw-semibold">Nama Lengkap</th>
                        <td class="fw-bold text-dark fs-5"><?= htmlspecialchars($santri["nama_santri"]) ?></td>
                    </tr>
                    <tr class="border-bottom">
                        <th class="text-muted fw-semibold">Jenis Kelamin</th>
                        <td>
                            <?= ($santri["jenis_kelamin"] ?? 'L') == 'L' ? '<i class="fa-solid fa-mars text-primary me-1"></i> Laki-laki' : '<i class="fa-solid fa-venus text-danger me-1"></i> Perempuan' ?>
                        </td>
                    </tr>
                    <tr class="border-bottom">
                        <th class="text-muted fw-semibold">Jenjang Pendidikan</th>
                        <td><?= htmlspecialchars($santri["nama_jenjang"]) ?></td>
                    </tr>
                    <tr class="border-bottom">
                        <th class="text-muted fw-semibold">Kelas</th>
                        <td><?= htmlspecialchars($santri["kelas"] ?? '-') ?></td>
                    </tr>
                    <tr class="border-bottom">
                        <th class="text-muted fw-semibold">Nama Orang Tua / Wali</th>
                        <td class="fw-semibold"><?= htmlspecialchars($wali) ?></td>
                    </tr>
                    <tr class="border-bottom">
                        <th class="text-muted fw-semibold">No. HP Orang Tua / Wali</th>
                        <td>
                            <?php if (!empty($santri["no_hp_ortu"])) { ?>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $santri["no_hp_ortu"]) ?>" target="_blank" class="text-success text-decoration-none fw-semibold">
                                    <i class="fa-brands fa-whatsapp me-1"></i> <?= htmlspecialchars($santri["no_hp_ortu"]) ?>
                                </a>
                            <?php } else { ?>
                                <span class="text-muted">-</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr class="border-bottom">
                        <th class="text-muted fw-semibold">Alamat Rumah</th>
                        <td><?= nl2br(htmlspecialchars($santri["alamat"] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted fw-semibold">Status Keaktifan</th>
                        <td>
                            <?php if ($santri["status"] == "Aktif") { ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php } else { ?>
                                <span class="badge bg-secondary">Non-Aktif</span>
                            <?php } ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>