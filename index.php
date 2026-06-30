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
    <title><?php echo __('site_title'); ?> - <?php echo __('giris_yap'); ?></title>
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <span class="auth-icon"><?php echo $aktif_tip === 'doktor' ? '👨‍⚕️' : '🏥'; ?></span>
                <h1><?php echo $aktif_tip === 'doktor' ? __('doktor_girisi') : __('hasta_girisi'); ?></h1>
                <p><?php echo $aktif_tip === 'doktor' ? __('doktor_giris_aciklama') : __('hasta_giris_aciklama'); ?></p>
            </div>

            <div class="auth-tabs">
                <a href="index.php?tip=hasta" class="auth-tab <?php echo $aktif_tip === 'hasta' ? 'active' : ''; ?>"><?php echo __('hasta'); ?></a>
                <a href="index.php?tip=doktor" class="auth-tab <?php echo $aktif_tip === 'doktor' ? 'active' : ''; ?>"><?php echo __('doktor'); ?></a>
            </div>

            <?php 
            if (isset($_GET['durum'])) {
                mesaj_goster($_GET['durum']);
            }
            ?>

            <form action="islem.php" method="post">
                <input type="hidden" name="giris_tipi" value="<?php echo $aktif_tip; ?>">
                <div class="form-group">
                    <label for="kullanici_tc"><?php echo __('tc_kimlik'); ?></label>
                    <input type="text" id="kullanici_tc" name="kullanici_tc" class="form-control" placeholder="11 Haneli TC Kimlik Numarası" maxlength="11" required pattern="\d{11}">
                </div>
                <div class="form-group">
                    <label for="kullanici_password"><?php echo __('sifre'); ?></label>
                    <input type="password" id="kullanici_password" name="kullanici_password" class="form-control" placeholder="Şifreniz" required>
                </div>
                <button type="submit" class="btn btn-primary" name="giris_yap"><?php echo __('giris_yap'); ?></button>
                <div style="text-align: center; margin-top: 12px;">
                    <a href="sifre_sifirlama.php" style="color: var(--primary); font-size: 13px;"><?php echo __('sifremi_unuttum'); ?></a>
                    <span style="color: var(--text-muted); margin: 0 6px;">|</span>
                    <a href="sira_sorgu.php" style="color: var(--primary); font-size: 13px;"><?php echo __('sira_sorgula'); ?></a>
                </div>
            </form>

            <?php if ($aktif_tip === 'hasta'): ?>
            <div class="auth-footer">
                <?php echo __('hesabiniz_yok_mu'); ?> <a href="uye.php"><?php echo __('uye_ol'); ?></a>
            </div>
            <?php else: ?>
            <div class="auth-footer auth-hint">
                <?php echo __('demo_doktor_bilgi'); ?>: TC <strong>11111111110</strong> / <?php echo __('sifre'); ?> <strong>doktor123</strong>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
