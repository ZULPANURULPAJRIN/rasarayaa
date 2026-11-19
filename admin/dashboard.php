<?php
session_start();
require_once "../koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$query_total = mysqli_query($conn, "SELECT COUNT(*) AS jumlah FROM pesanan");
$data_total = mysqli_fetch_assoc($query_total);

$query_proses = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS jumlah FROM pesanan
     WHERE status IN ('menunggu', 'diproses')"
);
$data_proses = mysqli_fetch_assoc($query_proses);

$query_pendapatan = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(total_harga), 0) AS total
     FROM pesanan
     WHERE status = 'selesai'"
);
$data_pendapatan = mysqli_fetch_assoc($query_pendapatan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - RASA RAYA</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8f7ef;
            color: #253c2e;
        }

        nav {
            background: #245c43;
            color: white;
            padding: 20px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 18px;
        }

        .container {
            max-width: 1100px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px #0000000d;
        }

        .card h3 {
            color: #68766d;
            font-size: 15px;
        }

        .number {
            font-size: 28px;
            font-weight: bold;
            margin: 12px 0 0;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 30px;
        }

        .button {
            display: inline-block;
            background: #f39a35;
            color: #253c2e;
            text-decoration: none;
            padding: 14px 20px;
            border-radius: 9px;
            font-weight: bold;
        }

        .button.secondary {
            background: #245c43;
            color: white;
        }
    </style>
</head>

<body>

<nav>
    <strong>🍽️ RASA RAYA | ADMIN</strong>

    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="pesanan.php">Kelola Pesanan</a>
        <a href="menu.php">Kelola Menu</a>
        <a href="../logout.php">Keluar</a>
    </div>
</nav>

<div class="container">
    <h1>Dashboard Admin</h1>
    <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?>!</p>

    <div class="cards">
        <div class="card">
            <h3>Total Pesanan</h3>
            <p class="number"><?= (int)$data_total['jumlah'] ?></p>
        </div>

        <div class="card">
            <h3>Pesanan Belum Selesai</h3>
            <p class="number"><?= (int)$data_proses['jumlah'] ?></p>
        </div>

        <div class="card">
            <h3>Total Pendapatan</h3>
            <p class="number">
                Rp<?= number_format((float)$data_pendapatan['total'], 0, ',', '.') ?>
            </p>
            <small>Hanya dari pesanan berstatus selesai</small>
        </div>
    </div>

    <div class="actions">
        <a href="pesanan.php" class="button">Kelola Pesanan</a>
        <a href="menu.php" class="button secondary">Kelola Menu</a>
    </div>
</div>

</body>
</html>