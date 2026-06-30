# 🏥 Hastane Otomasyonu (PHP + MySQL)

Bu proje, bir hastaneye sunulabilecek düzeyde geliştirilmiş, modern ve güvenli bir **hastane randevu otomasyonu** sistemidir.

## ✨ Özellikler

- **Hasta / Doktor Ayrımı:** Ayrı giriş sekmeleri; hastalar randevu alır, doktorlar kendi panelinden hastalarını görür.
- **Saat Bazlı Randevu:** 09:00–16:30 arası 30 dakikalık slotlar; dolu saatler otomatik gizlenir.
- **Çakışma Engeli:** Aynı doktor + tarih + saate ikinci randevu alınamaz.
- **Modern Tasarım:** Tamamen responsive, Inter fontlu, profesyonel renk paletine sahip, sağlık sektörüne uygun modern arayüz tasarımı.
- **Güvenli Üyelik Sistemi:** `password_hash()` ve `password_verify()` ile veri güvenliği. TC Kimlik format kontrolü ve e-posta kontrolü.
- **CSRF Koruması:** Tüm oturum ve işlem formlarında CSRF (Cross-Site Request Forgery) koruması; randevu silme dahil POST ile yapılır.
- **Çakışma Engeli:** Transaction + `SELECT FOR UPDATE` + UNIQUE index ile aynı doktor + tarih + saate ikinci randevu alınamaz.
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

### Hasta
1. **Kayıt Ol:** Giriş ekranında "Hemen Üye Olun" ile hasta hesabı oluşturun.
2. **Giriş Yap:** "Hasta" sekmesinden TC ve şifre ile giriş yapın.
3. **Randevu Al:** Doktor, tarih ve müsait saat seçerek randevu alın.
4. **Randevularım:** Randevularınızı görüntüleyin veya iptal edin.

### Doktor
1. **Giriş Yap:** "Doktor" sekmesinden TC ve şifre ile giriş yapın.
2. **Hasta Listesi:** Bugün / yaklaşan / tüm randevuları hasta bilgileriyle görün.
3. **Detay:** Her randevuda hasta adı, TC, telefon, e-posta, tarih ve saat bilgisi yer alır.

### Demo Doktor Hesapları
Migration sonrası otomatik oluşturulur (şifre hepsi için `doktor123`):

| Doktor | TC | Klinik |
|--------|-----|--------|
| Dr. Ahmet Yılmaz | 11111111110 | Kardiyoloji |
| Dr. Ayşe Demir | 22222222220 | Kadın Doğum |
| Dr. Mehmet Kaya | 33333333330 | Ortopedi |
| Dr. Zeynep Öztürk | 44444444440 | Nöroloji |
