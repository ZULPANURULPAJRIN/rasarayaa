<?php
session_start();
require_once "koneksi.php";

$pesan = "";
$berhasil = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password_baru = $_POST["password_baru"] ?? "";
    $konfirmasi = $_POST["konfirmasi"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = "Masukkan alamat email yang valid.";
    } elseif (strlen($password_baru) < 8) {
        $pesan = "Password minimal 8 karakter.";
    } elseif ($password_baru !== $konfirmasi) {
        $pesan = "Konfirmasi password tidak cocok.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ?"
        );

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $hasil = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($hasil);
        mysqli_stmt_close($stmt);

        if (!$user) {
            $pesan = "Email tidak ditemukan.";
        } else {
            $hash = password_hash($password_baru, PASSWORD_DEFAULT);

            $update = mysqli_prepare(
                $conn,
                "UPDATE users SET password = ? WHERE id = ?"
            );

            mysqli_stmt_bind_param($update, "si", $hash, $user["id"]);

            if (mysqli_stmt_execute($update)) {
                $berhasil = true;
                $pesan = "Password berhasil diubah!";
            } else {
                $pesan = "Password gagal diubah.";
            }

            mysqli_stmt_close($update);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi | RASA RAYA</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            background: #edf6e9;
            font-family: Arial, sans-serif;
        }

        .card {
            width: 100%;
            max-width: 420px;
            padding: 35px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(24, 58, 42, 0.12);
        }

        .logo {
            text-align: center;
            color: #183a2a;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .logo span {
            color: #ff8a3d;
        }

        h1 {
            color: #183a2a;
            font-size: 25px;
            margin-bottom: 10px;
        }

        .deskripsi {
            color: #758078;
            font-size: 14px;
            line-height: 1.6;
        }

        label {
            display: block;
            margin: 18px 0 8px;
            font-size: 14px;
            font-weight: bold;
            color: #183a2a;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #dce5dc;
            border-radius: 10px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 25px;
            border: none;
            border-radius: 10px;
            background: #2e7d32;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #183a2a;
        }

        .pesan {
            padding: 12px;
            margin: 15px 0;
            border-radius: 8px;
            background: #fff0f0;
            color: #a32222;
            font-size: 13px;
        }

        .sukses {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .kembali {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #2e7d32;
            font-size: 14px;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="logo">RASA<span>RAYA.</span></div>

        <h1>Lupa Kata Sandi?</h1>

        <p class="deskripsi">
            Masukkan email terdaftar dan buat kata sandi baru.
        </p>

        <?php if ($pesan !== ""): ?>
            <div class="pesan <?= $berhasil ? 'sukses' : '' ?>">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        
<?php if (!$berhasil): ?>
    <form method="POST">
        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            placeholder="Masukkan email terdaftar"
            required
        >

        <label for="password_baru">Password Baru</label>
        <input
            type="password"
            id="password_baru"
            name="password_baru"
            minlength="8"
            placeholder="Minimal 8 karakter"
            required
        >

        <label for="konfirmasi">Konfirmasi Password Baru</label>
        <input
            type="password"
            id="konfirmasi"
            name="konfirmasi"
            minlength="8"
            placeholder="Ulangi password baru"
            required
        >

        <button type="submit">Ubah Password</button>
    </form>

    <a href="login.php" class="kembali">← Kembali ke Login</a>

<?php else: ?>
    <a href="login.php" class="tombol-login">Login Sekarang</a>
<?php endif; ?>