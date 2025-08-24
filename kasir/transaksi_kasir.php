<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'kasir') {
    header('Location: ../login/login.php');
    exit;
}

// Ambil data barang
$barang = $conn->query("SELECT * FROM barang");

// Inisialisasi transaksi
if (!isset($_SESSION['transaksi'])) {
    $_SESSION['transaksi'] = [];
}

// Tambah item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $id_barang = $_POST['barang'];
    $jumlah = (int) $_POST['jumlah'];

    $data = $conn->query("SELECT * FROM barang WHERE id_barang='$id_barang'")->fetch_assoc();

    if ($data && $jumlah > 0 && $data['stok'] >= $jumlah) {
        $_SESSION['transaksi'][] = [
            'id_barang'    => $data['id_barang'],
            'nama_barang'  => $data['nama_barang'],
            'harga'        => $data['harga_barang'],
            'jumlah'       => $jumlah,
            'tanggal'      => date('Y-m-d'),
        ];
    }
}

// Reset transaksi
if (isset($_POST['reset'])) {
    unset($_SESSION['transaksi']);
    $_SESSION['transaksi'] = [];
}

// Selesaikan transaksi
if (isset($_POST['bayar'])) {
    $username_kasir = $_SESSION['username']; // ambil username kasir dari session
    $tanggal = date('Y-m-d');
    $no_transaksi = uniqid('trx');

    foreach ($_SESSION['transaksi'] as $item) {
        $id_barang = $item['id_barang'];
        $nama_barang = $item['nama_barang'];
        $jumlah = $item['jumlah'];
        $harga = $item['harga'];
        $total = $jumlah * $harga;

        // Masukkan ke tabel transaksi
       $id_transaksi = uniqid('trx'); // atau gunakan UUID jika lebih kompleks

$conn->query("INSERT INTO transaksi (id_transaksi, tanggal, username_kasir, id_barang, jumlah, harga_satuan) 
              VALUES ('$id_transaksi', '$tanggal', '$username_kasir', '$id_barang', '$jumlah', '$harga')");

        mysqli_query($conn, "INSERT INTO riwayat_transaksi (id_transaksi, tanggal, nama_barang, harga, jumlah, total)
VALUES ('$id_transaksi', '$tanggal', '$nama_barang', '$harga', '$jumlah', '$total')");

        // Kurangi stok barang
        $conn->query("UPDATE barang SET stok = stok - $jumlah WHERE id_barang = '$id_barang'");
    }

    unset($_SESSION['transaksi']);
    $_SESSION['transaksi'] = [];

    header("Location: transaksi_kasir.php?sukses=1");
    exit;
}

// Hitung total
$totalBayar = 0;
foreach ($_SESSION['transaksi'] as $item) {
    $totalBayar += $item['harga'] * $item['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Transaksi Kasir</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head><body class="bg-dark text-light">

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
        <a href="#" class="btn btn-light mb-2 d-flex align-items-center active text-dark">
          <img src="../icon/icon_transaksi.jpg" class="me-2" style="height:24px;"> Transaksi
        </a>
        <a href="riwayat.php" class="btn btn-outline-light mb-2 d-flex align-items-center">
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
        <div class="row">
          <!-- Transaction Area -->
          <div class="col-md-8">
            <h3 class="mb-4 text-warning">Transaksi</h3>

            <?php if (isset($_GET['sukses'])): ?>
              <div class="alert alert-success">
                Transaksi berhasil diselesaikan.
              </div>
            <?php endif; ?>

            <!-- Form Input -->
            <form method="POST" class="mb-4">
              <div class="row g-2">
                <div class="col-md-6">
                  <label class="form-label">Cari Barang</label>
                  <select name="barang" class="form-select" required>
                    <option value="">-- Pilih Barang --</option>
                    <?php 
                    $barang->data_seek(0);
                    while ($row = $barang->fetch_assoc()): 
                    ?>
                      <option value="<?= $row['id_barang'] ?>"><?= htmlspecialchars($row['nama_barang']) ?> - Stok: <?= $row['stok'] ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Jumlah</label>
                  <select name="jumlah" class="form-select" required>
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                      <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                  </select>
                </div>
              </div>
              <div class="mt-3">
                <button type="submit" name="tambah" class="btn btn-primary me-2">Tambah</button>
                <button type="submit" name="reset" class="btn btn-secondary">Ulangi</button>
              </div>
            </form>

            <!-- Transaction Table -->
            <div class="table-responsive">
              <table class="table table-dark table-striped table-bordered mb-0">
                <thead class="table-secondary text-dark">
                  <tr>
                    <th>No</th>
                    <th>ID Barang</th>
                    <th>Tanggal</th>
                    <th>Nama Barang</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($_SESSION['transaksi']) == 0): ?>
                    <tr><td colspan="7" class="text-center text-muted">Belum ada transaksi</td></tr>
                  <?php else: ?>
                    <?php foreach ($_SESSION['transaksi'] as $i => $item): ?>
                      <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($item['id_barang']) ?></td>
                        <td><?= $item['tanggal'] ?></td>
                        <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                        <td>Rp. <?= number_format($item['harga'], 0, ',', '.') ?></td>
                        <td><?= $item['jumlah'] ?></td>
                        <td>Rp. <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Nota Area -->
          <div class="col-md-4">
            <div class="card shadow-sm bg-dark text-light">
              <div class="card-body">
                <h5 class="card-title">No. <?= uniqid('trx') ?></h5>
                <h6 class="card-subtitle mb-2 text-secondary">Total</h6>
                <p class="card-text fs-4 fw-bold">Rp. <?= number_format($totalBayar, 0, ',', '.') ?></p>
                <form method="POST">
                  <input type="hidden" name="bayar" value="1">
                  <button type="submit" class="btn btn-success w-100">Selesaikan Transaksi</button>
                </form>
              </div>
            </div>
          </div>
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