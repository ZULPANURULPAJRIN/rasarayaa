    <?php
    session_start();
    require_once "../koneksi.php";

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }

    $user_id = (int) $_SESSION['user_id'];
    if (!empty($_GET['detail'])) {
        $detail_id = filter_input(INPUT_GET, 'detail', FILTER_VALIDATE_INT);

        if (!$detail_id) {
            header("Location: pesanan.php");
            exit;
        }

        $stmt_detail = mysqli_prepare(
            $conn,
"SELECT p.id, p.nomor_pesanan, p.tipe_pesanan,
        p.nomor_meja, p.total_harga, p.status,
        p.created_at, p.status_diperbarui
 FROM pesanan p
 WHERE p.id = ? AND p.user_id = ?"
        );

        mysqli_stmt_bind_param($stmt_detail, "ii", $detail_id, $user_id);
        mysqli_stmt_execute($stmt_detail);
        $hasil_detail = mysqli_stmt_get_result($stmt_detail);
        $pesanan_detail = mysqli_fetch_assoc($hasil_detail);

        if (!$pesanan_detail) {
            header("Location: pesanan.php");
            exit;
        }

        $stmt_item = mysqli_prepare(
            $conn,
            "SELECT m.nama_menu, d.jumlah, d.harga, d.subtotal
            FROM detail_pesanan d
            JOIN menu m ON d.menu_id = m.id
            WHERE d.pesanan_id = ?"
        );

        mysqli_stmt_bind_param($stmt_item, "i", $detail_id);
        mysqli_stmt_execute($stmt_item);
        $daftar_detail = mysqli_stmt_get_result($stmt_item);
    }

$cari = trim($_GET['cari'] ?? '');

$sql = "SELECT id, nomor_pesanan, tipe_pesanan, nomor_meja,
               total_harga, status, created_at, status_diperbarui
        FROM pesanan
        WHERE user_id = ?";

if ($cari !== '') {
    $sql .= " AND nomor_pesanan LIKE ?";
}

$sql .= " ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);

