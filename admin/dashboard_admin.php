<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login/login.php');
    exit;
}

$stok_rendah = [];
$sql = "SELECT id_barang, nama_barang, stok FROM barang WHERE stok < 10 ORDER BY stok ASC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $stok_rendah[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/dashboard_admin.css">
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
        <a href="#" class="nav-link-custom active">
          <img src="../icon/icon_dashboard.png" class="icon-img"> Dashboard
        </a>
        <a href="kelola_produk_admin.php" class="nav-link-custom">
          <img src="../icon/icon_produk.png" class="icon-img"> Kelola Produk
        </a>
        <a href="laporan_penjualan.php" class="nav-link-custom">
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
      <main class="col-md-10 p-4">
        <h3 class="text-danger mb-4">Stok Menipis</h3>
        <?php if (empty($stok_rendah)): ?>
          <p class="text-muted">Tidak ada barang dengan stok di bawah 10.</p>
        <?php else: ?>
          <div class="row">
            <?php foreach ($stok_rendah as $barang): ?>
              <div class="col-md-3 mb-4">
                <div class="stok-card">
                  <h5 class="barang-nama"><?= htmlspecialchars($barang['nama_barang']) ?></h5>
                  <p class="barang-stok">Stok: <strong><?= $barang['stok'] ?></strong></p>
                  <a href="kelola_produk_admin.php" class="btn-stok">Perbarui Stok</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </main>

    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    editor bay @vitamin
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>