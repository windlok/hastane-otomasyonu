<?php 
ob_start();
session_start();
include'bagla.php';

// Çıkış işlemi
if(isset($_GET['islem'])) {
    if($_GET['islem'] == 'cikis') {
        session_destroy();
        header('location:index.php');
        exit;
    }
    
    // Randevu silme işlemi
    if($_GET['islem'] == 'randevu_sil') {
        if(!isset($_SESSION['kullanici_tc'])) {
            header('location:index.php');
            exit;
        }

        $randevu_id = isset($_GET['id']) ? $_GET['id'] : 0;
        
        // Kullanıcının kendi randevusunu sildiğinden emin olalım
        $kullanici_sorgu = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ?');
        $kullanici_sorgu->execute([$_SESSION['kullanici_tc']]);
        $kullanici = $kullanici_sorgu->fetch(PDO::FETCH_ASSOC);

        if($kullanici) {
            $randevu_kontrol = $db->prepare('SELECT * FROM randevu WHERE randevu_id = ? AND kullanici_id = ?');
            $randevu_kontrol->execute([$randevu_id, $kullanici['kullanici_id']]);
            
            if($randevu_kontrol->rowCount() > 0) {
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
if(isset($_POST['randevu_kayıt'])) {
    if(!isset($_SESSION['kullanici_tc'])) {
        header('location:index.php?durum=giris_gerekli');
        exit;
    }

    $tarih = isset($_POST['tarih']) ? $_POST['tarih'] : null;
    $sehir = isset($_POST['sehirler']) ? $_POST['sehirler'] : null;
    $hastane = isset($_POST['hastane']) ? $_POST['hastane'] : null;
    $klinik = isset($_POST['klinik']) ? $_POST['klinik'] : null;
    $doktor = isset($_POST['doktor']) ? $_POST['doktor'] : null;
    $kullanici_tc = $_SESSION['kullanici_tc'];

    if(!$tarih || !$hastane || !$klinik || !$doktor) {
        header('location:randevu.php?durum=bos_alan');
        exit;
    }

    try {
        // Önce kullanıcı ID'sini alalım
        $kullanici_sorgu = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ?');
        $kullanici_sorgu->execute([$kullanici_tc]);
        $kullanici = $kullanici_sorgu->fetch(PDO::FETCH_ASSOC);
        
        if(!$kullanici) {
            header('location:randevu.php?durum=kullanici_bulunamadi');
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

        if($ekle) {
            header('location:randevu.php?durum=basarili');
            exit;
        } else {
            header('location:randevu.php?durum=hata');
            exit;
        }
    } catch(PDOException $e) {
        error_log("Randevu kayıt hatası: " . $e->getMessage());
        header('location:randevu.php?durum=sistem_hatasi');
        exit;
    }
}

if(isset($_POST['kullanicikaydet'])) {
    $kullanici_tc = isset($_POST['kullanici_tc']) ? trim($_POST['kullanici_tc']) : null;
    $kullanici_adsoyad = isset($_POST['kullanici_adsoyad']) ? htmlspecialchars(trim($_POST['kullanici_adsoyad'])) : null;
    $kullanici_password = isset($_POST['kullanici_password']) ? $_POST['kullanici_password'] : null;

    // TC Kimlik kontrolü
    if(!preg_match('/^[0-9]{11}$/', $kullanici_tc)) {
        header('location:uye.php?durum=gecersiz_tc');
        exit;
    }

    // Boş alan kontrolü
    if(!$kullanici_tc || !$kullanici_adsoyad || !$kullanici_password) {
        header('location:uye.php?durum=bos_alan');
        exit;
    }

    try {
        //veritabanı ekleme işlemi
        $sorgu = $db->prepare('INSERT INTO kullanici SET
            kullanici_tc = ?,
            kullanici_adsoyad = ?,
            kullanici_password = ?
        ');
        $ekle = $sorgu->execute([
            $kullanici_tc, 
            $kullanici_adsoyad, 
            $kullanici_password
        ]);
        
        if ($ekle) {
            header('location:index.php?durum=basarili');
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
if(isset($_POST['giris_yap'])) {
    $kullanici_tc = isset($_POST['kullanici_tc']) ? trim($_POST['kullanici_tc']) : null;
    $kullanici_password = isset($_POST['kullanici_password']) ? $_POST['kullanici_password'] : null;
    
    if(!$kullanici_tc || !$kullanici_password) {
        header('location:index.php?durum=bos_alan');
        exit;
    }

    try {
        $sorgu = $db->prepare('SELECT * FROM kullanici WHERE kullanici_tc = ? AND kullanici_password = ?');
        $sorgu->execute([$kullanici_tc, $kullanici_password]);
        
        if($sorgu->rowCount() > 0) {
            $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);
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
?>