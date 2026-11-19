<?php
require_once "koneksi.php";

$pesan = "";
$jenis_pesan = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $konfirmasi = $_POST["konfirmasi"] ?? "";

    if ($nama === "" || $email === "" || $password === "") {
        $pesan = "Semua kolom wajib diisi.";
        $jenis_pesan = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = "Format email tidak valid.";
        $jenis_pesan = "error";
    } elseif (strlen($password) < 8) {
        $pesan = "Password minimal 8 karakter.";
        $jenis_pesan = "error";
    } elseif ($password !== $konfirmasi) {
        $pesan = "Konfirmasi password tidak cocok.";
        $jenis_pesan = "error";
    } else {
        $cek = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ?"
        );

        mysqli_stmt_bind_param($cek, "s", $email);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $pesan = "Email sudah terdaftar.";
            $jenis_pesan = "error";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (nama, email, password)
                 VALUES (?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $nama,
                $email,
                $password_hash
            );

            if (mysqli_stmt_execute($stmt)) {
                header("Location: login.php?daftar=berhasil");
                exit;
            } else {
                $pesan = "Registrasi gagal. Silakan coba lagi.";
                $jenis_pesan = "error";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($cek);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun | RASA RAYA</title>
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 25px;
            background: #edf6e9;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            padding: 35px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 15px 45px rgba(24, 58, 42, 0.1);
        }

        .auth-logo {
            display: block;
            text-align: center;
            margin-bottom: 25px;
        }

        .auth-card h1 {
            color: #183a2a;
            font-size: 27px;
            margin-bottom: 8px;
        }

        .auth-description {
            color: #758078;
            font-size: 14px;
            margin-bottom: 22px;
        }

        .auth-card label {
            display: block;
            margin: 15px 0 7px;
            font-size: 13px;
            font-weight: 800;
        }

        .auth-card input {
            width: 100%;
            padding: 13px;
            border: 1px solid #dce5dc;
            border-radius: 10px;
            font: inherit;
        }

        .auth-card input:focus {
            outline: 2px solid #b9ddb7;
            border-color: #2e7d32;
        }

        .auth-submit {
            width: 100%;
            border: none;
            border-radius: 10px;
            padding: 14px;
            margin-top: 23px;
            background: #2e7d32;
            color: white;
            font: inherit;
            font-weight: 900;
            cursor: pointer;
        }

        .auth-message {
            padding: 12px;
            border-radius: 9px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .error {
            color: #a32222;
            background: #fff0f0;
        }

        .auth-footer {
            text-align: center;
            font-size: 13px;
            margin-top: 20px;
        }

        .auth-footer a {
            color: #2e7d32;
            font-weight: 900;
        }

        .back-home {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #758078;
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <a href="index.php" class="logo auth-logo">
        RASA<span>RAYA.</span>
    </a>

    <h1>Buat Akun Baru ✨</h1>
    <p class="auth-description">
        Daftar sekarang dan nikmati kemudahan memesan makanan.
    </p>

    <?php if ($pesan !== ""): ?>
        <div class="auth-message <?= htmlspecialchars($jenis_pesan) ?>">
            <?= htmlspecialchars($pesan) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nama">Nama Lengkap</label>
        <input
            type="text"
            id="nama"
            name="nama"
            placeholder="Masukkan nama lengkap"
            maxlength="100"
            autocomplete="name"
            required
        >

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            placeholder="nama@email.com"
            maxlength="100"
            autocomplete="email"
            required
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="Minimal 8 karakter"
            minlength="8"
            autocomplete="new-password"
            required
        >

        <label for="konfirmasi">Konfirmasi Password</label>
        <input
            type="password"
            id="konfirmasi"
            name="konfirmasi"
            placeholder="Ulangi password"
            minlength="8"
            autocomplete="new-password"
            required
        >

        <button type="submit" class="auth-submit">
            Daftar Sekarang
        </button>
    </form>

    <p class="auth-footer">
        Sudah punya akun?
        <a href="login.php">Masuk di sini</a>
    </p>

    <a href="index.php" class="back-home">← Kembali ke Beranda</a>
</div>

</body>
</html>