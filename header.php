<?php 

ob_start();
session_start();

include 'bagla.php';


?>

 <!DOCTYPE html>
 <html lang="tr">
 <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Hastane Otomasyonu</title>
 </head>
 <body>
    <div class="ust_bar">
        <a href="anasayfa.php"><h1>Hastane Otomasyonu</h1></a>
        <div class="menu">
            <a href="hesap.php"><h5>Hesap Bilgileri</h5></a>
            <a href="randevu.php"><h5>Randevu Bilgileri</h5></a>
        </div>
        <?php if(isset($_SESSION['kullanici_adsoyad'])): ?>
        <div class="kullanici-bilgi">
            <span style="color: white; margin-right: 20px;">Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['kullanici_adsoyad']); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <a href="islem.php?islem=cikis" class="cikis">
        Çıkış Yap
    </a>


 </body>
 </html>