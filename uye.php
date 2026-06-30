<?php 
include 'header.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title><?php echo __('site_title'); ?> - <?php echo __('uye_ol'); ?></title>
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <span class="auth-icon">📝</span>
                <h1><?php echo __('uye_ol'); ?></h1>
                <p><?php echo __('uye_ol_aciklama'); ?></p>
            </div>

            <?php 
            if (isset($_GET['durum'])) {
                mesaj_goster($_GET['durum']);
            }
            ?>

            <form action="islem.php" method="post">
                    <?php echo csrf_input(); ?>
                <div class="form-group">
                    <label for="kullanici_adsoyad"><?php echo __('ad_soyad'); ?> *</label>
                    <input type="text" id="kullanici_adsoyad" name="kullanici_adsoyad" class="form-control" placeholder="Adınız ve Soyadınız" required>
                </div>
                <div class="form-group">
                    <label for="kullanici_tc"><?php echo __('tc_kimlik'); ?> *</label>
                    <input type="text" id="kullanici_tc" name="kullanici_tc" class="form-control" placeholder="11 Haneli TC Kimlik" maxlength="11" required pattern="\d{11}">
                </div>
                <div class="form-group">
                    <label for="kullanici_telefon"><?php echo __('telefon'); ?></label>
                    <input type="tel" id="kullanici_telefon" name="kullanici_telefon" class="form-control" placeholder="05xxxxxxxxx" pattern="[0-9]{10,11}">
                </div>
                <div class="form-group">
                    <label for="kullanici_email"><?php echo __('eposta'); ?></label>
                    <input type="email" id="kullanici_email" name="kullanici_email" class="form-control" placeholder="ornek@mail.com">
                </div>
                <div class="form-group">
                    <label for="kullanici_password"><?php echo __('sifre'); ?> * (<?php echo __('en_az_6_karakter'); ?>)</label>
                    <input type="password" id="kullanici_password" name="kullanici_password" class="form-control" placeholder="Şifreniz" required minlength="6">
                </div>
                <button type="submit" class="btn btn-success" name="kullanicikaydet"><?php echo __('uye_ol'); ?></button>
            </form>

            <div class="auth-footer">
                <?php echo __('zaten_uye_misiniz'); ?> <a href="index.php"><?php echo __('giris_yap'); ?></a>
            </div>
        </div>
    </div>
</body>
</html>