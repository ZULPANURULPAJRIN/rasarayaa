<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RASA RAYA | Pemesanan Makanan</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- NAVBAR -->
    <header class="navbar">
        <a href="index.php" class="logo">
            RASA<span>RAYA.</span>
        </a>

        <nav class="nav-menu">
            <a href="#home">BERANDA</a>
            <a href="#kategori">KATEGORI</a>
            <a href="#menu">MENU</a>
        </nav>

        <div class="nav-actions">

        </div>
    </header>

    <!-- HERO -->
    <main>
        <section class="hero" id="home">
            <div class="hero-content">
                <span class="hero-label">🌿 FRESH FOOD, HAPPY MOOD!</span>

                <h1>
                    Makanan Enak,<br>
                    <span>Suasana Menyenangkan.</span>
                </h1>

                <p>
                    Temukan berbagai makanan dan minuman favoritmu.
                    Pesan dengan mudah, nikmati setiap rasanya!
                </p>

                
<div class="nav-buttons">
    <a href="login.php" class="btn-login">Login</a>
    <a href="register.php" class="btn-register">Register</a>
</div>

                <div class="hero-info">
        
                    <div>
                        <strong>Fresh</strong>
                        <span>Setiap Hari</span>
                    </div>

                    <div>
                        <strong>Easy</strong>
                        <span>Ordering</span>
                    </div>
                </div>
            </div>

            <div class="hero-image">
                <img
                    src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=900&q=85"
                    alt="Hidangan makanan segar"
                >
                <div class="floating-card">
                    <span>🍽️</span>
                    <div>
                        <strong>Fresh & Delicious</strong>
                        <p>Dibuat dengan sepenuh hati</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- KATEGORI -->
        <section class="category-section" id="kategori">
            <div class="section-heading">
                <div>
                    <span class="section-label">PILIH FAVORITMU</span>
                    <h2>Kategori Menu</h2>
                </div>
                <p>Beragam pilihan untuk menemani harimu.</p>
            </div>

            <div class="category-list">
                <a href="#menu" class="category-card">
                    <span>🍚</span>
                    <strong>Menu Nasi</strong>
                </a>

                <a href="#menu" class="category-card">
                    <span>🍜</span>
                    <strong>Mi & Pasta</strong>
                </a>

                <a href="#menu" class="category-card">
                    <span>🍔</span>
                    <strong>Fast Food</strong>
                </a>

                <a href="#menu" class="category-card">
                    <span>🥟</span>
                    <strong>Camilan</strong>
                </a>

                <a href="#menu" class="category-card">
                    <span>🥤</span>
                    <strong>Minuman</strong>
                </a>
            </div>
        </section>

        <!-- MENU FAVORIT -->
        <section class="menu-section" id="menu">
            <div class="section-heading">
                <div>
                    <span class="section-label">ENAKNYA BIKIN HAPPY</span>
                    <h2>Menu Favorit</h2>
                </div>
                <a href="pelanggan/menu.php" class="see-all">
                    Lihat Semua →
                </a>
            </div>

            <div class="menu-list">
                <article class="menu-card">
                    <img
                        src="https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=600&q=80"
                        alt="Nasi goreng"
                    >
                    <div class="menu-details">
                        <span class="menu-category">MENU NASI</span>
                        <h3>Nasi Goreng Spesial</h3>
                        <p>Nasi goreng gurih dengan topping pilihan.</p>
                        <div class="menu-bottom">
                            <strong>Rp18.000</strong>
                            <a href="login.php" class="add-btn">+</a>
                        </div>
                    </div>
                </article>

                <article class="menu-card">
                    <img
                        src="https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80"
                        alt="Mi kuah"
                    >
                    <div class="menu-details">
                        <span class="menu-category">MI & PASTA</span>
                        <h3>Mi Kuah Spesial</h3>
                        <p>Mi hangat dengan kuah gurih dan topping.</p>
                        <div class="menu-bottom">
                            <strong>Rp15.000</strong>
                            <a href="login.php" class="add-btn">+</a>
                        </div>
                    </div>
                </article>

                <article class="menu-card">
                    <img
                        src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80"
                        alt="Burger"
                    >
                    <div class="menu-details">
                        <span class="menu-category">FAST FOOD</span>
                        <h3>Classic Beef Burger</h3>
                        <p>Roti lembut dengan patty dan sayuran segar.</p>
                        <div class="menu-bottom">
                            <strong>Rp25.000</strong>
                            <a href="login.php" class="add-btn">+</a>
                        </div>
                    </div>
                </article>

                <article class="menu-card">
                    <img
                        src="https://images.unsplash.com/photo-1563245372-f21724e3856d?auto=format&fit=crop&w=600&q=80"
                        alt="Dimsum"
                    >
                    <div class="menu-details">
                        <span class="menu-category">CAMILAN</span>
                        <h3>Dimsum Ayam</h3>
                        <p>Dimsum lembut yang cocok untuk camilan.</p>
                        <div class="menu-bottom">
                            <strong>Rp16.000</strong>
                            <a href="login.php" class="add-btn">+</a>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <a href="index.php" class="logo">
            RASA<span>RAYA.</span>
        </a>
        <p>Temukan rasa favoritmu, nikmati setiap momen.</p>
        <small>&copy; 2026 RASA RAYA. All rights reserved.</small>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>