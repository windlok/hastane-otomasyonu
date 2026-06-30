-- Ek-1: Aynı doktor + aynı tarih + aynı saat çift kaydını engelle
USE hastane_otomasyonu;

-- Önce olası tekrarları temizle (varsa)
DELETE r1 FROM randevu r1
INNER JOIN randevu r2
  ON r1.doktor_id = r2.doktor_id
 AND r1.randevu_tarih = r2.randevu_tarih
 AND r1.randevu_saat = r2.randevu_saat
 AND r1.durum = 'aktif' AND r2.durum = 'aktif'
 AND r1.randevu_id > r2.randevu_id;

-- UNIQUE index: veritabanı seviyesinde çift kayıt koruması
CREATE UNIQUE INDEX uq_randevu_aktif ON randevu(doktor_id, randevu_tarih, randevu_saat);

INSERT IGNORE INTO migrations (migration_name) VALUES ('005_uq_randevu_aktif');
