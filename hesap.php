<?php 
include 'header.php';
oturum_kontrol();

// Kullanıcı bilgilerini çekelim
try {
    $sorgu = $db->prepare('SELECT * FROM kullanici WHERE kullanici_tc = ?');
    $sorgu->execute([$_SESSION['kullanici_tc']]);
    $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);
    
    if (!$kullanici) {
        header('location:islem.php?islem=cikis');
        exit;
    }
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hastane Otomasyonu - Profilim</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="page-container">
        <div class="page-header">
            <h2>Hesap Ayarları</h2>
            <p>Profil bilgilerinizi görüntüleyebilir, güncelleyebilir veya şifrenizi değiştirebilirsiniz.</p>
        </div>

        <?php 
        if (isset($_GET['durum'])) {
            mesaj_goster($_GET['durum']);
        }
        ?>

        <div class="account-grid">
            <!-- Profil Bilgileri Kartı -->
            <div class="card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php 
                        // Baş harfleri alalım
                        $ad_soyad_dizi = explode(" ", $kullanici['kullanici_adsoyad']);
                        $bas_harfler = "";
                        if (count($ad_soyad_dizi) > 0) {
                            $bas_harfler .= mb_substr($ad_soyad_dizi[0], 0, 1, 'UTF-8');
                        }
                        if (count($ad_soyad_dizi) > 1) {
                            $bas_harfler .= mb_substr($ad_soyad_dizi[count($ad_soyad_dizi) - 1], 0, 1, 'UTF-8');
                        }
                        echo htmlspecialchars(mb_strtoupper($bas_harfler, 'UTF-8'));
                        ?>
                    </div>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($kullanici['kullanici_adsoyad']); ?></h3>
                        <p>TC Kimlik: <?php echo htmlspecialchars($kullanici['kullanici_tc']); ?></p>
                    </div>
                </div>

                <form action="islem.php" method="post">
                    <?php echo csrf_input(); ?>
                    
                    <div class="form-group">
                        <label for="kullanici_tc_goster">TC Kimlik Numarası (Değiştirilemez)</label>
                        <input type="text" id="kullanici_tc_goster" class="form-control" value="<?php echo htmlspecialchars($kullanici['kullanici_tc']); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="kullanici_adsoyad">Ad Soyad *</label>
                        <input type="text" id="kullanici_adsoyad" name="kullanici_adsoyad" class="form-control" value="<?php echo htmlspecialchars($kullanici['kullanici_adsoyad']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="kullanici_telefon">Telefon Numarası</label>
                        <input type="tel" id="kullanici_telefon" name="kullanici_telefon" class="form-control" value="<?php echo htmlspecialchars($kullanici['kullanici_telefon']); ?>" placeholder="05xxxxxxxxx" pattern="[0-9]{10,11}">
                    </div>

                    <div class="form-group">
                        <label for="kullanici_email">E-posta Adresi</label>
                        <input type="email" id="kullanici_email" name="kullanici_email" class="form-control" value="<?php echo htmlspecialchars($kullanici['kullanici_email']); ?>" placeholder="ornek@mail.com">
                    </div>

                    <div style="margin-top: 30px;">
                        <button type="submit" name="hesap_guncelle" class="btn btn-primary">Bilgileri Güncelle</button>
                    </div>
                </form>
            </div>

            <!-- Şifre Değiştirme Kartı -->
            <div class="card">
                <h3 class="card-title">
                    <span class="card-icon">🔒</span> Şifre Değiştir
                </h3>
                <p style="color: var(--text-light); font-size: 14px; margin-bottom: 20px;">
                    Şifrenizi değiştirmek istemiyorsanız bu alanları boş bırakabilirsiniz.
                </p>

                <form action="islem.php" method="post">
                    <?php echo csrf_input(); ?>
                    <input type="hidden" name="kullanici_adsoyad" value="<?php echo htmlspecialchars($kullanici['kullanici_adsoyad']); ?>">
                    <input type="hidden" name="kullanici_telefon" value="<?php echo htmlspecialchars($kullanici['kullanici_telefon'] ?? ''); ?>">
                    <input type="hidden" name="kullanici_email" value="<?php echo htmlspecialchars($kullanici['kullanici_email'] ?? ''); ?>">
                    
                    <div class="form-group">
                        <label for="mevcut_sifre">Mevcut Şifre</label>
                        <input type="password" id="mevcut_sifre" name="mevcut_sifre" class="form-control" placeholder="Mevcut Şifreniz">
                    </div>

                    <div class="form-group">
                        <label for="yeni_sifre">Yeni Şifre (En az 6 karakter)</label>
                        <input type="password" id="yeni_sifre" name="yeni_sifre" class="form-control" placeholder="Yeni Şifreniz" minlength="6">
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button type="submit" name="hesap_guncelle" class="btn btn-primary">Şifreyi Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>