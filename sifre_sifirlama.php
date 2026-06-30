<?php 
include 'header.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title><?php echo __('site_title'); ?> - <?php echo __('sifre_sifirlama_baslik'); ?></title>
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <span class="auth-icon">🔑</span>
                <h1><?php echo __('sifre_sifirlama_baslik'); ?></h1>
                <p><?php echo __('sifre_sifirlama_aciklama'); ?></p>
            </div>

            <?php 
            if (isset($_GET['durum'])) {
                mesaj_goster($_GET['durum']);
            }
            ?>

            <form action="islem.php" method="post">
                <div class="form-group">
                    <label for="kullanici_tc"><?php echo __('tc_kimlik'); ?></label>
                    <input type="text" id="kullanici_tc" name="kullanici_tc" class="form-control" placeholder="11 Haneli TC Kimlik" maxlength="11" required pattern="\d{11}">
                </div>
                <div class="form-group">
                    <label for="kullanici_email"><?php echo __('eposta'); ?></label>
                    <input type="email" id="kullanici_email" name="kullanici_email" class="form-control" placeholder="Kayıtlı e-posta adresiniz" required>
                </div>
                <div class="form-group">
                    <label for="yeni_sifre"><?php echo __('yeni_sifre'); ?> (<?php echo __('en_az_6_karakter'); ?>)</label>
                    <input type="password" id="yeni_sifre" name="yeni_sifre" class="form-control" placeholder="Yeni şifreniz" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary" name="sifre_sifirla"><?php echo __('sifreyi_sifirla'); ?></button>
            </form>

            <div class="auth-footer">
                <a href="index.php"><?php echo __('ana_giris_don'); ?></a>
            </div>
        </div>
    </div>
</body>
</html>
