-- Mevcut veritabanlarını v2 şemasına yükselt
USE hastane_otomasyonu;

ALTER TABLE kullanici ADD COLUMN guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE kullanici ADD COLUMN son_giris TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE kullanici ADD COLUMN aktif TINYINT(1) DEFAULT 1;

ALTER TABLE randevu ADD COLUMN durum ENUM('aktif', 'iptal', 'tamamlandi') DEFAULT 'aktif';
ALTER TABLE randevu ADD COLUMN iptal_tarihi TIMESTAMP NULL DEFAULT NULL;
