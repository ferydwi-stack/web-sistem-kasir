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

</head>
<body class="bg-dark text-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center fw-bold" href="#">
        <img src="../logo/logo_login.jpg" alt="Logo" class="me-2 rounded" style="height:40px;">
        SISTEM KASIR <span class="text-warning ms-1">KASIR</span>
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
      <h5 class="text-center text-warning mb-3">Kasir</h5>
      <hr class="border-secondary">
      <a href="kelola_produk_kasir.php" class="btn btn-outline-light mb-2 d-flex align-items-center">
        <img src="../icon/icon_produk.png" class="me-2" style="height:24px;"> Produk Tersedia
      </a>
      <a href="transaksi_kasir.php" class="btn btn-outline-light mb-2 d-flex align-items-center">
        <img src="../icon/icon_transaksi.jpg" class="me-2" style="height:24px;"> Transaksi
      </a>
      <a href="riwayat.php" class="btn btn-light mb-2 d-flex align-items-center active text-dark">
        <img src="../icon/icon_laporan.png" class="me-2" style="height:24px;"> Riwayat
      </a>
      <div class="mt-auto pt-3">
        <a href="../login/logout.php" class="btn btn-danger d-flex align-items-center">
          <img src="../icon/icon_logout.jpg" class="me-2" style="height:24px;"> Log out
        </a>
      </div>
    </div>

    <!-- Main Content -->
    <main class="col-md-10 py-4">
      <h3 class="mb-4 text-warning">Riwayat Penjualan</h3>

      <!-- Filter Form -->
      <form class="row g-2 align-items-end mb-4" method="GET">
        <div class="col-auto">
          <label for="tanggal" class="form-label mb-0 text-light">Filter Tanggal:</label>
        </div>
        <div class="col-auto">
          <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal) ?>">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">Filter</button>
        </div>
        <div class="col-auto">
          <a href="riwayat.php" class="btn btn-secondary">Reset</a>
        </div>
      </form>

      <!-- Table -->
      <div class="table-responsive">
        <table class="table table-dark table-striped table-bordered mb-0">
          <thead class="table-secondary text-dark">
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
              echo "<tr><td colspan='7' class='text-center text-muted'>Belum ada transaksi" . (!empty($tanggal) ? " pada tanggal " . $tanggal : "") . "</td></tr>";
            } else {
              echo "<tr class='bg-light fw-bold'>";
              echo "<td colspan='6' class='text-end'>Total Keseluruhan:</td>";
              echo "<td>Rp. " . number_format($total_keseluruhan, 0, ',', '.') . "</td>";
              echo "</tr>";
            }
            ?>
          </tbody>
        </table>
      </div>

      <?php if ($found_data): ?>
        <div class="mt-3 text-light">
          <small>
            Total <?= $no - 1 ?> transaksi<?= !empty($tanggal) ? " pada tanggal " . $tanggal : "" ?>
          </small>
        </div>
      <?php endif; ?>
    </main>

  </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-center py-3 mt-4 text-light border-top border-secondary">
  editor bay @vitamin
</footer>

</body>
</html>