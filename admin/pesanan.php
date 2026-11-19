<?php
session_start();
require_once "../koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';

    $status_valid = ['menunggu', 'diproses', 'selesai', 'dibatalkan'];

    if ($id && in_array($status, $status_valid, true)) {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE pesanan
 SET status = ?, status_diperbarui = NOW()
 WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "si", $status, $id);

        if (mysqli_stmt_execute($stmt)) {
            $pesan = "Status pesanan berhasil diperbarui.";
        } else {
            $pesan = "Gagal memperbarui status pesanan.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $pesan = "Data yang dimasukkan tidak valid.";
    }
}

$query = mysqli_query(
    $conn,
    "SELECT p.id, p.nomor_pesanan, p.tipe_pesanan,
            p.nomor_meja, p.total_harga, p.status, p.created_at,
            u.nama, u.email
     FROM pesanan p
     JOIN users u ON p.user_id = u.id
     ORDER BY p.created_at DESC"
);

if (!$query) {
    die("Gagal mengambil data pesanan. Periksa struktur tabel database.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - RASA RAYA</title>

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
            padding: 20px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .notice {
            background: #dff5e6;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .table-wrapper {
            background: white;
            border-radius: 12px;
            padding: 18px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th, td {
            text-align: left;
            padding: 14px 10px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        th {
            background: #245c43;
            color: white;
        }

        select, button {
            padding: 9px;
            border-radius: 7px;
            border: 1px solid #ccc;
        }

        button {
            background: #f39a35;
            border: none;
            cursor: pointer;
            font-weight: bold;
            margin-top: 7px;
        }

        .empty {
            text-align: center;
            padding: 30px;
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
    <h1>Kelola Pesanan</h1>
    <p>Lihat pesanan pelanggan dan perbarui statusnya.</p>

    <?php if ($pesan !== ""): ?>
        <div class="notice">
            <?= htmlspecialchars($pesan) ?>
        </div>
    <?php endif; ?>

    <div class="table-wrapper">
        <?php if (mysqli_num_rows($query) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nomor Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Jenis Pesanan</th>
                        <th>Total</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $no = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><?= $no++ ?></td>

                            <td>
                                <?= htmlspecialchars($row['nomor_pesanan']) ?>
                            </td>

                            <td>
                                <strong><?= htmlspecialchars($row['nama']) ?></strong><br>
                                <small><?= htmlspecialchars($row['email']) ?></small>
                            </td>

                            <td>
                                <?= $row['tipe_pesanan'] === 'dine-in'
                                    ? 'Dine-in (Meja ' .
                                        htmlspecialchars($row['nomor_meja'] ?? '-') . ')'
                                    : 'Takeaway' ?>
                            </td>

                            <td>
                                Rp<?= number_format((float)$row['total_harga'], 0, ',', '.') ?>
                            </td>

                            <td>
                                <?= date('d-m-Y H:i', strtotime($row['created_at'])) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(ucfirst($row['status'])) ?>
                            </td>

                            <td>
                                <form method="POST">
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$row['id'] ?>"
                                    >

                                    <select name="status" required>
                                        <?php
                                        $daftar_status = [
                                            'menunggu' => 'Menunggu',
                                            'diproses' => 'Diproses',
                                            'selesai' => 'Selesai',
                                            'dibatalkan' => 'Dibatalkan'
                                        ];
                                        ?>

                                        <?php foreach ($daftar_status as $nilai => $label): ?>
                                            <option
                                                value="<?= $nilai ?>"
                                                <?= $row['status'] === $nilai ? 'selected' : '' ?>
                                            >
                                                <?= $label ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <button type="submit">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        <?php else: ?>
            <div class="empty">
                <h3>Belum Ada Pesanan</h3>
                <p>Pesanan pelanggan akan muncul di halaman ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>