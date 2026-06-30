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
- **Randevu Düzenleme:** Alınmış randevuları güncelleme (doktor/tarih/saat değiştirme).
- **Şifre Sıfırlama:** TC kimlik ve e-posta doğrulaması ile şifre yenileme.
- **Admin Paneli:** Kullanıcı yönetimi (aktif/pasif), randevu listesi, doktor ekleme.
- **Doktor Çalışma Saatleri:** Haftalık bazda çalışma günleri ve saat ayarları.
- **E-posta Bildirimleri:** Randevu alma, güncelleme, iptal ve şifre sıfırlama olaylarında e-posta bildirimi.
- **PDF Rapor:** Tarih aralığına göre randevu raporu (PDF çıktı / yazdırma).
- **Tıbbi Notlar:** Doktorların randevulara tıbbi not eklemesi, hastaların görüntülemesi.
- **Online Reçete Sistemi:** İlaç listesi (İlaç | Doz | Süre | Açıklama) şeklinde reçete yazma ve görüntüleme.
- **Kuyruk / Sıra Yönetimi:** Otomatik sıra numarası, hasta sıra takibi, doktor sıra listesi (30sn otomatik yenileme).
- **Çoklu Dil Desteği:** Türkçe / İngilizce dil seçeneği (navbar switcher).

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

### Admin
1. **Giriş:** `http://localhost/hastane/admin/` adresinden kullanıcı adı (`admin`) ve şifre (`password`) ile giriş.
2. **Panel:** Kullanıcıları aktif/pasif yapma, tüm randevuları görme, yeni doktor ekleme.

### Demo Hesapları

| Rol | TC / Kullanıcı Adı | Şifre |
|-----|---------------------|-------|
| Admin | `admin` (kullanıcı adı) | `password` |
| Dr. Ahmet Yılmaz (Kardiyoloji) | 11111111110 | doktor123 |
| Dr. Ayşe Demir (Kadın Doğum) | 22222222220 | doktor123 |
| Dr. Mehmet Kaya (Ortopedi) | 33333333330 | doktor123 |
| Dr. Zeynep Öztürk (Nöroloji) | 44444444440 | doktor123 |

### Sıra / Kuyruk Ekranı
Bekleme salonu TV'leri için: `http://localhost/hastane/sira_ekrani.php` (anonim, oturum gerekmez, 15sn otomatik yenileme).
