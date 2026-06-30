<?php 
include 'header.php';

// Çıkış işlemi
if (isset($_GET['islem'])) {
    if ($_GET['islem'] == 'cikis') {
        session_destroy();
        header('location:index.php');
        exit;
    }
}

// Randevu silme işlemi (POST + CSRF)
if (isset($_POST['randevu_sil'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('location:randevu.php?durum=guvenlik_hatasi');
        exit;
    }

    if (!isset($_SESSION['kullanici_tc']) || ($_SESSION['kullanici_rol'] ?? 'hasta') !== 'hasta') {
        header('location:index.php');
        exit;
    }

    $randevu_id = isset($_POST['randevu_sil_id']) ? intval($_POST['randevu_sil_id']) : 0;

    $kullanici_sorgu = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ?');
    $kullanici_sorgu->execute([$_SESSION['kullanici_tc']]);
    $kullanici = $kullanici_sorgu->fetch(PDO::FETCH_ASSOC);

    if ($kullanici) {
        $randevu_kontrol = $db->prepare('SELECT r.*, k.kullanici_email FROM randevu r JOIN kullanici k ON r.kullanici_id = k.kullanici_id WHERE r.randevu_id = ? AND r.kullanici_id = ?');
        $randevu_kontrol->execute([$randevu_id, $kullanici['kullanici_id']]);
        $randevu_bilgi = $randevu_kontrol->fetch(PDO::FETCH_ASSOC);

        if ($randevu_bilgi) {
            $sil = $db->prepare("UPDATE randevu SET durum = 'iptal', iptal_tarihi = NOW() WHERE randevu_id = ? AND kullanici_id = ?");
            $sil->execute([$randevu_id, $kullanici['kullanici_id']]);

            // Sıra numaralarını yeniden hesapla
            $sira_reset = $db->prepare("UPDATE randevu SET sira_no = NULL WHERE doktor_id = ? AND randevu_tarih = ? AND durum = 'aktif'");
            $sira_reset->execute([$randevu_bilgi['doktor_id'], $randevu_bilgi['randevu_tarih']]);
            $yenile = $db->prepare("SELECT randevu_id FROM randevu WHERE doktor_id = ? AND randevu_tarih = ? AND durum = 'aktif' ORDER BY randevu_saat ASC");
            $yenile->execute([$randevu_bilgi['doktor_id'], $randevu_bilgi['randevu_tarih']]);
            $sira_counter = 1;
            while ($satir = $yenile->fetch(PDO::FETCH_ASSOC)) {
                $sira_up = $db->prepare("UPDATE randevu SET sira_no = ? WHERE randevu_id = ?");
                $sira_up->execute([$sira_counter++, $satir['randevu_id']]);
            }

            // E-posta bildirimi
            if (!empty($randevu_bilgi['kullanici_email'])) {
                $konu = "Randevu İptali - Hastane Otomasyonu";
                $mesaj = "<h2>Randevunuz İptal Edildi</h2>
                    <p><strong>Doktor:</strong> {$randevu_bilgi['randevu_doktoru']}</p>
                    <p><strong>Tarih:</strong> " . date('d.m.Y', strtotime($randevu_bilgi['randevu_tarih'])) . "</p>
                    <p><strong>Saat:</strong> " . date('H:i', strtotime($randevu_bilgi['randevu_saat'])) . "</p>
                    <p>Randevunuz başarıyla iptal edilmiştir.</p>";
                mail_gonder($randevu_bilgi['kullanici_email'], $konu, $mesaj);
            }

            header('location:randevu.php?durum=silindi');
            exit;
        }
    }

    header('location:randevu.php');
    exit;
}

// Randevu kayıt işlemi
if (isset($_POST['randevu_kayit'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('location:anasayfa.php?durum=guvenlik_hatasi');
        exit;
    }

    if (!isset($_SESSION['kullanici_tc']) || ($_SESSION['kullanici_rol'] ?? 'hasta') !== 'hasta') {
        header('location:index.php?durum=giris_gerekli');
        exit;
    }

    $tarih = isset($_POST['tarih']) ? $_POST['tarih'] : null;
    $doktor_id = isset($_POST['doktor_id']) ? intval($_POST['doktor_id']) : 0;
    $saat = isset($_POST['saat']) ? trim($_POST['saat']) : null;
    $kullanici_tc = $_SESSION['kullanici_tc'];

    if (!$tarih || !$doktor_id || !$saat) {
        header('location:anasayfa.php?durum=bos_alan');
        exit;
    }

    if (strtotime($tarih) < strtotime(date('Y-m-d'))) {
        header('location:anasayfa.php?durum=gecmis_tarih');
        exit;
    }

    if (!preg_match('/^\d{2}:\d{2}$/', $saat) || !in_array($saat, tum_saatler(), true)) {
        header('location:anasayfa.php?durum=gecersiz_saat');
        exit;
    }

    try {
        $db->beginTransaction();

        // Kullanıcı kontrolü
        $kullanici_sorgu = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ?');
        $kullanici_sorgu->execute([$kullanici_tc]);
        $kullanici = $kullanici_sorgu->fetch(PDO::FETCH_ASSOC);

        if (!$kullanici) {
            $db->rollBack();
            header('location:anasayfa.php?durum=kullanici_bulunamadi');
            exit;
        }

        // Doktor kontrolü
        $doktor_sorgu = $db->prepare('SELECT d.*, k.kullanici_adsoyad FROM doktor d JOIN kullanici k ON d.kullanici_id = k.kullanici_id WHERE d.doktor_id = ?');
        $doktor_sorgu->execute([$doktor_id]);
        $doktor = $doktor_sorgu->fetch(PDO::FETCH_ASSOC);

        if (!$doktor) {
            $db->rollBack();
            header('location:anasayfa.php?durum=hata');
            exit;
        }

        // Race condition önleme: aynı doktor+tarih+saaati kilitler
        $cakisma = $db->prepare("SELECT randevu_id FROM randevu WHERE doktor_id = ? AND randevu_tarih = ? AND randevu_saat = ? AND durum = 'aktif' FOR UPDATE");
        $cakisma->execute([$doktor_id, $tarih, $saat . ':00']);
        if ($cakisma->fetch()) {
            $db->rollBack();
            header('location:anasayfa.php?durum=dolu_saat');
            exit;
        }

        $sorgu = $db->prepare('INSERT INTO randevu SET
            kullanici_id = ?,
            doktor_id = ?,
            randevu_sehir = ?,
            randevu_tarih = ?,
            randevu_saat = ?,
            randevu_hastane = ?,
            randevu_klinik = ?,
            randevu_doktoru = ?
        ');

        $ekle = $sorgu->execute([
            $kullanici['kullanici_id'],
            $doktor_id,
            $doktor['sehir'],
            $tarih,
            $saat . ':00',
            $doktor['hastane'],
            $doktor['klinik'],
            $doktor['kullanici_adsoyad']
        ]);

        if ($ekle) {
            $yeni_id = $db->lastInsertId();

            // Sıra numarası ata (doktor + tarih bazında)
            $max_sira = $db->prepare("SELECT COALESCE(MAX(sira_no), 0) + 1 FROM randevu WHERE doktor_id = ? AND randevu_tarih = ? AND durum = 'aktif' AND randevu_id != ?");
            $max_sira->execute([$doktor_id, $tarih, $yeni_id]);
            $sira_no = $max_sira->fetchColumn();

            $sira_guncelle = $db->prepare("UPDATE randevu SET sira_no = ? WHERE randevu_id = ?");
            $sira_guncelle->execute([$sira_no, $yeni_id]);

            $db->commit();

            // E-posta bildirimi
            $mail_to = $db->prepare('SELECT kullanici_email FROM kullanici WHERE kullanici_id = ?');
            $mail_to->execute([$kullanici['kullanici_id']]);
            $email_adres = $mail_to->fetchColumn();
            if ($email_adres) {
                $tarih_fmt = date('d.m.Y', strtotime($tarih));
                $konu = "Randevu Onayı - Hastane Otomasyonu";
                $mesaj = "<h2>Randevunuz Oluşturuldu</h2>
                    <p><strong>Doktor:</strong> {$doktor['kullanici_adsoyad']}</p>
                    <p><strong>Klinik:</strong> {$doktor['klinik']}</p>
                    <p><strong>Tarih:</strong> {$tarih_fmt}</p>
                    <p><strong>Saat:</strong> {$saat}</p>
                    <p><strong>Hastane:</strong> {$doktor['hastane']}</p>
                    <p>Geçmiş olsun, randevunuz başarıyla kaydedildi.</p>";
                mail_gonder($email_adres, $konu, $mesaj);
            }

            header('location:randevu.php?durum=basarili');
            exit;
        }

        $db->rollBack();
        header('location:anasayfa.php?durum=hata');
        exit;
    } catch(PDOException $e) {
        if ($db->inTransaction()) $db->rollBack();
        error_log("Randevu kayıt hatası: " . $e->getMessage());
        // UNIQUE ihlali durumunda (yedek koruma)
        if ($e->getCode() == 23000 && strpos($e->getMessage(), 'uq_randevu_aktif') !== false) {
            header('location:anasayfa.php?durum=dolu_saat');
            exit;
        }
        header('location:anasayfa.php?durum=sistem_hatasi');
        exit;
    }
}

// Randevu güncelleme
if (isset($_POST['randevu_guncelle'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('location:anasayfa.php?durum=guvenlik_hatasi');
        exit;
    }
    if (!isset($_SESSION['kullanici_tc']) || ($_SESSION['kullanici_rol'] ?? 'hasta') !== 'hasta') {
        header('location:index.php?durum=giris_gerekli');
        exit;
    }

    $randevu_id = isset($_POST['randevu_id']) ? intval($_POST['randevu_id']) : 0;
    $tarih = isset($_POST['tarih']) ? $_POST['tarih'] : null;
    $doktor_id = isset($_POST['doktor_id']) ? intval($_POST['doktor_id']) : 0;
    $saat = isset($_POST['saat']) ? trim($_POST['saat']) : null;

    if (!$randevu_id || !$tarih || !$doktor_id || !$saat) {
        header('location:anasayfa.php?durum=bos_alan');
        exit;
    }
    if (strtotime($tarih) < strtotime(date('Y-m-d'))) {
        header('location:anasayfa.php?durum=gecmis_tarih');
        exit;
    }
    if (!preg_match('/^\d{2}:\d{2}$/', $saat) || !in_array($saat, tum_saatler(), true)) {
        header('location:anasayfa.php?durum=gecersiz_saat');
        exit;
    }

    try {
        $db->beginTransaction();

        $k_id = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ?');
        $k_id->execute([$_SESSION['kullanici_tc']]);
        $kullanici = $k_id->fetch(PDO::FETCH_ASSOC);
        if (!$kullanici) { $db->rollBack(); header('location:index.php'); exit; }

        $yetki = $db->prepare('SELECT randevu_id FROM randevu WHERE randevu_id = ? AND kullanici_id = ?');
        $yetki->execute([$randevu_id, $kullanici['kullanici_id']]);
        if (!$yetki->fetch()) { $db->rollBack(); header('location:randevu.php'); exit; }

        $doktor = $db->prepare('SELECT d.*, k.kullanici_adsoyad FROM doktor d JOIN kullanici k ON d.kullanici_id = k.kullanici_id WHERE d.doktor_id = ?');
        $doktor->execute([$doktor_id]);
        $d = $doktor->fetch(PDO::FETCH_ASSOC);
        if (!$d) { $db->rollBack(); header('location:anasayfa.php?durum=hata'); exit; }

        $cakisma = $db->prepare("SELECT randevu_id FROM randevu WHERE doktor_id = ? AND randevu_tarih = ? AND randevu_saat = ? AND durum = 'aktif' AND randevu_id != ? FOR UPDATE");
        $cakisma->execute([$doktor_id, $tarih, $saat . ':00', $randevu_id]);
        if ($cakisma->fetch()) { $db->rollBack(); header('location:anasayfa.php?durum=dolu_saat'); exit; }

        $guncelle = $db->prepare('UPDATE randevu SET randevu_tarih = ?, randevu_saat = ?, randevu_sehir = ?, randevu_hastane = ?, randevu_klinik = ?, randevu_doktoru = ? WHERE randevu_id = ?');
        $guncelle->execute([$tarih, $saat . ':00', $d['sehir'], $d['hastane'], $d['klinik'], $d['kullanici_adsoyad'], $randevu_id]);

        $db->commit();

        // E-posta bildirimi
        $mail_to = $db->prepare('SELECT kullanici_email FROM kullanici WHERE kullanici_id = ?');
        $mail_to->execute([$kullanici['kullanici_id']]);
        $email_adres = $mail_to->fetchColumn();
        if ($email_adres) {
            $konu = "Randevu Güncellendi - Hastane Otomasyonu";
            $mesaj = "<h2>Randevunuz Güncellendi</h2>
                <p><strong>Doktor:</strong> {$d['kullanici_adsoyad']}</p>
                <p><strong>Tarih:</strong> " . date('d.m.Y', strtotime($tarih)) . "</p>
                <p><strong>Saat:</strong> {$saat}</p>
                <p>Randevu bilgileriniz başarıyla güncellenmiştir.</p>";
            mail_gonder($email_adres, $konu, $mesaj);
        }

        header('location:randevu.php?durum=guncellendi');
        exit;
    } catch(PDOException $e) {
        if ($db->inTransaction()) $db->rollBack();
        error_log("Randevu güncelleme hatası: " . $e->getMessage());
        if ($e->getCode() == 23000 && strpos($e->getMessage(), 'uq_randevu_aktif') !== false) {
            header('location:anasayfa.php?durum=dolu_saat');
            exit;
        }
        header('location:anasayfa.php?durum=sistem_hatasi');
        exit;
    }
}

// Kullanıcı kayıt işlemi
if (isset($_POST['kullanicikaydet'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('location:uye.php?durum=guvenlik_hatasi');
        exit;
    }

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
            kullanici_email = ?,
            rol = ?
        ');
        $ekle = $sorgu->execute([
            $kullanici_tc, 
            $kullanici_adsoyad, 
            $hashed_password,
            $kullanici_telefon,
            $kullanici_email,
            'hasta'
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
    $giris_tipi = isset($_POST['giris_tipi']) ? $_POST['giris_tipi'] : 'hasta';
    
    if (!$kullanici_tc || !$kullanici_password) {
        header('location:index.php?durum=bos_alan');
        exit;
    }

    if (!in_array($giris_tipi, ['hasta', 'doktor'], true)) {
        $giris_tipi = 'hasta';
    }

    try {
        $sorgu = $db->prepare('SELECT * FROM kullanici WHERE kullanici_tc = ?');
        $sorgu->execute([$kullanici_tc]);
        $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);
        
        if ($kullanici && password_verify($kullanici_password, $kullanici['kullanici_password'])) {
            $kullanici_rol = $kullanici['rol'] ?? 'hasta';

            if ($kullanici_rol !== $giris_tipi && $kullanici_rol !== 'admin') {
                header('location:index.php?durum=yanlis_rol&tip=' . $giris_tipi);
                exit;
            }

            oturum_kur($kullanici, $db);

            if ($kullanici_rol === 'admin') {
                header('location:admin_panel.php');
            } elseif ($kullanici_rol === 'doktor') {
                header('location:doktor_panel.php');
            } else {
                header('location:anasayfa.php');
            }
            exit;
        }

        header('location:index.php?durum=hatali_giris&tip=' . $giris_tipi);
        exit;
    } catch(PDOException $e) {
        error_log("Giriş hatası: " . $e->getMessage());
        header('location:index.php?durum=sistem_hatasi');
        exit;
    }
}

// Şifre Sıfırlama
if (isset($_POST['sifre_sifirla'])) {
    $kullanici_tc = isset($_POST['kullanici_tc']) ? trim($_POST['kullanici_tc']) : null;
    $kullanici_email = isset($_POST['kullanici_email']) ? trim($_POST['kullanici_email']) : null;
    $yeni_sifre = isset($_POST['yeni_sifre']) ? $_POST['yeni_sifre'] : null;

    if (!$kullanici_tc || !$kullanici_email || !$yeni_sifre) {
        header('location:sifre_sifirlama.php?durum=bos_alan');
        exit;
    }

    if (!preg_match('/^[0-9]{11}$/', $kullanici_tc)) {
        header('location:sifre_sifirlama.php?durum=gecersiz_tc');
        exit;
    }

    if (strlen($yeni_sifre) < 6) {
        header('location:sifre_sifirlama.php?durum=kisa_sifre');
        exit;
    }

    try {
        $sorgu = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ? AND kullanici_email = ?');
        $sorgu->execute([$kullanici_tc, $kullanici_email]);
        $kullanici = $sorgu->fetch(PDO::FETCH_ASSOC);

        if (!$kullanici) {
            header('location:sifre_sifirlama.php?durum=hatali_giris');
            exit;
        }

        $hashed = password_hash($yeni_sifre, PASSWORD_DEFAULT);
        $guncelle = $db->prepare('UPDATE kullanici SET kullanici_password = ? WHERE kullanici_id = ?');
        $guncelle->execute([$hashed, $kullanici['kullanici_id']]);

        // E-posta bildirimi
        $to = $kullanici_email;
        $subject = "Hastane Otomasyonu - Şifreniz Sıfırlandı";
        $message = "Sayın {$kullanici_tc},\n\nŞifreniz başarıyla sıfırlanmıştır.\n\nYeni şifrenizle giriş yapabilirsiniz.\n\nHastane Otomasyonu";
        $headers = "From: noreply@hastaneotomasyonu.com";
        @mail($to, $subject, $message, $headers);

        header('location:index.php?durum=basarili_kayit');
        exit;
    } catch(PDOException $e) {
        error_log("Şifre sıfırlama hatası: " . $e->getMessage());
        header('location:sifre_sifirlama.php?durum=sistem_hatasi');
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

// Admin: Kullanıcı aktif/pasif
if (isset($_POST['admin_kullanici_toggle'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('location:admin_panel.php?durum=guvenlik_hatasi');
        exit;
    }
    if (!isset($_SESSION['kullanici_rol']) || $_SESSION['kullanici_rol'] !== 'admin') {
        header('location:index.php');
        exit;
    }
    $kullanici_id = intval($_POST['admin_kullanici_id']);
    try {
        $mevcut = $db->prepare('SELECT aktif FROM kullanici WHERE kullanici_id = ? AND rol != ?');
        $mevcut->execute([$kullanici_id, 'admin']);
        $row = $mevcut->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $yeni_durum = $row['aktif'] ? 0 : 1;
            $guncelle = $db->prepare('UPDATE kullanici SET aktif = ? WHERE kullanici_id = ?');
            $guncelle->execute([$yeni_durum, $kullanici_id]);
        }
        header('location:admin_panel.php?tab=kullanicilar&durum=guncellendi');
        exit;
    } catch(PDOException $e) {
        error_log("Admin kullanıcı toggle hatası: " . $e->getMessage());
        header('location:admin_panel.php?durum=sistem_hatasi');
        exit;
    }
}

// Admin: Doktor ekle
if (isset($_POST['admin_doktor_ekle'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('location:admin_panel.php?durum=guvenlik_hatasi');
        exit;
    }
    if (!isset($_SESSION['kullanici_rol']) || $_SESSION['kullanici_rol'] !== 'admin') {
        header('location:index.php');
        exit;
    }

    $adsoyad = isset($_POST['kullanici_adsoyad']) ? htmlspecialchars(trim($_POST['kullanici_adsoyad'])) : null;
    $tc = isset($_POST['kullanici_tc']) ? trim($_POST['kullanici_tc']) : null;
    $email = isset($_POST['kullanici_email']) ? trim($_POST['kullanici_email']) : null;
    $telefon = isset($_POST['kullanici_telefon']) ? trim($_POST['kullanici_telefon']) : null;
    $password = isset($_POST['kullanici_password']) ? $_POST['kullanici_password'] : null;
    $sehir = isset($_POST['doktor_sehir']) ? htmlspecialchars(trim($_POST['doktor_sehir'])) : null;
    $hastane = isset($_POST['doktor_hastane']) ? htmlspecialchars(trim($_POST['doktor_hastane'])) : null;
    $klinik = isset($_POST['doktor_klinik']) ? htmlspecialchars(trim($_POST['doktor_klinik'])) : null;

    if (!$adsoyad || !$tc || !$email || !$telefon || !$password || !$sehir || !$hastane || !$klinik) {
        header('location:admin_panel.php?tab=doktor-ekle&durum=bos_alan');
        exit;
    }
    if (!preg_match('/^[0-9]{11}$/', $tc)) {
        header('location:admin_panel.php?tab=doktor-ekle&durum=gecersiz_tc');
        exit;
    }
    if (strlen($password) < 6) {
        header('location:admin_panel.php?tab=doktor-ekle&durum=kisa_sifre');
        exit;
    }

    try {
        $db->beginTransaction();

        $kontrol = $db->prepare('SELECT kullanici_id FROM kullanici WHERE kullanici_tc = ?');
        $kontrol->execute([$tc]);
        if ($kontrol->fetch()) {
            $db->rollBack();
            header('location:admin_panel.php?tab=doktor-ekle&durum=mukerrer_kayit');
            exit;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $ekle = $db->prepare('INSERT INTO kullanici (kullanici_tc, kullanici_password, kullanici_adsoyad, kullanici_email, kullanici_telefon, rol, aktif, kayit_tarihi) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())');
        $ekle->execute([$tc, $hashed, $adsoyad, $email, $telefon, 'doktor']);

        $kullanici_id = $db->lastInsertId();

        $doktor_ekle = $db->prepare('INSERT INTO doktor (kullanici_id, sehir, hastane, klinik) VALUES (?, ?, ?, ?)');
        $doktor_ekle->execute([$kullanici_id, $sehir, $hastane, $klinik]);

        $db->commit();
        header('location:admin_panel.php?tab=doktor-ekle&durum=basarili_kayit');
        exit;
    } catch(PDOException $e) {
        if ($db->inTransaction()) $db->rollBack();
        error_log("Admin doktor ekleme hatası: " . $e->getMessage());
        header('location:admin_panel.php?tab=doktor-ekle&durum=sistem_hatasi');
        exit;
    }
}

// Doktor çalışma saatleri kaydet
if (isset($_POST['doktor_saat_kaydet'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('location:doktor_panel.php?filtre=saatler&durum=guvenlik_hatasi');
        exit;
    }
    if (!isset($_SESSION['kullanici_rol']) || $_SESSION['kullanici_rol'] !== 'doktor') {
        header('location:index.php');
        exit;
    }

    $doktor_id = $_SESSION['doktor_id'] ?? 0;
    if (!$doktor_id) {
        header('location:islem.php?islem=cikis');
        exit;
    }

    try {
        $db->beginTransaction();
        $sil = $db->prepare('DELETE FROM doktor_calisma_saat WHERE doktor_id = ?');
        $sil->execute([$doktor_id]);

        $ekle = $db->prepare('INSERT INTO doktor_calisma_saat (doktor_id, gun, baslangic, bitis) VALUES (?, ?, ?, ?)');
        $gun_adi = ['', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'];

        for ($gun = 1; $gun <= 7; $gun++) {
            $calisiyor = isset($_POST["calisma_gun_$gun"]) ? intval($_POST["calisma_gun_$gun"]) : 0;
            if ($calisiyor) {
                $baslangic = isset($_POST["baslangic_$gun"]) ? trim($_POST["baslangic_$gun"]) : '09:00';
                $bitis = isset($_POST["bitis_$gun"]) ? trim($_POST["bitis_$gun"]) : '17:00';
                if (preg_match('/^\d{2}:\d{2}$/', $baslangic) && preg_match('/^\d{2}:\d{2}$/', $bitis)) {
                    $ekle->execute([$doktor_id, $gun, $baslangic . ':00', $bitis . ':00']);
                }
            }
        }

        $db->commit();
        header('location:doktor_panel.php?filtre=saatler&durum=guncellendi');
        exit;
    } catch(PDOException $e) {
        if ($db->inTransaction()) $db->rollBack();
        error_log("Doktor saat kaydetme hatası: " . $e->getMessage());
        header('location:doktor_panel.php?filtre=saatler&durum=sistem_hatasi');
        exit;
    }
}

// Doktor not kaydet
if (isset($_POST['randevu_not_kaydet'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('location:doktor_panel.php?durum=guvenlik_hatasi');
        exit;
    }
    if (!isset($_SESSION['kullanici_rol']) || $_SESSION['kullanici_rol'] !== 'doktor') {
        header('location:index.php');
        exit;
    }

    $randevu_id = isset($_POST['not_randevu_id']) ? intval($_POST['not_randevu_id']) : 0;
    $not_metni = isset($_POST['not_metni']) ? trim($_POST['not_metni']) : null;
    $doktor_id = $_SESSION['doktor_id'] ?? 0;

    if (!$randevu_id || !$not_metni || !$doktor_id) {
        header("location:randevu_not.php?randevu_id=$randevu_id&durum=bos_alan");
        exit;
    }

    try {
        $ekle = $db->prepare('INSERT INTO randevu_not (randevu_id, doktor_id, not_metni, olusturma_tarihi) VALUES (?, ?, ?, NOW())');
        $ekle->execute([$randevu_id, $doktor_id, htmlspecialchars($not_metni)]);
        header("location:randevu_not.php?randevu_id=$randevu_id&durum=guncellendi");
        exit;
    } catch(PDOException $e) {
        error_log("Not kaydetme hatasi: " . $e->getMessage());
        header("location:randevu_not.php?randevu_id=$randevu_id&durum=sistem_hatasi");
        exit;
    }
}

// Reçete kaydet
if (isset($_POST['recete_kaydet'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('location:doktor_panel.php?durum=guvenlik_hatasi');
        exit;
    }
    if (!isset($_SESSION['kullanici_rol']) || $_SESSION['kullanici_rol'] !== 'doktor') {
        header('location:index.php');
        exit;
    }

    $randevu_id = isset($_POST['recete_randevu_id']) ? intval($_POST['recete_randevu_id']) : 0;
    $ilaclar = isset($_POST['ilaclar']) ? trim($_POST['ilaclar']) : null;
    $doktor_id = $_SESSION['doktor_id'] ?? 0;

    if (!$randevu_id || !$ilaclar || !$doktor_id) {
        header("location:recete.php?randevu_id=$randevu_id&durum=bos_alan");
        exit;
    }

    try {
        $rs = $db->prepare("SELECT kullanici_id FROM randevu WHERE randevu_id = ? AND doktor_id = ?");
        $rs->execute([$randevu_id, $doktor_id]);
        $row = $rs->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            header("location:recete.php?randevu_id=$randevu_id&durum=hata");
            exit;
        }

        $ekle = $db->prepare('INSERT INTO recete (randevu_id, doktor_id, hasta_id, ilaclar, olusturma_tarihi) VALUES (?, ?, ?, ?, NOW())');
        $ekle->execute([$randevu_id, $doktor_id, $row['kullanici_id'], htmlspecialchars($ilaclar)]);

        header("location:doktor_panel.php?durum=guncellendi");
        exit;
    } catch(PDOException $e) {
        error_log("Reçete kaydetme hatası: " . $e->getMessage());
        header("location:recete.php?randevu_id=$randevu_id&durum=sistem_hatasi");
        exit;
    }
}
?>