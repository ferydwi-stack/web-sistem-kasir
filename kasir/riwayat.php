<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'kasir') {
    header('Location: ../login/login.php');
    exit;
}

$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

$query = "SELECT * FROM riwayat_transaksi";
if (!empty($tanggal)) {
    $query .= " WHERE tanggal = '$tanggal'";
}
$query .= " ORDER BY tanggal DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Penjualan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/riwayat.css">
</head>
<body>

  <!-- Header -->
  <div class="header">
    <img src="../logo/logo_login.jpg" alt="Logo" class="header-logo">
    <h5 class="header-title">SISTEM KASIR</h5>
  </div>

  <!-- Main Container -->
  <div class="container-fluid">
    <div class="row">

      <!-- Sidebar -->
      <div class="col-md-2 sidebar">
        <div class="menu-title">Kasir</div>
        <hr>
        <a href="kelola_produk_kasir.php" class="nav-link-custom">
          <img src="../icon/icon_produk.png" class="icon-img"> Produk Tersedia
        </a>
        <a href="transaksi_kasir.php" class="nav-link-custom">
          <img src="../icon/icon_transaksi.jpg" class="icon-img"> Transaksi
        </a>
        <a href="riwayat.php" class="nav-link-custom active">
          <img src="../icon/icon_laporan.png" class="icon-img"> Riwayat
        </a>
        <div class="mt-auto pt-3">
          <a href="../login/logout.php" class="nav-link-custom">
            <img src="../icon/icon_logout.jpg" class="icon-img"> Log out
          </a>
        </div>
      </div>

      <!-- Main Content -->
      <main class="col-md-10 p-4">
        <h3 class="mb-4">Riwayat Penjualan</h3>

        <!-- Filter Form -->
        <div class="filter-container">
          <form class="filter-form" method="GET">
            <div class="filter-group">
              <label class="filter-label" for="tanggal">Filter Tanggal:</label>
              <input type="date" name="tanggal" id="tanggal" class="filter-input" value="<?= htmlspecialchars($tanggal) ?>">
            </div>
            <div class="filter-group">
              <button type="submit" class="btn-filter">Filter</button>
            </div>
            <div class="filter-group">
              <a href="riwayat.php" class="btn-reset">Reset</a>
            </div>
          </form>
        </div>

        <!-- Transaction History Table -->
        <div class="table-container">
          <table class="table table-bordered mb-0">
            <thead>
              <tr>
                <th>No</th>
                <th>ID Transaksi</th>
                <th>Tanggal</th>
                <th>Nama Barang</th>
                <th>Harga Barang</th>
                <th>Jumlah Beli</th>
                <th>Total Pembayaran</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              $total_keseluruhan = 0;
              $found_data = false;
              
              while ($row = mysqli_fetch_assoc($result)) {
                $found_data = true;
                $total_keseluruhan += $row['total'];
                echo "<tr>";
                echo "<td>$no</td>";
                echo "<td>" . htmlspecialchars($row['id_transaksi']) . "</td>";
                echo "<td>{$row['tanggal']}</td>";
                echo "<td>" . htmlspecialchars($row['nama_barang']) . "</td>";
                echo "<td>Rp. " . number_format($row['harga'], 0, ',', '.') . "</td>";
                echo "<td>{$row['jumlah']}</td>";
                echo "<td>Rp. " . number_format($row['total'], 0, ',', '.') . "</td>";
                echo "</tr>";
                $no++;
              }

              if (!$found_data) {
                echo "<tr><td colspan='7' class='empty-message'>Belum ada transaksi" . (!empty($tanggal) ? " pada tanggal " . $tanggal : "") . "</td></tr>";
              } else {
                // Tampilkan total keseluruhan jika ada data
                echo "<tr style='background-color: #f8f9fa; font-weight: bold;'>";
                echo "<td colspan='6' style='text-align: right;'>Total Keseluruhan:</td>";
                echo "<td>Rp. " . number_format($total_keseluruhan, 0, ',', '.') . "</td>";
                echo "</tr>";
              }
              ?>
            </tbody>
          </table>
        </div>

        <?php if ($found_data): ?>
          <div class="mt-3">
            <small class="text-muted">
              Total <?= $no - 1 ?> transaksi<?= !empty($tanggal) ? " pada tanggal " . $tanggal : "" ?>
            </small>
          </div>
        <?php endif; ?>
      </main>

    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    editor bay @vitamin
  </div>

</body>
</html>