<?php 
include 'header.php';
$aktif_tip = isset($_GET['tip']) && $_GET['tip'] === 'doktor' ? 'doktor' : 'hasta';
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
                <span class="auth-icon"><?php echo $aktif_tip === 'doktor' ? '👨‍⚕️' : '🏥'; ?></span>
                <h1><?php echo $aktif_tip === 'doktor' ? 'Doktor Girişi' : 'Hasta Girişi'; ?></h1>
                <p><?php echo $aktif_tip === 'doktor' ? 'Doktor panelinize giriş yapın' : 'TC Kimlik numaranız ve şifrenizle giriş yapın'; ?></p>
            </div>

            <div class="auth-tabs">
                <a href="index.php?tip=hasta" class="auth-tab <?php echo $aktif_tip === 'hasta' ? 'active' : ''; ?>">Hasta</a>
                <a href="index.php?tip=doktor" class="auth-tab <?php echo $aktif_tip === 'doktor' ? 'active' : ''; ?>">Doktor</a>
            </div>

            <?php 
            if (isset($_GET['durum'])) {
                mesaj_goster($_GET['durum']);
            }
            ?>

            <form action="islem.php" method="post">
                <input type="hidden" name="giris_tipi" value="<?php echo $aktif_tip; ?>">
                <div class="form-group">
                    <label for="kullanici_tc">TC Kimlik Numarası</label>
                    <input type="text" id="kullanici_tc" name="kullanici_tc" class="form-control" placeholder="11 Haneli TC Kimlik Numarası" maxlength="11" required pattern="\d{11}">
                </div>
                <div class="form-group">
                    <label for="kullanici_password">Şifre</label>
                    <input type="password" id="kullanici_password" name="kullanici_password" class="form-control" placeholder="Şifreniz" required>
                </div>
                <button type="submit" class="btn btn-primary" name="giris_yap">Giriş Yap</button>
                <div style="text-align: center; margin-top: 12px;">
                    <a href="sifre_sifirlama.php" style="color: var(--primary); font-size: 13px;">Şifremi Unuttum</a>
                </div>
            </form>

            <?php if ($aktif_tip === 'hasta'): ?>
            <div class="auth-footer">
                Hesabınız yok mu? <a href="uye.php">Hemen Üye Olun</a>
            </div>
            <?php else: ?>
            <div class="auth-footer auth-hint">
                Demo doktor: TC <strong>11111111110</strong> / Şifre <strong>doktor123</strong>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
