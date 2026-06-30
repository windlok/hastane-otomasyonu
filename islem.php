<?php 
ob_start();
session_start();
include 'bagla.php';

// Çıkış işlemi
if (isset($_GET['islem'])) {
    if ($_GET['islem'] == 'cikis') {
        session_destroy();
        header('location:index.php');
        exit;
    }
    
    // Randevu silme işlemi
    if ($_GET['islem'] == 'randevu_sil') {
        if (!isset($_SESSION['kullanici_tc'])) {
            header('location:index.php');
            exit;
        }

        $randevu_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Kullanıcının kendi randevusunu sildiğinden emin olalım
        $kullanici_sorgu = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ?');
        $kullanici_sorgu->execute([$_SESSION['kullanici_tc']]);
        $kullanici = $kullanici_sorgu->fetch(PDO::FETCH_ASSOC);

        if ($kullanici) {
            $randevu_kontrol = $db->prepare('SELECT * FROM randevu WHERE randevu_id = ? AND kullanici_id = ?');
            $randevu_kontrol->execute([$randevu_id, $kullanici['kullanici_id']]);
            
            if ($randevu_kontrol->rowCount() > 0) {
                $sil = $db->prepare('DELETE FROM randevu WHERE randevu_id = ? AND kullanici_id = ?');
                $sil->execute([$randevu_id, $kullanici['kullanici_id']]);
                
                header('location:randevu.php?durum=silindi');
                exit;
            }
        }
        
        header('location:randevu.php');
        exit;
    }
}

// Randevu kayıt işlemi
if (isset($_POST['randevu_kayıt'])) {
    // CSRF kontrolü
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('location:anasayfa.php?durum=guvenlik_hatasi');
        exit;
    }

    if (!isset($_SESSION['kullanici_tc'])) {
        header('location:index.php?durum=giris_gerekli');
        exit;
    }

    $tarih = isset($_POST['tarih']) ? $_POST['tarih'] : null;
    $sehir = isset($_POST['sehirler']) ? $_POST['sehirler'] : null;
    $hastane = isset($_POST['hastane']) ? $_POST['hastane'] : null;
    $klinik = isset($_POST['klinik']) ? $_POST['klinik'] : null;
    $doktor = isset($_POST['doktor']) ? $_POST['doktor'] : null;
    $kullanici_tc = $_SESSION['kullanici_tc'];

    if (!$tarih || !$sehir || !$hastane || !$klinik || !$doktor) {
        header('location:anasayfa.php?durum=bos_alan');
        exit;
    }

    // Geçmiş tarih kontrolü
    if (strtotime($tarih) < strtotime(date('Y-m-d'))) {
        header('location:anasayfa.php?durum=gecmis_tarih');
        exit;
    }

    try {
        // Önce kullanıcı ID'sini alalım
        $kullanici_sorgu = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ?');
        $kullanici_sorgu->execute([$kullanici_tc]);
        $kullanici = $kullanici_sorgu->fetch(PDO::FETCH_ASSOC);
        
        if (!$kullanici) {
            header('location:anasayfa.php?durum=kullanici_bulunamadi');
            exit;
        }

        $sorgu = $db->prepare('INSERT INTO randevu SET
            kullanici_id = ?,
            randevu_sehir = ?,
            randevu_tarih = ?,
            randevu_hastane = ?,
            randevu_klinik = ?,
            randevu_doktoru = ?
        ');
        
        $ekle = $sorgu->execute([
            $kullanici['kullanici_id'],
            $sehir,
            $tarih,
            $hastane,
            $klinik,
            $doktor
        ]);

        if ($ekle) {
            header('location:randevu.php?durum=basarili');
            exit;
        } else {
            header('location:anasayfa.php?durum=hata');
            exit;
        }
    } catch(PDOException $e) {
        error_log("Randevu kayıt hatası: " . $e->getMessage());
        header('location:anasayfa.php?durum=sistem_hatasi');
        exit;
    }
}

