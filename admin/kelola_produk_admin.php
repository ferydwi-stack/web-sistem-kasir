<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login/login.php');
    exit;
}

// PROSES FORM TAMBAH / EDIT / HAPUS
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $aksi = $_POST['aksi'];

    if ($aksi == 'tambah') {
        $id = mysqli_real_escape_string($conn, $_POST['id_barang']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama_barang']);
        $stok = mysqli_real_escape_string($conn, $_POST['stok']);
        $harga = mysqli_real_escape_string($conn, $_POST['harga_barang']);
        $conn->query("INSERT INTO barang (id_barang, nama_barang, stok, harga_barang) 
                      VALUES ('$id', '$nama', $stok, $harga)");

    } elseif ($aksi == 'edit') {
        $id = mysqli_real_escape_string($conn, $_POST['id_barang']);
        if (isset($_POST['hapus'])) {
            $conn->query("DELETE FROM barang WHERE id_barang='$id'");
        } else {
            $nama = mysqli_real_escape_string($conn, $_POST['nama_barang']);
            $stok = mysqli_real_escape_string($conn, $_POST['stok']);
            $harga = mysqli_real_escape_string($conn, $_POST['harga_barang']);
            $conn->query("UPDATE barang SET nama_barang='$nama', stok=$stok, harga_barang=$harga 
                          WHERE id_barang='$id'");
        }
    }
    header("Location: kelola_produk_admin.php");
    exit;
}

$sql = "SELECT * FROM barang";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Kelola Produk</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
<div class="bg-dark text-light">

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
        <a href="#" class="btn btn-light mb-2 d-flex align-items-center active">
          <img src="../icon/icon_produk.png" class="me-2" style="height:24px;"> Kelola Produk
        </a>
        <a href="laporan_penjualan.php" class="btn btn-outline-light mb-2 d-flex align-items-center">
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
        <h3 class="mb-4 text-warning">Kelola Produk</h3>
        <button class="btn btn-primary mb-3" onclick="document.getElementById('modalTambah').style.display='flex'">
          + Tambah Barang
        </button>
        <div class="table-responsive">
          <table class="table table-bordered table-dark mb-0">
            <thead class="table-secondary text-dark">
              <tr>
                <th>ID</th>
                <th>Nama Barang</th>
                <th>Stok</th>
                <th>Harga</th>
                <th>Opsi</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['id_barang']) ?></td>
                <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                <td><?= $row['stok'] ?></td>
                <td>Rp. <?= number_format($row['harga_barang'], 0, ',', '.') ?></td>
                <td>
                  <button class="btn btn-warning btn-sm" 
                    onclick="openEditModal('<?= htmlspecialchars($row['id_barang']) ?>', 
                                         '<?= htmlspecialchars($row['nama_barang'], ENT_QUOTES) ?>',
                                         <?= $row['stok'] ?>,
                                         <?= $row['harga_barang'] ?>)">
                    Perbarui
                  </button>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </main>

    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-center py-3 mt-4 border-top border-secondary text-light">
    editor bay @vitamin
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function openEditModal(id, nama, stok, harga) {
      document.getElementById('modalEdit').style.display = 'flex';
      document.getElementById('edit_id_barang').value = id;
      document.getElementById('edit_nama_barang').value = nama;
      document.getElementById('edit_stok').value = stok;
      document.getElementById('edit_harga_barang').value = harga;
    }

    // Tutup modal jika klik di luar kotaknya
    window.onclick = function(event) {
      if (event.target.classList.contains('modal')) {
        event.target.style.display = "none";
      }
    }
  </script>

</body>
</html>