<?php
session_start();

if (!isset($_SESSION["id_user"])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/koneksi.php";

// Panggil Header & Sidebar Bootstrap
include "includes/header.php";
?>

<div class="mb-4">
    <h2 class="fw-bold text-dark">Dashboard Utama</h2>
    <p class="text-muted">Selamat datang di Sistem Informasi Manajemen Keuangan Santri (KESAN).</p>
</div>

<!-- KARTU PENCARIAN CEPAT -->
<div class="card card-custom p-4 mb-4 border-0 shadow-sm bg-white">
    <div class="row align-items-center">
        <div class="col-lg-6 mb-3 mb-lg-0">
            <h5 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-magnifying-glass text-primary me-2"></i>Pencarian Cepat Data
            </h5>
            <p class="text-muted small mb-0">Cari data santri, pembayaran SPP, Madin, atau transaksi keuangan dengan mudah.</p>
        </div>
        <div class="col-lg-6">
            <form action="santri/index.php" method="GET" id="dashboardSearchForm">
                <div class="input-group">
                    <select class="form-select flex-grow-0" style="width: auto; max-width: 140px;" id="searchModuleSelect" onchange="document.getElementById('dashboardSearchForm').action = this.value;">
                        <option value="santri/index.php">Santri</option>
                        <option value="spp/index.php">SPP</option>
                        <option value="madin/index.php">Madin</option>
                        <option value="tabungan/index.php">Tabungan</option>
                        <option value="sangu/index.php">Uang Sangu</option>
                    </select>
                    <input type="text" name="q" class="form-control" placeholder="Ketik kata kunci yang dicari..." required>
                    <button class="btn btn-primary fw-bold px-3" type="submit">
                        <i class="fa-solid fa-search me-1"></i> Cari
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- KARTU RINGKASAN MODUL -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card card-custom bg-primary text-white p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0 fw-bold"><i class="fa-solid fa-user-graduate me-2"></i> Data Santri</h5>
            </div>
            <p class="mb-3 small opacity-75">Kelola data santri aktif, data wali, serta jenjang pendidikan santri.</p>
            <a href="santri/index.php" class="btn btn-light btn-sm fw-bold text-primary mt-auto">Kelola Santri &rarr;</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom bg-success text-white p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0 fw-bold"><i class="fa-solid fa-money-bill-wave me-2"></i> Pembayaran SPP</h5>
            </div>
            <p class="mb-3 small opacity-75">Pencatatan iuran bulanan SPP santri secara teratur dan transparan.</p>
            <a href="spp/index.php" class="btn btn-light btn-sm fw-bold text-success mt-auto">Kelola SPP &rarr;</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom bg-warning text-dark p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0 fw-bold"><i class="fa-solid fa-piggy-bank me-2"></i> Tabungan Santri</h5>
            </div>
            <p class="mb-3 small text-dark opacity-75">Layanan transaksi setoran dan penarikan tabungan santri.</p>
            <a href="tabungan/index.php" class="btn btn-dark btn-sm fw-bold text-warning mt-auto">Kelola Tabungan &rarr;</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card card-custom bg-info text-white p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0 fw-bold"><i class="fa-solid fa-school me-2"></i> Pembayaran Madin</h5>
            </div>
            <p class="mb-3 small opacity-75">Pencatatan iuran Madrasah Diniyah (Madin) untuk para santri.</p>
            <a href="madin/index.php" class="btn btn-light btn-sm fw-bold text-info mt-auto">Kelola Madin &rarr;</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom bg-secondary text-white p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0 fw-bold"><i class="fa-solid fa-wallet me-2"></i> Uang Sangu</h5>
            </div>
            <p class="mb-3 small opacity-75">Pengelolaan saldo harian dan penarikan uang sangu santri.</p>
            <a href="sangu/index.php" class="btn btn-light btn-sm fw-bold text-secondary mt-auto">Kelola Uang Sangu &rarr;</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom bg-dark text-white p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0 fw-bold"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Rekap Laporan</h5>
            </div>
            <p class="mb-3 small opacity-75">Ringkasan total kas masuk, pengeluaran, dan laporan siap cetak.</p>
            <a href="laporan/index.php" class="btn btn-outline-light btn-sm fw-bold mt-auto">Lihat Laporan &rarr;</a>
        </div>
    </div>
</div>

<?php 
// Panggil Footer Bootstrap
include "includes/footer.php"; 
?>