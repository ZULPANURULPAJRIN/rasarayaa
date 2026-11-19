<?php
session_start();
require_once "../koneksi.php";

// Pastikan pelanggan sudah login
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

// Ambil daftar menu dari database
$sql = "SELECT * FROM menu WHERE status = 'tersedia' ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Makanan | RASA RAYA</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .food-bottom form {
    margin: 0;
}

.add-btn {
    display: flex;
    width: 38px;
    height: 38px;
    justify-content: center;
    align-items: center;
    border: none;
    border-radius: 10px;
    background: #2e7d32;
    color: white;
    font-size: 24px;
    cursor: pointer;
}
        .menu-header {
            padding: 45px 7%;
            background: #edf6e9;
        }

        .menu-header h1 {
            color: #183a2a;
            font-size: 36px;
            margin-bottom: 10px;
        }

        .menu-header p {
            color: #758078;
        }

        .welcome {
            color: #2e7d32;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .menu-container {
            padding: 40px 7% 70px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 25px;
        }

        .food-card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #eee;
            box-shadow: 0 5px 20px rgba(0,0,0,.04);
        }

        .food-card img {
            width: 100%;
            height: 210px;
            object-fit: cover;
            display: block;
        }

        .food-info {
            padding: 20px;
        }

        .food-info h3 {
            color: #183a2a;
            margin-bottom: 8px;
        }

        .food-info p {
            color: #758078;
            font-size: 13px;
            line-height: 1.6;
        }

        .food-category {
            display: inline-block;
            color: #2e7d32;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .food-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 18px;
        }

        .food-price {
            color: #183a2a;
            font-size: 17px;
            font-weight: bold;
        }

        .empty-menu {
            padding: 35px;
            background: white;
            border-radius: 15px;
            color: #758078;
            text-align: center;
        }

        .logout-btn {
            color: #c0392b;
            font-weight: bold;
        }

        @media (max-width: 800px) {
            .menu-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 550px) {
            .menu-grid {
                grid-template-columns: 1fr;
            }

            .menu-header h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>

<header class="navbar">
    <a href="../index.php" class="logo">RASA<span>RAYA.</span></a>

    <nav class="nav-menu">
        <a href="menu.php">Beranda</a>
        <a href="#daftar-menu">Menu</a>
        <a href="keranjang.php">Keranjang</a>
        <a href="pesanan.php">Pesanan Saya</a>
    </nav>

    <div class="nav-actions">
        <span><?= htmlspecialchars($_SESSION["nama"]) ?></span>
        <a href="../logout.php" class="logout-btn">Keluar</a>
    </div>
</header>

<section class="menu-header">
    <p class="welcome">
        Halo, <?= htmlspecialchars($_SESSION["nama"]) ?>! 👋
    </p>
    <h1>Temukan Menu Favoritmu</h1>
    <p>Pilih makanan dan minuman yang ingin kamu nikmati hari ini.</p>
</section>

<main class="menu-container" id="daftar-menu">
    <div class="section-heading">
        <div>
            <span class="section-label">PILIHAN UNTUKMU</span>
            <h2>Semua Menu</h2>
        </div>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="menu-grid">
            <?php while ($item = mysqli_fetch_assoc($result)): ?>
                <article class="food-card">
                    <?php if (!empty($item["gambar"])): ?>
                        <img
                            src="../assets/img/<?= htmlspecialchars($item["gambar"]) ?>"
                            alt="<?= htmlspecialchars($item["nama_menu"]) ?>"
                        >
                    <?php else: ?>
                        <img
                            src="https://placehold.co/600x400/edf6e9/183a2a?text=RASA+RAYA"
                            alt="Gambar menu belum tersedia"
                        >
                    <?php endif; ?>

                    <div class="food-info">
                        <span class="food-category">
                            <?= htmlspecialchars($item["kategori"]) ?>
                        </span>

                        <h3><?= htmlspecialchars($item["nama_menu"]) ?></h3>

                        <p>
                            <?= htmlspecialchars($item["deskripsi"] ?? "") ?>
                        </p>

                        <div class="food-bottom">
                            <span class="food-price">
                                Rp<?= number_format((float)$item["harga"], 0, ",", ".") ?>
                            </span>

                            <form action="keranjang.php" method="POST">
    <input type="hidden" name="menu_id" value="<?= (int)$item['id'] ?>">
    <button type="submit" name="tambah" class="add-btn">+</button>
</form>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-menu">
            <h3>Menu sedang disiapkan 🍽️</h3>
            <p>Belum ada menu di database. Silakan tambahkan menu terlebih dahulu.</p>
        </div>
    <?php endif; ?>
</main>

<footer class="footer">
    <a href="../index.php" class="logo">RASA<span>RAYA.</span></a>
    <p>Temukan rasa favoritmu, nikmati setiap momen.</p>
    <small>&copy; 2026 RASA RAYA</small>
</footer>

</body>
</html>