<?php 
include 'header.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Hastane Otomasyonu - Üye Ol</title>
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <span class="auth-icon">📝</span>
                <h1>Üye Ol</h1>
                <p>Hasta kaydı oluşturun — doktor hesapları yönetici tarafından açılır</p>
            </div>

            <?php 
            if (isset($_GET['durum'])) {
                mesaj_goster($_GET['durum']);
            }
            ?>

            <form action="islem.php" method="post">
                <div class="form-group">
                    <label for="kullanici_adsoyad">Ad Soyad *</label>
                    <input type="text" id="kullanici_adsoyad" name="kullanici_adsoyad" class="form-control" placeholder="Adınız ve Soyadınız" required>
                </div>
                <div class="form-group">
                    <label for="kullanici_tc">TC Kimlik Numarası *</label>
                    <input type="text" id="kullanici_tc" name="kullanici_tc" class="form-control" placeholder="11 Haneli TC Kimlik" maxlength="11" required pattern="\d{11}">
                </div>
                <div class="form-group">
                    <label for="kullanici_telefon">Telefon Numarası</label>
                    <input type="tel" id="kullanici_telefon" name="kullanici_telefon" class="form-control" placeholder="05xxxxxxxxx" pattern="[0-9]{10,11}">
                </div>
                <div class="form-group">
                    <label for="kullanici_email">E-posta Adresi</label>
                    <input type="email" id="kullanici_email" name="kullanici_email" class="form-control" placeholder="ornek@mail.com">
                </div>
                <div class="form-group">
                    <label for="kullanici_password">Şifre * (En az 6 karakter)</label>
                    <input type="password" id="kullanici_password" name="kullanici_password" class="form-control" placeholder="Şifreniz" required minlength="6">
                </div>
                <button type="submit" class="btn btn-success" name="kullanicikaydet">Üye Ol</button>
            </form>

            <div class="auth-footer">
                Zaten üye misiniz? <a href="index.php">Giriş Yapın</a>
            </div>
        </div>
    </div>
</body>
</html>