// Kullanıcı kayıt işlemi
if (isset($_POST['kullanicikaydet'])) {
    $kullanici_tc = isset($_POST['kullanici_tc']) ? trim($_POST['kullanici_tc']) : null;
    $kullanici_adsoyad = isset($_POST['kullanici_adsoyad']) ? htmlspecialchars(trim($_POST['kullanici_adsoyad'])) : null;
    $kullanici_password = isset($_POST['kullanici_password']) ? $_POST['kullanici_password'] : null;
    $kullanici_telefon = isset($_POST['kullanici_telefon']) ? trim($_POST['kullanici_telefon']) : null;
    $kullanici_email = isset($_POST['kullanici_email']) ? trim($_POST['kullanici_email']) : null;

    // TC Kimlik kontrolü
    if (!preg_match('/^[0-9]{11}$/', $kullanici_tc)) {
        header('location:uye.php?durum=gecersiz_tc');
        exit;
    }

    // Boş alan kontrolü
    if (!$kullanici_tc || !$kullanici_adsoyad || !$kullanici_password) {
        header('location:uye.php?durum=bos_alan');
        exit;
    }

    // Şifre uzunluk kontrolü
    if (strlen($kullanici_password) < 6) {
        header('location:uye.php?durum=kisa_sifre');
        exit;
    }

    try {
        // TC veya E-posta mükerrerlik kontrolü
        $kontrol = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ?' . ($kullanici_email ? ' OR kullanici_email = ?' : ''));
        $params = [$kullanici_tc];
        if ($kullanici_email) $params[] = $kullanici_email;
        $kontrol->execute($params);
        
        if ($kontrol->rowCount() > 0) {
            header('location:uye.php?durum=mukerrer_kayit');
            exit;
        }

        // Güvenli şifre hash'leme
        $hashed_password = password_hash($kullanici_password, PASSWORD_DEFAULT);

        // Veritabanı ekleme işlemi
        $sorgu = $db->prepare('INSERT INTO kullanici SET
            kullanici_tc = ?,
            kullanici_adsoyad = ?,
            kullanici_password = ?,
            kullanici_telefon = ?,
            kullanici_email = ?
        ');
        $ekle = $sorgu->execute([
            $kullanici_tc, 
            $kullanici_adsoyad, 
            $hashed_password,
            $kullanici_telefon,
            $kullanici_email
        ]);
        
        if ($ekle) {
            header('location:index.php?durum=basarili_kayit');
            exit;
        } else {
            header('location:uye.php?durum=hata');
            exit;
        }
    } catch(PDOException $e) {
        error_log("Kullanıcı kaydı hatası: " . $e->getMessage());
        header('location:uye.php?durum=sistem_hatasi');
        exit;
    }
}

// Giriş işlemi için kontrol
if (isset($_POST['giris_yap'])) {
    $kullanici_tc = isset($_POST['kullanici_tc']) ? trim($_POST['kullanici_tc']) : null;
    $kullanici_password = isset($_POST['kullanici_password']) ? $_POST['kullanici_password'] : null;
    
    if (!$kullanici_tc || !$kullanici_password) {
        header('location:index.php?durum=bos_alan');
        exit;
    }

    try {
        $sorgu = $db->prepare('SELECT * FROM kullanici WHERE kullanici_tc = ?');
        $sorgu->execute([$kullanici_tc]);
        $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);
        
        if ($kullanici && password_verify($kullanici_password, $kullanici['kullanici_password'])) {
            $_SESSION['kullanici_tc'] = $kullanici_tc;
            $_SESSION['kullanici_adsoyad'] = $kullanici['kullanici_adsoyad'];
            
            header('location:anasayfa.php');
            exit;
        } else {
            header('location:index.php?durum=hatali_giris');
            exit;
        }
    } catch(PDOException $e) {
        error_log("Giriş hatası: " . $e->getMessage());
        header('location:index.php?durum=sistem_hatasi');
        exit;
    }
}

// Hesap Bilgileri Güncelleme
if (isset($_POST['hesap_guncelle'])) {
    // CSRF kontrolü
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('location:hesap.php?durum=guvenlik_hatasi');
        exit;
    }

    if (!isset($_SESSION['kullanici_tc'])) {
        header('location:index.php');
        exit;
    }

    $kullanici_adsoyad = isset($_POST['kullanici_adsoyad']) ? htmlspecialchars(trim($_POST['kullanici_adsoyad'])) : null;
    $kullanici_telefon = isset($_POST['kullanici_telefon']) ? trim($_POST['kullanici_telefon']) : null;
    $kullanici_email = isset($_POST['kullanici_email']) ? trim($_POST['kullanici_email']) : null;
    $mevcut_sifre = isset($_POST['mevcut_sifre']) ? $_POST['mevcut_sifre'] : null;
    $yeni_sifre = isset($_POST['yeni_sifre']) ? $_POST['yeni_sifre'] : null;

    if (!$kullanici_adsoyad) {
        header('location:hesap.php?durum=bos_adsoyad');
        exit;
    }

    try {
        // Mevcut kullanıcıyı çek
        $sorgu = $db->prepare('SELECT * FROM kullanici WHERE kullanici_tc = ?');
        $sorgu->execute([$_SESSION['kullanici_tc']]);
        $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

        if (!$kullanici) {
            header('location:index.php');
            exit;
        }

        // Email çakışma kontrolü
        if ($kullanici_email && $kullanici_email !== $kullanici['kullanici_email']) {
            $email_kontrol = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_email = ? AND kullanici_tc != ?');
            $email_kontrol->execute([$kullanici_email, $_SESSION['kullanici_tc']]);
            if ($email_kontrol->rowCount() > 0) {
                header('location:hesap.php?durum=mukerrer_email');
                exit;
            }
        }

        // Şifre güncelleme isteği varsa
        $sifre_sql = "";
        $params = [$kullanici_adsoyad, $kullanici_telefon, $kullanici_email];

        if (!empty($mevcut_sifre) && !empty($yeni_sifre)) {
            if (!password_verify($mevcut_sifre, $kullanici['kullanici_password'])) {
                header('location:hesap.php?durum=hatali_sifre');
                exit;
            }
            if (strlen($yeni_sifre) < 6) {
                header('location:hesap.php?durum=kisa_sifre');
                exit;
            }
            $sifre_sql = ", kullanici_password = ?";
            $params[] = password_hash($yeni_sifre, PASSWORD_DEFAULT);
        }

        $params[] = $_SESSION['kullanici_tc'];

        $guncelle = $db->prepare("UPDATE kullanici SET
            kullanici_adsoyad = ?,
            kullanici_telefon = ?,
            kullanici_email = ?
            $sifre_sql
            WHERE kullanici_tc = ?
        ");

        $sonuc = $guncelle->execute($params);

        if ($sonuc) {
            $_SESSION['kullanici_adsoyad'] = $kullanici_adsoyad; // Session'daki adı güncelle
            header('location:hesap.php?durum=guncellendi');
            exit;
        } else {
            header('location:hesap.php?durum=hata');
            exit;
        }

    } catch(PDOException $e) {
        error_log("Hesap güncelleme hatası: " . $e->getMessage());
        header('location:hesap.php?durum=sistem_hatasi');
        exit;
    }
}
?>