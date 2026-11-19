<?php
session_start();
require_once "../koneksi.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION["keranjang"])) {
    $_SESSION["keranjang"] = [];
}

// Tambahkan menu ke keranjang
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["tambah"])) {
    $menu_id = (int)($_POST["menu_id"] ?? 0);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM menu WHERE id = ? AND status = 'tersedia'"
    );
    mysqli_stmt_bind_param($stmt, "i", $menu_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION["keranjang"][$menu_id] =
            ($_SESSION["keranjang"][$menu_id] ?? 0) + 1;
    }

    mysqli_stmt_close($stmt);

    header("Location: keranjang.php");
    exit;
}

// Hapus satu jenis menu dari keranjang
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["hapus"])) {
    $menu_id = (int)($_POST["menu_id"] ?? 0);
    unset($_SESSION["keranjang"][$menu_id]);

    header("Location: keranjang.php");
    exit;
}

// Perbarui jumlah menu
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["perbarui"])) {
    foreach (($_POST["jumlah"] ?? []) as $id => $jumlah) {
        $id = (int)$id;
        $jumlah = (int)$jumlah;

        if ($jumlah <= 0) {
            unset($_SESSION["keranjang"][$id]);
        } elseif (isset($_SESSION["keranjang"][$id])) {
            $_SESSION["keranjang"][$id] = min($jumlah, 99);
        }
    }

    header("Location: keranjang.php");
    exit;
}

// Ambil data menu dalam keranjang
$items = [];
$total = 0;

