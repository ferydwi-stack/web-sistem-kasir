<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login/login.php');
    exit;
}

$username = $_SESSION['username'];

$periode = $_GET['periode'] ?? 'hari';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$cariBarang = $_GET['cari_barang'] ?? '';
$cariTransaksi = $_GET['cari_transaksi'] ?? '';

$where = [];

if (!empty($cariTransaksi)) {
    $cariTransaksi = mysqli_real_escape_string($conn, $cariTransaksi);
    $where[] = "t.id_transaksi LIKE '%$cariTransaksi%'";
} else {
    if ($periode && $tanggal) {
        $tanggalObj = date('Y-m-d', strtotime($tanggal));
        if ($periode == 'hari') {
            $where[] = "t.tanggal = '$tanggalObj'";
        } elseif ($periode == 'bulan') {
            $bulan = date('m', strtotime($tanggal));
            $tahun = date('Y', strtotime($tanggal));
            $where[] = "MONTH(t.tanggal) = '$bulan'";
            $where[] = "YEAR(t.tanggal) = '$tahun'";
        } elseif ($periode == 'tahun') {
            $tahun = date('Y', strtotime($tanggal));
            $where[] = "YEAR(t.tanggal) = '$tahun'";
        }
    }
}

// Pencarian barang, hanya ditambahkan jika ada input untuk barang
if (!empty($cariBarang)) {
    $cariBarang = mysqli_real_escape_string($conn, $cariBarang);
    $where[] = "b.nama_barang LIKE '%$cariBarang%'";
}

// Jika tidak ada filter lain, tampilkan semua data berdasarkan transaksi
$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Query untuk mengambil data transaksi
$query = "
SELECT 
    t.id_transaksi,
    t.tanggal,
    k.nama AS nama_kasir,
    b.nama_barang,
    t.jumlah,
    t.harga_satuan AS harga,
    (t.jumlah * t.harga_satuan) AS total
FROM transaksi t
JOIN kasir k ON t.username_kasir = k.username
JOIN barang b ON t.id_barang = b.id_barang
$whereClause
ORDER BY t.tanggal DESC
LIMIT 50
";

$result = mysqli_query($conn, $query);

// Query untuk summary keuntungan dan barang terjual
$summaryQuery = "
SELECT 
    SUM(t.jumlah * t.harga_satuan) AS total_keuntungan,
    SUM(t.jumlah) AS total_barang_terjual
FROM transaksi t
JOIN barang b ON t.id_barang = b.id_barang
$whereClause
";
$summaryResult = mysqli_query($conn, $summaryQuery);
$summary = mysqli_fetch_assoc($summaryResult);
$totalKeuntungan = $summary['total_keuntungan'] ?? 0;
$totalBarangTerjual = $summary['total_barang_terjual'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Laporan Penjualan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-dark text-light">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center fw-bold" href="#">
        <img src="../logo/logo_login.jpg" alt="Logo" class="me-2 rounded" style="height:40px;">
        SISTEM KASIR <span class="text-warning ms-1">ADMIN</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">

      <!-- Sidebar -->
      <div class="col-md-2 d-flex flex-column" style="background-color:#343a40; min-height:100vh; padding-top:1rem;">
        <h5 class="text-center text-warning mb-3">Admin</h5>
        <hr class="border-secondary">
        <a href="dashboard_admin.php" class="btn btn-outline-light mb-2 d-flex align-items-center">
          <img src="../icon/icon_dashboard.png" class="me-2" style="height:24px;"> Dashboard
        </a>
        <a href="kelola_produk_admin.php" class="btn btn-outline-light mb-2 d-flex align-items-center">
          <img src="../icon/icon_produk.png" class="me-2" style="height:24px;"> Kelola Produk
        </a>
        <a href="#" class="btn btn-light mb-2 d-flex align-items-center active text-dark">
          <img src="../icon/icon_laporan.png" class="me-2" style="height:24px;"> Laporan Penjualan
        </a>
        <a href="kelola_akun.php" class="btn btn-outline-light mb-2 d-flex align-items-center">
          <img src="../icon/icon_kelola_akun.png" class="me-2" style="height:24px;"> Kelola Akun
        </a>
        <div class="mt-auto pt-3">
          <a href="../login/logout.php" class="btn btn-danger d-flex align-items-center">
            <img src="../icon/icon_logout.jpg" class="me-2" style="height:24px;"> Log out
          </a>
        </div>
      </div>

      <!-- Main Content -->
      <main class="col-md-10 py-4">
        <h3 class="mb-4 text-warning">Laporan Penjualan</h3>

        <!-- Filter Form -->
        <form method="GET" class="row g-2 align-items-end mb-4">
          <div class="col-auto">
            <select name="periode" class="form-select">
              <option value="">-- Pilih Periode --</option>
              <option value="hari" <?= $periode == 'hari' ? 'selected' : '' ?>>Hari</option>
              <option value="bulan" <?= $periode == 'bulan' ? 'selected' : '' ?>>Bulan</option>
              <option value="tahun" <?= $periode == 'tahun' ? 'selected' : '' ?>>Tahun</option>
            </select>
          </div>
          <div class="col-auto">
            <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal) ?>">
          </div>
          <div class="col-auto">
            <input type="text" name="cari_barang" class="form-control" placeholder="Cari Nama Barang" value="<?= htmlspecialchars($cariBarang) ?>">
          </div>
          <div class="col-auto">
            <input type="text" name="cari_transaksi" class="form-control" placeholder="Cari No Transaksi" value="<?= htmlspecialchars($cariTransaksi) ?>">
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-primary">FILTER</button>
          </div>
          <div class="col-auto">
            <a href="#" id="exportExcelBtn" class="btn btn-success">📄 Export Excel</a>
          </div>
        </form>

        <!-- Summary Cards -->
        <div class="row mb-4">
          <div class="col-md-6">
            <div class="card text-white bg-success mb-3">
              <div class="card-body">
                <h5 class="card-title">Total Keuntungan</h5>
                <p class="card-text fs-4">Rp. <?= number_format($totalKeuntungan, 0, ',', '.') ?></p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card text-dark bg-warning mb-3">
              <div class="card-body">
                <h5 class="card-title">Total Barang Terjual</h5>
                <p class="card-text fs-4"><?= number_format($totalBarangTerjual) ?> pcs</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
          <table class="table table-bordered table-dark mb-0">
            <thead class="table-secondary text-dark">
              <tr>
                <th>KASIR</th>
                <th>NO TRANSAKSI</th>
                <th>BARANG</th>
                <th>JUMLAH</th>
                <th>HARGA</th>
                <th>TOTAL</th>
                <th>TANGGAL</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['nama_kasir']) ?></td>
                    <td><?= htmlspecialchars($row['id_transaksi']) ?></td>
                    <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                    <td><?= $row['jumlah'] ?></td>
                    <td>Rp. <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td>Rp. <?= number_format($row['total'], 0, ',', '.') ?></td>
                    <td><?= $row['tanggal'] ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="7" class="text-center text-muted">Tidak ada data transaksi.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </main>

    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-center py-3 mt-4 text-light border-top border-secondary">
    editor bay @vitamin
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('exportExcelBtn').addEventListener('click', function (e) {
      e.preventDefault();
      const params = new URLSearchParams(window.location.search);
      const exportUrl = 'export_excel.php?' + params.toString();
      window.location.href = exportUrl;
    });
  </script>

</body>

</html>