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
            <?php if ($rol === 'admin'): ?>
            <a href="admin_panel.php" class="nav-link <?php echo $current_page === 'admin_panel.php' ? 'active' : ''; ?>">
                <span class="nav-icon">🔧</span> Admin Paneli
            </a>
            <a href="anasayfa.php" class="nav-link <?php echo $current_page === 'anasayfa.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📋</span> Randevu Al
            </a>
            <a href="randevu.php" class="nav-link <?php echo $current_page === 'randevu.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📅</span> Randevularım
            </a>
            <a href="hesap.php" class="nav-link <?php echo $current_page === 'hesap.php' ? 'active' : ''; ?>">
                <span class="nav-icon">👤</span> Hesabım
            </a>
            <?php elseif ($rol === 'hasta'): ?>
            <a href="anasayfa.php" class="nav-link <?php echo $current_page === 'anasayfa.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📋</span> Randevu Al
            </a>
            <a href="randevu.php" class="nav-link <?php echo $current_page === 'randevu.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📅</span> Randevularım
            </a>
            <a href="sira.php" class="nav-link <?php echo $current_page === 'sira.php' ? 'active' : ''; ?>">
                <span class="nav-icon">🔢</span> Sıram
            </a>
            <a href="rapor.php" class="nav-link <?php echo $current_page === 'rapor.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span> Rapor
            </a>
            <a href="hesap.php" class="nav-link <?php echo $current_page === 'hesap.php' ? 'active' : ''; ?>">
                <span class="nav-icon">👤</span> Hesabım
            </a>
            <?php elseif ($rol === 'doktor'): ?>
            <a href="doktor_panel.php" class="nav-link <?php echo $current_page === 'doktor_panel.php' ? 'active' : ''; ?>">
                <span class="nav-icon">👥</span> Hasta Randevularım
            </a>
            <a href="sira.php" class="nav-link <?php echo $current_page === 'sira.php' ? 'active' : ''; ?>">
                <span class="nav-icon">🔢</span> Sıra Listesi
            </a>
            <a href="rapor.php" class="nav-link <?php echo $current_page === 'rapor.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span> Rapor
            </a>
            <a href="hesap.php" class="nav-link <?php echo $current_page === 'hesap.php' ? 'active' : ''; ?>">
                <span class="nav-icon">👤</span> Profilim
            </a>
            <?php endif; ?>
        </div>
        <div class="navbar-right">
            <div class="lang-switcher" style="margin-right:12px;">
                <a href="dil_degistir.php?dil=tr" style="text-decoration:none;font-size:13px;padding:4px 8px;border-radius:4px;font-weight:600;<?php echo $dil === 'tr' ? 'background:rgba(255,255,255,0.2);color:#fff;' : 'color:rgba(255,255,255,0.6);'; ?>">TR</a>
                <a href="dil_degistir.php?dil=en" style="text-decoration:none;font-size:13px;padding:4px 8px;border-radius:4px;font-weight:600;<?php echo $dil === 'en' ? 'background:rgba(255,255,255,0.2);color:#fff;' : 'color:rgba(255,255,255,0.6);'; ?>">EN</a>
            </div>
            <?php if(isset($_SESSION['kullanici_adsoyad'])): ?>
            <span class="user-greeting">
                <?php echo $rol === 'doktor' ? '👨‍⚕️' : ($rol === 'admin' ? '🔧' : '🙂'); ?>
                <strong><?php echo htmlspecialchars($_SESSION['kullanici_adsoyad']); ?></strong>
            </span>
            <?php endif; ?>
            <a href="islem.php?islem=cikis" class="btn-logout"><?php echo __('cikis_yap'); ?></a>
        </div>
    </div>
</nav>
