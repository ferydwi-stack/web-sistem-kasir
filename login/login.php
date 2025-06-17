<?php
session_start();
include '../db.php'; // Pastikan file ini menginisialisasi $conn = new mysqli(...);

$error = "";

// Jika sudah login, langsung arahkan ke dashboard sesuai role
if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard_admin.php");
    } elseif ($_SESSION['role'] === 'kasir') {
        header("Location: ../kasir/kelola_produk_kasir.php");
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);

    // Cek akun admin
    $stmt_admin = $conn->prepare("SELECT * FROM admin WHERE username=?");
    $stmt_admin->bind_param("s", $username);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($result_admin->num_rows === 1) {
        $admin = $result_admin->fetch_assoc();

        // Cek apakah password sudah di-hash
        if (password_verify($password, $admin['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'admin';
            header("Location: ../admin/dashboard_admin.php");
            exit;
        }

        // Jika password belum di-hash dan cocok persis, izinkan login (fallback)
        if ($password === $admin['password']) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'admin';
            header("Location: ../admin/dashboard_admin.php");
            exit;
        }
    }

    // Cek akun kasir
    $stmt_kasir = $conn->prepare("SELECT * FROM kasir WHERE username=?");
    $stmt_kasir->bind_param("s", $username);
    $stmt_kasir->execute();
    $result_kasir = $stmt_kasir->get_result();

    if ($result_kasir->num_rows === 1) {
        $kasir = $result_kasir->fetch_assoc();

        if (password_verify($password, $kasir['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'kasir';
            $_SESSION['nama'] = $kasir['nama'];
            $_SESSION['tanggal_masuk'] = $kasir['tanggal_masuk'];

            // Update status menjadi online
            $update_status = $conn->prepare("UPDATE kasir SET status='online' WHERE username=?");
            $update_status->bind_param("s", $username);
            $update_status->execute();

            header("Location: ../kasir/kelola_produk_kasir.php");
            exit;
        }

        // Fallback untuk password belum di-hash
        if ($password === $kasir['password']) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'kasir';
            $_SESSION['nama'] = $kasir['nama'];
            $_SESSION['tanggal_masuk'] = $kasir['tanggal_masuk'];

            $update_status = $conn->prepare("UPDATE kasir SET status='online' WHERE username=?");
            $update_status->bind_param("s", $username);
            $update_status->execute();

            header("Location: ../kasir/kelola_produk_kasir.php");
            exit;
        }
    }

    // Jika semua gagal
    $error = "Username atau password salah.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Page</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=MedievalSharp&display=swap');

    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(180deg, #3f4748 0%, #8ca6b0 100%);
      box-sizing: border-box;
    }

    .container {
      display: flex;
      border-radius: 0.5rem;
      overflow: hidden;
      width: 100%;
      max-width: 600px;
      background-color: #8ca6b0;
      height: 320px;
    }

    .left-img img {
      height: 100%;
      width: 160px;
      object-fit: cover;
    }

    .form-container {
      flex: 1;
      padding: 2rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      box-sizing: border-box;
      height: 100%;
    }

    .form-container img {
      width: 50px;
      height: 45px;
      background-color: white;
      border-radius: 999px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      margin-bottom: 0.55rem;
      object-fit: scale-down;
    }

    .form-container p {
      margin-bottom: 1rem;
      font-size: 0.875rem;
      color: #000;
    }

    .form-container form {
      width: 100%;
      max-width: 240px;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .input-group {
      position: relative;
    }

    .input-group i {
      position: absolute;
      left: 8px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 0.75rem;
      color: #000;
    }

    .input-group input {
      width: 100%;
      padding: 0.4rem 0.5rem 0.4rem 1.75rem;
      font-size: 0.75rem;
      border: 1px solid #000;
      border-radius: 0.2rem;
      background-color: rgba(140, 166, 176, 0.7);
      color: #000;
      box-sizing: border-box;
    }

    .form-container button {
      width: 100%;
      padding: 0.4rem;
      font-size: 0.75rem;
      background-color: #6f7f85;
      color: #000;
      border: none;
      border-radius: 0.2rem;
      cursor: pointer;
    }

    .error-message {
      background-color: #fee;
      color: #c00;
      font-size: 0.75rem;
      padding: 0.5rem;
      border: 1px solid #c00;
      border-radius: 0.2rem;
      margin-bottom: 0.5rem;
      text-align: center;
      width: 100%;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="left-img">
      <img src="../logo/logothrif.jpg" alt="Login Image">
    </div>
    <div class="form-container">
      <img src="../logo/logo_login.jpg" alt="Logo" width="100" height="50"/>
      <p>LOGIN</p>

      <?php if (!empty($error)): ?>
        <div class="error-message"><?= $error ?></div>
      <?php endif; ?>

      <form method="post">
        <div class="input-group">
          <i class="fas fa-user"></i>
          <input type="text" name="username" placeholder="username" required />
        </div>
        <div class="input-group">
          <i class="fas fa-lock"></i>
          <input type="password" name="password" placeholder="password" required />
        </div>
        <button type="submit">LOGIN</button>
      </form>
    </div>
  </div>
</body>
</html>
