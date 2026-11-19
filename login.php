<?php
session_start();
require_once "koneksi.php";

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, nama, email, password, role FROM users WHERE email = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($hasil);

    if ($user && password_verify($password, $user["password"])) {
        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["nama"] = $user["nama"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["role"] = $user["role"];

        if ($user["role"] === "admin") {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: pelanggan/menu.php");
        }

        exit;
    } else {
        $pesan = "Email atau password salah.";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | RASA RAYA</title>

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

        .login-card {
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
            margin-bottom: 30px;
        }

        .logo span {
            color: #ff8a3d;
        }

        h1 {
            color: #183a2a;
            font-size: 26px;
            margin-bottom: 10px;
        }

        .description {
            color: #758078;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin: 15px 0 8px;
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
            margin-bottom: 15px;
            border-radius: 8px;
            background: #fff0f0;
            color: #a32222;
            font-size: 13px;
        }

        .daftar {
            margin-top: 22px;
            text-align: center;
            font-size: 14px;
        }

        .daftar a {
            color: #2e7d32;
            font-weight: bold;
            text-decoration: none;
        }

        .beranda {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #758078;
            font-size: 13px;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="login-card">

        <div class="logo">RASA<span>RAYA.</span></div>

        <h1>Selamat Datang!</h1>

        <p class="description">
            Masuk ke akunmu dan temukan menu makanan favoritmu.
        </p>

        <?php if ($pesan !== ""): ?>
            <div class="pesan">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="Masukkan email"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Masukkan password"
                required
            >
<div style="text-align: right; margin-top: 10px;">
    <a href="lupa-password.php"
       style="color: #2e7d32; font-size: 13px; text-decoration: none;">
        Lupa Kata Sandi?
    </a>
</div> 
            <button type="submit" name="Login" class="btn-login">
                Login
            </button>
            <a href="register.php" class="btn-register"
            Register
        </a>
            
        </form>

        <p class="daftar">
            Belum punya akun?
            <a href="register.php">Daftar di sini</a>
        </p>

        <a href="index.php" class="beranda">← Kembali ke Beranda</a>

    </div>
</body>
</html>
