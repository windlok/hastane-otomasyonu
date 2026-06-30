-- Doktor/hasta rol sistemi ve saat bazlı randevu
USE hastane_otomasyonu;

ALTER TABLE kullanici ADD COLUMN rol ENUM('hasta', 'doktor') NOT NULL DEFAULT 'hasta';

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

ALTER TABLE randevu ADD COLUMN doktor_id INT NULL;
ALTER TABLE randevu ADD COLUMN randevu_saat TIME NULL;

ALTER TABLE randevu ADD CONSTRAINT fk_randevu_doktor
  FOREIGN KEY (doktor_id) REFERENCES doktor(doktor_id)
  ON DELETE SET NULL;

CREATE INDEX idx_randevu_doktor_tarih_saat ON randevu(doktor_id, randevu_tarih, randevu_saat);
