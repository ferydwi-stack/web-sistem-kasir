<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'kasir') {
    header('Location: ../login/login.php');
    exit;
}

$sql = "SELECT * FROM barang";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Kasir</title>
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
        <a href="#" class="btn btn-light mb-2 d-flex align-items-center active text-dark">
          <img src="../icon/icon_produk.png" class="me-2" style="height:24px;"> Produk Tersedia
        </a>
        <a href="transaksi_kasir.php" class="btn btn-outline-light mb-2 d-flex align-items-center">
          <img src="../icon/icon_transaksi.jpg" class="me-2" style="height:24px;"> Transaksi
        </a>
        <a href="riwayat.php" class="btn btn-outline-light mb-2 d-flex align-items-center">
          <img src="../icon/icon_riwayat.png" class="me-2" style="height:24px;"> Riwayat
        </a>
        <div class="mt-auto pt-3">
          <a href="../login/logout.php" class="btn btn-danger d-flex align-items-center">
            <img src="../icon/icon_logout.jpg" class="me-2" style="height:24px;"> Log out
          </a>
        </div>
      </div>

      <!-- Main Content -->
      <main class="col-md-10 py-4">
        <h3 class="mb-4 text-warning">Produk Tersedia</h3>
        <div class="table-responsive">
          <table class="table table-dark table-striped table-bordered mb-0">
            <thead class="table-secondary text-dark">
              <tr>
                <th>Id</th>
                <th>Nama Barang</th>
                <th>Jumlah</th>
                <th>Harga Barang</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id_barang']) ?></td>
                  <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                  <td><?= $row['stok'] ?></td>
                  <td>Rp. <?= number_format($row['harga_barang'], 0, ',', '.') ?></td>
                </tr>
              <?php endwhile; ?>
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

</body>

</html>