foreach ($_SESSION["keranjang"] as $id => $jumlah) {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, nama_menu, harga, gambar
         FROM menu
         WHERE id = ? AND status = 'tersedia'"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $item = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$item) {
        unset($_SESSION["keranjang"][$id]);
        continue;
    }

    $item["jumlah"] = (int)$jumlah;
    $item["subtotal"] = (float)$item["harga"] * (int)$jumlah;

    $total += $item["subtotal"];
    $items[] = $item;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang | RASA RAYA</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        body {
            background: #fffaf1;
        }

        .cart-container {
            max-width: 1050px;
            margin: 45px auto;
            padding: 0 25px 50px;
        }

        .cart-title {
            color: #183a2a;
            margin-bottom: 25px;
        }

        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 25px;
            align-items: start;
        }

        .cart-items,
        .cart-summary {
            padding: 25px;
            background: white;
            border-radius: 18px;
            box-shadow: 0 5px 20px rgba(0,0,0,.04);
        }

        .cart-item {
            display: grid;
            grid-template-columns: 90px 1fr;
            gap: 18px;
            padding: 18px 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item img {
            width: 90px;
            height: 90px;
            border-radius: 12px;
            object-fit: cover;
        }

        .cart-item h3 {
            color: #183a2a;
            font-size: 16px;
            margin-bottom: 7px;
        }

        .cart-price {
            color: #2e7d32;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .quantity-input {
            width: 65px;
            padding: 7px;
            border: 1px solid #ddd;
            border-radius: 7px;
        }

        .remove-btn {
            padding: 7px 10px;
            background: #fff0f0;
            color: #b42318;
            border: none;
            border-radius: 7px;
            cursor: pointer;
        }

        .update-btn {
            margin-top: 20px;
            padding: 10px 15px;
            background: #edf6e9;
            color: #183a2a;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }

        .cart-summary h2 {
            color: #183a2a;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin: 15px 0;
            font-size: 14px;
        }

        .summary-total {
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 18px;
            font-weight: bold;
            color: #183a2a;
        }

        .checkout-btn {
            display: block;
            width: 100%;
            margin-top: 20px;
            padding: 13px;
            border: none;
            border-radius: 10px;
            background: #2e7d32;
            color: white;
            text-align: center;
            font-weight: bold;
            cursor: pointer;
        }

        .continue-btn {
            display: inline-block;
            margin-top: 20px;
            color: #2e7d32;
            font-weight: bold;
        }

        .empty-cart {
            text-align: center;
            padding: 45px 15px;
        }

        .empty-cart p {
            margin-top: 10px;
            color: #758078;
        }

        @media (max-width: 750px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<header class="navbar">
    <a href="../index.php" class="logo">RASA<span>RAYA.</span></a>

    <nav class="nav-menu">
        <a href="menu.php">Menu</a>
        <a href="keranjang.php">Keranjang</a>
        <a href="pesanan.php">Pesanan Saya</a>
    </nav>

    <div class="nav-actions">
        <span><?= htmlspecialchars($_SESSION["nama"]) ?></span>
        <a href="../logout.php" class="login-btn">Keluar</a>
    </div>
</header>

<main class="cart-container">
    <h1 class="cart-title">Keranjang Belanja 🛒</h1>

    <?php if (count($items) > 0): ?>

        <div class="cart-layout">
            <section class="cart-items">

                <form method="POST" action="keranjang.php">
                    <?php foreach ($items as $item): ?>
                        <div class="cart-item">

                            <img
                                src="<?= !empty($item["gambar"])
                                    ? "../assets/img/" . htmlspecialchars($item["gambar"])
                                    : "https://placehold.co/200x200/edf6e9/183a2a?text=Menu" ?>"
                                alt="<?= htmlspecialchars($item["nama_menu"]) ?>"
                            >

                            <div>
                                <h3><?= htmlspecialchars($item["nama_menu"]) ?></h3>

                                <p class="cart-price">
                                    Rp<?= number_format($item["harga"], 0, ",", ".") ?>
                                </p>

                                <label>
                                    Jumlah:
                                    <input
                                        class="quantity-input"
                                        type="number"
                                        name="jumlah[<?= (int)$item["id"] ?>]"
                                        value="<?= (int)$item["jumlah"] ?>"
                                        min="0"
                                        max="99"
                                    >
                                </label>

                                <p style="margin-top:10px;">
                                    Subtotal:
                                    <strong>
                                        Rp<?= number_format($item["subtotal"], 0, ",", ".") ?>
                                    </strong>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <button
                        class="update-btn"
                        type="submit"
                        name="perbarui"
                    >
                        Perbarui Keranjang
                    </button>
                </form>

                <?php foreach ($items as $item): ?>
                    <form method="POST" action="keranjang.php" style="margin-top:8px;">
                        <input
                            type="hidden"
                            name="menu_id"
                            value="<?= (int)$item["id"] ?>"
                        >
                        <button class="remove-btn" type="submit" name="hapus">
                            Hapus <?= htmlspecialchars($item["nama_menu"]) ?>
                        </button>
                    </form>
                <?php endforeach; ?>

                <a href="menu.php" class="continue-btn">← Tambah menu lainnya</a>
            </section>

            <aside class="cart-summary">
                <h2>Ringkasan Pesanan</h2>

                <div class="summary-row">
                    <span>Total item</span>
                    <strong><?= array_sum(array_column($items, "jumlah")) ?></strong>
                </div>

                <div class="summary-row summary-total">
                    <span>Total harga</span>
                    <span>Rp<?= number_format($total, 0, ",", ".") ?></span>
                </div>

                <a href="checkout.php" class="checkout-btn">
                    Lanjut Checkout →
                </a>
            </aside>
        </div>

    <?php else: ?>

        <section class="cart-items empty-cart">
            <h2>Keranjangmu masih kosong 🍽️</h2>
            <p>Yuk, pilih makanan favoritmu terlebih dahulu.</p>
            <a href="menu.php" class="checkout-btn">Lihat Menu</a>
        </section>

    <?php endif; ?>
</main>

<footer class="footer">
    <a href="../index.php" class="logo">RASA<span>RAYA.</span></a>
    <p>Temukan rasa favoritmu, nikmati setiap momen.</p>
</footer>

</body>
</html>
