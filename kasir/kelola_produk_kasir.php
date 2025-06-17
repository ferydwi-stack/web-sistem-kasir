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
  <link rel="stylesheet" href="../css/kelola_produk_kasir.css">
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
        <a href="#" class="nav-link-custom active">
          <img src="../icon/icon_produk.png" class="icon-img"> Produk Tersedia
        </a>
        <a href="transaksi_kasir.php" class="nav-link-custom">
          <img src="../icon/icon_transaksi.jpg" class="icon-img"> Transaksi
        </a>
        <a href="riwayat.php" class="nav-link-custom">
          <img src="../icon/icon_riwayat.png" class="icon-img"> Riwayat
        </a>
        <div class="mt-auto pt-3">
          <a href="../login/logout.php" class="nav-link-custom">
            <img src="../icon/icon_logout.jpg" class="icon-img"> Log out
          </a>
        </div>
      </div>

      <!-- Main Content -->
      <main class="col-md-10 main-content">
        <h3 class="page-title">Produk Tersedia</h3>

        <div class="table-container">
          <table class="custom-table">
            <thead>
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
  <div class="footer">
    editor bay @vitamin
  </div>

</body>
</html>