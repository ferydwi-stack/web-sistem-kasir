<?php
session_start();
include '../db.php'; // tambahkan koneksi database

if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    $username = $_SESSION['username'];
    $role = $_SESSION['role'];

    // Jika kasir, ubah status jadi offline
    if ($role === 'kasir') {
        $stmt = $conn->prepare("UPDATE kasir SET status='offline' WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
    }
}

session_unset(); // Hapus semua variabel session
session_destroy(); // Hancurkan session

header("Location: ../login/login.php"); // Arahkan ke halaman login
exit;
