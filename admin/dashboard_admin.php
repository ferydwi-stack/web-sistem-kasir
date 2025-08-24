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
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
      <!-- Sidebar -->
<aside class="col-12 col-md-2 collapse d-md-block p-3" id="sidebarMenu" 
       style="background-color:#495057; color:#f8f9fa; min-height:100vh;">
  <h6 class="text-center text-warning mb-3 fw-bold">Admin Menu</h6>
  <div class="list-group list-group-flush">
    <a href="#" class="list-group-item list-group-item-action bg-light text-dark active d-flex align-items-center mb-2 rounded">
      <img src="../icon/icon_dashboard.png" class="me-2" style="height:22px;"> Dashboard
    </a>
    <a href="kelola_produk_admin.php" class="list-group-item list-group-item-action bg-light text-dark d-flex align-items-center mb-2 rounded">
      <img src="../icon/icon_produk.png" class="me-2" style="height:22px;"> Kelola Produk
    </a>
    <a href="laporan_penjualan.php" class="list-group-item list-group-item-action bg-light text-dark d-flex align-items-center mb-2 rounded">
      <img src="../icon/icon_laporan.png" class="me-2" style="height:22px;"> Laporan Penjualan
    </a>
    <a href="kelola_akun.php" class="list-group-item list-group-item-action bg-light text-dark d-flex align-items-center mb-2 rounded">
      <img src="../icon/icon_kelola_akun.png" class="me-2" style="height:22px;"> Kelola Akun
    </a>
    <a href="../login/logout.php" class="list-group-item list-group-item-action bg-danger text-light d-flex align-items-center mt-2 rounded">
      <img src="../icon/icon_logout.jpg" class="me-2" style="height:22px;"> Logout
    </a>
  </div>
</aside>


      <!-- Main Content -->
      <main class="col-12 col-md-10 px-3 py-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center mb-4">
          <h3 class="text-warning fw-bold mb-2 mb-md-0">Stok Menipis</h3>
          <span class="ms-md-2 badge bg-warning text-dark fs-6">Barang &lt; 10</span>
        </div>

        <?php if (empty($stok_rendah)): ?>
          <div class="alert alert-info shadow-sm">Tidak ada barang dengan stok di bawah 10.</div>
        <?php else: ?>
          <div class="row g-3">
            <?php foreach ($stok_rendah as $barang): ?>
              <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 bg-secondary text-light shadow-sm border-0">
                  <div class="card-body">
                    <h5 class="card-title fw-bold text-warning">
                      <?= htmlspecialchars($barang['nama_barang']) ?>
                    </h5>
                    <p class="card-text">
                      Stok: <span class="badge bg-danger fs-6 px-3"><?= $barang['stok'] ?></span>
                    </p>
                    <a href="kelola_produk_admin.php" class="btn btn-warning btn-sm w-100">Perbarui Stok</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </main>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-center py-3 mt-4 border-top border-secondary">
    <span class="text-secondary">Editor by @vitamin</span>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
