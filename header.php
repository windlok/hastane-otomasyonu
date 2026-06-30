<?php 

ob_start();
session_start();

include 'bagla.php';

// CSRF Token üretme
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function csrf_kontrol() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('location:index.php?durum=guvenlik_hatasi');
        exit;
    }
}

// Hata/başarı mesajları için yardımcı fonksiyon
function mesaj_goster($durum) {
    $mesajlar = [
        'basarili' => ['class' => 'alert-success', 'mesaj' => 'Randevunuz başarıyla oluşturuldu! Geçmiş olsun dileriz.'],
        'basarili_kayit' => ['class' => 'alert-success', 'mesaj' => 'Kayıt işlemi başarılı! Lütfen giriş yapınız.'],
        'bos_alan' => ['class' => 'alert-danger', 'mesaj' => 'Lütfen tüm alanları doldurun!'],
        'hatali_giris' => ['class' => 'alert-danger', 'mesaj' => 'TC Kimlik veya şifre hatalı!'],
        'giris_gerekli' => ['class' => 'alert-warning', 'mesaj' => 'Bu işlemi yapabilmek için giriş yapmalısınız!'],
        'guvenlik_hatasi' => ['class' => 'alert-danger', 'mesaj' => 'Güvenlik doğrulaması başarısız (CSRF)!'],
        'sistem_hatasi' => ['class' => 'alert-danger', 'mesaj' => 'Bir sistem hatası oluştu, lütfen daha sonra tekrar deneyin.'],
        'gecmis_tarih' => ['class' => 'alert-danger', 'mesaj' => 'Geçmiş bir tarihe randevu alamazsınız!'],
        'kullanici_bulunamadi' => ['class' => 'alert-danger', 'mesaj' => 'Kullanıcı bulunamadı!'],
        'hata' => ['class' => 'alert-danger', 'mesaj' => 'İşlem sırasında bir hata oluştu!'],
        'gecersiz_tc' => ['class' => 'alert-danger', 'mesaj' => 'Lütfen 11 haneli geçerli bir TC Kimlik numarası girin!'],
        'kisa_sifre' => ['class' => 'alert-danger', 'mesaj' => 'Şifreniz en az 6 karakter olmalıdır!'],
        'mukerrer_kayit' => ['class' => 'alert-danger', 'mesaj' => 'Bu TC Kimlik veya E-posta adresiyle zaten bir kayıt bulunmaktadır!'],
        'silindi' => ['class' => 'alert-success', 'mesaj' => 'Randevunuz başarıyla iptal edildi.'],
        'guncellendi' => ['class' => 'alert-success', 'mesaj' => 'Profil bilgileriniz başarıyla güncellendi.'],
        'bos_adsoyad' => ['class' => 'alert-danger', 'mesaj' => 'Ad Soyad alanı boş bırakılamaz!'],
        'mukerrer_email' => ['class' => 'alert-danger', 'mesaj' => 'Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor!'],
        'hatali_sifre' => ['class' => 'alert-danger', 'mesaj' => 'Mevcut şifrenizi yanlış girdiniz!'],
        'yanlis_rol' => ['class' => 'alert-danger', 'mesaj' => 'Bu giriş türü hesabınızla eşleşmiyor! Lütfen doğru panelden giriş yapın.'],
        'dolu_saat' => ['class' => 'alert-danger', 'mesaj' => 'Seçtiğiniz saat dolu. Lütfen başka bir saat seçin.'],
        'gecersiz_saat' => ['class' => 'alert-danger', 'mesaj' => 'Geçersiz veya müsait olmayan bir saat seçtiniz.'],
        'yetkisiz' => ['class' => 'alert-danger', 'mesaj' => 'Bu sayfaya erişim yetkiniz yok!'],
    ];
    
    if (isset($mesajlar[$durum])) {
        $m = $mesajlar[$durum];
        echo '<div class="alert ' . $m['class'] . '">' . $m['mesaj'] . '</div>';
    }
}

// Oturum kontrolü
function oturum_kontrol() {
    if (!isset($_SESSION['kullanici_tc'])) {
        header('location:index.php');
        exit;
    }
}

function kullanici_rol() {
    return $_SESSION['kullanici_rol'] ?? 'hasta';
}

function hasta_kontrol() {
    oturum_kontrol();
    if (kullanici_rol() !== 'hasta') {
        header('location:doktor_panel.php?durum=yetkisiz');
        exit;
    }
}

function doktor_kontrol() {
    oturum_kontrol();
    if (kullanici_rol() !== 'doktor') {
        header('location:anasayfa.php?durum=yetkisiz');
        exit;
    }
}

function tum_saatler() {
    $saatler = [];
    for ($saat = 9; $saat < 17; $saat++) {
        $saatler[] = sprintf('%02d:00', $saat);
        $saatler[] = sprintf('%02d:30', $saat);
    }
    return $saatler;
}

function musait_saatler(PDO $db, int $doktor_id, string $tarih) {
    if (!$doktor_id || !$tarih) {
        return [];
    }

    $stmt = $db->prepare("SELECT TIME_FORMAT(randevu_saat, '%H:%i') AS saat FROM randevu WHERE doktor_id = ? AND randevu_tarih = ? AND durum = 'aktif'");
    $stmt->execute([$doktor_id, $tarih]);
    $dolu = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $musait = array_values(array_diff(tum_saatler(), $dolu));

    if ($tarih === date('Y-m-d')) {
        $simdi = date('H:i');
        $musait = array_values(array_filter($musait, fn($s) => $s > $simdi));
    }

    return $musait;
}

function oturum_kur($kullanici, PDO $db) {
    $_SESSION['kullanici_tc'] = $kullanici['kullanici_tc'];
    $_SESSION['kullanici_adsoyad'] = $kullanici['kullanici_adsoyad'];
    $_SESSION['kullanici_id'] = $kullanici['kullanici_id'];
    $_SESSION['kullanici_rol'] = $kullanici['rol'] ?? 'hasta';

    $guncelle = $db->prepare('UPDATE kullanici SET son_giris = NOW() WHERE kullanici_id = ?');
    $guncelle->execute([$kullanici['kullanici_id']]);

    if ($_SESSION['kullanici_rol'] === 'doktor') {
        $doktor = $db->prepare('SELECT doktor_id FROM doktor WHERE kullanici_id = ?');
        $doktor->execute([$kullanici['kullanici_id']]);
        $doktor_kayit = $doktor->fetch(PDO::FETCH_ASSOC);
        $_SESSION['doktor_id'] = $doktor_kayit['doktor_id'] ?? null;
    } else {
        unset($_SESSION['doktor_id']);
    }
}
?>