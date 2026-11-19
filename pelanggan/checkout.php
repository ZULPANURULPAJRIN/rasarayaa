<?php
session_start();
require_once "../koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION['keranjang']) || empty($_SESSION['keranjang'])) {
    header("Location: keranjang.php");
    exit;
}

$keranjang = $_SESSION['keranjang'];
$daftar_menu = [];
$total = 0;

$ids = array_map('intval', array_keys($keranjang));
$id_list = implode(',', $ids);

$query = mysqli_query(
    $conn,
    "SELECT id, nama_menu, harga, status
     FROM menu
     WHERE id IN ($id_list)"
);

while ($item = mysqli_fetch_assoc($query)) {
    $jumlah = (int) $keranjang[$item['id']];

    if ($item['status'] !== 'tersedia' || $jumlah < 1) {
        continue;
    }

    $item['jumlah'] = $jumlah;
    $item['subtotal'] = (float) $item['harga'] * $jumlah;
    $daftar_menu[] = $item;
    $total += $item['subtotal'];
}

if (empty($daftar_menu)) {
    header("Location: keranjang.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipe = $_POST['tipe_pesanan'] ?? '';
    $nomor_meja = trim($_POST['nomor_meja'] ?? '');
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? '';

    if (!in_array($tipe, ['dine-in', 'takeaway'], true)) {
        $error = "Silakan pilih jenis pesanan.";
    } elseif (!in_array($metode_pembayaran, ['cash', 'qris'], true)) {
        $error = "Silakan pilih metode pembayaran.";
    } elseif ($tipe === 'dine-in' && $nomor_meja === '') {
        $error = "Nomor meja wajib diisi untuk pesanan dine-in.";
    } else {
        $nomor_pesanan = 'RR-' . date('YmdHis') . '-' . random_int(100, 999);
        $user_id = (int) $_SESSION['user_id'];

        mysqli_begin_transaction($conn);

        try {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO pesanan
                (user_id, nomor_pesanan, tipe_pesanan, nomor_meja,
                 total_harga, status, metode_pembayaran)
                VALUES (?, ?, ?, ?, ?, 'menunggu', ?)"
            );

            $meja_db = $tipe === 'dine-in' ? $nomor_meja : null;

            mysqli_stmt_bind_param(
                $stmt,
                "isssds",
                $user_id,
                $nomor_pesanan,
                $tipe,
                $meja_db,
                $total,
                $metode_pembayaran
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Pesanan gagal disimpan.");
            }

            $pesanan_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);

            $detail_stmt = mysqli_prepare(
                $conn,
                "INSERT INTO detail_pesanan
                (pesanan_id, menu_id, jumlah, harga, subtotal)
                VALUES (?, ?, ?, ?, ?)"
            );

            foreach ($daftar_menu as $item) {
                $menu_id = (int) $item['id'];
                $jumlah = (int) $item['jumlah'];
                $harga = (float) $item['harga'];
                $subtotal = (float) $item['subtotal'];

                mysqli_stmt_bind_param(
                    $detail_stmt,
                    "iiidd",
                    $pesanan_id,
                    $menu_id,
                    $jumlah,
                    $harga,
                    $subtotal
                );

                if (!mysqli_stmt_execute($detail_stmt)) {
                    throw new Exception("Detail pesanan gagal disimpan.");
                }
            }

            mysqli_stmt_close($detail_stmt);
            mysqli_commit($conn);

            $_SESSION['keranjang'] = [];
            $_SESSION['pesanan_terakhir'] = $nomor_pesanan;

            header("Location: pesanan.php?berhasil=1");
            exit;

        } catch (Throwable $e) {
            mysqli_rollback($conn);
            $error = "Pesanan gagal disimpan. Periksa struktur tabel database dan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - RASA RAYA</title>
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
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav a { color: white; text-decoration: none; margin-left: 18px; }
        .container {
            max-width: 760px;
            margin: 35px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px #0000000d;
        }
        h1, h2 { margin-top: 0; }
        .item {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .total {
            display: flex;
            justify-content: space-between;
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
        }
        label {
            display: block;
            margin: 15px 0 8px;
            font-weight: bold;
        }
        select, input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }
        button {
            width: 100%;
            margin-top: 22px;
            padding: 14px;
            background: #f39a35;
            color: #253c2e;
            border: none;
            border-radius: 9px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .error {
            padding: 12px;
            border-radius: 8px;
            background: #ffe6e6;
            color: #a11;
        }
        .muted { color: #777; font-size: 14px; }
    </style>
</head>
<body>
<nav>
    <strong>🍽️ RASA RAYA</strong>
    <div>
        <a href="menu.php">Menu</a>
        <a href="keranjang.php">Keranjang</a>
        <a href="../logout.php">Keluar</a>
    </div>
</nav>

<div class="container">
    <h1>Checkout Pesanan</h1>
    <p>Periksa pesananmu sebelum mengirimkannya.</p>

    <div class="card">
        <h2>Ringkasan Pesanan</h2>

        <?php foreach ($daftar_menu as $item): ?>
            <div class="item">
                <div>
                    <strong><?= htmlspecialchars($item['nama_menu']) ?></strong>
                    <div class="muted">
                        <?= $item['jumlah'] ?> × Rp<?= number_format($item['harga'], 0, ',', '.') ?>
                    </div>
                </div>
                <strong>Rp<?= number_format($item['subtotal'], 0, ',', '.') ?></strong>
            </div>
        <?php endforeach; ?>

        <div class="total">
            <span>Total</span>
            <span>Rp<?= number_format($total, 0, ',', '.') ?></span>
        </div>
    </div>

    <div class="card">
        <h2>Jenis Pesanan</h2>

        <?php if ($error !== ''): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="tipe_pesanan">Pilih jenis pesanan</label>
            <select name="tipe_pesanan" id="tipe_pesanan" required>
                <option value="">-- Pilih --</option>
                <option value="dine-in">Dine-in (Makan di Tempat)</option>
                <option value="takeaway">Takeaway (Dibawa Pulang)</option>
            </select>

            <div id="meja-wrapper" style="display:none;">
                <label for="nomor_meja">Nomor Meja</label>
                <input
                    type="text"
                    name="nomor_meja"
                    id="nomor_meja"
                    placeholder="Contoh: Meja 05"
                >
            </div>
<label for="metode_pembayaran">Metode Pembayaran</label>

<select name="metode_pembayaran" id="metode_pembayaran" required>
    <option value="">-- Pilih Pembayaran --</option>
    <option value="cash">Bayar di Kasir</option>

</select>


            <button type="submit">Buat Pesanan</button>
        </form>
    </div>
</div>

<script>
const tipePesanan = document.getElementById('tipe_pesanan');
const mejaWrapper = document.getElementById('meja-wrapper');
const nomorMeja = document.getElementById('nomor_meja');

function aturNomorMeja() {
    const dineIn = tipePesanan.value === 'dine-in';
    mejaWrapper.style.display = dineIn ? 'block' : 'none';
    nomorMeja.required = dineIn;

    if (!dineIn) {
        nomorMeja.value = '';
    }
}

tipePesanan.addEventListener('change', aturNomorMeja);
aturNomorMeja();
</script>
</body>
</html>