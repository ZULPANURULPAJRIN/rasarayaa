<?php
session_start();
require_once "../koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pesan = "";

// Tambah menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama = trim($_POST['nama_menu'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga = filter_input(INPUT_POST, 'harga', FILTER_VALIDATE_FLOAT);

    if ($nama === '' || $kategori === '' || $harga === false || $harga === null || $harga <= 0) {
        $pesan = "Nama, kategori, dan harga wajib diisi dengan benar.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO menu (nama_menu, kategori, deskripsi, harga, gambar, status)
             VALUES (?, ?, ?, ?, '', 'tersedia')"
        );

        mysqli_stmt_bind_param($stmt, "sssd", $nama, $kategori, $deskripsi, $harga);

        if (mysqli_stmt_execute($stmt)) {
            $pesan = "Menu berhasil ditambahkan.";
        } else {
            $pesan = "Menu gagal ditambahkan.";
        }

        mysqli_stmt_close($stmt);
    }
}

// Edit menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $nama = trim($_POST['nama_menu'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $harga = filter_input(INPUT_POST, 'harga', FILTER_VALIDATE_FLOAT);
    $status = $_POST['status'] ?? '';

    if (
        !$id || $nama === '' || $kategori === '' ||
        $harga === false || $harga === null || $harga <= 0 ||
        !in_array($status, ['tersedia', 'habis'], true)
    ) {
        $pesan = "Data menu tidak valid.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE menu
             SET nama_menu = ?, kategori = ?, deskripsi = ?, harga = ?, status = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssdsi",
            $nama,
            $kategori,
            $deskripsi,
            $harga,
            $status,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            $pesan = "Menu berhasil diperbarui.";
        } else {
            $pesan = "Menu gagal diperbarui.";
        }

        mysqli_stmt_close($stmt);
    }
}

// Hapus menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($id) {
        $stmt = mysqli_prepare($conn, "DELETE FROM menu WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            $pesan = "Menu berhasil dihapus.";
        } else {
            $pesan = "Menu tidak dapat dihapus. Menu mungkin sudah digunakan dalam pesanan.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $pesan = "ID menu tidak valid.";
    }
}

$query = mysqli_query($conn, "SELECT * FROM menu ORDER BY id DESC");

if (!$query) {
    die("Gagal mengambil data menu. Periksa struktur tabel database.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - RASA RAYA</title>

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
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: 14px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px #0000000d;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        input, textarea, select {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 7px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 75px;
        }

        button {
            padding: 10px 14px;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            font-weight: bold;
        }

        .add-btn {
            background: #f39a35;
            color: #253c2e;
            margin-top: 18px;
        }

        .save-btn {
            background: #245c43;
            color: white;
        }

        .delete-btn {
            background: #d9534f;
            color: white;
            margin-top: 8px;
        }

        .notice {
            background: #dff5e6;
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #245c43;
            color: white;
        }

        .edit-form {
            min-width: 230px;
        }

        .edit-form input,
        .edit-form textarea,
        .edit-form select {
            margin-bottom: 8px;
        }

        .empty {
            padding: 20px;
            text-align: center;
        }

        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }

            nav {
                flex-direction: column;
                align-items: flex-start;
            }

            nav a {
                display: inline-block;
                margin: 10px 10px 0 0;
            }
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
    <h1>Kelola Menu</h1>
    <p>Tambahkan dan kelola daftar makanan serta minuman RASA RAYA.</p>

    <?php if ($pesan !== ''): ?>
        <div class="notice"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Tambah Menu Baru</h2>

        <form method="POST">
            <div class="form-grid">
                <div>
                    <label>Nama Menu</label>
                    <input type="text" name="nama_menu" required>
                </div>

                <div>
                    <label>Kategori</label>
                    <select name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Menu Nasi">Menu Nasi</option>
                        <option value="Mi & Pasta">Mi & Pasta</option>
                        <option value="Fast Food">Fast Food</option>
                        <option value="Camilan">Camilan</option>
                        <option value="Minuman">Minuman</option>
                    </select>
                </div>

                <div>
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" min="1" step="1" required>
                </div>

                <div>
                    <label>Deskripsi</label>
                    <textarea name="deskripsi"></textarea>
                </div>
            </div>

            <button class="add-btn" type="submit" name="tambah">
                + Tambah Menu
            </button>
        </form>
    </div>

    <div class="card">
        <h2>Daftar Menu</h2>

        <div class="table-wrapper">
            <?php if (mysqli_num_rows($query) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Menu</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td><?= (int)$item['id'] ?></td>

                                <td>
                                    <form method="POST" class="edit-form">
                                        <input type="hidden" name="id"
                                               value="<?= (int)$item['id'] ?>">

                                        <label>Nama</label>
                                        <input type="text" name="nama_menu"
                                               value="<?= htmlspecialchars($item['nama_menu']) ?>"
                                               required>

                                        <label>Deskripsi</label>
                                        <textarea name="deskripsi"><?= htmlspecialchars($item['deskripsi'] ?? '') ?></textarea>
                                </td>

                                <td>
                                        <label>Kategori</label>
                                        <select name="kategori" required>
                                            <?php
                                            $kategori_list = [
                                                'Menu Nasi',
                                                'Mi & Pasta',
                                                'Fast Food',
                                                'Camilan',
                                                'Minuman'
                                            ];
                                            ?>

                                            <?php foreach ($kategori_list as $kategori): ?>
                                                <option
                                                    value="<?= htmlspecialchars($kategori) ?>"
                                                    <?= $item['kategori'] === $kategori ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($kategori) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                </td>

                                <td>
                                        <label>Harga</label>
                                        <input type="number" name="harga"
                                               value="<?= (float)$item['harga'] ?>"
                                               min="1" step="1" required>
                                </td>

                                <td>
                                        <label>Status</label>
                                        <select name="status">
                                            <option value="tersedia"
                                                <?= $item['status'] === 'tersedia' ? 'selected' : '' ?>>
                                                Tersedia
                                            </option>

                                            <option value="habis"
                                                <?= $item['status'] === 'habis' ? 'selected' : '' ?>>
                                                Habis
                                            </option>
                                        </select>
                                </td>

                                <td>
                                        <button type="submit" name="edit" class="save-btn">
                                            Simpan Perubahan
                                        </button>
                                    </form>

                                    <form method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus menu ini?');">
                                        <input type="hidden" name="id"
                                               value="<?= (int)$item['id'] ?>">
                                        <button type="submit" name="hapus" class="delete-btn">
                                            Hapus Menu
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty">Belum ada menu yang ditambahkan.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>