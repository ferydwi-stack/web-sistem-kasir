<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login/login.php');
    exit;
}

// Tambah kasir
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kasir'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $tanggal_masuk = date('Y-m-d');
    $status = 'baru'; // Define status variable

    $cek = mysqli_query($conn, "SELECT * FROM kasir WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "❌ Username sudah digunakan!";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO kasir (username, password, nama, tanggal_masuk, status)
                        VALUES ('$username', '$password', '$nama', '$tanggal_masuk', '$status')");
        if ($insert) {
            header("Location: kelola_akun.php");
            exit;
        } else {
            $pesan = "❌ Gagal menambahkan kasir.";
        }
    }
}

// Proses Update Kasir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_kasir'])) {
    $username_lama = mysqli_real_escape_string($conn, $_POST['username_lama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $update = mysqli_query($conn, "UPDATE kasir SET username='$username', password='$password', nama='$nama' WHERE username='$username_lama'");

    if ($update) {
        header("Location: kelola_akun.php");
        exit;
    } else {
        $pesan = "❌ Gagal memperbarui kasir.";
    }
}

// Proses Hapus Kasir dari Modal (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_kasir'])) {
    $hapus_user = mysqli_real_escape_string($conn, $_POST['username_hapus']);
    mysqli_query($conn, "DELETE FROM kasir WHERE username='$hapus_user'");
    header("Location: kelola_akun.php");
    exit;
}

$query = "SELECT * FROM kasir";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Kelola Akun</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/kelola_akun.css">
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
        <a href="kelola_produk_admin.php" class="nav-link-custom">
          <img src="../icon/icon_produk.png" class="icon-img"> Kelola Produk
        </a>
        <a href="laporan_penjualan.php" class="nav-link-custom">
          <img src="../icon/icon_laporan.png" class="icon-img"> Laporan Penjualan
        </a>
        <a href="#" class="nav-link-custom active">
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
        <h3 class="page-title">Data Kasir</h3>
        
        <button class="btn-add" onclick="document.getElementById('modalTambah').style.display='block'">
          + Tambah Kasir
        </button>

        <?php if ($pesan != ''): ?>
          <div class="error-message"><?= $pesan ?></div>
        <?php endif; ?>

        <div class="table-container">
          <table class="custom-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Username</th>
                <th>Kata Sandi</th>
                <th>Nama</th>
                <th>Tanggal Masuk</th>
                <th>Role</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= htmlspecialchars($row['username']) ?></td>
                  <td><?= htmlspecialchars($row['password']) ?></td>
                  <td><?= htmlspecialchars($row['nama']) ?></td>
                  <td><?= $row['tanggal_masuk'] ?></td>
                  <td>kasir</td>
                  <td><?= htmlspecialchars($row['status']) ?></td>
                  <td>
                    <button class="btn-edit" onclick='bukaModalEdit(`<?= htmlspecialchars($row["username"]) ?>`, `<?= htmlspecialchars($row["password"]) ?>`, `<?= htmlspecialchars($row["nama"]) ?>`, `<?= htmlspecialchars($row["status"]) ?>`)'>
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

  <!-- Modal Tambah Kasir -->
  <div id="modalTambah" class="modal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('modalTambah').style.display='none'">&times;</span>
      <h3>Tambah Kasir</h3>
      <form method="POST">
        <input type="hidden" name="tambah_kasir" value="1">
        <div class="form-group">
          <label>Username:</label>
          <input type="text" name="username" required>
        </div>
        <div class="form-group">
          <label>Password:</label>
          <input type="password" name="password" required>
        </div>
        <div class="form-group">
          <label>Nama Lengkap:</label>
          <input type="text" name="nama" required>
        </div>
        <button type="submit" class="btn-submit">Simpan</button>
      </form>
    </div>
  </div>

  <!-- Modal Edit Kasir -->
  <div id="modalEdit" class="modal">
    <div class="modal-content">
      <span class="close" onclick="document.getElementById('modalEdit').style.display='none'">&times;</span>
      <h3>Edit Kasir</h3>
      <form method="POST">
        <input type="hidden" name="edit_kasir" value="1">
        <input type="hidden" name="username_lama" id="edit_username_lama">

        <div class="form-group">
          <label>Username:</label>
          <input type="text" name="username" id="edit_username" required>
        </div>
        <div class="form-group">
          <label>Password:</label>
          <input type="text" name="password" id="edit_password" required>
        </div>
        <div class="form-group">
          <label>Nama Lengkap:</label>
          <input type="text" name="nama" id="edit_nama" required>
        </div>
        <button type="submit" class="btn-submit">Simpan Perubahan</button>
      </form>

      <!-- Tombol Hapus Kasir -->
      <form method="POST" onsubmit="return confirm('Yakin ingin menghapus kasir ini?')">
        <input type="hidden" name="hapus_kasir" value="1">
        <input type="hidden" name="username_hapus" id="hapus_username">
        <button type="submit" class="btn-delete">
          Hapus Kasir
        </button>
      </form>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    editor bay @vitamin
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function bukaModalEdit(username, password, nama, status) {
      document.getElementById('modalEdit').style.display = 'block';
      document.getElementById('edit_username').value = username;
      document.getElementById('edit_password').value = password;
      document.getElementById('edit_nama').value = nama;
      document.getElementById('edit_username_lama').value = username;
      document.getElementById('hapus_username').value = username;
    }

    window.onclick = function(event) {
      const modalTambah = document.getElementById('modalTambah');
      const modalEdit = document.getElementById('modalEdit');
      if (event.target == modalTambah) modalTambah.style.display = "none";
      if (event.target == modalEdit) modalEdit.style.display = "none";
    }
  </script>

</body>
</html>