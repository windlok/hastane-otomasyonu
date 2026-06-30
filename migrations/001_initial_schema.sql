-- İlk kurulum: veritabanı ve temel tablolar
CREATE DATABASE IF NOT EXISTS hastane_otomasyonu
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_turkish_ci;

USE hastane_otomasyonu;

CREATE TABLE IF NOT EXISTS kullanici (
  kullanici_id INT AUTO_INCREMENT PRIMARY KEY,
  kullanici_tc VARCHAR(11) NOT NULL UNIQUE,
  kullanici_adsoyad VARCHAR(120) NOT NULL,
  kullanici_password VARCHAR(255) NOT NULL,
  kullanici_telefon VARCHAR(15) DEFAULT NULL,
  kullanici_email VARCHAR(150) DEFAULT NULL,
  kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  son_giris TIMESTAMP NULL DEFAULT NULL,
  aktif TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS randevu (
  randevu_id INT AUTO_INCREMENT PRIMARY KEY,
  kullanici_id INT NOT NULL,
  randevu_sehir VARCHAR(50) NOT NULL,
  randevu_tarih DATE NOT NULL,
  randevu_hastane VARCHAR(150) NOT NULL,
  randevu_klinik VARCHAR(150) NOT NULL,
  randevu_doktoru VARCHAR(150) NOT NULL,
  olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  durum ENUM('aktif', 'iptal', 'tamamlandi') DEFAULT 'aktif',
  iptal_tarihi TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT fk_randevu_kullanici
    FOREIGN KEY (kullanici_id) REFERENCES kullanici(kullanici_id)
    ON DELETE CASCADE
);
