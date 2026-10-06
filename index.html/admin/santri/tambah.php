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

/* ==============================================================
   PROSES SIMPAN DATA SANTRI
   ============================================================== */
if (isset($_POST["simpan"])) {
    $nama_santri   = trim($_POST["nama_santri"] ?? '');
    $jenis_kelamin = $_POST["jenis_kelamin"] ?? 'L';
    $id_jenjang    = !empty($_POST["id_jenjang"]) ? (int)$_POST["id_jenjang"] : NULL;
    $kelas         = trim($_POST["kelas"] ?? '');
    $nama_ortu     = trim($_POST["nama_ortu"] ?? '');
    $no_hp_ortu    = trim($_POST["no_hp_ortu"] ?? '');
    $alamat        = trim($_POST["alamat"] ?? '');
    $status        = $_POST["status"] ?? 'Aktif';

    if (empty($nama_santri) || empty($jenis_kelamin) || empty($id_jenjang) || empty($kelas) || empty($nama_ortu) || empty($no_hp_ortu) || empty($alamat)) {
        $error = "Semua kolom wajib diisi!";
    } else {
        $sql = "INSERT INTO santri (nama_santri, jenis_kelamin, id_jenjang, kelas, nama_ortu, no_hp_ortu, alamat, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        /** @var mysqli $conn */
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssisssss", $nama_santri, $jenis_kelamin, $id_jenjang, $kelas, $nama_ortu, $no_hp_ortu, $alamat, $status);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal menyimpan data santri.";
        }
    }
}

/* ==============================================================
   AMBIL DATA JENJANG UNTUK DROPDOWN
   ============================================================== */
$sql_jenjang = "SELECT id_jenjang, nama_jenjang FROM jenjang ORDER BY nama_jenjang ASC";
/** @var mysqli $conn */
$res_jenjang = @mysqli_query($conn, $sql_jenjang);

include "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Tambah Santri Baru</h4>
        <p class="text-muted small mb-0">Lengkapi formulir untuk menambahkan data santri</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary fw-semibold">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Data Santri
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
                        <label class="form-label fw-semibold">Nama Santri</label>
                        <input type="text" name="nama_santri" class="form-control" placeholder="Masukkan nama santri" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="L">Laki-Laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jenjang Pendidikan</label>
                        <select name="id_jenjang" class="form-select" required>
                            <option value="">-- Pilih Jenjang --</option>
                            <?php 
                            if ($res_jenjang && mysqli_num_rows($res_jenjang) > 0) {
                                while ($j = mysqli_fetch_assoc($res_jenjang)) { 
                            ?>
                                    <option value="<?= $j['id_jenjang'] ?>"><?= htmlspecialchars($j['nama_jenjang']) ?></option>
                            <?php 
                                }
                            } 
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kelas</label>
                        <input type="text" name="kelas" class="form-control" placeholder="Contoh: 7A, 10-IPA" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Orang Tua / Wali</label>
                        <input type="text" name="nama_ortu" class="form-control" placeholder="Masukkan nama orang tua/wali" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">No. HP Orang Tua / Wali</label>
                        <input type="text" name="no_hp_ortu" class="form-control" placeholder="Contoh: 081234567890" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat lengkap santri" required></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Status Santri</label>
                        <select name="status" class="form-select" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-primary fw-semibold w-100 py-2">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Santri
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>