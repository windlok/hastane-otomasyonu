# 🏥 Hastane Otomasyonu (PHP + MySQL)

Bu proje, bir hastaneye sunulabilecek düzeyde geliştirilmiş, modern ve güvenli bir **hastane randevu otomasyonu** sistemidir.

## ✨ Özellikler

- **Modern Tasarım:** Tamamen responsive, Inter fontlu, profesyonel renk paletine sahip, sağlık sektörüne uygun modern arayüz tasarımı.
- **Güvenli Üyelik Sistemi:** `password_hash()` ve `password_verify()` ile veri güvenliği. TC Kimlik format kontrolü ve e-posta kontrolü.
- **CSRF Koruması:** Tüm formlarda CSRF (Cross-Site Request Forgery) koruması.
- **Randevu Yönetimi:** Şehir, hastane, klinik ve doktor seçimiyle randevu alma. Geçmiş tarihlere randevu alımının engellenmesi.
- **Detaylı İstatistikler:** Profilinizde toplam randevu, yaklaşan randevu ve geçmiş randevu adetlerinin anlık takibi.
- **Profil Yönetimi:** Şifre, telefon, e-posta ve isim-soyad bilgilerini güvenli bir şekilde güncelleyebilme paneli.
- **Kolay İptal:** Yaklaşan randevuları tek tıkla iptal edebilme imkanı.

## 🛠️ Teknolojiler

- **Backend:** PHP 8+ (PDO ile güvenli MySQL bağlantısı)
- **Frontend:** HTML5, CSS3 (Modern HSL Değişkenleri, Flexbox, Grid)
- **Veritabanı:** MySQL / MariaDB

## 📦 Kurulum

1. **Projeyi Web Kök Dizinine Kopyalayın:**
   - XAMPP kullanıyorsanız: `C:\xampp\htdocs\hastane` dizinine kopyalayın veya projeniz için bir Symbolic Link oluşturun.

2. **Veritabanını Kurun:**
   - Önerilen yöntem — migration çalıştırıcı:
     ```bash
     php migrate.php
     ```
   - Alternatif: phpMyAdmin veya MySQL CLI ile `schema.sql` dosyasını içeri aktarın:
     ```bash
     mysql -u root -p < schema.sql
     ```

3. **Veritabanı Bağlantısını Yapılandırın (`bagla.php`):**
   ```php
   $db = new PDO(
       'mysql:host=localhost;dbname=hastane_otomasyonu;charset=utf8mb4',
       'root',
       'SIFRENIZ',
       [
           PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
           PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
           PDO::ATTR_EMULATE_PREPARES => false,
       ]
   );
   ```

4. **Uygulamayı Başlatın:**
   Tarayıcınızdan `http://localhost/hastane/` adresine gidin.

## 👥 Kullanım Akışı

1. **Kayıt Ol:** Giriş ekranında "Hemen Üye Olun" linkine tıklayarak Ad Soyad, TC, Telefon, E-posta ve Şifre bilgileriyle kayıt oluşturun.
2. **Giriş Yap:** Oluşturduğunuz üyelikle güvenli bir şekilde giriş yapın.
3. **Randevu Al:** İstediğiniz tarihi seçerek (geçmiş tarihler engellenmiştir) randevunuzu kaydedin.
4. **Randevularım:** Alınan randevuları kontrol edin veya yaklaşan bir randevunuzu iptal edin.
5. **Hesabım:** Profil bilgilerinizi güncelleyin ya da şifrenizi değiştirin.
