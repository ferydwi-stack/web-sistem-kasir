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
  <link rel="stylesheet" href="../css/laporan_penjualan.css">
</head>
<body>

  <!-- Header -->
  <div class="header">
    <img src="../logo/logo_login.jpg" alt="Logo" class="header-logo">
    <h5 class="header-title">SISTEM KASIR VIA ADMIN</h5>
  </div>

  <!-- Main Container -->
  <div class="container-fluid">
    <div class="row">

      <!-- Sidebar -->
      <div class="col-md-2 sidebar">
        <div class="menu-title">Admin</div>
        <hr>
        <a href="dashboard_admin.php" class="nav-link-custom">
          <img src="../icon/icon_dashboard.png" class="icon-img"> Dashboard
        </a>
        <a href="kelola_produk_admin.php" class="nav-link-custom">
          <img src="../icon/icon_produk.png" class="icon-img"> Kelola Produk
        </a>
        <a href="#" class="nav-link-custom active">
          <img src="../icon/icon_laporan.png" class="icon-img"> Laporan Penjualan
        </a>
        <a href="kelola_akun.php" class="nav-link-custom">
          <img src="../icon/icon_kelola_akun.png" class="icon-img"> Kelola Akun
        </a>
        <div class="mt-auto pt-3">
          <a href="../login/logout.php" class="nav-link-custom">
            <img src="../icon/icon_logout.jpg" class="icon-img"> Log out
          </a>
        </div>
      </div>

      <!-- Main Content -->
      <main class="col-md-10 main-content">
        <h3 class="page-title">Laporan Penjualan</h3>

        <!-- Filter Form -->
        <form method="GET" class="filter-form">
          <select name="periode">
            <option value="">-- Pilih Periode --</option>
            <option value="hari" <?= $periode == 'hari' ? 'selected' : '' ?>>Hari</option>
            <option value="bulan" <?= $periode == 'bulan' ? 'selected' : '' ?>>Bulan</option>
            <option value="tahun" <?= $periode == 'tahun' ? 'selected' : '' ?>>Tahun</option>
          </select>
          <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
          <input type="text" name="cari_barang" placeholder="Cari Nama Barang" value="<?= htmlspecialchars($cariBarang) ?>">
          <input type="text" name="cari_transaksi" placeholder="Cari No Transaksi" value="<?= htmlspecialchars($cariTransaksi) ?>">
          <button type="submit" class="btn-filter">FILTER</button>
          <a href="#" id="exportExcelBtn" class="btn-excel">📄 Export Excel</a>
        </form>

        <!-- Summary Cards -->
        <div class="summary-row">
          <div class="summary-card green">
            <h4>Total Keuntungan</h4>
            <p>Rp. <?= number_format($totalKeuntungan, 0, ',', '.') ?></p>
          </div>
          <div class="summary-card yellow">
            <h4>Total Barang Terjual</h4>
            <p><?= number_format($totalBarangTerjual) ?> pcs</p>
          </div>
        </div>

        <!-- Date Info -->
        <?php if (!isset($_GET['periode']) && !isset($_GET['tanggal']) && !isset($_GET['cari_barang'])): ?>
          <div class="date-info">
            <h4>Transaksi Hari Ini (<?= date('d-m-Y') ?>)</h4>
          </div>
        <?php endif; ?>

        <!-- Table -->
        <div class="table-container">
          <table class="custom-table">
            <thead>
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
                <tr><td colspan="7">Tidak ada data transaksi.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </main>

    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    editor bay @vitamin
  </div>

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