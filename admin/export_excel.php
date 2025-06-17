<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login/login.php');
    exit;
}

// Ambil data filter
$periode = $_GET['periode'] ?? 'hari';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$cariBarang = $_GET['cari_barang'] ?? '';
$cariTransaksi = $_GET['cari_transaksi'] ?? '';

// Format nama file berdasarkan filter
$namaFile = "laporan_penjualan";

if (!empty($periode)) {
    if ($periode == 'hari') {
        $namaFile .= "_harian_" . date('d-m-Y', strtotime($tanggal));
    } elseif ($periode == 'bulan') {
        $namaFile .= "_bulanan_" . date('F_Y', strtotime($tanggal));
    } elseif ($periode == 'tahun') {
        $namaFile .= "_tahunan_" . date('Y', strtotime($tanggal));
    }
}

if (!empty($cariBarang)) {
    $namaFile .= "_barang_" . preg_replace('/\s+/', '_', $cariBarang);
}

if (!empty($cariTransaksi)) {
    $namaFile .= "_trx_" . $cariTransaksi;
}

$namaFile .= ".xls";

// Header untuk Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=$namaFile");

// Query filter
$where = [];

if (!empty($cariTransaksi)) {
    $cariTransaksi = mysqli_real_escape_string($conn, $cariTransaksi);
    $where[] = "t.id_transaksi LIKE '%$cariTransaksi%'";
} else {
    if ($periode && $tanggal) {
        $tanggalObj = date('Y-m-d', strtotime($tanggal));
        if ($periode == 'hari') {
            $where[] = "t.tanggal = '$tanggalObj'";
        } elseif ($periode == 'bulan') {
            $bulan = date('m', strtotime($tanggal));
            $tahun = date('Y', strtotime($tanggal));
            $where[] = "MONTH(t.tanggal) = '$bulan'";
            $where[] = "YEAR(t.tanggal) = '$tahun'";
        } elseif ($periode == 'tahun') {
            $tahun = date('Y', strtotime($tanggal));
            $where[] = "YEAR(t.tanggal) = '$tahun'";
        }
    }
}

if (!empty($cariBarang)) {
    $cariBarang = mysqli_real_escape_string($conn, $cariBarang);
    $where[] = "b.nama_barang LIKE '%$cariBarang%'";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Ambil data transaksi
$query = "
SELECT 
    t.id_transaksi,
    t.tanggal,
    k.nama AS nama_kasir,
    b.nama_barang,
    t.jumlah,
    t.harga_satuan AS harga,
    (t.jumlah * t.harga_satuan) AS total
FROM transaksi t
JOIN kasir k ON t.username_kasir = k.username
JOIN barang b ON t.id_barang = b.id_barang
$whereClause
ORDER BY t.tanggal DESC
";

$result = mysqli_query($conn, $query);
?>

<!-- Tampilan Excel -->
<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;">
    <thead>
        <tr style="background-color:#c0d6e4; font-weight:bold; text-align:center;">
            <th colspan="7" style="font-size:16px; padding:10px;">LAPORAN PENJUALAN</th>
        </tr>
        <tr style="background-color:#e2f0fb; font-weight:bold; text-align:center;">
            <th>KASIR</th>
            <th>NO TRANSAKSI</th>
            <th>BARANG</th>
            <th>JUMLAH</th>
            <th>HARGA</th>
            <th>TOTAL</th>
            <th>TANGGAL</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['nama_kasir'] ?></td>
                    <td><?= $row['id_transaksi'] ?></td>
                    <td><?= $row['nama_barang'] ?></td>
                    <td><?= $row['jumlah'] ?></td>
                    <td>Rp. <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td>Rp. <?= number_format($row['total'], 0, ',', '.') ?></td>
                    <td><?= $row['tanggal'] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align:center;">Tidak ada data transaksi.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
