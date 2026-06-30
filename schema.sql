-- Hastane Otomasyonu - Veritabanı Şeması
-- MySQL / MariaDB
-- Sürüm: 2.0 (Migration destekli)

CREATE DATABASE IF NOT EXISTS hastane_otomasyonu
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_turkish_ci;

USE hastane_otomasyonu;

-- Migration takip tablosu
CREATE TABLE IF NOT EXISTS migrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  migration_name VARCHAR(255) NOT NULL,
  executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ana kullanıcı tablosu
CREATE TABLE IF NOT EXISTS kullanici (
  kullanici_id INT AUTO_INCREMENT PRIMARY KEY,
  kullanici_tc VARCHAR(11) NOT NULL UNIQUE,
  kullanici_adsoyad VARCHAR(120) NOT NULL,
  kullanici_password VARCHAR(255) NOT NULL,
  kullanici_telefon VARCHAR(15) DEFAULT NULL,
  kullanici_email VARCHAR(150) DEFAULT NULL,
  rol ENUM('hasta', 'doktor') NOT NULL DEFAULT 'hasta',
  kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  guncelleme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  son_giris TIMESTAMP NULL DEFAULT NULL,
  aktif TINYINT(1) DEFAULT 1
);

-- Doktor profil tablosu
CREATE TABLE IF NOT EXISTS doktor (
  doktor_id INT AUTO_INCREMENT PRIMARY KEY,
  kullanici_id INT NOT NULL UNIQUE,
  klinik VARCHAR(150) NOT NULL,
  hastane VARCHAR(150) NOT NULL,
  sehir VARCHAR(50) NOT NULL,
  CONSTRAINT fk_doktor_kullanici
    FOREIGN KEY (kullanici_id) REFERENCES kullanici(kullanici_id)
    ON DELETE CASCADE
);

-- Randevu tablosu
CREATE TABLE IF NOT EXISTS randevu (
  randevu_id INT AUTO_INCREMENT PRIMARY KEY,
  kullanici_id INT NOT NULL,
  doktor_id INT NULL,
  randevu_sehir VARCHAR(50) NOT NULL,
  randevu_tarih DATE NOT NULL,
  randevu_saat TIME NULL,
  randevu_hastane VARCHAR(150) NOT NULL,
  randevu_klinik VARCHAR(150) NOT NULL,
  randevu_doktoru VARCHAR(150) NOT NULL,
  olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  durum ENUM('aktif', 'iptal', 'tamamlandi') DEFAULT 'aktif',
  iptal_tarihi TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT fk_randevu_kullanici
    FOREIGN KEY (kullanici_id) REFERENCES kullanici(kullanici_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_randevu_doktor
    FOREIGN KEY (doktor_id) REFERENCES doktor(doktor_id)
    ON DELETE SET NULL
);

-- İndeksler (uq_ = aynı doktor-aynı saat-aynı tarihe çift kaydı engeller)
CREATE INDEX idx_kullanici_tc ON kullanici(kullanici_tc);
CREATE INDEX idx_kullanici_email ON kullanici(kullanici_email);
CREATE INDEX idx_randevu_kullanici_tarih ON randevu(kullanici_id, randevu_tarih);
-- UNIQUE: aynı doktor + aynı tarih + aynı saat için yalnız bir aktif randevu
CREATE UNIQUE INDEX uq_randevu_aktif ON randevu(doktor_id, randevu_tarih, randevu_saat);

-- Migration kaydı: İlk şema

-- Not: Migration'ları çalıştırmak için: php migrate.php