if ($cari !== '') {
    $kata_kunci = "%" . $cari . "%";
    mysqli_stmt_bind_param($stmt, "is", $user_id, $kata_kunci);
} else {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>


    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pesanan Saya - RASA RAYA</title>

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

            nav a {
                color: white;
                text-decoration: none;
                margin-left: 18px;
            }

            .container {
                max-width: 1000px;
                margin: 35px auto;
                padding: 0 20px;
            }

            .order-card {
                background: white;
                border-radius: 14px;
                padding: 22px;
                margin-bottom: 18px;
                box-shadow: 0 4px 15px #0000000d;
            }

            .order-header {
                display: flex;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 10px;
                border-bottom: 1px solid #eee;
                padding-bottom: 15px;
            }

            .badge {
                display: inline-block;
                padding: 7px 12px;
                border-radius: 20px;
                font-size: 13px;
                font-weight: bold;
                background: #fff0d8;
                color: #9b5b08;
            }

            .selesai {
                background: #dff5e6;
                color: #216b3c;
            }

            .dibatalkan {
                background: #ffe2e2;
                color: #a12626;
            }

            .detail {
                line-height: 1.9;
                margin-top: 12px;
            }

            .total {
                font-size: 19px;
                font-weight: bold;
                margin-top: 12px;
            }

            .empty {
                background: white;
                padding: 35px;
                text-align: center;
                border-radius: 14px;
            }

            .button {
                display: inline-block;
                background: #f39a35;
                color: #253c2e;
                padding: 12px 18px;
                border-radius: 8px;
                text-decoration: none;
                font-weight: bold;
                margin-top: 15px;
            }

            @media (max-width: 600px) {
                nav {
                    padding: 16px;
                    flex-direction: column;
                    gap: 12px;
                }

                nav a { margin: 0 5px; }
            }
            .search-order {
    display: flex;
    gap: 10px;
    margin: 20px 0;
    flex-wrap: wrap;
}

.search-order input {
    flex: 1;
    min-width: 200px;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
}

.search-order button {
    padding: 12px 20px;
    background: #245c43;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

.search-order a {
    padding: 12px 16px;
    background: #eee;
    color: #333;
    border-radius: 8px;
    text-decoration: none;
}
.notification {
    background: #dcfce7;
    color: #166534;
    padding: 15px 20px;
    border-radius: 10px;
    margin: 15px 0;
    font-weight: bold;
}
        </style>
    </head>

    <body>

    <nav>
        <strong>🍽️ RASA RAYA</strong>

        <div>
            <a href="menu.php">Menu</a>
            <a href="keranjang.php">Keranjang</a>
            <a href="pesanan.php">Pesanan Saya</a>
            <a href="../logout.php">Keluar</a>
        </div>
    </nav>

    <div class="container">
        <h1>Pesanan Saya</h1>
        <p>Lihat riwayat dan status pesanan makananmu.</p>
        <?php if (isset($_GET['status_update'])): ?>
    <div class="notification">
        🔔 Status pesanan berhasil diperbarui!
    </div>
<?php endif; ?>
        <form method="GET" action="pesanan.php" class="search-order">
    <input
        type="text"
        name="cari"
        placeholder="Cari nomor pesanan..."
        value="<?= htmlspecialchars($_GET['cari'] ?? '') ?>"
    >

    <button type="submit">Cari</button>

    <a href="pesanan.php">Reset</a>
</form>
        

        <?php if (isset($_GET['berhasil'])): ?>
            <div class="order-card">
                <strong>✅ Pesanan berhasil dibuat!</strong>
                <p>Pesananmu sudah tersimpan di sistem RASA RAYA.</p>
                <?php if (!empty($_SESSION['pesanan_terakhir'])): ?>
                    <p>Nomor pesanan:
                        <strong><?= htmlspecialchars($_SESSION['pesanan_terakhir']) ?></strong>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php if (isset($pesanan_detail)): ?>

        <div class="order-card">
            <h2>Detail Pesanan</h2>

            <p>
                Nomor Pesanan:
                <strong><?= htmlspecialchars($pesanan_detail['nomor_pesanan']) ?></strong>
            </p>

            <p>
                Tanggal:
                <?= date('d-m-Y H:i', strtotime($pesanan_detail['created_at'])) ?>
            </p>

            <p>
                Jenis Pesanan:
                <?= $pesanan_detail['tipe_pesanan'] === 'dine-in'
                    ? 'Dine-in'
                    : 'Takeaway' ?>
            </p>

            <?php if ($pesanan_detail['tipe_pesanan'] === 'dine-in'): ?>
                <p>
                    Nomor Meja:
                    <?= htmlspecialchars($pesanan_detail['nomor_meja'] ?? '-') ?>
                </p>
            <?php endif; ?>

            <p>
                Status:
                <strong><?= htmlspecialchars(ucfirst($pesanan_detail['status'])) ?></strong>
            </p>
<?php if (!empty($pesanan_detail['status_diperbarui'])): ?>
    <p>
        Terakhir diperbarui:
        <?= date(
            'd-m-Y H:i',
            strtotime($pesanan_detail['status_diperbarui'])
        ) ?>
    </p>
<?php endif; ?>
            <hr>

            <h3>Daftar Makanan</h3>

            <?php if (mysqli_num_rows($daftar_detail) > 0): ?>
                <?php while ($item_detail = mysqli_fetch_assoc($daftar_detail)): ?>
                    <div class="item">
                        <div>
                            <strong><?= htmlspecialchars($item_detail['nama_menu']) ?></strong>
                            <div class="muted">
                                <?= (int)$item_detail['jumlah'] ?> ×
                                Rp<?= number_format((float)$item_detail['harga'], 0, ',', '.') ?>
                            </div>
                        </div>

                        <strong>
                            Rp<?= number_format((float)$item_detail['subtotal'], 0, ',', '.') ?>
                        </strong>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Belum ada rincian makanan.</p>
            <?php endif; ?>

            <div class="total">
        Total: Rp<?= number_format((float)$pesanan_detail['total_harga'], 0, ',', '.') ?>
    </div>

    <a href="pesanan.php" class="button">
        ← Kembali ke Pesanan Saya
    </a>

        </div>

    <?php else: ?>
        <?php if (mysqli_num_rows($result) > 0): ?>

    <?php while ($pesanan = mysqli_fetch_assoc($result)): ?>
    <?php
    $status = $pesanan['status'];

    $kelas_status = $status === 'selesai'
        ? 'selesai'
        : ($status === 'dibatalkan' ? 'dibatalkan' : '');
    ?>

                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <strong>
                                <?= htmlspecialchars($pesanan['nomor_pesanan']) ?>
                            </strong>
                            <p>
                                <?= date('d-m-Y H:i', strtotime($pesanan['created_at'])) ?>
                            </p>
                        </div>

                        <span class="badge <?= $kelas_status ?>">
                            <?= htmlspecialchars(ucfirst($status)) ?>
                        </span>
                    </div>

                    <div class="detail">
                        <div>
                            Jenis pesanan:
                            <strong>
                                <?= $pesanan['tipe_pesanan'] === 'dine-in'
                                    ? 'Dine-in'
                                    : 'Takeaway' ?>
                            </strong>
                        </div>

                        <?php if ($pesanan['tipe_pesanan'] === 'dine-in'): ?>
                            <div>
                                Nomor meja:
                                <?= htmlspecialchars($pesanan['nomor_meja'] ?? '-') ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="total">
                        Total: Rp<?= number_format((float)$pesanan['total_harga'], 0, ',', '.') ?>
                    </div>
                </div>
    <a href="pesanan.php?detail=<?= (int)$pesanan['id'] ?>" class="button">
        Lihat Detail Pesanan
    </a>
            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty">
                <h2>Belum Ada Pesanan</h2>
                <p>Kamu belum memiliki riwayat pesanan.</p>
                <a href="menu.php" class="button">Lihat Menu</a>
            </div>

        <?php endif; ?>
    <?php endif; ?>
    </div>

    </body>
    </html>