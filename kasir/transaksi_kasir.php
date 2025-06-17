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
  <link rel="stylesheet" href="../css/transaksi_kasir.css">
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
        <a href="#" class="nav-link-custom active">
          <img src="../icon/icon_transaksi.jpg" class="icon-img"> Transaksi
        </a>
        <a href="riwayat.php" class="nav-link-custom">
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
        <div class="row">
          <!-- Transaction Area -->
          <div class="col-md-8">
            <h3 class="mb-4">Transaksi</h3>

            <?php if (isset($_GET['sukses'])): ?>
              <div class="alert-success">
                Transaksi berhasil diselesaikan.
              </div>
            <?php endif; ?>

            <!-- Form Input -->
            <div class="form-container">
              <form method="POST">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Cari Barang</label>
                      <select name="barang" class="form-select" required>
                        <option value="">-- Pilih Barang --</option>
                        <?php 
                        // Reset pointer untuk mengulang data barang
                        $barang->data_seek(0);
                        while ($row = $barang->fetch_assoc()): 
                        ?>
                          <option value="<?= $row['id_barang'] ?>"><?= htmlspecialchars($row['nama_barang']) ?> - Stok: <?= $row['stok'] ?></option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Jumlah</label>
                      <select name="jumlah" class="form-select" required>
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                          <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="mt-3">
                  <button type="submit" name="tambah" class="btn-tambah">Tambah</button>
                  <button type="submit" name="reset" class="btn-reset">Ulangi</button>
                </div>
              </form>
            </div>

            <!-- Transaction Table -->
            <div class="table-container">
              <table class="table table-bordered mb-0">
                <thead>
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
            <div class="nota-card">
              <div class="nota-number">No. <?= uniqid('trx') ?></div>
              <div class="nota-total">Rp. <?= number_format($totalBayar, 0, ',', '.') ?></div>
              <form method="POST">
                <input type="hidden" name="bayar" value="1">
                <button type="submit" class="btn-bayar">Selesaikan Transaksi</button>
              </form>
            </div>
          </div>
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