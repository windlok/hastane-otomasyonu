<?php 
include 'header.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Hastane Otomasyonu - Giriş Yap</title>
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <span class="auth-icon">🏥</span>
                <h1>Hastane Otomasyonu</h1>
                <p>Lütfen TC Kimlik numaranız ve şifrenizle giriş yapın</p>
            </div>

            <?php 
            if (isset($_GET['durum'])) {
                mesaj_goster($_GET['durum']);
            }
            ?>

            <form action="islem.php" method="post">
                <div class="form-group">
                    <label for="kullanici_tc">TC Kimlik Numarası</label>
                    <input type="text" id="kullanici_tc" name="kullanici_tc" class="form-control" placeholder="11 Haneli TC Kimlik Numarası" maxlength="11" required pattern="\d{11}">
                </div>
                <div class="form-group">
                    <label for="kullanici_password">Şifre</label>
                    <input type="password" id="kullanici_password" name="kullanici_password" class="form-control" placeholder="Şifreniz" required>
                </div>
                <button type="submit" class="btn btn-primary" name="giris_yap">Giriş Yap</button>
            </form>

            <div class="auth-footer">
                Hesabınız yok mu? <a href="uye.php">Hemen Üye Olun</a>
            </div>
        </div>
    </div>
</body>
</html>