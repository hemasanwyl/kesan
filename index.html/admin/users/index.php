<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once "../../config/koneksi.php";

/** @var mysqli $conn */

// Proses Hapus Admin
if (isset($_GET['hapus'])) {
    $id_user_hapus = (int)$_GET['hapus'];
    
    // Mencegah menghapus akun sendiri
    if ($id_user_hapus == $_SESSION['id_user']) {
        $_SESSION['msg_error'] = "Anda tidak dapat menghapus akun Anda sendiri!";
    } else {
        $sql_hapus = "DELETE FROM users WHERE id_user = ?";
        $stmt_hapus = mysqli_prepare($conn, $sql_hapus);
        mysqli_stmt_bind_param($stmt_hapus, "i", $id_user_hapus);
        if (mysqli_stmt_execute($stmt_hapus)) {
            $_SESSION['msg_success'] = "Pengguna berhasil dihapus!";
        } else {
            $_SESSION['msg_error'] = "Gagal menghapus pengguna.";
        }
    }
    header("Location: index.php");
    exit;
}

// Parameter Pencarian
$q = trim($_GET["q"] ?? "");
$is_filtered = ($q !== "");

// Ambil Data User Berdasarkan Pencarian
$sql = "SELECT * FROM users";
if ($is_filtered) {
    $sql .= " WHERE nama LIKE ? OR username LIKE ?";
}
$sql .= " ORDER BY nama ASC";

if ($is_filtered) {
    $stmt = mysqli_prepare($conn, $sql);
    $term = "%" . $q . "%";
    mysqli_stmt_bind_param($stmt, "ss", $term, $term);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}

$total_users = $result ? mysqli_num_rows($result) : 0;

include "../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Kelola Admin / Administrator</h4>
        <p class="text-muted small mb-0">Kelola daftar akun pengguna yang memiliki akses ke sistem</p>
    </div>
    <a href="tambah.php" class="btn btn-primary fw-semibold">
        <i class="fa-solid fa-user-plus me-1"></i> Tambah Admin
    </a>
</div>

<?php if (isset($_SESSION['msg_success'])) { ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-1"></i> <?= $_SESSION['msg_success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['msg_success']); ?>
<?php } ?>

<?php if (isset($_SESSION['msg_error'])) { ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-exclamation me-1"></i> <?= $_SESSION['msg_error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['msg_error']); ?>
<?php } ?>

<!-- FORM PENCARIAN -->
<div class="card card-custom p-3 mb-4 bg-white border-0 shadow-sm">
    <form method="GET" action="index.php" class="row g-2 align-items-end">
        <div class="col-md-9 col-lg-10">
            <label class="form-label small fw-bold text-muted mb-1">Cari Admin</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-search"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Cari berdasarkan nama atau username..." value="<?= htmlspecialchars($q) ?>">
            </div>
        </div>
        <div class="col-md-3 col-lg-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                <i class="fa-solid fa-magnifying-glass me-1"></i> Cari
            </button>
            <?php if ($is_filtered) { ?>
                <a href="index.php" class="btn btn-outline-secondary" title="Reset Pencarian">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            <?php } ?>
        </div>
    </form>
    <?php if ($is_filtered) { ?>
        <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">
                <i class="fa-solid fa-info-circle text-primary me-1"></i>
                Ditemukan <strong><?= $total_users ?></strong> admin untuk kata kunci: <span class="badge bg-light text-dark border">"<?= htmlspecialchars($q) ?>"</span>
            </small>
            <a href="index.php" class="small text-danger text-decoration-none fw-semibold">
                <i class="fa-solid fa-xmark me-1"></i>Hapus Filter
            </a>
        </div>
    <?php } ?>
</div>

<div class="card card-custom">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" width="5%">#</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th class="text-center" width="18%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if ($total_users > 0) {
                        while ($row = mysqli_fetch_assoc($result)) { 
                    ?>
                        <tr>
                            <td class="ps-3"><?= $no++ ?></td>
                            <td class="fw-semibold text-dark">
                                <i class="fa-solid fa-circle-user text-primary me-2 fs-5"></i>
                                <?= htmlspecialchars($row['nama']) ?>
                                <?php if ($row['id_user'] == $_SESSION['id_user']) { ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">Saya</span>
                                <?php } ?>
                            </td>
                            <td><code><?= htmlspecialchars($row['username']) ?></code></td>
                            <td class="text-center">
                                <a href="edit.php?id=<?= $row['id_user'] ?>" class="btn btn-sm btn-warning me-1 fw-semibold">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                                <?php if ($row['id_user'] != $_SESSION['id_user']) { ?>
                                    <a href="index.php?hapus=<?= $row['id_user'] ?>" class="btn btn-sm btn-danger fw-semibold" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </a>
                                <?php } else { ?>
                                    <button class="btn btn-sm btn-secondary disabled" title="Tidak dapat menghapus akun sendiri"><i class="fa-solid fa-trash"></i> Hapus</button>
                                <?php } ?>
                <?php 
                        }
                    } 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>