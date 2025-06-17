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
  <link rel="stylesheet" href="../css/kelola_produk_admin.css">
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
        <a href="#" class="nav-link-custom active">
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
      <main class="col-md-10 main-content">
        <h3 class="page-title">Kelola Produk</h3>
        
        <button class="btn-add" onclick="document.getElementById('modalTambah').style.display='flex'">
          + Tambah Barang
        </button>

        <div class="table-container">
          <table class="custom-table">
            <thead>
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
                  <button class="btn-update" 
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

  <!-- Modal Tambah -->
  <div class="modal" id="modalTambah">
    <div class="modal-content">
      <h3>Tambah Barang</h3>
      <form method="POST">
        <input type="hidden" name="aksi" value="tambah">
        <div class="form-group">
          <label>ID Barang:</label>
          <input type="text" name="id_barang" required>
        </div>
        <div class="form-group">
          <label>Nama Barang:</label>
          <input type="text" name="nama_barang" required>
        </div>
        <div class="form-group">
          <label>Stok:</label>
          <input type="number" name="stok" required>
        </div>
        <div class="form-group">
          <label>Harga Barang:</label>
          <input type="number" name="harga_barang" step="0.01" required>
        </div>
        <button type="submit" class="btn-success">Simpan</button>
        <button type="button" class="btn-cancel" onclick="document.getElementById('modalTambah').style.display='none'">Batal</button>
      </form>
    </div>
  </div>

  <!-- Modal Edit -->
  <div class="modal" id="modalEdit">
    <div class="modal-content">
      <h3>Perbarui Barang</h3>
      <form method="POST">
        <input type="hidden" name="aksi" value="edit">
        <input type="hidden" name="id_barang" id="edit_id_barang">
        <div class="form-group">
          <label>Nama Barang:</label>
          <input type="text" name="nama_barang" id="edit_nama_barang" required>
        </div>
        <div class="form-group">
          <label>Stok:</label>
          <input type="number" name="stok" id="edit_stok" required>
        </div>
        <div class="form-group">
          <label>Harga Barang:</label>
          <input type="number" name="harga_barang" id="edit_harga_barang" step="0.01" required>
        </div>
        <button type="submit" class="btn-success">Simpan Perubahan</button>
        <button type="submit" name="hapus" value="1" class="btn-danger" onclick="return confirm('Yakin ingin menghapus barang ini?')">Hapus Barang</button>
        <button type="button" class="btn-cancel" onclick="document.getElementById('modalEdit').style.display='none'">Batal</button>
      </form>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    editor bay @vitamin
  </div>

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