<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Kesan</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background-color: #2c3e50; color: #fff; }
        .sidebar a { color: #ecf0f1; text-decoration: none; padding: 12px 20px; display: block; border-radius: 6px; margin-bottom: 4px; }
        .sidebar a:hover, .sidebar a.active { background-color: #34495e; color: #1abc9c; }
        .content { padding: 30px; }
        .card-custom { border: none; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .dataTables_wrapper .dataTables_filter input { border-radius: 6px; padding: 5px 12px; border: 1px solid #ced4da; }
        .dataTables_wrapper .dataTables_length select { border-radius: 6px; padding: 4px 10px; border: 1px solid #ced4da; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- SIDEBAR NAVIGASI -->
        <div class="col-md-3 col-lg-2 sidebar p-3 d-none d-md-block">
            <h4 class="text-center py-3 fw-bold border-bottom border-secondary text-success">KESAN</h4>
            <div class="mt-3">
                <a href="/kesan/admin/dashboard.php"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
                <a href="/kesan/admin/santri/index.php"><i class="fa-solid fa-user-graduate me-2"></i> Data Santri</a>
                <a href="/kesan/admin/spp/index.php"><i class="fa-solid fa-money-bill-wave me-2"></i> SPP</a>
                <a href="/kesan/admin/madin/index.php"><i class="fa-solid fa-school me-2"></i> Madin</a>
                <a href="/kesan/admin/sangu/index.php"><i class="fa-solid fa-wallet me-2"></i> Uang Sangu</a>
                <a href="/kesan/admin/tabungan/index.php"><i class="fa-solid fa-piggy-bank me-2"></i> Tabungan</a>
                <a href="/kesan/admin/users/index.php"><i class="fa-solid fa-users-gear me-2"></i> Kelola Admin</a>
                <a href="/kesan/admin/laporan/index.php"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Laporan</a>
                <hr class="border-secondary">
                <a href="/kesan/auth/logout.php" class="text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
            </div>
        </div>

        <!-- AREA KONTEN UTAMA -->
        <div class="col-md-9 col-lg-10 content">
            <!-- NAVBAR ATAS -->
            <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded card-custom flex-wrap gap-2">
                <div class="d-flex align-items-center flex-grow-1">
                    <h5 class="m-0 text-secondary fw-bold me-3 d-none d-lg-block">Sistem Keuangan Santri</h5>
                    <!-- Form Pencarian Cepat Santri Global -->
                    <form action="/kesan/admin/santri/index.php" method="GET" class="d-flex me-auto" style="min-width: 250px; max-width: 360px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Cari santri cepat..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" aria-label="Cari santri">
                            <button class="btn btn-primary btn-sm px-3" type="submit">Cari</button>
                        </div>
                    </form>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary fs-6 py-2 px-3"><i class="fa-solid fa-user me-1"></i> Admin: <?= htmlspecialchars($_SESSION["nama"] ?? 'User') ?></span>
                </div>
            </div>