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
      <div class="col-md-2 d-flex flex-column" style="background-color:#343a40; min-height:100vh; padding-top:1rem;">
        <h5 class="text-center text-warning mb-3">Admin</h5>
        <hr class="border-secondary">
        <a href="dashboard_admin.php" class="btn btn-outline-light mb-2 d-flex align-items-center">
          <img src="../icon/icon_dashboard.png" class="me-2" style="height:24px;"> Dashboard
        </a>
        <a href="kelola_produk_admin.php" class="btn btn-outline-light mb-2 d-flex align-items-center">
          <img src="../icon/icon_produk.png" class="me-2" style="height:24px;"> Kelola Produk
        </a>
        <a href="laporan_penjualan.php" class="btn btn-outline-light mb-2 d-flex align-items-center">
          <img src="../icon/icon_laporan.png" class="me-2" style="height:24px;"> Laporan Penjualan
        </a>
        <a href="#" class="btn btn-light mb-2 d-flex align-items-center active">
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
        <h3 class="mb-4 text-warning">Data Kasir</h3>
        <button class="btn btn-primary mb-3" onclick="document.getElementById('modalTambah').style.display='flex'">
          + Tambah Kasir
        </button>
        <?php if ($pesan != ''): ?>
          <div class="alert alert-danger mb-3"><?= $pesan ?></div>
        <?php endif; ?>
        <div class="table-responsive">
          <table class="table table-bordered table-dark mb-0">
            <thead class="table-secondary text-dark">
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
                    <button class="btn btn-warning btn-sm" onclick='bukaModalEdit(`<?= htmlspecialchars($row["username"]) ?>`, `<?= htmlspecialchars($row["password"]) ?>`, `<?= htmlspecialchars($row["nama"]) ?>`, `<?= htmlspecialchars($row["status"]) ?>`)'>
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

  <!-- Modal Tambah & Edit -->
  <div id="modalTambah" class="modal" style="display:none; align-items:center; justify-content:center; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:1050;">
    <div class="bg-white rounded shadow p-4 border border-dark" style="min-width:300px; max-width:400px; margin:auto;">
      <span class="float-end" style="cursor:pointer; font-size:1.5rem;" onclick="document.getElementById('modalTambah').style.display='none'">&times;</span>
      <h4 class="mb-3">Tambah Kasir</h4>
      <form method="POST">
        <input type="hidden" name="tambah_kasir" value="1">
        <div class="mb-3">
          <label class="form-label">Username:</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password:</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Nama Lengkap:</label>
          <input type="text" name="nama" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Simpan</button>
      </form>
    </div>
  </div>

  <div id="modalEdit" class="modal" style="display:none; align-items:center; justify-content:center; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:1050;">
    <div class="bg-white rounded shadow p-4 border border-dark" style="min-width:300px; max-width:400px; margin:auto;">
      <span class="float-end" style="cursor:pointer; font-size:1.5rem;" onclick="document.getElementById('modalEdit').style.display='none'">&times;</span>
      <h4 class="mb-3">Edit Kasir</h4>
      <form method="POST">
        <input type="hidden" name="edit_kasir" value="1">
        <input type="hidden" name="username_lama" id="edit_username_lama">
        <div class="mb-3">
          <label class="form-label">Username:</label>
          <input type="text" name="username" id="edit_username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password:</label>
          <input type="text" name="password" id="edit_password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Nama Lengkap:</label>
          <input type="text" name="nama" id="edit_nama" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Simpan Perubahan</button>
      </form>
      <form method="POST" onsubmit="return confirm('Yakin ingin menghapus kasir ini?')">
        <input type="hidden" name="hapus_kasir" value="1">
        <input type="hidden" name="username_hapus" id="hapus_username">
        <button type="submit" class="btn btn-danger w-100 mt-2">Hapus Kasir</button>
      </form>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-center py-3 mt-4 text-light border-top border-secondary">
    editor bay @vitamin
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function bukaModalEdit(username, password, nama, status) {
      document.getElementById('modalEdit').style.display = 'flex';
      document.getElementById('edit_username').value = username;
      document.getElementById('edit_password').value = password;
      document.getElementById('edit_nama').value = nama;
      document.getElementById('edit_username_lama').value = username;
      document.getElementById('hapus_username').value = username;
    }

    window.onclick = function(event) {
      if(event.target.classList.contains('modal')){
        event.target.style.display = "none";
      }
    }
  </script>

</body>

</html>