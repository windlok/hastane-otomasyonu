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
?>