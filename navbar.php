<?php
$current_page = basename($_SERVER['PHP_SELF']);
$rol = kullanici_rol();
?>
<nav class="navbar">
    <div class="navbar-inner">
        <a href="<?php echo $rol === 'doktor' ? 'doktor_panel.php' : 'anasayfa.php'; ?>" class="navbar-brand">
            <span class="brand-icon">🏥</span>
            <span class="brand-text">Hastane Otomasyonu</span>
        </a>
        <div class="navbar-menu">
            <?php if ($rol === 'hasta'): ?>
            <a href="anasayfa.php" class="nav-link <?php echo $current_page === 'anasayfa.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📋</span> Randevu Al
            </a>
            <a href="randevu.php" class="nav-link <?php echo $current_page === 'randevu.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📅</span> Randevularım
            </a>
            <a href="hesap.php" class="nav-link <?php echo $current_page === 'hesap.php' ? 'active' : ''; ?>">
                <span class="nav-icon">👤</span> Hesabım
            </a>
            <?php else: ?>
            <a href="doktor_panel.php" class="nav-link <?php echo $current_page === 'doktor_panel.php' ? 'active' : ''; ?>">
                <span class="nav-icon">👥</span> Hasta Randevularım
            </a>
            <a href="hesap.php" class="nav-link <?php echo $current_page === 'hesap.php' ? 'active' : ''; ?>">
                <span class="nav-icon">👤</span> Profilim
            </a>
            <?php endif; ?>
        </div>
        <div class="navbar-right">
            <?php if(isset($_SESSION['kullanici_adsoyad'])): ?>
            <span class="user-greeting">
                <?php echo $rol === 'doktor' ? '👨‍⚕️' : '🙂'; ?>
                <strong><?php echo htmlspecialchars($_SESSION['kullanici_adsoyad']); ?></strong>
            </span>
            <?php endif; ?>
            <a href="islem.php?islem=cikis" class="btn-logout">Çıkış Yap</a>
        </div>
    </div>
</nav>
